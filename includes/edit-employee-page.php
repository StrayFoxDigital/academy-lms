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

// Handle form submission
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

// Fetch all users for the manager dropdown, excluding those with the 'subscriber' (Employee) role
$users = get_users( array(
    'fields' => array( 'ID', 'display_name', 'roles' )
) );

$managers = array_filter($users, function($user) {
    return isset($user->roles) && is_array($user->roles) && !in_array('subscriber', $user->roles);
});

?>

<div class="wrap">
    <h1>Edit Employee</h1>
    <form method="post" action="">
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
</div>
