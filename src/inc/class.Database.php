<?php

namespace Temperature_Monitor\Server;

class TemperatureMonitorDatabase {

	/**
	 * @var string The database host.
	 */
	public $host;

	/**
	 * @var string The database username.
	 */
	public $user;

	/**
	 * @var string The database password.
	 */
	public $password;

	/**
	 * @var string The database name.
	 */
	public $database;

	/**
	 * @var string The table name.
	 */
	public $table;

	/**
	 * @var object The database connection object.
	 */
	public $connection;

	/**
	 * Database constructor.
	 *
	 * Sets up the initial properties and initializes the connection.
	 *
	 * @param string $host     The database host.
	 * @param string $user     The database username.
	 * @param string $password The database password.
	 * @param string $database The database name.
	 *
	 * @throws \Exception if the database config file does not exist.
	 */
	public function __construct( $host, $user, $password, $database ) {

		$this->host     = $host;
		$this->user     = $user;
		$this->password = $password;
		$this->database = $database;

		$this->table = 'readings';

		$this->connect();
		$this->create_table_if_not_exists();

		return $this->connection;
	}

	/**
	 * Connects to the database.
	 *
	 * @throws \Exception on a connection error.
	 */
	public function connect() {

		@$connection = new \mysqli(
			$this->host,
			$this->user,
			$this->password,
			$this->database
		);

		if ( $connection->connect_errno ) {
			throw new \Exception( $connection->connect_error );
		}

		$this->connection = $connection;
	}

	/**
	 * Creates the database table if it does not exist.
	 */
	public function create_table_if_not_exists() {

		$sql = sprintf(
			'CREATE TABLE IF NOT EXISTS %s (
				id          INT      UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				datetime    DATETIME ,
				temperature FLOAT    (6,3)
			 )',
			$this->table
		);

		$this->query( $sql );
	}

	/**
	 * Runs a query on the database.
	 *
	 * @param string $query The query.
	 *
	 * @return object The mysql result object.
	 *
	 * @throws \Exception if there is an error executing the query.
	 */
	public function query( $query = '' ) {

		$db_response = $this->connection->query( $query );

		if ( false === $db_response ) {
			$response_body = array(
				'error' => $this->connection->error,
				'query' => $query,
			);
			throw new \Exception( json_encode( $response_body ), 500 );
		}

		return $db_response;
	}

	/**
	 * Retrieve rows from the database.
	 *
	 * @param string $where The where clause.
	 *
	 * @return object The MySQL response object.
	 */
	public function select( $where = '' ) {

		if ( ! empty( $where ) ) {
			$where = "WHERE {$where}";
		}

		$sql = sprintf( 'SELECT * FROM %s %s',
			$this->table,
			$where
		);

		$result = $this->query( $sql );

		$rows = array();
		while( $row = $result->fetch_assoc() ) {
			$rows[] = $row;
		}

		return $rows;
	}

	/**
	 * Inserts a record into the table.
	 *
	 * @param array $data The data to be inserted.
	 */
	public function insert( $data = array() ) {

		$keys   = array_keys( $data );
		$values = array_values( $data );

		// Escape all the values.
		$values = array_map(
			array( $this->connection, 'real_escape_string' ),
			$values
		);

		$keys_string   = join( ', ',   $keys );
		$values_string = join( "', '", $values );

		$sql = sprintf( "INSERT INTO %s ( %s ) VALUES ( '%s' )",
			$this->table,
			$keys_string,
			$values_string
		);

		$this->query( $sql );
	}

	/**
	 * Converts a UNIX timestamp to MySQL datetime format.
	 *
	 * @param int $timestamp The UNIX timestamp.
	 *
	 * @return string The timestamp in MySQL datetime format.
	 */
	public function timestamp2datetime( $timestamp = 0 ) {
		$datetime = date( 'Y-m-d H:i:s', $timestamp );
		return $datetime;
	}
}
