<?php
/**
 * This file sets up the custom autoloader.
 *
 * @package Split Domain
 */

namespace Split_Domain;

/**
 * Autoload classes.
 *
 * @param  string $cls Class name.
 */
function autoload( $cls ) {
	$cls = ltrim( $cls, '\\' );
	if ( strpos( $cls, 'Split_Domain\\' ) !== 0 ) {
		return;
	}

	$cls = strtolower( str_replace( [ 'Split_Domain\\', '_' ], [ '', '-' ], $cls ) );
	$dirs = explode( '\\', $cls );
	$cls = array_pop( $dirs );

	require_once( PATH . rtrim( '/lib/' . implode( '/', $dirs ), '/' ) . '/class-' . $cls . '.php' );
}
spl_autoload_register( '\Split_Domain\autoload' );
