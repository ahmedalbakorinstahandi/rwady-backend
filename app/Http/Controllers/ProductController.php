<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\CreateProductRequest;
use App\Http\Requests\Product\ReOrderProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Http\Services\ProductService;
use App\Services\ResponseService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index()
    {
        $products = $this->productService->index(request()->all());

        return  ResponseService::response(
            [
                'success' => true,
                'data' => $products,
                'meta' => true,
                'resource' => ProductResource::class,
                'status' => 200,
            ]
        );
    }

    public function show(int $id)
    {
        $product = $this->productService->show($id);

        return  ResponseService::response(
            [
                'success' => true,
                'data' => $product,
                'resource' => ProductResource::class,
                'status' => 200,
            ]
        );
    }

    public function create(CreateProductRequest $request)
    {
        $product = $this->productService->create($request->validated());

        return  ResponseService::response(
            [
                'success' => true,
                'data' => $product,
                'message' => 'messages.product.created_successfully',
                'status' => 201,
                'resource' => ProductResource::class,
            ]
        );
    }

    public function update(UpdateProductRequest $request, int $id)
    {

        $product = $this->productService->show($id);

        $product = $this->productService->update($request->validated(), $product);

        return  ResponseService::response(
            [
                'success' => true,
                'data' => $product,
                'message' => 'messages.product.updated_successfully',
                'status' => 200,
                'resource' => ProductResource::class,
            ]
        );
    }

    public function delete(int $id)
    {
        $product = $this->productService->show($id);
        $this->productService->delete($product);

        return  ResponseService::response(
            [
                'success' => true,
                'message' => 'messages.product.deleted_successfully',
                'status' => 200,
            ]
        );
    }

    public function toggleFavorite(int $id)
    {
        $product = $this->productService->show($id);
        $isFavorite = $this->productService->toggleFavorite($product);

        return  ResponseService::response(
            [
                'success' => true,
                'data' => [
                    'is_favorite' => $isFavorite,
                ],
                'message' => $isFavorite ? 'messages.product.added_to_favorites' : 'messages.product.removed_from_favorites',
                'status' => 200,
            ]
        );
    }

    public function reorder($id, ReOrderProductRequest $request)
    {
        $product = $this->productService->show($id);

        $product = $this->productService->reorder($product, $request->validated());


        return $this->index();
    }
}
