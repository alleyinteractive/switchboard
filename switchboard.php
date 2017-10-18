<?php
/**
 * Plugin Name:     Switchboard
 * Plugin URI:      https://github.com/alleyinteractive/switchboard
 * Description:     Serve multiple domains with one WordPress site.
 * Author:          Matthew Boynes
 * Author URI:      https://www.alleyinteractive.com/
 * Text Domain:     switchboard
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Switchboard
 */

namespace Switchboard;

define( __NAMESPACE__ . '\PATH', __DIR__ );
define( __NAMESPACE__ . '\URL', trailingslashit( plugins_url( '', __FILE__ ) ) );

// Custom autoloader.
require_once( PATH . '/lib/autoload.php' );

// Singleton trait.
require_once( PATH . '/lib/trait-singleton.php' );

// Setup actions to autoload everything else.
add_action( 'after_setup_theme', [ '\Switchboard\Core', 'instance' ] );
add_action( 'after_setup_theme', [ '\Switchboard\Site', 'instance' ] );
add_action( 'after_setup_theme', [ '\Switchboard\Templates', 'instance' ] );
add_action( 'after_setup_theme', [ '\Switchboard\Settings', 'instance' ] );
