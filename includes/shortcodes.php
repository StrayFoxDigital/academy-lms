<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Vulpes_LMS_Shortcodes {

    public function __construct() {
        add_shortcode( 'vulpes_user_profile', array( $this, 'user_profile_shortcode' ) );
        add_shortcode( 'vulpes_user_training_log', array( $this, 'user_training_log_shortcode' ) );
        add_shortcode( 'vulpes_user_enrolled_courses', array( $this, 'user_enrolled_courses_shortcode' ) );
        add_shortcode( 'vulpes_user_learning_path_scores', array( $this, 'user_learning_path_scores_shortcode' ) );
        add_shortcode( 'vulpes_full_training_log', array( $this, 'full_training_log_shortcode' ) );
        add_shortcode( 'vulpes_all_groups', array( $this, 'all_groups_shortcode' ) );
        add_shortcode( 'vulpes_my_team', array( $this, 'my_team_shortcode' ) ); // New shortcode

        // Enqueue shortcode styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
    }

    public function enqueue_styles() {
        wp_enqueue_style( 'vulpes-lms-shortcodes', plugin_dir_url( __FILE__ ) . '../assets/css/shortcodes.css', array(), '1.0', 'all' );
    }

    public function user_profile_shortcode() {
        if ( ! is_user_logged_in() ) {
            return '<p>You need to be logged in to view your profile.</p>';
        }

        $user_id = get_current_user_id();
        $user = get_userdata( $user_id );

        $manager_id = get_user_meta( $user_id, 'manager', true );
        $manager = get_userdata( $manager_id );
        $manager_name = $manager ? $manager->display_name : 'N/A';

        ob_start();
        ?>
        <div class="vulpes-user-profile vulpes-lms-shortcodes">
            <div class="user-avatar">
                <?php echo get_avatar( $user_id, 96 ); ?>
            </div>
            <div class="user-info">
                <h2><?php echo esc_html( $user->display_name ); ?></h2>
                <h4><?php echo esc_html( $user->user_email ); ?></h4>
                <hr />
                <p><strong>Position:</strong> <?php echo esc_html( get_user_meta( $user_id, 'position', true ) ); ?></br>
                <strong>Manager:</strong> <?php echo esc_html( $manager_name ); ?></br>
                <strong>Group:</strong> <?php echo esc_html( get_user_meta( $user_id, 'group', true ) ); ?></p>
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
        <div class="vulpes-lms-shortcodes">
            <table>
                <thead>
                    <tr>
                        <th>Course Name</th>
                        <th>Completed Date</th>
                        <th>Expiry Date</th>
                        <th>View Files</th>
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
                                    <a href="<?php echo esc_url( $log->uploads ); ?>" target="_blank">View Files</a>
                                <?php else : ?>
                                    <span>No Files</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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
        <div class="vulpes-lms-shortcodes">
            <table>
                <thead>
                    <tr>
                        <th>Course Name</th>
                        <th>Date Enrolled</th>
                        <th>Status</th>
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
        </div>
        <?php
        return ob_get_clean();
    }

    private function get_level_from_score( $score ) {
        if ( $score >= 1 && $score <= 20 ) {
            return 'Beginner';
        } elseif ( $score >= 21 && $score <= 40 ) {
            return 'Novice';
        } elseif ( $score >= 41 && $score <= 60 ) {
            return 'Competent';
        } elseif ( $score >= 61 && $score <= 80 ) {
            return 'Proficient';
        } elseif ( $score >= 81 && $score <= 100 ) {
            return 'Expert';
        } else {
            return 'Unknown';
        }
    }

    public function user_learning_path_scores_shortcode() {
        if ( ! is_user_logged_in() ) {
            return '<p>You need to be logged in to view your learning path scores.</p>';
        }

        global $wpdb;
        $user_id = get_current_user_id();
        $learning_paths_table = $wpdb->prefix . 'vulpes_lms_learning_paths';
        $courses_table = $wpdb->prefix . 'vulpes_lms_courses';
        $training_log_table = $wpdb->prefix . 'vulpes_lms_training_log';
        $course_assignments_table = $wpdb->prefix . 'vulpes_lms_course_assignments';

        // Fetch all learning paths
        $learning_paths = $wpdb->get_results( "SELECT * FROM $learning_paths_table" );

        // Array to store learning path scores
        $learning_path_scores = [];

        // Calculate scores for each learning path
        foreach ( $learning_paths as $learning_path ) {
            $learning_path_name = $learning_path->learning_path_name;

            // Get all courses in this learning path
            $courses = $wpdb->get_results( $wpdb->prepare( "SELECT id, competency_score FROM $courses_table WHERE learning_path = %s", $learning_path_name ) );

            $total_score = 0;
            $total_achievable_score = 0;

            foreach ( $courses as $course ) {
                $course_id = $course->id;
                $competency_score = $course->competency_score;

                // Check if the user has completed this course
                $completed_course = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $training_log_table WHERE employee_id = %d AND course_name = (SELECT course_name FROM $courses_table WHERE id = %d)", $user_id, $course_id ) );

                if ( $completed_course ) {
                    $total_score += $competency_score;
                }

                $total_achievable_score += $competency_score;
            }

            // Check if the user is enrolled in any courses of this learning path
            $enrolled_courses = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $course_assignments_table WHERE employee_id = %d AND course_id IN (SELECT id FROM $courses_table WHERE learning_path = %s)", $user_id, $learning_path_name ) );

            if ( $total_score > 0 || ! empty( $enrolled_courses ) ) {
                $learning_path_scores[] = array(
                    'learning_path_name' => $learning_path_name,
                    'total_score' => $total_score,
                    'total_achievable_score' => $total_achievable_score,
                    'level' => $this->get_level_from_score( $total_score )
                );
            }
        }

        ob_start();
        ?>
        <div class="vulpes-lms-shortcodes">
            <table>
                <thead>
                    <tr>
                        <th>Learning Path</th>
                        <th>Score</th>
                        <th>Level</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( ! empty( $learning_path_scores ) ) : ?>
                        <?php foreach ( $learning_path_scores as $learning_path_score ) : ?>
                            <tr>
                                <td><?php echo esc_html( $learning_path_score['learning_path_name'] ); ?></td>
                                <td><?php echo esc_html( $learning_path_score['total_score'] . ' / ' . $learning_path_score['total_achievable_score'] ); ?></td>
                                <td><?php echo esc_html( $learning_path_score['level'] ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="3">No scores found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
        return ob_get_clean();
    }

    public function full_training_log_shortcode() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vulpes_lms_training_log';

        $training_logs = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY date_completed DESC" );

        if ( empty( $training_logs ) ) {
            return '<p>No training records found.</p>';
        }

        ob_start();
        ?>
        <div class="vulpes-lms-shortcodes">
            <table>
                <thead>
                    <tr>
                        <th>Employee Name</th>
                        <th>Course Name</th>
                        <th>Date Completed</th>
                        <th>Expiry Date</th>
                        <th>Status</th>
                        <th>View Files</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $training_logs as $log ) : ?>
                        <?php
                            $status = 'Complete';
                            $expiry_date = strtotime( $log->expiry_date );
                            $current_date = time();
                            $days_to_expiry = ( $expiry_date - $current_date ) / ( 60 * 60 * 24 );

                            if ( $days_to_expiry <= 0 ) {
                                $status = 'EXPIRED';
                            } elseif ( $days_to_expiry <= 30 ) {
                                $status = 'Due to Expire';
                            }
                        ?>
                        <tr>
                            <td><?php echo esc_html( $log->employee_name ); ?></td>
                            <td><?php echo esc_html( $log->course_name ); ?></td>
                            <td><?php echo esc_html( date( 'd-m-Y', strtotime( $log->date_completed ) ) ); ?></td>
                            <td><?php echo esc_html( date( 'd-m-Y', strtotime( $log->expiry_date ) ) ); ?></td>
                            <td><?php echo esc_html( $status ); ?></td>
                            <td>
                                <?php if ( $log->uploads ) : ?>
                                    <a href="<?php echo esc_url( $log->uploads ); ?>" target="_blank">View Files</a>
                                <?php else : ?>
                                    <span>No Files</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
        return ob_get_clean();
    }

    public function all_groups_shortcode() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vulpes_lms_groups';

        $groups = $wpdb->get_results( "SELECT * FROM $table_name" );

        if ( empty( $groups) ) {
            return '<p>No groups found.</p>';
        }

        ob_start();
        ?>
        <div class="vulpes-lms-shortcodes">
            <table>
                <thead>
                    <tr>
                        <th>Group Name</th>
                        <th>Manager</th>
                        <th>Assigned Employees</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $groups as $group ) : ?>
                        <tr>
                            <td><?php echo esc_html( $group->group_name ); ?></td>
                            <td>
                                <?php 
                                $manager = get_userdata( $group->manager );
                                echo esc_html( $manager ? $manager->display_name : 'Manager not found' );
                                ?>
                            </td>
                            <td><?php echo esc_html( count( get_users( array( 'meta_key' => 'group', 'meta_value' => $group->group_name ) ) ) ); ?></td>
                            <td>
                                <a href="#">Manage</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
        return ob_get_clean();
    }

    public function my_team_shortcode() {
        if ( ! is_user_logged_in() ) {
            return '<p>You need to be logged in to view your team.</p>';
        }

        global $wpdb;
        $user_id = get_current_user_id();
        $users = get_users( array(
            'meta_key' => 'manager',
            'meta_value' => $user_id,
        ) );

        if ( empty( $users ) ) {
            return '<p>You have no team members.</p>';
        }

        ob_start();
        ?>
        <div class="vulpes-lms-shortcodes">
            <table>
                <thead>
                    <tr>
                        <th>Display Name</th>
                        <th>Position</th>
                        <th>Manager</th>
                        <th>Group</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $users as $user ) : ?>
                        <tr>
                            <td><?php echo esc_html( $user->display_name ); ?></td>
                            <td><?php echo esc_html( get_user_meta( $user->ID, 'position', true ) ); ?></td>
                            <td>
                                <?php 
                                $manager_id = get_user_meta( $user->ID, 'manager', true );
                                $manager = get_userdata( $manager_id );
                                echo esc_html( $manager ? $manager->display_name : 'N/A' );
                                ?>
                            </td>
                            <td><?php echo esc_html( get_user_meta( $user->ID, 'group', true ) ); ?></td>
                            <td>
                                <a href="#">Manage</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
        return ob_get_clean();
    }
}

new Vulpes_LMS_Shortcodes();
