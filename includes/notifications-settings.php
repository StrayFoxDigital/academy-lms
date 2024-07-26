<?php

// Register settings and add fields
add_action('admin_init', 'vulpes_lms_register_notification_settings');
function vulpes_lms_register_notification_settings() {
    register_setting('vulpes_lms_notifications_settings', 'vulpes_lms_notifications_settings');

    add_settings_section(
        'vulpes_lms_notifications_main',
        'Notification Settings',
        'vulpes_lms_notifications_section_text',
        'vulpes_lms_notifications'
    );

    add_settings_field(
        'vulpes_lms_notifications_enabled',
        'Enable Notifications',
        'vulpes_lms_notifications_enabled_field',
        'vulpes_lms_notifications',
        'vulpes_lms_notifications_main'
    );

    add_settings_field(
        'vulpes_lms_notifications_html',
        'Use HTML Emails',
        'vulpes_lms_notifications_html_field',
        'vulpes_lms_notifications',
        'vulpes_lms_notifications_main'
    );

    add_settings_field(
        'vulpes_lms_notifications_logo',
        'Email Logo',
        'vulpes_lms_notifications_logo_field',
        'vulpes_lms_notifications',
        'vulpes_lms_notifications_main'
    );

    add_settings_field(
        'vulpes_lms_notifications_messages',
        'Notification Messages',
        'vulpes_lms_notifications_messages_field',
        'vulpes_lms_notifications',
        'vulpes_lms_notifications_main'
    );
}

function vulpes_lms_notifications_section_text() {
    echo '<p>Configure the notification settings for Vulpes LMS.</p>';
}

function vulpes_lms_notifications_enabled_field() {
    $options = get_option('vulpes_lms_notifications_settings');
    $enabled = isset($options['enabled']) ? $options['enabled'] : 0;
    echo "<input type='checkbox' name='vulpes_lms_notifications_settings[enabled]' value='1' " . checked(1, $enabled, false) . " />";
}

function vulpes_lms_notifications_html_field() {
    $options = get_option('vulpes_lms_notifications_settings');
    $html = isset($options['html']) ? $options['html'] : 0;
    echo "<input type='checkbox' name='vulpes_lms_notifications_settings[html]' value='1' " . checked(1, $html, false) . " />";
}

function vulpes_lms_notifications_logo_field() {
    $options = get_option('vulpes_lms_notifications_settings');
    $logo = isset($options['logo']) ? $options['logo'] : '';
    echo '<input type="hidden" name="vulpes_lms_notifications_settings[logo]" id="vulpes_lms_logo" value="' . esc_attr($logo) . '" />';
    echo '<img id="vulpes_lms_logo_preview" src="' . esc_url($logo) . '" style="max-width: 150px; height: auto; display: ' . (empty($logo) ? 'none' : 'block') . ';" />';
    echo '<input type="button" class="button" value="Upload Logo" id="upload_logo_button" />';
    echo '<input type="button" class="button" value="Remove Logo" id="remove_logo_button" style="display: ' . (empty($logo) ? 'none' : 'inline-block') . ';" />';
    echo '<p class="description">Select a logo to include in the HTML emails.</p>';
}

function vulpes_lms_notifications_messages_field() {
    $options = get_option('vulpes_lms_notifications_settings');
    $messages = isset($options['messages']) ? $options['messages'] : array();
    $default_messages = array(
        'employee_enrollment' => 'You have been enrolled into $site_name.',
        'course_enrollment' => 'You have been enrolled in $course_name.',
        'course_expiry_soon' => 'Your course $course_name is due to expire in 30 days.',
        'course_expired' => 'Your course $course_name has expired.'
    );

    foreach ($default_messages as $key => $default_message) {
        $message = isset($messages[$key]) ? $messages[$key] : $default_message;
        echo '<p><strong>' . ucfirst(str_replace('_', ' ', $key)) . ':</strong></p>';
        echo '<textarea name="vulpes_lms_notifications_settings[messages][' . $key . ']" rows="5" cols="50">' . esc_textarea($message) . '</textarea>';
    }
}

// Enqueue media uploader script for logo upload
add_action('admin_enqueue_scripts', 'vulpes_lms_notifications_scripts');
function vulpes_lms_notifications_scripts() {
    wp_enqueue_media();
    wp_enqueue_script('vulpes_lms_notifications_admin_js', plugin_dir_url(__FILE__) . '../assets/js/admin.js', array('jquery'), null, true);
}
?>
