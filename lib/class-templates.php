<?php
/**
 * This file contains the Template class for modifying templates.
 *
 * @package Switchboard
 */

namespace Switchboard;

/**
 * Handle all template modifications.
 */
class Templates {
	use Singleton;

	/**
	 * Setup the singleton.
	 */
	public function setup() {
		add_filter( 'template_include', [ $this, 'template_include_prefix' ], 50 );

		/**
		 * You can prefix any string by calling
		 * `apply_filters( 'switchboard_prefix', $thing )`. For example:
		 *
		 * ```php
		 * get_header( apply_filters( 'switchboard_prefix', null ) );
		 * ```
		 *
		 * This will load header-{domain slug}.php if it exists, and header.php
		 * otherwise.
		 */
		add_filter( 'switchboard_prefix', [ $this, 'prefix' ] );
	}

	/**
	 * Filter `'template_include'` to add a prefix for the current domain slug.
	 *
	 * It's important to note that this only applies _after_ the template
	 * hierarchy does its work. In other words, if the template hierarchy
	 * decides that `category.php` is the best template for this page, this will
	 * look for `{domain slug}-category.php`, it *will not* also look for
	 * `{domain slug}-category-{$slug}.php`, etc.
	 *
	 * @todo maybe this should repeat the template hierarchy? Worth the overhead?
	 *
	 * @param  string $template Template being included.
	 * @return string Filtered template.
	 */
	public function template_include_prefix( $template ) {
		/**
		 * Filter to disable template overrides.
		 *
		 * @param bool $disable_templates Template won't be overridden if true.
		 *                                Defaults to false.
		 * @param string $template The template being loaded.
		 */
		if (
			false === strpos( $template, 'wp-content/themes/' )
			|| false !== strpos( $template, 'themes/vip/plugins/' )
			|| apply_filters( 'switchboard_disable_templates', false, $template )
		) {
			return $template;
		}

		// Ensure $template is valid and does not contain dir traversal. If it's
		// invalid, don't attempt to modify it -- the theme or a plugin is doing
		// something abnormal.
		if ( 0 !== validate_file( $template ) ) {
			return $template;
		}

		$templates = [];

		// Get the basename of the template.
		$file = str_replace( [ STYLESHEETPATH . '/', TEMPLATEPATH . '/' ], '', $template );
		$base = substr( $file, 0, -4 );

		// Maybe look in a subdirectory for the template.
		$site_subdir = apply_filters( 'switchboard_template_subdirectory', false, Core::instance()->get_current_site_term(), $template );
		if ( $site_subdir ) {
			$templates[] = trailingslashit( $site_subdir ) . $base . '.php';
		}

		// Look for {slug}-{basename}.php, e.g. domain-single.php.
		$templates[] = $this->prefix( $base ) . '.php';

		// If we found a better template, return it. Otherwise, return the one
		// we were passed.
		$new_template = locate_template( $templates );

		if ( $new_template ) {
			return $new_template;
		} else {
			return $template;
		}
	}

	/**
	 * Prefix something with the current domain (and a hyphen). This is useful
	 * with templates.
	 *
	 * @param  string|null $name Optional. String to prefix with `{slug}-`. If
	 *                           null, only the slug will be returned.
	 * @return string|null
	 */
	public function prefix( $name = null ) {
		// Get the site term for the current domain.
		$slug = Core::instance()->get_current_site_term( 'slug' );

		if ( ! $slug ) {
			return $name;
		} elseif ( null === $name ) {
			return $slug;
		} else {
			return "{$slug}-{$name}";
		}
	}
}
