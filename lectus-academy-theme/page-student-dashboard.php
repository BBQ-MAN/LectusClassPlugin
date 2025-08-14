<?php
/**
 * Template Name: Student Dashboard
 * Description: Student dashboard page with Inflearn-inspired design
 *
 * @package LectusAcademy
 */

// Check if user is logged in
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

get_header();

$user_id = get_current_user_id();
$user = wp_get_current_user();

// Get enrolled courses
global $wpdb;
$enrollment_table = $wpdb->prefix . 'lectus_enrollment';
$enrolled_courses = array();

if ($wpdb->get_var("SHOW TABLES LIKE '$enrollment_table'") == $enrollment_table) {
    $enrolled_courses = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $enrollment_table WHERE user_id = %d AND status = 'active' ORDER BY enrolled_at DESC",
        $user_id
    ));
}

// Calculate statistics
$total_courses = count($enrolled_courses);
$completed_courses = 0;
$total_progress = 0;
$certificates_earned = 0;

foreach ($enrolled_courses as $enrollment) {
    $progress = lectus_academy_get_course_progress($enrollment->course_id, $user_id);
    $total_progress += $progress;
    if ($progress >= 100) {
        $completed_courses++;
    }
}

$average_progress = $total_courses > 0 ? round($total_progress / $total_courses) : 0;

// Get certificates
$certificates_table = $wpdb->prefix . 'lectus_certificates';
if ($wpdb->get_var("SHOW TABLES LIKE '$certificates_table'") == $certificates_table) {
    $certificates_earned = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $certificates_table WHERE user_id = %d",
        $user_id
    ));
}
?>

<!-- Dashboard Header -->
<div class="dashboard-header">
    <div class="container">
        <div class="dashboard-welcome">
            <h1><?php printf(__('안녕하세요, %s님!', 'lectus-academy'), esc_html($user->display_name)); ?></h1>
            <p><?php esc_html_e('오늘도 열심히 학습해보세요!', 'lectus-academy'); ?></p>
        </div>
        
        <div class="dashboard-stats">
            <div class="stat-card">
                <h3><?php esc_html_e('수강중인 강의', 'lectus-academy'); ?></h3>
                <div class="stat-number"><?php echo $total_courses; ?></div>
            </div>
            <div class="stat-card">
                <h3><?php esc_html_e('완료한 강의', 'lectus-academy'); ?></h3>
                <div class="stat-number"><?php echo $completed_courses; ?></div>
            </div>
            <div class="stat-card">
                <h3><?php esc_html_e('평균 진도율', 'lectus-academy'); ?></h3>
                <div class="stat-number"><?php echo $average_progress; ?>%</div>
            </div>
            <div class="stat-card">
                <h3><?php esc_html_e('획득한 수료증', 'lectus-academy'); ?></h3>
                <div class="stat-number"><?php echo $certificates_earned; ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Dashboard Content -->
<div class="dashboard-body">
    <div class="container">
        <div class="dashboard-content">
            <!-- Main Content -->
            <div class="dashboard-main">
                <!-- Tab Navigation -->
                <div class="dashboard-tabs">
                    <ul class="tab-nav">
                        <li class="active">
                            <a href="#learning" data-tab="learning">
                                <i class="fas fa-book"></i> 학습중
                            </a>
                        </li>
                        <li>
                            <a href="#completed" data-tab="completed">
                                <i class="fas fa-check-circle"></i> 완료함
                            </a>
                        </li>
                        <li>
                            <a href="#wishlist" data-tab="wishlist">
                                <i class="fas fa-heart"></i> 위시리스트
                            </a>
                        </li>
                        <li>
                            <a href="#certificates" data-tab="certificates">
                                <i class="fas fa-certificate"></i> 수료증
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Learning Tab -->
                    <div id="learning" class="tab-pane active">
                        <div class="courses-grid">
                            <?php 
                            if ($enrolled_courses) :
                                foreach ($enrolled_courses as $enrollment) :
                                    $course = get_post($enrollment->course_id);
                                    if (!$course) continue;
                                    
                                    $progress = lectus_academy_get_course_progress($enrollment->course_id, $user_id);
                                    $lessons = lectus_academy_get_course_lessons($enrollment->course_id);
                                    $lesson_count = count($lessons);
                                    
                                    // Calculate days remaining
                                    $expires_at = strtotime($enrollment->expires_at);
                                    $days_remaining = ceil(($expires_at - time()) / (60 * 60 * 24));
                                    
                                    if ($progress < 100) :
                            ?>
                            <div class="dashboard-course-card">
                                <div class="course-thumbnail">
                                    <?php if (has_post_thumbnail($course->ID)) : ?>
                                        <?php echo get_the_post_thumbnail($course->ID, 'course-thumbnail'); ?>
                                    <?php else : ?>
                                        <div class="placeholder-thumbnail">
                                            <i class="fas fa-graduation-cap"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="course-progress-overlay">
                                        <div class="circular-progress" data-progress="<?php echo $progress; ?>">
                                            <span><?php echo $progress; ?>%</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="course-info">
                                    <h3 class="course-title">
                                        <a href="<?php echo get_permalink($course->ID); ?>">
                                            <?php echo esc_html($course->post_title); ?>
                                        </a>
                                    </h3>
                                    
                                    <div class="course-meta">
                                        <span class="lesson-count">
                                            <i class="fas fa-video"></i> <?php echo $lesson_count; ?>개 수업
                                        </span>
                                        <span class="days-remaining">
                                            <i class="fas fa-calendar"></i> 
                                            <?php 
                                            if ($days_remaining > 0) {
                                                printf(__('%d일 남음', 'lectus-academy'), $days_remaining);
                                            } else {
                                                esc_html_e('기간 만료', 'lectus-academy');
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $progress; ?>%"></div>
                                    </div>
                                    
                                    <div class="course-actions">
                                        <a href="<?php echo get_permalink($course->ID); ?>" class="btn btn-primary btn-block">
                                            <i class="fas fa-play"></i> 학습 계속하기
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php 
                                    endif;
                                endforeach;
                            else :
                            ?>
                            <div class="empty-state">
                                <i class="fas fa-book-open"></i>
                                <h3><?php esc_html_e('수강중인 강의가 없습니다', 'lectus-academy'); ?></h3>
                                <p><?php esc_html_e('새로운 강의를 시작해보세요!', 'lectus-academy'); ?></p>
                                <a href="<?php echo esc_url(get_post_type_archive_link('coursesingle')); ?>" class="btn btn-primary">
                                    강의 둘러보기
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Completed Tab -->
                    <div id="completed" class="tab-pane">
                        <div class="courses-grid">
                            <?php 
                            $has_completed = false;
                            if ($enrolled_courses) :
                                foreach ($enrolled_courses as $enrollment) :
                                    $course = get_post($enrollment->course_id);
                                    if (!$course) continue;
                                    
                                    $progress = lectus_academy_get_course_progress($enrollment->course_id, $user_id);
                                    
                                    if ($progress >= 100) :
                                        $has_completed = true;
                            ?>
                            <div class="dashboard-course-card completed">
                                <div class="course-thumbnail">
                                    <?php if (has_post_thumbnail($course->ID)) : ?>
                                        <?php echo get_the_post_thumbnail($course->ID, 'course-thumbnail'); ?>
                                    <?php else : ?>
                                        <div class="placeholder-thumbnail">
                                            <i class="fas fa-graduation-cap"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="completion-badge">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                </div>
                                
                                <div class="course-info">
                                    <h3 class="course-title">
                                        <a href="<?php echo get_permalink($course->ID); ?>">
                                            <?php echo esc_html($course->post_title); ?>
                                        </a>
                                    </h3>
                                    
                                    <div class="completion-date">
                                        <i class="fas fa-calendar-check"></i>
                                        <?php echo date('Y년 m월 d일', strtotime($enrollment->enrolled_at)); ?> 완료
                                    </div>
                                    
                                    <div class="course-actions">
                                        <a href="<?php echo get_permalink($course->ID); ?>" class="btn btn-outline btn-sm">
                                            <i class="fas fa-redo"></i> 다시 보기
                                        </a>
                                        <button class="btn btn-primary btn-sm">
                                            <i class="fas fa-certificate"></i> 수료증
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php 
                                    endif;
                                endforeach;
                            endif;
                            
                            if (!$has_completed) :
                            ?>
                            <div class="empty-state">
                                <i class="fas fa-trophy"></i>
                                <h3><?php esc_html_e('아직 완료한 강의가 없습니다', 'lectus-academy'); ?></h3>
                                <p><?php esc_html_e('열심히 학습하여 첫 번째 강의를 완료해보세요!', 'lectus-academy'); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Wishlist Tab -->
                    <div id="wishlist" class="tab-pane">
                        <div class="empty-state">
                            <i class="fas fa-heart"></i>
                            <h3><?php esc_html_e('위시리스트가 비어있습니다', 'lectus-academy'); ?></h3>
                            <p><?php esc_html_e('관심있는 강의를 위시리스트에 추가해보세요!', 'lectus-academy'); ?></p>
                            <a href="<?php echo esc_url(get_post_type_archive_link('coursesingle')); ?>" class="btn btn-primary">
                                강의 둘러보기
                            </a>
                        </div>
                    </div>
                    
                    <!-- Certificates Tab -->
                    <div id="certificates" class="tab-pane">
                        <?php if ($certificates_earned > 0) : ?>
                        <div class="certificates-grid">
                            <?php
                            $certificates = $wpdb->get_results($wpdb->prepare(
                                "SELECT * FROM $certificates_table WHERE user_id = %d ORDER BY generated_at DESC",
                                $user_id
                            ));
                            
                            foreach ($certificates as $certificate) :
                                $course = get_post($certificate->course_id);
                                if (!$course) continue;
                            ?>
                            <div class="certificate-card">
                                <div class="certificate-icon">
                                    <i class="fas fa-award"></i>
                                </div>
                                <h4><?php echo esc_html($course->post_title); ?></h4>
                                <p class="certificate-number">
                                    인증번호: <?php echo esc_html($certificate->certificate_number); ?>
                                </p>
                                <p class="certificate-date">
                                    발급일: <?php echo date('Y년 m월 d일', strtotime($certificate->generated_at)); ?>
                                </p>
                                <button class="btn btn-primary btn-sm">
                                    <i class="fas fa-download"></i> PDF 다운로드
                                </button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else : ?>
                        <div class="empty-state">
                            <i class="fas fa-certificate"></i>
                            <h3><?php esc_html_e('아직 수료증이 없습니다', 'lectus-academy'); ?></h3>
                            <p><?php esc_html_e('강의를 완료하면 수료증을 받을 수 있습니다!', 'lectus-academy'); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="dashboard-sidebar">
                <!-- Profile Card -->
                <div class="sidebar-card profile-card">
                    <div class="profile-header">
                        <?php echo get_avatar($user_id, 80); ?>
                        <h3><?php echo esc_html($user->display_name); ?></h3>
                        <p><?php echo esc_html($user->user_email); ?></p>
                    </div>
                    <div class="profile-stats">
                        <div class="stat-item">
                            <span class="stat-value"><?php echo $total_courses; ?></span>
                            <span class="stat-label">수강 강의</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value"><?php echo $completed_courses; ?></span>
                            <span class="stat-label">완료</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value"><?php echo $certificates_earned; ?></span>
                            <span class="stat-label">수료증</span>
                        </div>
                    </div>
                    <a href="<?php echo esc_url(get_edit_profile_url()); ?>" class="btn btn-outline btn-block">
                        <i class="fas fa-user-edit"></i> 프로필 수정
                    </a>
                </div>
                
                <!-- Recent Activity -->
                <div class="sidebar-card">
                    <h3>최근 활동</h3>
                    <ul class="activity-list">
                        <li>
                            <i class="fas fa-play-circle"></i>
                            <div>
                                <span class="activity-text">WordPress 플러그인 개발 학습 시작</span>
                                <span class="activity-time">2시간 전</span>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <span class="activity-text">레슨 3 완료</span>
                                <span class="activity-time">어제</span>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-certificate"></i>
                            <div>
                                <span class="activity-text">기초 과정 수료증 획득</span>
                                <span class="activity-time">3일 전</span>
                            </div>
                        </li>
                    </ul>
                </div>
                
                <!-- Quick Links -->
                <div class="sidebar-card">
                    <h3>바로가기</h3>
                    <div class="quick-links">
                        <a href="<?php echo esc_url(get_post_type_archive_link('coursesingle')); ?>" class="quick-link">
                            <i class="fas fa-search"></i>
                            <span>강의 찾기</span>
                        </a>
                        <a href="<?php echo esc_url(home_url('/roadmap')); ?>" class="quick-link">
                            <i class="fas fa-route"></i>
                            <span>로드맵</span>
                        </a>
                        <a href="<?php echo esc_url(home_url('/community')); ?>" class="quick-link">
                            <i class="fas fa-users"></i>
                            <span>커뮤니티</span>
                        </a>
                        <a href="<?php echo esc_url(home_url('/support')); ?>" class="quick-link">
                            <i class="fas fa-question-circle"></i>
                            <span>도움말</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Tab switching
document.addEventListener('DOMContentLoaded', function() {
    const tabLinks = document.querySelectorAll('.dashboard-tabs .tab-nav a');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all
            tabLinks.forEach(l => l.parentElement.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));
            
            // Add active class to clicked
            this.parentElement.classList.add('active');
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });
});
</script>

<?php get_footer(); ?>