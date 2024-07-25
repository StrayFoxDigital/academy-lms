<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;
$table_name = $wpdb->prefix . 'vulpes_lms_subject_groups';

// Handle form submission for adding a subject group
if ( isset( $_POST['subject_group_name'] ) && isset( $_POST['description'] ) ) {
    $subject_group_name = sanitize_text_field( $_POST['subject_group_name'] );
    $description = sanitize_textarea_field( $_POST['description'] );

    $wpdb->insert(
        $table_name,
        array(
            'subject_group_name' => $subject_group_name,
            'description' => $description,
        )
    );

    echo '<div class="updated"><p>Subject group added successfully.</p></div>';
}

// Handle subject group deletion
if ( isset( $_GET['action'] ) && $_GET['action'] == 'delete' && isset( $_GET['subject_group_id'] ) ) {
    $subject_group_id = intval( $_GET['subject_group_id'] );
    $subject_group = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $subject_group_id ) );

    if ( $subject_group ) {
        // Remove the subject group from courses
        $wpdb->update(
            "{$wpdb->prefix}vulpes_lms_courses",
            array( 'subject_group' => '' ),
            array( 'subject_group' => $subject_group->subject_group_name )
        );

        // Delete the subject group
        $wpdb->delete( $table_name, array( 'id' => $subject_group_id ) );
        echo '<div class="updated"><p>Subject group deleted successfully.</p></div>';
    } else {
        echo '<div class="error"><p>Subject group not found.</p></div>';
    }
}

// Fetch all subject groups
$subject_groups = $wpdb->get_results( "SELECT * FROM $table_name" );

?>

<div class="wrap">
    <h1>Manage Subject Groups</h1>
    <h2 class="nav-tab-wrapper">
        <a href="#subject-groups" class="nav-tab nav-tab-active">Subject Groups</a>
        <a href="#add-new" class="nav-tab">Add New</a>
    </h2>

    <div id="subject-groups" class="tab-content">
        <h2>Existing Subject Groups</h2>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Subject Group Name</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Description</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Courses Assigned</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Total Achievable Score</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ( ! empty( $subject_groups ) ) : ?>
                    <?php foreach ( $subject_groups as $subject_group ) : ?>
                        <tr>
                            <td><?php echo esc_html( $subject_group->subject_group_name ); ?></td>
                            <td><?php echo esc_html( $subject_group->description ); ?></td>
                            <td><?php echo esc_html( count( $wpdb->get_results( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}vulpes_lms_courses WHERE subject_group = %s", $subject_group->subject_group_name ) ) ) ); ?></td>
                            <td><?php echo esc_html( vulpes_lms_calculate_total_achievable_score( $subject_group->subject_group_name ) ); ?></td>
                            <td>
                                <a href="<?php echo admin_url( 'admin.php?page=vulpes-lms-edit-subject-group&subject_group_id=' . $subject_group->id ); ?>" class="button">Manage</a>
                                <a href="<?php echo admin_url( 'admin.php?page=vulpes-lms-subject-groups&action=delete&subject_group_id=' . $subject_group->id ); ?>" class="button" onclick="return confirm('Are you sure you want to delete this subject group?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5">No subject groups found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div id="add-new" class="tab-content" style="display: none;">
        <form method="post" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="subject_group_name">Subject Group Name</label></th>
                    <td><input type="text" id="subject_group_name" name="subject_group_name" class="regular-text" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="description">Description</label></th>
                    <td><textarea id="description" name="description" class="regular-text"></textarea></td>
                </tr>
            </table>
            <?php submit_button( 'Add Subject Group' ); ?>
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
