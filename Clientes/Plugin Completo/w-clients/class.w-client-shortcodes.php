<?php

/**
 * Client shortcodes
 *
 * @author Williarts
 */
class W_Client_Shortcodes {
    
    public static $instance;
    
    /**
     * Construct Client Shortcodes
     */
    public function __construct() {
        if ( ! function_exists( 'the_client' ) ) return false;
        add_shortcode( 'query_client', array( $this, 'query_client' ) );
        add_shortcode( 'the_client', array( $this, 'the_client' ) );
        add_shortcode( 'the_client_name', array( $this, 'the_client_name' ) );
        add_shortcode( 'the_client_description', array( $this, 'the_client_description' ) );
        add_shortcode( 'the_client_date', array( $this, 'the_client_date' ) );
        add_shortcode( 'the_client_link', array( $this, 'the_client_permalink' ) );
        add_shortcode( 'the_client_pictures_loop', array( $this, 'the_client_pictures_loop' ) );
        add_shortcode( 'the_client_image', array( $this, 'the_client_image' ) );
        add_shortcode( 'the_client_image_cover', array( $this, 'the_client_image_cover' ) );
        add_shortcode( 'the_client_image_link', array( $this, 'the_client_image_link' ) );
        add_shortcode( 'the_client_image_description', array( $this, 'the_client_image_description' ) );
    }
    
    /**
     * Singleton
     */
    public static function getInstance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new W_Client_Shortcodes();
        }
        return self::$instance;
    }
    
    /**
     * Query clients in the database.
     * 
     * @param Array $atts
     */
    public function query_client( $atts, $content = null ) {
        $a = shortcode_atts( array(
            'pid' => false,
            'orderby' => '',
            'per_page' => get_option( 'posts_per_page' ),
            'paged' => 1
        ), $atts );
        
        $orderby = explode( ",", $a['orderby'] );
        
        ob_start();
        
        if ( $a['pid'] ) {
            the_client( $a['pid'] );
        } else {
            query_clients( '%', array(
                'per_page' => $a['per_page'],
                'paged' => $a['paged'],
                'orderby' => $orderby
            ) );
            while ( have_clients() ) : the_client();
                echo do_shortcode( $content );
            endwhile;
        }
        
        return ob_get_clean();
    }
    
    /**
     * Iterate the client index in the loop.
     * 
     * @param Array $atts
     */
    public function the_client( $atts ) {
        $a = shortcode_atts( array(
            'id' => ''
        ), $atts );
        
        the_client( $a['id'] );
    }
    
    /**
     * Display the client name
     * 
     * @param Array $atts
     */
    public function the_client_name() {
        ob_start();
        the_client_name();
        return ob_get_clean();
    }
    
    /**
     * Display the client description
     * 
     */
    public function the_client_description() {
        ob_start();
        the_client_description();
        return ob_get_clean();
    }
    
    /**
     * Display the client date
     * 
     * @param Array $atts
     */
    public function the_client_date() {
        ob_start();
        the_client_date();
        return ob_get_clean();
    }
    
    /**
     * Display the client link
     * 
     * @param Array $atts
     * @param String $content
     */
    public function the_client_permalink( $atts, $content = null ) {
        ob_start();
        
        if ( ! is_null( $content ) ) {
            $a_tag = '<a href="' . get_client_permalink() . '"';
            if ( is_array( $atts ) ) {
                foreach ( $atts as $key => $value ) {
                    $a_tag .= " $key=\"$value\"";
                }
            }
            echo $a_tag . '>' . do_shortcode( $content ) . '</a>';
        }
        
        return ob_get_clean();
    }
    
    /**
     * Iterate the client index in the loop.
     * 
     * @param Array $atts
     * @param String $content
     */
    public function the_client_pictures_loop( $atts, $content = null ) {
        ob_start();
        
        if ( ! is_null( $content ) ) {
            while ( have_client_pictures() ) {
                the_client_picture();
                echo do_shortcode( $content );
            }
        }
        
        return ob_get_clean();
    }
    
    /**
     * Display the client image
     */
    public function the_client_image() {
        ob_start();
        the_client_picture_file();
        return ob_get_clean();
    }
    
    /**
     * Display the client image
     */
    public function the_client_image_cover( $atts ) {
        $a = shortcode_atts( array(
            'size' => false
        ), $atts );
        
        ob_start();
        the_client_cover( $a['size'] );
        return ob_get_clean();
    }
    
    /**
     * Display the client cover description
     */
    public function the_client_cover_description( $atts ) {
        $a = shortcode_atts( array(
            'limit_char' => false
        ), $atts );
        
        ob_start();
        the_client_cover_description( $a['limit_char'] );
        return ob_get_clean();
    }
    
    /**
     * Display the client link
     * 
     * @param Array $atts
     * @param String $content
     */
    public function the_client_image_link( $atts, $content = null ) {
        ob_start();
        
        if ( ! is_null( $content ) ) {
            $a_tag = '<a href="' . get_client_picture_file() . '"';
            foreach ( $atts as $key => $value ) {
                $a_tag .= " $key=\"$value\"";
            }
            echo $a_tag . '>' . do_shortcode( $content ) . '</a>';
        }
        
        return ob_get_clean();
    }
    
}