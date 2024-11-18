<?php
/**
 * Our activation call
 *
 * @package ScrubCommentAuthorIP
 */

// Declare our namespace.
namespace Norcross\ScrubCommentAuthorIP\Activate;

// Set our aliases.
use Norcross\ScrubCommentAuthorIP as Core;

/**
 * Our inital setup function when activated.
 *
 * @return void
 */
function activate() {

	// Set our initial option.
	update_option( Core\OPTION_KEY, 'yes', false );

	// Include our action so that we may add to this later.
	do_action( Core\HOOK_PREFIX . 'activate_process' );
}
register_activation_hook( Core\FILE, __NAMESPACE__ . '\activate' );
