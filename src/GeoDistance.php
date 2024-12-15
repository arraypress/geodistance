<?php
/**
 * Class GeoDistance
 *
 * A simple utility class for calculating distances between two geographical points
 * using the Haversine formula. Supports calculations in miles and kilometers.
 * Includes WordPress integration for error handling when in a WordPress environment.
 *
 * Example usage:
 * ```php
 * $calculator = new GeoDistance(
 * ['latitude' => 40.7128, 'longitude' => -74.0060],     // Point A
 * ['latitude' => 51.5074, 'longitude' => -0.1278],      // Point B
 * 'km'                                                   // Optional: unit (default: 'mi')
 * );
 * $distance = $calculator->get_distance();
 * ```
 *
 * @package     ArrayPress/Utils
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Math;

use InvalidArgumentException;

class GeoDistance {

	/**
	 * First point coordinates
	 *
	 * @var array
	 */
	private array $pointA;

	/**
	 * Second point coordinates
	 *
	 * @var array
	 */
	private array $pointB;

	/**
	 * Unit of measurement
	 *
	 * @var string
	 */
	private string $unit;

	/**
	 * Store the last error
	 *
	 * @var \WP_Error|InvalidArgumentException|null
	 */
	private $last_error = null;

	/**
	 * Valid units and their Earth radius values
	 *
	 * @var array
	 */
	private const EARTH_RADIUS = [
		'mi' => 3959,
		'km' => 6371
	];

	/**
	 * GeoDistance constructor.
	 *
	 * @param array  $pointA Array with 'latitude' and 'longitude' keys
	 * @param array  $pointB Array with 'latitude' and 'longitude' keys
	 * @param string $unit   Unit of measurement ('mi' or 'km')
	 *
	 * @throws InvalidArgumentException If coordinates are invalid in non-WordPress environment
	 */
	public function __construct( array $pointA, array $pointB, string $unit = 'mi' ) {
		$validation = $this->validate_coordinates( $pointA, 'Point A' );
		if ( $validation !== true ) {
			return $validation;
		}

		$validation = $this->validate_coordinates( $pointB, 'Point B' );
		if ( $validation !== true ) {
			return $validation;
		}

		$validation = $this->validate_unit( $unit );
		if ( $validation !== true ) {
			return $validation;
		}

		$this->pointA = $pointA;
		$this->pointB = $pointB;
		$this->unit   = $unit;
	}

	/**
	 * Set new coordinates for Point A
	 *
	 * @param array $pointA Array with 'latitude' and 'longitude' keys
	 *
	 * @return bool|\WP_Error Returns true on success, WP_Error on failure in WordPress
	 * @throws InvalidArgumentException If coordinates are invalid in non-WordPress environment
	 */
	public function set_point_a( array $pointA ) {
		$validation = $this->validate_coordinates( $pointA, 'Point A' );
		if ( $validation !== true ) {
			return $validation;
		}

		$this->pointA = $pointA;

		return true;
	}

	/**
	 * Set new coordinates for Point B
	 *
	 * @param array $pointB Array with 'latitude' and 'longitude' keys
	 *
	 * @return bool|\WP_Error Returns true on success, WP_Error on failure in WordPress
	 * @throws InvalidArgumentException If coordinates are invalid in non-WordPress environment
	 */
	public function set_point_b( array $pointB ) {
		$validation = $this->validate_coordinates( $pointB, 'Point B' );
		if ( $validation !== true ) {
			return $validation;
		}

		$this->pointB = $pointB;

		return true;
	}

	/**
	 * Get coordinates of Point A
	 *
	 * @return array Array with 'latitude' and 'longitude' keys
	 */
	public function get_point_a(): array {
		return $this->pointA;
	}

	/**
	 * Get coordinates of Point B
	 *
	 * @return array Array with 'latitude' and 'longitude' keys
	 */
	public function get_point_b(): array {
		return $this->pointB;
	}

	/**
	 * Get the current unit of measurement
	 *
	 * @return string Current unit ('mi' or 'km')
	 */
	public function get_unit(): string {
		return $this->unit;
	}

	/**
	 * Set the unit of measurement
	 *
	 * @param string $unit Unit of measurement ('mi' or 'km')
	 *
	 * @return bool|\WP_Error Returns true on success, WP_Error on failure in WordPress
	 * @throws InvalidArgumentException If unit is invalid in non-WordPress environment
	 */
	public function set_unit( string $unit ) {
		$validation = $this->validate_unit( $unit );
		if ( $validation !== true ) {
			return $validation;
		}

		$this->unit = $unit;

		return true;
	}

	/**
	 * Calculate the distance between the two points
	 *
	 * @return float|\WP_Error The distance in the specified unit, rounded to 2 decimal places,
	 *                         or WP_Error in WordPress environment if there was an error
	 * @throws InvalidArgumentException In non-WordPress environment if there was an error
	 */
	public function get_distance() {
		try {
			// Convert coordinates to radians
			$lat1 = deg2rad( $this->pointA['latitude'] );
			$lon1 = deg2rad( $this->pointA['longitude'] );
			$lat2 = deg2rad( $this->pointB['latitude'] );
			$lon2 = deg2rad( $this->pointB['longitude'] );

			// Calculate differences
			$latDiff = $lat2 - $lat1;
			$lonDiff = $lon2 - $lon1;

			// Haversine formula
			$a = sin( $latDiff / 2 ) * sin( $latDiff / 2 ) +
			     cos( $lat1 ) * cos( $lat2 ) *
			     sin( $lonDiff / 2 ) * sin( $lonDiff / 2 );

			$c = 2 * asin( sqrt( $a ) );

			return round( self::EARTH_RADIUS[ $this->unit ] * $c, 2 );
		} catch ( \Exception $e ) {
			return $this->handle_error( $e->getMessage(), 'calculation_error' );
		}
	}

	/**
	 * Get the last error if any
	 *
	 * @return \WP_Error|InvalidArgumentException|null
	 */
	public function get_last_error() {
		return $this->last_error;
	}

	/**
	 * Check if we're in a WordPress environment
	 *
	 * @return bool
	 */
	private function is_wordpress(): bool {
		return function_exists( 'wp_error' ) && class_exists( 'WP_Error' );
	}

	/**
	 * Handle errors based on environment
	 *
	 * @param string $message Error message
	 * @param string $code    Error code
	 *
	 * @return \WP_Error|void Returns WP_Error in WordPress environment
	 * @throws InvalidArgumentException In non-WordPress environment
	 */
	private function handle_error( string $message, string $code = 'invalid_argument' ) {
		$this->last_error = $this->is_wordpress()
			? new \WP_Error( $code, $message )
			: new InvalidArgumentException( $message );

		if ( ! $this->is_wordpress() ) {
			throw $this->last_error;
		}

		return $this->last_error;
	}

	/**
	 * Validate coordinate array format and values
	 *
	 * @param array  $point     Coordinate array to validate
	 * @param string $pointName Name of the point for error messages
	 *
	 * @return bool|\WP_Error Returns true if valid, WP_Error in WordPress if invalid
	 * @throws InvalidArgumentException In non-WordPress environment if invalid
	 */
	private function validate_coordinates( array $point, string $pointName ) {
		if ( ! isset( $point['latitude'] ) || ! isset( $point['longitude'] ) ) {
			return $this->handle_error(
				"$pointName must contain 'latitude' and 'longitude' keys",
				'invalid_coordinates'
			);
		}

		$lat = $point['latitude'];
		$lon = $point['longitude'];

		if ( ! is_numeric( $lat ) || $lat < - 90 || $lat > 90 ) {
			return $this->handle_error(
				"$pointName latitude must be between -90 and 90 degrees",
				'invalid_latitude'
			);
		}

		if ( ! is_numeric( $lon ) || $lon < - 180 || $lon > 180 ) {
			return $this->handle_error(
				"$pointName longitude must be between -180 and 180 degrees",
				'invalid_longitude'
			);
		}

		return true;
	}

	/**
	 * Validate unit of measurement
	 *
	 * @param string $unit Unit to validate
	 *
	 * @return bool|\WP_Error Returns true if valid, WP_Error in WordPress if invalid
	 * @throws InvalidArgumentException In non-WordPress environment if invalid
	 */
	private function validate_unit( string $unit ) {
		if ( ! array_key_exists( $unit, self::EARTH_RADIUS ) ) {
			return $this->handle_error(
				sprintf( 'Invalid unit. Supported units are: %s', implode( ', ', array_keys( self::EARTH_RADIUS ) ) ),
				'invalid_unit'
			);
		}

		return true;
	}
}