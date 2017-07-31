<?php if ( ! defined( 'ABSPATH' ) ) die( 'No direct script access.' );

/**
 * Class used to install and uninstall this plugin
 *
 * @author Wime
 */
class WM_Banner_Setup {
    
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

        // SQL create table banner
        $create_table_banner_sql = '' .
                'CREATE TABLE ' . $wpdb->prefix . WM_Banner_Model::$table_name . ' (
                    banner_id BIGINT(20) unsigned NOT NULL AUTO_INCREMENT,
                    banner_name VARCHAR(45) NOT NULL,
                    banner_width INT(11) NOT NULL,
                    banner_height INT(11) NOT NULL,
                    banner_date datetime NOT NULL DEFAULT "0000-00-00 00:00:00",
                    PRIMARY KEY (banner_id)
             )' . $charset_collate;

        // Check if table banner not exists
        if ( ! $wpdb->get_var( "SHOW TABLES LIKE '" . $wpdb->prefix . WM_Banner_Model::$table_name . "'" ) )
            dbDelta( $create_table_banner_sql );

        // SQL create table banner_picture
        $create_table_banner_picture_sql = '' .
                'CREATE TABLE ' . $wpdb->prefix . WM_Banner_Picture_Model::$table_name . ' (
                    banner_picture_id BIGINT(20) unsigned NOT NULL AUTO_INCREMENT,
                    banner_id BIGINT(20) unsigned NOT NULL,
                    banner_picture_filename VARCHAR(120) NOT NULL,
                    banner_picture_thumbnail VARCHAR(120) NOT NULL,
                    banner_picture_description VARCHAR(255),
                    banner_picture_link VARCHAR(255),
                    banner_picture_target VARCHAR(25),
                    banner_picture_uploaded DATETIME,
                    PRIMARY KEY (banner_picture_id),
                    KEY banner_id (banner_id)
             )' . $charset_collate;

        // Check if table banner_picture not exists
        if ( ! $wpdb->get_var( "SHOW TABLES LIKE '" . $wpdb->prefix . WM_Banner_Picture_Model::$table_name . "'" ) )
            dbDelta( $create_table_banner_picture_sql );
    }
    
    /*
     * Uninstall this plugin
     */
    public static function uninstall() {
        global $wpdb;
        $tables = array();
        $id = isset( $_REQUEST[ 'id' ] ) ? intval( $_REQUEST[ 'id' ] ) : 0;
        if ( $id > 0 ) {
            $tables[] = $wpdb->get_blog_prefix( $id ) . WM_Banner_Model::$table_name;
            $tables[] = $wpdb->get_blog_prefix( $id ) . WM_Banner_Picture_Model::$table_name;
        }
        return $tables;
    }

}