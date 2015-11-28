<?php

namespace Temperature_Monitor\Server;

class TemperatureMonitorPostRequest {

	/**
	 * @var int The temperature reading of the current request.
	 */
	public $temperature;

	/**
	 * @var int The timestamp of the current request.
	 */
	public $timestamp;

	/**
	 * @var object The database object.
	 */
	public $database;

	/**
	 * Constructor
	 *
	 * Sets up the initial state of the application.
	 *
	 * @param object $database The database connection object.
	 *
	 * @throws \Exception if required parameters are missing.
	 */
	public function __construct( $database ) {

		if ( ! isset( $_REQUEST['temperature'] ) || null === trim( $_REQUEST['temperature'] ) ) {
			throw new \Exception( 'Missing temperature parameter' );
		} else {
			$temperature = $_REQUEST['temperature'];
		}

		if ( ! empty( $_REQUEST['timestamp'] ) ) {
			$timestamp = $_REQUEST['timestamp'];
		} else {
			$timestamp = time();
		}

		$this->temperature = $this->sanitize( 'temperature', $temperature );
		$this->timestamp   = $this->sanitize( 'timestamp',   $timestamp );

		$this->database = $database;
	}

	/**
	 * Processes the saving of the data.
	 *
	 * @return array The response data.
	 */
	public function process() {

		$data = array(
			'datetime'    => $this->database->timestamp2datetime( $this->timestamp ),
			'temperature' => $this->temperature,
		);

		$this->database->insert( $data );

		$response = array(
			'code' => 201, // Created.
			'body' => $this->get_response_body()
		);
		return $response;
	}

	/**
	 * Generates the response body for a successful request.
	 *
	 * @return string The response body.
	 */
	public function get_response_body() {
		return sprintf( 'Reading on %s for a temperature of %s successfully recorded.',
			date( 'r', $this->timestamp ),
			$this->temperature
		);
	}

	/**
	 * Sanitizes user-submitted data.
	 *
	 * @param string $key   The data key.
	 * @param string $value The untrusted value.
	 *
	 * @return mixed The sanitized value.
	 *
	 * @throws \Exception if invalid data was submitted.
	 */
	public function sanitize( $key = '', $value = '' ) {

		switch( $key ) {
			case 'temperature':
				$sanitized_value = floatval( $value );
				break;
			case 'timestamp':
				$sanitized_value = abs( intval( $value ) );
				break;
			default:
				$sanitized_value = null;
		}

		// If the user has submitted invalid data, we don't want to continue.
		if ( "{$value}" !== "{$sanitized_value}" ) {
			throw new \Exception( "Invalid data submitted for {$key}." );
		}

		return $sanitized_value;
	}
}
