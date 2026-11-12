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

        currentTab: 'general',
        activeTabXhr: null,

        init: function () {
            this.initTabs();
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

        // Tab Navigation via AJAX
        initTabs: function () {
            var self = this;

            // Determine initial tab from URL or active tab in DOM
            var urlParams = new URLSearchParams(window.location.search);
            self.currentTab = urlParams.get('tab') || (royalSmtp.currentTab || 'general');

            // Handle tab clicks dynamically
            $(document).on('click', '.royal-smtp-wrap .nav-tab-wrapper a.nav-tab', function (e) {
                e.preventDefault();

                var $tab = $(this);
                var tab = $tab.data('tab');

                if (!tab) {
                    var href = $tab.attr('href') || '';
                    var match = href.match(/[?&]tab=([^&]+)/);
                    tab = match ? match[1] : 'general';
                }

                self.loadTab(tab, true);
            });

            // Handle browser back and forward navigation
            window.addEventListener('popstate', function () {
                var params = new URLSearchParams(window.location.search);
                var tab = params.get('tab') || 'general';
                self.loadTab(tab, false);
            });
        },

        // Load tab content via AJAX
        loadTab: function (tab, updateUrl) {
            var self = this;
            tab = tab || 'general';

            // Abort ongoing tab request if any
            if (self.activeTabXhr) {
                self.activeTabXhr.abort();
                self.activeTabXhr = null;
            }

            self.currentTab = tab;

            // Update nav-tab visual active state
            var $tabs = $('.royal-smtp-wrap .nav-tab-wrapper a.nav-tab');
            $tabs.removeClass('nav-tab-active');
            var $active = $tabs.filter('[data-tab="' + tab + '"]');
            if (!$active.length) {
                $active = $tabs.filter(function () {
                    var href = $(this).attr('href') || '';
                    return href.indexOf('tab=' + tab) !== -1;
                });
            }
            if ($active.length) {
                $active.addClass('nav-tab-active');
            }

            // Update browser URL without full page reload
            if (updateUrl !== false && window.history && window.history.pushState) {
                var currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('page', 'royal-smtp');
                currentUrl.searchParams.set('tab', tab);
                window.history.pushState({ tab: tab }, '', currentUrl.toString());
            }

            var $container = $('.royal-smtp-tab-content');

            // Show loading placeholder
            $container.addClass('royal-smtp-loading').html(
                '<div class="royal-smtp-tab-loader">' +
                    '<span class="dashicons dashicons-update spin"></span>' +
                    '<p>' + (royalSmtp.i18n.loading || 'Loading...') + '</p>' +
                '</div>'
            );

            // Fetch tab view via AJAX
            self.activeTabXhr = $.ajax({
                url: royalSmtp.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'royal_smtp_load_tab',
                    nonce: royalSmtp.nonce,
                    tab: tab
                },
                success: function (response) {
                    if (response.success && response.data && typeof response.data.html !== 'undefined') {
                        $container.removeClass('royal-smtp-loading').html(response.data.html);
                        self.onTabLoaded(tab);
                    } else {
                        var errMsg = (response && response.data) ? response.data : 'Failed to load tab content.';
                        $container.removeClass('royal-smtp-loading').html(
                            '<div class="royal-smtp-notice error"><p>' + errMsg + '</p></div>'
                        );
                    }
                },
                error: function (xhr, status) {
                    if (status === 'abort') {
                        return;
                    }
                    $container.removeClass('royal-smtp-loading').html(
                        '<div class="royal-smtp-notice error"><p>An error occurred while loading this tab. <a href="?page=royal-smtp&tab=' + encodeURIComponent(tab) + '">Click here to reload</a>.</p></div>'
                    );
                },
                complete: function () {
                    self.activeTabXhr = null;
                }
            });
        },

        // Post-tab-load initializations
        onTabLoaded: function (tab) {
            if (tab === 'logs' || tab === 'queue') {
                this.initDataTables();
            } else if (tab === 'smtp') {
                var $activeProvider = $('.royal-smtp-provider-item.active');
                if ($activeProvider.length) {
                    var provider = $activeProvider.find('input').val();
                    if (royalSmtp.providers && royalSmtp.providers[provider] && royalSmtp.providers[provider].note) {
                        $('#royal-smtp-provider-note')
                            .html('<span class="dashicons dashicons-info"></span> ' + royalSmtp.providers[provider].note)
                            .addClass('info')
                            .show();
                    }
                }
            }
        },

        // Provider selection
        initProviderSelection: function () {
            var self = this;

            var updateProviderDetails = function ($item) {
                var provider = $item.find('input').val();

                // Update active state
                $('.royal-smtp-provider-item').removeClass('active');
                $item.addClass('active');
                $item.find('input').prop('checked', true);

                // Auto-fill settings
                if (royalSmtp.providers && royalSmtp.providers[provider]) {
                    var settings = royalSmtp.providers[provider];

                    if (settings.host) {
                        $('#smtp_host').val(settings.host);
                    }
                    if (settings.port) {
                        $('#smtp_port').val(settings.port);
                    }

                    // Encryption
                    if (settings.encryption) {
                        $('input[name="royal_smtp_options[smtp_encryption]"]').prop('checked', false);
                        $('#smtp_encryption_' + settings.encryption).prop('checked', true);
                    }

                    // Auth
                    if (typeof settings.auth !== 'undefined') {
                        $('#smtp_auth').prop('checked', settings.auth);
                    }

                    // Auto-fill username if predefined (e.g. ZeptoMail 'emailapikey', SendGrid 'apikey')
                    if (settings.user) {
                        $('input[name="royal_smtp_options[smtp_user]"]').val(settings.user);
                    }

                    // Show note if available
                    if (settings.note) {
                        $('#royal-smtp-provider-note')
                            .html('<span class="dashicons dashicons-info"></span> ' + settings.note)
                            .addClass('info')
                            .slideDown(200);
                    } else {
                        $('#royal-smtp-provider-note').slideUp(200);
                    }
                }
            };

            $(document).on('click', '.royal-smtp-provider-item', function () {
                updateProviderDetails($(this));
            });

            // Display active provider note on initial load if present
            var $activeProvider = $('.royal-smtp-provider-item.active');
            if ($activeProvider.length) {
                var initialProvider = $activeProvider.find('input').val();
                if (royalSmtp.providers && royalSmtp.providers[initialProvider] && royalSmtp.providers[initialProvider].note) {
                    $('#royal-smtp-provider-note')
                        .html('<span class="dashicons dashicons-info"></span> ' + royalSmtp.providers[initialProvider].note)
                        .addClass('info')
                        .show();
                }
            }
        },

        // Encryption to Port sync
        initEncryptionPortSync: function () {
            $(document).on('change', 'input[name="royal_smtp_options[smtp_encryption]"]', function () {
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
            $(document).on('click', '.royal-smtp-toggle-password', function () {
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

        // Form submit with SweetAlert2 loading & dynamic refresh
        initFormSubmit: function () {
            var self = this;

            $(document).on('submit', 'form[id^="royal-smtp-"]', function (e) {
                e.preventDefault();
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
                            timer: 1500,
                            timerProgressBar: true,
                            showConfirmButton: false
                        });

                        // Silently refresh current tab after short delay to show sanitized values
                        setTimeout(function () {
                            self.loadTab(self.currentTab, false);
                        }, 1200);
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

            $(document).on('click', '#royal-smtp-send-test', function () {
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

            $(document).on('click', '#royal-smtp-test-connection', function () {
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
                if ($.fn.DataTable.isDataTable('#royal-smtp-logs-table')) {
                    $('#royal-smtp-logs-table').DataTable().destroy();
                }
                $('#royal-smtp-logs-table').DataTable({
                    pageLength: 25,
                    order: [[0, 'desc']],
                    language: {
                        emptyTable: 'No logs found'
                    }
                });
            }

            if ($('#royal-smtp-queue-table').length && $.fn.DataTable) {
                if ($.fn.DataTable.isDataTable('#royal-smtp-queue-table')) {
                    $('#royal-smtp-queue-table').DataTable().destroy();
                }
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
            $(document).on('click', '#royal-smtp-clear-logs', function () {
                Swal.fire({
                    title: royalSmtp.i18n.confirmClear,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d63638',
                    confirmButtonText: 'Clear All'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        self.ajaxAction('royal_smtp_clear_logs', {}, function () {
                            self.loadTab('logs', false);
                        });
                    }
                });
            });

            // Refresh logs
            $(document).on('click', '#royal-smtp-refresh-logs', function () {
                self.loadTab('logs', false);
            });

            // Filter logs by status
            $(document).on('change', '#royal-smtp-log-filter', function () {
                var val = $(this).val();
                if ($('#royal-smtp-logs-table').length && $.fn.DataTable.isDataTable('#royal-smtp-logs-table')) {
                    $('#royal-smtp-logs-table').DataTable().column(3).search(val).draw();
                }
            });
        },

        // Queue actions
        initQueueActions: function () {
            var self = this;

            // Retry queue item
            $(document).on('click', '.royal-smtp-retry-queue', function () {
                var id = $(this).data('id');
                self.ajaxAction('royal_smtp_retry_queue', { id: id }, function () {
                    self.loadTab('queue', false);
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
            $(document).on('click', '#royal-smtp-clear-sent', function () {
                self.ajaxAction('royal_smtp_clear_queue', { type: 'sent' }, function () {
                    self.loadTab('queue', false);
                });
            });

            // Clear all queue
            $(document).on('click', '#royal-smtp-clear-queue', function () {
                Swal.fire({
                    title: royalSmtp.i18n.confirmClear,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d63638',
                    confirmButtonText: 'Clear All'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        self.ajaxAction('royal_smtp_clear_queue', { type: 'all' }, function () {
                            self.loadTab('queue', false);
                        });
                    }
                });
            });

            // Refresh queue
            $(document).on('click', '#royal-smtp-refresh-queue', function () {
                self.loadTab('queue', false);
            });

            // Filter queue by status
            $(document).on('change', '#royal-smtp-queue-filter', function () {
                var val = $(this).val();
                if ($('#royal-smtp-queue-table').length && $.fn.DataTable.isDataTable('#royal-smtp-queue-table')) {
                    $('#royal-smtp-queue-table').DataTable().column(3).search(val).draw();
                }
            });
        },

        // Debug actions
        initDebugActions: function () {
            var self = this;

            $(document).on('click', '#royal-smtp-clear-debug', function () {
                self.ajaxAction('royal_smtp_clear_debug', {}, function () {
                    self.loadTab('debug', false);
                });
            });
        },

        // Import/Export
        initImportExport: function () {
            var self = this;

            // Export
            $(document).on('click', '#royal-smtp-export', function () {
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
            $(document).on('click', '#royal-smtp-import', function () {
                var fileInput = $('#royal-smtp-import-file')[0];
                var file = fileInput ? fileInput.files[0] : null;

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
                                        self.loadTab('tools', false);
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
            $(document).on('click', '#royal-smtp-cleanup-logs', function () {
                self.ajaxAction('royal_smtp_cleanup_logs', {}, function () {
                    self.showAlert('success', royalSmtp.i18n.success, 'Old logs cleaned up');
                    self.loadTab('tools', false);
                });
            });

            // Cleanup queue
            $(document).on('click', '#royal-smtp-cleanup-queue', function () {
                self.ajaxAction('royal_smtp_clear_queue', { type: 'sent' }, function () {
                    self.showAlert('success', royalSmtp.i18n.success, 'Sent emails cleared from queue');
                    self.loadTab('tools', false);
                });
            });

            // Reset settings
            $(document).on('click', '#royal-smtp-reset', function () {
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
                            self.showAlert('success', royalSmtp.i18n.success, 'Settings reset to default');
                            self.loadTab('tools', false);
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
