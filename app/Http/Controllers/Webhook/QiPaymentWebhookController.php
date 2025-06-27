<?php

namespace App\Http\Controllers\Webhook;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Services\Payment\QiSignatureValidator;

class QiPaymentWebhookController extends Controller
{
    /**
     * استقبال Webhook من QiCard والتحقق من التوقيع
     */
    public function handle(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('X-Signature');

        if (!QiSignatureValidator::verify($payload, $signature)) {
            Log::warning('Invalid QI Signature', ['payload' => $payload]);
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        // ✅ التوقيع صحيح - تابع تنفيذ الإجراءات
        Log::info('Valid QI Webhook Received', ['payload' => $payload]);

        // مثال على المعالجة
        // Order::where('payment_id', $payload['paymentId'])->update(['status' => $payload['status']]);

        return response()->json(['message' => 'Webhook processed'], 200);
    }
}
