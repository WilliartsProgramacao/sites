<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

/**
 * This class is composed of methods that perform filters and transactions in the database
 *
 * @author Wime
 */
class W_Client_Picture_Model {
    
    public static $table_name = 'client_picture';
    
    /*
     * Count records
     */
    public static function count_all( $eid ) {
        global $wpdb;

        $sql = $sql = $wpdb->prepare( 'SELECT COUNT(*)
                  FROM ' . $wpdb->prefix . W_Client_Picture_Model::$table_name . ' AS client_picture
                 WHERE client_picture.client_id = %d
        ', $eid );
        
        return $wpdb->get_var( $sql );
    }

    /*
     * Find a client_picture by id
     */
    public static function find_by_id( $epid ) {
        global $wpdb;

        $sql = $wpdb->prepare( '
                SELECT picture.*
                  FROM ' . $wpdb->prefix . W_Client_Picture_Model::$table_name . ' AS picture
                 WHERE picture.client_picture_id = %d
        ', $epid );
        
        return $wpdb->get_row( $sql );
    }
    
    /*
     * Find client_picture by client
     */
    public static function find_by_client( $eid, $attr = array() ) {
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
                  FROM ' . $wpdb->prefix . W_Client_Picture_Model::$table_name . ' AS picture
                 WHERE picture.client_id = %d
        ', $eid );
        
        if ( $per_page ) {
            $sql .= $wpdb->prepare( ' LIMIT %d', $per_page );
            
            if ( $offset )
                $sql .= $wpdb->prepare( ' OFFSET %d', $offset );
        }
        
        return $wpdb->get_results( $sql );
    }
    
    /*
     * Insert client
     */
    public static function insert( $client_picture ) {
        global $wpdb;

        return $wpdb->insert(
                $wpdb->prefix . W_Client_Picture_Model::$table_name,
                array(
                    'client_id' => $client_picture['client_id'],
                    'client_picture_filename' => $client_picture['client_picture_filename'],
                    'client_picture_thumbnail' => $client_picture['client_picture_thumbnail'],
                    'client_picture_orientation' => $client_picture['client_picture_orientation'],
                    'client_picture_description' => $client_picture['client_picture_description'],
                    'client_picture_uploaded' => $client_picture['client_picture_uploaded']
                ),
                array( '%d', '%s', '%s', '%s', '%s', '%s' )
        );
    }
    
    /*
     * Update client
     */
    public static function update( $client_picture, $client_picture_id ) {
        global $wpdb;
        
        return $wpdb->update(
                $wpdb->prefix . W_Client_Picture_Model::$table_name,
                array(
                    'client_picture_description' => $client_picture['client_picture_description'],
                ),
                array( 'client_picture_id' => $client_picture_id ),
                array( '%s' ),
                array( '%d' )
        );
    }
    
    /*
     * Delete client
     */
    public static function delete( $epid ) {
        global $wpdb;
        
        return $wpdb->delete(
                $wpdb->prefix . W_Client_Picture_Model::$table_name,
                array( 'client_picture_id' => $epid ),
                array( '%d' )
        );
    }
    
}