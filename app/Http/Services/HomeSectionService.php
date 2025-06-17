<?php

namespace App\Http\Services;

use App\Http\Resources\BannerResource;
use App\Http\Resources\BrandResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\FeaturedSectionResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\SettingResource;
use App\Models\Banner;
use App\Models\HomeSection;
use App\Models\Setting;
use App\Models\User;
use App\Services\MessageService;
use App\Services\OrderHelper;

class HomeSectionService
{
    public function getHomeSections()
    {
        $homeSections = HomeSection::where('availability', true)->orderBy('orders', 'asc')->get();


        return $homeSections;
    }



    public function show($id)
    {
        $homeSection = HomeSection::where('id', $id)->where('availability', true)->first();

        if (!$homeSection) {
            MessageService::abort(404, 'messages.home_section.not_found');
        }

        return $homeSection;
    }

    public function reorder($homeSection, $validatedData)
    {
        OrderHelper::reorder($homeSection, $validatedData['orders']);

        return $homeSection;
    }


    public function create($data)
    {

        $data['show_title'] = true;
        $data['status'] = 'static';
        $data['can_show_more'] = true;
        if ($data['type'] === 'category_products') {
            $data['show_more_path'] = '/products?category_id=' . $data['item_id'];
        } elseif ($data['type'] === 'brand_products') {
            $data['show_more_path'] = '/products?brand_id=' . $data['item_id'];
        }

        $homeSection = HomeSection::create($data);

        OrderHelper::assign($homeSection);


        return $homeSection;
    }
}
