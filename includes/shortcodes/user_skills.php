<?php
// Shortcode: [vulpes_user_skills]

function vulpes_user_skills_shortcode($atts)
{
    if (!is_user_logged_in()) {
        return '<p>You need to be logged in to view your skills.</p>';
    }

    global $wpdb;
    $user_id = get_current_user_id();

    // Fetch all assigned skills for the logged-in user, ordered by parent_skill and id
    $assigned_skills = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}vulpes_lms_skill_assignments WHERE employee_id = %d ORDER BY parent_skill ASC, id ASC",
        $user_id
    ));

    if (empty($assigned_skills)) {
        return '<p>You have no assigned skills.</p>';
    }

    // Hardcoded base URL for the images located in the plugin directory
    $image_base_url = '/wp-content/plugins/vulpes-lms/assets/imgs/progress-images/';

    // Start building the output
    ob_start();
?>
    <div class="vulpes-user-skills-container">
        <table class="vulpes-skill-table">
            <tbody>
                <?php
                foreach ($assigned_skills as $skill) {
                    if ($skill->is_parent === 'true') {
                        // Display parent skill with inline styles
                ?>
                        <tr class="parent-skill">
                            <td style="font-weight: bold; font-size: 1.2em; color: #333; padding-top: 20px; padding-bottom: 10px;">
                                <?php echo esc_html($skill->skill_name); ?>
                            </td>
                        </tr>
                        <?php

                        // Display child skills related to this parent skill
                        foreach ($assigned_skills as $child_skill) {
                            if ($child_skill->parent_skill === $skill->skill_name) {
                        ?>
                                <tr class="child-skill">
                                    <td style="padding-left: 20px;">
                                        — <?php echo esc_html($child_skill->skill_name); ?>
                                    </td>
                                </tr>
                                <tr class="child-skill">
                                    <td style="padding-left: 20px; padding-top: 0px;"> <!-- Reduced space above the image -->
                                        <img src="<?php echo esc_url($image_base_url . 'progress-' . intval($child_skill->level) . '.png'); ?>" alt="Level <?php echo esc_attr($child_skill->level); ?>" height="38px" width="500px" style="margin-bottom: 0px;" />
                                    </td>
                                </tr>
                <?php
                            }
                        }
                    }
                }

                // Display standalone skills
                ?>
                <tr class="parent-skill">
                    <td style="font-weight: bold; font-size: 1.2em; color: #333; padding-top: 20px; padding-bottom: 10px;">
                        Standalone
                    </td>
                </tr>
                <?php
                foreach ($assigned_skills as $skill) {
                    if ($skill->parent_skill === 'Standalone') {
                ?>
                        <tr class="child-skill">
                            <td style="padding-left: 20px;">— <?php echo esc_html($skill->skill_name); ?></td>
                        </tr>
                        <tr class="child-skill">
                            <td style="padding-left: 20px;">
                                <img src="<?php echo esc_url($image_base_url . 'progress-' . intval($skill->level) . '.png'); ?>" alt="Level <?php echo esc_attr($skill->level); ?>" height="38px" width="500px" />
                            </td>
                        </tr>
                <?php
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
<?php
    return ob_get_clean();
}

add_shortcode('vulpes_user_skills', 'vulpes_user_skills_shortcode');
