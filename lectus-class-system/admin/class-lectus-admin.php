<?php
/**
 * Admin functionality for Lectus Class System
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Admin {
    
    public static function init() {
        // Admin notices
        add_action('admin_notices', array(__CLASS__, 'admin_notices'));
        
        // Bulk actions
        add_filter('bulk_actions-users', array(__CLASS__, 'add_bulk_actions'));
        add_filter('handle_bulk_actions-users', array(__CLASS__, 'handle_bulk_actions'), 10, 3);
    }
    
    public static function render_students_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('수강생 관리', 'lectus-class-system'); ?></h1>
            
            <?php
            // Get filter parameters
            $course_filter = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
            $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
            
            // Get courses for filter dropdown
            $courses = get_posts(array(
                'post_type' => 'coursesingle',
                'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC'
            ));
            ?>
            
            <div class="tablenav top">
                <form method="get" action="">
                    <input type="hidden" name="page" value="lectus-students" />
                    
                    <select name="course_id">
                        <option value=""><?php _e('모든 강의', 'lectus-class-system'); ?></option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course->ID; ?>" <?php selected($course_filter, $course->ID); ?>>
                                <?php echo esc_html($course->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select name="status">
                        <option value=""><?php _e('모든 상태', 'lectus-class-system'); ?></option>
                        <option value="active" <?php selected($status_filter, 'active'); ?>><?php _e('활성', 'lectus-class-system'); ?></option>
                        <option value="paused" <?php selected($status_filter, 'paused'); ?>><?php _e('일시정지', 'lectus-class-system'); ?></option>
                        <option value="expired" <?php selected($status_filter, 'expired'); ?>><?php _e('만료', 'lectus-class-system'); ?></option>
                        <option value="cancelled" <?php selected($status_filter, 'cancelled'); ?>><?php _e('취소', 'lectus-class-system'); ?></option>
                    </select>
                    
                    <input type="submit" class="button" value="<?php _e('필터', 'lectus-class-system'); ?>" />
                </form>
                
                <div class="alignright">
                    <a href="#" class="button" onclick="lectusExportStudents(); return false;">
                        <?php _e('엑셀 내보내기', 'lectus-class-system'); ?>
                    </a>
                </div>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('수강생', 'lectus-class-system'); ?></th>
                        <th><?php _e('이메일', 'lectus-class-system'); ?></th>
                        <th><?php _e('등록 강의', 'lectus-class-system'); ?></th>
                        <th><?php _e('진도', 'lectus-class-system'); ?></th>
                        <th><?php _e('상태', 'lectus-class-system'); ?></th>
                        <th><?php _e('등록일', 'lectus-class-system'); ?></th>
                        <th><?php _e('만료일', 'lectus-class-system'); ?></th>
                        <th><?php _e('작업', 'lectus-class-system'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    global $wpdb;
                    $enrollment_table = $wpdb->prefix . 'lectus_enrollment';
                    
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
                    
                    foreach ($enrollments as $enrollment):
                        $course = get_post($enrollment->course_id);
                        if (!$course) continue;
                        
                        $progress = Lectus_Progress::get_course_progress($enrollment->user_id, $enrollment->course_id);
                    ?>
                        <tr>
                            <td>
                                <strong>
                                    <a href="<?php echo get_edit_user_link($enrollment->user_id); ?>">
                                        <?php echo esc_html($enrollment->display_name); ?>
                                    </a>
                                </strong>
                            </td>
                            <td><?php echo esc_html($enrollment->user_email); ?></td>
                            <td>
                                <a href="<?php echo get_edit_post_link($course->ID); ?>">
                                    <?php echo esc_html($course->post_title); ?>
                                </a>
                            </td>
                            <td>
                                <div style="background: #f0f0f0; width: 100px; height: 20px; border-radius: 10px; overflow: hidden;">
                                    <div style="background: #4CAF50; height: 100%; width: <?php echo $progress; ?>%;"></div>
                                </div>
                                <span><?php echo $progress; ?>%</span>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $enrollment->status; ?>">
                                    <?php echo Lectus_Enrollment::get_status_label($enrollment->status); ?>
                                </span>
                            </td>
                            <td><?php echo date_i18n(get_option('date_format'), strtotime($enrollment->enrolled_at)); ?></td>
                            <td>
                                <?php 
                                echo $enrollment->expires_at ? 
                                    date_i18n(get_option('date_format'), strtotime($enrollment->expires_at)) : 
                                    __('무제한', 'lectus-class-system');
                                ?>
                            </td>
                            <td>
                                <button class="button button-small" onclick="lectusManageStudent(<?php echo $enrollment->user_id; ?>, <?php echo $enrollment->course_id; ?>)">
                                    <?php _e('관리', 'lectus-class-system'); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <script>
        function lectusManageStudent(userId, courseId) {
            // Student management functionality
            if(confirm('수강생 관리 페이지로 이동하시겠습니까?')) {
                window.location.href = 'admin.php?page=lectus-students&user_id=' + userId + '&course_id=' + courseId;
            }
        }
        
        function lectusExportStudents() {
            // Export functionality
            if(confirm('수강생 데이터를 CSV 파일로 내보내겠습니까?')) {
                window.location.href = 'admin.php?page=lectus-admin&action=export&format=csv';
            }
        }
        </script>
        
        <style>
        .status-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-active { background: #4CAF50; color: white; }
        .status-paused { background: #FF9800; color: white; }
        .status-expired { background: #9E9E9E; color: white; }
        .status-cancelled { background: #f44336; color: white; }
        </style>
        <?php
    }
    
    public static function render_certificates_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('수료증 관리', 'lectus-class-system'); ?></h1>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('수료증 번호', 'lectus-class-system'); ?></th>
                        <th><?php _e('수강생', 'lectus-class-system'); ?></th>
                        <th><?php _e('강의', 'lectus-class-system'); ?></th>
                        <th><?php _e('발급일', 'lectus-class-system'); ?></th>
                        <th><?php _e('작업', 'lectus-class-system'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    global $wpdb;
                    $table = $wpdb->prefix . 'lectus_certificates';
                    
                    $certificates = $wpdb->get_results(
                        "SELECT c.*, u.display_name 
                         FROM $table c
                         LEFT JOIN {$wpdb->users} u ON c.user_id = u.ID
                         ORDER BY c.issued_at DESC
                         LIMIT 100"
                    );
                    
                    foreach ($certificates as $certificate):
                        $course = get_post($certificate->course_id);
                        if (!$course) continue;
                    ?>
                        <tr>
                            <td>
                                <code><?php echo esc_html($certificate->certificate_number); ?></code>
                            </td>
                            <td>
                                <a href="<?php echo get_edit_user_link($certificate->user_id); ?>">
                                    <?php echo esc_html($certificate->display_name); ?>
                                </a>
                            </td>
                            <td><?php echo esc_html($course->post_title); ?></td>
                            <td><?php echo date_i18n(get_option('date_format'), strtotime($certificate->issued_at)); ?></td>
                            <td>
                                <a href="<?php echo Lectus_Certificate::get_certificate_url($certificate->id); ?>" 
                                   class="button button-small" target="_blank">
                                    <?php _e('보기', 'lectus-class-system'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    public static function admin_notices() {
        // Check for pending actions
        $screen = get_current_screen();
        
        if ($screen->id === 'dashboard') {
            global $wpdb;
            $enrollment_table = $wpdb->prefix . 'lectus_enrollment';
            
            // Check for expiring enrollments
            $expiring_count = $wpdb->get_var(
                "SELECT COUNT(*) FROM $enrollment_table 
                 WHERE status = 'active' 
                 AND expires_at IS NOT NULL 
                 AND expires_at <= DATE_ADD(NOW(), INTERVAL 7 DAY)"
            );
            
            if ($expiring_count > 0) {
                ?>
                <div class="notice notice-warning">
                    <p>
                        <?php 
                        printf(
                            __('%d개의 수강 등록이 7일 이내에 만료됩니다.', 'lectus-class-system'),
                            $expiring_count
                        );
                        ?>
                        <a href="<?php echo admin_url('admin.php?page=lectus-students&status=active'); ?>">
                            <?php _e('확인하기', 'lectus-class-system'); ?>
                        </a>
                    </p>
                </div>
                <?php
            }
        }
    }
    
    public static function add_bulk_actions($actions) {
        $actions['enroll_course'] = __('강의에 등록', 'lectus-class-system');
        $actions['unenroll_course'] = __('강의 등록 취소', 'lectus-class-system');
        return $actions;
    }
    
    public static function handle_bulk_actions($redirect_to, $action, $user_ids) {
        if ($action === 'enroll_course' || $action === 'unenroll_course') {
            // Bulk enrollment implementation placeholder
            // This would require course selection UI and enrollment logic
            $redirect_to = add_query_arg('bulk_action_result', 'bulk_enrollment_pending', $redirect_to);
        }
        
        return $redirect_to;
    }
}