<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );
/*
  Plugin Name: W Clientes Plugin
  Plugin URI: http://williarts.com.br
  Description: Gerencie seus clientes como posts.
  Author: Williarts
  Author URI: http://williarts.com.br
 */

define( 'W_CLIENTE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'W_CLIENTE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include essentials files
require_once W_CLIENTE_PLUGIN_DIR . 'class.w-cliente-admin.php';
require_once W_CLIENTE_PLUGIN_DIR . 'class.w-cliente-setup.php';

// Initialization plugin
add_action( 'init', array( 'W_Cliente_Admin', 'init' ) );

// Admin head post_type
add_action( 'admin_head-edit.php', array( 'W_Cliente_Admin', 'render_css' ) );

// Install essential resources on activated plugin
add_action( 'activated_plugin', array( 'W_Cliente_Setup', 'install' ) );

// Uninstall some resources on deactivated plugin
add_action( 'deactivated_plugin', array( 'W_Cliente_Setup', 'uninstall' ) );