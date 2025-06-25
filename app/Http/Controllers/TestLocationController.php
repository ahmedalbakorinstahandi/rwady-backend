<?php

namespace App\Http\Controllers;

use App\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TestLocationController extends Controller
{
    /**
     * Test location service with Baghdad coordinates
     */
    public function testLocation(): JsonResponse
    {
        $latitude = 33.310122;
        $longitude = 44.368598;

        $result = LocationService::getLocationData($latitude, $longitude);

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Location data retrieved successfully',
                'data' => $result
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve location data'
        ], 400);
    }

    /**
     * Test location service with custom coordinates
     */
    public function testCustomLocation(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180'
        ]);

        $latitude = $request->latitude;
        $longitude = $request->longitude;

        $result = LocationService::getLocationData($latitude, $longitude);

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Location data retrieved successfully',
                'data' => $result
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve location data for the provided coordinates'
        ], 400);
    }

    /**
     * Test API connection
     */
    public function testApiConnection(): JsonResponse
    {
        $result = LocationService::testApiConnection();

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json($result, 400);
    }
} 