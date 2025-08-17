<?php
/**
 * The front page template file - Inflearn style
 *
 * @package LectusAcademy
 */

get_header();
?>

<main id="primary" class="">
    
    <!-- Hero Section -->
    <section class="hero-section bg-gradient-to-br from-blue-600 to-blue-800 text-white py-20">
        <div class="hero-container max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="hero-content text-center">
                <h1 class="hero-title text-4xl md:text-5xl lg:text-6xl font-bold mb-6">
                    <?php esc_html_e('성장하는 건축가를 위한', 'lectus-academy'); ?><br>
                    <?php esc_html_e('온라인 강의 플랫폼', 'lectus-academy'); ?>
                </h1>
                <p class="hero-subtitle text-xl md:text-2xl mb-8 text-blue-100">
                    <?php esc_html_e('개발, 디자인, 비즈니스 등 다양한 분야의 전문 지식을 온라인으로 학습하세요', 'lectus-academy'); ?>
                </p>
                <div class="hero-search max-w-2xl mx-auto">
                    <form role="search" method="get" class="search-form relative" action="<?php echo esc_url(home_url('/')); ?>">
                        <input type="search" 
                               class="search-input w-full px-6 py-4 rounded-full text-gray-900 text-lg focus:outline-none focus:ring-4 focus:ring-blue-300" 
                               placeholder="<?php esc_attr_e('배우고 싶은 지식을 검색해보세요', 'lectus-academy'); ?>" 
                               value="<?php echo get_search_query(); ?>" 
                               name="s">
                        <button type="submit" class="search-submit absolute right-2 top-1/2 transform -translate-y-1/2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-full transition">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Keywords -->
    <section class="keywords-section bg-white py-4 border-b">
        <div class="keywords-container max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="keywords-wrapper flex flex-wrap items-center gap-3">
                <span class="keywords-label text-gray-600 font-medium"><?php esc_html_e('인기 검색어', 'lectus-academy'); ?></span>
                <?php
                $popular_keywords = array('React', 'Python', 'JavaScript', 'Spring', 'Node.js', 'Vue.js', 'Java', 'Django');
                foreach ($popular_keywords as $keyword) :
                ?>
                <a href="<?php echo esc_url(home_url('/?s=' . urlencode($keyword))); ?>" class="keyword-tag px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-full text-sm text-gray-700 transition">
                    <?php echo esc_html($keyword); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Courses -->
    <section class="featured-section py-16 bg-gray-50">
        <div class="featured-container max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="featured-header flex justify-between items-center mb-8">
                <h2 class="section-title text-3xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-fire text-red-500"></i>
                    <?php esc_html_e('인기 강의', 'lectus-academy'); ?>
                </h2>
                <a href="<?php echo esc_url(get_post_type_archive_link('coursesingle')); ?>" class="view-all text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1">
                    <?php esc_html_e('전체보기', 'lectus-academy'); ?>
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
            
            <div class="featured-grid grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">
                <?php
                // Get featured courses
                $featured_courses = new WP_Query(array(
                    'post_type' => 'coursesingle',
                    'posts_per_page' => 5,
                    'meta_key' => '_course_featured',
                    'meta_value' => 'yes',
                    'orderby' => 'menu_order',
                    'order' => 'ASC',
                ));
                
                // If no featured courses, get recent courses
                if (!$featured_courses->have_posts()) {
                    $featured_courses = new WP_Query(array(
                        'post_type' => 'coursesingle',
                        'posts_per_page' => 5,
                        'orderby' => 'date',
                        'order' => 'DESC',
                    ));
                }
                
                if ($featured_courses->have_posts()) :
                    while ($featured_courses->have_posts()) : $featured_courses->the_post();
                        $course_id = get_the_ID();
                        $instructor_id = get_post_field('post_author', $course_id);
                        $instructor_name = get_the_author_meta('display_name', $instructor_id);
                        $enrolled_count = lectus_academy_get_enrolled_count($course_id);
                        $lesson_count = count(lectus_academy_get_course_lessons($course_id));
                        $course_level = get_the_terms($course_id, 'course_level');
                        $level_name = $course_level && !is_wp_error($course_level) ? $course_level[0]->name : '';
                        
                        // Get price
                        $product_id = get_post_meta($course_id, '_linked_product_id', true);
                        $price_html = '무료';
                        $original_price = '';
                        $sale_price = '';
                        $discount_percent = 0;
                        
                        if ($product_id && function_exists('wc_get_product')) {
                            $product = wc_get_product($product_id);
                            if ($product) {
                                if ($product->is_on_sale()) {
                                    $original_price = $product->get_regular_price();
                                    $sale_price = $product->get_sale_price();
                                    $discount_percent = round((($original_price - $sale_price) / $original_price) * 100);
                                    $price_html = wc_price($sale_price);
                                } else {
                                    $price_html = $product->get_price_html();
                                }
                            }
                        }
                ?>
                <article class="course-card bg-white rounded-lg shadow-sm hover:shadow-lg transition-shadow overflow-hidden">
                    <a href="<?php the_permalink(); ?>" class="course-link block">
                        <div class="course-thumbnail relative">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('medium_large', ['class' => 'w-full h-40 object-cover']); ?>
                            <?php else : ?>
                                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/course-placeholder.jpg'); ?>" alt="" class="w-full h-40 object-cover">
                            <?php endif; ?>
                            <?php if ($discount_percent > 0) : ?>
                                <span class="discount-badge absolute top-2 left-2 bg-red-500 text-white px-2 py-1 rounded text-sm font-bold"><?php echo esc_html($discount_percent); ?>%</span>
                            <?php elseif ((time() - get_the_time('U')) < 7 * DAY_IN_SECONDS) : ?>
                                <span class="new-badge absolute top-2 left-2 bg-green-500 text-white px-2 py-1 rounded text-sm font-bold">NEW</span>
                            <?php endif; ?>
                        </div>
                        <div class="course-content p-4">
                            <h3 class="course-title font-semibold text-gray-900 mb-1 line-clamp-2"><?php the_title(); ?></h3>
                            <div class="course-instructor text-sm text-gray-600 mb-2"><?php echo esc_html($instructor_name); ?></div>
                            
                            <div class="course-rating flex items-center gap-1 mb-2">
                                <span class="rating-stars text-yellow-400">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </span>
                                <span class="rating-count text-sm text-gray-600">(<?php echo esc_html($enrolled_count); ?>)</span>
                            </div>
                            
                            <div class="course-meta flex gap-3 text-xs text-gray-500 mb-3">
                                <span class="meta-item flex items-center gap-1">
                                    <i class="fas fa-book"></i>
                                    <?php printf(esc_html__('%d개 수업', 'lectus-academy'), $lesson_count); ?>
                                </span>
                                <?php if ($level_name) : ?>
                                <span class="meta-item flex items-center gap-1">
                                    <i class="fas fa-signal"></i>
                                    <?php echo esc_html($level_name); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="course-pricing flex items-center gap-2">
                                <?php if ($discount_percent > 0) : ?>
                                    <span class="discount-percent bg-red-100 text-red-600 px-2 py-1 rounded text-xs font-bold"><?php echo esc_html($discount_percent); ?>%</span>
                                    <span class="original-price text-gray-400 line-through text-sm"><?php echo wc_price($original_price); ?></span>
                                <?php endif; ?>
                                <span class="current-price font-bold text-lg text-gray-900"><?php echo $price_html; ?></span>
                            </div>
                        </div>
                    </a>
                </article>
                <?php
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
            </div>
        </div>
    </section>

    <!-- New Courses -->
    <section class="new-courses-section py-16 bg-white">
        <div class="new-courses-container max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="new-courses-header flex justify-between items-center mb-8">
                <h2 class="section-title text-3xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-sparkles text-blue-500"></i>
                    <?php esc_html_e('신규 강의', 'lectus-academy'); ?>
                </h2>
                <a href="<?php echo esc_url(get_post_type_archive_link('coursesingle') . '?orderby=date'); ?>" class="view-all text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1">
                    <?php esc_html_e('전체보기', 'lectus-academy'); ?>
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
            
            <div class="new-courses-grid grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">
                <?php
                $new_courses = new WP_Query(array(
                    'post_type' => 'coursesingle',
                    'posts_per_page' => 5,
                    'orderby' => 'date',
                    'order' => 'DESC',
                ));
                
                if ($new_courses->have_posts()) :
                    while ($new_courses->have_posts()) : $new_courses->the_post();
                        // Same course card code as above
                        get_template_part('template-parts/content', 'course-card');
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories-section py-16 bg-gray-50">
        <div class="categories-container max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="categories-header mb-8">
                <h2 class="section-title text-3xl font-bold text-gray-900 text-center">
                    <?php esc_html_e('카테고리별 강의', 'lectus-academy'); ?>
                </h2>
            </div>
            
            <div class="categories-grid grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php
                $categories = get_terms(array(
                    'taxonomy' => 'course_category',
                    'hide_empty' => true,
                    'number' => 8,
                ));
                
                $category_colors = array(
                    '#30b2e5', '#5cc4ed', '#2090c0', '#1d7aa0',
                    '#4da8d4', '#3b9bc8', '#6fbfe8', '#85cef0'
                );
                
                if ($categories && !is_wp_error($categories)) :
                    foreach ($categories as $index => $category) :
                        $color = isset($category_colors[$index]) ? $category_colors[$index] : '#667eea';
                ?>
                <a href="<?php echo esc_url(get_term_link($category)); ?>" class="category-card block p-8 rounded-lg text-white transition-transform hover:scale-105" style="background: linear-gradient(135deg, <?php echo esc_attr($color); ?> 0%, <?php echo esc_attr($color); ?>aa 100%);">
                    <div class="category-content text-center">
                        <h3 class="category-name text-lg font-bold mb-2"><?php echo esc_html($category->name); ?></h3>
                        <p class="category-count text-sm opacity-90">
                            <?php printf(esc_html__('%d개 강의', 'lectus-academy'), $category->count); ?>
                        </p>
                    </div>
                </a>
                <?php
                    endforeach;
                endif;
                ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section py-20 bg-gradient-to-r from-blue-600 to-purple-600 text-white">
        <div class="cta-container max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="cta-title text-4xl font-bold mb-4">
                <?php esc_html_e('지식을 나누고 성장하는 공간', 'lectus-academy'); ?>
            </h2>
            <p class="cta-subtitle text-xl mb-8 text-blue-100">
                <?php esc_html_e('당신의 지식과 경험을 공유하여 더 많은 사람들과 함께 성장하세요', 'lectus-academy'); ?>
            </p>
            <div class="cta-buttons flex flex-col md:flex-row gap-4 justify-center">
                <a href="<?php echo esc_url(home_url('/apply-instructor')); ?>" class="cta-button-primary px-8 py-4 bg-white text-blue-600 font-bold rounded-lg hover:bg-gray-100 transition">
                    <?php esc_html_e('지식공유 신청하기', 'lectus-academy'); ?>
                </a>
                <a href="<?php echo esc_url(get_post_type_archive_link('coursesingle')); ?>" class="cta-button-secondary px-8 py-4 border-2 border-white text-white font-bold rounded-lg hover:bg-white hover:text-blue-600 transition">
                    <?php esc_html_e('강의 둘러보기', 'lectus-academy'); ?>
                </a>
            </div>
        </div>
    </section>

</main>

<?php
get_footer();
?>