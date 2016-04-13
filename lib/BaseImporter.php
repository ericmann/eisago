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
 * Each Importer will display progress differently:
 * - Imperative imports will display each filename and a progress bar for each
 * - Concurrent imports will display all 5 files they're working on with progress bars for each
 * - Parallel imports will display all files they're working on with progress bars for each
 *
 * Total execution time will be displayed after the import run.
 */
abstract class BaseImporter {
	public function __construct( OutputInterface $output ) {
	}
}