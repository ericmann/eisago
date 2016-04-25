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
		$this->output->printTable( true );
		
		// Now that we're done, print a random Proverb to the screen
		//$proverbs = $this->client->selectCollection( 'imperative', 'Proverbs' );
		//$rob_not = $proverbs->findOne( [ 'chapter' => 22, 'verse' => 22 ] );
		//$verse = Verse::parse( $rob_not );
			
		//$this->output->writeln( $verse->title . ' - ' . $verse->content );
	}

	/**
	 * Read a file one line at a time, create a Verse from each line, and store each Verse in the database.
	 * 
	 * @param string $file
	 */
	protected function importFile( string $file ) {
		Loop\timer( 0.1 * mt_rand( 0, 20 ), function() use ( $file ) {
			$book = substr( explode( '/', $file )[ 1 ], 0, - 4 );

			// First, count all of the lines
			$length = Counter::countFrom( $file );

			Reader::readInto( $file, array( $this, 'importLine' ) );

			$this->output->addBook( $book, $length, $length );
			$this->output->printTable( true );
		} );

		Loop\run();
	}
}