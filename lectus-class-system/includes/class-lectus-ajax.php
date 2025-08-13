<?php
/**
 * AJAX Handlers for Lectus Class System
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Ajax {
    
    public static function init() {
        // Admin AJAX handlers
        add_action('wp_ajax_lectus_update_lesson_progress', array(__CLASS__, 'update_lesson_progress'));
        add_action('wp_ajax_lectus_complete_lesson', array(__CLASS__, 'complete_lesson'));
        add_action('wp_ajax_lectus_enroll_student', array(__CLASS__, 'enroll_student'));
        add_action('wp_ajax_lectus_unenroll_student', array(__CLASS__, 'unenroll_student'));
        add_action('wp_ajax_lectus_reset_progress', array(__CLASS__, 'reset_progress'));
        add_action('wp_ajax_lectus_generate_certificate', array(__CLASS__, 'generate_certificate'));
        add_action('wp_ajax_lectus_bulk_upload_lessons', array(__CLASS__, 'bulk_upload_lessons'));
        
        // Frontend AJAX handlers
        add_action('wp_ajax_nopriv_lectus_update_lesson_progress', array(__CLASS__, 'update_lesson_progress'));
        add_action('wp_ajax_nopriv_lectus_complete_lesson', array(__CLASS__, 'complete_lesson'));
    }
    
    public static function update_lesson_progress() {
        // Verify nonce and request method
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-ajax-nonce')) {
            wp_send_json_error(array('message' => __('보안 검증 실패', 'lectus-class-system')), 403);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_send_json_error(array('message' => __('잘못된 요청 방식', 'lectus-class-system')), 405);
            return;
        }
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(array('message' => __('로그인이 필요합니다.', 'lectus-class-system')), 401);
            return;
        }
        
        // Enhanced input validation
        $lesson_id = isset($_POST['lesson_id']) ? absint($_POST['lesson_id']) : 0;
        $progress = isset($_POST['progress']) ? max(0, min(100, absint($_POST['progress']))) : 0;
        
        if (!$lesson_id || !get_post($lesson_id) || get_post_type($lesson_id) !== 'lesson') {
            wp_send_json_error(array('message' => __('유효하지 않은 레슨입니다.', 'lectus-class-system')), 400);
            return;
        }
        
        $course_id = get_post_meta($lesson_id, '_course_id', true);
        
        if (!$course_id || !get_post($course_id)) {
            wp_send_json_error(array('message' => __('연결된 강의가 없습니다.', 'lectus-class-system')), 400);
            return;
        }
        
        // Check enrollment with proper error handling
        try {
            if (!Lectus_Enrollment::is_enrolled($user_id, $course_id)) {
                wp_send_json_error(array('message' => __('이 강의에 등록되지 않았습니다.', 'lectus-class-system')), 403);
                return;
            }
        } catch (Exception $e) {
            Lectus_Logger::error('Enrollment check failed: ' . $e->getMessage(), 'enrollment_check', array(
                'user_id' => $user_id,
                'course_id' => $course_id,
                'lesson_id' => $lesson_id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ));
            wp_send_json_error(array('message' => __('등록 상태 확인 중 오류가 발생했습니다.', 'lectus-class-system')), 500);
            return;
        }
        
        // Update progress
        $result = Lectus_Progress::update_progress($user_id, $course_id, $lesson_id, $progress);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => __('진도가 업데이트되었습니다.', 'lectus-class-system'),
                'progress' => $progress
            ));
        } else {
            wp_send_json_error(array('message' => __('진도 업데이트에 실패했습니다.', 'lectus-class-system')));
        }
    }
    
    public static function complete_lesson() {
        // Verify nonce and request method
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-ajax-nonce')) {
            wp_send_json_error(array('message' => __('보안 검증 실패', 'lectus-class-system')), 403);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_send_json_error(array('message' => __('잘못된 요청 방식', 'lectus-class-system')), 405);
            return;
        }
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(array('message' => __('로그인이 필요합니다.', 'lectus-class-system')), 401);
            return;
        }
        
        // Enhanced input validation
        $lesson_id = isset($_POST['lesson_id']) ? absint($_POST['lesson_id']) : 0;
        
        if (!$lesson_id || !get_post($lesson_id) || get_post_type($lesson_id) !== 'lesson') {
            wp_send_json_error(array('message' => __('유효하지 않은 레슨입니다.', 'lectus-class-system')), 400);
            return;
        }
        
        $course_id = get_post_meta($lesson_id, '_course_id', true);
        
        if (!$course_id || !get_post($course_id)) {
            wp_send_json_error(array('message' => __('연결된 강의가 없습니다.', 'lectus-class-system')), 400);
            return;
        }
        
        // Check enrollment with proper error handling
        try {
            if (!Lectus_Enrollment::is_enrolled($user_id, $course_id)) {
                wp_send_json_error(array('message' => __('이 강의에 등록되지 않았습니다.', 'lectus-class-system')), 403);
                return;
            }
        } catch (Exception $e) {
            Lectus_Logger::error('Enrollment check failed in complete_lesson: ' . $e->getMessage(), 'enrollment_check', array(
                'user_id' => $user_id,
                'course_id' => $course_id,
                'lesson_id' => $lesson_id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ));
            wp_send_json_error(array('message' => __('등록 상태 확인 중 오류가 발생했습니다.', 'lectus-class-system')), 500);
            return;
        }
        
        // Complete lesson
        $result = Lectus_Progress::complete_lesson($user_id, $course_id, $lesson_id);
        
        if ($result) {
            // Check if course is completed
            $course_completed = Lectus_Progress::is_course_completed($user_id, $course_id);
            
            $response = array(
                'message' => __('레슨을 완료했습니다.', 'lectus-class-system'),
                'lesson_completed' => true,
                'course_completed' => $course_completed
            );
            
            if ($course_completed) {
                // Generate certificate if enabled
                $certificate_enabled = get_post_meta($course_id, '_certificate_enabled', true);
                if ($certificate_enabled) {
                    $certificate_id = Lectus_Certificate::generate($user_id, $course_id);
                    if ($certificate_id) {
                        $response['certificate_generated'] = true;
                        $response['certificate_url'] = Lectus_Certificate::get_certificate_url($certificate_id);
                    }
                }
                
                // Send completion email
                do_action('lectus_course_completed', $user_id, $course_id);
            }
            
            wp_send_json_success($response);
        } else {
            wp_send_json_error(array('message' => __('레슨 완료 처리에 실패했습니다.', 'lectus-class-system')));
        }
    }
    
    public static function enroll_student() {
        // Verify nonce and admin capabilities
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-ajax-nonce')) {
            wp_send_json_error(array('message' => __('보안 검증 실패', 'lectus-class-system')), 403);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_send_json_error(array('message' => __('잘못된 요청 방식', 'lectus-class-system')), 405);
            return;
        }
        
        if (!current_user_can('manage_students')) {
            wp_send_json_error(array('message' => __('권한이 없습니다.', 'lectus-class-system')), 403);
            return;
        }
        
        // Enhanced input validation
        $user_id = isset($_POST['user_id']) ? absint($_POST['user_id']) : 0;
        $course_id = isset($_POST['course_id']) ? absint($_POST['course_id']) : 0;
        $duration = isset($_POST['duration']) ? absint($_POST['duration']) : 0;
        
        if (!$user_id || !get_user_by('id', $user_id)) {
            wp_send_json_error(array('message' => __('유효하지 않은 사용자입니다.', 'lectus-class-system')), 400);
            return;
        }
        
        if (!$course_id || !get_post($course_id)) {
            wp_send_json_error(array('message' => __('유효하지 않은 강의입니다.', 'lectus-class-system')), 400);
            return;
        }
        
        // Enroll student
        $result = Lectus_Enrollment::enroll($user_id, $course_id, 0, $duration);
        
        if ($result) {
            wp_send_json_success(array('message' => __('수강생이 등록되었습니다.', 'lectus-class-system')));
        } else {
            wp_send_json_error(array('message' => __('등록에 실패했습니다.', 'lectus-class-system')));
        }
    }
    
    public static function unenroll_student() {
        // Verify nonce and admin capabilities
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-ajax-nonce')) {
            wp_send_json_error(array('message' => __('보안 검증 실패', 'lectus-class-system')), 403);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_send_json_error(array('message' => __('잘못된 요청 방식', 'lectus-class-system')), 405);
            return;
        }
        
        if (!current_user_can('manage_students')) {
            wp_send_json_error(array('message' => __('권한이 없습니다.', 'lectus-class-system')), 403);
            return;
        }
        
        // Enhanced input validation
        $user_id = isset($_POST['user_id']) ? absint($_POST['user_id']) : 0;
        $course_id = isset($_POST['course_id']) ? absint($_POST['course_id']) : 0;
        
        if (!$user_id || !get_user_by('id', $user_id)) {
            wp_send_json_error(array('message' => __('유효하지 않은 사용자입니다.', 'lectus-class-system')), 400);
            return;
        }
        
        if (!$course_id || !get_post($course_id)) {
            wp_send_json_error(array('message' => __('유효하지 않은 강의입니다.', 'lectus-class-system')), 400);
            return;
        }
        
        // Unenroll student
        $result = Lectus_Enrollment::unenroll($user_id, $course_id);
        
        if ($result) {
            wp_send_json_success(array('message' => __('수강생 등록이 취소되었습니다.', 'lectus-class-system')));
        } else {
            wp_send_json_error(array('message' => __('등록 취소에 실패했습니다.', 'lectus-class-system')));
        }
    }
    
    public static function reset_progress() {
        // Verify nonce and admin capabilities
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-ajax-nonce')) {
            wp_send_json_error(array('message' => __('보안 검증 실패', 'lectus-class-system')), 403);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_send_json_error(array('message' => __('잘못된 요청 방식', 'lectus-class-system')), 405);
            return;
        }
        
        if (!current_user_can('manage_students')) {
            wp_send_json_error(array('message' => __('권한이 없습니다.', 'lectus-class-system')), 403);
            return;
        }
        
        // Enhanced input validation
        $user_id = isset($_POST['user_id']) ? absint($_POST['user_id']) : 0;
        $course_id = isset($_POST['course_id']) ? absint($_POST['course_id']) : 0;
        
        if (!$user_id || !get_user_by('id', $user_id)) {
            wp_send_json_error(array('message' => __('유효하지 않은 사용자입니다.', 'lectus-class-system')), 400);
            return;
        }
        
        if (!$course_id || !get_post($course_id)) {
            wp_send_json_error(array('message' => __('유효하지 않은 강의입니다.', 'lectus-class-system')), 400);
            return;
        }
        
        // Reset progress
        $result = Lectus_Progress::reset_course_progress($user_id, $course_id);
        
        if ($result) {
            wp_send_json_success(array('message' => __('진도가 초기화되었습니다.', 'lectus-class-system')));
        } else {
            wp_send_json_error(array('message' => __('진도 초기화에 실패했습니다.', 'lectus-class-system')));
        }
    }
    
    public static function generate_certificate() {
        // Verify nonce and request method
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-ajax-nonce')) {
            wp_send_json_error(array('message' => __('보안 검증 실패', 'lectus-class-system')), 403);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_send_json_error(array('message' => __('잘못된 요청 방식', 'lectus-class-system')), 405);
            return;
        }
        
        $user_id = isset($_POST['user_id']) ? absint($_POST['user_id']) : get_current_user_id();
        $course_id = isset($_POST['course_id']) ? absint($_POST['course_id']) : 0;
        
        if (!$user_id || !get_user_by('id', $user_id)) {
            wp_send_json_error(array('message' => __('유효하지 않은 사용자입니다.', 'lectus-class-system')), 400);
            return;
        }
        
        if (!$course_id || !get_post($course_id)) {
            wp_send_json_error(array('message' => __('유효하지 않은 강의입니다.', 'lectus-class-system')), 400);
            return;
        }
        
        // Check if course is completed
        if (!Lectus_Progress::is_course_completed($user_id, $course_id)) {
            wp_send_json_error(array('message' => __('강의를 완료하지 않았습니다.', 'lectus-class-system')));
        }
        
        // Generate certificate
        $certificate_id = Lectus_Certificate::generate($user_id, $course_id);
        
        if ($certificate_id) {
            wp_send_json_success(array(
                'message' => __('수료증이 생성되었습니다.', 'lectus-class-system'),
                'certificate_url' => Lectus_Certificate::get_certificate_url($certificate_id)
            ));
        } else {
            wp_send_json_error(array('message' => __('수료증 생성에 실패했습니다.', 'lectus-class-system')));
        }
    }
    
    public static function bulk_upload_lessons() {
        // Verify nonce and admin capabilities
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-ajax-nonce')) {
            wp_send_json_error(array('message' => __('보안 검증 실패', 'lectus-class-system')), 403);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_send_json_error(array('message' => __('잘못된 요청 방식', 'lectus-class-system')), 405);
            return;
        }
        
        if (!current_user_can('publish_lessons')) {
            wp_send_json_error(array('message' => __('권한이 없습니다.', 'lectus-class-system')), 403);
            return;
        }
        
        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        $csv_data = isset($_POST['csv_data']) ? $_POST['csv_data'] : '';
        
        if (!$course_id || empty($csv_data)) {
            wp_send_json_error(array('message' => __('잘못된 요청입니다.', 'lectus-class-system')));
        }
        
        // Parse CSV data
        $lines = explode("\n", $csv_data);
        $created = 0;
        $errors = array();
        
        foreach ($lines as $index => $line) {
            if ($index === 0) continue; // Skip header
            
            $data = str_getcsv($line);
            if (count($data) < 4) continue;
            
            $lesson_title = sanitize_text_field($data[0]);
            $lesson_type = sanitize_text_field($data[1]);
            $lesson_duration = intval($data[2]);
            $lesson_content = wp_kses_post($data[3]);
            
            if (empty($lesson_title)) continue;
            
            // Create lesson
            $lesson_id = wp_insert_post(array(
                'post_title' => $lesson_title,
                'post_content' => $lesson_content,
                'post_type' => 'lesson',
                'post_status' => 'publish',
                'menu_order' => $index
            ));
            
            if ($lesson_id && !is_wp_error($lesson_id)) {
                update_post_meta($lesson_id, '_course_id', $course_id);
                update_post_meta($lesson_id, '_lesson_type', $lesson_type);
                update_post_meta($lesson_id, '_lesson_duration', $lesson_duration);
                update_post_meta($lesson_id, '_completion_criteria', 'view');
                $created++;
            } else {
                $errors[] = sprintf(__('레슨 "%s" 생성 실패', 'lectus-class-system'), $lesson_title);
            }
        }
        
        if ($created > 0) {
            $response = array(
                'message' => sprintf(__('%d개의 레슨이 생성되었습니다.', 'lectus-class-system'), $created),
                'created' => $created
            );
            
            if (!empty($errors)) {
                $response['errors'] = $errors;
            }
            
            wp_send_json_success($response);
        } else {
            wp_send_json_error(array('message' => __('레슨 생성에 실패했습니다.', 'lectus-class-system')));
        }
    }
}