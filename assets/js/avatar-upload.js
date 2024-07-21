jQuery(document).ready(function($){
    var mediaUploader;

    $('#upload-avatar-button').click(function(e) {
        e.preventDefault();
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Avatar',
            button: {
                text: 'Choose Avatar'
            },
            multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#avatar').val(attachment.url);
            $('#avatar-preview').attr('src', attachment.url).show();
        });

        mediaUploader.open();
    });

    $('#remove-avatar-button').click(function() {
        $('#avatar').val('');
        $('#avatar-preview').hide();
    });
});
