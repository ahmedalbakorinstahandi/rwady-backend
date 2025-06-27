<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;

class QiPaymentService
{
    // بيانات الاعتماد الأساسية - يُفضل نقلها إلى .env
    protected string $baseUrl;
    protected string $username;
    protected string $password;
    protected string $terminalId;

    public function __construct()
    {
        $this->baseUrl = config('services.qi.base_url', 'https://uat-sandbox-3ds-api.qi.iq/api/v1/');
        $this->username = config('services.qi.username', 'paymentgatewaytest');
        $this->password = config('services.qi.password', 'WHaNFE5C3qlChqNbAzH4');
        $this->terminalId = config('services.qi.terminal_id', '237984');
    }

    /**
     * إرسال طلب دفع (Payment Request) إلى بوابة QI
     * 
     * @param array $data - بيانات الدفع: amount, currency, etc.
     * @return array
     */
    public function createPayment(array $data): array
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->withHeaders([
                    'X-Terminal-Id' => $this->terminalId,
                ])
                ->post($this->baseUrl . 'payment', $data);

            return $response->json();
        } catch (RequestException $e) {
            Log::error('QI Create Payment Failed', ['error' => $e->getMessage()]);
            return ['error' => 'request_failed'];
        }
    }

    /**
     * التحقق من حالة الدفع عبر paymentId
     *
     * @param string $paymentId
     * @return array
     */
    public function getPaymentStatus(string $paymentId): array
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->withHeaders([
                    'X-Terminal-Id' => $this->terminalId,
                ])
                ->get($this->baseUrl . "payment/{$paymentId}/status");

            return $response->json();
        } catch (RequestException $e) {
            Log::error('QI Get Payment Status Failed', ['error' => $e->getMessage()]);
            return ['error' => 'request_failed'];
        }
    }

    /**
     * إلغاء الدفع باستخدام paymentId
     *
     * @param string $paymentId
     * @return array
     */
    public function cancelPayment(string $paymentId): array
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->withHeaders([
                    'X-Terminal-Id' => $this->terminalId,
                ])
                ->post($this->baseUrl . "payment/{$paymentId}/cancel");

            return $response->json();
        } catch (RequestException $e) {
            Log::error('QI Cancel Payment Failed', ['error' => $e->getMessage()]);
            return ['error' => 'request_failed'];
        }
    }
}
