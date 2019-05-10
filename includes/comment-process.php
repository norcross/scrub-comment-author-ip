<?php
/**
 * The comment related actions and filters.
 *
 * @package ScrubCommentAuthorIP
 */

// Call our namepsace.
namespace Norcross\ScrubCommentAuthorIP\CommentProcess;

// Set our alias items.
use Norcross\ScrubCommentAuthorIP as Core;
use Norcross\ScrubCommentAuthorIP\Helpers as Helpers;
use Norcross\ScrubCommentAuthorIP\Database as Database;

/**
 * Start our engines.
 */
add_filter( 'preprocess_comment', __NAMESPACE__ . '\set_masked_author_ip', 10 );
add_action( 'comment_post', __NAMESPACE__ . '\confirm_masked_author_ip', 99, 3 );

/**
 * Set the comment data array with our masked IP address.
 *
 * @param  array $commentdata  The comment data as originally posted.
 *
 * @return array
 */
function set_masked_author_ip( $commentdata ) {

	// Set the array with our IP.
	$commentdata['comment_author_IP'] = Helpers\fetch_masked_ip();

	// Return the array of data.
	return $commentdata;
}

/**
 * Confirm that the IP address is set to our masked one.
 *
 * @param  integer        $comment_id        The comment ID just created.
 * @param  integer|string $comment_approved  1 if the comment is approved, 0 if not, 'spam' if spam.
 * @param  array          $commentdata       The entire set of comment data.
 *
 * @return void
 */
function confirm_masked_author_ip( $comment_id, $comment_approved, $commentdata ) {

	// Get my masked IP address.
	$masked_ip  = Helpers\fetch_masked_ip();

	// Check that we have the IP and it's our replacement.
	if ( ! empty( $commentdata['comment_author_IP'] ) && $masked_ip === sanitize_text_field( $commentdata['comment_author_IP'] ) ) {
		return;
	}

	// Go and replace our data directly.
	Database\replace_single_comment_ip( $comment_id );
}
