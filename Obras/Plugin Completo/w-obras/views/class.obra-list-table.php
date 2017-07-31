<?php if ( ! defined( 'ABSPATH' ) ) die( 'No direct script access.' );

class W_Obra_List_Table extends WP_List_Table {

    var $total_items = 0;
    
    var $total_found_items = 0;
    
    var $found_items = array();

    function get_columns() {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'obra_name' => __( 'Obra' ),
            'obra_date' => __( 'Date' ),
            'obra_cover' => __( 'Thumbnail' ),
            'obra_slug' => __( 'Slug' )
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

        $this->total_items = W_Obra_Model::count_all();
        
        $this->total_found_items = W_Obra_Model::count_all( $search );
        
        $items = W_Obra_Model::find_all(
                $search,
                array (
                    'orderby' => array( 'obra_date DESC', 'obra_name ASC' ),
                    'per_page' => $per_page,
                    'paged' => $current_page
                )
        );
        
        foreach ( $items as $item ) {
            $this->found_items[] = array(
                'ID' => $item->obra_id,
                'obra_name' => $item->obra_name,
                'obra_date' => $item->obra_date,
                'obra_cover' => $item->obra_cover,
                'obra_slug' => $item->obra_slug
            );
        }

        $hidden = array( 'obra_slug' );
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
            case 'obra_name' :
                return $item[$column_name];
            case 'obra_date' :
                return mysql2date( 'd/m/Y', $item[$column_name] );
            case 'obra_cover' :
                if ( $item[$column_name] ) {
                    $thumbnail = wp_get_attachment_image_src( $item[$column_name], 'thumbnail' )[0];
                    return '<img src="' . $thumbnail . '" width="120" />';
                }
                return '<img src="' . W_OBRA_PLUGIN_URL . 'assets/img/no-image.png" width="120" height="100" />';
            case 'obra_slug' :
                return $item[$column_name];
            default:
                return print_r( $item, true );
        }
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'obra_name' => array( 'obra_name', false )
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
                '<input type="checkbox" name="eid[]" value="%s" />', $item['ID']
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

    function column_obra_name( $item ) {
        global $w_obra_config;
        $actions = array(
            'edit' => '<a href="' . sprintf( menu_page_url( $w_obra_config[ 'slug_page_form_view' ], false ).'&eid=%s', $item['ID'] ) . '">' . __( 'Edit' ) . '</a>',
            'delete' => '<a href="' . wp_nonce_url( sprintf( admin_url( 'admin.php' ).'?action=%s&eid=%s', $w_obra_config[ 'slug_action_delete' ], $item['ID'] ), 'obra_delete_'.$item['ID'] ).'" onclick="return confirm(\'Tem certeza que deseja excluir?\')">'.__( 'Delete Permanently' ).'</a>',
            'view' => '<a href="' . home_url( $w_obra_config['rewrite'] . '/' . $item['ID'] . '/' . $item['obra_slug']  ) . '">'.__( 'View' ).'</a>',
        );
        $item['obra_name'] = '<a href="' . sprintf( menu_page_url( $w_obra_config[ 'slug_page_form_view' ], false ).'&eid=%s', $item['ID'] ) . '" class="row-title">' . $item['obra_name'] . '</a>';
        return sprintf( '%1$s %2$s', $item['obra_name'], $this->row_actions( $actions ) );
    }
    
    function display_tablenav( $which ) {
        global $w_obra_config;
        if ( $which === 'bottom' )
            return;
        ?>
            <div class="tablenav <?php echo esc_attr( $which ); ?>">
                <div class="alignleft actions">
                    <select name="action" id="doaction">
                        <option value="-1" selected="selected">Ações em massa</option>
                        <option value="<?php echo $w_obra_config[ 'slug_action_delete' ]; ?>">Excluir permanentemente</option>
                    </select>
                    <?php submit_button( __( 'Apply' ), 'action', false, false, array( 'id' => 'obra-doaction' ) ); ?>
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
        global $w_obra_config;
        ?>
            <ul class="subsubsub">
                <li class="all">
                    <a href="<?php menu_page_url( $w_obra_config[ 'slug_page_list_view' ] ) ?>">
                        <?php echo __( 'All' ) ?> <span class="count">(<?php echo $this->total_items ?>)</span>
                    </a>
                </li>
            </ul>
        <?php
    }

}