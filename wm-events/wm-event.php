<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );
/*
  Plugin Name: WM Eventos
  Plugin URI: http://wime.com.br
  Description: Crie e gerencie Evento de Imagens no seu site.
  Author: Wime
  Author URI: http://wime.com.br
 */

// Uploads directory
$upload_dir = wp_upload_dir();

// Plugin paths
define( 'WM_EVENT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WM_EVENT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WM_EVENT_UPLOAD_DIR', $upload_dir['basedir'] . '/wm-events/' );
define( 'WM_EVENT_UPLOAD_URL', $upload_dir['baseurl'] . '/wm-events/' );

// Capability
define( 'WM_EVENT_CAPABILITY', 'edit_posts' );

// Configuration parameters
$wm_event_config = array(
    'rewrite' => 'eventos',
    'slug_page_list_view' => 'wm-event',
    'slug_page_form_view' => 'wm-event-form',
    'slug_page_upload_view' => 'wm-event-upload',
    'slug_action_save' => 'wm-event-save',
    'slug_action_delete' => 'wm-event-delete',
    'slug_action_delete_picture' => 'wm-event-delete-picture',
);

// Include essentials files
require_once WM_EVENT_PLUGIN_DIR . 'models/class.wm-event.php';
require_once WM_EVENT_PLUGIN_DIR . 'models/class.wm-event-picture.php';
require_once WM_EVENT_PLUGIN_DIR . 'class.wm-event-shortcodes.php';
require_once WM_EVENT_PLUGIN_DIR . 'class.wm-event-admin.php';
require_once WM_EVENT_PLUGIN_DIR . 'class.wm-event-setup.php';
require_once WM_EVENT_PLUGIN_DIR . 'wm-event-functions.php';

// Initialization plugin
add_action( 'init', array( 'WM_Event_Admin', 'init' ) );
add_action( 'admin_init', array( 'WM_Event_Admin', 'admin_init' ) );

// Instance shortcodes
WM_Event_Shortcodes::getInstance();

// Install essentials resources on activated plugin
add_action( 'activated_plugin', array( 'WM_Event_Setup', 'install' ) );

// Uninstall some resources on deactivated plugin
add_action( 'deactivated_plugin', array( 'WM_Event_Setup', 'uninstall' ) );