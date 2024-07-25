<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Function to calculate total achievable score for a subject group
function vulpes_lms_calculate_total_achievable_score( $subject_group_name ) {
    global $wpdb;
    $total_score = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT SUM(competency_score) FROM {$wpdb->prefix}vulpes_lms_courses WHERE subject_group = %s",
            $subject_group_name
        )
    );
    return $total_score ? $total_score : 0;
}

// Hook to update subject groups table on plugin activation
function vulpes_lms_update_subject_groups_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'vulpes_lms_subject_groups';
    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'total_achievable_score'");

    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_name ADD total_achievable_score INT DEFAULT 0");
    }
}
