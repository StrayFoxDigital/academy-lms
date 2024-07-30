<?php

// Shortcode: [vulpes_user_profile]

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function vulpes_user_profile_shortcode() {
    if ( ! is_user_logged_in() ) {
        return '<p>You need to be logged in to view your profile.</p>';
    }

    $user_id = get_current_user_id();
    $user = get_userdata( $user_id );

    $manager_id = get_user_meta( $user_id, 'manager', true );
    $manager = get_userdata( $manager_id );
    $manager_name = $manager ? $manager->display_name : 'N/A';

    ob_start();
    ?>
    <div class="vulpes-user-profile vulpes-lms-shortcodes">
        <div class="user-avatar">
            <?php echo get_avatar( $user_id, 96 ); ?>
        </div>
        <div class="user-info">
            <h2><?php echo esc_html( $user->display_name ); ?></h2>
            <h4><?php echo esc_html( $user->user_email ); ?></h4>
            <hr />
            <p><strong>Position:</strong> <?php echo esc_html( get_user_meta( $user_id, 'position', true ) ); ?></br>
            <strong>Manager:</strong> <?php echo esc_html( $manager_name ); ?></br>
            <strong>Group:</strong> <?php echo esc_html( get_user_meta( $user_id, 'group', true ) ); ?></p>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode( 'vulpes_user_profile', 'vulpes_user_profile_shortcode' );