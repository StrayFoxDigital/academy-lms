<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;
$user_id = isset( $_GET['user_id'] ) ? intval( $_GET['user_id'] ) : 0;

if ( ! $user_id ) {
    echo '<div class="error"><p>Employee not found.</p></div>';
    return;
}

$user = get_userdata( $user_id );

if ( ! $user ) {
    echo '<div class="error"><p>Employee not found.</p></div>';
    return;
}

// Handle form submission for updating employee details
if ( isset( $_POST['first_name'] ) && isset( $_POST['last_name'] ) && isset( $_POST['position'] ) && isset( $_POST['manager'] ) && isset( $_POST['location'] ) && isset( $_POST['role'] ) ) {
    $first_name = sanitize_text_field( $_POST['first_name'] );
    $last_name = sanitize_text_field( $_POST['last_name'] );
    $position = sanitize_text_field( $_POST['position'] );
    $manager = intval( $_POST['manager'] );
    $location = sanitize_text_field( $_POST['location'] );
    $role = sanitize_text_field( $_POST['role'] );

    // Update user data
    wp_update_user( array(
        'ID' => $user_id,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'role' => $role,
    ) );

    // Update user meta
    update_user_meta( $user_id, 'position', $position );
    update_user_meta( $user_id, 'manager', $manager );
    update_user_meta( $user_id, 'location', $location );

    echo '<div class="updated"><p>Employee updated successfully.</p></div>';

    // Refresh user data
    $user = get_userdata( $user_id );
}

// Handle form submission for adding a new training record
if ( isset( $_POST['course_id'] ) && isset( $_POST['date_completed'] ) ) {
    $course_id = intval( $_POST['course_id'] );
    $date_completed = sanitize_text_field( $_POST['date_completed'] );

    // Handle file upload
    if ( ! function_exists( 'wp_handle_upload' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
    }
    $uploaded_file = $_FILES['training_document'];
    $upload_overrides = array( 'test_form' => false );
    $movefile = wp_handle_upload( $uploaded_file, $upload_overrides );

    if ( $movefile && ! isset( $movefile['error'] ) ) {
        $file_url = $movefile['url'];
    } else {
        $file_url = '';
    }

    // Get course name
    $course = $wpdb->get_row( $wpdb->prepare( "SELECT course_name, expiry_duration FROM {$wpdb->prefix}academy_lms_courses WHERE id = %d", $course_id ) );

    if ( $course ) {
        $expiry_date = date( 'Y-m-d', strtotime( $date_completed . ' + ' . $course->expiry_duration . ' days' ) );

        $wpdb->insert(
            $wpdb->prefix . 'academy_lms_training_log',
            array(
                'employee_id' => $user_id,
                'employee_name' => $user->first_name . ' ' . $user->last_name,
                'course_name' => $course->course_name,
                'date_completed' => date( 'Y-m-d', strtotime( $date_completed ) ),
                'expiry_date' => $expiry_date,
                'uploads' => $file_url,
            )
        );

        echo '<div class="updated"><p>Training record added successfully.</p></div>';
    }
}

// Fetch all users for the manager dropdown
$allowed_roles = array( 'administrator', 'editor', 'author' ); // Editor is now Superuser, Author is now Manager
$managers = get_users( array(
    'role__in' => $allowed_roles,
) );

// Fetch all courses for the course dropdown
$courses = $wpdb->get_results( "SELECT id, course_name FROM {$wpdb->prefix}academy_lms_courses" );

// Fetch employee's training records
$training_logs = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}academy_lms_training_log WHERE employee_id = %d ORDER BY date_completed DESC", $user_id ) );

?>

<div class="wrap">
    <h1>Edit Employee</h1>
    <form method="post" action="" enctype="multipart/form-data">
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="first_name">First Name</label></th>
                <td><input type="text" id="first_name" name="first_name" class="regular-text" value="<?php echo esc_attr( $user->first_name ); ?>" required /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="last_name">Last Name</label></th>
                <td><input type="text" id="last_name" name="last_name" class="regular-text" value="<?php echo esc_attr( $user->last_name ); ?>" required /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="position">Position</label></th>
                <td><input type="text" id="position" name="position" class="regular-text" value="<?php echo esc_attr( get_user_meta( $user_id, 'position', true ) ); ?>" required /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="manager">Manager</label></th>
                <td>
                    <select id="manager" name="manager" required>
                        <option value="">Select a Manager</option>
                        <?php foreach ( $managers as $manager_user ) : ?>
                            <option value="<?php echo esc_attr( $manager_user->ID ); ?>" <?php selected( $manager_user->ID, get_user_meta( $user_id, 'manager', true ) ); ?>>
                                <?php echo esc_html( $manager_user->display_name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="location">Location</label></th>
                <td><input type="text" id="location" name="location" class="regular-text" value="<?php echo esc_attr( get_user_meta( $user_id, 'location', true ) ); ?>" required /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="role">Role</label></th>
                <td>
                    <select id="role" name="role" required>
                        <option value="administrator" <?php selected( 'administrator', $user->roles[0] ); ?>>Administrator</option>
                        <option value="editor" <?php selected( 'editor', $user->roles[0] ); ?>>Superuser</option>
                        <option value="author" <?php selected( 'author', $user->roles[0] ); ?>>Manager</option>
                        <option value="subscriber" <?php selected( 'subscriber', $user->roles[0] ); ?>>Employee</option>
                    </select>
                </td>
            </tr>
        </table>
        <?php submit_button( 'Update Employee' ); ?>
    </form>
    <a href="<?php echo admin_url( 'admin.php?page=academy-lms-employees' ); ?>" class="button">Back to Employees</a>

    <h2>Add Training Record</h2>
    <form method="post" action="" enctype="multipart/form-data">
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="course_id">Course</label></th>
                <td>
                    <select id="course_id" name="course_id" required>
                        <option value="">Select a Course</option>
                        <?php foreach ( $courses as $course ) : ?>
                            <option value="<?php echo esc_attr( $course->id ); ?>"><?php echo esc_html( $course->course_name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="date_completed">Date Completed</label></th>
                <td><input type="date" id="date_completed" name="date_completed" required /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="training_document">Training Document (optional)</label></th>
                <td><input type="file" id="training_document" name="training_document" /></td>
            </tr>
        </table>
        <?php submit_button( 'Add Training Record' ); ?>
    </form>

    <h2>Training Records</h2>
    <table class="widefat fixed" cellspacing="0">
        <thead>
            <tr>
                <th id="columnname" class="manage-column column-columnname" scope="col">Course Name</th>
                <th id="columnname" class="manage-column column-columnname" scope="col">Date Completed</th>
                <th id="columnname" class="manage-column column-columnname" scope="col">Expiry Date</th>
                <th id="columnname" class="manage-column column-columnname" scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $training_logs ) ) : ?>
                <?php foreach ( $training_logs as $log ) : ?>
                    <tr>
                        <td><?php echo esc_html( $log->course_name ); ?></td>
                        <td><?php echo esc_html( date( 'd-m-Y', strtotime( $log->date_completed ) ) ); ?></td>
                        <td><?php echo esc_html( date( 'd-m-Y', strtotime( $log->expiry_date ) ) ); ?></td>
                        <td>
                            <?php if ( $log->uploads ) : ?>
                                <a href="<?php echo esc_url( $log->uploads ); ?>" class="button" target="_blank">View Files</a>
                            <?php else : ?>
                                <a href="#" class="button disabled" aria-disabled="true">View Files</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="4">No training records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
