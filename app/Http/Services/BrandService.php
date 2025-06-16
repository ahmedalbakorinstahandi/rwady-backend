<?php

namespace App\Http\Services;

use App\Http\Permissions\BrandPermission;
use App\Models\Brand;
use App\Services\LanguageService;
use App\Services\MessageService;
use App\Services\FilterService;

class BrandService
{
    public function index(array $filters = [])
    {
        $query = Brand::query();
        $searchFields = ['name', 'description'];
        $numericFields = [];
        $dateFields = ['created_at'];
        $exactMatchFields = ['availability'];
        $inFields = [];

        $query = BrandPermission::filterIndex($query);

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
        $brand = Brand::where('id', $id)->first();
        if (!$brand) {
            MessageService::abort(404, 'brand.not_found');
        }
        $brand->load(['seo']);
        return $brand;
    }

    public function create($data)
    {
        $data = LanguageService::prepareTranslatableData($data, new Brand);
        $brand = Brand::create($data);

        if (isset($data['seo'])) {
            $brand->seo()->create([
                'seoable_type' => Brand::class,
                'seoable_id' => $brand->id,
            ], $data['seo']);
        }

        $brand = $this->show($brand->id);

        return $brand;
    }

    public function update($data, $brand)
    {
        $data = LanguageService::prepareTranslatableData($data, $brand);
        $brand->update($data);

        if (isset($data['seo'])) {
            $brand->seo()->updateOrCreate([
                'seoable_type' => Brand::class,
                'seoable_id' => $brand->id,
            ], $data['seo']);
        }

        $brand = $this->show($brand->id);

        return $brand;
    }

    public function delete($brand)
    {
        $brand->products()->detach();
        $brand->delete();
    }
} 