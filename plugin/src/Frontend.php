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
 * Frontend class
 *
 * Loads frontend functions
 *
 * @since 1.0.0
 */
class Frontend {


	/**
	 * Frontend constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// add plugin shortcode
		add_shortcode( 'edd_simple_registration', [ $this, 'render_shortcode_content' ] );

		// handle processing the custom registration form
		// we can't leverage the standard form action due to its username requirement
		add_action( 'edd_simple_user_registration', [ $this, 'process_user_registration' ] );

		// save name inputs if included in registration forms
		add_action( 'edd_insert_user', [ $this, 'save_name_inputs' ] );
	}


	/**
	 * Render the registration template.
	 *
	 * @since 1.0.0
	 *
	 * @return string template HTML
	 */
	public function render_shortcode_content( $atts ) {
		global $wp, $edd_simple_registration_options, $edd_register_redirect;

		// set the redirect to the current URL
		$edd_register_redirect = home_url( $wp->request );

		// allow changing the default settings via shortcode attribute
		$show_names    = 'disabled' !== edd_get_option( 'srfe_name_fields', 'disabled' );
		$require_names = 'required' === edd_get_option( 'srfe_name_fields', 'disabled' );

		$atts = shortcode_atts( [
			'button'        => __( 'Register', 'simple-registration-for-edd' ),
			'show_names'    => $show_names ? 'yes' : 'no',
			'require_names' => $require_names ? 'yes' : 'no',
		], $atts );

		ob_start();

		if ( ! is_user_logged_in() ) {

			$edd_simple_registration_options = [
				'show_names'          => 'yes' === $atts['show_names'],
				'require_names'       => 'yes' === $atts['require_names'],
				'button_text'         => $atts['button'],
				'show_privacy_policy' => '1' === edd_get_option( 'srfe_generate_show_privacy_policy', 'no' ),
				'offer_password'      => 'no' === edd_get_option( 'srfe_generate_pw', 'no' ),
			];

			$this->load_template( 'registration-form.php' );

		} else {

			$user_message = '';

			if ( isset( $_GET['simple_registration_completed'] ) ) {
				$user_message .= '<p><strong>' . esc_html__( 'Your password has been automatically generated. Please reset it the next time you log in.', 'simple-registration-for-edd' ) . '</strong></p>';
			}

			$purchase_history_url  = get_permalink( edd_get_option( 'purchase_history_page', false ) );
			$user_message         .= sprintf( esc_html__( 'Welcome! You can %1$sview your account here%2$s.', 'simple-registration-for-edd' ), '<a href="' . esc_url( $purchase_history_url ) . '">', '</a>' );

			/**
			 * Filters the message shown to logged in users.
			 *
			 * @since 1.0.0
			 *
			 * @param string $user_message the message for logged in users.
			 */
			echo apply_filters( 'simple_registration_for_edd_logged_in_message', $user_message );
		}

		$return = ob_get_contents();
		ob_end_clean();

		return $return;
	}


	/**
	 * Processes registration from our custom form.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data the form data
	 */
	public function process_user_registration( $data ) {

		// bail for logged in users or missing form action
		if ( is_user_logged_in() || empty( $_POST['edd_simple_registration_submit'] ) ) {
			return;
		}

		// check for honeypot fields, so long bots!
		if ( ! empty( $_POST['email'] ) || ! empty( $_POST['name'] ) ) {
			edd_set_error( 'honeypot_submitted', __( 'Error registering your account, please try again.', 'simple-registration-for-edd' ) );
		}

		/** @see easy-digital-downloads/includes/login-register.php */
		do_action( 'edd_pre_process_register_form' );

		// Use of EDD textdomains is intentional here to avoid duplication
		if ( email_exists( $data['edd_user_email'] ) ) {
			edd_set_error( 'email_unavailable', __( 'Email address already taken', 'easy-digital-downloads' ) );
		}

		if ( empty( $data['edd_user_email'] ) || ! is_email( $data['edd_user_email'] ) ) {
			edd_set_error( 'email_invalid', __( 'Invalid email', 'easy-digital-downloads' ) );
		}

		/** @see easy-digital-downloads/includes/login-register.php */
		do_action( 'edd_process_register_form' );

		// Check for errors and redirect if none present
		$errors = edd_get_errors();

		if ( empty( $errors ) ) {

			$redirect = apply_filters( 'edd_register_redirect', $data['edd_redirect'] );
			$username = $this->generate_username( sanitize_text_field( $data['edd_user_email'] ) );

			edd_register_and_login_new_user( [
				'user_login'      => $username,
				'user_pass'       => ! empty( $data['edd_user_pass'] ) ? sanitize_text_field( $data['edd_user_pass'] ) : wp_generate_password( 32 ),
				'user_email'      => $data['edd_user_email'],
				'user_registered' => date( 'Y-m-d H:i:s' ),
				'role'            => get_option( 'default_role' )
			] );

			wp_redirect( add_query_arg( [ 'simple_registration_completed' => 'yes' ], $redirect) );
			edd_die();
		}
	}


	/**
	 * Save first and last name fields to customer profiles if enabled.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id the ID of the new user
	 */
	public function save_name_inputs( $user_id ) {

		$user      = get_userdata( $user_id );
		$user_data = [ 'ID' => $user_id ];

		if ( isset( $_POST['edd_registration_fname'] ) && ! empty( $_POST['edd_registration_fname'] ) ) {

			$user_data['first_name'] = sanitize_text_field( $_POST['edd_registration_fname'] );
		}

		if ( isset( $_POST['edd_registration_lname'] ) && ! empty( $_POST['edd_registration_lname'] ) ) {

			$user_data['last_name'] = sanitize_text_field( $_POST['edd_registration_lname'] );
		}

		// set display name to first name to start
		$user_data['display_name'] = isset( $user_data['first_name'] ) ? $user_data['first_name'] : $user->user_login;

		// if we have a full name, set that as display name, and let translators adjust the name
		/* translators: Placeholders: %1$s - first or given name, %2$s - surname or last name */
		$user_data['display_name'] = isset( $user_data['first_name'], $user_data['last_name'] ) ? sprintf( _x( '%1$s %2$s', 'User full name', 'simple-registration-for-edd' ), $user_data['first_name'], $user_data['last_name'] ) : $user_data['display_name'];

		wp_update_user( $user_data );
	}


	/**
	 * Helper to create a new username.
	 *
	 * @since 1.0.0
	 *
	 * @param string $email user's billing email
	 * @return string the new username
	 */
	private function generate_username( $email ) {

		$username = sanitize_user( current( explode( '@', $email ) ), true );

		// ensure username is unique
		$append     = 1;
		$o_username = $username;

		while ( username_exists( $username ) ) {

			$username = $o_username . $append;
			$append++;
		}

		return $username;
	}


	/**
	 * Locates the EDD template file from our templates directory.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name the template file name
	 */
	private function load_template( $name ) {

		// set the path to our templates directory
		$plugin_path = untrailingslashit( edd_simple_registration()->get_plugin_path() ) . '/templates/';

		// if a template is found, make it so
		if ( is_readable( $plugin_path . $name ) ) {

			$template = $plugin_path . $name;
			load_template( $template );
		}
	}


}
