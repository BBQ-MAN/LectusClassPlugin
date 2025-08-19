<?php
/**
 * Cache Management System for Lectus Class System
 * 
 * Provides comprehensive caching for expensive queries and operations
 * 
 * @package Lectus_Class_System
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Cache_Manager {
    
    /**
     * Cache groups for organization
     */
    const CACHE_GROUP = 'lectus';
    const CACHE_GROUP_COURSES = 'lectus_courses';
    const CACHE_GROUP_STUDENTS = 'lectus_students';
    const CACHE_GROUP_PROGRESS = 'lectus_progress';
    const CACHE_GROUP_QA = 'lectus_qa';
    const CACHE_GROUP_MATERIALS = 'lectus_materials';
    
    /**
     * Initialize cache manager
     */
    public static function init() {
        // Clear cache on specific actions
        add_action('lectus_student_enrolled', array(__CLASS__, 'clear_enrollment_cache'), 10, 2);
        add_action('lectus_student_unenrolled', array(__CLASS__, 'clear_enrollment_cache'), 10, 2);
        add_action('lectus_lesson_completed', array(__CLASS__, 'clear_progress_cache'), 10, 2);
        add_action('save_post_coursesingle', array(__CLASS__, 'clear_course_cache'), 10, 1);
        add_action('save_post_lesson', array(__CLASS__, 'clear_lesson_cache'), 10, 1);
        
        // Admin cache clear action
        add_action('admin_init', array(__CLASS__, 'handle_cache_clear_request'));
        
        // Schedule cache cleanup
        add_action('lectus_hourly_cache_cleanup', array(__CLASS__, 'cleanup_expired_cache'));
        
        if (!wp_next_scheduled('lectus_hourly_cache_cleanup')) {
            wp_schedule_event(time(), 'hourly', 'lectus_hourly_cache_cleanup');
        }
    }
    
    /**
     * Get cached data or execute callback
     * 
     * @param string $key Cache key
     * @param callable $callback Function to get data if not cached
     * @param int $expiration Cache duration in seconds
     * @param string $group Cache group
     * @return mixed
     */
    public static function get_cached($key, $callback, $expiration = null, $group = null) {
        // Use defaults if not provided
        $expiration = $expiration ?: LECTUS_CACHE_DURATION;
        $group = $group ?: self::CACHE_GROUP;
        
        // Try to get from cache
        $cached = self::get($key, $group);
        
        if ($cached !== false) {
            return $cached;
        }
        
        // Execute callback to get fresh data
        $data = call_user_func($callback);
        
        // Store in cache
        if ($data !== false && $data !== null) {
            self::set($key, $data, $group, $expiration);
        }
        
        return $data;
    }
    
    /**
     * Get cache value
     * 
     * @param string $key Cache key
     * @param string $group Cache group
     * @return mixed
     */
    public static function get($key, $group = null) {
        $group = $group ?: self::CACHE_GROUP;
        
        // Try object cache first
        if (LECTUS_ENABLE_OBJECT_CACHE && function_exists('wp_cache_get')) {
            $value = wp_cache_get($key, $group);
            if ($value !== false) {
                return $value;
            }
        }
        
        // Fallback to transients
        $transient_key = self::get_transient_key($key, $group);
        return get_transient($transient_key);
    }
    
    /**
     * Set cache value
     * 
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param string $group Cache group
     * @param int $expiration Expiration in seconds
     * @return bool
     */
    public static function set($key, $value, $group = null, $expiration = null) {
        $group = $group ?: self::CACHE_GROUP;
        $expiration = $expiration ?: LECTUS_CACHE_DURATION;
        
        // Store in object cache
        if (LECTUS_ENABLE_OBJECT_CACHE && function_exists('wp_cache_set')) {
            wp_cache_set($key, $value, $group, $expiration);
        }
        
        // Also store in transients for persistence
        $transient_key = self::get_transient_key($key, $group);
        return set_transient($transient_key, $value, $expiration);
    }
    
    /**
     * Delete cache value
     * 
     * @param string $key Cache key
     * @param string $group Cache group
     * @return bool
     */
    public static function delete($key, $group = null) {
        $group = $group ?: self::CACHE_GROUP;
        
        // Delete from object cache
        if (LECTUS_ENABLE_OBJECT_CACHE && function_exists('wp_cache_delete')) {
            wp_cache_delete($key, $group);
        }
        
        // Delete transient
        $transient_key = self::get_transient_key($key, $group);
        return delete_transient($transient_key);
    }
    
    /**
     * Clear cache group
     * 
     * @param string $group Cache group to clear
     * @return bool
     */
    public static function clear_group($group) {
        global $wpdb;
        
        // Clear object cache group
        if (LECTUS_ENABLE_OBJECT_CACHE && function_exists('wp_cache_flush_group')) {
            wp_cache_flush_group($group);
        }
        
        // Clear transients for this group
        $prefix = '_transient_' . $group . '_';
        $sql = $wpdb->prepare(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE %s",
            $wpdb->esc_like($prefix) . '%'
        );
        
        return $wpdb->query($sql) !== false;
    }
    
    /**
     * Clear all Lectus cache
     * 
     * @return bool
     */
    public static function clear_all() {
        $groups = array(
            self::CACHE_GROUP,
            self::CACHE_GROUP_COURSES,
            self::CACHE_GROUP_STUDENTS,
            self::CACHE_GROUP_PROGRESS,
            self::CACHE_GROUP_QA,
            self::CACHE_GROUP_MATERIALS
        );
        
        foreach ($groups as $group) {
            self::clear_group($group);
        }
        
        // Clear specific cached items
        delete_transient('lectus_statistics');
        delete_transient('lectus_recent_enrollments');
        delete_transient('lectus_popular_courses');
        
        return true;
    }
    
    /**
     * Cache course data
     * 
     * @param int $course_id Course ID
     * @param array $data Course data
     * @return bool
     */
    public static function cache_course($course_id, $data) {
        $key = 'course_' . $course_id;
        return self::set($key, $data, self::CACHE_GROUP_COURSES, LECTUS_COURSE_CACHE_DURATION);
    }
    
    /**
     * Get cached course data
     * 
     * @param int $course_id Course ID
     * @return mixed
     */
    public static function get_course($course_id) {
        $key = 'course_' . $course_id;
        return self::get($key, self::CACHE_GROUP_COURSES);
    }
    
    /**
     * Cache student enrollments
     * 
     * @param int $user_id User ID
     * @param array $enrollments Enrollment data
     * @return bool
     */
    public static function cache_user_enrollments($user_id, $enrollments) {
        $key = 'enrollments_' . $user_id;
        return self::set($key, $enrollments, self::CACHE_GROUP_STUDENTS, LECTUS_CACHE_DURATION);
    }
    
    /**
     * Get cached student enrollments
     * 
     * @param int $user_id User ID
     * @return mixed
     */
    public static function get_user_enrollments($user_id) {
        $key = 'enrollments_' . $user_id;
        return self::get($key, self::CACHE_GROUP_STUDENTS);
    }
    
    /**
     * Cache course progress
     * 
     * @param int $user_id User ID
     * @param int $course_id Course ID
     * @param array $progress Progress data
     * @return bool
     */
    public static function cache_course_progress($user_id, $course_id, $progress) {
        $key = 'progress_' . $user_id . '_' . $course_id;
        return self::set($key, $progress, self::CACHE_GROUP_PROGRESS, LECTUS_PROGRESS_CACHE_DURATION);
    }
    
    /**
     * Get cached course progress
     * 
     * @param int $user_id User ID
     * @param int $course_id Course ID
     * @return mixed
     */
    public static function get_course_progress($user_id, $course_id) {
        $key = 'progress_' . $user_id . '_' . $course_id;
        return self::get($key, self::CACHE_GROUP_PROGRESS);
    }
    
    /**
     * Cache expensive query result
     * 
     * @param string $query_hash Query hash
     * @param mixed $result Query result
     * @param int $expiration Cache duration
     * @return bool
     */
    public static function cache_query($query_hash, $result, $expiration = null) {
        $key = 'query_' . $query_hash;
        return self::set($key, $result, self::CACHE_GROUP, $expiration);
    }
    
    /**
     * Get cached query result
     * 
     * @param string $query_hash Query hash
     * @return mixed
     */
    public static function get_query($query_hash) {
        $key = 'query_' . $query_hash;
        return self::get($key, self::CACHE_GROUP);
    }
    
    /**
     * Clear enrollment cache
     * 
     * @param int $user_id User ID
     * @param int $course_id Course ID
     */
    public static function clear_enrollment_cache($user_id, $course_id) {
        self::delete('enrollments_' . $user_id, self::CACHE_GROUP_STUDENTS);
        self::delete('course_students_' . $course_id, self::CACHE_GROUP_COURSES);
        self::clear_statistics_cache();
    }
    
    /**
     * Clear progress cache
     * 
     * @param int $user_id User ID
     * @param int $lesson_id Lesson ID
     */
    public static function clear_progress_cache($user_id, $lesson_id) {
        // Get course ID from lesson
        $course_id = get_post_meta($lesson_id, '_course_id', true);
        
        if ($course_id) {
            self::delete('progress_' . $user_id . '_' . $course_id, self::CACHE_GROUP_PROGRESS);
        }
        
        self::clear_statistics_cache();
    }
    
    /**
     * Clear course cache
     * 
     * @param int $course_id Course ID
     */
    public static function clear_course_cache($course_id) {
        self::delete('course_' . $course_id, self::CACHE_GROUP_COURSES);
        self::delete('course_students_' . $course_id, self::CACHE_GROUP_COURSES);
        self::delete('course_lessons_' . $course_id, self::CACHE_GROUP_COURSES);
        self::clear_statistics_cache();
    }
    
    /**
     * Clear lesson cache
     * 
     * @param int $lesson_id Lesson ID
     */
    public static function clear_lesson_cache($lesson_id) {
        $course_id = get_post_meta($lesson_id, '_course_id', true);
        
        if ($course_id) {
            self::delete('course_lessons_' . $course_id, self::CACHE_GROUP_COURSES);
        }
    }
    
    /**
     * Clear statistics cache
     */
    public static function clear_statistics_cache() {
        delete_transient('lectus_statistics');
        delete_transient('lectus_recent_enrollments');
        delete_transient('lectus_popular_courses');
        delete_transient('lectus_completion_rates');
    }
    
    /**
     * Get transient key for fallback storage
     * 
     * @param string $key Cache key
     * @param string $group Cache group
     * @return string
     */
    private static function get_transient_key($key, $group) {
        return substr($group . '_' . $key, 0, 172); // Max transient name length
    }
    
    /**
     * Handle admin cache clear request
     */
    public static function handle_cache_clear_request() {
        if (isset($_POST['lectus_clear_cache']) && 
            current_user_can('manage_options') &&
            wp_verify_nonce($_POST['lectus_cache_nonce'], 'lectus_clear_cache')) {
            
            self::clear_all();
            
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible">';
                echo '<p>' . __('Cache cleared successfully.', 'lectus-class-system') . '</p>';
                echo '</div>';
            });
        }
    }
    
    /**
     * Clean up expired cache
     */
    public static function cleanup_expired_cache() {
        global $wpdb;
        
        // Clean expired transients
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_timeout_lectus%' 
             AND option_value < UNIX_TIMESTAMP()"
        );
        
        // Also delete the actual transients
        $wpdb->query(
            "DELETE o1 FROM {$wpdb->options} o1
             LEFT JOIN {$wpdb->options} o2 
             ON o2.option_name = CONCAT('_transient_timeout_', SUBSTRING(o1.option_name, 12))
             WHERE o1.option_name LIKE '_transient_lectus%'
             AND o2.option_name IS NULL"
        );
    }
    
    /**
     * Get cache statistics
     * 
     * @return array
     */
    public static function get_cache_stats() {
        global $wpdb;
        
        $stats = array(
            'total_cached_items' => 0,
            'cache_size' => 0,
            'groups' => array()
        );
        
        // Count transients
        $count = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_lectus%'"
        );
        
        $stats['total_cached_items'] = intval($count);
        
        // Estimate cache size
        $size = $wpdb->get_var(
            "SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_lectus%'"
        );
        
        $stats['cache_size'] = intval($size);
        
        return $stats;
    }
}