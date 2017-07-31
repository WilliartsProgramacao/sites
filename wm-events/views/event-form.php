<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

wp_enqueue_style( 'wm-event', WM_EVENT_PLUGIN_URL . 'assets/css/wm-event.css' );
wp_enqueue_script( 'jquery-maskedinput', WM_EVENT_PLUGIN_URL . 'assets/js/jquery.maskedinput.min.js' );
wp_enqueue_media();

?>

<div class="wrap" id="wm-event-form">
    <h2><?php echo empty( $event->event_id ) ? 'Adicionar novo evento' : 'Editar evento'; ?></h2>
    
    <?php if ( $message ) : ?>
        <div id="message" class="<?php echo $message[1]; ?> below-h2">
            <p><?php echo $message[0]; ?></p>
        </div>
    <?php endif; ?>
    
    <form name="eventForm" action="<?php echo admin_url( 'admin.php' ) . '?action=' . $wm_event_config['slug_action_save']; ?>" method="post">
        <?php wp_nonce_field( 'event_form_view', 'event_form_view_nonce' ); ?>
        <input type="hidden" name="event_id" value="<?php echo $event->event_id ?>">
        
        <div id="post-body" class="metabox-holder columns-2">
            <div id="event-form-view" class="postbox">
                <h3 class="hndle"><span>Informações do Evento</span></h3>
                <div class="inside">
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row"><label for="event_name">* Evento</label></th>
                                <td class="<?php echo $message_id == 9 ? 'form-invalid' : '' ?>">
                                    <input type="text" name="event_name" id="event_name" value="<?php echo $event->event_name ?>" maxlength="45" autofocus="true" required>
                                    <p class="description">Nome do evento que será exibido no site</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="event_description">Descrição</label></th>
                                <td>
                                    <textarea name="event_description" id="event_description" rows="8"><?php echo $event->event_description ?></textarea>
                                    <p class="description">Nome do evento que será exibido no site</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="event_date">* Data</label></th>
                                <td class="<?php echo $message_id == 10 ? 'form-invalid' : '' ?>">
                                    <input type="text" name="event_date" id="event_date" class="datepicker" value="<?php echo $event->event_date ?>" maxlength="10" required>
                                    <p class="description">Data que aconteceu o evento</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="wm_upload_button">Imagem capa</label></th>
                                <td>
                                    <input type="hidden" name="event_cover" id="event_cover" value="<?php echo $event->event_cover ?>" maxlength="255" required>
                                    <button type="button" class="button button-upload" id="wm_upload_button" data-title="Selecione uma imagem">
                                        <span class="dashicons dashicons-format-image"></span>
                                        Selecionar imagem
                                    </button>
                                    <a href="javascript:void(0)" class="btn-link-remove" id="wm_remove_link">Remover</a>
                                    <div id="wm_image_upload">
                                        <?php if ( ! empty( $event->event_cover ) ) : ?>
                                            <?php $attachment = wp_get_attachment_image( $event->event_cover, 'medium' ); ?>
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
        
        <?php if ( ! empty( $event->event_id ) ) : ?>
            <?php
                $picture_list_table = new WM_Event_Picture_List_Table();
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
    jQuery("#wm_upload_button").live("click", function( event ){
        event.preventDefault();
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
            jQuery("#event_cover").val(attachment.id);
            jQuery("#wm_image_upload").html(
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
    jQuery("#wm_remove_link").live("click", function( event ){
        jQuery("#event_cover").val("");
        jQuery("#wm_image_upload").html("");
    });
    
</script>