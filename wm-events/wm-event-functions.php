<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

global $wm_event_loop;

$wm_event_loop = array(
    'events' => null,
    'event' => null,
    'event_picturies' => null,
    'event_picture' => null,
    'event_current' => -1,
    'event_picture_current' => -1
);

function query_events( $s = '%', $attr = array() ) {
    global $wm_event_loop;
    $wm_event_loop['events'] = WM_Event_Model::find_all( $s, $attr );
    $wm_event_loop['event_current'] = -1;
    $wm_event_loop['event'] = null;
}

function query_events_reset() {
    global $wm_event_loop;
    $wm_event_loop['events'] = null;
    $wm_event_loop['event'] = null;
    $wm_event_loop['event_current'] = -1;
}

function have_events() {
    global $wm_event_loop;
    $per_page = get_option( 'posts_per_page' );
    if ( is_null( $wm_event_loop['events'] ) ) {
        $wm_event_loop['events'] = WM_Event_Model::find_all( '%', 
                array( 'per_page' => $per_page, 'paged' => get_query_var( 'event_paged' ) ) );
    }
    if ( $wm_event_loop['event_current'] + 1 < count( $wm_event_loop['events'] ) ) {
        $wm_event_loop['event_current']++;
        return true;
    }
    $wm_event_loop = array(
        'events' => null,
        'event' => null,
        'event_picturies' => null,
        'event_picture' => null,
        'event_current' => -1,
        'event_picture_current' => -1,
    );
    return false;
}

function has_events() {
    return WM_Event_Model::count_all() > 0;
}

function the_event( $id = null ) {
    global $wm_event_loop;
    if ( ! is_null( $id ) )
        $wm_event_loop['event'] = WM_Event_Model::find_by_id( $id );
    else
        $wm_event_loop['event'] = $wm_event_loop['events'][$wm_event_loop['event_current']];
}

function the_event_id() { 
    global $wm_event_loop;
    if ( is_object( $wm_event_loop['event'] ) && $wm_event_loop['event']->event_id )
        echo $wm_event_loop['event']->event_id;
    else
        return false;
}

function the_event_count_pictures() { 
    global $wm_event_loop;
    if ( is_object( $wm_event_loop['event'] ) )
        echo $wm_event_loop['event']->count_pictures;
    else
        return false;
}

function the_event_name() { 
    global $wm_event_loop;
    if ( is_object( $wm_event_loop['event'] ) && $wm_event_loop['event']->event_name )
        echo $wm_event_loop['event']->event_name;
    else
        return false;
}

function the_event_description( $limit = false ) { 
    global $wm_event_loop;
    if ( is_object( $wm_event_loop['event'] ) && $wm_event_loop['event']->event_description )
        echo $limit !== false ? substr( $wm_event_loop['event']->event_description, 0, $limit ) : $wm_event_loop['event']->event_description;
    else
        return false;
}

function the_event_date() { 
    global $wm_event_loop;
    if ( is_object( $wm_event_loop['event'] ) && $wm_event_loop['event']->event_date )
        echo date( 'd/m/Y', strtotime( $wm_event_loop['event']->event_date ) );
    else
        return false;
}

function have_event_pictures( $per_page = false, $paged = false ) {
    global $wm_event_loop;
    if ( ! $wm_event_loop['event'] )
        return false;
    if ( is_null( $wm_event_loop['event_picturies'] ) ) {
        $wm_event_loop['event_picturies'] = WM_Event_Picture_Model::find_by_event( $wm_event_loop['event']->event_id, array( 'per_page' => $per_page, 'paged' => $paged ) );
    }
    if ( $wm_event_loop['event_picture_current']  < count( $wm_event_loop['event_picturies'] ) ) {
        $wm_event_loop['event_picture_current']++;
        return true;
    }
    $wm_event_loop['event_picturies'] = null;
    $wm_event_loop['event_picture'] = null;
    $wm_event_loop['event_picture_current'] = -1;
    return false;
}

function the_event_picture() {
    global $wm_event_loop;
    $wm_event_loop['event_picture'] = $wm_event_loop['event_picturies'][$wm_event_loop['event_picture_current']];
}

function get_event_picture_file() {
    global $wm_event_loop;
    
    $image_src = false;
    if ( isset( $wm_event_loop['event_picture']->event_picture_filename ) )
        $image_src = WM_EVENT_UPLOAD_URL . $wm_event_loop['event']->event_id . '/' . $wm_event_loop['event_picture']->event_picture_filename;
    
    return $image_src;
}

function the_event_picture_file( $size = false, $atts = array() ) {
    $image_src = get_event_picture_file();
    echo get_event_render_image( $image_src, $size, $atts );
}

function get_event_picture_thumb() {
    global $wm_event_loop;
    
    $image_src = false;
    if ( isset( $wm_event_loop['event_picture']->event_picture_thumbnail ) )
        $image_src = WM_EVENT_UPLOAD_URL . $wm_event_loop['event']->event_id . '/' . $wm_event_loop['event_picture']->event_picture_thumbnail;
    
    return $image_src;
}

function the_event_picture_thumb( $size = false, $atts = array() ) {
    $image_src = get_event_picture_thumb();
    echo get_event_render_image( $image_src, $size, $atts );
}

function get_event_cover( $size = 'full' ) {
    global $wm_event_loop;
    $event = $wm_event_loop['event'];
    
    $event_cover = false;
    if ( ! is_null( $event ) && isset( $event->event_id ) && $event->event_cover ) {
        $event_cover = wp_get_attachment_image_src( $event->event_cover, $size );
    }

    return $event_cover ? $event_cover[0] : false;
}

function the_event_cover( $size = false, $atts = array() ) {
    $event_cover = get_event_cover( $size );
    $image_src = $event_cover ?: WM_EVENT_PLUGIN_URL . 'assets/img/no-image.png';
    echo get_event_render_image( $image_src, $size, $atts );
}

function get_event_picture_orientation() {
    global $wm_event_loop;
    return $wm_event_loop['event_picture']->event_picture_orientation;
}

function get_event_picture_uploaded() {
    global $wm_event_loop;
    if ( is_object( $wm_event_loop['event_picture'] ) && $wm_event_loop['event_picture']->event_picture_uploaded )
        return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $wm_event_loop['event_picture']->event_picture_uploaded );
    else
        return false;
}

function the_event_picture_uploaded() { 
    if ( get_event_picture_uploaded() )
        echo get_event_picture_uploaded();
}

function the_event_picture_orientation() {
    echo get_event_picture_orientation();
}

function the_event_picture_description( ) {
    global $wm_event_loop;
    echo esc_attr( $wm_event_loop['event_picture']->event_picture_description );
}

function get_event_permalink() {
    global $wm_event_loop, $wm_event_config;
    return home_url( $wm_event_config['rewrite'] . '/' . $wm_event_loop['event']->event_id .
            '/' . $wm_event_loop['event']->event_slug . '/' );
}

function the_event_permalink() {
    echo get_event_permalink();
}

function the_event_pagination() {
    $big = 999999999;
    
    $posts_per_page = get_option( 'posts_per_page' );
    $max_num_pages = ceil( WM_Event_Model::count_all() / $posts_per_page );

    $paginate = paginate_links( array(
        'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
        'type' => 'array',
        'total' => $max_num_pages,
        'current' => max( 1, get_query_var( 'event_paged' ) ),
        'prev_text' => __( '&laquo;' ),
        'next_text' => __( '&raquo;' ),
    ) );

    if ( $max_num_pages > 1 ) {
        $current_page = get_query_var( 'event_paged' ) ? get_query_var( 'event_paged' ) : 1;

        $html = '<ul class="pagination">';
        foreach ( $paginate as $key => $page ) {
            $class_name = ( $current_page > 1 ? $key : $key + 1 ) == $current_page ? ' class="active"' : '';
            $html .= '<li'.$class_name.'>' . $page . '</li>';
        }
        $html .= '</ul>';
        echo $html;
    }
}

function get_event_render_image( $image_src, $size = false, $atts = array() ) {
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