<?php
/**
 * Rate Limiting System for Lectus Class System
 * 
 * Provides comprehensive rate limiting for all AJAX endpoints and critical operations
 * 
 * @package Lectus_Class_System
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Rate_Limiter {
    
    /**
     * Rate limit configurations per action
     */
    private static $limits = array(
        // Q&A System
        'qa_submit_question' => array('limit' => 10, 'window' => HOUR_IN_SECONDS),
        'qa_submit_answer' => array('limit' => 20, 'window' => HOUR_IN_SECONDS),
        'qa_vote' => array('limit' => 50, 'window' => HOUR_IN_SECONDS),
        
        // Materials
        'material_upload' => array('limit' => 20, 'window' => HOUR_IN_SECONDS),
        'material_download' => array('limit' => 100, 'window' => HOUR_IN_SECONDS),
        
        // Enrollment
        'free_enrollment' => array('limit' => 5, 'window' => DAY_IN_SECONDS),
        'enrollment_request' => array('limit' => 10, 'window' => HOUR_IN_SECONDS),
        
        // Progress
        'mark_complete' => array('limit' => 100, 'window' => HOUR_IN_SECONDS),
        'progress_update' => array('limit' => 200, 'window' => HOUR_IN_SECONDS),
        
        // General AJAX
        'ajax_request' => array('limit' => 100, 'window' => 300), // 5 minutes
        'form_submission' => array('limit' => 20, 'window' => 300),
        
        // Authentication
        'login_attempt' => array('limit' => 5, 'window' => 900), // 15 minutes
        'password_reset' => array('limit' => 3, 'window' => HOUR_IN_SECONDS),
        
        // API
        'api_request' => array('limit' => 1000, 'window' => HOUR_IN_SECONDS),
        
        // Default
        'default' => array('limit' => 60, 'window' => 60) // 60 requests per minute
    );
    
    /**
     * Check if action is rate limited
     * 
     * @param string $action Action identifier
     * @param int|null $user_id User ID (null for IP-based limiting)
     * @param array|null $custom_limit Custom limit configuration
     * @return bool|WP_Error True if allowed, WP_Error if rate limited
     */
    public static function check($action = 'default', $user_id = null, $custom_limit = null) {
        // Get configuration
        $config = $custom_limit ?: self::get_limit_config($action);
        
        // Get identifier
        $identifier = self::get_identifier($action, $user_id);
        
        // Get current count
        $current_count = self::get_current_count($identifier);
        
        // Check if limit exceeded
        if ($current_count >= $config['limit']) {
            $retry_after = self::get_retry_after($identifier, $config['window']);
            
            return new WP_Error(
                'rate_limit_exceeded',
                sprintf(
                    __('Rate limit exceeded. Please try again in %s.', 'lectus-class-system'),
                    human_time_diff(time(), time() + $retry_after)
                ),
                array(
                    'status' => 429,
                    'retry_after' => $retry_after,
                    'limit' => $config['limit'],
                    'window' => $config['window']
                )
            );
        }
        
        // Increment counter
        self::increment_counter($identifier, $config['window']);
        
        return true;
    }
    
    /**
     * Check rate limit without incrementing
     * 
     * @param string $action Action identifier
     * @param int|null $user_id User ID
     * @return array Rate limit status
     */
    public static function get_status($action = 'default', $user_id = null) {
        $config = self::get_limit_config($action);
        $identifier = self::get_identifier($action, $user_id);
        $current_count = self::get_current_count($identifier);
        $remaining = max(0, $config['limit'] - $current_count);
        
        return array(
            'limit' => $config['limit'],
            'remaining' => $remaining,
            'used' => $current_count,
            'window' => $config['window'],
            'reset_at' => self::get_reset_time($identifier, $config['window'])
        );
    }
    
    /**
     * Reset rate limit for specific action and user
     * 
     * @param string $action Action identifier
     * @param int|null $user_id User ID
     * @return bool
     */
    public static function reset($action = 'default', $user_id = null) {
        $identifier = self::get_identifier($action, $user_id);
        return delete_transient($identifier);
    }
    
    /**
     * Reset all rate limits for a user
     * 
     * @param int $user_id User ID
     * @return int Number of limits reset
     */
    public static function reset_user_limits($user_id) {
        global $wpdb;
        
        $pattern = 'lectus_rate_limit_%_user_' . $user_id;
        $sql = $wpdb->prepare(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE %s 
             AND option_name LIKE %s",
            $wpdb->esc_like('_transient_lectus_rate_limit_') . '%',
            '%' . $wpdb->esc_like('_user_' . $user_id)
        );
        
        return $wpdb->query($sql);
    }
    
    /**
     * Get limit configuration for action
     * 
     * @param string $action Action identifier
     * @return array
     */
    private static function get_limit_config($action) {
        return self::$limits[$action] ?? self::$limits['default'];
    }
    
    /**
     * Get unique identifier for rate limiting
     * 
     * @param string $action Action identifier
     * @param int|null $user_id User ID
     * @return string
     */
    private static function get_identifier($action, $user_id = null) {
        if ($user_id) {
            return 'lectus_rate_limit_' . $action . '_user_' . $user_id;
        } else {
            $ip = self::get_client_ip();
            return 'lectus_rate_limit_' . $action . '_ip_' . md5($ip);
        }
    }
    
    /**
     * Get current request count
     * 
     * @param string $identifier Unique identifier
     * @return int
     */
    private static function get_current_count($identifier) {
        $data = get_transient($identifier);
        
        if (!$data || !is_array($data)) {
            return 0;
        }
        
        return $data['count'] ?? 0;
    }
    
    /**
     * Increment request counter
     * 
     * @param string $identifier Unique identifier
     * @param int $window Time window in seconds
     * @return void
     */
    private static function increment_counter($identifier, $window) {
        $data = get_transient($identifier);
        
        if (!$data || !is_array($data)) {
            $data = array(
                'count' => 0,
                'first_request' => time()
            );
        }
        
        $data['count']++;
        $data['last_request'] = time();
        
        set_transient($identifier, $data, $window);
    }
    
    /**
     * Get retry after time
     * 
     * @param string $identifier Unique identifier
     * @param int $window Time window
     * @return int Seconds until retry
     */
    private static function get_retry_after($identifier, $window) {
        $data = get_transient($identifier);
        
        if (!$data || !isset($data['first_request'])) {
            return 0;
        }
        
        $elapsed = time() - $data['first_request'];
        return max(0, $window - $elapsed);
    }
    
    /**
     * Get reset time for rate limit
     * 
     * @param string $identifier Unique identifier
     * @param int $window Time window
     * @return int Unix timestamp
     */
    private static function get_reset_time($identifier, $window) {
        $data = get_transient($identifier);
        
        if (!$data || !isset($data['first_request'])) {
            return time() + $window;
        }
        
        return $data['first_request'] + $window;
    }
    
    /**
     * Get client IP address
     * 
     * @return string
     */
    private static function get_client_ip() {
        $ip_keys = array(
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',  // Proxy
            'HTTP_X_REAL_IP',        // Nginx
            'REMOTE_ADDR'            // Standard
        );
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = $_SERVER[$key];
                
                // Handle comma-separated IPs
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                
                $ip = trim($ip);
                
                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP,
                    FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Add rate limit headers to response
     * 
     * @param string $action Action identifier
     * @param int|null $user_id User ID
     * @return void
     */
    public static function add_headers($action = 'default', $user_id = null) {
        $status = self::get_status($action, $user_id);
        
        header('X-RateLimit-Limit: ' . $status['limit']);
        header('X-RateLimit-Remaining: ' . $status['remaining']);
        header('X-RateLimit-Reset: ' . $status['reset_at']);
    }
    
    /**
     * AJAX handler wrapper with rate limiting
     * 
     * @param callable $callback Callback function
     * @param string $action Rate limit action
     * @param array|null $custom_limit Custom limit configuration
     * @return void
     */
    public static function ajax_handler($callback, $action = 'ajax_request', $custom_limit = null) {
        $user_id = get_current_user_id();
        $check = self::check($action, $user_id ?: null, $custom_limit);
        
        if (is_wp_error($check)) {
            wp_send_json_error(array(
                'message' => $check->get_error_message(),
                'code' => 'rate_limit_exceeded',
                'data' => $check->get_error_data()
            ), 429);
            return;
        }
        
        // Add rate limit headers
        self::add_headers($action, $user_id ?: null);
        
        // Execute callback
        call_user_func($callback);
    }
    
    /**
     * Initialize rate limiting system
     */
    public static function init() {
        // Add admin page for rate limit management
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        
        // Clean up expired transients
        add_action('wp_scheduled_delete', array(__CLASS__, 'cleanup_expired_limits'));
        
        // Add REST API rate limiting
        add_filter('rest_pre_dispatch', array(__CLASS__, 'rest_rate_limit'), 10, 3);
    }
    
    /**
     * Add admin menu for rate limit management
     */
    public static function add_admin_menu() {
        if (current_user_can('manage_options')) {
            add_submenu_page(
                'lectus-class-system',
                __('Rate Limits', 'lectus-class-system'),
                __('Rate Limits', 'lectus-class-system'),
                'manage_options',
                'lectus-rate-limits',
                array(__CLASS__, 'render_admin_page')
            );
        }
    }
    
    /**
     * REST API rate limiting
     */
    public static function rest_rate_limit($result, $server, $request) {
        if (strpos($request->get_route(), '/lectus/') === 0) {
            $user_id = get_current_user_id();
            $check = self::check('api_request', $user_id ?: null);
            
            if (is_wp_error($check)) {
                return $check;
            }
        }
        
        return $result;
    }
}