<?php

// Shortcode: [vulpes_my_team]

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function vulpes_my_team_shortcode() {
    if ( ! is_user_logged_in() ) {
        return '<p>You need to be logged in to view your team.</p>';
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $users = get_users( array(
        'meta_key' => 'manager',
        'meta_value' => $user_id,
    ) );

    if ( empty( $users ) ) {
        return '<p>You have no team members.</p>';
    }

    ob_start();
    ?>
    <div class="vulpes-lms-shortcodes">
        <input type="text" id="team-search" placeholder="Search by Display Name" onkeyup="filterTeam()" style="width: 100%; padding: 8px; margin-bottom: 10px;">

        <table id="team-table">
            <thead>
                <tr>
                    <th>Display Name</th>
                    <th>Position</th>
                    <th>Manager</th>
                    <th>Group</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $users as $user ) : ?>
                    <tr>
                        <td><?php echo esc_html( $user->display_name ); ?></td>
                        <td><?php echo esc_html( get_user_meta( $user->ID, 'position', true ) ); ?></td>
                        <td>
                            <?php 
                            $manager_id = get_user_meta( $user->ID, 'manager', true );
                            $manager = get_userdata( $manager_id );
                            echo esc_html( $manager ? $manager->display_name : 'N/A' );
                            ?>
                        </td>
                        <td><?php echo esc_html( get_user_meta( $user->ID, 'group', true ) ); ?></td>
                        <td>
                            <a href="#">Manage</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script>
    function filterTeam() {
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("team-search");
        filter = input.value.toUpperCase();
        table = document.getElementById("team-table");
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

add_shortcode( 'vulpes_my_team', 'vulpes_my_team_shortcode' );

?>