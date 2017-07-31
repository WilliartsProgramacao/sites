<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

/**
 * This class is composed of methods that perform filters and transactions in the database
 *
 * @author Williarts
 */
class W_Obra_Model {
    
    public static $table_name = 'obra';
    
    /*
     * Count records
     */
    public static function count_all( $s = '%' ) {
        global $wpdb;
        $sql = $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . W_Obra_Model::$table_name . ' AS obra WHERE obra.obra_name LIKE "%s"', $s );
        return $wpdb->get_var( $sql );
    }

    /*
     * Find a obra by id
     */
    public static function find_by_id( $obra_id ) {
        global $wpdb;
        $sql = $wpdb->prepare( 'SELECT obra.* FROM ' . $wpdb->prefix . W_Obra_Model::$table_name . ' AS obra WHERE obra.obra_id = %d', $obra_id );
        return $wpdb->get_row( $sql );
    }

    /*
     * Find all obra
     */
    public static function find_all( $s = '%', $attr = array() ) {
        global $wpdb;
        
        $default_attr = array(
            'order' => false,
            'orderby' => false,
            'per_page' => get_option( 'posts_per_page' ),
            'paged' => get_query_var( 'obra_paged' )
        );
        $attr = wp_parse_args( $attr, $default_attr );
        
        extract( $attr ); // Extract array to variables
        
        $offset = (int) $attr['paged'] > 1 ? ( (int) $paged - 1 ) * (int) $per_page : false;
        
        $sql_count_pictures = 'SELECT COUNT(*) FROM ' . $wpdb->prefix . W_Obra_Picture_Model::$table_name . ' gp WHERE gp.obra_id = obra.obra_id';
        
        $sql = $wpdb->prepare( 'SELECT obra.*, ('.$sql_count_pictures.') AS count_pictures FROM ' . $wpdb->prefix . W_Obra_Model::$table_name . ' AS obra WHERE obra.obra_name LIKE "%s"', $s );
        
        $valid_fields = array( 'obra_id', 'obra_name', 'obra_slug', 'obra_date' );
        
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
    public static function find_by_last_obra_id() {
        global $wpdb;

        $sql = 'SELECT obra.obra_id FROM ' . $wpdb->prefix . W_Obra_Model::$table_name . ' AS obra ORDER BY obra.obra_id DESC LIMIT 1';

        return $wpdb->get_var( $sql );
    }

    /*
     * Insert obra
     */
    public static function insert( $obra ) {
        global $wpdb;

        return $wpdb->insert(
                $wpdb->prefix . W_Obra_Model::$table_name,
                array(
                    'obra_name' => $obra['obra_name'],
                    'obra_description' => $obra['obra_description'],
                    'obra_date' => $obra['obra_date'],
                    'obra_slug' => $obra['obra_slug'],
                    'obra_cover' => $obra['obra_cover']
                ),
                array( '%s', '%s', '%s', '%s', '%d' )
        );
    }
    
    /*
     * Update obra
     */
    public static function update( $obra ) {
        global $wpdb;
        
        return $wpdb->update(
                $wpdb->prefix . W_Obra_Model::$table_name,
                array(
                    'obra_name' => $obra['obra_name'],
                    'obra_description' => $obra['obra_description'],
                    'obra_date' => $obra['obra_date'],
                    'obra_slug' => $obra['obra_slug'],
                    'obra_cover' => $obra['obra_cover']
                ),
                array( 'obra_id' => $obra['obra_id'] ),
                array( '%s', '%s', '%s', '%s', '%d' ),
                array( '%d' )
        );
    }
    
    /*
     * Delete obra
     */
    public static function delete( $obra_id ) {
        global $wpdb;
        
        return $wpdb->delete(
                $wpdb->prefix . W_Obra_Model::$table_name,
                array( 'obra_id' => $obra_id ),
                array( '%d' )
        );
    }
    
}