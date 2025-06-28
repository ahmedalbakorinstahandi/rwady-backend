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

    // ✅ [1] يتحقق من أهلية الزبون
    public function checkEligibility(CheckEligibilityRequest $request)
    {
        return $this->service->checkEligibility($request->validated());
    }

    // ✅ [2] يتحقق من صحة الخطة ويرسل كود التحقق
    public function validatePlan(ValidatePlanRequest $request)
    {
        return $this->service->validatePlan($request->validated());
    }

    // ✅ [3] تنفيذ القسط بعد إدخال رمز OTP
    public function confirmInstallment(ConfirmInstallmentRequest $request)
    {
        return $this->service->confirmInstallment($request->validated());
    }
}