<?php
/**
 * Plugin Name: Lectus Class System
 * Plugin URI: https://example.com/lectus-class-system
 * Description: WordPress용 전문 교육 서비스 플러그인 - 패키지강의 관리, WooCommerce 연동, 수강생 관리, 수료증 발급
 * Version: 1.0.0
 * Author: Lectus Team
 * Author URI: https://example.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: lectus-class-system
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 8.0
 * WC requires at least: 6.0
 * WC tested up to: 8.0
 * Woo: 12345:abcdefghijklmnopqrstuvwxyz
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Declare HPOS compatibility
add_action('before_woocommerce_init', function() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

// Define plugin constants
define('LECTUS_VERSION', '1.0.0');
define('LECTUS_PLUGIN_FILE', __FILE__);
define('LECTUS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LECTUS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LECTUS_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoloader
require_once LECTUS_PLUGIN_DIR . 'includes/class-lectus-autoloader.php';
Lectus_Autoloader::init();

// Main plugin class
class Lectus_Class_System {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
    }
    
    private function init_hooks() {
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Init action
        add_action('init', array($this, 'init'));
        add_action('plugins_loaded', array($this, 'plugins_loaded'));
        
        // Admin actions
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        }
        
        // Frontend actions
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts'));
    }
    
    private function load_dependencies() {
        // Core files
        require_once LECTUS_PLUGIN_DIR . 'includes/class-lectus-logger.php';
        require_once LECTUS_PLUGIN_DIR . 'includes/class-lectus-post-types.php';
        require_once LECTUS_PLUGIN_DIR . 'includes/class-lectus-taxonomies.php';
        require_once LECTUS_PLUGIN_DIR . 'includes/class-lectus-capabilities.php';
        require_once LECTUS_PLUGIN_DIR . 'includes/class-lectus-ajax.php';
        require_once LECTUS_PLUGIN_DIR . 'includes/class-lectus-shortcodes.php';
        require_once LECTUS_PLUGIN_DIR . 'includes/class-lectus-templates.php';
        
        // WooCommerce integration
        if (class_exists('WooCommerce')) {
            require_once LECTUS_PLUGIN_DIR . 'includes/class-lectus-woocommerce.php';
        }
        
        // Student management
        require_once LECTUS_PLUGIN_DIR . 'includes/class-lectus-student.php';
        require_once LECTUS_PLUGIN_DIR . 'includes/class-lectus-progress.php';
        require_once LECTUS_PLUGIN_DIR . 'includes/class-lectus-enrollment.php';
        
        // Certificate system
        require_once LECTUS_PLUGIN_DIR . 'includes/class-lectus-certificate.php';
        
        // Q&A system
        require_once LECTUS_PLUGIN_DIR . 'includes/class-lectus-qa.php';
        
        // Bulk upload system
        require_once LECTUS_PLUGIN_DIR . 'includes/class-lectus-bulk-upload.php';
        
        // Materials system
        require_once LECTUS_PLUGIN_DIR . 'includes/class-lectus-materials.php';
        
        // Admin bar customization
        require_once LECTUS_PLUGIN_DIR . 'includes/class-lectus-admin-bar.php';
        
        // Admin files
        if (is_admin()) {
            require_once LECTUS_PLUGIN_DIR . 'admin/class-lectus-admin.php';
            require_once LECTUS_PLUGIN_DIR . 'admin/class-lectus-admin-dashboard.php';
            require_once LECTUS_PLUGIN_DIR . 'admin/class-lectus-admin-settings.php';
            require_once LECTUS_PLUGIN_DIR . 'admin/class-lectus-admin-reports.php';
            
            // Test functionality is now integrated into settings page
        }
    }
    
    public function init() {
        // Load textdomain
        load_plugin_textdomain('lectus-class-system', false, dirname(LECTUS_PLUGIN_BASENAME) . '/languages');
        
        // Initialize post types
        Lectus_Post_Types::init();
        
        // Initialize taxonomies
        Lectus_Taxonomies::init();
        
        // Initialize capabilities
        Lectus_Capabilities::init();
        
        // Initialize AJAX handlers
        Lectus_Ajax::init();
        
        // Initialize logger
        Lectus_Logger::init();
        
        // Initialize shortcodes
        Lectus_Shortcodes::init();
        
        // Initialize templates
        Lectus_Templates::init();
        
        // Initialize WooCommerce integration
        if (class_exists('WooCommerce')) {
            Lectus_WooCommerce::init();
        }
        
        // Initialize student management
        Lectus_Student::init();
        Lectus_Progress::init();
        Lectus_Enrollment::init();
        
        // Initialize certificate system
        Lectus_Certificate::init();
        
        // Initialize Q&A system
        Lectus_QA::init();
        
        // Initialize bulk upload system
        Lectus_Bulk_Upload::init();
        
        // Initialize materials system
        Lectus_Materials::init();
        
        // Initialize admin bar customization
        Lectus_Admin_Bar::init();
    }
    
    public function plugins_loaded() {
        // Check for required plugins
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
        }
    }
    
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('Lectus Class System', 'lectus-class-system'),
            __('Lectus Class', 'lectus-class-system'),
            'manage_options',
            'lectus-class-system',
            array('Lectus_Admin_Dashboard', 'render_dashboard'),
            'dashicons-welcome-learn-more',
            30
        );
        
        // Submenu pages
        add_submenu_page(
            'lectus-class-system',
            __('대시보드', 'lectus-class-system'),
            __('대시보드', 'lectus-class-system'),
            'manage_options',
            'lectus-class-system',
            array('Lectus_Admin_Dashboard', 'render_dashboard')
        );
        
        add_submenu_page(
            'lectus-class-system',
            __('패키지강의', 'lectus-class-system'),
            __('패키지강의', 'lectus-class-system'),
            'manage_options',
            'edit.php?post_type=coursepackage'
        );
        
        add_submenu_page(
            'lectus-class-system',
            __('단과강의', 'lectus-class-system'),
            __('단과강의', 'lectus-class-system'),
            'manage_options',
            'edit.php?post_type=coursesingle'
        );
        
        add_submenu_page(
            'lectus-class-system',
            __('레슨', 'lectus-class-system'),
            __('레슨', 'lectus-class-system'),
            'manage_options',
            'edit.php?post_type=lesson'
        );
        
        add_submenu_page(
            'lectus-class-system',
            __('수강생 관리', 'lectus-class-system'),
            __('수강생 관리', 'lectus-class-system'),
            'manage_options',
            'lectus-students',
            array('Lectus_Admin', 'render_students_page')
        );
        
        add_submenu_page(
            'lectus-class-system',
            __('수료증', 'lectus-class-system'),
            __('수료증', 'lectus-class-system'),
            'manage_options',
            'lectus-certificates',
            array('Lectus_Admin', 'render_certificates_page')
        );
        
        add_submenu_page(
            'lectus-class-system',
            __('보고서', 'lectus-class-system'),
            __('보고서', 'lectus-class-system'),
            'manage_options',
            'lectus-reports',
            array('Lectus_Admin_Reports', 'render_reports_page')
        );
        
        add_submenu_page(
            'lectus-class-system',
            __('설정', 'lectus-class-system'),
            __('설정', 'lectus-class-system'),
            'manage_options',
            'lectus-settings',
            array('Lectus_Admin_Settings', 'render_settings_page')
        );
        
        // Hidden page for logs (accessible through settings)
        add_submenu_page(
            null, // Hidden from menu
            __('로그 보기', 'lectus-class-system'),
            __('로그 보기', 'lectus-class-system'),
            'manage_options',
            'lectus-logs',
            array(__CLASS__, 'render_logs_page')
        );
        
        // Hidden page for test functionality (accessible through settings)
        add_submenu_page(
            null, // Hidden from menu
            __('테스트', 'lectus-class-system'),
            __('테스트', 'lectus-class-system'),
            'manage_options',
            'lectus-test',
            array(__CLASS__, 'render_test_page')
        );
    }
    
    public static function render_test_page() {
        $test = isset($_GET['test']) ? sanitize_text_field($_GET['test']) : '';
        
        echo '<div class="wrap">';
        echo '<h1>' . __('테스트 페이지', 'lectus-class-system') . '</h1>';
        
        switch ($test) {
            case 'enrollment':
                echo '<h2>' . __('수강 등록 테스트', 'lectus-class-system') . '</h2>';
                // Include enrollment test functionality
                if (file_exists(LECTUS_PLUGIN_DIR . 'templates/test-enrollment.php')) {
                    include LECTUS_PLUGIN_DIR . 'templates/test-enrollment.php';
                }
                break;
                
            case 'certificate':
                echo '<h2>' . __('수료증 생성 테스트', 'lectus-class-system') . '</h2>';
                // Include certificate test functionality
                if (file_exists(LECTUS_PLUGIN_DIR . 'templates/test-certificate.php')) {
                    include LECTUS_PLUGIN_DIR . 'templates/test-certificate.php';
                }
                break;
                
            case 'qa':
                echo '<h2>' . __('Q&A 시스템 테스트', 'lectus-class-system') . '</h2>';
                // Include Q&A test functionality
                if (file_exists(LECTUS_PLUGIN_DIR . 'templates/test-qa.php')) {
                    include LECTUS_PLUGIN_DIR . 'templates/test-qa.php';
                }
                break;
                
            case 'email':
                echo '<h2>' . __('이메일 알림 테스트', 'lectus-class-system') . '</h2>';
                // Include email test functionality
                if (file_exists(LECTUS_PLUGIN_DIR . 'templates/test-email.php')) {
                    include LECTUS_PLUGIN_DIR . 'templates/test-email.php';
                }
                break;
                
            default:
                echo '<p>' . __('테스트 유형을 선택하세요.', 'lectus-class-system') . '</p>';
                echo '<ul>';
                echo '<li><a href="?page=lectus-test&test=enrollment">' . __('수강 등록 테스트', 'lectus-class-system') . '</a></li>';
                echo '<li><a href="?page=lectus-test&test=certificate">' . __('수료증 생성 테스트', 'lectus-class-system') . '</a></li>';
                echo '<li><a href="?page=lectus-test&test=qa">' . __('Q&A 시스템 테스트', 'lectus-class-system') . '</a></li>';
                echo '<li><a href="?page=lectus-test&test=email">' . __('이메일 알림 테스트', 'lectus-class-system') . '</a></li>';
                echo '</ul>';
                break;
        }
        
        echo '<p><a href="' . admin_url('admin.php?page=lectus-settings#development') . '" class="button">' . __('설정으로 돌아가기', 'lectus-class-system') . '</a></p>';
        echo '</div>';
    }
    
    public function admin_enqueue_scripts($hook) {
        // Admin styles
        wp_enqueue_style(
            'lectus-admin',
            LECTUS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            LECTUS_VERSION
        );
        
        // Admin scripts
        wp_enqueue_script(
            'lectus-admin',
            LECTUS_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'wp-color-picker'),
            LECTUS_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('lectus-admin', 'lectus_ajax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('lectus-ajax-nonce')
        ));
    }
    
    public function frontend_enqueue_scripts() {
        // Frontend styles
        wp_enqueue_style(
            'lectus-frontend',
            LECTUS_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            LECTUS_VERSION
        );
        
        // Frontend scripts
        wp_enqueue_script(
            'lectus-frontend',
            LECTUS_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            LECTUS_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('lectus-frontend', 'lectus_ajax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('lectus-ajax-nonce')
        ));
    }
    
    public function activate() {
        try {
            // Create database tables
            $this->create_tables();
            
            // Create default options
            $this->create_default_options();
            
            // Create user roles and capabilities
            Lectus_Capabilities::create_roles();
            
            // Flush rewrite rules
            flush_rewrite_rules();
            
            // Log successful activation
            Lectus_Logger::info('Plugin activated successfully', 'activation');
            
        } catch (Exception $e) {
            Lectus_Logger::error('Plugin activation failed: ' . $e->getMessage(), 'activation', array(
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ));
            
            // Show admin notice
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error"><p>';
                printf(__('Lectus Class System 활성화 실패: %s', 'lectus-class-system'), esc_html($e->getMessage()));
                echo '</p></div>';
            });
        }
    }
    
    public function deactivate() {
        // Clean up scheduled events
        wp_clear_scheduled_hook('lectus_daily_cron');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Student progress table
        $table_progress = $wpdb->prefix . 'lectus_progress';
        $sql_progress = "CREATE TABLE IF NOT EXISTS $table_progress (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            course_id bigint(20) NOT NULL,
            lesson_id bigint(20) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'not_started',
            progress int(3) NOT NULL DEFAULT 0,
            started_at datetime DEFAULT NULL,
            completed_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY course_id (course_id),
            KEY lesson_id (lesson_id)
        ) $charset_collate;";
        
        // Student enrollment table
        $table_enrollment = $wpdb->prefix . 'lectus_enrollment';
        $sql_enrollment = "CREATE TABLE IF NOT EXISTS $table_enrollment (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            course_id bigint(20) NOT NULL,
            order_id bigint(20) DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            enrolled_at datetime NOT NULL,
            expires_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY course_id (course_id),
            KEY order_id (order_id)
        ) $charset_collate;";
        
        // Certificates table
        $table_certificates = $wpdb->prefix . 'lectus_certificates';
        $sql_certificates = "CREATE TABLE IF NOT EXISTS $table_certificates (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            course_id bigint(20) NOT NULL,
            certificate_number varchar(50) NOT NULL,
            issued_at datetime NOT NULL,
            pdf_url varchar(255) DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY certificate_number (certificate_number),
            KEY user_id (user_id),
            KEY course_id (course_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_progress);
        dbDelta($sql_enrollment);
        dbDelta($sql_certificates);
        
        // Create Q&A tables
        Lectus_QA::create_table();
        
        // Create Materials tables
        Lectus_Materials::create_table();
        Lectus_QA::create_votes_table();
    }
    
    private function create_default_options() {
        // General settings
        add_option('lectus_enable_certificates', 'yes');
        add_option('lectus_certificate_template', 'default');
        add_option('lectus_completion_threshold', '80');
        
        // Email settings
        add_option('lectus_enable_email_notifications', 'yes');
        add_option('lectus_enrollment_email_subject', '수강 등록이 완료되었습니다');
        add_option('lectus_completion_email_subject', '축하합니다! 과정을 완료하셨습니다');
        
        // Access settings
        add_option('lectus_default_access_duration', '365');
        add_option('lectus_enable_drm_protection', 'no');
    }
    
    public function render_logs_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('이 페이지에 액세스할 권한이 없습니다.'));
        }
        
        // Handle log cleanup
        if (isset($_POST['cleanup_logs']) && wp_verify_nonce($_POST['_wpnonce'], 'cleanup_logs')) {
            $days = intval($_POST['cleanup_days']) ?: 30;
            $deleted = Lectus_Logger::cleanup_old_logs($days);
            echo '<div class="notice notice-success"><p>' . sprintf(__('%d개의 로그 항목이 삭제되었습니다.', 'lectus-class-system'), $deleted) . '</p></div>';
        }
        
        // Get filter parameters
        $level_filter = isset($_GET['level']) ? intval($_GET['level']) : null;
        $context_filter = isset($_GET['context']) ? sanitize_text_field($_GET['context']) : null;
        
        // Get log entries
        $logs = Lectus_Logger::get_log_entries(100, $level_filter, $context_filter);
        
        // Get available contexts
        global $wpdb;
        $contexts = $wpdb->get_col("SELECT DISTINCT context FROM {$wpdb->prefix}lectus_logs ORDER BY context");
        
        ?>
        <div class="wrap">
            <h1><?php _e('로그 보기', 'lectus-class-system'); ?></h1>
            
            <!-- Filters -->
            <form method="get" style="margin-bottom: 20px;">
                <input type="hidden" name="page" value="lectus-logs">
                
                <select name="level">
                    <option value=""><?php _e('모든 레벨', 'lectus-class-system'); ?></option>
                    <option value="1" <?php selected($level_filter, 1); ?>><?php _e('오류', 'lectus-class-system'); ?></option>
                    <option value="2" <?php selected($level_filter, 2); ?>><?php _e('경고', 'lectus-class-system'); ?></option>
                    <option value="3" <?php selected($level_filter, 3); ?>><?php _e('정보', 'lectus-class-system'); ?></option>
                    <option value="4" <?php selected($level_filter, 4); ?>><?php _e('디버그', 'lectus-class-system'); ?></option>
                </select>
                
                <select name="context">
                    <option value=""><?php _e('모든 컨텍스트', 'lectus-class-system'); ?></option>
                    <?php foreach ($contexts as $context): ?>
                        <option value="<?php echo esc_attr($context); ?>" <?php selected($context_filter, $context); ?>>
                            <?php echo esc_html($context); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <input type="submit" class="button" value="<?php _e('필터', 'lectus-class-system'); ?>">
                <a href="?page=lectus-logs" class="button"><?php _e('초기화', 'lectus-class-system'); ?></a>
            </form>
            
            <!-- Cleanup Form -->
            <form method="post" style="float: right; margin-bottom: 20px;">
                <?php wp_nonce_field('cleanup_logs'); ?>
                <input type="number" name="cleanup_days" value="30" min="1" max="365" style="width: 60px;">
                <span><?php _e('일 이전 로그 삭제', 'lectus-class-system'); ?></span>
                <input type="submit" name="cleanup_logs" class="button button-secondary" value="<?php _e('정리', 'lectus-class-system'); ?>" onclick="return confirm('정말 삭제하시겠습니까?');">
            </form>
            
            <div style="clear: both;"></div>
            
            <!-- Log Entries -->
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('시간', 'lectus-class-system'); ?></th>
                        <th><?php _e('레벨', 'lectus-class-system'); ?></th>
                        <th><?php _e('컨텍스트', 'lectus-class-system'); ?></th>
                        <th><?php _e('메시지', 'lectus-class-system'); ?></th>
                        <th><?php _e('사용자', 'lectus-class-system'); ?></th>
                        <th><?php _e('데이터', 'lectus-class-system'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6"><?php _e('로그 항목이 없습니다.', 'lectus-class-system'); ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <?php
                            $level_names = array(1 => '오류', 2 => '경고', 3 => '정보', 4 => '디버그');
                            $level_colors = array(1 => '#dc3545', 2 => '#ffc107', 3 => '#17a2b8', 4 => '#6c757d');
                            ?>
                            <tr>
                                <td><?php echo esc_html($log->created_at); ?></td>
                                <td style="color: <?php echo $level_colors[$log->level]; ?>; font-weight: bold;">
                                    <?php echo $level_names[$log->level]; ?>
                                </td>
                                <td><?php echo esc_html($log->context); ?></td>
                                <td style="max-width: 300px; word-wrap: break-word;">
                                    <?php echo esc_html(wp_trim_words($log->message, 10, '...')); ?>
                                </td>
                                <td>
                                    <?php 
                                    if ($log->user_id) {
                                        $user = get_user_by('id', $log->user_id);
                                        echo $user ? esc_html($user->user_login) : 'Unknown';
                                    } else {
                                        echo 'Guest';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($log->data && $log->data !== 'null'): ?>
                                        <details>
                                            <summary>보기</summary>
                                            <pre style="font-size: 11px; max-height: 100px; overflow-y: auto;"><?php echo esc_html($log->data); ?></pre>
                                        </details>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    public function woocommerce_missing_notice() {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p><?php _e('Lectus Class System: WooCommerce가 설치되지 않았습니다. 결제 기능을 사용하려면 WooCommerce를 설치하세요.', 'lectus-class-system'); ?></p>
        </div>
        <?php
    }
}

// Initialize plugin
function lectus_class_system() {
    return Lectus_Class_System::get_instance();
}

// Start the plugin
lectus_class_system();