<?php
/**
 * Our deactivation call
 *
 * @package ScrubCommentAuthorIP
 */

// Declare our namespace.
namespace Norcross\ScrubCommentAuthorIP\Deactivate;

// Set our aliases.
use Norcross\ScrubCommentAuthorIP as Core;

/**
 * Delete various options when deactivating the plugin.
 *
 * @return void
 */
function deactivate() {

	// Include our action so that we may add to this later.
	do_action( Core\HOOK_PREFIX . 'deactivate_process' );

	// And flush our rewrite rules.
	flush_rewrite_rules();
}
register_deactivation_hook( Core\FILE, __NAMESPACE__ . '\deactivate' );
