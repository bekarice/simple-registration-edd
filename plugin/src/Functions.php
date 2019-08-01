<?php
/**
 * Simple registration for EDD
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   Simple_Registration/Plugin/Src
 * @author    SkyVerge
 * @category  Admin
 * @copyright Copyright (c) 2019, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

/**
 * Returns the One True Instance of Simple Registration for EDD.
 *
 * @since 1.0.0
 *
 * @return SkyVerge\EDD\Simple_Registration\Plugin
 */
function edd_simple_registration() {
	return \SkyVerge\EDD\Simple_Registration\Plugin::instance();
}
