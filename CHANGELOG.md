# Changelog

All notable changes to Royal SMTP will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [26.01.19] - 2026-01-19

### Added
- Initial public release on GitHub
- Multiple SMTP provider support (Gmail, Outlook, Yahoo, Amazon SES, SendGrid, Mailgun, SMTP.com, Brevo, Postmark, SparkPost)
- One-click test email functionality
- SMTP connection test feature
- Comprehensive email logging system
- Email queue system for batch processing
- SweetAlert2 integration for beautiful notifications
- DataTables integration for email log management
- Loading states with visual feedback during AJAX operations
- Bengali (বাংলা) language translation
- Tab-based settings interface
- Secure credential storage
- TLS/SSL encryption support
- Auto-close popup option for notifications
- From Name defaults to site name
- From Email defaults to admin email

### Features
- **SMTP Configuration Tab**
  - Provider selection with pre-configured settings
  - Custom SMTP server support
  - Authentication options (None, Plain, Login, CRAM-MD5)
  - Encryption options (None, TLS, SSL)
  
- **Email Settings Tab**
  - From Name configuration
  - From Email configuration
  - Force From Name/Email options
  
- **Test Email Tab**
  - Send test email with custom recipient
  - Real-time delivery status
  - Detailed error reporting
  
- **Connection Test Tab**
  - Test SMTP server connectivity
  - Port availability check
  - Authentication verification
  
- **Email Logs Tab**
  - DataTables powered log viewer
  - Search, sort, and filter capabilities
  - View email details in modal
  - Delete individual logs
  - Clear all logs option
  - Resend failed emails
  
- **Queue Tab**
  - View queued emails
  - Process queue manually
  - Enable/disable queue system
  
- **Advanced Tab**
  - Debug mode toggle
  - Email logging toggle
  - Failure notification settings

### Security
- Nonce verification for all AJAX requests
- Capability checks for admin actions
- Sanitization of all input data
- Secure password storage

### Technical
- WordPress 5.8+ compatibility
- PHP 7.4+ requirement
- Clean, documented code
- Proper WordPress hooks and filters
- Modular class-based architecture

---

## Upgrade Notice

### 26.01.19
Initial release - Install and configure your SMTP settings for reliable email delivery.
