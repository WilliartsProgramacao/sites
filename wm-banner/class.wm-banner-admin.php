<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

/*
 * Methods and administrative management feature of this plugin
 * 
 * @author Wime
 */
class WM_Banner_Admin {
    
    /*
     * Initialization method
     */
    public static function init() {
        global $_registered_pages, $wm_banner_config;
        
        /*
         * Add action menu banner page
         */
        add_action( 'admin_menu', function() {
            global $wm_banner_config;
            $hook = add_menu_page(
                    'Banners',
                    'Banners',
                    WM_BANNER_CAPABILITY,
                    $wm_banner_config[ 'slug_page_list_view' ],
                    array( 'WM_Banner_Admin', 'banner_list_view' ),
                    'dashicons-images-alt2',
                    9
            );
            add_submenu_page(
                    $wm_banner_config[ 'slug_page_list_view' ],
                    'Todas os Banners',
                    'Todas os Banners',
                    WM_BANNER_CAPABILITY,
                    $wm_banner_config[ 'slug_page_list_view' ],
                    array( 'WM_Banner_Admin', 'banner_list_view' )
            );
            add_submenu_page(
                    $wm_banner_config[ 'slug_page_list_view' ],
                    'Adicionar Novo',
                    'Adicionar Novo',
                    WM_BANNER_CAPABILITY,
                    $wm_banner_config[ 'slug_page_form_view' ],
                    array( 'WM_Banner_Admin', 'banner_form_view' )
            );
            add_action( "load-$hook", function() {
                $option = 'per_page';
                $args = array(
                    'label' => 'Banner(s)',
                    'default' => 20,
                    'option' => 'banners_per_page'
                );
                add_screen_option( $option, $args );
            } );
        } );
        add_filter( 'set-screen-option', function( $status, $option, $value ) {
            if ( 'banners_per_page' == $option )
                return $value;
            return $status;
        }, 10, 3 );
        
        /*
         * Page Hidden Banner Upload
         */
        $hookname = get_plugin_page_hookname(
                $wm_banner_config[ 'slug_page_upload_view' ],
                $wm_banner_config[ 'slug_page_list_view' ]
        );
        add_action( $hookname, array( 'WM_Banner_Admin', 'banner_upload_view' ) );
        $_registered_pages[$hookname] = true;
        
        /*
         * Add New Banner Item in New Content Menu
         */
        add_action( 'wp_before_admin_bar_render', function (){
            global $wp_admin_bar, $wm_banner_config;
            $wp_admin_bar->add_node( $args = array(
                'parent' => 'new-content',
                'id'     => 'new-banner',
                'title'  => 'Banner',
                'href'   => admin_url( 'admin.php?page=' . $wm_banner_config['slug_page_form_view'] )
            ) );
        }, 100 );
    }
    
    /*
     * Initialization method used only in wp-admin.
     */
    public static function admin_init() {
        global $wm_banner_config;

        // Ckeck is set action 
        if ( isset( $_GET['action'] ) ) {
            // Banner ID
            $banner_id = isset( $_GET['bid'] ) ? $_GET['bid'] : null;
            
            // Banner Picture ID
            $banner_picture_id = isset( $_GET['gpid'] ) ? $_GET['gpid'] : null;
            
            // Ckeck action save banner
            if ( $_GET['action'] == $wm_banner_config['slug_action_save'] ) {
                WM_Banner_Admin::save();
            }
            
            // Ckeck action delete banner
            if ( $_GET['action'] == $wm_banner_config['slug_action_delete'] && ! is_null( $banner_id ) ) {
                WM_Banner_Admin::delete( $banner_id );
            }

            // Ckeck action delete picture banner
            if ( $_GET['action'] == $wm_banner_config['slug_action_delete_picture'] && ! is_null( $banner_picture_id ) ) {
                WM_Banner_Admin::delete_picture( $banner_picture_id, $banner_id );
            }
        } // end check action
    }

    /*
     * Indexed list of banners
     */
    public static function banner_list_view() {
        global $wm_banner_config;
        
        // Include Banner List Table Class
        require_once 'views/class.banner-list-table.php';
        
        // Status deleted
        $deleted = isset( $_GET['deleted'] ) ? $_GET['deleted'] : '';
        
        // Status message
        $message = isset( $_GET['message'] ) ? WM_Banner_Admin::message( $_GET['message'], $deleted ) : false;
        
        // Include Banner List View
        require_once 'views/banner-list.php';
    }
    
    /*
     * Registration Form Banner
     */
    public static function banner_form_view() {
        global $wm_banner_config;
        $banner_id = isset( $_GET['bid'] ) ? $_GET['bid'] : -1;
                
        // Include Banner Picture List Table Class
        require_once 'views/class.banner-picture-list-table.php';
        
        // Message ID
        $message_id = isset( $_GET['message'] ) ? $_GET['message'] : false;
        
        // Status message
        $message = WM_Banner_Admin::message( $message_id );
        
        // Load banner
        $banner = WM_Banner_Model::find_by_id( $banner_id );
        
        if ( ! $banner ) {
            $banner = new stdClass();
            $banner->banner_id = '';
            $banner->banner_name = '';
            $banner->banner_width = '';
            $banner->banner_height = '';
        }

        // Include Banner Form View
        require_once 'views/banner-form.php';
    }
    
    /*
     * Upload Files Banner
     */
    public static function banner_upload_view() {
        global $wm_banner_config;
        $banner_id = isset( $_GET['bid'] ) ? $_GET['bid'] : -1;
        
        // Load banner
        $banner = WM_Banner_Model::find_by_id( $banner_id );
        
        if ( ! $banner ) {
            $banner = new stdClass();
            $banner->banner_id = '';
            $banner->banner_name = '';
            $banner->banner_width = '';
            $banner->banner_height = '';
        }
        
        // Max Upload Size per File
        $upload_size_unit = $max_upload_size = wp_max_upload_size();
        $sizes = array( 'KB', 'MB', 'GB' );
        for ( $u = -1; $upload_size_unit > 1024 && $u < count( $sizes ) - 1; $u ++ ) {
            $upload_size_unit /= 1024;
        }
        if ( $u < 0 ) {
            $upload_size_unit = 0;
            $u = 0;
        } else {
            $upload_size_unit = ((int) $upload_size_unit) . $sizes[$u];
        }
        
        // Include Banner Upload View
        require_once 'views/banner-upload.php';
    }
    
    /*
     * Save banner
     */
    public static function save() {
        global $wm_banner_config;
        
        $banner = $_POST;
        
        // Check the user's permissions.
        if ( ! current_user_can( WM_BANNER_CAPABILITY ) ) {
            wp_die( 'Sem permissões suficientes para executar esta ação.' );
        }
        
        // Validade banner name
        if ( trim( $banner['banner_name'] ) == '' ) {
            return wp_redirect( menu_page_url( $wm_banner_config['slug_page_form_view'], false ) . '&bid=' . $banner['banner_id'] . '&message=9' );
        }
        
        // Validade banner size
        if ( ( ! is_numeric( $banner['banner_width'] ) || $banner['banner_width'] < 1 ) ||
                ( ! is_numeric( $banner['banner_height'] ) || $banner['banner_height'] < 1 ) ) {
            return wp_redirect( menu_page_url( $wm_banner_config['slug_page_form_view'], false ) . '&bid=' . $banner['banner_id'] . '&message=10' );
        }
        
        if ( empty( $banner['banner_id'] ) ) { // Insert banner
            $affected_records = WM_Banner_Model::insert( $banner );
            $banner['banner_id'] = WM_Banner_Model::find_by_last_banner_id();
        
        } else { // Updade banner
            $affected_records = WM_Banner_Model::update( $banner );
            
            // Update Picture Description
            if ( isset( $banner['picture_description'] ) && is_array( $banner['picture_description'] ) ) {
                foreach ( $banner['picture_description'] as $key => $value ) {
                    WM_Banner_Picture_Model::update(
                            array(
                                'banner_picture_description' => $value,
                                'banner_picture_link' => $banner['picture_link'][$key],
                                'banner_picture_target' => $banner['picture_target'][$key]
                            ),
                            $key
                    );
                }
            }
        }
        
        if ( $affected_records !== false ) // Redirect on success
            return wp_redirect( menu_page_url( $wm_banner_config['slug_page_form_view'], false ) . '&bid=' . $banner['banner_id'] . '&message=1' );
        
        else // Redirect on failure
            return wp_redirect( menu_page_url( $wm_banner_config['slug_page_form_view'], false ) . '&bid=' . $banner['banner_id'] . '&message=4' );
    }
    
    /*
     * Delete banner and picture
     */
    public static function delete( $bid ) {
        global $wm_banner_config;
        
        // Check the user's permissions.
        if ( ! current_user_can( WM_BANNER_CAPABILITY ) ) {
            wp_die( 'Sem permissões suficientes para executar esta ação.' );
        }
        
        if ( ! is_array( $bid ) ) { // Single delete banner
            
            // Verify that the nonce is valid.
            if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'banner_delete_' . $bid ) ) {
                wp_die( 'Requisição inválida.' );
            }

            // Get banner pictures
            $pictures = WM_Banner_Picture_Model::find_by_banner( $bid );
            
            // Delete banner pictures
            foreach ( $pictures as $picture )
                WM_Banner_Admin::delete_picture ( $picture->banner_picture_id );

            // Delete record in database
            $affected_records = WM_Banner_Model::delete( $bid );
            
            if ( $affected_records !== false ) // Redirect on success
                return wp_redirect( menu_page_url( $wm_banner_config['slug_page_list_view'], false ) . '&message=2' );

            else // Redirect on failure
                return wp_redirect( menu_page_url( $wm_banner_config['slug_page_list_view'], false ) . '&message=5' );
            
        } else { // Bulk delete banner
            
            foreach ( $bid as $banner_id ) {
                // Get banner pictures
                $pictures = WM_Banner_Picture_Model::find_by_banner( $banner_id );

                // Delete banner pictures
                foreach ( $pictures as $picture )
                    WM_Banner_Admin::delete_picture ( $picture->banner_picture_id );

                // Delete record in database
                WM_Banner_Model::delete( $banner_id );
            }
            
            // Redirect on success
            return wp_redirect( menu_page_url( $wm_banner_config['slug_page_list_view'], false ) . '&message=11&deleted=' . count( $bid ) );
        }
        
        // On success bulk delete
        return true;
    }
    
    /*
     * Delete picture from banner
     */
    public static function delete_picture( $gpid, $bid = null ) {
        global $wm_banner_config;
        
        // Check the user's permissions.
        if ( ! current_user_can( WM_BANNER_CAPABILITY ) ) {
            wp_die( 'Sem permissões suficientes para executar esta ação.' );
        }
        
        // Verify that the nonce is valid.
        if ( ! is_null( $bid ) ) {
            if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'banner_picture_delete_' . $gpid ) ) {
                wp_die( 'Requisição inválida.' );
            }
        }
        
        // Get banner picture
        $picture = WM_Banner_Picture_Model::find_by_id( $gpid );
        
        // Check banner picture
        if ( ! $picture ) {
            
            // Redirect on failure
            if ( ! is_null( $bid ) )
                return wp_redirect( menu_page_url( $wm_banner_config['slug_page_form_view'], false ) . '&bid=' . $bid . '&message=6' );
            
            // Return on failure
            else
                return false;
        }
        
        // OK, it's safe to delete data now.
        
        $image_file = WM_BANNER_UPLOAD_DIR . $picture->banner_picture_filename;
        $thumb_file = WM_BANNER_UPLOAD_DIR . $picture->banner_picture_thumbnail;
        
        // Delete files
        @unlink( $image_file );
        @unlink( $thumb_file );
        
        // Check if file exists
        if ( is_file( $image_file ) || is_file( $thumb_file ) ) {
            
            // Redirect on failure
            if ( ! is_null( $bid ) )
                return wp_redirect( menu_page_url( $wm_banner_config['slug_page_form_view'], false ) . '&bid=' . $bid . '&message=7' );
            
            // Return on failure
            else
                return false;
        }
        
        // Delete record in database
        $affected_records = WM_Banner_Picture_Model::delete( $gpid );
        
        if ( ! $affected_records ) {
            
            // Redirect on failure
            if ( ! is_null( $bid ) )
                return wp_redirect( menu_page_url( $wm_banner_config['slug_page_form_view'], false ) . '&bid=' . $bid . '&message=8' );
            
            // Return on failure
            else
                return false;
        }

        // Redirect on success
        if ( ! is_null( $bid ) )
            return wp_redirect( menu_page_url( $wm_banner_config['slug_page_form_view'], false ) . '&bid=' . $bid . '&message=3' );

        // Return on success
        return true;
    }
    
    /*
     * Status messages
     */
    public static function message( $msg_id, $text = '' ) {
        $messages = array(
            array( 'Banner atualizado.', 'updated' ),
            array( 'Banner excluído.', 'updated' ),
            array( 'Imagem excluída.', 'updated' ),
            array( 'Falha ao tentar atualizar o banner.', 'error' ),
            array( 'Falha ao tentar excluir o banner.', 'error' ),
            array( 'Esta imagem não existe ou pode já ter sido excluída!', 'error' ),
            array( 'Não foi possível excluir os arquivos, se o problema persistir, verifique as permissões!', 'error' ),
            array( 'Não foi possível excluir o registro no banco de dados!', 'error' ),
            array( 'Informe um nome para o banner.', 'error' ),
            array( 'Informe o tamanho do banner.', 'error' ),
            array( '<b>' . $text . '</b> banner(s) excluído(s)', 'updated' )
        );
        return $msg_id && isset( $messages[$msg_id-1] ) ? $messages[$msg_id-1] : false;
    }
    
}