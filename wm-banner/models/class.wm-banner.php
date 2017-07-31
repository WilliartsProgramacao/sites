<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

/**
 * This class is composed of methods that perform filters and transactions in the database
 *
 * @author Wime
 */
class WM_Banner_Model {
    
    public static $table_name = 'banner';
    
    /*
     * Count records
     */
    public static function count_all( $s = '%' ) {
        global $wpdb;

        $sql = $wpdb->prepare( '
                SELECT COUNT(*)
                  FROM ' . $wpdb->prefix . WM_Banner_Model::$table_name . ' AS banner
                 WHERE banner.banner_name LIKE "%s"
        ', $s );
        
        return $wpdb->get_var( $sql );
    }

    /*
     * Find a banner by id
     */
    public static function find_by_id( $banner_id ) {
        global $wpdb;

        $sql = $wpdb->prepare( '
                SELECT banner.*
                  FROM ' . $wpdb->prefix . WM_Banner_Model::$table_name . ' AS banner
                 WHERE banner.banner_id = %d
        ', $banner_id );
        
        return $wpdb->get_row( $sql );
    }

    /*
     * Find all banner
     */
    public static function find_all( $s = '%', $attr = array() ) {
        global $wpdb;
        
        $default_attr = array(
            'order' => false,
            'orderby' => false,
            'per_page' => false,
            'paged' => false
        );
        $attr = wp_parse_args( $attr, $default_attr );
        
        extract( $attr ); // Extract array to variables
        
        $offset = (int) $attr['paged'] > 1 ? ( (int) $paged - 1 ) * (int) $per_page : false;
        
        $sql_count_pictures = '
                SELECT COUNT(*)
                FROM ' . $wpdb->prefix . WM_Banner_Picture_Model::$table_name . ' bp
                WHERE bp.banner_id = banner.banner_id';
        
        $sql_banner_cover = '
                SELECT bp.banner_picture_thumbnail
                FROM ' . $wpdb->prefix . WM_Banner_Picture_Model::$table_name . ' bp
                WHERE bp.banner_id = banner.banner_id
                ORDER BY bp.banner_picture_id DESC
                LIMIT 1';
        
        $sql = $wpdb->prepare( '
                SELECT banner.*,
                       ('.$sql_count_pictures.') AS count_pictures,
                       ('.$sql_banner_cover.') AS banner_cover
                  FROM ' . $wpdb->prefix . WM_Banner_Model::$table_name . ' AS banner
                 WHERE banner.banner_name LIKE "%s"
        ', $s );
        
        if ( in_array( $orderby, array( 'banner_id', 'banner_name', 'banner_slug' ) ) ) {
            $sql .= ' ORDER BY ' . $orderby;
            
            if ( in_array( strtoupper( $order ), array( 'ASC', 'DESC' ) ) )
                $sql .= ' ' . $order;
        }
            
        if ( $per_page ) {
            $sql .= $wpdb->prepare( ' LIMIT %d', $per_page );
            
            if ( $offset )
                $sql .= $wpdb->prepare( ' OFFSET %d', $offset );
        }
        
        return $wpdb->get_results( $sql );
    }
    
    /*
     * Find by last Id
     */
    public static function find_by_last_banner_id() {
        global $wpdb;

        $sql = 'SELECT banner.banner_id
                  FROM ' . $wpdb->prefix . WM_Banner_Model::$table_name . ' AS banner
              ORDER BY banner.banner_id DESC
                 LIMIT 1';

        return $wpdb->get_var( $sql );
    }

    /*
     * Insert banner
     */
    public static function insert( $banner ) {
        global $wpdb;

        return $wpdb->insert(
                $wpdb->prefix . WM_Banner_Model::$table_name,
                array(
                    'banner_name' => $banner['banner_name'],
                    'banner_width' => $banner['banner_width'],
                    'banner_height' => $banner['banner_height'],
                    'banner_date' => date_i18n( 'Y-m-d H:i:s' )
                ),
                array( '%s', '%s', '%s' )
        );
    }
    
    /*
     * Update banner
     */
    public static function update( $banner ) {
        global $wpdb;
        
        return $wpdb->update(
                $wpdb->prefix . WM_Banner_Model::$table_name,
                array(
                    'banner_name' => $banner['banner_name'],
                    'banner_width' => $banner['banner_width'],
                    'banner_height' => $banner['banner_height']
                ),
                array( 'banner_id' => $banner['banner_id'] ),
                array( '%s', '%s' ),
                array( '%d' )
        );
    }
    
    /*
     * Delete banner
     */
    public static function delete( $banner_id ) {
        global $wpdb;
        
        return $wpdb->delete(
                $wpdb->prefix . WM_Banner_Model::$table_name,
                array( 'banner_id' => $banner_id ),
                array( '%d' )
        );
    }
    
}