<?php
namespace Replace_Me;

/**
 * Autoload classes.
 *
 * @param  string $cls Class name.
 */
function autoload( $cls ) {
	$cls = ltrim( $cls, '\\' );
	if ( strpos( $cls, 'Replace_Me\\' ) !== 0 ) {
		return;
	}

	$cls = strtolower( str_replace( [ 'Replace_Me\\', '_' ], [ '', '-' ], $cls ) );
	$dirs = explode( '\\', $cls );
	$cls = array_pop( $dirs );

	require_once( PATH . rtrim( '/lib/' . implode( '/', $dirs ), '/' ) . '/class-' . $cls . '.php' );
}
spl_autoload_register( '\Replace_Me\autoload' );
