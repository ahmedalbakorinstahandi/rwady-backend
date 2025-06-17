<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\AssignProductsToCategoryRequest;
use App\Http\Requests\Category\CreateCategoryRequest;
use App\Http\Requests\Category\ReOrderCategoryRequest;
use App\Http\Requests\Category\UnassignProductsFromCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Services\CategoryService;
use App\Services\ResponseService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        $categories = $this->categoryService->index(request()->all());

        return ResponseService::response([
            'success' => true,
            'data' => $categories,
            'meta' => true,
            'resource' => CategoryResource::class,
            'status' => 200,
        ]);
    }

    public function show(int $id)
    {
        $category = $this->categoryService->show($id);

        return ResponseService::response([
            'success' => true,
            'data' => $category,
            'status' => 200,
        ]);
    }

    public function create(CreateCategoryRequest $request)
    {
        $category = $this->categoryService->create($request->validated());

        return ResponseService::response([
            'success' => true,
            'data' => $category,
            'message' => 'messages.category.created_successfully',
            'status' => 201,
            'resource' => CategoryResource::class,
        ]);
    }

    public function update(UpdateCategoryRequest $request, int $id)
    {
        $category = $this->categoryService->show($id);
        $category = $this->categoryService->update($request->validated(), $category);

        return ResponseService::response([
            'success' => true,
            'data' => $category,
            'message' => 'messages.category.updated_successfully',
            'status' => 200,
            'resource' => CategoryResource::class,
        ]);
    }

    public function delete(int $id)
    {
        $category = $this->categoryService->show($id);
        $this->categoryService->delete($category);

        return ResponseService::response([
            'success' => true,
            'message' => 'messages.category.deleted_successfully',
            'status' => 200,
        ]);
    }

    public function reorder($id, ReOrderCategoryRequest $request)
    {
        $category = $this->categoryService->show($id);

        $category = $this->categoryService->reorder($category, $request->validated());


        return $this->index();
    }

    public function assignProductsToCategory(int $id, AssignProductsToCategoryRequest $request)
    {
        $category = $this->categoryService->show($id);

        $this->categoryService->assignProductsToCategory($category, $request->validated());

        return ResponseService::response([
            'success' => true,
            'message' => 'messages.category.products_assigned_successfully',
            'data' => [
                'products_count' => $category->products()->count(),
            ],
            'status' => 200,
        ]);
    }

    public function unassignProductsFromCategory(int $id, UnassignProductsFromCategoryRequest $request)
    {
        $category = $this->categoryService->show($id);

        $this->categoryService->unassignProductsFromCategory($category, $request->validated());

        return ResponseService::response([
            'success' => true,
            'message' => 'messages.category.products_unassigned_successfully',
            'data' => [
                'products_count' => $category->products()->count(),
            ],
            'status' => 200,
        ]);
    }
}
