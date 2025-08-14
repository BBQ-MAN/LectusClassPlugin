<?php
/**
 * The front page template file - Inflearn style
 *
 * @package LectusAcademy
 */

get_header();
?>

<main id="primary" class="site-main">
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">
                    <?php esc_html_e('성장하는 개발자를 위한', 'lectus-academy'); ?><br>
                    <?php esc_html_e('온라인 강의 플랫폼', 'lectus-academy'); ?>
                </h1>
                <p class="hero-subtitle">
                    <?php esc_html_e('개발, 디자인, 비즈니스 등 다양한 분야의 전문 지식을 온라인으로 학습하세요', 'lectus-academy'); ?>
                </p>
                <div class="hero-search">
                    <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
                        <input type="search" 
                               class="search-input" 
                               placeholder="<?php esc_attr_e('배우고 싶은 지식을 검색해보세요', 'lectus-academy'); ?>" 
                               value="<?php echo get_search_query(); ?>" 
                               name="s">
                        <button type="submit" class="search-submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Keywords -->
    <section class="popular-keywords">
        <div class="container">
            <div class="keywords-list">
                <span class="keyword-label"><?php esc_html_e('인기 검색어', 'lectus-academy'); ?></span>
                <?php
                $popular_keywords = array('React', 'Python', 'JavaScript', 'Spring', 'Node.js', 'Vue.js', 'Java', 'Django');
                foreach ($popular_keywords as $keyword) :
                ?>
                <a href="<?php echo esc_url(home_url('/?s=' . urlencode($keyword))); ?>" class="keyword-tag">
                    <?php echo esc_html($keyword); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Courses -->
    <section class="course-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-fire" style="color: #ff6b6b;"></i>
                    <?php esc_html_e('인기 강의', 'lectus-academy'); ?>
                </h2>
                <a href="<?php echo esc_url(get_post_type_archive_link('coursesingle')); ?>" class="section-link">
                    <?php esc_html_e('전체보기', 'lectus-academy'); ?>
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
            
            <div class="course-grid">
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
                <article class="course-card">
                    <a href="<?php the_permalink(); ?>" class="course-card-link">
                        <div class="course-thumbnail">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('medium_large'); ?>
                            <?php else : ?>
                                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/course-placeholder.jpg'); ?>" alt="">
                            <?php endif; ?>
                            <?php if ($discount_percent > 0) : ?>
                                <span class="course-badge sale"><?php echo esc_html($discount_percent); ?>%</span>
                            <?php elseif ((time() - get_the_time('U')) < 7 * DAY_IN_SECONDS) : ?>
                                <span class="course-badge new">NEW</span>
                            <?php endif; ?>
                        </div>
                        <div class="course-content">
                            <h3 class="course-title"><?php the_title(); ?></h3>
                            <div class="course-instructor"><?php echo esc_html($instructor_name); ?></div>
                            
                            <div class="course-rating">
                                <span class="rating-stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </span>
                                <span class="rating-count">(<?php echo esc_html($enrolled_count); ?>)</span>
                            </div>
                            
                            <div class="course-meta">
                                <span class="course-meta-item">
                                    <i class="fas fa-book"></i>
                                    <?php printf(esc_html__('%d개 수업', 'lectus-academy'), $lesson_count); ?>
                                </span>
                                <?php if ($level_name) : ?>
                                <span class="course-meta-item">
                                    <i class="fas fa-signal"></i>
                                    <?php echo esc_html($level_name); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="course-price">
                                <?php if ($discount_percent > 0) : ?>
                                    <span class="price-discount"><?php echo esc_html($discount_percent); ?>%</span>
                                    <span class="price-original"><?php echo wc_price($original_price); ?></span>
                                <?php endif; ?>
                                <span class="price-sale"><?php echo $price_html; ?></span>
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
    <section class="course-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-sparkles" style="color: #4c6ef5;"></i>
                    <?php esc_html_e('신규 강의', 'lectus-academy'); ?>
                </h2>
                <a href="<?php echo esc_url(get_post_type_archive_link('coursesingle') . '?orderby=date'); ?>" class="section-link">
                    <?php esc_html_e('전체보기', 'lectus-academy'); ?>
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
            
            <div class="course-grid">
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
    <section class="categories-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">
                    <?php esc_html_e('카테고리별 강의', 'lectus-academy'); ?>
                </h2>
            </div>
            
            <div class="categories-grid">
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
                <a href="<?php echo esc_url(get_term_link($category)); ?>" class="category-card" style="background: linear-gradient(135deg, <?php echo esc_attr($color); ?> 0%, <?php echo esc_attr($color); ?>aa 100%);">
                    <div class="category-card-content">
                        <h3 class="category-name"><?php echo esc_html($category->name); ?></h3>
                        <p class="category-count">
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
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2 class="cta-title">
                    <?php esc_html_e('지식을 나누고 성장하는 공간', 'lectus-academy'); ?>
                </h2>
                <p class="cta-subtitle">
                    <?php esc_html_e('당신의 지식과 경험을 공유하여 더 많은 사람들과 함께 성장하세요', 'lectus-academy'); ?>
                </p>
                <div class="cta-buttons">
                    <a href="<?php echo esc_url(home_url('/apply-instructor')); ?>" class="btn btn-primary btn-lg">
                        <?php esc_html_e('지식공유 신청하기', 'lectus-academy'); ?>
                    </a>
                    <a href="<?php echo esc_url(get_post_type_archive_link('coursesingle')); ?>" class="btn btn-outline btn-lg">
                        <?php esc_html_e('강의 둘러보기', 'lectus-academy'); ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

</main>

<style>
/* Additional styles for front page */
.popular-keywords {
    padding: 20px 0;
    background: white;
    border-bottom: 1px solid var(--border-light);
}

.keywords-list {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.keyword-label {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-secondary);
}

.keyword-tag {
    padding: 6px 12px;
    background: var(--bg-gray);
    border-radius: 20px;
    font-size: 14px;
    color: var(--text-primary);
    transition: all 0.2s;
}

.keyword-tag:hover {
    background: var(--primary-color);
    color: white;
}

.categories-section {
    padding: 60px 0;
    background: var(--bg-gray);
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.category-card {
    padding: 40px 30px;
    border-radius: var(--radius-lg);
    color: white;
    text-align: center;
    transition: transform 0.3s;
}

.category-card:hover {
    transform: translateY(-4px);
    color: white;
}

.category-name {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 8px;
}

.category-count {
    font-size: 14px;
    opacity: 0.9;
}

.cta-section {
    padding: 80px 0;
    background: linear-gradient(135deg, #30b2e5 0%, #2090c0 100%);
    color: white;
    text-align: center;
}

.cta-title {
    font-size: 36px;
    font-weight: 700;
    margin-bottom: 16px;
}

.cta-subtitle {
    font-size: 18px;
    margin-bottom: 32px;
    opacity: 0.95;
}

.cta-buttons {
    display: flex;
    gap: 16px;
    justify-content: center;
}

.btn-lg {
    padding: 14px 32px;
    font-size: 16px;
}

.btn-outline {
    background: transparent;
    border: 2px solid white;
    color: white;
}

.btn-outline:hover {
    background: white;
    color: var(--primary-color);
}
</style>

<?php
get_footer();
?>