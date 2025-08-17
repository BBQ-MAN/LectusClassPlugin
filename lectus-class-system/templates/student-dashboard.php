<?php
/**
 * Student Dashboard Template
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!is_user_logged_in()) {
    echo '<p>' . __('로그인이 필요합니다.', 'lectus-class-system') . '</p>';
    return;
}

$user_id = get_current_user_id();
$enrollments = Lectus_Enrollment::get_user_enrollments($user_id, 'active');
$certificates = Lectus_Certificate::get_user_certificates($user_id);
?>

<div class="lectus-student-dashboard">
    <div class="dashboard-header">
        <h2><?php _e('내 학습 대시보드', 'lectus-class-system'); ?></h2>
        <p><?php printf(__('안녕하세요, %s님!', 'lectus-class-system'), wp_get_current_user()->display_name); ?></p>
    </div>
    
    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-number"><?php echo count($enrollments); ?></div>
            <div class="stat-label"><?php _e('수강중인 강의', 'lectus-class-system'); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo count($certificates); ?></div>
            <div class="stat-label"><?php _e('수료증', 'lectus-class-system'); ?></div>
        </div>
        <div class="stat-card">
            <?php
            $total_progress = 0;
            foreach ($enrollments as $enrollment) {
                $total_progress += Lectus_Progress::get_course_progress($user_id, $enrollment->course_id);
            }
            $avg_progress = count($enrollments) > 0 ? round($total_progress / count($enrollments)) : 0;
            ?>
            <div class="stat-number"><?php echo $avg_progress; ?>%</div>
            <div class="stat-label"><?php _e('평균 진도', 'lectus-class-system'); ?></div>
        </div>
    </div>
    
    <div class="dashboard-content">
        <div class="dashboard-section">
            <h3><?php _e('진행중인 강의', 'lectus-class-system'); ?></h3>
            <?php if (empty($enrollments)): ?>
                <p><?php _e('현재 수강중인 강의가 없습니다.', 'lectus-class-system'); ?></p>
                <a href="<?php echo get_post_type_archive_link('coursesingle'); ?>" class="button"><?php _e('강의 둘러보기', 'lectus-class-system'); ?></a>
            <?php else: ?>
                <div class="courses-grid">
                    <?php foreach ($enrollments as $enrollment): 
                        $course = get_post($enrollment->course_id);
                        if (!$course) continue;
                        $progress = Lectus_Progress::get_course_progress($user_id, $enrollment->course_id);
                        $is_completed = Lectus_Progress::is_course_completed($user_id, $enrollment->course_id);
                    ?>
                        <div class="course-card <?php echo $is_completed ? 'completed' : ''; ?>">
                            <div class="course-header">
                                <h4>
                                    <a href="<?php echo get_permalink($course->ID); ?>">
                                        <?php echo esc_html($course->post_title); ?>
                                    </a>
                                </h4>
                                <?php if ($is_completed): ?>
                                    <span class="completion-badge"><?php _e('완료', 'lectus-class-system'); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="course-progress">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $progress; ?>%"></div>
                                </div>
                                <span class="progress-text"><?php echo $progress; ?>% <?php _e('완료', 'lectus-class-system'); ?></span>
                            </div>
                            
                            <div class="course-meta">
                                <span class="enrollment-date">
                                    <?php _e('등록일:', 'lectus-class-system'); ?> 
                                    <?php echo date_i18n(get_option('date_format'), strtotime($enrollment->enrolled_at)); ?>
                                </span>
                                <?php if ($enrollment->expires_at): ?>
                                    <span class="expiry-date">
                                        <?php _e('만료일:', 'lectus-class-system'); ?> 
                                        <?php echo date_i18n(get_option('date_format'), strtotime($enrollment->expires_at)); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="course-actions">
                                <?php 
                                $continue_url = Lectus_Progress::get_continue_learning_url($user_id, $course->ID);
                                ?>
                                <a href="<?php echo esc_url($continue_url); ?>" class="button button-primary">
                                    <?php echo $progress > 0 ? __('계속 학습', 'lectus-class-system') : __('학습 시작', 'lectus-class-system'); ?>
                                </a>
                                <?php if ($is_completed): ?>
                                    <?php 
                                    $certificates = Lectus_Certificate::get_user_certificates($user_id);
                                    $course_certificate = null;
                                    foreach ($certificates as $cert) {
                                        if ($cert->course_id == $course->ID) {
                                            $course_certificate = $cert;
                                            break;
                                        }
                                    }
                                    ?>
                                    <?php if ($course_certificate): ?>
                                        <a href="<?php echo Lectus_Certificate::get_certificate_url($course_certificate->id); ?>" 
                                           class="button button-secondary" target="_blank">
                                            <?php _e('수료증 보기', 'lectus-class-system'); ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($certificates)): ?>
            <div class="dashboard-section">
                <h3><?php _e('내 수료증', 'lectus-class-system'); ?></h3>
                <div class="certificates-list">
                    <?php foreach ($certificates as $certificate): 
                        $course = get_post($certificate->course_id);
                        if (!$course) continue;
                    ?>
                        <div class="certificate-item">
                            <div class="certificate-info">
                                <h4><?php echo esc_html($course->post_title); ?></h4>
                                <p class="certificate-number">
                                    <?php _e('수료증 번호:', 'lectus-class-system'); ?> 
                                    <code><?php echo esc_html($certificate->certificate_number); ?></code>
                                </p>
                                <p class="certificate-date">
                                    <?php _e('발급일:', 'lectus-class-system'); ?> 
                                    <?php echo date_i18n(get_option('date_format'), strtotime($certificate->issued_at)); ?>
                                </p>
                            </div>
                            <div class="certificate-actions">
                                <a href="<?php echo Lectus_Certificate::get_certificate_url($certificate->id); ?>" 
                                   class="button" target="_blank">
                                    <?php _e('수료증 보기', 'lectus-class-system'); ?>
                                </a>
                                <a href="<?php echo Lectus_Certificate::get_certificate_url($certificate->id); ?>?download=pdf" 
                                   class="button" target="_blank">
                                    <?php _e('PDF 다운로드', 'lectus-class-system'); ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.lectus-student-dashboard {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.dashboard-header {
    text-align: center;
    margin-bottom: 30px;
}

.dashboard-header h2 {
    margin-bottom: 10px;
    color: #333;
}

.dashboard-stats {
    display: flex;
    gap: 20px;
    margin-bottom: 40px;
    justify-content: center;
}

.stat-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
    min-width: 120px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-number {
    font-size: 2em;
    font-weight: bold;
    color: #007cba;
    margin-bottom: 5px;
}

.stat-label {
    color: #666;
    font-size: 14px;
}

.dashboard-section {
    margin-bottom: 40px;
}

.dashboard-section h3 {
    margin-bottom: 20px;
    color: #333;
    border-bottom: 2px solid #007cba;
    padding-bottom: 10px;
}

.courses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.course-card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.course-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.course-card.completed {
    border-left: 4px solid #28a745;
}

.course-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 15px;
}

.course-header h4 {
    margin: 0;
    flex: 1;
}

.course-header a {
    text-decoration: none;
    color: #333;
}

.course-header a:hover {
    color: #007cba;
}

.completion-badge {
    background: #28a745;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}

.course-progress {
    margin-bottom: 15px;
}

.progress-bar {
    background: #e9ecef;
    border-radius: 4px;
    height: 8px;
    overflow: hidden;
    margin-bottom: 5px;
}

.progress-fill {
    background: #007cba;
    height: 100%;
    transition: width 0.3s;
}

.progress-text {
    font-size: 14px;
    color: #666;
}

.course-meta {
    font-size: 13px;
    color: #666;
    margin-bottom: 15px;
}

.course-meta span {
    display: block;
    margin-bottom: 3px;
}

.course-actions {
    display: flex;
    gap: 10px;
}

.button {
    display: inline-block;
    padding: 8px 16px;
    background: #007cba;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
    border: none;
    cursor: pointer;
}

.button:hover {
    background: #005a87;
    color: white;
}

.button-secondary {
    background: #6c757d;
}

.button-secondary:hover {
    background: #545b62;
}

.certificates-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.certificate-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.certificate-info h4 {
    margin: 0 0 5px 0;
}

.certificate-number {
    margin: 5px 0;
    font-size: 13px;
}

.certificate-date {
    margin: 5px 0 0 0;
    color: #666;
    font-size: 13px;
}

.certificate-actions {
    display: flex;
    gap: 10px;
}

@media (max-width: 768px) {
    .dashboard-stats {
        flex-direction: column;
        align-items: center;
    }
    
    .courses-grid {
        grid-template-columns: 1fr;
    }
    
    .certificate-item {
        flex-direction: column;
        align-items: start;
        gap: 15px;
    }
    
    .certificate-actions {
        width: 100%;
        justify-content: flex-start;
    }
}
</style>