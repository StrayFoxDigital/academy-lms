<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;
$table_name = $wpdb->prefix . 'vulpes_lms_learning_paths';
$learning_path_id = isset( $_GET['learning_path_id'] ) ? intval( $_GET['learning_path_id'] ) : 0;

if ( ! $learning_path_id ) {
    echo '<div class="error"><p>Learning path not found.</p></div>';
    return;
}

$learning_path = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $learning_path_id ) );

if ( ! $learning_path ) {
    echo '<div class="error"><p>Learning path not found.</p></div>';
    return;
}

// Handle form submission
if ( isset( $_POST['learning_path_name'] ) && isset( $_POST['description'] ) ) {
    $learning_path_name = sanitize_text_field( $_POST['learning_path_name'] );
    $description = sanitize_textarea_field( $_POST['description'] );

    $wpdb->update(
        $table_name,
        array(
            'learning_path_name' => $learning_path_name,
            'description' => $description,
        ),
        array( 'id' => $learning_path_id )
    );

    echo '<div class="updated"><p>Learning path updated successfully.</p></div>';

    // Refresh learning path data
    $learning_path = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $learning_path_id ) );
}

?>

<div class="wrap">
    <h1>Edit Learning Path</h1>
    <form method="post" action="">
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="learning_path_name">Learning Path Name</label></th>
                <td><input type="text" id="learning_path_name" name="learning_path_name" class="regular-text" value="<?php echo esc_attr( $learning_path->learning_path_name ); ?>" required /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="description">Description</label></th>
                <td><textarea id="description" name="description" class="regular-text"><?php echo esc_textarea( $learning_path->description ); ?></textarea></td>
            </tr>
        </table>
        <?php submit_button( 'Update Learning Path' ); ?>
    </form>
    <a href="<?php echo admin_url( 'admin.php?page=vulpes-lms-learning-paths' ); ?>" class="button">Back to Learning Paths</a>
</div>