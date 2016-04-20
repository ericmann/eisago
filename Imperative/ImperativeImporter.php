<?php
namespace EAMann\Eisago;

use MongoDB\Client;

class ImperativeImporter extends BaseImporter {

	/**
	 * @var \MongoDB\Client
	 */
	protected $client;


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
		$this->client = new Client( 'mongodb://192.168.99.100:27017' );

		// Ensure we're working with a clean slate
		$this->client->dropDatabase( 'imperative' );
		
		// Run our import one file at a time
		array_map( array( $this, 'importFile' ), $this->getFileList() );
		
		// Now that we're done, print a random Proverb to the screen
		$proverbs = $this->client->selectCollection( 'imperative', 'Proverbs' );
		$rob_not = $proverbs->findOne( [ 'chapter' => 22, 'verse' => 22 ] );
		$verse = Verse::parse( $rob_not );
			
		$this->output->writeln( $verse->title . ' - ' . $verse->content );
	}

	/**
	 * Read a file one line at a time, create a Verse from each line, and store each Verse in the database.
	 * 
	 * @param string $file
	 */
	protected function importFile( string $file ) {
		$book = substr( explode( '/', $file )[1], 0, -4 );
		$this->output->write( $book . ' ' );

		Reader::readInto( $file, array( $this, 'importLine' ) );
		$this->output->write( '', true );
	}

	/**
	 * Read a single line, parse it as a verse, and store it in the database.
	 * 
	 * @param string $line
	 */
	public function importLine( string $line ) {
		$this->output->write( '.' );

		// Create our verse
		$verse = new Verse( $line );

		// Save our verse
		$book = $this->client->selectCollection( 'imperative', $verse->book );
		$book->insertOne( $verse );
	}
}