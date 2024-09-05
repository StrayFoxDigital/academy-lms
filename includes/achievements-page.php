<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function vulpes_lms_manage_achievements_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'vulpes_achievements';

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['achievement_name'])) {
        $achievement_name = sanitize_text_field($_POST['achievement_name']);
        $description = sanitize_textarea_field($_POST['description']);
        $img_url_unobtained = esc_url_raw($_POST['img_url_unobtained']);
        $img_url_obtained = esc_url_raw($_POST['img_url_obtained']);

        // Insert the new achievement into the database
        $wpdb->insert(
            $table_name,
            array(
                'achievement_name' => $achievement_name,
                'description' => $description,
                'img_url_unobtained' => $img_url_unobtained,
                'img_url_obtained' => $img_url_obtained,
            )
        );

        echo '<div class="updated"><p>Achievement added successfully.</p></div>';
    }

    // Enqueue the admin-style.css file
    wp_enqueue_style('vulpes-admin-style', plugin_dir_url(__FILE__) . '../assets/css/admin-style.css');

    // Fetch all achievements
    $achievements = $wpdb->get_results("SELECT * FROM $table_name");

    ?>
    <div class="wrap">
        <h1>Manage Achievements</h1>
        <h2 class="nav-tab-wrapper">
            <a href="#achievements-list" class="nav-tab nav-tab-active">Achievements</a>
            <a href="#add-new-achievement" class="nav-tab">Add New Achievement</a>
        </h2>
        
        <div id="achievements-list" class="tab-content">
            <h2>Manage Achievements</h2>
            <table class="widefat fixed" cellspacing="0">
                <thead>
                    <tr>
                        <th>Achievement Name</th>
                        <th>Description</th>
                        <th>Unobtained Image</th>
                        <th>Obtained Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($achievements)) : ?>
                        <?php foreach ($achievements as $achievement) : ?>
                            <tr>
                                <td><?php echo esc_html($achievement->achievement_name); ?></td>
                                <td><?php echo esc_html($achievement->description); ?></td>
                                <td><img src="<?php echo esc_url($achievement->img_url_unobtained); ?>" alt="Unobtained Image" style="max-width: 50px;"></td>
                                <td><img src="<?php echo esc_url($achievement->img_url_obtained); ?>" alt="Obtained Image" style="max-width: 50px;"></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=vulpes-lms-manage-achievement&id=' . $achievement->id); ?>" class="button">Manage</a>
                                    <a href="<?php echo admin_url('admin.php?page=vulpes-lms-delete-achievement&id=' . $achievement->id); ?>" class="button" onclick="return confirm('Are you sure you want to delete this achievement?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5">No achievements found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div id="add-new-achievement" class="tab-content" style="display: none;">
            <h2>Add New Achievement</h2>
            <form method="post" action="">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="achievement_name">Achievement Name</label></th>
                        <td><input type="text" id="achievement_name" name="achievement_name" class="regular-text" required /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="description">Description</label></th>
                        <td><textarea id="description" name="description" class="regular-text" required></textarea></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="img_url_unobtained">Unobtained Image</label></th>
                        <td>
                            <input type="text" id="img_url_unobtained" name="img_url_unobtained" class="regular-text" required />
                            <button type="button" class="button vulpes-lms-upload-button" data-target="#img_url_unobtained">Upload Image</button>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="img_url_obtained">Obtained Image</label></th>
                        <td>
                            <input type="text" id="img_url_obtained" name="img_url_obtained" class="regular-text" required />
                            <button type="button" class="button vulpes-lms-upload-button" data-target="#img_url_obtained">Upload Image</button>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Add Achievement'); ?>
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

            // WordPress Media Uploader
            var file_frame;
            $('.vulpes-lms-upload-button').on('click', function(e) {
                e.preventDefault();
                var target = $(this).data('target');

                // If the media frame already exists, reopen it.
                if (file_frame) {
                    file_frame.off('select'); // Remove previous event listeners to avoid conflicts
                    file_frame.open();
                    return;
                }

                // Create a new media frame
                file_frame = wp.media({
                    title: 'Select or Upload Image',
                    button: {
                        text: 'Use this image'
                    },
                    multiple: false
                });

                // When an image is selected, run a callback.
                file_frame.on('select', function() {
                    var attachment = file_frame.state().get('selection').first().toJSON();
                    $(target).val(attachment.url);
                });

                // Finally, open the modal
                file_frame.open();
            });
        });
    </script>
    <?php
}

vulpes_lms_manage_achievements_page();
