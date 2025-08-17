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
<div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl md:text-4xl font-bold mb-2"><?php printf(__('안녕하세요, %s님!', 'lectus-academy'), esc_html($user->display_name)); ?></h1>
            <p class="text-blue-100 text-lg"><?php esc_html_e('오늘도 열심히 학습해보세요!', 'lectus-academy'); ?></p>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-6 text-center">
                <h3 class="text-sm text-blue-100 mb-2"><?php esc_html_e('수강중인 강의', 'lectus-academy'); ?></h3>
                <div class="text-3xl font-bold"><?php echo $total_courses; ?></div>
            </div>
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-6 text-center">
                <h3 class="text-sm text-blue-100 mb-2"><?php esc_html_e('완료한 강의', 'lectus-academy'); ?></h3>
                <div class="text-3xl font-bold"><?php echo $completed_courses; ?></div>
            </div>
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-6 text-center">
                <h3 class="text-sm text-blue-100 mb-2"><?php esc_html_e('평균 진도율', 'lectus-academy'); ?></h3>
                <div class="text-3xl font-bold"><?php echo $average_progress; ?>%</div>
            </div>
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-6 text-center">
                <h3 class="text-sm text-blue-100 mb-2"><?php esc_html_e('획득한 수료증', 'lectus-academy'); ?></h3>
                <div class="text-3xl font-bold"><?php echo $certificates_earned; ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Dashboard Content -->
<div class="bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Tab Navigation -->
                <div class="bg-white rounded-lg shadow-sm mb-6">
                    <ul class="flex border-b">
                        <li class="active">
                            <a href="#learning" data-tab="learning" class="inline-flex items-center gap-2 px-6 py-4 border-b-2 border-blue-600 text-blue-600 font-medium">
                                <i class="fas fa-book"></i> 학습중
                            </a>
                        </li>
                        <li>
                            <a href="#completed" data-tab="completed" class="inline-flex items-center gap-2 px-6 py-4 border-b-2 border-transparent text-gray-600 hover:text-gray-900 font-medium">
                                <i class="fas fa-check-circle"></i> 완료함
                            </a>
                        </li>
                        <li>
                            <a href="#wishlist" data-tab="wishlist" class="inline-flex items-center gap-2 px-6 py-4 border-b-2 border-transparent text-gray-600 hover:text-gray-900 font-medium">
                                <i class="fas fa-heart"></i> 위시리스트
                            </a>
                        </li>
                        <li>
                            <a href="#certificates" data-tab="certificates" class="inline-flex items-center gap-2 px-6 py-4 border-b-2 border-transparent text-gray-600 hover:text-gray-900 font-medium">
                                <i class="fas fa-certificate"></i> 수료증
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Learning Tab -->
                    <div id="learning" class="tab-pane">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                            <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                                <div class="relative">
                                    <?php if (has_post_thumbnail($course->ID)) : ?>
                                        <?php echo get_the_post_thumbnail($course->ID, 'course-thumbnail', ['class' => 'w-full h-48 object-cover']); ?>
                                    <?php else : ?>
                                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                            <i class="fas fa-graduation-cap text-4xl text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="absolute top-2 right-2 bg-white rounded-full p-3 shadow-lg">
                                        <div class="text-center" data-progress="<?php echo $progress; ?>">
                                            <span class="text-lg font-bold text-blue-600"><?php echo $progress; ?>%</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="p-4">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-3">
                                        <a href="<?php echo get_permalink($course->ID); ?>" class="hover:text-blue-600 transition-colors">
                                            <?php echo esc_html($course->post_title); ?>
                                        </a>
                                    </h3>
                                    
                                    <div class="flex items-center gap-4 text-sm text-gray-600 mb-3">
                                        <span class="flex items-center gap-1">
                                            <i class="fas fa-video"></i> <?php echo $lesson_count; ?>개 수업
                                        </span>
                                        <span class="flex items-center gap-1 <?php echo $days_remaining <= 7 ? 'text-red-600 font-medium' : ''; ?>">
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
                                    
                                    <div class="w-full bg-gray-200 rounded-full h-2 mb-4">
                                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: <?php echo $progress; ?>%"></div>
                                    </div>
                                    
                                    <a href="<?php echo get_permalink($course->ID); ?>" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                                        <i class="fas fa-play"></i> 학습 계속하기
                                    </a>
                                </div>
                            </div>
                            <?php 
                                    endif;
                                endforeach;
                            else :
                            ?>
                            <div class="col-span-full text-center py-12">
                                <i class="fas fa-book-open text-6xl text-gray-300 mb-4"></i>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2"><?php esc_html_e('수강중인 강의가 없습니다', 'lectus-academy'); ?></h3>
                                <p class="text-gray-600 mb-6"><?php esc_html_e('새로운 강의를 시작해보세요!', 'lectus-academy'); ?></p>
                                <a href="<?php echo esc_url(get_post_type_archive_link('coursesingle')); ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                                    강의 둘러보기
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Completed Tab -->
                    <div id="completed" class="tab-pane hidden">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                            <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                                <div class="relative">
                                    <?php if (has_post_thumbnail($course->ID)) : ?>
                                        <?php echo get_the_post_thumbnail($course->ID, 'course-thumbnail', ['class' => 'w-full h-48 object-cover']); ?>
                                    <?php else : ?>
                                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                            <i class="fas fa-graduation-cap text-4xl text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="absolute top-2 right-2 bg-green-500 text-white rounded-full p-3 shadow-lg">
                                        <i class="fas fa-check-circle text-xl"></i>
                                    </div>
                                </div>
                                
                                <div class="p-4">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-3">
                                        <a href="<?php echo get_permalink($course->ID); ?>" class="hover:text-blue-600 transition-colors">
                                            <?php echo esc_html($course->post_title); ?>
                                        </a>
                                    </h3>
                                    
                                    <div class="flex items-center gap-2 text-sm text-green-600 mb-4">
                                        <i class="fas fa-calendar-check"></i>
                                        <?php echo date('Y년 m월 d일', strtotime($enrollment->enrolled_at)); ?> 완료
                                    </div>
                                    
                                    <div class="flex gap-2">
                                        <a href="<?php echo get_permalink($course->ID); ?>" class="flex-1 flex items-center justify-center gap-2 px-3 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                                            <i class="fas fa-redo"></i> 다시 보기
                                        </a>
                                        <button class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
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
                            <div class="col-span-full text-center py-12">
                                <i class="fas fa-trophy text-6xl text-gray-300 mb-4"></i>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2"><?php esc_html_e('아직 완료한 강의가 없습니다', 'lectus-academy'); ?></h3>
                                <p class="text-gray-600"><?php esc_html_e('열심히 학습하여 첫 번째 강의를 완료해보세요!', 'lectus-academy'); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Wishlist Tab -->
                    <div id="wishlist" class="tab-pane hidden">
                        <div class="text-center py-12">
                            <i class="fas fa-heart text-6xl text-gray-300 mb-4"></i>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2"><?php esc_html_e('위시리스트가 비어있습니다', 'lectus-academy'); ?></h3>
                            <p class="text-gray-600 mb-6"><?php esc_html_e('관심있는 강의를 위시리스트에 추가해보세요!', 'lectus-academy'); ?></p>
                            <a href="<?php echo esc_url(get_post_type_archive_link('coursesingle')); ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                                강의 둘러보기
                            </a>
                        </div>
                    </div>
                    
                    <!-- Certificates Tab -->
                    <div id="certificates" class="tab-pane hidden">
                        <?php if ($certificates_earned > 0) : ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <?php
                            $certificates = $wpdb->get_results($wpdb->prepare(
                                "SELECT * FROM $certificates_table WHERE user_id = %d ORDER BY generated_at DESC",
                                $user_id
                            ));
                            
                            foreach ($certificates as $certificate) :
                                $course = get_post($certificate->course_id);
                                if (!$course) continue;
                            ?>
                            <div class="bg-white rounded-lg shadow-sm p-6 border-2 border-blue-100 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4 mx-auto">
                                    <i class="fas fa-award text-2xl text-blue-600"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-900 text-center mb-3"><?php echo esc_html($course->post_title); ?></h4>
                                <p class="text-sm text-gray-600 text-center mb-2">
                                    인증번호: <span class="font-medium"><?php echo esc_html($certificate->certificate_number); ?></span>
                                </p>
                                <p class="text-sm text-gray-600 text-center mb-4">
                                    발급일: <?php echo date('Y년 m월 d일', strtotime($certificate->generated_at)); ?>
                                </p>
                                <button class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                                    <i class="fas fa-download"></i> PDF 다운로드
                                </button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else : ?>
                        <div class="text-center py-12">
                            <i class="fas fa-certificate text-6xl text-gray-300 mb-4"></i>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2"><?php esc_html_e('아직 수료증이 없습니다', 'lectus-academy'); ?></h3>
                            <p class="text-gray-600"><?php esc_html_e('강의를 완료하면 수료증을 받을 수 있습니다!', 'lectus-academy'); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Profile Card -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <div class="text-center mb-4">
                        <?php echo get_avatar($user_id, 80, '', '', ['class' => 'w-20 h-20 rounded-full mx-auto mb-3']); ?>
                        <h3 class="text-lg font-semibold text-gray-900"><?php echo esc_html($user->display_name); ?></h3>
                        <p class="text-sm text-gray-600"><?php echo esc_html($user->user_email); ?></p>
                    </div>
                    <div class="grid grid-cols-3 gap-4 mb-4 text-center">
                        <div>
                            <span class="block text-2xl font-bold text-gray-900"><?php echo $total_courses; ?></span>
                            <span class="text-xs text-gray-600">수강 강의</span>
                        </div>
                        <div>
                            <span class="block text-2xl font-bold text-gray-900"><?php echo $completed_courses; ?></span>
                            <span class="text-xs text-gray-600">완료</span>
                        </div>
                        <div>
                            <span class="block text-2xl font-bold text-gray-900"><?php echo $certificates_earned; ?></span>
                            <span class="text-xs text-gray-600">수료증</span>
                        </div>
                    </div>
                    <a href="<?php echo esc_url(get_edit_profile_url()); ?>" class="w-full flex items-center justify-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                        <i class="fas fa-user-edit"></i> 프로필 수정
                    </a>
                </div>
                
                <!-- Recent Activity -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">최근 활동</h3>
                    <ul class="space-y-3">
                        <li class="flex gap-3">
                            <i class="fas fa-play-circle text-blue-500 mt-1"></i>
                            <div class="flex-1">
                                <span class="block text-sm text-gray-900">WordPress 플러그인 개발 학습 시작</span>
                                <span class="text-xs text-gray-500">2시간 전</span>
                            </div>
                        </li>
                        <li class="flex gap-3">
                            <i class="fas fa-check-circle text-green-500 mt-1"></i>
                            <div class="flex-1">
                                <span class="block text-sm text-gray-900">레슨 3 완료</span>
                                <span class="text-xs text-gray-500">어제</span>
                            </div>
                        </li>
                        <li class="flex gap-3">
                            <i class="fas fa-certificate text-purple-500 mt-1"></i>
                            <div class="flex-1">
                                <span class="block text-sm text-gray-900">기초 과정 수료증 획득</span>
                                <span class="text-xs text-gray-500">3일 전</span>
                            </div>
                        </li>
                    </ul>
                </div>
                
                <!-- Quick Links -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">바로가기</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <a href="<?php echo esc_url(get_post_type_archive_link('coursesingle')); ?>" class="flex flex-col items-center gap-2 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-search text-xl text-blue-600"></i>
                            <span class="text-sm text-gray-700">강의 찾기</span>
                        </a>
                        <a href="<?php echo esc_url(home_url('/roadmap')); ?>" class="flex flex-col items-center gap-2 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-route text-xl text-blue-600"></i>
                            <span class="text-sm text-gray-700">로드맵</span>
                        </a>
                        <a href="<?php echo esc_url(home_url('/community')); ?>" class="flex flex-col items-center gap-2 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-users text-xl text-blue-600"></i>
                            <span class="text-sm text-gray-700">커뮤니티</span>
                        </a>
                        <a href="<?php echo esc_url(home_url('/support')); ?>" class="flex flex-col items-center gap-2 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-question-circle text-xl text-blue-600"></i>
                            <span class="text-sm text-gray-700">도움말</span>
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
    const tabLinks = document.querySelectorAll('[data-tab]');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active styling from all tabs
            tabLinks.forEach(l => {
                l.classList.remove('border-blue-600', 'text-blue-600');
                l.classList.add('border-transparent', 'text-gray-600');
            });
            
            // Hide all tab panes
            tabPanes.forEach(p => p.classList.add('hidden'));
            
            // Add active styling to clicked tab
            this.classList.remove('border-transparent', 'text-gray-600');
            this.classList.add('border-blue-600', 'text-blue-600');
            
            // Show corresponding tab pane
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId).classList.remove('hidden');
        });
    });
    
    // Show first tab by default
    if (tabPanes.length > 0) {
        tabPanes[0].classList.remove('hidden');
    }
});
</script>

<?php get_footer(); ?>