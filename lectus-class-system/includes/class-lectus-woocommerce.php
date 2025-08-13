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
        
        // Course selection
        $courses = get_posts(array(
            'post_type' => 'coursesingle',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));
        
        $course_options = array('' => __('선택하세요', 'lectus-class-system'));
        foreach ($courses as $course) {
            $course_options[$course->ID] = $course->post_title;
        }
        
        woocommerce_wp_select(array(
            'id' => '_lectus_course_id',
            'label' => __('연결된 단과강의', 'lectus-class-system'),
            'description' => __('이 상품을 구매하면 접근할 수 있는 단과강의를 선택하세요.', 'lectus-class-system'),
            'desc_tip' => true,
            'options' => $course_options
        ));
        
        // Package selection
        $packages = get_posts(array(
            'post_type' => 'coursepackage',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));
        
        $package_options = array('' => __('선택하세요', 'lectus-class-system'));
        foreach ($packages as $package) {
            $package_options[$package->ID] = $package->post_title;
        }
        
        woocommerce_wp_select(array(
            'id' => '_lectus_package_id',
            'label' => __('연결된 패키지강의', 'lectus-class-system'),
            'description' => __('이 상품을 구매하면 접근할 수 있는 패키지강의를 선택하세요.', 'lectus-class-system'),
            'desc_tip' => true,
            'options' => $package_options
        ));
        
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
        
        echo '</div>';
    }
    
    public static function save_product_options($post_id) {
        $course_id = isset($_POST['_lectus_course_id']) ? sanitize_text_field($_POST['_lectus_course_id']) : '';
        $package_id = isset($_POST['_lectus_package_id']) ? sanitize_text_field($_POST['_lectus_package_id']) : '';
        $duration = isset($_POST['_lectus_access_duration']) ? sanitize_text_field($_POST['_lectus_access_duration']) : '';
        $auto_enroll = isset($_POST['_lectus_auto_enroll']) ? 'yes' : 'no';
        
        // Get product object for HPOS compatibility
        $product = wc_get_product($post_id);
        
        if ($product) {
            // Use HPOS-compatible methods to save meta
            $product->update_meta_data('_lectus_course_id', $course_id);
            $product->update_meta_data('_lectus_package_id', $package_id);
            $product->update_meta_data('_lectus_access_duration', $duration);
            $product->update_meta_data('_lectus_auto_enroll', $auto_enroll);
            $product->save();
        } else {
            // Fallback to traditional post meta for non-product posts
            update_post_meta($post_id, '_lectus_course_id', $course_id);
            update_post_meta($post_id, '_lectus_package_id', $package_id);
            update_post_meta($post_id, '_lectus_access_duration', $duration);
            update_post_meta($post_id, '_lectus_auto_enroll', $auto_enroll);
        }
        
        // Update course/package with product ID for reverse lookup
        if ($course_id) {
            update_post_meta($course_id, '_wc_product_id', $post_id);
        }
        if ($package_id) {
            update_post_meta($package_id, '_wc_product_id', $post_id);
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
            
            // Get linked course or package using HPOS-compatible methods
            $course_id = $product->get_meta('_lectus_course_id') ?: get_post_meta($product_id, '_lectus_course_id', true);
            $package_id = $product->get_meta('_lectus_package_id') ?: get_post_meta($product_id, '_lectus_package_id', true);
            $duration = $product->get_meta('_lectus_access_duration') ?: get_post_meta($product_id, '_lectus_access_duration', true);
            
            // Enroll in single course
            if ($course_id) {
                Lectus_Enrollment::enroll($user_id, $course_id, $order_id, $duration);
                
                // Log enrollment
                $order->add_order_note(sprintf(
                    __('수강생이 "%s" 강의에 자동 등록되었습니다.', 'lectus-class-system'),
                    get_the_title($course_id)
                ));
            }
            
            // Enroll in package courses
            if ($package_id) {
                $package_courses = get_post_meta($package_id, '_package_courses', true);
                if (is_array($package_courses)) {
                    foreach ($package_courses as $pkg_course_id) {
                        Lectus_Enrollment::enroll($user_id, $pkg_course_id, $order_id, $duration);
                    }
                    
                    $order->add_order_note(sprintf(
                        __('수강생이 "%s" 패키지의 모든 강의에 자동 등록되었습니다.', 'lectus-class-system'),
                        get_the_title($package_id)
                    ));
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
        
        $course_id = get_post_meta($post->ID, '_lectus_course_id', true);
        $package_id = get_post_meta($post->ID, '_lectus_package_id', true);
        
        if ($course_id || $package_id) {
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
        
        $course_id = get_post_meta($post->ID, '_lectus_course_id', true);
        $package_id = get_post_meta($post->ID, '_lectus_package_id', true);
        $duration = get_post_meta($post->ID, '_lectus_access_duration', true);
        
        echo '<h2>' . __('강의 정보', 'lectus-class-system') . '</h2>';
        
        if ($course_id) {
            $course = get_post($course_id);
            if ($course) {
                echo '<h3>' . esc_html($course->post_title) . '</h3>';
                echo wpautop($course->post_excerpt);
                
                // Show lessons count
                $lessons = get_posts(array(
                    'post_type' => 'lesson',
                    'meta_key' => '_course_id',
                    'meta_value' => $course_id,
                    'posts_per_page' => -1
                ));
                
                echo '<ul>';
                echo '<li>' . sprintf(__('레슨 수: %d개', 'lectus-class-system'), count($lessons)) . '</li>';
                
                if ($duration) {
                    echo '<li>' . sprintf(__('수강 기간: %d일', 'lectus-class-system'), $duration) . '</li>';
                } else {
                    echo '<li>' . __('수강 기간: 무제한', 'lectus-class-system') . '</li>';
                }
                
                $completion_score = get_post_meta($course_id, '_completion_score', true);
                echo '<li>' . sprintf(__('수료 기준: %d%%', 'lectus-class-system'), $completion_score ?: 80) . '</li>';
                
                $certificate_enabled = get_post_meta($course_id, '_certificate_enabled', true);
                if ($certificate_enabled) {
                    echo '<li>' . __('수료증 발급 가능', 'lectus-class-system') . '</li>';
                }
                echo '</ul>';
            }
        }
        
        if ($package_id) {
            $package = get_post($package_id);
            if ($package) {
                echo '<h3>' . esc_html($package->post_title) . '</h3>';
                echo wpautop($package->post_excerpt);
                
                $package_courses = get_post_meta($package_id, '_package_courses', true);
                if (is_array($package_courses) && !empty($package_courses)) {
                    echo '<h4>' . __('포함된 강의:', 'lectus-class-system') . '</h4>';
                    echo '<ul>';
                    foreach ($package_courses as $pkg_course_id) {
                        $pkg_course = get_post($pkg_course_id);
                        if ($pkg_course) {
                            echo '<li>' . esc_html($pkg_course->post_title) . '</li>';
                        }
                    }
                    echo '</ul>';
                }
            }
        }
    }
    
    public static function validate_add_to_cart($passed, $product_id, $quantity) {
        // Get product object for HPOS compatibility
        $product = wc_get_product($product_id);
        if (!$product) return $passed;
        
        // Use HPOS-compatible methods to get meta
        $course_id = $product->get_meta('_lectus_course_id') ?: get_post_meta($product_id, '_lectus_course_id', true);
        $package_id = $product->get_meta('_lectus_package_id') ?: get_post_meta($product_id, '_lectus_package_id', true);
        
        if (!$course_id && !$package_id) {
            return $passed;
        }
        
        // Check if user is already enrolled
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            
            if ($course_id && Lectus_Enrollment::is_enrolled($user_id, $course_id)) {
                wc_add_notice(__('이미 이 강의에 등록되어 있습니다.', 'lectus-class-system'), 'error');
                return false;
            }
            
            if ($package_id) {
                $package_courses = get_post_meta($package_id, '_package_courses', true);
                if (is_array($package_courses)) {
                    $already_enrolled = array();
                    foreach ($package_courses as $pkg_course_id) {
                        if (Lectus_Enrollment::is_enrolled($user_id, $pkg_course_id)) {
                            $already_enrolled[] = get_the_title($pkg_course_id);
                        }
                    }
                    
                    if (!empty($already_enrolled)) {
                        wc_add_notice(
                            sprintf(
                                __('다음 강의에 이미 등록되어 있습니다: %s', 'lectus-class-system'),
                                implode(', ', $already_enrolled)
                            ),
                            'notice'
                        );
                    }
                }
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
}