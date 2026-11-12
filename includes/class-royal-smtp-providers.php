<?php
/**
 * SMTP Providers Class - Pre-configured provider settings
 *
 * @package RoyalSMTP
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Royal_SMTP_Providers {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {}

    /**
     * Get all available providers
     */
    public static function get_providers() {
        return array(
            'other' => array(
                'name'        => __( 'Other SMTP', 'royal-smtp' ),
                'description' => __( 'Use custom SMTP server settings', 'royal-smtp' ),
                'icon'        => 'dashicons-email-alt',
                'host'        => '',
                'port'        => 587,
                'encryption'  => 'tls',
                'auth'        => true,
            ),
            'gmail' => array(
                'name'        => __( 'Gmail / Google Workspace', 'royal-smtp' ),
                'description' => __( 'Use Gmail or Google Workspace SMTP', 'royal-smtp' ),
                'icon'        => 'dashicons-google',
                'host'        => 'smtp.gmail.com',
                'port'        => 587,
                'encryption'  => 'tls',
                'auth'        => true,
                'note'        => __( '<strong>Direct Connection (No extra plugins or Cloud API setup needed):</strong> Turn on 2-Step Verification in your Google Account and generate a 16-character App Password to paste into the Password field.', 'royal-smtp' ) . ' <a href="https://myaccount.google.com/apppasswords" target="_blank">' . __( 'Generate Google App Password &rarr;', 'royal-smtp' ) . '</a>',
            ),
            'zoho' => array(
                'name'        => __( 'Zoho Mail', 'royal-smtp' ),
                'description' => __( 'Use Zoho Mail SMTP', 'royal-smtp' ),
                'icon'        => 'dashicons-email-alt2',
                'host'        => 'smtp.zoho.com',
                'port'        => 587,
                'encryption'  => 'tls',
                'auth'        => true,
                'note'        => __( '<strong>Auto-Connect without extra plugins:</strong> Enter your full Zoho email as Username. Generate an Application-Specific Password from Zoho Security settings.', 'royal-smtp' ) . ' <a href="https://accounts.zoho.com/home#security/security_pwd" target="_blank">' . __( 'Generate Zoho App Password &rarr;', 'royal-smtp' ) . '</a><br><small>' . __( 'Regional hosts: EU (smtp.zoho.eu), India (smtp.zoho.in), Australia (smtp.zoho.com.au)', 'royal-smtp' ) . '</small>',
            ),
            'zeptomail' => array(
                'name'        => __( 'Zoho ZeptoMail', 'royal-smtp' ),
                'description' => __( 'Transactional email service by Zoho', 'royal-smtp' ),
                'icon'        => 'dashicons-email-alt',
                'host'        => 'smtp.zeptomail.com',
                'port'        => 587,
                'encryption'  => 'tls',
                'auth'        => true,
                'user'        => 'emailapikey',
                'note'        => __( '<strong>Auto-Connect without extra plugins:</strong> Username is automatically preset to <code>emailapikey</code>. Simply copy your Send Mail Token from your ZeptoMail Mail Agent setup and paste it into the Password field.', 'royal-smtp' ) . ' <a href="https://zeptomail.zoho.com" target="_blank">' . __( 'Open ZeptoMail Console &rarr;', 'royal-smtp' ) . '</a><br><small>' . __( 'Regional hosts: EU (smtp.zeptomail.eu), India (smtp.zeptomail.in), Australia (smtp.zeptomail.com.au)', 'royal-smtp' ) . '</small>',
            ),
            'outlook' => array(
                'name'        => __( 'Outlook / Microsoft 365', 'royal-smtp' ),
                'description' => __( 'Use Outlook or Microsoft 365 SMTP', 'royal-smtp' ),
                'icon'        => 'dashicons-email',
                'host'        => 'smtp.office365.com',
                'port'        => 587,
                'encryption'  => 'tls',
                'auth'        => true,
                'note'        => __( 'Requires App Password if 2FA enabled.', 'royal-smtp' ) . ' <a href="https://account.live.com/proofs/AppPassword" target="_blank">' . __( 'Create App Password', 'royal-smtp' ) . '</a>',
            ),
            'yahoo' => array(
                'name'        => __( 'Yahoo Mail', 'royal-smtp' ),
                'description' => __( 'Use Yahoo Mail SMTP', 'royal-smtp' ),
                'icon'        => 'dashicons-email',
                'host'        => 'smtp.mail.yahoo.com',
                'port'        => 587,
                'encryption'  => 'tls',
                'auth'        => true,
                'note'        => __( 'Requires App Password.', 'royal-smtp' ) . ' <a href="https://login.yahoo.com/account/security/app-passwords" target="_blank">' . __( 'Create App Password', 'royal-smtp' ) . '</a>',
            ),
            'mailgun' => array(
                'name'        => __( 'Mailgun', 'royal-smtp' ),
                'description' => __( 'Transactional email service', 'royal-smtp' ),
                'icon'        => 'dashicons-email-alt',
                'host'        => 'smtp.mailgun.org',
                'port'        => 587,
                'encryption'  => 'tls',
                'auth'        => true,
            ),
            'sendgrid' => array(
                'name'        => __( 'SendGrid', 'royal-smtp' ),
                'description' => __( 'Cloud email delivery service', 'royal-smtp' ),
                'icon'        => 'dashicons-email-alt',
                'host'        => 'smtp.sendgrid.net',
                'port'        => 587,
                'encryption'  => 'tls',
                'auth'        => true,
                'user'        => 'apikey',
                'note'        => __( 'Username is preset to "apikey". Enter your SendGrid API key as password.', 'royal-smtp' ),
            ),
            'sendinblue' => array(
                'name'        => __( 'Brevo (Sendinblue)', 'royal-smtp' ),
                'description' => __( 'Marketing and transactional email', 'royal-smtp' ),
                'icon'        => 'dashicons-email-alt',
                'host'        => 'smtp-relay.brevo.com',
                'port'        => 587,
                'encryption'  => 'tls',
                'auth'        => true,
            ),
            'aws_ses' => array(
                'name'        => __( 'Amazon SES', 'royal-smtp' ),
                'description' => __( 'Amazon Simple Email Service', 'royal-smtp' ),
                'icon'        => 'dashicons-cloud',
                'host'        => 'email-smtp.us-east-1.amazonaws.com',
                'port'        => 587,
                'encryption'  => 'tls',
                'auth'        => true,
                'note'        => __( 'Replace region in hostname (e.g., us-east-1, eu-west-1)', 'royal-smtp' ),
            ),
            'postmark' => array(
                'name'        => __( 'Postmark', 'royal-smtp' ),
                'description' => __( 'Transactional email for web apps', 'royal-smtp' ),
                'icon'        => 'dashicons-email-alt',
                'host'        => 'smtp.postmarkapp.com',
                'port'        => 587,
                'encryption'  => 'tls',
                'auth'        => true,
            ),
            'sparkpost' => array(
                'name'        => __( 'SparkPost', 'royal-smtp' ),
                'description' => __( 'Email delivery service', 'royal-smtp' ),
                'icon'        => 'dashicons-email-alt',
                'host'        => 'smtp.sparkpostmail.com',
                'port'        => 587,
                'encryption'  => 'tls',
                'auth'        => true,
                'note'        => __( 'Use "SMTP_Injection" as username', 'royal-smtp' ),
            ),
        );
    }

    /**
     * Get single provider config
     */
    public static function get_provider( $provider_id ) {
        $providers = self::get_providers();
        return isset( $providers[ $provider_id ] ) ? $providers[ $provider_id ] : $providers['other'];
    }

    /**
     * Get provider settings as JSON for JavaScript
     */
    public static function get_providers_json() {
        return wp_json_encode( self::get_providers() );
    }
}
