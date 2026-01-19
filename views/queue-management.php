<?php
/**
 * Queue Management Tab
 *
 * @package RoyalSMTP
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$stats = Royal_SMTP_Queue::get_stats();
$is_enabled = Royal_SMTP_Queue::is_enabled();
?>

<div class="royal-smtp-queue-section">

    <?php if ( ! $is_enabled ) : ?>
        <div class="royal-smtp-notice warning">
            <span class="dashicons dashicons-warning"></span>
            <?php esc_html_e( 'Email queue is disabled. Enable it in General settings to use this feature.', 'royal-smtp' ); ?>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="royal-smtp-stats-grid">
        <div class="royal-smtp-stat-card">
            <span class="stat-icon dashicons dashicons-clock"></span>
            <div class="stat-content">
                <span class="stat-number"><?php echo esc_html( $stats['pending'] ); ?></span>
                <span class="stat-label"><?php esc_html_e( 'Pending', 'royal-smtp' ); ?></span>
            </div>
        </div>
        <div class="royal-smtp-stat-card info">
            <span class="stat-icon dashicons dashicons-update"></span>
            <div class="stat-content">
                <span class="stat-number"><?php echo esc_html( $stats['processing'] ); ?></span>
                <span class="stat-label"><?php esc_html_e( 'Processing', 'royal-smtp' ); ?></span>
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
            <select id="royal-smtp-queue-filter" class="royal-smtp-select">
                <option value=""><?php esc_html_e( 'All Status', 'royal-smtp' ); ?></option>
                <option value="pending"><?php esc_html_e( 'Pending', 'royal-smtp' ); ?></option>
                <option value="processing"><?php esc_html_e( 'Processing', 'royal-smtp' ); ?></option>
                <option value="sent"><?php esc_html_e( 'Sent', 'royal-smtp' ); ?></option>
                <option value="failed"><?php esc_html_e( 'Failed', 'royal-smtp' ); ?></option>
            </select>
            <button type="button" id="royal-smtp-refresh-queue" class="button">
                <span class="dashicons dashicons-update"></span> <?php esc_html_e( 'Refresh', 'royal-smtp' ); ?>
            </button>
        </div>
        <div class="action-right">
            <button type="button" id="royal-smtp-clear-sent" class="button">
                <span class="dashicons dashicons-dismiss"></span> <?php esc_html_e( 'Clear Sent', 'royal-smtp' ); ?>
            </button>
            <button type="button" id="royal-smtp-clear-queue" class="button button-link-delete">
                <span class="dashicons dashicons-trash"></span> <?php esc_html_e( 'Clear All', 'royal-smtp' ); ?>
            </button>
        </div>
    </div>

    <!-- Queue Table -->
    <table id="royal-smtp-queue-table" class="display" style="width:100%">
        <thead>
            <tr>
                <th><?php esc_html_e( 'ID', 'royal-smtp' ); ?></th>
                <th><?php esc_html_e( 'To', 'royal-smtp' ); ?></th>
                <th><?php esc_html_e( 'Subject', 'royal-smtp' ); ?></th>
                <th><?php esc_html_e( 'Status', 'royal-smtp' ); ?></th>
                <th><?php esc_html_e( 'Attempts', 'royal-smtp' ); ?></th>
                <th><?php esc_html_e( 'Created', 'royal-smtp' ); ?></th>
                <th><?php esc_html_e( 'Actions', 'royal-smtp' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $queue = Royal_SMTP_Queue::get_queue( array( 'per_page' => 50 ) );
            foreach ( $queue as $item ) :
                $status_class = 'info';
                if ( $item->status === 'sent' ) $status_class = 'success';
                if ( $item->status === 'failed' ) $status_class = 'error';
                if ( $item->status === 'pending' ) $status_class = 'warning';
            ?>
            <tr>
                <td><?php echo esc_html( $item->id ); ?></td>
                <td><?php echo esc_html( $item->to_email ); ?></td>
                <td><?php echo esc_html( $item->subject ); ?></td>
                <td>
                    <span class="royal-smtp-status royal-smtp-status-<?php echo esc_attr( $status_class ); ?>">
                        <?php echo esc_html( ucfirst( $item->status ) ); ?>
                    </span>
                </td>
                <td><?php echo esc_html( $item->attempts . '/' . $item->max_attempts ); ?></td>
                <td><?php echo esc_html( date_i18n( 'd M Y H:i', strtotime( $item->created_at ) ) ); ?></td>
                <td>
                    <?php if ( $item->status === 'failed' ) : ?>
                        <button type="button" class="button button-small royal-smtp-retry-queue" 
                                data-id="<?php echo esc_attr( $item->id ); ?>">
                            <span class="dashicons dashicons-controls-repeat"></span>
                        </button>
                    <?php endif; ?>
                    <button type="button" class="button button-small royal-smtp-delete-queue" 
                            data-id="<?php echo esc_attr( $item->id ); ?>">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ( empty( $queue ) ) : ?>
        <div class="royal-smtp-empty-state">
            <span class="dashicons dashicons-clock"></span>
            <p><?php esc_html_e( 'No emails in queue.', 'royal-smtp' ); ?></p>
        </div>
    <?php endif; ?>

</div>
