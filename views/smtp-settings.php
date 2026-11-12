<?php
/**
 * SMTP Settings Tab
 *
 * @package RoyalSMTP
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$options = get_option( 'royal_smtp_options', array() );
$providers = Royal_SMTP_Providers::get_providers();
?>

<form method="post" action="options.php" id="royal-smtp-smtp-form">
    <?php settings_fields( 'royal_smtp_group' ); ?>

    <div class="royal-smtp-settings-grid">

        <!-- Provider Selection Card -->
        <div class="royal-smtp-card royal-smtp-card-full">
            <div class="royal-smtp-card-header">
                <span class="dashicons dashicons-cloud"></span>
                <?php esc_html_e( 'Email Provider', 'royal-smtp' ); ?>
            </div>
            <div class="royal-smtp-card-body">
                <div class="royal-smtp-providers-grid">
                    <?php foreach ( $providers as $key => $provider ) : ?>
                        <label class="royal-smtp-provider-item <?php echo ( $options['provider'] ?? 'other' ) === $key ? 'active' : ''; ?>">
                            <input type="radio" name="royal_smtp_options[provider]" value="<?php echo esc_attr( $key ); ?>" 
                                   <?php checked( $options['provider'] ?? 'other', $key ); ?> />
                            <span class="dashicons <?php echo esc_attr( $provider['icon'] ); ?>"></span>
                            <span class="provider-name"><?php echo esc_html( $provider['name'] ); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <!-- Provider Note & Auto-Connect Instructions -->
                <div id="royal-smtp-provider-note" class="royal-smtp-notice" style="display:none; margin-top: 15px;"></div>
            </div>
        </div>

        <!-- SMTP Server Card -->
        <div class="royal-smtp-card">
            <div class="royal-smtp-card-header">
                <span class="dashicons dashicons-admin-site"></span>
                <?php esc_html_e( 'SMTP Server', 'royal-smtp' ); ?>
            </div>
            <div class="royal-smtp-card-body">
                <div class="royal-smtp-field-group">
                    <label class="royal-smtp-field-label"><?php esc_html_e( 'SMTP Host', 'royal-smtp' ); ?></label>
                    <input type="text" name="royal_smtp_options[smtp_host]" id="smtp_host"
                           value="<?php echo esc_attr( $options['smtp_host'] ?? '' ); ?>" 
                           class="regular-text" 
                           placeholder="smtp.example.com" />
                </div>
                <div class="royal-smtp-field-group">
                    <label class="royal-smtp-field-label"><?php esc_html_e( 'Encryption', 'royal-smtp' ); ?></label>
                    <div class="royal-smtp-radio-group">
                        <label>
                            <input type="radio" name="royal_smtp_options[smtp_encryption]" value="none" id="smtp_encryption_none"
                                   <?php checked( $options['smtp_encryption'] ?? 'none', 'none' ); ?> />
                            <?php esc_html_e( 'None', 'royal-smtp' ); ?>
                        </label>
                        <label>
                            <input type="radio" name="royal_smtp_options[smtp_encryption]" value="ssl" id="smtp_encryption_ssl"
                                   <?php checked( $options['smtp_encryption'] ?? '', 'ssl' ); ?> />
                            <?php esc_html_e( 'SSL', 'royal-smtp' ); ?>
                        </label>
                        <label>
                            <input type="radio" name="royal_smtp_options[smtp_encryption]" value="tls" id="smtp_encryption_tls"
                                   <?php checked( $options['smtp_encryption'] ?? '', 'tls' ); ?> />
                            <?php esc_html_e( 'TLS', 'royal-smtp' ); ?>
                        </label>
                    </div>
                </div>
                <div class="royal-smtp-field-group">
                    <label class="royal-smtp-field-label"><?php esc_html_e( 'SMTP Port', 'royal-smtp' ); ?></label>
                    <input type="number" name="royal_smtp_options[smtp_port]" id="smtp_port"
                           value="<?php echo esc_attr( $options['smtp_port'] ?? 587 ); ?>" 
                           class="small-text" />
                    <p class="royal-smtp-field-desc"><?php esc_html_e( 'TLS: 587, SSL: 465, None: 25', 'royal-smtp' ); ?></p>
                </div>
            </div>
        </div>

        <!-- Authentication Card -->
        <div class="royal-smtp-card">
            <div class="royal-smtp-card-header">
                <span class="dashicons dashicons-lock"></span>
                <?php esc_html_e( 'Authentication', 'royal-smtp' ); ?>
            </div>
            <div class="royal-smtp-card-body">
                <div class="royal-smtp-field-group">
                    <label class="royal-smtp-checkbox-label">
                        <input type="checkbox" name="royal_smtp_options[smtp_auth]" value="1" id="smtp_auth"
                               <?php checked( $options['smtp_auth'] ?? 0, 1 ); ?> />
                        <?php esc_html_e( 'Enable Authentication', 'royal-smtp' ); ?>
                    </label>
                </div>
                <div class="royal-smtp-field-group">
                    <label class="royal-smtp-field-label"><?php esc_html_e( 'Username', 'royal-smtp' ); ?></label>
                    <input type="text" name="royal_smtp_options[smtp_user]" 
                           value="<?php echo esc_attr( $options['smtp_user'] ?? '' ); ?>" 
                           class="regular-text" 
                           autocomplete="off" />
                </div>
                <div class="royal-smtp-field-group">
                    <label class="royal-smtp-field-label"><?php esc_html_e( 'Password', 'royal-smtp' ); ?></label>
                    <div class="royal-smtp-password-field">
                        <input type="password" name="royal_smtp_options[smtp_pass]" id="smtp_pass"
                               value="<?php echo ! empty( $options['smtp_pass'] ) ? '••••••••••••' : ''; ?>" 
                               class="regular-text" 
                               autocomplete="new-password" />
                        <button type="button" class="button royal-smtp-toggle-password">
                            <span class="dashicons dashicons-visibility"></span>
                        </button>
                    </div>
                    <p class="royal-smtp-field-desc"><?php esc_html_e( 'Password is encrypted before storage.', 'royal-smtp' ); ?></p>
                </div>
            </div>
        </div>

        <!-- From Email/Name Card -->
        <div class="royal-smtp-card">
            <div class="royal-smtp-card-header">
                <span class="dashicons dashicons-admin-users"></span>
                <?php esc_html_e( 'From Settings', 'royal-smtp' ); ?>
            </div>
            <div class="royal-smtp-card-body">
                <div class="royal-smtp-field-group">
                    <label class="royal-smtp-field-label"><?php esc_html_e( 'From Email', 'royal-smtp' ); ?></label>
                    <input type="email" name="royal_smtp_options[from_email]" 
                           value="<?php echo esc_attr( $options['from_email'] ?? '' ); ?>" 
                           class="regular-text" 
                           placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" />
                    <p class="royal-smtp-field-desc"><?php esc_html_e( 'The email address used as the sender.', 'royal-smtp' ); ?></p>
                </div>
                <div class="royal-smtp-field-group">
                    <label class="royal-smtp-field-label"><?php esc_html_e( 'From Name', 'royal-smtp' ); ?></label>
                    <input type="text" name="royal_smtp_options[from_name]" 
                           value="<?php echo esc_attr( $options['from_name'] ?? '' ); ?>" 
                           class="regular-text" 
                           placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
                    <p class="royal-smtp-field-desc"><?php esc_html_e( 'The name used as the sender.', 'royal-smtp' ); ?></p>
                </div>
            </div>
        </div>

        <!-- Connection Test Card -->
        <div class="royal-smtp-card">
            <div class="royal-smtp-card-header">
                <span class="dashicons dashicons-yes-alt"></span>
                <?php esc_html_e( 'Connection Test', 'royal-smtp' ); ?>
            </div>
            <div class="royal-smtp-card-body">
                <p><?php esc_html_e( 'Test SMTP connection without sending an email.', 'royal-smtp' ); ?></p>
                <button type="button" id="royal-smtp-test-connection" class="button button-secondary">
                    <span class="dashicons dashicons-update"></span> <?php esc_html_e( 'Test Connection', 'royal-smtp' ); ?>
                </button>
                <div id="royal-smtp-connection-result"></div>
            </div>
        </div>

    </div>

    <!-- Save Button -->
    <div class="royal-smtp-save-area">
        <?php submit_button( __( 'Save SMTP Settings', 'royal-smtp' ), 'primary', 'submit', false ); ?>
    </div>
</form>
