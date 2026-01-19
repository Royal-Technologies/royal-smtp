<?php
/**
 * Tools Tab - Import/Export
 *
 * @package RoyalSMTP
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="royal-smtp-tools-section">

    <div class="royal-smtp-settings-grid">

        <!-- Export Card -->
        <div class="royal-smtp-card">
            <div class="royal-smtp-card-header">
                <span class="dashicons dashicons-download"></span>
                <?php esc_html_e( 'Export Settings', 'royal-smtp' ); ?>
            </div>
            <div class="royal-smtp-card-body">
                <p><?php esc_html_e( 'Export your Royal SMTP settings to a JSON file. This file can be imported into another site.', 'royal-smtp' ); ?></p>
                <div class="royal-smtp-notice info">
                    <span class="dashicons dashicons-info"></span>
                    <?php esc_html_e( 'Sensitive data like passwords are included in encrypted form.', 'royal-smtp' ); ?>
                </div>
                <button type="button" id="royal-smtp-export" class="button button-primary">
                    <span class="dashicons dashicons-download"></span> <?php esc_html_e( 'Export Settings', 'royal-smtp' ); ?>
                </button>
            </div>
        </div>

        <!-- Import Card -->
        <div class="royal-smtp-card">
            <div class="royal-smtp-card-header">
                <span class="dashicons dashicons-upload"></span>
                <?php esc_html_e( 'Import Settings', 'royal-smtp' ); ?>
            </div>
            <div class="royal-smtp-card-body">
                <p><?php esc_html_e( 'Import Royal SMTP settings from a previously exported JSON file.', 'royal-smtp' ); ?></p>
                <div class="royal-smtp-notice warning">
                    <span class="dashicons dashicons-warning"></span>
                    <?php esc_html_e( 'This will overwrite your current settings!', 'royal-smtp' ); ?>
                </div>
                <div class="royal-smtp-field-group">
                    <input type="file" id="royal-smtp-import-file" accept=".json" />
                </div>
                <button type="button" id="royal-smtp-import" class="button button-secondary">
                    <span class="dashicons dashicons-upload"></span> <?php esc_html_e( 'Import Settings', 'royal-smtp' ); ?>
                </button>
            </div>
        </div>

        <!-- Reset Card -->
        <div class="royal-smtp-card">
            <div class="royal-smtp-card-header">
                <span class="dashicons dashicons-image-rotate"></span>
                <?php esc_html_e( 'Reset Settings', 'royal-smtp' ); ?>
            </div>
            <div class="royal-smtp-card-body">
                <p><?php esc_html_e( 'Reset all settings to their default values.', 'royal-smtp' ); ?></p>
                <div class="royal-smtp-notice error">
                    <span class="dashicons dashicons-warning"></span>
                    <?php esc_html_e( 'This action cannot be undone!', 'royal-smtp' ); ?>
                </div>
                <button type="button" id="royal-smtp-reset" class="button button-link-delete">
                    <span class="dashicons dashicons-trash"></span> <?php esc_html_e( 'Reset to Defaults', 'royal-smtp' ); ?>
                </button>
            </div>
        </div>

        <!-- Database Card -->
        <div class="royal-smtp-card">
            <div class="royal-smtp-card-header">
                <span class="dashicons dashicons-database"></span>
                <?php esc_html_e( 'Database', 'royal-smtp' ); ?>
            </div>
            <div class="royal-smtp-card-body">
                <p><?php esc_html_e( 'Manage database tables used by the plugin.', 'royal-smtp' ); ?></p>
                
                <div class="royal-smtp-db-actions">
                    <div class="db-action-item">
                        <span class="dashicons dashicons-list-view"></span>
                        <div>
                            <strong><?php esc_html_e( 'Email Logs', 'royal-smtp' ); ?></strong>
                            <p><?php 
                                $log_count = Royal_SMTP_Logger::get_total_count();
                                printf( esc_html__( '%d records', 'royal-smtp' ), $log_count ); 
                            ?></p>
                        </div>
                        <button type="button" class="button button-small" id="royal-smtp-cleanup-logs">
                            <?php esc_html_e( 'Clean Old (30 days)', 'royal-smtp' ); ?>
                        </button>
                    </div>
                    
                    <div class="db-action-item">
                        <span class="dashicons dashicons-clock"></span>
                        <div>
                            <strong><?php esc_html_e( 'Email Queue', 'royal-smtp' ); ?></strong>
                            <p><?php 
                                $queue_stats = Royal_SMTP_Queue::get_stats();
                                printf( esc_html__( '%d records', 'royal-smtp' ), $queue_stats['total'] ); 
                            ?></p>
                        </div>
                        <button type="button" class="button button-small" id="royal-smtp-cleanup-queue">
                            <?php esc_html_e( 'Clear Sent', 'royal-smtp' ); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
