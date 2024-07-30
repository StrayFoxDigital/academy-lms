<?php

// Shortcode: [vulpes_user_enrolled_courses]

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function vulpes_user_enrolled_courses_shortcode() {
    if ( ! is_user_logged_in() ) {
        return '<p>You need to be logged in to view your enrolled courses.</p>';
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $table_name = $wpdb->prefix . 'vulpes_lms_course_assignments';
    $courses_table_name = $wpdb->prefix . 'vulpes_lms_courses';
    
    $enrolled_courses = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE employee_id = %d ORDER BY date_enrolled DESC", $user_id ) );

    if ( empty( $enrolled_courses ) ) {
        return '<p>You are not enrolled in any courses.</p>';
    }

    ob_start();
    ?>
    <div class="vulpes-lms-shortcodes">
        <table>
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Date Enrolled</th>
                    <th>Status</th>
                    <th>Link to Course</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $enrolled_courses as $course ) : ?>
                    <?php
                    $course_link = $wpdb->get_var( $wpdb->prepare( "SELECT course_link FROM $courses_table_name WHERE id = %d", $course->course_id ) );
                    ?>
                    <tr>
                        <td><?php echo esc_html( $course->course_name ); ?></td>
                        <td><?php echo esc_html( date( 'd-m-Y', strtotime( $course->date_enrolled ) ) ); ?></td>
                        <td><?php echo esc_html( ucfirst( $course->status ) ); ?></td>
                        <td><?php echo $course_link ? '<a href="' . esc_url( $course_link ) . '" target="_blank">Link</a>' : 'N/A'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode( 'vulpes_user_enrolled_courses', 'vulpes_user_enrolled_courses_shortcode' );
?>