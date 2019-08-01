<?php
/**
 * Simple registration for EDD
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * @author    SkyVerge
 * @copyright Copyright (c) 2019, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

/**
 * Renders the registration form via shortcode or widget.
 *
 * This is basically a duplicate of templates/shortcode-register.php, with some exceptions:
 * - registration form is always displayed
 * - login form is not shown
 * - translations are escaped
 *
 * Note that the use of 'easy-digital-downloads' textdomain is intentional!
 *
 * @version 1.0.0
 * @since 1.0.0
 */

defined( 'ABSPATH' ) or exit;

global $edd_simple_registration_options, $edd_register_redirect;

do_action( 'edd_print_errors' );
?>

<?php do_action( 'edd_before_customer_login_form' ); ?>

<style>
	.edd_gotcha{
		opacity: 0;
		position: absolute;
		top: 0;
		left: 0;
		height: 0;
		width: 0;
		z-index: -1;
	}
</style>

<div class="u-column2 col-2 simple-registration-edd registration-form edd">

	<form id="edd_register_form" class="edd_form" action="" method="post">

		<?php do_action( 'edd_register_form_fields_top' ); ?>

		<fieldset>

			<?php do_action( 'edd_register_form_fields_before' ); ?>

			<?php if ( $edd_simple_registration_options['show_names'] ) : ?>
				<p class="edd-FormRow edd-FormRow--first form-row form-row-first">
					<label for="edd_registration_fname"><?php esc_html_e( 'First name', 'simple-registration-for-edd' ); ?><?php echo $edd_simple_registration_options['require_names'] ? ' <span class="required" aria-label=" ' . esc_attr( __( 'Required input', 'simple-registration-for-edd' ) ) . '">*</span>' : ''; ?></label>
					<input type="text" class="edd-Input edd-Input--text input-text" name="edd_registration_fname" id="edd_registration_fname" value="<?php if ( ! empty( $_POST['edd_registration_fname'] ) ) echo esc_attr( $_POST['edd_registration_fname'] ); ?>" <?php echo $edd_simple_registration_options['require_names'] ? ' required' : ''; ?>/>
				</p>

				<p class="edd-FormRow edd-FormRow--last form-row form-row-last">
					<label for="edd_registration_lname"><?php esc_html_e( 'Last name', 'simple-registration-for-edd' ); ?><?php echo $edd_simple_registration_options['require_names'] ? ' <span class="required" aria-label=" ' . esc_attr( __( 'Required input', 'simple-registration-for-edd' ) ) . '">*</span>' : ''; ?></label>
					<input type="text" class="edd-Input edd-Input--text input-text" name="edd_registration_lname" id="edd_registration_lname" value="<?php if ( ! empty( $_POST['edd_registration_lname'] ) ) echo esc_attr( $_POST['edd_registration_lname'] ); ?>" <?php echo $edd_simple_registration_options['require_names'] ? ' required' : ''; ?>/>
				</p>
			<?php endif; ?>

			<p class="edd-form-row edd-form-row--wide form-row form-row-wide">
				<label for="edd-user-email"><?php esc_html_e( 'Email', 'easy-digital-downloads' ); ?>&nbsp;<span class="required" aria-label="<?php echo esc_attr( __( 'Required input', 'simple-registration-for-edd' ) ); ?>">*</span></label>
				<input id="edd-user-email" class="required edd-input" type="email" name="edd_user_email" value="<?php echo ( ! empty( $_POST['edd_user_email'] ) ) ? esc_attr( wp_unslash( $_POST['edd_user_email'] ) ) : ''; ?>" />
			</p>

			<?php if ( $edd_simple_registration_options['offer_password'] ) : ?>

				<p>
					<label for="edd-user-pass"><?php esc_html_e( 'Password', 'easy-digital-downloads' ); ?>&nbsp;<span class="required" aria-label="<?php echo esc_attr( __( 'Required input', 'simple-registration-for-edd' ) ); ?>">*</span></label>
					<input id="edd-user-pass" class="password required edd-input" type="password" name="edd_user_pass" />
				</p>

			<?php endif; ?>

			<?php if ( $edd_simple_registration_options['show_privacy_policy'] ) : // show privacy policy text ?>
				<p><?php esc_html_e( 'Example privacy text', 'simple-registration-for-edd' ); ?></p>
			<?php endif; ?>

			<?php do_action( 'edd_register_form_fields_before_submit' ); ?>

			<?php // spam Trap ?>
			<p>
				<label class="edd_gotcha" for="name">Name</label>
				<input class="edd_gotcha" autocomplete="off" type="text" id="name" name="name" placeholder="Your name here">
			</p>

			<p>
				<label class="edd_gotcha" for="email">Email</label>
				<input class="edd_gotcha" autocomplete="off" type="email" id="email" name="email" placeholder="Your e-mail here">
			</p>
			<?php // end spam Trap ?>

			<p>
				<input type="hidden" name="edd_action" value="simple_user_registration" />
				<input type="hidden" name="edd_redirect" value="<?php echo esc_url( $edd_register_redirect ); ?>"/>
				<input class="edd-submit" name="edd_simple_registration_submit" type="submit" value="<?php echo esc_attr( $edd_simple_registration_options['button_text'] ); ?>" />
			</p>

			<?php do_action( 'edd_register_form_fields_after' ); ?>

		</fieldset>

		<?php do_action( 'edd_register_form_fields_bottom' ); ?>

	</form>
</div>
