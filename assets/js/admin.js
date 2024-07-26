jQuery(document).ready(function ($) {
    var mediaUploader;

    $('#upload_logo_button').click(function (e) {
        e.preventDefault();
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Select Logo',
            button: {
                text: 'Select Logo'
            }, multiple: false });

        mediaUploader.on('select', function () {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#vulpes_lms_logo').val(attachment.url);
            $('#vulpes_lms_logo_preview').attr('src', attachment.url).show();
            $('#remove_logo_button').show();
        });
        mediaUploader.open();
    });

    $('#remove_logo_button').click(function (e) {
        e.preventDefault();
        $('#vulpes_lms_logo').val('');
        $('#vulpes_lms_logo_preview').hide();
        $('#remove_logo_button').hide();
    });
});
