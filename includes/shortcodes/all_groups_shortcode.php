<?php

// Shortcode: [vulpes_all_groups]

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function vulpes_all_groups_shortcode() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'vulpes_lms_groups';

    $groups = $wpdb->get_results( "SELECT * FROM $table_name" );

    if ( empty( $groups ) ) {
        return '<p>No groups found.</p>';
    }

    ob_start();
    ?>
    <div class="vulpes-lms-shortcodes">
        <table>
            <thead>
                <tr>
                    <th>Group Name</th>
                    <th>Manager</th>
                    <th>Assigned Employees</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
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
                            <a href="#">Manage</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode( 'vulpes_all_groups', 'vulpes_all_groups_shortcode' );