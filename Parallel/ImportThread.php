<?php
namespace EAMann\Eisago;

use MongoDB\Client;

class ImportThread extends \Thread {

	/**
	 * @var string
	 */
	protected $file;

	/**
	 * @var string
	 */
	protected $database;

	/**
	 * @var string
	 */
	public $book;

	/**
	 * @var int
	 */
	public $total;
	
	public function __construct( string $file, string $database ) {
		$this->file = $file;
		$this->database = $database;
	}

	/**
	 * Execute the thread once it's started up
	 */
	public function run() {
		// In a new context, it's necessary to re-include Composer dependencies
		require_once( __DIR__ . '/../vendor/autoload.php' );

		// Get the name of the book from the filename
		$this->book = substr( explode( '/', $this->file )[1], 0, -4 );

		// First, count all of the lines
		$this->total = $this->countLines();

		// Read a line (verse) from the file into our database
		$this->readLine( [ $this, 'importLine' ] );
	}

	/**
	 * Override the default Thread startup so we can force autoloading to work right.
	 *
	 * @param int|null $options
	 *
	 * @return bool
	 */
	public function start( int $options = null ) {
		return parent::start( PTHREADS_INHERIT_NONE );
	}

	/**
	 * Read a single line, parse it as a verse, and store it in the database.
	 *
	 * @param string $line
	 */
	public function importLine( string $line ) {
		// Create our verse
		$verse = new Verse( $line );

		// Save our verse with a new Mongo connection
		$client = new Client( 'mongodb://mongo:27017' );
		$book = $client->selectCollection( $this->database, $verse->book );
		$book->insertOne( $verse );
	}

	/**
	 * Count the number of lines in the file we're importing
	 *
	 * @return int
	 */
	protected function countLines() {
		$handle = fopen( $this->file, 'r' );
		$length = 0;

		while ( ! feof( $handle ) ) {
			fgets( $handle );
			$length++;
		}

		fclose( $handle );

		return $length;
	}

	/**
	 * Pass each read line through a processing mechanism
	 *
	 * @param callable $processor
	 *
	 * @return bool
	 */
	protected function readLine( callable $processor ) {
		$handle = fopen( $this->file, 'r' );

		try {
			while ( ! feof( $handle ) ) {
				$processor( fgets( $handle ) );
			}

			fclose( $handle );

			return true;
		} catch ( IOException $e ) {
			// Something was wrong with the file, so return false
			return false;
		}
	}
}