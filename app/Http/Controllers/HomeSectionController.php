<?php

namespace App\Http\Controllers;

use App\Http\Requests\HomeSection\ReOrderHomeSectionRequest;
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

    public function show($id)
    {
        $homeSection = $this->homeSectionService->show($id);

        return ResponseService::response(
            [
                'success' => true,
                'data' => $homeSection,
                'status' => 200,
            ],
        );
    }

    public function reorder($id, ReOrderHomeSectionRequest $request)
    {
        $homeSection = $this->homeSectionService->show($id);

        $homeSection = $this->homeSectionService->reorder($homeSection, $request->validated());


        return $this->index();
    }
}
