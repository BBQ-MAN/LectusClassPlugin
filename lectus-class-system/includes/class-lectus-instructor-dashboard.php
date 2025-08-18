<?php
/**
 * Instructor Dashboard for Lectus Class System
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Instructor_Dashboard {
    
    public static function init() {
        // Add instructor dashboard menu
        add_action('admin_menu', array(__CLASS__, 'add_instructor_menu'), 10);
        
        // Dashboard widgets AJAX handlers
        add_action('wp_ajax_lectus_instructor_get_stats', array(__CLASS__, 'ajax_get_instructor_stats'));
    }
    
    /**
     * Add instructor dashboard menu
     */
    public static function add_instructor_menu() {
        // Check if user is instructor or admin
        if (!current_user_can('manage_qa') && !current_user_can('manage_options')) {
            return;
        }
        
        // Add main dashboard page
        add_submenu_page(
            'lectus-class-system',
            __('강사 대시보드', 'lectus-class-system'),
            __('강사 대시보드', 'lectus-class-system'),
            'manage_qa',
            'lectus-instructor-dashboard',
            array(__CLASS__, 'render_dashboard_page'),
            5
        );
        
        // Add courses management page for instructors
        add_submenu_page(
            'lectus-class-system',
            __('내 강의 관리', 'lectus-class-system'),
            __('내 강의', 'lectus-class-system'),
            'manage_qa',
            'lectus-instructor-courses',
            array(__CLASS__, 'render_courses_page'),
            15
        );
        
        // Add students management page for instructors
        add_submenu_page(
            'lectus-class-system',
            __('수강생 관리', 'lectus-class-system'),
            __('내 수강생', 'lectus-class-system'),
            'manage_qa',
            'lectus-instructor-students',
            array(__CLASS__, 'render_students_page'),
            25
        );
        
        // Add reports page for instructors
        add_submenu_page(
            'lectus-class-system',
            __('강의 리포트', 'lectus-class-system'),
            __('리포트', 'lectus-class-system'),
            'manage_qa',
            'lectus-instructor-reports',
            array(__CLASS__, 'render_reports_page'),
            35
        );
    }
    
    /**
     * Render instructor dashboard page
     */
    public static function render_dashboard_page() {
        $current_user_id = get_current_user_id();
        $is_admin = current_user_can('manage_options');
        
        // Get instructor's courses
        $courses = self::get_instructor_courses($current_user_id, $is_admin);
        
        // Get statistics
        $stats = self::get_instructor_statistics($current_user_id, $is_admin);
        
        ?>
        <div class="wrap">
            <h1><?php _e('강사 대시보드', 'lectus-class-system'); ?></h1>
            
            <!-- Welcome Message -->
            <div class="welcome-panel">
                <div class="welcome-panel-content">
                    <h2><?php printf(__('안녕하세요, %s님!', 'lectus-class-system'), wp_get_current_user()->display_name); ?></h2>
                    <p class="about-description"><?php _e('강의 관리 및 수강생 현황을 한눈에 확인하세요.', 'lectus-class-system'); ?></p>
                    
                    <div class="welcome-panel-column-container">
                        <div class="welcome-panel-column">
                            <h3><?php _e('빠른 작업', 'lectus-class-system'); ?></h3>
                            <ul>
                                <li><a href="<?php echo admin_url('post-new.php?post_type=coursesingle'); ?>" class="button button-primary"><?php _e('새 강의 만들기', 'lectus-class-system'); ?></a></li>
                                <li><a href="<?php echo admin_url('admin.php?page=lectus-instructor-qa'); ?>"><?php _e('Q&A 답변하기', 'lectus-class-system'); ?></a></li>
                                <li><a href="<?php echo admin_url('admin.php?page=lectus-instructor-students'); ?>"><?php _e('수강생 관리', 'lectus-class-system'); ?></a></li>
                            </ul>
                        </div>
                        
                        <div class="welcome-panel-column">
                            <h3><?php _e('도움말', 'lectus-class-system'); ?></h3>
                            <ul>
                                <li><a href="#"><?php _e('강의 제작 가이드', 'lectus-class-system'); ?></a></li>
                                <li><a href="#"><?php _e('수강생과 소통하기', 'lectus-class-system'); ?></a></li>
                                <li><a href="#"><?php _e('강의 평가 관리', 'lectus-class-system'); ?></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Dashboard -->
            <div id="dashboard-widgets-wrap">
                <div id="dashboard-widgets" class="metabox-holder">
                    <div class="postbox-container">
                        <div class="meta-box-sortables">
                            
                            <!-- Course Statistics -->
                            <div class="postbox">
                                <h2 class="hndle"><span><?php _e('강의 현황', 'lectus-class-system'); ?></span></h2>
                                <div class="inside">
                                    <div class="main">
                                        <ul>
                                            <li><?php printf(__('전체 강의: <strong>%d</strong>개', 'lectus-class-system'), $stats['total_courses']); ?></li>
                                            <li><?php printf(__('활성 강의: <strong>%d</strong>개', 'lectus-class-system'), $stats['active_courses']); ?></li>
                                            <li><?php printf(__('총 레슨: <strong>%d</strong>개', 'lectus-class-system'), $stats['total_lessons']); ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Student Statistics -->
                            <div class="postbox">
                                <h2 class="hndle"><span><?php _e('수강생 현황', 'lectus-class-system'); ?></span></h2>
                                <div class="inside">
                                    <div class="main">
                                        <ul>
                                            <li><?php printf(__('전체 수강생: <strong>%d</strong>명', 'lectus-class-system'), $stats['total_students']); ?></li>
                                            <li><?php printf(__('활성 수강생: <strong>%d</strong>명', 'lectus-class-system'), $stats['active_students']); ?></li>
                                            <li><?php printf(__('이번 달 신규: <strong>%d</strong>명', 'lectus-class-system'), $stats['new_students_this_month']); ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Q&A Statistics -->
                            <div class="postbox">
                                <h2 class="hndle"><span><?php _e('Q&A 현황', 'lectus-class-system'); ?></span></h2>
                                <div class="inside">
                                    <div class="main">
                                        <ul>
                                            <li><?php printf(__('미답변 질문: <strong style="color: #d63638;">%d</strong>개', 'lectus-class-system'), $stats['unanswered_questions']); ?></li>
                                            <li><?php printf(__('전체 질문: <strong>%d</strong>개', 'lectus-class-system'), $stats['total_questions']); ?></li>
                                            <li><?php printf(__('답변률: <strong>%.1f%%</strong>', 'lectus-class-system'), $stats['answer_rate']); ?></li>
                                        </ul>
                                        <?php if ($stats['unanswered_questions'] > 0): ?>
                                            <p><a href="<?php echo admin_url('admin.php?page=lectus-instructor-qa&status=unanswered'); ?>" class="button"><?php _e('미답변 질문 보기', 'lectus-class-system'); ?></a></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Recent Activities -->
                            <div class="postbox">
                                <h2 class="hndle"><span><?php _e('최근 활동', 'lectus-class-system'); ?></span></h2>
                                <div class="inside">
                                    <div class="main">
                                        <?php self::render_recent_activities($current_user_id, $is_admin); ?>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render courses management page
     */
    public static function render_courses_page() {
        $current_user_id = get_current_user_id();
        $is_admin = current_user_can('manage_options');
        
        // Get instructor's courses
        $courses = self::get_instructor_courses($current_user_id, $is_admin);
        
        ?>
        <div class="wrap">
            <h1><?php _e('내 강의 관리', 'lectus-class-system'); ?>
                <a href="<?php echo admin_url('post-new.php?post_type=coursesingle'); ?>" class="page-title-action"><?php _e('새 강의 추가', 'lectus-class-system'); ?></a>
            </h1>
            
            <?php if (empty($courses)): ?>
                <div class="notice notice-info">
                    <p><?php _e('아직 담당 강의가 없습니다.', 'lectus-class-system'); ?></p>
                </div>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('강의명', 'lectus-class-system'); ?></th>
                            <th><?php _e('수강생 수', 'lectus-class-system'); ?></th>
                            <th><?php _e('레슨 수', 'lectus-class-system'); ?></th>
                            <th><?php _e('상태', 'lectus-class-system'); ?></th>
                            <th><?php _e('작업', 'lectus-class-system'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): 
                            $student_count = self::get_course_student_count($course->ID);
                            $lesson_count = self::get_course_lesson_count($course->ID);
                        ?>
                            <tr>
                                <td>
                                    <strong><a href="<?php echo get_edit_post_link($course->ID); ?>"><?php echo esc_html($course->post_title); ?></a></strong>
                                </td>
                                <td><?php echo $student_count; ?></td>
                                <td><?php echo $lesson_count; ?></td>
                                <td><?php echo get_post_status_object($course->post_status)->label; ?></td>
                                <td>
                                    <a href="<?php echo get_edit_post_link($course->ID); ?>"><?php _e('편집', 'lectus-class-system'); ?></a> |
                                    <a href="<?php echo get_permalink($course->ID); ?>" target="_blank"><?php _e('보기', 'lectus-class-system'); ?></a> |
                                    <a href="<?php echo admin_url('admin.php?page=lectus-instructor-students&course_id=' . $course->ID); ?>"><?php _e('수강생', 'lectus-class-system'); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Render students management page
     */
    public static function render_students_page() {
        global $wpdb;
        $current_user_id = get_current_user_id();
        $is_admin = current_user_can('manage_options');
        
        // Get instructor's courses
        $courses = self::get_instructor_courses($current_user_id, $is_admin);
        
        if (empty($courses)) {
            ?>
            <div class="wrap">
                <h1><?php _e('수강생 관리', 'lectus-class-system'); ?></h1>
                <div class="notice notice-info">
                    <p><?php _e('담당 강의가 없습니다.', 'lectus-class-system'); ?></p>
                </div>
            </div>
            <?php
            return;
        }
        
        // Get course filter
        $course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
        
        // Get students
        $students = self::get_instructor_students($current_user_id, $course_id, $is_admin);
        
        ?>
        <div class="wrap">
            <h1><?php _e('수강생 관리', 'lectus-class-system'); ?></h1>
            
            <!-- Filter -->
            <div class="tablenav top">
                <form method="get">
                    <input type="hidden" name="page" value="lectus-instructor-students">
                    <select name="course_id" onchange="this.form.submit()">
                        <option value=""><?php _e('모든 강의', 'lectus-class-system'); ?></option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course->ID; ?>" <?php selected($course_id, $course->ID); ?>>
                                <?php echo esc_html($course->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            
            <?php if (empty($students)): ?>
                <div class="notice notice-info">
                    <p><?php _e('수강생이 없습니다.', 'lectus-class-system'); ?></p>
                </div>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('이름', 'lectus-class-system'); ?></th>
                            <th><?php _e('이메일', 'lectus-class-system'); ?></th>
                            <th><?php _e('강의', 'lectus-class-system'); ?></th>
                            <th><?php _e('진도율', 'lectus-class-system'); ?></th>
                            <th><?php _e('등록일', 'lectus-class-system'); ?></th>
                            <th><?php _e('상태', 'lectus-class-system'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo esc_html($student->display_name); ?></td>
                                <td><?php echo esc_html($student->user_email); ?></td>
                                <td><?php echo esc_html($student->course_title); ?></td>
                                <td>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $student->progress; ?>%"></div>
                                        <span><?php echo $student->progress; ?>%</span>
                                    </div>
                                </td>
                                <td><?php echo date_i18n(get_option('date_format'), strtotime($student->enrolled_at)); ?></td>
                                <td>
                                    <?php
                                    $status_class = $student->status == 'active' ? 'success' : 'warning';
                                    $status_text = $student->status == 'active' ? __('활성', 'lectus-class-system') : __('만료', 'lectus-class-system');
                                    ?>
                                    <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <style>
            .progress-bar {
                background: #f0f0f0;
                border-radius: 3px;
                height: 20px;
                position: relative;
                overflow: hidden;
            }
            .progress-fill {
                background: #2271b1;
                height: 100%;
                transition: width 0.3s;
            }
            .progress-bar span {
                position: absolute;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
                font-size: 11px;
                font-weight: bold;
            }
            .badge {
                padding: 2px 8px;
                border-radius: 3px;
                font-size: 12px;
            }
            .badge-success {
                background: #00a32a;
                color: white;
            }
            .badge-warning {
                background: #dba617;
                color: white;
            }
        </style>
        <?php
    }
    
    /**
     * Render reports page
     */
    public static function render_reports_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('강의 리포트', 'lectus-class-system'); ?></h1>
            <p><?php _e('강의 성과 및 통계를 확인하세요.', 'lectus-class-system'); ?></p>
            
            <!-- Reports will be implemented here -->
            <div class="notice notice-info">
                <p><?php _e('리포트 기능은 준비 중입니다.', 'lectus-class-system'); ?></p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get instructor courses
     */
    private static function get_instructor_courses($user_id, $is_admin = false) {
        if ($is_admin) {
            return get_posts(array(
                'post_type' => 'coursesingle',
                'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC'
            ));
        }
        
        return get_posts(array(
            'post_type' => 'coursesingle',
            'meta_key' => '_course_instructor_id',
            'meta_value' => $user_id,
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));
    }
    
    /**
     * Get instructor statistics
     */
    private static function get_instructor_statistics($user_id, $is_admin = false) {
        global $wpdb;
        
        $courses = self::get_instructor_courses($user_id, $is_admin);
        $course_ids = wp_list_pluck($courses, 'ID');
        
        $stats = array(
            'total_courses' => count($courses),
            'active_courses' => 0,
            'total_lessons' => 0,
            'total_students' => 0,
            'active_students' => 0,
            'new_students_this_month' => 0,
            'total_questions' => 0,
            'unanswered_questions' => 0,
            'answer_rate' => 0
        );
        
        if (empty($course_ids)) {
            return $stats;
        }
        
        // Count active courses
        foreach ($courses as $course) {
            if ($course->post_status == 'publish') {
                $stats['active_courses']++;
            }
        }
        
        // Count lessons
        $stats['total_lessons'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'lesson'
            AND pm.meta_key = '_lesson_course_id'
            AND pm.meta_value IN (" . implode(',', array_fill(0, count($course_ids), '%d')) . ")",
            ...$course_ids
        ));
        
        // Count students
        $table_name = $wpdb->prefix . 'lectus_enrollment';
        $stats['total_students'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT user_id) FROM {$table_name}
            WHERE course_id IN (" . implode(',', array_fill(0, count($course_ids), '%d')) . ")",
            ...$course_ids
        ));
        
        // Count active students
        $stats['active_students'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT user_id) FROM {$table_name}
            WHERE course_id IN (" . implode(',', array_fill(0, count($course_ids), '%d')) . ")
            AND status = 'active'",
            ...$course_ids
        ));
        
        // Count new students this month
        $first_day = date('Y-m-01');
        $stats['new_students_this_month'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT user_id) FROM {$table_name}
            WHERE course_id IN (" . implode(',', array_fill(0, count($course_ids), '%d')) . ")
            AND enrolled_at >= %s",
            ...array_merge($course_ids, array($first_day))
        ));
        
        // Count Q&A
        $qa_table = $wpdb->prefix . 'lectus_qa_questions';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$qa_table}'") == $qa_table) {
            $stats['total_questions'] = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$qa_table}
                WHERE course_id IN (" . implode(',', array_fill(0, count($course_ids), '%d')) . ")",
                ...$course_ids
            ));
            
            $stats['unanswered_questions'] = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$qa_table}
                WHERE course_id IN (" . implode(',', array_fill(0, count($course_ids), '%d')) . ")
                AND status = 'pending'",
                ...$course_ids
            ));
            
            if ($stats['total_questions'] > 0) {
                $stats['answer_rate'] = (($stats['total_questions'] - $stats['unanswered_questions']) / $stats['total_questions']) * 100;
            }
        }
        
        return $stats;
    }
    
    /**
     * Get course student count
     */
    private static function get_course_student_count($course_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'lectus_enrollment';
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT user_id) FROM {$table_name}
            WHERE course_id = %d AND status = 'active'",
            $course_id
        ));
    }
    
    /**
     * Get course lesson count
     */
    private static function get_course_lesson_count($course_id) {
        global $wpdb;
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'lesson'
            AND p.post_status = 'publish'
            AND pm.meta_key = '_lesson_course_id'
            AND pm.meta_value = %d",
            $course_id
        ));
    }
    
    /**
     * Get instructor students
     */
    private static function get_instructor_students($user_id, $course_id = 0, $is_admin = false) {
        global $wpdb;
        
        $courses = self::get_instructor_courses($user_id, $is_admin);
        $course_ids = wp_list_pluck($courses, 'ID');
        
        if (empty($course_ids)) {
            return array();
        }
        
        $table_enrollment = $wpdb->prefix . 'lectus_enrollment';
        $table_progress = $wpdb->prefix . 'lectus_progress';
        
        // Build query
        $query = "SELECT DISTINCT 
            u.ID as user_id,
            u.display_name,
            u.user_email,
            e.course_id,
            p.post_title as course_title,
            e.enrolled_at,
            e.status,
            COALESCE(
                (SELECT AVG(progress) FROM {$table_progress} 
                WHERE user_id = u.ID AND course_id = e.course_id), 0
            ) as progress
            FROM {$wpdb->users} u
            INNER JOIN {$table_enrollment} e ON u.ID = e.user_id
            INNER JOIN {$wpdb->posts} p ON e.course_id = p.ID
            WHERE 1=1";
        
        $query_params = array();
        
        if ($course_id) {
            $query .= " AND e.course_id = %d";
            $query_params[] = $course_id;
        } else {
            $placeholders = implode(',', array_fill(0, count($course_ids), '%d'));
            $query .= " AND e.course_id IN ({$placeholders})";
            $query_params = array_merge($query_params, $course_ids);
        }
        
        $query .= " ORDER BY e.enrolled_at DESC";
        
        if (!empty($query_params)) {
            $query = $wpdb->prepare($query, $query_params);
        }
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Render recent activities
     */
    private static function render_recent_activities($user_id, $is_admin = false) {
        global $wpdb;
        
        $courses = self::get_instructor_courses($user_id, $is_admin);
        $course_ids = wp_list_pluck($courses, 'ID');
        
        if (empty($course_ids)) {
            echo '<p>' . __('활동 내역이 없습니다.', 'lectus-class-system') . '</p>';
            return;
        }
        
        // Get recent enrollments
        $table_enrollment = $wpdb->prefix . 'lectus_enrollment';
        $placeholders = implode(',', array_fill(0, count($course_ids), '%d'));
        
        $recent_enrollments = $wpdb->get_results($wpdb->prepare(
            "SELECT e.*, u.display_name, p.post_title as course_title
            FROM {$table_enrollment} e
            INNER JOIN {$wpdb->users} u ON e.user_id = u.ID
            INNER JOIN {$wpdb->posts} p ON e.course_id = p.ID
            WHERE e.course_id IN ({$placeholders})
            ORDER BY e.enrolled_at DESC
            LIMIT 5",
            ...$course_ids
        ));
        
        if ($recent_enrollments) {
            echo '<h4>' . __('최근 등록 수강생', 'lectus-class-system') . '</h4>';
            echo '<ul>';
            foreach ($recent_enrollments as $enrollment) {
                $time_diff = human_time_diff(strtotime($enrollment->enrolled_at), current_time('timestamp'));
                printf(
                    '<li>%s님이 <strong>%s</strong> 강의를 수강 시작 (%s 전)</li>',
                    esc_html($enrollment->display_name),
                    esc_html($enrollment->course_title),
                    $time_diff
                );
            }
            echo '</ul>';
        } else {
            echo '<p>' . __('최근 활동이 없습니다.', 'lectus-class-system') . '</p>';
        }
    }
    
    /**
     * AJAX handler to get instructor statistics
     */
    public static function ajax_get_instructor_stats() {
        check_ajax_referer('lectus_instructor_nonce', 'nonce');
        
        if (!current_user_can('manage_qa')) {
            wp_die(-1);
        }
        
        $user_id = get_current_user_id();
        $is_admin = current_user_can('manage_options');
        
        $stats = self::get_instructor_statistics($user_id, $is_admin);
        
        wp_send_json_success($stats);
    }
}