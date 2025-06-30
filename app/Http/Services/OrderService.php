<?php

namespace App\Http\Services;

use App\Http\Permissions\OrderPermission;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\User;
use App\Services\FilterService;
use App\Services\MessageService;
use App\Http\Services\Payment\QiPaymentService;
use App\Http\Services\AqsatiInstallmentService;
use Illuminate\Support\Str;

class OrderService
{
    public function index($filters = [])
    {
        $query = Order::query()->with('couponUsage.coupon');


        $searchFields = ['name', 'description'];
        $numericFields = [];
        $dateFields = ['created_at'];
        $exactMatchFields = ['availability', 'parent_id'];
        $inFields = [];

        $query = OrderPermission::filterIndex($query);

        $query = FilterService::applyFilters(
            $query,
            $filters,
            $searchFields,
            $numericFields,
            $dateFields,
            $exactMatchFields,
            $inFields
        );

        return $query;
    }

    public function show($id)
    {
        $order = Order::where('id', $id)->first();

        if (!$order) {
            MessageService::abort(404, 'messages.order.not_found');
        }

        $order->load(['orderProducts.product', 'couponUsage.coupon', 'payments', 'statuses']);


        $user = User::auth();

        if ($user->isAdmin()) {
            $order->load('user');
        }

        return $order;
    }


    public function create($data)
    {


        // Order
        // user_id : Auth User ✅
        // code : Random ✅
        // status : pending ✅
        // payment_fees : حسب طريقة الدفع ✅
        // notes : request notes ✅

        $coupon = null;
        if (isset($data['coupon_code'])) {
            $coupon = Coupon::where('code', $data['coupon_code'])->first();
            if (!$coupon || !$coupon->is_active) {
                MessageService::abort(404, 'messages.coupon.invalid');
            }
        }

        $user  = User::auth();


        $data['user_id'] = $user->id;
        $data['code'] = Str::random(10);
        $data['status'] = 'pending';

        $data['notes'] = $data['notes'] ?? null;

        $successUrl = $data['success_url'];
        $failUrl = $data['fail_url'];

        $order = Order::create($data);

        $order->code = 'ORD-' . $order->id;
        $order_id = $order->id;

        $order->statuses()->create([
            'status' => 'pending',
        ]);

        $products = $data['products'];

        foreach ($products as $productData) {
            $product = Product::find($productData['product_id']);

            if (!$product) {
                MessageService::abort(404, 'messages.product.not_found');
            }

            $orderProduct = OrderProduct::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $productData['quantity'],
                'price' => $product->final_price,
                'cost_price' => $product->final_cost_price,
                'status' => 'pending',
                'shipping_rate' => $product->getShippingRateAttribute($productData['quantity']),
                'color_id' => $productData['color_id'] ?? null,
            ]);
        }

        if ($data['payment_method'] == 'qi') {

            $order->payment_method = 'qi';
            $order->payment_fees = config('services.qi.fees', 10);

            $order->save();




            $qiPaymentService = new QiPaymentService();


            $paymentData = [
                'amount' => $order->total_amount,
                'currency' => 'IQD',
                'requestId' =>  "{$order->id}",
                'description' => trans('messages.payment.description'),
                'successRedirectUrl' => $successUrl . '/' . $order_id,
                'failRedirectUrl' => $failUrl . '/' . $order_id,
                'notificationUrl' =>  url('/api/webhook/qi-payment'),
            ];

            $paymentSession = $qiPaymentService->createPayment($paymentData);

            $order->metadata = $paymentSession;

            $order->payment_session_id =  'qi-' . $paymentSession['requestId'];
        } elseif ($data['payment_method'] == 'cash') {
            $order->payment_method = 'cash';
            $order->payment_fees = 0;
            $order->save();

            // TODO : Send notification to user and admin


        } elseif ($data['payment_method'] == 'transfer') {
            $order->payment_method = 'transfer';
            $order->payment_fees = 0;
            $order->save();


            if (isset($data['attached'])) {
                $order->payments()->create([
                    'amount' => $order->total_amount,
                    'description' => [
                        'ar' => 'دفع بواسطة التحويل',
                        'en' => 'Payment by transfer',
                    ],
                    'status' => 'pending',
                    'method' => 'transfer',
                    'attached' => $data['attached'],
                ]);
            }

            // TODO : Send notification to user and admin

        } elseif ($data['payment_method'] == 'installment') {
            $order->payment_method = 'installment';
            $order->payment_fees = config('services.aqsati.our_fees', 5);
            $order->save();


            $aqsatiService = new AqsatiInstallmentService();

            // تحقق من الأهلية
            $eligibility = $aqsatiService->checkEligibility([
                'identity' => $data['identity'],
                // 'type_of_customer' => $data['type_of_customer'] ?? 1,
                'type_of_customer' => 1,
            ]);

            $sessionId = $eligibility['data']['session']['sessionId'];

            // تحقق من الخطة
            $validation = $aqsatiService->validatePlan([
                'session_id' => $sessionId,
                'amount' => $order->total_amount,
                'count_of_month' => 10,
            ]);

            // تأكيد القسط بكود الاختبار أو كود المستخدم
            $confirmation = $aqsatiService->confirmInstallment([
                'session_id' => $sessionId,
                'otp' => $data['otp'] ?? 22331144,
                'note' => 'Order #' . $order->id,
                'payment_card' => '',
            ]);

            // حفظ metadata
            $order->metadata = [
                'installment_id' => $confirmation['data']['installmentId'] ?? null,
                'amount' => $confirmation['data']['amount'] ?? null,
                'amount_per_month' => $confirmation['data']['amountPerMonth'] ?? null,
                'months' => $confirmation['data']['countOfMonth'] ?? null,
                'due_date' => $confirmation['data']['dueDate'] ?? null,
                'operation_id' => $confirmation['data']['operationId'] ?? null,
                'to_be_deducted' => $confirmation['data']['toBeDeducted'] ?? null,
            ];
        }


        $order->save();


        // Order Payment
        // order_id : Order ID ✅
        // amount : Payment Amount from payment method or add payment manually ✅
        // description : Payment Description from static text for each payment method ✅
        // status : pending ✅
        // is_refund : false ✅

        $order = $this->show($order->id);


        return $order;
    }

    public function update($order, $data)
    {
        $order->update($data);

        $order = $this->show($order->id);

        return $order;
    }

    public function delete($order)
    {
        $order->orderProducts()->delete();
        $order->couponUsage()->delete();
        $order->payments()->delete();
        $order->statuses()->delete();
        $order->delete();
    }
}
