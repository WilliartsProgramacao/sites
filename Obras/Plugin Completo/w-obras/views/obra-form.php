<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

wp_enqueue_style( 'w-obra', W_OBRA_PLUGIN_URL . 'assets/css/w-obra.css' );
wp_enqueue_script( 'jquery-maskedinput', W_OBRA_PLUGIN_URL . 'assets/js/jquery.maskedinput.min.js' );
wp_enqueue_media();

?>

<div class="wrap" id="w-obra-form">
    <h2><?php echo empty( $obra->obra_id ) ? 'Adicionar nova obra' : 'Editar obra'; ?></h2>
    
    <?php if ( $message ) : ?>
        <div id="message" class="<?php echo $message[1]; ?> below-h2">
            <p><?php echo $message[0]; ?></p>
        </div>
    <?php endif; ?>
    
    <form name="obraForm" action="<?php echo admin_url( 'admin.php' ) . '?action=' . $w_obra_config['slug_action_save']; ?>" method="post">
        <?php wp_nonce_field( 'obra_form_view', 'obra_form_view_nonce' ); ?>
        <input type="hidden" name="obra_id" value="<?php echo $obra->obra_id ?>">
        
        <div id="post-body" class="metabox-holder columns-2">
            <div id="obra-form-view" class="postbox">
                <h3 class="hndle"><span>Informações da Obra</span></h3>
                <div class="inside">
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row"><label for="obra_name">* Obra</label></th>
                                <td class="<?php echo $message_id == 9 ? 'form-invalid' : '' ?>">
                                    <input type="text" name="obra_name" id="obra_name" value="<?php echo $obra->obra_name ?>" maxlength="45" autofocus="true" required>
                                    <p class="description">Nome da obra</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="obra_description">Descrição</label></th>
                                <td>
                                    <textarea name="obra_description" id="obra_description" rows="8"><?php echo $obra->obra_description ?></textarea>
                                    <p class="description">Descrição da obra</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="obra_date">* Data</label></th>
                                <td class="<?php echo $message_id == 10 ? 'form-invalid' : '' ?>">
                                    <input type="text" name="obra_date" id="obra_date" class="datepicker" value="<?php echo $obra->obra_date ?>" maxlength="10" required>
                                    <p class="description">Data da obra</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="w_upload_button">Imagem capa</label></th>
                                <td>
                                    <input type="hidden" name="obra_cover" id="obra_cover" value="<?php echo $obra->obra_cover ?>" maxlength="255" required>
                                    <button type="button" class="button button-upload" id="w_upload_button" data-title="Selecione uma imagem">
                                        <span class="dashicons dashicons-format-image"></span>
                                        Selecionar imagem
                                    </button>
                                    <a href="javascript:void(0)" class="btn-link-remove" id="w_remove_link">Remover</a>
                                    <div id="w_image_upload">
                                        <?php if ( ! empty( $obra->obra_cover ) ) : ?>
                                            <?php $attachment = wp_get_attachment_image( $obra->obra_cover, 'medium' ); ?>
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
        
        <?php if ( ! empty( $obra->obra_id ) ) : ?>
            <?php
                $picture_list_table = new W_Obra_Picture_List_Table();
                $picture_list_table->prepare_items();
                $picture_list_table->get_sortable_columns();

                $picture_list_table->display();
            ?>
            <p class="submit">  ''
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
    jQuery("#w_upload_button").live("click", function( obra ){
        obra.probraDefault();
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
            jQuery("#obra_cover").val(attachment.id);
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
    jQuery("#w_remove_link").live("click", function( obra ){
        jQuery("#obra_cover").val("");
        jQuery("#w_image_upload").html("");
    });
    
</script>