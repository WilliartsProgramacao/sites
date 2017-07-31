<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );
/*
  Plugin Name: W Obras Plugin
  Plugin URI: http://williarts.com.br
  Description: Gerencie suas obras como posts.
  Author: Williarts
  Author URI: http://williarts.com.br
 */

define( 'W_OBRA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'W_OBRA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include essentials files
require_once W_OBRA_PLUGIN_DIR . 'class.w-obra-admin.php';
require_once W_OBRA_PLUGIN_DIR . 'class.w-obra-setup.php';

// Initialization plugin
add_action( 'init', array( 'W_Obra_Admin', 'init' ) );

// Admin head post_type
add_action( 'admin_head-edit.php', array( 'W_Obra_Admin', 'render_css' ) );

// Install essential resources on activated plugin
add_action( 'activated_plugin', array( 'W_Obra_Setup', 'install' ) );

// Uninstall some resources on deactivated plugin
add_action( 'deactivated_plugin', array( 'W_Obra_Setup', 'uninstall' ) );