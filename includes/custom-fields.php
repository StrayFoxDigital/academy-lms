<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Academy_LMS_Custom_Fields {

    public function __construct() {
        add_action( 'show_user_profile', array( $this, 'add_custom_user_profile_fields' ) );
        add_action( 'edit_user_profile', array( $this, 'add_custom_user_profile_fields' ) );

        add_action( 'personal_options_update', array( $this, 'save_custom_user_profile_fields' ) );
        add_action( 'edit_user_profile_update', array( $this, 'save_custom_user_profile_fields' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_media_uploader' ) );
    }

    public function enqueue_media_uploader() {
        wp_enqueue_media();
        wp_enqueue_script( 'academy-lms-avatar-upload', plugin_dir_url( __FILE__ ) . '../assets/js/avatar-upload.js', array( 'jquery' ), '1.0', true );
        wp_enqueue_style( 'academy-lms-admin-style', plugin_dir_url( __FILE__ ) . '../assets/css/admin-style.css', array(), '1.0' );
    }

    public function add_custom_user_profile_fields( $user ) {
        ?>
        <h3><?php esc_html_e( 'Additional Information', 'academy-lms' ); ?></h3>

        <table class="form-table">
            <tr>
                <th><label for="position"><?php esc_html_e( 'Position', 'academy-lms' ); ?></label></th>
                <td>
                    <input type="text" name="position" id="position" value="<?php echo esc_attr( get_the_author_meta( 'position', $user->ID ) ); ?>" class="regular-text" /><br />
                    <span class="description"><?php esc_html_e( 'Please enter your position.', 'academy-lms' ); ?></span>
                </td>
            </tr>
            <tr>
                <th><label for="manager"><?php esc_html_e( 'Manager', 'academy-lms' ); ?></label></th>
                <td>
                    <input type="text" name="manager" id="manager" value="<?php echo esc_attr( get_the_author_meta( 'manager', $user->ID ) ); ?>" class="regular-text" /><br />
                    <span class="description"><?php esc_html_e( 'Please enter your manager\'s name.', 'academy-lms' ); ?></span>
                </td>
            </tr>
            <tr>
                <th><label for="location"><?php esc_html_e( 'Location', 'academy-lms' ); ?></label></th>
                <td>
                    <input type="text" name="location" id="location" value="<?php echo esc_attr( get_the_author_meta( 'location', $user->ID ) ); ?>" class="regular-text" /><br />
                    <span class="description"><?php esc_html_e( 'Please enter your location.', 'academy-lms' ); ?></span>
                </td>
            </tr>
            <tr>
                <th><label for="group"><?php esc_html_e( 'Group', 'academy-lms' ); ?></label></th>
                <td>
                    <input type="text" name="group" id="group" value="<?php echo esc_attr( get_the_author_meta( 'group', $user->ID ) ); ?>" class="regular-text" /><br />
                    <span class="description"><?php esc_html_e( 'Please enter your group.', 'academy-lms' ); ?></span>
                </td>
            </tr>
            <tr>
                <th><label for="avatar"><?php esc_html_e( 'Avatar', 'academy-lms' ); ?></label></th>
                <td>
                    <input type="hidden" name="avatar" id="avatar" value="<?php echo esc_attr( get_the_author_meta( 'avatar', $user->ID ) ); ?>" class="regular-text" />
                    <img id="avatar-preview" src="<?php echo esc_attr( get_the_author_meta( 'avatar', $user->ID ) ); ?>" style="max-width: 150px; display: block; margin-bottom: 10px;" />
                    <input type="button" id="upload-avatar-button" class="button" value="<?php esc_attr_e( 'Upload Avatar', 'academy-lms' ); ?>" />
                    <input type="button" id="remove-avatar-button" class="button" value="<?php esc_attr_e( 'Remove Avatar', 'academy-lms' ); ?>" />
                </td>
            </tr>
        </table>
        <?php
    }

    public function save_custom_user_profile_fields( $user_id ) {
        if ( ! current_user_can( 'edit_user', $user_id ) ) {
            return false;
        }

        update_user_meta( $user_id, 'position', sanitize_text_field( $_POST['position'] ) );
        update_user_meta( $user_id, 'manager', sanitize_text_field( $_POST['manager'] ) );
        update_user_meta( $user_id, 'location', sanitize_text_field( $_POST['location'] ) );
        update_user_meta( $user_id, 'group', sanitize_text_field( $_POST['group'] ) );
        update_user_meta( $user_id, 'avatar', esc_url_raw( $_POST['avatar'] ) );
    }
}
