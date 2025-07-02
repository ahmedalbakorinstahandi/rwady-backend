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
use App\Models\User;

class HomeSectionController extends Controller
{
    protected $homeSectionService;

    public function __construct(HomeSectionService $homeSectionService)
    {
        $this->homeSectionService = $homeSectionService;
    }

    public function index()
    {
        // Cache user auth to avoid repeated queries
        $user = cache()->remember('current_user', 300, function () {
            return User::auth();
        });
        
        $cacheKey = "home_sections_response_" . ($user ? $user->id : 'guest');
        
        return cache()->remember($cacheKey, 300, function () {
            $homeSections = $this->homeSectionService->getHomeSections();

            return ResponseService::response(
                [
                    'success' => true,
                    'data' => $homeSections,
                    'status' => 200,
                    'resource' => HomeSectionResource::class,
                ],
            );
        });
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
        
        // Clear cache after update
        $this->clearHomeSectionCache();
        
        return $this->index();
    }

    public function delete($id)
    {
        $homeSection = $this->homeSectionService->show($id);

        if ($homeSection->status === 'static') {
            MessageService::abort(400, 'messages.home_section.cannot_delete_static');
        }

        $this->homeSectionService->delete($homeSection);
        
        // Clear cache after delete
        $this->clearHomeSectionCache();
        
        return $this->index();
    }

    public function reorder($id, ReOrderHomeSectionRequest $request)
    {
        $homeSection = $this->homeSectionService->show($id);
        $homeSection = $this->homeSectionService->reorder($homeSection, $request->validated());

        // Clear cache after reorder
        $this->clearHomeSectionCache();

        return $this->index();
    }

    private function clearHomeSectionCache()
    {
        // Clear all home section related cache
        cache()->flush();
        
        // Clear user auth cache
        User::clearAuthCache();
    }
}
