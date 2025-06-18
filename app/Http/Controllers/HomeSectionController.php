<?php

namespace App\Http\Controllers;

use App\Http\Requests\HomeSection\CreateHomeSectionRequest;
use App\Http\Requests\HomeSection\ReOrderHomeSectionRequest;
use App\Http\Requests\HomeSection\UpdateHomeSectionRequest;
use App\Http\Resources\HomeSectionResource;
use App\Http\Services\HomeSectionService;
use App\Services\MessageService;
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
                'resource' => HomeSectionResource::class,
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
                'resource' => HomeSectionResource::class,
            ],
        );
    }

    public function create(CreateHomeSectionRequest $request) 
    {
        $homeSection = $this->homeSectionService->create($request->validated());

        return ResponseService::response(
            [
                'success' => true,
                'data' => $homeSection,
                'status' => 200,
                'resource' => HomeSectionResource::class,
            ],
        );
    }

    public function update(UpdateHomeSectionRequest $request, $id)
    {
        $homeSection = $this->homeSectionService->show($id);
        $homeSection = $this->homeSectionService->update($homeSection, $request->validated());
        return $this->index();
    }

    public function delete($id)
    {
        $homeSection = $this->homeSectionService->show($id);

        if ($homeSection->status === 'static') {
            MessageService::abort(400, 'message.home_section.cannot_delete_static');
        }


        $this->homeSectionService->delete($homeSection);
        return $this->index();
    }

    public function reorder($id, ReOrderHomeSectionRequest $request)
    {
        $homeSection = $this->homeSectionService->show($id);

        $homeSection = $this->homeSectionService->reorder($homeSection, $request->validated());


        return $this->index();
    }
}
