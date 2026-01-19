<?php
/**
 * Admin Class - Settings page and admin functionality
 *
 * @package RoyalSMTP
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Royal_SMTP_Admin {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __( 'Royal SMTP', 'royal-smtp' ),
            __( 'Royal SMTP', 'royal-smtp' ),
            'manage_options',
            'royal-smtp',
            array( $this, 'render_settings_page' ),
            'dashicons-email-alt',
            90
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            'royal_smtp_group',
            'royal_smtp_options',
            array( 'Royal_SMTP_Security', 'sanitize_settings' )
        );
    }

    /**
     * Enqueue assets
     */
    public function enqueue_assets( $hook ) {
        if ( $hook !== 'toplevel_page_royal-smtp' ) {
            return;
        }

        // SweetAlert2
        wp_enqueue_script( 
            'sweetalert2', 
            'https://cdn.jsdelivr.net/npm/sweetalert2@11', 
            array(), 
            '11.0.0', 
            true 
        );

        // DataTables
        wp_enqueue_style(
            'datatables',
            'https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css',
            array(),
            '1.13.6'
        );
        wp_enqueue_script(
            'datatables',
            'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js',
            array( 'jquery' ),
            '1.13.6',
            true
        );

        // Plugin CSS
        wp_enqueue_style(
            'royal-smtp-admin',
            ROYAL_SMTP_URL . 'assets/css/admin-style.css',
            array(),
            ROYAL_SMTP_VERSION
        );

        // Plugin JS
        wp_enqueue_script(
            'royal-smtp-admin',
            ROYAL_SMTP_URL . 'assets/js/admin-script.js',
            array( 'jquery', 'sweetalert2', 'datatables' ),
            ROYAL_SMTP_VERSION,
            true
        );

        // Localize script
        wp_localize_script( 'royal-smtp-admin', 'royalSmtp', array(
            'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
            'nonce'     => wp_create_nonce( 'royal_smtp_nonce' ),
            'providers' => Royal_SMTP_Providers::get_providers(),
            'i18n'      => array(
                'success'           => __( 'Success!', 'royal-smtp' ),
                'error'             => __( 'Error!', 'royal-smtp' ),
                'saving'            => __( 'Saving...', 'royal-smtp' ),
                'saved'             => __( 'Saved!', 'royal-smtp' ),
                'settingsSaved'     => __( 'Settings saved successfully.', 'royal-smtp' ),
                'testEmailSent'     => __( 'Test email sent!', 'royal-smtp' ),
                'testEmailFailed'   => __( 'Failed to send test email.', 'royal-smtp' ),
                'connectionSuccess' => __( 'Connection successful!', 'royal-smtp' ),
                'connectionFailed'  => __( 'Connection failed.', 'royal-smtp' ),
                'confirmDelete'     => __( 'Are you sure you want to delete this?', 'royal-smtp' ),
                'confirmClear'      => __( 'Are you sure you want to clear all logs?', 'royal-smtp' ),
                'processing'        => __( 'Processing...', 'royal-smtp' ),
                'sending'           => __( 'Sending...', 'royal-smtp' ),
                'testing'           => __( 'Testing...', 'royal-smtp' ),
                'importing'         => __( 'Importing...', 'royal-smtp' ),
                'importSuccess'     => __( 'Settings imported successfully!', 'royal-smtp' ),
                'invalidFile'       => __( 'Invalid file format.', 'royal-smtp' ),
            ),
        ) );

        // Check for settings update
        if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] === 'true' ) {
            $script = "
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: '" . esc_js( __( 'Success!', 'royal-smtp' ) ) . "',
                        text: '" . esc_js( __( 'Settings saved successfully.', 'royal-smtp' ) ) . "',
                        icon: 'success',
                        confirmButtonText: '" . esc_js( __( 'Great!', 'royal-smtp' ) ) . "',
                        confirmButtonColor: '#3085d6'
                    });
                });
            ";
            wp_add_inline_script( 'sweetalert2', $script );
        }
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
        ?>
        <div class="wrap royal-smtp-wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <h2 class="nav-tab-wrapper">
                <a href="?page=royal-smtp&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-admin-settings"></span> <?php esc_html_e( 'General', 'royal-smtp' ); ?>
                </a>
                <a href="?page=royal-smtp&tab=smtp" class="nav-tab <?php echo $active_tab === 'smtp' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-email-alt"></span> <?php esc_html_e( 'SMTP', 'royal-smtp' ); ?>
                </a>
                <a href="?page=royal-smtp&tab=logs" class="nav-tab <?php echo $active_tab === 'logs' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-list-view"></span> <?php esc_html_e( 'Email Logs', 'royal-smtp' ); ?>
                </a>
                <a href="?page=royal-smtp&tab=queue" class="nav-tab <?php echo $active_tab === 'queue' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-clock"></span> <?php esc_html_e( 'Queue', 'royal-smtp' ); ?>
                </a>
                <a href="?page=royal-smtp&tab=debug" class="nav-tab <?php echo $active_tab === 'debug' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-admin-tools"></span> <?php esc_html_e( 'Debug', 'royal-smtp' ); ?>
                </a>
                <a href="?page=royal-smtp&tab=tools" class="nav-tab <?php echo $active_tab === 'tools' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e( 'Tools', 'royal-smtp' ); ?>
                </a>
                <a href="?page=royal-smtp&tab=about" class="nav-tab <?php echo $active_tab === 'about' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-info"></span> <?php esc_html_e( 'About', 'royal-smtp' ); ?>
                </a>
            </h2>

            <div class="royal-smtp-tab-content">
                <?php
                switch ( $active_tab ) {
                    case 'smtp':
                        include ROYAL_SMTP_PATH . 'views/smtp-settings.php';
                        break;
                    case 'logs':
                        include ROYAL_SMTP_PATH . 'views/email-logs.php';
                        break;
                    case 'queue':
                        include ROYAL_SMTP_PATH . 'views/queue-management.php';
                        break;
                    case 'debug':
                        include ROYAL_SMTP_PATH . 'views/debug-logs.php';
                        break;
                    case 'tools':
                        include ROYAL_SMTP_PATH . 'views/tools.php';
                        break;
                    case 'about':
                        include ROYAL_SMTP_PATH . 'views/about.php';
                        break;
                    default:
                        include ROYAL_SMTP_PATH . 'views/general-settings.php';
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }
}
