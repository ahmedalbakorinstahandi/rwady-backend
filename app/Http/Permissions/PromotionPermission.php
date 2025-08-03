<?php

namespace App\Http\Permissions;

use App\Models\User;
use App\Services\MessageService;

class PromotionPermission
{
    public static function filterIndex($query)
    {
        $user = User::auth();

        if (!$user->isAdmin()) {
            return $query->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('start_at')
                        ->orWhere('start_at', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('end_at')
                        ->orWhere('end_at', '>=', now());
                });
        }

        return $query;
    }

    public static function show($promotion)
    {
        $user = User::auth();

        if (!$user || !$user->isAdmin()) {
            if ($promotion->status != 'active') {
                MessageService::abort(403, 'messages.promotion.not_found');
            }

            if ($promotion->start_at && $promotion->end_at) {
                if ($promotion->start_at > now() || $promotion->end_at < now()) {
                    MessageService::abort(403, 'messages.promotion.not_found');
                }
            }

            return true;
        }
    }
}
