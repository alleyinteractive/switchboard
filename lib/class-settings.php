<?php
/**
 * This file contains the singleton class for Settings.
 *
 * @package  Switchboard
 */

namespace Switchboard;

/**
 * Settings
 */
class Settings {
	use Singleton;

	/**
	 * The option_name in which to store these settings.
	 *
	 * @var string
	 */
	protected $option_name = 'site_domain_settings';

	/**
	 * Setup the singleton.
	 */
	public function setup() {
		add_action( 'fm_submenu_site_domain_settings', [ $this, 'fields' ] );
		if ( function_exists( 'fm_register_submenu_page' ) ) {
			fm_register_submenu_page( $this->option_name, 'switchboard', __( 'Site Domain Settings', 'switchboard' ), __( 'Settings', 'switchboard' ), 'manage_options' );
		}
	}

	/**
	 * `site_domain_settings` Fieldmanager fields.
	 */
	public function fields() {
		$children = array(
			'default' => new \Fieldmanager_Select( array(
				'label' => __( 'Default Domain', 'switchboard' ),
				'datasource' => new \Fieldmanager_Datasource_Term( array(
					'taxonomy' => Site::instance()->name,
				) ),
			) ),
		);

		$fm = new \Fieldmanager_Group( array(
			'name' => $this->option_name,
			'children' => apply_filters( 'site_domain_settings_children_fields', $children ),
		) );
		$fm->activate_submenu_page();
	}

	/**
	 * Get the settings or an individual setting.
	 *
	 * @param  string $key Optional. If set, the sub-setting to retrieve.
	 * @return mixed
	 */
	public function get_setting( $key = null ) {
		$settings = get_option( $this->option_name, [] );

		if ( ! $key ) {
			return $settings;
		} elseif ( isset( $settings[ $key ] ) ) {
			return $settings[ $key ];
		} else {
			return false;
		}
	}
}
add_action( 'after_setup_theme', [ 'Settings', 'instance' ] );
