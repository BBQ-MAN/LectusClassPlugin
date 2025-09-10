<?php
/**
 * Shortcodes for Lectus Class System
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Shortcodes {
    
    public static function init() {
        // Course shortcodes
        add_shortcode('lectus_courses', array(__CLASS__, 'courses_list'));
        add_shortcode('lectus_course', array(__CLASS__, 'single_course'));
        add_shortcode('lectus_my_courses', array(__CLASS__, 'my_courses'));
        add_shortcode('lectus_course_progress', array(__CLASS__, 'course_progress'));
        add_shortcode('lectus_featured_courses', array(__CLASS__, 'featured_courses'));
        add_shortcode('lectus_course_categories', array(__CLASS__, 'course_categories'));
        
        // Package shortcodes
        add_shortcode('lectus_packages', array(__CLASS__, 'packages_list'));
        
        // Enrollment shortcodes
        add_shortcode('lectus_enroll_button', array(__CLASS__, 'enroll_button'));
        add_shortcode('lectus_enrollment_form', array(__CLASS__, 'enrollment_form'));
        
        // Certificate shortcodes
        add_shortcode('lectus_certificates', array(__CLASS__, 'certificates'));
        add_shortcode('lectus_certificate_verify', array(__CLASS__, 'certificate_verify'));
        
        // Student dashboard shortcode
        add_shortcode('lectus_student_dashboard', array(__CLASS__, 'student_dashboard'));
        
        // Authentication shortcodes
        add_shortcode('lectus_login_form', array(__CLASS__, 'login_form'));
        add_shortcode('lectus_registration_form', array(__CLASS__, 'registration_form'));
        add_shortcode('lectus_user_profile', array(__CLASS__, 'user_profile'));
        
        // Search shortcode
        add_shortcode('lectus_search_form', array(__CLASS__, 'search_form'));
    }
    
    public static function courses_list($atts) {
        $atts = shortcode_atts(array(
            'type' => 'coursesingle',
            'category' => '',
            'limit' => 12,
            'columns' => 4,
            'orderby' => 'date',
            'order' => 'DESC'
        ), $atts);
        
        $args = array(
            'post_type' => $atts['type'],
            'posts_per_page' => $atts['limit'],
            'orderby' => $atts['orderby'],
            'order' => $atts['order']
        );
        
        if (!empty($atts['category'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'course_category',
                    'field' => 'slug',
                    'terms' => $atts['category']
                )
            );
        }
        
        $courses = new WP_Query($args);
        
        ob_start();
        ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php if ($courses->have_posts()): ?>
                <?php while ($courses->have_posts()): $courses->the_post(); 
                    $course_id = get_the_ID();
                    
                    // Use the unified template for course card
                    include LECTUS_PLUGIN_DIR . 'templates/course-card.php';
                ?>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            <?php else: ?>
                <div class="col-span-full text-center py-12">
                    <i class="fas fa-book-open text-6xl text-gray-300 mb-4"></i>
                    <p class="text-xl text-gray-500"><?php _e('아직 등록된 강의가 없습니다.', 'lectus-class-system'); ?></p>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Display package products list
     */
    public static function packages_list($atts) {
        $atts = shortcode_atts(array(
            'limit' => 12,
            'columns' => 3,
            'orderby' => 'date',
            'order' => 'DESC',
            'category' => ''
        ), $atts);
        
        // Get products with course packages
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => $atts['limit'],
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
            'meta_query' => array(
                array(
                    'key' => '_lectus_course_ids',
                    'compare' => 'EXISTS'
                )
            )
        );
        
        // Package type removed - using categories instead
        
        // Filter by product category if specified
        if (!empty($atts['category'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => $atts['category']
                )
            );
        }
        
        $products = new WP_Query($args);
        
        ob_start();
        ?>
        <div class="lectus-packages-grid" style="display: grid; grid-template-columns: repeat(<?php echo esc_attr($atts['columns']); ?>, 1fr); gap: 30px;">
            <?php if ($products->have_posts()): ?>
                <?php while ($products->have_posts()): $products->the_post(); 
                    $product = wc_get_product(get_the_ID());
                    if (!$product) continue;
                    
                    // Load package card template
                    include LECTUS_PLUGIN_DIR . 'templates/package-card.php';
                ?>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                    <p><?php _e('패키지 상품이 없습니다.', 'lectus-class-system'); ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <style>
        @media (max-width: 1024px) {
            .lectus-packages-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }
        
        @media (max-width: 640px) {
            .lectus-packages-grid {
                grid-template-columns: 1fr !important;
            }
        }
        </style>
        <?php
        return ob_get_clean();
    }
    
    public static function my_courses($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . __('로그인이 필요합니다.', 'lectus-class-system') . '</p>';
        }
        
        $user_id = get_current_user_id();
        $enrollments = Lectus_Enrollment::get_user_enrollments($user_id);
        
        ob_start();
        ?>
        <div class="lectus-my-courses">
            <?php if (!empty($enrollments)): ?>
                <table class="my-courses-table">
                    <thead>
                        <tr>
                            <th><?php _e('강의명', 'lectus-class-system'); ?></th>
                            <th><?php _e('진도율', 'lectus-class-system'); ?></th>
                            <th><?php _e('등록일', 'lectus-class-system'); ?></th>
                            <th><?php _e('만료일', 'lectus-class-system'); ?></th>
                            <th><?php _e('상태', 'lectus-class-system'); ?></th>
                            <th><?php _e('동작', 'lectus-class-system'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrollments as $enrollment): 
                            $course = get_post($enrollment->course_id);
                            if (!$course) continue;
                            
                            $progress = Lectus_Progress::get_course_progress($user_id, $enrollment->course_id);
                        ?>
                            <tr>
                                <td>
                                    <a href="<?php echo get_permalink($course->ID); ?>">
                                        <?php echo esc_html($course->post_title); ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $progress; ?>%;"></div>
                                        <span><?php echo $progress; ?>%</span>
                                    </div>
                                </td>
                                <td><?php echo date_i18n(get_option('date_format'), strtotime($enrollment->enrolled_at)); ?></td>
                                <td>
                                    <?php 
                                    echo $enrollment->expires_at ? 
                                        date_i18n(get_option('date_format'), strtotime($enrollment->expires_at)) : 
                                        __('무제한', 'lectus-class-system');
                                    ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $enrollment->status; ?>">
                                        <?php echo Lectus_Enrollment::get_status_label($enrollment->status); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php $continue_url = Lectus_Progress::get_continue_learning_url($user_id, $course->ID); ?>
                                    <a href="<?php echo esc_url($continue_url); ?>" class="button button-small">
                                        <?php _e('계속 학습', 'lectus-class-system'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?php _e('등록한 강의가 없습니다.', 'lectus-class-system'); ?></p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public static function course_progress($atts) {
        $atts = shortcode_atts(array(
            'course_id' => get_the_ID(),
            'user_id' => get_current_user_id()
        ), $atts);
        
        if (!$atts['user_id']) {
            return '';
        }
        
        $progress = Lectus_Progress::get_course_progress($atts['user_id'], $atts['course_id']);
        
        ob_start();
        ?>
        <div class="lectus-course-progress">
            <div class="progress-bar large">
                <div class="progress-fill" style="width: <?php echo $progress; ?>%;">
                    <span><?php echo $progress; ?>%</span>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public static function enroll_button($atts) {
        $atts = shortcode_atts(array(
            'course_id' => get_the_ID(),
            'course_type' => '',
            'text' => __('수강 신청', 'lectus-class-system'),
            'purchase_text' => __('구매하기', 'lectus-class-system'),
            'class' => 'button button-primary'
        ), $atts);
        
        $course_id = $atts['course_id'];
        $course = get_post($course_id);
        
        if (!$course) {
            return '<span class="button button-disabled">' . __('강의를 찾을 수 없습니다', 'lectus-class-system') . '</span>';
        }
        
        // Determine course type if not provided
        if (empty($atts['course_type'])) {
            $atts['course_type'] = $course->post_type;
        }
        
        if (!is_user_logged_in()) {
            return '<a href="' . wp_login_url(get_permalink($course_id)) . '" class="' . esc_attr($atts['class']) . '">' . 
                   __('로그인하여 수강 신청', 'lectus-class-system') . '</a>';
        }
        
        $user_id = get_current_user_id();
        
        // Check if user is already enrolled
        if (Lectus_Enrollment::is_enrolled($user_id, $course_id)) {
            $continue_url = Lectus_Progress::get_continue_learning_url($user_id, $course_id);
            return '<a href="' . esc_url($continue_url) . '" class="' . esc_attr($atts['class']) . '">' . 
                   __('학습 계속하기', 'lectus-class-system') . '</a>';
        }
        
        // Check course price
        $price = get_post_meta($course_id, '_course_price', true);
        $is_free = (!$price || !is_numeric($price) || $price <= 0);
        
        // Check if course has WooCommerce product
        if (Lectus_WooCommerce::course_has_product($course_id)) {
            // Course has WooCommerce product - show purchase button
            return '<button class="lectus-purchase-btn ' . esc_attr($atts['class']) . '" ' .
                   'data-course-id="' . esc_attr($course_id) . '" ' .
                   'data-course-type="' . esc_attr($atts['course_type']) . '">' . 
                   esc_html($atts['purchase_text']) . '</button>';
        } elseif ($is_free) {
            // Free course - show free enrollment button
            return '<button class="lectus-enroll-btn ' . esc_attr($atts['class']) . '" ' .
                   'data-course-id="' . esc_attr($course_id) . '">' . 
                   __('무료 수강 신청', 'lectus-class-system') . '</button>';
        } else {
            // Paid course without WooCommerce product
            return '<span class="button button-disabled" title="' . __('상품 준비 중입니다', 'lectus-class-system') . '">' . 
                   __('준비 중', 'lectus-class-system') . '</span>';
        }
    }
    
    public static function certificates($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . __('로그인이 필요합니다.', 'lectus-class-system') . '</p>';
        }
        
        $user_id = get_current_user_id();
        $certificates = Lectus_Certificate::get_user_certificates($user_id);
        
        ob_start();
        ?>
        <div class="lectus-certificates">
            <?php if (!empty($certificates)): ?>
                <table class="certificates-table">
                    <thead>
                        <tr>
                            <th><?php _e('강의명', 'lectus-class-system'); ?></th>
                            <th><?php _e('수료증 번호', 'lectus-class-system'); ?></th>
                            <th><?php _e('발급일', 'lectus-class-system'); ?></th>
                            <th><?php _e('동작', 'lectus-class-system'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($certificates as $certificate): 
                            $course = get_post($certificate->course_id);
                            if (!$course) continue;
                        ?>
                            <tr>
                                <td><?php echo esc_html($course->post_title); ?></td>
                                <td><?php echo esc_html($certificate->certificate_number); ?></td>
                                <td><?php echo date_i18n(get_option('date_format'), strtotime($certificate->issued_at)); ?></td>
                                <td>
                                    <a href="<?php echo Lectus_Certificate::get_certificate_url($certificate->id); ?>" 
                                       class="button button-small" target="_blank">
                                        <?php _e('다운로드', 'lectus-class-system'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?php _e('발급받은 수료증이 없습니다.', 'lectus-class-system'); ?></p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public static function certificate_verify($atts) {
        ob_start();
        ?>
        <div class="lectus-certificate-verify">
            <form method="get" action="">
                <label for="cert_number"><?php _e('수료증 번호:', 'lectus-class-system'); ?></label>
                <input type="text" name="cert_number" id="cert_number" required />
                <button type="submit"><?php _e('확인', 'lectus-class-system'); ?></button>
            </form>
            
            <?php
            if (isset($_GET['cert_number'])) {
                $cert_number = sanitize_text_field($_GET['cert_number']);
                $certificate = Lectus_Certificate::verify($cert_number);
                
                if ($certificate) {
                    $user = get_user_by('id', $certificate->user_id);
                    $course = get_post($certificate->course_id);
                    ?>
                    <div class="verification-result success">
                        <h3><?php _e('유효한 수료증입니다', 'lectus-class-system'); ?></h3>
                        <p><strong><?php _e('수강생:', 'lectus-class-system'); ?></strong> <?php echo esc_html($user->display_name); ?></p>
                        <p><strong><?php _e('강의:', 'lectus-class-system'); ?></strong> <?php echo esc_html($course->post_title); ?></p>
                        <p><strong><?php _e('발급일:', 'lectus-class-system'); ?></strong> <?php echo date_i18n(get_option('date_format'), strtotime($certificate->issued_at)); ?></p>
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="verification-result error">
                        <p><?php _e('유효하지 않은 수료증 번호입니다.', 'lectus-class-system'); ?></p>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public static function student_dashboard($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . __('로그인이 필요합니다.', 'lectus-class-system') . '</p>';
        }
        
        $user = wp_get_current_user();
        
        // If user is instructor, redirect to instructor center
        if (in_array('lectus_instructor', $user->roles)) {
            wp_redirect(admin_url('admin.php?page=lectus-instructor-center'));
            exit;
        }
        
        ob_start();
        
        // Include the student dashboard template
        $template_file = LECTUS_PLUGIN_DIR . 'templates/student-dashboard.php';
        if (file_exists($template_file)) {
            include $template_file;
        } else {
            echo '<p>' . __('대시보드 템플릿을 찾을 수 없습니다.', 'lectus-class-system') . '</p>';
        }
        
        return ob_get_clean();
    }
    
    /**
     * Display featured courses
     */
    public static function featured_courses($atts) {
        $atts = shortcode_atts(array(
            'limit' => 8,
            'columns' => 4,
            'orderby' => 'meta_value_num',
            'meta_key' => '_course_featured',
            'order' => 'DESC'
        ), $atts);
        
        $args = array(
            'post_type' => 'coursesingle',
            'posts_per_page' => $atts['limit'],
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => '_course_featured',
                    'value' => '1',
                    'compare' => '='
                ),
                array(
                    'key' => '_course_popular',
                    'value' => '1',
                    'compare' => '='
                )
            )
        );
        
        // If no featured courses, get recent courses
        $featured = new WP_Query($args);
        if (!$featured->have_posts()) {
            $args['meta_query'] = array();
            $args['orderby'] = 'date';
            $featured = new WP_Query($args);
        }
        
        ob_start();
        ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-<?php echo $atts['columns']; ?> gap-6">
            <?php if ($featured->have_posts()): ?>
                <?php while ($featured->have_posts()): $featured->the_post(); 
                    $course_id = get_the_ID();
                    // Use the unified template for course card
                    include LECTUS_PLUGIN_DIR . 'templates/course-card.php';
                ?>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            <?php else: ?>
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500"><?php _e('강의가 없습니다.', 'lectus-class-system'); ?></p>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Display course categories
     */
    public static function course_categories($atts) {
        $atts = shortcode_atts(array(
            'show_count' => true,
            'show_empty' => false,
            'columns' => 4,
            'limit' => 0
        ), $atts);
        
        $args = array(
            'taxonomy' => 'course_category',
            'hide_empty' => !$atts['show_empty'],
            'number' => $atts['limit']
        );
        
        $categories = get_terms($args);
        
        if (empty($categories) || is_wp_error($categories)) {
            return '<p>' . __('카테고리가 없습니다.', 'lectus-class-system') . '</p>';
        }
        
        ob_start();
        ?>
        <div class="lectus-course-categories" style="display: grid; grid-template-columns: repeat(<?php echo min($atts['columns'], count($categories)); ?>, 1fr); gap: 20px; margin: 20px 0;">
            <?php foreach ($categories as $category): 
                $icon_class = 'fas fa-book'; // Default icon
                $bg_color = '#' . substr(md5($category->name), 0, 6); // Generate color from name
            ?>
                <a href="<?php echo get_term_link($category); ?>" class="category-card" style="display: block; padding: 30px 20px; background: white; border: 1px solid #e5e7eb; border-radius: 8px; text-align: center; text-decoration: none; color: inherit; transition: all 0.3s; box-shadow: 0 1px 3px rgba(0,0,0,0.1);" 
                   onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" 
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)';">
                    <div class="category-icon" style="width: 60px; height: 60px; margin: 0 auto 15px; background: linear-gradient(135deg, <?php echo $bg_color; ?>22, <?php echo $bg_color; ?>44); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="<?php echo $icon_class; ?>" style="font-size: 24px; color: <?php echo $bg_color; ?>;"></i>
                    </div>
                    <h3 style="margin: 0 0 8px; font-size: 18px; font-weight: 600; color: #1f2937;"><?php echo esc_html($category->name); ?></h3>
                    <?php if ($atts['show_count']): ?>
                        <span style="color: #6b7280; font-size: 14px;"><?php echo sprintf(__('%d개 강의', 'lectus-class-system'), $category->count); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($category->description)): ?>
                        <p style="margin: 10px 0 0; color: #6b7280; font-size: 14px; line-height: 1.5;"><?php echo esc_html($category->description); ?></p>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
        
        <style>
        @media (max-width: 1024px) {
            .lectus-course-categories {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }
        @media (max-width: 640px) {
            .lectus-course-categories {
                grid-template-columns: 1fr !important;
            }
        }
        </style>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Display login form
     */
    public static function login_form($atts) {
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            return '<p>' . sprintf(__('안녕하세요, %s님! 이미 로그인되어 있습니다.', 'lectus-class-system'), $user->display_name) . ' <a href="' . wp_logout_url() . '">' . __('로그아웃', 'lectus-class-system') . '</a></p>';
        }
        
        $atts = shortcode_atts(array(
            'redirect' => '',
            'form_id' => 'lectus-login-form',
            'label_username' => __('사용자명 또는 이메일', 'lectus-class-system'),
            'label_password' => __('비밀번호', 'lectus-class-system'),
            'label_remember' => __('로그인 상태 유지', 'lectus-class-system'),
            'label_log_in' => __('로그인', 'lectus-class-system')
        ), $atts);
        
        $args = array(
            'echo' => false,
            'redirect' => $atts['redirect'] ?: home_url('/student-dashboard/'),
            'form_id' => $atts['form_id'],
            'label_username' => $atts['label_username'],
            'label_password' => $atts['label_password'],
            'label_remember' => $atts['label_remember'],
            'label_log_in' => $atts['label_log_in'],
            'remember' => true
        );
        
        $form = wp_login_form($args);
        
        // Add custom styling
        $styled_form = '<div class="lectus-login-form-wrapper" style="max-width: 400px; margin: 0 auto; padding: 30px; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">';
        $styled_form .= '<h2 style="margin: 0 0 25px; text-align: center; color: #1f2937; font-size: 24px;">' . __('로그인', 'lectus-class-system') . '</h2>';
        $styled_form .= str_replace('class="', 'style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px;" class="', $form);
        $styled_form .= '<p style="text-align: center; margin-top: 20px;">';
        $styled_form .= '<a href="' . wp_lostpassword_url() . '" style="color: #3b82f6; text-decoration: none;">' . __('비밀번호를 잊으셨나요?', 'lectus-class-system') . '</a>';
        $styled_form .= ' | ';
        $styled_form .= '<a href="' . wp_registration_url() . '" style="color: #3b82f6; text-decoration: none;">' . __('회원가입', 'lectus-class-system') . '</a>';
        $styled_form .= '</p>';
        $styled_form .= '</div>';
        
        // Add CSS for button styling
        $styled_form .= '<style>
        #' . $atts['form_id'] . ' input[type="submit"] {
            width: 100%;
            padding: 12px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
        }
        #' . $atts['form_id'] . ' input[type="submit"]:hover {
            background: #2563eb;
        }
        </style>';
        
        return $styled_form;
    }
    
    /**
     * Display registration form
     */
    public static function registration_form($atts) {
        if (is_user_logged_in()) {
            return '<p>' . __('이미 로그인되어 있습니다.', 'lectus-class-system') . '</p>';
        }
        
        if (!get_option('users_can_register')) {
            return '<p>' . __('현재 회원가입이 비활성화되어 있습니다.', 'lectus-class-system') . '</p>';
        }
        
        ob_start();
        ?>
        <div class="lectus-registration-form" style="max-width: 500px; margin: 0 auto; padding: 30px; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h2 style="margin: 0 0 25px; text-align: center; color: #1f2937; font-size: 24px;"><?php _e('회원가입', 'lectus-class-system'); ?></h2>
            
            <form method="post" action="<?php echo site_url('wp-login.php?action=register'); ?>" id="lectus-register-form">
                <div style="margin-bottom: 20px;">
                    <label for="user_login" style="display: block; margin-bottom: 5px; color: #374151; font-weight: 500;"><?php _e('사용자명', 'lectus-class-system'); ?> <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="user_login" id="user_login" required 
                           style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px;" />
                    <small style="color: #6b7280; font-size: 12px;"><?php _e('영문, 숫자, 언더스코어(_)만 사용 가능합니다.', 'lectus-class-system'); ?></small>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label for="user_email" style="display: block; margin-bottom: 5px; color: #374151; font-weight: 500;"><?php _e('이메일 주소', 'lectus-class-system'); ?> <span style="color: #ef4444;">*</span></label>
                    <input type="email" name="user_email" id="user_email" required 
                           style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px;" />
                    <small style="color: #6b7280; font-size: 12px;"><?php _e('비밀번호 재설정 링크가 이 이메일로 전송됩니다.', 'lectus-class-system'); ?></small>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label for="first_name" style="display: block; margin-bottom: 5px; color: #374151; font-weight: 500;"><?php _e('이름', 'lectus-class-system'); ?></label>
                    <input type="text" name="first_name" id="first_name" 
                           style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px;" />
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label for="last_name" style="display: block; margin-bottom: 5px; color: #374151; font-weight: 500;"><?php _e('성', 'lectus-class-system'); ?></label>
                    <input type="text" name="last_name" id="last_name" 
                           style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px;" />
                </div>
                
                <?php do_action('register_form'); ?>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="terms" required style="margin-right: 8px;" />
                        <span style="color: #374151; font-size: 14px;">
                            <?php _e('이용약관 및 개인정보처리방침에 동의합니다.', 'lectus-class-system'); ?> <span style="color: #ef4444;">*</span>
                        </span>
                    </label>
                </div>
                
                <button type="submit" style="width: 100%; padding: 12px; background: #3b82f6; color: white; border: none; border-radius: 4px; font-size: 16px; font-weight: 500; cursor: pointer; transition: background 0.3s;"
                        onmouseover="this.style.background='#2563eb'" onmouseout="this.style.background='#3b82f6'">
                    <?php _e('회원가입', 'lectus-class-system'); ?>
                </button>
                
                <?php wp_nonce_field('lectus-register', 'lectus-register-nonce'); ?>
            </form>
            
            <p style="text-align: center; margin-top: 20px; color: #6b7280;">
                <?php _e('이미 계정이 있으신가요?', 'lectus-class-system'); ?>
                <a href="<?php echo wp_login_url(); ?>" style="color: #3b82f6; text-decoration: none;"><?php _e('로그인', 'lectus-class-system'); ?></a>
            </p>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Display user profile
     */
    public static function user_profile($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . __('로그인이 필요합니다.', 'lectus-class-system') . ' <a href="' . wp_login_url() . '">' . __('로그인', 'lectus-class-system') . '</a></p>';
        }
        
        $user = wp_get_current_user();
        $user_id = $user->ID;
        
        ob_start();
        ?>
        <div class="lectus-user-profile" style="max-width: 800px; margin: 0 auto; padding: 30px; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h2 style="margin: 0 0 30px; color: #1f2937; font-size: 24px;"><?php _e('내 프로필', 'lectus-class-system'); ?></h2>
            
            <div style="display: flex; gap: 30px; margin-bottom: 30px;">
                <div style="flex-shrink: 0;">
                    <?php echo get_avatar($user_id, 120, '', '', array('style' => 'border-radius: 50%; border: 4px solid #e5e7eb;')); ?>
                </div>
                <div style="flex-grow: 1;">
                    <h3 style="margin: 0 0 10px; color: #1f2937; font-size: 20px;"><?php echo esc_html($user->display_name); ?></h3>
                    <p style="color: #6b7280; margin: 0 0 5px;"><?php echo esc_html($user->user_email); ?></p>
                    <p style="color: #6b7280; margin: 0;"><?php echo sprintf(__('가입일: %s', 'lectus-class-system'), date_i18n(get_option('date_format'), strtotime($user->user_registered))); ?></p>
                </div>
            </div>
            
            <div style="border-top: 1px solid #e5e7eb; padding-top: 30px;">
                <h3 style="margin: 0 0 20px; color: #1f2937; font-size: 18px;"><?php _e('학습 통계', 'lectus-class-system'); ?></h3>
                
                <?php
                $enrolled_courses = Lectus_Enrollment::get_user_enrollments($user_id);
                $completed_courses = 0;
                $total_progress = 0;
                
                foreach ($enrolled_courses as $enrollment) {
                    $progress = Lectus_Progress::get_course_progress($user_id, $enrollment->course_id);
                    $total_progress += $progress;
                    if ($progress >= 100) {
                        $completed_courses++;
                    }
                }
                
                $avg_progress = count($enrolled_courses) > 0 ? round($total_progress / count($enrolled_courses)) : 0;
                $certificates = Lectus_Certificate::get_user_certificates($user_id);
                ?>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px;">
                    <div style="text-align: center; padding: 20px; background: #f3f4f6; border-radius: 8px;">
                        <div style="font-size: 32px; font-weight: bold; color: #3b82f6; margin-bottom: 5px;"><?php echo count($enrolled_courses); ?></div>
                        <div style="color: #6b7280; font-size: 14px;"><?php _e('수강 중인 강의', 'lectus-class-system'); ?></div>
                    </div>
                    <div style="text-align: center; padding: 20px; background: #f3f4f6; border-radius: 8px;">
                        <div style="font-size: 32px; font-weight: bold; color: #10b981; margin-bottom: 5px;"><?php echo $completed_courses; ?></div>
                        <div style="color: #6b7280; font-size: 14px;"><?php _e('완료한 강의', 'lectus-class-system'); ?></div>
                    </div>
                    <div style="text-align: center; padding: 20px; background: #f3f4f6; border-radius: 8px;">
                        <div style="font-size: 32px; font-weight: bold; color: #f59e0b; margin-bottom: 5px;"><?php echo $avg_progress; ?>%</div>
                        <div style="color: #6b7280; font-size: 14px;"><?php _e('평균 진도율', 'lectus-class-system'); ?></div>
                    </div>
                    <div style="text-align: center; padding: 20px; background: #f3f4f6; border-radius: 8px;">
                        <div style="font-size: 32px; font-weight: bold; color: #8b5cf6; margin-bottom: 5px;"><?php echo count($certificates); ?></div>
                        <div style="color: #6b7280; font-size: 14px;"><?php _e('획득한 수료증', 'lectus-class-system'); ?></div>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 30px; padding-top: 30px; border-top: 1px solid #e5e7eb; text-align: center;">
                <a href="<?php echo home_url('/student-dashboard/'); ?>" style="display: inline-block; padding: 12px 24px; background: #3b82f6; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px;"
                   onmouseover="this.style.background='#2563eb'" onmouseout="this.style.background='#3b82f6'">
                    <?php _e('내 강의실', 'lectus-class-system'); ?>
                </a>
                <a href="<?php echo wp_logout_url(); ?>" style="display: inline-block; padding: 12px 24px; background: #6b7280; color: white; text-decoration: none; border-radius: 4px;"
                   onmouseover="this.style.background='#4b5563'" onmouseout="this.style.background='#6b7280'">
                    <?php _e('로그아웃', 'lectus-class-system'); ?>
                </a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Display search form
     */
    public static function search_form($atts) {
        $atts = shortcode_atts(array(
            'placeholder' => __('강의 검색...', 'lectus-class-system'),
            'button_text' => __('검색', 'lectus-class-system'),
            'post_type' => 'coursesingle'
        ), $atts);
        
        ob_start();
        ?>
        <div class="lectus-search-form" style="max-width: 600px; margin: 20px auto;">
            <form method="get" action="<?php echo home_url('/'); ?>" style="display: flex; gap: 10px;">
                <input type="search" name="s" placeholder="<?php echo esc_attr($atts['placeholder']); ?>" 
                       value="<?php echo get_search_query(); ?>"
                       style="flex: 1; padding: 12px 20px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 16px;" />
                <input type="hidden" name="post_type" value="<?php echo esc_attr($atts['post_type']); ?>" />
                <button type="submit" 
                        style="padding: 12px 30px; background: #3b82f6; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 500; cursor: pointer; transition: background 0.3s;"
                        onmouseover="this.style.background='#2563eb'" onmouseout="this.style.background='#3b82f6'">
                    <i class="fas fa-search" style="margin-right: 5px;"></i>
                    <?php echo esc_html($atts['button_text']); ?>
                </button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}