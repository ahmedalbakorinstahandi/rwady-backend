<?php

namespace App\Http\Services;

use App\Models\HomeSection;

class HomeSectionService
{
    public function getHomeSections() 
    {
        $homeSections = HomeSection::all();


        return $homeSections;
    }
}
