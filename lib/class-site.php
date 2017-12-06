<?php
/**
 * This file defines the `site` Taxonomy class.
 *
 * @package Switchboard
 */

namespace Switchboard;

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
		$this->object_types = apply_filters( 'switchboard_post_types', [ 'post', 'page' ] );
		add_action( 'fm_post', [ $this, 'site_dropdown' ] );
		add_action( 'fm_term_' . $this->name, [ $this, 'aliases_fields' ], 7 );
		add_action( 'edited_' . $this->name, [ $this, 'update_cache' ] );
		add_action( 'created_' . $this->name, [ $this, 'update_cache' ] );
		add_action( 'delete_' . $this->name, [ $this, 'update_cache' ] );

		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_menu', [ $this, 'admin_submenus' ], 20 );
		add_action( 'admin_head', [ $this, 'activate_parent_menu' ] );

		add_action( 'admin_print_footer_scripts-edit-tags.php', [ $this, 'ui_override_script' ] );
		add_action( 'admin_print_footer_scripts-term.php', [ $this, 'ui_override_script' ] );

		add_filter( 'pre_insert_term', [ $this, 'santize_term_data' ], 10, 2 );
		add_filter( 'wp_update_term_data', [ $this, 'prevent_invalid_domains_on_edit' ], 10, 3 );

		parent::setup();
	}

	/**
	 * Modify the admin menu to make Domains top-level.
	 */
	public function admin_menu() {
		add_menu_page( _x( 'Domains', 'switchboard menu item', 'switchboard' ), _x( 'Domains', 'switchboard menu item', 'switchboard' ), 'manage_options', 'switchboard', '__return_false', 'dashicons-networking', '4.01' );
		add_submenu_page( 'switchboard', __( 'Edit Domains', 'switchboard' ), __( 'Edit Domains', 'switchboard' ), 'manage_options', 'edit-tags.php?taxonomy=' . $this->name );
	}

	/**
	 * Our top-level menu items are by themselves useless, so we have to remove the
	 * blank links.
	 */
	function admin_submenus() {
		global $submenu;
		$remove_top_levels = [ 'switchboard' ];
		foreach ( $remove_top_levels as $slug ) {
			if ( isset( $submenu[ $slug ] ) ) {
				array_shift( $submenu[ $slug ] );
			}
		}
	}

	/**
	 * Highlight the parent menu if this submenu item is active.
	 */
	public function activate_parent_menu() {
		global $parent_file, $submenu_file, $taxonomy;
		if ( $this->name === $taxonomy ) {
			$submenu_file = 'edit-tags.php?taxonomy=' . $taxonomy; // WPCS: override ok.
			$parent_file = 'switchboard'; // WPCS: override ok.
		}
	}

	/**
	 * Create the taxonomy.
	 */
	public function create_taxonomy() {
		register_taxonomy( $this->name, $this->object_types, [
			'labels' => [
				'name'                  => __( 'Sites', 'switchboard' ),
				'singular_name'         => __( 'Site', 'switchboard' ),
				'search_items'          => __( 'Search Sites', 'switchboard' ),
				'popular_items'         => __( 'Popular Sites', 'switchboard' ),
				'all_items'             => __( 'All Sites', 'switchboard' ),
				'parent_item'           => __( 'Parent Site', 'switchboard' ),
				'parent_item_colon'     => __( 'Parent Site', 'switchboard' ),
				'edit_item'             => __( 'Edit Site', 'switchboard' ),
				'view_item'             => __( 'View Site', 'switchboard' ),
				'update_item'           => __( 'Update Site', 'switchboard' ),
				'add_new_item'          => __( 'Add New Site', 'switchboard' ),
				'new_item_name'         => __( 'New Site Name', 'switchboard' ),
				'add_or_remove_items'   => __( 'Add or remove Sites', 'switchboard' ),
				'choose_from_most_used' => __( 'Choose from most used Sites', 'switchboard' ),
				'menu_name'             => __( 'Sites', 'switchboard' ),
			],
			'rewrite' => false,
			'show_ui' => true,
			'show_tagcloud' => false,
			'show_admin_column' => true,
			'show_in_menu' => false,
		] );

		do_action( 'switchboard_taxonomy_registered', $this->name );
	}

	/**
	 * Set the site on posts. Replaces the default term meta box.
	 *
	 * @todo Make "Default" the first in the list.
	 * @todo Remove dependence on Fieldmanager
	 *
	 * @param  string $post_type The current post type.
	 */
	public function site_dropdown( $post_type ) {
		if ( ! in_array( $post_type, $this->object_types ) ) {
			return;
		}

		$fm = new \Fieldmanager_Group( [
			'name' => 'post_domains',
			'children' => [
				'allowed' => new \Fieldmanager_Checkboxes( [
					'label' => __( 'Allow on these domains:', 'switchboard' ),
					'remove_default_meta_boxes' => true,
					'datasource' => new \Fieldmanager_Datasource_Term( [
						'taxonomy' => $this->name,
						'only_save_to_taxonomy' => true,
					] ),
				] ),
				'primary' => new \Fieldmanager_Select( [
					'label' => __( 'Primary Domain:', 'switchboard' ),
					'default_value' => Settings::instance()->get_setting( 'default' ),
					'datasource' => new \Fieldmanager_Datasource_Term( [
						'taxonomy' => $this->name,
					] ),
				] ),
			],
		] );
		$fm->add_meta_box( __( 'Site', 'switchboard' ), $this->object_types, 'side', 'high' );
	}

	/**
	 * Create the UI for domain aliases.
	 */
	public function aliases_fields() {
		$fm = new \Fieldmanager_Group( array(
			'name' => 'aliases_wrapper',
			'label' => __( 'Domain Aliases', 'switchboard' ),
			'description' => __( 'Domain aliases will automatically get redirected to the domain. For example, you could add an alias "www.domain.com" for the domain "domain.com" to redirect all pages on www.domain.com to domain.com.', 'switchboard' ),
			'serialize_data' => false,
			'add_to_prefix' => false,
			'children' => [
				'alias' => new \Fieldmanager_TextField( [
					'serialize_data' => false,
					'one_label_per_item' => false,
					'limit' => 0,
					'extra_elements' => 0,
					'add_more_label' => __( 'Add a domain alias', 'switchboard' ),
					'sanitize' => [ $this, 'validate_domain' ],
					'attributes' => [
						'placeholder' => 'www.domain.com',
						'size' => 50,
					],
				] ),
			],
		) );
		$fm->add_term_meta_box( '', array( 'site-domain' ) );
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
			$cache[ $term->name ] = [
				'term_id' => $term->term_id,
				'slug' => $term->slug,
			];
		}
		update_option( 'switchboard_sites', $cache );

		// Unset the cached site term in the Core class.
		Core::instance()->site_term = null;

		// Update the domain aliases cache.
		Core::update_domain_aliases_cache();
	}

	/**
	 * UI Overrides for the new/edit term screens for this taxonomy.
	 */
	public function ui_override_script() {
		global $taxnow;
		if ( $taxnow && $this->name === $taxnow ) :
			?>
			<script type="text/javascript">
			jQuery( function( $ ) {
				$('.term-name-wrap label,.manage-column.column-name > a > span:first-child').text( <?php echo wp_json_encode( __( 'Domain', 'switchboard' ) ); ?> );
				$('#tag-name').attr( 'placeholder', 'domain.com' );
				$('.term-name-wrap p').text( <?php echo wp_json_encode( __( 'The domain, without the protocol (http://) and without a slash at the end. Be sure to include the "www." if the domain will use that.', 'switchboard' ) ); ?> );
				$('.term-slug-wrap p').text( <?php echo wp_json_encode( __( 'The slug is used for templating. It should be all lowercase and contain only letters, numbers, and hyphens.', 'switchboard' ) ); ?> );
				$('.term-description-wrap p').text( <?php echo wp_json_encode( __( 'The description is used for internal notes.', 'switchboard' ) ); ?> );
			});
			</script>
			<?php
		endif;
	}

	/**
	 * Validate and normalize a domain string.
	 *
	 * @param  string $domain Domain.
	 * @return string|false   Domain, normalized, if valid; false if invalid.
	 */
	public function validate_domain( $domain ) {
		$domain = strtolower( $domain );
		$domain_full = $domain;

		// Prepend protocol for URL validation.
		if ( ! preg_match( '#^https?://#', $domain_full ) ) {
			$domain_full = 'http://' . $domain_full;
		}
		$domain = parse_url( $domain_full, PHP_URL_HOST );

		if (
			empty( $domain )
			// @todo Need another option; this will fail on URLs that contain
			// multibyte characters.
			|| ! filter_var( $domain_full, FILTER_VALIDATE_URL )
			|| ! preg_match( '/^[-_\w]+(\.[-_\w]+)+$/', $domain )
		) {
			return false;
		}

		return $domain;
	}

	/**
	 * Prevent terms in this taxonomy from being created if the `name` is not a
	 * valid domain.
	 *
	 * @param  string $term     Term name.
	 * @param  string $taxonomy Taxonomy slug.
	 * @return string           Term name.
	 */
	public function santize_term_data( $term, $taxonomy ) {
		if ( $this->name === $taxonomy ) {
			$term = $this->validate_domain( $term );
			if ( ! $term ) {
				return new \WP_Error( 'invalid-domain', __( 'Invalid domain. The domain should be of the form "example.com" or "www.example.com"', 'switchboard' ) );
			}
		}

		return $term;
	}

	/**
	 * Prevent `wp_update_term()` from setting an invalid `name` for this
	 * taxonomy. This method will call `wp_die()` if the name is invalid, since
	 * `wp_update_term()` offers no way to gracefully reject changes.
	 *
	 * @param  array  $data     Term data.
	 * @param  int    $term_id  Term ID.
	 * @param  string $taxonomy Taxonomy slug.
	 * @return array            Term data.
	 */
	public function prevent_invalid_domains_on_edit( $data, $term_id, $taxonomy ) {
		global $wp_list_table;
		if (
			$this->name === $taxonomy
			&& $wp_list_table instanceof WP_List_Table
			&& 'editedtag' === $wp_list_table->current_action()
		) {
			$term = $this->validate_domain( $data['name'] );
			if ( ! $term ) {
				wp_die( esc_html__( 'Invalid domain. The domain should be of the form "example.com" or "www.example.com". Please go back and enter a new domain.', 'switchboard' ) );
			}
		}

		return $data;
	}
}
