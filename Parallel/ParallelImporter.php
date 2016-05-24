<?php
namespace EAMann\Eisago;

use Icicle\Awaitable\Promise;
use Icicle\Coroutine;
use Icicle\Loop;
use MongoDB\Client;

class ParallelImporter extends BaseImporter {

	/**
	 * @var string
	 */
	protected $database = 'parallel';

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

			// Build an array of awaitables that will import each book in turn
			$promises = array_map( [ $this, 'importFile' ], $this->getFileList() );

			// Once all promises have finished, update our output
			\Icicle\Awaitable\all( $promises )->then( function( $books ) use ( $resolve, $client ) {

				// Print our output
				$this->output->printTable( true );

				// Now that we're done, print a random verse from James to the screen
				$chapter = mt_rand( 1, 5 );
				$verse_num = mt_rand( 1, 17 );
				$james = $client->selectCollection( $this->database, 'James' );
				$verse = $james->findOne( [ 'chapter' => $chapter, 'verse' => $verse_num ] );
				$verse = Verse::parse( $verse );

				$this->output->writeln( $verse->title . ' - ' . $verse->content );

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
			// Create a thread to process the import itself
			$context = new ImportThread( $file, $this->database );

			// Simulate network latency of the actual import
			Loop\timer( 0.1 * mt_rand( 0, 20 ), function() use ( $context, $resolve ) {
				// Start the thread's execution
				$context->start();
				
				// Wait for the thread to complete
				$context->join();

				// Update the stored output with the data that's just been read
				$this->output->addBook( $context->book, $context->total, $context->total );

				// Update our table's output with current progress
				if ( $this->verbose ) {
					$this->output->printTable( true );
				}

				// Resolve the promise so execution can continue.
				$resolve( $context->book );
			} );
		} );
	}
}