<?php
/**
 * Single Product Template for Course Products
 * 
 * This template is used to display WooCommerce products that have linked courses
 */

if (!defined('ABSPATH')) {
    exit;
}

global $product;

// Get course data
$course_ids = $product->get_meta('_lectus_course_ids');
$duration = $product->get_meta('_lectus_access_duration');
$auto_enroll = $product->get_meta('_lectus_auto_enroll');

// Check if user is enrolled
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

// Calculate statistics
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
        
        // Get instructor
        $instructor_id = get_post_field('post_author', $course_id);
        $instructor_name = get_the_author_meta('display_name', $instructor_id);
        
        $courses_data[] = array(
            'id' => $course_id,
            'title' => $course->post_title,
            'description' => $course->post_excerpt ?: wp_trim_words($course->post_content, 30),
            'lesson_count' => $lesson_count,
            'duration' => $course_duration,
            'instructor' => $instructor_name,
            'thumbnail' => get_the_post_thumbnail_url($course_id, 'medium'),
            'completion_score' => get_post_meta($course_id, '_completion_score', true) ?: 80,
            'certificate_enabled' => get_post_meta($course_id, '_certificate_enabled', true)
        );
    }
}

$is_package = count($course_ids) > 1;
?>

<div class="lectus-product-wrapper">
    <div class="container">
        <!-- Hero Section -->
        <div class="product-hero">
            <div class="hero-content">
                <div class="hero-left">
                    <?php if ($is_package): ?>
                        <span class="package-badge">
                            <i class="dashicons dashicons-portfolio"></i>
                            <?php echo sprintf(__('%d개 강의 패키지', 'lectus-class-system'), count($course_ids)); ?>
                        </span>
                    <?php endif; ?>
                    
                    <h1 class="product-title"><?php echo $product->get_name(); ?></h1>
                    
                    <?php if ($product->get_short_description()): ?>
                        <div class="product-excerpt">
                            <?php echo $product->get_short_description(); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="product-meta">
                        <div class="meta-item">
                            <i class="dashicons dashicons-book-alt"></i>
                            <span><?php echo sprintf(__('%d개 강의', 'lectus-class-system'), count($course_ids)); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="dashicons dashicons-media-document"></i>
                            <span><?php echo sprintf(__('%d개 레슨', 'lectus-class-system'), $total_lessons); ?></span>
                        </div>
                        <?php if ($total_duration > 0): 
                            $hours = floor($total_duration / 60);
                            $minutes = $total_duration % 60;
                        ?>
                        <div class="meta-item">
                            <i class="dashicons dashicons-clock"></i>
                            <span><?php echo sprintf(__('%d시간 %d분', 'lectus-class-system'), $hours, $minutes); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="meta-item">
                            <i class="dashicons dashicons-calendar-alt"></i>
                            <span><?php echo $duration ? sprintf(__('수강기간 %d일', 'lectus-class-system'), $duration) : __('무제한 수강', 'lectus-class-system'); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="hero-right">
                    <div class="purchase-box">
                        <div class="price-section">
                            <?php if ($product->is_on_sale()): ?>
                                <div class="original-price">
                                    <?php echo wc_price($product->get_regular_price()); ?>
                                </div>
                            <?php endif; ?>
                            <div class="current-price">
                                <?php echo $product->get_price_html(); ?>
                            </div>
                            <?php if ($product->is_on_sale()): 
                                $discount = round((($product->get_regular_price() - $product->get_sale_price()) / $product->get_regular_price()) * 100);
                            ?>
                                <div class="discount-badge">
                                    <?php echo sprintf(__('%d%% 할인', 'lectus-class-system'), $discount); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="purchase-actions">
                            <?php if ($is_enrolled): ?>
                                <a href="<?php echo esc_url(wc_get_account_endpoint_url('my-courses')); ?>" class="btn btn-enrolled">
                                    <i class="dashicons dashicons-yes-alt"></i>
                                    <?php _e('모든 강의 수강중', 'lectus-class-system'); ?>
                                </a>
                            <?php elseif ($enrolled_count > 0): ?>
                                <form class="cart" method="post" enctype='multipart/form-data'>
                                    <button type="submit" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" class="btn btn-primary single_add_to_cart_button">
                                        <?php echo sprintf(__('%d개 추가 수강 신청', 'lectus-class-system'), count($course_ids) - $enrolled_count); ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <form class="cart" method="post" enctype='multipart/form-data'>
                                    <button type="submit" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" class="btn btn-primary single_add_to_cart_button">
                                        <?php echo esc_html($product->single_add_to_cart_text()); ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <button class="btn btn-secondary btn-wishlist">
                                <i class="dashicons dashicons-heart"></i>
                                <?php _e('찜하기', 'lectus-class-system'); ?>
                            </button>
                        </div>
                        
                        <div class="purchase-info">
                            <div class="info-item">
                                <i class="dashicons dashicons-shield-alt"></i>
                                <span><?php _e('30일 환불 보장', 'lectus-class-system'); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="dashicons dashicons-update"></i>
                                <span><?php _e('평생 업데이트', 'lectus-class-system'); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="dashicons dashicons-awards"></i>
                                <span><?php _e('수료증 발급', 'lectus-class-system'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Course Content Section -->
        <div class="product-content">
            <div class="content-tabs">
                <ul class="tab-nav">
                    <li class="active" data-tab="courses"><?php _e('강의 정보', 'lectus-class-system'); ?></li>
                    <li data-tab="curriculum"><?php _e('커리큘럼', 'lectus-class-system'); ?></li>
                    <li data-tab="instructor"><?php _e('강사 소개', 'lectus-class-system'); ?></li>
                    <li data-tab="reviews"><?php _e('수강 후기', 'lectus-class-system'); ?></li>
                </ul>
                
                <div class="tab-content">
                    <!-- Courses Tab -->
                    <div class="tab-pane active" id="courses">
                        <?php if (!empty($courses_data)): ?>
                            <h2><?php _e('포함된 강의', 'lectus-class-system'); ?></h2>
                            <div class="courses-grid">
                                <?php foreach ($courses_data as $course_data): ?>
                                    <div class="course-card">
                                        <?php if ($course_data['thumbnail']): ?>
                                            <div class="course-thumbnail">
                                                <img src="<?php echo esc_url($course_data['thumbnail']); ?>" alt="<?php echo esc_attr($course_data['title']); ?>">
                                            </div>
                                        <?php else: ?>
                                            <div class="course-thumbnail placeholder">
                                                <i class="dashicons dashicons-welcome-learn-more"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="course-content">
                                            <h3><?php echo esc_html($course_data['title']); ?></h3>
                                            <p><?php echo esc_html($course_data['description']); ?></p>
                                            
                                            <div class="course-meta">
                                                <span class="meta-item">
                                                    <i class="dashicons dashicons-admin-users"></i>
                                                    <?php echo esc_html($course_data['instructor']); ?>
                                                </span>
                                                <span class="meta-item">
                                                    <i class="dashicons dashicons-media-document"></i>
                                                    <?php echo sprintf(__('%d개 레슨', 'lectus-class-system'), $course_data['lesson_count']); ?>
                                                </span>
                                                <?php if ($course_data['duration'] > 0): 
                                                    $hours = floor($course_data['duration'] / 60);
                                                    $minutes = $course_data['duration'] % 60;
                                                ?>
                                                <span class="meta-item">
                                                    <i class="dashicons dashicons-clock"></i>
                                                    <?php echo sprintf(__('%d시간 %d분', 'lectus-class-system'), $hours, $minutes); ?>
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="course-features">
                                                <span class="feature-item">
                                                    <i class="dashicons dashicons-yes"></i>
                                                    <?php echo sprintf(__('수료 기준 %d%%', 'lectus-class-system'), $course_data['completion_score']); ?>
                                                </span>
                                                <?php if ($course_data['certificate_enabled']): ?>
                                                <span class="feature-item">
                                                    <i class="dashicons dashicons-awards"></i>
                                                    <?php _e('수료증 발급', 'lectus-class-system'); ?>
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Product Description -->
                        <div class="product-description">
                            <h2><?php _e('상세 설명', 'lectus-class-system'); ?></h2>
                            <?php echo $product->get_description(); ?>
                        </div>
                    </div>
                    
                    <!-- Curriculum Tab -->
                    <div class="tab-pane" id="curriculum">
                        <h2><?php _e('전체 커리큘럼', 'lectus-class-system'); ?></h2>
                        <?php foreach ($courses_data as $course_data): 
                            $lessons = get_posts(array(
                                'post_type' => 'lesson',
                                'meta_key' => '_course_id',
                                'meta_value' => $course_data['id'],
                                'orderby' => 'menu_order',
                                'order' => 'ASC',
                                'posts_per_page' => -1
                            ));
                        ?>
                        <div class="curriculum-section">
                            <h3><?php echo esc_html($course_data['title']); ?></h3>
                            <div class="lesson-list">
                                <?php foreach ($lessons as $index => $lesson): 
                                    $lesson_type = get_post_meta($lesson->ID, '_lesson_type', true);
                                    $lesson_duration = get_post_meta($lesson->ID, '_estimated_duration', true);
                                ?>
                                <div class="lesson-item">
                                    <span class="lesson-number"><?php echo ($index + 1); ?></span>
                                    <span class="lesson-title"><?php echo esc_html($lesson->post_title); ?></span>
                                    <span class="lesson-type">
                                        <?php
                                        switch($lesson_type) {
                                            case 'video': echo '<i class="dashicons dashicons-video-alt3"></i>'; break;
                                            case 'text': echo '<i class="dashicons dashicons-text-page"></i>'; break;
                                            case 'quiz': echo '<i class="dashicons dashicons-forms"></i>'; break;
                                            case 'assignment': echo '<i class="dashicons dashicons-edit"></i>'; break;
                                            default: echo '<i class="dashicons dashicons-media-default"></i>';
                                        }
                                        ?>
                                    </span>
                                    <?php if ($lesson_duration): ?>
                                    <span class="lesson-duration"><?php echo $lesson_duration; ?>분</span>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Instructor Tab -->
                    <div class="tab-pane" id="instructor">
                        <h2><?php _e('강사 소개', 'lectus-class-system'); ?></h2>
                        <?php 
                        // Get unique instructors
                        $instructors = array();
                        foreach ($courses_data as $course_data) {
                            $course_id = $course_data['id'];
                            $instructor_id = get_post_field('post_author', $course_id);
                            if (!isset($instructors[$instructor_id])) {
                                $instructors[$instructor_id] = array(
                                    'name' => get_the_author_meta('display_name', $instructor_id),
                                    'bio' => get_the_author_meta('description', $instructor_id),
                                    'avatar' => get_avatar_url($instructor_id, array('size' => 150)),
                                    'courses' => array()
                                );
                            }
                            $instructors[$instructor_id]['courses'][] = $course_data['title'];
                        }
                        ?>
                        
                        <?php foreach ($instructors as $instructor): ?>
                        <div class="instructor-card">
                            <img src="<?php echo esc_url($instructor['avatar']); ?>" alt="<?php echo esc_attr($instructor['name']); ?>" class="instructor-avatar">
                            <div class="instructor-info">
                                <h3><?php echo esc_html($instructor['name']); ?></h3>
                                <p><?php echo esc_html($instructor['bio'] ?: __('강사 소개가 준비 중입니다.', 'lectus-class-system')); ?></p>
                                <div class="instructor-courses">
                                    <strong><?php _e('담당 강의:', 'lectus-class-system'); ?></strong>
                                    <?php echo implode(', ', $instructor['courses']); ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Reviews Tab -->
                    <div class="tab-pane" id="reviews">
                        <h2><?php _e('수강 후기', 'lectus-class-system'); ?></h2>
                        <?php
                        // If WooCommerce reviews are enabled
                        if (comments_open()) {
                            comments_template();
                        } else {
                            echo '<p>' . __('아직 등록된 후기가 없습니다.', 'lectus-class-system') . '</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.lectus-product-wrapper {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, sans-serif;
    color: #333;
    line-height: 1.6;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Hero Section */
.product-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 60px 0;
    margin-bottom: 40px;
}

.hero-content {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 40px;
    align-items: start;
}

.package-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(255, 255, 255, 0.2);
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    margin-bottom: 20px;
}

.product-title {
    font-size: 36px;
    font-weight: 700;
    margin: 0 0 20px;
    line-height: 1.2;
}

.product-excerpt {
    font-size: 18px;
    opacity: 0.95;
    margin-bottom: 30px;
    line-height: 1.6;
}

.product-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 16px;
}

.meta-item .dashicons {
    font-size: 20px;
    width: 20px;
    height: 20px;
    opacity: 0.9;
}

/* Purchase Box */
.purchase-box {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
}

.price-section {
    text-align: center;
    padding-bottom: 20px;
    border-bottom: 1px solid #e0e0e0;
    margin-bottom: 20px;
}

.original-price {
    color: #999;
    text-decoration: line-through;
    font-size: 18px;
    margin-bottom: 5px;
}

.current-price {
    font-size: 32px;
    font-weight: 700;
    color: #333;
}

.discount-badge {
    display: inline-block;
    background: #ff5722;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 14px;
    margin-top: 10px;
}

.purchase-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 20px;
}

.btn {
    padding: 14px 24px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none;
}

.btn-primary {
    background: #007cba;
    color: white;
}

.btn-primary:hover {
    background: #005a87;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 124, 186, 0.3);
}

.btn-secondary {
    background: #f0f0f0;
    color: #333;
}

.btn-secondary:hover {
    background: #e0e0e0;
}

.btn-enrolled {
    background: #4caf50;
    color: white;
}

.btn-wishlist .dashicons {
    color: #ff5722;
}

.purchase-info {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.purchase-info .info-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    color: #666;
}

/* Content Tabs */
.content-tabs {
    margin-top: 40px;
}

.tab-nav {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    border-bottom: 2px solid #e0e0e0;
}

.tab-nav li {
    padding: 15px 30px;
    cursor: pointer;
    font-weight: 600;
    color: #666;
    transition: all 0.3s ease;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
}

.tab-nav li:hover {
    color: #007cba;
}

.tab-nav li.active {
    color: #007cba;
    border-bottom-color: #007cba;
}

.tab-content {
    padding: 40px 0;
}

.tab-pane {
    display: none;
}

.tab-pane.active {
    display: block;
}

/* Courses Grid */
.courses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 30px;
    margin: 30px 0;
}

.course-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.course-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.course-thumbnail {
    width: 100%;
    height: 200px;
    overflow: hidden;
    background: #f5f5f5;
}

.course-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.course-thumbnail.placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.course-thumbnail.placeholder .dashicons {
    font-size: 60px;
    color: white;
    opacity: 0.5;
}

.course-content {
    padding: 20px;
}

.course-content h3 {
    margin: 0 0 10px;
    font-size: 18px;
    color: #333;
}

.course-content p {
    color: #666;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 15px;
}

.course-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e0e0e0;
}

.course-meta .meta-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 13px;
    color: #666;
}

.course-features {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 13px;
    color: #4caf50;
}

/* Curriculum */
.curriculum-section {
    margin-bottom: 40px;
}

.curriculum-section h3 {
    background: #f5f5f5;
    padding: 15px 20px;
    margin: 0 0 20px;
    border-left: 4px solid #007cba;
}

.lesson-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.lesson-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 20px;
    background: #f9f9f9;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.lesson-item:hover {
    background: #f0f0f0;
}

.lesson-number {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    background: #007cba;
    color: white;
    border-radius: 50%;
    font-size: 14px;
    font-weight: 600;
}

.lesson-title {
    flex: 1;
    font-weight: 500;
}

.lesson-type .dashicons {
    font-size: 20px;
    color: #666;
}

.lesson-duration {
    color: #999;
    font-size: 14px;
}

/* Instructor */
.instructor-card {
    display: flex;
    gap: 30px;
    padding: 30px;
    background: #f9f9f9;
    border-radius: 8px;
    margin-bottom: 20px;
}

.instructor-avatar {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
}

.instructor-info {
    flex: 1;
}

.instructor-info h3 {
    margin: 0 0 15px;
    font-size: 24px;
    color: #333;
}

.instructor-info p {
    color: #666;
    line-height: 1.6;
    margin-bottom: 15px;
}

.instructor-courses {
    color: #666;
    font-size: 14px;
}

/* Responsive */
@media (max-width: 768px) {
    .hero-content {
        grid-template-columns: 1fr;
    }
    
    .courses-grid {
        grid-template-columns: 1fr;
    }
    
    .tab-nav {
        flex-wrap: wrap;
    }
    
    .tab-nav li {
        flex: 1;
        text-align: center;
        padding: 10px;
        font-size: 14px;
    }
    
    .instructor-card {
        flex-direction: column;
        text-align: center;
    }
    
    .instructor-avatar {
        margin: 0 auto;
    }
}
</style>

<script>
(function() {
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProductPage);
    } else {
        initProductPage();
    }
    
    function initProductPage() {
        // Tab switching
        var tabNavItems = document.querySelectorAll('.tab-nav li');
        tabNavItems.forEach(function(item) {
            item.addEventListener('click', function() {
                var tabId = this.getAttribute('data-tab');
                
                // Update nav
                tabNavItems.forEach(function(navItem) {
                    navItem.classList.remove('active');
                });
                this.classList.add('active');
                
                // Update content
                var tabPanes = document.querySelectorAll('.tab-pane');
                tabPanes.forEach(function(pane) {
                    pane.classList.remove('active');
                });
                var targetPane = document.getElementById(tabId);
                if (targetPane) {
                    targetPane.classList.add('active');
                }
            });
        });
        
        // Wishlist button
        var wishlistBtn = document.querySelector('.btn-wishlist');
        if (wishlistBtn) {
            wishlistBtn.addEventListener('click', function() {
                this.classList.toggle('active');
                var icon = this.querySelector('.dashicons');
                if (icon) {
                    if (this.classList.contains('active')) {
                        icon.classList.remove('dashicons-heart');
                        icon.classList.add('dashicons-yes');
                    } else {
                        icon.classList.remove('dashicons-yes');
                        icon.classList.add('dashicons-heart');
                    }
                }
            });
        }
    }
})();
</script>