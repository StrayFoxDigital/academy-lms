<?php
/*
Plugin Name: Vulpes LMS
Plugin URI: https://academy.strayfox.co.uk
Description: A Learning Management System (LMS) plugin for WordPress
Version: 0.1.0-alpha
Author: Stray Fox Digital Limited
Author URI: https://strayfoxdigital.com
License: Proprietary
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin path
define('VULPES_LMS_PATH', plugin_dir_path(__FILE__));
define('VULPES_LMS_VERSION', '0.1.0-alpha');

// Include necessary files
require_once VULPES_LMS_PATH . 'includes/class-vulpes-lms.php';
require_once VULPES_LMS_PATH . 'includes/roles.php';
require_once VULPES_LMS_PATH . 'includes/shortcodes.php';
require_once VULPES_LMS_PATH . 'includes/functions.php';
require_once VULPES_LMS_PATH . 'includes/activator.php'; // Include the activator file
require_once VULPES_LMS_PATH . 'includes/email-notifications.php'; // Include the email notifications file
require_once VULPES_LMS_PATH . 'includes/updater.php'; // Include the updater file

// Initialize the plugin
function vulpes_lms_init() {
    $vulpes_lms = new Vulpes_LMS();

    // Initialize the updater
    $updater = new Vulpes_LMS_Updater(__FILE__);
    $updater->set_username('StrayFoxDigital');
    $updater->set_repository('academy-lms');
    $updater->initialize();
}
add_action('plugins_loaded', 'vulpes_lms_init');

// Redirect to home URL after logout
add_action('wp_logout', 'ps_redirect_after_logout');
function ps_redirect_after_logout() {
    wp_safe_redirect(home_url());
    exit();
}
