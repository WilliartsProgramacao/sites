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

$event = WM_Event_Model::find_by_id( $_REQUEST['eid'] );

// Check parameters
if ( ! is_object( $event ) || ! $event->event_id )
    die( '{"jsonrpc" : "2.0", "error" : {"code": 5, "message": "Parâmetros inválido!"}, "id" : "id"}' );

// Check blog space WPMU
if ( is_multisite() && ! get_site_option( 'upload_space_check_disabled' ) ) {
    $quota = get_space_allowed();
    $used = get_space_used();
    if ( $quota <= $used ) {
        die( '{"jsonrpc" : "2.0", "error" : {"code": 6, "message": "Você usou toda sua cota de armazenamento de '.$quota.' MB."}, "id" : "id"}' );
    }
}

// Event upload dir
$event_upload_dir =  WM_EVENT_UPLOAD_DIR . $event->event_id . '/';

// Check dir
if ( ! is_dir( $event_upload_dir ) )
    mkdir( $event_upload_dir, 0777, true );

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
while ( is_file( $event_upload_dir . $new_filename ) ) {
    $new_filename = $file_info['filename'] . '_' . $idx_filename ++ . '.' . strtolower( $file_info['extension'] );
}

// Check validation
if ( count( $validation ) > 0 ) {
    die( '{"jsonrpc" : "2.0", "error" : {"code": 4, "message": "Falha ao tentar carregar o arquivo!"}, "id" : "id"}' );
}

// Execute picture upload
if ( move_uploaded_file( $file['tmp_name'], $event_upload_dir . $new_filename ) ) {

    // File info
    $file_info = pathinfo( $new_filename );
    $new_filename_thumb = $event_upload_dir . $file_info['filename'] . '_thumb.' . $file_info['extension'];

    // Thumbnail resize
    $picture_thumb_size = new stdClass();
    $picture_thumb_size->width = 350;
    $picture_thumb_size->height = 320;

    // Thumbnail
    $thumbnail = wp_get_image_editor( $event_upload_dir . $new_filename );
    if ( ! is_wp_error( $thumbnail ) ) {
        $thumbnail_size = getimagesize( $event_upload_dir . $new_filename );
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

    $image = wp_get_image_editor( $event_upload_dir . $new_filename );
    if ( ! is_wp_error( $image ) ) {
        $image->resize( $picture_size->width, $picture_size->height, false );
        $image_resized = $image->save( $event_upload_dir . $new_filename );
    }

    $picture_data = array(
        'event_id' => $event->event_id,
        'event_picture_filename' => str_replace( $event_upload_dir, '', $image_resized['file'] ),
        'event_picture_thumbnail' => str_replace( $event_upload_dir, '', $thumbnail_resized['file'] ),
        'event_picture_uploaded' => date_i18n( 'Y-m-d H:i:s' ),
        'event_picture_description' => '',
        'event_picture_orientation' => $thumbnail_orientation
    );

    $result = WM_Event_Picture_Model::insert( $picture_data );

    if ( $result > 0 ) {
        // Return Success JSON-RPC response
        die( '{"jsonrpc" : "2.0", "result" : null, "filename" : "'.$picture_data['event_picture_filename'].'", "thumbnail" : "'.$picture_data['event_picture_thumbnail'].'",  "id" : "id"}' );
    }
}
die( '{"jsonrpc" : "2.0", "error" : {"code": 4, "message": "Falha ao tentar carregar o arquivo!"}, "id" : "id"}' );