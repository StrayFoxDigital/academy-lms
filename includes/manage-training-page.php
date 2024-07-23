<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;
$training_id = isset( $_GET['training_id'] ) ? intval( $_GET['training_id'] ) : 0;

if ( ! $training_id ) {
    echo '<div class="error"><p>Training record not found.</p></div>';
    return;
}

$training_log = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}academy_lms_training_log WHERE id = %d", $training_id ) );

if ( ! $training_log ) {
    echo '<div class="error"><p>Training record not found.</p></div>';
    return;
}

?>

<div class="wrap">
    <h1>Manage Training Record</h1>
    <table class="form-table">
        <tr valign="top">
            <th scope="row">Employee Name</th>
            <td><?php echo esc_html( $training_log->employee_name ); ?></td>
        </tr>
        <tr valign="top">
            <th scope="row">Course Name</th>
            <td><?php echo esc_html( $training_log->course_name ); ?></td>
        </tr>
        <tr valign="top">
            <th scope="row">Date Completed</th>
            <td><?php echo esc_html( date( 'd-m-Y', strtotime( $training_log->date_completed ) ) ); ?></td>
        </tr>
        <tr valign="top">
            <th scope="row">Expiry Date</th>
            <td><?php echo esc_html( date( 'd-m-Y', strtotime( $training_log->expiry_date ) ) ); ?></td>
        </tr>
    </table>
    <a href="<?php echo admin_url( 'admin.php?page=academy-lms-training-log' ); ?>" class="button">Back to Training Log</a>
</div>
