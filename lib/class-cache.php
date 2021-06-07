<?php
/**
 * This file contains the purge cache functionality of the plugin.
 *
 * @package Switchboard
 */

namespace Switchboard;

/**
 * Class for cache functionality.
 */
class Cache {
	use Singleton;

	/**
	 * Setup the singleton.
	 */
	public function setup() {
		add_filter( 'wpcom_vip_cache_purge_post_post_urls', [ $this, 'cache_purge_post_urls_domains' ], 10, 2 );
	}


	/**
	 * Get all URLs to be purged for a given term and domain.
	 *
	 * @param object $term A WP term object.
	 * @param string $site site domain.
	 *
	 * @return array An array of URLs to be purged
	 */
	public static function get_purge_urls_for_term_domain( $term, $site ) : array {
		// Belt and braces: get the term object,
		// in case something sent us a term ID.
		$term = get_term( $term );

		if ( is_wp_error( $term ) || empty( $term ) ) {
			return [];
		}

		$term_purge_urls = [];

		$taxonomy_name   = $term->taxonomy;
		$maybe_purge_url = get_term_link( $term, $taxonomy_name );
		if ( is_wp_error( $maybe_purge_url ) ) {
			return [];
		}

		if ( ! empty( $site ) ) {
			$maybe_purge_url = $site . wp_make_link_relative( $maybe_purge_url );
		}

		if ( $maybe_purge_url && is_string( $maybe_purge_url ) ) {
			$term_purge_urls[] = $maybe_purge_url;
		}

		return $term_purge_urls;
	}

	/**
	 * Hooks the wpcom_vip_cache_purge_post_post_urls filter to
	 * purge urls in all the domains assigned to the post.
	 *
	 * This targets posts of post type "post" only.
	 *
	 * @param array $urls An array of URLs to be purged.
	 * @param int   $post_id The ID of the post for which we're purging URLs.
	 * @return array An array of URLs to be purged.
	 */
	public static function cache_purge_post_urls_domains( $urls, $post_id ) {
		$post = get_post( $post_id );
		if ( empty( $post ) ) {
			return $urls;
		}

		// Get post domains.
		$site_domains = Core::instance()->get_sites_for_post( $post_id );

		if ( empty( $site_domains ) ) {
			return $urls;
		}

		// Post relative link.
		$rel_link = wp_make_link_relative( get_permalink( $post->ID ) );

		// http or https.
		$protocol = is_ssl() ? 'https://' : 'http://';

		// Taxonomies we need to purge.
		$taxonomies = [ 'category', 'post_tag' ];

		foreach ( $site_domains as $domain ) {
			$site = $protocol . $domain->name;

			// Single post url.
			$urls[] = $site . $rel_link;


			// Taxonomy archives.
			foreach ( $taxonomies as $taxonomy ) {
				$terms = get_the_terms( $post_id, $taxonomy );
				if ( false === $terms ) {
					continue;
				}
				foreach ( $terms as $term ) {
					$urls = array_merge( $urls, self::get_purge_urls_for_term_domain( $term, $site ) );
				}
			}
		}

		return array_unique( $urls );
	}
}
