<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Academy_LMS {

    public function __construct() {
        // Load custom fields
        require_once ACADEMY_LMS_PATH . 'includes/custom-fields.php';
        new Academy_LMS_Custom_Fields();

        // Load roles
        require_once ACADEMY_LMS_PATH . 'includes/roles.php';
        new Academy_LMS_Roles();

        // Add admin menu
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

        // Filter to replace Gravatar
        add_filter( 'get_avatar', array( $this, 'replace_gravatar_with_custom_avatar' ), 10, 5 );
    }

    public function add_admin_menu() {
        add_menu_page( 'Academy LMS', 'Academy LMS', 'manage_options', 'academy-lms', array( $this, 'info_page' ), 'dashicons-welcome-learn-more' );
        add_submenu_page( 'academy-lms', 'Employees', 'Employees', 'manage_options', 'academy-lms-employees', array( $this, 'employees_page' ) );
        add_submenu_page( 'academy-lms', 'Groups', 'Groups', 'manage_options', 'academy-lms-groups', array( $this, 'groups_page' ) );
        add_submenu_page( 'academy-lms', 'Training Courses', 'Training Courses', 'manage_options', 'academy-lms-courses', array( $this, 'courses_page' ) );
        add_submenu_page( 'academy-lms', 'Training Log', 'Training Log', 'manage_options', 'academy-lms-training-log', array( $this, 'training_log_page' ) );
        add_submenu_page( null, 'Edit Employee', 'Edit Employee', 'manage_options', 'academy-lms-edit-employee', array( $this, 'edit_employee_page' ) );
        add_submenu_page( null, 'Edit Group', 'Edit Group', 'manage_options', 'academy-lms-edit-group', array( $this, 'edit_group_page' ) );
        add_submenu_page( null, 'Edit Course', 'Edit Course', 'manage_options', 'academy-lms-edit-course', array( $this, 'edit_course_page' ) );
        add_submenu_page( null, 'Manage Training', 'Manage Training', 'manage_options', 'academy-lms-manage-training', array( $this, 'manage_training_page' ) );
    }

    public function info_page() {
        echo '<div class="wrap"><h1>Academy LMS</h1><p>This is an information page awaiting content.</p></div>';
    }

    public function groups_page() {
        require_once ACADEMY_LMS_PATH . 'includes/groups-page.php';
    }

    public function courses_page() {
        require_once ACADEMY_LMS_PATH . 'includes/courses-page.php';
    }

    public function employees_page() {
        require_once ACADEMY_LMS_PATH . 'includes/employees-page.php';
    }

    public function training_log_page() {
        require_once ACADEMY_LMS_PATH . 'includes/training-log-page.php';
    }

    public function edit_group_page() {
        require_once ACADEMY_LMS_PATH . 'includes/edit-group-page.php';
    }

    public function edit_course_page() {
        require_once ACADEMY_LMS_PATH . 'includes/edit-course-page.php';
    }

    public function edit_employee_page() {
        require_once ACADEMY_LMS_PATH . 'includes/edit-employee-page.php';
    }

    public function manage_training_page() {
        require_once ACADEMY_LMS_PATH . 'includes/manage-training-page.php';
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
