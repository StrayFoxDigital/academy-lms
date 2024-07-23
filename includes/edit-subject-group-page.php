<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;
$table_name = $wpdb->prefix . 'vulpes_lms_subject_groups';
$subject_group_id = isset( $_GET['subject_group_id'] ) ? intval( $_GET['subject_group_id'] ) : 0;

if ( ! $subject_group_id ) {
    echo '<div class="error"><p>Subject group not found.</p></div>';
    return;
}

$subject_group = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $subject_group_id ) );

if ( ! $subject_group ) {
    echo '<div class="error"><p>Subject group not found.</p></div>';
    return;
}

// Handle form submission
if ( isset( $_POST['subject_group_name'] ) && isset( $_POST['description'] ) ) {
    $subject_group_name = sanitize_text_field( $_POST['subject_group_name'] );
    $description = sanitize_textarea_field( $_POST['description'] );

    $wpdb->update(
        $table_name,
        array(
            'subject_group_name' => $subject_group_name,
            'description' => $description,
        ),
        array( 'id' => $subject_group_id )
    );

    echo '<div class="updated"><p>Subject group updated successfully.</p></div>';

    // Refresh subject group data
    $subject_group = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $subject_group_id ) );
}

?>

<div class="wrap">
    <h1>Edit Subject Group</h1>
    <form method="post" action="">
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="subject_group_name">Subject Group Name</label></th>
                <td><input type="text" id="subject_group_name" name="subject_group_name" class="regular-text" value="<?php echo esc_attr( $subject_group->subject_group_name ); ?>" required /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="description">Description</label></th>
                <td><textarea id="description" name="description" class="regular-text"><?php echo esc_textarea( $subject_group->description ); ?></textarea></td>
            </tr>
        </table>
        <?php submit_button( 'Update Subject Group' ); ?>
    </form>
    <a href="<?php echo admin_url( 'admin.php?page=vulpes-lms-subject-groups' ); ?>" class="button">Back to Subject Groups</a>
</div>
