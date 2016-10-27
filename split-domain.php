<?php
/**
 * Plugin Name:     Split Domain
 * Plugin URI:      https://github.com/alleyinteractive/split-domain
 * Description:     Serve multiple domains with one site.
 * Author:          Matthew Boynes
 * Author URI:      https://www.alleyinteractive.com/
 * Text Domain:     split-domain
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Split Domain
 */

namespace Split_Domain;

define( __NAMESPACE__ . '\PATH', __DIR__ );
define( __NAMESPACE__ . '\URL', trailingslashit( plugins_url( '', __FILE__ ) ) );

// Custom autoloader.
require_once( PATH . '/lib/autoload.php' );

// Singleton trait.
require_once( PATH . '/lib/trait-singleton.php' );

// Setup actions to autoload everything else.
add_action( 'after_setup_theme', [ '\Split_Domain\Site', 'instance' ] );
