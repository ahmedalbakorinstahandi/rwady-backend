<?php

// 📂 app/Http/Controllers/InstallmentController.php

namespace App\Http\Controllers;

use App\Http\Requests\Installment\CheckEligibilityRequest;
use App\Http\Requests\Installment\ValidatePlanRequest;
use App\Http\Requests\Installment\ConfirmInstallmentRequest;
use App\Http\Services\AqsatiInstallmentService;
use Illuminate\Http\Request;

class InstallmentController extends Controller
{
    protected AqsatiInstallmentService $service;

    public function __construct(AqsatiInstallmentService $service)
    {
        $this->service = $service;
    }

    public function checkEligibility(CheckEligibilityRequest $request)
    {
        return $this->service->checkEligibility($request->validated());
    }

    public function validatePlan(ValidatePlanRequest $request)
    {
        return $this->service->validatePlan($request->validated());
    }

    public function confirmInstallment(ConfirmInstallmentRequest $request)
    {
        return $this->service->confirmInstallment($request->validated());
    }
}
