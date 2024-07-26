<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Register the cron job event on plugin activation
register_activation_hook( __FILE__, 'vulpes_lms_activate' );
function vulpes_lms_activate() {
    if ( ! wp_next_scheduled( 'vulpes_lms_course_expiry_notification' ) ) {
        wp_schedule_event( time(), 'daily', 'vulpes_lms_course_expiry_notification' );
    }
}

// Clear the cron job event on plugin deactivation
register_deactivation_hook( __FILE__, 'vulpes_lms_deactivate' );
function vulpes_lms_deactivate() {
    wp_clear_scheduled_hook( 'vulpes_lms_course_expiry_notification' );
}

// Add the cron job action
add_action( 'vulpes_lms_course_expiry_notification', 'vulpes_lms_check_course_expiry' );

function vulpes_lms_check_course_expiry() {
    global $wpdb;

    // Fetch courses expiring in the next 30 days
    $expiring_courses = $wpdb->get_results( "
        SELECT * FROM {$wpdb->prefix}vulpes_lms_training_log
        WHERE expiry_date = CURDATE() + INTERVAL 30 DAY
    " );

    foreach ( $expiring_courses as $course ) {
        $employee = get_userdata( $course->employee_id );
        $manager = get_userdata( get_user_meta( $course->employee_id, 'manager', true ) );

        // Email to employee
        vulpes_lms_send_email( $employee->user_email, 'Course Expiry Notification', 'course_expiry_soon', $course, $employee );

        // Email to manager
        if ( $manager ) {
            vulpes_lms_send_email( $manager->user_email, 'Course Expiry Notification', 'course_expiry_soon', $course, $employee );
        }
    }

    // Fetch courses that have expired today
    $expired_courses = $wpdb->get_results( "
        SELECT * FROM {$wpdb->prefix}vulpes_lms_training_log
        WHERE expiry_date = CURDATE()
    " );

    foreach ( $expired_courses as $course ) {
        $employee = get_userdata( $course->employee_id );
        $manager = get_userdata( get_user_meta( $course->employee_id, 'manager', true ) );

        // Email to employee
        vulpes_lms_send_email( $employee->user_email, 'Course Expired Notification', 'course_expired', $course, $employee );

        // Email to manager
        if ( $manager ) {
            vulpes_lms_send_email( $manager->user_email, 'Course Expired Notification', 'course_expired', $course, $employee );
        }
    }
}

function vulpes_lms_send_email( $to, $subject, $template, $course, $employee ) {
    $options = get_option( 'vulpes_lms_notifications_settings' );

    if ( ! isset( $options['enabled'] ) || ! $options['enabled'] ) {
        return;
    }

    $messages = isset( $options['messages'] ) ? $options['messages'] : array();
    $message_template = isset( $messages[$template] ) ? $messages[$template] : '';

    $replacements = array(
        '$user' => $employee->display_name,
        '$course_name' => $course->course_name,
        '$site_title' => get_bloginfo( 'name' ),
    );

    $message = str_replace( array_keys( $replacements ), $replacements, $message_template );

    if ( isset( $options['html'] ) && $options['html'] ) {
        $logo_url = isset( $options['logo'] ) ? $options['logo'] : '';

        $email_heading = '';
        switch ( $template ) {
            case 'employee_enrollment':
                $email_heading = 'Employee Enrollment';
                break;
            case 'course_enrollment':
                $email_heading = 'Course Enrollment';
                break;
            case 'course_expiry_soon':
                $email_heading = 'Course Expiry Notification';
                break;
            case 'course_expired':
                $email_heading = 'Course Expired Notification';
                break;
        }

        $html_message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Email Notification</title>
        </head>
        <body style='font-family: Arial, sans-serif; background-color: #2A2D3C; margin: 0; padding: 75px;'>
            <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='max-width: 600px; margin: 0 auto; padding: 20px; background-color: #ffffff;'>
                <tr>
                    <td style='text-align: center; padding-bottom: 20px;'>
                        <img src='$logo_url' alt='Logo' style='max-width: 150px; height: auto;'>
                    </td>
                </tr>
                <tr>
                    <td style='padding: 20px; background-color: #4F5D75; color: #ffffff; text-align: center;'>
                        <h1 style='margin: 0; font-size: 24px;'>$email_heading</h1>
                    </td>
                </tr>
                <tr>
                    <td style='padding: 20px; color: #333333;'>
                        <p style='margin: 0; font-size: 16px; line-height: 1.5; font-weight:bold;'>
                            Hello, {$employee->display_name}
                        </p></br>
                        <p style='margin: 0; font-size: 16px; line-height: 1.5;'>
                            $message
                        </p></br>
                        <p style='margin: 0; font-size: 16px; line-height: 1.5; font-weight:bold;'>
                            Thank you,</br> " . get_bloginfo( 'name' ) . "
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style='padding: 20px; text-align: center; color: #888888; font-size: 12px;'>
                        <p style='margin: 0;'>&copy; 2024 " . get_bloginfo( 'name' ) . " All rights reserved.</br>
                        Powered by: Vulpes LMS
                        </p>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";

        add_filter( 'wp_mail_content_type', function() { return 'text/html'; } );
        wp_mail( $to, $subject, $html_message );
        remove_filter( 'wp_mail_content_type', function() { return 'text/html'; } );
    } else {
        wp_mail( $to, $subject, $message );
    }
}

// Add action hooks for user enrollment and course enrollment
add_action( 'user_register', 'vulpes_lms_user_registered' );
function vulpes_lms_user_registered( $user_id ) {
    $user = get_userdata( $user_id );
    vulpes_lms_send_email( $user->user_email, 'Welcome to Vulpes LMS', 'employee_enrollment', null, $user );
}

add_action( 'vulpes_lms_course_enrolled', 'vulpes_lms_course_enrolled_notification', 10, 2 );
function vulpes_lms_course_enrolled_notification( $user_id, $course_name ) {
    $user = get_userdata( $user_id );
    $course = (object) array( 'course_name' => $course_name );
    vulpes_lms_send_email( $user->user_email, 'Course Enrollment', 'course_enrollment', $course, $user );
}
