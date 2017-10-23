=== Switchboard ===
Contributors: mboynes, alleyinteractive
Tags: domains, multiple domains, multiple sites, syndication
Requires at least: 4.6
Tested up to: 4.8.2
Stable tag: 0.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin allows a single (non-multisite) WordPress site to handle multiple domains.

== Description ==

Switchboard lets your non-multisite WordPress site serve content across many domains. Create one piece of content (like a post or page) and it can be used on any number of different sites. SEO-friendly, built-in support for domain-based templates, simple to use and to extend.

= Requirements =

* Requires PHP 5.4 or higher.
* You must be able to make changes to your `wp-config.php` file.
* [Fieldmanager](https://github.com/alleyinteractive/wordpress-fieldmanager/)

== Installation ==

1. Upload the plugin to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. In your `wp-config.php` file, add the following block to allow your site to respond to arbitrary domains:

    ```
    if ( isset( $_SERVER['HTTP_HOST'] ) ) {
    	$scheme = 'http';

    	// If we have detected that the end use is HTTPS, make sure we pass that
    	// through here, so <img> tags and the like don't generate mixed-mode
    	// content warnings.
    	if ( isset( $_SERVER['HTTP_USER_AGENT_HTTPS'] ) && 'ON' === $_SERVER['HTTP_USER_AGENT_HTTPS'] ) {
    		$scheme = 'https';
    	}
    	define( 'WP_HOME',    $scheme . '://' . $_SERVER['HTTP_HOST'] );
    	define( 'WP_SITEURL', $scheme . '://' . $_SERVER['HTTP_HOST'] );
    }
    ```
