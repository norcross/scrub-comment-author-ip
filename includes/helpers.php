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
