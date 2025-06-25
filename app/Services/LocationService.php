<?php


namespace App\Services;

use App\Models\City;
use Illuminate\Pagination\LengthAwarePaginator;


class LocationService
{

    public static function getLocationData($latitude, $longitude)
    {
        // $apiKey = env('GOOGLE_MAPS_API_KEY');
        $apiKey = 'AIzaSyCkMlal5E0x_tV7q0AtwP8hLA_XJQBwSfo';

        $language = request()->header('Accept-Language') ?? 'en';
        $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$latitude},{$longitude}&key={$apiKey}&language={$language}";

        $response = file_get_contents($url);
        $responseData = json_decode($response, true);

        if ($responseData['status'] == 'OK') {
            $googleMapData = $responseData['results'][0];
            // $cityPlaceId = null;

            // foreach ($responseData['results'] as $result) {
            //     if (in_array('administrative_area_level_1', $result['types']) && in_array('political', $result['types'])) {
            //         $cityPlaceId = $result['place_id'];
            //         break;
            //     }
            // }



            // if (!$cityPlaceId) {
            //     MessageService::abort(400, 'messages.invalid_location');
            // }


            // $city = City::where('place_id', $cityPlaceId)->first();


            return [
                'address' => $googleMapData['formatted_address'] ?? '',
                'city' => $googleMapData['address_components']['locality'] ?? '',
                // 'city' =>  ,
                'country' => $googleMapData['address_components']['country'] ?? '',
                'postal_code' => $googleMapData['address_components']['postal_code'] ?? '',
                'address_secondary' => $googleMapData['address_components']['sublocality'] ?? '',
                'state' => $googleMapData['address_components']['administrative_area_level_1'] ?? '',
                'latitude' => $latitude,
                'longitude' => $longitude,
              ];
        }

        return null;
    }
}
