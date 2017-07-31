<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

global $wm_banner_loop;

$wm_banner_loop = array(
    'banners' => null,
    'banner' => null,
    'banner_picturies' => null,
    'banner_picture' => null,
    'banner_current' => -1,
    'banner_picture_current' => -1,
);

function have_banners() {
    global $wm_banner_loop;
    $per_page = get_option( 'posts_per_page' );
    if ( is_null( $wm_banner_loop['banners'] ) ) {
        $wm_banner_loop['banners'] = WM_Banner_Model::find_all( '%', 
                array( 'per_page' => $per_page, 'paged' => get_query_var( 'banner_paged' ) ) );
    }
    if ( $wm_banner_loop['banner_current'] + 1 < count( $wm_banner_loop['banners'] ) ) {
        $wm_banner_loop['banner_current']++;
        return true;
    }
    $wm_banner_loop = array(
        'banners' => null,
        'banner' => null,
        'banner_picturies' => null,
        'banner_picture' => null,
        'banner_current' => -1,
        'banner_picture_current' => -1,
    );
    return false;
}

function the_banner( $id = null ) {
    global $wm_banner_loop;
    if ( ! is_null( $id ) )
        $wm_banner_loop['banner'] = WM_Banner_Model::find_by_id( $id );
    else
        $wm_banner_loop['banner'] = $wm_banner_loop['banners'][$wm_banner_loop['banner_current']];
}

function banner_exists() {
    global $wm_banner_loop;
    return $wm_banner_loop['banner'];
}

function the_banner_id() { 
    global $wm_banner_loop;
    if ( is_object( $wm_banner_loop['banner'] ) && $wm_banner_loop['banner']->banner_id )
        echo $wm_banner_loop['banner']->banner_id;
    else
        return false;
}

function the_banner_count_pictures() { 
    if ( get_banner_count_pictures() !== false )
        echo get_banner_count_pictures();
}

function get_banner_count_pictures() { 
    global $wm_banner_loop;
    if ( is_object( $wm_banner_loop['banner'] ) ) {
        if ( is_null( $wm_banner_loop['banner_picturies'] ) ) {
            $wm_banner_loop['banner_picturies'] = WM_Banner_Picture_Model::find_by_banner( $wm_banner_loop['banner']->banner_id );
        }
    }
    return ! is_null( $wm_banner_loop['banner_picturies'] ) ? count( $wm_banner_loop['banner_picturies'] ) : 0;
}

function the_banner_name() { 
    global $wm_banner_loop;
    if ( is_object( $wm_banner_loop['banner'] ) && $wm_banner_loop['banner']->banner_name )
        echo $wm_banner_loop['banner']->banner_name;
    else
        return false;
}

function have_banner_pictures() {
    global $wm_banner_loop;
    if ( ! $wm_banner_loop['banner'] )
        return false;
    if ( is_null( $wm_banner_loop['banner_picturies'] ) ) {
        $wm_banner_loop['banner_picturies'] = WM_Banner_Picture_Model::find_by_banner( $wm_banner_loop['banner']->banner_id );
    }
    if ( $wm_banner_loop['banner_picture_current'] + 1 < count( $wm_banner_loop['banner_picturies'] ) ) {
        $wm_banner_loop['banner_picture_current']++;
        return true;
    }
    $wm_banner_loop['banner_picturies'] = null;
    $wm_banner_loop['banner_picture'] = null;
    $wm_banner_loop['banner_picture_current'] = -1;
    return false;
}

function the_banner_picture() {
    global $wm_banner_loop;
    $wm_banner_loop['banner_picture'] = $wm_banner_loop['banner_picturies'][$wm_banner_loop['banner_picture_current']];
}

function get_banner_picture_file() {
    global $wm_banner_loop;
    
    $image_src = false;
    if ( isset( $wm_banner_loop['banner_picture']->banner_picture_filename ) )
        $image_src = WM_BANNER_UPLOAD_URL . $wm_banner_loop['banner_picture']->banner_picture_filename;
    
    return $image_src;
}

function the_banner_picture_file( $size = false, $atts = array() ) {
    $image_src = get_banner_picture_file();
    echo get_banner_render_image( $image_src, $size, $atts );
}

function get_banner_picture_thumb() {
    global $wm_banner_loop;
    return WM_BANNER_UPLOAD_URL . $wm_banner_loop['banner_picture']->banner_picture_thumbnail;
}

function the_banner_picture_thumb( $size = false ) {
    $file_info = pathinfo( get_banner_picture_thumb() );
    $image_src = get_banner_picture_thumb();
    
    echo '<img src="' . $image_src . '"' .
            ( is_array( $size ) ? ' style="width:'.$size[0] . ($size[0] != 'auto' ? 'px' : '')
                               .'; height:'.$size[1] . ($size[1] != 'auto' ? 'px' : '').'"' : '' ) .
            ' alt="' . $file_info['filename'] . '"' .
            ' title="' . $file_info['filename'] . '">';
}

function get_banner_cover() {
    global $wm_banner_loop;
    return $wm_banner_loop['banner']->banner_cover ? WM_BANNER_UPLOAD_URL . $wm_banner_loop['banner']->banner_cover : false;
}

function the_banner_cover( $size = false ) {
    $file_info = pathinfo( get_banner_cover() );
    $image_src = get_banner_cover() ? get_banner_cover() : WM_BANNER_PLUGIN_URL . 'assets/img/no-image.png';
    
    echo '<img src="' . $image_src . '"' .
            ( is_array( $size ) ? ' style="width:'.$size[0] . ($size[0] != 'auto' ? 'px' : '')
                               .'; height:'.$size[1] . ($size[1] != 'auto' ? 'px' : '').'"' : '' ) .
            ' alt="' . $file_info['filename'] . '"' .
            ' title="' . $file_info['filename'] . '">';
}

function get_banner_picture_orientation() {
    global $wm_banner_loop;
    return $wm_banner_loop['banner_picture']->banner_picture_orientation;
}

function get_banner_picture_uploaded() {
    global $wm_banner_loop;
    if ( is_object( $wm_banner_loop['banner_picture'] ) && $wm_banner_loop['banner_picture']->banner_picture_uploaded )
        return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $wm_banner_loop['banner_picture']->banner_picture_uploaded );
    else
        return false;
}

function the_banner_picture_uploaded() { 
    if ( get_banner_picture_uploaded() )
        echo get_banner_picture_uploaded();
}

function the_banner_picture_orientation() {
    echo get_banner_picture_orientation();
}

function get_banner_picture_description() {
    global $wm_banner_loop;
    if ( is_object( $wm_banner_loop['banner_picture'] ) && $wm_banner_loop['banner_picture']->banner_picture_description )
        return esc_attr( $wm_banner_loop['banner_picture']->banner_picture_description );
    return false;
}

function the_banner_picture_description() {
    echo get_banner_picture_description();
}

function get_banner_picture_link() {
    global $wm_banner_loop;
    if ( is_object( $wm_banner_loop['banner_picture'] ) && $wm_banner_loop['banner_picture']->banner_picture_link )
        return esc_attr( $wm_banner_loop['banner_picture']->banner_picture_link );
    return false;
}

function get_banner_picture_target() {
    global $wm_banner_loop;
    if ( is_object( $wm_banner_loop['banner_picture'] ) && $wm_banner_loop['banner_picture']->banner_picture_target )
        return esc_attr( $wm_banner_loop['banner_picture']->banner_picture_target );
    return false;
}

function get_banner_render_image( $image_src, $size = false, $atts = array() ) {
    global $_wp_additional_image_sizes;
    
    $defaults = array(
        'src' => $image_src
    );
    
    $a = wp_parse_args( $atts, $defaults );
    
    if ( ! isset( $a['style'] ) ) {
        $image_width = false;
        $image_height = false;
        
        if ( is_array( $size ) && count( $size ) == 2 ) {   
            $image_width = $size[0] != 'auto' ? absint( $size[0] ) . 'px' : $size[0];
            $image_height = $size[1] != 'auto' ? absint( $size[1] ) . 'px' : $size[1];
            
        } elseif ( is_string( $size ) && isset( $_wp_additional_image_sizes[ $size ] ) ) {
            $image_width = $size['width'] . 'px';
            $image_height = $size['height'] . 'px';
        }
        
        if ( $image_width !== false && $image_height !== false ) {
            $a['style'] = "width: {$image_width}; height: {$image_height}";
        }
    }
    
    $html = "<img";
    foreach ( $a as $attr_name => $attr_value ) {
        $attr_name = sanitize_key( $attr_name );
        $html .= " {$attr_name}=\"{$attr_value}\"";
    }
    $html .= ">";
    
    return $html;
}