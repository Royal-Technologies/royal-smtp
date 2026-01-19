# Royal SMTP

![Version](https://img.shields.io/badge/version-26.01.19-blue.svg)
![License](https://img.shields.io/badge/license-GPL--2.0%2B-green.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)

Reliable WordPress Email Delivery - A powerful SMTP mailer plugin with email logging, queue system, and multiple provider support.

## 📧 Description

**Royal SMTP** ensures reliable email delivery from your WordPress site by connecting to trusted SMTP servers. Say goodbye to emails landing in spam folders!

### Why Choose Royal SMTP?

WordPress's default `wp_mail()` function often fails to deliver emails reliably. Royal SMTP solves this by:
- Connecting your site to professional SMTP servers
- Providing detailed email logging
- Supporting multiple email providers
- Offering a beautiful, modern admin interface

## 🚀 Features

### Multiple SMTP Providers
- **Gmail** (OAuth 2.0 & SMTP)
- **Outlook / Office 365** (OAuth 2.0 & SMTP)
- **Yahoo Mail**
- **Amazon SES**
- **SendGrid**
- **Mailgun**
- **SMTP.com**
- **Brevo (Sendinblue)**
- **Postmark**
- **SparkPost**
- **Custom SMTP Server**

### Core Features
- ✅ **One-Click Test Email** - Instantly verify your SMTP configuration
- ✅ **Connection Test** - Test SMTP server connectivity before sending
- ✅ **Email Logs** - Track every email with detailed logging
- ✅ **Email Queue** - Queue emails for batch processing
- ✅ **Secure Authentication** - TLS/SSL encryption support
- ✅ **SweetAlert2 Notifications** - Beautiful, modern alerts
- ✅ **DataTables Integration** - Powerful email log management
- ✅ **Multilingual Ready** - Bengali (বাংলা) included

### Admin Features
- Modern, intuitive dashboard
- Tab-based settings organization
- Real-time AJAX operations
- Responsive design for all devices
- Loading states with visual feedback

## 📦 Installation

### From GitHub (Manual)
1. Download the latest release from [GitHub](https://github.com/mehdiakram/royal-smtp)
2. Upload the `royal-smtp` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to **Royal SMTP** in the admin menu to configure

### Using Git
```bash
cd wp-content/plugins/
git clone https://github.com/mehdiakram/royal-smtp.git
```

## ⚙️ Configuration

### Basic Setup
1. Navigate to **Royal SMTP** in your WordPress admin
2. Select your email provider (Gmail, Outlook, etc.)
3. Enter your SMTP credentials
4. Click "Save Settings"
5. Send a test email to verify

### Gmail Setup
1. Select "Gmail" as provider
2. Use your Gmail address as SMTP username
3. Generate an "App Password" from Google Account settings
4. Use the App Password as SMTP password
5. SMTP Host: `smtp.gmail.com`
6. SMTP Port: `587` (TLS) or `465` (SSL)

### Custom SMTP
1. Select "Other" as provider
2. Enter your SMTP host (e.g., `mail.yourdomain.com`)
3. Enter your SMTP port (commonly `587` or `465`)
4. Choose encryption (TLS recommended)
5. Enter your email and password

## 🖥️ Screenshots

The plugin features a modern, card-based interface with:
- Clean settings tabs
- Visual provider selection
- Real-time test results
- Comprehensive email logs

## 📋 Changelog

See [CHANGELOG.md](CHANGELOG.md) for a detailed version history.

## 🔧 Requirements

- **WordPress:** 5.8 or higher
- **PHP:** 7.4 or higher
- **MySQL:** 5.6 or higher

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 🐛 Bug Reports

If you discover any bugs, please create an issue on [GitHub Issues](https://github.com/mehdiakram/royal-smtp/issues).

## 📄 License

This project is licensed under the GPL-2.0+ License - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

**Royal Technologies**
- Website: [https://royaltechbd.com](https://royaltechbd.com)
- GitHub: [@mehdiakram](https://github.com/mehdiakram)

## 🙏 Credits

- [PHPMailer](https://github.com/PHPMailer/PHPMailer) - Email sending library
- [SweetAlert2](https://sweetalert2.github.io/) - Beautiful alerts
- [DataTables](https://datatables.net/) - Advanced table features

---

**Made with ❤️ in Bangladesh**
