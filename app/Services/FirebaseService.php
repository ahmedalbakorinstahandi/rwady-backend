<?php

namespace App\Services;

use App\Http\Services\NotificationService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\Notification;
use Laravel\Sanctum\PersonalAccessToken;

class FirebaseService
{
    public static $firebaseMessaging;

    /**
     * Subscribe to a topic using a token.
     */
    public static function subscribeToTopic($registrationToken, $topic)
    {
        if (!self::isValidTopic($topic)) {
            return [
                'success' => false,
                'message' => 'Topic name format is invalid',
            ];
        }

        $messaging = self::getFirebaseMessaging()->createMessaging();

        try {
            $response = $messaging->subscribeToTopic($topic, $registrationToken);
            return [
                'success' => true,
                'message' => 'Successfully subscribed to topic',
                'response' => $response,
            ];
        } catch (\Throwable $e) {
            return self::handleException($e);
        }
    }


    public static function subscribeToAllTopic($request, $user)
    {


        $deviceToken = $request->device_token;

        Log::info('device_token' . $deviceToken);

        $latestToken = $user->tokens()->latest()->first();

        Log::info('latestToken' . $latestToken);
        if ($latestToken) {
            DB::table('personal_access_tokens')
                ->where('id', $latestToken->id)
                ->update(['device_token' => $deviceToken]);
        }

        $APP_ENV_TYPE = env('APP_ENV_TYPE', 'staging');

        $topics = [
            'user-' . $user->id,
            'role-' . $user->role,
            'all-users',
        ];

        if ($APP_ENV_TYPE != 'production') {
            for ($i = 0; $i < count($topics); $i++) {
                $topics[$i] = $topics[$i] . '-' . $APP_ENV_TYPE;
            }
        }


        foreach ($topics as $topic) {

            $subscriptionResult = FirebaseService::subscribeToTopic($deviceToken, $topic);

            // i need store device token in personal access token table

            if (!$subscriptionResult['success']) {
                Log::error('Failed to subscribe to topic', [
                    'topic' => $topic,
                    'device_token' => $deviceToken,
                    'error' => $subscriptionResult['error'] ?? 'Unknown error',
                ]);
            }
        }
    }


    /**
     * Send notification to a specific topic.
     */
    public static function sendToTopic($topic, $title, $body, $data = [], $channelId = null)
    {
        $messaging = self::getFirebaseMessaging()->createMessaging();

        $messageConfig = self::createMessageConfig($topic, $title, $body, $data, $channelId);
        $message = CloudMessage::fromArray($messageConfig);

        try {
            $response = $messaging->send($message);
            return [
                'success' => true,
                'message' => 'Notification sent successfully',
                'response' => $response,
            ];
        } catch (\Throwable $e) {
            return self::handleException($e);
        }
    }
    public static function sendToTopicAndStorage($topic, $users_ids, $notificationable, $title, $body, $replace, $data = [], $isCustom = false, $channelId = null)
    {
        // Store notification first (this should always work)
        NotificationService::storeNotification(
            $users_ids,
            $notificationable,
            $title,
            $body,
            $replace,
            $data,
            $isCustom
        );

        // Prepare data for Firebase
        $data['notificationable_id'] = $notificationable['id'] ?? null;
        $type = $notificationable['type'] ?? 'Custom';
        $data['notificationable_type'] = $type;

        // Try to send Firebase notification with retry logic
        $maxRetries = 3;
        $retryDelay = 2; // seconds

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $messaging = self::getFirebaseMessaging()->createMessaging();

                if ($isCustom) {
                    $messageConfig = self::createMessageConfig($topic, $title,  $body,  $data, $channelId);
                } else {
                    $messageConfig = self::createMessageConfig($topic, __($title, $replace), __($body, $replace), $data, $channelId);
                }
                $message = CloudMessage::fromArray($messageConfig);

                $response = $messaging->send($message);

                Log::info('Firebase notification sent successfully', [
                    'topic' => $topic,
                    'attempt' => $attempt,
                    'response' => $response,
                ]);

                return [
                    'success' => true,
                    'message' => 'Notification sent successfully',
                    'response' => $response,
                ];
            } catch (\Throwable $e) {
                $errorMessage = $e->getMessage();
                $isInvalidGrant = str_contains($errorMessage, 'invalid_grant');

                Log::warning('Firebase notification attempt failed', [
                    'attempt' => $attempt,
                    'max_retries' => $maxRetries,
                    'error' => $errorMessage,
                    'topic' => $topic,
                ]);

                // If it's an invalid_grant error and we have more retries, wait and try again
                if ($isInvalidGrant && $attempt < $maxRetries) {
                    Log::info('Retrying Firebase notification after invalid_grant error', [
                        'attempt' => $attempt + 1,
                        'delay' => $retryDelay,
                    ]);

                    sleep($retryDelay);
                    $retryDelay *= 2; // Exponential backoff

                    // Reset Firebase messaging instance to force re-initialization
                    self::$firebaseMessaging = null;

                    continue;
                }

                // If we've exhausted retries or it's not an invalid_grant error, handle the exception
                return self::handleException($e);
            }
        }

        // This should never be reached, but just in case
        return [
            'success' => false,
            'message' => 'Failed to send notification after all retry attempts',
        ];
    }


    public static function sendToTokensAndStorage($users_ids, $notificationable, $title, $body, $replace, $data = [], $isCustom = false, $channelId = null)
    {
        $messaging = self::getFirebaseMessaging()->createMessaging();

        // اجلب جميع device tokens
        $tokens = PersonalAccessToken::whereIn('tokenable_id', $users_ids)
            ->whereNull('logouted_at')
            ->whereNotNull('device_token')
            ->pluck('device_token')
            ->unique()
            ->toArray();

        Log::info('tokens', $tokens);

        // خزّن الإشعار في قاعدة البيانات
        NotificationService::storeNotification(
            $users_ids,
            $notificationable,
            $title,
            $body,
            $replace,
            $data,
            $isCustom
        );

        // تجهيز بيانات الإشعار
        $data['notificationable_id'] = $notificationable['id'] ?? null;
        $type = $notificationable['type'] ?? 'Custom';
        $data['notificationable_type'] = $type;

        // تحضير النصوص
        if ($isCustom) {
            $notificationTitle = $title;
            $notificationBody = $body;
        } else {
            $customReplace = $replace;
            unset($customReplace['locales']);
            $notificationTitle = __($title, $customReplace);
            $notificationBody = __($body, $customReplace);
        }

        // إذا لم يوجد أي tokens نتوقف
        if (empty($tokens)) {
            return [
                'success' => false,
                'message' => 'No device tokens found'
            ];
        }

        try {
            // Build common notification + data part once
            $notification = Notification::create($notificationTitle, $notificationBody);

            $androidConfig = null;
            if ($channelId) {
                $androidConfig = [
                    'notification' => [
                        'channel_id' => $channelId,
                    ],
                ];
            }

            // Multicast message
            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withData(
                    data: collect($data)
                        ->map(fn($value) => json_encode($value))
                        ->toArray()
                );

            if ($androidConfig) {
                $message = $message->withAndroidConfig($androidConfig);
            }

            $response = $messaging->sendMulticast($message, $tokens);

            Log::info('Notification sent successfully', [
                'successes' => $response->successes()->count(),
                'failures' => $response->failures()->count(),
                'total' => count($tokens),
            ]);

            // Log detailed failure information if there are failures
            if ($response->failures()->count() > 0) {
                $failureDetails = [];
                foreach ($response->failures() as $failure) {
                    $failureDetails[] = [
                        'token' => $failure->target()->value(),
                        'error' => $failure->error()->getMessage(),
                        'code' => $failure->error()->getCode(),
                    ];
                }

                Log::warning('Firebase notification failures detected', [
                    'total_failures' => $response->failures()->count(),
                    'failure_details' => $failureDetails,
                ]);
            }


            return [
                'success' => true,
                'message' => sprintf(
                    'Sent to %d tokens (%d success, %d failure)',
                    count($tokens),
                    $response->successes()->count(),
                    $response->failures()->count()
                ),
                'response' => $response,
            ];
        } catch (\Throwable $e) {
            return self::handleException($e);
        }
    }


    /**
     * Unsubscribe from a specific topic.
     */
    public static function unsubscribeFromTopic($registrationToken, $topic)
    {
        $messaging = self::getFirebaseMessaging()->createMessaging();

        try {
            $response = $messaging->unsubscribeFromTopic($topic, $registrationToken);
            return [
                'success' => true,
                'message' => 'Successfully unsubscribed from topic',
                'response' => $response,
            ];
        } catch (\Throwable $e) {
            return self::handleException($e);
        }
    }
    public static function unsubscribeFromAllTopic($personalAccessToken)
    {

        if ($personalAccessToken) {

            $deviceToken = $personalAccessToken->device_token;

            if ($deviceToken) {
                $user = Auth::user();

                $APP_ENV_TYPE = env('APP_ENV_TYPE', 'staging');

                $topics = [
                    'user-' . $user->id,
                    'role-' . $user->role,
                    'all-users',
                ];

                if ($APP_ENV_TYPE != 'production') {
                    for ($i = 0; $i < count($topics); $i++) {
                        $topics[$i] = $topics[$i] . '-' . $APP_ENV_TYPE;
                    }
                }

                foreach ($topics as $topic) {
                    FirebaseService::removeTopicFromToken($deviceToken, $topic);
                }
            }

            // $personalAccessToken->delete();
        }
    }

    /**
     * Remove a topic from a specific token.
     */
    public static function removeTopicFromToken($registrationToken, $topic)
    {
        if (!self::isValidTopic($topic)) {
            return [
                'success' => false,
                'message' => 'Topic name format is invalid',
            ];
        }

        $messaging = self::getFirebaseMessaging()->createMessaging();

        try {
            $response = $messaging->unsubscribeFromTopic($topic, $registrationToken);
            return [
                'success' => true,
                'message' => 'Successfully removed topic from token',
                'response' => $response,
            ];
        } catch (\Throwable $e) {
            return self::handleException($e);
        }
    }

    /**
     * Validate topic name.
     */
    protected static function isValidTopic($topic)
    {
        return preg_match('/^[a-zA-Z0-9_-]+$/', $topic);
    }

    /**
     * Setup Firebase Messaging.
     */
    public static function getFirebaseMessaging()
    {
        if (!self::$firebaseMessaging) {
            try {
                $serviceAccount = self::loadServiceAccount();

                // Validate service account data
                if (!self::validateServiceAccount($serviceAccount)) {
                    throw new \Exception("Invalid Firebase service account configuration.");
                }

                self::$firebaseMessaging = (new Factory)
                    ->withServiceAccount($serviceAccount);
            } catch (\Throwable $e) {
                Log::error('Firebase initialization failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }
        }

        return self::$firebaseMessaging;
    }

    /**
     * Load service account data.
     */
    public static function loadServiceAccount()
    {
        $serviceAccountPath = storage_path('firebase/rwady-4f7a2-firebase-adminsdk-fbsvc-b2a187cd1d.json');

        if (!file_exists($serviceAccountPath)) {
            throw new \Exception("Firebase service account file not found at: " . $serviceAccountPath);
        }

        $serviceAccountContent = file_get_contents($serviceAccountPath);
        if (!$serviceAccountContent) {
            throw new \Exception("Unable to read Firebase service account file.");
        }

        $serviceAccount = json_decode($serviceAccountContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON in Firebase service account file: " . json_last_error_msg());
        }

        return $serviceAccount;
    }

    /**
     * Validate service account configuration.
     */
    protected static function validateServiceAccount($serviceAccount)
    {
        $requiredFields = ['type', 'project_id', 'private_key_id', 'private_key', 'client_email', 'client_id'];

        foreach ($requiredFields as $field) {
            if (!isset($serviceAccount[$field]) || empty($serviceAccount[$field])) {
                Log::error('Firebase service account missing required field', ['field' => $field]);
                return false;
            }
        }

        // Validate private key format
        if (!str_contains($serviceAccount['private_key'], '-----BEGIN PRIVATE KEY-----')) {
            Log::error('Firebase service account has invalid private key format');
            return false;
        }

        return true;
    }

    /**
     * Handle exceptions and log them.
     */
    protected static function handleException(\Throwable $e)
    {
        $errorMessage = $e->getMessage();
        $isInvalidGrant = str_contains($errorMessage, 'invalid_grant');

        if ($isInvalidGrant) {
            Log::error('Firebase Invalid Grant Error - Possible causes: expired credentials, clock skew, or network issues', [
                'error' => $errorMessage,
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()->toISOString(),
                'server_time' => date('Y-m-d H:i:s'),
            ]);

            // Reset Firebase messaging instance to force re-initialization
            self::$firebaseMessaging = null;

            return [
                'success' => false,
                'message' => 'Firebase authentication error. Please check credentials and try again.',
                'error' => $errorMessage,
                'retry_after' => 60, // Suggest retry after 1 minute
            ];
        }

        Log::error('Firebase Error', [
            'error' => $errorMessage,
            'trace' => $e->getTraceAsString(),
        ]);

        return [
            'success' => false,
            'message' => $errorMessage,
            'error' => $errorMessage,
        ];
    }

    /**
     * Create message configuration.
     */
    protected static function createMessageConfig($topic, $title, $body, $data, $channelId)
    {

        $config = [
            'topic' => $topic,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $data,

        ];

        if ($channelId) {
            $config['android'] = [
                'notification' => [
                    'channel_id' => $channelId,
                ],
            ];
        }

        return $config;
    }

    /**
     * Send a single notification to multiple FCM registration tokens
     * (multicast). Returns the raw Firebase response so you can inspect
     * per-token success / failure.
     *
     * @param  string[] $registrationTokens  An array of FCM tokens
     * @param  string   $title               Notification title
     * @param  string   $body                Notification body
     * @param  array    $data                Extra key/value payload (optional)
     * @param  string|null $channelId        Android channel-id (optional)
     * @return array                         [
     *                                          'success' => bool,
     *                                          'message' => string,
     *                                          'response' => \Kreait\Firebase\Messaging\MulticastSendReport|mixed
     *                                       ]
     */
    public static function sendToTokens(
        array $registrationTokens,
        string $title,
        string $body,
        array $data = [],
        ?string $channelId = null
    ) {
        if (empty($registrationTokens)) {
            return [
                'success' => false,
                'message' => 'No registration tokens supplied.',
            ];
        }

        $messaging = self::getFirebaseMessaging()->createMessaging();

        // Build common notification + data part once
        $notification = Notification::create($title, $body);

        $androidConfig = null;
        if ($channelId) {
            $androidConfig = [
                'notification' => [
                    'channel_id' => $channelId,
                ],
            ];
        }

        // Multicast message
        $message = CloudMessage::new()
            ->withNotification($notification)
            ->withData(
                data: collect($data)
                    ->map(fn($value) => json_encode($value))
                    ->toArray()
            );



        if ($androidConfig) {
            $message = $message->withAndroidConfig($androidConfig);
        }

        try {
            /** @var \Kreait\Firebase\Messaging\MulticastSendReport $report */
            $report = $messaging->sendMulticast($message, $registrationTokens);

            return [
                'success'  => true,
                'message' => sprintf(
                    'Sent to %d tokens (%d success, %d failure)',
                    count($registrationTokens),
                    $report->successes()->count(),
                    $report->failures()->count()
                ),
                'response' => $report,
            ];
        } catch (\Throwable $e) {
            return self::handleException($e);
        }
    }



    public static function sendIncomingCall($topic, $serviceRequestId, $requestFilter, $customerName, $avatar, $phone)
    {
        $messaging = self::getFirebaseMessaging()->createMessaging();

        $data = [
            'call_id' => uniqid(),
            'type' => 'incoming_call',
            'service_request_id' => $serviceRequestId,
            'caller_name' => $customerName,
            'avatar' => $avatar,
            'handle' => $phone,
            'user_id' => Auth::check() ? User::auth()->id : 0,
        ];

        $messageConfig = [
            'topic' => $topic,
            'data' => $data,
            'android' => [
                'priority' => 'high',
                'ttl' => '30s',
                // 'notification' => [
                //     'channel_id' => 'incoming_calls',
                //     'default_sound' => true,
                //     'default_vibrate_timings' => true,
                //     'sound' => 'default',
                // ],
            ],
            'apns' => [
                'headers' => [
                    'apns-priority' => '10',
                ],
                'payload' => [
                    'aps' => [
                        'category' => 'INCOMING_CALL',
                        'content-available' => 1,
                    ],
                ],
            ],
        ];

        $message = CloudMessage::fromArray($messageConfig);

        try {
            $response = $messaging->send($message);
            return [
                'success' => true,
                'message' => 'Incoming call notification sent successfully to topic',
                'response' => $response,
            ];
        } catch (\Throwable $e) {
            return self::handleException($e);
        }
    }
}
