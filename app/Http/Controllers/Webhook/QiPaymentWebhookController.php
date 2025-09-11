<?php

namespace App\Http\Controllers\Webhook;

use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Notifications\OrderNotification;
use App\Http\Services\Payment\QiSignatureValidator;
use App\Models\Order;
use App\Models\User;
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

        // البحث عن الطلب باستخدام paymentId أو requestId


        // order id split  requestId by -  and get index 1 
        $orderId = explode('-', $payload['requestId'])[0];
        $order = Order::where('payment_session_id', $orderId)
            ->first();

        if (!$order) {
            Log::warning('Order not found', [
                'paymentId' => $payload['paymentId'],
                'requestId' => $payload['requestId'] ?? 'not provided'
            ]);
            MessageService::abort(404, 'messages.order.not_found');
        }

        $order->payments()->create([
            'amount' => $order->total_amount,
            'description' => [
                'ar' => 'تم الدفع بواسطة بطاقة الائتمان',
                'en' => 'Payment by credit card',
            ],
            'status' => 'completed',
            'method' => 'qi',
            'metadata' => $payload,
        ]);




        $order->statuses()->create([
            'status' => 'paid',
            'statusable_type' => Order::class,
            'statusable_id' => $order->id,
        ]);


        // clear cart
        $user = User::find($order->user_id);
        if ($user && ($order->metadata['direct_order'] ?? false) == false) {
            $user->cartItems()->delete();
        }


        // $user = User::where('id', $order->user_id)->first();

        // $orderProducts = $order->orderProducts;

        // foreach ($orderProducts as $orderProduct) {
        //     $cartItem = $user->cartItems()->where('product_id', $orderProduct->product_id)->where('color_id', $orderProduct->color_id)->first();
        //     if ($cartItem) {
        //         $cartItem->delete();
        //     }
        // }

        OrderNotification::newOrder($order);





        return ResponseService::response([
            'success' => true,
            'message' => 'messages.payment.webhook_processed'
        ], 200);
    }
}
