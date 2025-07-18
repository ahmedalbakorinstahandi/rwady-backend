<?php

return [
    'admin' => [
        'order' => [
            'new' => [
                'title' => 'New Order',
                'body' => 'A new order #:order_id has been created by :user_name',
            ],

            'status' => [
                'pending' => [
                    'title' => 'Order Pending',
                    'body' => 'The order #:order_id has been updated to pending',
                ],

                'in_progress' => [
                    'title' => 'Order In Progress',
                    'body' => 'The order #:order_id has been updated to in progress',
                ],

                'shipping' => [
                    'title' => 'Order Shipping',
                    'body' => 'The order #:order_id has been updated to shipping',
                ],

                'completed' => [
                    'title' => 'Order Completed',
                    'body' => 'The order #:order_id has been updated to completed',
                ],

                'cancelled' => [
                    'title' => 'Order Cancelled',
                    'body' => 'The order #:order_id has been updated to cancelled',
                ],
            ],

        ],
    ],
];
