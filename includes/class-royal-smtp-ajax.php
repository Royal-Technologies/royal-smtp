<?php
/**
 * AJAX Class - Handle all AJAX requests
 *
 * @package RoyalSMTP
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Royal_SMTP_Ajax {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Test email
        add_action( 'wp_ajax_royal_smtp_test_email', array( $this, 'test_email' ) );
        
        // Test connection
        add_action( 'wp_ajax_royal_smtp_test_connection', array( $this, 'test_connection' ) );
        
        // Get logs
        add_action( 'wp_ajax_royal_smtp_get_logs', array( $this, 'get_logs' ) );
        
        // Delete log
        add_action( 'wp_ajax_royal_smtp_delete_log', array( $this, 'delete_log' ) );
        
        // Clear all logs
        add_action( 'wp_ajax_royal_smtp_clear_logs', array( $this, 'clear_logs' ) );
        
        // Get queue
        add_action( 'wp_ajax_royal_smtp_get_queue', array( $this, 'get_queue' ) );
        
        // Retry queue item
        add_action( 'wp_ajax_royal_smtp_retry_queue', array( $this, 'retry_queue' ) );
        
        // Delete queue item
        add_action( 'wp_ajax_royal_smtp_delete_queue', array( $this, 'delete_queue' ) );
        
        // Clear queue
        add_action( 'wp_ajax_royal_smtp_clear_queue', array( $this, 'clear_queue' ) );
        
        // Export settings
        add_action( 'wp_ajax_royal_smtp_export_settings', array( $this, 'export_settings' ) );
        
        // Import settings
        add_action( 'wp_ajax_royal_smtp_import_settings', array( $this, 'import_settings' ) );
        
        // Clear debug log
        add_action( 'wp_ajax_royal_smtp_clear_debug', array( $this, 'clear_debug' ) );
        
        // Load tab
        add_action( 'wp_ajax_royal_smtp_load_tab', array( $this, 'load_tab' ) );
        
        // Cleanup old logs
        add_action( 'wp_ajax_royal_smtp_cleanup_logs', array( $this, 'cleanup_logs' ) );
        
        // Reset settings
        add_action( 'wp_ajax_royal_smtp_reset_settings', array( $this, 'reset_settings' ) );
    }

    /**
     * Cleanup old logs
     */
    public function cleanup_logs() {
        $this->verify_request();
        Royal_SMTP_Logger::delete_old_logs( 30 );
        wp_send_json_success( __( 'Old logs cleaned up successfully', 'royal-smtp' ) );
    }

    /**
     * Reset all settings to defaults
     */
    public function reset_settings() {
        $this->verify_request();
        $defaults = array(
            'mailer'               => 'default',
            'provider'             => 'other',
            'from_email'           => get_option( 'admin_email' ),
            'from_name'            => get_bloginfo( 'name' ),
            'smtp_host'            => '',
            'smtp_port'            => 587,
            'smtp_encryption'      => 'tls',
            'smtp_auth'            => 1,
            'smtp_user'            => '',
            'smtp_pass'            => '',
            'email_logging'        => 0,
            'debug_mode'           => 0,
            'queue_enabled'        => 0,
            'failure_notification' => 0,
            'notification_email'   => get_option( 'admin_email' ),
        );
        update_option( 'royal_smtp_options', $defaults );
        wp_send_json_success( __( 'Settings reset to default successfully', 'royal-smtp' ) );
    }

    /**
     * Load tab content via AJAX
     */
    public function load_tab() {
        $this->verify_request();

        $tab = isset( $_POST['tab'] ) ? sanitize_key( $_POST['tab'] ) : 'general';
        $allowed = array( 'general', 'smtp', 'logs', 'queue', 'debug', 'tools', 'about' );

        if ( ! in_array( $tab, $allowed, true ) ) {
            $tab = 'general';
        }

        $html = Royal_SMTP_Admin::get_tab_content( $tab );

        wp_send_json_success( array(
            'tab'  => $tab,
            'html' => $html,
        ) );
    }

    /**
     * Verify request
     */
    private function verify_request() {
        if ( ! Royal_SMTP_Security::can_manage() ) {
            wp_send_json_error( __( 'Permission denied', 'royal-smtp' ) );
        }

        if ( ! check_ajax_referer( 'royal_smtp_nonce', 'nonce', false ) ) {
            wp_send_json_error( __( 'Security check failed', 'royal-smtp' ) );
        }
    }

    /**
     * Send test email
     */
    public function test_email() {
        $this->verify_request();

        $to = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';

        if ( empty( $to ) ) {
            wp_send_json_error( __( 'Please enter a valid email address', 'royal-smtp' ) );
        }

        $subject = sprintf(
            /* translators: %s: site name */
            __( '[%s] Royal SMTP Test Email', 'royal-smtp' ),
            get_bloginfo( 'name' )
        );

        $message = sprintf(
            /* translators: 1: time, 2: site URL */
            __( "This is a test email sent from Royal SMTP plugin.\n\nTime: %1\$s\nSite: %2\$s\n\nIf you received this email, your SMTP configuration is working correctly!", 'royal-smtp' ),
            current_time( 'mysql' ),
            get_site_url()
        );

        // Capture any mail errors
        global $phpmailer;
        
        // Clear any previous errors
        add_action( 'wp_mail_failed', function( $wp_error ) use ( &$mail_error ) {
            $mail_error = $wp_error->get_error_message();
        } );

        $sent = wp_mail( $to, $subject, $message );

        if ( $sent ) {
            wp_send_json_success( sprintf(
                /* translators: %s: email address */
                __( 'Test email sent successfully to %s', 'royal-smtp' ),
                $to
            ) );
        } else {
            // Get detailed error
            $error_msg = __( 'Failed to send test email.', 'royal-smtp' );
            
            if ( ! empty( $mail_error ) ) {
                $error_msg .= ' ' . $mail_error;
            } elseif ( isset( $phpmailer ) && ! empty( $phpmailer->ErrorInfo ) ) {
                $error_msg .= ' ' . $phpmailer->ErrorInfo;
            } else {
                $error_msg .= ' ' . __( 'Please check your SMTP settings.', 'royal-smtp' );
            }
            
            wp_send_json_error( $error_msg );
        }
    }

    /**
     * Test SMTP connection
     */
    public function test_connection() {
        $this->verify_request();

        $result = Royal_SMTP_Mailer::test_connection();

        if ( $result['success'] ) {
            wp_send_json_success( $result['message'] );
        } else {
            wp_send_json_error( $result['message'] );
        }
    }

    /**
     * Get email logs
     */
    public function get_logs() {
        $this->verify_request();

        $page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
        $per_page = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 20;
        $status = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '';

        $logs = Royal_SMTP_Logger::get_logs( array(
            'page'     => $page,
            'per_page' => $per_page,
            'status'   => $status,
        ) );

        $total = Royal_SMTP_Logger::get_total_count( $status );
        $stats = Royal_SMTP_Logger::get_stats();

        wp_send_json_success( array(
            'logs'       => $logs,
            'total'      => $total,
            'total_pages'=> ceil( $total / $per_page ),
            'stats'      => $stats,
        ) );
    }

    /**
     * Delete log
     */
    public function delete_log() {
        $this->verify_request();

        $id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

        if ( ! $id ) {
            wp_send_json_error( __( 'Invalid log ID', 'royal-smtp' ) );
        }

        Royal_SMTP_Logger::delete( $id );

        wp_send_json_success( __( 'Log deleted', 'royal-smtp' ) );
    }

    /**
     * Clear all logs
     */
    public function clear_logs() {
        $this->verify_request();

        Royal_SMTP_Logger::delete_all();

        wp_send_json_success( __( 'All logs cleared', 'royal-smtp' ) );
    }

    /**
     * Get queue
     */
    public function get_queue() {
        $this->verify_request();

        $page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
        $per_page = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 20;
        $status = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '';

        $queue = Royal_SMTP_Queue::get_queue( array(
            'page'     => $page,
            'per_page' => $per_page,
            'status'   => $status,
        ) );

        $stats = Royal_SMTP_Queue::get_stats();

        wp_send_json_success( array(
            'queue' => $queue,
            'stats' => $stats,
        ) );
    }

    /**
     * Retry queue item
     */
    public function retry_queue() {
        $this->verify_request();

        $id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

        if ( ! $id ) {
            wp_send_json_error( __( 'Invalid queue ID', 'royal-smtp' ) );
        }

        Royal_SMTP_Queue::retry( $id );

        wp_send_json_success( __( 'Email queued for retry', 'royal-smtp' ) );
    }

    /**
     * Delete queue item
     */
    public function delete_queue() {
        $this->verify_request();

        $id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

        if ( ! $id ) {
            wp_send_json_error( __( 'Invalid queue ID', 'royal-smtp' ) );
        }

        Royal_SMTP_Queue::delete( $id );

        wp_send_json_success( __( 'Queue item deleted', 'royal-smtp' ) );
    }

    /**
     * Clear queue
     */
    public function clear_queue() {
        $this->verify_request();

        $type = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : 'sent';

        if ( $type === 'all' ) {
            Royal_SMTP_Queue::clear_all();
        } else {
            Royal_SMTP_Queue::clear_sent();
        }

        wp_send_json_success( __( 'Queue cleared', 'royal-smtp' ) );
    }

    /**
     * Export settings
     */
    public function export_settings() {
        $this->verify_request();

        $options = get_option( 'royal_smtp_options', array() );
        
        // Remove sensitive data or encrypt it
        $export_data = array(
            'plugin'   => 'royal-smtp',
            'version'  => ROYAL_SMTP_VERSION,
            'exported' => current_time( 'mysql' ),
            'settings' => $options,
        );

        wp_send_json_success( $export_data );
    }

    /**
     * Import settings
     */
    public function import_settings() {
        $this->verify_request();

        $import_data = isset( $_POST['data'] ) ? $_POST['data'] : '';

        if ( empty( $import_data ) ) {
            wp_send_json_error( __( 'No import data provided', 'royal-smtp' ) );
        }

        // Parse JSON
        $data = json_decode( stripslashes( $import_data ), true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            wp_send_json_error( __( 'Invalid JSON data', 'royal-smtp' ) );
        }

        if ( ! isset( $data['plugin'] ) || $data['plugin'] !== 'royal-smtp' ) {
            wp_send_json_error( __( 'Invalid import file', 'royal-smtp' ) );
        }

        if ( ! isset( $data['settings'] ) || ! is_array( $data['settings'] ) ) {
            wp_send_json_error( __( 'No settings found in import file', 'royal-smtp' ) );
        }

        // Sanitize and save
        $sanitized = Royal_SMTP_Security::sanitize_settings( $data['settings'] );
        update_option( 'royal_smtp_options', $sanitized );

        wp_send_json_success( __( 'Settings imported successfully', 'royal-smtp' ) );
    }

    /**
     * Clear debug log
     */
    public function clear_debug() {
        $this->verify_request();

        Royal_SMTP_Debug::clear_debug_log();

        wp_send_json_success( __( 'Debug log cleared', 'royal-smtp' ) );
    }
}
