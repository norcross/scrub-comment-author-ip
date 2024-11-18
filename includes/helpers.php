<?php
/**
 * Our helper functions to use across the plugin.
 *
 * @package ScrubCommentAuthorIP
 */

// Call our namepsace.
namespace Norcross\ScrubCommentAuthorIP\Helpers;

// Set our alias items.
use Norcross\ScrubCommentAuthorIP as Core;

// And pull in any other namespaces.
use WP_Error;

/**
 * Check the setting to see if we have enabled it.
 *
 * @param  string $return_type  How to return the option. Accepts "boolean" or "string".
 *
 * @return mixed
 */
function maybe_scrub_enabled( $return_type = 'string' ) {

	// First get the option key itself.
	$set_option = get_option( Core\OPTION_KEY, 'no' );

	// Switch through our return types.
	switch ( esc_attr( $return_type ) ) {

		// Handle the boolean return.
		case 'bool' :
		case 'boolean' :

			// Check for the stored "yes" to return.
			return ! empty( $set_option ) && 'yes' === sanitize_text_field( $set_option ) ? true : false;

			// Done.
			break;

		// Handle my yes / no string return.
		case 'string' :

			// Check for the stored "yes" to return.
			return ! empty( $set_option ) && 'yes' === sanitize_text_field( $set_option ) ? 'yes' : 'no';

			// Done.
			break;
	}

	// Return an error set because they done messed up.
	return new WP_Error( 'invalid_return_type', __( 'You requested an invalid return type.', 'scrub-comment-author-ip' ) );
}

/**
 * Set up the IP address to use as a mask in the database.
 *
 * @return string
 */
function fetch_masked_ip() {

	// Set up the masked IP address we want to use.
	$masked_ip  = apply_filters( Core\HOOK_PREFIX . 'default_masked_ip', '127.0.0.1' );

	// Confirm it's a valid IP before returning it.
	return ! empty( $masked_ip ) && filter_var( $masked_ip, FILTER_VALIDATE_IP ) ? $masked_ip : '127.0.0.1';
}

/**
 * Get the URL for our settings page with any custom args.
 *
 * @param  array  $args  The possible array of args.
 *
 * @return string
 */
function fetch_settings_url( $args = [] ) {

	// If we have no args, just do the basic link.
	if ( empty( $args ) ) {
		return admin_url( 'options-discussion.php' );
	}

	// Now return it in args.
	return add_query_arg( $args, admin_url( 'options-discussion.php' ) );
}
