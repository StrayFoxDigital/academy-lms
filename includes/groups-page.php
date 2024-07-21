<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;
$table_name = $wpdb->prefix . 'academy_lms_groups';

// Handle form submission for adding a group
if ( isset( $_POST['group_name'] ) && isset( $_POST['manager'] ) ) {
    $group_name = sanitize_text_field( $_POST['group_name'] );
    $manager = intval( $_POST['manager'] );

    $wpdb->insert(
        $table_name,
        array(
            'group_name' => $group_name,
            'manager' => $manager,
        )
    );

    echo '<div class="updated"><p>Group added successfully.</p></div>';
}

// Handle group deletion
if ( isset( $_GET['action'] ) && $_GET['action'] == 'delete' && isset( $_GET['group_id'] ) ) {
    $group_id = intval( $_GET['group_id'] );
    $group = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $group_id ) );

    if ( $group ) {
        // Remove group data from users
        $users = get_users( array(
            'meta_key' => 'group',
            'meta_value' => $group->group_name,
            'fields' => 'ID'
        ) );

        foreach ( $users as $user_id ) {
            delete_user_meta( $user_id, 'group' );
        }

        // Delete the group
        $wpdb->delete( $table_name, array( 'id' => $group_id ) );
        echo '<div class="updated"><p>Group deleted successfully.</p></div>';
    } else {
        echo '<div class="error"><p>Group not found.</p></div>';
    }
}

// Fetch all groups
$groups = $wpdb->get_results( "SELECT * FROM $table_name" );

// Fetch users with roles Administrator, Superuser, and Manager
$allowed_roles = array( 'administrator', 'editor', 'author' ); // Editor is now Superuser, Author is now Manager
$users = get_users( array(
    'role__in' => $allowed_roles,
) );
?>

<div class="wrap">
    <h1>Manage Groups</h1>
    <form method="post" action="">
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="group_name">Group Name</label></th>
                <td><input type="text" id="group_name" name="group_name" class="regular-text" required /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="manager">Manager</label></th>
                <td>
                    <select id="manager" name="manager" required>
                        <option value="">Select a Manager</option>
                        <?php foreach ( $users as $user ) : ?>
                            <option value="<?php echo esc_attr( $user->ID ); ?>"><?php echo esc_html( $user->display_name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>
        <?php submit_button( 'Add Group' ); ?>
    </form>

    <h2>Existing Groups</h2>
    <table class="widefat fixed" cellspacing="0">
        <thead>
            <tr>
                <th id="columnname" class="manage-column column-columnname" scope="col">Group Name</th>
                <th id="columnname" class="manage-column column-columnname" scope="col">Manager</th>
                <th id="columnname" class="manage-column column-columnname" scope="col">Assigned Employees</th>
                <th id="columnname" class="manage-column column-columnname" scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $groups ) ) : ?>
                <?php foreach ( $groups as $group ) : ?>
                    <tr>
                        <td><?php echo esc_html( $group->group_name ); ?></td>
                        <td>
                            <?php 
                            $manager = get_userdata( $group->manager );
                            echo esc_html( $manager ? $manager->display_name : 'Manager not found' );
                            ?>
                        </td>
                        <td><?php echo esc_html( count( get_users( array( 'meta_key' => 'group', 'meta_value' => $group->group_name ) ) ) ); ?></td>
                        <td>
                            <a href="<?php echo admin_url( 'admin.php?page=academy-lms-edit-group&group_id=' . $group->id ); ?>" class="button">Manage</a>
                            <a href="<?php echo admin_url( 'admin.php?page=academy-lms-groups&action=delete&group_id=' . $group->id ); ?>" class="button" onclick="return confirm('Are you sure you want to delete this group?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="4">No groups found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
