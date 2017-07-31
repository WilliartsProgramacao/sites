<?php if ( ! defined( 'ABSPATH' ) ) die( 'No direct script access.' );

class W_Obra_Picture_List_Table extends WP_List_Table {

    var $total_items = 0;
    
    var $total_found_items = 0;
    
    var $found_items = array();

    function get_columns() {
        $columns = array(
            'ID' => __( 'ID' ),
            'picture' => __( 'Thumbnail' ),
            'filename' => __( 'Nome do Arquivo' ),
            'description' => __( 'Description' ),
            'uploaded' => __( 'Uploaded' )
        );
        return $columns;
    }

    function prepare_items() {
        $columns = $this->get_columns();

        $eid = isset( $_GET['eid'] ) ? $_GET['eid'] : 0;
        
        $per_page = 25;
        $current_page = $this->get_pagenum();
        $this->total_items = W_Obra_Picture_Model::count_all( $eid );
        $this->total_found_items = $this->total_items;

        $items = W_Obra_Picture_Model::find_by_obra( $eid, array (
            'per_page' => $per_page,
            'paged' => $current_page
        ) );
        
        foreach ( $items as $item ) {
            $this->found_items[] = array(
                'ID' => $item->obra_picture_id,
                'picture' => $item->obra_picture_filename,
                'filename' => $item->obra_picture_filename,
                'description' => $item->obra_picture_description,
                'uploaded' => $item->obra_picture_uploaded
            );
        }
        
        $hidden = array( 'uploaded' );
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array( $columns, $hidden, $sortable );
        
        $this->set_pagination_args( array(
            'total_items' => $this->total_found_items,
            'per_page' => $per_page
        ) );
        
        $this->items = $this->found_items;
    }

    function column_default( $item, $column_name ) {
        $eid = isset( $_GET['eid'] ) ? $_GET['eid'] : 0;
        switch ( $column_name ) {
            case 'ID':
                return $item[$column_name];
            case 'picture':
                $file_info = pathinfo( $item[$column_name] );
                $extension = in_array( $file_info['extension'], array( 'jpg', 'jpeg', 'png', 'gif' ) ) ? $file_info['extension'] : 'png';
                return '<img src="' . W_OBRA_UPLOAD_URL . $eid . '/' . $file_info['filename'] . '_thumb.' . $extension . '" alt="" title="" height="75">';
            case 'filename':
                return '<strong>' . $item[$column_name] . '</strong><br>' . mysql2date( get_option( 'date_format' ), $item['uploaded'] ) .
                        $this->column_name( $item );
            case 'description':
                return '<textarea class="picture_description" name="picture_description[' . $item['ID'] . ']">' . $item[$column_name] . '</textarea>';
            default:
                return print_r( $item, true );
        }
    }

    function column_name( $item ) {
        global $w_obra_config
        $_wpnonce = wp_create_nonce( 'obra_picture_delete_' . $item['ID'] );
        $actions = array(
            'delete' => sprintf(
                    '<a href="'.admin_url( 'admin.php' ).'?action=%s&eid=%d&epid=%d&_wpnonce=%s">Excluir imagem</a>',
                    $w_obra_config['slug_action_delete_picture'],
                    isset( $_GET['eid'] ) ? $_GET['eid'] : 0,
                    $item['ID'],
                    $_wpnonce
            )
        );
        return sprintf( '%1$s', $this->row_actions( $actions ) );
    }

    function display_tablenav( $which ) {
        global $w_obra_config;
        $eid = isset( $_GET['eid'] ) ? $_GET['eid'] : 0;
        
        if ( $which === 'bottom' )
            return;
        ?>
            <div class="tablenav <?php echo esc_attr( $which ); ?>">
                <div class="alignleft actions">
                    <button class="button" type="button" onclick="location.href='<?php echo admin_url( 'admin.php' ) . '?page=' . $w_obra_config['slug_page_upload_view'] . '&eid=' . $eid; ?>'">Carregar imagem</button>
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
        ?><ul class="subsubsub">
            <li class="all"><a href="#"><?php echo __( 'All' ) ?> <span class="count">(<?php echo $this->obra_count ?>)</span></a></li>
        </ul><?php
    }

}
