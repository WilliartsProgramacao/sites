<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

class W_Cliente_Setup {
    
    public static function install() {
        // Create post type and taxonomy
        W_Cliente_Admin::init();
        
        // Flush permalinks rules
        flush_rewrite_rules();
    }
    
    public static function uninstall() {
        // Flush permalinks rules
        delete_option('rewrite_rules');
    }
}