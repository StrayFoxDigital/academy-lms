<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Vulpes_LMS_Roles {

    public function __construct() {
        add_action( 'init', array( $this, 'add_custom_roles' ) );
        add_action( 'init', array( $this, 'remove_contributor_role' ) );
        add_filter( 'editable_roles', array( $this, 'editable_roles' ) );
    }

    public function add_custom_roles() {
        // Change role names
        global $wp_roles;

        if ( ! isset( $wp_roles ) ) {
            $wp_roles = new WP_Roles();
        }

        $wp_roles->roles['editor']['name'] = 'Superuser';
        $wp_roles->role_names['editor'] = 'Superuser';

        $wp_roles->roles['author']['name'] = 'Manager';
        $wp_roles->role_names['author'] = 'Manager';

        $wp_roles->roles['subscriber']['name'] = 'Employee';
        $wp_roles->role_names['subscriber'] = 'Employee';
    }

    public function remove_contributor_role() {
        // Remove Contributor role
        remove_role( 'contributor' );
    }

    public function editable_roles( $roles ) {
        if ( isset( $roles['contributor'] ) ) {
            unset( $roles['contributor'] );
        }
        return $roles;
    }
}

new Vulpes_LMS_Roles();
