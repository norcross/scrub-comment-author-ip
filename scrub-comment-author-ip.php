<?php
/**
 * Plugin Name:     Scrub Comment Author IP
 * Plugin URI:      https://github.com/norcross/scrub-comment-author-ip
 * Description:     Make sure no real IP addresses are stored in WP comments.
 * Author:          Andrew Norcross
 * Author URI:      http://andrewnorcross.com
 * Text Domain:     scrub-comment-author-ip
 * Domain Path:     /languages
 * Version:         0.0.1
 *
 * @package         ScrubCommentAuthorIP
 */

// Call our namepsace.
namespace Norcross\ScrubCommentAuthorIP;

// Call our CLI namespace.
use WP_CLI;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Define our version.
define( __NAMESPACE__ . '\VERS', '0.0.1' );

// Plugin root file.
define( __NAMESPACE__ . '\FILE', __FILE__ );

// Define our file base.
define( __NAMESPACE__ . '\BASE', plugin_basename( __FILE__ ) );

// Plugin Folder URL.
define( __NAMESPACE__ . '\URL', plugin_dir_url( __FILE__ ) );

// Set our assets URL constant.
define( __NAMESPACE__ . '\ASSETS_URL', URL . 'assets' );

// Set our includes and template path constants.
define( __NAMESPACE__ . '\INCLUDES_PATH', __DIR__ . '/includes' );

// Set the prefix for our actions and filters.
define( __NAMESPACE__ . '\HOOK_PREFIX', 'scrub_comment_author_ip_' );

// Go and load our files.
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/settings-api.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/comment-process.php';

// Check that we have the CLI constant available.
if ( defined( 'WP_CLI' ) && WP_CLI ) {

	// Load our commands file.
	require_once dirname( __FILE__ ) . '/includes/cli-commands.php';

	// And add our command.
	WP_CLI::add_command( 'scrub-cli', ScrubCLICommands::class );
}

// Load the triggered file loads.
require_once __DIR__ . '/includes/activate.php';
require_once __DIR__ . '/includes/deactivate.php';
require_once __DIR__ . '/includes/uninstall.php';
