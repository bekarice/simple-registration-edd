<?php
/**
 * Simple registration for EDD
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @author    SkyVerge
 * @category  Admin
 * @copyright Copyright (c) 2019, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

namespace SkyVerge\EDD\Simple_Registration;

defined( 'ABSPATH' ) or exit;

/**
 * Admin class.
 *
 * @since 1.0.0
 */
class Admin {


	/**
	 * Admin constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// add our settings to WooCommerce > Settings > Account
		add_filter( 'edd_settings_extensions', [ $this, 'add_settings' ] );
	}


	/**
	 * Add our plugin settings under WooCommerce > Settings > Accounts.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings the EDD extension settings
	 * @return array updated settings
	 */
	public function add_settings( $settings ) {

		$new_settings = [
			[
				'id'   => 'simple_registration_for_edd_options',
				'name' => '<strong>' . __( 'Simple registration', 'simple-registration-for-edd' ) . '</strong>',
				'type' => 'header',
				'desc' => __( 'Determine which fields are shown on simple registration forms.', 'simple-registration-for-edd' ),
			],
			[
				'id'      => 'srfe_name_fields',
				'name'    => __( 'Show name fields', 'simple-registration-for-edd' ),
				'std'     => 'disabled',
				'type'    => 'select',
				'options' => [
					'disabled' => __( 'Do not show first and last name fields', 'simple-registration-for-edd' ),
					'enabled'  => __( 'Show optional first and last name fields', 'simple-registration-for-edd' ),
					'required' => __( 'Require first and last name fields', 'simple-registration-for-edd' ),
				],
				'tooltip_desc' => __( 'Determines whether these fields are shown and required.', 'simple-registration-for-edd' ),
			],
			[
				'id'   => 'srfe_generate_pw',
				'name' => __( 'Automatically generate passwords', 'simple-registration-for-edd' ),
				'desc' => __( 'Enable to generate passwords. Disable to offer a password input.', 'simple-registration-for-edd' ),
				'std'  => 'yes',
				'type' => 'checkbox',
			],
			[
				'id'   => 'srfe_generate_show_privacy_policy',
				'name' => __( 'Show privacy text', 'simple-registration-for-edd' ),
				'desc' => __( 'Enable to show privacy policy text after this form.', 'simple-registration-for-edd' ),
				'std'  => 'no',
				'type' => 'checkbox',
			],
		];

		return array_merge( $settings, $new_settings );
	}


}
