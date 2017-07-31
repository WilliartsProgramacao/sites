<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

global $w_obra_loop;

$w_obra_loop = array(
    'obras' => null,
    'obra' => null,
    'obra_picturies' => null,
    'obra_picture' => null,
    'obra_current' => -1,
    'obra_picture_current' => -1
);

function query_obras( $s = '%', $attr = array() ) {
    global $w_obra_loop;
    $w_obra_loop['obras'] = W_Obra_Model::find_all( $s, $attr );
    $w_obra_loop['obra_current'] = -1;
    $w_obra_loop['obra'] = null;
}

function query_obras_reset() {
    global $w_obra_loop;
    $w_obra_loop['obras'] = null;
    $w_obra_loop['obra'] = null;
    $w_obra_loop['obra_current'] = -1;
}

function have_obras() {
    global $w_obra_loop;
    $per_page = get_option( 'posts_per_page' );
    if ( is_null( $w_obra_loop['obras'] ) ) {
        $w_obra_loop['obras'] = W_Obra_Model::find_all( '%', 
                array( 'per_page' => $per_page, 'paged' => get_query_var( 'obra_paged' ) ) );
    }
    if ( $w_obra_loop['obra_current'] + 1 < count( $w_obra_loop['obras'] ) ) {
        $w_obra_loop['obra_current']++;
        return true;
    }
    $w_obra_loop = array(
        'obras' => null,
        'obra' => null,
        'obra_picturies' => null,
        'obra_picture' => null,
        'obra_current' => -1,
        'obra_picture_current' => -1,
    );
    return false;
}

function has_obras() {
    return W_Obra_Model::count_all() > 0;
}

function the_obra( $id = null ) {
    global $w_obra_loop;
    if ( ! is_null( $id ) )
        $w_obra_loop['obra'] = W_Obra_Model::find_by_id( $id );
    else
        $w_obra_loop['obra'] = $w_obra_loop['obras'][$w_obra_loop['obra_current']];
}

function the_obra_id() { 
    global $w_obra_loop;
    if ( is_object( $w_obra_loop['obra'] ) && $w_obra_loop['obra']->obra_id )
        echo $w_obra_loop['obra']->obra_id;
    else
        return false;
}

function the_obra_count_pictures() { 
    global $w_obra_loop;
    if ( is_object( $w_obra_loop['obra'] ) )
        echo $w_obra_loop['obra']->count_pictures;
    else
        return false;
}

function the_obra_name() { 
    global $w_obra_loop;
    if ( is_object( $w_obra_loop['obra'] ) && $w_obra_loop['obra']->obra_name )
        echo $w_obra_loop['obra']->obra_name;
    else
        return false;
}

function the_obra_description( $limit = false ) { 
    global $w_obra_loop;
    if ( is_object( $w_obra_loop['obra'] ) && $w_obra_loop['obra']->obra_description )
        echo $limit !== false ? substr( $w_obra_loop['obra']->obra_description, 0, $limit ) : $w_obra_loop['obra']->obra_description;
    else
        return false;
}

function the_obra_date() { 
    global $w_obra_loop;
    if ( is_object( $w_obra_loop['obra'] ) && $w_obra_loop['obra']->obra_date )
        echo date( 'd/m/Y', strtotime( $w_obra_loop['obra']->obra_date ) );
    else
        return false;
}

function have_obra_pictures( $per_page = false, $paged = false ) {
    global $w_obra_loop;
    if ( ! $w_obra_loop['obra'] )
        return false;
    if ( is_null( $w_obra_loop['obra_picturies'] ) ) {
        $w_obra_loop['obra_picturies'] = W_Obra_Picture_Model::find_by_obra( $w_obra_loop['obra']->obra_id, array( 'per_page' => $per_page, 'paged' => $paged ) );
    }
    if ( $w_obra_loop['obra_picture_current']  < count( $w_obra_loop['obra_picturies'] ) ) {
        $w_obra_loop['obra_picture_current']++;
        return true;
    }
    $w_obra_loop['obra_picturies'] = null;
    $w_obra_loop['obra_picture'] = null;
    $w_obra_loop['obra_picture_current'] = -1;
    return false;
}

function the_obra_picture() {
    global $w_obra_loop;
    $w_obra_loop['obra_picture'] = $w_obra_loop['obra_picturies'][$w_obra_loop['obra_picture_current']];
}

function get_obra_picture_file() {
    global $w_obra_loop;
    
    $image_src = false;
    if ( isset( $w_obra_loop['obra_picture']->obra_picture_filename ) )
        $image_src = W_OBRA_UPLOAD_URL . $w_obra_loop['obra']->obra_id . '/' . $w_obra_loop['obra_picture']->obra_picture_filename;
    
    return $image_src;
}

function the_obra_picture_file( $size = false, $atts = array() ) {
    $image_src = get_obra_picture_file();
    echo get_obra_render_image( $image_src, $size, $atts );
}

function get_obra_picture_thumb() {
    global $w_obra_loop;
    
    $image_src = false;
    if ( isset( $w_obra_loop['obra_picture']->obra_picture_thumbnail ) )
        $image_src = W_OBRA_UPLOAD_URL . $w_obra_loop['obra']->obra_id . '/' . $w_obra_loop['obra_picture']->obra_picture_thumbnail;
    
    return $image_src;
}

function the_obra_picture_thumb( $size = false, $atts = array() ) {
    $image_src = get_obra_picture_thumb();
    echo get_obra_render_image( $image_src, $size, $atts );
}

function get_obra_cover( $size = 'full' ) {
    global $w_obra_loop;
    $obra = $w_obra_loop['obra'];
    
    $obra_cover = false;
    if ( ! is_null( $obra ) && isset( $obra->obra_id ) && $obra->obra_cover ) {
        $obra_cover = wp_get_attachment_image_src( $obra->obra_cover, $size );
    }

    return $obra_cover ? $obra_cover[0] : false;
}

function the_obra_cover( $size = false, $atts = array() ) {
    $obra_cover = get_obra_cover( $size );
    $image_src = $obra_cover ?: W_OBRA_PLUGIN_URL . 'assets/img/no-image.png';
    echo get_obra_render_image( $image_src, $size, $atts );
}

function get_obra_picture_orientation() {
    global $w_obra_loop;
    return $w_obra_loop['obra_picture']->obra_picture_orientation;
}

function get_obra_picture_uploaded() {
    global $w_obra_loop;
    if ( is_object( $w_obra_loop['obra_picture'] ) && $w_obra_loop['obra_picture']->obra_picture_uploaded )
        return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $w_obra_loop['obra_picture']->obra_picture_uploaded );
    else
        return false;
}

function the_obra_picture_uploaded() { 
    if ( get_obra_picture_uploaded() )
        echo get_obra_picture_uploaded();
}

function the_obra_picture_orientation() {
    echo get_obra_picture_orientation();
}

function the_obra_picture_description( ) {
    global $w_obra_loop;
    echo esc_attr( $w_obra_loop['obra_picture']->obra_picture_description );
}

function get_obra_permalink() {
    global $w_obra_loop, $w_obra_config;
    return home_url( $w_obra_config['rewrite'] . '/' . $w_obra_loop['obra']->obra_id .
            '/' . $w_obra_loop['obra']->obra_slug . '/' );
}

function the_obra_permalink() {
    echo get_obra_permalink();
}

function the_obra_pagination() {
    $big = 999999999;
    
    $posts_per_page = get_option( 'posts_per_page' );
    $max_num_pages = ceil( W_Obra_Model::count_all() / $posts_per_page );

    $paginate = paginate_links( array(
        'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
        'type' => 'array',
        'total' => $max_num_pages,
        'current' => max( 1, get_query_var( 'obra_paged' ) ),
        'prev_text' => __( '&laquo;' ),
        'next_text' => __( '&raquo;' ),
    ) );

    if ( $max_num_pages > 1 ) {
        $current_page = get_query_var( 'obra_paged' ) ? get_query_var( 'obra_paged' ) : 1;

        $html = '<ul class="pagination">';
        foreach ( $paginate as $key => $page ) {
            $class_name = ( $current_page > 1 ? $key : $key + 1 ) == $current_page ? ' class="active"' : '';
            $html .= '<li'.$class_name.'>' . $page . '</li>';
        }
        $html .= '</ul>';
        echo $html;
    }
}

function get_obra_render_image( $image_src, $size = false, $atts = array() ) {
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