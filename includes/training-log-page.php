<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;

$search_query = '';
if ( isset( $_POST['s'] ) ) {
    $search_query = sanitize_text_field( $_POST['s'] );
}

$orderby = 'date_completed';
$order = 'DESC';

if ( isset( $_GET['orderby'] ) && in_array( $_GET['orderby'], array( 'employee_name', 'course_name', 'date_completed', 'expiry_date' ) ) ) {
    $orderby = sanitize_sql_orderby( $_GET['orderby'] );
}

if ( isset( $_GET['order'] ) && in_array( $_GET['order'], array( 'ASC', 'DESC' ) ) ) {
    $order = sanitize_text_field( $_GET['order'] );
}

$table_name = $wpdb->prefix . 'academy_lms_training_log';

if ( $search_query ) {
    $training_logs = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM $table_name WHERE employee_name LIKE %s OR course_name LIKE %s ORDER BY $orderby $order",
        '%' . $wpdb->esc_like( $search_query ) . '%',
        '%' . $wpdb->esc_like( $search_query ) . '%'
    ) );
} else {
    $training_logs = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY $orderby $order" );
}

$sort_order = $order === 'ASC' ? 'DESC' : 'ASC';

?>

<div class="wrap">
    <h1>Training Log</h1>
    <form method="post" action="">
        <p>
            <input type="text" name="s" value="<?php echo esc_attr( $search_query ); ?>" placeholder="Search by employee or course"/>
            <input type="submit" value="Search" class="button"/>
        </p>
    </form>
    <table class="widefat fixed" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><a href="?page=academy-lms-training-log&orderby=employee_name&order=<?php echo $sort_order; ?>">Employee Name</a></th>
                <th scope="col"><a href="?page=academy-lms-training-log&orderby=course_name&order=<?php echo $sort_order; ?>">Course Name</a></th>
                <th scope="col"><a href="?page=academy-lms-training-log&orderby=date_completed&order=<?php echo $sort_order; ?>">Date Completed</a></th>
                <th scope="col"><a href="?page=academy-lms-training-log&orderby=expiry_date&order=<?php echo $sort_order; ?>">Expiry Date</a></th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $training_logs ) ) : ?>
                <?php foreach ( $training_logs as $log ) : ?>
                    <tr>
                        <td><?php echo esc_html( $log->employee_name ); ?></td>
                        <td><?php echo esc_html( $log->course_name ); ?></td>
                        <td><?php echo esc_html( date( 'd-m-Y', strtotime( $log->date_completed ) ) ); ?></td>
                        <td><?php echo esc_html( date( 'd-m-Y', strtotime( $log->expiry_date ) ) ); ?></td>
                        <td>
                            <?php if ( $log->uploads ) : ?>
                                <a href="<?php echo esc_url( $log->uploads ); ?>" class="button" target="_blank">View Files</a>
                            <?php else : ?>
                                <a href="#" class="button disabled" aria-disabled="true">View Files</a>
                            <?php endif; ?>
                            <a href="<?php echo admin_url( 'admin.php?page=academy-lms-manage-training&training_id=' . $log->id ); ?>" class="button">Manage</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5">No training logs found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
