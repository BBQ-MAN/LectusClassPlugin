<?php
/**
 * Enhanced Logging System for Lectus Class System
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Logger {
    
    const LEVEL_ERROR = 1;
    const LEVEL_WARNING = 2;
    const LEVEL_INFO = 3;
    const LEVEL_DEBUG = 4;
    
    private static $log_levels = array(
        self::LEVEL_ERROR => 'ERROR',
        self::LEVEL_WARNING => 'WARNING', 
        self::LEVEL_INFO => 'INFO',
        self::LEVEL_DEBUG => 'DEBUG'
    );
    
    /**
     * Initialize logger
     */
    public static function init() {
        // Set up custom error handler for Lectus
        add_action('wp_loaded', array(__CLASS__, 'setup_error_handling'));
    }
    
    /**
     * Setup error handling
     */
    public static function setup_error_handling() {
        // Only in debug mode
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }
        
        // Set custom error handler for Lectus operations
        set_error_handler(array(__CLASS__, 'handle_php_error'), E_ALL);
    }
    
    /**
     * Handle PHP errors
     */
    public static function handle_php_error($errno, $errstr, $errfile, $errline) {
        // Only handle errors from Lectus files
        if (strpos($errfile, 'lectus-class-system') === false) {
            return false;
        }
        
        $error_type = self::get_error_type($errno);
        $message = sprintf('[%s] %s in %s on line %d', $error_type, $errstr, $errfile, $errline);
        
        self::log($message, self::LEVEL_ERROR, 'php_error');
        
        // Don't prevent normal error handling
        return false;
    }
    
    /**
     * Log message with context
     */
    public static function log($message, $level = self::LEVEL_INFO, $context = 'general', $data = array()) {
        // Check if logging is enabled
        if (!self::is_logging_enabled()) {
            return;
        }
        
        // Check log level
        $current_level = self::get_log_level();
        if ($level > $current_level) {
            return;
        }
        
        $log_entry = self::format_log_entry($message, $level, $context, $data);
        
        // Write to WordPress error log
        error_log($log_entry);
        
        // Store in database for admin viewing (optional)
        if (self::should_store_in_db($level)) {
            self::store_log_entry($message, $level, $context, $data);
        }
        
        // Send critical errors via email (if configured)
        if ($level === self::LEVEL_ERROR && self::should_email_errors()) {
            self::send_error_email($message, $context, $data);
        }
    }
    
    /**
     * Log error
     */
    public static function error($message, $context = 'general', $data = array()) {
        self::log($message, self::LEVEL_ERROR, $context, $data);
    }
    
    /**
     * Log warning
     */
    public static function warning($message, $context = 'general', $data = array()) {
        self::log($message, self::LEVEL_WARNING, $context, $data);
    }
    
    /**
     * Log info
     */
    public static function info($message, $context = 'general', $data = array()) {
        self::log($message, self::LEVEL_INFO, $context, $data);
    }
    
    /**
     * Log debug
     */
    public static function debug($message, $context = 'general', $data = array()) {
        self::log($message, self::LEVEL_DEBUG, $context, $data);
    }
    
    /**
     * Format log entry
     */
    private static function format_log_entry($message, $level, $context, $data) {
        $timestamp = current_time('Y-m-d H:i:s');
        $level_name = self::$log_levels[$level];
        $user_id = get_current_user_id();
        $ip = self::get_client_ip();
        
        $log_parts = array(
            '[' . $timestamp . ']',
            '[LECTUS]',
            '[' . $level_name . ']',
            '[' . $context . ']',
            '[User:' . $user_id . ']',
            '[IP:' . $ip . ']',
            $message
        );
        
        $log_entry = implode(' ', $log_parts);
        
        // Add data if present
        if (!empty($data)) {
            $log_entry .= ' | Data: ' . wp_json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        
        return $log_entry;
    }
    
    /**
     * Store log entry in database
     */
    private static function store_log_entry($message, $level, $context, $data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'lectus_logs';
        
        // Create table if not exists
        self::maybe_create_log_table();
        
        $wpdb->insert(
            $table_name,
            array(
                'level' => $level,
                'context' => $context,
                'message' => $message,
                'data' => wp_json_encode($data),
                'user_id' => get_current_user_id(),
                'ip_address' => self::get_client_ip(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'created_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s')
        );
    }
    
    /**
     * Create log table
     */
    private static function maybe_create_log_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'lectus_logs';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            level tinyint(1) NOT NULL,
            context varchar(50) NOT NULL,
            message text NOT NULL,
            data longtext,
            user_id bigint(20) DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY level (level),
            KEY context (context),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Get log entries for admin viewing
     */
    public static function get_log_entries($limit = 100, $level = null, $context = null) {
        if (!current_user_can('manage_options')) {
            return array();
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'lectus_logs';
        
        $query = "SELECT * FROM $table_name WHERE 1=1";
        $params = array();
        
        if ($level) {
            $query .= " AND level = %d";
            $params[] = $level;
        }
        
        if ($context) {
            $query .= " AND context = %s";
            $params[] = $context;
        }
        
        $query .= " ORDER BY created_at DESC LIMIT %d";
        $params[] = $limit;
        
        if (empty($params)) {
            return $wpdb->get_results($query);
        }
        
        return $wpdb->get_results($wpdb->prepare($query, $params));
    }
    
    /**
     * Clear old log entries
     */
    public static function cleanup_old_logs($days = 30) {
        if (!current_user_can('manage_options')) {
            return false;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'lectus_logs';
        
        $result = $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_name WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
        
        self::info('Log cleanup completed', 'maintenance', array('deleted_entries' => $result));
        
        return $result;
    }
    
    /**
     * Get client IP address
     */
    private static function get_client_ip() {
        $ip_keys = array('HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Get PHP error type name
     */
    private static function get_error_type($errno) {
        $error_types = array(
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_STRICT => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED'
        );
        
        return $error_types[$errno] ?? 'UNKNOWN';
    }
    
    /**
     * Check if logging is enabled
     */
    private static function is_logging_enabled() {
        return get_option('lectus_enable_logging', true);
    }
    
    /**
     * Get current log level
     */
    private static function get_log_level() {
        return get_option('lectus_log_level', self::LEVEL_WARNING);
    }
    
    /**
     * Check if should store in database
     */
    private static function should_store_in_db($level) {
        return $level <= self::LEVEL_WARNING && get_option('lectus_store_logs_db', false);
    }
    
    /**
     * Check if should email errors
     */
    private static function should_email_errors() {
        return get_option('lectus_email_errors', false) && get_option('admin_email');
    }
    
    /**
     * Send error email
     */
    private static function send_error_email($message, $context, $data) {
        $admin_email = get_option('admin_email');
        if (!$admin_email) {
            return;
        }
        
        $subject = '[' . get_bloginfo('name') . '] Lectus Class System Error';
        $body = sprintf(
            "An error occurred in Lectus Class System:\n\nContext: %s\nMessage: %s\nTime: %s\nUser: %s\nURL: %s\n\nData: %s",
            $context,
            $message,
            current_time('Y-m-d H:i:s'),
            wp_get_current_user()->user_login ?? 'Guest',
            $_SERVER['REQUEST_URI'] ?? 'Unknown',
            wp_json_encode($data, JSON_PRETTY_PRINT)
        );
        
        wp_mail($admin_email, $subject, $body);
    }
}