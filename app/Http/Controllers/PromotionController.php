<?php

namespace App\Http\Controllers;

use App\Http\Resources\PromotionResource;
use App\Http\Services\PromotionService;
use App\Services\ResponseService;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    protected $promotionService;

    public function __construct(PromotionService $promotionService)
    {
        $this->promotionService = $promotionService;
    }

    

    public function index(Request $request)
    {
        $filters = $request->all();

        $promotions = $this->promotionService->index($filters);

        return ResponseService::response(
            [
                'success' => true,
                'data' => $promotions,
                'resource' => PromotionResource::class,
                'meta' => true,
            ],
            200
        );
    }
}
