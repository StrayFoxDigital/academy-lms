<?php
/*
Plugin Name: Vulpes LMS
Plugin URI: https://academy.strayfox.co.uk
Description: A Learning Management System (LMS) plugin for WordPress
Version: 1.0
Author: Stray Fox Digital Limited
Author URI: https://strayfoxdigital.com
License: Proprietary
*/

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define plugin path
define( 'VULPES_LMS_PATH', plugin_dir_path( __FILE__ ) );

// Include the main class file
require_once VULPES_LMS_PATH . 'includes/class-vulpes-lms.php';
require_once VULPES_LMS_PATH . 'includes/roles.php';
require_once VULPES_LMS_PATH . 'includes/shortcodes.php'; // Include the shortcodes file

// Initialize the plugin
function vulpes_lms_init() {
    $vulpes_lms = new Vulpes_LMS();
}
add_action( 'plugins_loaded', 'vulpes_lms_init' );

// Activation hook: create the necessary tables
register_activation_hook( __FILE__, 'vulpes_lms_install' );

function vulpes_lms_install() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Create groups table
    $table_name = $wpdb->prefix . 'vulpes_lms_groups';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        group_name varchar(255) NOT NULL,
        manager varchar(255) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    // Create courses table
    $table_name = $wpdb->prefix . 'vulpes_lms_courses';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        course_name varchar(255) NOT NULL,
        course_description text NOT NULL,
        expiry_duration int NOT NULL,
        training_provider varchar(255),
        subject_group varchar(255),
        competency_score int,
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

    // Create subject groups table
    $table_name = $wpdb->prefix . 'vulpes_lms_subject_groups';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        subject_group_name varchar(255) NOT NULL,
        description text,
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

    // Create uploads folder
    $upload_dir = wp_upload_dir();
    $dir = $upload_dir['basedir'] . '/vulpes_lms_uploads';

    if ( ! file_exists( $dir ) ) {
        wp_mkdir_p( $dir );
    }
}
