<?php if ( ! defined( 'ABSPATH' ) ) die( 'No direct script access.' );

/**
 * Class used to install and uninstall this plugin
 *
 * @author Williarts
 */
class W_Obra_Setup {
    
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

        // SQL create table obra
        $create_table_obra_sql = '' .
                'CREATE TABLE ' . $wpdb->prefix . W_Obra_Model::$table_name . ' (
                    obra_id BIGINT(20) unsigned NOT NULL AUTO_INCREMENT,
                    obra_name VARCHAR(45) NOT NULL,
                    obra_description TEXT,
                    obra_slug VARCHAR(45) NOT NULL,
                    obra_date DATE NOT NULL DEFAULT "0000-00-00",
                    PRIMARY KEY (obra_id)
             )' . $charset_collate;

        // Check if table obra not exists
        if ( ! $wpdb->get_var( "SHOW TABLES LIKE '" . $wpdb->prefix . W_Obra_Model::$table_name . "'" ) )
            dbDelta( $create_table_obra_sql );

        // SQL create table obra_picture
        $create_table_obra_picture_sql = '' .
                'CREATE TABLE ' . $wpdb->prefix . W_Obra_Picture_Model::$table_name . ' (
                    obra_picture_id BIGINT(20) unsigned NOT NULL AUTO_INCREMENT,
                    obra_id BIGINT(20) unsigned NOT NULL,
                    obra_picture_filename VARCHAR(120) NOT NULL,
                    obra_picture_thumbnail VARCHAR(120) NOT NULL,
                    obra_picture_orientation VARCHAR(15),
                    obra_picture_description VARCHAR(255),
                    obra_picture_uploaded DATETIME,
                    PRIMARY KEY (obra_picture_id),
                    KEY obra_id (obra_id)
             )' . $charset_collate;

        // Check if table obra_picture not exists
        if ( ! $wpdb->get_var( "SHOW TABLES LIKE '" . $wpdb->prefix . W_Obra_Picture_Model::$table_name . "'" ) )
            dbDelta( $create_table_obra_picture_sql );
    }
    
    /*
     * Uninstall this plugin
     */
    public static function uninstall() {
        // Flush permalinks rules
        delete_option('rewrite_rules');
    }

}