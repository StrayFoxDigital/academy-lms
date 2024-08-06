<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $wpdb;
$skills_table = $wpdb->prefix . 'vulpes_lms_skills_list';
$skill_id = isset($_GET['skill_id']) ? intval($_GET['skill_id']) : 0;

if (!$skill_id) {
    echo '<div class="error"><p>Skill not found.</p></div>';
    return;
}

$skill = $wpdb->get_row($wpdb->prepare("SELECT * FROM $skills_table WHERE id = %d", $skill_id));

if (!$skill) {
    echo '<div class="error"><p>Skill not found.</p></div>';
    return;
}

// Handle form submission
if (isset($_POST['skill_name']) && isset($_POST['parent_skill']) && isset($_POST['description'])) {
    $skill_name = sanitize_text_field($_POST['skill_name']);
    $parent_skill = sanitize_text_field($_POST['parent_skill']);
    $is_parent = isset($_POST['is_parent']) ? 'true' : 'false';
    $description = sanitize_textarea_field($_POST['description']);

    // Update the skill record
    $wpdb->update(
        $skills_table,
        array(
            'skill_name' => $skill_name,
            'parent_skill' => $parent_skill,
            'is_parent' => $is_parent,
            'description' => $description
        ),
        array('id' => $skill_id)
    );

    echo '<div class="updated"><p>Skill updated successfully.</p></div>';

    $skill = $wpdb->get_row($wpdb->prepare("SELECT * FROM $skills_table WHERE id = %d", $skill_id));
}

// Fetch all skills for the parent dropdown
$all_skills = $wpdb->get_results("SELECT * FROM $skills_table WHERE id != $skill_id AND is_parent = 'true'");

?>

<div class="wrap">
    <h1>Manage Skill</h1>
    <form method="post" action="">
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="skill_name">Skill Name</label></th>
                <td><input type="text" id="skill_name" name="skill_name" class="regular-text" value="<?php echo esc_attr($skill->skill_name); ?>" required /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="is_parent">Is Parent</label></th>
                <td>
                    <input type="checkbox" id="is_parent" name="is_parent" value="true" <?php checked($skill->is_parent, 'true'); ?> />
                    <label for="is_parent">Check this if the skill is a parent skill</label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="parent_skill">Parent Skill</label></th>
                <td>
                    <select id="parent_skill" name="parent_skill" <?php if ($skill->is_parent == 'true') echo 'disabled'; ?>>
                        <option value="None" <?php selected($skill->parent_skill, 'None'); ?>>None</option>
                        <?php foreach ($all_skills as $parent_skill) : ?>
                            <option value="<?php echo esc_attr($parent_skill->skill_name); ?>" <?php selected($parent_skill->skill_name, $skill->parent_skill); ?>><?php echo esc_html($parent_skill->skill_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="description">Description</label></th>
                <td><textarea id="description" name="description" class="regular-text"><?php echo esc_textarea($skill->description); ?></textarea></td>
            </tr>
        </table>
        <?php submit_button('Update Skill'); ?>
    </form>
    <a href="<?php echo admin_url('admin.php?page=vulpes-lms-skills'); ?>" class="button">Back to Skills</a>
</div>