<?php

// Shortcode: [vulpes_user_training_log]

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function vulpes_user_training_log_shortcode() {
    if ( ! is_user_logged_in() ) {
        return '<p>You need to be logged in to view your training log.</p>';
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $table_name = $wpdb->prefix . 'vulpes_lms_training_log';
    
    $training_logs = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE employee_id = %d ORDER BY date_completed DESC", $user_id ) );

    if ( empty( $training_logs ) ) {
        return '<p>You have no training records.</p>';
    }

    ob_start();
    ?>
    <div class="vulpes-lms-shortcodes">
        <table>
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Completed Date</th>
                    <th>Expiry Date</th>
                    <th>View Files</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $training_logs as $log ) : ?>
                    <tr>
                        <td><?php echo esc_html( $log->course_name ); ?></td>
                        <td><?php echo esc_html( date( 'd-m-Y', strtotime( $log->date_completed ) ) ); ?></td>
                        <td><?php echo esc_html( date( 'd-m-Y', strtotime( $log->expiry_date ) ) ); ?></td>
                        <td>
                            <?php if ( $log->uploads ) : ?>
                                <a href="<?php echo esc_url( $log->uploads ); ?>" target="_blank">View Files</a>
                            <?php else : ?>
                                <span>No Files</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode( 'vulpes_user_training_log', 'vulpes_user_training_log_shortcode' );