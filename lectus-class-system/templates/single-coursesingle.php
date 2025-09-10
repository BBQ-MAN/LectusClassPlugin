<?php
/**
 * Template for displaying single course (course detail page)
 *
 * @package LectusClassSystem
 */

get_header();

// Get course data
$course_id = get_the_ID();
$user_id = get_current_user_id();
$instructor_id = get_post_field('post_author', $course_id);
$instructor_name = get_the_author_meta('display_name', $instructor_id);
$instructor_avatar = get_avatar_url($instructor_id, array('size' => 96));
$instructor_bio = get_the_author_meta('description', $instructor_id);

// Get course meta
$duration = get_post_meta($course_id, '_course_duration', true);
$access_mode = get_post_meta($course_id, '_access_mode', true);
$difficulty = get_post_meta($course_id, '_course_difficulty', true) ?: '초급';

// Get enrollment status
$is_enrolled = false;
$progress = 0;
if ($user_id && class_exists('Lectus_Enrollment')) {
    $is_enrolled = Lectus_Enrollment::is_enrolled($user_id, $course_id);
    if ($is_enrolled && class_exists('Lectus_Progress')) {
        $progress = Lectus_Progress::get_course_progress($user_id, $course_id);
    }
}

// Get price information
$product_id = get_post_meta($course_id, '_linked_product_id', true);
if (!$product_id) {
    $product_id = get_post_meta($course_id, '_product_id', true);
}

$price_html = '무료';
$is_free = true;

if ($product_id && function_exists('wc_get_product')) {
    $product = wc_get_product($product_id);
    if ($product) {
        $is_free = false;
        $price_html = $product->get_price_html();
    }
}

// Get course statistics
$enrolled_count = 0;
if (class_exists('Lectus_Enrollment')) {
    $enrolled_count = Lectus_Enrollment::get_course_enrollment_count($course_id);
}

// Get lessons
$lessons = get_posts(array(
    'post_type' => 'lesson',
    'meta_key' => '_course_id',
    'meta_value' => $course_id,
    'posts_per_page' => -1,
    'orderby' => 'menu_order',
    'order' => 'ASC'
));
$lesson_count = count($lessons);

// Get course items (sections and lessons)
require_once LECTUS_PLUGIN_DIR . 'includes/class-lectus-course-items.php';
$course_items = Lectus_Course_Items::get_course_items($course_id);
?>

<div class="min-h-screen bg-gray-50">
    <!-- Course Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white">
        <div class="max-w-7xl mx-auto px-4 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Course Info (2/3) -->
                <div class="lg:col-span-2">
                    <nav class="text-sm mb-4 opacity-90">
                        <a href="<?php echo home_url(); ?>" class="hover:underline">홈</a>
                        <span class="mx-2">/</span>
                        <a href="<?php echo home_url('/courses'); ?>" class="hover:underline">강의</a>
                        <span class="mx-2">/</span>
                        <span><?php the_title(); ?></span>
                    </nav>
                    
                    <h1 class="text-3xl lg:text-4xl font-bold mb-4"><?php the_title(); ?></h1>
                    
                    <div class="flex items-center gap-4 mb-6">
                        <img src="<?php echo esc_url($instructor_avatar); ?>" 
                             alt="<?php echo esc_attr($instructor_name); ?>" 
                             class="w-12 h-12 rounded-full">
                        <div>
                            <div class="font-semibold"><?php echo esc_html($instructor_name); ?></div>
                            <div class="text-sm opacity-90">강사</div>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap gap-6 text-sm">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-users"></i>
                            <span><?php echo number_format($enrolled_count); ?>명 수강중</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-book"></i>
                            <span><?php echo $lesson_count; ?>개 강의</span>
                        </div>
                        <?php if ($duration): ?>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-clock"></i>
                            <span><?php echo $duration; ?>일 수강기간</span>
                        </div>
                        <?php endif; ?>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-signal"></i>
                            <span><?php echo esc_html($difficulty); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Enrollment Card (1/3) -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-lg p-6 text-gray-900">
                        <?php if (has_post_thumbnail()): ?>
                            <div class="mb-4 rounded-lg overflow-hidden">
                                <?php the_post_thumbnail('medium_large', array('class' => 'w-full h-auto')); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($is_enrolled): ?>
                            <!-- Already Enrolled -->
                            <div class="mb-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-gray-600">진도율</span>
                                    <span class="font-semibold"><?php echo $progress; ?>%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo $progress; ?>%"></div>
                                </div>
                            </div>
                            
                            <a href="<?php echo home_url('/my-classroom'); ?>" 
                               class="block w-full bg-blue-600 text-white text-center py-3 rounded-lg hover:bg-blue-700 transition">
                                내 강의실로 이동
                            </a>
                            
                            <?php if (!empty($course_items)): ?>
                                <?php
                                // Find first incomplete lesson
                                $next_lesson_id = null;
                                foreach ($course_items as $item) {
                                    if ($item['type'] === 'lesson') {
                                        if (!Lectus_Progress::is_lesson_completed($user_id, $course_id, $item['id'])) {
                                            $next_lesson_id = $item['id'];
                                            break;
                                        }
                                    }
                                }
                                ?>
                                <?php if ($next_lesson_id): ?>
                                    <a href="<?php echo get_permalink($next_lesson_id); ?>" 
                                       class="block w-full mt-3 bg-gray-100 text-gray-900 text-center py-3 rounded-lg hover:bg-gray-200 transition">
                                        이어서 학습하기
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- Not Enrolled -->
                            <div class="mb-6">
                                <div class="text-3xl font-bold text-gray-900 mb-2">
                                    <?php echo $price_html; ?>
                                </div>
                            </div>
                            
                            <?php if ($user_id): ?>
                                <?php echo do_shortcode('[lectus_enroll_button course_id="' . $course_id . '"]'); ?>
                            <?php else: ?>
                                <a href="<?php echo wp_login_url(get_permalink()); ?>" 
                                   class="block w-full bg-blue-600 text-white text-center py-3 rounded-lg hover:bg-blue-700 transition">
                                    로그인하여 수강 신청
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <!-- Wishlist Button -->
                        <?php if ($user_id && !$is_enrolled): ?>
                            <button class="w-full mt-3 border border-gray-300 text-gray-700 py-3 rounded-lg hover:bg-gray-50 transition lectus-wishlist-btn" 
                                    data-course-id="<?php echo $course_id; ?>">
                                <i class="far fa-heart"></i> 위시리스트에 추가
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Course Content -->
    <div class="max-w-7xl mx-auto px-4 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content (2/3) -->
            <div class="lg:col-span-2">
                <!-- Tabs -->
                <div class="bg-white rounded-lg shadow mb-8">
                    <div class="border-b">
                        <nav class="flex" role="tablist">
                            <button class="px-6 py-4 font-medium text-gray-700 border-b-2 border-blue-600 course-tab-btn active" 
                                    data-tab="overview">
                                강의소개
                            </button>
                            <button class="px-6 py-4 font-medium text-gray-500 hover:text-gray-700 course-tab-btn" 
                                    data-tab="curriculum">
                                커리큘럼
                            </button>
                            <button class="px-6 py-4 font-medium text-gray-500 hover:text-gray-700 course-tab-btn" 
                                    data-tab="reviews">
                                수강평
                            </button>
                            <?php if ($is_enrolled): ?>
                            <button class="px-6 py-4 font-medium text-gray-500 hover:text-gray-700 course-tab-btn" 
                                    data-tab="qa">
                                Q&A
                            </button>
                            <button class="px-6 py-4 font-medium text-gray-500 hover:text-gray-700 course-tab-btn" 
                                    data-tab="materials">
                                강의자료
                            </button>
                            <?php endif; ?>
                        </nav>
                    </div>
                    
                    <!-- Tab Contents -->
                    <div class="p-6">
                        <!-- Overview Tab -->
                        <div class="course-tab-content" id="overview-tab">
                            <div class="prose max-w-none">
                                <?php the_content(); ?>
                            </div>
                        </div>
                        
                        <!-- Curriculum Tab -->
                        <div class="course-tab-content hidden" id="curriculum-tab">
                            <?php if (!empty($course_items)): ?>
                                <?php
                                $current_section = null;
                                $section_lesson_number = 0;
                                $total_duration = 0;
                                ?>
                                
                                <?php foreach ($course_items as $item): ?>
                                    <?php if ($item['type'] === 'section'): ?>
                                        <?php if ($current_section !== null): ?>
                                            </ol>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div class="mb-6">
                                            <h3 class="text-lg font-semibold mb-3">
                                                <?php echo esc_html($item['title']); ?>
                                            </h3>
                                            <?php if (!empty($item['description'])): ?>
                                                <p class="text-gray-600 mb-3"><?php echo esc_html($item['description']); ?></p>
                                            <?php endif; ?>
                                            <ol class="space-y-2">
                                        <?php 
                                        $current_section = $item;
                                        $section_lesson_number = 0;
                                        ?>
                                        
                                    <?php elseif ($item['type'] === 'lesson'): ?>
                                        <?php if ($current_section === null): ?>
                                            <div class="mb-6">
                                                <ol class="space-y-2">
                                            <?php $current_section = array('type' => 'unsectioned'); ?>
                                        <?php endif; ?>
                                        
                                        <?php
                                        $section_lesson_number++;
                                        $lesson_completed = false;
                                        $lesson_locked = false;
                                        
                                        if ($user_id && $is_enrolled) {
                                            $lesson_completed = Lectus_Progress::is_lesson_completed($user_id, $course_id, $item['id']);
                                            
                                            // Check if lesson is locked (sequential mode)
                                            if ($access_mode === 'sequential' && isset($prev_lesson_id)) {
                                                if (!Lectus_Progress::is_lesson_completed($user_id, $course_id, $prev_lesson_id)) {
                                                    $lesson_locked = true;
                                                }
                                            }
                                        }
                                        
                                        if (!empty($item['duration'])) {
                                            $total_duration += intval($item['duration']);
                                        }
                                        ?>
                                        
                                        <li class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                            <div class="flex items-center gap-3">
                                                <span class="text-gray-500"><?php echo sprintf('%02d', $section_lesson_number); ?></span>
                                                <?php if ($lesson_locked): ?>
                                                    <i class="fas fa-lock text-gray-400"></i>
                                                    <span class="text-gray-500"><?php echo esc_html($item['title']); ?></span>
                                                <?php elseif ($is_enrolled): ?>
                                                    <a href="<?php echo get_permalink($item['id']); ?>" 
                                                       class="text-gray-900 hover:text-blue-600">
                                                        <?php echo esc_html($item['title']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-gray-700"><?php echo esc_html($item['title']); ?></span>
                                                <?php endif; ?>
                                                
                                                <?php if ($lesson_completed): ?>
                                                    <span class="text-green-500 text-sm">✓ 완료</span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php if (!empty($item['duration'])): ?>
                                                <span class="text-sm text-gray-500">
                                                    <?php echo $item['duration']; ?>분
                                                </span>
                                            <?php endif; ?>
                                        </li>
                                        
                                        <?php $prev_lesson_id = $item['id']; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                
                                <?php if ($current_section !== null): ?>
                                    </ol>
                                </div>
                                <?php endif; ?>
                                
                                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-700">총 <?php echo $lesson_count; ?>개 강의</span>
                                        <?php if ($total_duration > 0): ?>
                                            <span class="text-gray-700">총 <?php echo floor($total_duration / 60); ?>시간 <?php echo $total_duration % 60; ?>분</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <p class="text-gray-500">커리큘럼이 준비중입니다.</p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Reviews Tab -->
                        <div class="course-tab-content hidden" id="reviews-tab">
                            <p class="text-gray-500">수강평 기능이 준비중입니다.</p>
                        </div>
                        
                        <!-- Q&A Tab (Only for enrolled users) -->
                        <?php if ($is_enrolled): ?>
                        <div class="course-tab-content hidden" id="qa-tab">
                            <?php echo do_shortcode('[lectus_qa course_id="' . $course_id . '" show_form="yes"]'); ?>
                        </div>
                        
                        <!-- Materials Tab (Only for enrolled users) -->
                        <div class="course-tab-content hidden" id="materials-tab">
                            <?php echo do_shortcode('[lectus_course_materials course_id="' . $course_id . '"]'); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar (1/3) -->
            <div class="lg:col-span-1">
                <!-- Instructor Info -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-lg font-semibold mb-4">강사 소개</h3>
                    <div class="flex items-center gap-4 mb-4">
                        <img src="<?php echo esc_url($instructor_avatar); ?>" 
                             alt="<?php echo esc_attr($instructor_name); ?>" 
                             class="w-16 h-16 rounded-full">
                        <div>
                            <div class="font-semibold"><?php echo esc_html($instructor_name); ?></div>
                            <div class="text-sm text-gray-600">강사</div>
                        </div>
                    </div>
                    <?php if ($instructor_bio): ?>
                        <p class="text-gray-600 text-sm"><?php echo esc_html($instructor_bio); ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Course Features -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4">강의 특징</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <i class="fas fa-check-circle text-green-500 mt-1"></i>
                            <span class="text-gray-700">평생 무제한 수강</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fas fa-check-circle text-green-500 mt-1"></i>
                            <span class="text-gray-700">수료증 발급</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fas fa-check-circle text-green-500 mt-1"></i>
                            <span class="text-gray-700">Q&A 질문 답변</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fas fa-check-circle text-green-500 mt-1"></i>
                            <span class="text-gray-700">강의 자료 제공</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Tab switching
    $('.course-tab-btn').on('click', function() {
        var tabId = $(this).data('tab');
        
        // Update button states
        $('.course-tab-btn').removeClass('active border-b-2 border-blue-600 text-gray-700').addClass('text-gray-500');
        $(this).addClass('active border-b-2 border-blue-600 text-gray-700').removeClass('text-gray-500');
        
        // Show/hide content
        $('.course-tab-content').addClass('hidden');
        $('#' + tabId + '-tab').removeClass('hidden');
    });
    
    // Wishlist button
    $('.lectus-wishlist-btn').on('click', function() {
        var $btn = $(this);
        var courseId = $btn.data('course-id');
        
        // Toggle icon
        var $icon = $btn.find('i');
        if ($icon.hasClass('far')) {
            $icon.removeClass('far').addClass('fas text-red-500');
            $btn.html('<i class="fas fa-heart text-red-500"></i> 위시리스트에서 제거');
        } else {
            $icon.removeClass('fas text-red-500').addClass('far');
            $btn.html('<i class="far fa-heart"></i> 위시리스트에 추가');
        }
        
        // Here you would add AJAX call to save wishlist status
    });
});
</script>

<?php get_footer(); ?>