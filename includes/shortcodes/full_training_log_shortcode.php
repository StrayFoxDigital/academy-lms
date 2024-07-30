<?php

// Shortcode: [vulpes_full_training_log]

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function vulpes_full_training_log_shortcode() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'vulpes_lms_training_log';

    $training_logs = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY date_completed DESC" );

    if ( empty( $training_logs ) ) {
        return '<p>No training records found.</p>';
    }

    ob_start();
    ?>
    <div class="vulpes-lms-shortcodes">
        <table>
            <thead>
                <tr>
                    <th>Employee Name</th>
                    <th>Course Name</th>
                    <th>Date Completed</th>
                    <th>Expiry Date</th>
                    <th>Status</th>
                    <th>View Files</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $training_logs as $log ) : ?>
                    <?php
                        $status = 'Complete';
                        $expiry_date = strtotime( $log->expiry_date );
                        $current_date = time();
                        $days_to_expiry = ( $expiry_date - $current_date ) / ( 60 * 60 * 24 );

                        if ( $days_to_expiry <= 0 ) {
                            $status = 'EXPIRED';
                        } elseif ( $days_to_expiry <= 30 ) {
                            $status = 'Due to Expire';
                        }
                    ?>
                    <tr>
                        <td><?php echo esc_html( $log->employee_name ); ?></td>
                        <td><?php echo esc_html( $log->course_name ); ?></td>
                        <td><?php echo esc_html( date( 'd-m-Y', strtotime( $log->date_completed ) ) ); ?></td>
                        <td><?php echo esc_html( date( 'd-m-Y', strtotime( $log->expiry_date ) ) ); ?></td>
                        <td><?php echo esc_html( $status ); ?></td>
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

add_shortcode( 'vulpes_full_training_log', 'vulpes_full_training_log_shortcode' );