<?php
namespace EAMann\Eisago;

use Icicle\Awaitable\Promise;
use Icicle\Loop;
use MongoDB\Client;

class ConcurrentImporter extends BaseImporter {

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
			$client = new Client( 'mongodb://192.168.99.100:27017' );

			// Ensure we're working with a clean slate
			$client->dropDatabase( 'concurrent' );

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
			Loop\timer( 0.1 * mt_rand( 0, 10 ), function() use ( $file, $resolve ) {
				$book = substr( explode( '/', $file )[1], 0, -4 );

				// First, count all of the lines
				$length = Counter::countFrom( $file );
				$this->output->addBook( $book, $length );

				Reader::readInto( $file, array( $this, 'importLine' ) );

				$resolve();
			} );
		} );
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
		$client = new Client( 'mongodb://192.168.99.100:27017' );
		$book = $client->selectCollection( 'concurrent', $verse->book );
		$book->insertOne( $verse );

		$this->output->incrementPosition( $verse->book );
		if ( $this->verbose ) {
			$this->output->printTable();	
		}
	}
}