<?php
namespace EAMann\Eisago;

class DataConnection {
	/**
	 * @var \MongoClient
	 */
	protected $client;

	/**
	 * @var \MongoDB
	 */
	protected $database;

	/**
	 * Open a connection to our data source as needed.
	 *
	 * @param String $database Name of the database with which we'll interact
	 */
	protected function __construct( String $database) {
		$this->client = new \MongoClient();
		$this->database = $this->client->selectDB( $database );
	}

	/**
	 * Insert a verse into the database itself.
	 *
	 * @param String $database
	 * @param Verse  $verse
	 */
	public static function insert( String $database, Verse $verse ) {
		$connection = new self( $database );
		$book = $connection->database->selectCollection( $verse->book );
		$book->insert( $verse );
	}

	/**
	 * Clean up our resources
	 */
	public function finalize() {
		$this->client->close();
		$this->database = null;
		$this->client = null;
	}
}