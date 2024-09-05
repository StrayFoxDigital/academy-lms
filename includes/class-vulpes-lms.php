<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Vulpes_LMS {

    public function __construct() {
        // Load custom fields
        require_once VULPES_LMS_PATH . 'includes/custom-fields.php';
        new Vulpes_LMS_Custom_Fields();

        // Load roles
        require_once VULPES_LMS_PATH . 'includes/roles.php';
        new Vulpes_LMS_Roles();

        // Add admin menu
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

        // Filter to replace Gravatar
        add_filter( 'get_avatar', array( $this, 'replace_gravatar_with_custom_avatar' ), 10, 5 );

        // Load notification settings
        require_once VULPES_LMS_PATH . 'includes/notifications-settings.php';
    }

    public function add_admin_menu() {
        add_menu_page( 'Vulpes LMS', 'Vulpes LMS', 'manage_options', 'vulpes-lms', array( $this, 'admin_page' ), 'dashicons-welcome-learn-more' );
        add_submenu_page( 'vulpes-lms', 'Training Log', 'Training Log', 'manage_options', 'vulpes-lms-training-log', array( $this, 'training_log_page' ) );
        add_submenu_page( 'vulpes-lms', 'Training Courses', 'Training Courses', 'manage_options', 'vulpes-lms-courses', array( $this, 'courses_page' ) );
        add_submenu_page( 'vulpes-lms', 'Learning Paths', 'Learning Paths', 'manage_options', 'vulpes-lms-learning-paths', array( $this, 'learning_paths_page' ) );
        add_submenu_page( 'vulpes-lms', 'Employees', 'Employees', 'manage_options', 'vulpes-lms-employees', array( $this, 'employees_page' ) );
        add_submenu_page( 'vulpes-lms', 'Employee Groups', 'Employee Groups', 'manage_options', 'vulpes-lms-groups', array( $this, 'groups_page' ) );
        add_submenu_page( 'vulpes-lms', 'Reports', 'Reports', 'manage_options', 'vulpes-lms-reports', array( $this, 'reports_page' ) );
        add_submenu_page( 'vulpes-lms', 'Skills Management', 'Skills Management', 'manage_options', 'vulpes-lms-skills', array( $this, 'skills_page' ) );
        add_submenu_page( 'vulpes-lms', 'Achievements', 'Achievements', 'manage_options', 'vulpes-lms-achievements', array( $this, 'achievements_page' ) );
        add_submenu_page( null, 'Edit Employee', 'Edit Employee', 'manage_options', 'vulpes-lms-edit-employee', array( $this, 'edit_employee_page' ) );
        add_submenu_page( null, 'Edit Group', 'Edit Group', 'manage_options', 'vulpes-lms-edit-group', array( $this, 'edit_group_page' ) );
        add_submenu_page( null, 'Edit Course', 'Edit Course', 'manage_options', 'vulpes-lms-edit-course', array( $this, 'edit_course_page' ) );
        add_submenu_page( null, 'Edit Learning Path', 'Edit Learning Path', 'manage_options', 'vulpes-lms-edit-learning-path', array( $this, 'edit_learning_path_page' ) );
        add_submenu_page( null, 'Manage Training', 'Manage Training', 'manage_options', 'vulpes-lms-manage-training', array( $this, 'manage_training_page' ) );
        add_submenu_page( null, 'Manage Skill', 'Manage Skill', 'manage_options', 'vulpes-lms-manage-skill', array( $this, 'manage_skill_page' ) );
    }

    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>Vulpes LMS</h1>
            <h2 class="nav-tab-wrapper">
                <a href="#general" class="nav-tab nav-tab-active">General</a>
                <a href="#notifications" class="nav-tab">Notifications</a>
                <a href="#help-support" class="nav-tab">Help & Support</a>
            </h2>
            
            <div id="general" class="tab-content">
                <p>This is the General tab content. Placeholder for now.</p>
            </div>

            <div id="notifications" class="tab-content" style="display: none;">
                <form method="post" action="options.php">
                    <?php
                    settings_fields('vulpes_lms_notifications_settings');
                    do_settings_sections('vulpes_lms_notifications');
                    submit_button();
                    ?>
                </form>
            </div>

            <div id="help-support" class="tab-content" style="display: none;">
                <p>This is the Help & Support tab content. Placeholder for now.</p>
            </div>
        </div>

        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('.nav-tab').click(function(e) {
                    e.preventDefault();
                    $('.nav-tab').removeClass('nav-tab-active');
                    $(this).addClass('nav-tab-active');
                    $('.tab-content').hide();
                    $($(this).attr('href')).show();
                });

                // Keep the active tab after form submission
                var hash = window.location.hash;
                if (hash) {
                    $('.nav-tab[href="' + hash + '"]').click();
                }
            });
        </script>
        <?php
    }

    public function training_log_page() {
        require_once VULPES_LMS_PATH . 'includes/training-log-page.php';
    }

    public function courses_page() {
        require_once VULPES_LMS_PATH . 'includes/courses-page.php';
    }

    public function learning_paths_page() {
        require_once VULPES_LMS_PATH . 'includes/learning-paths-page.php';
    }

    public function employees_page() {
        require_once VULPES_LMS_PATH . 'includes/employees-page.php';
    }

    public function groups_page() {
        require_once VULPES_LMS_PATH . 'includes/groups-page.php';
    }

    public function reports_page() {
        echo '<div class="wrap"><h1>Reports</h1><p>This is a placeholder for the Reports page.</p></div>';
    }

    public function edit_group_page() {
        require_once VULPES_LMS_PATH . 'includes/edit-group-page.php';
    }

    public function edit_course_page() {
        require_once VULPES_LMS_PATH . 'includes/edit-course-page.php';
    }

    public function edit_employee_page() {
        require_once VULPES_LMS_PATH . 'includes/edit-employee-page.php';
    }

    public function edit_learning_path_page() {
        require_once VULPES_LMS_PATH . 'includes/edit-learning-path-page.php';
    }

    public function manage_training_page() {
        require_once VULPES_LMS_PATH . 'includes/manage-training-page.php';
    }

    public function skills_page() {
        require_once VULPES_LMS_PATH . 'includes/skills-page.php';
    }

    public function manage_skill_page() {
        require_once VULPES_LMS_PATH . 'includes/manage-skill-page.php';
    }

    public function achievements_page() {
        require_once VULPES_LMS_PATH . 'includes/achievements-page.php';
    }

    public function replace_gravatar_with_custom_avatar( $avatar, $id_or_email, $size, $default, $alt ) {
        $user_id = 0;
        if ( is_numeric( $id_or_email ) ) {
            $user_id = (int) $id_or_email;
        } elseif ( is_object( $id_or_email ) ) {
            if ( ! empty( $id_or_email->user_id ) ) {
                $user_id = (int) $id_or_email->user_id;
            }
        } else {
            $user = get_user_by( 'email', $id_or_email );
            if ( $user ) {
                $user_id = $user->ID;
            }
        }

        if ( $user_id ) {
            $custom_avatar = get_user_meta( $user_id, 'avatar', true );
            if ( $custom_avatar ) {
                $avatar = '<img src="' . esc_url( $custom_avatar ) . '" alt="' . esc_attr( $alt ) . '" class="avatar avatar-' . esc_attr( $size ) . ' photo" height="' . esc_attr( $size ) . '" width="' . esc_attr( $size ) . '" />';
            }
        }

        return $avatar;
    }
}