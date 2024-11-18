<?php
/**
 * The specific functions for the bulk action.
 *
 * @package ScrubCommentAuthorIP
 */

// Call our namepsace.
namespace Norcross\ScrubCommentAuthorIP\BulkProcess;

// Set our alias items.
use Norcross\ScrubCommentAuthorIP as Core;
use Norcross\ScrubCommentAuthorIP\Helpers as Helpers;
use Norcross\ScrubCommentAuthorIP\Database as Database;

/**
 * Start our engines.
 */
add_action( 'admin_init', __NAMESPACE__ . '\run_admin_bulk_request' );
add_action( 'admin_notices', __NAMESPACE__ . '\admin_bulk_action_notice' );
add_filter( 'removable_query_args', __NAMESPACE__ . '\admin_removable_args' );

/**
 * Check for the bulk request coming from the admin.
 *
 * @return void
 */
function run_admin_bulk_request() {

	// Confirm we requested this action.
	$confirm_action = filter_input( INPUT_GET, 'ip-scrub-run-bulk', FILTER_SANITIZE_SPECIAL_CHARS ); // phpcs:ignore -- the nonce check is happening after this.

	// Make sure it is what we want.
	if ( empty( $confirm_action ) || 'yes' !== $confirm_action ) {
		return;
	}

	// Make sure we have a nonce.
	$confirm_nonce  = filter_input( INPUT_GET, 'ip-scrub-nonce', FILTER_SANITIZE_SPECIAL_CHARS ); // phpcs:ignore -- the nonce check is happening after this.

	// Handle the nonce check.
	if ( empty( $confirm_nonce ) || ! wp_verify_nonce( $confirm_nonce, 'ip_scrub_bulk' ) ) {

		// Let them know they had a failure.
		wp_die( esc_html__( 'There was an error validating the nonce.', 'scrub-comment-author-ip' ), esc_html__( 'Scrub Comment IP Bulk Action', 'scrub-comment-author-ip' ), [ 'back_link' => true ] );
	}

	// Now get the IDs of the comments we have.
	$fetch_comments = Database\get_ids_for_update();

	// If none exist, say so.
	if ( empty( $fetch_comments ) ) {

		// Now set my redirect link.
		$redirect_link  = Helpers\fetch_settings_url( ['ip-scrub-bulk-error' => 'no-comments', 'ip-scrub-bulk-success' => 'no'] );

		// Do the redirect.
		wp_safe_redirect( $redirect_link );
		exit;
	}

	// Handle the WP_Error return on it's own.
	if ( is_wp_error( $fetch_comments ) ) {

		// Now set my redirect link.
		$redirect_link  = Helpers\fetch_settings_url( ['ip-scrub-bulk-error' => 'query-error', 'ip-scrub-bulk-success' => 'no'] );

		// Do the redirect.
		wp_safe_redirect( $redirect_link );
		exit;
	}

	// Attempt the update.
	$attempt_update = Database\replace_batch_comment_ips( $fetch_comments );

	// Bail if the update did.
	if ( empty( $attempt_update ) || is_wp_error( $attempt_update ) ) {

		// Now set my redirect link.
		$redirect_link  = Helpers\fetch_settings_url( ['ip-scrub-bulk-error' => 'update-error', 'ip-scrub-bulk-success' => 'no'] );

		// Do the redirect.
		wp_safe_redirect( $redirect_link );
		exit;
	}

	// Now set my redirect link.
	$redirect_link  = Helpers\fetch_settings_url( ['ip-scrub-bulk-count' => count( $fetch_comments ), 'ip-scrub-bulk-success' => 'yes'] );

	// Do the redirect.
	wp_safe_redirect( $redirect_link );
	exit;
}

/**
 * Check for the result of bulk action.
 *
 * @return void
 */
function admin_bulk_action_notice() {

	// Confirm we requested this action.
	$confirm_result = filter_input( INPUT_GET, 'ip-scrub-bulk-success', FILTER_SANITIZE_SPECIAL_CHARS ); // phpcs:ignore -- no need for a nonce check.

	// Make sure it is what we want.
	if ( empty( $confirm_result ) ) {
		return;
	}

	// Handle the success first.
	if ( 'yes' === $confirm_result ) {

		// Set the counts.
		$set_counts = filter_input( INPUT_GET, 'ip-scrub-bulk-count', FILTER_SANITIZE_NUMBER_INT );

		// Set our notice text.
		$set_notice = sprintf( _n( 'Success! %d comment was updated.', 'Success! %d comments were updated.', absint( $set_counts ), 'scrub-comment-author-ip' ), absint( $set_counts ) );

		// Set the wrapper around it.
		echo '<div class="notice notice-success is-dismissible">';

			// Display the actual message.
			echo '<p><strong>' . wp_kses_post( $set_notice ) . '</strong></p>';

		// Close the wrapper.
		echo '</div>';

		// And be done.
		return;
	}

	// Handle the errors now.
	$set_error  = filter_input( INPUT_GET, 'ip-scrub-bulk-error', FILTER_SANITIZE_SPECIAL_CHARS );

	// If we have no comments, show a warning.
	if ( 'no-comments' === $set_error ) {

		// Set the notice text.
		$set_notice = __( 'There are no comments requiring update at this time.', 'scrub-comment-author-ip' );

		// Set the wrapper around it.
		echo '<div class="notice notice-warning is-dismissible">';

			// Display the actual message.
			echo '<p><strong>' . wp_kses_post( $set_notice ) . '</strong></p>';

		// Close the wrapper.
		echo '</div>';

		// And finish.
		return;
	}

	// Handle the rest of the possible error messages.
	switch ( $set_error ) {

		case 'query-error' :
			$set_notice = __( 'There was an error attempting to retrieve the comments. Please check your error logs.', 'scrub-comment-author-ip' );
			break;

		case 'update-error' :
			$set_notice = __( 'There was an error attempting to update the comments. Please check your error logs.', 'scrub-comment-author-ip' );
			break;

		default :
			$set_notice = __( 'There was an unknown error. Please check your error logs.', 'scrub-comment-author-ip' );
			break;
	}

	// Set the wrapper around it.
	echo '<div class="notice notice-error is-dismissible">';

		// Display the actual message.
		echo '<p><strong>' . wp_kses_post( $set_notice ) . '</strong></p>';

	// Close the wrapper.
	echo '</div>';

	// Nothing left to display.
}

/**
 * Add our custom strings to the vars.
 *
 * @param  array $args  The existing array of args.
 *
 * @return array $args  The modified array of args.
 */
function admin_removable_args( $args ) {

	// Set an array of the args we wanna exclude.
	$remove = [
		'ip-scrub-bulk-error',
		'ip-scrub-bulk-count',
		'ip-scrub-bulk-success',
	];

	// Include my new args and return.
	return wp_parse_args( $remove, $args );
}
