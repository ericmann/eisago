<?php
namespace EAMann\Eisago;

use MongoDB\Client;
use Icicle\Loop;

class ImperativeImporter extends BaseImporter {

	/**
	 * @var string
	 */
	protected $database = 'imperative';

	/**
	 * Actually invoke the import mechanism
	 *
	 * @param string $path Path from which to import data
	 *
	 * @return mixed
	 */
	public function run( string $path ) {
		$this->path = $path;

		// Set up our database connection
		$client = new Client( 'mongodb://mongo:27017' );

		// Ensure we're working with a clean slate
		$client->dropDatabase( $this->database );
		
		// Run our import one file at a time
		array_map( array( $this, 'importFile' ), $this->getFileList() );

		// Print our output
		$this->output->printTable( true );
		
		// Now that we're done, print a random Proverb to the screen
		$chapter = mt_rand( 1, 33 );
		$verse_num = mt_rand( 1, 20 );
		$proverbs = $client->selectCollection( $this->database, 'Proverbs' );
		$verse = $proverbs->findOne( [ 'chapter' => $chapter, 'verse' => $verse_num ] );
		$verse = Verse::parse( $verse );
			
		$this->output->writeln( $verse->title . ' - ' . $verse->content );
	}

	/**
	 * Read a file one line at a time, create a Verse from each line, and store each Verse in the database.
	 * 
	 * @param string $file
	 */
	protected function importFile( string $file ) {
		// Loop applied for simulated latency
		Loop\timer( 0.1 * mt_rand( 0, 20 ), function() use ( $file ) {
			// Get the name of the book from the filename
			$book = substr( explode( '/', $file )[ 1 ], 0, - 4 );

			// First, count all of the lines
			$length = Counter::countFrom( $file );

			// Read a line (verse) from the file into our database
			Reader::readInto( $file, [ $this, 'importLine' ] );

			// Update the stored output with the data that's just been read
			$this->output->addBook( $book, $length, $length );

			// Update our table's output with current progress
			if ( $this->verbose ) {
				$this->output->printTable( true );
			}
		} );

		// Kick off the loop if it's not already running
		Loop\run();
	}
}