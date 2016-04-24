<?php
namespace EAMann\Eisago;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generic importer from which all others inherit and extend functionality.
 *
 * Basic workflow will follow this pattern:
 * - Read directory listing of /data to determine file names
 * - Read each file and parse them line-by-line to create verses
 * - Insert each verse into Mongo
 *
 * Each Importer will display progress differently. See README.md for details
 */
abstract class BaseImporter {

	/**
	 * @var OutputInterface
	 */
	protected $output;

	/**
	 * @var string
	 */
	protected $path;

	/**
	 * Base constructor.
	 *
	 * @param OutputWriter $output
	 */
	public function __construct( OutputWriter $output ) {
		$this->output = $output;
	}

	/**
	 * Get a list of files in the specified import directory through which we must iterate.
	 *
	 * @return array
	 */
	protected function getFileList() {
		$files = scandir( $this->path );

		if ( false === $files ) {
			return [];
		}

		//return array_slice( array_map( function ( $file ) { return $this->path . '/' . $file; }, array_diff( $files, ['..', '.'] ) ), 0, 5 );
		return array_map( function ( $file ) { return $this->path . '/' . $file; }, array_diff( $files, ['..', '.'] ) );
	}

	/**
	 * Actually invoke the import mechanism
	 *
	 * @param string $path Path from which to import data
	 *
	 * @return mixed
	 */
	abstract function run( string $path );
}