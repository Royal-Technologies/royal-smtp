<?php
/**
 * Email Logs Tab
 *
 * @package RoyalSMTP
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$stats = Royal_SMTP_Logger::get_stats();
?>

<div class="royal-smtp-logs-section">

    <!-- Stats Cards -->
    <div class="royal-smtp-stats-grid">
        <div class="royal-smtp-stat-card">
            <span class="stat-icon dashicons dashicons-email-alt"></span>
            <div class="stat-content">
                <span class="stat-number"><?php echo esc_html( $stats['total'] ); ?></span>
                <span class="stat-label"><?php esc_html_e( 'Total Emails', 'royal-smtp' ); ?></span>
            </div>
        </div>
        <div class="royal-smtp-stat-card success">
            <span class="stat-icon dashicons dashicons-yes-alt"></span>
            <div class="stat-content">
                <span class="stat-number"><?php echo esc_html( $stats['sent'] ); ?></span>
                <span class="stat-label"><?php esc_html_e( 'Sent', 'royal-smtp' ); ?></span>
            </div>
        </div>
        <div class="royal-smtp-stat-card error">
            <span class="stat-icon dashicons dashicons-dismiss"></span>
            <div class="stat-content">
                <span class="stat-number"><?php echo esc_html( $stats['failed'] ); ?></span>
                <span class="stat-label"><?php esc_html_e( 'Failed', 'royal-smtp' ); ?></span>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="royal-smtp-action-bar">
        <div class="action-left">
            <select id="royal-smtp-log-filter" class="royal-smtp-select">
                <option value=""><?php esc_html_e( 'All Status', 'royal-smtp' ); ?></option>
                <option value="sent"><?php esc_html_e( 'Sent', 'royal-smtp' ); ?></option>
                <option value="failed"><?php esc_html_e( 'Failed', 'royal-smtp' ); ?></option>
            </select>
            <button type="button" id="royal-smtp-refresh-logs" class="button">
                <span class="dashicons dashicons-update"></span> <?php esc_html_e( 'Refresh', 'royal-smtp' ); ?>
            </button>
        </div>
        <div class="action-right">
            <button type="button" id="royal-smtp-clear-logs" class="button button-link-delete">
                <span class="dashicons dashicons-trash"></span> <?php esc_html_e( 'Clear All Logs', 'royal-smtp' ); ?>
            </button>
        </div>
    </div>

    <!-- Logs Table -->
    <table id="royal-smtp-logs-table" class="display" style="width:100%">
        <thead>
            <tr>
                <th><?php esc_html_e( 'ID', 'royal-smtp' ); ?></th>
                <th><?php esc_html_e( 'To', 'royal-smtp' ); ?></th>
                <th><?php esc_html_e( 'Subject', 'royal-smtp' ); ?></th>
                <th><?php esc_html_e( 'Status', 'royal-smtp' ); ?></th>
                <th><?php esc_html_e( 'Date', 'royal-smtp' ); ?></th>
                <th><?php esc_html_e( 'Actions', 'royal-smtp' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $logs = Royal_SMTP_Logger::get_logs( array( 'per_page' => 50 ) );
            foreach ( $logs as $log ) :
                $status_class = $log->status === 'sent' ? 'success' : 'error';
            ?>
            <tr>
                <td><?php echo esc_html( $log->id ); ?></td>
                <td><?php echo esc_html( $log->to_email ); ?></td>
                <td><?php echo esc_html( $log->subject ); ?></td>
                <td>
                    <span class="royal-smtp-status royal-smtp-status-<?php echo esc_attr( $status_class ); ?>">
                        <?php echo esc_html( ucfirst( $log->status ) ); ?>
                    </span>
                </td>
                <td><?php echo esc_html( date_i18n( 'd M Y H:i', strtotime( $log->created_at ) ) ); ?></td>
                <td>
                    <?php if ( $log->status === 'failed' && ! empty( $log->error_message ) ) : ?>
                        <button type="button" class="button button-small royal-smtp-view-error" 
                                data-error="<?php echo esc_attr( $log->error_message ); ?>">
                            <span class="dashicons dashicons-info"></span>
                        </button>
                    <?php endif; ?>
                    <button type="button" class="button button-small royal-smtp-delete-log" 
                            data-id="<?php echo esc_attr( $log->id ); ?>">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ( empty( $logs ) ) : ?>
        <div class="royal-smtp-empty-state">
            <span class="dashicons dashicons-email-alt"></span>
            <p><?php esc_html_e( 'No email logs found.', 'royal-smtp' ); ?></p>
            <p class="description"><?php esc_html_e( 'Enable email logging in General settings to start tracking emails.', 'royal-smtp' ); ?></p>
        </div>
    <?php endif; ?>

</div>
