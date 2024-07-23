<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;
$table_name = $wpdb->prefix . 'vulpes_lms_courses';

if ( $_SERVER['REQUEST_METHOD'] == 'POST' && isset( $_POST['course_name'] ) ) {
    $course_name = sanitize_text_field( $_POST['course_name'] );
    $course_description = sanitize_textarea_field( $_POST['course_description'] );
    $expiry_duration = intval( $_POST['expiry_duration'] );
    $training_provider = sanitize_text_field( $_POST['training_provider'] );

    $wpdb->insert( $table_name, array(
        'course_name' => $course_name,
        'course_description' => $course_description,
        'expiry_duration' => $expiry_duration,
        'training_provider' => $training_provider,
    ) );
}

$courses = $wpdb->get_results( "SELECT * FROM $table_name" );

?>
<div class="wrap">
    <h1><?php esc_html_e( 'Training Courses', 'vulpes-lms' ); ?></h1>
    <form method="post">
        <table class="form-table">
            <tr>
                <th><label for="course_name"><?php esc_html_e( 'Course Name', 'vulpes-lms' ); ?></label></th>
                <td><input type="text" name="course_name" id="course_name" class="regular-text" required></td>
            </tr>
            <tr>
                <th><label for="course_description"><?php esc_html_e( 'Course Description', 'vulpes-lms' ); ?></label></th>
                <td><textarea name="course_description" id="course_description" class="large-text" rows="5" required></textarea></td>
            </tr>
            <tr>
                <th><label for="expiry_duration"><?php esc_html_e( 'Expiry Duration (days)', 'vulpes-lms' ); ?></label></th>
                <td><input type="number" name="expiry_duration" id="expiry_duration" class="small-text" required></td>
            </tr>
            <tr>
                <th><label for="training_provider"><?php esc_html_e( 'Training Provider', 'vulpes-lms' ); ?></label></th>
                <td><input type="text" name="training_provider" id="training_provider" class="regular-text" required></td>
            </tr>
        </table>
        <?php submit_button( __( 'Add Course', 'vulpes-lms' ) ); ?>
    </form>
    <h2><?php esc_html_e( 'Existing Courses', 'vulpes-lms' ); ?></h2>
    <table class="widefat fixed" cellspacing="0">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Course Name', 'vulpes-lms' ); ?></th>
                <th><?php esc_html_e( 'Course Description', 'vulpes-lms' ); ?></th>
                <th><?php esc_html_e( 'Expiry Duration', 'vulpes-lms' ); ?></th>
                <th><?php esc_html_e( 'Training Provider', 'vulpes-lms' ); ?></th>
                <th><?php esc_html_e( 'Assigned Employees', 'vulpes-lms' ); ?></th>
                <th><?php esc_html_e( 'Actions', 'vulpes-lms' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $courses as $course ) : ?>
                <tr>
                    <td><?php echo esc_html( $course->course_name ); ?></td>
                    <td><?php echo esc_html( $course->course_description ); ?></td>
                    <td><?php echo esc_html( $course->expiry_duration ); ?></td>
                    <td><?php echo esc_html( $course->training_provider ); ?></td>
                    <td>
                        <?php
                        $assigned_employees = $wpdb->get_var( $wpdb->prepare(
                            "SELECT COUNT(*) FROM {$wpdb->prefix}vulpes_lms_training_log WHERE course_name = %s",
                            $course->course_name
                        ) );
                        echo esc_html( $assigned_employees );
                        ?>
                    </td>
                    <td>
                        <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'vulpes-lms-edit-course', 'course_id' => $course->id ), admin_url( 'admin.php' ) ) ); ?>" class="button"><?php esc_html_e( 'Manage', 'vulpes-lms' ); ?></a>
                        <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'delete', 'course_id' => $course->id ), admin_url( 'admin.php?page=vulpes-lms-courses' ) ), 'delete_course_' . $course->id ) ); ?>" class="button"><?php esc_html_e( 'Delete', 'vulpes-lms' ); ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
