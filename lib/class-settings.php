<?php
/**
 * This file contains the singleton class for Settings.
 *
 * @package  Split Domain
 */

namespace Split_Domain;

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
			fm_register_submenu_page( $this->option_name, 'split-domain', __( 'Site Domain Settings', 'split-domain' ), __( 'Settings', 'split-domain' ), 'manage_options' );
		}
	}

	/**
	 * `site_domain_settings` Fieldmanager fields.
	 */
	public function fields() {
		$fm = new \Fieldmanager_Group( array(
			'name' => $this->option_name,
			'children' => array(
				'default' => new \Fieldmanager_Select( array(
					'label' => __( 'Default Domain', 'split-domain' ),
					'datasource' => new \Fieldmanager_Datasource_Term( array(
						'taxonomy' => Site::instance()->name,
					) ),
				) ),
			),
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
