<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

/*
 * Methods and administrative management feature of this plugin
 * 
 * @author Williarts
 */
class W_Obra_Admin {
    
    /*
     * Initialization method
     */
    public static function init() {
        global $_registered_pages, $w_obra_config;
        
        /*
         * Add obra rewrite rules
         */
        add_rewrite_rule( '^' . $w_obra_config[ 'rewrite' ] . '/page/([^/]*)/?', 'index.php?obra=true&obra_paged=$matches[1]', 'top' );
        add_rewrite_rule( '^' . $w_obra_config[ 'rewrite' ] . '/([^/]*)/([^/]*)/?', 'index.php?obra_id=$matches[1]', 'top' );
        add_rewrite_rule( '^' . $w_obra_config[ 'rewrite' ] . '/?', 'index.php?obra=true', 'top' );
        flush_rewrite_rules();
        
        /*
         * Add query_vars
         */
        add_filter( 'query_vars', function ( $query_vars ) {
            $query_vars[] = 'obra';
            $query_vars[] = 'obra_id';
            $query_vars[] = 'obra_paged';
            return $query_vars;
        } );

        /*
         * Include template file
         */
        add_filter( 'template_include', function ( $template ) {
            global $wp_query;
            
            if ( isset( $wp_query->query_vars['obra'] ) ) {
                $tpl_file_obra = get_stylesheet_directory() . '/archive-obra.php';
                if ( is_file( $tpl_file_obra ) ) {
                    return $tpl_file_obra;
                }
            }
            elseif ( isset( $wp_query->query_vars['obra_id'] ) ) {
                $tpl_file_obra_single = get_stylesheet_directory() . '/single-obra.php';
                if ( is_file( $tpl_file_obra_single ) ) {
                    return $tpl_file_obra_single;
                }
            }
            return $template;
        }, 1, 1 );
        
        /*
         * Add action menu obra page
         */
        add_action( 'admin_menu', function() {
            global $w_obra_config;
            $hook = add_menu_page(
                    'Obras',
                    'Obras',
                    W_OBRAS_CAPABILITY,
                    $w_obra_config[ 'slug_page_list_view' ],
                    array( 'W_Obra_Admin', 'obra_list_view' ),
                    'dashicons-groups',
                    11
            );
            add_submenu_page(
                    $w_obra_config[ 'slug_page_list_view' ],
                    'Todos as Obras',
                    'Todos as Obras',
                    W_OBRAS_CAPABILITY,
                    $w_obra_config[ 'slug_page_list_view' ],
                    array( 'W_Obra_Admin', 'obra_list_view' )
            );
            add_submenu_page(
                    $w_obra_config[ 'slug_page_list_view' ],
                    'Adicionar Nova',
                    'Adicionar Nova',
                    W_OBRAS_CAPABILITY,
                    $w_obra_config[ 'slug_page_form_view' ],
                    array( 'W_Obra_Admin', 'obra_form_view' )
            );
            add_action( "load-$hook", function() {
                $option = 'per_page';
                $args = array(
                    'label' => 'Obra(s)',
                    'default' => 20,
                    'option' => 'obras_per_page'
                );
                add_screen_option( $option, $args );
            } );
        } );
        add_filter( 'set-screen-option', function( $status, $option, $value ) {
            if ( 'obras_per_page' == $option )
                return $value;
            return $status;
        }, 10, 3 );
        
        /*
         * Page Hidden Obra Upload
         */
        $hookname = get_plugin_page_hookname(
                $w_obra_config[ 'slug_page_upload_view' ],
                $w_obra_config[ 'slug_page_list_view' ]
        );
        add_action( $hookname, array( 'W_Obra_Admin', 'obra_upload_view' ) );
        $_registered_pages[$hookname] = true;
        
        /*
         * Add New Obra Item in New Content Menu
         */
        add_action( 'wp_before_admin_bar_render', function (){
            global $wp_admin_bar, $w_obra_config;
            $wp_admin_bar->add_node( $args = array(
                'parent' => 'new-content',
                'id'     => 'new-obra',
                'title'  => 'Obra',
                'href'   => admin_url( 'admin.php?page=' . $w_obra_config['slug_page_form_view'] )
            ) );
        }, 100 );
    }
    
    /*
     * Initialization method used only in wp-admin.
     */
    public static function admin_init() {
        global $w_obra_config;

        // Ckeck is set action 
        if ( isset( $_GET['action'] ) ) {
            // Obra ID
            $obra_id = isset( $_GET['eid'] ) ? $_GET['eid'] : null;
            
            // Obra Picture ID
            $obra_picture_id = isset( $_GET['epid'] ) ? $_GET['epid'] : null;
            
            // Ckeck action save obra
            if ( $_GET['action'] == $w_obra_config['slug_action_save'] ) {
                W_Obra_Admin::save();
            }
            
            // Ckeck action delete obra
            if ( $_GET['action'] == $w_obra_config['slug_action_delete'] && ! is_null( $obra_id ) ) {
                W_Obra_Admin::delete( $obra_id );
            }

            // Ckeck action delete picture obra
            if ( $_GET['action'] == $w_obra_config['slug_action_delete_picture'] && ! is_null( $obra_picture_id ) ) {
                W_Obra_Admin::delete_picture( $obra_picture_id, $obra_id );
            }
        } // end check action
    }

    /*
     * Indexed list of obras
     */
    public static function obra_list_view() {
        global $w_obra_config;
        
        // Include Obra List Table Class
        require_once 'views/class.obra-list-table.php';
        
        // Status deleted
        $deleted = isset( $_GET['deleted'] ) ? $_GET['deleted'] : '';
        
        // Status message
        $message = isset( $_GET['message'] ) ? W_Obra_Admin::message( $_GET['message'], $deleted ) : false;
        
        // Include Obra List View
        require_once 'views/obra-list.php';
    }
    
    /*
     * Registration Form Obra
     */
    public static function obra_form_view() {
        global $w_obra_config;
        $obra_id = isset( $_GET['eid'] ) ? $_GET['eid'] : -1;
                
        // Include Obra Picture List Table Class
        require_once 'views/class.obra-picture-list-table.php';
        
        // Message ID
        $message_id = isset( $_GET['message'] ) ? $_GET['message'] : false;
        
        // Status message
        $message = W_Obra_Admin::message( $message_id );
        
        // Load obra
        $obra = W_Obra_Model::find_by_id( $obra_id );
        
        if ( ! $obra ) {
            $obra = new stdClass();
            $obra->obra_id = '';
            $obra->obra_name = '';
            $obra->obra_description = '';
            $obra->obra_slug = '';
            $obra->obra_date = '';
            $obra->obra_cover = '';
        } else {
            $obra->obra_date = date( 'd/m/Y', strtotime( $obra->obra_date ) );
        }

        // Include Obra Form View
        require_once 'views/obra-form.php';
    }
    
    /*
     * Upload Files Obra
     */
    public static function obra_upload_view() {
        global $w_obra_config;
        $obra_id = isset( $_GET['eid'] ) ? $_GET['eid'] : -1;
        
        // Load obra
        $obra = W_Obra_Model::find_by_id( $obra_id );
        
        if ( ! $obra ) {
            $obra = new stdClass();
            $obra->obra_id = '';
            $obra->obra_name = '';
            $obra->obra_description = '';
            $obra->obra_slug = '';
            $obra->obra_date = '';
            $obra->obra_cover = '';
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
        
        // Include Obra Upload View
        require_once 'views/obra-upload.php';
    }
    
    /*
     * Save obra
     */
    public static function save() {
        global $w_obra_config;
        
        $obra = $_POST;
        
        // Check the user's permissions.
        if ( ! current_user_can( W_OBRAS_CAPABILITY ) ) {
            wp_die( 'Sem permissões suficientes para executar esta ação.' );
        }
        
        // Validade obra name field
        if ( trim( $obra['obra_name'] ) == '' ) {
            return wp_redirect( menu_page_url( $w_obra_config['slug_page_form_view'], false ) . '&eid=' . $obra['obra_id'] . '&message=9' );
        }
        
        // Validade format obra date
        if ( empty( trim( $obra['obra_date'] ) ) || ! preg_match( "~\d{2,2}/\d{2,2}/\d{4,4}~", $obra['obra_date'] ) ) {
            return wp_redirect( menu_page_url( $w_obra_config['slug_page_form_view'], false ) . '&eid=' . $obra['obra_id'] . '&message=10' );
        }
        
        // Validade obra date field
        $obra_date_array = explode( '/', $obra['obra_date'] );
        if ( ! checkdate( $obra_date_array[1], $obra_date_array[0], $obra_date_array[2] ) ) {
            return wp_redirect( menu_page_url( $w_obra_config['slug_page_form_view'], false ) . '&eid=' . $obra['obra_id'] . '&message=10' );
        }
        
        // Format obra date
        $obra['obra_date'] = date( 'Y-m-d', mktime( 0, 0, 0, $obra_date_array[1], $obra_date_array[0], $obra_date_array[2] ) );
        
        // Generate slug obra
        $obra['obra_slug'] = sanitize_title( $obra['obra_name'] );
        
        if ( empty( $obra['obra_id'] ) ) { // Insert obra
            $affected_records = W_Obra_Model::insert( $obra );
            $obra['obra_id'] = W_Obra_Model::find_by_last_obra_id();
            mkdir( W_OBRAS_UPLOAD_DIR . $obra['obra_id'] );
            
        } else { // Updade obra
            $affected_records = W_Obra_Model::update( $obra );
            
            // Update Picture Description
            if ( isset( $obra['picture_description'] ) && is_array( $obra['picture_description'] ) ) {
                foreach ( $obra['picture_description'] as $key => $value ) {
                    W_Obra_Picture_Model::update( array( 'obra_picture_description' => $value ), $key );
                }
            }
        }
        
        if ( $affected_records !== false ) // Redirect on success
            return wp_redirect( menu_page_url( $w_obra_config['slug_page_form_view'], false ) . '&eid=' . $obra['obra_id'] . '&message=1' );
        
        else // Redirect on failure
            return wp_redirect( menu_page_url( $w_obra_config['slug_page_form_view'], false ) . '&eid=' . $obra['obra_id'] . '&message=4' );
    }
    
    /*
     * Delete obra and picture
     */
    public static function delete( $eid ) {
        global $w_obra_config;
        
        // Check the user's permissions.
        if ( ! current_user_can( W_OBRAS_CAPABILITY ) ) {
            wp_die( 'Sem permissões suficientes para executar esta ação.' );
        }
        
        if ( ! is_array( $eid ) ) { // Single delete obra
            
            // Verify that the nonce is valid.
            if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'obra_delete_' . $eid ) ) {
                wp_die( 'Requisição inválida.' );
            }

            // Get obra pictures
            $pictures = W_Obra_Picture_Model::find_by_obra( $eid );
            
            // Delete obra pictures
            foreach ( $pictures as $picture )
                W_Obra_Admin::delete_picture ( $picture->obra_picture_id );

            // Delete obra dir
            if ( is_dir( W_OBRAS_UPLOAD_DIR . $eid . '/' ) )
                rmdir ( W_OBRAS_UPLOAD_DIR . $eid . '/' );
            
            // Delete record in database
            $affected_records = W_Obra_Model::delete( $eid );
            
            if ( $affected_records !== false ) // Redirect on success
                return wp_redirect( menu_page_url( $w_obra_config['slug_page_list_view'], false ) . '&message=2' );

            else // Redirect on failure
                return wp_redirect( menu_page_url( $w_obra_config['slug_page_list_view'], false ) . '&message=5' );
            
        } else { // Bulk delete obra
            
            foreach ( $eid as $obra_id ) {
                // Get obra pictures
                $pictures = W_Obra_Picture_Model::find_by_obra( $obra_id );

                // Delete obra pictures
                foreach ( $pictures as $picture )
                    W_Obra_Admin::delete_picture ( $picture->obra_picture_id );

                // Delete record in database
                W_Obra_Model::delete( $obra_id );
            }
            
            // Redirect on success
            return wp_redirect( menu_page_url( $w_obra_config['slug_page_list_view'], false ) . '&message=11&deleted=' . count( $eid ) );
        }
        
        // On success bulk delete
        return true;
    }
    
    /*
     * Delete picture from obra
     */
    public static function delete_picture( $epid, $eid = null ) {
        global $w_obra_config;
        
        // Check the user's permissions.
        if ( ! current_user_can( W_OBRAS_CAPABILITY ) ) {
            wp_die( 'Sem permissões suficientes para executar esta ação.' );
        }
        
        // Verify that the nonce is valid.
        if ( ! is_null( $eid ) ) {
            if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'obra_picture_delete_' . $epid ) ) {
                wp_die( 'Requisição inválida.' );
            }
        }
        
        // Get obra picture
        $picture = W_Obra_Picture_Model::find_by_id( $epid );
        
        // Check obra picture
        if ( ! $picture ) {
            
            // Redirect on failure
            if ( ! is_null( $eid ) )
                return wp_redirect( menu_page_url( $w_obra_config['slug_page_form_view'], false ) . '&eid=' . $eid . '&message=6' );
            
            // Return on failure
            else
                return false;
        }
        
        // OK, it's safe to delete data now.
        
        $image_file = W_OBRAS_UPLOAD_DIR . $picture->obra_id . '/' . $picture->obra_picture_filename;
        $thumb_file = W_OBRAS_UPLOAD_DIR . $picture->obra_id . '/' . $picture->obra_picture_thumbnail;
        
        // Delete files
        @unlink( $image_file );
        @unlink( $thumb_file );
        
        // Check if file exists
        if ( is_file( $image_file ) || is_file( $thumb_file ) ) {
            
            // Redirect on failure
            if ( ! is_null( $eid ) )
                return wp_redirect( menu_page_url( $w_obra_config['slug_page_form_view'], false ) . '&eid=' . $eid . '&message=7' );
            
            // Return on failure
            else
                return false;
        }
        
        // Delete record in database
        $affected_records = W_Obra_Picture_Model::delete( $epid );
        
        if ( ! $affected_records ) {
            
            // Redirect on failure
            if ( ! is_null( $eid ) )
                return wp_redirect( menu_page_url( $w_obra_config['slug_page_form_view'], false ) . '&eid=' . $eid . '&message=8' );
            
            // Return on failure
            else
                return false;
        }

        // Redirect on success
        if ( ! is_null( $eid ) )
            return wp_redirect( menu_page_url( $w_obra_config['slug_page_form_view'], false ) . '&eid=' . $eid . '&message=3' );

        // Return on success
        return true;
    }
    
    /*
     * Status messages
     */
    public static function message( $msg_id, $text = '' ) {
        $messages = array(
            array( 'Obrao atualizada.', 'updated' ),
            array( 'Obrao excluída.', 'updated' ),
            array( 'Imagem excluída.', 'updated' ),
            array( 'Falha ao tentar atualizar a obrao.', 'error' ),
            array( 'Falha ao tentar excluir a obrao.', 'error' ),
            array( 'Esta imagem não existe ou pode já ter sido excluída!', 'error' ),
            array( 'Não foi possível excluir os arquivos, se o problema persistir, verifique as permissões!', 'error' ),
            array( 'Não foi possível excluir o registro no banco de dados!', 'error' ),
            array( 'Informe um nome para a obrao.', 'error' ),
            array( 'Informe uma data válida para a obrao.', 'error' ),
            array( '<b>' . $text . '</b> obrao(s) excluída(s)', 'updated' )
        );
        return $msg_id && isset( $messages[$msg_id-1] ) ? $messages[$msg_id-1] : false;
    }
    
}