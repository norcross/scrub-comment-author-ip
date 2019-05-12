<?php
/**
 * Our uninstall call.
 *
 * @package ScrubCommentAuthorIP
 */

// Declare our namespace.
namespace Norcross\ScrubCommentAuthorIP\Uninstall;

// Set our aliases.
use Norcross\ScrubCommentAuthorIP as Core;

/**
 * Delete various options when uninstalling the plugin.
 *
 * @return void
 */
function uninstall() {

	// Delete the option.
	delete_option( Core\OPTION_KEY );

	// Include our action so that we may add to this later.
	do_action( Core\HOOK_PREFIX . 'uninstall_process' );

	// And flush our rewrite rules.
	flush_rewrite_rules();
}
register_uninstall_hook( Core\FILE, __NAMESPACE__ . '\uninstall' );
