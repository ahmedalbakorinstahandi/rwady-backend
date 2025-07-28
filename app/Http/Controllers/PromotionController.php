<?php

namespace App\Http\Controllers;

use App\Http\Requests\Promotion\CreatePromotionRequest;
use App\Http\Requests\Promotion\UpdatePromotionRequest;
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

    public function show($id)
    {
        $promotion = $this->promotionService->show($id);

        return ResponseService::response(
            [
                'success' => true,
                'data' => $promotion,
                'resource' => PromotionResource::class,
            ],
            200
        );
    }

    public function create(CreatePromotionRequest $request)
    {

        $data = $request->validated();

        $promotion = $this->promotionService->create($data);

        return ResponseService::response(
            [
                'success' => true,
                'data' => $promotion,
                'message' => 'messages.promotion.created_successfully',
                'resource' => PromotionResource::class,
            ],
            201
        );
    }

    public function update(UpdatePromotionRequest $request, $id)
    {
        $data = $request->validated();

        $promotion = $this->promotionService->show($id);

        $promotion = $this->promotionService->update($promotion, $data);

        return ResponseService::response(
            [
                'success' => true,
                'data' => $promotion,
                'message' => 'messages.promotion.updated_successfully',
                'resource' => PromotionResource::class,
            ],
            200
        );
    }

    // delete
    public function delete($id)
    {
        $promotion = $this->promotionService->show($id);

        $this->promotionService->delete($promotion);

        return ResponseService::response(
            [
                'success' => true,
                'message' => 'messages.promotion.deleted_successfully',
            ],
            200
        );
    }
}
