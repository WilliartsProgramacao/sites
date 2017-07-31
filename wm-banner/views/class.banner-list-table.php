<?php if ( ! defined( 'ABSPATH' ) ) die( 'No direct script access.' );

class WM_Banner_List_Table extends WP_List_Table {

    var $total_items = 0;
    
    var $total_found_items = 0;
    
    var $found_items = array();

    function get_columns() {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'banner_name' => __( 'Banner' ),
            'banner_dimension' => __( 'Dimensão' ),
            'banner_date' => __( 'Date' ),
            'banner_cover' => __( 'Thumbnail' )
        );
        return $columns;
    }

    function prepare_items() {
        $columns = $this->get_columns();

        $user = get_current_user_id();
        $screen = get_current_screen();
        $screen_option = $screen->get_option( 'per_page', 'option' );
        $per_page_screen = get_user_meta( $user, $screen_option, true );
        
        $per_page = $per_page_screen ? $per_page_screen : 20;
        
        $current_page = $this->get_pagenum();
        $search = isset( $_GET['s'] ) ? '%' . $_GET['s'] . '%' : '%';

        $this->total_items = WM_Banner_Model::count_all();
        
        $this->total_found_items = WM_Banner_Model::count_all( $search );
        
        $items = WM_Banner_Model::find_all(
                $search,
                array (
                    'order' => isset( $_GET['order'] ) ? $_GET['order'] : false,
                    'orderby' => isset( $_GET['orderby'] ) ? $_GET['orderby'] : false,
                    'per_page' => $per_page,
                    'paged' => $current_page
                )
        );
        
        foreach ( $items as $item ) {
            $this->found_items[] = array(
                'ID' => $item->banner_id,
                'banner_name' => $item->banner_name,
                'banner_dimension' => '',
                'banner_width' => $item->banner_width,
                'banner_height' => $item->banner_height,
                'banner_date' => $item->banner_date,
                'banner_cover' => $item->banner_cover
            );
        }

        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array( $columns, $hidden, $sortable );
        usort( $this->found_items, array( &$this, 'usort_reorder' ) );
        
        $this->set_pagination_args( array(
            'total_items' => $this->total_found_items,
            'per_page' => $per_page
        ) );

        $this->items = $this->found_items;
    }

    function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'banner_name' :
                return $item[$column_name];
            case 'banner_dimension' :
                return $item['banner_width'] . ' x ' . $item['banner_height'] . ' px';
            case 'banner_date' :
                return mysql2date( 'd/m/Y', $item[$column_name] )
                    . '<br />' . mysql2date( 'H\hi', $item[$column_name] );
            case 'banner_cover' :
                if ( $item[$column_name] ) {
                    return '<img src="' . WM_BANNER_UPLOAD_URL . $item[$column_name] . '" width="120" />';
                }
                return '<img src="' . WM_BANNER_PLUGIN_URL . 'assets/img/no-image.png" width="120" height="100" />';
            default:
                return print_r( $item, true );
        }
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'banner_name' => array( 'banner_name', false )
        );
        return $sortable_columns;
    }

    function usort_reorder( $a, $b ) {
        $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'ID';
        $order = ( ! empty( $_GET['order'] ) ) ? $_GET['order'] : 'asc';
        $result = strcmp( $a[$orderby], $b[$orderby] );
        return ( $order === 'asc' ) ? $result : -$result;
    }

    function column_cb( $item ) {
        return sprintf(
                '<input type="checkbox" name="bid[]" value="%s" />', $item['ID']
        );
    }

    function get_bulk_actions() {
        $actions = array(
            'delete' => __( 'Delete Permanently' )
        );
        return $actions;
    }

    function process_bulk_action() {
        if ( 'delete' === $this->current_action() ) {
            wp_die( 'Items deleted (or they would be if we had items to delete)!' );
        }
    }

    function column_banner_name( $item ) {
        global $wm_banner_config;
        $actions = array(
            'edit' => '<a href="' . sprintf( menu_page_url( $wm_banner_config[ 'slug_page_form_view' ], false ).'&bid=%s', $item['ID'] ) . '">' . __( 'Edit' ) . '</a>',
            'delete' => '<a href="' . wp_nonce_url( sprintf( admin_url( 'admin.php' ).'?action=%s&bid=%s', $wm_banner_config[ 'slug_action_delete' ], $item['ID'] ), 'banner_delete_'.$item['ID'] ).'">'.__( 'Delete Permanently' ).'</a>',
        );
        $item['banner_name'] = '<a href="' . sprintf( menu_page_url( $wm_banner_config[ 'slug_page_form_view' ], false ).'&bid=%s', $item['ID'] ) . '" class="row-title">' . $item['banner_name'] . '</a>';
        return sprintf( '%1$s %2$s', $item['banner_name'], $this->row_actions( $actions ) );
    }
    
    function display_tablenav( $which ) {
        global $wm_banner_config;
        if ( $which === 'bottom' )
            return;
        ?>
            <div class="tablenav <?php echo esc_attr( $which ); ?>">
                <div class="alignleft actions">
                    <select name="action" id="doaction">
                        <option value="-1" selected="selected">Ações em massa</option>
                        <option value="<?php echo $wm_banner_config[ 'slug_action_delete' ]; ?>">Excluir permanentemente</option>
                    </select>
                    <?php submit_button( __( 'Apply' ), 'action', false, false, array( 'id' => 'banner-doaction' ) ); ?>
                </div>
                <?php
                    $this->extra_tablenav( $which );
                    $this->pagination( $which );
                ?>
                <br class="clear" />
            </div>
        <?php
    }

    function views() {
        global $wm_banner_config;
        ?>
            <ul class="subsubsub">
                <li class="all">
                    <a href="<?php menu_page_url( $wm_banner_config[ 'slug_page_list_view' ] ) ?>">
                        <?php echo __( 'All' ) ?> <span class="count">(<?php echo $this->total_items ?>)</span>
                    </a>
                </li>
            </ul>
        <?php
    }

}