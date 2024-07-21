<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;

// Handle form submission
if ( isset( $_POST['first_name'] ) && isset( $_POST['last_name'] ) && isset( $_POST['email'] ) && isset( $_POST['position'] ) && isset( $_POST['manager'] ) && isset( $_POST['location'] ) && isset( $_POST['role'] ) && isset( $_POST['group'] ) ) {
    $first_name = sanitize_text_field( $_POST['first_name'] );
    $last_name = sanitize_text_field( $_POST['last_name'] );
    $email = sanitize_email( $_POST['email'] );
    $position = sanitize_text_field( $_POST['position'] );
    $manager = intval( $_POST['manager'] );
    $location = sanitize_text_field( $_POST['location'] );
    $role = sanitize_text_field( $_POST['role'] );
    $group = sanitize_text_field( $_POST['group'] );

    // Create a new user
    $user_id = wp_insert_user( array(
        'user_login' => $email,
        'user_email' => $email,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'role' => $role,
    ) );

    // Check for errors
    if ( is_wp_error( $user_id ) ) {
        echo '<div class="error"><p>Error adding employee: ' . $user_id->get_error_message() . '</p></div>';
    } else {
        // Update user meta
        update_user_meta( $user_id, 'position', $position );
        update_user_meta( $user_id, 'manager', $manager );
        update_user_meta( $user_id, 'location', $location );
        update_user_meta( $user_id, 'group', $group );
        echo '<div class="updated"><p>Employee added successfully.</p></div>';
    }
}

// Fetch all users for display in the table
$users = get_users();
$groups = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}academy_lms_groups" );

?>

<div class="wrap">
    <h1>Manage Employees</h1>
    <form method="post" action="">
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="first_name">First Name</label></th>
                <td><input type="text" id="first_name" name="first_name" class="regular-text" required /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="last_name">Last Name</label></th>
                <td><input type="text" id="last_name" name="last_name" class="regular-text" required /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="email">Email</label></th>
                <td><input type="email" id="email" name="email" class="regular-text" required /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="position">Position</label></th>
                <td><input type="text" id="position" name="position" class="regular-text" required /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="manager">Manager</label></th>
                <td>
                    <select id="manager" name="manager" required>
                        <option value="">Select a Manager</option>
                        <?php foreach ( $users as $user ) : ?>
                            <?php if ( ! in_array( 'subscriber', $user->roles ) ) : ?>
                                <option value="<?php echo esc_attr( $user->ID ); ?>"><?php echo esc_html( $user->display_name ); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="location">Location</label></th>
                <td><input type="text" id="location" name="location" class="regular-text" required /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="role">Role</label></th>
                <td>
                    <select id="role" name="role" required>
                        <option value="administrator">Administrator</option>
                        <option value="editor">Superuser</option>
                        <option value="author">Manager</option>
                        <option value="subscriber">Employee</option>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="group">Group</label></th>
                <td>
                    <select id="group" name="group" required>
                        <option value="">Select a Group</option>
                        <?php foreach ( $groups as $group ) : ?>
                            <option value="<?php echo esc_attr( $group->group_name ); ?>"><?php echo esc_html( $group->group_name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>
        <?php submit_button( 'Add Employee' ); ?>
    </form>

    <h2>Existing Employees</h2>
    <table class="widefat fixed" cellspacing="0">
        <thead>
            <tr>
                <th id="columnname" class="manage-column column-columnname" scope="col">Display Name</th>
                <th id="columnname" class="manage-column column-columnname" scope="col">Email</th>
                <th id="columnname" class="manage-column column-columnname" scope="col">Position</th>
                <th id="columnname" class="manage-column column-columnname" scope="col">Manager</th>
                <th id="columnname" class="manage-column column-columnname" scope="col">Group</th>
                <th id="columnname" class="manage-column column-columnname" scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $users ) ) : ?>
                <?php foreach ( $users as $user ) : ?>
                    <tr>
                        <td><?php echo esc_html( $user->display_name ); ?></td>
                        <td><?php echo esc_html( $user->user_email ); ?></td>
                        <td><?php echo esc_html( get_user_meta( $user->ID, 'position', true ) ); ?></td>
                        <td>
                            <?php 
                            $manager_id = get_user_meta( $user->ID, 'manager', true );
                            $manager = get_userdata( $manager_id );
                            echo esc_html( $manager ? $manager->display_name : 'N/A' );
                            ?>
                        </td>
                        <td><?php echo esc_html( get_user_meta( $user->ID, 'group', true ) ); ?></td>
                        <td>
                            <a href="<?php echo admin_url( 'admin.php?page=academy-lms-edit-employee&user_id=' . $user->ID ); ?>" class="button">Manage</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="6">No employees found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
