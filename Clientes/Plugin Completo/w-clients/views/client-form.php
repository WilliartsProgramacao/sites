<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

wp_enqueue_style( 'w-client', W_CLIENT_PLUGIN_URL . 'assets/css/w-client.css' );
wp_enqueue_script( 'jquery-maskedinput', W_CLIENT_PLUGIN_URL . 'assets/js/jquery.maskedinput.min.js' );
wp_enqueue_media();

?>

<div class="wrap" id="w-client-form">
    <h2><?php echo empty( $client->client_id ) ? 'Adicionar novo cliento' : 'Editar cliento'; ?></h2>
    
    <?php if ( $message ) : ?>
        <div id="message" class="<?php echo $message[1]; ?> below-h2">
            <p><?php echo $message[0]; ?></p>
        </div>
    <?php endif; ?>
    
    <form name="clientForm" action="<?php echo admin_url( 'admin.php' ) . '?action=' . $w_client_config['slug_action_save']; ?>" method="post">
        <?php wp_nonce_field( 'client_form_view', 'client_form_view_nonce' ); ?>
        <input type="hidden" name="client_id" value="<?php echo $client->client_id ?>">
        
        <div id="post-body" class="metabox-holder columns-2">
            <div id="client-form-view" class="postbox">
                <h3 class="hndle"><span>Informações do Cliente</span></h3>
                <div class="inside">
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row"><label for="client_name">* Cliente</label></th>
                                <td class="<?php echo $message_id == 9 ? 'form-invalid' : '' ?>">
                                    <input type="text" name="client_name" id="client_name" value="<?php echo $client->client_name ?>" maxlength="45" autofocus="true" required>
                                    <p class="description">Nome do cliente que será exibido no site</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="client_description">Descrição</label></th>
                                <td>
                                    <textarea name="client_description" id="client_description" rows="8"><?php echo $client->client_description ?></textarea>
                                    <p class="description">Nome do cliente que será exibido no site</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="client_date">* Data</label></th>
                                <td class="<?php echo $message_id == 10 ? 'form-invalid' : '' ?>">
                                    <input type="text" name="client_date" id="client_date" class="datepicker" value="<?php echo $client->client_date ?>" maxlength="10" required>
                                    <p class="description">Data</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="w_upload_button">Imagem capa</label></th>
                                <td>
                                    <input type="hidden" name="client_cover" id="client_cover" value="<?php echo $client->client_cover ?>" maxlength="255" required>
                                    <button type="button" class="button button-upload" id="w_upload_button" data-title="Selecione uma imagem">
                                        <span class="dashicons dashicons-format-image"></span>
                                        Selecionar imagem
                                    </button>
                                    <a href="javascript:void(0)" class="btn-link-remove" id="w_remove_link">Remover</a>
                                    <div id="w_image_upload">
                                        <?php if ( ! empty( $client->client_cover ) ) : ?>
                                            <?php $attachment = wp_get_attachment_image( $client->client_cover, 'medium' ); ?>
                                            <?php echo $attachment ?>
                                        <?php endif; ?>
                                    </div>
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
        
        <?php if ( ! empty( $client->client_id ) ) : ?>
            <?php
                $picture_list_table = new W_Client_Picture_List_Table();
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
    
    // Datepicker
    jQuery(function($){
        $(".datepicker").mask("99/99/9999");
    });
    
     // Uploading files
    var file_frame;
    jQuery("#w_upload_button").live("click", function( client ){
        client.prclientDefault();
        // If the media frame already exists, reopen it.
        if ( file_frame ) {
            file_frame.open();
            return;
        }
        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: jQuery(this).data("title"),
            multiple: false  // Set to true to allow multiple files to be selected
        });
        // When an image is selected, run a callback.
        file_frame.on( "select", function() {
            // We set multiple to false so only get one image from the uploader
            var attachment = file_frame.state().get("selection").first().toJSON();
            // Do something with attachment.id and/or attachment.url here
            jQuery("#client_cover").val(attachment.id);
            jQuery("#w_image_upload").html(
                jQuery("<img>")
                    .attr("src", attachment.sizes.medium ?
                                 attachment.sizes.medium.url :
                                 attachment.url)
            );
        });
        // Finally, open the modal
        file_frame.open();
    });
    
    // Remove reference file
    jQuery("#w_remove_link").live("click", function( client ){
        jQuery("#client_cover").val("");
        jQuery("#w_image_upload").html("");
    });
    
</script>