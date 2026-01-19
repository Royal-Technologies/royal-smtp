<?php
/**
 * Main Plugin Loader Class
 *
 * @package RoyalSMTP
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Royal_SMTP_Loader {

    /**
     * Single instance
     */
    private static $instance = null;

    /**
     * Get instance
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Load dependencies
     */
    private function load_dependencies() {
        require_once ROYAL_SMTP_PATH . 'includes/class-royal-smtp-security.php';
        require_once ROYAL_SMTP_PATH . 'includes/class-royal-smtp-providers.php';
        require_once ROYAL_SMTP_PATH . 'includes/class-royal-smtp-mailer.php';
        require_once ROYAL_SMTP_PATH . 'includes/class-royal-smtp-logger.php';
        require_once ROYAL_SMTP_PATH . 'includes/class-royal-smtp-debug.php';
        require_once ROYAL_SMTP_PATH . 'includes/class-royal-smtp-queue.php';
        require_once ROYAL_SMTP_PATH . 'includes/class-royal-smtp-ajax.php';
        require_once ROYAL_SMTP_PATH . 'includes/class-royal-smtp-admin.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Load text domain
        add_action( 'init', array( $this, 'load_textdomain' ) );

        // Activation hook
        register_activation_hook( ROYAL_SMTP_FILE, array( $this, 'activate' ) );

        // Deactivation hook
        register_deactivation_hook( ROYAL_SMTP_FILE, array( $this, 'deactivate' ) );

        // Check and create tables if needed
        add_action( 'admin_init', array( $this, 'maybe_create_tables' ) );

        // Initialize modules
        Royal_SMTP_Security::get_instance();
        Royal_SMTP_Providers::get_instance();
        Royal_SMTP_Mailer::get_instance();
        Royal_SMTP_Logger::get_instance();
        Royal_SMTP_Debug::get_instance();
        Royal_SMTP_Queue::get_instance();
        Royal_SMTP_Ajax::get_instance();
        Royal_SMTP_Admin::get_instance();
    }

    /**
     * Load text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'royal-smtp',
            false,
            dirname( plugin_basename( ROYAL_SMTP_FILE ) ) . '/languages'
        );
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        Royal_SMTP_Logger::create_table();
        Royal_SMTP_Queue::create_table();

        // Schedule cron events
        if ( ! wp_next_scheduled( 'royal_smtp_process_queue' ) ) {
            wp_schedule_event( time(), 'every_minute', 'royal_smtp_process_queue' );
        }

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook( 'royal_smtp_process_queue' );
    }

    /**
     * Check and create tables if they don't exist
     */
    public function maybe_create_tables() {
        global $wpdb;
        
        $logs_table = $wpdb->prefix . 'royal_smtp_logs';
        $queue_table = $wpdb->prefix . 'royal_smtp_queue';
        
        // Check if tables exist
        $logs_exists = $wpdb->get_var( "SHOW TABLES LIKE '$logs_table'" );
        $queue_exists = $wpdb->get_var( "SHOW TABLES LIKE '$queue_table'" );
        
        if ( ! $logs_exists ) {
            Royal_SMTP_Logger::create_table();
        }
        
        if ( ! $queue_exists ) {
            Royal_SMTP_Queue::create_table();
        }
    }
}
