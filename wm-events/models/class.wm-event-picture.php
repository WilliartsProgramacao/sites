<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

/**
 * This class is composed of methods that perform filters and transactions in the database
 *
 * @author Wime
 */
class WM_Event_Picture_Model {
    
    public static $table_name = 'event_picture';
    
    /*
     * Count records
     */
    public static function count_all( $eid ) {
        global $wpdb;

        $sql = $sql = $wpdb->prepare( 'SELECT COUNT(*)
                  FROM ' . $wpdb->prefix . WM_Event_Picture_Model::$table_name . ' AS event_picture
                 WHERE event_picture.event_id = %d
        ', $eid );
        
        return $wpdb->get_var( $sql );
    }

    /*
     * Find a event_picture by id
     */
    public static function find_by_id( $epid ) {
        global $wpdb;

        $sql = $wpdb->prepare( '
                SELECT picture.*
                  FROM ' . $wpdb->prefix . WM_Event_Picture_Model::$table_name . ' AS picture
                 WHERE picture.event_picture_id = %d
        ', $epid );
        
        return $wpdb->get_row( $sql );
    }
    
    /*
     * Find event_picture by event
     */
    public static function find_by_event( $eid, $attr = array() ) {
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
                  FROM ' . $wpdb->prefix . WM_Event_Picture_Model::$table_name . ' AS picture
                 WHERE picture.event_id = %d
        ', $eid );
        
        if ( $per_page ) {
            $sql .= $wpdb->prepare( ' LIMIT %d', $per_page );
            
            if ( $offset )
                $sql .= $wpdb->prepare( ' OFFSET %d', $offset );
        }
        
        return $wpdb->get_results( $sql );
    }
    
    /*
     * Insert event
     */
    public static function insert( $event_picture ) {
        global $wpdb;

        return $wpdb->insert(
                $wpdb->prefix . WM_Event_Picture_Model::$table_name,
                array(
                    'event_id' => $event_picture['event_id'],
                    'event_picture_filename' => $event_picture['event_picture_filename'],
                    'event_picture_thumbnail' => $event_picture['event_picture_thumbnail'],
                    'event_picture_orientation' => $event_picture['event_picture_orientation'],
                    'event_picture_description' => $event_picture['event_picture_description'],
                    'event_picture_uploaded' => $event_picture['event_picture_uploaded']
                ),
                array( '%d', '%s', '%s', '%s', '%s', '%s' )
        );
    }
    
    /*
     * Update event
     */
    public static function update( $event_picture, $event_picture_id ) {
        global $wpdb;
        
        return $wpdb->update(
                $wpdb->prefix . WM_Event_Picture_Model::$table_name,
                array(
                    'event_picture_description' => $event_picture['event_picture_description'],
                ),
                array( 'event_picture_id' => $event_picture_id ),
                array( '%s' ),
                array( '%d' )
        );
    }
    
    /*
     * Delete event
     */
    public static function delete( $epid ) {
        global $wpdb;
        
        return $wpdb->delete(
                $wpdb->prefix . WM_Event_Picture_Model::$table_name,
                array( 'event_picture_id' => $epid ),
                array( '%d' )
        );
    }
    
}