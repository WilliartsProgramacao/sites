<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

class WM_Receita_Admin {
     
    public static function init() {
        /*
         * Labels receita taxonomy and post type
         */
        $taxonomy_labels = array(
		'name'                       => 'Categorias de Receita',
		'singular_name'              => 'Categoria',
		'menu_name'                  => 'Categorias',
		'add_new'                    => 'Adicionar Nova',
		'add_new_item'               => 'Adicionar nova categoria',
		'new_item'                   => 'Nova Categoria',
		'edit_item'                  => 'Editar Categoria',
		'view_item'                  => 'Ver Categoria',
		'all_items'                  => 'Todas as Categorias',
		'search_items'               => 'Pesquisar',
		'not_found'                  => 'Nenhuma categoria foi encontrada.',
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'update_item'                => 'Categoria atualizada',
		'new_item_name'              => 'Nova categoria',
		'separate_items_with_commas' => 'Categorias separadas por vírgula',
		'add_or_remove_items'        => 'Adicionar ou remover categoria',
		'choose_from_most_used'      => 'Escolha as mais usadas',
	);
        
        /*
         * Create receita taxonomy
         */
        $args = array(
		'hierarchical'      => true,
		'labels'            => $taxonomy_labels,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'cat_receitas' )
	);
        
	register_taxonomy( 'cat_receitas', 'receitas', $args );
        
        /*
         * Create receita post type
         */
        $post_type_labels = array(
		'name'               => 'Receitas',
		'singular_name'      => 'Receita',
		'menu_name'          => 'Receitas',
		'name_admin_bar'     => 'Receita',
		'add_new'            => 'Adicionar Nova',
		'add_new_item'       => 'Adicionar nova receita',
		'new_item'           => 'Nova Receita',
		'edit_item'          => 'Editar Receita',
		'view_item'          => 'Ver Receita',
		'all_items'          => 'Todas as Receitas',
		'search_items'       => 'Pesquisar',
		'not_found'          => 'Nenhuma receita foi encontrada.',
		'not_found_in_trash' => 'Nenhuma receita foi encontrada na lixeira.',
	);
        register_post_type( 'receitas', array(
		'labels'             => $post_type_labels,
                'taxonomies'         => array( 'cat_receitas' ),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => false,
		'query_var'          => true,
		'menu_icon'          => 'dashicons-id',
		'rewrite'            => array( 'slug' => 'receitas' ),
                'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title', 'editor', 'thumbnail' )
	));
        
        // Add to admin_init function
        add_filter( 'manage_edit-receitas_columns', function ( $receitas_columns ) {
            $new_columns['cb'] = '<input type="checkbox" />';
            $new_columns['title'] = _x( 'Title', 'column name' );
            $new_columns['date'] = _x( 'Date', 'column name' );
            $new_columns['thumbnail'] = __( 'Thumbnail' );
            return $new_columns;
        } );
        
        // Render table-list post type
        add_action( 'manage_receitas_posts_custom_column', function ( $column_name, $id ) {
            switch ( $column_name ) {
                case 'thumbnail':
                    echo get_the_post_thumbnail( $id, array( 100, 100 ) );
                    break;
                default:
                    break;
            } // end switch
        }, 10, 2 );
        
    }
    
    // Render CSS Styles
    public static function render_css() {
        global $post_type;
        
        // If the current post type doesn't match, return, ie. end execution here
	if( $post_type != 'receitas' )
            return;
        
        ?>
            <style type="text/css">
            .post-type-receitas .wp-list-table .column-date { width: 150px !important; }
            .post-type-receitas .wp-list-table .column-thumbnail { width: 100px; }
        </style>
        <?php
    }
    
}