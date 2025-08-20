<?php
/**
 * Wishlist functionality for Lectus Class System
 * 
 * @package LectusClassSystem
 * @since 1.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Wishlist {
    
    /**
     * Table name
     */
    private static $table_name;
    
    /**
     * Initialize the wishlist class
     */
    public static function init() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'lectus_wishlist';
        
        // Add hooks
        add_action('wp_ajax_lectus_toggle_wishlist', array(__CLASS__, 'ajax_toggle_wishlist'));
        add_action('wp_ajax_nopriv_lectus_toggle_wishlist', array(__CLASS__, 'ajax_toggle_wishlist_nopriv'));
        add_action('wp_ajax_lectus_get_wishlist_status', array(__CLASS__, 'ajax_get_wishlist_status'));
        add_action('wp_ajax_nopriv_lectus_get_wishlist_status', array(__CLASS__, 'ajax_get_wishlist_status_nopriv'));
    }
    
    /**
     * Create database table
     */
    public static function create_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'lectus_wishlist';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            course_id bigint(20) UNSIGNED NOT NULL,
            added_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_course (user_id, course_id),
            KEY user_id (user_id),
            KEY course_id (course_id),
            KEY added_at (added_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $result = dbDelta($sql);
        
        // Verify table creation
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        if ($table_exists) {
            error_log('[Lectus Wishlist] Table created successfully: ' . $table_name);
        } else {
            error_log('[Lectus Wishlist] Failed to create table: ' . $table_name);
            error_log('[Lectus Wishlist] SQL: ' . $sql);
            if (!empty($wpdb->last_error)) {
                error_log('[Lectus Wishlist] DB Error: ' . $wpdb->last_error);
            }
        }
        
        return $table_exists;
    }
    
    /**
     * Check if table exists and create if needed
     */
    public static function ensure_table_exists() {
        global $wpdb;
        
        if (!isset(self::$table_name)) {
            self::$table_name = $wpdb->prefix . 'lectus_wishlist';
        }
        
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '" . self::$table_name . "'") == self::$table_name;
        
        if (!$table_exists) {
            error_log('[Lectus Wishlist] Table missing, creating...');
            return self::create_table();
        }
        
        return true;
    }
    
    /**
     * Check if course is in user's wishlist
     * 
     * @param int $user_id
     * @param int $course_id
     * @return bool
     */
    public static function is_in_wishlist($user_id, $course_id) {
        global $wpdb;
        
        if (!$user_id || !$course_id) {
            return false;
        }
        
        // Ensure table exists
        if (!self::ensure_table_exists()) {
            error_log('[Lectus Wishlist] Cannot check wishlist - table creation failed');
            return false;
        }
        
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM " . self::$table_name . " WHERE user_id = %d AND course_id = %d",
            $user_id,
            $course_id
        ));
        
        if ($wpdb->last_error) {
            error_log('[Lectus Wishlist] DB Error in is_in_wishlist: ' . $wpdb->last_error);
            return false;
        }
        
        return $exists > 0;
    }
    
    /**
     * Add course to wishlist
     * 
     * @param int $user_id
     * @param int $course_id
     * @return bool|WP_Error
     */
    public static function add_to_wishlist($user_id, $course_id) {
        global $wpdb;
        
        // Validate input
        if (!$user_id || !$course_id) {
            return new WP_Error('invalid_input', __('잘못된 요청입니다.', 'lectus-class-system'));
        }
        
        // Ensure table exists
        if (!self::ensure_table_exists()) {
            return new WP_Error('db_error', __('데이터베이스 테이블을 생성할 수 없습니다.', 'lectus-class-system'));
        }
        
        // Check if course exists
        if (!get_post($course_id) || get_post_type($course_id) !== 'coursesingle') {
            return new WP_Error('invalid_course', __('유효하지 않은 강의입니다.', 'lectus-class-system'));
        }
        
        // Check if already in wishlist
        if (self::is_in_wishlist($user_id, $course_id)) {
            return new WP_Error('already_exists', __('이미 위시리스트에 있습니다.', 'lectus-class-system'));
        }
        
        // Add to wishlist
        $result = $wpdb->insert(
            self::$table_name,
            array(
                'user_id' => $user_id,
                'course_id' => $course_id,
                'added_at' => current_time('mysql')
            ),
            array('%d', '%d', '%s')
        );
        
        if ($result === false) {
            $error_msg = $wpdb->last_error ? $wpdb->last_error : __('데이터베이스 오류가 발생했습니다.', 'lectus-class-system');
            error_log('[Lectus Wishlist] Insert error: ' . $error_msg);
            return new WP_Error('db_error', $error_msg);
        }
        
        // Trigger action
        do_action('lectus_wishlist_added', $user_id, $course_id);
        
        return true;
    }
    
    /**
     * Remove course from wishlist
     * 
     * @param int $user_id
     * @param int $course_id
     * @return bool|WP_Error
     */
    public static function remove_from_wishlist($user_id, $course_id) {
        global $wpdb;
        
        // Validate input
        if (!$user_id || !$course_id) {
            return new WP_Error('invalid_input', __('잘못된 요청입니다.', 'lectus-class-system'));
        }
        
        // Ensure table exists
        if (!self::ensure_table_exists()) {
            return new WP_Error('db_error', __('데이터베이스 테이블을 생성할 수 없습니다.', 'lectus-class-system'));
        }
        
        // Check if in wishlist
        if (!self::is_in_wishlist($user_id, $course_id)) {
            return new WP_Error('not_found', __('위시리스트에 없는 강의입니다.', 'lectus-class-system'));
        }
        
        // Remove from wishlist
        $result = $wpdb->delete(
            self::$table_name,
            array(
                'user_id' => $user_id,
                'course_id' => $course_id
            ),
            array('%d', '%d')
        );
        
        if ($result === false) {
            $error_msg = $wpdb->last_error ? $wpdb->last_error : __('데이터베이스 오류가 발생했습니다.', 'lectus-class-system');
            error_log('[Lectus Wishlist] Delete error: ' . $error_msg);
            return new WP_Error('db_error', $error_msg);
        }
        
        // Trigger action
        do_action('lectus_wishlist_removed', $user_id, $course_id);
        
        return true;
    }
    
    /**
     * Toggle wishlist status
     * 
     * @param int $user_id
     * @param int $course_id
     * @return array
     */
    public static function toggle_wishlist($user_id, $course_id) {
        if (self::is_in_wishlist($user_id, $course_id)) {
            $result = self::remove_from_wishlist($user_id, $course_id);
            $action = 'removed';
        } else {
            $result = self::add_to_wishlist($user_id, $course_id);
            $action = 'added';
        }
        
        if (is_wp_error($result)) {
            return array(
                'success' => false,
                'message' => $result->get_error_message(),
                'action' => null
            );
        }
        
        return array(
            'success' => true,
            'action' => $action,
            'message' => $action === 'added' 
                ? __('위시리스트에 추가되었습니다.', 'lectus-class-system')
                : __('위시리스트에서 제거되었습니다.', 'lectus-class-system')
        );
    }
    
    /**
     * Get user's wishlist
     * 
     * @param int $user_id
     * @param array $args
     * @return array
     */
    public static function get_user_wishlist($user_id, $args = array()) {
        global $wpdb;
        
        // Ensure table exists
        if (!self::ensure_table_exists()) {
            error_log('[Lectus Wishlist] Cannot get user wishlist - table creation failed');
            return array();
        }
        
        $defaults = array(
            'limit' => -1,
            'offset' => 0,
            'orderby' => 'added_at',
            'order' => 'DESC',
            'return_ids' => false
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Build query
        $query = "SELECT course_id, added_at FROM " . self::$table_name . " WHERE user_id = %d";
        $query_args = array($user_id);
        
        // Add ordering
        $allowed_orderby = array('added_at', 'course_id');
        $orderby = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'added_at';
        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';
        $query .= " ORDER BY $orderby $order";
        
        // Add limit
        if ($args['limit'] > 0) {
            $query .= " LIMIT %d OFFSET %d";
            $query_args[] = $args['limit'];
            $query_args[] = $args['offset'];
        }
        
        // Execute query
        $results = $wpdb->get_results($wpdb->prepare($query, $query_args));
        
        if ($wpdb->last_error) {
            error_log('[Lectus Wishlist] DB Error in get_user_wishlist: ' . $wpdb->last_error);
            return array();
        }
        
        if ($args['return_ids']) {
            return wp_list_pluck($results, 'course_id');
        }
        
        // Get full course data
        $wishlist = array();
        foreach ($results as $item) {
            $course = get_post($item->course_id);
            if ($course && $course->post_status === 'publish') {
                $wishlist[] = array(
                    'course_id' => $item->course_id,
                    'course' => $course,
                    'added_at' => $item->added_at
                );
            }
        }
        
        return $wishlist;
    }
    
    /**
     * Get wishlist count for user
     * 
     * @param int $user_id
     * @return int
     */
    public static function get_wishlist_count($user_id) {
        global $wpdb;
        
        // Ensure table exists
        if (!self::ensure_table_exists()) {
            return 0;
        }
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM " . self::$table_name . " WHERE user_id = %d",
            $user_id
        ));
        
        if ($wpdb->last_error) {
            error_log('[Lectus Wishlist] DB Error in get_wishlist_count: ' . $wpdb->last_error);
            return 0;
        }
        
        return (int) $count;
    }
    
    /**
     * Get users who wishlisted a course
     * 
     * @param int $course_id
     * @return int
     */
    public static function get_course_wishlist_count($course_id) {
        global $wpdb;
        
        // Ensure table exists
        if (!self::ensure_table_exists()) {
            return 0;
        }
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM " . self::$table_name . " WHERE course_id = %d",
            $course_id
        ));
        
        if ($wpdb->last_error) {
            error_log('[Lectus Wishlist] DB Error in get_course_wishlist_count: ' . $wpdb->last_error);
            return 0;
        }
        
        return (int) $count;
    }
    
    /**
     * AJAX handler for toggling wishlist
     */
    public static function ajax_toggle_wishlist() {
        // Verify nonce
        if (!check_ajax_referer('lectus_academy_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('보안 검증에 실패했습니다.', 'lectus-class-system')));
            return;
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(array(
                'message' => __('로그인이 필요한 기능입니다.', 'lectus-class-system'),
                'require_login' => true
            ));
            return;
        }
        
        $user_id = get_current_user_id();
        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        
        if (!$course_id) {
            wp_send_json_error(array('message' => __('잘못된 요청입니다.', 'lectus-class-system')));
            return;
        }
        
        // Log the request
        error_log('[Lectus Wishlist] Toggle request - User: ' . $user_id . ', Course: ' . $course_id);
        
        // Toggle wishlist
        $result = self::toggle_wishlist($user_id, $course_id);
        
        if ($result['success']) {
            $result['count'] = self::get_wishlist_count($user_id);
            $result['course_count'] = self::get_course_wishlist_count($course_id);
            
            error_log('[Lectus Wishlist] Toggle success - Action: ' . $result['action']);
            
            wp_send_json_success($result);
        } else {
            error_log('[Lectus Wishlist] Toggle failed - Error: ' . $result['message']);
            wp_send_json_error($result);
        }
        
        // Make sure to exit
        wp_die();
    }
    
    /**
     * AJAX handler for non-logged in users
     */
    public static function ajax_toggle_wishlist_nopriv() {
        wp_send_json_error(array(
            'message' => __('로그인이 필요한 기능입니다.', 'lectus-class-system'),
            'require_login' => true
        ));
    }
    
    /**
     * AJAX handler for getting wishlist status
     */
    public static function ajax_get_wishlist_status() {
        // Verify nonce
        if (!check_ajax_referer('lectus_academy_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('보안 검증에 실패했습니다.', 'lectus-class-system')));
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_success(array('in_wishlist' => false, 'logged_in' => false));
        }
        
        $user_id = get_current_user_id();
        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        
        if (!$course_id) {
            wp_send_json_error(array('message' => __('잘못된 요청입니다.', 'lectus-class-system')));
        }
        
        $in_wishlist = self::is_in_wishlist($user_id, $course_id);
        
        wp_send_json_success(array(
            'in_wishlist' => $in_wishlist,
            'logged_in' => true,
            'count' => self::get_course_wishlist_count($course_id)
        ));
    }
    
    /**
     * AJAX handler for non-logged in users getting status
     */
    public static function ajax_get_wishlist_status_nopriv() {
        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        
        if (!$course_id) {
            wp_send_json_error(array('message' => __('잘못된 요청입니다.', 'lectus-class-system')));
        }
        
        wp_send_json_success(array(
            'in_wishlist' => false,
            'logged_in' => false,
            'count' => self::get_course_wishlist_count($course_id)
        ));
    }
    
    /**
     * Clean up wishlist for deleted courses
     */
    public static function cleanup_deleted_courses() {
        global $wpdb;
        
        // Get all course IDs in wishlist
        $course_ids = $wpdb->get_col("SELECT DISTINCT course_id FROM " . self::$table_name);
        
        if (empty($course_ids)) {
            return;
        }
        
        // Find deleted courses
        $existing_courses = get_posts(array(
            'post_type' => 'coursesingle',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'post__in' => $course_ids
        ));
        
        $deleted_courses = array_diff($course_ids, $existing_courses);
        
        // Remove deleted courses from wishlist
        if (!empty($deleted_courses)) {
            $placeholders = implode(',', array_fill(0, count($deleted_courses), '%d'));
            $wpdb->query($wpdb->prepare(
                "DELETE FROM " . self::$table_name . " WHERE course_id IN ($placeholders)",
                $deleted_courses
            ));
        }
    }
}

// Initialize the class
add_action('init', array('Lectus_Wishlist', 'init'));