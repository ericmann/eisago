<?php
namespace EAMann\Eisago;

class Reader {
	/**
	 * @var resource
	 */
	protected $handle;

	/**
	 * Link to a specific file and allow the processor to read from it
	 *
	 * @throws IOException
	 *
	 * @param string $file
	 */
	protected function __construct( string $file ) {
		$this->handle = fopen( $file, 'r' );
		
		if ( false === $this->handle ) {
			throw new IOException();
		}
	}

	/**
	 * Instantiate a new reader and pass each line through the processing callback.
	 *
	 * @param string   $file      File to read
	 * @param callable $processor Callback that will be executed once for each line in the file
	 *
	 * @return bool
	 */
	public static function readInto( string $file, callable $processor ) {
		try {
			$reader = new self( $file );

			while ( ! feof( $reader->handle ) ) {
				$processor( fgets( $reader->handle ) );
			}

			fclose( $reader->handle );

			return true;
		} catch ( IOException $e ) {
			// Something was wrong with the file, so return false
			return false;
		}
	}
}