<?php

namespace App\Http\Services;

use App\Http\Permissions\OrderPaymentPermission;
use App\Models\OrderPayment;
use App\Services\FilterService;
use App\Services\MessageService;

class OrderPaymentService
{
    public function index($data)
    {
        $query = OrderPayment::query();

        $query = OrderPaymentPermission::index($query);

        $orderPayments = FilterService::applyFilters(
            $query,
            $data,
            ['description'],
            ['order_id', 'amount'],
            [],
            ['order_id', 'status', 'is_refund', 'method'],
            ['order_id', 'status', 'method'],
        );



        return $orderPayments;
    }

    public function show($id)
    {
        $orderPayment = OrderPayment::where('id', $id)->first();
        if (!$orderPayment) {
            MessageService::abort(404, 'messages.order_payment.not_found');
        }
        return $orderPayment;
    }

    public function create($data)
    {


        $paymentData = [
            'order_id' => $data['order_id'],
            'amount' => $data['amount'],
            'description' => [
                'ar' =>  'تم إضافة دفعة جديدة بمبلغ ' . $data['amount'] . ' بواسطة الأدمن ملاحظات ' . $data['message'],
                'en' => 'A new payment was added with an amount of ' . $data['amount'] . ' by the admin with notes ' . $data['message'],
            ],
            'status' => $data['status'],
            'method' => $data['method'],
            'attached' => $data['attached'] ?? null,
            'created_by' => 'admin',
            'is_refund' => false,
        ];

        $orderPayment = OrderPayment::create($paymentData);



        return $orderPayment;
    }

    public function update($orderPayment, $data)
    {

        $orderPayment->update($data);


        return $orderPayment;
    }

    public function delete($orderPayment)
    {
        $orderPayment->delete();
    }
}
