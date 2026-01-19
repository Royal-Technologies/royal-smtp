<?php
/**
 * About Tab
 *
 * @package RoyalSMTP
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="royal-smtp-about-container">
    
    <!-- Plugin Header -->
    <div class="royal-smtp-about-header">
        <span class="dashicons dashicons-email-alt"></span>
        <div style="display: grid; gap: 10px;">
            <h2 class="royal-smtp-about-title"><?php esc_html_e( 'Royal SMTP', 'royal-smtp' ); ?></h2>
            <span class="royal-smtp-about-version">v<?php echo esc_html( ROYAL_SMTP_VERSION ); ?></span>
        </div>
    </div>

    <!-- Description -->
    <div class="royal-smtp-about-section">
        <h3><span class="dashicons dashicons-info"></span> <?php esc_html_e( 'About This Plugin', 'royal-smtp' ); ?></h3>
        <p><?php esc_html_e( 'Royal SMTP is a powerful WordPress SMTP mailer plugin that helps you send emails reliably using your preferred email provider. With features like email logging, debug mode, queue system, and beautiful SweetAlert2 notifications, Royal SMTP ensures your emails are delivered successfully.', 'royal-smtp' ); ?></p>
    </div>

    <!-- Features Grid -->
    <div class="royal-smtp-about-section">
        <h3><span class="dashicons dashicons-star-filled"></span> <?php esc_html_e( 'Key Features', 'royal-smtp' ); ?></h3>
        <div class="royal-smtp-feature-grid">
            <div class="royal-smtp-feature-item">
                <span class="dashicons dashicons-cloud"></span>
                <?php esc_html_e( 'Multiple SMTP Providers', 'royal-smtp' ); ?>
            </div>
            <div class="royal-smtp-feature-item">
                <span class="dashicons dashicons-shield"></span>
                <?php esc_html_e( 'Password Encryption', 'royal-smtp' ); ?>
            </div>
            <div class="royal-smtp-feature-item">
                <span class="dashicons dashicons-list-view"></span>
                <?php esc_html_e( 'Email Logging', 'royal-smtp' ); ?>
            </div>
            <div class="royal-smtp-feature-item">
                <span class="dashicons dashicons-clock"></span>
                <?php esc_html_e( 'Email Queue System', 'royal-smtp' ); ?>
            </div>
            <div class="royal-smtp-feature-item">
                <span class="dashicons dashicons-admin-tools"></span>
                <?php esc_html_e( 'Debug Mode', 'royal-smtp' ); ?>
            </div>
            <div class="royal-smtp-feature-item">
                <span class="dashicons dashicons-yes-alt"></span>
                <?php esc_html_e( 'Connection Testing', 'royal-smtp' ); ?>
            </div>
            <div class="royal-smtp-feature-item">
                <span class="dashicons dashicons-download"></span>
                <?php esc_html_e( 'Import/Export Settings', 'royal-smtp' ); ?>
            </div>
            <div class="royal-smtp-feature-item">
                <span class="dashicons dashicons-bell"></span>
                <?php esc_html_e( 'Failure Notifications', 'royal-smtp' ); ?>
            </div>
            <div class="royal-smtp-feature-item">
                <span class="dashicons dashicons-translation"></span>
                <?php esc_html_e( 'Multi-language Support', 'royal-smtp' ); ?>
            </div>
            <div class="royal-smtp-feature-item">
                <span class="dashicons dashicons-smiley"></span>
                <?php esc_html_e( 'SweetAlert2 Popups', 'royal-smtp' ); ?>
            </div>
        </div>
    </div>

    <!-- Supported Providers -->
    <div class="royal-smtp-about-section">
        <h3><span class="dashicons dashicons-networking"></span> <?php esc_html_e( 'Supported Providers', 'royal-smtp' ); ?></h3>
        <div class="royal-smtp-providers-list">
            <span class="provider-tag">Gmail</span>
            <span class="provider-tag">Outlook/365</span>
            <span class="provider-tag">Zoho</span>
            <span class="provider-tag">Yahoo</span>
            <span class="provider-tag">Mailgun</span>
            <span class="provider-tag">SendGrid</span>
            <span class="provider-tag">Brevo</span>
            <span class="provider-tag">Amazon SES</span>
            <span class="provider-tag">Postmark</span>
            <span class="provider-tag">SparkPost</span>
            <span class="provider-tag">Custom SMTP</span>
        </div>
    </div>

    <!-- Developer Card -->
    <div class="royal-smtp-developer-card">
        <h3>👨‍💻 <?php esc_html_e( 'Developer', 'royal-smtp' ); ?></h3>
        <p class="company-name">Royal Technologies</p>
        <p class="company-desc"><?php esc_html_e( 'Professional Web Development & Design Solutions', 'royal-smtp' ); ?></p>
    </div>

    <!-- Contact Grid -->
    <div class="royal-smtp-contact-grid">
        <div class="royal-smtp-contact-card website">
            <h4>🌐 <?php esc_html_e( 'Website', 'royal-smtp' ); ?></h4>
            <a href="https://www.royaltechbd.com/" target="_blank">www.royaltechbd.com <span class="dashicons dashicons-external" style="font-size: 14px;"></span></a>
        </div>
        <div class="royal-smtp-contact-card email">
            <h4>📧 <?php esc_html_e( 'Email', 'royal-smtp' ); ?></h4>
            <a href="mailto:info@royaltechbd.com">info@royaltechbd.com</a>
        </div>
        <div class="royal-smtp-contact-card phone">
            <h4>📱 <?php esc_html_e( 'Mobile', 'royal-smtp' ); ?></h4>
            <a href="tel:+8801552333272">+880 1552-333272</a>
        </div>
        <div class="royal-smtp-contact-card address">
            <h4>📍 <?php esc_html_e( 'Address', 'royal-smtp' ); ?></h4>
            <p>D - 408, Housing Estate<br>Kushtia - 7000, Bangladesh</p>
        </div>
    </div>

    <!-- Footer -->
    <div class="royal-smtp-footer-text">
        <?php printf( 
            /* translators: 1: plugin version, 2: current year */
            esc_html__( 'Royal SMTP v%1$s | © %2$s Royal Technologies', 'royal-smtp' ), 
            ROYAL_SMTP_VERSION, 
            date( 'Y' ) 
        ); ?>
    </div>

</div>
