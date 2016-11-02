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
	public $name = 'site-domain';

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
		add_action( 'fm_post', [ $this, 'site_dropdown' ] );
		add_action( 'edited_site-domain', [ $this, 'update_cache' ] );
		add_action( 'created_site-domain', [ $this, 'update_cache' ] );
		add_action( 'delete_site-domain', [ $this, 'update_cache' ] );

		// @todo move menu item, use `dashicons-networking`.

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
			'show_ui' => true,
			'show_tagcloud' => false,
			'show_admin_column' => true,
		] );
	}

	/**
	 * Set the site on posts. Replaces the default term meta box.
	 *
	 * @todo Make "Default" the first in the list.
	 *
	 * @param  string $post_type The current post type.
	 */
	public function site_dropdown( $post_type ) {
		if ( ! in_array( $post_type, $this->object_types ) ) {
			return;
		}

		$fm = new \Fieldmanager_Select( array(
			'name' => $this->name,
			'remove_default_meta_boxes' => true,
			'first_empty' => true,
			'datasource' => new \Fieldmanager_Datasource_Term( array(
				'taxonomy' => $this->name,
				'only_save_to_taxonomy' => true,
			) ),
		) );
		$fm->add_meta_box( __( 'Site', 'split-domain' ), $this->object_types, 'side', 'high' );
	}

	/**
	 * Update the site cache when terms are modified.
	 */
	public function update_cache() {
		$terms = get_terms( [
			'taxonomy'   => $this->name,
			'hide_empty' => false,
		] );

		if ( ! $terms || is_wp_error( $terms ) ) {
			return;
		}

		$cache = [];
		foreach ( $terms as $term ) {
			$cache[ $term->name ] = [ 'term_id' => $term->term_id, 'slug' => $term->slug ];
		}
		update_option( 'split_domain_sites', $cache );

		// Unset the cached site term in the Core class.
		Core::instance()->site_term = null;
	}
}
