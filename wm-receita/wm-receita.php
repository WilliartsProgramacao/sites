<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );
/*
  Plugin Name: WM Receitas
  Plugin URI: http://wime.com.br
  Description: Gerencie suas receitas como Posts.
  Author: Wime
  Author URI: http://wime.com.br
 */

define( 'WM_RECEITA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WM_RECEITA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include essentials files
require_once WM_RECEITA_PLUGIN_DIR . 'class.wm-receita-admin.php';
require_once WM_RECEITA_PLUGIN_DIR . 'class.wm-receita-widgets.php';
require_once WM_RECEITA_PLUGIN_DIR . 'class.wm-receita-setup.php';

// Initialization plugin
add_action( 'init', array( 'WM_Receita_Admin', 'init' ) );

// Admin head post_type
add_action( 'admin_head-edit.php', array( 'WM_Receita_Admin', 'render_css' ) );
 
// Action register widget
add_action( 'widgets_init', array( 'WP_Widget_Receita', 'init' ) );

// Install essential resources on activated plugin
add_action( 'activated_plugin', array( 'WM_Receita_Setup', 'install' ) );

// Uninstall some resources on deactivated plugin
add_action( 'deactivated_plugin', array( 'WM_Receita_Setup', 'uninstall' ) );