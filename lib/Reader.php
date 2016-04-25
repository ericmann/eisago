<?php
namespace EAMann\Eisago;

class Reader extends FileAccess {
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