<?php
/**
 * Debug Class - Debug mode functionality
 *
 * @package RoyalSMTP
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Royal_SMTP_Debug {

    private static $instance = null;
    private $debug_output = array();

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {}

    /**
     * Capture PHPMailer debug output
     */
    public function capture_debug( $str, $level ) {
        $this->debug_output[] = array(
            'level'   => $level,
            'message' => $str,
            'time'    => current_time( 'mysql' ),
        );

        // Also store in transient for retrieval
        $existing = get_transient( 'royal_smtp_debug_log' ) ?: array();
        $existing[] = array(
            'level'   => $level,
            'message' => $str,
            'time'    => current_time( 'mysql' ),
        );

        // Keep only last 100 entries
        if ( count( $existing ) > 100 ) {
            $existing = array_slice( $existing, -100 );
        }

        set_transient( 'royal_smtp_debug_log', $existing, HOUR_IN_SECONDS );
    }

    /**
     * Get debug output
     */
    public function get_debug_output() {
        return $this->debug_output;
    }

    /**
     * Get stored debug log
     */
    public static function get_debug_log() {
        return get_transient( 'royal_smtp_debug_log' ) ?: array();
    }

    /**
     * Clear debug log
     */
    public static function clear_debug_log() {
        delete_transient( 'royal_smtp_debug_log' );
    }

    /**
     * Format debug output for display
     */
    public static function format_for_display( $log_entries = null ) {
        if ( $log_entries === null ) {
            $log_entries = self::get_debug_log();
        }

        if ( empty( $log_entries ) ) {
            return '<p class="royal-smtp-no-logs">' . __( 'No debug information available.', 'royal-smtp' ) . '</p>';
        }

        $output = '<div class="royal-smtp-debug-log">';
        
        foreach ( $log_entries as $entry ) {
            $level_class = 'level-' . intval( $entry['level'] );
            $output .= sprintf(
                '<div class="debug-entry %s"><span class="debug-time">[%s]</span> <span class="debug-message">%s</span></div>',
                esc_attr( $level_class ),
                esc_html( $entry['time'] ),
                esc_html( $entry['message'] )
            );
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Check if debug mode is enabled
     */
    public static function is_enabled() {
        $options = get_option( 'royal_smtp_options', array() );
        return ! empty( $options['debug_mode'] );
    }

    /**
     * Log custom debug message
     */
    public static function log( $message, $level = 1 ) {
        if ( ! self::is_enabled() ) {
            return;
        }

        $instance = self::get_instance();
        $instance->capture_debug( $message, $level );
    }
}
