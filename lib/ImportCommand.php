<?php
namespace EAMann\Eisago\Command;

use EAMann\Eisago;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The actual wiring for the Symfony import command.
 * @package EAMann\Eisago\Command
 */
class ImportCommand extends Command {
	
	/**
	 * Configure the command so the name, description, and options appear properly
	 * in help text in the console.
	 */
	protected function configure() {
		$this->setName( 'import' )
			->setDescription( 'Import the files in the /data directory' )
			->addOption(
				'mode',
				null,
				InputOption::VALUE_OPTIONAL,
				'Run-mode',
				'imperative'
			);
	}

	/**
	 * Actually execute the command, creating an importer object as needed.
	 * 
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 *
	 * @return null|int null or 0 if everything went fine, or an error code
	 */
	function execute( InputInterface $input, OutputInterface $output ) {
		$outputWriter = new Eisago\OutputWriter( $output );

		switch( $input->getOption( 'mode' ) ) {
			case 'concurrent':
				$output->writeln( 'Executing concurrently ...' );

				$importer = new Eisago\ConcurrentImporter( $outputWriter );
				$importer->run( 'data' );
				break;
			case 'parallel':
				$output->writeln( 'Executing in parallel ...' );

				$importer = new Eisago\ParallelImporter( $outputWriter );
				$importer->run( 'data' );
				break;
			case 'imperative':
			default:
				$output->writeln( 'Executing synchronously ...' );
			
				$importer = new Eisago\ImperativeImporter( $outputWriter );
				$importer->run( 'data' );
				break;
		}
	}
}