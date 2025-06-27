<?php

namespace App\Http\Controllers\Webhook;

use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Services\Payment\QiSignatureValidator;
use App\Models\Order;
use App\Services\MessageService;

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
            MessageService::abort(401, 'messages.payment.invalid_signature');
        }

        // ✅ التوقيع صحيح - تابع تنفيذ الإجراءات
        Log::info('Valid QI Webhook Received', ['payload' => $payload]);

        // مثال على المعالجة
        // Order::where('payment_id', $payload['paymentId'])->update(['status' => $payload['status']]);

        $order = Order::where('payment_session_id', 'qi-' . $payload['paymentId'])->first();
        if (!$order) {
            Log::warning('Order not found', ['paymentId' => $payload['paymentId']]);
            MessageService::abort(404, 'messages.order.not_found');
        }

        // $order->payments()->create([
        //     'amount' => $payload['amount'],
        //     'status' => $payload['status'],
        //     'payment_method' => 'qi',
        //     'payment_session_id' => 'qi_' . $payload['paymentId'],
        // ]);

      
        //     $table->float('amount');
        //     $table->longText('description');
        //     $table->enum('status', ["pending","completed","failed"]);
        //     $table->boolean('is_refund')->default(false);
        //     $table->enum('method', ["qi","installment","transfer","cash"]);
        //     $table->string('attached', 110);
        //     $table->longText('metadata')->nullable();
        //     $table->timestamps();
        //     $table->softDeletes();
        // });






        return ResponseService::response([
            'success' => true,
            'message' => 'messages.payment.webhook_processed'
        ], 200);
    }
}
