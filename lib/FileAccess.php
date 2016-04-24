<?php
namespace EAMann\Eisago;

abstract class FileAccess {
	/**
	 * @var resource
	 */
	protected $handle;

	/**
	 * Link to a specific file and allow the habdler to read from it
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
}