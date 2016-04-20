<?php
namespace EAMann\Eisago;

use MongoDB\Model\BSONDocument;

class Verse implements \Serializable {
	public $book;

	public $chapter;

	public $verse;

	public $title;

	public $content;

	/**
	 * Verse constructor.
	 * 
	 * @param string|null $maybeSerialized
	 */
	public function __construct( string $maybeSerialized = null ) {
		if ( null !== $maybeSerialized ) {
			$this->unserialize( $maybeSerialized );
		}
	}

	/**
	 * Convert a Mongo document back into a Verse object
	 *
	 * @param BSONDocument $doc
	 *
	 * @return Verse
	 */
	public static function parse( BSONDocument $doc ) {
		$verse = new self;
		$verse->book = $doc['book'];
		$verse->chapter = $doc['chapter'];
		$verse->verse = $doc['verse'];
		$verse->title = $doc['title'];
		$verse->content = $doc['content'];

		return $verse;
	}

	/**
	 * Serialize the Verse in the same format we read from.
	 *
	 * @return string
	 */
	public function serialize() {
		return implode( '||', [ $this->book, $this->chapter, $this->verse, $this->content ] );
	}

	/**
	 * Explode our serialized string into a real Verse object.
	 *
	 * @param string $serialized
	 */
	public function unserialize( $serialized ) {
		$parts = explode( '||', $serialized );

		$this->book = $parts[0];
		$this->chapter = (int) $parts[1];
		$this->verse = (int) $parts[2];
		$this->title = $this->book . ' ' . $this->chapter . ':' . $this->verse;
		$this->content = $parts[3];
	}
}