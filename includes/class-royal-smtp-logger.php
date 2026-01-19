<?php
/**
 * Logger Class - Email logging functionality
 *
 * @package RoyalSMTP
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Royal_SMTP_Logger {

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
        self::$table_name = $wpdb->prefix . 'royal_smtp_logs';
    }

    /**
     * Create database table
     */
    public static function create_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'royal_smtp_logs';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            to_email VARCHAR(255) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'sent',
            error_message TEXT,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * Log email
     */
    public static function log( $to_email, $subject, $status, $error_message = '' ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'royal_smtp_logs';

        $wpdb->insert(
            $table_name,
            array(
                'to_email'      => sanitize_email( $to_email ),
                'subject'       => sanitize_text_field( substr( $subject, 0, 255 ) ),
                'status'        => sanitize_text_field( $status ),
                'error_message' => sanitize_textarea_field( $error_message ),
                'created_at'    => current_time( 'mysql' ),
            ),
            array( '%s', '%s', '%s', '%s', '%s' )
        );

        return $wpdb->insert_id;
    }

    /**
     * Get logs
     */
    public static function get_logs( $args = array() ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'royal_smtp_logs';

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

        $allowed_orderby = array( 'id', 'to_email', 'subject', 'status', 'created_at' );
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
     * Get total count
     */
    public static function get_total_count( $status = '' ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'royal_smtp_logs';

        if ( ! empty( $status ) ) {
            return $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE status = %s",
                $status
            ) );
        }

        return $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
    }

    /**
     * Get stats
     */
    public static function get_stats() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'royal_smtp_logs';

        // Check if table exists
        $table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" );
        if ( ! $table_exists ) {
            return array(
                'total'  => 0,
                'sent'   => 0,
                'failed' => 0,
            );
        }

        $total = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
        $sent = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE status = 'sent'" );
        $failed = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE status = 'failed'" );

        return array(
            'total'  => (int) $total,
            'sent'   => (int) $sent,
            'failed' => (int) $failed,
        );
    }

    /**
     * Delete log
     */
    public static function delete( $id ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'royal_smtp_logs';

        return $wpdb->delete( $table_name, array( 'id' => $id ), array( '%d' ) );
    }

    /**
     * Delete all logs
     */
    public static function delete_all() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'royal_smtp_logs';

        return $wpdb->query( "TRUNCATE TABLE $table_name" );
    }

    /**
     * Delete old logs (older than X days)
     */
    public static function delete_old_logs( $days = 30 ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'royal_smtp_logs';

        return $wpdb->query( $wpdb->prepare(
            "DELETE FROM $table_name WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ) );
    }
}
