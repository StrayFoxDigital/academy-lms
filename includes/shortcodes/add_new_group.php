<?php

// Shortcode: [vulpes_add_new_group]

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function vulpes_add_new_group_shortcode() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'vulpes_lms_groups';

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

        // Redirect to the frontend groups page
        echo '<script type="text/javascript">
            window.location = "' . site_url('/groups/') . '";
        </script>';
        return;
    }

    // Fetch users with roles Administrator, Superuser, and Manager
    $allowed_roles = array( 'administrator', 'editor', 'author' ); // Editor is now Superuser, Author is now Manager
    $users = get_users( array(
        'role__in' => $allowed_roles,
    ) );

    ob_start();
    ?>
    <div class="vulpes-lms-shortcodes">
        <form method="post" action="">
            <table class="form-table" style="width: 100%;">
                <tr valign="top">
                    <th scope="row" style="text-align: left; width: 25%;"><label for="group_name">Group Name</label></th>
                    <td style="width: 75%;"><input type="text" id="group_name" name="group_name" class="regular-text" style="width: 100%;" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row" style="text-align: left;"><label for="manager">Manager</label></th>
                    <td>
                        <select id="manager" name="manager" required style="width: 100%;">
                            <option value="">Select a Manager</option>
                            <?php foreach ( $users as $user ) : ?>
                                <option value="<?php echo esc_attr( $user->ID ); ?>"><?php echo esc_html( $user->display_name ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
            <div style="text-align: right; margin-top: 10px;">
                <button type="submit" class="button-primary">Add Group</button>
            </div>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode( 'vulpes_add_new_group', 'vulpes_add_new_group_shortcode' );

?>
