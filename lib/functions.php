<?php
/**
 * This file contains helper functions for the plugin.
 *
 * Each of these helpers can be called directly, e.g.:
 *
 *     $site_term = \Switchboard\get_current_site_term();
 *     $site_slug = \Switchboard\get_current_site_term( 'slug' );
 *
 * However, to make this plugin safer to integrate with themes and plugins, it's
 * also possible to call each of the helpers via a custom filter of the same
 * name with a 'sbh:' prefix. Simply pass 'null' (or a fallback value of your
 * choosing) as the first filter argument, then the remaining function params as
 * appropriate. For instance,
 *
 *     $site_term = apply_filters( 'sbh:get_current_site_term', null );
 *     $site_slug = apply_filters( 'sbh:get_current_site_term', null, 'slug' );
 *
 * @package Switchboard
 */

namespace Switchboard;

/**
 * @see Core::get_current_site_term().
 */
function get_current_site_term( $property = null ) {
	return Core::instance()->get_current_site_term( $property );
}
add_filter( 'sbh:get_current_site_term', __NAMESPACE__ . '\get_current_site_term', 10, 2 );

/**
 * @see Core::get_post_primary_site().
 */
function get_post_primary_site( $post = null ) {
	return Core::get_post_primary_site( $post );
}
add_filter( 'sbh:get_post_primary_site', __NAMESPACE__ . '\get_post_primary_site', 10, 2 );

/**
 * @see Core::get_sites_for_post().
 */
function get_sites_for_post( $post = null ) {
	return Core::get_sites_for_post( $post );
}
add_filter( 'sbh:get_sites_for_post', __NAMESPACE__ . '\get_sites_for_post', 10, 2 );

/**
 * @see Core::is_post_allowed_on_current_site().
 */
function is_post_allowed_on_current_site( $post = null ) {
	return Core::is_post_allowed_on_current_site( $post );
}
add_filter( 'sbh:is_post_allowed_on_current_site', __NAMESPACE__ . '\is_post_allowed_on_current_site', 10, 2 );

/**
 * @see Core::is_post_allowed_on_site().
 */
function is_post_allowed_on_site( $post, $site_id ) {
	return Core::is_post_allowed_on_site( $post, $site_id );
}
add_filter( 'sbh:is_post_allowed_on_site', __NAMESPACE__ . '\is_post_allowed_on_site', 10, 3 );

/**
 * @see Core::get_post_site().
 */
function get_post_site( $post = null, $prefer = 'current' ) {
	return Core::get_post_site( $post, $prefer );
}
add_filter( 'sbh:get_post_site', __NAMESPACE__ . '\get_post_site', 10, 3 );

/**
 * @see Core::get_default_site().
 */
function get_default_site() {
	return Core::get_default_site();
}
add_filter( 'sbh:get_default_site', __NAMESPACE__ . '\get_default_site' );

/**
 * Get the raw "Default Domain" setting as a term id.
 *
 * @return int Domain term id.
 */
function get_default_site_id() {
	return intval( Settings::instance()->get_setting( 'default' ) );
}
add_filter( 'sbh:get_default_site_id', __NAMESPACE__ . '\get_default_site_id' );
