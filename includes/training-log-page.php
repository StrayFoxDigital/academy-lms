<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;

if ( isset( $_GET['delete_training'] ) && isset( $_GET['training_id'] ) ) {
    $training_id = intval( $_GET['training_id'] );
    
    // Get the training record to find the file URL
    $training_record = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}vulpes_lms_training_log WHERE id = %d", $training_id ) );
    
    if ( $training_record ) {
        // Delete the file if it exists
        if ( ! empty( $training_record->uploads ) ) {
            $file_path = str_replace( wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $training_record->uploads );
            if ( file_exists( $file_path ) ) {
                unlink( $file_path );
            }
        }
        
        // Delete the training record from the database
        $wpdb->delete( $wpdb->prefix . 'vulpes_lms_training_log', array( 'id' => $training_id ) );
        echo '<div class="updated"><p>Training record and associated files deleted successfully.</p></div>';
    }
}

$search_query = '';
if ( isset( $_POST['s'] ) ) {
    $search_query = sanitize_text_field( $_POST['s'] );
}

$orderby = 'date_completed';
$order = 'DESC';

if ( isset( $_GET['orderby'] ) && in_array( $_GET['orderby'], array( 'employee_name', 'course_name', 'date_completed', 'expiry_date', 'status' ) ) ) {
    $orderby = sanitize_sql_orderby( $_GET['orderby'] );
}

if ( isset( $_GET['order'] ) && in_array( $_GET['order'], array( 'ASC', 'DESC' ) ) ) {
    $order = sanitize_text_field( $_GET['order'] );
}

$table_name = $wpdb->prefix . 'vulpes_lms_training_log';

// Default ordering, not by status as it is computed in PHP
if ( $orderby === 'status' ) {
    $orderby = 'expiry_date'; // Default to expiry_date for sorting
}

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
                <th scope="col"><a href="?page=vulpes-lms-training-log&orderby=employee_name&order=<?php echo $sort_order; ?>">Employee Name</a></th>
                <th scope="col"><a href="?page=vulpes-lms-training-log&orderby=course_name&order=<?php echo $sort_order; ?>">Course Name</a></th>
                <th scope="col"><a href="?page=vulpes-lms-training-log&orderby=date_completed&order=<?php echo $sort_order; ?>">Date Completed</a></th>
                <th scope="col"><a href="?page=vulpes-lms-training-log&orderby=expiry_date&order=<?php echo $sort_order; ?>">Expiry Date</a></th>
                <th scope="col"><a href="?page=vulpes-lms-training-log&orderby=status&order=<?php echo $sort_order; ?>">Status</a></th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $training_logs ) ) : ?>
                <?php
                    // Prepare an array to store status for sorting
                    $status_logs = array();
                    foreach ( $training_logs as $log ) {
                        $expiry_date = strtotime( $log->expiry_date );
                        $current_date = time();
                        $days_to_expiry = ( $expiry_date - $current_date ) / ( 60 * 60 * 24 );

                        if ( $days_to_expiry <= 0 ) {
                            $status = 'EXPIRED';
                        } elseif ( $days_to_expiry <= 30 ) {
                            $status = 'Due to Expire';
                        } else {
                            $status = 'Complete';
                        }
                        $status_logs[] = array(
                            'log' => $log,
                            'status' => $status
                        );
                    }

                    // Sort based on status if required
                    if ( $orderby === 'status' ) {
                        usort( $status_logs, function( $a, $b ) use ( $order ) {
                            $status_order = array( 'Complete' => 1, 'Due to Expire' => 2, 'EXPIRED' => 3 );
                            $comparison = $status_order[ $a['status'] ] <=> $status_order[ $b['status'] ];
                            return $order === 'ASC' ? $comparison : -$comparison;
                        } );
                    }
                ?>
                <?php foreach ( $status_logs as $status_log ) : ?>
                    <?php $log = $status_log['log']; ?>
                    <tr>
                        <td><?php echo esc_html( $log->employee_name ); ?></td>
                        <td><?php echo esc_html( $log->course_name ); ?></td>
                        <td><?php echo esc_html( date( 'd-m-Y', strtotime( $log->date_completed ) ) ); ?></td>
                        <td><?php echo esc_html( date( 'd-m-Y', strtotime( $log->expiry_date ) ) ); ?></td>
                        <td><?php echo esc_html( $status_log['status'] ); ?></td>
                        <td>
                            <?php if ( $log->uploads ) : ?>
                                <a href="<?php echo esc_url( $log->uploads ); ?>" class="button" target="_blank">View Files</a>
                            <?php else : ?>
                                <a href="#" class="button disabled" aria-disabled="true">View Files</a>
                            <?php endif; ?>
                            <a href="<?php echo admin_url( 'admin.php?page=vulpes-lms-manage-training&training_id=' . $log->id ); ?>" class="button">Manage</a>
                            <a href="<?php echo admin_url( 'admin.php?page=vulpes-lms-training-log&delete_training=1&training_id=' . $log->id ); ?>" class="button" onclick="return confirm('Are you sure you want to delete this training record and its associated files?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="6">No training logs found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
