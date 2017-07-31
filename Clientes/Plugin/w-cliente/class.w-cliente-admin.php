<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

class W_Cliente_Admin {
     
    public static function init() {
        
        add_filter( 'enter_title_here', array( 'W_Cliente_Admin', 'change_default_title_post_type' ) );

        /*
         * Create receita post type
         */
        $post_type_labels = array(
		'name'               => 'Clientes',
		'singular_name'      => 'Cliente',
		'menu_name'          => 'Clientes',
		'name_admin_bar'     => 'Cliente',
		'add_new'            => 'Adicionar Novo',
		'add_new_item'       => 'Adicionar novo cliente',
		'new_item'           => 'Novo Cliente',
		'edit_item'          => 'Editar Cliente',
		'view_item'          => 'Ver Cliente',
		'all_items'          => 'Todos os Clientes',
		'search_items'       => 'Pesquisar',
		'not_found'          => 'Nenhum cliente foi encontrado.',
		'not_found_in_trash' => 'Nenhum cliente foi encontrado na lixeira.',
	);
        register_post_type( 'clientes', array(
		'labels'             => $post_type_labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => false,
		'query_var'          => true,
		'menu_icon'          => 'dashicons-admin-users',
		'rewrite'            => array( 'slug' => 'clientes' ),
        'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title' )
	));
        
        // Add to admin_init function
        add_filter( 'manage_edit-clientes_columns', function ( $clientes_columns ) {
            $new_columns['cb'] = '<input type="checkbox" />';
            $new_columns['title'] = _x( 'Title', 'column name' );
            $new_columns['date'] = _x( 'Date', 'column name' );
            $new_columns['thumbnail'] = __( 'Thumbnail' );
            return $new_columns;
        } );
        
        // Render table-list post type
        add_action( 'manage_clientes_posts_custom_column', function ( $column_name, $id ) {
            switch ( $column_name ) {
                case 'thumbnail':
                    echo get_the_post_thumbnail( $id, array( 100, 100 ) );
                    break;
                default:
                    break;
            } // end switch
        }, 10, 2 );
        
    }

    // Change default title in post type Author
    public static function change_default_title_post_type( $title ){
         $screen = get_current_screen(); 
         if  ( $screen->post_type == 'clientes' ) {
              return 'Nome Cliente';
         }    
         return $title;
    }

    // Render CSS Styles
    public static function render_css() {
        global $post_type;
        
        // If the current post type doesn't match, return, ie. end execution here
	if( $post_type != 'clientes' )
            return;
        
        ?>
            <style type="text/css">
            .post-type-clientes .wp-list-table .column-date { width: 150px !important; }
            .post-type-clientes .wp-list-table .column-thumbnail { width: 100px; }
        </style>
        <?php
    }
    
}