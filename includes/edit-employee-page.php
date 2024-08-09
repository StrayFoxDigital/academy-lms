<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $wpdb;
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if (!$user_id) {
    echo '<div class="error"><p>Employee not found.</p></div>';
    return;
}

$user = get_userdata($user_id);

if (!$user) {
    echo '<div class="error"><p>Employee not found.</p></div>';
    return;
}

// Function to set custom upload directory
function vulpes_lms_custom_upload_directory($dirs)
{
    $dirs['subdir'] = '/vulpes-lms-uploads' . $dirs['subdir'];
    $dirs['path'] = $dirs['basedir'] . $dirs['subdir'];
    $dirs['url'] = $dirs['baseurl'] . $dirs['subdir'];
    return $dirs;
}

// Handle form submission for updating employee details
if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['position']) && isset($_POST['manager']) && isset($_POST['location']) && isset($_POST['role'])) {
    $first_name = sanitize_text_field($_POST['first_name']);
    $last_name = sanitize_text_field($_POST['last_name']);
    $position = sanitize_text_field($_POST['position']);
    $manager = intval($_POST['manager']);
    $location = sanitize_text_field($_POST['location']);
    $role = sanitize_text_field($_POST['role']);

    // Update user data
    wp_update_user(
        array(
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'role' => $role,
        )
    );

    // Update user meta
    update_user_meta($user_id, 'position', $position);
    update_user_meta($user_id, 'manager', $manager);
    update_user_meta($user_id, 'location', $location);

    echo '<div class="updated"><p>Employee updated successfully.</p></div>';

    // Refresh user data
    $user = get_userdata($user_id);
}

// Handle form submission for adding a new training record
if (isset($_POST['course_id']) && isset($_POST['date_completed'])) {
    $course_id = intval($_POST['course_id']);
    $date_completed = sanitize_text_field($_POST['date_completed']);

    // Handle file upload
    if (isset($_FILES['training_document']) && !empty($_FILES['training_document']['name'])) {
        if (!function_exists('wp_handle_upload')) {
            require_once (ABSPATH . 'wp-admin/includes/file.php');
        }

        $uploaded_file = $_FILES['training_document'];
        $upload_overrides = array('test_form' => false);

        // Set the upload directory
        add_filter('upload_dir', 'vulpes_lms_custom_upload_directory');

        $movefile = wp_handle_upload($uploaded_file, $upload_overrides);

        // Remove the custom upload directory filter after upload
        remove_filter('upload_dir', 'vulpes_lms_custom_upload_directory');

        if ($movefile && !isset($movefile['error'])) {
            $file_url = $movefile['url'];
        } else {
            $file_url = '';
        }
    } else {
        $file_url = '';
    }

    // Get course name
    $course = $wpdb->get_row($wpdb->prepare("SELECT course_name, expiry_duration FROM {$wpdb->prefix}vulpes_lms_courses WHERE id = %d", $course_id));

    if ($course) {
        $expiry_date = date('Y-m-d', strtotime($date_completed . ' + ' . $course->expiry_duration . ' days'));

        $wpdb->insert(
            $wpdb->prefix . 'vulpes_lms_training_log',
            array(
                'employee_id' => $user_id,
                'employee_name' => $user->first_name . ' ' . $user->last_name,
                'course_name' => $course->course_name,
                'date_completed' => date('Y-m-d', strtotime($date_completed)),
                'expiry_date' => $expiry_date,
                'uploads' => $file_url,
            )
        );

        // Delete the course enrollment record
        $wpdb->delete(
            $wpdb->prefix . 'vulpes_lms_course_assignments',
            array(
                'employee_id' => $user_id,
                'course_id' => $course_id,
            )
        );

        echo '<div class="updated"><p>Training record added and course enrollment deleted successfully.</p></div>';
    }
}

// Handle form submission for enrolling in a course
if (isset($_POST['enroll_course_id'])) {
    $course_id = intval($_POST['enroll_course_id']);
    $course = $wpdb->get_row($wpdb->prepare("SELECT course_name FROM {$wpdb->prefix}vulpes_lms_courses WHERE id = %d", $course_id));

    if ($course) {
        $wpdb->insert(
            $wpdb->prefix . 'vulpes_lms_course_assignments',
            array(
                'employee_id' => $user_id,
                'employee_name' => $user->first_name . ' ' . $user->last_name,
                'course_id' => $course_id,
                'course_name' => $course->course_name,
                'date_enrolled' => current_time('mysql'),
                'status' => 'enrolled'
            )
        );

        // Trigger the course enrollment email notification
        do_action('vulpes_lms_course_enrolled', $user_id, $course->course_name);

        echo '<div class="updated"><p>Course enrolled successfully.</p></div>';
    }
}

// Handle form submission for enrolling in a learning path
if (isset($_POST['enroll_learning_path_id'])) {
    $learning_path_id = intval($_POST['enroll_learning_path_id']);
    $learning_path = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}vulpes_lms_learning_paths WHERE id = %d", $learning_path_id));

    if ($learning_path) {
        $courses = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}vulpes_lms_courses WHERE learning_path = %s", $learning_path->learning_path_name));

        foreach ($courses as $course) {
            $wpdb->insert(
                $wpdb->prefix . 'vulpes_lms_course_assignments',
                array(
                    'employee_id' => $user_id,
                    'employee_name' => $user->first_name . ' ' . $user->last_name,
                    'course_id' => $course->id,
                    'course_name' => $course->course_name,
                    'date_enrolled' => current_time('mysql'),
                    'status' => 'enrolled'
                )
            );

            // Trigger the course enrollment email notification for each course
            do_action('vulpes_lms_course_enrolled', $user_id, $course->course_name);
        }

        echo '<div class="updated"><p>Learning path enrolled successfully.</p></div>';
    }
}

// Handle unenroll action
if (isset($_GET['unenroll']) && isset($_GET['course_assignment_id'])) {
    $course_assignment_id = intval($_GET['course_assignment_id']);
    $wpdb->delete($wpdb->prefix . 'vulpes_lms_course_assignments', array('id' => $course_assignment_id));

    echo '<div class="updated"><p>Course unenrolled successfully.</p></div>';
}

// Fetch all users for the manager dropdown
$allowed_roles = array('administrator', 'editor', 'author'); // Editor is now Superuser, Author is now Manager
$managers = get_users(
    array(
        'role__in' => $allowed_roles,
    )
);

// Fetch all courses for the course dropdown
$courses = $wpdb->get_results("SELECT id, course_name FROM {$wpdb->prefix}vulpes_lms_courses");

// Fetch all learning paths for the learning path dropdown
$learning_paths = $wpdb->get_results("SELECT id, learning_path_name FROM {$wpdb->prefix}vulpes_lms_learning_paths");

// Fetch employee's training records
$training_logs = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}vulpes_lms_training_log WHERE employee_id = %d ORDER BY date_completed DESC", $user_id));

// Fetch employee's course enrollments
$course_enrollments = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}vulpes_lms_course_assignments WHERE employee_id = %d ORDER BY date_enrolled DESC", $user_id));

// Handle form submission for assigning skills
if (isset($_POST['skill_id'])) {
    $skill_id = intval($_POST['skill_id']);
    $skill = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}vulpes_lms_skills WHERE id = %d", $skill_id));

    if ($skill) {
        $wpdb->insert(
            $wpdb->prefix . 'vulpes_lms_skill_assignments',
            array(
                'skill_name' => $skill->skill_name,
                'is_parent' => $skill->is_parent,
                'employee_id' => $user_id,
                'level' => 0,
                'type' => 'Assigned',
            )
        );

        // If the skill is a parent, assign all child skills as well
        if ($skill->is_parent === 'yes') {
            $child_skills = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}vulpes_lms_skills WHERE parent_skill = %s",
                    $skill->skill_name
                )
            );

            foreach ($child_skills as $child_skill) {
                $wpdb->insert(
                    $wpdb->prefix . 'vulpes_lms_skill_assignments',
                    array(
                        'skill_name' => $child_skill->skill_name,
                        'is_parent' => $child_skill->is_parent,
                        'employee_id' => $user_id,
                        'level' => 0,
                        'type' => 'Assigned',
                    )
                );
            }
        }

        echo '<div class="updated"><p>Skill(s) assigned successfully.</p></div>';
    }
}

?>

<div class="wrap">
    <h1>Edit Employee</h1>
    <a href="<?php echo admin_url('admin.php?page=vulpes-lms-employees'); ?>" class="button">Back to Employees</a>
    <h2 class="nav-tab-wrapper">
        <a href="#details" class="nav-tab nav-tab-active">Details</a>
        <a href="#training-records" class="nav-tab">Training Records</a>
        <a href="#enrollment" class="nav-tab">Enrollment</a>
        <a href="#skills" class="nav-tab">Skills</a> <!-- Added new tab here -->
    </h2>

    <div id="details" class="tab-content">
        <form method="post" action="" enctype="multipart/form-data">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="first_name">First Name</label></th>
                    <td><input type="text" id="first_name" name="first_name" class="regular-text"
                            value="<?php echo esc_attr($user->first_name); ?>" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="last_name">Last Name</label></th>
                    <td><input type="text" id="last_name" name="last_name" class="regular-text"
                            value="<?php echo esc_attr($user->last_name); ?>" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="position">Position</label></th>
                    <td><input type="text" id="position" name="position" class="regular-text"
                            value="<?php echo esc_attr(get_user_meta($user_id, 'position', true)); ?>" required />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="manager">Manager</label></th>
                    <td>
                        <select id="manager" name="manager" required>
                            <option value="">Select a Manager</option>
                            <?php foreach ($managers as $manager_user): ?>
                                <option value="<?php echo esc_attr($manager_user->ID); ?>" <?php selected($manager_user->ID, get_user_meta($user_id, 'manager', true)); ?>>
                                    <?php echo esc_html($manager_user->display_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="location">Location</label></th>
                    <td><input type="text" id="location" name="location" class="regular-text"
                            value="<?php echo esc_attr(get_user_meta($user_id, 'location', true)); ?>" required />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="role">Role</label></th>
                    <td>
                        <select id="role" name="role" required>
                            <option value="administrator" <?php selected('administrator', $user->roles[0]); ?>>
                                Administrator</option>
                            <option value="editor" <?php selected('editor', $user->roles[0]); ?>>Superuser</option>
                            <option value="author" <?php selected('author', $user->roles[0]); ?>>Manager</option>
                            <option value="subscriber" <?php selected('subscriber', $user->roles[0]); ?>>Employee
                            </option>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button('Update Employee'); ?>
        </form>
    </div>

    <div id="training-records" class="tab-content" style="display: none;">
        <h2>Add Training Record</h2>
        <form method="post" action="" enctype="multipart/form-data">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="course_id">Course</label></th>
                    <td>
                        <select id="course_id" name="course_id" required>
                            <option value="">Select a Course</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo esc_attr($course->id); ?>">
                                    <?php echo esc_html($course->course_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="date_completed">Date Completed</label></th>
                    <td><input type="date" id="date_completed" name="date_completed" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="training_document">Training Document (optional)</label></th>
                    <td><input type="file" id="training_document" name="training_document" /></td>
                </tr>
            </table>
            <?php submit_button('Add Training Record'); ?>
        </form>

        <h2>Training Records</h2>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Course Name</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Date Completed</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Expiry Date</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Status</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($training_logs)): ?>
                    <?php foreach ($training_logs as $log): ?>
                        <?php
                        $status = 'Complete';
                        $expiry_date = strtotime($log->expiry_date);
                        $current_date = time();
                        $days_to_expiry = ($expiry_date - $current_date) / (60 * 60 * 24);

                        if ($days_to_expiry <= 0) {
                            $status = 'EXPIRED';
                        } elseif ($days_to_expiry <= 30) {
                            $status = 'Due to Expire';
                        }
                        ?>
                        <tr>
                            <td><?php echo esc_html($log->course_name); ?></td>
                            <td><?php echo esc_html(date('d-m-Y', strtotime($log->date_completed))); ?></td>
                            <td><?php echo esc_html(date('d-m-Y', strtotime($log->expiry_date))); ?></td>
                            <td><?php echo esc_html($status); ?></td>
                            <td>
                                <?php if ($log->uploads): ?>
                                    <a href="<?php echo esc_url($log->uploads); ?>" class="button" target="_blank">View Files</a>
                                <?php else: ?>
                                    <a href="#" class="button disabled" aria-disabled="true">View Files</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No training records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div id="enrollment" class="tab-content" style="display: none;">
        <h2>Enroll in a Course</h2>
        <form method="post" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="enroll_course_id">Course</label></th>
                    <td>
                        <select id="enroll_course_id" name="enroll_course_id" required>
                            <option value="">Select a Course</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo esc_attr($course->id); ?>">
                                    <?php echo esc_html($course->course_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button('Enroll'); ?>
        </form>

        <h2>Enroll in a Learning Path</h2>
        <form method="post" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="enroll_learning_path_id">Learning Path</label></th>
                    <td>
                        <select id="enroll_learning_path_id" name="enroll_learning_path_id" required>
                            <option value="">Select a Learning Path</option>
                            <?php foreach ($learning_paths as $learning_path): ?>
                                <option value="<?php echo esc_attr($learning_path->id); ?>">
                                    <?php echo esc_html($learning_path->learning_path_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button('Enroll'); ?>
        </form>

        <h2>Enrolled Courses</h2>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Course Name</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Enrolled Date</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($course_enrollments)): ?>
                    <?php foreach ($course_enrollments as $enrollment): ?>
                        <tr>
                            <td><?php echo esc_html($enrollment->course_name); ?></td>
                            <td><?php echo esc_html(date('d-m-Y', strtotime($enrollment->date_enrolled))); ?></td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=vulpes-lms-edit-employee&user_id=' . $user_id . '&unenroll=1&course_assignment_id=' . $enrollment->id); ?>"
                                    class="button">Unenroll</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">No enrolled courses found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- New Skills Tab Content -->
    <div id="skills" class="tab-content" style="display: none;">
        <h2>Assign Skills</h2>
        <form method="post" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="skill_id">Skill</label></th>
                    <td>
                        <select id="skill_id" name="skill_id" required>
                            <option value="">Select a Skill</option>
                            <?php
                            // Fetch all skills that are not already assigned to the user
                            $assigned_skills = $wpdb->get_col(
                                $wpdb->prepare(
                                    "SELECT skill_name FROM {$wpdb->prefix}vulpes_lms_skill_assignments WHERE employee_id = %d",
                                    $user_id
                                )
                            );

                            $skills = $wpdb->get_results("SELECT id, skill_name FROM {$wpdb->prefix}vulpes_lms_skills_list");

                            foreach ($skills as $skill) {
                                if (!in_array($skill->skill_name, $assigned_skills)) {
                                    echo '<option value="' . esc_attr($skill->id) . '">' . esc_html($skill->skill_name) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button('Assign Skill'); ?>
        </form>

        <h2>Assigned Skills</h2>
        <form method="post" action="">
            <table class="widefat fixed" cellspacing="0">
                <thead>
                    <tr>
                        <th id="columnname" class="manage-column column-columnname" scope="col">Skill Name</th>
                        <th id="columnname" class="manage-column column-columnname" scope="col">Level</th>
                        <th id="columnname" class="manage-column column-columnname" scope="col">Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch all assigned skills, ordered by parent_skill and skill_name
                    $assigned_skills = $wpdb->get_results(
                        $wpdb->prepare(
                            "SELECT * FROM {$wpdb->prefix}vulpes_lms_skill_assignments WHERE employee_id = %d ORDER BY parent_skill ASC, id ASC",
                            $user_id
                        )
                    );

                    if (!empty($assigned_skills)):
                        // First, display parent skills and their children
                        foreach ($assigned_skills as $parent_skill):
                            if ($parent_skill->is_parent === 'true' || empty($parent_skill->parent_skill)):
                                // Calculate the total and earned points for child skills
                                $child_total_points = 0;
                                $child_earned_points = 0;
                                $child_count = 0;

                                foreach ($assigned_skills as $child_skill) {
                                    if ($child_skill->parent_skill === $parent_skill->skill_name) {
                                        $child_earned_points += $child_skill->level;
                                        $child_total_points += 6;
                                        $child_count++;
                                    }
                                }

                                $parent_level_display = $child_count > 0 ? "$child_earned_points / $child_total_points" : '0 / 0';
                                ?>
                                <tr style="font-weight: bold;">
                                    <td><?php echo esc_html($parent_skill->skill_name); ?></td>
                                    <td><?php echo $parent_level_display; ?></td>
                                    <td><?php echo esc_html($parent_skill->type); ?></td>
                                </tr>

                                <?php
                                // Display child skills related to this parent skill
                                foreach ($assigned_skills as $child_skill):
                                    if ($child_skill->parent_skill === $parent_skill->skill_name): ?>
                                        <tr>
                                            <td style="padding-left: 20px;">— <?php echo esc_html($child_skill->skill_name); ?></td>
                                            <td>
                                                <input type="number" name="child_skill_levels[<?php echo $child_skill->id; ?>]"
                                                    value="<?php echo esc_attr($child_skill->level); ?>" min="0" max="6" />
                                            </td>
                                            <td><?php echo esc_html($child_skill->type); ?></td>
                                        </tr>
                                    <?php endif;
                                endforeach;

                            endif;
                        endforeach;

                        // Now display the "Standalone" category and its skills
                        ?>
                        <tr style="font-weight: bold;">
                            <td>Standalone</td>
                            <td></td> <!-- No level calculation for Standalone -->
                            <td></td>
                        </tr>
                        <?php

                        foreach ($assigned_skills as $skill):
                            if ($skill->parent_skill === 'Standalone'): ?>
                                <tr>
                                    <td style="padding-left: 20px;">— <?php echo esc_html($skill->skill_name); ?></td>
                                    <td>
                                        <input type="number" name="child_skill_levels[<?php echo $skill->id; ?>]"
                                            value="<?php echo esc_attr($skill->level); ?>" min="0" max="6" />
                                    </td>
                                    <td><?php echo esc_html($skill->type); ?></td>
                                </tr>
                            <?php endif;
                        endforeach;

                    else: ?>
                        <tr>
                            <td colspan="3">No skills assigned.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php submit_button('Save Skill Levels'); ?>
        </form>

        <?php
        // Handle form submission for saving skill levels
        if (isset($_POST['child_skill_levels'])) {
            foreach ($_POST['child_skill_levels'] as $child_skill_id => $new_level) {
                $new_level = intval($new_level);

                // Update the level in the database
                $wpdb->update(
                    $wpdb->prefix . 'vulpes_lms_skill_assignments',
                    array('level' => $new_level),
                    array('id' => intval($child_skill_id)),
                    array('%d'),
                    array('%d')
                );
            }

            echo '<div class="updated"><p>Skill levels updated successfully.</p></div>';
        }
        ?>
    </div>

    <?php
    // Handle form submission for assigning skills
    if (isset($_POST['skill_id'])) {
        $skill_id = intval($_POST['skill_id']);

        // Fetch the selected skill details from the skills list
        $skill = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}vulpes_lms_skills_list WHERE id = %d", $skill_id));

        if ($skill) {
            // Insert the selected skill into the skill assignments table
            $wpdb->insert(
                $wpdb->prefix . 'vulpes_lms_skill_assignments',
                array(
                    'skill_name' => $skill->skill_name,
                    'is_parent' => $skill->is_parent,
                    'parent_skill' => $skill->parent_skill,  // Insert parent_skill
                    'employee_id' => $user_id,
                    'level' => 0,
                    'type' => 'Assigned',
                )
            );

            // If the skill is a parent, assign all child skills as well
            if ($skill->is_parent === 'true') {
                // Fetch all child skills where the parent_skill matches the current skill's skill_name
                $child_skills = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}vulpes_lms_skills_list WHERE parent_skill = %s",
                        $skill->skill_name
                    )
                );

                foreach ($child_skills as $child_skill) {
                    // Ensure the child skill isn't already assigned to avoid duplicates
                    $already_assigned = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT COUNT(*) FROM {$wpdb->prefix}vulpes_lms_skill_assignments WHERE skill_name = %s AND employee_id = %d",
                            $child_skill->skill_name,
                            $user_id
                        )
                    );

                    if (!$already_assigned) {
                        $wpdb->insert(
                            $wpdb->prefix . 'vulpes_lms_skill_assignments',
                            array(
                                'skill_name' => $child_skill->skill_name,
                                'is_parent' => $child_skill->is_parent,
                                'parent_skill' => $child_skill->parent_skill,  // Insert parent_skill
                                'employee_id' => $user_id,
                                'level' => 0,
                                'type' => 'Assigned',
                            )
                        );
                    }
                }
            }

            echo '<div class="updated"><p>Skill(s) assigned successfully.</p></div>';
        }
    }
    ?>

    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('.nav-tab').click(function (e) {
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