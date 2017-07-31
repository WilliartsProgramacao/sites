<?php

/**
 * Event shortcodes
 *
 * @author Wime
 */
class WM_Event_Shortcodes {
    
    public static $instance;
    
    /**
     * Construct Event Shortcodes
     */
    public function __construct() {
        if ( ! function_exists( 'the_event' ) ) return false;
        add_shortcode( 'query_event', array( $this, 'query_event' ) );
        add_shortcode( 'the_event', array( $this, 'the_event' ) );
        add_shortcode( 'the_event_name', array( $this, 'the_event_name' ) );
        add_shortcode( 'the_event_description', array( $this, 'the_event_description' ) );
        add_shortcode( 'the_event_date', array( $this, 'the_event_date' ) );
        add_shortcode( 'the_event_link', array( $this, 'the_event_permalink' ) );
        add_shortcode( 'the_event_pictures_loop', array( $this, 'the_event_pictures_loop' ) );
        add_shortcode( 'the_event_image', array( $this, 'the_event_image' ) );
        add_shortcode( 'the_event_image_cover', array( $this, 'the_event_image_cover' ) );
        add_shortcode( 'the_event_image_link', array( $this, 'the_event_image_link' ) );
        add_shortcode( 'the_event_image_description', array( $this, 'the_event_image_description' ) );
    }
    
    /**
     * Singleton
     */
    public static function getInstance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new WM_Event_Shortcodes();
        }
        return self::$instance;
    }
    
    /**
     * Query events in the database.
     * 
     * @param Array $atts
     */
    public function query_event( $atts, $content = null ) {
        $a = shortcode_atts( array(
            'pid' => false,
            'orderby' => '',
            'per_page' => get_option( 'posts_per_page' ),
            'paged' => 1
        ), $atts );
        
        $orderby = explode( ",", $a['orderby'] );
        
        ob_start();
        
        if ( $a['pid'] ) {
            the_event( $a['pid'] );
        } else {
            query_events( '%', array(
                'per_page' => $a['per_page'],
                'paged' => $a['paged'],
                'orderby' => $orderby
            ) );
            while ( have_events() ) : the_event();
                echo do_shortcode( $content );
            endwhile;
        }
        
        return ob_get_clean();
    }
    
    /**
     * Iterate the event index in the loop.
     * 
     * @param Array $atts
     */
    public function the_event( $atts ) {
        $a = shortcode_atts( array(
            'id' => ''
        ), $atts );
        
        the_event( $a['id'] );
    }
    
    /**
     * Display the event name
     * 
     * @param Array $atts
     */
    public function the_event_name() {
        ob_start();
        the_event_name();
        return ob_get_clean();
    }
    
    /**
     * Display the event description
     * 
     */
    public function the_event_description() {
        ob_start();
        the_event_description();
        return ob_get_clean();
    }
    
    /**
     * Display the event date
     * 
     * @param Array $atts
     */
    public function the_event_date() {
        ob_start();
        the_event_date();
        return ob_get_clean();
    }
    
    /**
     * Display the event link
     * 
     * @param Array $atts
     * @param String $content
     */
    public function the_event_permalink( $atts, $content = null ) {
        ob_start();
        
        if ( ! is_null( $content ) ) {
            $a_tag = '<a href="' . get_event_permalink() . '"';
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
     * Iterate the event index in the loop.
     * 
     * @param Array $atts
     * @param String $content
     */
    public function the_event_pictures_loop( $atts, $content = null ) {
        ob_start();
        
        if ( ! is_null( $content ) ) {
            while ( have_event_pictures() ) {
                the_event_picture();
                echo do_shortcode( $content );
            }
        }
        
        return ob_get_clean();
    }
    
    /**
     * Display the event image
     */
    public function the_event_image() {
        ob_start();
        the_event_picture_file();
        return ob_get_clean();
    }
    
    /**
     * Display the event image
     */
    public function the_event_image_cover( $atts ) {
        $a = shortcode_atts( array(
            'size' => false
        ), $atts );
        
        ob_start();
        the_event_cover( $a['size'] );
        return ob_get_clean();
    }
    
    /**
     * Display the event cover description
     */
    public function the_event_cover_description( $atts ) {
        $a = shortcode_atts( array(
            'limit_char' => false
        ), $atts );
        
        ob_start();
        the_event_cover_description( $a['limit_char'] );
        return ob_get_clean();
    }
    
    /**
     * Display the event link
     * 
     * @param Array $atts
     * @param String $content
     */
    public function the_event_image_link( $atts, $content = null ) {
        ob_start();
        
        if ( ! is_null( $content ) ) {
            $a_tag = '<a href="' . get_event_picture_file() . '"';
            foreach ( $atts as $key => $value ) {
                $a_tag .= " $key=\"$value\"";
            }
            echo $a_tag . '>' . do_shortcode( $content ) . '</a>';
        }
        
        return ob_get_clean();
    }
    
}