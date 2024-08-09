<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Activation hook: create the necessary tables
register_activation_hook( __FILE__, 'vulpes_lms_install' );

function vulpes_lms_install() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Include the upgrade.php file to use the dbDelta function
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    // Create groups table
    $table_name = $wpdb->prefix . 'vulpes_lms_groups';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        group_name varchar(255) NOT NULL,
        manager varchar(255) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    dbDelta( $sql );

    // Create courses table
    $table_name = $wpdb->prefix . 'vulpes_lms_courses';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        course_name varchar(255) NOT NULL,
        course_description text NOT NULL,
        expiry_duration int NOT NULL,
        training_provider varchar(255),
        learning_path varchar(255),
        competency_score int,
        course_link varchar(255),
        PRIMARY KEY  (id)
    ) $charset_collate;";
    dbDelta( $sql );

    // Create training log table
    $table_name = $wpdb->prefix . 'vulpes_lms_training_log';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        employee_id mediumint(9) NOT NULL,
        employee_name varchar(255) NOT NULL,
        course_name varchar(255) NOT NULL,
        date_completed date NOT NULL,
        expiry_date date NOT NULL,
        uploads varchar(255),
        PRIMARY KEY  (id)
    ) $charset_collate;";
    dbDelta( $sql );

    // Create learning paths table
    $table_name = $wpdb->prefix . 'vulpes_lms_learning_paths';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        learning_path_name varchar(255) NOT NULL,
        description text,
        total_achievable_score int DEFAULT 0,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    dbDelta( $sql );

    // Create course assignments table
    $table_name = $wpdb->prefix . 'vulpes_lms_course_assignments';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        employee_id mediumint(9) NOT NULL,
        employee_name varchar(255) NOT NULL,
        course_id mediumint(9) NOT NULL,
        course_name varchar(255) NOT NULL,
        date_enrolled date NOT NULL,
        status varchar(255) NOT NULL DEFAULT 'enrolled',
        PRIMARY KEY  (id)
    ) $charset_collate;";
    dbDelta( $sql );

    // Create skills list table
    $table_name = $wpdb->prefix . 'vulpes_lms_skills_list';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        skill_name varchar(255) NOT NULL,
        parent_skill varchar(255) NOT NULL,
        is_parent varchar(5) NOT NULL,
        description text DEFAULT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    dbDelta( $sql );

    // Create skill assignments table
    $table_name = $wpdb->prefix . 'vulpes_lms_skill_assignments';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        skill_name varchar(255) NOT NULL,
        is_parent varchar(5) NOT NULL,
        employee_id mediumint(9) NOT NULL,
        level int NOT NULL DEFAULT 0,
        type varchar(255) NOT NULL,
        parent_skill varchar(255) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    dbDelta( $sql );

    // Create experience table
    $table_name = $wpdb->prefix . 'vulpes_lms_experience';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        company varchar(255) NOT NULL,
        role varchar(255) NOT NULL,
        date_from date NOT NULL,
        date_to date,
        details text,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    dbDelta( $sql );

    // Create uploads folder
    $upload_dir = wp_upload_dir();
    $dir = $upload_dir['basedir'] . '/vulpes-lms-uploads';

    if ( ! file_exists( $dir ) ) {
        wp_mkdir_p( $dir );
    }
}
