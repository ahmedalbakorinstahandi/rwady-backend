<?php

namespace App\Services;

use App\Models\City;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class LocationService
{
    public static function getLocationData($latitude, $longitude)
    {
        try {
            // Validate coordinates
            if (!is_numeric($latitude) || !is_numeric($longitude)) {
                Log::error('Invalid coordinates provided', ['latitude' => $latitude, 'longitude' => $longitude]);
                return null;
            }

            // $apiKey = env('GOOGLE_MAPS_API_KEY');
            $apiKey = 'AIzaSyCkMlal5E0x_tV7q0AtwP8hLA_XJQBwSfo';

            $language = request()->header('Accept-Language') ?? 'en';
            $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$latitude},{$longitude}&key={$apiKey}&language={$language}";

            $response = file_get_contents($url);
            
            if ($response === false) {
                Log::error('Failed to fetch data from Google Maps API');
                return null;
            }

            $responseData = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to decode JSON response from Google Maps API');
                return null;
            }

            if ($responseData['status'] !== 'OK') {
                Log::error('Google Maps API returned error status', ['status' => $responseData['status']]);
                return null;
            }

            if (empty($responseData['results'])) {
                Log::error('No results returned from Google Maps API');
                return null;
            }

            $googleMapData = $responseData['results'][0];
            $addressComponents = $googleMapData['address_components'];

            // Helper function to find component by type
            $findComponent = function($type) use ($addressComponents) {
                foreach ($addressComponents as $component) {
                    if (in_array($type, $component['types'])) {
                        return $component['long_name'];
                    }
                }
                return '';
            };

            $locationData = [
                'address' => $googleMapData['formatted_address'] ?? '',
                'city' => $findComponent('locality') ?: $findComponent('administrative_area_level_2'),
                'country' => $findComponent('country'),
                'postal_code' => $findComponent('postal_code'),
                'address_secondary' => $findComponent('sublocality') ?: $findComponent('neighborhood'),
                'state' => $findComponent('administrative_area_level_1'),
                'latitude' => $latitude,
                'longitude' => $longitude,
            ];

            // Log successful retrieval
            Log::info('Location data retrieved successfully', [
                'coordinates' => "{$latitude},{$longitude}",
                'address' => $locationData['address']
            ]);

            return $locationData;

        } catch (\Exception $e) {
            Log::error('Exception in LocationService::getLocationData', [
                'message' => $e->getMessage(),
                'coordinates' => "{$latitude},{$longitude}"
            ]);
            return null;
        }
    }

    /**
     * Helper method to extract address components from Google Maps response
     */
    private static function extractAddressComponent($addressComponents, $type)
    {
        foreach ($addressComponents as $component) {
            if (in_array($type, $component['types'])) {
                return $component['long_name'];
            }
        }
        return '';
    }

    /**
     * Test method to verify API connection
     */
    public static function testApiConnection()
    {
        $testLat = 33.310122;
        $testLng = 44.368598;
        
        $result = self::getLocationData($testLat, $testLng);
        
        if ($result) {
            return [
                'success' => true,
                'data' => $result
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to retrieve location data'
        ];
    }
}
