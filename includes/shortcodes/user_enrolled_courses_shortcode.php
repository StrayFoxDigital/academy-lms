<?php

// Shortcode: [vulpes_user_enrolled_courses]

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function vulpes_user_enrolled_courses_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>You need to be logged in to view your enrolled courses.</p>';
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $table_name = $wpdb->prefix . 'vulpes_lms_course_assignments';
    $courses_table_name = $wpdb->prefix . 'vulpes_lms_courses';
    
    // Fetch enrolled courses with their learning paths
    $enrolled_courses = $wpdb->get_results($wpdb->prepare(
        "SELECT c.*, lc.learning_path FROM $table_name c
        LEFT JOIN $courses_table_name lc ON c.course_id = lc.id
        WHERE c.employee_id = %d ORDER BY lc.learning_path ASC, c.date_enrolled DESC",
        $user_id
    ));

    if (empty($enrolled_courses)) {
        return '<p>You are not enrolled in any courses.</p>';
    }

    // Group courses by learning path
    $courses_by_path = [];
    foreach ($enrolled_courses as $course) {
        $path = !empty($course->learning_path) ? $course->learning_path : 'Standalone Courses';
        if (!isset($courses_by_path[$path])) {
            $courses_by_path[$path] = [];
        }
        $courses_by_path[$path][] = $course;
    }

    ob_start();
    ?>
    <div class="vulpes-lms-shortcodes">
        <?php foreach ($courses_by_path as $path_name => $courses) : ?>
            <h3><?php echo esc_html($path_name); ?></h3>
            <table>
                <thead>
                    <tr>
                        <th style="width: 60%;">Course Name</th>
                        <th style="width: 15%;">Date Enrolled</th>
                        <th style="width: 15%;">Status</th>
                        <th style="width: 10%;">Link</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $course) : ?>
                        <?php
                        $course_url = $wpdb->get_var($wpdb->prepare("SELECT course_url FROM $courses_table_name WHERE id = %d", $course->course_id));
                        ?>
                        <tr>
                            <td><?php echo esc_html($course->course_name); ?></td>
                            <td><?php echo esc_html(date('d-m-Y', strtotime($course->date_enrolled))); ?></td>
                            <td><?php echo esc_html(ucfirst($course->status)); ?></td>
                            <td><?php echo $course_url ? '<a href="' . esc_url($course_url) . '" target="_blank">Link</a>' : 'N/A'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('vulpes_user_enrolled_courses', 'vulpes_user_enrolled_courses_shortcode');
?>
