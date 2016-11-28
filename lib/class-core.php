<?php
/**
 * This file contains the core functionality of the plugin.
 *
 * @package Split Domain
 */

namespace Split_Domain;

/**
 * Class for core functionality.
 */
class Core {
	use Singleton;

	/**
	 * Store a reference to the current site term.
	 *
	 * @var \WP_Term
	 */
	public $site_term;

	/**
	 * Setup the singleton.
	 */
	public function setup() {
		add_action( 'template_redirect', [ $this, 'redirects' ] );

		// Filter permalinks.
		add_filter( 'post_link',         [ $this, 'post_link' ],     10, 2 );
		add_filter( 'page_link',         [ $this, 'post_link' ],     10, 2 );
		add_filter( 'attachment_link',   [ $this, 'post_link' ],     10, 2 );
		add_filter( 'post_type_link',    [ $this, 'post_link' ],     10, 2 );
		add_filter( 'get_canonical_url', [ $this, 'canonical_url' ], 10, 2 );

		// Filter admin urls.
		add_filter( 'admin_url', [ $this, 'admin_url' ] );
		add_action( 'admin_init', [ $this, 'admin_redirect' ] );
	}

	/**
	 * Get the current site term, or a property of that term.
	 *
	 * @param  string $property Optional. Property to return, like term_id or
	 *                          slug. If absent, the WP_Term object will return.
	 * @return mixed Depends on $property value.
	 */
	public function get_current_site_term( $property = null ) {
		if ( ! isset( $this->site_term ) ) {
			if ( ! in_array( $property, [ 'slug', 'term_id', 'name' ] ) && ! did_action( 'init' ) ) {
				_doing_it_wrong( __METHOD__, esc_html__( 'You cannot use this method that way before "init" because taxonomies have not been registered yet. You can only get the term_id, slug, or name of the term this early.', 'split-domain' ), '4.6.0' );
				return false;
			}

			// Get the current site using cached data instead of querying for terms.
			$current_site = $this->get_current_site_from_cache();
			if ( empty( $current_site['term_id'] ) ) {
				return false;
			}

			// If we have the data we need in cache, return it right away.
			if ( ! empty( $current_site[ $property ] ) ) {
				return $current_site[ $property ];
			}

			// Get the site term for the current site.
			$term = get_term( $current_site['term_id'], Site::instance()->name );

			// Return false on error.
			if ( is_wp_error( $term ) || empty( $term->slug ) ) {
				$this->site_term = false;
			} else {
				$this->site_term = $term;
			}
		}

		// If we want a specific property, return that.
		if ( $this->site_term && $property ) {
			return isset( $this->site_term->$property ) ? $this->site_term->$property : null;
		} else {
			return $this->site_term;
		}
	}

	/**
	 * Get the current site from the cached option.
	 *
	 * @return array {
	 *     Basic information about the current site.
	 *
	 *     @type int $term_id The site's term id.
	 *     @type string $name The site's term name (same as the current host).
	 *     @type string $slug The site's term slug.
	 * }
	 */
	public static function get_current_site_from_cache() {
		$domains = get_option( 'split_domain_sites', [] );
		if ( empty( $domains ) ) {
			return;
		}

		$current_host = parse_url( home_url(), PHP_URL_HOST );
		if ( ! empty( $domains[ $current_host ] ) ) {
			$return = $domains[ $current_host ];
			$return['name'] = $current_host;
			return $return;
		} else {
			return false;
		}
	}

	/**
	 * Get the primary site for a given (or the current) post.
	 *
	 * @param  \WP_Post|int $post Optional. Post object or ID.
	 * @return \WP_Term|false Term object on success, false otherwise.
	 */
	public static function get_post_primary_site( $post = null ) {
		$post = get_post( $post );
		if ( $post ) {
			// First check if we have a (valid) primary domain.
			$post_domains = get_post_meta( $post->ID, 'post_domains', true );
			if ( ! empty( $post_domains['primary'] ) ) {
				$site = get_term( $post_domains['primary'], Site::instance()->name );
				if ( $site && ! is_wp_error( $site ) && ! empty( $site->term_id ) ) {
					return $site;
				}
			}
		}

		return false;
	}

	/**
	 * Get the 'site-domain' WP_Term objects for a given (or the current) post.
	 *
	 * @param  \WP_Post|int $post Optional. Post object or ID.
	 * @return array \WP_Term objects.
	 */
	public static function get_sites_for_post( $post = null ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return [];
		}

		$sites = get_the_terms( $post, Site::instance()->name );
		if ( is_wp_error( $sites ) || empty( $sites ) ) {
			return [];
		} else {
			return $sites;
		}
	}

	/**
	 * Is the given or current post allowed on the current site?
	 *
	 * @param  \WP_Post|int $post Optional. Post object or ID.
	 * @return bool True if yes, false if no.
	 */
	public static function is_post_allowed_on_current_site( $post = null ) {
		$sites = self::get_sites_for_post( $post );

		// First check if the current site is in the list and prefer that.
		$current_site_id = intval( self::instance()->get_current_site_term( 'term_id' ) );
		foreach ( $sites as $site ) {
			if ( intval( $site->term_id ) === $current_site_id ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the 'site-domain' WP_Term for a given (or the current) post.
	 *
	 * @param  \WP_Post|int $post Optional. Post object or ID.
	 * @param  string       $prefer Optional. Which site to prefer, the 'primary'
	 *                              or the 'current'. Defaults to 'current'.
	 * @return \WP_Term|false Term object on success, false on failure.
	 */
	public static function get_post_site( $post = null, $prefer = 'current' ) {
		// Sometimes we'll prefer the primary domain for a post, and sometimes
		// we'll prefer the current domain.
		$order = 'primary' === $prefer ? [ 'primary', 'current' ] : [ 'current', 'primary' ];
		foreach ( $order as $check ) {
			if ( 'primary' === $check ) {
				$primary = self::get_post_primary_site( $post );
				if ( $primary ) {
					return $primary;
				}
			} else {
				if ( self::is_post_allowed_on_current_site( $post ) ) {
					return self::instance()->get_current_site_term();
				}
			}
		}

		// If we've made it this far, the post doesn't have a primary domain
		// and it's not part of the current site. The next in line is any
		// allowed site.
		$sites = self::get_sites_for_post( $post );
		if ( ! empty( $sites ) ) {
			return reset( $sites );
		}

		// If all else fails, return the default site.
		return self::get_default_site();
	}

	/**
	 * Get the default site term.
	 *
	 * @return \WP_Term|false Term object on success, false on failure.
	 */
	public static function get_default_site() {
		$default_term_id = Settings::instance()->get_setting( 'default' );
		if ( is_int( $default_term_id ) ) {
			$term = get_term( $default_term_id, Site::instance()->name );
			if ( $term && ! is_wp_error( $term ) ) {
				return $term;
			}
		}
		return false;
	}

	/**
	 * Redirect to the canonical version of the current url if the site is not
	 * correct.
	 */
	public function redirects() {
		if ( is_singular() ) {
			$post_site = $this->get_post_site();
			if ( ! $post_site ) {
				$post_site = $this->get_default_site();
				if ( ! $post_site ) {
					return;
				}
			}

			$current_site = $this->get_current_site_term();
			if ( ! $current_site || $post_site->name === $current_site->name ) {
				return;
			}

			wp_redirect( str_replace( $current_site->name, $post_site->name, get_permalink() ), 301 );
			exit;
		}
	}

	/**
	 * Set the correct domain in permalinks. This is a hook for all filters
	 * stemming from `get_permalink()`.
	 *
	 * @param  string       $permalink URL.
	 * @param  int|\WP_Post $post Post object or ID.
	 * @return string URL.
	 */
	public function post_link( $permalink, $post ) {
		$post_site = $this->get_post_site( $post, ( is_admin() ? 'primary' : 'current' ) );
		if ( ! $post_site ) {
			$post_site = $this->get_default_site();
		}

		if ( ! empty( $post_site->name ) && false === strpos( $permalink, $post_site->name ) ) {
			$permalink = str_replace( parse_url( home_url(), PHP_URL_HOST ), $post_site->name, $permalink );
		}
		return $permalink;
	}

	/**
	 * Set the correct domain in canonical urls. This is a hook for
	 * `get_canonical_url` and differs from `post_link()` in that it will only
	 * change the URL if the URL's domain is not the post's primary domain.
	 *
	 * @param  string       $permalink URL.
	 * @param  int|\WP_Post $post Post object or ID.
	 * @return string URL.
	 */
	public function canonical_url( $permalink, $post ) {
		$post_site = $this->get_post_primary_site( $post );
		if (
			$post_site
			&& ! empty( $post_site->name )
			&& false === strpos( $permalink, $post_site->name )
		) {
			$permalink = str_replace( parse_url( $permalink, PHP_URL_HOST ), $post_site->name, $permalink );
		}

		return $permalink;
	}

	/**
	 * Filter admin_url to set the domain to be the default domain.
	 *
	 * @param  string $url The admin URL being filtered.
	 * @return string URL.
	 */
	public function admin_url( $url ) {
		$site = $this->get_default_site();

		if ( ! empty( $site->name ) && false === strpos( $url, $site->name ) ) {
			$url = str_replace( parse_url( home_url(), PHP_URL_HOST ), $site->name, $url );
		}

		return $url;
	}

	/**
	 * Redirect admin screens to the default domain, should it be different from
	 * the current domain.
	 */
	public function admin_redirect() {
		if (
			! isset( $_SERVER['HTTP_HOST'] )
			|| ! isset( $_SERVER['REQUEST_URI'] )
			|| defined( 'DOING_AJAX' ) && DOING_AJAX
			|| ! apply_filters( 'split_domain_redirect_admin_domain', true )
		) {
			return;
		}

		$site = $this->get_default_site();
		if ( ! empty( $site->name ) && false === strpos( $_SERVER['HTTP_HOST'], $site->name ) ) { // WPCS: sanitization ok.
			wp_redirect( esc_url_raw( 'http://' . $site->name . $_SERVER['REQUEST_URI'] ) ); // WPCS: sanitization ok.
			exit();
		}
	}
}
