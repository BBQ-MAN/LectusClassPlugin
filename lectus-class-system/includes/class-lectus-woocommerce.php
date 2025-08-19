<?php
/**
 * WooCommerce Integration for Lectus Class System
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_WooCommerce {
    
    public static function init() {
        // Product meta boxes
        add_action('woocommerce_product_options_general_product_data', array(__CLASS__, 'add_product_options'));
        add_action('woocommerce_process_product_meta', array(__CLASS__, 'save_product_options'));
        
        // Order hooks
        add_action('woocommerce_order_status_completed', array(__CLASS__, 'handle_order_completed'));
        add_action('woocommerce_order_status_processing', array(__CLASS__, 'handle_order_processing'));
        add_action('woocommerce_order_status_refunded', array(__CLASS__, 'handle_order_refunded'));
        add_action('woocommerce_order_status_cancelled', array(__CLASS__, 'handle_order_cancelled'));
        
        // Product tabs
        add_filter('woocommerce_product_tabs', array(__CLASS__, 'add_course_tab'));
        
        // Custom product template for course products
        add_filter('single_template', array(__CLASS__, 'custom_product_template'), 10);
        add_filter('template_include', array(__CLASS__, 'override_product_template'), 99);
        
        // Add to cart validation
        add_filter('woocommerce_add_to_cart_validation', array(__CLASS__, 'validate_add_to_cart'), 10, 3);
        
        // My Account endpoints
        add_action('init', array(__CLASS__, 'add_endpoints'));
        add_filter('query_vars', array(__CLASS__, 'add_query_vars'), 0);
        add_filter('woocommerce_account_menu_items', array(__CLASS__, 'add_menu_items'));
        add_action('woocommerce_account_my-courses_endpoint', array(__CLASS__, 'my_courses_content'));
        add_action('woocommerce_account_certificates_endpoint', array(__CLASS__, 'certificates_content'));
        
        // Admin hooks for product creation
        add_action('post_row_actions', array(__CLASS__, 'add_create_product_button'), 10, 2);
        add_action('wp_ajax_lectus_create_product', array(__CLASS__, 'ajax_create_product'));
        
        // Frontend hooks for purchase flow
        add_action('wp_ajax_lectus_get_course_product', array(__CLASS__, 'ajax_get_course_product'));
        add_action('wp_ajax_nopriv_lectus_get_course_product', array(__CLASS__, 'ajax_get_course_product'));
    }
    
    public static function add_product_options() {
        global $post;
        
        echo '<div class="options_group lectus_course_options">';
        
        // Get selected courses
        $selected_courses = get_post_meta($post->ID, '_lectus_course_ids', true);
        if (!is_array($selected_courses)) {
            $selected_courses = array();
        }
        
        // Get all courses with categories
        $courses = get_posts(array(
            'post_type' => 'coursesingle',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));
        
        // Group courses by category
        $courses_by_category = array();
        $uncategorized_courses = array();
        
        foreach ($courses as $course) {
            $categories = wp_get_post_terms($course->ID, 'course_category');
            if (!empty($categories)) {
                foreach ($categories as $category) {
                    if (!isset($courses_by_category[$category->term_id])) {
                        $courses_by_category[$category->term_id] = array(
                            'name' => $category->name,
                            'courses' => array()
                        );
                    }
                    $courses_by_category[$category->term_id]['courses'][] = $course;
                }
            } else {
                $uncategorized_courses[] = $course;
            }
        }
        
        // Course selection with search and checkboxes
        echo '<div class="form-field _lectus_course_ids_field">';
        echo '<label>' . __('연결된 강의들', 'lectus-class-system') . '</label>';
        echo '<div style="border: 1px solid #ddd; padding: 10px; background: #f9f9f9; border-radius: 3px;">';
        
        // Search box
        echo '<input type="text" id="lectus_course_search" placeholder="' . __('강의 검색...', 'lectus-class-system') . '" style="width: 100%; margin-bottom: 10px; padding: 5px;">';
        
        // Course list with checkboxes
        echo '<div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; background: white; padding: 10px;">';
        
        // Display courses by category
        foreach ($courses_by_category as $cat_id => $category_data) {
            echo '<div class="lectus-course-category" style="margin-bottom: 15px;">';
            echo '<strong style="color: #007cba; display: block; margin-bottom: 5px;">📁 ' . esc_html($category_data['name']) . '</strong>';
            echo '<div style="margin-left: 20px;">';
            foreach ($category_data['courses'] as $course) {
                $checked = in_array($course->ID, $selected_courses) ? 'checked="checked"' : '';
                echo '<label class="lectus-course-item" style="display: block; margin: 3px 0; cursor: pointer;" data-course-title="' . esc_attr(strtolower($course->post_title)) . '">';
                echo '<input type="checkbox" name="_lectus_course_ids[]" value="' . esc_attr($course->ID) . '" ' . $checked . ' style="margin-right: 8px;">';
                echo esc_html($course->post_title);
                
                // Show lesson count
                $lesson_count = count(get_posts(array(
                    'post_type' => 'lesson',
                    'meta_key' => '_course_id',
                    'meta_value' => $course->ID,
                    'posts_per_page' => -1
                )));
                if ($lesson_count > 0) {
                    echo ' <span style="color: #999; font-size: 12px;">(' . $lesson_count . '개 레슨)</span>';
                }
                echo '</label>';
            }
            echo '</div>';
            echo '</div>';
        }
        
        // Display uncategorized courses
        if (!empty($uncategorized_courses)) {
            echo '<div class="lectus-course-category" style="margin-bottom: 15px;">';
            echo '<strong style="color: #666; display: block; margin-bottom: 5px;">📄 ' . __('미분류', 'lectus-class-system') . '</strong>';
            echo '<div style="margin-left: 20px;">';
            foreach ($uncategorized_courses as $course) {
                $checked = in_array($course->ID, $selected_courses) ? 'checked="checked"' : '';
                echo '<label class="lectus-course-item" style="display: block; margin: 3px 0; cursor: pointer;" data-course-title="' . esc_attr(strtolower($course->post_title)) . '">';
                echo '<input type="checkbox" name="_lectus_course_ids[]" value="' . esc_attr($course->ID) . '" ' . $checked . ' style="margin-right: 8px;">';
                echo esc_html($course->post_title);
                
                // Show lesson count
                $lesson_count = count(get_posts(array(
                    'post_type' => 'lesson',
                    'meta_key' => '_course_id',
                    'meta_value' => $course->ID,
                    'posts_per_page' => -1
                )));
                if ($lesson_count > 0) {
                    echo ' <span style="color: #999; font-size: 12px;">(' . $lesson_count . '개 레슨)</span>';
                }
                echo '</label>';
            }
            echo '</div>';
            echo '</div>';
        }
        
        echo '</div>'; // End course list
        
        // Selected count display
        echo '<div style="margin-top: 10px; color: #007cba;">';
        echo '<span id="lectus_selected_count">0</span>개 강의 선택됨';
        echo '</div>';
        
        echo '</div>'; // End wrapper
        echo '<p class="description">' . __('이 상품을 구매하면 접근할 수 있는 강의들을 선택하세요. 하나만 선택하면 단일 강의, 여러개 선택하면 패키지 상품이 됩니다.', 'lectus-class-system') . '</p>';
        echo '</div>';
        
        // Access duration
        woocommerce_wp_text_input(array(
            'id' => '_lectus_access_duration',
            'label' => __('수강 기간 (일)', 'lectus-class-system'),
            'description' => __('구매 후 강의에 접근할 수 있는 기간 (일 단위). 0 또는 비워두면 무제한.', 'lectus-class-system'),
            'desc_tip' => true,
            'type' => 'number',
            'custom_attributes' => array(
                'min' => '0',
                'step' => '1'
            )
        ));
        
        // Auto enrollment
        woocommerce_wp_checkbox(array(
            'id' => '_lectus_auto_enroll',
            'label' => __('자동 수강 등록', 'lectus-class-system'),
            'description' => __('결제 완료 시 자동으로 강의에 등록합니다.', 'lectus-class-system'),
            'value' => get_post_meta($post->ID, '_lectus_auto_enroll', true) ?: 'yes'
        ));
        
        // JavaScript for search and count
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Update selected count
            function updateSelectedCount() {
                var count = $('input[name="_lectus_course_ids[]"]:checked').length;
                $('#lectus_selected_count').text(count);
            }
            
            // Initial count
            updateSelectedCount();
            
            // Update count on checkbox change
            $('input[name="_lectus_course_ids[]"]').on('change', updateSelectedCount);
            
            // Search functionality
            $('#lectus_course_search').on('keyup', function() {
                var searchTerm = $(this).val().toLowerCase();
                
                $('.lectus-course-item').each(function() {
                    var courseTitle = $(this).data('course-title');
                    if (courseTitle.indexOf(searchTerm) > -1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
                
                // Hide empty categories
                $('.lectus-course-category').each(function() {
                    var visibleCourses = $(this).find('.lectus-course-item:visible').length;
                    if (visibleCourses === 0) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                });
            });
        });
        </script>
        <?php
        
        echo '</div>';
    }
    
    public static function save_product_options($post_id) {
        $course_ids = isset($_POST['_lectus_course_ids']) ? array_map('absint', $_POST['_lectus_course_ids']) : array();
        $duration = isset($_POST['_lectus_access_duration']) ? sanitize_text_field($_POST['_lectus_access_duration']) : '';
        $auto_enroll = isset($_POST['_lectus_auto_enroll']) ? 'yes' : 'no';
        
        // Get product object for HPOS compatibility
        $product = wc_get_product($post_id);
        
        if ($product) {
            // Use HPOS-compatible methods to save meta
            $product->update_meta_data('_lectus_course_ids', $course_ids);
            $product->update_meta_data('_lectus_access_duration', $duration);
            $product->update_meta_data('_lectus_auto_enroll', $auto_enroll);
            $product->save();
        } else {
            // Fallback to traditional post meta for non-product posts
            update_post_meta($post_id, '_lectus_course_ids', $course_ids);
            update_post_meta($post_id, '_lectus_access_duration', $duration);
            update_post_meta($post_id, '_lectus_auto_enroll', $auto_enroll);
        }
        
        // Update courses with product ID for reverse lookup
        foreach ($course_ids as $course_id) {
            $product_ids = get_post_meta($course_id, '_wc_product_ids', true);
            if (!is_array($product_ids)) {
                $product_ids = array();
            }
            if (!in_array($post_id, $product_ids)) {
                $product_ids[] = $post_id;
                update_post_meta($course_id, '_wc_product_ids', $product_ids);
            }
        }
    }
    
    public static function handle_order_completed($order_id) {
        self::process_order_enrollment($order_id, 'active');
    }
    
    public static function handle_order_processing($order_id) {
        self::process_order_enrollment($order_id, 'active');
    }
    
    public static function handle_order_refunded($order_id) {
        self::process_order_unenrollment($order_id);
    }
    
    public static function handle_order_cancelled($order_id) {
        self::process_order_unenrollment($order_id);
    }
    
    private static function process_order_enrollment($order_id, $status = 'active') {
        $order = wc_get_order($order_id);
        if (!$order) return;
        
        // Use HPOS-compatible methods
        $user_id = $order->get_user_id();
        if (!$user_id) return;
        
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            
            // Get product object for HPOS compatibility
            $product = wc_get_product($product_id);
            if (!$product) continue;
            
            // Use product meta data methods for HPOS compatibility
            $auto_enroll = $product->get_meta('_lectus_auto_enroll') ?: get_post_meta($product_id, '_lectus_auto_enroll', true);
            
            if ($auto_enroll !== 'yes') continue;
            
            // Get linked courses using HPOS-compatible methods
            $course_ids = $product->get_meta('_lectus_course_ids') ?: get_post_meta($product_id, '_lectus_course_ids', true);
            $duration = $product->get_meta('_lectus_access_duration') ?: get_post_meta($product_id, '_lectus_access_duration', true);
            
            // Enroll in multiple courses
            if (!empty($course_ids) && is_array($course_ids)) {
                $enrolled_courses = array();
                foreach ($course_ids as $course_id) {
                    if (Lectus_Enrollment::enroll($user_id, $course_id, $order_id, $duration)) {
                        $enrolled_courses[] = get_the_title($course_id);
                    }
                }
                
                if (!empty($enrolled_courses)) {
                    $course_count = count($enrolled_courses);
                    if ($course_count > 1) {
                        $note_text = sprintf(__('수강생이 패키지 상품의 %d개 강의에 자동 등록되었습니다: %s', 'lectus-class-system'), 
                            $course_count,
                            implode(', ', $enrolled_courses)
                        );
                    } else {
                        $note_text = sprintf(__('수강생이 강의에 자동 등록되었습니다: %s', 'lectus-class-system'), 
                            implode(', ', $enrolled_courses)
                        );
                    }
                    
                    $order->add_order_note($note_text);
                }
            }
        }
        
        // Send enrollment notification
        do_action('lectus_student_enrolled_via_woocommerce', $user_id, $order_id);
    }
    
    
    private static function process_order_unenrollment($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) return;
        
        $user_id = $order->get_user_id();
        if (!$user_id) return;
        
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_enrollment';
        
        // Find all enrollments for this order
        $enrollments = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d AND order_id = %d",
            $user_id,
            $order_id
        ));
        
        foreach ($enrollments as $enrollment) {
            // Mark as cancelled instead of deleting
            $wpdb->update(
                $table,
                array('status' => 'cancelled'),
                array('id' => $enrollment->id)
            );
            
            $order->add_order_note(sprintf(
                __('수강생의 "%s" 강의 등록이 취소되었습니다.', 'lectus-class-system'),
                get_the_title($enrollment->course_id)
            ));
        }
    }
    
    public static function add_course_tab($tabs) {
        global $post;
        
        $course_ids = get_post_meta($post->ID, '_lectus_course_ids', true);
        
        if (!empty($course_ids) && is_array($course_ids)) {
            $tabs['course_info'] = array(
                'title' => __('강의 정보', 'lectus-class-system'),
                'priority' => 15,
                'callback' => array(__CLASS__, 'course_tab_content')
            );
        }
        
        return $tabs;
    }
    
    public static function course_tab_content() {
        global $post;
        
        $course_ids = get_post_meta($post->ID, '_lectus_course_ids', true);
        $duration = get_post_meta($post->ID, '_lectus_access_duration', true);
        
        echo '<h2>' . __('강의 정보', 'lectus-class-system') . '</h2>';
        
        // Show package/single badge based on course count
        if (!empty($course_ids) && is_array($course_ids) && count($course_ids) > 1) {
            echo '<p class="package-type-badge" style="background: #007cba; color: white; display: inline-block; padding: 5px 10px; border-radius: 3px; margin-bottom: 15px;">';
            echo __('패키지 상품', 'lectus-class-system');
            echo '</p>';
        }
        
        if (!empty($course_ids) && is_array($course_ids)) {
            echo '<h3>' . __('포함된 강의 목록', 'lectus-class-system') . '</h3>';
            
            $total_lessons = 0;
            $total_duration = 0;
            
            echo '<div class="course-list" style="margin-top: 20px;">';
            foreach ($course_ids as $course_id) {
                $course = get_post($course_id);
                if (!$course) continue;
                
                echo '<div style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px;">';
                echo '<h4 style="margin-top: 0;">' . esc_html($course->post_title) . '</h4>';
                
                if ($course->post_excerpt) {
                    echo '<p>' . esc_html($course->post_excerpt) . '</p>';
                }
                
                // Show lessons count
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
                
                echo '<ul style="margin: 10px 0;">';
                echo '<li>' . sprintf(__('레슨 수: %d개', 'lectus-class-system'), $lesson_count) . '</li>';
                
                if ($course_duration > 0) {
                    $hours = floor($course_duration / 60);
                    $minutes = $course_duration % 60;
                    echo '<li>' . sprintf(__('예상 수강 시간: %d시간 %d분', 'lectus-class-system'), $hours, $minutes) . '</li>';
                }
                
                $completion_score = get_post_meta($course_id, '_completion_score', true);
                echo '<li>' . sprintf(__('수료 기준: %d%%', 'lectus-class-system'), $completion_score ?: 80) . '</li>';
                
                $certificate_enabled = get_post_meta($course_id, '_certificate_enabled', true);
                if ($certificate_enabled) {
                    echo '<li>' . __('수료증 발급 가능', 'lectus-class-system') . '</li>';
                }
                echo '</ul>';
                echo '</div>';
            }
            echo '</div>';
            
            // Summary
            echo '<div style="background: #f0f0f0; padding: 15px; border-radius: 5px; margin-top: 20px;">';
            echo '<h4 style="margin-top: 0;">' . __('패키지 요약', 'lectus-class-system') . '</h4>';
            echo '<ul style="margin: 0;">';
            echo '<li>' . sprintf(__('총 강의 수: %d개', 'lectus-class-system'), count($course_ids)) . '</li>';
            echo '<li>' . sprintf(__('총 레슨 수: %d개', 'lectus-class-system'), $total_lessons) . '</li>';
            
            if ($total_duration > 0) {
                $hours = floor($total_duration / 60);
                $minutes = $total_duration % 60;
                echo '<li>' . sprintf(__('총 예상 수강 시간: %d시간 %d분', 'lectus-class-system'), $hours, $minutes) . '</li>';
            }
            
            if ($duration) {
                echo '<li>' . sprintf(__('수강 기간: %d일', 'lectus-class-system'), $duration) . '</li>';
            } else {
                echo '<li>' . __('수강 기간: 무제한', 'lectus-class-system') . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }
    }
    
    public static function validate_add_to_cart($passed, $product_id, $quantity) {
        // Get product object for HPOS compatibility
        $product = wc_get_product($product_id);
        if (!$product) return $passed;
        
        // Use HPOS-compatible methods to get meta
        $course_ids = $product->get_meta('_lectus_course_ids') ?: get_post_meta($product_id, '_lectus_course_ids', true);
        
        if (empty($course_ids) || !is_array($course_ids)) {
            return $passed;
        }
        
        // Check if user is already enrolled
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $already_enrolled = array();
            
            foreach ($course_ids as $course_id) {
                if (Lectus_Enrollment::is_enrolled($user_id, $course_id)) {
                    $already_enrolled[] = get_the_title($course_id);
                }
            }
            
            if (count($already_enrolled) === count($course_ids)) {
                wc_add_notice(__('이미 이 패키지의 모든 강의에 등록되어 있습니다.', 'lectus-class-system'), 'error');
                return false;
            } elseif (!empty($already_enrolled)) {
                wc_add_notice(
                    sprintf(
                        __('다음 강의에 이미 등록되어 있습니다: %s', 'lectus-class-system'),
                        implode(', ', $already_enrolled)
                    ),
                    'notice'
                );
            }
        }
        
        return $passed;
    }
    
    public static function add_endpoints() {
        add_rewrite_endpoint('my-courses', EP_ROOT | EP_PAGES);
        add_rewrite_endpoint('certificates', EP_ROOT | EP_PAGES);
    }
    
    public static function add_query_vars($vars) {
        $vars[] = 'my-courses';
        $vars[] = 'certificates';
        return $vars;
    }
    
    public static function add_menu_items($items) {
        $new_items = array();
        
        foreach ($items as $key => $value) {
            $new_items[$key] = $value;
            
            if ($key === 'orders') {
                $new_items['my-courses'] = __('내 강의', 'lectus-class-system');
                $new_items['certificates'] = __('수료증', 'lectus-class-system');
            }
        }
        
        return $new_items;
    }
    
    public static function my_courses_content() {
        echo do_shortcode('[lectus_my_courses]');
    }
    
    public static function certificates_content() {
        echo do_shortcode('[lectus_certificates]');
    }
    
    /**
     * Add "Create Product" button to course/package admin list
     */
    public static function add_create_product_button($actions, $post) {
        if (!in_array($post->post_type, array('coursesingle', 'coursepackage'))) {
            return $actions;
        }
        
        if (!current_user_can('manage_woocommerce')) {
            return $actions;
        }
        
        // Check if product already exists
        $product_id = get_post_meta($post->ID, '_wc_product_id', true);
        if ($product_id && get_post($product_id)) {
            $actions['view_product'] = sprintf(
                '<a href="%s" title="%s">%s</a>',
                get_edit_post_link($product_id),
                esc_attr__('연결된 상품 보기', 'lectus-class-system'),
                __('상품 보기', 'lectus-class-system')
            );
        } else {
            $actions['create_product'] = sprintf(
                '<a href="#" class="lectus-create-product" data-course-id="%d" data-course-type="%s" title="%s">%s</a>',
                $post->ID,
                $post->post_type,
                esc_attr__('WooCommerce 상품 생성', 'lectus-class-system'),
                __('상품 생성', 'lectus-class-system')
            );
        }
        
        return $actions;
    }
    
    /**
     * AJAX handler for creating WooCommerce product from course
     */
    public static function ajax_create_product() {
        // Verify nonce and permissions
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-ajax-nonce')) {
            wp_send_json_error(array('message' => __('보안 검증 실패', 'lectus-class-system')), 403);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_send_json_error(array('message' => __('잘못된 요청 방식', 'lectus-class-system')), 405);
            return;
        }
        
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => __('권한이 없습니다.', 'lectus-class-system')), 403);
            return;
        }
        
        $course_id = isset($_POST['course_id']) ? absint($_POST['course_id']) : 0;
        $course_type = isset($_POST['course_type']) ? sanitize_text_field($_POST['course_type']) : '';
        
        if (!$course_id || !in_array($course_type, array('coursesingle', 'coursepackage'))) {
            wp_send_json_error(array('message' => __('유효하지 않은 강의입니다.', 'lectus-class-system')), 400);
            return;
        }
        
        $course = get_post($course_id);
        if (!$course) {
            wp_send_json_error(array('message' => __('강의를 찾을 수 없습니다.', 'lectus-class-system')), 404);
            return;
        }
        
        // Check if product already exists
        $existing_product_id = get_post_meta($course_id, '_wc_product_id', true);
        if ($existing_product_id && get_post($existing_product_id)) {
            wp_send_json_error(array('message' => __('이미 상품이 생성되어 있습니다.', 'lectus-class-system')), 400);
            return;
        }
        
        try {
            $product_data = array(
                'post_title' => $course->post_title,
                'post_content' => $course->post_content,
                'post_excerpt' => $course->post_excerpt,
                'post_status' => 'publish',
                'post_type' => 'product',
                'meta_input' => array(
                    '_visibility' => 'visible',
                    '_stock_status' => 'instock',
                    '_manage_stock' => 'no',
                    '_virtual' => 'yes',
                    '_downloadable' => 'no',
                    '_sold_individually' => 'yes',
                    '_purchase_note' => __('구매해 주셔서 감사합니다. 강의에 자동으로 등록됩니다.', 'lectus-class-system'),
                    '_lectus_auto_enroll' => 'yes'
                )
            );
            
            // Set course or package ID
            if ($course_type === 'coursesingle') {
                $product_data['meta_input']['_lectus_course_id'] = $course_id;
            } else {
                $product_data['meta_input']['_lectus_package_id'] = $course_id;
            }
            
            // Get price from course meta
            $price = get_post_meta($course_id, '_course_price', true);
            if ($price && is_numeric($price)) {
                $product_data['meta_input']['_price'] = $price;
                $product_data['meta_input']['_regular_price'] = $price;
            }
            
            // Get access duration
            $duration = get_post_meta($course_id, '_access_duration', true);
            if ($duration) {
                $product_data['meta_input']['_lectus_access_duration'] = $duration;
            }
            
            $product_id = wp_insert_post($product_data);
            
            if (is_wp_error($product_id)) {
                throw new Exception($product_id->get_error_message());
            }
            
            // Set product categories
            $course_categories = wp_get_post_terms($course_id, ($course_type === 'coursesingle' ? 'course_category' : 'package_category'));
            if (!empty($course_categories)) {
                $product_cat_ids = array();
                foreach ($course_categories as $cat) {
                    // Find or create corresponding product category
                    $product_cat = get_term_by('name', $cat->name, 'product_cat');
                    if (!$product_cat) {
                        $result = wp_insert_term($cat->name, 'product_cat', array(
                            'description' => $cat->description,
                            'slug' => $cat->slug
                        ));
                        if (!is_wp_error($result)) {
                            $product_cat_ids[] = $result['term_id'];
                        }
                    } else {
                        $product_cat_ids[] = $product_cat->term_id;
                    }
                }
                
                if (!empty($product_cat_ids)) {
                    wp_set_post_terms($product_id, $product_cat_ids, 'product_cat');
                }
            }
            
            // Copy featured image
            $thumbnail_id = get_post_thumbnail_id($course_id);
            if ($thumbnail_id) {
                set_post_thumbnail($product_id, $thumbnail_id);
            }
            
            // Save reverse reference
            update_post_meta($course_id, '_wc_product_id', $product_id);
            
            // Log success
            Lectus_Logger::info(
                sprintf('WooCommerce product created for %s: %s (ID: %d)', 
                    $course_type === 'coursesingle' ? 'course' : 'package',
                    $course->post_title, 
                    $product_id
                ), 
                'woocommerce', 
                array(
                    'course_id' => $course_id,
                    'course_type' => $course_type,
                    'product_id' => $product_id,
                    'user_id' => get_current_user_id()
                )
            );
            
            wp_send_json_success(array(
                'message' => __('상품이 성공적으로 생성되었습니다.', 'lectus-class-system'),
                'product_id' => $product_id,
                'edit_url' => get_edit_post_link($product_id),
                'view_url' => get_permalink($product_id)
            ));
            
        } catch (Exception $e) {
            Lectus_Logger::error(
                'Failed to create WooCommerce product: ' . $e->getMessage(), 
                'woocommerce', 
                array(
                    'course_id' => $course_id,
                    'course_type' => $course_type,
                    'error' => $e->getMessage(),
                    'user_id' => get_current_user_id()
                )
            );
            
            wp_send_json_error(array('message' => sprintf(
                __('상품 생성에 실패했습니다: %s', 'lectus-class-system'),
                $e->getMessage()
            )));
        }
    }
    
    /**
     * Get WooCommerce product URL for a course
     */
    public static function get_course_product_url($course_id, $course_type = 'coursesingle') {
        $product_id = get_post_meta($course_id, '_wc_product_id', true);
        
        if ($product_id && get_post_status($product_id) === 'publish') {
            return get_permalink($product_id);
        }
        
        return false;
    }
    
    /**
     * Check if course has a WooCommerce product
     */
    public static function course_has_product($course_id) {
        $product_id = get_post_meta($course_id, '_wc_product_id', true);
        return $product_id && get_post_status($product_id) === 'publish';
    }
    
    /**
     * AJAX handler for getting course product information
     */
    public static function ajax_get_course_product() {
        // Verify nonce and permissions
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-ajax-nonce')) {
            wp_send_json_error(array('message' => __('보안 검증 실패', 'lectus-class-system')), 403);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_send_json_error(array('message' => __('잘못된 요청 방식', 'lectus-class-system')), 405);
            return;
        }
        
        $course_id = isset($_POST['course_id']) ? absint($_POST['course_id']) : 0;
        $course_type = isset($_POST['course_type']) ? sanitize_text_field($_POST['course_type']) : 'coursesingle';
        
        if (!$course_id || !in_array($course_type, array('coursesingle', 'coursepackage'))) {
            wp_send_json_error(array('message' => __('유효하지 않은 강의입니다.', 'lectus-class-system')), 400);
            return;
        }
        
        $course = get_post($course_id);
        if (!$course) {
            wp_send_json_error(array('message' => __('강의를 찾을 수 없습니다.', 'lectus-class-system')), 404);
            return;
        }
        
        // Check if user is already enrolled (for logged-in users)
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            
            if ($course_type === 'coursesingle' && Lectus_Enrollment::is_enrolled($user_id, $course_id)) {
                wp_send_json_error(array('message' => __('이미 이 강의에 등록되어 있습니다.', 'lectus-class-system')), 400);
                return;
            }
            
            if ($course_type === 'coursepackage') {
                $package_courses = get_post_meta($course_id, '_package_courses', true);
                if (is_array($package_courses)) {
                    $enrolled_courses = array();
                    foreach ($package_courses as $pkg_course_id) {
                        if (Lectus_Enrollment::is_enrolled($user_id, $pkg_course_id)) {
                            $enrolled_courses[] = get_the_title($pkg_course_id);
                        }
                    }
                    
                    if (!empty($enrolled_courses)) {
                        wp_send_json_error(array(
                            'message' => sprintf(
                                __('다음 강의에 이미 등록되어 있습니다: %s', 'lectus-class-system'),
                                implode(', ', $enrolled_courses)
                            )
                        ), 400);
                        return;
                    }
                }
            }
        }
        
        // Get associated product
        $product_id = get_post_meta($course_id, '_wc_product_id', true);
        
        if (!$product_id || get_post_status($product_id) !== 'publish') {
            wp_send_json_error(array(
                'message' => __('이 강의는 현재 구매할 수 없습니다. 관리자에게 문의하세요.', 'lectus-class-system')
            ), 404);
            return;
        }
        
        $product_url = get_permalink($product_id);
        
        if (!$product_url) {
            wp_send_json_error(array(
                'message' => __('상품 페이지를 찾을 수 없습니다.', 'lectus-class-system')
            ), 404);
            return;
        }
        
        // Log access for analytics
        Lectus_Logger::info(
            sprintf('Course product accessed: %s (ID: %d)', 
                $course->post_title, 
                $course_id
            ), 
            'woocommerce', 
            array(
                'course_id' => $course_id,
                'course_type' => $course_type,
                'product_id' => $product_id,
                'user_id' => get_current_user_id(),
                'user_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            )
        );
        
        wp_send_json_success(array(
            'product_id' => $product_id,
            'product_url' => $product_url,
            'course_title' => $course->post_title,
            'message' => __('상품 페이지로 이동합니다.', 'lectus-class-system')
        ));
    }
    
    /**
     * Use custom template for products with linked courses
     */
    public static function custom_product_template($template) {
        if (is_singular('product')) {
            global $post;
            
            // Check if product has linked courses
            $course_ids = get_post_meta($post->ID, '_lectus_course_ids', true);
            
            if (!empty($course_ids)) {
                // Use our custom template
                $custom_template = LECTUS_PLUGIN_DIR . 'templates/single-product-course.php';
                if (file_exists($custom_template)) {
                    return $custom_template;
                }
            }
        }
        
        return $template;
    }
    
    /**
     * Override WooCommerce product template for course products
     */
    public static function override_product_template($template) {
        if (is_singular('product')) {
            global $post;
            
            // Check if product has linked courses
            $course_ids = get_post_meta($post->ID, '_lectus_course_ids', true);
            
            if (!empty($course_ids)) {
                // Check if we should use custom template
                $use_custom = apply_filters('lectus_use_custom_product_template', true, $post->ID);
                
                if ($use_custom) {
                    $custom_template = LECTUS_PLUGIN_DIR . 'templates/single-product-course.php';
                    if (file_exists($custom_template)) {
                        // Load WooCommerce functions and global product
                        if (function_exists('wc_get_product')) {
                            global $product;
                            $product = wc_get_product($post->ID);
                        }
                        return $custom_template;
                    }
                }
            }
        }
        
        return $template;
    }
}