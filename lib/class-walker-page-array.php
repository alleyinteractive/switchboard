<?php
/**
 * Walker_Page_Array Class
 *
 * @package Switchboard
 */

namespace Switchboard;

/**
 * Class used to create a hierarchical array of pages for use in
 * Fieldmanager_Select fields.
 *
 * @see \Walker
 */
class Walker_Page_Array extends \Walker {

	/**
	 * What the class handles.
	 *
	 * @var string
	 *
	 * @see \Walker::$tree_type
	 */
	public $tree_type = 'page';

	/**
	 * Database fields to use.
	 *
	 * @var array
	 *
	 * @see \Walker::$db_fields
	 */
	public $db_fields = [
		'parent' => 'post_parent',
		'id'     => 'ID',
	];

	/**
	 * Starts the element output.
	 *
	 * @see \Walker::start_el()
	 *
	 * @param string  $output Used to append additional content. Passed by reference.
	 * @param WP_Post $page   Page data object.
	 * @param int     $depth  Optional. Depth of page in reference to parent pages. Used for padding.
	 *                        Default 0.
	 * @param array   $args   Optional. Uses 'selected' argument for selected page to set selected HTML
	 *                        attribute for option element. Uses 'value_field' argument to fill "value"
	 *                        attribute. See wp_dropdown_pages(). Default empty array.
	 * @param int     $id     Optional. ID of the current page. Default 0 (unused).
	 */
	public function start_el( &$output, $page, $depth = 0, $args = array(), $id = 0 ) {
		$pad = str_repeat('&nbsp;', $depth * 3);

		$title = $page->post_title;
		if ( '' === $title ) {
			/* translators: %d: ID of a post */
			$title = sprintf( __( '#%d (no title)', 'switchboard' ), $page->ID );
		}
		$output .= sprintf( '"%s":"%s",', $page->ID, $pad . addcslashes( $title, '"' ) );
	}
}
