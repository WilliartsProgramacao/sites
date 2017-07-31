<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

wp_enqueue_style( 'wm-event', WM_EVENT_PLUGIN_URL . 'assets/css/wm-event.css' );

$event_list_table = new WM_Event_List_Table();
$event_list_table->prepare_items();
$event_list_table->get_sortable_columns();
?>

<div class="wrap" id="wm-event-list">
    <h2>Eventos <a href="<?php menu_page_url( $wm_event_config[ 'slug_page_form_view' ] ); ?>" class="add-new-h2">Adicionar Novo</a></h2>
    
    <?php if ( $message ) : ?>
        <div id="message" class="<?php echo $message[1]; ?> below-h2">
            <p><?php echo $message[0]; ?></p>
        </div>
    <?php endif; ?>
    
    <?php $event_list_table->views(); ?>
    
    <form id="event-filter" method="get" onsubmit="eventListView.onSubmit(this);">
        <input type="hidden" name="page" id="frm-page" value="<?php echo $wm_event_config['slug_page_list_view']; ?>">
        <?php $event_list_table->search_box( 'Pesquisar eventos', 'search-event-id'); ?>
        <?php $event_list_table->display(); ?>
    </form>
</div>

<script type="text/javascript">
    
    var eventListView = {
        
        init : function () {
            jQuery("#doaction").change(function(){
                if (this.value == "<?php echo $wm_event_config[ 'slug_action_delete' ]; ?>") {
                    jQuery("#event-filter").attr("action", "<?php echo admin_url( 'admin.php' ); ?>");
                }else{
                    jQuery("#event-filter").attr("action", "");
                }
            });
        },
        
        onSubmit : function(el) {
            if (jQuery("#doaction").val() == "<?php echo $wm_event_config[ 'slug_action_delete' ]; ?>") {
                jQuery("#frm-page").remove();
            }
        }
        
    };
    jQuery(function(){ eventListView.init(); });
    
</script>