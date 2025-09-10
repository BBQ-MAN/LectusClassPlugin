<?php
/**
 * Student Dashboard Template
 * 
 * @package LectusClassSystem
 */

get_header();

// Check if user is logged in
if (!is_user_logged_in()) {
    ?>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-8 text-center">
            <i class="fas fa-lock text-6xl text-gray-400 mb-4"></i>
            <h2 class="text-2xl font-bold mb-4"><?php _e('로그인이 필요합니다', 'lectus-class-system'); ?></h2>
            <p class="text-gray-600 mb-6"><?php _e('내 강의실을 이용하려면 먼저 로그인해주세요.', 'lectus-class-system'); ?></p>
            <a href="<?php echo wp_login_url(get_permalink()); ?>" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                <?php _e('로그인', 'lectus-class-system'); ?>
            </a>
        </div>
    </div>
    <?php
    get_footer();
    return;
}

$user = wp_get_current_user();
$user_id = $user->ID;

// Get user's enrollments
$enrollments = Lectus_Enrollment::get_user_enrollments($user_id);

// Get certificates
$certificates = Lectus_Certificate::get_user_certificates($user_id);

// Calculate statistics
$total_courses = count($enrollments);
$completed_courses = 0;
$in_progress_courses = 0;
$total_progress = 0;

foreach ($enrollments as $enrollment) {
    $progress = Lectus_Progress::get_course_progress($user_id, $enrollment->course_id);
    $total_progress += $progress;
    
    if ($progress >= 100) {
        $completed_courses++;
    } else if ($progress > 0) {
        $in_progress_courses++;
    }
}

$avg_progress = $total_courses > 0 ? round($total_progress / $total_courses) : 0;
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">
                        <?php echo sprintf(__('안녕하세요, %s님!', 'lectus-class-system'), $user->display_name); ?>
                    </h1>
                    <p class="text-gray-600"><?php _e('오늘도 열심히 학습해보세요!', 'lectus-class-system'); ?></p>
                </div>
                <div class="hidden md:block">
                    <?php echo get_avatar($user_id, 80, '', '', array('class' => 'rounded-full')); ?>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <i class="fas fa-book text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600"><?php _e('전체 수강', 'lectus-class-system'); ?></p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $total_courses; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600"><?php _e('완료', 'lectus-class-system'); ?></p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $completed_courses; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-lg">
                        <i class="fas fa-spinner text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600"><?php _e('학습 중', 'lectus-class-system'); ?></p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $in_progress_courses; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <i class="fas fa-award text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600"><?php _e('수료증', 'lectus-class-system'); ?></p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo count($certificates); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="border-b">
                <nav class="flex -mb-px">
                    <button class="tab-button active px-6 py-4 text-sm font-medium text-blue-600 border-b-2 border-blue-600" data-tab="courses">
                        <?php _e('수강 중인 강의', 'lectus-class-system'); ?>
                    </button>
                    <button class="tab-button px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700" data-tab="completed">
                        <?php _e('완료한 강의', 'lectus-class-system'); ?>
                    </button>
                    <button class="tab-button px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700" data-tab="certificates">
                        <?php _e('수료증', 'lectus-class-system'); ?>
                    </button>
                    <button class="tab-button px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700" data-tab="wishlist">
                        <?php _e('위시리스트', 'lectus-class-system'); ?>
                    </button>
                </nav>
            </div>

            <!-- Tab Contents -->
            <div class="p-6">
                <!-- Courses Tab -->
                <div class="tab-content" id="courses-tab">
                    <?php if (!empty($enrollments)) : ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($enrollments as $enrollment) : 
                                $course = get_post($enrollment->course_id);
                                if (!$course) continue;
                                
                                $progress = Lectus_Progress::get_course_progress($user_id, $enrollment->course_id);
                                if ($progress >= 100) continue; // Skip completed courses in this tab
                                
                                $continue_url = Lectus_Progress::get_continue_learning_url($user_id, $enrollment->course_id);
                            ?>
                                <div class="border rounded-lg overflow-hidden hover:shadow-lg transition">
                                    <div class="aspect-video bg-gray-100 relative">
                                        <?php if (has_post_thumbnail($course->ID)) : ?>
                                            <?php echo get_the_post_thumbnail($course->ID, 'medium', ['class' => 'w-full h-full object-cover']); ?>
                                        <?php else : ?>
                                            <div class="w-full h-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                                                <i class="fas fa-graduation-cap text-white text-4xl opacity-50"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Progress Overlay -->
                                        <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white p-2">
                                            <div class="flex justify-between text-xs mb-1">
                                                <span><?php _e('진도율', 'lectus-class-system'); ?></span>
                                                <span><?php echo $progress; ?>%</span>
                                            </div>
                                            <div class="w-full bg-gray-700 rounded-full h-2">
                                                <div class="bg-blue-500 h-2 rounded-full" style="width: <?php echo $progress; ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="p-4">
                                        <h3 class="font-bold text-gray-900 mb-2 line-clamp-2">
                                            <?php echo esc_html($course->post_title); ?>
                                        </h3>
                                        <p class="text-sm text-gray-600 mb-4">
                                            <?php 
                                            $expires = $enrollment->expires_at ? 
                                                sprintf(__('만료: %s', 'lectus-class-system'), date_i18n(get_option('date_format'), strtotime($enrollment->expires_at))) :
                                                __('무제한', 'lectus-class-system');
                                            echo $expires;
                                            ?>
                                        </p>
                                        <a href="<?php echo esc_url($continue_url); ?>" class="block w-full bg-blue-600 text-white text-center py-2 rounded hover:bg-blue-700 transition">
                                            <?php _e('학습 계속하기', 'lectus-class-system'); ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="text-center py-12">
                            <i class="fas fa-book-open text-6xl text-gray-300 mb-4"></i>
                            <p class="text-xl text-gray-500 mb-6"><?php _e('수강 중인 강의가 없습니다.', 'lectus-class-system'); ?></p>
                            <a href="<?php echo get_post_type_archive_link('coursesingle'); ?>" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                                <?php _e('강의 둘러보기', 'lectus-class-system'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Completed Tab -->
                <div class="tab-content hidden" id="completed-tab">
                    <?php 
                    $has_completed = false;
                    foreach ($enrollments as $enrollment) {
                        $progress = Lectus_Progress::get_course_progress($user_id, $enrollment->course_id);
                        if ($progress >= 100) {
                            $has_completed = true;
                            break;
                        }
                    }
                    
                    if ($has_completed) : ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($enrollments as $enrollment) : 
                                $course = get_post($enrollment->course_id);
                                if (!$course) continue;
                                
                                $progress = Lectus_Progress::get_course_progress($user_id, $enrollment->course_id);
                                if ($progress < 100) continue; // Skip incomplete courses
                            ?>
                                <div class="border rounded-lg overflow-hidden">
                                    <div class="aspect-video bg-gray-100 relative">
                                        <?php if (has_post_thumbnail($course->ID)) : ?>
                                            <?php echo get_the_post_thumbnail($course->ID, 'medium', ['class' => 'w-full h-full object-cover']); ?>
                                        <?php else : ?>
                                            <div class="w-full h-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center">
                                                <i class="fas fa-check-circle text-white text-4xl"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="absolute top-2 right-2 bg-green-500 text-white px-3 py-1 rounded-full text-sm font-bold">
                                            <?php _e('완료', 'lectus-class-system'); ?>
                                        </div>
                                    </div>
                                    
                                    <div class="p-4">
                                        <h3 class="font-bold text-gray-900 mb-2 line-clamp-2">
                                            <?php echo esc_html($course->post_title); ?>
                                        </h3>
                                        <p class="text-sm text-gray-600 mb-4">
                                            <?php echo sprintf(__('완료일: %s', 'lectus-class-system'), 
                                                date_i18n(get_option('date_format'), strtotime($enrollment->updated_at))); ?>
                                        </p>
                                        <a href="<?php echo get_permalink($course->ID); ?>" class="block w-full bg-gray-600 text-white text-center py-2 rounded hover:bg-gray-700 transition">
                                            <?php _e('다시 보기', 'lectus-class-system'); ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="text-center py-12">
                            <i class="fas fa-trophy text-6xl text-gray-300 mb-4"></i>
                            <p class="text-xl text-gray-500"><?php _e('아직 완료한 강의가 없습니다.', 'lectus-class-system'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Certificates Tab -->
                <div class="tab-content hidden" id="certificates-tab">
                    <?php if (!empty($certificates)) : ?>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b">
                                        <th class="text-left py-3 px-4"><?php _e('강의명', 'lectus-class-system'); ?></th>
                                        <th class="text-left py-3 px-4"><?php _e('수료증 번호', 'lectus-class-system'); ?></th>
                                        <th class="text-left py-3 px-4"><?php _e('발급일', 'lectus-class-system'); ?></th>
                                        <th class="text-left py-3 px-4"><?php _e('다운로드', 'lectus-class-system'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($certificates as $certificate) : 
                                        $course = get_post($certificate->course_id);
                                        if (!$course) continue;
                                    ?>
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="py-3 px-4"><?php echo esc_html($course->post_title); ?></td>
                                            <td class="py-3 px-4 font-mono text-sm"><?php echo esc_html($certificate->certificate_number); ?></td>
                                            <td class="py-3 px-4"><?php echo date_i18n(get_option('date_format'), strtotime($certificate->issued_at)); ?></td>
                                            <td class="py-3 px-4">
                                                <a href="<?php echo Lectus_Certificate::get_certificate_url($certificate->id); ?>" 
                                                   class="text-blue-600 hover:text-blue-700" target="_blank">
                                                    <i class="fas fa-download"></i> <?php _e('다운로드', 'lectus-class-system'); ?>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="text-center py-12">
                            <i class="fas fa-certificate text-6xl text-gray-300 mb-4"></i>
                            <p class="text-xl text-gray-500"><?php _e('발급받은 수료증이 없습니다.', 'lectus-class-system'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Wishlist Tab -->
                <div class="tab-content hidden" id="wishlist-tab">
                    <?php 
                    if (class_exists('Lectus_Wishlist')) {
                        $wishlist_items = Lectus_Wishlist::get_user_wishlist($user_id);
                        
                        if (!empty($wishlist_items)) : ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <?php foreach ($wishlist_items as $item) : 
                                    $course_id = $item->course_id;
                                    // Use the unified course card template
                                    include LECTUS_PLUGIN_DIR . 'templates/course-card.php';
                                endforeach; ?>
                            </div>
                        <?php else : ?>
                            <div class="text-center py-12">
                                <i class="fas fa-heart text-6xl text-gray-300 mb-4"></i>
                                <p class="text-xl text-gray-500"><?php _e('위시리스트가 비어있습니다.', 'lectus-class-system'); ?></p>
                            </div>
                        <?php endif;
                    } else { ?>
                        <p><?php _e('위시리스트 기능을 사용할 수 없습니다.', 'lectus-class-system'); ?></p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Tab switching
    $('.tab-button').on('click', function() {
        var tab = $(this).data('tab');
        
        // Update button states
        $('.tab-button').removeClass('active text-blue-600 border-b-2 border-blue-600').addClass('text-gray-500');
        $(this).addClass('active text-blue-600 border-b-2 border-blue-600').removeClass('text-gray-500');
        
        // Update content
        $('.tab-content').addClass('hidden');
        $('#' + tab + '-tab').removeClass('hidden');
    });
});
</script>

<?php get_footer(); ?>