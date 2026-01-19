<?php
/**
 * Queue Class - Email queue system with WP Cron
 *
 * @package RoyalSMTP
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Royal_SMTP_Queue {

    private static $instance = null;
    private static $table_name;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'royal_smtp_queue';

        // Register cron schedule
        add_filter( 'cron_schedules', array( $this, 'add_cron_schedule' ) );
        
        // Process queue
        add_action( 'royal_smtp_process_queue', array( $this, 'process_queue' ) );
    }

    /**
     * Add custom cron schedule
     */
    public function add_cron_schedule( $schedules ) {
        $schedules['every_minute'] = array(
            'interval' => 60,
            'display'  => __( 'Every Minute', 'royal-smtp' ),
        );
        return $schedules;
    }

    /**
     * Create database table
     */
    public static function create_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'royal_smtp_queue';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            to_email VARCHAR(255) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            message LONGTEXT NOT NULL,
            headers TEXT,
            attachments TEXT,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            attempts INT(11) NOT NULL DEFAULT 0,
            max_attempts INT(11) NOT NULL DEFAULT 3,
            error_message TEXT,
            created_at DATETIME NOT NULL,
            processed_at DATETIME,
            PRIMARY KEY (id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * Add email to queue
     */
    public static function add( $to, $subject, $message, $headers = '', $attachments = array() ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'royal_smtp_queue';

        $to_email = is_array( $to ) ? implode( ', ', $to ) : $to;

        $wpdb->insert(
            $table_name,
            array(
                'to_email'    => sanitize_text_field( $to_email ),
                'subject'     => sanitize_text_field( $subject ),
                'message'     => $message,
                'headers'     => is_array( $headers ) ? serialize( $headers ) : $headers,
                'attachments' => serialize( $attachments ),
                'status'      => 'pending',
                'attempts'    => 0,
                'created_at'  => current_time( 'mysql' ),
            ),
            array( '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' )
        );

        return $wpdb->insert_id;
    }

    /**
     * Process queue
     */
    public function process_queue() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'royal_smtp_queue';

        // Get pending emails
        $emails = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table_name WHERE status = 'pending' AND attempts < max_attempts ORDER BY created_at ASC LIMIT %d",
            10
        ) );

        foreach ( $emails as $email ) {
            // Mark as processing
            $wpdb->update(
                $table_name,
                array( 'status' => 'processing' ),
                array( 'id' => $email->id ),
                array( '%s' ),
                array( '%d' )
            );

            // Prepare data
            $to = $email->to_email;
            $subject = $email->subject;
            $message = $email->message;
            $headers = maybe_unserialize( $email->headers );
            $attachments = maybe_unserialize( $email->attachments );

            // Try to send
            $sent = wp_mail( $to, $subject, $message, $headers, $attachments );

            if ( $sent ) {
                // Success
                $wpdb->update(
                    $table_name,
                    array(
                        'status'       => 'sent',
                        'processed_at' => current_time( 'mysql' ),
                        'attempts'     => $email->attempts + 1,
                    ),
                    array( 'id' => $email->id ),
                    array( '%s', '%s', '%d' ),
                    array( '%d' )
                );
            } else {
                // Failed
                $new_status = ( $email->attempts + 1 >= $email->max_attempts ) ? 'failed' : 'pending';
                
                $wpdb->update(
                    $table_name,
                    array(
                        'status'        => $new_status,
                        'attempts'      => $email->attempts + 1,
                        'error_message' => __( 'Email sending failed', 'royal-smtp' ),
                    ),
                    array( 'id' => $email->id ),
                    array( '%s', '%d', '%s' ),
                    array( '%d' )
                );
            }
        }
    }

    /**
     * Get queue items
     */
    public static function get_queue( $args = array() ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'royal_smtp_queue';

        // Check if table exists
        $table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" );
        if ( ! $table_exists ) {
            return array();
        }

        $defaults = array(
            'per_page' => 20,
            'page'     => 1,
            'status'   => '',
            'orderby'  => 'created_at',
            'order'    => 'DESC',
        );

        $args = wp_parse_args( $args, $defaults );
        $offset = ( $args['page'] - 1 ) * $args['per_page'];

        $where = '';
        if ( ! empty( $args['status'] ) ) {
            $where = $wpdb->prepare( " WHERE status = %s", $args['status'] );
        }

        $allowed_orderby = array( 'id', 'to_email', 'subject', 'status', 'created_at', 'attempts' );
        $orderby = in_array( $args['orderby'], $allowed_orderby ) ? $args['orderby'] : 'created_at';
        $order = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

        $sql = $wpdb->prepare(
            "SELECT * FROM $table_name $where ORDER BY $orderby $order LIMIT %d OFFSET %d",
            $args['per_page'],
            $offset
        );

        return $wpdb->get_results( $sql );
    }

    /**
     * Get queue stats
     */
    public static function get_stats() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'royal_smtp_queue';

        // Check if table exists
        $table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" );
        if ( ! $table_exists ) {
            return array(
                'total'      => 0,
                'pending'    => 0,
                'processing' => 0,
                'sent'       => 0,
                'failed'     => 0,
            );
        }

        $total = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
        $pending = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE status = 'pending'" );
        $processing = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE status = 'processing'" );
        $sent = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE status = 'sent'" );
        $failed = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE status = 'failed'" );

        return array(
            'total'      => (int) $total,
            'pending'    => (int) $pending,
            'processing' => (int) $processing,
            'sent'       => (int) $sent,
            'failed'     => (int) $failed,
        );
    }

    /**
     * Delete queue item
     */
    public static function delete( $id ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'royal_smtp_queue';

        return $wpdb->delete( $table_name, array( 'id' => $id ), array( '%d' ) );
    }

    /**
     * Retry failed email
     */
    public static function retry( $id ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'royal_smtp_queue';

        return $wpdb->update(
            $table_name,
            array(
                'status'   => 'pending',
                'attempts' => 0,
            ),
            array( 'id' => $id ),
            array( '%s', '%d' ),
            array( '%d' )
        );
    }

    /**
     * Clear sent emails
     */
    public static function clear_sent() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'royal_smtp_queue';

        return $wpdb->query( "DELETE FROM $table_name WHERE status = 'sent'" );
    }

    /**
     * Clear all queue
     */
    public static function clear_all() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'royal_smtp_queue';

        return $wpdb->query( "TRUNCATE TABLE $table_name" );
    }

    /**
     * Check if queue is enabled
     */
    public static function is_enabled() {
        $options = get_option( 'royal_smtp_options', array() );
        return ! empty( $options['queue_enabled'] );
    }
}
