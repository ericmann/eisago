<?php
namespace EAMann\Eisago;

use MongoDB\Client;

/**
 * Generic importer from which all others inherit and extend functionality.
 *
 * Basic workflow will follow this pattern:
 * - Read directory listing of /data to determine file names
 * - Read each file and parse them line-by-line to create verses
 * - Insert each verse into Mongo
 *
 * Each Importer will display progress differently. See README.md for details
 */
abstract class BaseImporter {

	/**
	 * @var string
	 */
	protected $database;

	/**
	 * @var OutputWriter
	 */
	protected $output;

	/**
	 * @var bool
	 */
	protected $verbose;

	/**
	 * @var string
	 */
	protected $path;

	/**
	 * Base constructor.
	 *
	 * @param OutputWriter $output
	 * @param bool         $verbose
	 */
	public function __construct( OutputWriter $output, bool $verbose ) {
		$this->output = $output;
		$this->verbose = $verbose;
	}

	/**
	 * Get a list of files in the specified import directory through which we must iterate.
	 *
	 * @return array
	 */
	protected function getFileList() {
		$files = scandir( $this->path );

		if ( false === $files ) {
			return [];
		}

		return array_map( function ( $file ) { return $this->path . '/' . $file; }, array_diff( $files, ['..', '.'] ) );
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
	 * Actually invoke the import mechanism
	 *
	 * @param string $path Path from which to import data
	 *
	 * @return mixed
	 */
	abstract function run( string $path );
}