<?php

require_once 'vendor/autoload.php';

// Simulate Laravel request
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ar';

// Test coordinates for Baghdad, Iraq
$latitude = 33.310122;
$longitude = 44.368598;

echo "Testing Location Service with Baghdad coordinates:\n";
echo "Latitude: $latitude\n";
echo "Longitude: $longitude\n\n";

// Test the service
$result = \App\Services\LocationService::getLocationData($latitude, $longitude);

if ($result) {
    echo "Location Data Retrieved Successfully:\n";
    echo "Address: " . $result['address'] . "\n";
    echo "City: " . $result['city'] . "\n";
    echo "Country: " . $result['country'] . "\n";
    echo "State: " . $result['state'] . "\n";
    echo "Postal Code: " . $result['postal_code'] . "\n";
    echo "Address Secondary: " . $result['address_secondary'] . "\n";
    echo "Latitude: " . $result['latitude'] . "\n";
    echo "Longitude: " . $result['longitude'] . "\n";
} else {
    echo "Failed to retrieve location data\n";
} 