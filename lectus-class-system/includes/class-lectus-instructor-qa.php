<?php
/**
 * Instructor Q&A Management for Lectus Class System
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Instructor_QA {
    
    public static function init() {
        // Add instructor menu
        add_action('admin_menu', array(__CLASS__, 'add_instructor_menu'), 20);
        
        // AJAX handlers for instructor actions
        add_action('wp_ajax_lectus_instructor_answer', array(__CLASS__, 'ajax_instructor_answer'));
        add_action('wp_ajax_lectus_instructor_moderate', array(__CLASS__, 'ajax_moderate_qa'));
        add_action('wp_ajax_lectus_instructor_mark_best', array(__CLASS__, 'ajax_mark_best_answer'));
        add_action('wp_ajax_lectus_instructor_delete_qa', array(__CLASS__, 'ajax_delete_qa'));
    }
    
    /**
     * Add instructor Q&A menu
     */
    public static function add_instructor_menu() {
        // Check if user is instructor or admin
        if (!current_user_can('manage_qa') && !current_user_can('manage_options')) {
            return;
        }
        
        add_submenu_page(
            'lectus-class-system',
            __('강사 Q&A 관리', 'lectus-class-system'),
            __('강사 Q&A', 'lectus-class-system'),
            'manage_qa',
            'lectus-instructor-qa',
            array(__CLASS__, 'render_instructor_page')
        );
    }
    
    /**
     * Render instructor Q&A management page
     */
    public static function render_instructor_page() {
        global $wpdb;
        
        $current_user_id = get_current_user_id();
        $is_admin = current_user_can('manage_options');
        
        // Get courses assigned to this instructor (or all if admin)
        $courses = array();
        if ($is_admin) {
            $courses = get_posts(array(
                'post_type' => 'coursesingle',
                'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC'
            ));
        } else {
            $courses = get_posts(array(
                'post_type' => 'coursesingle',
                'meta_key' => '_course_instructor_id',
                'meta_value' => $current_user_id,
                'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC'
            ));
        }
        
        // Get selected course
        $selected_course = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
        
        // Get filter parameters
        $filter_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $filter_type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';
        
        ?>
        <div class="wrap">
            <h1><?php _e('강사 Q&A 관리', 'lectus-class-system'); ?></h1>
            
            <?php if (empty($courses)): ?>
                <div class="notice notice-warning">
                    <p><?php _e('담당 강의가 없습니다.', 'lectus-class-system'); ?></p>
                </div>
            <?php else: ?>
                
                <!-- Filters -->
                <div class="tablenav top">
                    <form method="get" action="">
                        <input type="hidden" name="page" value="lectus-instructor-qa" />
                        
                        <select name="course_id" onchange="this.form.submit()">
                            <option value=""><?php _e('모든 강의', 'lectus-class-system'); ?></option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course->ID; ?>" <?php selected($selected_course, $course->ID); ?>>
                                    <?php echo esc_html($course->post_title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <select name="status" onchange="this.form.submit()">
                            <option value=""><?php _e('모든 상태', 'lectus-class-system'); ?></option>
                            <option value="pending" <?php selected($filter_status, 'pending'); ?>><?php _e('대기중', 'lectus-class-system'); ?></option>
                            <option value="approved" <?php selected($filter_status, 'approved'); ?>><?php _e('승인됨', 'lectus-class-system'); ?></option>
                            <option value="rejected" <?php selected($filter_status, 'rejected'); ?>><?php _e('거부됨', 'lectus-class-system'); ?></option>
                        </select>
                        
                        <select name="type" onchange="this.form.submit()">
                            <option value=""><?php _e('모든 타입', 'lectus-class-system'); ?></option>
                            <option value="unanswered" <?php selected($filter_type, 'unanswered'); ?>><?php _e('미답변 질문', 'lectus-class-system'); ?></option>
                            <option value="answered" <?php selected($filter_type, 'answered'); ?>><?php _e('답변된 질문', 'lectus-class-system'); ?></option>
                        </select>
                    </form>
                </div>
                
                <?php
                // Build query for Q&A items
                $table = $wpdb->prefix . 'lectus_qa';
                $course_ids = array_map(function($course) { return $course->ID; }, $courses);
                
                $where_clauses = array("q.type = 'question'");
                
                // Filter by instructor's courses
                if (!$is_admin && !empty($course_ids)) {
                    $where_clauses[] = "q.course_id IN (" . implode(',', $course_ids) . ")";
                }
                
                // Filter by selected course
                if ($selected_course) {
                    $where_clauses[] = $wpdb->prepare("q.course_id = %d", $selected_course);
                }
                
                // Filter by status
                if ($filter_status) {
                    $where_clauses[] = $wpdb->prepare("q.status = %s", $filter_status);
                }
                
                $where_sql = implode(' AND ', $where_clauses);
                
                // Get questions
                $query = "SELECT q.*, u.display_name, c.post_title as course_title, l.post_title as lesson_title,
                         (SELECT COUNT(*) FROM $table a WHERE a.parent_id = q.id AND a.type = 'answer') as answer_count
                         FROM $table q
                         LEFT JOIN {$wpdb->users} u ON q.user_id = u.ID
                         LEFT JOIN {$wpdb->posts} c ON q.course_id = c.ID
                         LEFT JOIN {$wpdb->posts} l ON q.lesson_id = l.ID
                         WHERE $where_sql
                         ORDER BY q.created_at DESC";
                
                $questions = $wpdb->get_results($query);
                
                // Filter by answered/unanswered if needed
                if ($filter_type === 'unanswered') {
                    $questions = array_filter($questions, function($q) { return $q->answer_count == 0; });
                } elseif ($filter_type === 'answered') {
                    $questions = array_filter($questions, function($q) { return $q->answer_count > 0; });
                }
                ?>
                
                <!-- Questions Table -->
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 50px;"><?php _e('ID', 'lectus-class-system'); ?></th>
                            <th><?php _e('질문', 'lectus-class-system'); ?></th>
                            <th><?php _e('강의', 'lectus-class-system'); ?></th>
                            <th><?php _e('레슨', 'lectus-class-system'); ?></th>
                            <th><?php _e('작성자', 'lectus-class-system'); ?></th>
                            <th><?php _e('답변', 'lectus-class-system'); ?></th>
                            <th><?php _e('상태', 'lectus-class-system'); ?></th>
                            <th><?php _e('날짜', 'lectus-class-system'); ?></th>
                            <th><?php _e('작업', 'lectus-class-system'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($questions)): ?>
                            <tr>
                                <td colspan="9"><?php _e('질문이 없습니다.', 'lectus-class-system'); ?></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($questions as $question): ?>
                                <tr data-question-id="<?php echo $question->id; ?>">
                                    <td><?php echo $question->id; ?></td>
                                    <td>
                                        <strong><?php echo esc_html($question->title ?: wp_trim_words($question->content, 10)); ?></strong>
                                        <div class="row-actions">
                                            <span class="view">
                                                <a href="#" onclick="lectusInstructorViewQuestion(<?php echo $question->id; ?>); return false;">
                                                    <?php _e('보기', 'lectus-class-system'); ?>
                                                </a>
                                            </span>
                                        </div>
                                    </td>
                                    <td><?php echo esc_html($question->course_title); ?></td>
                                    <td><?php echo $question->lesson_title ? esc_html($question->lesson_title) : '-'; ?></td>
                                    <td><?php echo esc_html($question->display_name); ?></td>
                                    <td>
                                        <span class="answer-count <?php echo $question->answer_count == 0 ? 'no-answers' : ''; ?>">
                                            <?php echo $question->answer_count; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $question->status; ?>">
                                            <?php
                                            switch($question->status) {
                                                case 'pending':
                                                    _e('대기중', 'lectus-class-system');
                                                    break;
                                                case 'approved':
                                                    _e('승인됨', 'lectus-class-system');
                                                    break;
                                                case 'rejected':
                                                    _e('거부됨', 'lectus-class-system');
                                                    break;
                                            }
                                            ?>
                                        </span>
                                    </td>
                                    <td><?php echo date_i18n(get_option('date_format'), strtotime($question->created_at)); ?></td>
                                    <td>
                                        <button class="button button-primary button-small" onclick="lectusInstructorAnswerQuestion(<?php echo $question->id; ?>)">
                                            <?php _e('답변', 'lectus-class-system'); ?>
                                        </button>
                                        <?php if (current_user_can('moderate_qa')): ?>
                                            <button class="button button-small" onclick="lectusInstructorModerate(<?php echo $question->id; ?>, 'approve')">
                                                <?php _e('승인', 'lectus-class-system'); ?>
                                            </button>
                                            <button class="button button-small" onclick="lectusInstructorDelete(<?php echo $question->id; ?>)">
                                                <?php _e('삭제', 'lectus-class-system'); ?>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                
            <?php endif; ?>
        </div>
        
        <!-- Q&A Modal for viewing and answering -->
        <div id="lectus-qa-modal" style="display:none;">
            <div class="lectus-qa-modal-overlay"></div>
            <div class="lectus-qa-modal-content">
                <div class="lectus-qa-modal-header">
                    <h2><?php _e('Q&A 상세', 'lectus-class-system'); ?></h2>
                    <button class="lectus-modal-close">&times;</button>
                </div>
                <div class="lectus-qa-modal-body">
                    <!-- Content will be loaded here via AJAX -->
                </div>
            </div>
        </div>
        
        <style>
            .status-badge {
                padding: 3px 8px;
                border-radius: 3px;
                font-size: 11px;
                font-weight: bold;
                text-transform: uppercase;
            }
            .status-pending { background: #fcf8e3; color: #8a6d3b; }
            .status-approved { background: #dff0d8; color: #3c763d; }
            .status-rejected { background: #f2dede; color: #a94442; }
            
            .answer-count {
                display: inline-block;
                padding: 2px 6px;
                background: #0073aa;
                color: white;
                border-radius: 10px;
                font-size: 12px;
                font-weight: bold;
            }
            .answer-count.no-answers {
                background: #dc3232;
            }
            
            #lectus-qa-modal {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 100000;
            }
            
            .lectus-qa-modal-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.7);
            }
            
            .lectus-qa-modal-content {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: white;
                width: 90%;
                max-width: 800px;
                max-height: 90vh;
                overflow: auto;
                border-radius: 8px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            }
            
            .lectus-qa-modal-header {
                padding: 20px;
                border-bottom: 1px solid #ddd;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .lectus-qa-modal-header h2 {
                margin: 0;
            }
            
            .lectus-modal-close {
                background: none;
                border: none;
                font-size: 24px;
                cursor: pointer;
                color: #999;
            }
            
            .lectus-qa-modal-body {
                padding: 20px;
            }
            
            .qa-question {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 5px;
                margin-bottom: 20px;
            }
            
            .qa-answers {
                margin-top: 20px;
            }
            
            .qa-answer {
                border-left: 3px solid #0073aa;
                padding-left: 15px;
                margin-bottom: 15px;
            }
            
            .qa-answer.instructor-answer {
                border-left-color: #46b450;
                background: #f0f8ff;
                padding: 15px;
                border-radius: 5px;
            }
            
            .answer-form {
                margin-top: 20px;
                padding-top: 20px;
                border-top: 1px solid #ddd;
            }
            
            .answer-form textarea {
                width: 100%;
                min-height: 100px;
            }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // View question details
            window.lectusInstructorViewQuestion = function(questionId) {
                // Load question details via AJAX
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'lectus_get_qa_details',
                        question_id: questionId,
                        nonce: '<?php echo wp_create_nonce('lectus-qa-nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#lectus-qa-modal .lectus-qa-modal-body').html(response.data.html);
                            $('#lectus-qa-modal').fadeIn();
                        }
                    }
                });
            };
            
            // Answer question
            window.lectusInstructorAnswerQuestion = function(questionId) {
                lectusInstructorViewQuestion(questionId);
            };
            
            // Submit answer
            $(document).on('submit', '#instructor-answer-form', function(e) {
                e.preventDefault();
                
                var $form = $(this);
                var $button = $form.find('button[type="submit"]');
                
                $button.prop('disabled', true);
                
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'lectus_instructor_answer',
                        question_id: $form.find('input[name="question_id"]').val(),
                        answer: $form.find('textarea[name="answer"]').val(),
                        nonce: '<?php echo wp_create_nonce('lectus-instructor-nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            location.reload();
                        } else {
                            alert(response.data.message || '오류가 발생했습니다.');
                        }
                        $button.prop('disabled', false);
                    },
                    error: function() {
                        alert('오류가 발생했습니다.');
                        $button.prop('disabled', false);
                    }
                });
            });
            
            // Moderate question
            window.lectusInstructorModerate = function(questionId, action) {
                if (!confirm('이 질문을 ' + (action === 'approve' ? '승인' : '거부') + '하시겠습니까?')) {
                    return;
                }
                
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'lectus_instructor_moderate',
                        question_id: questionId,
                        moderate_action: action,
                        nonce: '<?php echo wp_create_nonce('lectus-instructor-nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            location.reload();
                        } else {
                            alert(response.data.message || '오류가 발생했습니다.');
                        }
                    }
                });
            };
            
            // Delete Q&A
            window.lectusInstructorDelete = function(questionId) {
                if (!confirm('정말로 이 질문을 삭제하시겠습니까?')) {
                    return;
                }
                
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'lectus_instructor_delete_qa',
                        question_id: questionId,
                        nonce: '<?php echo wp_create_nonce('lectus-instructor-nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            location.reload();
                        } else {
                            alert(response.data.message || '오류가 발생했습니다.');
                        }
                    }
                });
            };
            
            // Close modal
            $('.lectus-modal-close, .lectus-qa-modal-overlay').on('click', function() {
                $('#lectus-qa-modal').fadeOut();
            });
        });
        </script>
        <?php
    }
    
    /**
     * AJAX handler for instructor answer
     */
    public static function ajax_instructor_answer() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-instructor-nonce')) {
            wp_send_json_error(array('message' => __('보안 검증 실패', 'lectus-class-system')));
            return;
        }
        
        // Check permission
        if (!current_user_can('answer_questions')) {
            wp_send_json_error(array('message' => __('권한이 없습니다.', 'lectus-class-system')));
            return;
        }
        
        $question_id = isset($_POST['question_id']) ? intval($_POST['question_id']) : 0;
        $answer_content = isset($_POST['answer']) ? wp_kses_post($_POST['answer']) : '';
        
        if (!$question_id || empty($answer_content)) {
            wp_send_json_error(array('message' => __('필수 정보가 누락되었습니다.', 'lectus-class-system')));
            return;
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_qa';
        
        // Get question details
        $question = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d AND type = 'question'",
            $question_id
        ));
        
        if (!$question) {
            wp_send_json_error(array('message' => __('질문을 찾을 수 없습니다.', 'lectus-class-system')));
            return;
        }
        
        // Check if instructor has permission for this course
        $current_user_id = get_current_user_id();
        if (!current_user_can('manage_options')) {
            $instructor_id = get_post_meta($question->course_id, '_course_instructor_id', true);
            if ($instructor_id != $current_user_id) {
                wp_send_json_error(array('message' => __('이 강의의 강사가 아닙니다.', 'lectus-class-system')));
                return;
            }
        }
        
        // Insert answer
        $result = $wpdb->insert(
            $table,
            array(
                'parent_id' => $question_id,
                'course_id' => $question->course_id,
                'lesson_id' => $question->lesson_id,
                'user_id' => $current_user_id,
                'type' => 'answer',
                'content' => $answer_content,
                'status' => 'approved',
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result) {
            // Send notification to question author
            $question_author = get_user_by('id', $question->user_id);
            if ($question_author && $question_author->user_email) {
                $course_title = get_the_title($question->course_id);
                $subject = sprintf(__('[%s] 질문에 답변이 등록되었습니다', 'lectus-class-system'), $course_title);
                $message = sprintf(
                    __("안녕하세요 %s님,\n\n'%s' 강의에서 작성하신 질문에 강사님의 답변이 등록되었습니다.\n\n질문: %s\n\n답변: %s\n\n감사합니다.", 'lectus-class-system'),
                    $question_author->display_name,
                    $course_title,
                    wp_trim_words($question->content, 20),
                    wp_trim_words($answer_content, 50)
                );
                
                wp_mail($question_author->user_email, $subject, $message);
            }
            
            wp_send_json_success(array('message' => __('답변이 등록되었습니다.', 'lectus-class-system')));
        } else {
            wp_send_json_error(array('message' => __('답변 등록에 실패했습니다.', 'lectus-class-system')));
        }
    }
    
    /**
     * AJAX handler for moderating Q&A
     */
    public static function ajax_moderate_qa() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-instructor-nonce')) {
            wp_send_json_error(array('message' => __('보안 검증 실패', 'lectus-class-system')));
            return;
        }
        
        // Check permission
        if (!current_user_can('moderate_qa')) {
            wp_send_json_error(array('message' => __('권한이 없습니다.', 'lectus-class-system')));
            return;
        }
        
        $question_id = isset($_POST['question_id']) ? intval($_POST['question_id']) : 0;
        $moderate_action = isset($_POST['moderate_action']) ? sanitize_text_field($_POST['moderate_action']) : '';
        
        if (!$question_id || !in_array($moderate_action, array('approve', 'reject'))) {
            wp_send_json_error(array('message' => __('잘못된 요청입니다.', 'lectus-class-system')));
            return;
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_qa';
        
        $status = $moderate_action === 'approve' ? 'approved' : 'rejected';
        
        $result = $wpdb->update(
            $table,
            array('status' => $status, 'updated_at' => current_time('mysql')),
            array('id' => $question_id),
            array('%s', '%s'),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success(array('message' => __('상태가 변경되었습니다.', 'lectus-class-system')));
        } else {
            wp_send_json_error(array('message' => __('상태 변경에 실패했습니다.', 'lectus-class-system')));
        }
    }
    
    /**
     * AJAX handler for marking best answer
     */
    public static function ajax_mark_best_answer() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-instructor-nonce')) {
            wp_send_json_error(array('message' => __('보안 검증 실패', 'lectus-class-system')));
            return;
        }
        
        // Check permission
        if (!current_user_can('answer_questions')) {
            wp_send_json_error(array('message' => __('권한이 없습니다.', 'lectus-class-system')));
            return;
        }
        
        $answer_id = isset($_POST['answer_id']) ? intval($_POST['answer_id']) : 0;
        
        if (!$answer_id) {
            wp_send_json_error(array('message' => __('잘못된 요청입니다.', 'lectus-class-system')));
            return;
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_qa';
        
        // Get answer details
        $answer = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d AND type = 'answer'",
            $answer_id
        ));
        
        if (!$answer) {
            wp_send_json_error(array('message' => __('답변을 찾을 수 없습니다.', 'lectus-class-system')));
            return;
        }
        
        // Remove best answer status from other answers for this question
        $wpdb->update(
            $table,
            array('is_best_answer' => 0),
            array('parent_id' => $answer->parent_id, 'type' => 'answer'),
            array('%d'),
            array('%d', '%s')
        );
        
        // Mark this answer as best
        $result = $wpdb->update(
            $table,
            array('is_best_answer' => 1, 'updated_at' => current_time('mysql')),
            array('id' => $answer_id),
            array('%d', '%s'),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success(array('message' => __('베스트 답변으로 선정되었습니다.', 'lectus-class-system')));
        } else {
            wp_send_json_error(array('message' => __('베스트 답변 선정에 실패했습니다.', 'lectus-class-system')));
        }
    }
    
    /**
     * AJAX handler for deleting Q&A
     */
    public static function ajax_delete_qa() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-instructor-nonce')) {
            wp_send_json_error(array('message' => __('보안 검증 실패', 'lectus-class-system')));
            return;
        }
        
        // Check permission
        if (!current_user_can('delete_qa')) {
            wp_send_json_error(array('message' => __('권한이 없습니다.', 'lectus-class-system')));
            return;
        }
        
        $question_id = isset($_POST['question_id']) ? intval($_POST['question_id']) : 0;
        
        if (!$question_id) {
            wp_send_json_error(array('message' => __('잘못된 요청입니다.', 'lectus-class-system')));
            return;
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_qa';
        
        // Delete answers first
        $wpdb->delete($table, array('parent_id' => $question_id), array('%d'));
        
        // Delete question
        $result = $wpdb->delete($table, array('id' => $question_id), array('%d'));
        
        if ($result) {
            wp_send_json_success(array('message' => __('삭제되었습니다.', 'lectus-class-system')));
        } else {
            wp_send_json_error(array('message' => __('삭제에 실패했습니다.', 'lectus-class-system')));
        }
    }
}