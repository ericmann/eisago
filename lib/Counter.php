<?php
namespace EAMann\Eisago;

class Counter extends FileAccess {
	/**
	 * Instantiate a new counter and count each line in the file
	 *
	 * @param string $file File to read
	 *
	 * @return int
	 */
	public static function countFrom( string $file ) {
		try {
			$reader = new self( $file );
			$length = 0;

			while ( ! feof( $reader->handle ) ) {
				fgets( $reader->handle );
				$length++;
			}

			fclose( $reader->handle );

			return $length;
		} catch ( IOException $e ) {
			// Something was wrong with the file, so return false
			return 0;
		}
	}
}