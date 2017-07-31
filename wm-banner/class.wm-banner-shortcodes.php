<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

/**
 * Banner shortcodes
 *
 * @author Wime
 */
class WM_Banner_Shortcodes {
    
    public static $instance;
    
    /**
     * Construct Banner Shortcodes
     */
    public function __construct() {
        if ( ! function_exists( 'the_banner' ) ) return false;
        add_shortcode( 'the_banner', array( $this, 'the_banner' ) );
        add_shortcode( 'the_banner_name', array( $this, 'the_banner_name' ) );
        add_shortcode( 'the_banner_pictures_loop', array( $this, 'the_banner_pictures_loop' ) );
        add_shortcode( 'the_banner_image', array( $this, 'the_banner_image' ) );
        add_shortcode( 'the_banner_image_link', array( $this, 'the_banner_image_link' ) );
        add_shortcode( 'the_banner_image_description', array( $this, 'the_banner_image_description' ) );
    }
    
    /**
     * Singleton
     */
    public static function getInstance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new WM_Banner_Shortcodes();
        }
        return self::$instance;
    }
    
    /**
     * Iterate the banner index in the loop.
     * 
     * @param Array $atts
     */
    public function the_banner( $atts ) {
        $a = shortcode_atts( array(
            'id' => ''
        ), $atts );
        
        the_banner( $a['id'] );
    }
    
    /**
     * Display the post title
     * 
     * @param Array $atts
     */
    public function the_banner_name() {
        ob_start();
        the_banner_name();
        return ob_get_clean();
    }
    
    /**
     * Iterate the banner index in the loop.
     * 
     * @param Array $atts
     * @param String $content
     */
    public function the_banner_pictures_loop( $atts, $content = null ) {
        ob_start();
        
        if ( ! is_null( $content ) ) {
            while ( have_banner_pictures() ) {
                the_banner_picture();
                echo do_shortcode( $content );
            }
        }
        
        return ob_get_clean();
    }
    
    /**
     * Display the banner image
     */
    public function the_banner_image( $atts ) {
        ob_start();
        the_banner_picture_file( false, $atts );
        return ob_get_clean();
    }
    
    /**
     * Display the banner image description
     * 
     */
    public function the_banner_image_description() {
        ob_start();
        the_banner_picture_description();
        return ob_get_clean();
    }
    
    /**
     * Display the banner link
     * 
     * @param Array $atts
     * @param String $content
     */
    public function the_banner_image_link( $atts, $content = null ) {
        ob_start();
        
        if ( ! is_null( $content ) ) {
            $a_tag = '<a href="' . get_banner_picture_link() . '" target="' . get_banner_picture_target() . '"';
            foreach ( $atts as $key => $value ) {
                $a_tag .= " $key=\"$value\"";
            }
            echo $a_tag . '>' . do_shortcode( $content ) . '</a>';
        }
        
        return ob_get_clean();
    }
    
}
