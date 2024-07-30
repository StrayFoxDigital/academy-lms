<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Function to calculate total achievable score for a learning path
function vulpes_lms_calculate_total_achievable_score( $learning_path_name ) {
    global $wpdb;
    $total_score = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT SUM(competency_score) FROM {$wpdb->prefix}vulpes_lms_courses WHERE learning_path = %s",
            $learning_path_name
        )
    );
    return $total_score ? $total_score : 0;
}

// Hook to update learning paths table on plugin activation
function vulpes_lms_update_learning_paths_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'vulpes_lms_learning_paths';
    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'total_achievable_score'");

    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_name ADD total_achievable_score INT DEFAULT 0");
    }
}
?>