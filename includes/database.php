<?php
/**
 * The interactions with the WP database.
 *
 * @package ScrubCommentAuthorIP
 */

// Call our namepsace.
namespace Norcross\ScrubCommentAuthorIP\Database;

// Set our alias items.
use Norcross\ScrubCommentAuthorIP as Core;
use Norcross\ScrubCommentAuthorIP\Helpers as Helpers;

// And pull in any other namespaces.
use WP_Error;

/**
 * Just get a simple count.
 *
 * @return array
 */
function get_count_for_update() {

	// Fetch the masked IP.
	$masked_ip  = Helpers\fetch_masked_ip();

	// Call the global class.
	global $wpdb;

	// Set up our query.
	$query_args = $wpdb->prepare("
		SELECT   COUNT(*)
		FROM     $wpdb->comments
		WHERE    comment_author_IP NOT LIKE '%s'
	", esc_attr( $masked_ip ) );

	// Process the query.
	$query_run  = $wpdb->get_var( $query_args ); // phpcs:ignore -- we are skipping the overhead of get_comments.

	// Throw the error if we have one.
	if ( is_wp_error( $query_run ) ) {
		return new WP_Error( $query_run->get_error_code(), $query_run->get_error_message() );
	}

	// Return the count, whatever it may be.
	return absint( $query_run );
}

/**
 * Get all my comment IDs.
 *
 * @return array
 */
function get_ids_for_update() {

	// Fetch the masked IP.
	$masked_ip  = Helpers\fetch_masked_ip();

	// Call the global class.
	global $wpdb;

	// Set up our query.
	$query_args = $wpdb->prepare("
		SELECT   comment_ID
		FROM     $wpdb->comments
		WHERE    comment_author_IP NOT LIKE '%s'
	", esc_attr( $masked_ip ) );

	// Process the query.
	$query_run  = $wpdb->get_col( $query_args ); // phpcs:ignore -- we are skipping the overhead of get_comments.

	// Throw the error if we have one.
	if ( is_wp_error( $query_run ) ) {
		return new WP_Error( $query_run->get_error_code(), $query_run->get_error_message() );
	}

	// Bail if no IDs exist.
	if ( empty( $query_run ) ) {
		return false;
	}

	// Return the IDs, sanitized.
	return array_map( 'absint', $query_run );
}

/**
 * Replace a single comment IP address in the database directly.
 *
 * @param  integer $comment_id  The ID of the comment being updated.
 * @param  string  $masked_ip   The new masked IP we wanna use.
 *
 * @return mixed
 */
function replace_single_comment_ip( $comment_id = 0, $masked_ip = '' ) {

	// Bail if no comment ID was provided.
	if ( empty( $comment_id ) ) {
		return new WP_Error( 'missing_comment_id', __( 'The comment ID is required. So provide one.', 'scrub-comment-author-ip' ) );
	}

	// Check for it being enabled first.
	$maybe_enabled  = Helpers\maybe_scrub_enabled( 'boolean' );

	// Bail if we aren't enabled.
	if ( false === $maybe_enabled ) {
		return new WP_Error( 'not_enabled', __( 'The plugin setting has not been enabled.', 'scrub-comment-author-ip' ) );
	}

	// Define our masked IP.
	$set_new_ip = ! empty( $masked_ip ) ? $masked_ip : Helpers\fetch_masked_ip();

	// Call the global class.
	global $wpdb;

	// Run the actual DB update.
	$update_row = $wpdb->update( // phpcs:ignore -- we dont want to trigger anything else here.
		$wpdb->comments,
		[ 'comment_author_IP' => $set_new_ip ],
		[ 'comment_ID' => absint( $comment_id ) ],
		[ '%s' ],
		[ '%d' ],
	);

	// Return the error if we got one.
	if ( ! empty( $wpdb->last_error ) ) {
		return new WP_Error( 'update_row_failure', $wpdb->last_error );
	}

	// Return the boolean if it was updated or not.
	return false !== $update_row ? true : new WP_Error( 'update_row_failure', __( 'The database update failed.', 'scrub-comment-author-ip' ) );
}

/**
 * Replace a group of comment IP addresses in the database directly.
 *
 * @param  array $comment_ids  The ID of the comment being updated.
 *
 * @return mixed
 */
function replace_batch_comment_ips( $comment_ids = [] ) {

	// Bail if no comment IDs were provided.
	if ( empty( $comment_ids ) ) {
		return new WP_Error( 'missing_comment_ids', __( 'Comment IDs is required. So provide one.', 'scrub-comment-author-ip' ) );
	}

	// Check for it being enabled first.
	$maybe_enabled  = Helpers\maybe_scrub_enabled( 'boolean' );

	// Bail if we aren't enabled.
	if ( false === $maybe_enabled ) {
		return new WP_Error( 'not_enabled', __( 'The plugin setting has not been enabled.', 'scrub-comment-author-ip' ) );
	}

	// Fetch the masked IP.
	$masked_ip  = Helpers\fetch_masked_ip();

	// Call the global class.
	global $wpdb;

	// Now loop the IDs and run the update.
	foreach ( $comment_ids as $comment_id ) {

		// Run the actual DB update.
		$update_row = $wpdb->update( // phpcs:ignore -- we dont want to trigger anything else here.
			$wpdb->comments,
			[ 'comment_author_IP' => $masked_ip ],
			[ 'comment_ID' => absint( $comment_id ) ],
			[ '%s' ],
			[ '%d' ],
		);

		// Throw an error if it didn't work.
		if ( false === $update_row ) {

			// Set the error text.
			$error_text = sprintf( __( 'The database update failed on the following comment ID: %s', 'scrub-comment-author-ip' ), '<code>' . absint( $comment_id ) . '</code>' );

			// And return the error.
			return new WP_Error( 'update_row_failure', $error_text );
		}
	}

	// Return the boolean.
	return true;
}
