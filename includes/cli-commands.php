<?php
/**
 * The functionality tied to the WP-CLI stuff.
 *
 * @package ScrubCommentAuthorIP
 */

// Call our namepsace (same as the base).
namespace Norcross\ScrubCommentAuthorIP;

// Set our alias items.
use Norcross\ScrubCommentAuthorIP as Core;
use Norcross\ScrubCommentAuthorIP\Helpers as Helpers;
use Norcross\ScrubCommentAuthorIP\Database as Database;

// Pull in the CLI items.
use WP_CLI;
use WP_CLI_Command;

/**
 * Add a cleanup function to scrub IPs from existing comments.
 */
class ScrubCLICommands extends WP_CLI_Command {

	/**
	 * Get the array of arguments for the runcommand function.
	 *
	 * @param  array $custom  Any custom args to pass.
	 *
	 * @return array
	 */
	protected function get_command_args( $custom = array() ) {

		// Set my base args.
		$args   = array(
			'return'     => true,   // Return 'STDOUT'; use 'all' for full object.
			'parse'      => 'json', // Parse captured STDOUT to JSON array.
			'launch'     => false,  // Reuse the current process.
			'exit_error' => false,   // Halt script execution on error.
		);

		// Return either the base args, or the merged item.
		return ! empty( $custom ) ? wp_parse_args( $args, $custom ) : $args;
	}

	/**
	 * Swap out any existing IP data with our default.
	 *
	 * ## EXAMPLES
	 *
	 *     wp scrub-cli cleanup
	 *
	 * @when after_wp_load
	 */
	function cleanup() {

		// Check for it being enabled first.
		$is_enabled = Helpers\maybe_scrub_enabled( 'boolean' );

		// Bail if we aren't enabled.
		if ( false === $is_enabled ) {
			WP_CLI::error( __( 'The plugin setting has not been enabled.', 'scrub-comment-author-ip' ) );
		}

		// First attempt to get the IDs.
		$update_ids = Database\get_ids_for_update();

		// Bail on empty or error.
		if ( empty( $update_ids ) || is_wp_error( $update_ids ) ) {
			WP_CLI::error( __( 'No comment IDs could be retrieved.', 'scrub-comment-author-ip' ) );
		}

		// Fetch the masked IP.
		$masked_ip  = Helpers\fetch_masked_ip();

		// Count the IDs updated.
		$run_count  = count( $update_ids );

		// Set up the progress bar.
		$set_ticker = \WP_CLI\Utils\make_progress_bar( __( 'Beginning comment IP cleanup...', 'scrub-comment-author-ip' ), $run_count );

		// Now loop my comment IDs and change the IP.
		foreach ( $update_ids as $update_id ) {

			// Set my new term.
			WP_CLI::runcommand( 'comment update ' . absint( $update_id ) . ' --comment_author_IP="'.  $masked_ip . '" --quiet=true', $this->get_command_args() );

			// Add to the progress bar status.
			$set_ticker->tick();
		}

		// And done.
		$set_ticker->finish();

		// Show the result and bail.
		WP_CLI::success( sprintf( _n( '%d comment author IP has been updated.', '%d comment author IPs have been updated.', absint( $run_count ), 'scrub-comment-author-ip' ), absint( $run_count ) ) );
		WP_CLI::halt( 0 );
	}

	/**
	 * Enable the plugin setting.
	 *
	 * ## EXAMPLES
	 *
	 *     wp scrub-cli enable
	 *
	 * @when after_wp_load
	 */
	function enable() {

		// Set our option key.
		update_option( Core\OPTION_KEY, 'yes' );

		// Show the result and bail.
		WP_CLI::success( __( 'The plugin setting has been enabled.', 'scrub-comment-author-ip' ) );
		WP_CLI::halt( 0 );
	}

	/**
	 * Set the option to "no" in the database.
	 *
	 * ## EXAMPLES
	 *
	 *     wp scrub-cli disable
	 *
	 * @when after_wp_load
	 */
	function disable() {

		// Just set it to "no" for this.
		update_option( Core\OPTION_KEY, 'no' );

		// Show the result and bail.
		WP_CLI::success( __( 'The plugin setting has been disabled.', 'scrub-comment-author-ip' ) );
		WP_CLI::halt( 0 );
	}

	/**
	 * Delete the key in the database.
	 *
	 * ## EXAMPLES
	 *
	 *     wp scrub-cli delete
	 *
	 * @when after_wp_load
	 */
	function delete() {

		// First delete the key.
		delete_option( Core\OPTION_KEY );

		// The show the result and bail.
		WP_CLI::success( __( 'The plugin setting has been deleted.', 'scrub-comment-author-ip' ) );
		WP_CLI::halt( 0 );
	}

	/**
	 * Provides the status of the plugin.
	 *
	 * ## EXAMPLES
	 *
	 *     wp scrub-cli status
	 *
	 * @when after_wp_load
	 */
	function status() {

		// Check for it being enabled first.
		$maybe_enabled  = Helpers\maybe_scrub_enabled( 'boolean' );

		// Return the message.
		WP_CLI::success( false !== $maybe_enabled ? __( 'The plugin is enabled', 'scrub-comment-author-ip' ) : __( 'The plugin is disabled', 'scrub-comment-author-ip' ) );
	}

	/**
	 * This is a placeholder function for testing.
	 *
	 * ## EXAMPLES
	 *
	 *     wp scrub-cli runtests
	 *
	 * @when after_wp_load
	 */
	function runtests() {
		// This is blank, just here when I need it.
	}

	// End all custom CLI commands.
}
