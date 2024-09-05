<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Vulpes_LMS_Shortcodes
{

    public function __construct()
    {
        $this->load_shortcodes();
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
    }

    private function load_shortcodes()
    {
        require_once VULPES_LMS_PATH . 'includes/shortcodes/user_profile_shortcode.php';
        require_once VULPES_LMS_PATH . 'includes/shortcodes/user_training_log_shortcode.php';
        require_once VULPES_LMS_PATH . 'includes/shortcodes/user_enrolled_courses_shortcode.php';
        require_once VULPES_LMS_PATH . 'includes/shortcodes/user_learning_path_scores_shortcode.php';
        require_once VULPES_LMS_PATH . 'includes/shortcodes/full_training_log_shortcode.php';
        require_once VULPES_LMS_PATH . 'includes/shortcodes/all_groups_shortcode.php';
        require_once VULPES_LMS_PATH . 'includes/shortcodes/my_team_shortcode.php';
        require_once VULPES_LMS_PATH . 'includes/shortcodes/manage_group_shortcode.php';
        require_once VULPES_LMS_PATH . 'includes/shortcodes/add_new_group.php';
        require_once VULPES_LMS_PATH . 'includes/shortcodes/all_users_shortcode.php';
        require_once VULPES_LMS_PATH . 'includes/shortcodes/manage_user_shortcode.php';
        require_once VULPES_LMS_PATH . 'includes/shortcodes/user_skills.php';
    	require_once VULPES_LMS_PATH . 'includes/shortcodes/achievements_list.php';
    }

    public function enqueue_styles()
    {
        wp_enqueue_style('vulpes-lms-shortcodes', plugin_dir_url(__FILE__) . '../assets/css/shortcodes.css', array(), '1.0', 'all');
    }
}

new Vulpes_LMS_Shortcodes();
