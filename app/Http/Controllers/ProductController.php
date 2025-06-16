<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\CreateProductRequest;
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

    public function index(Request $request)
    {
        $products = $this->productService->index($request->all());

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
                'status' => 201,
                'resource' => ProductResource::class,
            ]
        );
    }
}
