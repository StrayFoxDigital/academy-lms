<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Vulpes_LMS_Shortcodes {

    public function __construct() {
        add_shortcode( 'vulpes_user_profile', array( $this, 'user_profile_shortcode' ) );
        add_shortcode( 'vulpes_user_training_log', array( $this, 'user_training_log_shortcode' ) );
        add_shortcode( 'vulpes_user_enrolled_courses', array( $this, 'user_enrolled_courses_shortcode' ) );
    }

    public function user_profile_shortcode() {
        if ( ! is_user_logged_in() ) {
            return '<p>You need to be logged in to view your profile.</p>';
        }

        $user_id = get_current_user_id();
        $user = get_userdata( $user_id );

        ob_start();
        ?>
        <div class="vulpes-user-profile">
            <div class="user-avatar">
                <?php echo get_avatar( $user_id, 96 ); ?>
            </div>
            <div class="user-info">
                <p><strong><?php echo esc_html( $user->display_name ); ?></strong></p>
                <p><?php echo esc_html( $user->user_email ); ?></p>
                <hr />
                <p><strong>Position:</strong> <?php echo esc_html( get_user_meta( $user_id, 'position', true ) ); ?></p>
                <p><strong>Manager:</strong> <?php echo esc_html( get_user_meta( $user_id, 'manager', true ) ); ?></p>
                <p><strong>Group:</strong> <?php echo esc_html( get_user_meta( $user_id, 'group', true ) ); ?></p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function user_training_log_shortcode() {
        if ( ! is_user_logged_in() ) {
            return '<p>You need to be logged in to view your training log.</p>';
        }

        global $wpdb;
        $user_id = get_current_user_id();
        $table_name = $wpdb->prefix . 'vulpes_lms_training_log';
        
        $training_logs = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE employee_id = %d ORDER BY date_completed DESC", $user_id ) );

        if ( empty( $training_logs ) ) {
            return '<p>You have no training records.</p>';
        }

        ob_start();
        ?>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Course Name</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Completed Date</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Expiry Date</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">View Files</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $training_logs as $log ) : ?>
                    <tr>
                        <td><?php echo esc_html( $log->course_name ); ?></td>
                        <td><?php echo esc_html( date( 'd-m-Y', strtotime( $log->date_completed ) ) ); ?></td>
                        <td><?php echo esc_html( date( 'd-m-Y', strtotime( $log->expiry_date ) ) ); ?></td>
                        <td>
                            <?php if ( $log->uploads ) : ?>
                                <a href="<?php echo esc_url( $log->uploads ); ?>" class="button" target="_blank">View Files</a>
                            <?php else : ?>
                                <a href="#" class="button disabled" aria-disabled="true">No Files</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }

    public function user_enrolled_courses_shortcode() {
        if ( ! is_user_logged_in() ) {
            return '<p>You need to be logged in to view your enrolled courses.</p>';
        }

        global $wpdb;
        $user_id = get_current_user_id();
        $table_name = $wpdb->prefix . 'vulpes_lms_course_assignments';
        
        $enrolled_courses = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE employee_id = %d ORDER BY date_enrolled DESC", $user_id ) );

        if ( empty( $enrolled_courses ) ) {
            return '<p>You are not enrolled in any courses.</p>';
        }

        ob_start();
        ?>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Course Name</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Date Enrolled</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $enrolled_courses as $course ) : ?>
                    <tr>
                        <td><?php echo esc_html( $course->course_name ); ?></td>
                        <td><?php echo esc_html( date( 'd-m-Y', strtotime( $course->date_enrolled ) ) ); ?></td>
                        <td><?php echo esc_html( ucfirst( $course->status ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }
}

new Vulpes_LMS_Shortcodes();
