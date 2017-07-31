<?php if ( ! defined( 'ABSPATH' ) ) die( 'No direct script access.' );

/**
 * Class used to install and uninstall this plugin
 *
 * @author Wime
 */
class WM_Event_Setup {
    
    /*
     * Install this plugin
     */
    public static function install() {
        global $wpdb;
        $charset_collate = '';
        
        // Essential features to install the plugin
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Charset collate
        if ( ! empty( $wpdb->charset ) )
            $charset_collate = " DEFAULT CHARACTER SET $wpdb->charset";
        
        if ( ! empty( $wpdb->collate ) )
            $charset_collate .= " COLLATE $wpdb->collate";

        // SQL create table event
        $create_table_event_sql = '' .
                'CREATE TABLE ' . $wpdb->prefix . WM_Event_Model::$table_name . ' (
                    event_id BIGINT(20) unsigned NOT NULL AUTO_INCREMENT,
                    event_name VARCHAR(45) NOT NULL,
                    event_description TEXT,
                    event_slug VARCHAR(45) NOT NULL,
                    event_id BIGINT(20) unsigned,
                    event_date DATE NOT NULL DEFAULT "0000-00-00",
                    PRIMARY KEY (event_id)
             )' . $charset_collate;

        // Check if table event not exists
        if ( ! $wpdb->get_var( "SHOW TABLES LIKE '" . $wpdb->prefix . WM_Event_Model::$table_name . "'" ) )
            dbDelta( $create_table_event_sql );

        // SQL create table event_picture
        $create_table_event_picture_sql = '' .
                'CREATE TABLE ' . $wpdb->prefix . WM_Event_Picture_Model::$table_name . ' (
                    event_picture_id BIGINT(20) unsigned NOT NULL AUTO_INCREMENT,
                    event_id BIGINT(20) unsigned NOT NULL,
                    event_picture_filename VARCHAR(120) NOT NULL,
                    event_picture_thumbnail VARCHAR(120) NOT NULL,
                    event_picture_orientation VARCHAR(15),
                    event_picture_description VARCHAR(255),
                    event_picture_uploaded DATETIME,
                    PRIMARY KEY (event_picture_id),
                    KEY event_id (event_id)
             )' . $charset_collate;

        // Check if table event_picture not exists
        if ( ! $wpdb->get_var( "SHOW TABLES LIKE '" . $wpdb->prefix . WM_Event_Picture_Model::$table_name . "'" ) )
            dbDelta( $create_table_event_picture_sql );
    }
    
    /*
     * Uninstall this plugin
     */
    public static function uninstall() {
        // Flush permalinks rules
        delete_option('rewrite_rules');
    }

}