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

    // ✅ [1] التحقق من أهلية الزبون
    public function checkEligibility(array $data)
    {
        $response = Http::withHeaders([
            'Authorization' => $this->token
        ])->post("{$this->baseUrl}/aqsati/ThirdParty/api/Integration/GetCustomerInformation", [
            'Identity' => $data['identity']
        ]);

        // if (!$response->ok()) {
        //     return response()->json(
        //          $response->json(),
        //         400
        //     );
        // }

        $data = $response->json();

        if (!$response->ok()) {
            $data = array_merge($data, [
                'success' => false,
                'key' => 'not_eligible'
            ]);
        } else {
            $data = array_merge($data, [
                'success' => true,
                'key' => 'eligible'
            ]);
        }

        return $data;
    }

    // ✅ [2] التحقق من الخطة وإرسال OTP
    public function validatePlan(array $data)
    {
        $response = Http::withHeaders([
            'Authorization' => $this->token
        ])->post("{$this->baseUrl}/aqsati/ThirdParty/api/Integration/ValidatorOfInstallment", [
            'sessionId' => $data['session_id'],
            'amount' => $data['amount'],
            'countOfMonth' => $data['count_of_month'],
        ]);

        if (!$response->ok()) {
            return response()->json(['message' => 'فشل في إعداد الخطة'], 400);
        }

        return $response->json();
    }

    // ✅ [3] تأكيد القسط برمز OTP
    public function confirmInstallment(array $data)
    {
        $payload = [
            'sessionId' => $data['session_id'],
            'OTP' => $data['otp'],
            'Note' => $data['note'] ?? '',
            'PaymentCard' => $data['payment_card'] ?? '',
        ];

        $response = Http::withHeaders([
            'Authorization' => $this->token
        ])->post("{$this->baseUrl}/aqsati/ThirdParty/api/Integration/CreateInstallment", $payload);

        if (!$response->ok()) {
            return response()->json(['message' => 'فشل تأكيد القسط'], 400);
        }

        return $response->json();
    }
}
