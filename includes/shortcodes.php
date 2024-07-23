<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Function to display user profile
function academy_lms_user_profile_shortcode( $atts ) {
    // Get current user info
    $current_user = wp_get_current_user();

    if ( ! $current_user->ID ) {
        return '<p>You need to be logged in to view your profile.</p>';
    }

    $avatar = get_avatar( $current_user->ID, 96 );
    $display_name = $current_user->display_name;
    $email = $current_user->user_email;
    $position = get_user_meta( $current_user->ID, 'position', true );
    $manager_id = get_user_meta( $current_user->ID, 'manager', true );
    $group = get_user_meta( $current_user->ID, 'group', true );

    // Get manager name
    $manager = $manager_id ? get_userdata( $manager_id )->display_name : 'N/A';

    ob_start();
    ?>
    <div class="academy-lms-user-profile">
        <div class="user-avatar"><?php echo $avatar; ?></div>
        <div class="user-display-name"><strong><?php echo esc_html( $display_name ); ?></strong></div>
        <div class="user-email"><?php echo esc_html( $email ); ?></div>
        <hr>
        <div class="user-position"><strong>Position:</strong> <?php echo esc_html( $position ); ?></div>
        <div class="user-manager"><strong>Manager:</strong> <?php echo esc_html( $manager ); ?></div>
        <div class="user-group"><strong>Group:</strong> <?php echo esc_html( $group ); ?></div>
    </div>
    <?php
    return ob_get_clean();
}

// Function to display user training log
function academy_lms_user_training_log_shortcode( $atts ) {
    // Get current user info
    $current_user = wp_get_current_user();

    if ( ! $current_user->ID ) {
        return '<p>You need to be logged in to view your training log.</p>';
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'academy_lms_training_log';
    $training_logs = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE employee_id = %d ORDER BY date_completed DESC", $current_user->ID ) );

    ob_start();
    ?>
    <div class="academy-lms-user-training-log">
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Completed Date</th>
                    <th>Expiry Date</th>
                    <th>Actions</th>
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
    <?php
    return ob_get_clean();
}

// Register the shortcodes
function academy_lms_register_shortcodes() {
    add_shortcode( 'academy_user_profile', 'academy_lms_user_profile_shortcode' );
    add_shortcode( 'academy_user_training_log', 'academy_lms_user_training_log_shortcode' );
}

add_action( 'init', 'academy_lms_register_shortcodes' );