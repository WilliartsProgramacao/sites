<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' ); ?>

<?php wp_enqueue_style( 'wm-banner', WM_BANNER_PLUGIN_URL . 'assets/css/wm-banner.css' ); ?>

<div class="wrap" id="wm-banner-form">
    <h2><?php echo empty( $banner->banner_id ) ? 'Adicionar nova banner' : 'Editar banner'; ?></h2>
    
    <?php if ( $message ) : ?>
        <div id="message" class="<?php echo $message[1]; ?> below-h2">
            <p><?php echo $message[0]; ?></p>
        </div>
    <?php endif; ?>
    
    <form name="bannerForm" action="<?php echo admin_url( 'admin.php' ) . '?action=' . $wm_banner_config['slug_action_save']; ?>" method="post">
        <?php wp_nonce_field( 'banner_form_view', 'banner_form_view_nonce' ); ?>
        <input type="hidden" name="banner_id" value="<?php echo $banner->banner_id ?>">
        
        <div id="post-body" class="metabox-holder columns-2">
            <div id="banner-form-view" class="postbox">
                <h3 class="hndle"><span>Informações do Banner</span></h3>
                <div class="inside">
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row"><label for="banner_name">* Banner</label></th>
                                <td class="<?php echo $message_id == 9 ? 'form-invalid' : '' ?>">
                                    <input type="text" name="banner_name" id="banner_name" value="<?php echo $banner->banner_name ?>" maxlength="45" autofocus="true" required>
                                    <p class="description">Nome do banner para futuras consultas.</p>
                                </td>
                            </tr>
                            <tr class="dimension<?php echo empty( $banner->banner_id ) ? ' hidden' : ''; ?>">
                                <th scope="row"><label>Dimensão</label></th>
                                <td>
                                    <p>
                                        <b><?php echo $banner->banner_width . ' x ' . $banner->banner_height . ' px'; ?></b>
                                        <a href="javascript:void(0)" onclick="bannerFormView.changeDimension();" style="margin-left: 20px;">Alterar dimensão</a>
                                    </p>
                                </td>
                            </tr>
                            <tr class="size<?php echo ! empty( $banner->banner_id ) ? ' hidden' : ''; ?>">
                                <th scope="row"><label for="banner_width">* Largura</label></th>
                                <td class="<?php echo $message_id == 10 ? 'form-invalid' : '' ?>">
                                    <input type="number" name="banner_width" id="banner_width" value="<?php echo $banner->banner_width ?>" min="1" max="999999" maxlength="6" required>
                                    <p class="description">Largura do banner.</p>
                                </td>
                            </tr>
                            <tr class="size<?php echo ! empty( $banner->banner_id ) ? ' hidden' : ''; ?>">
                                <th scope="row"><label for="banner_height">* Altura</label></th>
                                <td class="<?php echo $message_id == 10 ? 'form-invalid' : '' ?>">
                                    <input type="number" name="banner_height" id="banner_height" value="<?php echo $banner->banner_height ?>" min="1" max="999999" maxlength="6" required>
                                    <p class="description">Altura do banner.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="submit">
                        <button type="submit" class="button-primary"><?php echo __( 'Save Changes' ) ?></button>
                    </p>
                </div>
            </div>
        </div>
        
        <?php if ( ! empty( $banner->banner_id ) ) : ?>
            <div class="tablenav top" id="tablenav-top-picture">
                <button class="button" type="button" onclick="location.href='<?php echo admin_url( 'admin.php' ) . '?page=' . $wm_banner_config['slug_page_upload_view'] . '&bid=' . $banner->banner_id; ?>'">Carregar imagem</button>
            </div>
            <?php
                $picture_list_table = new WM_Banner_Picture_List_Table();
                $picture_list_table->prepare_items();
                $picture_list_table->get_sortable_columns();

                $picture_list_table->display();
            ?>
            <p class="submit">
                <button type="submit" class="button-primary"><?php echo __( 'Save Changes' ) ?></button>
            </p>
        <?php endif; ?>
    </form>
</div>

<script type="text/javascript">
    
    var bannerFormView = {
        
        changeDimension : function() {
            jQuery("#wm-banner-form .dimension").hide();
            jQuery("#wm-banner-form #tablenav-top-picture").hide();
            jQuery("#wm-banner-form .size").show();
        }
        
    };
    
</script>