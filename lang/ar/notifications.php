<?php

return [
    'admin' => [
        'order' => [
            'new' => [
                'title' => 'طلب جديد',
                'body' => 'تم إنشاء طلب جديد رقم :order_id من قبل :user_name',
            ],

            'status' => [
                'pending' => [
                    'title' => 'طلب قيد المراجعة',
                    'body' => 'تم تحديث حالة الطلب رقم :order_id إلى قيد المراجعة',
                ],
                'in_progress' => [
                    'title' => 'طلب قيد التنفيذ',
                    'body' => 'تم تحديث حالة الطلب رقم :order_id إلى قيد التنفيذ',
                ],
                'shipping' => [
                    'title' => 'طلب قيد الشحن',
                    'body' => 'تم تحديث حالة الطلب رقم :order_id إلى قيد الشحن',
                ],
                'completed' => [
                    'title' => 'تم إكمال الطلب',
                    'body' => 'تم تحديث حالة الطلب رقم :order_id إلى مكتمل',
                ],
                'cancelled' => [
                    'title' => 'تم إلغاء الطلب',
                    'body' => 'تم تحديث حالة الطلب رقم :order_id إلى ملغي',
                ],
            ],
        ],
    ],
];
