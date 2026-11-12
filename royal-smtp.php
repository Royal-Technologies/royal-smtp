<?php
/**
 * Plugin Name: Royal SMTP
 * Plugin URI:  https://royaltechbd.com/royal-smtp
 * Description: A powerful WordPress SMTP mailer plugin with email logging, queue system, multiple providers, and SweetAlert2 notifications.
 * Version:     26.01.20
 * Author:      Royal Technologies
 * Author URI:  https://royaltechbd.com
 * License:     GPL2
 * Text Domain: royal-smtp
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Plugin constants
if ( ! defined( 'ROYAL_SMTP_VERSION' ) ) {
    $plugin_data = get_file_data(
        __FILE__,
        array(
            'version' => 'Version',
        )
    );
    if ( ! empty( $plugin_data['version'] ) ) {
        define( 'ROYAL_SMTP_VERSION', $plugin_data['version'] );
    } else {
        define( 'ROYAL_SMTP_VERSION', '26.01.20' );
    }
}
define( 'ROYAL_SMTP_FILE', __FILE__ );
define( 'ROYAL_SMTP_PATH', plugin_dir_path( __FILE__ ) );
define( 'ROYAL_SMTP_URL', plugin_dir_url( __FILE__ ) );
define( 'ROYAL_SMTP_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Initialize the plugin
 */
function royal_smtp_init() {
    // Load the main loader class
    require_once ROYAL_SMTP_PATH . 'includes/class-royal-smtp-loader.php';
    Royal_SMTP_Loader::get_instance();

    // Standard Royal Technologies Plugin Updater (Auto-Updates)
    if ( is_admin() ) {
        require_once ROYAL_SMTP_PATH . 'includes/updater.php';
        $update_url = 'https://raw.githubusercontent.com/Royal-Technologies/royal-smtp/main/royal-smtp.json';
        new Royal_SMTP_Updater( ROYAL_SMTP_FILE, $update_url );
    }
}
add_action( 'plugins_loaded', 'royal_smtp_init' );

/**
 * Plugin activation hook
 */
function royal_smtp_activate() {
    // Create database tables on activation
    require_once ROYAL_SMTP_PATH . 'includes/class-royal-smtp-logger.php';
    require_once ROYAL_SMTP_PATH . 'includes/class-royal-smtp-queue.php';
    
    Royal_SMTP_Logger::create_table();
    Royal_SMTP_Queue::create_table();
    
    // Set default options if not exists
    if ( ! get_option( 'royal_smtp_options' ) ) {
        update_option( 'royal_smtp_options', array(
            'mailer'           => 'default',
            'provider'         => 'other',
            'from_email'       => get_option( 'admin_email' ),
            'from_name'        => get_bloginfo( 'name' ),
            'smtp_host'        => '',
            'smtp_port'        => 587,
            'smtp_encryption'  => 'tls',
            'smtp_auth'        => 1,
            'smtp_user'        => '',
            'smtp_pass'        => '',
            'email_logging'    => 0,
            'debug_mode'       => 0,
            'queue_enabled'    => 0,
            'failure_notification' => 0,
            'notification_email'   => get_option( 'admin_email' ),
        ) );
    }
    
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'royal_smtp_activate' );

/**
 * Plugin deactivation hook
 */
function royal_smtp_deactivate() {
    // Clear scheduled events
    wp_clear_scheduled_hook( 'royal_smtp_process_queue' );
}
register_deactivation_hook( __FILE__, 'royal_smtp_deactivate' );

/**
 * Add settings link on plugin page
 */
function royal_smtp_settings_link( $links ) {
    $settings_link = '<a href="' . admin_url( 'admin.php?page=royal-smtp' ) . '">' . __( 'Settings', 'royal-smtp' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
add_filter( 'plugin_action_links_' . ROYAL_SMTP_BASENAME, 'royal_smtp_settings_link' );