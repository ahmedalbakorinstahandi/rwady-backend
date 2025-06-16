<?php

namespace App\Http\Controllers;

use App\Http\Services\HomeSectionService;
use App\Services\ResponseService;
use Illuminate\Http\Request;

class HomeSectionController extends Controller
{
    protected $homeSectionService;

    public function __construct(HomeSectionService $homeSectionService)
    {
        $this->homeSectionService = $homeSectionService;
    }

    public function index()
    {
        $homeSections = $this->homeSectionService->getHomeSections();

        return ResponseService::response(
            [
                'success' => true,
                'data' => $homeSections,
                'status' => 200,
            ],
        );
    }
}
