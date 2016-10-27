<?php
/**
 * This file defines the abstract Taxonomy class.
 *
 * @package Split Domain
 */

namespace Split_Domain;

/**
 * Abstract class for taxonomy classes.
 */
abstract class Taxonomy {
	use Singleton;

	/**
	 * Name of the taxonomy.
	 *
	 * @var string
	 */
	public $name = null;

	/**
	 * Object types for this taxonomy.
	 *
	 * @var array
	 */
	public $object_types = [];

	/**
	 * Setup the singleton.
	 */
	public function setup() {
		// Create the taxonomy.
		add_action( 'init', [ $this, 'create_taxonomy' ] );
	}

	/**
	 * Create the taxonomy.
	 */
	abstract public function create_taxonomy();
}
