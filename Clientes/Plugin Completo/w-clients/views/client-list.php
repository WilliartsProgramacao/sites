<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

wp_enqueue_style( 'w-client', W_CLIENT_PLUGIN_URL . 'assets/css/w-client.css' );

$client_list_table = new W_Client_List_Table();
$client_list_table->prepare_items();
$client_list_table->get_sortable_columns();
?>

<div class="wrap" id="w-client-list">
    <h2>Clientes <a href="<?php menu_page_url( $w_client_config[ 'slug_page_form_view' ] ); ?>" class="add-new-h2">Adicionar Novo</a></h2>
    
    <?php if ( $message ) : ?>
        <div id="message" class="<?php echo $message[1]; ?> below-h2">
            <p><?php echo $message[0]; ?></p>
        </div>
    <?php endif; ?>
    
    <?php $client_list_table->views(); ?>
    
    <form id="client-filter" method="get" onsubmit="clientListView.onSubmit(this);">
        <input type="hidden" name="page" id="frm-page" value="<?php echo $w_client_config['slug_page_list_view']; ?>">
        <?php $client_list_table->search_box( 'Pesquisar clientes', 'search-client-id'); ?>
        <?php $client_list_table->display(); ?>
    </form>
</div>

<script type="text/javascript">
    
    var clientListView = {
        
        init : function () {
            jQuery("#doaction").change(function(){
                if (this.value == "<?php echo $w_client_config[ 'slug_action_delete' ]; ?>") {
                    jQuery("#client-filter").attr("action", "<?php echo admin_url( 'admin.php' ); ?>");
                }else{
                    jQuery("#client-filter").attr("action", "");
                }
            });
        },
        
        onSubmit : function(el) {
            if (jQuery("#doaction").val() == "<?php echo $w_client_config[ 'slug_action_delete' ]; ?>") {
                jQuery("#frm-page").remove();
            }
        }
        
    };
    jQuery(function(){ clientListView.init(); });
    
</script>