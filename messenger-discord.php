<?php
/**
 * Sends post and page interactions to a designated bot user webhook.
 *
 * @package rt-post-messenger
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 *
 * @wordpress-plugin
 * Plugin Name:       Messenger for Discord
 * Description:       Sends post and page interactions to a designated bot user webhook.
 * Plugin URI:        https://github.com/ReviveToday/Messenger-for-Discord
 * Version:           1.2.1
 * Author:            ReviveToday, soup-bowl
 * Author URI:        https://revive.today
 * License:           MIT
 */

/**
 * Autoloader.
 */
require_once __DIR__ . '/vendor/autoload.php';

$settings = new rtmessenger\Settings();
add_action( 'admin_menu', array( &$settings, 'add_admin_menu' ) );
add_action( 'admin_init', array( &$settings, 'settings_init' ) );

$metabox = new rtmessenger\Metabox();
add_action( 'add_meta_boxes', array( &$metabox, 'form_setup' ) );

$discord = new rtmessenger\Discord();
add_action( 'publish_post', array( &$discord, 'publish_handler' ), 10, 2 );
add_action( 'publish_page', array( &$discord, 'publish_handler' ), 10, 2 );
