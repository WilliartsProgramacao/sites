<?php
/**
 * Upload Management
 *
 * @package WordPress
 * @subpackage Plugins
 */

/** Load WordPress Bootstrap */
require_once '../../../wp-load.php';

if ( ! current_user_can( 'edit_posts' ) )
    wp_die( __( 'You do not have permission to upload files.' ) );

// 5 minutes execution time
@set_time_limit( 5 * 60 );

$file = isset( $_FILES['file'] ) ? $_FILES['file'] : null;

if ( is_null( $file ) )
    die( '{"jsonrpc" : "2.0", "error" : {"code": 5, "message": "Parâmetros inválido!"}, "id" : "id"}' );

$validation = array();

$obra = W_Obra_Model::find_by_id( $_REQUEST['eid'] );

// Check parameters
if ( ! is_object( $obra ) || ! $obra->obra_id )
    die( '{"jsonrpc" : "2.0", "error" : {"code": 5, "message": "Parâmetros inválido!"}, "id" : "id"}' );

// Check blog space WPMU
if ( is_multisite() && ! get_site_option( 'upload_space_check_disabled' ) ) {
    $quota = get_space_allowed();
    $used = get_space_used();
    if ( $quota <= $used ) {
        die( '{"jsonrpc" : "2.0", "error" : {"code": 6, "message": "Você usou toda sua cota de armazenamento de '.$quota.' MB."}, "id" : "id"}' );
    }
}

// Obra upload dir
$obra_upload_dir =  W_OBRA_UPLOAD_DIR . $obra->obra_id . '/';

// Check dir
if ( ! is_dir( $obra_upload_dir ) )
    mkdir( $obra_upload_dir, 0777, true );

// Check filesize
if ( $file['size'] > wp_max_upload_size() )
    $validation['size'] = $file['size'] / 1024;

// Check extension [allowed = gif, jpg, jpeg, png]
$file_info = pathinfo( $file['name'] );
if ( ! in_array( strtolower( $file_info['extension'] ), array( 'gif', 'jpg', 'jpeg', 'png' ) ) )
    $validation['extension'] = strtolower( $file_info['extension'] );

// Check if filename already exists
$idx_filename = 1;
$new_filename = $file_info['filename'] . '.' . strtolower( $file_info['extension'] );
while ( is_file( $obra_upload_dir . $new_filename ) ) {
    $new_filename = $file_info['filename'] . '_' . $idx_filename ++ . '.' . strtolower( $file_info['extension'] );
}

// Check validation
if ( count( $validation ) > 0 ) {
    die( '{"jsonrpc" : "2.0", "error" : {"code": 4, "message": "Falha ao tentar carregar o arquivo!"}, "id" : "id"}' );
}

// Execute picture upload
if ( move_uploaded_file( $file['tmp_name'], $obra_upload_dir . $new_filename ) ) {

    // File info
    $file_info = pathinfo( $new_filename );
    $new_filename_thumb = $obra_upload_dir . $file_info['filename'] . '_thumb.' . $file_info['extension'];

    // Thumbnail resize
    $picture_thumb_size = new stdClass();
    $picture_thumb_size->width = 350;
    $picture_thumb_size->height = 320;

    // Thumbnail
    $thumbnail = wp_get_image_editor( $obra_upload_dir . $new_filename );
    if ( ! is_wp_error( $thumbnail ) ) {
        $thumbnail_size = getimagesize( $obra_upload_dir . $new_filename );
        $thumbnail_orientation = $thumbnail_size[0] >= $thumbnail_size[1] ? 'horizontal' : 'vertical';
        if ( $thumbnail_orientation == 'horizontal' )
            $thumbnail->resize( $picture_thumb_size->width, $picture_thumb_size->height, true );
        else
            $thumbnail->resize( $picture_thumb_size->height, $picture_thumb_size->width, true );
        $thumbnail_resized = $thumbnail->save( $new_filename_thumb );
    }

    // Picture resize
    $picture_size = new stdClass();
    $picture_size->width = 800;
    $picture_size->height = 600;

    $image = wp_get_image_editor( $obra_upload_dir . $new_filename );
    if ( ! is_wp_error( $image ) ) {
        $image->resize( $picture_size->width, $picture_size->height, false );
        $image_resized = $image->save( $obra_upload_dir . $new_filename );
    }

    $picture_data = array(
        'obra_id' => $obra->obra_id,
        'obra_picture_filename' => str_replace( $obra_upload_dir, '', $image_resized['file'] ),
        'obra_picture_thumbnail' => str_replace( $obra_upload_dir, '', $thumbnail_resized['file'] ),
        'obra_picture_uploaded' => date_i18n( 'Y-m-d H:i:s' ),
        'obra_picture_description' => '',
        'obra_picture_orientation' => $thumbnail_orientation
    );

    $result = W_Obra_Picture_Model::insert( $picture_data );

    if ( $result > 0 ) {
        // Return Success JSON-RPC response
        die( '{"jsonrpc" : "2.0", "result" : null, "filename" : "'.$picture_data['obra_picture_filename'].'", "thumbnail" : "'.$picture_data['obra_picture_thumbnail'].'",  "id" : "id"}' );
    }
}
die( '{"jsonrpc" : "2.0", "error" : {"code": 4, "message": "Falha ao tentar carregar o arquivo!"}, "id" : "id"}' );