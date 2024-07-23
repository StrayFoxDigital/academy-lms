<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;
$table_name = $wpdb->prefix . 'vulpes_lms_courses';
$course_id = isset( $_GET['course_id'] ) ? intval( $_GET['course_id'] ) : 0;

if ( ! $course_id ) {
    wp_die( __( 'Course not found', 'vulpes-lms' ) );
}

$course = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $course_id ) );

if ( ! $course ) {
    wp_die( __( 'Course not found', 'vulpes-lms' ) );
}

if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
    $course_name = sanitize_text_field( $_POST['course_name'] );
    $course_description = sanitize_textarea_field( $_POST['course_description'] );
    $expiry_duration = intval( $_POST['expiry_duration'] );
    $training_provider = sanitize_text_field( $_POST['training_provider'] );
    $assigned_users = isset( $_POST['assigned_users'] ) ? array_map( 'sanitize_text_field', $_POST['assigned_users'] ) : array();

    $wpdb->update( $table_name, array(
        'course_name' => $course_name,
        'course_description' => $course_description,
        'expiry_duration' => $expiry_duration,
        'training_provider' => $training_provider,
    ), array( 'id' => $course_id ) );

    foreach ( $assigned_users as $user_id ) {
        $wpdb->replace( $wpdb->prefix . 'vulpes_lms_training_log', array(
            'employee_id' => $user_id,
            'employee_name' => get_userdata( $user_id )->display_name,
            'course_name' => $course_name,
            'date_completed' => current_time( 'mysql' ),
            'expiry_date' => date( 'Y-m-d', strtotime( '+' . $expiry_duration . ' days' ) ),
        ) );
    }

    $wpdb->query( $wpdb->prepare(
        "DELETE FROM {$wpdb->prefix}vulpes_lms_training_log WHERE course_name = %s AND employee_id NOT IN (" . implode( ',', array_map( 'intval', $assigned_users ) ) . ")",
        $course_name
    ) );

    wp_redirect( admin_url( 'admin.php?page=vulpes-lms-courses' ) );
    exit;
}

$assigned_users = $wpdb->get_col( $wpdb->prepare(
    "SELECT employee_id FROM {$wpdb->prefix}vulpes_lms_training_log WHERE course_name = %s",
    $course->course_name
) );

?>
<div class="wrap">
    <h1><?php esc_html_e( 'Edit Course', 'vulpes-lms' ); ?></h1>
    <form method="post">
        <table class="form-table">
            <tr>
                <th><label for="course_name"><?php esc_html_e( 'Course Name', 'vulpes-lms' ); ?></label></th>
                <td><input type="text" name="course_name" id="course_name" class="regular-text" value="<?php echo esc_attr( $course->course_name ); ?>" required></td>
            </tr>
            <tr>
                <th><label for="course_description"><?php esc_html_e( 'Course Description', 'vulpes-lms' ); ?></label></th>
                <td><textarea name="course_description" id="course_description" class="large-text" rows="5" required><?php echo esc_textarea( $course->course_description ); ?></textarea></td>
            </tr>
            <tr>
                <th><label for="expiry_duration"><?php esc_html_e( 'Expiry Duration (days)', 'vulpes-lms' ); ?></label></th>
                <td><input type="number" name="expiry_duration" id="expiry_duration" class="small-text" value="<?php echo esc_attr( $course->expiry_duration ); ?>" required></td>
            </tr>
            <tr>
                <th><label for="training_provider"><?php esc_html_e( 'Training Provider', 'vulpes-lms' ); ?></label></th>
                <td><input type="text" name="training_provider" id="training_provider" class="regular-text" value="<?php echo esc_attr( $course->training_provider ); ?>" required></td>
            </tr>
            <tr>
                <th><label for="assigned_users"><?php esc_html_e( 'Assigned Users', 'vulpes-lms' ); ?></label></th>
                <td>
                    <select name="assigned_users[]" id="assigned_users" class="regular-text" multiple required>
                        <?php
                        $users = get_users();
                        foreach ( $users as $user ) {
                            echo '<option value="' . esc_attr( $user->ID ) . '" ' . ( in_array( $user->ID, $assigned_users ) ? 'selected' : '' ) . '>' . esc_html( $user->display_name ) . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
        </table>
        <?php submit_button( __( 'Update Course', 'vulpes-lms' ) ); ?>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=vulpes-lms-courses' ) ); ?>" class="button"><?php esc_html_e( 'Back', 'vulpes-lms' ); ?></a>
    </form>
</div>
