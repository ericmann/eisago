<?php
namespace EAMann\Eisago;

use Icicle\Awaitable\Promise;
use Icicle\Concurrent\Threading\Thread;
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
$this->output->interface->writeln( 'dropped DB' );
			// Run our import one file at a time
			$promises = array_map( array( $this, 'importFile' ), $this->getFileList() );
$this->output->interface->writeln( 'created promises' );
			\Icicle\Awaitable\all( $promises )->then( function() use ( $resolve ) {
$this->output->interface->writeln( 'resolved promises' );
				$this->output->printTable( true );
				$resolve();
			} );

			Coroutine\create(function () {
				print 'inside routine';
				$thread = Thread::spawn(function () {
					$time = (yield $this->receive()); // Receive from the parent.
					sleep($time);
					yield $this->send("Hello!"); // Send to the parent.
				});

				print 'yielding to thread';
				yield $thread->send(3); // Send 3 to the context.

				print 'receiving from thread';
				$message = (yield $thread->receive()); // Receive from the context.
				yield $thread->join();

				print $message . "\n";
			});

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
$this->output->interface->writeln( $file );
		return new Promise( function( callable $resolve, callable $reject ) use ( $file ) {

//			Loop\timer( 0.1 * mt_rand( 0, 10 ), function() use ( $file, $resolve ) {
$this->output->interface->writeln( $file );

				$thread = Thread::spawn( function( $file, $output ) {
//					/** @var callable $handler */
//					$handler = ( yield $this->receive() ); // Receive import handler callback from parent

					$book = substr( explode( '/', $file )[1], 0, -4 );
//					$output->interface->writeln( $book );

//					// First, count all of the lines
//					$length = Counter::countFrom( $file );
//					$output->addBook( $book, $length );
//
//					ParallelReader::readInto( $file, $handler, $output );
//
					$this->send( $book );
				}, $file, $this->output );

				$this->output->interface->writeln( 'Created thread' );
//				yield $thread->send( array( $this, 'importLine' ) );
//
				$book = ( yield $thread->receive() );
$this->output->interface->writeln( $book );
				yield $thread->join();

				$resolve();
//			} );
		} );
	}

	/**
	 * Read a single line, parse it as a verse, and store it in the database.
	 *
	 * @param string       $line
	 * @param OutputWriter $output
	 */
	public function importLine( string $line, $output ) {
		// Create our verse
		$verse = new Verse( $line );

		// Save our verse with a new Mongo connection
		$client = new Client( 'mongodb://192.168.99.100:27017' );
		$book = $client->selectCollection( 'parallel', $verse->book );
		$book->insertOne( $verse );

		$output->incrementPosition( $verse->book );
		$output->printTable();
	}
}