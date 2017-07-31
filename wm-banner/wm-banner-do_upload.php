<?php
/**
 * Upload Management
 *
 * @package WordPress
 * @subpackage Plugins
 */

/** Load WordPress Bootstrap */
require_once '../../../wp-load.php';

if ( ! current_user_can( WM_BANNER_CAPABILITY ) )
    wp_die( __( 'You do not have permission to upload files.' ) );


// 5 minutes execution time
@set_time_limit( 5 * 60 );

$file = isset( $_FILES['file'] ) ? $_FILES['file'] : null;

if ( is_null( $file ) )
    die( '{"jsonrpc" : "2.0", "error" : {"code": 5, "message": "Parâmetros inválido!"}, "id" : "id"}' );

$validation = array();

$banner = WM_Banner_Model::find_by_id( $_REQUEST['bid'] );

// Check parameters
if ( ! is_object( $banner ) || ! $banner->banner_id )
    die( '{"jsonrpc" : "2.0", "error" : {"code": 5, "message": "Parâmetros inválido!"}, "id" : "id"}' );

// Check blog space WPMU
if ( is_multisite() && ! get_site_option( 'upload_space_check_disabled' ) ) {
    $quota = get_space_allowed();
    $used = get_space_used();
    if ( $quota <= $used ) {
        die( '{"jsonrpc" : "2.0", "error" : {"code": 6, "message": "Você usou toda sua cota de armazenamento de '.$quota.' MB."}, "id" : "id"}' );
    }
}

// Check dir
if ( ! is_dir( WM_BANNER_UPLOAD_DIR ) )
    mkdir( WM_BANNER_UPLOAD_DIR, 0777, true );

// Check filesize
if ( $file['size'] > wp_max_upload_size() )
    $validation['size'] = $file['size'] / 1024;

// Check extension [allowed = gif, jpg, jpeg, png]
$file_info = pathinfo( $file['name'] );
if ( ! in_array( strtolower( $file_info['extension'] ), array( 'gif', 'jpg', 'jpeg', 'png' ) ) )
    $validation['extension'] = strtolower( $file_info['extension'] );

// Check if filename already exists
$idx_filename = 1;
$new_filename = $file['name'];
while ( is_file( WM_BANNER_UPLOAD_DIR . $new_filename ) ) {
    $new_filename = $file_info['filename'] . '_' . $idx_filename ++ . '.' . $file_info['extension'];
}

// Check validation
if ( count( $validation ) > 0 ) {
    die( '{"jsonrpc" : "2.0", "error" : {"code": 4, "message": "Falha ao tentar carregar o arquivo!"}, "id" : "id"}' );
}

// Execute picture upload
if ( move_uploaded_file( $file['tmp_name'], WM_BANNER_UPLOAD_DIR . $new_filename ) ) {

    // File info
    $file_info = pathinfo( $new_filename );
    $new_filename_thumb = WM_BANNER_UPLOAD_DIR . $file_info['filename'] . '_thumb.' . $file_info['extension'];

    // Thumbnail resize
    $picture_thumb_size = new stdClass();
    $picture_thumb_size->width = 120;
    $picture_thumb_size->height = 105;

    // Thumbnail
    $thumbnail = wp_get_image_editor( WM_BANNER_UPLOAD_DIR . $new_filename );
    if ( ! is_wp_error( $thumbnail ) ) {
        $thumbnail_size = getimagesize( WM_BANNER_UPLOAD_DIR . $new_filename );
        $thumbnail_orientation = $thumbnail_size[0] >= $thumbnail_size[1] ? 'horizontal' : 'vertical';
        $thumbnail->resize( $picture_thumb_size->width, $picture_thumb_size->height, true );
        $thumbnail_resized = $thumbnail->save( $new_filename_thumb );
    }

    $image = wp_get_image_editor( WM_BANNER_UPLOAD_DIR . $new_filename );
    if ( ! is_wp_error( $image ) ) {
        $image->resize( $banner->banner_width, $banner->banner_height, false );
        $image_resized = $image->save( WM_BANNER_UPLOAD_DIR . $new_filename );
    }

    $picture_data = array(
        'banner_id' => $banner->banner_id,
        'banner_picture_filename' => str_replace( WM_BANNER_UPLOAD_DIR, '', $image_resized['file'] ),
        'banner_picture_thumbnail' => str_replace( WM_BANNER_UPLOAD_DIR, '', $thumbnail_resized['file'] ),
        'banner_picture_uploaded' => date_i18n( 'Y-m-d H:i:s' ),
        'banner_picture_description' => '',
        'banner_picture_link' => '',
        'banner_picture_target' => '_self'
    );

    $result = WM_Banner_Picture_Model::insert( $picture_data );

    if ( $result > 0 ) {
        // Return Success JSON-RPC response
        die( '{"jsonrpc" : "2.0", "result" : null, "filename" : "'.$picture_data['banner_picture_filename'].'", "thumbnail" : "'.$picture_data['banner_picture_thumbnail'].'",  "id" : "id"}' );
    }
}
die( '{"jsonrpc" : "2.0", "error" : {"code": 4, "message": "Falha ao tentar carregar o arquivo!"}, "id" : "id"}' );