<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;
$table_name = $wpdb->prefix . 'vulpes_lms_courses';
$course_id = isset( $_GET['course_id'] ) ? intval( $_GET['course_id'] ) : 0;

if ( ! $course_id ) {
    echo '<div class="error"><p>Course not found.</p></div>';
    return;
}

$course = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $course_id ) );

if ( ! $course ) {
    echo '<div class="error"><p>Course not found.</p></div>';
    return;
}

// Handle form submission
if ( isset( $_POST['course_name'] ) && isset( $_POST['course_description'] ) && isset( $_POST['expiry_duration'] ) && isset( $_POST['training_provider'] ) && isset( $_POST['subject_group'] ) && isset( $_POST['competency_score'] ) ) {
    $course_name = sanitize_text_field( $_POST['course_name'] );
    $course_description = sanitize_textarea_field( $_POST['course_description'] );
    $expiry_duration = intval( $_POST['expiry_duration'] );
    $training_provider = sanitize_text_field( $_POST['training_provider'] );
    $subject_group = sanitize_text_field( $_POST['subject_group'] );
    $competency_score = intval( $_POST['competency_score'] );

    $wpdb->update(
        $table_name,
        array(
            'course_name' => $course_name,
            'course_description' => $course_description,
            'expiry_duration' => $expiry_duration,
            'training_provider' => $training_provider,
            'subject_group' => $subject_group,
            'competency_score' => $competency_score,
        ),
        array( 'id' => $course_id )
    );

    echo '<div class="updated"><p>Course updated successfully.</p></div>';
}

// Fetch all subject groups
$subject_groups = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}vulpes_lms_subject_groups" );

?>

<div class="wrap">
    <h1>Edit Course</h1>
    <form method="post" action="">
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="course_name">Course Name</label></th>
                <td><input type="text" id="course_name" name="course_name" class="regular-text" value="<?php echo esc_attr( $course->course_name ); ?>" required /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="course_description">Course Description</label></th>
                <td><textarea id="course_description" name="course_description" class="regular-text" required><?php echo esc_textarea( $course->course_description ); ?></textarea></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="expiry_duration">Expiry Duration (days)</label></th>
                <td><input type="number" id="expiry_duration" name="expiry_duration" class="regular-text" value="<?php echo esc_attr( $course->expiry_duration ); ?>" required /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="training_provider">Training Provider</label></th>
                <td><input type="text" id="training_provider" name="training_provider" class="regular-text" value="<?php echo esc_attr( $course->training_provider ); ?>" required /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="subject_group">Subject Group</label></th>
                <td>
                    <select id="subject_group" name="subject_group" required>
                        <option value="">Select a Subject Group</option>
                        <?php foreach ( $subject_groups as $subject_group ) : ?>
                            <option value="<?php echo esc_attr( $subject_group->subject_group_name ); ?>" <?php selected( $subject_group->subject_group_name, $course->subject_group ); ?>><?php echo esc_html( $subject_group->subject_group_name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="competency_score">Competency Score</label></th>
                <td><input type="number" id="competency_score" name="competency_score" class="regular-text" value="<?php echo esc_attr( $course->competency_score ); ?>" required /></td>
            </tr>
        </table>
        <?php submit_button( 'Update Course' ); ?>
    </form>
    <a href="<?php echo admin_url( 'admin.php?page=vulpes-lms-courses' ); ?>" class="button">Back to Courses</a>
</div>
