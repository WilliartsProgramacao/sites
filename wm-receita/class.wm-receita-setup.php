<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

class WM_Receita_Setup {
    
    public static function install() {
        // Create post type and taxonomy
        WM_Receita_Admin::init();
        
        // Flush permalinks rules
        flush_rewrite_rules();
    }
    
    public static function uninstall() {
        // Flush permalinks rules
        delete_option('rewrite_rules');
    }
}