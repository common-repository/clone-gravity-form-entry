<?php

/**
 * Plugin Name: Clone Gravity Form Entry
 * Description: Clone your gravity form entries with just one click event.
 * Plugin URI: https://wordpress.org/plugins/clone-gravity-form-entry/
 * Author: Techeshta
 * Author URI: https://www.techeshta.com
 * Version: 1.0.9
 *
 * Text Domain: clone-gravity-form-entry
 */
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

/**
 * Define Plugin URL and Directory Path
 */
define('CGFE_URL', plugins_url('/', __FILE__));  // Define Plugin URL
define('CGFE_PATH', plugin_dir_path(__FILE__));  // Define Plugin Directory Path
define('CGFE_DOMAIN', 'clone-gravity-form-entry');
/*
 * Load styles
 * @since v1.0.0
 */

function cgfe_script_register() {
    wp_register_style('cgfe-style', CGFE_URL . 'assets/css/cgfe.css', array(), 1.0);
    wp_enqueue_style('cgfe-style');
}

add_action('admin_enqueue_scripts', 'cgfe_script_register');

/*
 * load plugin action
 */

function cgfe_plugin_load() {

    // Load plugin textdomain
    load_plugin_textdomain('clone-gravity-form-entry', false, basename(CGFE_PATH) . '/languages');
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

    //check install/activate state of gravity form
    if (!file_exists(CGFE_PATH . '../gravityforms/gravityforms.php')) { //if not installed
        add_action('admin_notices', 'cgfe_plugin_fail_install');
    } else if (!is_plugin_active('gravityforms/gravityforms.php')) { //if not activated
        add_action('admin_notices', 'cgfe_plugin_fail_load');
    } else if (is_plugin_active('gravityforms/gravityforms.php')) {
        //include function file
        require_once CGFE_PATH . 'includes/functions.php';
    }
}

add_action('plugins_loaded', 'cgfe_plugin_load');

//if gravity form is not installed
function cgfe_plugin_fail_load() {
    $screen = get_current_screen();
    if (isset($screen->parent_file) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id) {
        return;
    }

    if (!current_user_can('activate_plugins')) {
        return;
    }

    $message = '<p><strong>' . esc_html__('Clone gravity Form Entry', 'clone-gravity-form-entry') . '</strong>' . esc_html__(' is not working because you need to activate the Gravity Form plugin.', 'clone-gravity-form-entry') . '</p>';

    echo '<div class="error"><p>' . wp_kses_post($message) . '</p></div>';
}

//if gravity form is not activated
function cgfe_plugin_fail_install() {
    $screen = get_current_screen();
    if (isset($screen->parent_file) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id) {
        return;
    }

    if (!current_user_can('activate_plugins')) {
        return;
    }
    
    $message = '<p><strong>' . esc_html__('Clone gravity Form Entry', 'clone-gravity-form-entry') . '</strong>' . esc_html__(' is not working because you need to install the Gravity Form plugin.', 'clone-gravity-form-entry') . '</p>';

    echo '<div class="error"><p>' . wp_kses_post($message) . '</p></div>';
}
