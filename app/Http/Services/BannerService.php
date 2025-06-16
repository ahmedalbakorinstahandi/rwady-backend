<?php

namespace App\Http\Services;

use App\Http\Permissions\BannerPermission;
use App\Models\Banner;
use App\Services\LanguageService;
use App\Services\MessageService;
use App\Services\FilterService;

class BannerService
{
    public function index(array $filters = [])
    {
        $query = Banner::query();
        $searchFields = ['title', 'description', 'button_text', 'link'];
        $numericFields = [];
        $dateFields = ['start_date', 'end_date', 'created_at'];
        $exactMatchFields = ['is_popup', 'is_active'];
        $inFields = [];

        $query = BannerPermission::filterIndex($query);

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
        $banner = Banner::where('id', $id)->first();
        if (!$banner) {
            MessageService::abort(404, 'messages.banner.not_found');
        }
        return $banner;
    }

    public function create($data)
    {

        $data = LanguageService::prepareTranslatableData($data, new Banner);

        $banner = Banner::create($data);

        // $banner = $this->show($banner->id);

        return $banner;
    }

    public function update($data, $banner)
    {
        $data = LanguageService::prepareTranslatableData($data, $banner);

        $banner->update($data);

        // $banner = $this->show($banner->id);

        return $banner;
    }

    public function delete($banner)
    {
        $banner->delete();
    }
}
