import $ from 'jquery';

export function initMediaUploader(imageInputId, previewImageId, uploadButtonId, removeButtonId) {
    var mediaUploader;

    $('#upload_image_button').on('click', function(event) {
        event.preventDefault();

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Select or Upload an Image',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            var imageUrl = attachment.url;

            $('#pd_seo_optimizer_post_logo').val(imageUrl);
            $('#preview_image').attr('src', imageUrl).show();
            
            $('#remove_image_button').show();
        });

        mediaUploader.open();
    });

    $('#remove_image_button').on('click', function(event) {
        event.preventDefault();

        $('#pd_seo_optimizer_post_logo').val('');
        $('#preview_image').attr('src', '').hide();
        
        $(this).hide();
    });
}