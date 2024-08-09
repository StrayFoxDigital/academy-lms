<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $wpdb;
$skills_table = $wpdb->prefix . 'vulpes_lms_skills_list';

// Handle form submission for adding a skill
if (isset($_POST['skill_name'])) {
    $skill_name = sanitize_text_field($_POST['skill_name']);
    $is_parent = isset($_POST['is_parent']) ? 'true' : 'false';

    // Adjust parent_skill value based on whether the skill is a parent
    if ($is_parent === 'true') {
        $parent_skill = ''; // Set to blank if it's a parent skill
    } else {
        $parent_skill = sanitize_text_field($_POST['parent_skill']);
        if ($parent_skill === '') {
            $parent_skill = 'Standalone'; // Set to "Standalone" if no parent is selected
        }
    }

    $description = sanitize_textarea_field($_POST['description']);

    $wpdb->insert(
        $skills_table,
        array(
            'skill_name' => $skill_name,
            'parent_skill' => $parent_skill,
            'is_parent' => $is_parent,
            'description' => $description
        )
    );

    echo '<div class="updated"><p>Skill added successfully.</p></div>';
}

// Handle skill deletion
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['skill_id'])) {
    $skill_id = intval($_GET['skill_id']);
    $skill = $wpdb->get_row($wpdb->prepare("SELECT * FROM $skills_table WHERE id = %d", $skill_id));

    if ($skill) {
        $wpdb->delete($skills_table, array('id' => $skill_id));
        echo '<div class="updated"><p>Skill deleted successfully.</p></div>';
    } else {
        echo '<div class="error"><p>Skill not found.</p></div>';
    }
}

// Fetch all skills
$skills = $wpdb->get_results("SELECT * FROM $skills_table");

?>

<div class="wrap">
    <h1>Skills Management</h1>
    <h2 class="nav-tab-wrapper">
        <a href="#skills" class="nav-tab nav-tab-active">Skills List</a>
        <a href="#add-new" class="nav-tab">Add New</a>
    </h2>

    <div id="skills" class="tab-content">
        <h2>Existing Skills</h2>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th>Skill Name</th>
                    <th>Parent Skill</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($skills)):
                    // First, display parent skills and their children
                    foreach ($skills as $parent_skill):
                        if ($parent_skill->is_parent === 'true' || empty($parent_skill->parent_skill)):
                            ?>
                            <tr style="font-weight: bold;">
                                <td><?php echo esc_html($parent_skill->skill_name); ?></td>
                                <td><?php echo esc_html($parent_skill->parent_skill); ?></td>
                                <td><?php echo esc_html($parent_skill->description); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=vulpes-lms-manage-skill&skill_id=' . $parent_skill->id); ?>"
                                        class="button">Manage</a>
                                    <a href="<?php echo admin_url('admin.php?page=vulpes-lms-skills&action=delete&skill_id=' . $parent_skill->id); ?>"
                                        class="button"
                                        onclick="return confirm('Are you sure you want to delete this skill?');">Delete</a>
                                </td>
                            </tr>

                            <?php
                            // Display child skills related to this parent skill
                            foreach ($skills as $child_skill):
                                if ($child_skill->parent_skill === $parent_skill->skill_name): ?>
                                    <tr>
                                        <td style="padding-left: 20px;">— <?php echo esc_html($child_skill->skill_name); ?></td>
                                        <td><?php echo esc_html($child_skill->parent_skill); ?></td>
                                        <td><?php echo esc_html($child_skill->description); ?></td>
                                        <td>
                                            <a href="<?php echo admin_url('admin.php?page=vulpes-lms-manage-skill&skill_id=' . $child_skill->id); ?>"
                                                class="button">Manage</a>
                                            <a href="<?php echo admin_url('admin.php?page=vulpes-lms-skills&action=delete&skill_id=' . $child_skill->id); ?>"
                                                class="button"
                                                onclick="return confirm('Are you sure you want to delete this skill?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endif;
                            endforeach;

                        endif;
                    endforeach;

                    // Now display the "Standalone" category and its skills
                    ?>
                    <tr style="font-weight: bold;">
                        <td>Standalone</td>
                        <td></td> <!-- No parent for Standalone -->
                        <td></td>
                        <td></td>
                    </tr>
                    <?php

                    foreach ($skills as $skill):
                        if ($skill->parent_skill === 'Standalone'): ?>
                            <tr>
                                <td style="padding-left: 20px;">— <?php echo esc_html($skill->skill_name); ?></td>
                                <td><?php echo esc_html($skill->parent_skill); ?></td>
                                <td><?php echo esc_html($skill->description); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=vulpes-lms-manage-skill&skill_id=' . $skill->id); ?>"
                                        class="button">Manage</a>
                                    <a href="<?php echo admin_url('admin.php?page=vulpes-lms-skills&action=delete&skill_id=' . $skill->id); ?>"
                                        class="button"
                                        onclick="return confirm('Are you sure you want to delete this skill?');">Delete</a>
                                </td>
                            </tr>
                        <?php endif;
                    endforeach;

                else: ?>
                    <tr>
                        <td colspan="4">No skills found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div id="add-new" class="tab-content" style="display: none;">
        <form method="post" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="skill_name">Skill Name</label></th>
                    <td><input type="text" id="skill_name" name="skill_name" class="regular-text" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="is_parent">Is Parent</label></th>
                    <td>
                        <input type="checkbox" id="is_parent" name="is_parent" value="true" />
                        <label for="is_parent">Check this if the skill is a parent skill</label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="parent_skill">Parent Skill</label></th>
                    <td>
                        <select id="parent_skill" name="parent_skill">
                            <option value="">Standalone</option>
                            <?php foreach ($skills as $skill): ?>
                                <?php if ($skill->is_parent == 'true'): ?>
                                    <option value="<?php echo esc_attr($skill->skill_name); ?>">
                                        <?php echo esc_html($skill->skill_name); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="description">Description</label></th>
                    <td><textarea id="description" name="description" class="regular-text"></textarea></td>
                </tr>
            </table>
            <?php submit_button('Add Skill'); ?>
        </form>
    </div>
</div>


<div id="add-new" class="tab-content" style="display: none;">
    <form method="post" action="">
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="skill_name">Skill Name</label></th>
                <td><input type="text" id="skill_name" name="skill_name" class="regular-text" required /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="is_parent">Is Parent</label></th>
                <td>
                    <input type="checkbox" id="is_parent" name="is_parent" value="true" />
                    <label for="is_parent">Check this if the skill is a parent skill</label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="parent_skill">Parent Skill</label></th>
                <td>
                    <select id="parent_skill" name="parent_skill">
                        <option value="">Standalone</option>
                        <?php foreach ($skills as $skill): ?>
                            <?php if ($skill->is_parent == 'true'): ?>
                                <option value="<?php echo esc_attr($skill->skill_name); ?>">
                                    <?php echo esc_html($skill->skill_name); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="description">Description</label></th>
                <td><textarea id="description" name="description" class="regular-text"></textarea></td>
            </tr>
        </table>
        <?php submit_button('Add Skill'); ?>
    </form>
</div>
</div>


<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('.nav-tab').click(function (e) {
            e.preventDefault();
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            $('.tab-content').hide();
            $($(this).attr('href')).show();
        });

        $('#is_parent').change(function () {
            if ($(this).is(':checked')) {
                $('#parent_skill').prop('disabled', true);
            } else {
                $('#parent_skill').prop('disabled', false);
            }
        });

        // Keep the active tab after form submission
        var hash = window.location.hash;
        if (hash) {
            $('.nav-tab[href="' + hash + '"]').click();
        }
    });
</script>