<?php if ( ! defined( 'ABSPATH' ) ) die( 'No direct script access.' );

class WM_Banner_Picture_List_Table extends WP_List_Table {

    var $total_items = 0;
    
    var $total_found_items = 0;
    
    var $found_items = array();

    function get_columns() {
        $columns = array(
            'ID' => __( 'ID' ),
            'picture' => __( 'Thumbnail' ),
            'filename' => __( 'Nome do Arquivo' ),
            'description' => __( 'Description' ),
            'uploaded' => __( 'Uploaded' ),
            'link' => __( 'Link' ),
            'target' => __( 'Alvo' )
        );
        return $columns;
    }

    function prepare_items() {
        $columns = $this->get_columns();

        $bid = isset( $_GET['bid'] ) ? $_GET['bid'] : 0;

        $items = WM_Banner_Picture_Model::find_by_banner( $bid );

        foreach ( $items as $item ) {
            $this->found_items[] = array(
                'ID' => $item->banner_picture_id,
                'picture' => $item->banner_picture_filename,
                'filename' => $item->banner_picture_filename,
                'description' => $item->banner_picture_description,
                'link' => $item->banner_picture_link,
                'target' => $item->banner_picture_target,
                'uploaded' => $item->banner_picture_uploaded
            );
        }
        
        $hidden = array( 'target', 'uploaded' );
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array( $columns, $hidden, $sortable );
        
        $this->items = $this->found_items;
    }

    function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'ID':
                return $item[$column_name];
            case 'picture':
                $file_info = pathinfo( $item[$column_name] );
                $extension = in_array( $file_info['extension'], array( 'jpg', 'jpeg', 'png', 'gif' ) ) ? $file_info['extension'] : 'png';
                return '<img src="' . WM_BANNER_UPLOAD_URL . $file_info['filename'] . '_thumb.' . $extension . '" alt="" title="" height="75">';
            case 'filename':
                return '<strong>' . $item[$column_name] . '</strong><br>' . mysql2date( get_option( 'date_format' ), $item['uploaded'] ) .
                        $this->column_name( $item );
            case 'description':
                return '<textarea class="picture_description" name="picture_description[' . $item['ID'] . ']">' . $item[$column_name] . '</textarea>';
            case 'link':
                return '<input type="text" class="picture_link" name="picture_link[' . $item['ID'] . ']" value="'.$item[$column_name].'">' .
                    '<br><select name="picture_target[' . $item['ID'] . ']" class="picture_target">' .
                    '<option value="_self"'. ( $item['target'] == '_self' ? ' selected' : '' ) .'>mesma janela</option>' .
                    '<option value="_blank"'. ( $item['target'] == '_blank' ? ' selected' : '' ) .'>nova janela</option></select>';
            default:
                return print_r( $item, true );
        }
    }

    function column_name( $item ) {
        global $wm_banner_config;
        $_wpnonce = wp_create_nonce( 'banner_picture_delete_' . $item['ID'] );
        $actions = array(
            'delete' => sprintf(
                    '<a href="'.admin_url( 'admin.php' ).'?action=%s&bid=%d&gpid=%d&_wpnonce=%s">Excluir imagem</a>',
                    $wm_banner_config['slug_action_delete_picture'],
                    isset( $_GET['bid'] ) ? $_GET['bid'] : 0,
                    $item['ID'],
                    $_wpnonce
            )
        );
        return sprintf( '%1$s', $this->row_actions( $actions ) );
    }

    function display_tablenav( $which ) {
        return;
    }

    function views() {
        ?><ul class="subsubsub">
            <li class="all"><a href="#"><?php echo __( 'All' ) ?> <span class="count">(<?php echo $this->banner_count ?>)</span></a></li>
        </ul><?php
    }

}
