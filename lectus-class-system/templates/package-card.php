<?php
/**
 * Package Product Card Template
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get product data
$product = isset($args['product']) ? $args['product'] : null;
if (!$product || !$product instanceof WC_Product) {
    return;
}

$product_id = $product->get_id();
$course_ids = $product->get_meta('_lectus_course_ids');
$duration = $product->get_meta('_lectus_access_duration');

// Calculate package statistics
$total_lessons = 0;
$total_duration = 0;
$courses_data = array();

if (!empty($course_ids) && is_array($course_ids)) {
    foreach ($course_ids as $course_id) {
        $course = get_post($course_id);
        if (!$course) continue;
        
        $lessons = get_posts(array(
            'post_type' => 'lesson',
            'meta_key' => '_course_id',
            'meta_value' => $course_id,
            'posts_per_page' => -1
        ));
        
        $lesson_count = count($lessons);
        $total_lessons += $lesson_count;
        
        // Calculate estimated duration
        $course_duration = 0;
        foreach ($lessons as $lesson) {
            $lesson_duration = get_post_meta($lesson->ID, '_estimated_duration', true);
            if ($lesson_duration) {
                $course_duration += intval($lesson_duration);
            }
        }
        $total_duration += $course_duration;
        
        $courses_data[] = array(
            'title' => $course->post_title,
            'lesson_count' => $lesson_count
        );
    }
}

// Format duration
$duration_text = '';
if ($total_duration > 0) {
    $hours = floor($total_duration / 60);
    $minutes = $total_duration % 60;
    $duration_text = sprintf('%d시간 %d분', $hours, $minutes);
}

// Package type removed - categories will be used instead
// Check if this is a package (multiple courses) or single course
$is_package = !empty($course_ids) && is_array($course_ids) && count($course_ids) > 1;

// Check enrollment status
$is_enrolled = false;
$enrolled_count = 0;
if (is_user_logged_in() && !empty($course_ids)) {
    $user_id = get_current_user_id();
    foreach ($course_ids as $course_id) {
        if (Lectus_Enrollment::is_enrolled($user_id, $course_id)) {
            $enrolled_count++;
        }
    }
    $is_enrolled = ($enrolled_count === count($course_ids));
}
?>

<div class="package-card" data-product-id="<?php echo esc_attr($product_id); ?>">
    <div class="relative overflow-hidden">
        <?php if ($is_package): ?>
            <span class="package-badge">
                <?php echo sprintf(__('%d개 강의 패키지', 'lectus-class-system'), count($course_ids)); ?>
            </span>
        <?php endif; ?>
        
        <?php if ($product->is_on_sale()): ?>
            <span class="absolute top-3 right-3 px-3 py-1 bg-lectus-danger text-white text-xs font-semibold rounded-full z-10">
                SALE
            </span>
        <?php endif; ?>
        
        <?php if (has_post_thumbnail($product_id)): ?>
            <div class="w-full h-48 overflow-hidden bg-gray-100">
                <?php echo get_the_post_thumbnail($product_id, 'medium', array('class' => 'w-full h-full object-cover')); ?>
            </div>
        <?php else: ?>
            <div class="w-full h-48 flex items-center justify-center bg-gradient-to-br from-lectus-secondary to-lectus-secondary-dark">
                <svg class="w-16 h-16 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="card-body">
        <h3 class="text-lg font-semibold mb-2">
            <a href="<?php echo esc_url($product->get_permalink()); ?>" class="text-gray-900 hover:text-lectus-primary transition-colors">
                <?php echo esc_html($product->get_name()); ?>
            </a>
        </h3>
        
        <?php if ($product->get_short_description()): ?>
            <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                <?php echo wp_trim_words($product->get_short_description(), 20); ?>
            </p>
        <?php endif; ?>
        
        <div class="flex flex-wrap gap-3 mb-4 pb-4 border-b border-gray-200">
            <div class="package-info-item">
                <svg class="w-4 h-4 text-lectus-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <span><?php echo sprintf(__('%d개 강의', 'lectus-class-system'), count($course_ids)); ?></span>
            </div>
            
            <div class="package-info-item">
                <svg class="w-4 h-4 text-lectus-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span><?php echo sprintf(__('%d개 레슨', 'lectus-class-system'), $total_lessons); ?></span>
            </div>
            
            <?php if ($duration_text): ?>
                <div class="package-info-item">
                    <svg class="w-4 h-4 text-lectus-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span><?php echo esc_html($duration_text); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($duration): ?>
                <div class="package-info-item">
                    <svg class="w-4 h-4 text-lectus-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span><?php echo sprintf(__('수강기간 %d일', 'lectus-class-system'), $duration); ?></span>
                </div>
            <?php else: ?>
                <div class="package-info-item">
                    <svg class="w-4 h-4 text-lectus-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span><?php _e('무제한 수강', 'lectus-class-system'); ?></span>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($courses_data)): ?>
            <div class="mb-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-2"><?php _e('포함된 강의', 'lectus-class-system'); ?></h4>
                <ul class="space-y-1">
                    <?php foreach ($courses_data as $course_data): ?>
                        <li class="flex items-center justify-between text-sm">
                            <span class="flex items-center gap-1 text-gray-600">
                                <svg class="w-3 h-3 text-lectus-success" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <?php echo esc_html($course_data['title']); ?>
                            </span>
                            <span class="text-xs text-gray-500"><?php echo sprintf(__('%d개 레슨', 'lectus-class-system'), $course_data['lesson_count']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="card-footer flex items-center justify-between">
        <div class="package-price">
            <?php echo $product->get_price_html(); ?>
        </div>
        
        <div class="flex gap-2">
            <?php if ($is_enrolled): ?>
                <a href="<?php echo esc_url(wc_get_account_endpoint_url('my-courses')); ?>" class="btn btn-success">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <?php _e('수강중', 'lectus-class-system'); ?>
                </a>
            <?php elseif ($enrolled_count > 0): ?>
                <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" class="btn btn-primary">
                    <?php echo sprintf(__('%d개 추가', 'lectus-class-system'), count($course_ids) - $enrolled_count); ?>
                </a>
            <?php else: ?>
                <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" 
                   class="btn btn-primary <?php echo esc_attr($product->is_purchasable() && $product->is_in_stock() ? '' : 'opacity-50 cursor-not-allowed'); ?>"
                   <?php if ($product->supports('ajax_add_to_cart')): ?>
                   data-product_id="<?php echo esc_attr($product_id); ?>"
                   data-quantity="1"
                   <?php endif; ?>>
                    <?php echo esc_html($product->add_to_cart_text()); ?>
                </a>
            <?php endif; ?>
            
            <a href="<?php echo esc_url($product->get_permalink()); ?>" class="btn btn-outline">
                <?php _e('상세보기', 'lectus-class-system'); ?>
            </a>
        </div>
    </div>
</div>

