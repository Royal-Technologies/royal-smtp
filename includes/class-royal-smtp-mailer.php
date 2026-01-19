<?php
/**
 * Mailer Class - PHPMailer configuration
 *
 * @package RoyalSMTP
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Royal_SMTP_Mailer {

    private static $instance = null;
    private $options;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->options = get_option( 'royal_smtp_options', array() );
        
        // PHPMailer configuration
        add_action( 'phpmailer_init', array( $this, 'configure_phpmailer' ), 10, 1 );
        
        // Override From email and name
        add_filter( 'wp_mail_from', array( $this, 'custom_from_email' ) );
        add_filter( 'wp_mail_from_name', array( $this, 'custom_from_name' ) );

        // Log emails
        add_filter( 'wp_mail', array( $this, 'log_email_attempt' ), 999 );
        add_action( 'wp_mail_succeeded', array( $this, 'log_email_success' ) );
        add_action( 'wp_mail_failed', array( $this, 'log_email_failure' ) );
    }

    /**
     * Configure PHPMailer
     */
    public function configure_phpmailer( $phpmailer ) {
        $this->options = get_option( 'royal_smtp_options', array() );

        if ( empty( $this->options['mailer'] ) || $this->options['mailer'] !== 'smtp' ) {
            return;
        }

        $security = Royal_SMTP_Security::get_instance();

        $phpmailer->isSMTP();
        $phpmailer->Host       = $this->options['smtp_host'] ?? '';
        $phpmailer->Port       = $this->options['smtp_port'] ?? 587;
        $phpmailer->Username   = $this->options['smtp_user'] ?? '';
        $phpmailer->Password   = $security->decrypt( $this->options['smtp_pass'] ?? '' );
        $phpmailer->SMTPAuth   = (bool) ( $this->options['smtp_auth'] ?? false );

        // Encryption
        $encryption = $this->options['smtp_encryption'] ?? 'none';
        if ( $encryption !== 'none' ) {
            $phpmailer->SMTPSecure = $encryption;
        } else {
            $phpmailer->SMTPSecure = '';
            $phpmailer->SMTPAutoTLS = false;
        }

        // From email and name
        if ( ! empty( $this->options['from_email'] ) ) {
            $phpmailer->From = $this->options['from_email'];
            $phpmailer->Sender = $this->options['from_email'];
        }

        if ( ! empty( $this->options['from_name'] ) ) {
            $phpmailer->FromName = $this->options['from_name'];
        }

        // Debug mode
        if ( ! empty( $this->options['debug_mode'] ) ) {
            $phpmailer->SMTPDebug = 2;
            $phpmailer->Debugoutput = array( Royal_SMTP_Debug::get_instance(), 'capture_debug' );
        }
    }

    /**
     * Custom from email
     */
    public function custom_from_email( $original_email ) {
        $this->options = get_option( 'royal_smtp_options', array() );
        
        if ( ! empty( $this->options['mailer'] ) && $this->options['mailer'] === 'smtp' ) {
            // Use configured email or fallback to admin email
            $from_email = ! empty( $this->options['from_email'] ) 
                ? $this->options['from_email'] 
                : get_option( 'admin_email' );
            return $from_email;
        }
        
        return $original_email;
    }

    /**
     * Custom from name
     */
    public function custom_from_name( $original_name ) {
        $this->options = get_option( 'royal_smtp_options', array() );
        
        if ( ! empty( $this->options['mailer'] ) && $this->options['mailer'] === 'smtp' ) {
            // Use configured name or fallback to site name
            $from_name = ! empty( $this->options['from_name'] ) 
                ? $this->options['from_name'] 
                : get_bloginfo( 'name' );
            return $from_name;
        }
        
        return $original_name;
    }

    /**
     * Log email attempt - store email data temporarily
     */
    public function log_email_attempt( $args ) {
        // Store for later logging
        set_transient( 'royal_smtp_last_email', array(
            'to'      => is_array( $args['to'] ) ? implode( ', ', $args['to'] ) : $args['to'],
            'subject' => $args['subject'] ?? '',
            'time'    => current_time( 'mysql' ),
        ), 60 );

        return $args;
    }

    /**
     * Log successful email
     */
    public function log_email_success( $mail_data ) {
        $options = get_option( 'royal_smtp_options', array() );
        
        if ( empty( $options['email_logging'] ) ) {
            return;
        }

        $email_data = get_transient( 'royal_smtp_last_email' );
        if ( ! $email_data ) {
            $email_data = array(
                'to'      => is_array( $mail_data['to'] ) ? implode( ', ', $mail_data['to'] ) : $mail_data['to'],
                'subject' => $mail_data['subject'] ?? '',
            );
        }

        Royal_SMTP_Logger::log(
            $email_data['to'],
            $email_data['subject'],
            'sent',
            ''
        );

        delete_transient( 'royal_smtp_last_email' );
    }

    /**
     * Log failed email
     */
    public function log_email_failure( $wp_error ) {
        $options = get_option( 'royal_smtp_options', array() );

        $email_data = get_transient( 'royal_smtp_last_email' );
        $error_message = $wp_error->get_error_message();

        if ( ! empty( $options['email_logging'] ) ) {
            Royal_SMTP_Logger::log(
                $email_data['to'] ?? '',
                $email_data['subject'] ?? '',
                'failed',
                $error_message
            );
        }

        // Send failure notification
        if ( ! empty( $options['failure_notification'] ) && ! empty( $options['notification_email'] ) ) {
            $this->send_failure_notification( $email_data, $error_message );
        }

        delete_transient( 'royal_smtp_last_email' );
    }

    /**
     * Send failure notification to admin
     */
    private function send_failure_notification( $email_data, $error_message ) {
        $options = get_option( 'royal_smtp_options', array() );
        $admin_email = $options['notification_email'] ?? get_option( 'admin_email' );

        $subject = sprintf( 
            /* translators: %s: site name */
            __( '[%s] Email Delivery Failed', 'royal-smtp' ), 
            get_bloginfo( 'name' ) 
        );

        $message = sprintf(
            /* translators: 1: recipient, 2: subject, 3: error message, 4: time */
            __( "An email failed to send.\n\nTo: %1\$s\nSubject: %2\$s\nError: %3\$s\nTime: %4\$s", 'royal-smtp' ),
            $email_data['to'] ?? __( 'Unknown', 'royal-smtp' ),
            $email_data['subject'] ?? __( 'Unknown', 'royal-smtp' ),
            $error_message,
            current_time( 'mysql' )
        );

        // Use PHP mail to avoid loop
        @mail( $admin_email, $subject, $message );
    }

    /**
     * Test SMTP connection without sending email
     */
    public static function test_connection() {
        $options = get_option( 'royal_smtp_options', array() );
        $security = Royal_SMTP_Security::get_instance();

        if ( empty( $options['smtp_host'] ) ) {
            return array(
                'success' => false,
                'message' => __( 'SMTP Host is not configured', 'royal-smtp' ),
            );
        }

        $host = $options['smtp_host'];
        $port = $options['smtp_port'] ?? 587;
        $encryption = $options['smtp_encryption'] ?? 'none';
        $username = $options['smtp_user'] ?? '';
        $password = $security->decrypt( $options['smtp_pass'] ?? '' );
        $auth = (bool) ( $options['smtp_auth'] ?? false );

        // Try socket connection first
        $errno = 0;
        $errstr = '';
        $timeout = 10;

        $socket_host = $host;
        if ( $encryption === 'ssl' ) {
            $socket_host = 'ssl://' . $host;
        }

        $connection = @fsockopen( $socket_host, $port, $errno, $errstr, $timeout );

        if ( ! $connection ) {
            return array(
                'success' => false,
                'message' => sprintf(
                    /* translators: 1: host, 2: port, 3: error message */
                    __( 'Could not connect to %1$s:%2$d - %3$s', 'royal-smtp' ),
                    $host,
                    $port,
                    $errstr
                ),
            );
        }

        // Read greeting
        $response = fgets( $connection, 515 );
        if ( strpos( $response, '220' ) !== 0 ) {
            fclose( $connection );
            return array(
                'success' => false,
                'message' => __( 'Invalid server response', 'royal-smtp' ),
            );
        }

        // Send EHLO
        fputs( $connection, "EHLO " . gethostname() . "\r\n" );
        $response = '';
        while ( $line = fgets( $connection, 515 ) ) {
            $response .= $line;
            if ( substr( $line, 3, 1 ) === ' ' ) {
                break;
            }
        }

        // STARTTLS if needed
        if ( $encryption === 'tls' ) {
            fputs( $connection, "STARTTLS\r\n" );
            $response = fgets( $connection, 515 );
            if ( strpos( $response, '220' ) !== 0 ) {
                fclose( $connection );
                return array(
                    'success' => false,
                    'message' => __( 'STARTTLS failed', 'royal-smtp' ),
                );
            }
        }

        fclose( $connection );

        return array(
            'success' => true,
            'message' => sprintf(
                /* translators: 1: host, 2: port */
                __( 'Successfully connected to %1$s:%2$d', 'royal-smtp' ),
                $host,
                $port
            ),
        );
    }
}
