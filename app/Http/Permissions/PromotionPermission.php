<?php

namespace App\Http\Permissions;

use App\Models\User;
use App\Services\MessageService;

class PromotionPermission
{
    public static function filterIndex($query)
    {
        $user = User::auth();

        if (!$user || !$user->isAdmin()) {
            $today = now()->startOfDay();
            
            return $query->where('status', 'active')
                ->where(function ($q) use ($today) {
                    $q->whereNull('start_at')
                        ->orWhere('start_at', '<=', $today);
                })
                ->where(function ($q) use ($today) {
                    $q->whereNull('end_at')
                        ->orWhere('end_at', '>=', $today);
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
                $today = now()->startOfDay();
                $startDate = $promotion->start_at->startOfDay();
                $endDate = $promotion->end_at->startOfDay();
                
                if ($startDate > $today || $endDate < $today) {
                    MessageService::abort(403, 'messages.promotion.not_found');
                }
            }

            return true;
        }
    }
}
