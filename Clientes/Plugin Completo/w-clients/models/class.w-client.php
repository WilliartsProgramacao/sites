<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

/**
 * This class is composed of methods that perform filters and transactions in the database
 *
 * @author Williarts
 */
class W_Client_Model {
    
    public static $table_name = 'client';
    
    /*
     * Count records
     */
    public static function count_all( $s = '%' ) {
        global $wpdb;

        $sql = $wpdb->prepare( '
                SELECT COUNT(*)
                  FROM ' . $wpdb->prefix . W_Client_Model::$table_name . ' AS client
                 WHERE client.client_name LIKE "%s"
        ', $s );
        
        return $wpdb->get_var( $sql );
    }

    /*
     * Find a client by id
     */
    public static function find_by_id( $client_id ) {
        global $wpdb;

        $sql = $wpdb->prepare( '
                SELECT client.*
                  FROM ' . $wpdb->prefix . W_Client_Model::$table_name . ' AS client
                 WHERE client.client_id = %d
        ', $client_id );
        
        return $wpdb->get_row( $sql );
    }

    /*
     * Find all client
     */
    public static function find_all( $s = '%', $attr = array() ) {
        global $wpdb;
        
        $default_attr = array(
            'order' => false,
            'orderby' => false,
            'per_page' => get_option( 'posts_per_page' ),
            'paged' => get_query_var( 'client_paged' )
        );
        $attr = wp_parse_args( $attr, $default_attr );
        
        extract( $attr ); // Extract array to variables
        
        $offset = (int) $attr['paged'] > 1 ? ( (int) $paged - 1 ) * (int) $per_page : false;
        
        $sql_count_pictures = '
                SELECT COUNT(*)
                FROM ' . $wpdb->prefix . W_Client_Picture_Model::$table_name . ' gp
                WHERE gp.client_id = client.client_id';
        
        $sql = $wpdb->prepare( '
                SELECT client.*,
                       ('.$sql_count_pictures.') AS count_pictures
                  FROM ' . $wpdb->prefix . W_Client_Model::$table_name . ' AS client
                 WHERE client.client_name LIKE "%s"
        ', $s );
        
        $valid_fields = array( 'client_id', 'client_name', 'client_slug', 'client_date' );
        
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
    public static function find_by_last_client_id() {
        global $wpdb;

        $sql = 'SELECT client.client_id
                  FROM ' . $wpdb->prefix . W_Client_Model::$table_name . ' AS client
              ORDER BY client.client_id DESC
                 LIMIT 1';

        return $wpdb->get_var( $sql );
    }

    /*
     * Insert client
     */
    public static function insert( $client ) {
        global $wpdb;

        return $wpdb->insert(
                $wpdb->prefix . W_Client_Model::$table_name,
                array(
                    'client_name' => $client['client_name'],
                    'client_description' => $client['client_description'],
                    'client_date' => $client['client_date'],
                    'client_slug' => $client['client_slug'],
                    'client_cover' => $client['client_cover']
                ),
                array( '%s', '%s', '%s', '%s', '%d' )
        );
    }
    
    /*
     * Update client
     */
    public static function update( $client ) {
        global $wpdb;
        
        return $wpdb->update(
                $wpdb->prefix . W_Client_Model::$table_name,
                array(
                    'client_name' => $client['client_name'],
                    'client_description' => $client['client_description'],
                    'client_date' => $client['client_date'],
                    'client_slug' => $client['client_slug'],
                    'client_cover' => $client['client_cover']
                ),
                array( 'client_id' => $client['client_id'] ),
                array( '%s', '%s', '%s', '%s', '%d' ),
                array( '%d' )
        );
    }
    
    /*
     * Delete client
     */
    public static function delete( $client_id ) {
        global $wpdb;
        
        return $wpdb->delete(
                $wpdb->prefix . W_Client_Model::$table_name,
                array( 'client_id' => $client_id ),
                array( '%d' )
        );
    }
    
}