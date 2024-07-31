<?php
// Shortcode: [vulpes_all_users]

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function vulpes_all_users_shortcode() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return '<p>You do not have permission to view this page.</p>';
    }

    $users = get_users();

    if ( empty( $users ) ) {
        return '<p>No users found.</p>';
    }

    // Define the URL of the manage-user page
    $manage_user_url = site_url('/manage-user/');

    ob_start();
    ?>
    <div class="vulpes-lms-shortcodes">
        <table>
            <thead>
                <tr>
                    <th>Display Name</th>
                    <th>Position</th>
                    <th>Manager</th>
                    <th>Group</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $users as $user ) : ?>
                    <?php
                    $position = get_user_meta( $user->ID, 'position', true );
                    $manager_id = get_user_meta( $user->ID, 'manager', true );
                    $manager = get_userdata( $manager_id );
                    $group = get_user_meta( $user->ID, 'group', true );
                    ?>
                    <tr>
                        <td><?php echo esc_html( $user->display_name ); ?></td>
                        <td><?php echo esc_html( $position ); ?></td>
                        <td><?php echo esc_html( $manager ? $manager->display_name : 'N/A' ); ?></td>
                        <td><?php echo esc_html( $group ); ?></td>
                        <td><a href="<?php echo esc_url( add_query_arg( 'user_id', $user->ID, $manage_user_url ) ); ?>">Manage</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode( 'vulpes_all_users', 'vulpes_all_users_shortcode' );
?>
