<?php

namespace App\Http\Services;

use App\Http\Notifications\OrderNotification;
use App\Http\Permissions\OrderPermission;
use App\Http\Resources\PromotionResource;
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
use App\Models\Promotion;
use App\Models\Status;
use App\Services\LanguageService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderService
{
    public function index($filters = [])
    {
        $query = Order::query()->with(['couponUsage.coupon', 'address.countryInfo', 'address.cityInfo']);


        $searchFields = ['name', 'description'];
        $numericFields = [];
        $dateFields = ['created_at'];
        $exactMatchFields = ['availability', 'parent_id', 'status'];
        $inFields = ['status'];

        $query = OrderPermission::filterIndex($query);

        $query = FilterService::applyFilters(
            $query,
            $filters,
            $searchFields,
            $numericFields,
            $dateFields,
            $exactMatchFields,
            $inFields,
        );

        return $query;
    }

    public function show($id)
    {
        $order = Order::where('id', $id)->first();

        if (!$order) {
            MessageService::abort(404, 'messages.order.not_found');
        }

        $relations = [
            'orderProducts.product.media',
            'orderProducts.product.colors',
            'orderProducts.product.categories',
            'orderProducts.product.brands',
            'orderProducts.promotion',
            'couponUsage.coupon',
            'payments',
            'statuses',
            'address.countryInfo',
            'address.cityInfo',
            'promotionCart',
            'promotionShipping',
        ];


        $user = User::auth();

        if ($user->isAdmin()) {
            $relations[] = 'user';
        }

        $order->load($relations);

        return $order;
    }


    // public function checkOrderDetails($data)
    // {
    //     $productsData = $data['products'];
    //     $paymentMethod = $data['payment_method'];

    //     $paymentFeesPercentage = 0;
    //     if ($paymentMethod === 'qi') {
    //         $paymentFeesPercentage = config('services.qi.fees', 10);
    //     } elseif ($paymentMethod === 'installment') {
    //         $paymentFeesPercentage = config('services.aqsati.aqsati_installment_fees', 15) + config('services.aqsati.our_fees', 5);
    //     }

    //     $amount = 0;
    //     $shippingFees = 0;

    //     foreach ($productsData as $item) {
    //         $product = Product::find($item['product_id']);
    //         if (!$product) {
    //             MessageService::abort(404, 'messages.product.not_found');
    //         }

    //         $products[] = $product;
    //         $amount += $product->final_price * $item['quantity'];
    //         $shippingFees += $product->getShippingRateAttribute($item['quantity']) * $item['quantity'];
    //     }

    //     $coupon = null;
    //     $couponDiscountValue = 0;
    //     if (!empty($data['coupon_code'])) {
    //         $coupon = Coupon::where('code', $data['coupon_code'])->first();
    //         if (!$coupon || !$coupon->is_active) {
    //             MessageService::abort(404, 'messages.coupon.invalid');
    //         }

    //         $couponDiscountValue = $coupon->type === 'percentage'
    //             ? $amount * ($coupon->amount / 100)
    //             : $coupon->amount;
    //     }

    //     $promotionCartTotal = Promotion::where('type', 'cart_total')->where('status', 'active')->where('start_at', '<=', now())->where('end_at', '>=', now())->get()->last();

    //     $promotionCartTotalDiscountValue = null;

    //     if ($promotionCartTotal && $amount >= $promotionCartTotal->min_cart_total) {

    //         if ($promotionCartTotal->discount_type == 'fixed') {
    //             $promotionCartTotalDiscountValue = $promotionCartTotal->discount_value;
    //         } else {
    //             $promotionCartTotalDiscountValue = $amount * ($promotionCartTotal->discount_value / 100);
    //         }
    //     }

    //     $promotionFreeShipping = Promotion::where('type', 'shipping')->where('status', 'active')->where('start_at', '<=', now())->where('end_at', '>=', now())->get()->last();

    //     if ($promotionFreeShipping) {
    //         $shippingFees = 0;
    //     }

    //     $subtotal = $amount + $shippingFees - $couponDiscountValue - $promotionCartTotalDiscountValue;
    //     $paymentFeesValue = $subtotal * ($paymentFeesPercentage / 100);




    //     return [
    //         'amount' => round($amount, 2),
    //         'promotion_cart_total_discount_value' => round($promotionCartTotal ? $promotionCartTotalDiscountValue : null, 2),
    //         'amount_after_promotion_cart_total' => round($amount - ($promotionCartTotal ? $promotionCartTotalDiscountValue : 0), 2),
    //         'promotion_cart_total' => $promotionCartTotal ? new PromotionResource($promotionCartTotal) : null,
    //         'shipping_fees' => $shippingFees,
    //         'promotion_free_shipping' => $promotionFreeShipping ? new PromotionResource($promotionFreeShipping) : null,
    //         'amount_with_shipping' => round($amount + $shippingFees - $promotionCartTotalDiscountValue, 2),
    //         'coupon_discount_value' => $coupon ? $couponDiscountValue : null,
    //         'amount_with_shipping_after_coupon' => round($subtotal, 2),
    //         'payment_fees_percentage' => $paymentFeesPercentage,
    //         'payment_fees_value' => round($paymentFeesValue, 2),
    //         'amount_with_shipping_after_coupon_and_payment_fees' => round($subtotal + $paymentFeesValue, 2),
    //         'installment_count' => $paymentMethod == 'installment' ? 10 : null,
    //     ];
    // }


    public function checkOrderDetails(array $data)
    {
        $items         = $data['products'] ?? [];
        $paymentMethod = $data['payment_method'] ?? null;
        $couponCode    = trim($data['coupon_code'] ?? '');

        // Helpers: cents <-> float(2)
        $toCents = fn($v) => (int) round(((float) $v) * 100);
        $fromCents = fn($c) => round($c / 100, 2);
        $roundOrNull = fn($v) => $v === null ? null : round($v, 2);

        // 1) جلب المنتجات دفعة واحدة وتحقق أساسي
        $ids = array_map(fn($i) => (int) ($i['product_id'] ?? 0), $items);
        $products = Product::whereIn('id', $ids)->get()->keyBy('id');

        foreach ($items as $it) {
            $pid = (int) ($it['product_id'] ?? 0);
            $qty = (int) ($it['quantity'] ?? 0);
            if ($qty <= 0 || !$products->has($pid)) {
                MessageService::abort(422, 'messages.cart.invalid_item');
            }
            // TODO: تحقق المخزون/التوفّر إن لزم
        }

        // 2) مبالغ أولية (بالسنتات)
        $amountCents       = 0; // مجموع أسعار المنتجات فقط
        $shippingFeesCents = 0;

        foreach ($items as $it) {
            $p   = $products[$it['product_id']];
            $qty = (int) $it['quantity'];

            $lineProductsCents = $toCents($p->final_price) * $qty;
            $amountCents += $lineProductsCents;

            // ملاحظة: دالة الشحن تُعيد قيمة بوحدات العملة؛ نُحوّل ونضرب بالكمية
            $rate = (float) $p->getShippingRateAttribute($qty);
            $shippingFeesCents += $toCents($rate) * $qty;
        }

        // 3) عروض السلة (cart_total) — تُطبّق أولًا على مبلغ المنتجات فقط
        $now = now();
        $promotionCartTotal = Promotion::where('type', 'cart_total')
            ->where('status', 'active')
            ->where('start_at', '<=', $now)
            ->where('end_at', '>=', $now)
            ->orderByDesc('id') // إن وجدت priority أضفها هنا
            ->first();

        $promoCartDiscountCents = 0;
        if ($promotionCartTotal && $amountCents >= $toCents($promotionCartTotal->min_cart_total)) {
            if ($promotionCartTotal->discount_type === 'fixed') {
                $promoCartDiscountCents = $toCents($promotionCartTotal->discount_value);
            } else {
                // نسبة من مبلغ المنتجات
                $promoCartDiscountCents = (int) floor($amountCents * ((float)$promotionCartTotal->discount_value) / 100);
            }
            // لا تتجاوز الأساس
            $promoCartDiscountCents = max(0, min($promoCartDiscountCents, $amountCents));
        }

        // 4) كوبون — يُطبّق بعد عرض السلة وعلى المنتجات فقط
        $coupon = null;
        $couponDiscountCents = 0;
        if ($couponCode !== '') {
            $coupon = Coupon::where('code', $couponCode)->first();
            if (!$coupon || !$coupon->is_active) {
                MessageService::abort(404, 'messages.coupon.invalid');
            }
            // قاعدة: الأساس = مبلغ المنتجات بعد خصم عرض السلة
            $couponBaseCents = max(0, $amountCents - $promoCartDiscountCents);

            if ($coupon->type === 'percentage') {
                $couponDiscountCents = (int) floor($couponBaseCents * ((float)$coupon->amount) / 100);
            } else {
                $couponDiscountCents = $toCents($coupon->amount);
            }
            $couponDiscountCents = max(0, min($couponDiscountCents, $couponBaseCents));
        }

        // 5) الشحن المجاني — بلا شروط حاليًا
        $promotionFreeShipping = Promotion::where('type', 'shipping')
            ->where('status', 'active')
            ->where('start_at', '<=', $now)
            ->where('end_at', '>=', $now)
            ->orderByDesc('id')
            ->first();

        if ($promotionFreeShipping) {
            $shippingFeesCents = 0;
        }

        // 6) المجموع الجزئي قبل رسوم الدفع
        // ملاحظة: الكوبون لا يطال الشحن بحسب قاعدتك
        $subtotalCents = max(0, ($amountCents - $promoCartDiscountCents - $couponDiscountCents) + $shippingFeesCents);

        // 7) رسوم الدفع — تُحتسب أخيرًا على subtotal (بعد كل شيء)
        $feesPct = 0.0;
        if ($paymentMethod === 'qi') {
            $feesPct = (float) config('services.qi.fees', 10);
        } elseif ($paymentMethod === 'installment') {
            $feesPct = (float) config('services.aqsati.aqsati_installment_fees', 15)
                + (float) config('services.aqsati.our_fees', 5);
        }
        // value = نسبة من subtotal
        $paymentFeesCents = (int) floor($subtotalCents * $feesPct / 100);

        $grandTotalCents = $subtotalCents + $paymentFeesCents;

        // 8) عدد أقساط التقسيط من services
        $installmentCount = $paymentMethod === 'installment'
            ? (int) config('services.aqsati.count_of_month', 10)
            : null;

        // 9) إرجاع منسّق (خانتان فقط) مع nulls حقيقية عند اللزوم
        return [
            'amount'                                   => $fromCents($amountCents),
            'promotion_cart_total_discount_value'      => $promotionCartTotal ? $fromCents($promoCartDiscountCents) : null,
            'amount_after_promotion_cart_total'        => $fromCents($amountCents - $promoCartDiscountCents),

            'promotion_cart_total'                     => $promotionCartTotal ? new PromotionResource($promotionCartTotal) : null,

            'shipping_fees'                            => $fromCents($shippingFeesCents),
            'promotion_free_shipping'                  => $promotionFreeShipping ? new PromotionResource($promotionFreeShipping) : null,

            'amount_with_shipping'                     => $fromCents(($amountCents - $promoCartDiscountCents) + $shippingFeesCents),

            'coupon_discount_value'                    => $coupon ? $fromCents($couponDiscountCents) : null,
            'amount_with_shipping_after_coupon'        => $fromCents($subtotalCents),

            'payment_fees_percentage'                  => round($feesPct, 6), // تبقى كنسبة كما هي
            'payment_fees_value'                       => $fromCents($paymentFeesCents),
            'amount_with_shipping_after_coupon_and_payment_fees' => $fromCents($grandTotalCents),

            'installment_count'                        => $installmentCount,
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


        $direct_order =   isset($data['direct_order']) && $data['direct_order'] == true;


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

            $promotion = $product->getBestPromotionAttribute();

            $orderProduct = OrderProduct::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $productData['quantity'],
                'price' => $product->final_price,
                'cost_price' => $product->final_cost_price,
                'status' => 'pending',
                'shipping_rate' => $product->getShippingRateAttribute($productData['quantity']) * $productData['quantity'],
                'color_id' => $productData['color_id'] ?? null,
                'promotion_id' => $promotion ? $promotion->id : null,
                'promotion_title' => $promotion ? $promotion->title[LanguageService::getLocale()] : null,
                'promotion_discount_type' => $promotion ? $promotion->discount_type : null,
                'promotion_discount_value' => $promotion ? $promotion->discount_value : null,
            ]);
        }


        $promotionCartTotal = Promotion::where('type', 'cart_total')->where('status', 'active')->where('start_at', '<=', now())->where('end_at', '>=', now())->get()->last();

        if ($promotionCartTotal && $order->total_amount >= $promotionCartTotal->min_cart_total) {
            $order->promotion_cart_id = $promotionCartTotal->id;
            $order->promotion_cart_title = $promotionCartTotal->title[LanguageService::getLocale()];
            $order->promotion_cart_discount_type = $promotionCartTotal->discount_type;
            $order->promotion_cart_discount_value = $promotionCartTotal->discount_value;
        }

        $promotionFreeShipping = Promotion::where('type', 'shipping')->where('status', 'active')->where('start_at', '<=', now())->where('end_at', '>=', now())->get()->last();

        if ($promotionFreeShipping) {
            $order->promotion_shipping_id = $promotionFreeShipping->id;
            $order->promotion_shipping_title = $promotionFreeShipping->title[LanguageService::getLocale()];
            $order->promotion_free_shipping = true;
        }


        if ($data['payment_method'] == 'qi') {

            $order->payment_method = 'qi';
            $order->payment_fees = config('services.qi.fees', 3);
            $order->metadata = [
                'direct_order' => $direct_order,
            ];

            $order->save();


            $qiPaymentService = new QiPaymentService();



            $paymentData = [
                'requestId' =>  "{$order->id}-" . Str::random(10),
                // 
                'amount' => $order->total_amount,
                'locale' => 'en_US',
                'currency' => 'IQD',
                // 'description' => trans('messages.payment.description'),
                'finishPaymentUrl' => $successUrl . '/' . $order_id,
                'notificationUrl' =>  url('/api/webhook/qi-payment'),
                'customerInfo' => [
                    "firstName" => $user->name ?? '',
                    "phone" => $user->phone,
                    "accountId" => $user->id,
                    "accountNumber" => $user->phone,
                    "address" => $order->address?->address ?? '',
                    "city" => $order->address?->city ?? '',
                ],
                // 'additionalInfo' => [],
            ];


            $paymentSession = $qiPaymentService->createPayment($paymentData);
            // abort(response()->json($paymentSession));

            $order->metadata = $paymentSession;

            $order->payment_session_id =  'qi-' . $paymentSession['requestId'];
        } elseif ($data['payment_method'] == 'cash') {
            $order->payment_method = 'cash';
            $order->payment_fees = 0;
            $order->save();

            OrderNotification::newOrder($order);


            if (!$direct_order) {
                $user->cartItems()->delete();
            }
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

            if (!$direct_order) {
                $user->cartItems()->delete();
            }

            OrderNotification::newOrder($order);
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
                "direct_order" => $direct_order,
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

            $address['phone'] = $data['address']['phone'];
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

        OrderNotification::newOrder($order);

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
            'status' => 'completed',
            'method' => 'installment',
        ]);

        // if direct order
        if ($order->metadata['direct_order'] ?? false) {
            $user = User::auth();
            $user->cartItems()->delete();
        }



        return $order;
    }

    public function update($order, $data)
    {
        if ($order->status != $data['status']) {


            $order->status = $data['status'];
            $order->save();


            Status::create([
                'statusable_id' => $order->id,
                'statusable_type' => Order::class,
                'status' => $data['status'],
            ]);

            OrderNotification::updateOrderStatus($order);
        }


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

    public function refund($order, $data)
    {


        if ($data['method'] == 'qi' && $order->payment_method != 'qi') {
            MessageService::abort(400, 'messages.order.payment_method_not_qi');
        }

        $order->statuses()->create([
            'status' => 'refunded',
            'statusable_type' => Order::class,
            'statusable_id' => $order->id,
        ]);

        $payment = $order->payments()->create([
            'order_id' => $order->id,
            'amount' => $data['amount'],
            'description' => [
                'ar' => 'استرداد الدفع السبب ' . $data['reason'],
                'en' => 'Refund Payment Reason ' . $data['reason'],
            ],
            'status' => 'pending',
            'is_refund' => true,
            'method' => $data['method'],
            'attached' => $data['attached'] ?? null,
            'metadata' => [
                'requestId' => 'qi-refund-' . $order->id . '-' . Str::random(10),
            ],
        ]);

        if ($data['method'] == 'qi') {
            $qiPaymentService = new QiPaymentService();
            $qiData = [
                'requestId' => $payment->metadata['requestId'],
                'amount' => $data['amount'],
                'message' => $data['reason'],
                'extParams' => [
                    'orderId' => $order->id,
                ],
            ];
            $qiResponse = $qiPaymentService->refundPayment($payment->metadata['paymentId'], $qiData);

            // abort(
            //     response()->json(
            //         [
            //             'payment_id' => $order->metadata['paymentId'],
            //             'response' => $qiResponse,
            //             'qiData' => $qiData,
            //         ]
            //     )
            // );
            if ($qiResponse['status'] == 'SUCCESS') {
                $payment->update([
                    'amount' => $qiResponse['amount'],
                    'status' => 'completed',
                    'metadata' => $qiResponse,
                ]);
            } else {
                $payment->update([
                    'status' => 'failed',
                ]);
            }
        } elseif ($data['method'] == 'transfer') {
            $payment->update([
                'status' => 'completed',
            ]);
        } elseif ($data['method'] == 'cash') {
            $payment->update([
                'status' => 'completed',
            ]);
        } elseif ($data['method'] == 'installment') {
            $payment->update([
                'status' => 'completed',
            ]);
        } else {
            MessageService::abort(400, 'messages.order.payment_method_not_found');
        }

        $order->save();


        //     $table->enum('status', ["pending", "completed", "failed"]);


        return $order;
    }
}
