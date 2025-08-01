<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BulkSMSIraqService
{
    /**
     * إرسال رسالة OTP أو رسالة نصية
     *
     * @param string $recipient رقم المستلم (مثال: 9647501234567)
     * @param string $message نص الرسالة
     * @param string $type نوع الرسالة plain أو whatsapp
     * @param string|null $lang لغة الرسالة (للـ whatsapp فقط)
     * @return array
     */
    public static function send(string $recipient, string $message, string $type = 'plain', ?string $lang = null): array
    {
        $apiUrl = config('services.bulksms.url', 'https://gateway.standingtech.com/api/v4/sms/send');
        $apiKey = config('services.bulksms.key');

        $payload = [
            'recipient' => $recipient,
            'sender_id' => config('services.bulksms.sender', 'YourSender'),
            'type'      => $type,
            'message'   => $message,
        ];

        if ($lang && $type === 'whatsapp') {
            $payload['lang'] = $lang;
        }

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->post($apiUrl, $payload);

        return [
            'success' => $response->successful(),
            'status'  => $response->status(),
            'body'    => $response->json(),
        ];
    }
}
