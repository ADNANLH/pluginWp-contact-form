<?php
    /*
        Plugin Name: my contact form
        Plugin URI: http://localhost/form/wordpress/wp-admin/plugins/contact-form/contact-form.php
        Description: here you can add your form contact
        Version: 1.0.0
        Author: adnan lh
    */


    //+++ create table

    function wp_contact_form_create_table() {
        global $wpdb;
        $table = $wpdb->prefix . 'contact';
        $charset_collate = $wpdb->get_charset_collate();
    
        // Corrected the data type of the "id" column to INT(11) UNSIGNED AUTO_INCREMENT
        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            subject varchar(255) NOT NULL,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            message longtext NOT NULL
             
        ) $charset_collate;";
    
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        // Added IF NOT EXISTS to prevent errors if table already exists
        dbDelta ($sql);
    }
    register_activation_hook( __FILE__, 'wp_contact_form_create_table' );


    function contact_form_remove() {
        global $wpdb;
        $table = $wpdb->prefix . 'contact';
        // Use $wpdb->query() instead of mysql_query() and added IF EXISTS to prevent errors if table doesn't exist
        $wpdb->query("DROP TABLE IF EXISTS $table");
    }
    register_deactivation_hook(__FILE__, 'contact_form_remove');



    //display contact form 
    function contact_html() {
        echo '<form class="wpcf7-form-control" action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="POST">';
        echo '<p>';
        echo 'Name <span class="wpspan">*</span> <br />';
        echo '<input type="text" name="cf-name" class="wpinput" pattern="[a-zA-Z0-9 ]+" value="' . ( isset( $_POST["cf-name"] ) ? esc_attr( $_POST["cf-name"] ) : '' ) . '"  />';
        echo '</p>';
        
        echo '<p>';
        echo 'Email <span class="wpspan">*</span> <br />';
        echo '<input type="email" name="cf-email" class="wpinput" value="' . ( isset( $_POST["cf-email"] ) ? esc_attr( $_POST["cf-email"] ) : '' ) . '" />';
        echo '</p>';
        echo '<p>';
        echo 'Subject <span class="wpspan">*</span> <br />';
        echo '<input type="text" name="cf-subject" class="wpinput" pattern="[a-zA-Z ]+" value="' . ( isset( $_POST["cf-subject"] ) ? esc_attr( $_POST["cf-subject"] ) : '' ) . '" />';
        echo '</p>';
        echo '<p>';
        echo 'Message <span class="wpspan">*</span> <br />';
        echo '<textarea rows="10" cols="35" name="cf-message" class="wpinput" id="textarea">' . ( isset( $_POST["cf-message"] ) ? esc_attr( $_POST["cf-message"] ) : '' ) . '</textarea>';
        echo '</p>';
        echo '<p><input type="submit" class=" wpcf7-submit" name="cf-submitted" value="Send"/></p>';
        echo '</form>';
    }

    function shortcode() {
        ob_start();
        contact_html();
        return ob_get_clean();
    }

    add_shortcode( 'contact', 'shortcode' );





    if(isset($_POST['cf-submitted'])){

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $name = $_POST['cf-name'];
            $email = $_POST['cf-email'];
            $message = $_POST['cf-message'];
            $subject = $_POST['cf-subject'];
    
            // Check if all fields are not empty
            if(empty($name) || empty($email) || empty($message)){
              echo "Please fill out all fields.";
            } else {
    
                global $wpdb;
                $table = $wpdb->prefix . 'contact'; 
                $data = array(
                    'name' => $name,
                    'subject' => $subject,
                    'email' => $email,
                    'message' => $message
    
                );
                $wpdb->insert( $table, $data );
                // echo "<script>alert('l\'ajout est fait avec succées')</script>";
                
                
            }
        }
    
    }
    
  



    function my_plugin_menu() {
        add_menu_page(
            'Contact Form', // The title of the menu item
            'Contact Form', // The text to display in the dashboard menu
            'manage_options', // The minimum user capability required to access the menu item
            'contact-form-plugin', // The unique ID for the menu item
            'my_plugin_callback', // The callback function to display the menu item's content
            'dashicons-feedback', // The URL or dashicon class for the menu item's icon 3
        );

    }
    add_action( 'admin_menu', 'my_plugin_menu' );

    function my_plugin_callback() {
        
        global $wpdb;
        $table = $wpdb->prefix . 'contact';

        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            // Nettoyer l'entrée pour empêcher l'injection SQL
            $id = intval($_GET['id']);
            // Supprimer la ligne de la base de données
            $wpdb->delete($table, array('id' => $id), array('%d'));
        }
            $data = $wpdb->get_results("SELECT * FROM $table", ARRAY_A);

            echo '<div class="wrap"><h2>Contact Form Plugin Responses</h2>';
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr>';
            echo '<th scope="col">Subject</th>';
            echo '<th scope="col">Name</th>';
            echo '<th scope="col">Email</th>';
            echo '<th scope="col">Message</th>';
            echo '<th scope="col">Actions</th>';
            echo '</tr></thead><tbody>';
            foreach ($data as $row) {
                echo '<tr>';
                echo '<td>' . $row['subject'] . '</td>';
                echo '<td>' . $row['name'] . '</td>';
                echo '<td>' . $row['email'] . '</td>';
                echo '<td>' . $row['message'] . '</td>';
                echo '<td><a href="?page=contact-form-plugin&action=delete&id=' . $row['id'] . '">Delete</a></td>';
                echo '</tr>';
            }
            echo '</tbody></table></div>';
        }

        add_action('admin_menu', 'my_plugin_menu');
    

    
 



    
    function add_contact_form_styles() {
        wp_enqueue_style( 'contact-form-styles', plugins_url( 'contact-form-styles.css', __FILE__ ) );
    }
    add_action( 'wp_enqueue_scripts', 'add_contact_form_styles' );
    
    


    
    
  
?>