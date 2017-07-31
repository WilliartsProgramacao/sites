<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );
/*
  Plugin Name: W Clientes
  Plugin URI: http://williarts.com.br
  Description: Crie e gerencie Cliente no seu site.
  Author: Williarts
  Author URI: http://williarts.com.br
 */

// Uploads directory
$upload_dir = wp_upload_dir();

// Plugin paths
define( 'W_CLIENT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'W_CLIENT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'W_CLIENT_UPLOAD_DIR', $upload_dir['basedir'] . '/w-clients/' );
define( 'W_CLIENT_UPLOAD_URL', $upload_dir['baseurl'] . '/w-clients/' );

// Capability
define( 'W_CLIENT_CAPABILITY', 'edit_posts' );

// Configuration parameters
$w_client_config = array(
    'rewrite' => 'clientes',
    'slug_page_list_view' => 'w-client',
    'slug_page_form_view' => 'w-client-form',
    'slug_page_upload_view' => 'w-client-upload',
    'slug_action_save' => 'w-client-save',
    'slug_action_delete' => 'w-client-delete',
    'slug_action_delete_picture' => 'w-client-delete-picture',
);

// Include essentials files
require_once W_CLIENT_PLUGIN_DIR . 'models/class.w-client.php';
require_once W_CLIENT_PLUGIN_DIR . 'models/class.w-client-picture.php';
require_once W_CLIENT_PLUGIN_DIR . 'class.w-client-shortcodes.php';
require_once W_CLIENT_PLUGIN_DIR . 'class.w-client-admin.php';
require_once W_CLIENT_PLUGIN_DIR . 'class.w-client-setup.php';
require_once W_CLIENT_PLUGIN_DIR . 'w-client-functions.php';

// Initialization plugin
add_action( 'init', array( 'W_Client_Admin', 'init' ) );
add_action( 'admin_init', array( 'W_Client_Admin', 'admin_init' ) );

// Instance shortcodes
W_Client_Shortcodes::getInstance();

// Install essentials resources on activated plugin
add_action( 'activated_plugin', array( 'W_Client_Setup', 'install' ) );

// Uninstall some resources on deactivated plugin
add_action( 'deactivated_plugin', array( 'W_Client_Setup', 'uninstall' ) );