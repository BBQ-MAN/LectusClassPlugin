<?php
/**
 * Enrollment Management for Lectus Class System
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Enrollment {
    
    public static function init() {
        // Enrollment actions
        add_action('init', array(__CLASS__, 'handle_free_enrollment'));
        add_action('wp_ajax_lectus_free_enroll', array(__CLASS__, 'ajax_free_enroll'));
        add_action('wp_ajax_nopriv_lectus_free_enroll', array(__CLASS__, 'ajax_free_enroll'));
    }
    
    public static function enroll($user_id, $course_id, $order_id = 0, $duration = 0) {
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_enrollment';
        
        // Check if already enrolled
        if (self::is_enrolled($user_id, $course_id)) {
            return false;
        }
        
        // Calculate expiration date
        $expires_at = null;
        if ($duration > 0) {
            $expires_at = date('Y-m-d H:i:s', strtotime("+{$duration} days"));
        }
        
        // Insert enrollment record
        $result = $wpdb->insert(
            $table,
            array(
                'user_id' => $user_id,
                'course_id' => $course_id,
                'order_id' => $order_id,
                'status' => 'active',
                'enrolled_at' => current_time('mysql'),
                'expires_at' => $expires_at
            )
        );
        
        if ($result) {
            // Trigger enrollment action
            do_action('lectus_student_enrolled', $user_id, $course_id, $order_id);
            
            // Send enrollment email
            self::send_enrollment_email($user_id, $course_id);
            
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    public static function unenroll($user_id, $course_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_enrollment';
        
        $result = $wpdb->update(
            $table,
            array('status' => 'cancelled'),
            array(
                'user_id' => $user_id,
                'course_id' => $course_id
            )
        );
        
        if ($result) {
            // Reset progress
            Lectus_Progress::reset_course_progress($user_id, $course_id);
            
            // Trigger unenrollment action
            do_action('lectus_student_unenrolled', $user_id, $course_id);
            
            return true;
        }
        
        return false;
    }
    
    public static function is_enrolled($user_id, $course_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_enrollment';
        
        $enrollment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table 
             WHERE user_id = %d AND course_id = %d AND status = 'active'
             AND (expires_at IS NULL OR expires_at > NOW())",
            $user_id,
            $course_id
        ));
        
        return $enrollment !== null;
    }
    
    public static function get_enrollment($user_id, $course_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_enrollment';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d AND course_id = %d",
            $user_id,
            $course_id
        ));
    }
    
    public static function get_user_enrollments($user_id, $status = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_enrollment';
        
        $query = "SELECT * FROM $table WHERE user_id = %d";
        $params = array($user_id);
        
        if ($status) {
            $query .= " AND status = %s";
            $params[] = $status;
        }
        
        $query .= " ORDER BY enrolled_at DESC";
        
        return $wpdb->get_results($wpdb->prepare($query, $params));
    }
    
    public static function get_course_enrollments($course_id, $status = 'active') {
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_enrollment';
        
        $query = "SELECT * FROM $table WHERE course_id = %d";
        $params = array($course_id);
        
        if ($status) {
            $query .= " AND status = %s";
            $params[] = $status;
        }
        
        $query .= " ORDER BY enrolled_at DESC";
        
        return $wpdb->get_results($wpdb->prepare($query, $params));
    }
    
    public static function extend_enrollment($user_id, $course_id, $days) {
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_enrollment';
        
        $enrollment = self::get_enrollment($user_id, $course_id);
        if (!$enrollment) {
            return false;
        }
        
        // Calculate new expiration date
        if ($enrollment->expires_at) {
            $current_expiry = strtotime($enrollment->expires_at);
            $new_expiry = date('Y-m-d H:i:s', strtotime("+{$days} days", $current_expiry));
        } else {
            $new_expiry = date('Y-m-d H:i:s', strtotime("+{$days} days"));
        }
        
        $result = $wpdb->update(
            $table,
            array('expires_at' => $new_expiry),
            array('id' => $enrollment->id)
        );
        
        if ($result) {
            // Trigger extension action
            do_action('lectus_enrollment_extended', $user_id, $course_id, $days);
            return true;
        }
        
        return false;
    }
    
    public static function pause_enrollment($user_id, $course_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_enrollment';
        
        $result = $wpdb->update(
            $table,
            array('status' => 'paused'),
            array(
                'user_id' => $user_id,
                'course_id' => $course_id,
                'status' => 'active'
            )
        );
        
        return $result !== false;
    }
    
    public static function resume_enrollment($user_id, $course_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_enrollment';
        
        $result = $wpdb->update(
            $table,
            array('status' => 'active'),
            array(
                'user_id' => $user_id,
                'course_id' => $course_id,
                'status' => 'paused'
            )
        );
        
        return $result !== false;
    }
    
    public static function get_status_label($status) {
        $labels = array(
            'active' => __('활성', 'lectus-class-system'),
            'paused' => __('일시정지', 'lectus-class-system'),
            'expired' => __('만료', 'lectus-class-system'),
            'cancelled' => __('취소', 'lectus-class-system'),
            'completed' => __('완료', 'lectus-class-system')
        );
        
        return isset($labels[$status]) ? $labels[$status] : $status;
    }
    
    public static function handle_free_enrollment() {
        if (!isset($_POST['lectus_free_enroll_nonce']) || 
            !wp_verify_nonce($_POST['lectus_free_enroll_nonce'], 'lectus_free_enroll')) {
            return;
        }
        
        if (!is_user_logged_in()) {
            return;
        }
        
        $user_id = get_current_user_id();
        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        
        if (!$course_id) {
            return;
        }
        
        // Check if course is free
        $product_id = get_post_meta($course_id, '_wc_product_id', true);
        if ($product_id) {
            // Course has a product, not free
            return;
        }
        
        // Enroll user
        $result = self::enroll($user_id, $course_id);
        
        if ($result) {
            wp_redirect(get_permalink($course_id));
            exit;
        }
    }
    
    public static function ajax_free_enroll() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-ajax-nonce')) {
            wp_die('Security check failed');
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('로그인이 필요합니다.', 'lectus-class-system')));
        }
        
        $user_id = get_current_user_id();
        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        
        if (!$course_id) {
            wp_send_json_error(array('message' => __('잘못된 요청입니다.', 'lectus-class-system')));
        }
        
        // Check if course is free
        $product_id = get_post_meta($course_id, '_wc_product_id', true);
        if ($product_id) {
            wp_send_json_error(array('message' => __('이 강의는 유료 강의입니다.', 'lectus-class-system')));
        }
        
        // Check if already enrolled
        if (self::is_enrolled($user_id, $course_id)) {
            wp_send_json_error(array('message' => __('이미 등록된 강의입니다.', 'lectus-class-system')));
        }
        
        // Get default duration
        $duration = get_post_meta($course_id, '_course_duration', true);
        
        // Enroll user
        $result = self::enroll($user_id, $course_id, 0, $duration);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => __('강의에 등록되었습니다.', 'lectus-class-system'),
                'redirect' => get_permalink($course_id)
            ));
        } else {
            wp_send_json_error(array('message' => __('등록에 실패했습니다.', 'lectus-class-system')));
        }
    }
    
    private static function send_enrollment_email($user_id, $course_id) {
        $enable_emails = get_option('lectus_enable_email_notifications', 'yes');
        if ($enable_emails !== 'yes') {
            return;
        }
        
        $user = get_user_by('id', $user_id);
        $course = get_post($course_id);
        
        if (!$user || !$course) {
            return;
        }
        
        $subject = get_option('lectus_enrollment_email_subject', __('수강 등록이 완료되었습니다', 'lectus-class-system'));
        $subject = str_replace('{course_title}', $course->post_title, $subject);
        
        $message = sprintf(
            __("안녕하세요 %s님,\n\n'%s' 강의에 성공적으로 등록되었습니다.\n\n지금 바로 학습을 시작하세요: %s\n\n감사합니다.", 'lectus-class-system'),
            $user->display_name,
            $course->post_title,
            get_permalink($course_id)
        );
        
        wp_mail($user->user_email, $subject, $message);
    }
}