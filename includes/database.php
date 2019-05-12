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
 * Get all my comment IDs.
 *
 * @return array
 */
function get_ids_for_update() {

	// Fetch the masked IP.
	$masked_ip  = Helpers\fetch_masked_ip();

	// Call the global class.
	global $wpdb;

	// Set my table name.
	$table_name = $wpdb->prefix . 'comments';

	// Set up our query.
	$query_args = $wpdb->prepare("
		SELECT   comment_ID
		FROM     $table_name
		WHERE    comment_author_IP NOT LIKE '%s'
	", esc_attr( $masked_ip ) );

	// Process the query.
	$query_run  = $wpdb->get_col( $query_args );

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
 *
 * @return mixed
 */
function replace_single_comment_ip( $comment_id = 0 ) {

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

	// Call the global class.
	global $wpdb;

	// Set my table name.
	$table_name = $wpdb->prefix . 'comments';

	// Run the actual DB update.
	$update_row = $wpdb->update( $table_name,
		array( 'comment_author_IP' => Helpers\fetch_masked_ip() ),
		array( 'ID' => absint( $comment_id ) ),
		array( '%s' ),
		array( '%d' )
	);

	// Return the boolean if it was updated or not.
	return false !== $update_row ? true : new WP_Error( 'update_row_failure', __( 'The database update failed.', 'scrub-comment-author-ip' ) );;
}

/**
 * Replace a group of comment IP addresses in the database directly.
 *
 * @param  array $comment_ids  The ID of the comment being updated.
 *
 * @return mixed
 */
function replace_batch_comment_ips( $comment_ids = array() ) {

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

	// Set my table name.
	$table_name = $wpdb->prefix . 'comments';

	// Now loop the IDs and run the update.
	foreach ( $comment_ids as $comment_id ) {

		// Run the actual DB update.
		$update_row = $wpdb->update( $table_name,
			array( 'comment_author_IP' => $masked_ip ),
			array( 'ID' => absint( $comment_id ) ),
			array( '%s' ),
			array( '%d' )
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
