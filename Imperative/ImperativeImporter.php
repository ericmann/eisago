<?php
namespace EAMann\Eisago;

class ImperativeImporter extends BaseImporter {
	
	/**
	 * Actually invoke the import mechanism
	 *
	 * @param string $path Path from which to import data
	 *
	 * @return mixed
	 */
	public function run( string $path ) {
		$this->path = $path;
		
		// Run our import one file at a time
		array_map( array( $this, 'importFile' ), $this->getFileList() );
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

		// @TODO Save our verse

	}
}