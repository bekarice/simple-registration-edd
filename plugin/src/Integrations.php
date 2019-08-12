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
 * Integrations class.
 *
 * @since 1.0.0
 */
class Integrations {


	/**
	 * Integrations constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// load this class after other plugins
		add_action( 'plugins_loaded', [ $this, 'init_class' ], 11 );
	}


	/**
	 * Add hooks if particular plugins are available.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function init_class() {

		// Jilt for EDD
		if ( function_exists( 'edd_jilt' ) ) {

			add_action( 'edd_insert_user', [ $this, 'update_jilt_customer' ], 15 ); // keep this > priority 10
		}
	}


	/**
	 * Update the customer record in Jilt with marketing opt in.
	 *
	 * @internal
	 *
	 * @since 1.0.1-dev.1
	 *
	 * @param int $user_id the newly created customer's ID
	 */
	public function update_jilt_customer( $user_id ) {

		// only make changes for our form submission
		if ( get_user_meta( $user_id, '_created_via_simple_registration', true ) ) {

			$integration = edd_jilt()->get_integration();
			$api         = $integration->get_api();
			$user        = get_userdata( $user_id );

			if ( $integration->is_jilt_connected() ) {

				$token = is_string( $api->get_auth_token() ) ? $api->get_auth_token() : $api->get_auth_token()->get_token();
				$url = $this->get_customers_api_endpoint( $integration ) . str_replace( '.', '%2E', urlencode( $user->user_email ) );

				$args = [
					'method'       => 'PUT',
					'accept'       => 'application/json',
					'content-type' => 'application/x-www-form-urlencoded',
					'timeout'      => 3,
					'headers'      => [
						'x-jilt-shop-domain' => edd_jilt()->get_shop_domain(),
						'Authorization'      => $api->get_auth_scheme() . ' ' . $token,
					],
					'body' => [
						'accepts_marketing' => true,
					],
				];

				$response = wp_safe_remote_request( $url, $args );

				// we can't do anything with this data yet, but save it in case for GDPR
				$this->update_local_user_data( $user_id );
			}
		}
	}


	/**
	 * Returns the customer API endpoint for Jilt.
	 *
	 * @since 1.0.1-dev.1
	 *
	 * @param \EDD_Jilt_Integration $integration the Jilt integration class
	 * @return string the customers API endpoint
	 */
	private function get_customers_api_endpoint( $integration ) {

		return sprintf( '%s/shops/%s/customers/', $integration->get_api()->get_api_endpoint(), $integration->get_linked_shop_id() );
	}


	/**
	 * Update local customer meta with consent opt in info.
	 *
	 * @since 1.0.1-dev.1
	 *
	 * @param int $user_id the created customer's ID
	 */
	private function update_local_user_data( $user_id ) {

		$button_text = isset( $_POST['edd_simple_registration_submit'] ) ? sanitize_text_field( $_POST['edd_simple_registration_submit'] ) : __( 'Register', 'simple-registration-for-edd' );

		update_user_meta( $user_id, '_edd_jilt_accepts_marketing', true );
		update_user_meta( $user_id, '_edd_jilt_consent_context', 'simple_registration_form' );
		update_user_meta( $user_id, '_edd_jilt_consent_timestamp', date( 'Y-m-d\TH:i:s\Z', time() ) );
		update_user_meta( $user_id, '_edd_jilt_consent_notice', $button_text );

		if ( function_exists( 'edd_get_ip' ) ) {
			update_user_meta( $user_id, '_edd_jilt_consent_ip_address', edd_get_ip() );
		}
	}


}
