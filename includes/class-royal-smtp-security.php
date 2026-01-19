<?php
/**
 * Security Class - Password encryption and security features
 *
 * @package RoyalSMTP
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Royal_SMTP_Security {

    private static $instance = null;
    private $encryption_key;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->encryption_key = $this->get_encryption_key();
    }

    /**
     * Get encryption key
     */
    private function get_encryption_key() {
        if ( defined( 'ROYAL_SMTP_ENCRYPTION_KEY' ) ) {
            return ROYAL_SMTP_ENCRYPTION_KEY;
        }
        
        // Use AUTH_KEY from wp-config.php as fallback
        if ( defined( 'AUTH_KEY' ) && AUTH_KEY ) {
            return AUTH_KEY;
        }

        return 'royal-smtp-default-key-' . get_site_url();
    }

    /**
     * Encrypt password
     */
    public function encrypt( $data ) {
        if ( empty( $data ) ) {
            return '';
        }

        if ( ! function_exists( 'openssl_encrypt' ) ) {
            return base64_encode( $data );
        }

        $method = 'AES-256-CBC';
        $key = hash( 'sha256', $this->encryption_key, true );
        $iv = openssl_random_pseudo_bytes( 16 );

        $encrypted = openssl_encrypt( $data, $method, $key, OPENSSL_RAW_DATA, $iv );
        
        return base64_encode( $iv . $encrypted );
    }

    /**
     * Decrypt password
     */
    public function decrypt( $data ) {
        if ( empty( $data ) ) {
            return '';
        }

        if ( ! function_exists( 'openssl_decrypt' ) ) {
            return base64_decode( $data );
        }

        $method = 'AES-256-CBC';
        $key = hash( 'sha256', $this->encryption_key, true );
        
        $data = base64_decode( $data );
        $iv = substr( $data, 0, 16 );
        $encrypted = substr( $data, 16 );

        $decrypted = openssl_decrypt( $encrypted, $method, $key, OPENSSL_RAW_DATA, $iv );
        
        return $decrypted !== false ? $decrypted : '';
    }

    /**
     * Verify nonce
     */
    public static function verify_nonce( $nonce, $action = 'royal_smtp_nonce' ) {
        return wp_verify_nonce( $nonce, $action );
    }

    /**
     * Create nonce
     */
    public static function create_nonce( $action = 'royal_smtp_nonce' ) {
        return wp_create_nonce( $action );
    }

    /**
     * Check capability
     */
    public static function can_manage() {
        return current_user_can( 'manage_options' );
    }

    /**
     * Sanitize email settings
     */
    public static function sanitize_settings( $input ) {
        // Get existing settings and merge with new input
        $existing = get_option( 'royal_smtp_options', array() );
        $sanitized = $existing; // Start with existing values
        $security = self::get_instance();

        // Mailer type
        if ( isset( $input['mailer'] ) ) {
            $sanitized['mailer'] = sanitize_text_field( $input['mailer'] );
        }

        // Provider
        if ( isset( $input['provider'] ) ) {
            $sanitized['provider'] = sanitize_text_field( $input['provider'] );
        }

        // From email
        if ( isset( $input['from_email'] ) ) {
            $sanitized['from_email'] = sanitize_email( $input['from_email'] );
        }

        // From name
        if ( isset( $input['from_name'] ) ) {
            $sanitized['from_name'] = sanitize_text_field( $input['from_name'] );
        }

        // SMTP Host
        if ( isset( $input['smtp_host'] ) ) {
            $sanitized['smtp_host'] = sanitize_text_field( $input['smtp_host'] );
        }

        // SMTP Encryption
        if ( isset( $input['smtp_encryption'] ) ) {
            $allowed = array( 'none', 'ssl', 'tls' );
            $sanitized['smtp_encryption'] = in_array( $input['smtp_encryption'], $allowed ) 
                ? $input['smtp_encryption'] 
                : 'none';
        }

        // SMTP Port
        if ( isset( $input['smtp_port'] ) ) {
            $sanitized['smtp_port'] = absint( $input['smtp_port'] );
        }

        // SMTP Auth - only update if the field was in the form
        if ( array_key_exists( 'smtp_auth', $input ) || isset( $_POST['royal_smtp_options']['smtp_host'] ) ) {
            $sanitized['smtp_auth'] = isset( $input['smtp_auth'] ) ? 1 : 0;
        }

        // SMTP Username
        if ( isset( $input['smtp_user'] ) ) {
            $sanitized['smtp_user'] = sanitize_text_field( $input['smtp_user'] );
        }

        // SMTP Password - Encrypt it
        if ( isset( $input['smtp_pass'] ) && ! empty( $input['smtp_pass'] ) ) {
            // Check if password changed (not the masked version)
            if ( strpos( $input['smtp_pass'], '••••' ) === false ) {
                $sanitized['smtp_pass'] = $security->encrypt( $input['smtp_pass'] );
            }
            // else keep existing password (already in $sanitized from $existing)
        }

        // Debug mode - only update if the field was in the form
        if ( array_key_exists( 'debug_mode', $input ) || isset( $_POST['royal_smtp_options']['mailer'] ) ) {
            $sanitized['debug_mode'] = isset( $input['debug_mode'] ) ? 1 : 0;
        }

        // Email logging - only update if the field was in the form
        if ( array_key_exists( 'email_logging', $input ) || isset( $_POST['royal_smtp_options']['mailer'] ) ) {
            $sanitized['email_logging'] = isset( $input['email_logging'] ) ? 1 : 0;
        }

        // Queue enabled - only update if the field was in the form
        if ( array_key_exists( 'queue_enabled', $input ) || isset( $_POST['royal_smtp_options']['mailer'] ) ) {
            $sanitized['queue_enabled'] = isset( $input['queue_enabled'] ) ? 1 : 0;
        }

        // Failure notification - only update if the field was in the form
        if ( array_key_exists( 'failure_notification', $input ) || isset( $_POST['royal_smtp_options']['mailer'] ) ) {
            $sanitized['failure_notification'] = isset( $input['failure_notification'] ) ? 1 : 0;
        }
        
        if ( isset( $input['notification_email'] ) ) {
            $sanitized['notification_email'] = sanitize_email( $input['notification_email'] );
        }

        return $sanitized;
    }
}
