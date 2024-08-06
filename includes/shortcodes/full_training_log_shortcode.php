<?php

// Shortcode: [vulpes_full_training_log]

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function vulpes_full_training_log_shortcode() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'vulpes_lms_training_log';

    $training_logs = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY date_completed DESC" );

    if ( empty( $training_logs ) ) {
        return '<p>No training records found.</p>';
    }

    ob_start();
    ?>
    <div class="vulpes-lms-shortcodes">
        <div class="filters">
            <div class="filter-row">
                <input type="text" id="search-employee" placeholder="Search by Employee Name" onkeyup="filterTrainingLogs()">
                <input type="text" id="search-course" placeholder="Search by Course Name" onkeyup="filterTrainingLogs()">
            </div>
            <div class="filter-row">
                <input type="date" id="date-from" onchange="filterTrainingLogs()">
                <input type="date" id="date-to" onchange="filterTrainingLogs()">
            </div>
            <a href="#" onclick="clearFilters()" class="clear-filters">Clear all filters</a>
        </div>
        <table id="training-log-table">
            <thead>
                <tr>
                    <th>Employee Name</th>
                    <th>Course Name</th>
                    <th>Date Completed</th>
                    <th>Expiry Date</th>
                    <th>Status</th>
                    <th>View Files</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $training_logs as $log ) : ?>
                    <?php
                        $status = 'Complete';
                        $expiry_date = strtotime( $log->expiry_date );
                        $current_date = time();
                        $days_to_expiry = ( $expiry_date - $current_date ) / ( 60 * 60 * 24 );

                        if ( $days_to_expiry <= 0 ) {
                            $status = 'EXPIRED';
                        } elseif ( $days_to_expiry <= 30 ) {
                            $status = 'Due to Expire';
                        }
                    ?>
                    <tr>
                        <td><?php echo esc_html( $log->employee_name ); ?></td>
                        <td><?php echo esc_html( $log->course_name ); ?></td>
                        <td><?php echo esc_html( date( 'd-m-Y', strtotime( $log->date_completed ) ) ); ?></td>
                        <td><?php echo esc_html( date( 'd-m-Y', strtotime( $log->expiry_date ) ) ); ?></td>
                        <td><?php echo esc_html( $status ); ?></td>
                        <td>
                            <?php if ( $log->uploads ) : ?>
                                <a href="<?php echo esc_url( $log->uploads ); ?>" target="_blank">View Files</a>
                            <?php else : ?>
                                <span>No Files</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script>
    function filterTrainingLogs() {
        var employeeInput, courseInput, dateFrom, dateTo, table, tr, i, txtValue;
        employeeInput = document.getElementById("search-employee").value.toUpperCase();
        courseInput = document.getElementById("search-course").value.toUpperCase();
        dateFrom = document.getElementById("date-from").value;
        dateTo = document.getElementById("date-to").value;
        table = document.getElementById("training-log-table");
        tr = table.getElementsByTagName("tr");

        for (i = 1; i < tr.length; i++) {
            var tdEmployee = tr[i].getElementsByTagName("td")[0];
            var tdCourse = tr[i].getElementsByTagName("td")[1];
            var tdDate = tr[i].getElementsByTagName("td")[2];
            var display = true;

            if (tdEmployee && tdCourse && tdDate) {
                var employeeName = tdEmployee.textContent || tdEmployee.innerText;
                var courseName = tdCourse.textContent || tdCourse.innerText;
                var dateCompleted = tdDate.textContent || tdDate.innerText;
                var dateCompletedTimestamp = new Date(dateCompleted.split('-').reverse().join('-')).getTime();

                if (employeeName.toUpperCase().indexOf(employeeInput) === -1) {
                    display = false;
                }

                if (courseName.toUpperCase().indexOf(courseInput) === -1) {
                    display = false;
                }

                if (dateFrom && dateCompletedTimestamp < new Date(dateFrom).getTime()) {
                    display = false;
                }

                if (dateTo && dateCompletedTimestamp > new Date(dateTo).getTime()) {
                    display = false;
                }

                if (display) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }

    function clearFilters() {
        document.getElementById("search-employee").value = "";
        document.getElementById("search-course").value = "";
        document.getElementById("date-from").value = "";
        document.getElementById("date-to").value = "";
        filterTrainingLogs();
    }
    </script>
    <?php
    return ob_get_clean();
}

add_shortcode( 'vulpes_full_training_log', 'vulpes_full_training_log_shortcode' );
?>