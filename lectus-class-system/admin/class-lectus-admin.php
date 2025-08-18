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
        
        // Enqueue admin scripts and styles
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_assets'));
    }
    
    public static function enqueue_admin_assets($hook) {
        // Debug: Log the hook to see what we're getting
        error_log('Hook passed to enqueue_admin_assets: ' . $hook);
        
        // Check multiple possible hook variations for the students page
        $valid_hooks = [
            'lectus-class-system_page_lectus-students',
            'toplevel_page_lectus-students',
            'lectus-students',
            'admin_page_lectus-students'
        ];
        
        // Also check if we're on the students page by checking the page parameter
        $current_page = isset($_GET['page']) ? $_GET['page'] : '';
        
        if (!in_array($hook, $valid_hooks) && $current_page !== 'lectus-students') {
            return;
        }
        
        // Enqueue jQuery first
        wp_enqueue_script('jquery');
        
        // Enqueue enhanced JavaScript with correct path
        wp_enqueue_script(
            'lectus-admin-student-management',
            plugin_dir_url(dirname(__FILE__)) . 'assets/js/admin-student-management.js',
            array('jquery'),
            filemtime(plugin_dir_path(dirname(__FILE__)) . 'assets/js/admin-student-management.js'),
            true
        );
        
        // Localize script with AJAX data
        wp_localize_script('lectus-admin-student-management', 'lectusAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('lectus-ajax-nonce')
        ));
        
        // Enqueue enhanced CSS with correct path
        wp_enqueue_style(
            'lectus-admin-student-management',
            plugin_dir_url(dirname(__FILE__)) . 'assets/css/admin-student-management.css',
            array(),
            filemtime(plugin_dir_path(dirname(__FILE__)) . 'assets/css/admin-student-management.css')
        );
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
            
            <div class="lectus-filters">
                <form method="get" action="" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
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
                
                <button id="lectus-export-btn" class="button">
                    <span class="dashicons dashicons-download" style="margin-top: 3px;"></span>
                    <?php _e('엑셀 내보내기', 'lectus-class-system'); ?>
                </button>
            </div>
            
            <table class="wp-list-table widefat fixed striped lectus-students-table">
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
                        <tr class="lectus-student-row" data-user="<?php echo $enrollment->user_id; ?>" data-course="<?php echo $enrollment->course_id; ?>">
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
                                <div class="progress-bar">
                                    <div class="progress-bar-fill" style="width: <?php echo $progress; ?>%;"></div>
                                    <span class="progress-text"><?php echo $progress; ?>%</span>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $enrollment->status; ?>">
                                    <?php echo Lectus_Enrollment::get_status_label($enrollment->status); ?>
                                </span>
                            </td>
                            <td><?php echo date_i18n(get_option('date_format'), strtotime($enrollment->enrolled_at)); ?></td>
                            <td class="expiry-date">
                                <?php 
                                echo $enrollment->expires_at ? 
                                    date_i18n(get_option('date_format'), strtotime($enrollment->expires_at)) : 
                                    __('무제한', 'lectus-class-system');
                                ?>
                            </td>
                            <td>
                                <button class="button button-small lectus-manage-student" 
                                        data-user-id="<?php echo $enrollment->user_id; ?>" 
                                        data-course-id="<?php echo $enrollment->course_id; ?>"
                                        data-user-name="<?php echo esc_attr($enrollment->display_name); ?>"
                                        data-course-name="<?php echo esc_attr($course->post_title); ?>"
                                        data-status="<?php echo $enrollment->status; ?>"
                                        data-expires="<?php echo $enrollment->expires_at ? date_i18n(get_option('date_format'), strtotime($enrollment->expires_at)) : __('무제한', 'lectus-class-system'); ?>">
                                    <?php _e('관리', 'lectus-class-system'); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Inline Styles for Critical UI -->
        <style>
            /* Critical styles for modal and progress bar */
            .progress-bar {
                background: #f0f0f0;
                height: 24px;
                border-radius: 12px;
                overflow: hidden;
                position: relative;
            }
            .progress-bar-fill {
                background: linear-gradient(90deg, #46b450, #389e41);
                height: 100%;
                transition: width 0.5s ease;
            }
            .progress-text {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                font-size: 12px;
                font-weight: 600;
                color: #333;
                z-index: 1;
            }
            .status-badge {
                display: inline-block;
                padding: 4px 10px;
                border-radius: 12px;
                font-size: 12px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .status-active { background: #d4f4dd; color: #1e7e34; }
            .status-paused { background: #fff3cd; color: #856404; }
            .status-expired { background: #e5e5e5; color: #666; }
            .status-cancelled { background: #f8d7da; color: #721c24; }
            .lectus-modal-header {
                padding: 20px 30px;
                border-bottom: 1px solid #e5e5e5;
                display: flex;
                justify-content: space-between;
                align-items: center;
                background: #f8f9fa;
                border-radius: 8px 8px 0 0;
            }
            .lectus-modal-close {
                background: none;
                border: none;
                font-size: 24px;
                cursor: pointer;
                color: #999;
                width: 30px;
                height: 30px;
            }
            .lectus-modal-body { padding: 30px; }
            .student-info-section {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 30px;
            }
            .action-card {
                background: #fff;
                border: 2px solid #e5e5e5;
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 20px;
            }
            .lectus-btn {
                padding: 8px 16px;
                border: none;
                border-radius: 4px;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            .lectus-btn-primary { background: #007cba; color: #fff; }
            .lectus-btn-success { background: #46b450; color: #fff; }
            .lectus-btn-danger { background: #dc3545; color: #fff; }
            .lectus-btn-warning { background: #ffb900; color: #fff; }
        </style>
        
        <!-- Inline JavaScript for immediate functionality -->
        <script>
        jQuery(document).ready(function($) {
            // Basic modal functionality if enhanced JS doesn't load
            if (typeof StudentManagement === 'undefined') {
                // Basic management button handler
                $('.lectus-manage-student').on('click', function(e) {
                    e.preventDefault();
                    var btn = $(this);
                    
                    // Update modal with data
                    $('#modal-student-name').text(btn.data('user-name'));
                    $('#modal-course-name').text(btn.data('course-name'));
                    $('#modal-expires').text(btn.data('expires'));
                    $('#change-status').val(btn.data('status'));
                    
                    var statusLabels = {
                        'active': '활성',
                        'paused': '일시정지',
                        'expired': '만료',
                        'cancelled': '취소'
                    };
                    $('#modal-current-status')
                        .removeClass()
                        .addClass('status-badge status-' + btn.data('status'))
                        .text(statusLabels[btn.data('status')]);
                    
                    // Store current IDs
                    window.currentUserId = btn.data('user-id');
                    window.currentCourseId = btn.data('course-id');
                    
                    // Show modal
                    $('#lectus-modal-overlay').fadeIn(300);
                });
                
                // Close modal
                $('.lectus-modal-close, #lectus-modal-overlay').on('click', function(e) {
                    if (e.target === this) {
                        $('#lectus-modal-overlay').fadeOut(300);
                    }
                });
                
                // Basic AJAX handlers
                $('#lectus-extend-btn').on('click', function() {
                    var days = $('#extend-days').val();
                    if (!days || days < 1) {
                        alert('올바른 일수를 입력하세요.');
                        return;
                    }
                    if (!confirm('수강 기간을 ' + days + '일 연장하시겠습니까?')) {
                        return;
                    }
                    
                    var btn = $(this);
                    btn.prop('disabled', true);
                    
                    $.ajax({
                        url: ajaxurl,
                        method: 'POST',
                        data: {
                            action: 'lectus_extend_access',
                            user_id: window.currentUserId,
                            course_id: window.currentCourseId,
                            days: days,
                            nonce: '<?php echo wp_create_nonce('lectus-ajax-nonce'); ?>'
                        },
                        success: function(response) {
                            btn.prop('disabled', false);
                            if (response.success) {
                                alert(response.data.message);
                                location.reload();
                            } else {
                                alert(response.data.message || '오류가 발생했습니다.');
                            }
                        },
                        error: function() {
                            btn.prop('disabled', false);
                            alert('오류가 발생했습니다.');
                        }
                    });
                });
                
                $('#lectus-change-status-btn').on('click', function() {
                    var status = $('#change-status').val();
                    if (!confirm('상태를 변경하시겠습니까?')) {
                        return;
                    }
                    
                    var btn = $(this);
                    btn.prop('disabled', true);
                    
                    $.ajax({
                        url: ajaxurl,
                        method: 'POST',
                        data: {
                            action: 'lectus_change_status',
                            user_id: window.currentUserId,
                            course_id: window.currentCourseId,
                            status: status,
                            nonce: '<?php echo wp_create_nonce('lectus-ajax-nonce'); ?>'
                        },
                        success: function(response) {
                            btn.prop('disabled', false);
                            if (response.success) {
                                alert(response.data.message);
                                location.reload();
                            } else {
                                alert(response.data.message || '오류가 발생했습니다.');
                            }
                        },
                        error: function() {
                            btn.prop('disabled', false);
                            alert('오류가 발생했습니다.');
                        }
                    });
                });
                
                $('#lectus-reset-progress-btn').on('click', function() {
                    if (!confirm('정말로 진도를 초기화하시겠습니까?\n\n이 작업은 되돌릴 수 없습니다.')) {
                        return;
                    }
                    
                    var btn = $(this);
                    btn.prop('disabled', true);
                    
                    $.ajax({
                        url: ajaxurl,
                        method: 'POST',
                        data: {
                            action: 'lectus_reset_progress',
                            user_id: window.currentUserId,
                            course_id: window.currentCourseId,
                            nonce: '<?php echo wp_create_nonce('lectus-ajax-nonce'); ?>'
                        },
                        success: function(response) {
                            btn.prop('disabled', false);
                            if (response.success) {
                                alert(response.data.message);
                                location.reload();
                            } else {
                                alert(response.data.message || '오류가 발생했습니다.');
                            }
                        },
                        error: function() {
                            btn.prop('disabled', false);
                            alert('오류가 발생했습니다.');
                        }
                    });
                });
                
                $('#lectus-generate-cert-btn').on('click', function() {
                    if (!confirm('수료증을 발급하시겠습니까?')) {
                        return;
                    }
                    
                    var btn = $(this);
                    btn.prop('disabled', true);
                    
                    $.ajax({
                        url: ajaxurl,
                        method: 'POST',
                        data: {
                            action: 'lectus_generate_certificate',
                            user_id: window.currentUserId,
                            course_id: window.currentCourseId,
                            nonce: '<?php echo wp_create_nonce('lectus-ajax-nonce'); ?>'
                        },
                        success: function(response) {
                            btn.prop('disabled', false);
                            if (response.success) {
                                alert(response.data.message);
                                if (response.data.certificate_url) {
                                    window.open(response.data.certificate_url, '_blank');
                                }
                            } else {
                                alert(response.data.message || '오류가 발생했습니다.');
                            }
                        },
                        error: function() {
                            btn.prop('disabled', false);
                            alert('오류가 발생했습니다.');
                        }
                    });
                });
            }
        });
        </script>
        
        <!-- Student Management Modal -->
        <div id="lectus-modal-overlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.7); z-index:99999;">
            <div class="lectus-modal" style="position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background:#fff; border-radius:8px; width:600px; max-width:90%; max-height:90vh; overflow:auto; box-shadow:0 10px 40px rgba(0,0,0,0.2);">
                <div class="lectus-modal-header">
                    <h2><?php _e('수강생 관리', 'lectus-class-system'); ?></h2>
                    <button class="lectus-modal-close">×</button>
                </div>
                
                <div class="lectus-modal-body">
                    <!-- Student Information -->
                    <div class="student-info-section">
                        <div class="student-info-grid">
                            <div class="info-item">
                                <span class="info-label"><?php _e('수강생', 'lectus-class-system'); ?></span>
                                <span class="info-value" id="modal-student-name"></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><?php _e('강의', 'lectus-class-system'); ?></span>
                                <span class="info-value" id="modal-course-name"></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><?php _e('현재 상태', 'lectus-class-system'); ?></span>
                                <span id="modal-current-status" class="status-badge"></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><?php _e('만료일', 'lectus-class-system'); ?></span>
                                <span class="info-value" id="modal-expires"></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Management Actions -->
                    <div class="management-actions">
                        <!-- Extend Access -->
                        <div class="action-card">
                            <h4>
                                <span class="dashicons dashicons-calendar-alt"></span>
                                <?php _e('수강 기간 연장', 'lectus-class-system'); ?>
                            </h4>
                            <p class="action-description">
                                <?php _e('수강생의 강의 접근 기간을 연장합니다.', 'lectus-class-system'); ?>
                            </p>
                            <div class="action-controls">
                                <input type="number" id="extend-days" min="1" max="365" value="30" placeholder="일수">
                                <button id="lectus-extend-btn" class="lectus-btn lectus-btn-primary">
                                    <?php _e('연장하기', 'lectus-class-system'); ?>
                                    <span class="spinner"></span>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Change Status -->
                        <div class="action-card">
                            <h4>
                                <span class="dashicons dashicons-admin-settings"></span>
                                <?php _e('상태 변경', 'lectus-class-system'); ?>
                            </h4>
                            <p class="action-description">
                                <?php _e('수강 상태를 변경합니다.', 'lectus-class-system'); ?>
                            </p>
                            <div class="action-controls">
                                <select id="change-status">
                                    <option value="active"><?php _e('활성', 'lectus-class-system'); ?></option>
                                    <option value="paused"><?php _e('일시정지', 'lectus-class-system'); ?></option>
                                    <option value="expired"><?php _e('만료', 'lectus-class-system'); ?></option>
                                    <option value="cancelled"><?php _e('취소', 'lectus-class-system'); ?></option>
                                </select>
                                <button id="lectus-change-status-btn" class="lectus-btn lectus-btn-warning">
                                    <?php _e('변경하기', 'lectus-class-system'); ?>
                                    <span class="spinner"></span>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Reset Progress -->
                        <div class="action-card">
                            <h4>
                                <span class="dashicons dashicons-backup"></span>
                                <?php _e('진도 초기화', 'lectus-class-system'); ?>
                            </h4>
                            <p class="action-description" style="color: #dc3545;">
                                <?php _e('주의: 모든 진도 데이터가 초기화되며 복구할 수 없습니다.', 'lectus-class-system'); ?>
                            </p>
                            <div class="action-controls">
                                <button id="lectus-reset-progress-btn" class="lectus-btn lectus-btn-danger">
                                    <?php _e('진도 초기화', 'lectus-class-system'); ?>
                                    <span class="spinner"></span>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Generate Certificate -->
                        <div class="action-card">
                            <h4>
                                <span class="dashicons dashicons-awards"></span>
                                <?php _e('수료증 발급', 'lectus-class-system'); ?>
                            </h4>
                            <p class="action-description">
                                <?php _e('수료증을 생성하고 PDF 파일로 다운로드합니다.', 'lectus-class-system'); ?>
                            </p>
                            <div class="action-controls">
                                <button id="lectus-generate-cert-btn" class="lectus-btn lectus-btn-success">
                                    <?php _e('수료증 생성', 'lectus-class-system'); ?>
                                    <span class="spinner"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
