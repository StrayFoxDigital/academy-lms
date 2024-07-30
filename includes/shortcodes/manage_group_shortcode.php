<?php

// Shortcode: [vulpes_manage_group]

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function vulpes_manage_group_shortcode() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'vulpes_lms_groups';
    $group_id = isset( $_GET['group_id'] ) ? intval( $_GET['group_id'] ) : 0;

    if ( ! $group_id ) {
        return '<div class="error"><p>Group not found.</p></div>';
    }

    $group = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $group_id ) );

    if ( ! $group ) {
        return '<div class="error"><p>Group not found.</p></div>';
    }

    // Handle form submission
    if ( isset( $_POST['group_name'] ) && isset( $_POST['manager'] ) ) {
        $group_name = sanitize_text_field( $_POST['group_name'] );
        $manager = intval( $_POST['manager'] );

        $wpdb->update(
            $table_name,
            array(
                'group_name' => $group_name,
                'manager' => $manager,
            ),
            array( 'id' => $group_id )
        );

        // Update user group assignments
        $assigned_users = isset( $_POST['assigned_users'] ) ? array_map( 'intval', $_POST['assigned_users'] ) : array();
        $all_users = get_users( array( 'fields' => 'ID' ) );

        foreach ( $all_users as $user_id ) {
            if ( in_array( $user_id, $assigned_users ) ) {
                update_user_meta( $user_id, 'group', $group_name );
            } else {
                $user_group = get_user_meta( $user_id, 'group', true );
                if ( $user_group == $group_name ) {
                    delete_user_meta( $user_id, 'group' );
                }
            }
        }

        echo '<div class="updated"><p>Group updated successfully.</p></div>';

        // Refresh group data
        $group = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $group_id ) );
    }

    // Fetch all users except Employees for manager dropdown
    $allowed_roles = array( 'administrator', 'editor', 'author' );
    $managers = get_users( array(
        'role__in' => $allowed_roles,
    ) );

    // Fetch all users
    $all_users = get_users();

    // Fetch users assigned to the group
    $assigned_users = get_users( array(
        'meta_key' => 'group',
        'meta_value' => $group->group_name,
        'fields' => 'ID'
    ) );

    // Fetch users already assigned to other groups
    $users_assigned_to_other_groups = array();
    foreach ( $all_users as $user ) {
        $user_group = get_user_meta( $user->ID, 'group', true );
        if ( $user_group && $user_group !== $group->group_name ) {
            $users_assigned_to_other_groups[] = $user->ID;
        }
    }

    ob_start();
    ?>
    <div class="vulpes-lms-shortcodes">
        <form method="post" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="group_name">Group Name</label></th>
                    <td><input type="text" id="group_name" name="group_name" class="regular-text" value="<?php echo esc_attr( $group->group_name ); ?>" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="manager">Manager</label></th>
                    <td>
                        <select id="manager" name="manager" required>
                            <option value="">Select a Manager</option>
                            <?php foreach ( $managers as $user ) : ?>
                                <option value="<?php echo esc_attr( $user->ID ); ?>" <?php selected( $user->ID, $group->manager ); ?>><?php echo esc_html( $user->display_name ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="assigned_users">Assigned Users</label></th>
                    <td>
                        <div style="display: flex;">
                            <select id="available_users" multiple style="height: 200px; width: 45%; margin-right: 10px;">
                                <?php foreach ( $all_users as $user ) : ?>
                                    <?php if ( ! in_array( $user->ID, $assigned_users ) && ! in_array( $user->ID, $users_assigned_to_other_groups ) ) : ?>
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
            <?php submit_button( 'Update Group' ); ?>
        </form>
        <a href="<?php echo site_url('/groups/'); ?>" class="button">Back to Groups</a>
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
    <?php
    return ob_get_clean();
}

add_shortcode( 'vulpes_manage_group', 'vulpes_manage_group_shortcode' );

?>
