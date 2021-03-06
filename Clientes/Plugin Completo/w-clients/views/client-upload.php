<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' ); ?>

<?php wp_enqueue_style( 'w-client', W_CLIENT_PLUGIN_URL . 'assets/css/w-client.css' ); ?>

<?php if ( empty( $client->client_id ) ) : ?>

    <script>location.href="<?php menu_page_url( $w_client_config['slug_page_list_view'] ); ?>";</script>

<?php elseif ( $upload_size_unit == 0 ) : ?>
    
    <div class="wrap osb-client-upload">
        <h2>Cliente - <?php echo $client->client_name ?></h2>
        <p>Você usou toda sua cota de armazenamento de <?php echo get_space_allowed(); ?> MB.</p>
    </div>
    
<?php else : ?>

    <div class="wrap osb-client-upload">
        <h2>Cliente - <?php echo $client->client_name ?></h2>
        <form enctype="multipart/form-data" id="fileupload" method="post" action="#" class="media-upload-form type-form validate">
            <div id="plupload-upload-ui" class="hide-if-no-js drag-drop">
                <div id="drag-drop-area">
                    <div class="drag-drop-inside">
                        <p class="drag-drop-info">Solte os arquivos aqui</p>
                        <p>ou</p>
                        <p class="drag-drop-buttons">
                            <a href="javascript:;" class="button" id="btn-select-files">Selecionar arquivos</a>
                        </p>
                    </div>
                </div>
            </div>
            <p><span class="max-upload-size">Tamanho máximo do arquivo: <?php echo $upload_size_unit; ?>.</span></p>
            <div id="media-items"></div>
            <div class="submit">
                <button type="button" class="button-primary" onclick="clientUploadView.close();">Concluír</button>
            </div>
            <div id="console"></div>
        </form>
    </div>

    <script type="text/javascript" src="<?php echo W_CLIENT_PLUGIN_URL . 'assets/js/plupload.full.min.js'; ?>"></script>
    <script type="text/javascript" src="<?php echo W_CLIENT_PLUGIN_URL . 'assets/js/i18n/pt_BR.js'; ?>"></script>
    <script type="text/javascript">

        var clientUploadView = {
            
            uploading : false,

            init : function() {
                
                var clientUploader = new plupload.Uploader({
                    runtimes: 'html5,flash,silverlight,html4',
                    browse_button: 'btn-select-files',
                    drop_element: 'drag-drop-area',
                    container: document.getElementById('plupload-upload-ui'),
                    url: '<?php echo W_CLIENT_PLUGIN_URL . 'w-client-do_upload.php?eid=' . $client->client_id; ?>',
                    flash_swf_url: '<?php echo W_CLIENT_PLUGIN_URL . 'assets/js/Moxie.swf' ?>',
                    silverlight_xap_url: '<?php echo W_CLIENT_PLUGIN_URL . 'assets/js/Moxie.xap' ?>',
                    filters: {
                        max_file_size: '<?php echo wp_max_upload_size(); ?>',
                        mime_types: [
                            {title: "Imagens", extensions: "jpg,jpeg,gif,png"}
                        ]
                    },
                    init: {
                        PostInit: function() {
                            jQuery("#media-items").html("");

                            if (clientUploader.features.dragdrop) {

                                jQuery("#drag-drop-area")

                                    .on("dragenter", function(client){
                                        client.dataTransfer.dropEffect = "copy";

                                    }).on("dragover", function(){
                                        jQuery(this).parent().addClass("drag-over");

                                    }).on("dragleave", function(){
                                        jQuery(this).parent().removeClass("drag-over");

                                    }).on("drop", function(){
                                        jQuery(this).parent().removeClass("drag-over");

                                    });
                            }
                        },
                        FilesAdded: function(up, files) {
                            clientUploadView.uploading = true;
                            plupload.each(files, function(file) {
                                var html = '<div class="media-item" id="' + file.id + '">' +
                                                '<div class="progress">' +
                                                    '<div class="percent">0%</div>' +
                                                    '<div class="bar" style="width: ' + (file.percent * 2) + 'px;"></div>' +
                                                '</div>' +
                                                '<div class="filename original"> ' + file.name + '</div>' +
                                            '</div>';

                                jQuery("#media-items").html(jQuery("#media-items").html() + html);
                                clientUploader.start();
                            });
                        },
                        UploadProgress: function(up, file) {
                            jQuery("#" + file.id + " .percent").html(file.percent + "%");
                            jQuery("#" + file.id + " .bar").css({"width": (file.percent * 2) + "px"});
                        },
                        FileUploaded: function(up, file, info) {
                            var response = eval('(' + info.response + ')');
                            if (response.result == null) {
                                jQuery("#"+file.id).html(
                                    '<img class="pinkynail" src="<?php echo W_CLIENT_UPLOAD_URL . $client->client_id . '/'; ?>' + response.thumbnail + '" alt="">' +
                                    '<a class="edit-attachment" href="javascript:void(0)" onclick="jQuery(this).parent().fadeOut()" target="_blank">Dispensar</a>' +
                                    '<div class="filename new">' +
                                        '<span class="title">'+response.filename+'</span>' +
                                    '</div>'
                                );
                            } else {
                                jQuery("#" + file.id + " .percent").html("Falhou");
                                alert(response.error.message);
                            }
                        },
                        UploadComplete: function(up, files) {
                            clientUploadView.uploading = false;
                        },
                        Error: function(up, err) {
                            var html = '<div id="media-item-' + err.file.id + '" class="media-item error">' +
                                            '<p>' + err.file.name + ' - ' + err.message + '</p>' +
                                       '</div>';
                            jQuery("#media-items").append(html);
                        }
                    }
                });
                clientUploader.init();
            },

            close : function() {
                if (( ! clientUploadView.uploading) || (clientUploadView.uploading && confirm("Ainda não terminamos de carregar sua lista de arquivos, deseja concluír assim mesmo?"))) {
                    location.href = '<?php echo menu_page_url( $w_client_config['slug_page_form_view'], false ) . '&eid=' . $client->client_id ; ?>';
                }
            }

        };
        jQuery( function() { clientUploadView.init(); });

    </script>
    
<?php endif; ?>