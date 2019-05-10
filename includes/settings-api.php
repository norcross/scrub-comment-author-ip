<?php
/**
 * Hooking into the WP settings API.
 *
 * @package ScrubCommentAuthorIP
 */

// Call our namepsace.
namespace Norcross\ScrubCommentAuthorIP\SettingsAPI;

// Set our alias items.
use Norcross\ScrubCommentAuthorIP as Core;
use Norcross\ScrubCommentAuthorIP\Helpers as Helpers;
use Norcross\ScrubCommentAuthorIP\Database as Database;

/**
 * Start our engines.
 */
add_action( 'admin_init', __NAMESPACE__ . '\load_comment_settings' );

/**
 * Add a checkbox to the comment settings for removing IP addresses.
 *
 * @return void
 */
function load_comment_settings() {

	// Add out checkbox with a sanitiation callback.
	register_setting( 'discussion', 'ip_scrub_enable', __NAMESPACE__ . '\sanitize_scrub_setting' );

	// Load the actual field itself.
	add_settings_field( 'ip-scrub-enable', __( 'Scrub Comment IPs', 'scrub-comment-author-ip' ), __NAMESPACE__ . '\display_field', 'discussion',  'default' );
}

/**
 * Display a basic checkbox for our setting.
 *
 * @return HTML
 */
function display_field() {

	// Set a label with our default IP.
	$set_label  = sprintf( __( 'Replace the comment author IP address with %s', 'scrub-comment-author-ip' ), '<code>' . esc_attr( Helpers\fetch_masked_ip() ) . '</code>' );

	// Add a fieldset output.
	echo '<fieldset>';
		echo '<legend class="screen-reader-text"><span>' . __( 'Scrub Comment IPs', 'scrub-comment-author-ip' ) . '</span>';
	echo '</legend>';

	echo '<label for="ip_scrub_enable">';
		echo '<input name="ip_scrub_enable" type="checkbox" id="ip_scrub_enable" value="1" ' . checked( 'yes', get_option( 'ip_scrub_enable' ) ) . ' />';
		echo wp_kses_post( $set_label );
	echo '</label>';
}

/**
 * Make sure the setting is valid.
 *
 * @param  string	$input	The data entered in a settings field.
 *
 * @return string	$input	Our cleaned up data.
 */
function sanitize_scrub_setting( $input ) {
	return ! empty( $input ) ? 'yes' : 'no';
}
