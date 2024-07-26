<?php
// Ensure this file is not accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Send email function
function vulpes_lms_send_email($to, $subject, $message) {
    wp_mail($to, $subject, $message);
}

// Hook into user registration to send an email to the employee
add_action('user_register', 'vulpes_lms_notify_employee_registration');
function vulpes_lms_notify_employee_registration($user_id) {
    $user = get_userdata($user_id);
    $email = $user->user_email;
    $subject = 'Welcome to Vulpes LMS';
    $message = 'Hello ' . $user->display_name . ', welcome to the Vulpes LMS.';
    vulpes_lms_send_email($email, $subject, $message);
}

// Hook into course enrollment to send an email to the employee
add_action('vulpes_lms_course_enrolled', 'vulpes_lms_notify_course_enrollment', 10, 2);
function vulpes_lms_notify_course_enrollment($user_id, $course_name) {
    $user = get_userdata($user_id);
    $email = $user->user_email;
    $subject = 'Course Enrollment Confirmation';
    $message = 'Hello ' . $user->display_name . ', you have been enrolled in the course: ' . $course_name;
    vulpes_lms_send_email($email, $subject, $message);
}

// Check for courses due to expire and send reminder emails
function vulpes_lms_check_courses_expiry() {
    global $wpdb;
    $current_date = date('Y-m-d');
    $table_name = $wpdb->prefix . 'vulpes_lms_training_log';

    // Find courses expiring in 30 days
    $expiring_courses = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE expiry_date = DATE_ADD(%s, INTERVAL 30 DAY)",
        $current_date
    ));

    foreach ($expiring_courses as $course) {
        $user = get_userdata($course->employee_id);
        $manager = get_userdata(get_user_meta($course->employee_id, 'manager', true));
        $subject = 'Course Expiry Reminder';
        $message = 'Hello ' . $user->display_name . ', your course: ' . $course->course_name . ' is due to expire on ' . $course->expiry_date;
        
        vulpes_lms_send_email($user->user_email, $subject, $message);
        if ($manager) {
            vulpes_lms_send_email($manager->user_email, $subject, $message);
        }
    }

    // Find expired courses
    $expired_courses = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE expiry_date < %s",
        $current_date
    ));

    foreach ($expired_courses as $course) {
        $user = get_userdata($course->employee_id);
        $manager = get_userdata(get_user_meta($course->employee_id, 'manager', true));
        $subject = 'Course Expired';
        $message = 'Hello ' . $user->display_name . ', your course: ' . $course->course_name . ' has expired on ' . $course->expiry_date;
        
        vulpes_lms_send_email($user->user_email, $subject, $message);
        if ($manager) {
            vulpes_lms_send_email($manager->user_email, $subject, $message);
        }
    }
}
add_action('vulpes_lms_daily_event', 'vulpes_lms_check_courses_expiry');

// Schedule the daily event if not already scheduled
if (!wp_next_scheduled('vulpes_lms_daily_event')) {
    wp_schedule_event(time(), 'daily', 'vulpes_lms_daily_event');
}

// Hook into plugin deactivation to clear the scheduled event
register_deactivation_hook(__FILE__, 'vulpes_lms_deactivate');
function vulpes_lms_deactivate() {
    wp_clear_scheduled_hook('vulpes_lms_daily_event');
}
?>
