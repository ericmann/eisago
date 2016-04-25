<?php
namespace EAMann\Eisago;

use Icicle\Awaitable\Promise;
use Icicle\Coroutine;
use Icicle\Loop;
use MongoDB\Client;

class ParallelImporter extends BaseImporter {

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
			$client->dropDatabase( 'parallel' );
			
			$promises = array_map( [ $this, 'importFile' ], $this->getFileList() );

			\Icicle\Awaitable\all( $promises )->then( function() use ( $resolve ) {
				$this->output->printTable( true );
				$resolve();
			} );

			Loop\run();
		} );
	}

	protected function importFile( string $file ) {

		return new Promise( function( callable $resolve, callable $reject ) use ( $file ) {
			$context = new ImportThread( $file );

			Loop\timer( 0.1 * mt_rand( 0, 10 ), function() use ( $context, $resolve ) {
				$context->start();
				$context->join();

				$this->output->addBook( $context->book, $context->total, $context->total );
				if ( $this->verbose ) {
					$this->output->printTable( true );
				}
				
				$resolve();
			} );
		} );
	}
}