<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Switchboard
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

class Switchboard_Redirect_Exception extends \Exception {}

/**
 * Override wp_safe_redirect() to throw an exception.
 *
 * @param  string  $location Redirect location.
 * @param  int     $status   HTTP status code.
 * @throws \Switchboard_Redirect_Exception
 */
function wp_safe_redirect( $location, $status = 302 ) {
	throw new \Switchboard_Redirect_Exception( $location, $status );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/switchboard.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
