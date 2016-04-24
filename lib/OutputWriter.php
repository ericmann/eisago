<?php
namespace EAMann\Eisago;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class OutputWriter {

	/**
	 * @var OutputInterface
	 */
	protected $interface;

	/**
	 * @var array
	 */
	protected $storage = [];

	/**
	 * @var int
	 */
	protected $nextWrite = 0;

	/**
	 * Build up the output writer that will keep track of various books and the progress through each.
	 *
	 * @param OutputInterface $interface
	 */
	public function __construct( OutputInterface $interface ) {
		$this->interface = $interface;
	}

	/**
	 * Add a book to the internal hash table so we can track progress.
	 *
	 * @param string $book
	 * @param int    $verses
	 */
	public function addBook( string $book, int $verses ) {
		$this->storage[ $book ] = [ $verses, 0 ];
	}

	/**
	 * Increment the internal counter for a specific book.
	 *
	 * @throws \InvalidArgumentException
	 *
	 * @param string $book
	 */
	public function incrementPosition( string $book ) {
		if ( ! array_key_exists( $book, $this->storage ) ) {
			throw new \InvalidArgumentException( sprintf( 'No such book `%s`!', $book ) );
		}

		$this->storage[ $book ][1]++;
	}

	/**
	 * Print the internal hash table to the interface as a table.
	 *
	 * @param bool $force Force the generation of output
	 */
	public function printTable( bool $force = false ) {

		$table = new Table( $this->interface );
		$table->setHeaders( [ 'Book', 'Progress' ] );

		// Build up the table's contents
		$contents = [];
		foreach( $this->storage as $book => $iterations ) {
			if ( $iterations[0] === $iterations[1] ) {
				// Book is done importing
				$contents[] = [ $book, sprintf( '<fg=green>Done!</> Imported <fg=yellow>%d</> verses.', $iterations[1] ) ];
			} else {
				// Book is importing - Show progress
				$progress = $iterations[1] / $iterations[0];
				$count = ceil( $progress * 30 );
				$contents[] = [ $book, str_repeat( '.', intval( $count ) ) . str_repeat( ' ', intval( 31 - $count ) ) . ceil( $progress * 100 ) . '%' ];
			}
		}
		$table->setRows( $contents );

		$now = intval( microtime( true ) * 10 );

		if ( $force || $now > $this->nextWrite ) {
			$this->interface->write("\x0D\033\143" );
			$table->render();

			$this->nextWrite = $now + 2;
		}
	}
}