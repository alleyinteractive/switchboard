<?php
/**
 * This file defines the `site` Taxonomy class.
 *
 * @package Split Domain
 */

namespace Split_Domain;

/**
 * Taxonomy for Sites.
 */
class Site extends Taxonomy {

	/**
	 * Name of the taxonomy.
	 *
	 * @var string
	 */
	public $name = 'site';

	/**
	 * Object types for this taxonomy.
	 *
	 * @var array
	 */
	public $object_types;


	/**
	 * Setup the singleton.
	 */
	public function setup() {
		$this->object_types = apply_filters( 'split_domain_post_types', [ 'post', 'page' ] );

		parent::setup();
	}

	/**
	 * Create the taxonomy.
	 */
	public function create_taxonomy() {
		register_taxonomy( $this->name, $this->object_types, [
			'labels' => [
				'name'                  => __( 'Sites', 'split-domain' ),
				'singular_name'         => __( 'Site', 'split-domain' ),
				'search_items'          => __( 'Search Sites', 'split-domain' ),
				'popular_items'         => __( 'Popular Sites', 'split-domain' ),
				'all_items'             => __( 'All Sites', 'split-domain' ),
				'parent_item'           => __( 'Parent Site', 'split-domain' ),
				'parent_item_colon'     => __( 'Parent Site', 'split-domain' ),
				'edit_item'             => __( 'Edit Site', 'split-domain' ),
				'view_item'             => __( 'View Site', 'split-domain' ),
				'update_item'           => __( 'Update Site', 'split-domain' ),
				'add_new_item'          => __( 'Add New Site', 'split-domain' ),
				'new_item_name'         => __( 'New Site Name', 'split-domain' ),
				'add_or_remove_items'   => __( 'Add or remove Sites', 'split-domain' ),
				'choose_from_most_used' => __( 'Choose from most used Sites', 'split-domain' ),
				'menu_name'             => __( 'Sites', 'split-domain' ),
			],
			'rewrite' => false,
		] );
	}
}
