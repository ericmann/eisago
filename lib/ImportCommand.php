<?php
namespace EAMann\Eisago\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends Command {
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


	function execute( InputInterface $input, OutputInterface $output ) {

	}
}