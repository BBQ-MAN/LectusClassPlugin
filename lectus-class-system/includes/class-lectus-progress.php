<?php
/**
 * Progress Tracking for Lectus Class System
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Progress {
    
    public static function init() {
        // Scheduled events
        add_action('init', array(__CLASS__, 'schedule_events'));
        add_action('lectus_daily_progress_check', array(__CLASS__, 'daily_progress_check'));
    }
    
    public static function schedule_events() {
        if (!wp_next_scheduled('lectus_daily_progress_check')) {
            wp_schedule_event(time(), 'daily', 'lectus_daily_progress_check');
        }
    }
    
    public static function update_progress($user_id, $course_id, $lesson_id, $progress) {
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_progress';
        
        // Check if record exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d AND course_id = %d AND lesson_id = %d",
            $user_id,
            $course_id,
            $lesson_id
        ));
        
        if ($existing) {
            // Update existing record
            $result = $wpdb->update(
                $table,
                array(
                    'progress' => $progress,
                    'status' => $progress >= 100 ? 'completed' : 'in_progress',
                    'completed_at' => $progress >= 100 ? current_time('mysql') : null
                ),
                array(
                    'user_id' => $user_id,
                    'course_id' => $course_id,
                    'lesson_id' => $lesson_id
                )
            );
        } else {
            // Insert new record
            $result = $wpdb->insert(
                $table,
                array(
                    'user_id' => $user_id,
                    'course_id' => $course_id,
                    'lesson_id' => $lesson_id,
                    'status' => $progress >= 100 ? 'completed' : 'in_progress',
                    'progress' => $progress,
                    'started_at' => current_time('mysql'),
                    'completed_at' => $progress >= 100 ? current_time('mysql') : null
                )
            );
        }
        
        // Trigger action
        do_action('lectus_progress_updated', $user_id, $course_id, $lesson_id, $progress);
        
        return $result !== false;
    }
    
    public static function complete_lesson($user_id, $course_id, $lesson_id) {
        return self::update_progress($user_id, $course_id, $lesson_id, 100);
    }
    
    public static function get_lesson_progress($user_id, $course_id, $lesson_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_progress';
        
        $progress = $wpdb->get_var($wpdb->prepare(
            "SELECT progress FROM $table WHERE user_id = %d AND course_id = %d AND lesson_id = %d",
            $user_id,
            $course_id,
            $lesson_id
        ));
        
        return $progress !== null ? intval($progress) : 0;
    }
    
    public static function is_lesson_completed($user_id, $course_id, $lesson_id) {
        return self::get_lesson_progress($user_id, $course_id, $lesson_id) >= 100;
    }
    
    public static function get_course_progress($user_id, $course_id) {
        // Get all lessons for the course
        $lessons = get_posts(array(
            'post_type' => 'lesson',
            'meta_key' => '_course_id',
            'meta_value' => $course_id,
            'posts_per_page' => -1
        ));
        
        if (empty($lessons)) {
            return 0;
        }
        
        $total_progress = 0;
        foreach ($lessons as $lesson) {
            $total_progress += self::get_lesson_progress($user_id, $course_id, $lesson->ID);
        }
        
        return round($total_progress / count($lessons));
    }
    
    public static function is_course_completed($user_id, $course_id) {
        $progress = self::get_course_progress($user_id, $course_id);
        $completion_score = get_post_meta($course_id, '_completion_score', true) ?: 80;
        
        return $progress >= $completion_score;
    }
    
    public static function get_completed_lessons($user_id, $course_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_progress';
        
        $completed = $wpdb->get_results($wpdb->prepare(
            "SELECT lesson_id FROM $table 
             WHERE user_id = %d AND course_id = %d AND status = 'completed'",
            $user_id,
            $course_id
        ));
        
        return wp_list_pluck($completed, 'lesson_id');
    }
    
    public static function reset_course_progress($user_id, $course_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_progress';
        
        $result = $wpdb->delete(
            $table,
            array(
                'user_id' => $user_id,
                'course_id' => $course_id
            )
        );
        
        // Trigger action
        do_action('lectus_progress_reset', $user_id, $course_id);
        
        return $result !== false;
    }
    
    public static function reset_lesson_progress($user_id, $course_id, $lesson_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_progress';
        
        $result = $wpdb->delete(
            $table,
            array(
                'user_id' => $user_id,
                'course_id' => $course_id,
                'lesson_id' => $lesson_id
            )
        );
        
        return $result !== false;
    }
    
    public static function get_course_stats($course_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_progress';
        
        $stats = array(
            'total_students' => 0,
            'completed_students' => 0,
            'average_progress' => 0,
            'completion_rate' => 0
        );
        
        // Get enrolled students
        $enrollment_table = $wpdb->prefix . 'lectus_enrollment';
        $enrollments = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT user_id FROM $enrollment_table WHERE course_id = %d AND status = 'active'",
            $course_id
        ));
        
        $stats['total_students'] = count($enrollments);
        
        if ($stats['total_students'] > 0) {
            $total_progress = 0;
            foreach ($enrollments as $enrollment) {
                $progress = self::get_course_progress($enrollment->user_id, $course_id);
                $total_progress += $progress;
                
                if (self::is_course_completed($enrollment->user_id, $course_id)) {
                    $stats['completed_students']++;
                }
            }
            
            $stats['average_progress'] = round($total_progress / $stats['total_students']);
            $stats['completion_rate'] = round(($stats['completed_students'] / $stats['total_students']) * 100);
        }
        
        return $stats;
    }
    
    public static function get_lesson_stats($lesson_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_progress';
        
        $course_id = get_post_meta($lesson_id, '_course_id', true);
        
        $stats = array(
            'views' => 0,
            'completions' => 0,
            'average_progress' => 0,
            'completion_rate' => 0
        );
        
        // Get all progress records for this lesson
        $progress_records = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE lesson_id = %d",
            $lesson_id
        ));
        
        $stats['views'] = count($progress_records);
        
        if ($stats['views'] > 0) {
            $total_progress = 0;
            foreach ($progress_records as $record) {
                $total_progress += $record->progress;
                if ($record->status === 'completed') {
                    $stats['completions']++;
                }
            }
            
            $stats['average_progress'] = round($total_progress / $stats['views']);
            $stats['completion_rate'] = round(($stats['completions'] / $stats['views']) * 100);
        }
        
        return $stats;
    }
    
    public static function daily_progress_check() {
        // Check for stale enrollments
        global $wpdb;
        $enrollment_table = $wpdb->prefix . 'lectus_enrollment';
        
        // Get enrollments that are about to expire
        $expiring_soon = $wpdb->get_results(
            "SELECT * FROM $enrollment_table 
             WHERE status = 'active' 
             AND expires_at IS NOT NULL 
             AND expires_at <= DATE_ADD(NOW(), INTERVAL 7 DAY)"
        );
        
        foreach ($expiring_soon as $enrollment) {
            // Send reminder email
            do_action('lectus_enrollment_expiring_soon', $enrollment->user_id, $enrollment->course_id);
        }
        
        // Mark expired enrollments
        $wpdb->query(
            "UPDATE $enrollment_table 
             SET status = 'expired' 
             WHERE status = 'active' 
             AND expires_at IS NOT NULL 
             AND expires_at <= NOW()"
        );
    }
}