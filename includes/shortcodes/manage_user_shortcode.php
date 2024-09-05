<?php
// Shortcode: [vulpes_manage_user]

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function vulpes_manage_user_shortcode() {
    global $wpdb;
    $user_id = isset( $_GET['user_id'] ) ? intval( $_GET['user_id'] ) : 0;

    if ( ! $user_id ) {
        return '<div class="error"><p>User not found.</p></div>';
    }

    $user = get_userdata( $user_id );

    if ( ! $user ) {
        return '<div class="error"><p>User not found.</p></div>';
    }

    // Handle form submission
    if ( isset( $_POST['first_name'] ) && isset( $_POST['last_name'] ) && isset( $_POST['position'] ) && isset( $_POST['manager'] ) && isset( $_POST['group'] ) ) {
        $first_name = sanitize_text_field( $_POST['first_name'] );
        $last_name = sanitize_text_field( $_POST['last_name'] );
        $position = sanitize_text_field( $_POST['position'] );
        $manager = intval( $_POST['manager'] );
        $group = sanitize_text_field( $_POST['group'] );

        wp_update_user( array(
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
        ) );

        update_user_meta( $user_id, 'position', $position );
        update_user_meta( $user_id, 'manager', $manager );
        update_user_meta( $user_id, 'group', $group );

        echo '<div class="updated"><p>User updated successfully.</p></div>';

        $user = get_userdata( $user_id ); // Refresh user data
    }

    // Fetch all users except Employees for manager dropdown
    $allowed_roles = array( 'administrator', 'editor', 'author' );
    $managers = get_users( array(
        'role__in' => $allowed_roles,
    ) );

    ob_start();
    ?>
    <div class="vulpes-lms-shortcodes">
        <div style="display: flex;">
            <div style="flex: 0 0 25%; text-align: center;">
                <div class="user-avatar">
                    <?php echo get_avatar( $user_id, 150 ); ?>
                </div>
                <h2 style="padding-bottom: 10px;"><?php echo esc_html( $user->display_name ); ?></h2>
            </div>
            <div style="flex: 0 0 75%;">
                <form method="post" action="" id="user-edit-form">
                    <table class="form-table" style="width: 100%; text-align: left;">
                        <tr valign="top">
                            <th scope="row"><label for="first_name">First Name</label></th>
                            <td><input type="text" id="first_name" name="first_name" class="regular-text" style="width: 100%;" value="<?php echo esc_attr( $user->first_name ); ?>" required /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="last_name">Last Name</label></th>
                            <td><input type="text" id="last_name" name="last_name" class="regular-text" style="width: 100%;" value="<?php echo esc_attr( $user->last_name ); ?>" required /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="position">Position</label></th>
                            <td><input type="text" id="position" name="position" class="regular-text" style="width: 100%;" value="<?php echo esc_attr( get_user_meta( $user_id, 'position', true ) ); ?>" required /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="manager">Manager</label></th>
                            <td>
                                <select id="manager" name="manager" style="width: 100%;" required>
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
                            <th scope="row"><label for="group">Group</label></th>
                            <td><input type="text" id="group" name="group" class="regular-text" style="width: 100%;" value="<?php echo esc_attr( get_user_meta( $user_id, 'group', true ) ); ?>" required /></td>
                        </tr>
                    </table>
                    <div style="text-align: right; margin-top: 10px;">
                        <button type="submit" class="button-primary" id="submit-btn" style="display: none;">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function($) {
            var initialData = $('#user-edit-form').serialize();

            $('#user-edit-form input, #user-edit-form select').on('input change', function() {
                var currentData = $('#user-edit-form').serialize();
                if (initialData !== currentData) {
                    $('#submit-btn').show();
                } else {
                    $('#submit-btn').hide();
                }
            });
        })(jQuery);
    </script>
    <?php
    return ob_get_clean();
}

add_shortcode( 'vulpes_manage_user', 'vulpes_manage_user_shortcode' );
?>