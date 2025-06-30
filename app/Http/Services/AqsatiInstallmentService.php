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
        $response = Http::withHeaders([
            'Authorization' => $this->token
        ])->post("{$this->baseUrl}/aqsati/ThirdParty/api/Integration/GetCustomerInformation", [
            'Identity' => $data['identity']
        ]);

        $data = $response->json();


        $data = array_merge($data, [
            'success' => $data['succeeded'],
            'key' => !$data['succeeded'] ? 'not_eligible' : 'eligible',
        ]);

        return $data;
    }

    public function validatePlan(array $data)
    {
        $response = Http::withHeaders([
            'Authorization' => $this->token
        ])->post("{$this->baseUrl}/aqsati/ThirdParty/api/Integration/ValidatorOfInstallment", [
            'sessionId' => $data['session_id'],
            'amount' => $data['amount'],
            'countOfMonth' => $data['count_of_month'],
        ]);

        $data = $response->json();


        $data = array_merge([
            'success' => $data['succeeded'],
            'key' => $data['succeeded'] ? 'plan_setup_success' : 'failed_to_setup_plan',
        ], $data);


        return $data;
    }

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

        $data = $response->json();


        $data = array_merge([
            'success' => $data['succeeded'],
            'key' => !$data['succeeded'] ? 'failed_to_confirm_installment' : 'installment_confirmed',
        ], $data);



        return $data;
    }
}
