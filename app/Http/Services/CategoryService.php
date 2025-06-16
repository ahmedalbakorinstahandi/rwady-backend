<?php

namespace App\Http\Services;

use App\Http\Permissions\CategoryPermission;
use App\Models\Category;
use App\Services\FilterService;
use App\Services\LanguageService;
use App\Services\MessageService;

class CategoryService
{
    public function index(array $filters = [])
    {
        $query = Category::query();
        $searchFields = ['name', 'description'];
        $numericFields = [];
        $dateFields = ['created_at'];
        $exactMatchFields = ['availability', 'parent_id'];
        $inFields = [];

        $query = CategoryPermission::filterIndex($query);

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
            // 'products',
            'seo'
        ]);

        $category->loadCount('products');

        return $category;
    }

    public function create($data)
    {
        $data = LanguageService::prepareTranslatableData($data, new Category);

        $category = Category::create($data);


        $category = $this->show($category->id);


        return $category;
    }

    public function update($data, $category)
    {
        $data = LanguageService::prepareTranslatableData($data, $category);

        $category->update($data);

        $category = $this->show($category->id);

        return $category;
    }

    public function delete($category)
    {
        $category->seo()->delete();
        $category->children()->delete();
        $category->delete();
    }
}
