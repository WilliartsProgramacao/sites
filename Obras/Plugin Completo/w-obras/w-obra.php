<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );
/*
  Plugin Name: W Obras
  Plugin URI: http://williarts.com.br
  Description: Crie e gerencie Obras no seu site.
  Author: Williarts
  Author URI: http://williarts.com.br
 */

// Uploads directory
$upload_dir = wp_upload_dir();

// Plugin paths
define( 'W_OBRA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'W_OBRA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'W_OBRA_UPLOAD_DIR', $upload_dir['basedir'] . '/w-obras/' );
define( 'W_OBRA_UPLOAD_URL', $upload_dir['baseurl'] . '/w-obras/' );

// Capability
define( 'W_OBRA_CAPABILITY', 'edit_posts' );

// Configuration parameters
$w_obra_config = array(
    'rewrite' => 'obras',
    'slug_page_list_view' => 'w-obra',
    'slug_page_form_view' => 'w-obra-form',
    'slug_page_upload_view' => 'w-obra-upload',
    'slug_action_save' => 'w-obra-save',
    'slug_action_delete' => 'w-obra-delete',
    'slug_action_delete_picture' => 'w-obra-delete-picture',
);

// Include essentials files
require_once W_OBRA_PLUGIN_DIR . 'models/class.w-obra.php';
require_once W_OBRA_PLUGIN_DIR . 'models/class.w-obra-picture.php';
require_once W_OBRA_PLUGIN_DIR . 'class.w-obra-shortcodes.php';
require_once W_OBRA_PLUGIN_DIR . 'class.w-obra-admin.php';
require_once W_OBRA_PLUGIN_DIR . 'class.w-obra-setup.php';
require_once W_OBRA_PLUGIN_DIR . 'w-obra-functions.php';

// Initialization plugin
add_action( 'init', array( 'W_Obra_Admin', 'init' ) );
add_action( 'admin_init', array( 'W_Obra_Admin', 'admin_init' ) );

// Instance shortcodes
W_Obra_Shortcodes::getInstance();

// Install essentials resources on activated plugin
add_action( 'activated_plugin', array( 'W_Obra_Setup', 'install' ) );

// Uninstall some resources on deactivated plugin
add_action( 'deactivated_plugin', array( 'W_Obra_Setup', 'uninstall' ) );