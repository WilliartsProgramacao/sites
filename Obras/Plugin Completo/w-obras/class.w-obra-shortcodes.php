<?php

/**
 * Obra shortcodes
 *
 * @author Williarts
 */
class W_Obra_Shortcodes {
    
    public static $instance;
    
    /**
     * Construct Obra Shortcodes
     */
    public function __construct() {
        if ( ! function_exists( 'the_obra' ) ) return false;
        add_shortcode( 'query_obra', array( $this, 'query_obra' ) );
        add_shortcode( 'the_obra', array( $this, 'the_obra' ) );
        add_shortcode( 'the_obra_name', array( $this, 'the_obra_name' ) );
        add_shortcode( 'the_obra_description', array( $this, 'the_obra_description' ) );
        add_shortcode( 'the_obra_date', array( $this, 'the_obra_date' ) );
        add_shortcode( 'the_obra_link', array( $this, 'the_obra_permalink' ) );
        add_shortcode( 'the_obra_pictures_loop', array( $this, 'the_obra_pictures_loop' ) );
        add_shortcode( 'the_obra_image', array( $this, 'the_obra_image' ) );
        add_shortcode( 'the_obra_image_cover', array( $this, 'the_obra_image_cover' ) );
        add_shortcode( 'the_obra_image_link', array( $this, 'the_obra_image_link' ) );
        add_shortcode( 'the_obra_image_description', array( $this, 'the_obra_image_description' ) );
    }
    
    /**
     * Singleton
     */
    public static function getInstance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new W_Obra_Shortcodes();
        }
        return self::$instance;
    }
    
    /**
     * Query obras in the database.
     * 
     * @param Array $atts
     */
    public function query_obra( $atts, $content = null ) {
        $a = shortcode_atts( array(
            'pid' => false,
            'orderby' => '',
            'per_page' => get_option( 'posts_per_page' ),
            'paged' => 1
        ), $atts );
        
        $orderby = explode( ",", $a['orderby'] );
        
        ob_start();
        
        if ( $a['pid'] ) {
            the_obra( $a['pid'] );
        } else {
            query_obras( '%', array(
                'per_page' => $a['per_page'],
                'paged' => $a['paged'],
                'orderby' => $orderby
            ) );
            while ( have_obras() ) : the_obra();
                echo do_shortcode( $content );
            endwhile;
        }
        
        return ob_get_clean();
    }
    
    /**
     * Iterate the obra index in the loop.
     * 
     * @param Array $atts
     */
    public function the_obra( $atts ) {
        $a = shortcode_atts( array(
            'id' => ''
        ), $atts );
        
        the_obra( $a['id'] );
    }
    
    /**
     * Display the obra name
     * 
     * @param Array $atts
     */
    public function the_obra_name() {
        ob_start();
        the_obra_name();
        return ob_get_clean();
    }
    
    /**
     * Display the obra description
     * 
     */
    public function the_obra_description() {
        ob_start();
        the_obra_description();
        return ob_get_clean();
    }
    
    /**
     * Display the obra date
     * 
     * @param Array $atts
     */
    public function the_obra_date() {
        ob_start();
        the_obra_date();
        return ob_get_clean();
    }
    
    /**
     * Display the obra link
     * 
     * @param Array $atts
     * @param String $content
     */
    public function the_obra_permalink( $atts, $content = null ) {
        ob_start();
        
        if ( ! is_null( $content ) ) {
            $a_tag = '<a href="' . get_obra_permalink() . '"';
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
     * Iterate the obra index in the loop.
     * 
     * @param Array $atts
     * @param String $content
     */
    public function the_obra_pictures_loop( $atts, $content = null ) {
        ob_start();
        
        if ( ! is_null( $content ) ) {
            while ( have_obra_pictures() ) {
                the_obra_picture();
                echo do_shortcode( $content );
            }
        }
        
        return ob_get_clean();
    }
    
    /**
     * Display the obra image
     */
    public function the_obra_image() {
        ob_start();
        the_obra_picture_file();
        return ob_get_clean();
    }
    
    /**
     * Display the obra image
     */
    public function the_obra_image_cover( $atts ) {
        $a = shortcode_atts( array(
            'size' => false
        ), $atts );
        
        ob_start();
        the_obra_cover( $a['size'] );
        return ob_get_clean();
    }
    
    /**
     * Display the obra cover description
     */
    public function the_obra_cover_description( $atts ) {
        $a = shortcode_atts( array(
            'limit_char' => false
        ), $atts );
        
        ob_start();
        the_obra_cover_description( $a['limit_char'] );
        return ob_get_clean();
    }
    
    /**
     * Display the obra link
     * 
     * @param Array $atts
     * @param String $content
     */
    public function the_obra_image_link( $atts, $content = null ) {
        ob_start();
        
        if ( ! is_null( $content ) ) {
            $a_tag = '<a href="' . get_obra_picture_file() . '"';
            foreach ( $atts as $key => $value ) {
                $a_tag .= " $key=\"$value\"";
            }
            echo $a_tag . '>' . do_shortcode( $content ) . '</a>';
        }
        
        return ob_get_clean();
    }
    
}