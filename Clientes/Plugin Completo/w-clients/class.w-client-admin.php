<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

/*
 * Methods and administrative management feature of this plugin
 * 
 * @author Williarts
 */
class W_Client_Admin {
    
    /*
     * Initialization method
     */
    public static function init() {
        global $_registered_pages, $w_client_config;
        
        /*
         * Add client rewrite rules
         */
        add_rewrite_rule( '^' . $w_client_config[ 'rewrite' ] . '/page/([^/]*)/?', 'index.php?client=true&client_paged=$matches[1]', 'top' );
        add_rewrite_rule( '^' . $w_client_config[ 'rewrite' ] . '/([^/]*)/([^/]*)/?', 'index.php?client_id=$matches[1]', 'top' );
        add_rewrite_rule( '^' . $w_client_config[ 'rewrite' ] . '/?', 'index.php?client=true', 'top' );
        flush_rewrite_rules();
        
        /*
         * Add query_vars
         */
        add_filter( 'query_vars', function ( $query_vars ) {
            $query_vars[] = 'client';
            $query_vars[] = 'client_id';
            $query_vars[] = 'client_paged';
            return $query_vars;
        } );

        /*
         * Include template file
         */
        add_filter( 'template_include', function ( $template ) {
            global $wp_query;
            
            if ( isset( $wp_query->query_vars['client'] ) ) {
                $tpl_file_client = get_stylesheet_directory() . '/archive-client.php';
                if ( is_file( $tpl_file_client ) ) {
                    return $tpl_file_client;
                }
            }
            elseif ( isset( $wp_query->query_vars['client_id'] ) ) {
                $tpl_file_client_single = get_stylesheet_directory() . '/single-client.php';
                if ( is_file( $tpl_file_client_single ) ) {
                    return $tpl_file_client_single;
                }
            }
            return $template;
        }, 1, 1 );
        
        /*
         * Add action menu client page
         */
        add_action( 'admin_menu', function() {
            global $w_client_config;
            $hook = add_menu_page(
                    'Clientes',
                    'Clientes',
                    W_CLIENT_CAPABILITY,
                    $w_client_config[ 'slug_page_list_view' ],
                    array( 'W_Client_Admin', 'client_list_view' ),
                    'dashicons-groups',
                    11
            );
            add_submenu_page(
                    $w_client_config[ 'slug_page_list_view' ],
                    'Todos os Clientes',
                    'Todos os Clientes',
                    W_CLIENT_CAPABILITY,
                    $w_client_config[ 'slug_page_list_view' ],
                    array( 'W_Client_Admin', 'client_list_view' )
            );
            add_submenu_page(
                    $w_client_config[ 'slug_page_list_view' ],
                    'Adicionar Novo',
                    'Adicionar Novo',
                    W_CLIENT_CAPABILITY,
                    $w_client_config[ 'slug_page_form_view' ],
                    array( 'W_Client_Admin', 'client_form_view' )
            );
            add_action( "load-$hook", function() {
                $option = 'per_page';
                $args = array(
                    'label' => 'Cliente(s)',
                    'default' => 20,
                    'option' => 'clients_per_page'
                );
                add_screen_option( $option, $args );
            } );
        } );
        add_filter( 'set-screen-option', function( $status, $option, $value ) {
            if ( 'clients_per_page' == $option )
                return $value;
            return $status;
        }, 10, 3 );
        
        /*
         * Page Hidden Client Upload
         */
        $hookname = get_plugin_page_hookname(
                $w_client_config[ 'slug_page_upload_view' ],
                $w_client_config[ 'slug_page_list_view' ]
        );
        add_action( $hookname, array( 'W_Client_Admin', 'client_upload_view' ) );
        $_registered_pages[$hookname] = true;
        
        /*
         * Add New Client Item in New Content Menu
         */
        add_action( 'wp_before_admin_bar_render', function (){
            global $wp_admin_bar, $w_client_config;
            $wp_admin_bar->add_node( $args = array(
                'parent' => 'new-content',
                'id'     => 'new-client',
                'title'  => 'Cliento',
                'href'   => admin_url( 'admin.php?page=' . $w_client_config['slug_page_form_view'] )
            ) );
        }, 100 );
    }
    
    /*
     * Initialization method used only in wp-admin.
     */
    public static function admin_init() {
        global $w_client_config;

        // Ckeck is set action 
        if ( isset( $_GET['action'] ) ) {
            // Client ID
            $client_id = isset( $_GET['eid'] ) ? $_GET['eid'] : null;
            
            // Client Picture ID
            $client_picture_id = isset( $_GET['epid'] ) ? $_GET['epid'] : null;
            
            // Ckeck action save client
            if ( $_GET['action'] == $w_client_config['slug_action_save'] ) {
                W_Client_Admin::save();
            }
            
            // Ckeck action delete client
            if ( $_GET['action'] == $w_client_config['slug_action_delete'] && ! is_null( $client_id ) ) {
                W_Client_Admin::delete( $client_id );
            }

            // Ckeck action delete picture client
            if ( $_GET['action'] == $w_client_config['slug_action_delete_picture'] && ! is_null( $client_picture_id ) ) {
                W_Client_Admin::delete_picture( $client_picture_id, $client_id );
            }
        } // end check action
    }

    /*
     * Indexed list of clients
     */
    public static function client_list_view() {
        global $w_client_config;
        
        // Include Client List Table Class
        require_once 'views/class.client-list-table.php';
        
        // Status deleted
        $deleted = isset( $_GET['deleted'] ) ? $_GET['deleted'] : '';
        
        // Status message
        $message = isset( $_GET['message'] ) ? W_Client_Admin::message( $_GET['message'], $deleted ) : false;
        
        // Include Client List View
        require_once 'views/client-list.php';
    }
    
    /*
     * Registration Form Client
     */
    public static function client_form_view() {
        global $w_client_config;
        $client_id = isset( $_GET['eid'] ) ? $_GET['eid'] : -1;
                
        // Include Client Picture List Table Class
        require_once 'views/class.client-picture-list-table.php';
        
        // Message ID
        $message_id = isset( $_GET['message'] ) ? $_GET['message'] : false;
        
        // Status message
        $message = W_Client_Admin::message( $message_id );
        
        // Load client
        $client = W_Client_Model::find_by_id( $client_id );
        
        if ( ! $client ) {
            $client = new stdClass();
            $client->client_id = '';
            $client->client_name = '';
            $client->client_description = '';
            $client->client_slug = '';
            $client->client_date = '';
            $client->client_cover = '';
        } else {
            $client->client_date = date( 'd/m/Y', strtotime( $client->client_date ) );
        }

        // Include Client Form View
        require_once 'views/client-form.php';
    }
    
    /*
     * Upload Files Client
     */
    public static function client_upload_view() {
        global $w_client_config;
        $client_id = isset( $_GET['eid'] ) ? $_GET['eid'] : -1;
        
        // Load client
        $client = W_Client_Model::find_by_id( $client_id );
        
        if ( ! $client ) {
            $client = new stdClass();
            $client->client_id = '';
            $client->client_name = '';
            $client->client_description = '';
            $client->client_slug = '';
            $client->client_date = '';
            $client->client_cover = '';
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
        
        // Include Client Upload View
        require_once 'views/client-upload.php';
    }
    
    /*
     * Save client
     */
    public static function save() {
        global $w_client_config;
        
        $client = $_POST;
        
        // Check the user's permissions.
        if ( ! current_user_can( W_CLIENT_CAPABILITY ) ) {
            wp_die( 'Sem permissões suficientes para executar esta ação.' );
        }
        
        // Validade client name field
        if ( trim( $client['client_name'] ) == '' ) {
            return wp_redirect( menu_page_url( $w_client_config['slug_page_form_view'], false ) . '&eid=' . $client['client_id'] . '&message=9' );
        }
        
        // Validade format client date
        if ( empty( trim( $client['client_date'] ) ) || ! preg_match( "~\d{2,2}/\d{2,2}/\d{4,4}~", $client['client_date'] ) ) {
            return wp_redirect( menu_page_url( $w_client_config['slug_page_form_view'], false ) . '&eid=' . $client['client_id'] . '&message=10' );
        }
        
        // Validade client date field
        $client_date_array = explode( '/', $client['client_date'] );
        if ( ! checkdate( $client_date_array[1], $client_date_array[0], $client_date_array[2] ) ) {
            return wp_redirect( menu_page_url( $w_client_config['slug_page_form_view'], false ) . '&eid=' . $client['client_id'] . '&message=10' );
        }
        
        // Format client date
        $client['client_date'] = date( 'Y-m-d', mktime( 0, 0, 0, $client_date_array[1], $client_date_array[0], $client_date_array[2] ) );
        
        // Generate slug client
        $client['client_slug'] = sanitize_title( $client['client_name'] );
        
        if ( empty( $client['client_id'] ) ) { // Insert client
            $affected_records = W_Client_Model::insert( $client );
            $client['client_id'] = W_Client_Model::find_by_last_client_id();
            mkdir( W_CLIENT_UPLOAD_DIR . $client['client_id'] );
            
        } else { // Updade client
            $affected_records = W_Client_Model::update( $client );
            
            // Update Picture Description
            if ( isset( $client['picture_description'] ) && is_array( $client['picture_description'] ) ) {
                foreach ( $client['picture_description'] as $key => $value ) {
                    W_Client_Picture_Model::update( array( 'client_picture_description' => $value ), $key );
                }
            }
        }
        
        if ( $affected_records !== false ) // Redirect on success
            return wp_redirect( menu_page_url( $w_client_config['slug_page_form_view'], false ) . '&eid=' . $client['client_id'] . '&message=1' );
        
        else // Redirect on failure
            return wp_redirect( menu_page_url( $w_client_config['slug_page_form_view'], false ) . '&eid=' . $client['client_id'] . '&message=4' );
    }
    
    /*
     * Delete client and picture
     */
    public static function delete( $eid ) {
        global $w_client_config;
        
        // Check the user's permissions.
        if ( ! current_user_can( W_CLIENT_CAPABILITY ) ) {
            wp_die( 'Sem permissões suficientes para executar esta ação.' );
        }
        
        if ( ! is_array( $eid ) ) { // Single delete client
            
            // Verify that the nonce is valid.
            if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'client_delete_' . $eid ) ) {
                wp_die( 'Requisição inválida.' );
            }

            // Get client pictures
            $pictures = W_Client_Picture_Model::find_by_client( $eid );
            
            // Delete client pictures
            foreach ( $pictures as $picture )
                W_Client_Admin::delete_picture ( $picture->client_picture_id );

            // Delete client dir
            if ( is_dir( W_CLIENT_UPLOAD_DIR . $eid . '/' ) )
                rmdir ( W_CLIENT_UPLOAD_DIR . $eid . '/' );
            
            // Delete record in database
            $affected_records = W_Client_Model::delete( $eid );
            
            if ( $affected_records !== false ) // Redirect on success
                return wp_redirect( menu_page_url( $w_client_config['slug_page_list_view'], false ) . '&message=2' );

            else // Redirect on failure
                return wp_redirect( menu_page_url( $w_client_config['slug_page_list_view'], false ) . '&message=5' );
            
        } else { // Bulk delete client
            
            foreach ( $eid as $client_id ) {
                // Get client pictures
                $pictures = W_Client_Picture_Model::find_by_client( $client_id );

                // Delete client pictures
                foreach ( $pictures as $picture )
                    W_Client_Admin::delete_picture ( $picture->client_picture_id );

                // Delete record in database
                W_Client_Model::delete( $client_id );
            }
            
            // Redirect on success
            return wp_redirect( menu_page_url( $w_client_config['slug_page_list_view'], false ) . '&message=11&deleted=' . count( $eid ) );
        }
        
        // On success bulk delete
        return true;
    }
    
    /*
     * Delete picture from client
     */
    public static function delete_picture( $epid, $eid = null ) {
        global $w_client_config;
        
        // Check the user's permissions.
        if ( ! current_user_can( W_CLIENT_CAPABILITY ) ) {
            wp_die( 'Sem permissões suficientes para executar esta ação.' );
        }
        
        // Verify that the nonce is valid.
        if ( ! is_null( $eid ) ) {
            if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'client_picture_delete_' . $epid ) ) {
                wp_die( 'Requisição inválida.' );
            }
        }
        
        // Get client picture
        $picture = W_Client_Picture_Model::find_by_id( $epid );
        
        // Check client picture
        if ( ! $picture ) {
            
            // Redirect on failure
            if ( ! is_null( $eid ) )
                return wp_redirect( menu_page_url( $w_client_config['slug_page_form_view'], false ) . '&eid=' . $eid . '&message=6' );
            
            // Return on failure
            else
                return false;
        }
        
        // OK, it's safe to delete data now.
        
        $image_file = W_CLIENT_UPLOAD_DIR . $picture->client_id . '/' . $picture->client_picture_filename;
        $thumb_file = W_CLIENT_UPLOAD_DIR . $picture->client_id . '/' . $picture->client_picture_thumbnail;
        
        // Delete files
        @unlink( $image_file );
        @unlink( $thumb_file );
        
        // Check if file exists
        if ( is_file( $image_file ) || is_file( $thumb_file ) ) {
            
            // Redirect on failure
            if ( ! is_null( $eid ) )
                return wp_redirect( menu_page_url( $w_client_config['slug_page_form_view'], false ) . '&eid=' . $eid . '&message=7' );
            
            // Return on failure
            else
                return false;
        }
        
        // Delete record in database
        $affected_records = W_Client_Picture_Model::delete( $epid );
        
        if ( ! $affected_records ) {
            
            // Redirect on failure
            if ( ! is_null( $eid ) )
                return wp_redirect( menu_page_url( $w_client_config['slug_page_form_view'], false ) . '&eid=' . $eid . '&message=8' );
            
            // Return on failure
            else
                return false;
        }

        // Redirect on success
        if ( ! is_null( $eid ) )
            return wp_redirect( menu_page_url( $w_client_config['slug_page_form_view'], false ) . '&eid=' . $eid . '&message=3' );

        // Return on success
        return true;
    }
    
    /*
     * Status messages
     */
    public static function message( $msg_id, $text = '' ) {
        $messages = array(
            array( 'Cliento atualizada.', 'updated' ),
            array( 'Cliento excluída.', 'updated' ),
            array( 'Imagem excluída.', 'updated' ),
            array( 'Falha ao tentar atualizar a cliento.', 'error' ),
            array( 'Falha ao tentar excluir a cliento.', 'error' ),
            array( 'Esta imagem não existe ou pode já ter sido excluída!', 'error' ),
            array( 'Não foi possível excluir os arquivos, se o problema persistir, verifique as permissões!', 'error' ),
            array( 'Não foi possível excluir o registro no banco de dados!', 'error' ),
            array( 'Informe um nome para a cliento.', 'error' ),
            array( 'Informe uma data válida para a cliento.', 'error' ),
            array( '<b>' . $text . '</b> cliento(s) excluída(s)', 'updated' )
        );
        return $msg_id && isset( $messages[$msg_id-1] ) ? $messages[$msg_id-1] : false;
    }
    
}