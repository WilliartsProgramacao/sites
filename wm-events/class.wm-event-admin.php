<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

/*
 * Methods and administrative management feature of this plugin
 * 
 * @author Wime
 */
class WM_Event_Admin {
    
    /*
     * Initialization method
     */
    public static function init() {
        global $_registered_pages, $wm_event_config;
        
        /*
         * Add event rewrite rules
         */
        add_rewrite_rule( '^' . $wm_event_config[ 'rewrite' ] . '/page/([^/]*)/?', 'index.php?event=true&event_paged=$matches[1]', 'top' );
        add_rewrite_rule( '^' . $wm_event_config[ 'rewrite' ] . '/([^/]*)/([^/]*)/?', 'index.php?event_id=$matches[1]', 'top' );
        add_rewrite_rule( '^' . $wm_event_config[ 'rewrite' ] . '/?', 'index.php?event=true', 'top' );
        flush_rewrite_rules();
        
        /*
         * Add query_vars
         */
        add_filter( 'query_vars', function ( $query_vars ) {
            $query_vars[] = 'event';
            $query_vars[] = 'event_id';
            $query_vars[] = 'event_paged';
            return $query_vars;
        } );

        /*
         * Include template file
         */
        add_filter( 'template_include', function ( $template ) {
            global $wp_query;
            
            if ( isset( $wp_query->query_vars['event'] ) ) {
                $tpl_file_event = get_stylesheet_directory() . '/archive-event.php';
                if ( is_file( $tpl_file_event ) ) {
                    return $tpl_file_event;
                }
            }
            elseif ( isset( $wp_query->query_vars['event_id'] ) ) {
                $tpl_file_event_single = get_stylesheet_directory() . '/single-event.php';
                if ( is_file( $tpl_file_event_single ) ) {
                    return $tpl_file_event_single;
                }
            }
            return $template;
        }, 1, 1 );
        
        /*
         * Add action menu event page
         */
        add_action( 'admin_menu', function() {
            global $wm_event_config;
            $hook = add_menu_page(
                    'Eventos',
                    'Eventos',
                    WM_EVENT_CAPABILITY,
                    $wm_event_config[ 'slug_page_list_view' ],
                    array( 'WM_Event_Admin', 'event_list_view' ),
                    'dashicons-groups',
                    11
            );
            add_submenu_page(
                    $wm_event_config[ 'slug_page_list_view' ],
                    'Todas as Eventos',
                    'Todas as Eventos',
                    WM_EVENT_CAPABILITY,
                    $wm_event_config[ 'slug_page_list_view' ],
                    array( 'WM_Event_Admin', 'event_list_view' )
            );
            add_submenu_page(
                    $wm_event_config[ 'slug_page_list_view' ],
                    'Adicionar Novo',
                    'Adicionar Novo',
                    WM_EVENT_CAPABILITY,
                    $wm_event_config[ 'slug_page_form_view' ],
                    array( 'WM_Event_Admin', 'event_form_view' )
            );
            add_action( "load-$hook", function() {
                $option = 'per_page';
                $args = array(
                    'label' => 'Evento(s)',
                    'default' => 20,
                    'option' => 'events_per_page'
                );
                add_screen_option( $option, $args );
            } );
        } );
        add_filter( 'set-screen-option', function( $status, $option, $value ) {
            if ( 'events_per_page' == $option )
                return $value;
            return $status;
        }, 10, 3 );
        
        /*
         * Page Hidden Event Upload
         */
        $hookname = get_plugin_page_hookname(
                $wm_event_config[ 'slug_page_upload_view' ],
                $wm_event_config[ 'slug_page_list_view' ]
        );
        add_action( $hookname, array( 'WM_Event_Admin', 'event_upload_view' ) );
        $_registered_pages[$hookname] = true;
        
        /*
         * Add New Event Item in New Content Menu
         */
        add_action( 'wp_before_admin_bar_render', function (){
            global $wp_admin_bar, $wm_event_config;
            $wp_admin_bar->add_node( $args = array(
                'parent' => 'new-content',
                'id'     => 'new-event',
                'title'  => 'Evento',
                'href'   => admin_url( 'admin.php?page=' . $wm_event_config['slug_page_form_view'] )
            ) );
        }, 100 );
    }
    
    /*
     * Initialization method used only in wp-admin.
     */
    public static function admin_init() {
        global $wm_event_config;

        // Ckeck is set action 
        if ( isset( $_GET['action'] ) ) {
            // Event ID
            $event_id = isset( $_GET['eid'] ) ? $_GET['eid'] : null;
            
            // Event Picture ID
            $event_picture_id = isset( $_GET['epid'] ) ? $_GET['epid'] : null;
            
            // Ckeck action save event
            if ( $_GET['action'] == $wm_event_config['slug_action_save'] ) {
                WM_Event_Admin::save();
            }
            
            // Ckeck action delete event
            if ( $_GET['action'] == $wm_event_config['slug_action_delete'] && ! is_null( $event_id ) ) {
                WM_Event_Admin::delete( $event_id );
            }

            // Ckeck action delete picture event
            if ( $_GET['action'] == $wm_event_config['slug_action_delete_picture'] && ! is_null( $event_picture_id ) ) {
                WM_Event_Admin::delete_picture( $event_picture_id, $event_id );
            }
        } // end check action
    }

    /*
     * Indexed list of events
     */
    public static function event_list_view() {
        global $wm_event_config;
        
        // Include Event List Table Class
        require_once 'views/class.event-list-table.php';
        
        // Status deleted
        $deleted = isset( $_GET['deleted'] ) ? $_GET['deleted'] : '';
        
        // Status message
        $message = isset( $_GET['message'] ) ? WM_Event_Admin::message( $_GET['message'], $deleted ) : false;
        
        // Include Event List View
        require_once 'views/event-list.php';
    }
    
    /*
     * Registration Form Event
     */
    public static function event_form_view() {
        global $wm_event_config;
        $event_id = isset( $_GET['eid'] ) ? $_GET['eid'] : -1;
                
        // Include Event Picture List Table Class
        require_once 'views/class.event-picture-list-table.php';
        
        // Message ID
        $message_id = isset( $_GET['message'] ) ? $_GET['message'] : false;
        
        // Status message
        $message = WM_Event_Admin::message( $message_id );
        
        // Load event
        $event = WM_Event_Model::find_by_id( $event_id );
        
        if ( ! $event ) {
            $event = new stdClass();
            $event->event_id = '';
            $event->event_name = '';
            $event->event_description = '';
            $event->event_slug = '';
            $event->event_date = '';
            $event->event_cover = '';
        } else {
            $event->event_date = date( 'd/m/Y', strtotime( $event->event_date ) );
        }

        // Include Event Form View
        require_once 'views/event-form.php';
    }
    
    /*
     * Upload Files Event
     */
    public static function event_upload_view() {
        global $wm_event_config;
        $event_id = isset( $_GET['eid'] ) ? $_GET['eid'] : -1;
        
        // Load event
        $event = WM_Event_Model::find_by_id( $event_id );
        
        if ( ! $event ) {
            $event = new stdClass();
            $event->event_id = '';
            $event->event_name = '';
            $event->event_description = '';
            $event->event_slug = '';
            $event->event_date = '';
            $event->event_cover = '';
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
        
        // Include Event Upload View
        require_once 'views/event-upload.php';
    }
    
    /*
     * Save event
     */
    public static function save() {
        global $wm_event_config;
        
        $event = $_POST;
        
        // Check the user's permissions.
        if ( ! current_user_can( WM_EVENT_CAPABILITY ) ) {
            wp_die( 'Sem permissões suficientes para executar esta ação.' );
        }
        
        // Validade event name field
        if ( trim( $event['event_name'] ) == '' ) {
            return wp_redirect( menu_page_url( $wm_event_config['slug_page_form_view'], false ) . '&eid=' . $event['event_id'] . '&message=9' );
        }
        
        // Validade format event date
        if ( empty( trim( $event['event_date'] ) ) || ! preg_match( "~\d{2,2}/\d{2,2}/\d{4,4}~", $event['event_date'] ) ) {
            return wp_redirect( menu_page_url( $wm_event_config['slug_page_form_view'], false ) . '&eid=' . $event['event_id'] . '&message=10' );
        }
        
        // Validade event date field
        $event_date_array = explode( '/', $event['event_date'] );
        if ( ! checkdate( $event_date_array[1], $event_date_array[0], $event_date_array[2] ) ) {
            return wp_redirect( menu_page_url( $wm_event_config['slug_page_form_view'], false ) . '&eid=' . $event['event_id'] . '&message=10' );
        }
        
        // Format event date
        $event['event_date'] = date( 'Y-m-d', mktime( 0, 0, 0, $event_date_array[1], $event_date_array[0], $event_date_array[2] ) );
        
        // Generate slug event
        $event['event_slug'] = sanitize_title( $event['event_name'] );
        
        if ( empty( $event['event_id'] ) ) { // Insert event
            $affected_records = WM_Event_Model::insert( $event );
            $event['event_id'] = WM_Event_Model::find_by_last_event_id();
            mkdir( WM_EVENT_UPLOAD_DIR . $event['event_id'] );
            
        } else { // Updade event
            $affected_records = WM_Event_Model::update( $event );
            
            // Update Picture Description
            if ( isset( $event['picture_description'] ) && is_array( $event['picture_description'] ) ) {
                foreach ( $event['picture_description'] as $key => $value ) {
                    WM_Event_Picture_Model::update( array( 'event_picture_description' => $value ), $key );
                }
            }
        }
        
        if ( $affected_records !== false ) // Redirect on success
            return wp_redirect( menu_page_url( $wm_event_config['slug_page_form_view'], false ) . '&eid=' . $event['event_id'] . '&message=1' );
        
        else // Redirect on failure
            return wp_redirect( menu_page_url( $wm_event_config['slug_page_form_view'], false ) . '&eid=' . $event['event_id'] . '&message=4' );
    }
    
    /*
     * Delete event and picture
     */
    public static function delete( $eid ) {
        global $wm_event_config;
        
        // Check the user's permissions.
        if ( ! current_user_can( WM_EVENT_CAPABILITY ) ) {
            wp_die( 'Sem permissões suficientes para executar esta ação.' );
        }
        
        if ( ! is_array( $eid ) ) { // Single delete event
            
            // Verify that the nonce is valid.
            if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'event_delete_' . $eid ) ) {
                wp_die( 'Requisição inválida.' );
            }

            // Get event pictures
            $pictures = WM_Event_Picture_Model::find_by_event( $eid );
            
            // Delete event pictures
            foreach ( $pictures as $picture )
                WM_Event_Admin::delete_picture ( $picture->event_picture_id );

            // Delete event dir
            if ( is_dir( WM_EVENT_UPLOAD_DIR . $eid . '/' ) )
                rmdir ( WM_EVENT_UPLOAD_DIR . $eid . '/' );
            
            // Delete record in database
            $affected_records = WM_Event_Model::delete( $eid );
            
            if ( $affected_records !== false ) // Redirect on success
                return wp_redirect( menu_page_url( $wm_event_config['slug_page_list_view'], false ) . '&message=2' );

            else // Redirect on failure
                return wp_redirect( menu_page_url( $wm_event_config['slug_page_list_view'], false ) . '&message=5' );
            
        } else { // Bulk delete event
            
            foreach ( $eid as $event_id ) {
                // Get event pictures
                $pictures = WM_Event_Picture_Model::find_by_event( $event_id );

                // Delete event pictures
                foreach ( $pictures as $picture )
                    WM_Event_Admin::delete_picture ( $picture->event_picture_id );

                // Delete record in database
                WM_Event_Model::delete( $event_id );
            }
            
            // Redirect on success
            return wp_redirect( menu_page_url( $wm_event_config['slug_page_list_view'], false ) . '&message=11&deleted=' . count( $eid ) );
        }
        
        // On success bulk delete
        return true;
    }
    
    /*
     * Delete picture from event
     */
    public static function delete_picture( $epid, $eid = null ) {
        global $wm_event_config;
        
        // Check the user's permissions.
        if ( ! current_user_can( WM_EVENT_CAPABILITY ) ) {
            wp_die( 'Sem permissões suficientes para executar esta ação.' );
        }
        
        // Verify that the nonce is valid.
        if ( ! is_null( $eid ) ) {
            if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'event_picture_delete_' . $epid ) ) {
                wp_die( 'Requisição inválida.' );
            }
        }
        
        // Get event picture
        $picture = WM_Event_Picture_Model::find_by_id( $epid );
        
        // Check event picture
        if ( ! $picture ) {
            
            // Redirect on failure
            if ( ! is_null( $eid ) )
                return wp_redirect( menu_page_url( $wm_event_config['slug_page_form_view'], false ) . '&eid=' . $eid . '&message=6' );
            
            // Return on failure
            else
                return false;
        }
        
        // OK, it's safe to delete data now.
        
        $image_file = WM_EVENT_UPLOAD_DIR . $picture->event_id . '/' . $picture->event_picture_filename;
        $thumb_file = WM_EVENT_UPLOAD_DIR . $picture->event_id . '/' . $picture->event_picture_thumbnail;
        
        // Delete files
        @unlink( $image_file );
        @unlink( $thumb_file );
        
        // Check if file exists
        if ( is_file( $image_file ) || is_file( $thumb_file ) ) {
            
            // Redirect on failure
            if ( ! is_null( $eid ) )
                return wp_redirect( menu_page_url( $wm_event_config['slug_page_form_view'], false ) . '&eid=' . $eid . '&message=7' );
            
            // Return on failure
            else
                return false;
        }
        
        // Delete record in database
        $affected_records = WM_Event_Picture_Model::delete( $epid );
        
        if ( ! $affected_records ) {
            
            // Redirect on failure
            if ( ! is_null( $eid ) )
                return wp_redirect( menu_page_url( $wm_event_config['slug_page_form_view'], false ) . '&eid=' . $eid . '&message=8' );
            
            // Return on failure
            else
                return false;
        }

        // Redirect on success
        if ( ! is_null( $eid ) )
            return wp_redirect( menu_page_url( $wm_event_config['slug_page_form_view'], false ) . '&eid=' . $eid . '&message=3' );

        // Return on success
        return true;
    }
    
    /*
     * Status messages
     */
    public static function message( $msg_id, $text = '' ) {
        $messages = array(
            array( 'Evento atualizada.', 'updated' ),
            array( 'Evento excluída.', 'updated' ),
            array( 'Imagem excluída.', 'updated' ),
            array( 'Falha ao tentar atualizar a evento.', 'error' ),
            array( 'Falha ao tentar excluir a evento.', 'error' ),
            array( 'Esta imagem não existe ou pode já ter sido excluída!', 'error' ),
            array( 'Não foi possível excluir os arquivos, se o problema persistir, verifique as permissões!', 'error' ),
            array( 'Não foi possível excluir o registro no banco de dados!', 'error' ),
            array( 'Informe um nome para a evento.', 'error' ),
            array( 'Informe uma data válida para a evento.', 'error' ),
            array( '<b>' . $text . '</b> evento(s) excluída(s)', 'updated' )
        );
        return $msg_id && isset( $messages[$msg_id-1] ) ? $messages[$msg_id-1] : false;
    }
    
}