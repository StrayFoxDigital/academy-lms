<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;
$table_name = $wpdb->prefix . 'vulpes_lms_learning_paths';

// Handle form submission for adding a learning path
if ( isset( $_POST['learning_path_name'] ) && isset( $_POST['description'] ) ) {
    $learning_path_name = sanitize_text_field( $_POST['learning_path_name'] );
    $description = sanitize_textarea_field( $_POST['description'] );

    $wpdb->insert(
        $table_name,
        array(
            'learning_path_name' => $learning_path_name,
            'description' => $description,
        )
    );

    echo '<div class="updated"><p>Learning path added successfully.</p></div>';
}

// Handle learning path deletion
if ( isset( $_GET['action'] ) && $_GET['action'] == 'delete' && isset( $_GET['learning_path_id'] ) ) {
    $learning_path_id = intval( $_GET['learning_path_id'] );
    $learning_path = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $learning_path_id ) );

    if ( $learning_path ) {
        // Remove the learning path from courses
        $wpdb->update(
            "{$wpdb->prefix}vulpes_lms_courses",
            array( 'learning_path' => '' ),
            array( 'learning_path' => $learning_path->learning_path_name )
        );

        // Delete the learning path
        $wpdb->delete( $table_name, array( 'id' => $learning_path_id ) );
        echo '<div class="updated"><p>Learning path deleted successfully.</p></div>';
    } else {
        echo '<div class="error"><p>Learning path not found.</p></div>';
    }
}

// Fetch all learning paths
$learning_paths = $wpdb->get_results( "SELECT * FROM $table_name" );

?>

<div class="wrap">
    <h1>Manage Learning Paths</h1>
    <h2 class="nav-tab-wrapper">
        <a href="#learning-paths" class="nav-tab nav-tab-active">Learning Paths</a>
        <a href="#add-new" class="nav-tab">Add New</a>
    </h2>

    <div id="learning-paths" class="tab-content">
        <h2>Existing Learning Paths</h2>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Learning Path Name</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Description</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Courses Assigned</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Total Achievable Score</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ( ! empty( $learning_paths ) ) : ?>
                    <?php foreach ( $learning_paths as $learning_path ) : ?>
                        <tr>
                            <td><?php echo esc_html( $learning_path->learning_path_name ); ?></td>
                            <td><?php echo esc_html( $learning_path->description ); ?></td>
                            <td><?php echo esc_html( count( $wpdb->get_results( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}vulpes_lms_courses WHERE learning_path = %s", $learning_path->learning_path_name ) ) ) ); ?></td>
                            <td><?php echo esc_html( vulpes_lms_calculate_total_achievable_score( $learning_path->learning_path_name ) ); ?></td>
                            <td>
                                <a href="<?php echo admin_url( 'admin.php?page=vulpes-lms-edit-learning-path&learning_path_id=' . $learning_path->id ); ?>" class="button">Manage</a>
                                <a href="<?php echo admin_url( 'admin.php?page=vulpes-lms-learning-paths&action=delete&learning_path_id=' . $learning_path->id ); ?>" class="button" onclick="return confirm('Are you sure you want to delete this learning path?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5">No learning paths found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div id="add-new" class="tab-content" style="display: none;">
        <form method="post" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="learning_path_name">Learning Path Name</label></th>
                    <td><input type="text" id="learning_path_name" name="learning_path_name" class="regular-text" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="description">Description</label></th>
                    <td><textarea id="description" name="description" class="regular-text"></textarea></td>
                </tr>
            </table>
            <?php submit_button( 'Add Learning Path' ); ?>
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