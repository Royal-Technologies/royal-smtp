<?php
/**
 * Debug Logs Tab
 *
 * @package RoyalSMTP
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$is_enabled = Royal_SMTP_Debug::is_enabled();
$debug_log = Royal_SMTP_Debug::get_debug_log();
?>

<div class="royal-smtp-debug-section">

    <?php if ( ! $is_enabled ) : ?>
        <div class="royal-smtp-notice warning">
            <span class="dashicons dashicons-warning"></span>
            <?php esc_html_e( 'Debug mode is disabled. Enable it in General settings to capture SMTP debug information.', 'royal-smtp' ); ?>
        </div>
    <?php endif; ?>

    <!-- Debug Info Card -->
    <div class="royal-smtp-card">
        <div class="royal-smtp-card-header">
            <span class="dashicons dashicons-admin-tools"></span>
            <?php esc_html_e( 'SMTP Debug Log', 'royal-smtp' ); ?>
            <div class="royal-smtp-header-actions">
                <button type="button" id="royal-smtp-clear-debug" class="button button-small">
                    <span class="dashicons dashicons-trash"></span> <?php esc_html_e( 'Clear Log', 'royal-smtp' ); ?>
                </button>
            </div>
        </div>
        <div class="royal-smtp-card-body">
            <?php if ( ! empty( $debug_log ) ) : ?>
                <div class="royal-smtp-debug-log">
                    <?php foreach ( $debug_log as $entry ) : ?>
                        <div class="debug-entry level-<?php echo esc_attr( $entry['level'] ); ?>">
                            <span class="debug-time">[<?php echo esc_html( $entry['time'] ); ?>]</span>
                            <span class="debug-message"><?php echo esc_html( $entry['message'] ); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="royal-smtp-empty-state">
                    <span class="dashicons dashicons-admin-tools"></span>
                    <p><?php esc_html_e( 'No debug information available.', 'royal-smtp' ); ?></p>
                    <p class="description"><?php esc_html_e( 'Send a test email with debug mode enabled to see SMTP communication details.', 'royal-smtp' ); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- System Info Card -->
    <div class="royal-smtp-card">
        <div class="royal-smtp-card-header">
            <span class="dashicons dashicons-info"></span>
            <?php esc_html_e( 'System Information', 'royal-smtp' ); ?>
        </div>
        <div class="royal-smtp-card-body">
            <table class="royal-smtp-info-table">
                <tr>
                    <th><?php esc_html_e( 'PHP Version', 'royal-smtp' ); ?></th>
                    <td><?php echo esc_html( phpversion() ); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'WordPress Version', 'royal-smtp' ); ?></th>
                    <td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Plugin Version', 'royal-smtp' ); ?></th>
                    <td><?php echo esc_html( ROYAL_SMTP_VERSION ); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'OpenSSL', 'royal-smtp' ); ?></th>
                    <td>
                        <?php if ( function_exists( 'openssl_encrypt' ) ) : ?>
                            <span class="royal-smtp-status royal-smtp-status-success"><?php esc_html_e( 'Available', 'royal-smtp' ); ?></span>
                        <?php else : ?>
                            <span class="royal-smtp-status royal-smtp-status-error"><?php esc_html_e( 'Not Available', 'royal-smtp' ); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'fsockopen', 'royal-smtp' ); ?></th>
                    <td>
                        <?php if ( function_exists( 'fsockopen' ) ) : ?>
                            <span class="royal-smtp-status royal-smtp-status-success"><?php esc_html_e( 'Available', 'royal-smtp' ); ?></span>
                        <?php else : ?>
                            <span class="royal-smtp-status royal-smtp-status-error"><?php esc_html_e( 'Not Available', 'royal-smtp' ); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Server', 'royal-smtp' ); ?></th>
                    <td><?php echo esc_html( $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ); ?></td>
                </tr>
            </table>
        </div>
    </div>

</div>
