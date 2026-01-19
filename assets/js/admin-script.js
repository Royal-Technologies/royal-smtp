/**
 * Royal SMTP Admin JavaScript
 *
 * @package RoyalSMTP
 */

(function ($) {
    'use strict';

    // Initialize when DOM is ready
    $(document).ready(function () {
        RoyalSMTP.init();
    });

    // Main object
    window.RoyalSMTP = {

        init: function () {
            this.initProviderSelection();
            this.initEncryptionPortSync();
            this.initPasswordToggle();
            this.initFormSubmit();
            this.initTestEmail();
            this.initTestConnection();
            this.initDataTables();
            this.initLogActions();
            this.initQueueActions();
            this.initDebugActions();
            this.initImportExport();
            this.initToolsActions();
        },

        // Provider selection
        initProviderSelection: function () {
            var self = this;

            $('.royal-smtp-provider-item').on('click', function () {
                var $item = $(this);
                var provider = $item.find('input').val();

                // Update active state
                $('.royal-smtp-provider-item').removeClass('active');
                $item.addClass('active');
                $item.find('input').prop('checked', true);

                // Auto-fill settings
                if (royalSmtp.providers && royalSmtp.providers[provider]) {
                    var settings = royalSmtp.providers[provider];

                    $('#smtp_host').val(settings.host);
                    $('#smtp_port').val(settings.port);

                    // Encryption
                    $('input[name="royal_smtp_options[smtp_encryption]"]').prop('checked', false);
                    $('#smtp_encryption_' + settings.encryption).prop('checked', true);

                    // Auth
                    $('#smtp_auth').prop('checked', settings.auth);

                    // Show note if available
                    if (settings.note) {
                        $('#royal-smtp-provider-note')
                            .html('<span class="dashicons dashicons-info"></span> ' + settings.note)
                            .addClass('info')
                            .show();
                    } else {
                        $('#royal-smtp-provider-note').hide();
                    }
                }
            });
        },

        // Encryption to Port sync
        initEncryptionPortSync: function () {
            $('input[name="royal_smtp_options[smtp_encryption]"]').on('change', function () {
                var encryption = $(this).val();
                var port = 587; // default TLS

                if (encryption === 'ssl') {
                    port = 465;
                } else if (encryption === 'none') {
                    port = 25;
                } else if (encryption === 'tls') {
                    port = 587;
                }

                $('#smtp_port').val(port);
            });
        },

        // Password toggle
        initPasswordToggle: function () {
            $('.royal-smtp-toggle-password').on('click', function () {
                var $btn = $(this);
                var $input = $btn.siblings('input');
                var type = $input.attr('type');

                if (type === 'password') {
                    $input.attr('type', 'text');
                    $btn.find('.dashicons').removeClass('dashicons-visibility').addClass('dashicons-hidden');
                } else {
                    $input.attr('type', 'password');
                    $btn.find('.dashicons').removeClass('dashicons-hidden').addClass('dashicons-visibility');
                }
            });
        },

        // Form submit with SweetAlert2 loading
        initFormSubmit: function () {
            $('form[id^="royal-smtp-"]').on('submit', function (e) {
                var $form = $(this);

                // Show saving popup
                Swal.fire({
                    title: royalSmtp.i18n.saving || 'Saving...',
                    text: 'Please wait while saving settings...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: function () {
                        Swal.showLoading();
                    }
                });

                // Submit via AJAX
                e.preventDefault();

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: $form.serialize(),
                    success: function () {
                        Swal.fire({
                            icon: 'success',
                            title: royalSmtp.i18n.saved || 'Saved!',
                            text: royalSmtp.i18n.settingsSaved || 'Settings saved successfully.',
                            confirmButtonColor: '#2271b1',
                            timer: 2000,
                            timerProgressBar: true
                        }).then(function () {
                            location.reload();
                        });
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: royalSmtp.i18n.error || 'Error!',
                            text: 'Failed to save settings.',
                            confirmButtonColor: '#2271b1'
                        });
                    }
                });
            });
        },

        // Test email
        initTestEmail: function () {
            var self = this;

            $('#royal-smtp-send-test').on('click', function () {
                var email = $('#royal-smtp-test-email').val();

                if (!email) {
                    self.showAlert('error', royalSmtp.i18n.error, 'Please enter an email address');
                    return;
                }

                var $btn = $(this);
                $btn.prop('disabled', true).find('.dashicons').addClass('spin');

                // Show loading popup
                Swal.fire({
                    title: royalSmtp.i18n.sending || 'Sending...',
                    text: 'Please wait while sending test email...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: function () {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: royalSmtp.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'royal_smtp_test_email',
                        nonce: royalSmtp.nonce,
                        email: email
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: royalSmtp.i18n.success,
                                text: response.data,
                                timer: 1500,
                                timerProgressBar: true,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: royalSmtp.i18n.error,
                                text: response.data,
                                confirmButtonColor: '#2271b1'
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: royalSmtp.i18n.error,
                            text: 'Request failed',
                            confirmButtonColor: '#2271b1'
                        });
                    },
                    complete: function () {
                        $btn.prop('disabled', false).find('.dashicons').removeClass('spin');
                    }
                });
            });
        },

        // Test connection
        initTestConnection: function () {
            var self = this;

            $('#royal-smtp-test-connection').on('click', function () {
                var $btn = $(this);
                var $result = $('#royal-smtp-connection-result');

                $btn.prop('disabled', true).find('.dashicons').addClass('spin');

                // Show loading popup
                Swal.fire({
                    title: royalSmtp.i18n.testing || 'Testing...',
                    text: 'Please wait while testing SMTP connection...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: function () {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: royalSmtp.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'royal_smtp_test_connection',
                        nonce: royalSmtp.nonce
                    },
                    success: function (response) {
                        if (response.success) {
                            $result.html('<span class="dashicons dashicons-yes-alt" style="color:#00a32a;"></span> ' + response.data);
                            Swal.fire({
                                icon: 'success',
                                title: royalSmtp.i18n.connectionSuccess,
                                text: response.data,
                                timer: 1500,
                                timerProgressBar: true,
                                showConfirmButton: false
                            });
                        } else {
                            $result.html('<span class="dashicons dashicons-dismiss" style="color:#d63638;"></span> ' + response.data);
                            Swal.fire({
                                icon: 'error',
                                title: royalSmtp.i18n.connectionFailed,
                                text: response.data,
                                confirmButtonColor: '#2271b1'
                            });
                        }
                    },
                    error: function () {
                        $result.html('<span class="dashicons dashicons-dismiss" style="color:#d63638;"></span> Request failed');
                        Swal.fire({
                            icon: 'error',
                            title: royalSmtp.i18n.error,
                            text: 'Request failed',
                            confirmButtonColor: '#2271b1'
                        });
                    },
                    complete: function () {
                        $btn.prop('disabled', false).find('.dashicons').removeClass('spin');
                    }
                });
            });
        },

        // DataTables
        initDataTables: function () {
            if ($('#royal-smtp-logs-table').length && $.fn.DataTable) {
                $('#royal-smtp-logs-table').DataTable({
                    pageLength: 25,
                    order: [[0, 'desc']],
                    language: {
                        emptyTable: 'No logs found'
                    }
                });
            }

            if ($('#royal-smtp-queue-table').length && $.fn.DataTable) {
                $('#royal-smtp-queue-table').DataTable({
                    pageLength: 25,
                    order: [[0, 'desc']],
                    language: {
                        emptyTable: 'No items in queue'
                    }
                });
            }
        },

        // Log actions
        initLogActions: function () {
            var self = this;

            // View error
            $(document).on('click', '.royal-smtp-view-error', function () {
                var error = $(this).data('error');
                self.showAlert('error', 'Error Details', error);
            });

            // Delete log
            $(document).on('click', '.royal-smtp-delete-log', function () {
                var id = $(this).data('id');
                var $row = $(this).closest('tr');

                Swal.fire({
                    title: royalSmtp.i18n.confirmDelete,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d63638',
                    confirmButtonText: 'Delete'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        self.ajaxAction('royal_smtp_delete_log', { id: id }, function () {
                            $row.fadeOut(300, function () { $(this).remove(); });
                        });
                    }
                });
            });

            // Clear all logs
            $('#royal-smtp-clear-logs').on('click', function () {
                Swal.fire({
                    title: royalSmtp.i18n.confirmClear,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d63638',
                    confirmButtonText: 'Clear All'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        self.ajaxAction('royal_smtp_clear_logs', {}, function () {
                            location.reload();
                        });
                    }
                });
            });
        },

        // Queue actions
        initQueueActions: function () {
            var self = this;

            // Retry queue item
            $(document).on('click', '.royal-smtp-retry-queue', function () {
                var id = $(this).data('id');
                self.ajaxAction('royal_smtp_retry_queue', { id: id }, function () {
                    location.reload();
                });
            });

            // Delete queue item
            $(document).on('click', '.royal-smtp-delete-queue', function () {
                var id = $(this).data('id');
                var $row = $(this).closest('tr');

                self.ajaxAction('royal_smtp_delete_queue', { id: id }, function () {
                    $row.fadeOut(300, function () { $(this).remove(); });
                });
            });

            // Clear sent
            $('#royal-smtp-clear-sent').on('click', function () {
                self.ajaxAction('royal_smtp_clear_queue', { type: 'sent' }, function () {
                    location.reload();
                });
            });

            // Clear all queue
            $('#royal-smtp-clear-queue').on('click', function () {
                Swal.fire({
                    title: royalSmtp.i18n.confirmClear,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d63638',
                    confirmButtonText: 'Clear All'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        self.ajaxAction('royal_smtp_clear_queue', { type: 'all' }, function () {
                            location.reload();
                        });
                    }
                });
            });
        },

        // Debug actions
        initDebugActions: function () {
            var self = this;

            $('#royal-smtp-clear-debug').on('click', function () {
                self.ajaxAction('royal_smtp_clear_debug', {}, function () {
                    location.reload();
                });
            });
        },

        // Import/Export
        initImportExport: function () {
            var self = this;

            // Export
            $('#royal-smtp-export').on('click', function () {
                $.ajax({
                    url: royalSmtp.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'royal_smtp_export_settings',
                        nonce: royalSmtp.nonce
                    },
                    success: function (response) {
                        if (response.success) {
                            var data = JSON.stringify(response.data, null, 2);
                            var blob = new Blob([data], { type: 'application/json' });
                            var url = URL.createObjectURL(blob);
                            var a = document.createElement('a');
                            a.href = url;
                            a.download = 'royal-smtp-settings-' + new Date().toISOString().split('T')[0] + '.json';
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                            URL.revokeObjectURL(url);

                            self.showAlert('success', royalSmtp.i18n.success, 'Settings exported');
                        }
                    }
                });
            });

            // Import
            $('#royal-smtp-import').on('click', function () {
                var file = $('#royal-smtp-import-file')[0].files[0];

                if (!file) {
                    self.showAlert('error', royalSmtp.i18n.error, 'Please select a file');
                    return;
                }

                var reader = new FileReader();
                reader.onload = function (e) {
                    try {
                        var data = e.target.result;

                        $.ajax({
                            url: royalSmtp.ajaxUrl,
                            type: 'POST',
                            data: {
                                action: 'royal_smtp_import_settings',
                                nonce: royalSmtp.nonce,
                                data: data
                            },
                            success: function (response) {
                                if (response.success) {
                                    self.showAlert('success', royalSmtp.i18n.success, royalSmtp.i18n.importSuccess);
                                    setTimeout(function () {
                                        location.reload();
                                    }, 1500);
                                } else {
                                    self.showAlert('error', royalSmtp.i18n.error, response.data);
                                }
                            }
                        });
                    } catch (err) {
                        self.showAlert('error', royalSmtp.i18n.error, royalSmtp.i18n.invalidFile);
                    }
                };
                reader.readAsText(file);
            });
        },

        // Tools actions
        initToolsActions: function () {
            var self = this;

            // Cleanup logs
            $('#royal-smtp-cleanup-logs').on('click', function () {
                self.ajaxAction('royal_smtp_cleanup_logs', {}, function () {
                    self.showAlert('success', royalSmtp.i18n.success, 'Old logs cleaned up');
                    location.reload();
                });
            });

            // Cleanup queue
            $('#royal-smtp-cleanup-queue').on('click', function () {
                self.ajaxAction('royal_smtp_clear_queue', { type: 'sent' }, function () {
                    self.showAlert('success', royalSmtp.i18n.success, 'Sent emails cleared from queue');
                    location.reload();
                });
            });

            // Reset settings
            $('#royal-smtp-reset').on('click', function () {
                Swal.fire({
                    title: 'Reset All Settings?',
                    text: 'This will reset all settings to defaults. This cannot be undone!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d63638',
                    confirmButtonText: 'Reset'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        self.ajaxAction('royal_smtp_reset_settings', {}, function () {
                            location.reload();
                        });
                    }
                });
            });
        },

        // Helper: AJAX action
        ajaxAction: function (action, data, successCallback) {
            var self = this;
            data.action = action;
            data.nonce = royalSmtp.nonce;

            $.ajax({
                url: royalSmtp.ajaxUrl,
                type: 'POST',
                data: data,
                success: function (response) {
                    if (response.success) {
                        if (successCallback) successCallback(response.data);
                    } else {
                        self.showAlert('error', royalSmtp.i18n.error, response.data);
                    }
                },
                error: function () {
                    self.showAlert('error', royalSmtp.i18n.error, 'Request failed');
                }
            });
        },

        // Helper: Show SweetAlert
        showAlert: function (type, title, text) {
            Swal.fire({
                icon: type,
                title: title,
                text: text,
                confirmButtonColor: '#2271b1',
                timer: 1500,
                timerProgressBar: true,
                showConfirmButton: false
            });
        }
    };

    // Spinner animation
    var style = document.createElement('style');
    style.textContent = '@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } } .spin { animation: spin 1s linear infinite; }';
    document.head.appendChild(style);

})(jQuery);
