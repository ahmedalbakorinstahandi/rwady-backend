<?php

namespace App\Http\Services;

use App\Http\Permissions\FeaturedSectionPermission;
use App\Models\FeaturedSection;
use App\Services\LanguageService;
use App\Services\MessageService;
use App\Services\FilterService;

class FeaturedSectionService
{
    public function index(array $filters = [])
    {
        $query = FeaturedSection::query();
        $searchFields = ['name'];
        $numericFields = [];
        $dateFields = ['start_date', 'end_date', 'created_at'];
        $exactMatchFields = ['availability'];
        $inFields = [];

        $query = FeaturedSectionPermission::filterIndex($query);

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
        $section = FeaturedSection::where('id', $id)->first();
        if (!$section) {
            MessageService::abort(404, 'messages.featured_section.not_found');
        }
        return $section;
    }

    public function create($data)
    {
        $data = LanguageService::prepareTranslatableData($data, new FeaturedSection);
        $section = FeaturedSection::create($data);
        return $section;
    }

    public function update($data, $section)
    {
        $data = LanguageService::prepareTranslatableData($data, $section);

        $section->update($data);

        return $section;
    }

    public function delete($section)
    {
        $section->delete();
    }
} 