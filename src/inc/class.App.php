<?php

namespace Temperature_Monitor\Server;

class TemperatureMonitorApp {

	/**
	 * @var array The configuration parameters.
	 */
	public $config;

	/**
	 * @var object The database object.
	 */
	public $database;

	/**
	 * @var array The response data.
	 */
	public $response;

	/**
	 * App constructor.
	 *
	 * Sets up error handling and bootstraps the application.
	 */
	public function __construct() {

		try {

			$this->setup();
			$this->run();

		} catch ( \Exception $e ) {

			$this->handle_exception( $e );
		}
	}

	/**
	 * Reads the config file and sets up the initial state of the application.
	 */
	public function setup() {

		// Set the default response to failure. We'll set it to whatever is appropriate later on if the app completes
		// successfully.
		$this->response = array(
			'code' => 400,
			'body' => 'Bad request',
		);

		$this->config = $this->get_config();

		// Set the timezone.
		date_default_timezone_set( $this->config['timezone'] );

		$this->database = new \Temperature_Monitor\Server\TemperatureMonitorDatabase(
			$this->config['db_host'],
			$this->config['db_user'],
			$this->config['db_password'],
			$this->config['db_name']
		);
	}

	/**
	 * Reads the configuration file and makes sure it is valid.
	 *
	 * @return array The configuration parameters.
	 *
	 * @throws \Exception if the configuration is invalid.
	 */
	public function get_config() {

		$config_file = false;

		// Allow the configuration file to be located in the parent directory of the webroot for security reasons.
		if ( file_exists( dirname( APP_DIR ) . '/config.php' ) ) {
			$config_file = dirname( APP_DIR ) . '/config.php';
		}
		elseif ( file_exists( APP_DIR . '/config.php' ) ) {
			$config_file = APP_DIR . '/config.php';
		}

		if ( false === $config_file ) {
			throw new \Exception( 'Config file not present' );
		}

		$config = require_once( $config_file );

		if ( empty( $config['timezone'] )
		  OR empty( $config['db_host'] )
		  OR empty( $config['db_user'] )
		  OR empty( $config['db_password'] )
	      OR empty( $config['db_name'] )
		) {
			throw new \Exception( 'Invalid database config' );
		}

		$this->config = $config;
		return $config;
	}

	/**
	 * Runs the app.
	 *
	 * @throws \Exception if the request method is invalid, or an empty response is returned.
	 */
	public function run() {

		switch( $_SERVER['REQUEST_METHOD'] ) {
			case 'POST':
				$request = new \Temperature_Monitor\Server\TemperatureMonitorPostRequest( $this->database );
				break;
			case 'GET':
				$request = new \Temperature_Monitor\Server\TemperatureMonitorGetRequest( $this->database );
				break;
			default:
				throw new \Exception;
		}

		// An exception should be thrown which will abort the app if an error occurs.
		$response = (array) $request->process();

		// An empty body should never be returned by a handler for a successful request.
		if ( empty( $response['body'] ) ) {
			throw new \Exception;
		}
		$this->response['body'] = $response['body'];

		// The handler can optionally return an HTTP status code.
		if ( empty( $response['code'] ) ) {
			$this->response['code'] = 200;
		} else {
			$this->response['code'] = $response['code'];
		}

		$this->send_response();
	}

	/**
	 * Sets the appropriate HTTP status headers, and returns a JSON-formatted response body.
	 */
	public function send_response() {

		$response = $this->response;

		if ( $response['code'] >= 200 && $response['code'] <= 299 ) {
			$response['type'] = 'success';
		} else {
			$response['type'] = 'failure';
		}

		$json_response = array(
			'type' => $response['type'],
			'data' => $response['body'],
		);

		http_response_code( $response['code'] );

		header( 'Content-type: application/json' );
		echo json_encode( $json_response );
		die();
	}

	/**
	 * Handles any exceptions that are thrown.
	 *
	 * @param object $exception The thrown exception.
	 */
	public function handle_exception( $exception ) {

		$code        = $exception->getCode();
		$description = $exception->getMessage();

		// Set the app's response code to the exception's code if one was supplied to the exception. Otherwise the
		// response code will be the app's default of 400.
		if ( 0 !== $code ) {
			$this->response['code'] = $code;
		}

		// If a description was supplied to the exception, format and save it.
		if ( ! empty( $description ) ) {

			// Try to json_decode the response in case the description is a valid JSON string. Otherwise, use the
			// response text as-is.
			$json_decoded = json_decode( $description );
			if ( null !== $json_decoded ) {
				$description = $json_decoded;
			}

			$this->response['body'] = $description;
		}

		$this->send_response();
	}
}
