<?php
/**
 * This file sets up the custom autoloader.
 *
 * @package Switchboard
 */

namespace Switchboard;

/**
 * Autoload classes.
 *
 * @param  string $cls Class name.
 */
function autoload( $cls ) {
	$cls = ltrim( $cls, '\\' );
	if ( strpos( $cls, 'Switchboard\\' ) !== 0 ) {
		return;
	}

	$cls = strtolower( str_replace( [ 'Switchboard\\', '_' ], [ '', '-' ], $cls ) );
	$dirs = explode( '\\', $cls );
	$cls = array_pop( $dirs );

	require_once( PATH . rtrim( '/lib/' . implode( '/', $dirs ), '/' ) . '/class-' . $cls . '.php' );
}
spl_autoload_register( '\Switchboard\autoload' );
