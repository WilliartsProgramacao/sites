<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

global $w_client_loop;

$w_client_loop = array(
    'clients' => null,
    'client' => null,
    'client_picturies' => null,
    'client_picture' => null,
    'client_current' => -1,
    'client_picture_current' => -1
);

function query_clients( $s = '%', $attr = array() ) {
    global $w_client_loop;
    $w_client_loop['clients'] = W_Client_Model::find_all( $s, $attr );
    $w_client_loop['client_current'] = -1;
    $w_client_loop['client'] = null;
}

function query_clients_reset() {
    global $w_client_loop;
    $w_client_loop['clients'] = null;
    $w_client_loop['client'] = null;
    $w_client_loop['client_current'] = -1;
}

function have_clients() {
    global $w_client_loop;
    $per_page = get_option( 'posts_per_page' );
    if ( is_null( $w_client_loop['clients'] ) ) {
        $w_client_loop['clients'] = W_Client_Model::find_all( '%', 
                array( 'per_page' => $per_page, 'paged' => get_query_var( 'client_paged' ) ) );
    }
    if ( $w_client_loop['client_current'] + 1 < count( $w_client_loop['clients'] ) ) {
        $w_client_loop['client_current']++;
        return true;
    }
    $w_client_loop = array(
        'clients' => null,
        'client' => null,
        'client_picturies' => null,
        'client_picture' => null,
        'client_current' => -1,
        'client_picture_current' => -1,
    );
    return false;
}

function has_clients() {
    return W_Client_Model::count_all() > 0;
}

function the_client( $id = null ) {
    global $w_client_loop;
    if ( ! is_null( $id ) )
        $w_client_loop['client'] = W_Client_Model::find_by_id( $id );
    else
        $w_client_loop['client'] = $w_client_loop['clients'][$w_client_loop['client_current']];
}

function the_client_id() { 
    global $w_client_loop;
    if ( is_object( $w_client_loop['client'] ) && $w_client_loop['client']->client_id )
        echo $w_client_loop['client']->client_id;
    else
        return false;
}

function the_client_count_pictures() { 
    global $w_client_loop;
    if ( is_object( $w_client_loop['client'] ) )
        echo $w_client_loop['client']->count_pictures;
    else
        return false;
}

function the_client_name() { 
    global $w_client_loop;
    if ( is_object( $w_client_loop['client'] ) && $w_client_loop['client']->client_name )
        echo $w_client_loop['client']->client_name;
    else
        return false;
}

function the_client_description( $limit = false ) { 
    global $w_client_loop;
    if ( is_object( $w_client_loop['client'] ) && $w_client_loop['client']->client_description )
        echo $limit !== false ? substr( $w_client_loop['client']->client_description, 0, $limit ) : $w_client_loop['client']->client_description;
    else
        return false;
}

function the_client_date() { 
    global $w_client_loop;
    if ( is_object( $w_client_loop['client'] ) && $w_client_loop['client']->client_date )
        echo date( 'd/m/Y', strtotime( $w_client_loop['client']->client_date ) );
    else
        return false;
}

function have_client_pictures( $per_page = false, $paged = false ) {
    global $w_client_loop;
    if ( ! $w_client_loop['client'] )
        return false;
    if ( is_null( $w_client_loop['client_picturies'] ) ) {
        $w_client_loop['client_picturies'] = W_Client_Picture_Model::find_by_client( $w_client_loop['client']->client_id, array( 'per_page' => $per_page, 'paged' => $paged ) );
    }
    if ( $w_client_loop['client_picture_current']  < count( $w_client_loop['client_picturies'] ) ) {
        $w_client_loop['client_picture_current']++;
        return true;
    }
    $w_client_loop['client_picturies'] = null;
    $w_client_loop['client_picture'] = null;
    $w_client_loop['client_picture_current'] = -1;
    return false;
}

function the_client_picture() {
    global $w_client_loop;
    $w_client_loop['client_picture'] = $w_client_loop['client_picturies'][$w_client_loop['client_picture_current']];
}

function get_client_picture_file() {
    global $w_client_loop;
    
    $image_src = false;
    if ( isset( $w_client_loop['client_picture']->client_picture_filename ) )
        $image_src = W_CLIENT_UPLOAD_URL . $w_client_loop['client']->client_id . '/' . $w_client_loop['client_picture']->client_picture_filename;
    
    return $image_src;
}

function the_client_picture_file( $size = false, $atts = array() ) {
    $image_src = get_client_picture_file();
    echo get_client_render_image( $image_src, $size, $atts );
}

function get_client_picture_thumb() {
    global $w_client_loop;
    
    $image_src = false;
    if ( isset( $w_client_loop['client_picture']->client_picture_thumbnail ) )
        $image_src = W_CLIENT_UPLOAD_URL . $w_client_loop['client']->client_id . '/' . $w_client_loop['client_picture']->client_picture_thumbnail;
    
    return $image_src;
}

function the_client_picture_thumb( $size = false, $atts = array() ) {
    $image_src = get_client_picture_thumb();
    echo get_client_render_image( $image_src, $size, $atts );
}

function get_client_cover( $size = 'full' ) {
    global $w_client_loop;
    $client = $w_client_loop['client'];
    
    $client_cover = false;
    if ( ! is_null( $client ) && isset( $client->client_id ) && $client->client_cover ) {
        $client_cover = wp_get_attachment_image_src( $client->client_cover, $size );
    }

    return $client_cover ? $client_cover[0] : false;
}

function the_client_cover( $size = false, $atts = array() ) {
    $client_cover = get_client_cover( $size );
    $image_src = $client_cover ?: W_CLIENT_PLUGIN_URL . 'assets/img/no-image.png';
    echo get_client_render_image( $image_src, $size, $atts );
}

function get_client_picture_orientation() {
    global $w_client_loop;
    return $w_client_loop['client_picture']->client_picture_orientation;
}

function get_client_picture_uploaded() {
    global $w_client_loop;
    if ( is_object( $w_client_loop['client_picture'] ) && $w_client_loop['client_picture']->client_picture_uploaded )
        return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $w_client_loop['client_picture']->client_picture_uploaded );
    else
        return false;
}

function the_client_picture_uploaded() { 
    if ( get_client_picture_uploaded() )
        echo get_client_picture_uploaded();
}

function the_client_picture_orientation() {
    echo get_client_picture_orientation();
}

function the_client_picture_description( ) {
    global $w_client_loop;
    echo esc_attr( $w_client_loop['client_picture']->client_picture_description );
}

function get_client_permalink() {
    global $w_client_loop, $w_client_config;
    return home_url( $w_client_config['rewrite'] . '/' . $w_client_loop['client']->client_id .
            '/' . $w_client_loop['client']->client_slug . '/' );
}

function the_client_permalink() {
    echo get_client_permalink();
}

function the_client_pagination() {
    $big = 999999999;
    
    $posts_per_page = get_option( 'posts_per_page' );
    $max_num_pages = ceil( W_Client_Model::count_all() / $posts_per_page );

    $paginate = paginate_links( array(
        'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
        'type' => 'array',
        'total' => $max_num_pages,
        'current' => max( 1, get_query_var( 'client_paged' ) ),
        'prev_text' => __( '&laquo;' ),
        'next_text' => __( '&raquo;' ),
    ) );

    if ( $max_num_pages > 1 ) {
        $current_page = get_query_var( 'client_paged' ) ? get_query_var( 'client_paged' ) : 1;

        $html = '<ul class="pagination">';
        foreach ( $paginate as $key => $page ) {
            $class_name = ( $current_page > 1 ? $key : $key + 1 ) == $current_page ? ' class="active"' : '';
            $html .= '<li'.$class_name.'>' . $page . '</li>';
        }
        $html .= '</ul>';
        echo $html;
    }
}

function get_client_render_image( $image_src, $size = false, $atts = array() ) {
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