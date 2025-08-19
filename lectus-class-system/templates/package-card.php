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

<div class="lectus-package-card" data-product-id="<?php echo esc_attr($product_id); ?>">
    <div class="package-card-header">
        <?php if ($is_package): ?>
            <span class="package-badge package-multi">
                <?php echo sprintf(__('%d개 강의 패키지', 'lectus-class-system'), count($course_ids)); ?>
            </span>
        <?php endif; ?>
        
        <?php if ($product->is_on_sale()): ?>
            <span class="sale-badge">SALE</span>
        <?php endif; ?>
        
        <?php if (has_post_thumbnail($product_id)): ?>
            <div class="package-thumbnail">
                <?php echo get_the_post_thumbnail($product_id, 'medium'); ?>
            </div>
        <?php else: ?>
            <div class="package-thumbnail placeholder">
                <img src="<?php echo LECTUS_PLUGIN_URL; ?>assets/images/package-placeholder.jpg" alt="<?php echo esc_attr($product->get_name()); ?>">
            </div>
        <?php endif; ?>
    </div>
    
    <div class="package-card-body">
        <h3 class="package-title">
            <a href="<?php echo esc_url($product->get_permalink()); ?>">
                <?php echo esc_html($product->get_name()); ?>
            </a>
        </h3>
        
        <?php if ($product->get_short_description()): ?>
            <div class="package-excerpt">
                <?php echo wp_trim_words($product->get_short_description(), 20); ?>
            </div>
        <?php endif; ?>
        
        <div class="package-info">
            <div class="info-item">
                <i class="dashicons dashicons-book-alt"></i>
                <span><?php echo sprintf(__('%d개 강의', 'lectus-class-system'), count($course_ids)); ?></span>
            </div>
            
            <div class="info-item">
                <i class="dashicons dashicons-media-document"></i>
                <span><?php echo sprintf(__('%d개 레슨', 'lectus-class-system'), $total_lessons); ?></span>
            </div>
            
            <?php if ($duration_text): ?>
                <div class="info-item">
                    <i class="dashicons dashicons-clock"></i>
                    <span><?php echo esc_html($duration_text); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($duration): ?>
                <div class="info-item">
                    <i class="dashicons dashicons-calendar-alt"></i>
                    <span><?php echo sprintf(__('수강기간 %d일', 'lectus-class-system'), $duration); ?></span>
                </div>
            <?php else: ?>
                <div class="info-item">
                    <i class="dashicons dashicons-calendar-alt"></i>
                    <span><?php _e('무제한 수강', 'lectus-class-system'); ?></span>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($courses_data)): ?>
            <div class="package-courses">
                <h4 class="courses-title"><?php _e('포함된 강의', 'lectus-class-system'); ?></h4>
                <ul class="courses-list">
                    <?php foreach ($courses_data as $course_data): ?>
                        <li>
                            <span class="course-name"><?php echo esc_html($course_data['title']); ?></span>
                            <span class="lesson-count">(<?php echo sprintf(__('%d개 레슨', 'lectus-class-system'), $course_data['lesson_count']); ?>)</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="package-card-footer">
        <div class="package-price">
            <?php echo $product->get_price_html(); ?>
        </div>
        
        <div class="package-actions">
            <?php if ($is_enrolled): ?>
                <a href="<?php echo esc_url(wc_get_account_endpoint_url('my-courses')); ?>" class="button enrolled-button">
                    <i class="dashicons dashicons-yes-alt"></i>
                    <?php _e('수강중', 'lectus-class-system'); ?>
                </a>
            <?php elseif ($enrolled_count > 0): ?>
                <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" class="button add-to-cart-button">
                    <?php echo sprintf(__('%d개 추가 수강', 'lectus-class-system'), count($course_ids) - $enrolled_count); ?>
                </a>
            <?php else: ?>
                <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" 
                   class="button add-to-cart-button <?php echo esc_attr($product->is_purchasable() && $product->is_in_stock() ? '' : 'disabled'); ?>"
                   <?php if ($product->supports('ajax_add_to_cart')): ?>
                   data-product_id="<?php echo esc_attr($product_id); ?>"
                   data-quantity="1"
                   <?php endif; ?>>
                    <?php echo esc_html($product->add_to_cart_text()); ?>
                </a>
            <?php endif; ?>
            
            <a href="<?php echo esc_url($product->get_permalink()); ?>" class="button view-details-button">
                <?php _e('상세보기', 'lectus-class-system'); ?>
            </a>
        </div>
    </div>
</div>

<style>
.lectus-package-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.3s ease;
    background: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.lectus-package-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.package-card-header {
    position: relative;
    overflow: hidden;
}

.package-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    z-index: 2;
    text-transform: uppercase;
}

.package-badge.package-multi {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.sale-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #f44336;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    z-index: 2;
}

.package-thumbnail {
    width: 100%;
    height: 200px;
    overflow: hidden;
    background: #f5f5f5;
}

.package-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.package-thumbnail.placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.package-card-body {
    padding: 20px;
}

.package-title {
    margin: 0 0 10px;
    font-size: 18px;
    font-weight: 600;
}

.package-title a {
    color: #333;
    text-decoration: none;
}

.package-title a:hover {
    color: #007cba;
}

.package-excerpt {
    color: #666;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 15px;
}

.package-info {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e0e0e0;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 13px;
    color: #666;
}

.info-item .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
    color: #007cba;
}

.package-courses {
    margin-top: 15px;
}

.courses-title {
    font-size: 14px;
    font-weight: 600;
    margin: 0 0 10px;
    color: #333;
}

.courses-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.courses-list li {
    padding: 5px 0;
    font-size: 13px;
    color: #666;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.courses-list li:before {
    content: "✓";
    color: #4CAF50;
    font-weight: bold;
    margin-right: 8px;
}

.course-name {
    flex: 1;
}

.lesson-count {
    font-size: 12px;
    color: #999;
}

.package-card-footer {
    padding: 15px 20px;
    background: #f8f8f8;
    border-top: 1px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.package-price {
    font-size: 20px;
    font-weight: 600;
    color: #333;
}

.package-price del {
    color: #999;
    font-size: 16px;
    margin-right: 5px;
}

.package-price ins {
    color: #f44336;
    text-decoration: none;
}

.package-actions {
    display: flex;
    gap: 10px;
}

.package-actions .button {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.add-to-cart-button {
    background: #007cba;
    color: white;
}

.add-to-cart-button:hover {
    background: #005a87;
}

.add-to-cart-button.disabled {
    background: #ccc;
    cursor: not-allowed;
}

.view-details-button {
    background: #fff;
    color: #007cba;
    border: 1px solid #007cba;
}

.view-details-button:hover {
    background: #007cba;
    color: white;
}

.enrolled-button {
    background: #4CAF50;
    color: white;
}

.enrolled-button .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

/* Responsive */
@media (max-width: 768px) {
    .package-info {
        flex-direction: column;
        gap: 10px;
    }
    
    .package-card-footer {
        flex-direction: column;
        gap: 15px;
    }
    
    .package-actions {
        width: 100%;
        justify-content: stretch;
    }
    
    .package-actions .button {
        flex: 1;
        justify-content: center;
    }
}
</style>