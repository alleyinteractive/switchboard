<?php
/**
 * This file contains assorted compatibility fixes
 *
 * @package Switchboard
 */

namespace Switchboard;

// Register hooks.
add_action( 'after_setup_theme', __NAMESPACE__ . '\compat_init', 50 );
add_action( 'switchboard_compat_current_site_is_not_default', __NAMESPACE__ . '\redirect_jp_and_vp_requests_to_primary_domain' );
add_action( 'switchboard_compat_current_site_is_not_default', __NAMESPACE__ . '\disable_jetpack_sync' );
add_action( 'switchboard_compat_current_site_is_not_default', __NAMESPACE__ . '\disable_vaultpress' );

/**
 * Basic check for VaultPress and Jetpack requests, ensure they always go to the
 * default domain.
 */
function redirect_jp_and_vp_requests_to_primary_domain() {
	if (
		// Matches condition in vaultpress/vaultpress.php that invokes its request handling.
		( isset( $_GET['vaultpress'] ) && $_GET['vaultpress'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		// Matches condition in jetpack/class.jetpack.php that invokes its XML-RPC listener.
		|| ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST && isset( $_GET['for'] ) && 'jetpack' === $_GET['for'] )
	) {
		add_action( 'init', [ Core::instance(), 'redirect_to_default_domain' ], 100 );
	}
}

/**
 * Compatibility initializer. This fires actions that other compatibility code
 * can leverage, indicating whether or not the current site is the default
 * (which is the primary source of compatibility issues).
 */
function compat_init() {
	$current_site = (int) get_current_site_term( 'term_id' );
	$default_site = (int) Settings::instance()->get_setting( 'default' );

	if ( $current_site === $default_site ) {
		/**
		 * Tell compatibility functions that the current site is the default.
		 *
		 * Important! The term ids are provided for reference, but note that the
		 * taxonomy hasn't been registered yet and won't be until `init`.
		 *
		 * @param int $current_site The term ID of the current site.
		 * @param int $default_site The term ID of the default site.
		 */
		do_action(
			'switchboard_compat_current_site_is_default',
			$current_site,
			$default_site
		);
	} else {
		/**
		 * Tell compatibility functions that the current site is not the default.
		 *
		 * Important! The term ids are provided for reference, but note that the
		 * taxonomy hasn't been registered yet and won't be until `init`.
		 *
		 * @param int $current_site The term ID of the current site.
		 * @param int $default_site The term ID of the default site.
		 */
		do_action(
			'switchboard_compat_current_site_is_not_default',
			$current_site,
			$default_site
		);
	}
}

/**
 * Disable Jetpack's shutdown syncing on secondary domains.
 */
function disable_jetpack_sync() {
	add_filter( 'jetpack_sync_sender_should_load', '__return_false', 999999 );
}

/**
 * Disable VaultPress' shutdown pings on secondary domains.
 */
function disable_vaultpress() {
	global $vaultpress;
	if (
		$vaultpress
		&& class_exists( '\VaultPress' )
		&& $vaultpress instanceof \VaultPress
	) {
		remove_action( 'shutdown', array( $vaultpress, 'do_pings' ) );
	}
}
