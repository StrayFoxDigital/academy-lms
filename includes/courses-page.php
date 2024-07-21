<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;
$table_name = $wpdb->prefix . 'academy_lms_courses';

// Handle form submission
if ( isset( $_POST['course_name'] ) && isset( $_POST['course_description'] ) && isset( $_POST['expiry_duration'] ) && isset( $_POST['training_provider'] ) ) {
    $course_name = sanitize_text_field( $_POST['course_name'] );
    $course_description = sanitize_textarea_field( $_POST['course_description'] );
    $expiry_duration = intval( $_POST['expiry_duration'] );
    $training_provider = sanitize_text_field( $_POST['training_provider'] );

    $wpdb->insert(
        $table_name,
        array(
            'course_name' => $course_name,
            'course_description' => $course_description,
            'expiry_duration' => $expiry_duration,
            'training_provider' => $training_provider,
        )
    );

    echo '<div class="updated"><p>Course added successfully.</p></div>';
}

// Handle course deletion
if ( isset( $_GET['action'] ) && $_GET['action'] == 'delete' && isset( $_GET['course_id'] ) ) {
    $course_id = intval( $_GET['course_id'] );
    $wpdb->delete( $table_name, array( 'id' => $course_id ) );
    echo '<div class="updated"><p>Course deleted successfully.</p></div>';
}

// Fetch all courses
$courses = $wpdb->get_results( "SELECT * FROM $table_name" );

// Function to count assigned users for a course
function count_assigned_users( $course_name ) {
    $users = get_users( array(
        'meta_key' => 'courses',
        'meta_compare' => 'LIKE',
        'meta_value' => $course_name,
    ) );
    return count( $users );
}

?>

<div class="wrap">
    <h1>Manage Training Courses</h1>
    <form method="post" action="">
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="course_name">Course Name</label></th>
                <td><input type="text" id="course_name" name="course_name" class="regular-text" required /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="course_description">Course Description</label></th>
                <td><textarea id="course_description" name="course_description" class="large-text" rows="3" required></textarea></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="expiry_duration">Expiry Duration (days)</label></th>
                <td><input type="number" id="expiry_duration" name="expiry_duration" class="regular-text" required /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="training_provider">Training Provider</label></th>
                <td><input type="text" id="training_provider" name="training_provider" class="regular-text" required /></td>
            </tr>
        </table>
        <?php submit_button( 'Add Course' ); ?>
    </form>

    <h2>Existing Courses</h2>
    <table class="widefat fixed" cellspacing="0">
        <thead>
            <tr>
                <th id="columnname" class="manage-column column-columnname" scope="col">Course Name</th>
                <th id="columnname" class="manage-column column-columnname" scope="col">Training Provider</th>
                <th id="columnname" class="manage-column column-columnname" scope="col">Assigned Employees</th>
                <th id="columnname" class="manage-column column-columnname" scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $courses ) ) : ?>
                <?php foreach ( $courses as $course ) : ?>
                    <tr>
                        <td><?php echo esc_html( $course->course_name ); ?></td>
                        <td><?php echo esc_html( $course->training_provider ); ?></td>
                        <td><?php echo esc_html( count_assigned_users( $course->course_name ) ); ?></td>
                        <td>
                            <a href="<?php echo admin_url( 'admin.php?page=academy-lms-edit-course&course_id=' . $course->id ); ?>" class="button">Manage</a>
                            <a href="<?php echo admin_url( 'admin.php?page=academy-lms-courses&action=delete&course_id=' . $course->id ); ?>" class="button" onclick="return confirm('Are you sure you want to delete this course?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="4">No courses found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
