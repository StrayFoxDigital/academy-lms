<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;
$table_name = $wpdb->prefix . 'academy_lms_courses';
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
if ( isset( $_POST['course_name'] ) && isset( $_POST['course_description'] ) && isset( $_POST['expiry_duration'] ) && isset( $_POST['training_provider'] ) ) {
    $course_name = sanitize_text_field( $_POST['course_name'] );
    $course_description = sanitize_textarea_field( $_POST['course_description'] );
    $expiry_duration = intval( $_POST['expiry_duration'] );
    $training_provider = sanitize_text_field( $_POST['training_provider'] );

    $wpdb->update(
        $table_name,
        array(
            'course_name' => $course_name,
            'course_description' => $course_description,
            'expiry_duration' => $expiry_duration,
            'training_provider' => $training_provider,
        ),
        array( 'id' => $course_id )
    );

    // Update user course assignments
    $assigned_users = isset( $_POST['assigned_users'] ) ? array_map( 'intval', $_POST['assigned_users'] ) : array();
    $all_users = get_users( array( 'fields' => 'ID' ) );

    foreach ( $all_users as $user_id ) {
        $user_courses = get_user_meta( $user_id, 'courses', true ) ? get_user_meta( $user_id, 'courses', true ) : array();

        if ( in_array( $user_id, $assigned_users ) ) {
            if ( !in_array( $course_name, $user_courses ) ) {
                $user_courses[] = $course_name;
                update_user_meta( $user_id, 'courses', $user_courses );
            }
        } else {
            if ( in_array( $course_name, $user_courses ) ) {
                $user_courses = array_diff( $user_courses, array( $course_name ) );
                update_user_meta( $user_id, 'courses', $user_courses );
            }
        }
    }

    echo '<div class="updated"><p>Course updated successfully.</p></div>';
}

// Fetch all users
$all_users = get_users();

// Fetch users assigned to the course
$assigned_users = array();
foreach ( $all_users as $user ) {
    $user_courses = get_user_meta( $user->ID, 'courses', true );
    if ( is_array( $user_courses ) && in_array( $course->course_name, $user_courses ) ) {
        $assigned_users[] = $user->ID;
    }
}

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
                <td><textarea id="course_description" name="course_description" class="large-text" rows="3" required><?php echo esc_textarea( $course->course_description ); ?></textarea></td>
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
                <th scope="row"><label for="assigned_users">Assigned Users</label></th>
                <td>
                    <div style="display: flex;">
                        <select id="available_users" multiple style="height: 200px; width: 45%; margin-right: 10px;">
                            <?php foreach ( $all_users as $user ) : ?>
                                <?php if ( ! in_array( $user->ID, $assigned_users ) ) : ?>
                                    <option value="<?php echo esc_attr( $user->ID ); ?>"><?php echo esc_html( $user->display_name ); ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <div style="display: flex; flex-direction: column; justify-content: center;">
                            <button type="button" id="assign_user" class="button">&gt;&gt;</button>
                            <button type="button" id="unassign_user" class="button">&lt;&lt;</button>
                        </div>
                        <select id="assigned_users" name="assigned_users[]" multiple style="height: 200px; width: 45%;">
                            <?php foreach ( $all_users as $user ) : ?>
                                <?php if ( in_array( $user->ID, $assigned_users ) ) : ?>
                                    <option value="<?php echo esc_attr( $user->ID ); ?>"><?php echo esc_html( $user->display_name ); ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </td>
            </tr>
        </table>
        <?php submit_button( 'Update Course' ); ?>
    </form>
    <a href="<?php echo admin_url( 'admin.php?page=academy-lms-courses' ); ?>" class="button">Back to Courses</a>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    $('#assign_user').click(function() {
        $('#available_users option:selected').appendTo('#assigned_users');
    });

    $('#unassign_user').click(function() {
        $('#assigned_users option:selected').appendTo('#available_users');
    });

    // Ensure all assigned users are included in the form submission
    $('form').submit(function() {
        $('#assigned_users option').prop('selected', true);
    });
});
</script>
