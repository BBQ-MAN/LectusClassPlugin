<?php
/**
 * Single Course Template - Inflearn style
 *
 * @package LectusAcademy
 */

get_header();

while (have_posts()) :
    the_post();
    
    $course_id = get_the_ID();
    $lessons = lectus_academy_get_course_lessons($course_id);
    $lesson_count = count($lessons);
    $enrolled_count = lectus_academy_get_enrolled_count($course_id);
    $is_enrolled = lectus_academy_is_enrolled($course_id);
    $course_progress = lectus_academy_get_course_progress($course_id);
    
    // Get course meta
    $duration = get_post_meta($course_id, '_course_duration', true);
    $difficulty = get_post_meta($course_id, '_course_difficulty', true);
    $certificate = get_post_meta($course_id, '_course_certificate', true);
    $access_duration = get_post_meta($course_id, '_course_access_duration', true) ?: 365;
?>

<!-- Course Hero Section -->
<div class="bg-gray-900 text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <!-- Breadcrumb -->
                <nav class="flex items-center gap-2 text-sm text-gray-400 mb-4">
                    <a href="<?php echo esc_url(home_url()); ?>" class="hover:text-white transition-colors">홈</a>
                    <span>/</span>
                    <?php
                    $categories = get_the_terms($course_id, 'course_category');
                    if ($categories && !is_wp_error($categories)) :
                        $category = $categories[0];
                    ?>
                    <a href="<?php echo esc_url(get_term_link($category)); ?>" class="hover:text-white transition-colors">
                        <?php echo esc_html($category->name); ?>
                    </a>
                    <span>/</span>
                    <?php endif; ?>
                    <span><?php the_title(); ?></span>
                </nav>
                
                <!-- Course Title -->
                <h1 class="text-3xl md:text-4xl font-bold mb-6"><?php the_title(); ?></h1>
                
                <!-- Course Meta -->
                <div class="flex flex-wrap items-center gap-6 mb-6">
                    <div class="flex items-center gap-3">
                        <img src="<?php echo lectus_academy_get_instructor_avatar(get_the_ID()); ?>" 
                             alt="<?php echo lectus_academy_get_instructor_name(get_the_ID()); ?>"
                             class="w-12 h-12 rounded-full border-2 border-white">
                        <div>
                            <span class="text-sm text-gray-400 block">강사</span>
                            <span class="font-medium">
                                <?php echo lectus_academy_get_instructor_name(get_the_ID()); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <span class="text-gray-300">4.8 (128개 평가)</span>
                    </div>
                    
                    <div class="flex items-center gap-4 text-gray-300">
                        <span class="flex items-center gap-1">
                            <i class="fas fa-users"></i> <?php echo number_format($enrolled_count); ?>명 수강
                        </span>
                        <span class="flex items-center gap-1">
                            <i class="fas fa-heart"></i> 256명
                        </span>
                    </div>
                </div>
                
                <!-- Course Tags -->
                <div class="flex flex-wrap gap-2">
                    <?php
                    $tags = get_the_terms($course_id, 'course_tag');
                    if ($tags && !is_wp_error($tags)) :
                        foreach ($tags as $tag) :
                    ?>
                    <span class="px-3 py-1 bg-gray-800 text-gray-300 rounded-full text-sm">#<?php echo esc_html($tag->name); ?></span>
                    <?php 
                        endforeach;
                    endif;
                    ?>
                </div>
            </div>
            
        </div>
    </div>
</div>

<!-- Course Content -->
<div class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <!-- Tabs Navigation -->
                <div class="bg-white rounded-lg shadow-sm mb-6">
                    <ul class="flex border-b">
                        <li class="active">
                            <a href="#overview" data-tab="overview" class="inline-flex items-center gap-2 px-6 py-4 border-b-2 border-blue-600 text-blue-600 font-medium">
                                <i class="fas fa-info-circle"></i> 강의소개
                            </a>
                        </li>
                        <li>
                            <a href="#curriculum" data-tab="curriculum" class="inline-flex items-center gap-2 px-6 py-4 border-b-2 border-transparent text-gray-600 hover:text-gray-900 font-medium">
                                <i class="fas fa-list"></i> 커리큘럼
                            </a>
                        </li>
                        <li>
                            <a href="#reviews" data-tab="reviews" class="inline-flex items-center gap-2 px-6 py-4 border-b-2 border-transparent text-gray-600 hover:text-gray-900 font-medium">
                                <i class="fas fa-star"></i> 수강평
                            </a>
                        </li>
                        <li>
                            <a href="#qa" data-tab="qa" class="inline-flex items-center gap-2 px-6 py-4 border-b-2 border-transparent text-gray-600 hover:text-gray-900 font-medium">
                                <i class="fas fa-question-circle"></i> Q&A
                            </a>
                        </li>
                        <li>
                            <a href="#instructor" data-tab="instructor" class="inline-flex items-center gap-2 px-6 py-4 border-b-2 border-transparent text-gray-600 hover:text-gray-900 font-medium">
                                <i class="fas fa-user-tie"></i> 강사소개
                            </a>
                        </li>
                        <li>
                            <a href="#related" data-tab="related" class="inline-flex items-center gap-2 px-6 py-4 border-b-2 border-transparent text-gray-600 hover:text-gray-900 font-medium">
                                <i class="fas fa-book-open"></i> 연관강의
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Tab Content -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <!-- Overview Tab -->
                    <div id="overview" class="tab-pane active">
                        <div class="prose prose-lg max-w-none mb-8">
                            <?php the_content(); ?>
                        </div>
                        
                        <!-- What You'll Learn -->
                        <div class="mb-8">
                            <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                                <i class="fas fa-check-circle text-green-500"></i> 이런 걸 배워요
                            </h2>
                            <ul class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check text-green-500 mt-1"></i>
                                    <span>WordPress 플러그인 개발 기초</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check text-green-500 mt-1"></i>
                                    <span>LMS 시스템 구축 방법</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check text-green-500 mt-1"></i>
                                    <span>WooCommerce 연동 기법</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check text-green-500 mt-1"></i>
                                    <span>커스텀 포스트 타입 활용</span>
                                </li>
                            </ul>
                        </div>
                        
                        <!-- Requirements -->
                        <div class="mb-8">
                            <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                                <i class="fas fa-list-ul text-blue-500"></i> 선수 지식
                            </h2>
                            <ul class="space-y-2 text-gray-700">
                                <li class="flex items-start gap-2">
                                    <span class="text-gray-400">•</span>
                                    <span>기초적인 PHP 프로그래밍 지식</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-gray-400">•</span>
                                    <span>WordPress 기본 사용법</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-gray-400">•</span>
                                    <span>HTML/CSS 기초 지식</span>
                                </li>
                            </ul>
                        </div>
                        
                        <!-- Target Audience -->
                        <div class="mb-8">
                            <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                                <i class="fas fa-user-graduate text-purple-500"></i> 이런 분들께 추천해요
                            </h2>
                            <ul class="space-y-3">
                                <li class="flex items-start gap-3 p-3 bg-purple-50 rounded-lg">
                                    <i class="fas fa-user text-purple-500 mt-1"></i>
                                    <span>WordPress 개발을 시작하려는 개발자</span>
                                </li>
                                <li class="flex items-start gap-3 p-3 bg-purple-50 rounded-lg">
                                    <i class="fas fa-user text-purple-500 mt-1"></i>
                                    <span>온라인 교육 플랫폼을 구축하려는 기획자</span>
                                </li>
                                <li class="flex items-start gap-3 p-3 bg-purple-50 rounded-lg">
                                    <i class="fas fa-user text-purple-500 mt-1"></i>
                                    <span>LMS 시스템에 관심있는 학습자</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Curriculum Tab -->
                    <div id="curriculum" class="tab-pane" style="display:none;">
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                            <p class="text-gray-700">총 <?php echo $lesson_count; ?>개 수업 · <?php echo lectus_academy_format_duration($duration ?: 180); ?></p>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="border rounded-lg overflow-hidden">
                                <div class="bg-gray-100 px-4 py-3 flex justify-between items-center cursor-pointer hover:bg-gray-200 transition-colors" onclick="toggleSection(this)">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-chevron-down text-gray-600"></i>
                                        <span class="font-medium">섹션 1. 시작하기</span>
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        <span><?php echo $lesson_count; ?>강</span>
                                    </div>
                                </div>
                                
                                <div class="bg-white" style="display: block;">
                                    <?php 
                                    $lesson_num = 1;
                                    foreach ($lessons as $lesson) : 
                                        $lesson_duration = get_post_meta($lesson->ID, '_lesson_duration', true) ?: 10;
                                        $is_preview = get_post_meta($lesson->ID, '_is_preview', true);
                                        $is_completed = false;
                                        
                                        if ($is_enrolled) {
                                            // Check if lesson is completed
                                            global $wpdb;
                                            $table_name = $wpdb->prefix . 'lectus_progress';
                                            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
                                                $is_completed = $wpdb->get_var($wpdb->prepare(
                                                    "SELECT status FROM $table_name WHERE user_id = %d AND lesson_id = %d",
                                                    get_current_user_id(),
                                                    $lesson->ID
                                                )) === 'completed';
                                            }
                                        }
                                    ?>
                                    <div class="flex justify-between items-center px-4 py-3 border-t hover:bg-gray-50 <?php echo $is_completed ? 'bg-green-50' : ''; ?>">
                                        <div class="flex items-center gap-3">
                                            <span class="w-8 h-8 rounded-full <?php echo $is_completed ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-600'; ?> flex items-center justify-center text-sm font-medium">
                                                <?php if ($is_completed) : ?>
                                                    <i class="fas fa-check"></i>
                                                <?php else : ?>
                                                    <?php echo $lesson_num; ?>
                                                <?php endif; ?>
                                            </span>
                                            <span class="font-medium <?php echo $is_enrolled || $is_preview ? 'text-blue-600' : 'text-gray-700'; ?>">
                                                <?php if ($is_enrolled || $is_preview) : ?>
                                                    <a href="<?php echo get_permalink($lesson->ID); ?>" class="hover:underline">
                                                        <?php echo esc_html($lesson->post_title); ?>
                                                    </a>
                                                <?php else : ?>
                                                    <?php echo esc_html($lesson->post_title); ?>
                                                    <i class="fas fa-lock text-gray-400 ml-2"></i>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <?php if ($is_preview) : ?>
                                            <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded font-medium">미리보기</span>
                                            <?php endif; ?>
                                            <span class="text-sm text-gray-500 flex items-center gap-1">
                                                <i class="fas fa-play-circle"></i> <?php echo $lesson_duration; ?>분
                                            </span>
                                        </div>
                                    </div>
                                    <?php 
                                    $lesson_num++;
                                    endforeach; 
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reviews Tab -->
                    <div id="reviews" class="tab-pane" style="display:none;">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-bold">수강평</h3>
                            <?php if ($is_enrolled) : ?>
                            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">수강평 작성</button>
                            <?php endif; ?>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-6 mb-6">
                            <div class="text-center">
                                <div class="text-4xl font-bold text-gray-900 mb-2">4.8</div>
                                <div class="flex justify-center text-yellow-400 mb-2">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                                <span class="text-gray-600">128개의 수강평</span>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <!-- Sample Review -->
                            <div class="border rounded-lg p-4">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex gap-3">
                                        <?php echo get_avatar(1, 48, '', '', ['class' => 'rounded-full']); ?>
                                        <div>
                                            <div class="font-medium text-gray-900">김학생</div>
                                            <div class="flex text-yellow-400 text-sm">
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="text-sm text-gray-500">2024년 1월 15일</span>
                                </div>
                                <div class="text-gray-700">
                                    <p>정말 유익한 강의였습니다. 기초부터 실무까지 체계적으로 배울 수 있어서 좋았어요.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Q&A Tab -->
                    <div id="qa" class="tab-pane" style="display:none;">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-bold">질문 & 답변</h3>
                            <?php if ($is_enrolled) : ?>
                            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">질문하기</button>
                            <?php endif; ?>
                        </div>
                        
                        <div class="text-center py-12 text-gray-500">
                            <p>아직 등록된 질문이 없습니다.</p>
                        </div>
                    </div>
                    
                    <!-- Instructor Tab -->
                    <div id="instructor" class="tab-pane" style="display:none;">
                        <h3 class="text-xl font-bold mb-6">강사 소개</h3>
                        <div class="bg-gray-50 rounded-lg p-8">
                            <div class="text-center">
                                <img src="<?php echo lectus_academy_get_instructor_avatar(get_the_ID(), 120); ?>" 
                                     alt="<?php echo lectus_academy_get_instructor_name(get_the_ID()); ?>"
                                     class="w-30 h-30 rounded-full mx-auto mb-4 border-4 border-white shadow-lg">
                                <h4 class="text-2xl font-bold text-gray-900 mb-3"><?php echo lectus_academy_get_instructor_name(get_the_ID()); ?></h4>
                                <div class="max-w-2xl mx-auto">
                                    <p class="text-gray-600 leading-relaxed">
                                        <?php 
                                        $author_id = get_post_field('post_author', $course_id);
                                        $bio = get_the_author_meta('description', $author_id);
                                        echo $bio ?: '전문 강사로서 다년간의 실무 경험을 바탕으로 체계적이고 실용적인 교육을 제공합니다. 학습자가 실제 업무에 바로 적용할 수 있는 실무 중심의 커리큘럼으로 구성하여 효과적인 학습이 가능하도록 돕겠습니다.'; 
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Related Courses Tab -->
                    <div id="related" class="tab-pane" style="display:none;">
                        <h3 class="text-xl font-bold mb-6">연관 강의</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php
                            $related_args = array(
                                'post_type' => 'coursesingle',
                                'posts_per_page' => 6,
                                'post__not_in' => array($course_id),
                                'orderby' => 'rand'
                            );
                            
                            $related_query = new WP_Query($related_args);
                            
                            if ($related_query->have_posts()) :
                                while ($related_query->have_posts()) : $related_query->the_post();
                            ?>
                            <div class="bg-white rounded-lg border border-gray-200 hover:shadow-lg transition-shadow overflow-hidden">
                                <a href="<?php the_permalink(); ?>" class="block">
                                    <?php if (has_post_thumbnail()) : ?>
                                    <div class="aspect-video overflow-hidden">
                                        <?php the_post_thumbnail('medium', ['class' => 'w-full h-full object-cover hover:scale-105 transition-transform duration-300']); ?>
                                    </div>
                                    <?php endif; ?>
                                    <div class="p-4">
                                        <h4 class="font-medium text-gray-900 text-sm mb-2 line-clamp-2"><?php the_title(); ?></h4>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-bold text-blue-600">
                                                <?php echo lectus_academy_get_course_price(get_the_ID()); ?>
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                <?php echo lectus_academy_get_enrolled_count(get_the_ID()); ?>명 수강
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php 
                                endwhile;
                                wp_reset_postdata();
                            else :
                            ?>
                            <div class="col-span-full text-center py-12 text-gray-500">
                                <p>연관 강의가 없습니다.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Purchase Card -->
                <div id="purchase-card" class="bg-white rounded-lg shadow-lg p-6 h-fit sticky top-24 transition-all duration-300 hover:shadow-xl mb-6">
                    <?php if (has_post_thumbnail()) : ?>
                    <div class="mb-4 rounded-lg overflow-hidden">
                        <?php the_post_thumbnail('course-thumbnail', ['class' => 'w-full h-48 object-cover']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <div>
                        <div class="mb-4">
                            <span class="text-gray-600 text-sm">가격</span>
                            <div class="text-2xl font-bold text-gray-900">
                                <?php echo lectus_academy_get_course_price($course_id); ?>
                            </div>
                        </div>
                        
                        <div class="space-y-3 mb-4">
                            <?php if ($is_enrolled) : ?>
                                <a href="<?php echo esc_url(home_url('/my-courses')); ?>" 
                                   class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                                    <i class="fas fa-play"></i> 학습하기
                                </a>
                                <div class="mt-3">
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-gray-600">진도율: <?php echo $course_progress; ?>%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo $course_progress; ?>%"></div>
                                    </div>
                                </div>
                            <?php else : ?>
                                <button class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors flex items-center justify-center gap-2 enroll-btn" 
                                        data-course-id="<?php echo $course_id; ?>">
                                    <i class="fas fa-shopping-cart"></i> 수강신청
                                </button>
                                <button class="w-full border-2 border-gray-300 text-gray-700 py-3 px-4 rounded-lg font-medium hover:bg-gray-50 transition-colors flex items-center justify-center gap-2">
                                    <i class="fas fa-heart"></i> 위시리스트
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <div class="border-t pt-4">
                            <h4 class="font-medium text-gray-900 mb-3">포함 내용</h4>
                            <ul class="space-y-2 text-sm text-gray-600">
                                <li class="flex items-center gap-2"><i class="fas fa-video text-gray-400"></i> 강의 <?php echo $lesson_count; ?>개</li>
                                <li class="flex items-center gap-2"><i class="fas fa-clock text-gray-400"></i> 총 <?php echo lectus_academy_format_duration($duration ?: 180); ?></li>
                                <li class="flex items-center gap-2"><i class="fas fa-calendar text-gray-400"></i> 수강기한 <?php echo $access_duration; ?>일</li>
                                <li class="flex items-center gap-2"><i class="fas fa-mobile-alt text-gray-400"></i> 모바일 지원</li>
                                <?php if ($certificate) : ?>
                                <li class="flex items-center gap-2"><i class="fas fa-certificate text-gray-400"></i> 수료증 발급</li>
                                <?php endif; ?>
                                <li class="flex items-center gap-2"><i class="fas fa-file-download text-gray-400"></i> 강의자료 제공</li>
                            </ul>
                        </div>
                        
                        <div class="flex justify-center gap-2 pt-4 border-t">
                            <button class="w-10 h-10 rounded-full bg-gray-100 hover:bg-blue-600 hover:text-white transition-colors flex items-center justify-center" data-network="facebook">
                                <i class="fab fa-facebook-f"></i>
                            </button>
                            <button class="w-10 h-10 rounded-full bg-gray-100 hover:bg-blue-400 hover:text-white transition-colors flex items-center justify-center" data-network="twitter">
                                <i class="fab fa-twitter"></i>
                            </button>
                            <button class="w-10 h-10 rounded-full bg-gray-100 hover:bg-blue-700 hover:text-white transition-colors flex items-center justify-center" data-network="linkedin">
                                <i class="fab fa-linkedin-in"></i>
                            </button>
                            <button class="w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-600 hover:text-white transition-colors flex items-center justify-center" data-network="link">
                                <i class="fas fa-link"></i>
                            </button>
                        </div>
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
            
            // Remove active styles from all tabs
            tabLinks.forEach(l => {
                l.classList.remove('border-blue-600', 'text-blue-600');
                l.classList.add('border-transparent', 'text-gray-600');
            });
            
            // Hide all tab panes
            tabPanes.forEach(p => {
                p.style.display = 'none';
            });
            
            // Add active styles to clicked tab
            this.classList.remove('border-transparent', 'text-gray-600');
            this.classList.add('border-blue-600', 'text-blue-600');
            
            // Show selected tab pane
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId).style.display = 'block';
        });
    });
});

// Curriculum section toggle
function toggleSection(header) {
    const section = header.parentElement;
    const content = header.nextElementSibling;
    const icon = header.querySelector('.fas');
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.classList.remove('fa-chevron-right');
        icon.classList.add('fa-chevron-down');
    } else {
        content.style.display = 'none';
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-right');
    }
}

// Enhanced Sticky Purchase Card
function initStickyCard() {
    const purchaseCard = document.getElementById('purchase-card');
    const header = document.querySelector('header');
    
    if (!purchaseCard || !header) return;
    
    let isSticky = false;
    const headerHeight = header.offsetHeight;
    
    function updateCardPosition() {
        const scrollY = window.scrollY;
        const cardRect = purchaseCard.getBoundingClientRect();
        const shouldBeSticky = scrollY > 100;
        
        if (shouldBeSticky && !isSticky) {
            isSticky = true;
            purchaseCard.style.top = `${headerHeight + 20}px`;
            purchaseCard.classList.add('shadow-2xl', 'scale-105');
            purchaseCard.classList.remove('shadow-lg');
        } else if (!shouldBeSticky && isSticky) {
            isSticky = false;
            purchaseCard.style.top = '6rem';
            purchaseCard.classList.remove('shadow-2xl', 'scale-105');
            purchaseCard.classList.add('shadow-lg');
        }
    }
    
    window.addEventListener('scroll', updateCardPosition);
    window.addEventListener('resize', updateCardPosition);
}

// Enrollment
jQuery(document).ready(function($) {
    // Initialize enhanced sticky card
    initStickyCard();
    
    $('.enroll-btn').on('click', function() {
        const courseId = $(this).data('course-id');
        
        $.ajax({
            url: lectusAcademy.ajaxurl,
            type: 'POST',
            data: {
                action: 'lectus_academy_enroll',
                course_id: courseId,
                nonce: lectusAcademy.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });
});
</script>

<?php
endwhile;

get_footer();
?>