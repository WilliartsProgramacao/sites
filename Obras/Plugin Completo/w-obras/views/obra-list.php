<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

wp_enqueue_style( 'w-obra', W_OBRA_PLUGIN_URL . 'assets/css/w-obra.css' );

$obra_list_table = new W_Obra_List_Table();
$obra_list_table->prepare_items();
$obra_list_table->get_sortable_columns();
?>

<div class="wrap" id="w-obra-list">
    <h2>Obras <a href="<?php menu_page_url( $w_obra_config[ 'slug_page_form_view' ] ); ?>" class="add-new-h2">Adicionar Nova</a></h2>
    
    <?php if ( $message ) : ?>
        <div id="message" class="<?php echo $message[1]; ?> below-h2">
            <p><?php echo $message[0]; ?></p>
        </div>
    <?php endif; ?>
    
    <?php $obra_list_table->views(); ?>
    
    <form id="obra-filter" method="get" onsubmit="obraListView.onSubmit(this);">
        <input type="hidden" name="page" id="frm-page" value="<?php echo $w_obra_config['slug_page_list_view']; ?>">
        <?php $obra_list_table->search_box( 'Pesquisar obraos', 'search-obra-id'); ?>
        <?php $obra_list_table->display(); ?>
    </form>
</div>

<script type="text/javascript">
    
    var obraListView = {
        
        init : function () {
            jQuery("#doaction").change(function(){
                if (this.value == "<?php echo $w_obra_config[ 'slug_action_delete' ]; ?>") {
                    jQuery("#obra-filter").attr("action", "<?php echo admin_url( 'admin.php' ); ?>");
                }else{
                    jQuery("#obra-filter").attr("action", "");
                }
            });
        },
        
        onSubmit : function(el) {
            if (jQuery("#doaction").val() == "<?php echo $w_obra_config[ 'slug_action_delete' ]; ?>") {
                jQuery("#frm-page").remove();
            }
        }
        
    };
    jQuery(function(){ obraListView.init(); });
    
</script>