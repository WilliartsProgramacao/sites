<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

wp_enqueue_style( 'wm-banner', WM_BANNER_PLUGIN_URL . 'assets/css/wm-banner.css' );

$banner_list_table = new WM_Banner_List_Table();
$banner_list_table->prepare_items();
$banner_list_table->get_sortable_columns();
?>

<div class="wrap" id="wm-banner-list">
    <h2>Banners <a href="<?php menu_page_url( $wm_banner_config[ 'slug_page_form_view' ] ); ?>" class="add-new-h2">Adicionar Novo</a></h2>
    
    <?php if ( $message ) : ?>
        <div id="message" class="<?php echo $message[1]; ?> below-h2">
            <p><?php echo $message[0]; ?></p>
        </div>
    <?php endif; ?>
    
    <?php $banner_list_table->views(); ?>
    
    <form id="banner-filter" method="get" onsubmit="bannerListView.onSubmit(this);">
        <input type="hidden" name="page" id="frm-page" value="<?php echo $wm_banner_config['slug_page_list_view']; ?>">
        <?php $banner_list_table->search_box( 'Pesquisar banners', 'search-banner-id'); ?>
        <?php $banner_list_table->display(); ?>
    </form>
</div>

<script type="text/javascript">
    
    var bannerListView = {
        
        init : function () {
            jQuery("#doaction").change(function(){
                if (this.value == "<?php echo $wm_banner_config[ 'slug_action_delete' ]; ?>") {
                    jQuery("#banner-filter").attr("action", "<?php echo admin_url( 'admin.php' ); ?>");
                }else{
                    jQuery("#banner-filter").attr("action", "");
                }
            });
        },
        
        onSubmit : function(el) {
            if (jQuery("#doaction").val() == "<?php echo $wm_banner_config[ 'slug_action_delete' ]; ?>") {
                jQuery("#frm-page").remove();
            }
        }
        
    };
    jQuery(function(){ bannerListView.init(); });
    
</script>