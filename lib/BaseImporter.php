<?php
namespace EAMann\Eisago;

use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseImporter {
	public function __construct( OutputInterface $output ) {
	}
}