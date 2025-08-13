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
        
        // Enrollment shortcodes
        add_shortcode('lectus_enroll_button', array(__CLASS__, 'enroll_button'));
        add_shortcode('lectus_enrollment_form', array(__CLASS__, 'enrollment_form'));
        
        // Certificate shortcodes
        add_shortcode('lectus_certificates', array(__CLASS__, 'certificates'));
        add_shortcode('lectus_certificate_verify', array(__CLASS__, 'certificate_verify'));
        
        // Student dashboard shortcode
        add_shortcode('lectus_student_dashboard', array(__CLASS__, 'student_dashboard'));
    }
    
    public static function courses_list($atts) {
        $atts = shortcode_atts(array(
            'type' => 'coursesingle',
            'category' => '',
            'limit' => 12,
            'columns' => 3,
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
        <div class="lectus-courses-grid columns-<?php echo esc_attr($atts['columns']); ?>">
            <?php if ($courses->have_posts()): ?>
                <?php while ($courses->have_posts()): $courses->the_post(); ?>
                    <div class="lectus-course-card">
                        <?php if (has_post_thumbnail()): ?>
                            <div class="course-thumbnail">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('medium'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        <div class="course-content">
                            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <div class="course-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                            <div class="course-meta">
                                <?php
                                $duration = get_post_meta(get_the_ID(), '_course_duration', true);
                                if ($duration) {
                                    echo '<span class="duration">' . sprintf(__('수강 기간: %d일', 'lectus-class-system'), $duration) . '</span>';
                                }
                                
                                $price = get_post_meta(get_the_ID(), '_course_price', true);
                                if ($price && is_numeric($price) && $price > 0) {
                                    echo '<span class="price">' . sprintf(__('가격: %s원', 'lectus-class-system'), number_format($price)) . '</span>';
                                }
                                ?>
                            </div>
                            <div class="course-actions">
                                <a href="<?php the_permalink(); ?>" class="button button-secondary">
                                    <?php _e('자세히 보기', 'lectus-class-system'); ?>
                                </a>
                                <?php
                                // Show enrollment/purchase button
                                $course_id = get_the_ID();
                                $course_type = $atts['type'];
                                
                                if (is_user_logged_in() && Lectus_Enrollment::is_enrolled(get_current_user_id(), $course_id)) {
                                    // User is already enrolled
                                    echo '<a href="' . get_permalink($course_id) . '" class="button button-primary">' . __('학습 계속하기', 'lectus-class-system') . '</a>';
                                } else {
                                    // Check if course has WooCommerce product
                                    if (Lectus_WooCommerce::course_has_product($course_id)) {
                                        echo '<button class="button button-primary lectus-purchase-btn" data-course-id="' . esc_attr($course_id) . '" data-course-type="' . esc_attr($course_type) . '">' . __('구매하기', 'lectus-class-system') . '</button>';
                                    } elseif (!$price || $price <= 0) {
                                        // Free course
                                        if (is_user_logged_in()) {
                                            echo '<button class="button button-primary lectus-enroll-btn" data-course-id="' . esc_attr($course_id) . '">' . __('무료 수강 신청', 'lectus-class-system') . '</button>';
                                        } else {
                                            echo '<a href="' . wp_login_url(get_permalink($course_id)) . '" class="button button-primary">' . __('로그인하여 수강 신청', 'lectus-class-system') . '</a>';
                                        }
                                    } else {
                                        // Paid course without WooCommerce product
                                        echo '<span class="button button-disabled">' . __('준비 중', 'lectus-class-system') . '</span>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            <?php else: ?>
                <p><?php _e('강의가 없습니다.', 'lectus-class-system'); ?></p>
            <?php endif; ?>
        </div>
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
                                    <a href="<?php echo get_permalink($course->ID); ?>" class="button button-small">
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
            return '<a href="' . get_permalink($course_id) . '" class="' . esc_attr($atts['class']) . '">' . 
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
}