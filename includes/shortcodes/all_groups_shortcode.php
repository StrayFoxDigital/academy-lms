<?php

// Shortcode: [vulpes_all_groups]

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function vulpes_all_groups_shortcode() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'vulpes_lms_groups';

    $groups = $wpdb->get_results( "SELECT * FROM $table_name" );

    if ( empty( $groups ) ) {
        return '<p>No groups found.</p>';
    }

    // Define the URL of the manage-group page
    $manage_group_url = site_url('/manage-group/');

    ob_start();
    ?>
    <div class="vulpes-lms-shortcodes">
        <input type="text" id="group-search" placeholder="Search by Group Name" onkeyup="filterGroups()" style="width: 100%; padding: 8px; margin-bottom: 10px;">

        <table id="group-table">
            <thead>
                <tr>
                    <th>Group Name</th>
                    <th>Manager</th>
                    <th>Assigned Employees</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $groups as $group ) : ?>
                    <tr>
                        <td><?php echo esc_html( $group->group_name ); ?></td>
                        <td>
                            <?php 
                            $manager = get_userdata( $group->manager );
                            echo esc_html( $manager ? $manager->display_name : 'Manager not found' );
                            ?>
                        </td>
                        <td><?php echo esc_html( count( get_users( array( 'meta_key' => 'group', 'meta_value' => $group->group_name ) ) ) ); ?></td>
                        <td>
                            <a href="<?php echo esc_url( add_query_arg( 'group_id', $group->id, $manage_group_url ) ); ?>">Manage</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script>
    function filterGroups() {
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("group-search");
        filter = input.value.toUpperCase();
        table = document.getElementById("group-table");
        tr = table.getElementsByTagName("tr");
        for (i = 1; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[0];
            if (td) {
                txtValue = td.textContent || td.innerText;
                if (txtValue.toUpperCase().indexOf(filter) === 0) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }       
        }
    }
    </script>
    <?php
    return ob_get_clean();
}

add_shortcode( 'vulpes_all_groups', 'vulpes_all_groups_shortcode' );

?>