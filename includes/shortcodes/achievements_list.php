<?php
// Shortcode: [vulpes_achievements_list]

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function vulpes_achievements_list_shortcode() {
    if (!current_user_can('manage_options')) {
        return '<p>You do not have permission to view this content.</p>';
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'vulpes_achievements';
    $achievements = $wpdb->get_results("SELECT * FROM $table_name");

    if (empty($achievements)) {
        return '<p>No achievements found.</p>';
    }

    // Enqueue the shortcodes.css file
    wp_enqueue_style('vulpes-shortcodes-style', plugin_dir_url(__FILE__) . '../assets/css/shortcodes.css');

    ob_start();
    ?>
    <div class="vulpes-lms-shortcodes">
        <table class="widefat fixed" cellspacing="0" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 30%;">Achievement Name</th>
                    <th style="width: 30%;">Description</th>
                    <th style="width: 15%;">Unobtained</th>
                    <th style="width: 15%;">Obtained</th>
                    <th style="width: 10%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($achievements as $achievement) : ?>
                    <tr>
                        <td><?php echo esc_html($achievement->achievement_name); ?></td>
                        <td><?php echo esc_html($achievement->description); ?></td>
                        <td style="text-align: left;">
                            <img src="<?php echo esc_url($achievement->img_url_unobtained); ?>" alt="Unobtained Image" style="max-width: 50px;">
                        </td>
                        <td style="text-align: left;">
                            <img src="<?php echo esc_url($achievement->img_url_obtained); ?>" alt="Obtained Image" style="max-width: 50px;">
                        </td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=vulpes-lms-manage-achievement&id=' . $achievement->id); ?>">Manage</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('vulpes_achievements_list', 'vulpes_achievements_list_shortcode');
