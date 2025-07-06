<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AqsatiInstallmentService
{
    protected $baseUrl;
    protected $token;

    public function __construct()
    {
        $this->baseUrl = config('services.aqsati.url');
        $this->token = config('services.aqsati.token');
    }

    public function checkEligibility(array $data)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token
            ])->post("{$this->baseUrl}/aqsati/ThirdParty/api/Integration/GetCustomerInformation", [
                'Identity' => $data['identity']
            ]);

            if (!$response->successful()) {
                Log::error('Aqsati API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'url' => "{$this->baseUrl}/aqsati/ThirdParty/api/Integration/GetCustomerInformation"
                ]);
                
                return [
                    'success' => false,
                    'key' => 'api_error',
                    'message' => 'Failed to connect to Aqsati service'
                ];
            }

            $responseData = $response->json();
            
            if (is_null($responseData)) {
                Log::error('Aqsati API returned null response', [
                    'body' => $response->body(),
                    'url' => "{$this->baseUrl}/aqsati/ThirdParty/api/Integration/GetCustomerInformation"
                ]);
                
                return [
                    'success' => false,
                    'key' => 'invalid_response',
                    'message' => 'Invalid response from Aqsati service'
                ];
            }

            $responseData = array_merge($responseData, [
                'success' => $responseData['succeeded'] ?? false,
                'key' => !($responseData['succeeded'] ?? false) ? 'not_eligible' : 'eligible',
            ]);

            return $responseData;
        } catch (\Exception $e) {
            Log::error('Aqsati API exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'key' => 'exception',
                'message' => 'An error occurred while processing the request'
            ];
        }
    }

    public function validatePlan(array $data)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token
            ])->post("{$this->baseUrl}/aqsati/ThirdParty/api/Integration/ValidatorOfInstallment", [
                'sessionId' => $data['session_id'],
                'amount' => $data['amount'],
                'countOfMonth' => $data['count_of_month'],
            ]);

            if (!$response->successful()) {
                Log::error('Aqsati validate plan API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'url' => "{$this->baseUrl}/aqsati/ThirdParty/api/Integration/ValidatorOfInstallment"
                ]);
                
                return [
                    'success' => false,
                    'key' => 'api_error',
                    'message' => 'Failed to connect to Aqsati service'
                ];
            }

            $responseData = $response->json();
            
            if (is_null($responseData)) {
                Log::error('Aqsati validate plan API returned null response', [
                    'body' => $response->body(),
                    'url' => "{$this->baseUrl}/aqsati/ThirdParty/api/Integration/ValidatorOfInstallment"
                ]);
                
                return [
                    'success' => false,
                    'key' => 'invalid_response',
                    'message' => 'Invalid response from Aqsati service'
                ];
            }

            $responseData = array_merge([
                'success' => $responseData['succeeded'] ?? false,
                'key' => ($responseData['succeeded'] ?? false) ? 'plan_setup_success' : 'failed_to_setup_plan',
            ], $responseData);

            return $responseData;
        } catch (\Exception $e) {
            Log::error('Aqsati validate plan API exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'key' => 'exception',
                'message' => 'An error occurred while processing the request'
            ];
        }
    }

    public function confirmInstallment(array $data)
    {
        try {
            $payload = [
                'sessionId' => $data['session_id'],
                'OTP' => $data['otp'],
                'Note' => $data['note'] ?? '',
                'PaymentCard' => $data['payment_card'] ?? '',
            ];

            $response = Http::withHeaders([
                'Authorization' => $this->token
            ])->post("{$this->baseUrl}/aqsati/ThirdParty/api/Integration/CreateInstallment", $payload);

            if (!$response->successful()) {
                Log::error('Aqsati confirm installment API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'url' => "{$this->baseUrl}/aqsati/ThirdParty/api/Integration/CreateInstallment"
                ]);
                
                return [
                    'success' => false,
                    'key' => 'api_error',
                    'message' => 'Failed to connect to Aqsati service'
                ];
            }

            $responseData = $response->json();
            
            if (is_null($responseData)) {
                Log::error('Aqsati confirm installment API returned null response', [
                    'body' => $response->body(),
                    'url' => "{$this->baseUrl}/aqsati/ThirdParty/api/Integration/CreateInstallment"
                ]);
                
                return [
                    'success' => false,
                    'key' => 'invalid_response',
                    'message' => 'Invalid response from Aqsati service'
                ];
            }

            $responseData = array_merge([
                'success' => $responseData['succeeded'] ?? false,
                'key' => !($responseData['succeeded'] ?? false) ? 'failed_to_confirm_installment' : 'installment_confirmed',
            ], $responseData);

            return $responseData;
        } catch (\Exception $e) {
            Log::error('Aqsati confirm installment API exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'key' => 'exception',
                'message' => 'An error occurred while processing the request'
            ];
        }
    }
}
