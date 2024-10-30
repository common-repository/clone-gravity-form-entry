<?php
if (isset($_GET['page']) && $_GET['page'] == 'gf_entries') {

    // add clone option/menu
    function cgfe_first_column_actions($form_id, $field_id, $value, $entry) {
        $entry_id = $entry['id'];
        ?>
        <form method="POST" action="" class="cge-form">
            <input type="hidden" name="formid" value="<?php echo esc_attr($form_id); ?>">
            <input type="hidden" name="entryid" value="<?php echo esc_attr($entry_id); ?>">
            <?php wp_nonce_field('cgfe-nonce' . $entry_id, 'cgfe-nonce'); ?>|
            <input type="submit" name="clone_entry" value="Clone" class="cge-clone-button">
        </form>
        <?php
    }

    add_action('gform_entries_first_column_actions', 'cgfe_first_column_actions', 10, 4);

    //duplicate gravity form entry
    function cgfe_duplicate_gf_entries() {
        if (isset($_POST['clone_entry'])) {
            $gfform = sanitize_text_field($_POST['formid']);
            $gfentry = sanitize_text_field($_POST['entryid']);
            $cgfe_nonce = sanitize_text_field($_POST['cgfe-nonce']);

            //return if nonce verification fails
            if (!wp_verify_nonce($cgfe_nonce, 'cgfe-nonce' . $gfentry)) {
                return;
            }

            global $wpdb;
            $table = $wpdb->prefix . 'gf_entry';
            $meta_table = $wpdb->prefix . 'gf_entry_meta';

            //copy fields from entry table
            $query = $wpdb->prepare("SELECT * FROM {$table} WHERE ID = %d", $gfentry);
            $results = $wpdb->get_results($query, ARRAY_A);

            // Assuming you expect only one result
            if (!empty($results)) {
                $results = $results[0];            

                //remove id from array as id is auto increament
                unset($results['id']);

                //insert data into entry table
                $wpdb->insert($table, $results);
                $last_insert_id = $wpdb->insert_id; //id of last entry
                //select data from meta entry
                $meta_query = $wpdb->prepare("SELECT * FROM {$meta_table} WHERE form_id = %d AND entry_id = %d", $gfform, $gfentry);
                $meta_results = $wpdb->get_results($meta_query, ARRAY_A);

                foreach ($meta_results as $meta_result) {
                    //remove id from array as id is auto increament
                    unset($meta_result['id']);

                    //set entry id
                    $meta_result['entry_id'] = $last_insert_id;

                    //insert data into meta entry
                    $wpdb->insert($meta_table, $meta_result);
                }
            }
        }
    }

    add_action('init', 'cgfe_duplicate_gf_entries');
}
