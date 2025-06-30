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
use App\Models\CouponUsage;
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


    public function checkOrderDetails($data)
    {
        $productsData = $data['products'];
        $paymentMethod = $data['payment_method'];

        $paymentFeesPercentage = 0;
        if ($paymentMethod === 'qi') {
            $paymentFeesPercentage = config('services.qi.fees', 10);
        } elseif ($paymentMethod === 'installment') {
            $paymentFeesPercentage = config('services.aqsati.aqsati_installment_fees', 15) + config('services.aqsati.our_fees', 5);
        }

        $amount = 0;
        $shippingFees = 0;

        foreach ($productsData as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) {
                MessageService::abort(404, 'messages.product.not_found');
            }

            $products[] = $product;
            $amount += $product->final_price * $item['quantity'];
            $shippingFees += $product->getShippingRateAttribute($item['quantity']) * $item['quantity'];
        }

        $coupon = null;
        $couponDiscountValue = 0;
        if (!empty($data['coupon_code'])) {
            $coupon = Coupon::where('code', $data['coupon_code'])->first();
            if (!$coupon || !$coupon->is_active) {
                MessageService::abort(404, 'messages.coupon.invalid');
            }

            $couponDiscountValue = $coupon->type === 'percentage'
                ? $amount * ($coupon->amount / 100)
                : $coupon->amount;
        }

        $subtotal = $amount + $shippingFees - $couponDiscountValue;
        $paymentFeesValue = $subtotal * ($paymentFeesPercentage / 100);

        return [
            'amount' => $amount,
            'shipping_fees' => $shippingFees,
            'amount_with_shipping' => $amount + $shippingFees,
            'coupon_discount_value' => $coupon ? $couponDiscountValue : null,
            'amount_with_shipping_after_coupon' => $subtotal,
            'payment_fees_percentage' => $paymentFeesPercentage,
            'payment_fees_value' => $paymentFeesValue,
            'amount_with_shipping_after_coupon_and_payment_fees' => $subtotal + $paymentFeesValue,
            'installment_count' => $paymentMethod == 'installment' ? 10 : null,
        ];
    }



    public function create($data)
    {


        // Order
        // user_id : Auth User ✅
        // code : Random ✅
        // status : pending ✅
        // payment_fees : حسب طريقة الدفع ✅
        // notes : request notes ✅

        $user = User::auth();



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

        $coupon = null;

        if (isset($data['coupon_code'])) {
            $coupon = Coupon::where('code', $data['coupon_code'])->first();
            if (!$coupon || !$coupon->is_active) {
                MessageService::abort(404, 'messages.coupon.invalid');
            }


            $couponUsage = CouponUsage::create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'coupon_id' => $coupon->id,
                'discount_type' => $coupon->type,
                'discount_value' => $coupon->amount,
            ]);
        }

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
                'shipping_rate' => $product->getShippingRateAttribute($productData['quantity']) * $productData['quantity'],
                'color_id' => $productData['color_id'] ?? null,
            ]);
        }

        if ($data['payment_method'] == 'qi') {

            $order->payment_method = 'qi';
            $order->payment_fees = config('services.qi.fees', 3);

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

            // check eligibility
            $eligibility = $aqsatiService->checkEligibility([
                'identity' => $data['identity'],
                // 'type_of_customer' => $data['type_of_customer'] ?? 1,
                'type_of_customer' => 1,
            ]);

            if (!$eligibility['success']) {
                MessageService::abort(400, $eligibility['message']);
            }

            // metadata
            $order->metadata =   [
                'check_eligibility' => $eligibility,
            ];

            $order->save();

            $sessionId = $eligibility['data']['session']['sessionId'];

            $order->payment_session_id =  'aqsati-' . $sessionId;

            $validation = $aqsatiService->validatePlan([
                'session_id' => $sessionId,
                'amount' => $order->total_amount,
                'count_of_month' => 10,
            ]);

            if (!$validation['success']) {
                MessageService::abort(400, $validation['message']);
            }

            $orderMetadata = $order->metadata ?? [];
            $order->metadata = array_merge($orderMetadata, [
                'validate_plan' => $validation,
            ]);
        }
        if (isset($data['address'])) {
            // create address
            $addressService = new AddressService();

            $address = $data['address'];

            $address['name'] = 'Order Address ' . $order->id;
            $address['addressable_id'] = $order->id;
            $address['addressable_type'] = Order::class;
            $address['is_default'] = false;
            $addressService->create($address);
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


    // confirm otp
    public function confirmOtp($order, $data)
    {

        if ($order->payment_method != 'installment') {
            MessageService::abort(400, 'messages.order.payment_method_not_installment');
        }


        $aqsatiService = new AqsatiInstallmentService();


        $sessionId = $order->payment_session_id;

        $sessionId = str_replace('aqsati-', '', $sessionId);


        // تأكيد القسط بكود الاختبار أو كود المستخدم
        $confirmation = $aqsatiService->confirmInstallment([
            'session_id' => $sessionId,
            'otp' => $data['otp'],
            'note' => 'Order #' . $order->id,
            'payment_card' => '',
        ]);

        if (!$confirmation['success']) {
            MessageService::abort(400, $confirmation['message']);
        }

        $orderMetadata = $order->metadata ?? [];
        $order->metadata = array_merge($orderMetadata, [
            'confirm_otp' => $confirmation,
        ]);


        $order->save();

        $order->payments()->create([
            'amount' => $order->total_amount,
            'description' => [
                'ar' => 'دفع بواسطة التقسيط',
                'en' => 'Payment by installment',
            ],
            'status' => 'confirmed',
            'method' => 'installment',
        ]);

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
