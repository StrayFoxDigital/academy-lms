<?php

// Shortcode: [vulpes_user_learning_path_scores]

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function vulpes_user_learning_path_scores_shortcode() {
    if ( ! is_user_logged_in() ) {
        return '<p>You need to be logged in to view your learning path scores.</p>';
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $learning_paths_table = $wpdb->prefix . 'vulpes_lms_learning_paths';
    $courses_table = $wpdb->prefix . 'vulpes_lms_courses';
    $training_log_table = $wpdb->prefix . 'vulpes_lms_training_log';
    $course_assignments_table = $wpdb->prefix . 'vulpes_lms_course_assignments';

    // Fetch all learning paths
    $learning_paths = $wpdb->get_results( "SELECT * FROM $learning_paths_table" );

    // Array to store learning path scores
    $learning_path_scores = [];

    // Calculate scores for each learning path
    foreach ( $learning_paths as $learning_path ) {
        $learning_path_name = $learning_path->learning_path_name;

        // Get all courses in this learning path
        $courses = $wpdb->get_results( $wpdb->prepare( "SELECT id, competency_score FROM $courses_table WHERE learning_path = %s", $learning_path_name ) );

        $total_score = 0;
        $total_achievable_score = 0;

        foreach ( $courses as $course ) {
            $course_id = $course->id;
            $competency_score = $course->competency_score;

            // Check if the user has completed this course
            $completed_course = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $training_log_table WHERE employee_id = %d AND course_name = (SELECT course_name FROM $courses_table WHERE id = %d)", $user_id, $course_id ) );

            if ( $completed_course ) {
                $total_score += $competency_score;
            }

            $total_achievable_score += $competency_score;
        }

        // Check if the user is enrolled in any courses of this learning path
        $enrolled_courses = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $course_assignments_table WHERE employee_id = %d AND course_id IN (SELECT id FROM $courses_table WHERE learning_path = %s)", $user_id, $learning_path_name ) );

        if ( $total_score > 0 || ! empty( $enrolled_courses ) ) {
            $learning_path_scores[] = array(
                'learning_path_name' => $learning_path_name,
                'total_score' => $total_score,
                'total_achievable_score' => $total_achievable_score,
                'level' => get_level_from_score( $total_score )
            );
        }
    }

    ob_start();
    ?>
    <div class="vulpes-lms-shortcodes">
        <table>
            <thead>
                <tr>
                    <th style="width: 60%;">Learning Path</th>
                    <th style="width: 25%;">Progress</th>
                    <th style="width: 15%;">Level</th>
                </tr>
            </thead>
            <tbody>
                <?php if ( ! empty( $learning_path_scores ) ) : ?>
                    <?php foreach ( $learning_path_scores as $learning_path_score ) : ?>
                        <tr>
                            <td><?php echo esc_html( $learning_path_score['learning_path_name'] ); ?></td>
                            <td>
                                <div class="progress-container">
                                    <span class="progress-label"><?php echo round( ( $learning_path_score['total_score'] / $learning_path_score['total_achievable_score'] ) * 100 ); ?>%</span>
                                    <div class="progress-bar">
                                        <div class="progress" style="width: <?php echo ( $learning_path_score['total_score'] / $learning_path_score['total_achievable_score'] ) * 100; ?>%;"></div>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo esc_html( $learning_path_score['level'] ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="3">No scores found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode( 'vulpes_user_learning_path_scores', 'vulpes_user_learning_path_scores_shortcode' );

function get_level_from_score( $score ) {
    if ( $score >= 1 && $score <= 20 ) {
        return 'Beginner';
    } elseif ( $score >= 21 && $score <= 40 ) {
        return 'Novice';
    } elseif ( $score >= 41 && $score <= 60 ) {
        return 'Competent';
    } elseif ( $score >= 61 && $score <= 80 ) {
        return 'Proficient';
    } elseif ( $score >= 81 && $score <= 100 ) {
        return 'Expert';
    } else {
        return 'Unknown';
    }
}