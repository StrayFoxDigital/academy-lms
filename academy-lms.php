<?php
/*
Plugin Name: Academy LMS
Plugin URI: https://academy.strayfox.co.uk
Description: A Learning Management System (LMS) plugin for WordPress
Version: 1.0
Author: SFDIGITAL
Author URI: https://strayfoxdigital.com
*/

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define plugin path
define( 'ACADEMY_LMS_PATH', plugin_dir_path( __FILE__ ) );

// Include the main class file
require_once ACADEMY_LMS_PATH . 'includes/class-academy-lms.php';
require_once ACADEMY_LMS_PATH . 'includes/roles.php';

// Initialize the plugin
function academy_lms_init() {
    $academy_lms = new Academy_LMS();
}
add_action( 'plugins_loaded', 'academy_lms_init' );

// Activation hook: create the groups table
register_activation_hook( __FILE__, 'academy_lms_install' );

function academy_lms_install() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Create groups table
    $table_name = $wpdb->prefix . 'academy_lms_groups';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        group_name varchar(255) NOT NULL,
        manager varchar(255) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    // Create courses table
    $table_name = $wpdb->prefix . 'academy_lms_courses';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        course_name varchar(255) NOT NULL,
        course_description text NOT NULL,
        expiry_duration int NOT NULL,
        training_provider varchar(255),
        PRIMARY KEY  (id)
    ) $charset_collate;";
    dbDelta( $sql );
    
    // Add the training_provider column if it doesn't exist
    $existing_columns = $wpdb->get_col("DESC $table_name", 0);
    if ( ! in_array( 'training_provider', $existing_columns ) ) {
        $wpdb->query("ALTER TABLE $table_name ADD training_provider varchar(255)");
    }
}
