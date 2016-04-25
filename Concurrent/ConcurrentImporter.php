<?php
namespace EAMann\Eisago;

use Icicle\Awaitable\Promise;
use Icicle\Loop;
use MongoDB\Client;

class ConcurrentImporter extends BaseImporter {

	/**
	 * @var string
	 */
	protected $database = 'concurrent';
	
	/**
	 * Actually invoke the import mechanism
	 *
	 * @param string $path Path from which to import data
	 *
	 * @return mixed
	 */
	public function run( string $path ) {
		return new Promise( function( callable $resolve, callable $reject ) use ( $path ) {
			$this->path = $path;

			// Set up our database connection
			$client = new Client( 'mongodb://mongo:27017' );

			// Ensure we're working with a clean slate
			$client->dropDatabase( $this->database );

			// Run our import one file at a time
			$promises = array_map( array( $this, 'importFile' ), $this->getFileList() );

			\Icicle\Awaitable\all( $promises )->then( function() use ( $resolve ) {
				$this->output->printTable( true );
				$resolve();
			} );

			Loop\run();
		} );
	}

	/**
	 * Read a file one line at a time, create a Verse from each line, and store each Verse in the database.
	 *
	 * @param string $file
	 *
	 * @return Promise
	 */
	protected function importFile( string $file ) {
		return new Promise( function( callable $resolve, callable $reject ) use ( $file ) {
			// Simulate network latency as if we were making a remote request
			Loop\timer( 0.1 * mt_rand( 0, 20 ), function() use ( $file, $resolve ) {
				$book = substr( explode( '/', $file )[1], 0, -4 );

				// First, count all of the lines
				$length = Counter::countFrom( $file );

				Reader::readInto( $file, array( $this, 'importLine' ) );

				$this->output->addBook( $book, $length, $length );
				$this->output->printTable( true );

				$resolve();
			} );

			if ( ! Loop\isRunning() ) {
				Loop\run();
			}
		} );
	}
}