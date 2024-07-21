<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Academy_LMS_Roles {

    public function __construct() {
        add_action( 'init', array( $this, 'rename_roles' ) );
        add_action( 'init', array( $this, 'disable_contributor_role' ) );
    }

    public function rename_roles() {
        global $wp_roles;

        if ( ! isset( $wp_roles ) ) {
            $wp_roles = new WP_Roles();
        }

        // Rename Editor to Superuser
        if ( isset( $wp_roles->roles['editor'] ) ) {
            $wp_roles->roles['editor']['name'] = 'Superuser';
            $wp_roles->role_names['editor'] = 'Superuser';
        }

        // Rename Author to Manager
        if ( isset( $wp_roles->roles['author'] ) ) {
            $wp_roles->roles['author']['name'] = 'Manager';
            $wp_roles->role_names['author'] = 'Manager';
        }

        // Rename Subscriber to Employee
        if ( isset( $wp_roles->roles['subscriber'] ) ) {
            $wp_roles->roles['subscriber']['name'] = 'Employee';
            $wp_roles->role_names['subscriber'] = 'Employee';
        }
    }

    public function disable_contributor_role() {
        remove_role( 'contributor' );
    }
}

// Initialize the roles class
new Academy_LMS_Roles();
