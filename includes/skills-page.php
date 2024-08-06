<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $wpdb;
$skills_table = $wpdb->prefix . 'vulpes_lms_skills_list';

// Handle form submission for adding a skill
if (isset($_POST['skill_name'])) {
    $skill_name = sanitize_text_field($_POST['skill_name']);
    $parent_skill = sanitize_text_field($_POST['parent_skill']);
    $description = sanitize_textarea_field($_POST['description']);

    $is_parent = 'false';

    $wpdb->insert(
        $skills_table,
        array(
            'skill_name' => $skill_name,
            'parent_skill' => $parent_skill,
            'is_parent' => $is_parent,
            'description' => $description
        )
    );

    if (!empty($parent_skill)) {
        $wpdb->update(
            $skills_table,
            array('is_parent' => 'true'),
            array('skill_name' => $parent_skill)
        );
    }

    echo '<div class="updated"><p>Skill added successfully.</p></div>';
}

// Handle skill deletion
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['skill_id'])) {
    $skill_id = intval($_GET['skill_id']);
    $skill = $wpdb->get_row($wpdb->prepare("SELECT * FROM $skills_table WHERE id = %d", $skill_id));

    if ($skill) {
        // Check if the skill is a child and if its parent has other children
        if (!empty($skill->parent_skill)) {
            $parent_skill = $skill->parent_skill;
            $siblings = $wpdb->get_results($wpdb->prepare("SELECT * FROM $skills_table WHERE parent_skill = %s AND id != %d", $parent_skill, $skill_id));

            if (empty($siblings)) {
                $wpdb->update(
                    $skills_table,
                    array('is_parent' => 'false'),
                    array('skill_name' => $parent_skill)
                );
            }
        }

        // Delete the skill
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
    <h1>Manage Skills</h1>
    <h2 class="nav-tab-wrapper">
        <a href="#skills-list" class="nav-tab nav-tab-active">Skills List</a>
        <a href="#add-new" class="nav-tab">Add New</a>
    </h2>

    <div id="skills-list" class="tab-content">
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
                <?php if (!empty($skills)) : ?>
                    <?php foreach ($skills as $skill) : ?>
                        <tr>
                            <td><?php echo esc_html($skill->skill_name); ?></td>
                            <td><?php echo esc_html($skill->parent_skill); ?></td>
                            <td><?php echo esc_html($skill->description); ?></td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=vulpes-lms-manage-skill&skill_id=' . $skill->id); ?>" class="button">Manage</a>
                                <a href="<?php echo admin_url('admin.php?page=vulpes-lms-skills&action=delete&skill_id=' . $skill->id); ?>" class="button" onclick="return confirm('Are you sure you want to delete this skill?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="4">No skills found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div id="add-new" class="tab-content" style="display: none;">
        <h2>Add New Skill</h2>
        <form method="post" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="skill_name">Skill Name</label></th>
                    <td><input type="text" id="skill_name" name="skill_name" class="regular-text" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="parent_skill">Parent Skill</label></th>
                    <td>
                        <select id="parent_skill" name="parent_skill">
                            <option value="">None</option>
                            <?php foreach ($skills as $skill) : ?>
                                <option value="<?php echo esc_attr($skill->skill_name); ?>"><?php echo esc_html($skill->skill_name); ?></option>
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
