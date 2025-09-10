<?php
/**
 * Template for displaying course card
 * This template ensures consistent display between theme and plugin
 *
 * @package LectusClassSystem
 */

// Get course data
$course_id = isset($course_id) ? $course_id : get_the_ID();
$instructor_id = get_post_field('post_author', $course_id);
$instructor_name = get_the_author_meta('display_name', $instructor_id);
$instructor_avatar = get_avatar_url($instructor_id, array('size' => 40));

// Get enrollment and lesson counts
$enrolled_count = 0;
if (class_exists('Lectus_Enrollment')) {
    $enrolled_count = Lectus_Enrollment::get_course_enrollment_count($course_id);
}

$lessons = get_posts(array(
    'post_type' => 'lesson',
    'meta_key' => '_course_id',
    'meta_value' => $course_id,
    'posts_per_page' => -1
));
$lesson_count = count($lessons);

// Get course level
$course_level = get_the_terms($course_id, 'course_level');
$level_name = $course_level && !is_wp_error($course_level) ? $course_level[0]->name : '';
if (!$level_name) {
    $difficulty = get_post_meta($course_id, '_course_difficulty', true);
    $level_name = $difficulty ?: '초급';
}

// Get duration
$duration = get_post_meta($course_id, '_course_duration', true);

// Get price information
$product_id = get_post_meta($course_id, '_linked_product_id', true);
if (!$product_id) {
    $product_id = get_post_meta($course_id, '_product_id', true);
}

$price_html = '무료';
$original_price = '';
$sale_price = '';
$discount_percent = 0;
$is_free = true;

if ($product_id && function_exists('wc_get_product')) {
    $product = wc_get_product($product_id);
    if ($product) {
        $is_free = false;
        if ($product->is_on_sale()) {
            $original_price = $product->get_regular_price();
            $sale_price = $product->get_sale_price();
            if ($original_price > 0) {
                $discount_percent = round((($original_price - $sale_price) / $original_price) * 100);
            }
            $price_html = wc_price($sale_price);
        } else {
            $price_html = $product->get_price_html();
        }
    }
} else {
    // Check for custom price field
    $custom_price = get_post_meta($course_id, '_course_price', true);
    if ($custom_price && $custom_price > 0) {
        $is_free = false;
        $price_html = '₩' . number_format($custom_price);
    }
}

// Get rating (for now, use sample data - later implement actual rating system)
$rating = 4.5;
$rating_count = rand(10, 200);

// Check if course is new (published within last 7 days)
$is_new = (time() - get_post_time('U', false, $course_id)) < 7 * DAY_IN_SECONDS;
?>

<article class="course-card bg-white rounded-lg shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden group">
    <a href="<?php echo get_permalink($course_id); ?>" class="block">
        <!-- Thumbnail -->
        <div class="course-thumbnail relative aspect-video overflow-hidden bg-gray-100">
            <?php if (has_post_thumbnail($course_id)) : ?>
                <?php echo get_the_post_thumbnail($course_id, 'medium', array('class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300')); ?>
            <?php else : ?>
                <div class="w-full h-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                    <i class="fas fa-graduation-cap text-white text-4xl opacity-50"></i>
                </div>
            <?php endif; ?>
            
            <!-- Badges -->
            <div class="absolute top-3 left-3 flex gap-2">
                <?php if ($discount_percent > 0) : ?>
                    <span class="px-3 py-1 bg-red-500 text-white rounded-full text-xs font-bold">
                        <?php echo $discount_percent; ?>%
                    </span>
                <?php elseif ($is_new) : ?>
                    <span class="px-3 py-1 bg-green-500 text-white rounded-full text-xs font-bold">
                        NEW
                    </span>
                <?php endif; ?>
                
                <?php if ($level_name) : ?>
                    <span class="px-3 py-1 bg-white/90 backdrop-blur-sm rounded-full text-xs font-medium text-gray-700">
                        <?php echo esc_html($level_name); ?>
                    </span>
                <?php endif; ?>
            </div>
            
            <!-- Price Badge -->
            <?php if ($is_free) : ?>
                <div class="absolute top-3 right-3">
                    <span class="px-3 py-1 bg-green-500 text-white rounded-full text-xs font-bold">
                        무료
                    </span>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Content -->
        <div class="p-5">
            <!-- Title -->
            <h3 class="font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">
                <?php echo get_the_title($course_id); ?>
            </h3>
            
            <!-- Instructor -->
            <div class="flex items-center gap-2 mb-3">
                <img src="<?php echo esc_url($instructor_avatar); ?>" 
                     alt="<?php echo esc_attr($instructor_name); ?>" 
                     class="w-6 h-6 rounded-full">
                <span class="text-sm text-gray-600"><?php echo esc_html($instructor_name); ?></span>
            </div>
            
            <!-- Rating -->
            <div class="flex items-center gap-2 mb-3">
                <div class="flex text-yellow-400 text-sm">
                    <?php 
                    $full_stars = floor($rating);
                    $has_half = ($rating - $full_stars) >= 0.5;
                    
                    for ($i = 0; $i < $full_stars; $i++) {
                        echo '<i class="fas fa-star"></i>';
                    }
                    if ($has_half) {
                        echo '<i class="fas fa-star-half-alt"></i>';
                        $full_stars++;
                    }
                    for ($i = $full_stars; $i < 5; $i++) {
                        echo '<i class="far fa-star"></i>';
                    }
                    ?>
                </div>
                <span class="text-sm text-gray-600"><?php echo $rating; ?></span>
                <span class="text-sm text-gray-400">(<?php echo $rating_count; ?>)</span>
            </div>
            
            <!-- Meta Info -->
            <div class="flex items-center gap-4 text-sm text-gray-500 mb-4">
                <?php if ($duration) : ?>
                <span class="flex items-center gap-1">
                    <i class="far fa-clock"></i>
                    <?php echo $duration; ?>일
                </span>
                <?php endif; ?>
                <span class="flex items-center gap-1">
                    <i class="far fa-play-circle"></i>
                    <?php echo $lesson_count; ?>강
                </span>
                <span class="flex items-center gap-1">
                    <i class="far fa-user"></i>
                    <?php echo number_format($enrolled_count); ?>명
                </span>
            </div>
            
            <!-- Price -->
            <?php if (!$is_free) : ?>
            <div class="flex items-center gap-2 pt-3 border-t">
                <?php if ($discount_percent > 0) : ?>
                    <span class="text-gray-400 line-through text-sm">
                        <?php echo wc_price($original_price); ?>
                    </span>
                    <span class="text-red-500 font-bold">
                        <?php echo $discount_percent; ?>%
                    </span>
                <?php endif; ?>
                <span class="text-lg font-bold text-gray-900">
                    <?php echo $price_html; ?>
                </span>
            </div>
            <?php endif; ?>
        </div>
    </a>
</article>