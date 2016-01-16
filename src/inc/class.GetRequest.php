<?php

namespace Temperature_Monitor\Server;

class TemperatureMonitorGetRequest {

	/**
	 * @var string The date format of the dates returned.
	 */
	public $pretty_date_format;

	/**
	 * @var object The database object.
	 */
	public $database;

	/**
	 * Constructor
	 */
	public function __construct( $database ) {

		$this->pretty_date_format = 'D, M j, Y g:i a';

		$this->database = $database;
	}

	/**
	 * Processes the request.
	 *
	 * @return array The response data.
	 */
	public function process() {

		$records = $this->get_raw_records();
		$records = $this->format_records( $records );

		$most_recent_record = $records[0];
		$most_recent_record['time_ago'] = $this->time_ago( strtotime( $most_recent_record['datetime'] ) );

		$response = array(
			'body' => array(
				'most_recent_record' => $most_recent_record,
				'all_records'        => $records,
			),
		);
		return $response;
	}

	/**
	 * Gets the raw records from the database.
	 *
	 * @return array The database records.
	 */
	public function get_raw_records() {

		// Get results from the past week.
		$cutoff_timestamp = strtotime( 'one week ago' );

		$where = sprintf( '"datetime" >= "%s" ORDER BY datetime DESC',
			$this->database->timestamp2datetime( $cutoff_timestamp )
		);

		$records = $this->database->select( $where );
		return $records;
	}

	/**
	 * Adds additional human-friendly fields to the records provided.
	 *
	 * @param array $records The records to format.
	 *
	 * @return array The formatted records.
	 */
	public function format_records( $records ) {

		foreach ( $records as $key => $record ) {

			// Format the date.
			$pretty_date = date( $this->pretty_date_format, strtotime( $record['datetime'] ) );
			$records[ $key ]['pretty_date'] = $pretty_date;

			// Get the temperature in Fahrenheit.
			$temperature_fahrenheit = $this->celsius2fahrenheit( $record['temperature'] );
			$records[ $key ]['temperature_fahrenheit'] = round( $temperature_fahrenheit, 1 );
		}

		return $records;
	}

	/**
	 * Converts a temperature in Celsius to Fahrenheit.
	 *
	 * @param float $temperature The temperature in Celsius.
	 *
	 * @return float The temperature in Fahrenheit.
	 */
	public function celsius2fahrenheit( $temperature ) {

		return ( ( $temperature * 9 ) / 5 ) + 32;
	}

	/**
	 * Calculates a human-friendly string describing how long ago a timestamp occurred.
	 *
	 * @link http://philipnewcomer.net/2015/11/human-friendly-time-ago-php-function/
	 *
	 * @param int $timestamp The timestamp to check.
	 * @param int $now       The current time reference point.
	 *
	 * @return string The time ago in a human-friendly format.
	 *
	 * @throws \Exception if the timestamp is in the future.
	 */
	public function time_ago( $timestamp = 0, $now = 0 ) {

		// Set up an array of time intervals.
		$intervals = array(
			60 * 60 * 24 * 365 => 'year',
			60 * 60 * 24 * 30  => 'month',
			60 * 60 * 24 * 7   => 'week',
			60 * 60 * 24       => 'day',
			60 * 60            => 'hour',
			60                 => 'minute',
			1                  => 'second',
		);

		// Get the current time if a reference point has not been provided.
		if ( 0 === $now ) {
			$now = time();
		}

		// Make sure the timestamp to check predates the current time reference point.
		if ( $timestamp > $now ) {
			throw new \Exception( 'Timestamp postdates the current time reference point' );
		}

		// Calculate the time difference between the current time reference point and the timestamp we're comparing.
		$time_difference = (int) abs( $now - $timestamp );

		// Check the time difference against each item in our $intervals array. When we find an applicable interval,
		// calculate the amount of intervals represented by the the time difference and return it in a human-friendly
		// format.
		foreach ( $intervals as $interval => $label ) {

			// If the current interval is larger than our time difference, move on to the next smaller interval.
			if ( $time_difference < $interval ) {
				continue;
			}

			// Our time difference is smaller than the interval. Find the number of times our time difference will fit into
			// the interval.
			$time_difference_in_units = round( $time_difference / $interval );

			if ( $time_difference_in_units <= 1 ) {
				$time_ago = sprintf( 'one %s ago',
					$label
				);
			} else {
				$time_ago = sprintf( '%s %ss ago',
					$time_difference_in_units,
					$label
				);
			}

			return $time_ago;
		}
	}
}
