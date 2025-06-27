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
use App\Services\Payment\QiPaymentService;
use Illuminate\Support\Str;

class OrderService
{
    public function index($filters = [])
    {
        $query = Order::query()->with('children');

        $filters['sort_field'] = 'orders';
        $filters['sort_order'] =  $filters['sort_order'] ?? 'asc';

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

        $order->load(['orderProducts.product', 'couponUsage', 'payments', 'statuses']);


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

            $data['payment_method'] = 'qi';
            $data['payment_fees'] = config('services.qi.fees', 10);


            $qiPaymentService = new QiPaymentService();


            $paymentData = [
                'amount' => $order->total_amount_with_fees,
                'currency' => 'IQD',
                'requestId' => $order->id,
                'description' => trans('messages.payment.description'),
                'successRedirectUrl' => $successUrl . '/' . $order->id,
                'failRedirectUrl' => $failUrl . '/' . $order->id,
            ];

            $paymentSession = $qiPaymentService->createPayment($paymentData);

            $order->payment_session_id =  'qi_' . $paymentSession['id'];
        } elseif ($data['payment_method'] == 'cash') {
            $order->payment_method = 'cash';
            $order->payment_fees = 0;
        } elseif ($data['payment_method'] == 'transfer') {
            $order->payment_method = 'transfer';
            $order->payment_fees = 0;
        } elseif ($data['payment_method'] == 'installment') {
            $order->payment_method = 'installment';
            $order->payment_fees = 0;
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
        $order->delete();
    }
}
