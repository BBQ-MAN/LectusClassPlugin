<?php
/**
 * Q&A System for Lectus Class System
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_QA {
    
    public static function init() {
        // Ensure table exists
        self::maybe_create_table();
        
        // AJAX handlers for Q&A
        add_action('wp_ajax_lectus_submit_question', array(__CLASS__, 'ajax_submit_question'));
        add_action('wp_ajax_lectus_submit_answer', array(__CLASS__, 'ajax_submit_answer'));
        add_action('wp_ajax_lectus_vote_qa', array(__CLASS__, 'ajax_vote_qa'));
        
        // Frontend handlers
        add_action('wp_ajax_nopriv_lectus_submit_question', array(__CLASS__, 'ajax_submit_question'));
        
        // Admin menu integration
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'), 20);
        
        // Shortcode
        add_shortcode('lectus_qa', array(__CLASS__, 'qa_shortcode'));
    }
    
    /**
     * Check and create table if needed
     */
    private static function maybe_create_table() {
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_qa';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            self::create_table();
        }
    }
    
    /**
     * Create Q&A database table
     */
    public static function create_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'lectus_qa';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            parent_id bigint(20) DEFAULT 0,
            course_id bigint(20) NOT NULL,
            lesson_id bigint(20) DEFAULT NULL,
            user_id bigint(20) NOT NULL,
            type enum('question', 'answer') DEFAULT 'question',
            title varchar(255) DEFAULT NULL,
            content longtext NOT NULL,
            status enum('pending', 'approved', 'rejected') DEFAULT 'approved',
            votes int(11) DEFAULT 0,
            is_best_answer tinyint(1) DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY course_id (course_id),
            KEY lesson_id (lesson_id),
            KEY user_id (user_id),
            KEY parent_id (parent_id),
            KEY type (type),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Check rate limiting for user actions
     */
    private static function check_rate_limit($user_id, $action_type) {
        // Admins bypass rate limiting
        if (current_user_can('manage_options')) {
            return true;
        }
        
        $transient_key = 'lectus_qa_rate_limit_' . $user_id . '_' . $action_type;
        $attempts = get_transient($transient_key);
        
        // More generous limits: 30 questions or 50 answers per hour
        // For development/testing, you can increase these values
        $limit = ($action_type === 'question') ? 30 : 50;
        
        if ($attempts >= $limit) {
            return false;
        }
        
        // Increment counter - reduced time window to 10 minutes for testing
        set_transient($transient_key, ($attempts ? $attempts + 1 : 1), 10 * MINUTE_IN_SECONDS);
        return true;
    }
    
    /**
     * Check for duplicate content
     */
    private static function is_duplicate_content($user_id, $title, $content) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'lectus_qa';
        $similar_hash = md5($title . $content);
        
        // Check for exact duplicate within last 10 seconds (to prevent double submission)
        $recent_duplicate = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table 
             WHERE user_id = %d 
               AND MD5(CONCAT(COALESCE(title, ''), content)) = %s 
               AND created_at > DATE_SUB(NOW(), INTERVAL 10 SECOND)",
            $user_id,
            $similar_hash
        ));
        
        if ($recent_duplicate > 0) {
            return true;
        }
        
        // Check for similar content within last hour (spam prevention)
        $similar_content = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table 
             WHERE user_id = %d 
               AND title = %s 
               AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)",
            $user_id,
            $title
        ));
        
        return $similar_content > 0;
    }
    
    /**
     * Sanitize and validate Q&A content
     */
    private static function sanitize_qa_content($content) {
        // Remove potentially harmful content
        $content = wp_kses_post($content);
        
        // Remove excessive whitespace while preserving line breaks
        // Only remove multiple spaces, not all whitespace
        $content = preg_replace('/[^\S\r\n]+/', ' ', $content); // Multiple spaces to single space
        $content = preg_replace('/\n{3,}/', "\n\n", $content); // Limit consecutive line breaks to 2
        $content = trim($content);
        
        // Basic profanity filter (implement as needed)
        $content = self::filter_profanity($content);
        
        return $content;
    }
    
    /**
     * Basic profanity filter
     */
    private static function filter_profanity($content) {
        // Apply filters for content moderation
        $content = apply_filters('lectus_qa_content_filter', $content);
        
        return $content;
    }
    
    /**
     * Clear Q&A cache for course/lesson
     */
    private static function clear_qa_cache($course_id, $lesson_id = null) {
        // Clear all question cache entries for this course
        $cache_patterns = array(
            'lectus_qa_questions_' . $course_id . '_all_*',
            'lectus_qa_questions_' . $course_id . '_' . ($lesson_id ?: '*') . '_*'
        );
        
        // WordPress doesn't support wildcard cache deletion, so we'll use a group approach
        wp_cache_flush_group('lectus_qa');
        
        // Also clear object cache if using persistent cache
        if (function_exists('wp_cache_flush_runtime')) {
            wp_cache_flush_runtime();
        }
    }
    
    /**
     * Get cache statistics for debugging
     */
    public static function get_cache_stats() {
        if (!current_user_can('manage_options')) {
            return false;
        }
        
        return array(
            'cache_enabled' => wp_using_ext_object_cache(),
            'cache_hits' => wp_cache_get('cache_hits', 'lectus_qa') ?: 0,
            'cache_misses' => wp_cache_get('cache_misses', 'lectus_qa') ?: 0
        );
    }
    
    /**
     * Submit a new question with enhanced validation
     */
    public static function submit_question($course_id, $lesson_id, $user_id, $title, $content) {
        // Sanitize content
        $title = self::sanitize_qa_content($title);
        $content = self::sanitize_qa_content($content);
        
        // Additional validation
        if (empty($title) || empty($content)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Lectus Q&A: Empty title or content after sanitization');
            }
            return false;
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_qa';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Lectus Q&A: Table does not exist, creating it now');
            }
            self::create_table();
        }
        
        // Use prepared statement with proper data types
        $result = $wpdb->insert(
            $table,
            array(
                'course_id' => absint($course_id),
                'lesson_id' => $lesson_id ? absint($lesson_id) : null,
                'user_id' => absint($user_id),
                'type' => 'question',
                'title' => $title,
                'content' => $content,
                'status' => 'approved',
                'created_at' => current_time('mysql')
            ),
            array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            error_log('Lectus Q&A: Failed to insert question - ' . $wpdb->last_error);
            error_log('Lectus Q&A: Insert data - course_id: ' . $course_id . ', user_id: ' . $user_id . ', title: ' . $title);
            return false;
        }
        
        $question_id = $wpdb->insert_id;
        
        // Log successful submission only in debug mode
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf('Lectus Q&A: Question %d submitted successfully by user %d for course %d', $question_id, $user_id, $course_id));
        }
        
        // Clear related cache
        self::clear_qa_cache($course_id, $lesson_id);
        
        // Trigger action for notifications
        do_action('lectus_question_submitted', $question_id, $course_id, $user_id);
        
        return $question_id;
    }
    
    /**
     * Submit an answer to a question
     */
    public static function submit_answer($question_id, $user_id, $content) {
        global $wpdb;
        
        // Get question info
        $table = $wpdb->prefix . 'lectus_qa';
        $question = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d AND type = 'question'",
            $question_id
        ));
        
        if (!$question) {
            return false;
        }
        
        $result = $wpdb->insert(
            $table,
            array(
                'parent_id' => $question_id,
                'course_id' => $question->course_id,
                'lesson_id' => $question->lesson_id,
                'user_id' => $user_id,
                'type' => 'answer',
                'content' => $content,
                'status' => 'approved',
                'created_at' => current_time('mysql')
            ),
            array('%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s')
        );
        
        if ($result) {
            $answer_id = $wpdb->insert_id;
            
            // Clear answer cache for this question
            wp_cache_delete('lectus_qa_answers_' . $question_id, 'lectus_qa');
            
            // Clear questions cache as answer count changed
            self::clear_qa_cache($question->course_id, $question->lesson_id);
            
            // Trigger action for notifications
            do_action('lectus_answer_submitted', $answer_id, $question_id, $user_id);
            
            return $answer_id;
        }
        
        return false;
    }
    
    /**
     * Get questions for a course/lesson with optimized query and caching
     */
    public static function get_questions($course_id, $lesson_id = null, $limit = 20, $offset = 0) {
        global $wpdb;
        
        // Input validation
        $course_id = absint($course_id);
        $lesson_id = $lesson_id ? absint($lesson_id) : null;
        $limit = max(1, min(100, absint($limit))); // Limit between 1-100
        $offset = max(0, absint($offset));
        
        if (!$course_id) {
            return array();
        }
        
        // Check cache first
        $cache_key = 'lectus_qa_questions_' . $course_id . '_' . ($lesson_id ?: 'all') . '_' . $limit . '_' . $offset;
        $cached = wp_cache_get($cache_key, 'lectus_qa');
        
        if (false !== $cached) {
            return $cached;
        }
        
        $table = $wpdb->prefix . 'lectus_qa';
        
        // Optimized query with better indexing usage
        $query = "SELECT q.id, q.parent_id, q.course_id, q.lesson_id, q.user_id, 
                         q.title, q.content, q.status, q.votes, q.created_at,
                         u.display_name, u.user_email,
                         COALESCE(ac.answer_count, 0) as answer_count
                  FROM $table q
                  LEFT JOIN {$wpdb->users} u ON q.user_id = u.ID
                  LEFT JOIN (
                      SELECT parent_id, COUNT(*) as answer_count 
                      FROM $table 
                      WHERE type = 'answer' AND status = 'approved' 
                      GROUP BY parent_id
                  ) ac ON q.id = ac.parent_id
                  WHERE q.type = 'question' 
                    AND q.status = 'approved' 
                    AND q.course_id = %d";
        
        $params = array($course_id);
        
        if ($lesson_id) {
            $query .= " AND q.lesson_id = %d";
            $params[] = $lesson_id;
        }
        
        $query .= " ORDER BY q.votes DESC, q.created_at DESC";
        $query .= " LIMIT %d OFFSET %d";
        $params[] = $limit;
        $params[] = $offset;
        
        $results = $wpdb->get_results($wpdb->prepare($query, $params));
        
        // Cache results for 5 minutes
        wp_cache_set($cache_key, $results, 'lectus_qa', 300);
        
        return $results;
    }
    
    /**
     * Get answers for a question with caching
     */
    public static function get_answers($question_id) {
        global $wpdb;
        
        $question_id = absint($question_id);
        if (!$question_id) {
            return array();
        }
        
        // Check cache first
        $cache_key = 'lectus_qa_answers_' . $question_id;
        $cached = wp_cache_get($cache_key, 'lectus_qa');
        
        if (false !== $cached) {
            return $cached;
        }
        
        $table = $wpdb->prefix . 'lectus_qa';
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT a.id, a.parent_id, a.user_id, a.content, a.status, 
                    a.votes, a.is_best_answer, a.created_at,
                    u.display_name, u.user_email
             FROM $table a
             LEFT JOIN {$wpdb->users} u ON a.user_id = u.ID
             WHERE a.parent_id = %d 
               AND a.type = 'answer' 
               AND a.status = 'approved'
             ORDER BY a.is_best_answer DESC, a.votes DESC, a.created_at ASC",
            $question_id
        ));
        
        // Cache results for 5 minutes
        wp_cache_set($cache_key, $results, 'lectus_qa', 300);
        
        return $results;
    }
    
    /**
     * Mark answer as best answer
     */
    public static function mark_best_answer($answer_id, $user_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'lectus_qa';
        
        // Get answer and question info
        $answer = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d AND type = 'answer'",
            $answer_id
        ));
        
        if (!$answer) {
            return false;
        }
        
        $question = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d AND type = 'question'",
            $answer->parent_id
        ));
        
        // Check if user can mark best answer (question author or admin)
        if ($question->user_id != $user_id && !current_user_can('manage_students')) {
            return false;
        }
        
        // Remove previous best answer
        $wpdb->update(
            $table,
            array('is_best_answer' => 0),
            array('parent_id' => $answer->parent_id, 'type' => 'answer'),
            array('%d'),
            array('%d', '%s')
        );
        
        // Mark new best answer
        return $wpdb->update(
            $table,
            array('is_best_answer' => 1),
            array('id' => $answer_id),
            array('%d'),
            array('%d')
        );
    }
    
    /**
     * Vote on question or answer
     */
    public static function vote($qa_id, $user_id, $vote_type = 'up') {
        global $wpdb;
        
        $table = $wpdb->prefix . 'lectus_qa';
        $votes_table = $wpdb->prefix . 'lectus_qa_votes';
        
        // Check if user already voted
        $existing_vote = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $votes_table WHERE qa_id = %d AND user_id = %d",
            $qa_id, $user_id
        ));
        
        if ($existing_vote) {
            // Update existing vote
            $wpdb->update(
                $votes_table,
                array('vote_type' => $vote_type),
                array('qa_id' => $qa_id, 'user_id' => $user_id),
                array('%s'),
                array('%d', '%d')
            );
        } else {
            // Insert new vote
            $wpdb->insert(
                $votes_table,
                array(
                    'qa_id' => $qa_id,
                    'user_id' => $user_id,
                    'vote_type' => $vote_type,
                    'created_at' => current_time('mysql')
                ),
                array('%d', '%d', '%s', '%s')
            );
        }
        
        // Update vote count
        $vote_count = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(CASE WHEN vote_type = 'up' THEN 1 WHEN vote_type = 'down' THEN -1 ELSE 0 END) 
             FROM $votes_table WHERE qa_id = %d",
            $qa_id
        ));
        
        return $wpdb->update(
            $table,
            array('votes' => $vote_count),
            array('id' => $qa_id),
            array('%d'),
            array('%d')
        );
    }
    
    /**
     * AJAX handler for submitting questions
     */
    public static function ajax_submit_question() {
        // Debug logging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Lectus Q&A: ajax_submit_question called');
            error_log('POST data: ' . print_r($_POST, true));
        }
        
        // Enhanced security verification
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-ajax-nonce')) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Lectus Q&A: Nonce verification failed');
            }
            wp_send_json_error(array('message' => __('보안 검증 실패', 'lectus-class-system')), 403);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_send_json_error(array('message' => __('잘못된 요청 방식', 'lectus-class-system')), 405);
            return;
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('로그인이 필요합니다.', 'lectus-class-system')), 401);
            return;
        }
        
        // Rate limiting check
        if (!self::check_rate_limit(get_current_user_id(), 'question')) {
            wp_send_json_error(array('message' => __('너무 많은 요청입니다. 잠시 후 다시 시도해주세요.', 'lectus-class-system')), 429);
            return;
        }
        
        // Enhanced input validation
        $course_id = isset($_POST['course_id']) ? absint($_POST['course_id']) : 0;
        $lesson_id = isset($_POST['lesson_id']) ? absint($_POST['lesson_id']) : null;
        $title = isset($_POST['title']) ? sanitize_text_field(trim($_POST['title'])) : '';
        $content = isset($_POST['content']) ? wp_kses_post(trim($_POST['content'])) : '';
        
        // Validate course exists and user has access
        if (!$course_id || !get_post($course_id)) {
            wp_send_json_error(array('message' => __('유효하지 않은 강의입니다.', 'lectus-class-system')), 400);
            return;
        }
        
        // Check if user is enrolled in the course
        $user_id = get_current_user_id();
        if (!current_user_can('manage_options') && !Lectus_Enrollment::is_enrolled($user_id, $course_id)) {
            wp_send_json_error(array('message' => __('이 강의에 등록된 사용자만 질문할 수 있습니다.', 'lectus-class-system')), 403);
            return;
        }
        
        // Content length validation
        if (empty($title) || strlen($title) < 5 || strlen($title) > 255) {
            wp_send_json_error(array('message' => __('제목은 5자 이상 255자 이하로 입력해주세요.', 'lectus-class-system')), 400);
            return;
        }
        
        if (empty($content) || strlen($content) < 10 || strlen($content) > 10000) {
            wp_send_json_error(array('message' => __('내용은 10자 이상 10,000자 이하로 입력해주세요.', 'lectus-class-system')), 400);
            return;
        }
        
        // Check for spam/duplicate content
        if (self::is_duplicate_content($user_id, $title, $content)) {
            wp_send_json_error(array('message' => __('동일한 내용의 질문이 이미 존재합니다.', 'lectus-class-system')), 409);
            return;
        }
        
        // Additional check: Prevent rapid duplicate submissions (within 5 seconds)
        $submission_key = 'lectus_qa_submission_' . $user_id . '_' . md5($title . $content);
        if (get_transient($submission_key)) {
            wp_send_json_error(array('message' => __('이미 처리 중인 요청입니다. 잠시 후 다시 시도해주세요.', 'lectus-class-system')), 429);
            return;
        }
        set_transient($submission_key, true, 5); // Block duplicate for 5 seconds
        
        // Submit the question
        $question_id = self::submit_question($course_id, $lesson_id, $user_id, $title, $content);
        
        if ($question_id) {
            wp_send_json_success(array(
                'message' => __('질문이 등록되었습니다.', 'lectus-class-system'),
                'question_id' => $question_id,
                'redirect' => get_permalink($course_id) . '#qa-' . $question_id
            ));
        } else {
            // Log the error for debugging
            if (defined('WP_DEBUG') && WP_DEBUG) {
                global $wpdb;
                error_log('Lectus Q&A: Database error - ' . $wpdb->last_error);
                error_log('Lectus Q&A: Last query - ' . $wpdb->last_query);
            }
            wp_send_json_error(array(
                'message' => __('질문 등록에 실패했습니다. 데이터베이스 오류가 발생했습니다.', 'lectus-class-system'),
                'debug' => defined('WP_DEBUG') && WP_DEBUG ? $wpdb->last_error : null
            ));
        }
    }
    
    /**
     * AJAX handler for submitting answers
     */
    public static function ajax_submit_answer() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-ajax-nonce')) {
            wp_die('Security check failed');
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('로그인이 필요합니다.', 'lectus-class-system')));
        }
        
        $question_id = isset($_POST['question_id']) ? intval($_POST['question_id']) : 0;
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';
        
        if (!$question_id || empty($content)) {
            wp_send_json_error(array('message' => __('필수 정보가 누락되었습니다.', 'lectus-class-system')));
        }
        
        $user_id = get_current_user_id();
        $answer_id = self::submit_answer($question_id, $user_id, $content);
        
        if ($answer_id) {
            wp_send_json_success(array(
                'message' => __('답변이 등록되었습니다.', 'lectus-class-system'),
                'answer_id' => $answer_id
            ));
        } else {
            wp_send_json_error(array('message' => __('답변 등록에 실패했습니다.', 'lectus-class-system')));
        }
    }
    
    /**
     * AJAX handler for voting
     */
    public static function ajax_vote_qa() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-ajax-nonce')) {
            wp_die('Security check failed');
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('로그인이 필요합니다.', 'lectus-class-system')));
        }
        
        $qa_id = isset($_POST['qa_id']) ? intval($_POST['qa_id']) : 0;
        $vote_type = isset($_POST['vote_type']) ? sanitize_text_field($_POST['vote_type']) : 'up';
        
        if (!$qa_id || !in_array($vote_type, array('up', 'down'))) {
            wp_send_json_error(array('message' => __('잘못된 요청입니다.', 'lectus-class-system')));
        }
        
        $user_id = get_current_user_id();
        $result = self::vote($qa_id, $user_id, $vote_type);
        
        if ($result) {
            wp_send_json_success(array('message' => __('투표가 완료되었습니다.', 'lectus-class-system')));
        } else {
            wp_send_json_error(array('message' => __('투표에 실패했습니다.', 'lectus-class-system')));
        }
    }
    
    /**
     * Add admin menu for Q&A management
     */
    public static function add_admin_menu() {
        add_submenu_page(
            'lectus-class-system',
            __('Q&A 관리', 'lectus-class-system'),
            __('Q&A 관리', 'lectus-class-system'),
            'manage_options',
            'lectus-qa',
            array(__CLASS__, 'admin_page')
        );
    }
    
    /**
     * Admin page for Q&A management
     */
    public static function admin_page() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'lectus_qa';
        
        // Get all questions with pagination
        $per_page = 20;
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($current_page - 1) * $per_page;
        
        $total_items = $wpdb->get_var(
            "SELECT COUNT(*) FROM $table WHERE type = 'question'"
        );
        
        $questions = $wpdb->get_results($wpdb->prepare(
            "SELECT q.*, u.display_name, c.post_title as course_title, l.post_title as lesson_title,
                    (SELECT COUNT(*) FROM $table a WHERE a.parent_id = q.id AND a.type = 'answer') as answer_count
             FROM $table q
             LEFT JOIN {$wpdb->users} u ON q.user_id = u.ID
             LEFT JOIN {$wpdb->posts} c ON q.course_id = c.ID
             LEFT JOIN {$wpdb->posts} l ON q.lesson_id = l.ID
             WHERE q.type = 'question'
             ORDER BY q.created_at DESC
             LIMIT %d OFFSET %d",
            $per_page, $offset
        ));
        
        $total_pages = ceil($total_items / $per_page);
        ?>
        <div class="wrap">
            <h1><?php _e('Q&A 관리', 'lectus-class-system'); ?></h1>
            
            <div class="tablenav top">
                <div class="alignleft actions">
                    <select name="action" id="bulk-action-selector-top">
                        <option value=""><?php _e('일괄 작업 선택', 'lectus-class-system'); ?></option>
                        <option value="approve"><?php _e('승인', 'lectus-class-system'); ?></option>
                        <option value="reject"><?php _e('거부', 'lectus-class-system'); ?></option>
                        <option value="delete"><?php _e('삭제', 'lectus-class-system'); ?></option>
                    </select>
                    <input type="submit" class="button action" value="<?php _e('적용', 'lectus-class-system'); ?>">
                </div>
                
                <div class="tablenav-pages">
                    <span class="displaying-num"><?php printf(__('%d개 항목', 'lectus-class-system'), $total_items); ?></span>
                    <?php if ($total_pages > 1): ?>
                        <span class="pagination-links">
                            <?php
                            echo paginate_links(array(
                                'base' => add_query_arg('paged', '%#%'),
                                'format' => '',
                                'prev_text' => '&laquo;',
                                'next_text' => '&raquo;',
                                'current' => $current_page,
                                'total' => $total_pages
                            ));
                            ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <td class="check-column">
                            <input type="checkbox" id="cb-select-all-1">
                        </td>
                        <th><?php _e('질문', 'lectus-class-system'); ?></th>
                        <th><?php _e('작성자', 'lectus-class-system'); ?></th>
                        <th><?php _e('강의', 'lectus-class-system'); ?></th>
                        <th><?php _e('답변 수', 'lectus-class-system'); ?></th>
                        <th><?php _e('투표', 'lectus-class-system'); ?></th>
                        <th><?php _e('상태', 'lectus-class-system'); ?></th>
                        <th><?php _e('작성일', 'lectus-class-system'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($questions as $question): ?>
                        <tr>
                            <th class="check-column">
                                <input type="checkbox" name="question[]" value="<?php echo $question->id; ?>">
                            </th>
                            <td>
                                <strong><?php echo esc_html($question->title); ?></strong>
                                <div class="question-content" style="margin-top: 5px; color: #666; font-size: 12px;">
                                    <?php echo wp_trim_words(strip_tags($question->content), 15, '...'); ?>
                                </div>
                                <div class="row-actions">
                                    <span class="edit"><a href="#" onclick="viewQuestion(<?php echo $question->id; ?>)"><?php _e('보기', 'lectus-class-system'); ?></a> | </span>
                                    <span class="trash"><a href="#" onclick="deleteQuestion(<?php echo $question->id; ?>)" class="submitdelete"><?php _e('삭제', 'lectus-class-system'); ?></a></span>
                                </div>
                            </td>
                            <td><?php echo esc_html($question->display_name); ?></td>
                            <td>
                                <?php echo esc_html($question->course_title); ?>
                                <?php if ($question->lesson_title): ?>
                                    <br><small><?php echo esc_html($question->lesson_title); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $question->answer_count; ?></td>
                            <td><?php echo $question->votes; ?></td>
                            <td>
                                <span class="status-<?php echo $question->status; ?>">
                                    <?php 
                                    $statuses = array(
                                        'pending' => __('대기중', 'lectus-class-system'),
                                        'approved' => __('승인됨', 'lectus-class-system'),
                                        'rejected' => __('거부됨', 'lectus-class-system')
                                    );
                                    echo $statuses[$question->status];
                                    ?>
                                </span>
                            </td>
                            <td><?php echo date_i18n(get_option('date_format'), strtotime($question->created_at)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <style>
        .status-pending { color: #d63638; }
        .status-approved { color: #00a32a; }
        .status-rejected { color: #999; }
        </style>
        
        <script>
        function viewQuestion(questionId) {
            // Question viewing functionality
            window.location.href = 'admin.php?page=lectus-qa&action=view&question_id=' + questionId;
        }
        
        function deleteQuestion(questionId) {
            if (confirm('정말로 이 질문을 삭제하시겠습니까?')) {
                // Question deletion functionality
                window.location.href = 'admin.php?page=lectus-qa&action=delete&question_id=' + questionId;
            }
        }
        </script>
        <?php
    }
    
    /**
     * Q&A shortcode
     */
    public static function qa_shortcode($atts) {
        $atts = shortcode_atts(array(
            'course_id' => 0,
            'lesson_id' => 0,
            'show_form' => 'yes',
            'limit' => 10
        ), $atts);
        
        if (!$atts['course_id']) {
            return '<p>' . __('강의 ID가 필요합니다.', 'lectus-class-system') . '</p>';
        }
        
        ob_start();
        ?>
        <div class="lectus-qa-container" role="main" aria-label="<?php esc_attr_e('질문과 답변', 'lectus-class-system'); ?>">
            <?php if ($atts['show_form'] === 'yes' && is_user_logged_in()): ?>
                <section class="qa-form-section" aria-labelledby="qa-form-heading">
                    <h3 id="qa-form-heading"><?php _e('질문하기', 'lectus-class-system'); ?></h3>
                    <form id="lectus-qa-form" method="post" aria-describedby="qa-form-help">
                        <div id="qa-form-help" class="sr-only">
                            <?php _e('제목과 내용을 입력하여 질문을 등록하세요.', 'lectus-class-system'); ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="qa-title"><?php _e('제목', 'lectus-class-system'); ?> <span class="required" aria-label="필수">*</span></label>
                            <input type="text" id="qa-title" name="title" required maxlength="255" 
                                   aria-describedby="title-help" autocomplete="off">
                            <div id="title-help" class="field-help">
                                <?php _e('5자 이상 255자 이하로 입력해주세요.', 'lectus-class-system'); ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="qa-content"><?php _e('내용', 'lectus-class-system'); ?> <span class="required" aria-label="필수">*</span></label>
                            <textarea id="qa-content" name="content" rows="5" required 
                                      aria-describedby="content-help" maxlength="10000"></textarea>
                            <div id="content-help" class="field-help">
                                <?php _e('10자 이상 10,000자 이하로 입력해주세요.', 'lectus-class-system'); ?>
                                <span class="char-count" aria-live="polite">0 / 10,000</span>
                            </div>
                        </div>
                        
                        <input type="hidden" name="course_id" value="<?php echo esc_attr($atts['course_id']); ?>">
                        <input type="hidden" name="lesson_id" value="<?php echo esc_attr($atts['lesson_id']); ?>">
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-submit btn-primary" 
                                    aria-describedby="submit-help">
                                <?php _e('질문 등록', 'lectus-class-system'); ?>
                            </button>
                            <div id="submit-help" class="sr-only">
                                <?php _e('작성한 질문을 등록합니다.', 'lectus-class-system'); ?>
                            </div>
                        </div>
                        
                        <div id="form-status" class="form-status" aria-live="assertive" role="status"></div>
                    </form>
                </section>
            <?php elseif ($atts['show_form'] === 'yes'): ?>
                <div class="login-required" role="alert">
                    <p><?php _e('질문을 등록하려면 로그인이 필요합니다.', 'lectus-class-system'); ?></p>
                    <a href="<?php echo wp_login_url(get_permalink()); ?>" class="btn-login">
                        <?php _e('로그인', 'lectus-class-system'); ?>
                    </a>
                </div>
            <?php endif; ?>
            
            <section class="qa-list-section" aria-labelledby="qa-list-heading">
                <h3 id="qa-list-heading"><?php _e('질문과 답변', 'lectus-class-system'); ?></h3>
                <div id="qa-list" role="feed" aria-label="<?php esc_attr_e('질문 목록', 'lectus-class-system'); ?>">
                    <?php 
                    $questions = self::get_questions($atts['course_id'], $atts['lesson_id'], $atts['limit']);
                    foreach ($questions as $question): 
                        $answers = self::get_answers($question->id);
                    ?>
                        <div class="qa-item" data-question-id="<?php echo $question->id; ?>">
                            <div class="question">
                                <div class="qa-header">
                                    <h4><?php echo esc_html($question->title); ?></h4>
                                    <div class="qa-meta">
                                        <span class="author"><?php echo esc_html($question->display_name); ?></span>
                                        <span class="date"><?php echo human_time_diff(strtotime($question->created_at)); ?> <?php _e('전', 'lectus-class-system'); ?></span>
                                        <span class="votes"><?php _e('추천', 'lectus-class-system'); ?> <?php echo $question->votes; ?></span>
                                    </div>
                                </div>
                                <div class="qa-content">
                                    <?php echo wpautop(esc_html($question->content)); ?>
                                </div>
                                <div class="qa-actions">
                                    <button class="vote-btn" onclick="voteQA(<?php echo $question->id; ?>, 'up')"><?php _e('👍 추천', 'lectus-class-system'); ?></button>
                                    <button class="answer-btn" onclick="toggleAnswerForm(<?php echo $question->id; ?>)"><?php _e('답변하기', 'lectus-class-system'); ?></button>
                                </div>
                                
                                <?php if (is_user_logged_in()): ?>
                                    <div class="answer-form" id="answer-form-<?php echo $question->id; ?>" style="display: none;">
                                        <form onsubmit="submitAnswer(event, <?php echo $question->id; ?>)">
                                            <textarea name="content" placeholder="<?php _e('답변을 입력하세요...', 'lectus-class-system'); ?>" required></textarea>
                                            <button type="submit"><?php _e('답변 등록', 'lectus-class-system'); ?></button>
                                            <button type="button" onclick="toggleAnswerForm(<?php echo $question->id; ?>)"><?php _e('취소', 'lectus-class-system'); ?></button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($answers)): ?>
                                <div class="answers">
                                    <h5><?php printf(__('답변 (%d)', 'lectus-class-system'), count($answers)); ?></h5>
                                    <?php foreach ($answers as $answer): ?>
                                        <div class="answer <?php echo $answer->is_best_answer ? 'best-answer' : ''; ?>">
                                            <div class="qa-header">
                                                <div class="qa-meta">
                                                    <?php if ($answer->is_best_answer): ?>
                                                        <span class="best-badge"><?php _e('✓ 채택됨', 'lectus-class-system'); ?></span>
                                                    <?php endif; ?>
                                                    <span class="author"><?php echo esc_html($answer->display_name); ?></span>
                                                    <span class="date"><?php echo human_time_diff(strtotime($answer->created_at)); ?> <?php _e('전', 'lectus-class-system'); ?></span>
                                                    <span class="votes"><?php _e('추천', 'lectus-class-system'); ?> <?php echo $answer->votes; ?></span>
                                                </div>
                                            </div>
                                            <div class="qa-content">
                                                <?php echo wpautop(esc_html($answer->content)); ?>
                                            </div>
                                            <div class="qa-actions">
                                                <button class="vote-btn" onclick="voteQA(<?php echo $answer->id; ?>, 'up')"><?php _e('👍 추천', 'lectus-class-system'); ?></button>
                                                <?php if (is_user_logged_in() && (get_current_user_id() == $question->user_id || current_user_can('manage_students')) && !$answer->is_best_answer): ?>
                                                    <button class="best-answer-btn" onclick="markBestAnswer(<?php echo $answer->id; ?>)"><?php _e('✓ 채택', 'lectus-class-system'); ?></button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <style>
        .lectus-qa-container {
            max-width: 800px;
            margin: 20px 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        /* Accessibility helpers */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0,0,0,0);
            white-space: nowrap;
            border: 0;
        }
        
        .required {
            color: #d63638;
            font-weight: bold;
        }
        
        /* Form styling */
        .qa-form-section {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid #e0e0e0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1d2327;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.2s ease;
            box-sizing: border-box;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #0073aa;
            box-shadow: 0 0 0 1px #0073aa;
        }
        
        .field-help {
            font-size: 14px;
            color: #646970;
            margin-top: 5px;
        }
        
        .char-count {
            float: right;
            font-weight: 500;
        }
        
        .form-actions {
            margin-top: 20px;
        }
        
        .btn-submit {
            background: #0073aa;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.2s ease;
            min-height: 44px; /* Touch target size */
        }
        
        .btn-submit:hover {
            background: #005a87;
        }
        
        .btn-submit:focus {
            outline: 3px solid #007cba;
            outline-offset: 2px;
        }
        
        .btn-login {
            display: inline-block;
            background: #0073aa;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.2s ease;
        }
        
        .btn-login:hover {
            background: #005a87;
            color: white;
        }
        
        .form-status {
            margin-top: 15px;
            padding: 10px;
            border-radius: 4px;
            display: none;
        }
        
        .form-status.success {
            background: #d1eddf;
            color: #00a32a;
            border: 1px solid #00a32a;
        }
        
        .form-status.error {
            background: #f7dede;
            color: #d63638;
            border: 1px solid #d63638;
        }
        
        .login-required {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 20px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .lectus-qa-container {
                margin: 10px;
            }
            
            .qa-form-section {
                padding: 15px;
            }
            
            .form-group input,
            .form-group textarea {
                font-size: 16px; /* Prevent zoom on iOS */
            }
            
            .btn-submit {
                width: 100%;
                padding: 15px;
            }
        }
        .qa-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .question {
            padding: 20px;
            background: white;
        }
        .qa-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .qa-meta {
            font-size: 12px;
            color: #666;
        }
        .qa-meta span {
            margin-right: 15px;
        }
        .qa-content {
            margin: 15px 0;
            line-height: 1.6;
        }
        .qa-actions button {
            background: none;
            border: 1px solid #ddd;
            padding: 5px 10px;
            margin-right: 10px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }
        .answers {
            border-top: 1px solid #eee;
            background: #fafafa;
        }
        .answers h5 {
            padding: 15px 20px 0;
            margin: 0;
        }
        .answer {
            padding: 15px 20px;
            border-top: 1px solid #eee;
        }
        .best-answer {
            background: #f0f8ff;
            border-left: 4px solid #0073aa;
        }
        .best-badge {
            background: #0073aa;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
        }
        .answer-form {
            margin-top: 15px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .answer-form textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            var isSubmitting = false;
            var $form = $('#lectus-qa-form');
            var $status = $('#form-status');
            var $submitBtn = $('.btn-submit');
            var $charCount = $('.char-count');
            var $contentField = $('#qa-content');
            
            // Character counter
            $contentField.on('input', function() {
                var length = $(this).val().length;
                var remaining = 10000 - length;
                $charCount.text(length + ' / 10,000');
                
                if (remaining < 100) {
                    $charCount.addClass('warning');
                } else {
                    $charCount.removeClass('warning');
                }
                
                if (remaining < 0) {
                    $charCount.addClass('error');
                } else {
                    $charCount.removeClass('error');
                }
            });
            
            // Form validation
            function validateForm() {
                var title = $('#qa-title').val().trim();
                var content = $('#qa-content').val().trim();
                var errors = [];
                
                if (title.length < 5 || title.length > 255) {
                    errors.push('제목은 5자 이상 255자 이하로 입력해주세요.');
                }
                
                if (content.length < 10 || content.length > 10000) {
                    errors.push('내용은 10자 이상 10,000자 이하로 입력해주세요.');
                }
                
                return errors;
            }
            
            // Show status message
            function showStatus(message, type) {
                $status.removeClass('success error').addClass(type).text(message).show();
                
                // Announce to screen readers
                $status.attr('aria-live', type === 'error' ? 'assertive' : 'polite');
                
                setTimeout(function() {
                    $status.fadeOut();
                }, 5000);
            }
            
            // Form submission
            $form.on('submit', function(e) {
                e.preventDefault();
                
                if (isSubmitting) {
                    return;
                }
                
                // Validate form
                var errors = validateForm();
                if (errors.length > 0) {
                    showStatus(errors.join(' '), 'error');
                    return;
                }
                
                isSubmitting = true;
                $submitBtn.prop('disabled', true).text('등록 중...');
                
                var formData = $(this).serialize();
                formData += '&action=lectus_submit_question&nonce=' + lectus_ajax.nonce;
                
                $.post(lectus_ajax.ajaxurl, formData)
                    .done(function(response) {
                        if (response.success) {
                            showStatus(response.data.message, 'success');
                            $form[0].reset();
                            $charCount.text('0 / 10,000').removeClass('warning error');
                            
                            // Reload after delay to show success message
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            showStatus(response.data.message || '오류가 발생했습니다.', 'error');
                        }
                    })
                    .fail(function() {
                        showStatus('네트워크 오류가 발생했습니다. 다시 시도해주세요.', 'error');
                    })
                    .always(function() {
                        isSubmitting = false;
                        $submitBtn.prop('disabled', false).text('질문 등록');
                    });
            });
        });
        
        function toggleAnswerForm(questionId) {
            var form = document.getElementById('answer-form-' + questionId);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
        
        function submitAnswer(event, questionId) {
            event.preventDefault();
            
            var form = event.target;
            var content = form.content.value;
            
            jQuery.post(lectus_ajax.ajaxurl, {
                action: 'lectus_submit_answer',
                nonce: lectus_ajax.nonce,
                question_id: questionId,
                content: content
            }, function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            });
        }
        
        function voteQA(qaId, voteType) {
            jQuery.post(lectus_ajax.ajaxurl, {
                action: 'lectus_vote_qa',
                nonce: lectus_ajax.nonce,
                qa_id: qaId,
                vote_type: voteType
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            });
        }
        
        function markBestAnswer(answerId) {
            if (confirm('이 답변을 채택하시겠습니까?')) {
                // Best answer marking functionality
                var xhr = new XMLHttpRequest();
                xhr.open('POST', ajaxurl);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        location.reload();
                    }
                };
                xhr.send('action=lectus_mark_best_answer&answer_id=' + answerId + '&nonce=' + lectusAjax.nonce);
            }
        }
        </script>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Create votes table for Q&A voting system
     */
    public static function create_votes_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'lectus_qa_votes';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            qa_id bigint(20) NOT NULL,
            user_id bigint(20) NOT NULL,
            vote_type enum('up', 'down') NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_vote (qa_id, user_id),
            KEY qa_id (qa_id),
            KEY user_id (user_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
?>