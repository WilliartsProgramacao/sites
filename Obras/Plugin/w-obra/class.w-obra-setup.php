<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

class W_Obra_Setup {
    
    public static function install() {
        // Create post type and taxonomy
        W_Obra_Admin::init();
        
        // Flush permalinks rules
        flush_rewrite_rules();
    }
    
    public static function uninstall() {
        // Flush permalinks rules
        delete_option('rewrite_rules');
    }
}