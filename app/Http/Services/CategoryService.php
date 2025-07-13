<?php

namespace App\Http\Services;

use App\Http\Permissions\CategoryPermission;
use App\Models\Category;
use App\Models\Product;
use App\Services\FilterService;
use App\Services\LanguageService;
use App\Services\MessageService;
use App\Services\OrderHelper;

class CategoryService
{
    public function index($filters = [])
    {
        $query = Category::query()->with('children');

        $filters['sort_field'] = 'orders';
        $filters['sort_order'] =  $filters['sort_order'] ?? 'asc';

        $searchFields = ['name', 'description'];
        $numericFields = [];
        $dateFields = ['created_at'];
        $exactMatchFields = ['availability', 'parent_id'];
        $inFields = [];

        $query = CategoryPermission::filterIndex($query);

        if (empty($filters['parent_id'])) {
            $query->whereNull('parent_id');
        }

        $query = FilterService::applyFilters(
            $query,
            $filters,
            $searchFields,
            $numericFields,
            $dateFields,
            $exactMatchFields,
            $inFields
        );

        return $query;
    }

    public function show(int $id)
    {
        $category = Category::where('id', $id)->first();
        if (!$category) {
            MessageService::abort(404, 'messages.category.not_found');
        }

        $category->load([
            'parent',
            // 'children',
            'seo'
        ]);

        $category->loadCount('products');

        return $category;
    }

    public function create($data)
    {
        $data = LanguageService::prepareTranslatableData($data, new Category);

        $category = Category::create($data);

        OrderHelper::assign($category);

        if (isset($data['seo'])) {
            $category->seo()->create([
                'meta_title' => $data['seo']['meta_title'] ?? null,
                'meta_description' => $data['seo']['meta_description'] ?? null,
                'keywords' => $data['seo']['keywords'] ?? null,
                'image' => $data['seo']['image'] ?? null,
            ]);
        }


        $category = $this->show($category->id);


        return $category;
    }

    public function update($data, $category)
    {
        $data = LanguageService::prepareTranslatableData($data, $category);

        $category->update($data);

        if (isset($data['seo'])) {

            $category->seo()->updateOrCreate([
                'seoable_type' => Category::class,
                'seoable_id' => $category->id,
            ], [
                'meta_title' => $data['seo']['meta_title'] ?? null,
                'meta_description' => $data['seo']['meta_description'] ?? null,
                'keywords' => $data['seo']['keywords'] ?? null,
                'image' => $data['seo']['image'] ?? null,
            ]);
        }

        $category = $this->show($category->id);

        return $category;
    }

    public function delete($category)
    {
        $category->seo()->delete();
        $category->children()->delete();
        $category->delete();
    }

    public function reorder($category, $validatedData)
    {
        OrderHelper::reorder($category, $validatedData['orders']);

        return $category;
    }

    public function assignProductsToCategory($category, $data)
    {
        $productIds = array_unique(array_map('intval', $data['product_ids']));

        $validProductIds = Product::whereIn('id', $productIds)
            ->pluck('id')
            ->toArray();

        // if (empty($validProductIds)) {
        //     MessageService::abort(404, 'messages.product.not_found');
        // }

        $category->products()->syncWithoutDetaching($validProductIds);
        $category->loadCount('products');

        return $category;
    }

    public function unassignProductsFromCategory($category, $data)
    {
        $productIds = array_unique(array_map('intval', $data['product_ids']));

        $validProductIds = Product::whereIn('id', $productIds)
            ->pluck('id')
            ->toArray();

        // if (empty($validProductIds)) {
        //     MessageService::abort(404, 'messages.product.not_found');
        // }

        $category->products()->detach($validProductIds);
        $category->loadCount('products');

        return $category;
    }
}
