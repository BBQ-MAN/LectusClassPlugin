<?php
/**
 * Archive template for Courses
 * Unified course listing page
 *
 * @package LectusAcademy
 */

get_header();
?>

<main id="primary" class="min-h-screen bg-gradient-to-b from-gray-50 to-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <header class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                <?php
                if (is_post_type_archive('coursesingle')) {
                    echo '전체 강의';
                } elseif (is_tax('course_category')) {
                    single_term_title();
                } elseif (is_tax('course_level')) {
                    single_term_title();
                    echo ' 레벨';
                } else {
                    the_archive_title();
                }
                ?>
            </h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                최고의 전문가들과 함께 성장하세요. 실무에 바로 적용 가능한 실전 강의를 만나보세요.
            </p>
        </header>

        <!-- Category Filter -->
        <div class="flex flex-wrap justify-center gap-3 mb-12">
            <?php
            $current_category = get_queried_object();
            $is_category_archive = is_tax('course_category');
            $categories = get_terms(array(
                'taxonomy' => 'course_category',
                'hide_empty' => true,
            ));
            ?>
            
            <a href="<?php echo get_post_type_archive_link('coursesingle'); ?>" 
               class="px-6 py-2 <?php echo (!$is_category_archive) ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-300'; ?> rounded-full font-medium hover:bg-blue-700 hover:text-white transition-colors">
                전체
            </a>
            
            <?php if ($categories && !is_wp_error($categories)) : ?>
                <?php foreach ($categories as $category) : ?>
                    <a href="<?php echo get_term_link($category); ?>" 
                       class="px-6 py-2 <?php echo ($is_category_archive && $current_category->term_id == $category->term_id) ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-300'; ?> rounded-full font-medium hover:bg-blue-700 hover:text-white transition-colors">
                        <?php echo esc_html($category->name); ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Courses Grid -->
        <?php if (have_posts()) : ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php
                while (have_posts()) :
                    the_post();
                    $course_id = get_the_ID();
                    $lesson_count = count(lectus_academy_get_course_lessons($course_id));
                    $enrolled_count = function_exists('Lectus_Enrollment::get_course_enrollment_count') 
                        ? Lectus_Enrollment::get_course_enrollment_count($course_id) 
                        : 0;
                    $instructor_name = lectus_academy_get_instructor_name($course_id);
                    $price = lectus_academy_get_course_price($course_id);
                    $difficulty = get_post_meta($course_id, '_course_difficulty', true) ?: '초급';
                    $duration = get_post_meta($course_id, '_course_duration', true);
                    
                    // Get categories
                    $categories = get_the_terms($course_id, 'course_category');
                    $category_name = $categories && !is_wp_error($categories) ? $categories[0]->name : '';
                ?>
                    <article class="bg-white rounded-xl shadow-md hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden group">
                        <!-- Thumbnail -->
                        <div class="relative aspect-video overflow-hidden bg-gray-100">
                            <?php if (has_post_thumbnail()) : ?>
                                <a href="<?php the_permalink(); ?>" class="block">
                                    <?php the_post_thumbnail('medium_large', array(
                                        'class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300',
                                        'loading' => 'lazy'
                                    )); ?>
                                </a>
                            <?php else : ?>
                                <a href="<?php the_permalink(); ?>" class="flex items-center justify-center h-full bg-gradient-to-br from-blue-500 to-purple-600">
                                    <i class="fas fa-graduation-cap text-white text-4xl"></i>
                                </a>
                            <?php endif; ?>
                            
                            <!-- Badges -->
                            <div class="absolute top-3 left-3 flex gap-2">
                                <?php if ($category_name) : ?>
                                    <span class="px-2 py-1 bg-blue-600 text-white text-xs font-medium rounded">
                                        <?php echo esc_html($category_name); ?>
                                    </span>
                                <?php endif; ?>
                                <span class="px-2 py-1 bg-green-600 text-white text-xs font-medium rounded">
                                    <?php echo esc_html($difficulty); ?>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="p-4">
                            <!-- Title -->
                            <h3 class="font-bold text-gray-900 mb-2 line-clamp-2 min-h-[3rem]">
                                <a href="<?php the_permalink(); ?>" class="hover:text-blue-600 transition-colors">
                                    <?php the_title(); ?>
                                </a>
                            </h3>
                            
                            <!-- Instructor -->
                            <p class="text-sm text-gray-600 mb-3">
                                <?php echo esc_html($instructor_name); ?>
                            </p>
                            
                            <!-- Stats -->
                            <div class="flex items-center gap-3 text-xs text-gray-500 mb-3">
                                <span class="flex items-center gap-1">
                                    <i class="fas fa-book"></i>
                                    <?php echo $lesson_count; ?>개 레슨
                                </span>
                                <?php if ($duration) : ?>
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-clock"></i>
                                        <?php echo lectus_academy_format_duration($duration); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Rating -->
                            <div class="flex items-center gap-2 mb-3">
                                <div class="flex text-yellow-400 text-sm">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                                <span class="text-xs text-gray-600">
                                    4.5 (<?php echo number_format($enrolled_count); ?>명)
                                </span>
                            </div>
                            
                            <!-- Price & Action -->
                            <div class="flex items-center justify-between pt-3 border-t">
                                <span class="text-lg font-bold text-blue-600">
                                    <?php echo $price; ?>
                                </span>
                                <?php if (lectus_academy_is_enrolled($course_id)) : ?>
                                    <?php 
                                    $continue_url = class_exists('Lectus_Progress') 
                                        ? Lectus_Progress::get_continue_learning_url(get_current_user_id(), $course_id)
                                        : get_permalink($course_id);
                                    ?>
                                    <a href="<?php echo esc_url($continue_url); ?>" 
                                       class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition-colors">
                                        학습하기
                                    </a>
                                <?php else : ?>
                                    <a href="<?php the_permalink(); ?>" 
                                       class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition-colors">
                                        자세히 보기
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <div class="mt-12">
                <?php
                the_posts_pagination(array(
                    'mid_size' => 2,
                    'prev_text' => '<i class="fas fa-chevron-left"></i>',
                    'next_text' => '<i class="fas fa-chevron-right"></i>',
                    'class' => 'flex justify-center gap-2',
                ));
                ?>
            </div>
        <?php else : ?>
            <div class="text-center py-16">
                <i class="fas fa-book-open text-6xl text-gray-300 mb-4"></i>
                <h2 class="text-2xl font-semibold text-gray-700 mb-2">강의가 없습니다</h2>
                <p class="text-gray-500">아직 등록된 강의가 없습니다.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
/* Line clamp utility */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Pagination styles */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    margin-top: 3rem;
}

.pagination .page-numbers {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.375rem;
    font-weight: 500;
    transition: all 0.2s;
    background: white;
    border: 1px solid #e5e7eb;
    color: #374151;
}

.pagination .page-numbers:hover {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.pagination .page-numbers.current {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.pagination .dots {
    color: #9ca3af;
}
</style>

<?php
get_footer();
?>