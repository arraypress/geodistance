# ArrayPress GeoDistance

A powerful PHP utility class for calculating geographical distances between coordinates using the Haversine formula. This library provides a clean, intuitive interface for accurate distance calculations in both miles and kilometers, with special integration for WordPress environments.

## Features

- 🌍 **Multi-Unit Support**: Calculate distances in miles or kilometers
- 🎯 **Precision**: Results rounded to 2 decimal places for practical use
- 🛡️ **Input Validation**: Comprehensive coordinate and unit validation
- 📐 **Haversine Formula**: Accurate great-circle distance calculations
- 🔒 **Type Safety**: Full type hinting and return type declarations
- ⚡ **Simple Interface**: Easy to understand and implement
- 🔄 **WordPress Integration**: Native WP_Error support when in WordPress environment
- 🚫 **Error Handling**: Flexible error handling for both WordPress and standalone use

## Requirements

- PHP 7.4 or later
- WordPress

## Installation

Install via Composer:

```bash
composer require arraypress/geodistance
```

## Basic Usage

```php
use ArrayPress\Utils\Math\GeoDistance;

// New York coordinates
$pointA = [ 'latitude' => 40.7128, 'longitude' => -74.0060 ];

// London coordinates
$pointB = [ 'latitude' => 51.5074, 'longitude' => -0.1278 ];

// Initialize calculator
$calculator = new GeoDistance( $pointA, $pointB );

$distance = $calculator->get_distance();
if ( is_wp_error( $distance ) ) {
    echo $distance->get_error_message();
} else {
    echo "Distance: $distance {$calculator->get_unit()}";
}
```

## Point Management

### Getting Points

```php
// Get current coordinates
$pointA = $calculator->get_point_a();
$pointB = $calculator->get_point_b();
```

### Setting Points

```php
// WordPress Environment
$result = $calculator->set_point_a([
    'latitude'  => 35.6762,
    'longitude' => 139.6503
]);

if ( is_wp_error( $result ) ) {
    echo $result->get_error_message();
}
```

### Checking if a Point is Within Radius

```php
// Central Park, New York
$centralPoint = [ 'latitude' => 40.7829, 'longitude' => -73.9654 ];

// Times Square coordinates
$targetPoint = [ 'latitude' => 40.7580, 'longitude' => -73.9855 ];

// Initialize calculator with central point
$calculator = new GeoDistance( $centralPoint, $targetPoint );

// WordPress Environment
$radius = 2; // 2 miles radius
$isWithin = $calculator->is_within_radius( $targetPoint, $radius );
if ( is_wp_error( $isWithin ) ) {
    echo $isWithin->get_error_message();
} else {
    echo $isWithin ? "Location is within {$radius} {$calculator->get_unit()} radius" : "Location is outside radius";
}
```

## Unit Management

### Getting and Setting Units

```php
// Get current unit
$currentUnit = $calculator->get_unit();

// WordPress Environment
$result = $calculator->set_unit( 'km' );
if ( is_wp_error( $result ) ) {
    echo $result->get_error_message();
}
```

## Error Handling

### WordPress Environment

```php
// Check for errors using WP_Error
$calculator = new GeoDistance( $pointA, $pointB );

$result = $calculator->set_unit('invalid_unit');
if ( is_wp_error( $result ) ) {
    echo $result->get_error_message();
    echo $result->get_error_code();
}

// Get the last error
$lastError = $calculator->get_last_error();
if ( $lastError instanceof WP_Error ) {
    echo $lastError->get_error_message();
}
```

## Error Codes

The library uses the following error codes when in a WordPress environment:

- `invalid_coordinates`: Missing latitude or longitude keys
- `invalid_latitude`: Latitude value out of range (-90 to 90)
- `invalid_longitude`: Longitude value out of range (-180 to 180)
- `invalid_unit`: Unsupported unit of measurement
- `calculation_error`: Error during distance calculation

## Use Cases

- Distance Calculation: Calculate distances between geographical points
- Location-Based Services: Determine proximity between locations
- Travel Applications: Calculate travel distances
- Geofencing: Determine if points are within specific distances
- Delivery Services: Calculate shipping distances and zones
- WordPress Integration: Seamless integration with WordPress applications

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the GPL-2.0-or-later License.

## Support

- [Documentation](https://github.com/arraypress/geodistance)
- [Issue Tracker](https://github.com/arraypress/geodistance/issues)