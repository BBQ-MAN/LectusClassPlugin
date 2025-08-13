<?php
/**
 * Admin Dashboard for Lectus Class System
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Admin_Dashboard {
    
    public static function render_dashboard() {
        ?>
        <div class="wrap">
            <h1><?php _e('Lectus Class System 대시보드', 'lectus-class-system'); ?></h1>
            
            <div class="lectus-dashboard">
                <?php self::render_stats_boxes(); ?>
                <?php self::render_recent_activity(); ?>
                <?php self::render_quick_actions(); ?>
            </div>
        </div>
        
        <style>
        .lectus-dashboard {
            margin-top: 20px;
        }
        .lectus-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .lectus-stat-box {
            background: white;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            box-shadow: 0 1px 1px rgba(0,0,0,0.04);
        }
        .lectus-stat-box h3 {
            margin: 0 0 10px 0;
            color: #23282d;
            font-size: 14px;
            font-weight: 600;
        }
        .lectus-stat-box .stat-number {
            font-size: 32px;
            font-weight: 300;
            color: #0073aa;
            margin: 10px 0;
        }
        .lectus-stat-box .stat-description {
            color: #666;
            font-size: 13px;
        }
        .lectus-activity-box {
            background: white;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .lectus-activity-box h2 {
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .activity-item {
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .activity-time {
            color: #999;
            font-size: 12px;
        }
        .quick-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        .quick-actions .button {
            flex: 0 0 auto;
        }
        </style>
        <?php
    }
    
    private static function render_stats_boxes() {
        global $wpdb;
        
        // Get statistics
        $total_packages = wp_count_posts('coursepackage')->publish;
        $total_courses = wp_count_posts('coursesingle')->publish;
        $total_lessons = wp_count_posts('lesson')->publish;
        
        $enrollment_table = $wpdb->prefix . 'lectus_enrollment';
        $total_enrollments = $wpdb->get_var(
            "SELECT COUNT(*) FROM $enrollment_table WHERE status = 'active'"
        );
        
        $certificate_table = $wpdb->prefix . 'lectus_certificates';
        $total_certificates = $wpdb->get_var(
            "SELECT COUNT(*) FROM $certificate_table"
        );
        
        // Get this month's enrollments
        $month_enrollments = $wpdb->get_var(
            "SELECT COUNT(*) FROM $enrollment_table 
             WHERE status = 'active' 
             AND MONTH(enrolled_at) = MONTH(CURRENT_DATE())
             AND YEAR(enrolled_at) = YEAR(CURRENT_DATE())"
        );
        
        // Get active students
        $active_students = $wpdb->get_var(
            "SELECT COUNT(DISTINCT user_id) FROM $enrollment_table WHERE status = 'active'"
        );
        
        // Calculate completion rate
        $completed_courses = $wpdb->get_var(
            "SELECT COUNT(*) FROM $enrollment_table WHERE status = 'completed'"
        );
        $completion_rate = $total_enrollments > 0 ? round(($completed_courses / $total_enrollments) * 100) : 0;
        
        ?>
        <div class="lectus-stats-grid">
            <div class="lectus-stat-box">
                <h3><?php _e('총 패키지강의', 'lectus-class-system'); ?></h3>
                <div class="stat-number"><?php echo number_format($total_packages); ?></div>
                <div class="stat-description"><?php _e('등록된 패키지강의 수', 'lectus-class-system'); ?></div>
            </div>
            
            <div class="lectus-stat-box">
                <h3><?php _e('총 단과강의', 'lectus-class-system'); ?></h3>
                <div class="stat-number"><?php echo number_format($total_courses); ?></div>
                <div class="stat-description"><?php _e('등록된 단과강의 수', 'lectus-class-system'); ?></div>
            </div>
            
            <div class="lectus-stat-box">
                <h3><?php _e('총 레슨', 'lectus-class-system'); ?></h3>
                <div class="stat-number"><?php echo number_format($total_lessons); ?></div>
                <div class="stat-description"><?php _e('등록된 레슨 수', 'lectus-class-system'); ?></div>
            </div>
            
            <div class="lectus-stat-box">
                <h3><?php _e('활성 수강생', 'lectus-class-system'); ?></h3>
                <div class="stat-number"><?php echo number_format($active_students); ?></div>
                <div class="stat-description"><?php _e('현재 수강 중인 학생 수', 'lectus-class-system'); ?></div>
            </div>
            
            <div class="lectus-stat-box">
                <h3><?php _e('이달 신규 등록', 'lectus-class-system'); ?></h3>
                <div class="stat-number"><?php echo number_format($month_enrollments); ?></div>
                <div class="stat-description"><?php echo date_i18n('Y년 n월'); ?></div>
            </div>
            
            <div class="lectus-stat-box">
                <h3><?php _e('수료증 발급', 'lectus-class-system'); ?></h3>
                <div class="stat-number"><?php echo number_format($total_certificates); ?></div>
                <div class="stat-description"><?php _e('총 발급된 수료증', 'lectus-class-system'); ?></div>
            </div>
            
            <div class="lectus-stat-box">
                <h3><?php _e('활성 등록', 'lectus-class-system'); ?></h3>
                <div class="stat-number"><?php echo number_format($total_enrollments); ?></div>
                <div class="stat-description"><?php _e('현재 활성 수강 등록', 'lectus-class-system'); ?></div>
            </div>
            
            <div class="lectus-stat-box">
                <h3><?php _e('완료율', 'lectus-class-system'); ?></h3>
                <div class="stat-number"><?php echo $completion_rate; ?>%</div>
                <div class="stat-description"><?php _e('평균 강의 완료율', 'lectus-class-system'); ?></div>
            </div>
        </div>
        <?php
    }
    
    private static function render_recent_activity() {
        global $wpdb;
        
        // Get recent enrollments
        $enrollment_table = $wpdb->prefix . 'lectus_enrollment';
        $recent_enrollments = $wpdb->get_results(
            "SELECT e.*, u.display_name, p.post_title as course_title
             FROM $enrollment_table e
             LEFT JOIN {$wpdb->users} u ON e.user_id = u.ID
             LEFT JOIN {$wpdb->posts} p ON e.course_id = p.ID
             ORDER BY e.enrolled_at DESC
             LIMIT 10"
        );
        
        // Get recent certificates
        $certificate_table = $wpdb->prefix . 'lectus_certificates';
        $recent_certificates = $wpdb->get_results(
            "SELECT c.*, u.display_name, p.post_title as course_title
             FROM $certificate_table c
             LEFT JOIN {$wpdb->users} u ON c.user_id = u.ID
             LEFT JOIN {$wpdb->posts} p ON c.course_id = p.ID
             ORDER BY c.issued_at DESC
             LIMIT 5"
        );
        
        ?>
        <div class="lectus-activity-box">
            <h2><?php _e('최근 활동', 'lectus-class-system'); ?></h2>
            
            <div class="activity-list">
                <?php if (!empty($recent_enrollments)): ?>
                    <h3><?php _e('최근 수강 등록', 'lectus-class-system'); ?></h3>
                    <?php foreach ($recent_enrollments as $enrollment): ?>
                        <div class="activity-item">
                            <strong><?php echo esc_html($enrollment->display_name); ?></strong>
                            <?php _e('님이', 'lectus-class-system'); ?>
                            <strong><?php echo esc_html($enrollment->course_title); ?></strong>
                            <?php _e('강의에 등록했습니다', 'lectus-class-system'); ?>
                            <div class="activity-time">
                                <?php echo human_time_diff(strtotime($enrollment->enrolled_at), current_time('timestamp')); ?>
                                <?php _e('전', 'lectus-class-system'); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php if (!empty($recent_certificates)): ?>
                    <h3><?php _e('최근 수료증 발급', 'lectus-class-system'); ?></h3>
                    <?php foreach ($recent_certificates as $certificate): ?>
                        <div class="activity-item">
                            <strong><?php echo esc_html($certificate->display_name); ?></strong>
                            <?php _e('님이', 'lectus-class-system'); ?>
                            <strong><?php echo esc_html($certificate->course_title); ?></strong>
                            <?php _e('과정을 수료했습니다', 'lectus-class-system'); ?>
                            <div class="activity-time">
                                <?php echo human_time_diff(strtotime($certificate->issued_at), current_time('timestamp')); ?>
                                <?php _e('전', 'lectus-class-system'); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    private static function render_quick_actions() {
        ?>
        <div class="lectus-activity-box">
            <h2><?php _e('빠른 작업', 'lectus-class-system'); ?></h2>
            
            <div class="quick-actions">
                <a href="<?php echo admin_url('post-new.php?post_type=coursepackage'); ?>" class="button button-primary">
                    <?php _e('새 패키지강의 추가', 'lectus-class-system'); ?>
                </a>
                <a href="<?php echo admin_url('post-new.php?post_type=coursesingle'); ?>" class="button button-primary">
                    <?php _e('새 단과강의 추가', 'lectus-class-system'); ?>
                </a>
                <a href="<?php echo admin_url('post-new.php?post_type=lesson'); ?>" class="button">
                    <?php _e('새 레슨 추가', 'lectus-class-system'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=lectus-students'); ?>" class="button">
                    <?php _e('수강생 관리', 'lectus-class-system'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=lectus-certificates'); ?>" class="button">
                    <?php _e('수료증 관리', 'lectus-class-system'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=lectus-reports'); ?>" class="button">
                    <?php _e('보고서 보기', 'lectus-class-system'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=lectus-settings'); ?>" class="button">
                    <?php _e('설정', 'lectus-class-system'); ?>
                </a>
            </div>
        </div>
        <?php
    }
}