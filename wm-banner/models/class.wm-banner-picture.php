<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

/**
 * This class is composed of methods that perform filters and transactions in the database
 *
 * @author Wime
 */
class WM_Banner_Picture_Model {
    
    public static $table_name = 'banner_picture';

    /*
     * Find a banner_picture by id
     */
    public static function find_by_id( $gpid ) {
        global $wpdb;

        $sql = $wpdb->prepare( '
                SELECT picture.*
                  FROM ' . $wpdb->prefix . WM_Banner_Picture_Model::$table_name . ' AS picture
                 WHERE picture.banner_picture_id = %d
        ', $gpid );
        
        return $wpdb->get_row( $sql );
    }
    
    /*
     * Find banner_picture by banner
     */
    public static function find_by_banner( $gid, $attr = array() ) {
        global $wpdb;
        
        $default_attr = array(
            'per_page' => false,
            'paged' => false
        );
        $attr = wp_parse_args( $attr, $default_attr );

        extract( $attr ); // Extract array to variables
        
        $offset = (int) $attr['paged'] > 1 ? ( (int) $paged - 1 ) * (int) $per_page : false;
        
        $sql = $wpdb->prepare( '
                SELECT picture.*
                  FROM ' . $wpdb->prefix . WM_Banner_Picture_Model::$table_name . ' AS picture
                 WHERE picture.banner_id = %d
                 ORDER BY picture.banner_picture_id DESC
        ', $gid );
        
        if ( $per_page ) {
            $sql .= $wpdb->prepare( ' LIMIT %d', $per_page );
            
            if ( $offset )
                $sql .= $wpdb->prepare( ' OFFSET %d', $offset );
        }
        
        return $wpdb->get_results( $sql );
    }
    
    /*
     * Insert banner
     */
    public static function insert( $banner_picture ) {
        global $wpdb;

        return $wpdb->insert(
                $wpdb->prefix . WM_Banner_Picture_Model::$table_name,
                array(
                    'banner_id' => $banner_picture['banner_id'],
                    'banner_picture_filename' => $banner_picture['banner_picture_filename'],
                    'banner_picture_thumbnail' => $banner_picture['banner_picture_thumbnail'],
                    'banner_picture_description' => $banner_picture['banner_picture_description'],
                    'banner_picture_link' => $banner_picture['banner_picture_link'],
                    'banner_picture_target' => $banner_picture['banner_picture_target'],
                    'banner_picture_uploaded' => $banner_picture['banner_picture_uploaded']
                ),
                array( '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
        );
    }
    
    /*
     * Update banner
     */
    public static function update( $banner_picture, $banner_picture_id ) {
        global $wpdb;
        
        return $wpdb->update(
                $wpdb->prefix . WM_Banner_Picture_Model::$table_name,
                array(
                    'banner_picture_description' => $banner_picture['banner_picture_description'],
                    'banner_picture_link' => $banner_picture['banner_picture_link'],
                    'banner_picture_target' => $banner_picture['banner_picture_target'],
                ),
                array( 'banner_picture_id' => $banner_picture_id ),
                array( '%s', '%s', '%s' ),
                array( '%d' )
        );
    }
    
    /*
     * Delete banner
     */
    public static function delete( $gpid ) {
        global $wpdb;
        
        return $wpdb->delete(
                $wpdb->prefix . WM_Banner_Picture_Model::$table_name,
                array( 'banner_picture_id' => $gpid ),
                array( '%d' )
        );
    }
    
}