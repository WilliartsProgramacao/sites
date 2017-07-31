<?php if ( ! defined( 'ABSPATH' ) ) die( 'No direct script access.' );

/**
 * Class used to install and uninstall this plugin
 *
 * @author Williarts
 */
class W_Client_Setup {
    
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

        // SQL create table client
        $create_table_client_sql = '' .
                'CREATE TABLE ' . $wpdb->prefix . W_Client_Model::$table_name . ' (
                    client_id BIGINT(20) unsigned NOT NULL AUTO_INCREMENT,
                    client_name VARCHAR(45) NOT NULL,
                    client_description TEXT,
                    client_slug VARCHAR(45) NOT NULL,
                    client_date DATE NOT NULL DEFAULT "0000-00-00",
                    PRIMARY KEY (client_id)
             )' . $charset_collate;

        // Check if table client not exists
        if ( ! $wpdb->get_var( "SHOW TABLES LIKE '" . $wpdb->prefix . W_Client_Model::$table_name . "'" ) )
            dbDelta( $create_table_client_sql );

        // SQL create table client_picture
        $create_table_client_picture_sql = '' .
                'CREATE TABLE ' . $wpdb->prefix . W_Client_Picture_Model::$table_name . ' (
                    client_picture_id BIGINT(20) unsigned NOT NULL AUTO_INCREMENT,
                    client_id BIGINT(20) unsigned NOT NULL,
                    client_picture_filename VARCHAR(120) NOT NULL,
                    client_picture_thumbnail VARCHAR(120) NOT NULL,
                    client_picture_orientation VARCHAR(15),
                    client_picture_description VARCHAR(255),
                    client_picture_uploaded DATETIME,
                    PRIMARY KEY (client_picture_id),
                    KEY client_id (client_id)
             )' . $charset_collate;

        // Check if table client_picture not exists
        if ( ! $wpdb->get_var( "SHOW TABLES LIKE '" . $wpdb->prefix . W_Client_Picture_Model::$table_name . "'" ) )
            dbDelta( $create_table_client_picture_sql );
    }
    
    /*
     * Uninstall this plugin
     */
    public static function uninstall() {
        // Flush permalinks rules
        delete_option('rewrite_rules');
    }

}