<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );
/*
  Plugin Name: WM Banners
  Plugin URI: http://wime.com.br
  Description: Crie e gerencie Banners no seu site.
  Author: Wime
  Author URI: http://wime.com.br
 */

// Uploads directory
$upload_dir = wp_upload_dir();

define( 'WM_BANNER_CAPABILITY', 'edit_posts' );
define( 'WM_BANNER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WM_BANNER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WM_BANNER_UPLOAD_DIR', $upload_dir['basedir'] . '/wm-banners/' );
define( 'WM_BANNER_UPLOAD_URL', $upload_dir['baseurl'] . '/wm-banners/' );

// Configuration parameters
$wm_banner_config = array(
    'rewrite' => 'banner',
    'slug_page_list_view' => 'wm-banner',
    'slug_page_form_view' => 'wm-banner-form',
    'slug_page_upload_view' => 'wm-banner-upload',
    'slug_action_save' => 'wm-banner-save',
    'slug_action_delete' => 'wm-banner-delete',
    'slug_action_delete_picture' => 'wm-banner-delete-picture',
);

// Include essentials files
require_once WM_BANNER_PLUGIN_DIR . 'models/class.wm-banner.php';
require_once WM_BANNER_PLUGIN_DIR . 'models/class.wm-banner-picture.php';
require_once WM_BANNER_PLUGIN_DIR . 'views/class.banner-widget.php';
require_once WM_BANNER_PLUGIN_DIR . 'class.wm-banner-shortcodes.php';
require_once WM_BANNER_PLUGIN_DIR . 'class.wm-banner-admin.php';
require_once WM_BANNER_PLUGIN_DIR . 'class.wm-banner-setup.php';
require_once WM_BANNER_PLUGIN_DIR . 'wm-banner-functions.php';

// Initialization plugin
add_action( 'init', array( 'WM_Banner_Admin', 'init' ) );
add_action( 'admin_init', array( 'WM_Banner_Admin', 'admin_init' ) );

// Instance shortcodes
WM_Banner_Shortcodes::getInstance();

// Install essential resources for this plugin
add_action( 'activated_plugin', array( 'WM_Banner_Setup', 'install' ) );

// Add filter uninstall plugin on delete site wpmu
add_filter( 'wpmu_drop_tables', array( 'WM_Banner_Setup', 'uninstall' ) );