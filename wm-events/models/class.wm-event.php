<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

/**
 * This class is composed of methods that perform filters and transactions in the database
 *
 * @author Wime
 */
class WM_Event_Model {
    
    public static $table_name = 'event';
    
    /*
     * Count records
     */
    public static function count_all( $s = '%' ) {
        global $wpdb;

        $sql = $wpdb->prepare( '
                SELECT COUNT(*)
                  FROM ' . $wpdb->prefix . WM_Event_Model::$table_name . ' AS event
                 WHERE event.event_name LIKE "%s"
        ', $s );
        
        return $wpdb->get_var( $sql );
    }

    /*
     * Find a event by id
     */
    public static function find_by_id( $event_id ) {
        global $wpdb;

        $sql = $wpdb->prepare( '
                SELECT event.*
                  FROM ' . $wpdb->prefix . WM_Event_Model::$table_name . ' AS event
                 WHERE event.event_id = %d
        ', $event_id );
        
        return $wpdb->get_row( $sql );
    }

    /*
     * Find all event
     */
    public static function find_all( $s = '%', $attr = array() ) {
        global $wpdb;
        
        $default_attr = array(
            'order' => false,
            'orderby' => false,
            'per_page' => get_option( 'posts_per_page' ),
            'paged' => get_query_var( 'event_paged' )
        );
        $attr = wp_parse_args( $attr, $default_attr );
        
        extract( $attr ); // Extract array to variables
        
        $offset = (int) $attr['paged'] > 1 ? ( (int) $paged - 1 ) * (int) $per_page : false;
        
        $sql_count_pictures = '
                SELECT COUNT(*)
                FROM ' . $wpdb->prefix . WM_Event_Picture_Model::$table_name . ' gp
                WHERE gp.event_id = event.event_id';
        
        $sql = $wpdb->prepare( '
                SELECT event.*,
                       ('.$sql_count_pictures.') AS count_pictures
                  FROM ' . $wpdb->prefix . WM_Event_Model::$table_name . ' AS event
                 WHERE event.event_name LIKE "%s"
        ', $s );
        
        $valid_fields = array( 'event_id', 'event_name', 'event_slug', 'event_date' );
        
        $orderby_sql = '';
        if ( is_array( $orderby ) ) {
            foreach ( $orderby as $value ) {
                $value_array = explode( " ", trim( $value ) );
                if ( in_array( $value_array[0], $valid_fields ) ) {
                    $orderby_sql .= empty( $orderby_sql ) ? $value : ', ' . $value;
                }
            }

        } elseif ( is_string( $orderby ) ) {
            $orderby_array = explode( " ", trim( $orderby ) );
            if ( in_array( $orderby_array[0], $valid_fields ) ) {
                $orderby_sql .= empty( $orderby_sql ) ? $orderby : ', ' . $orderby;
            }
        }
        
        if ( ! empty( $orderby_sql ) ) {
            $sql .= ' ORDER BY ' . $orderby_sql;
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
    public static function find_by_last_event_id() {
        global $wpdb;

        $sql = 'SELECT event.event_id
                  FROM ' . $wpdb->prefix . WM_Event_Model::$table_name . ' AS event
              ORDER BY event.event_id DESC
                 LIMIT 1';

        return $wpdb->get_var( $sql );
    }

    /*
     * Insert event
     */
    public static function insert( $event ) {
        global $wpdb;

        return $wpdb->insert(
                $wpdb->prefix . WM_Event_Model::$table_name,
                array(
                    'event_name' => $event['event_name'],
                    'event_description' => $event['event_description'],
                    'event_date' => $event['event_date'],
                    'event_slug' => $event['event_slug'],
                    'event_cover' => $event['event_cover']
                ),
                array( '%s', '%s', '%s', '%s', '%d' )
        );
    }
    
    /*
     * Update event
     */
    public static function update( $event ) {
        global $wpdb;
        
        return $wpdb->update(
                $wpdb->prefix . WM_Event_Model::$table_name,
                array(
                    'event_name' => $event['event_name'],
                    'event_description' => $event['event_description'],
                    'event_date' => $event['event_date'],
                    'event_slug' => $event['event_slug'],
                    'event_cover' => $event['event_cover']
                ),
                array( 'event_id' => $event['event_id'] ),
                array( '%s', '%s', '%s', '%s', '%d' ),
                array( '%d' )
        );
    }
    
    /*
     * Delete event
     */
    public static function delete( $event_id ) {
        global $wpdb;
        
        return $wpdb->delete(
                $wpdb->prefix . WM_Event_Model::$table_name,
                array( 'event_id' => $event_id ),
                array( '%d' )
        );
    }
    
}