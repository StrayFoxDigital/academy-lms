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
    $training_table_name = $wpdb->prefix . 'vulpes_lms_training_log';
    $courses_table_name = $wpdb->prefix . 'vulpes_lms_courses';

    // Fetch training logs with associated learning paths
    $training_logs = $wpdb->get_results($wpdb->prepare(
        "SELECT tl.*, c.learning_path FROM $training_table_name tl
         LEFT JOIN $courses_table_name c ON tl.course_name = c.course_name
         WHERE tl.employee_id = %d
         ORDER BY c.learning_path ASC, tl.date_completed DESC",
        $user_id
    ));

    if (empty($training_logs)) {
        return '<p>You have no training records.</p>';
    }

    // Group training logs by learning path
    $logs_by_path = [];
    foreach ($training_logs as $log) {
        $path = !empty($log->learning_path) ? $log->learning_path : 'Standalone Courses';
        if (!isset($logs_by_path[$path])) {
            $logs_by_path[$path] = [];
        }
        $logs_by_path[$path][] = $log;
    }

    ob_start();
    ?>
    <div class="vulpes-lms-shortcodes">
        <?php foreach ($logs_by_path as $path_name => $logs) : ?>
            <h3><?php echo esc_html($path_name); ?></h3>
            <table>
                <thead>
                    <tr>
                        <th style="width: 60%;">Course Name</th>
                        <th style="width: 15%;">Completed Date</th>
                        <th style="width: 15%;">Expiry Date</th>
                        <th style="width: 10%;">Files</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log) : ?>
                        <tr>
                            <td><?php echo esc_html($log->course_name); ?></td>
                            <td><?php echo esc_html(date('d-m-Y', strtotime($log->date_completed))); ?></td>
                            <td><?php echo esc_html(date('d-m-Y', strtotime($log->expiry_date))); ?></td>
                            <td>
                                <?php if ($log->uploads) : ?>
                                    <a href="<?php echo esc_url($log->uploads); ?>" target="_blank">View</a>
                                <?php else : ?>
                                    <span>N/A</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('vulpes_user_training_log', 'vulpes_user_training_log_shortcode');
