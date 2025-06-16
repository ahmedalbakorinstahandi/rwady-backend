<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeaturedSection\CreateFeaturedSectionRequest;
use App\Http\Requests\FeaturedSection\UpdateFeaturedSectionRequest;
use App\Http\Resources\FeaturedSectionResource;
use App\Http\Services\FeaturedSectionService;
use App\Services\ResponseService;
use Illuminate\Http\Request;

class FeaturedSectionController extends Controller
{
    protected $featuredSectionService;

    public function __construct(FeaturedSectionService $featuredSectionService)
    {
        $this->featuredSectionService = $featuredSectionService;
    }

    public function index(Request $request)
    {
        $sections = $this->featuredSectionService->index($request->all());

        return ResponseService::response([
            'success' => true,
            'data' => $sections,
            'meta' => true,
            'resource' => FeaturedSectionResource::class,
            'status' => 200,
        ]);
    }

    public function show(int $id)
    {
        $section = $this->featuredSectionService->show($id);

        return ResponseService::response([
            'success' => true,
            'data' => $section,
            'resource' => FeaturedSectionResource::class,
            'status' => 200,
        ]);
    }

    public function create(CreateFeaturedSectionRequest $request)
    {
        $section = $this->featuredSectionService->create($request->validated());

        return ResponseService::response([
            'success' => true,
            'data' => $section,
            'message' => 'messages.featured_section.created_successfully',
            'status' => 201,
            'resource' => FeaturedSectionResource::class,
        ]);
    }

    public function update(UpdateFeaturedSectionRequest $request, int $id)
    {
        $section = $this->featuredSectionService->show($id);
        $section = $this->featuredSectionService->update($request->validated(), $section);

        return ResponseService::response([
            'success' => true,
            'data' => $section,
            'message' => 'messages.featured_section.updated_successfully',
            'status' => 200,
            'resource' => FeaturedSectionResource::class,
        ]);
    }

    public function delete(int $id)
    {
        $section = $this->featuredSectionService->show($id);
        $this->featuredSectionService->delete($section);

        return ResponseService::response([
            'success' => true,
            'message' => 'messages.featured_section.deleted_successfully',
            'status' => 200,
        ]);
    }
} 