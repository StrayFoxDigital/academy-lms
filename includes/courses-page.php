<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;
$table_name = $wpdb->prefix . 'vulpes_lms_courses';

// Handle form submission for adding a course
if ( isset( $_POST['course_name'] ) && isset( $_POST['course_description'] ) && isset( $_POST['expiry_duration'] ) && isset( $_POST['training_provider'] ) && isset( $_POST['learning_path'] ) && isset( $_POST['competency_score'] ) && isset( $_POST['course_url'] ) ) {
    $course_name = sanitize_text_field( $_POST['course_name'] );
    $course_description = sanitize_textarea_field( $_POST['course_description'] );
    $expiry_duration = intval( $_POST['expiry_duration'] );
    $training_provider = sanitize_text_field( $_POST['training_provider'] );
    $learning_path = sanitize_text_field( $_POST['learning_path'] );
    $competency_score = intval( $_POST['competency_score'] );
    $course_url = esc_url_raw( $_POST['course_url'] );

    $wpdb->insert(
        $table_name,
        array(
            'course_name' => $course_name,
            'course_description' => $course_description,
            'expiry_duration' => $expiry_duration,
            'training_provider' => $training_provider,
            'learning_path' => $learning_path,
            'competency_score' => $competency_score,
            'course_url' => $course_url,
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

// Fetch all learning paths
$learning_paths = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}vulpes_lms_learning_paths" );

?>

<div class="wrap">
    <h1>Manage Courses</h1>
    <h2 class="nav-tab-wrapper">
        <a href="#courses" class="nav-tab nav-tab-active">Courses</a>
        <a href="#add-new" class="nav-tab">Add New</a>
    </h2>

    <div id="courses" class="tab-content">
        <h2>Existing Courses</h2>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Course Name</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Training Provider</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Learning Path</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Competency Score</th>
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
                            <td><?php echo esc_html( $course->learning_path ); ?></td>
                            <td><?php echo esc_html( $course->competency_score ); ?></td>
                            <td><?php echo esc_html( count( get_users( array( 'meta_key' => 'course', 'meta_value' => $course->course_name ) ) ) ); ?></td>
                            <td>
                                <a href="<?php echo admin_url( 'admin.php?page=vulpes-lms-edit-course&course_id=' . $course->id ); ?>" class="button">Manage</a>
                                <a href="<?php echo admin_url( 'admin.php?page=vulpes-lms-courses&action=delete&course_id=' . $course->id ); ?>" class="button" onclick="return confirm('Are you sure you want to delete this course?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6">No courses found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div id="add-new" class="tab-content" style="display: none;">
        <form method="post" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="course_name">Course Name</label></th>
                    <td><input type="text" id="course_name" name="course_name" class="regular-text" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="course_description">Course Description</label></th>
                    <td><textarea id="course_description" name="course_description" class="regular-text" required></textarea></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="expiry_duration">Expiry Duration (days)</label></th>
                    <td><input type="number" id="expiry_duration" name="expiry_duration" class="regular-text" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="training_provider">Training Provider</label></th>
                    <td><input type="text" id="training_provider" name="training_provider" class="regular-text" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="learning_path">Learning Path</label></th>
                    <td>
                        <select id="learning_path" name="learning_path" required>
                            <option value="">Select a Learning Path</option>
                            <?php foreach ( $learning_paths as $learning_path ) : ?>
                                <option value="<?php echo esc_attr( $learning_path->learning_path_name ); ?>"><?php echo esc_html( $learning_path->learning_path_name ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="competency_score">Competency Score</label></th>
                    <td><input type="number" id="competency_score" name="competency_score" class="regular-text" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="course_url">Course URL</label></th>
                    <td><input type="url" id="course_url" name="course_url" class="regular-text" /></td>
                </tr>
            </table>
            <?php submit_button( 'Add Course' ); ?>
        </form>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    $('.nav-tab').click(function(e) {
        e.preventDefault();
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        $('.tab-content').hide();
        $($(this).attr('href')).show();
    });

    // Keep the active tab after form submission
    var hash = window.location.hash;
    if (hash) {
        $('.nav-tab[href="' + hash + '"]').click();
    }
});
</script>