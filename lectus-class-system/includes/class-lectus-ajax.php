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
        
        // Student management AJAX handlers
        add_action('wp_ajax_lectus_extend_access', array(__CLASS__, 'extend_access'));
        add_action('wp_ajax_lectus_change_status', array(__CLASS__, 'change_status'));
        add_action('wp_ajax_lectus_export_students', array(__CLASS__, 'export_students'));
        
        // Settings AJAX handlers
        add_action('wp_ajax_lectus_generate_test_data', array(__CLASS__, 'generate_test_data'));
        add_action('wp_ajax_lectus_clear_logs', array(__CLASS__, 'clear_logs'));
        add_action('wp_ajax_lectus_optimize_tables', array(__CLASS__, 'optimize_tables'));
        add_action('wp_ajax_lectus_create_test_pages', array(__CLASS__, 'create_test_pages'));
        add_action('wp_ajax_lectus_create_wishlist_table', array(__CLASS__, 'create_wishlist_table'));
        
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
    
    /**
     * Generate test data
     */
    public static function generate_test_data() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-test-data')) {
            wp_send_json_error(array('message' => __('보안 검증 실패', 'lectus-class-system')));
        }
        
        // Check permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('권한이 없습니다.', 'lectus-class-system')));
        }
        
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
        $created = 0;
        
        switch ($type) {
            case 'package':
                // Create test package courses
                for ($i = 1; $i <= 5; $i++) {
                    $post_id = wp_insert_post(array(
                        'post_title' => sprintf(__('테스트 패키지 강의 %d', 'lectus-class-system'), $i),
                        'post_content' => __('이것은 테스트 패키지 강의입니다.', 'lectus-class-system'),
                        'post_type' => 'coursepackage',
                        'post_status' => 'publish'
                    ));
                    if ($post_id) $created++;
                }
                break;
                
            case 'single':
                // Create test single courses
                for ($i = 1; $i <= 10; $i++) {
                    $post_id = wp_insert_post(array(
                        'post_title' => sprintf(__('테스트 단과 강의 %d', 'lectus-class-system'), $i),
                        'post_content' => __('이것은 테스트 단과 강의입니다.', 'lectus-class-system'),
                        'post_type' => 'coursesingle',
                        'post_status' => 'publish'
                    ));
                    if ($post_id) {
                        update_post_meta($post_id, '_course_duration', rand(30, 180));
                        update_post_meta($post_id, '_course_level', array_rand(array('beginner', 'intermediate', 'advanced')));
                        $created++;
                    }
                }
                break;
                
            case 'lessons':
                // Create test lessons
                $courses = get_posts(array(
                    'post_type' => 'coursesingle',
                    'posts_per_page' => 5,
                    'post_status' => 'publish'
                ));
                
                foreach ($courses as $course) {
                    for ($i = 1; $i <= 10; $i++) {
                        $post_id = wp_insert_post(array(
                            'post_title' => sprintf(__('레슨 %d - %s', 'lectus-class-system'), $i, $course->post_title),
                            'post_content' => __('이것은 테스트 레슨입니다.', 'lectus-class-system'),
                            'post_type' => 'lesson',
                            'post_status' => 'publish',
                            'menu_order' => $i
                        ));
                        if ($post_id) {
                            update_post_meta($post_id, '_course_id', $course->ID);
                            update_post_meta($post_id, '_lesson_duration', rand(10, 60));
                            $created++;
                        }
                    }
                }
                break;
                
            case 'students':
                // Create test students
                for ($i = 1; $i <= 20; $i++) {
                    $username = 'test_student_' . $i;
                    $email = $username . '@example.com';
                    
                    if (!username_exists($username)) {
                        $user_id = wp_create_user($username, wp_generate_password(), $email);
                        if ($user_id) {
                            $user = new WP_User($user_id);
                            $user->add_role('student');
                            $created++;
                        }
                    }
                }
                break;
        }
        
        if ($created > 0) {
            wp_send_json_success(array(
                'message' => sprintf(__('%d개의 테스트 데이터가 생성되었습니다.', 'lectus-class-system'), $created)
            ));
        } else {
            wp_send_json_error(array('message' => __('테스트 데이터 생성에 실패했습니다.', 'lectus-class-system')));
        }
    }
    
    /**
     * Clear logs
     */
    public static function clear_logs() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-clear-logs')) {
            wp_send_json_error(array('message' => __('보안 검증 실패', 'lectus-class-system')));
        }
        
        // Check permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('권한이 없습니다.', 'lectus-class-system')));
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_logs';
        $result = $wpdb->query("TRUNCATE TABLE $table");
        
        if ($result !== false) {
            wp_send_json_success(array('message' => __('로그가 삭제되었습니다.', 'lectus-class-system')));
        } else {
            wp_send_json_error(array('message' => __('로그 삭제에 실패했습니다.', 'lectus-class-system')));
        }
    }
    
    /**
     * Optimize database tables
     */
    public static function optimize_tables() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-optimize')) {
            wp_send_json_error(array('message' => __('보안 검증 실패', 'lectus-class-system')));
        }
        
        // Check permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('권한이 없습니다.', 'lectus-class-system')));
        }
        
        global $wpdb;
        $tables = array(
            'lectus_progress',
            'lectus_enrollment',
            'lectus_certificates',
            'lectus_qa_questions',
            'lectus_qa_answers',
            'lectus_materials',
            'lectus_logs',
            'lectus_rate_limits'
        );
        
        $optimized = 0;
        foreach ($tables as $table) {
            $full_table = $wpdb->prefix . $table;
            if ($wpdb->get_var("SHOW TABLES LIKE '$full_table'") == $full_table) {
                $result = $wpdb->query("OPTIMIZE TABLE $full_table");
                if ($result !== false) {
                    $optimized++;
                }
            }
        }
        
        wp_send_json_success(array(
            'message' => sprintf(__('%d개의 테이블이 최적화되었습니다.', 'lectus-class-system'), $optimized)
        ));
    }
    
    /**
     * Create test pages
     */
    public static function create_test_pages() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-test-pages')) {
            wp_send_json_error(array('message' => __('보안 검증 실패', 'lectus-class-system')));
        }
        
        // Check permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('권한이 없습니다.', 'lectus-class-system')));
        }
        
        // Include the test pages file
        if (file_exists(LECTUS_PLUGIN_DIR . 'templates/test-pages.php')) {
            require_once LECTUS_PLUGIN_DIR . 'templates/test-pages.php';
            
            // Call the function to create pages
            $created_pages = lectus_create_test_pages();
            
            if (!empty($created_pages)) {
                $message = __('테스트 페이지가 성공적으로 생성되었습니다:', 'lectus-class-system') . '<br>';
                foreach ($created_pages as $page) {
                    $message .= sprintf('✓ <a href="%s" target="_blank">%s</a><br>', 
                        esc_url($page['url']), 
                        esc_html($page['title'])
                    );
                }
                wp_send_json_success(array('message' => $message));
            } else {
                wp_send_json_success(array('message' => __('모든 테스트 페이지가 이미 존재합니다.', 'lectus-class-system')));
            }
        } else {
            wp_send_json_error(array('message' => __('테스트 페이지 파일을 찾을 수 없습니다.', 'lectus-class-system')));
        }
    }
    
    /**
     * Extend student access period
     */
    public static function extend_access() {
        // Verify nonce and admin capabilities
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-ajax-nonce')) {
            wp_send_json_error(array('message' => __('보안 검증 실패', 'lectus-class-system')), 403);
            return;
        }
        
        if (!current_user_can('manage_students')) {
            wp_send_json_error(array('message' => __('권한이 없습니다.', 'lectus-class-system')), 403);
            return;
        }
        
        $user_id = isset($_POST['user_id']) ? absint($_POST['user_id']) : 0;
        $course_id = isset($_POST['course_id']) ? absint($_POST['course_id']) : 0;
        $days = isset($_POST['days']) ? absint($_POST['days']) : 0;
        
        if (!$user_id || !$course_id || !$days) {
            wp_send_json_error(array('message' => __('필수 정보가 누락되었습니다.', 'lectus-class-system')), 400);
            return;
        }
        
        global $wpdb;
        $enrollment_table = $wpdb->prefix . 'lectus_enrollment';
        
        // Get current enrollment
        $enrollment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $enrollment_table WHERE user_id = %d AND course_id = %d",
            $user_id, $course_id
        ));
        
        if (!$enrollment) {
            wp_send_json_error(array('message' => __('등록 정보를 찾을 수 없습니다.', 'lectus-class-system')), 404);
            return;
        }
        
        // Calculate new expiry date
        $current_expiry = $enrollment->expires_at ? strtotime($enrollment->expires_at) : time();
        $new_expiry = date('Y-m-d H:i:s', $current_expiry + ($days * DAY_IN_SECONDS));
        
        // Update expiry date
        $result = $wpdb->update(
            $enrollment_table,
            array('expires_at' => $new_expiry),
            array('user_id' => $user_id, 'course_id' => $course_id),
            array('%s'),
            array('%d', '%d')
        );
        
        if ($result !== false) {
            wp_send_json_success(array(
                'message' => sprintf(__('수강 기간이 %d일 연장되었습니다.', 'lectus-class-system'), $days),
                'new_expiry' => date_i18n(get_option('date_format'), strtotime($new_expiry))
            ));
        } else {
            wp_send_json_error(array('message' => __('수강 기간 연장에 실패했습니다.', 'lectus-class-system')));
        }
    }
    
    /**
     * Change student enrollment status
     */
    public static function change_status() {
        // Verify nonce and admin capabilities
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-ajax-nonce')) {
            wp_send_json_error(array('message' => __('보안 검증 실패', 'lectus-class-system')), 403);
            return;
        }
        
        if (!current_user_can('manage_students')) {
            wp_send_json_error(array('message' => __('권한이 없습니다.', 'lectus-class-system')), 403);
            return;
        }
        
        $user_id = isset($_POST['user_id']) ? absint($_POST['user_id']) : 0;
        $course_id = isset($_POST['course_id']) ? absint($_POST['course_id']) : 0;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        
        if (!$user_id || !$course_id || !$status) {
            wp_send_json_error(array('message' => __('필수 정보가 누락되었습니다.', 'lectus-class-system')), 400);
            return;
        }
        
        // Validate status
        $valid_statuses = array('active', 'paused', 'expired', 'cancelled');
        if (!in_array($status, $valid_statuses)) {
            wp_send_json_error(array('message' => __('유효하지 않은 상태입니다.', 'lectus-class-system')), 400);
            return;
        }
        
        global $wpdb;
        $enrollment_table = $wpdb->prefix . 'lectus_enrollment';
        
        // Update status
        $result = $wpdb->update(
            $enrollment_table,
            array('status' => $status),
            array('user_id' => $user_id, 'course_id' => $course_id),
            array('%s'),
            array('%d', '%d')
        );
        
        if ($result !== false) {
            $status_label = Lectus_Enrollment::get_status_label($status);
            wp_send_json_success(array(
                'message' => sprintf(__('상태가 %s(으)로 변경되었습니다.', 'lectus-class-system'), $status_label),
                'status' => $status,
                'status_label' => $status_label
            ));
        } else {
            wp_send_json_error(array('message' => __('상태 변경에 실패했습니다.', 'lectus-class-system')));
        }
    }
    
    /**
     * Export students data to CSV
     */
    public static function export_students() {
        // Verify nonce and admin capabilities
        if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'lectus-ajax-nonce')) {
            wp_die(__('보안 검증 실패', 'lectus-class-system'));
        }
        
        if (!current_user_can('manage_students')) {
            wp_die(__('권한이 없습니다.', 'lectus-class-system'));
        }
        
        global $wpdb;
        $enrollment_table = $wpdb->prefix . 'lectus_enrollment';
        
        // Get filter parameters
        $course_filter = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        
        // Build query
        $query = "SELECT e.*, u.display_name, u.user_email 
                 FROM $enrollment_table e
                 LEFT JOIN {$wpdb->users} u ON e.user_id = u.ID
                 WHERE 1=1";
        
        if ($course_filter) {
            $query .= $wpdb->prepare(" AND e.course_id = %d", $course_filter);
        }
        
        if ($status_filter) {
            $query .= $wpdb->prepare(" AND e.status = %s", $status_filter);
        }
        
        $query .= " ORDER BY e.enrolled_at DESC";
        
        $enrollments = $wpdb->get_results($query);
        
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="students-' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Add BOM for Excel UTF-8 compatibility
        echo "\xEF\xBB\xBF";
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Write CSV headers
        fputcsv($output, array(
            __('수강생 이름', 'lectus-class-system'),
            __('이메일', 'lectus-class-system'),
            __('강의명', 'lectus-class-system'),
            __('진도율', 'lectus-class-system'),
            __('상태', 'lectus-class-system'),
            __('등록일', 'lectus-class-system'),
            __('만료일', 'lectus-class-system')
        ));
        
        // Write data rows
        foreach ($enrollments as $enrollment) {
            $course = get_post($enrollment->course_id);
            if (!$course) continue;
            
            $progress = Lectus_Progress::get_course_progress($enrollment->user_id, $enrollment->course_id);
            
            fputcsv($output, array(
                $enrollment->display_name,
                $enrollment->user_email,
                $course->post_title,
                $progress . '%',
                Lectus_Enrollment::get_status_label($enrollment->status),
                date_i18n(get_option('date_format'), strtotime($enrollment->enrolled_at)),
                $enrollment->expires_at ? date_i18n(get_option('date_format'), strtotime($enrollment->expires_at)) : __('무제한', 'lectus-class-system')
            ));
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Create wishlist table
     */
    public static function create_wishlist_table() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-create-table')) {
            wp_send_json_error(array('message' => __('보안 검증 실패', 'lectus-class-system')));
        }
        
        // Check permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('권한이 없습니다.', 'lectus-class-system')));
        }
        
        // Load the wishlist class if not already loaded
        if (!class_exists('Lectus_Wishlist')) {
            require_once LECTUS_PLUGIN_DIR . 'includes/class-lectus-wishlist.php';
        }
        
        // Create the table
        $result = Lectus_Wishlist::create_table();
        
        if ($result) {
            wp_send_json_success(array('message' => __('위시리스트 테이블이 성공적으로 생성되었습니다.', 'lectus-class-system')));
        } else {
            wp_send_json_error(array('message' => __('위시리스트 테이블 생성에 실패했습니다.', 'lectus-class-system')));
        }
    }
}