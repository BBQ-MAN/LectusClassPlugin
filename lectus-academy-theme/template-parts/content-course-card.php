<?php
/**
 * Template part for displaying course card
 *
 * @package LectusAcademy
 */

$course_id = get_the_ID();
$instructor_id = get_post_field('post_author', $course_id);
$instructor_name = get_the_author_meta('display_name', $instructor_id);
$enrolled_count = function_exists('lectus_academy_get_enrolled_count') ? lectus_academy_get_enrolled_count($course_id) : 0;
$lesson_count = function_exists('lectus_academy_get_course_lessons') ? count(lectus_academy_get_course_lessons($course_id)) : 0;
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