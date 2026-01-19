<?php
/**
 * General Settings Tab
 *
 * @package RoyalSMTP
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$options = get_option( 'royal_smtp_options', array() );
?>

<form method="post" action="options.php" id="royal-smtp-general-form">
    <?php settings_fields( 'royal_smtp_group' ); ?>

    <div class="royal-smtp-settings-grid">

        <!-- Mailer Selection Card -->
        <div class="royal-smtp-card">
            <div class="royal-smtp-card-header">
                <span class="dashicons dashicons-email"></span>
                <?php esc_html_e( 'Mailer Type', 'royal-smtp' ); ?>
            </div>
            <div class="royal-smtp-card-body">
                <div class="royal-smtp-field-group">
                    <label class="royal-smtp-field-label"><?php esc_html_e( 'Select Mailer', 'royal-smtp' ); ?></label>
                    <select name="royal_smtp_options[mailer]" id="royal-smtp-mailer" class="royal-smtp-select">
                        <option value="default" <?php selected( $options['mailer'] ?? 'default', 'default' ); ?>>
                            <?php esc_html_e( 'Default PHP Mail', 'royal-smtp' ); ?>
                        </option>
                        <option value="smtp" <?php selected( $options['mailer'] ?? '', 'smtp' ); ?>>
                            <?php esc_html_e( 'SMTP', 'royal-smtp' ); ?>
                        </option>
                    </select>
                    <p class="royal-smtp-field-desc"><?php esc_html_e( 'Select SMTP for reliable email delivery.', 'royal-smtp' ); ?></p>
                </div>
            </div>
        </div>

        <!-- Features Card -->
        <div class="royal-smtp-card">
            <div class="royal-smtp-card-header">
                <span class="dashicons dashicons-admin-generic"></span>
                <?php esc_html_e( 'Features', 'royal-smtp' ); ?>
            </div>
            <div class="royal-smtp-card-body">
                <div class="royal-smtp-field-group">
                    <label class="royal-smtp-checkbox-label">
                        <input type="checkbox" name="royal_smtp_options[email_logging]" value="1" 
                               <?php checked( $options['email_logging'] ?? 0, 1 ); ?> />
                        <?php esc_html_e( 'Enable Email Logging', 'royal-smtp' ); ?>
                    </label>
                    <p class="royal-smtp-field-desc"><?php esc_html_e( 'Log all sent emails for debugging.', 'royal-smtp' ); ?></p>
                </div>
                <div class="royal-smtp-field-group">
                    <label class="royal-smtp-checkbox-label">
                        <input type="checkbox" name="royal_smtp_options[debug_mode]" value="1" 
                               <?php checked( $options['debug_mode'] ?? 0, 1 ); ?> />
                        <?php esc_html_e( 'Enable Debug Mode', 'royal-smtp' ); ?>
                    </label>
                    <p class="royal-smtp-field-desc"><?php esc_html_e( 'Capture detailed SMTP debug information.', 'royal-smtp' ); ?></p>
                </div>
                <div class="royal-smtp-field-group">
                    <label class="royal-smtp-checkbox-label">
                        <input type="checkbox" name="royal_smtp_options[queue_enabled]" value="1" 
                               <?php checked( $options['queue_enabled'] ?? 0, 1 ); ?> />
                        <?php esc_html_e( 'Enable Email Queue', 'royal-smtp' ); ?>
                    </label>
                    <p class="royal-smtp-field-desc"><?php esc_html_e( 'Queue emails for background processing.', 'royal-smtp' ); ?></p>
                </div>
            </div>
        </div>

        <!-- Notifications Card -->
        <div class="royal-smtp-card">
            <div class="royal-smtp-card-header">
                <span class="dashicons dashicons-bell"></span>
                <?php esc_html_e( 'Failure Notifications', 'royal-smtp' ); ?>
            </div>
            <div class="royal-smtp-card-body">
                <div class="royal-smtp-field-group">
                    <label class="royal-smtp-checkbox-label">
                        <input type="checkbox" name="royal_smtp_options[failure_notification]" value="1" 
                               <?php checked( $options['failure_notification'] ?? 0, 1 ); ?> />
                        <?php esc_html_e( 'Send notification on email failure', 'royal-smtp' ); ?>
                    </label>
                </div>
                <div class="royal-smtp-field-group">
                    <label class="royal-smtp-field-label"><?php esc_html_e( 'Notification Email', 'royal-smtp' ); ?></label>
                    <input type="email" name="royal_smtp_options[notification_email]" 
                           value="<?php echo esc_attr( $options['notification_email'] ?? get_option( 'admin_email' ) ); ?>" 
                           class="regular-text" />
                    <p class="royal-smtp-field-desc"><?php esc_html_e( 'Email to receive failure notifications.', 'royal-smtp' ); ?></p>
                </div>
            </div>
        </div>

    </div>

    <!-- Test Email Section -->
    <div class="royal-smtp-test-email-section">
        <div class="royal-smtp-card">
            <div class="royal-smtp-card-header">
                <span class="dashicons dashicons-email-alt2"></span>
                <?php esc_html_e( 'Send Test Email', 'royal-smtp' ); ?>
            </div>
            <div class="royal-smtp-card-body">
                <div class="royal-smtp-inline-form">
                    <input type="email" id="royal-smtp-test-email" 
                           value="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>"
                           placeholder="<?php echo esc_attr__( 'Enter email address', 'royal-smtp' ); ?>" 
                           class="regular-text" />
                    <button type="button" id="royal-smtp-send-test" class="button button-secondary">
                        <span class="dashicons dashicons-email"></span> <?php esc_html_e( 'Send Test', 'royal-smtp' ); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Save Button -->
    <div class="royal-smtp-save-area">
        <?php submit_button( __( 'Save Settings', 'royal-smtp' ), 'primary', 'submit', false ); ?>
    </div>
</form>
