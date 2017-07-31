<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

/**
 * This class is composed of methods that perform filters and transactions in the database
 *
 * @author Wime
 */
class W_Obra_Picture_Model {
    
    public static $table_name = 'obra_picture';
    
    /*
     * Count records
     */
    public static function count_all( $eid ) {
        global $wpdb;
        $sql = $sql = $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . W_Obra_Picture_Model::$table_name . ' AS obra_picture WHERE obra_picture.obra_id = %d', $eid );
        return $wpdb->get_var( $sql );
    }

    /*
     * Find a obra_picture by id
     */
    public static function find_by_id( $epid ) {
        global $wpdb;
        $sql = $wpdb->prepare( 'SELECT picture.* FROM ' . $wpdb->prefix . W_Obra_Picture_Model::$table_name . ' AS picture WHERE picture.obra_picture_id = %d', $epid );
        return $wpdb->get_row( $sql );
    }
    
    /*
     * Find obra_picture by obra
     */
    public static function find_by_obra( $eid, $attr = array() ) {
        global $wpdb;
        
        $default_attr = array(
            'per_page' => false,
            'paged' => false
        );
        $attr = wp_parse_args( $attr, $default_attr );

        extract( $attr ); // Extract array to variables
        
        $offset = (int) $attr['paged'] > 1 ? ( (int) $paged - 1 ) * (int) $per_page : false;
        $sql = $wpdb->prepare( 'SELECT picture.* FROM ' . $wpdb->prefix . W_Obra_Picture_Model::$table_name . ' AS picture WHERE picture.obra_id = %d', $eid );
        if ( $per_page ) {
            $sql .= $wpdb->prepare( ' LIMIT %d', $per_page );
            if ( $offset ) {
                $sql .= $wpdb->prepare( ' OFFSET %d', $offset );
            }
        }
        
        return $wpdb->get_results( $sql );
    }
    
    /*
     * Insert obra
     */
    public static function insert( $obra_picture ) {
        global $wpdb;

        return $wpdb->insert(
                $wpdb->prefix . W_Obra_Picture_Model::$table_name,
                array(
                    'obra_id' => $obra_picture['obra_id'],
                    'obra_picture_filename' => $obra_picture['obra_picture_filename'],
                    'obra_picture_thumbnail' => $obra_picture['obra_picture_thumbnail'],
                    'obra_picture_orientation' => $obra_picture['obra_picture_orientation'],
                    'obra_picture_description' => $obra_picture['obra_picture_description'],
                    'obra_picture_uploaded' => $obra_picture['obra_picture_uploaded']
                ),
                array( '%d', '%s', '%s', '%s', '%s', '%s' )
        );
    }
    
    /*
     * Update obra
     */
    public static function update( $obra_picture, $obra_picture_id ) {
        global $wpdb;
        
        return $wpdb->update(
                $wpdb->prefix . W_Obra_Picture_Model::$table_name,
                array(
                    'obra_picture_description' => $obra_picture['obra_picture_description'],
                ),
                array( 'obra_picture_id' => $obra_picture_id ),
                array( '%s' ),
                array( '%d' )
        );
    }
    
    /*
     * Delete obra
     */
    public static function delete( $epid ) {
        global $wpdb;
        
        return $wpdb->delete(
                $wpdb->prefix . W_Obra_Picture_Model::$table_name,
                array( 'obra_picture_id' => $epid ),
                array( '%d' )
        );
    }
    
}