<?php
/**
 * Lectus Academy Theme Functions
 *
 * @package LectusAcademy
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme Setup
 */
if (!function_exists('lectus_academy_setup')) {
    function lectus_academy_setup() {
        // Add theme support features
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        add_theme_support('html5', array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script',
        ));
        
        add_theme_support('custom-logo', array(
            'height'      => 50,
            'width'       => 200,
            'flex-width'  => true,
            'flex-height' => true,
        ));
        
        add_theme_support('customize-selective-refresh-widgets');
        add_theme_support('automatic-feed-links');
        add_theme_support('align-wide');
        add_theme_support('responsive-embeds');
        
        // Register navigation menus
        register_nav_menus(array(
            'primary' => esc_html__('Primary Menu', 'lectus-academy'),
            'top-menu' => esc_html__('Top Menu (교육/커리어)', 'lectus-academy'),
            'main-menu' => esc_html__('Main Menu (강의/로드맵/멘토링)', 'lectus-academy'),
            'category-menu' => esc_html__('Category Menu', 'lectus-academy'),
            'footer'  => esc_html__('Footer Menu', 'lectus-academy'),
            'footer-company' => esc_html__('Footer Company Menu', 'lectus-academy'),
            'footer-partner' => esc_html__('Footer Partner Menu', 'lectus-academy'),
            'footer-support' => esc_html__('Footer Support Menu', 'lectus-academy'),
            'footer-community' => esc_html__('Footer Community Menu', 'lectus-academy'),
            'mobile'  => esc_html__('Mobile Menu', 'lectus-academy'),
        ));
        
        // Set content width
        if (!isset($content_width)) {
            $content_width = 1200;
        }
        
        // Add custom image sizes
        add_image_size('course-thumbnail', 400, 300, true);
        add_image_size('lesson-featured', 800, 450, true);
        add_image_size('instructor-avatar', 150, 150, true);
    }
}
add_action('after_setup_theme', 'lectus_academy_setup');

/**
 * Enqueue Scripts and Styles
 */
function lectus_academy_scripts() {
    // Theme stylesheet (WordPress theme info)
    wp_enqueue_style(
        'lectus-academy-style',
        get_stylesheet_uri(),
        array(),
        wp_get_theme()->get('Version')
    );
    
    // Tailwind CSS - force refresh with timestamp
    wp_enqueue_style(
        'lectus-academy-tailwind',
        get_template_directory_uri() . '/style-tailwind.css',
        array(),
        filemtime(get_template_directory() . '/style-tailwind.css')
    );
    
    // Responsive styles - DISABLED for Tailwind
    // if (file_exists(get_template_directory() . '/assets/css/responsive.css')) {
    //     wp_enqueue_style(
    //         'lectus-academy-responsive',
    //         get_template_directory_uri() . '/assets/css/responsive.css',
    //         array('lectus-academy-style'),
    //         '1.0.0'
    //     );
    // }
    
    // Google Fonts or Pretendard
    wp_enqueue_style(
        'lectus-academy-fonts',
        'https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css',
        array(),
        '1.0.0'
    );
    
    // Icons (Font Awesome)
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
        array(),
        '6.4.0'
    );
    
    // Theme JavaScript
    wp_enqueue_script(
        'lectus-academy-script',
        get_template_directory_uri() . '/assets/js/main.js',
        array('jquery'),
        wp_get_theme()->get('Version'),
        true
    );
    
    // Header functionality
    wp_enqueue_script(
        'lectus-academy-header',
        get_template_directory_uri() . '/assets/js/header.js',
        array('jquery'),
        '1.0.0',
        true
    );
    
    // Pass data to JavaScript
    wp_localize_script('lectus-academy-script', 'lectusAcademy', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('lectus-academy-nonce'),
        'is_user_logged_in' => is_user_logged_in(),
        'current_user_id' => get_current_user_id(),
        'theme_url' => get_template_directory_uri(),
        'translations' => array(
            'loading' => __('Loading...', 'lectus-academy'),
            'error' => __('An error occurred', 'lectus-academy'),
            'success' => __('Success!', 'lectus-academy'),
            'confirm' => __('Are you sure?', 'lectus-academy'),
        )
    ));
    
    // Course page specific scripts and fixes
    if (is_singular('coursesingle')) {
        // Enqueue separate tab and sticky card scripts
        wp_enqueue_script(
            'course-tabs',
            get_template_directory_uri() . '/js/course-tabs.js',
            array(),
            '1.0.2',
            true
        );
        
        wp_enqueue_script(
            'sticky-card',
            get_template_directory_uri() . '/js/sticky-card.js',
            array(),
            '1.0.4',
            true
        );
        
        wp_enqueue_script(
            'course-enrollment',
            get_template_directory_uri() . '/js/course-enrollment.js',
            array('jquery'),
            '1.0.2',
            true
        );
        
        // Fix course layout DOM structure
        wp_add_inline_script('lectus-academy-script', '
            (function() {
                console.log("[CourseLayout] Initializing layout fix");
                
                function ensureProperStructure() {
                    var courseLayout = document.querySelector(".course-layout");
                    var wrapper = document.querySelector(".course-content-wrapper");
                    var mainContent = document.querySelector(".course-main-content");
                    var sidebar = document.querySelector(".course-sidebar");
                    
                    // Ensure wrapper exists and has proper classes
                    if (courseLayout && !wrapper) {
                        wrapper = document.createElement("div");
                        wrapper.className = "course-content-wrapper grid grid-cols-1 lg:grid-cols-3 gap-8";
                        
                        // Move all children to wrapper
                        while (courseLayout.firstChild) {
                            wrapper.appendChild(courseLayout.firstChild);
                        }
                        courseLayout.appendChild(wrapper);
                        console.log("[CourseLayout] Created wrapper");
                    }
                    
                    // Ensure sidebar is inside wrapper
                    if (sidebar && wrapper && !wrapper.contains(sidebar)) {
                        wrapper.appendChild(sidebar);
                        console.log("[CourseLayout] Moved sidebar inside wrapper");
                    }
                    
                    // Add grid classes if missing
                    if (mainContent && !mainContent.classList.contains("lg:col-span-2")) {
                        mainContent.classList.add("lg:col-span-2");
                    }
                    
                    if (sidebar && !sidebar.classList.contains("lg:col-span-1")) {
                        sidebar.classList.add("lg:col-span-1");
                    }
                }
                
                // Run on DOM ready
                if (document.readyState === "loading") {
                    document.addEventListener("DOMContentLoaded", ensureProperStructure);
                } else {
                    ensureProperStructure();
                }
                
                // Run after delays for dynamic content
                setTimeout(ensureProperStructure, 500);
                setTimeout(ensureProperStructure, 1500);
                
                // Watch for AJAX changes
                if (window.jQuery) {
                    jQuery(document).ajaxComplete(function() {
                        setTimeout(ensureProperStructure, 100);
                    });
                }
            })();
        ');
    }
    
    // Comment reply script
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'lectus_academy_scripts');

/**
 * Redirect old URLs to new unified pages
 */
add_action('template_redirect', 'lectus_redirect_old_urls');
function lectus_redirect_old_urls() {
    // Redirect /courses/ to /course/
    if (is_page('courses')) {
        wp_redirect(get_post_type_archive_link('coursesingle'), 301);
        exit;
    }
    
    // Redirect /my-courses/ to /student-dashboard/
    if (is_page('my-courses')) {
        wp_redirect(home_url('/student-dashboard/'), 301);
        exit;
    }
}

/**
 * Register Widget Areas
 */
function lectus_academy_widgets_init() {
    register_sidebar(array(
        'name'          => esc_html__('Sidebar', 'lectus-academy'),
        'id'            => 'sidebar-1',
        'description'   => esc_html__('Add widgets here.', 'lectus-academy'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));
    
    register_sidebar(array(
        'name'          => esc_html__('Course Sidebar', 'lectus-academy'),
        'id'            => 'course-sidebar',
        'description'   => esc_html__('Sidebar for course pages.', 'lectus-academy'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
    
    register_sidebar(array(
        'name'          => esc_html__('Footer 1', 'lectus-academy'),
        'id'            => 'footer-1',
        'description'   => esc_html__('Footer widget area 1.', 'lectus-academy'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ));
    
    register_sidebar(array(
        'name'          => esc_html__('Footer 2', 'lectus-academy'),
        'id'            => 'footer-2',
        'description'   => esc_html__('Footer widget area 2.', 'lectus-academy'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ));
    
    register_sidebar(array(
        'name'          => esc_html__('Footer 3', 'lectus-academy'),
        'id'            => 'footer-3',
        'description'   => esc_html__('Footer widget area 3.', 'lectus-academy'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ));
    
    register_sidebar(array(
        'name'          => esc_html__('Footer 4', 'lectus-academy'),
        'id'            => 'footer-4',
        'description'   => esc_html__('Footer widget area 4.', 'lectus-academy'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ));
}
add_action('widgets_init', 'lectus_academy_widgets_init');

/**
 * Check if Lectus Class System plugin is active
 */
function lectus_academy_check_plugin() {
    if (!is_plugin_active('lectus-class-system/lectus-class-system.php')) {
        add_action('admin_notices', 'lectus_academy_plugin_notice');
    }
}
add_action('admin_init', 'lectus_academy_check_plugin');

/**
 * Admin notice for missing plugin
 */
function lectus_academy_plugin_notice() {
    ?>
    <div class="notice notice-warning is-dismissible">
        <p><?php esc_html_e('Lectus Academy theme requires the Lectus Class System plugin to be installed and activated for full functionality.', 'lectus-academy'); ?></p>
    </div>
    <?php
}

/**
 * Custom template tags
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions for theme
 */
require get_template_directory() . '/inc/custom-functions.php';

/**
 * Customizer additions
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load custom widgets
 */
require get_template_directory() . '/inc/widgets.php';

/**
 * Load test restoration functionality
 */
require get_template_directory() . '/inc/test-restoration.php';

/**
 * Load theme setup and activation handler
 */
require get_template_directory() . '/inc/theme-setup.php';

/**
 * Load custom menu walkers
 */
require get_template_directory() . '/inc/menu-walkers.php';

/**
 * Add custom body classes
 */
function lectus_academy_body_classes($classes) {
    // Add page layout classes
    if (is_singular()) {
        $classes[] = 'singular';
    }
    
    if (is_home() || is_archive()) {
        $classes[] = 'archive';
    }
    
    // Add course-related classes
    if (is_singular('coursesingle')) {
        $classes[] = 'single-course';
    }
    
    if (is_singular('lesson')) {
        $classes[] = 'single-lesson';
    }
    
    if (is_singular('coursepackage')) {
        $classes[] = 'single-package';
    }
    
    // Add user role classes
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        foreach ($user->roles as $role) {
            $classes[] = 'user-role-' . $role;
        }
    }
    
    return $classes;
}
add_filter('body_class', 'lectus_academy_body_classes');

/**
 * Custom excerpt length
 */
function lectus_academy_excerpt_length($length) {
    if (is_home() || is_archive()) {
        return 30;
    }
    return $length;
}
add_filter('excerpt_length', 'lectus_academy_excerpt_length');

/**
 * Custom excerpt more
 */
function lectus_academy_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'lectus_academy_excerpt_more');

/**
 * Add theme support for Lectus Class System features
 */
function lectus_academy_lectus_support() {
    // Add support for course templates
    add_theme_support('lectus-templates', array(
        'course-archive',
        'course-single',
        'lesson-single',
        'student-dashboard',
        'certificate-verification',
    ));
    
    // Add support for course features
    add_theme_support('lectus-features', array(
        'progress-tracking',
        'qa-system',
        'certificates',
        'materials',
        'enrollment',
    ));
}
add_action('after_setup_theme', 'lectus_academy_lectus_support');

/**
 * Course archive query modifications
 */
function lectus_academy_course_query($query) {
    if (!is_admin() && $query->is_main_query()) {
        // Course archive pages
        if (is_post_type_archive('coursesingle')) {
            $query->set('posts_per_page', 12);
            $query->set('orderby', 'menu_order');
            $query->set('order', 'ASC');
        }
        
        // Lesson archive pages  
        if (is_post_type_archive('lesson')) {
            $query->set('posts_per_page', 20);
        }
    }
}
add_action('pre_get_posts', 'lectus_academy_course_query');

/**
 * AJAX handler for course enrollment with enhanced security
 * 
 * @return void
 */
function lectus_academy_ajax_enroll() {
    // Verify nonce for security
    if (!check_ajax_referer('lectus-academy-nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Security check failed', 'lectus-academy')));
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please login to enroll', 'lectus-academy')));
    }
    
    // Validate and sanitize course ID
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    
    if ($course_id <= 0) {
        wp_send_json_error(array('message' => __('Invalid course ID', 'lectus-academy')));
    }
    
    // Verify course exists
    if ('coursesingle' !== get_post_type($course_id)) {
        wp_send_json_error(array('message' => __('Course not found', 'lectus-academy')));
    }
    
    // Check if Lectus_Enrollment class exists
    if (class_exists('Lectus_Enrollment')) {
        $user_id = get_current_user_id();
        
        // Check if already enrolled
        if (Lectus_Enrollment::is_enrolled($user_id, $course_id)) {
            wp_send_json_error(array('message' => __('You are already enrolled', 'lectus-academy')));
        }
        
        // Enroll the user
        $enrolled = Lectus_Enrollment::enroll($user_id, $course_id, 0, 365);
        
        if ($enrolled) {
            wp_send_json_success(array('message' => __('Successfully enrolled!', 'lectus-academy')));
        } else {
            wp_send_json_error(array('message' => __('Enrollment failed', 'lectus-academy')));
        }
    } else {
        wp_send_json_error(array('message' => __('Enrollment system not available', 'lectus-academy')));
    }
}
add_action('wp_ajax_lectus_academy_enroll', 'lectus_academy_ajax_enroll');
add_action('wp_ajax_nopriv_lectus_academy_enroll', 'lectus_academy_ajax_enroll');

/**
 * Helper function to get course progress with caching
 * 
 * @param int $course_id Course ID
 * @param int|null $user_id User ID (defaults to current user)
 * @return int Progress percentage (0-100)
 */
function lectus_academy_get_course_progress($course_id, $user_id = null) {
    // Validate inputs
    $course_id = intval($course_id);
    if ($course_id <= 0) {
        return 0;
    }
    
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    $user_id = intval($user_id);
    if ($user_id <= 0) {
        return 0;
    }
    
    // Check cache first
    $cache_key = "lectus_progress_{$user_id}_{$course_id}";
    $cached_progress = wp_cache_get($cache_key);
    
    if (false !== $cached_progress) {
        return intval($cached_progress);
    }
    
    $progress = 0;
    if (class_exists('Lectus_Progress')) {
        $progress = Lectus_Progress::get_course_progress($user_id, $course_id);
    }
    
    // Cache for 5 minutes
    wp_cache_set($cache_key, $progress, '', 300);
    
    return intval($progress);
}

/**
 * Helper function to check enrollment
 */
function lectus_academy_is_enrolled($course_id, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return false;
    }
    
    if (class_exists('Lectus_Enrollment')) {
        return Lectus_Enrollment::is_enrolled($user_id, $course_id);
    }
    
    return false;
}

/**
 * Helper function to get course lessons
 */
function lectus_academy_get_course_lessons($course_id) {
    $args = array(
        'post_type' => 'lesson',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_course_id',
                'value' => $course_id,
                'compare' => '='
            )
        ),
        'orderby' => 'menu_order',
        'order' => 'ASC'
    );
    
    return get_posts($args);
}

/**
 * Helper function to get instructor name
 */
function lectus_academy_get_instructor_name($post_id) {
    $author_id = get_post_field('post_author', $post_id);
    return get_the_author_meta('display_name', $author_id);
}

/**
 * Helper function to get instructor avatar
 */
function lectus_academy_get_instructor_avatar($post_id, $size = 96) {
    $author_id = get_post_field('post_author', $post_id);
    return get_avatar_url($author_id, array('size' => $size));
}

/**
 * Helper function to format course duration
 */
function lectus_academy_format_duration($minutes) {
    if ($minutes < 60) {
        return sprintf(__('%d minutes', 'lectus-academy'), $minutes);
    }
    
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    
    if ($mins == 0) {
        return sprintf(_n('%d hour', '%d hours', $hours, 'lectus-academy'), $hours);
    }
    
    return sprintf(__('%d hours %d minutes', 'lectus-academy'), $hours, $mins);
}

/**
 * Helper function to get course price
 */
function lectus_academy_get_course_price($course_id) {
    // Check if WooCommerce is active and course has a product
    if (function_exists('wc_get_product')) {
        $product_id = get_post_meta($course_id, '_product_id', true);
        
        if ($product_id) {
            $product = wc_get_product($product_id);
            if ($product) {
                return $product->get_price_html();
            }
        }
    }
    
    // Check for custom price field
    $price = get_post_meta($course_id, '_course_price', true);
    if ($price) {
        return wc_price($price);
    }
    
    return __('Free', 'lectus-academy');
}

/**
 * Helper function to get enrolled students count with caching and security
 * 
 * @param int $course_id Course ID
 * @return int Number of enrolled students
 */
function lectus_academy_get_enrolled_count($course_id) {
    $course_id = intval($course_id);
    if ($course_id <= 0) {
        return 0;
    }
    
    // Check cache first
    $cache_key = "lectus_enrolled_count_{$course_id}";
    $cached_count = wp_cache_get($cache_key);
    
    if (false !== $cached_count) {
        return intval($cached_count);
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'lectus_enrollment';
    
    // Check if table exists (cached check)
    static $table_exists = null;
    if (null === $table_exists) {
        $table_exists = ($wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $table_name
        )) === $table_name);
    }
    
    $count = 0;
    if ($table_exists) {
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM `{$table_name}` WHERE course_id = %d AND status = 'active'",
            $course_id
        ));
    }
    
    $count = intval($count);
    
    // Cache for 10 minutes
    wp_cache_set($cache_key, $count, '', 600);
    
    return $count;
}

/**
 * Add custom rewrite rules for course pages
 */
function lectus_academy_rewrite_rules() {
    add_rewrite_rule(
        'courses/([^/]+)/?$',
        'index.php?coursesingle=$matches[1]',
        'top'
    );
    
    add_rewrite_rule(
        'lessons/([^/]+)/?$',
        'index.php?lesson=$matches[1]',
        'top'
    );
    
    add_rewrite_rule(
        'student-dashboard/?$',
        'index.php?pagename=student-dashboard',
        'top'
    );
}
add_action('init', 'lectus_academy_rewrite_rules');

/**
 * Flush rewrite rules on theme activation
 */
function lectus_academy_flush_rewrites() {
    lectus_academy_rewrite_rules();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'lectus_academy_flush_rewrites');

/**
 * Setup essential pages on theme activation
 */
function lectus_academy_theme_activation() {
    // Create essential pages if they don't exist
    if (class_exists('Lectus_Academy_Test_Restoration')) {
        try {
            $created_pages = Lectus_Academy_Test_Restoration::create_essential_pages();
            
            if (!empty($created_pages)) {
                // Store a transient to show admin notice
                set_transient('lectus_academy_pages_created', $created_pages, 60);
            }
        } catch (Exception $e) {
            error_log('Lectus Academy: Failed to create pages on activation - ' . $e->getMessage());
        }
    }
    
    // Flush rewrite rules after creating pages
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'lectus_academy_theme_activation');

/**
 * Show admin notice after page creation
 */
function lectus_academy_activation_notice() {
    $created_pages = get_transient('lectus_academy_pages_created');
    
    if ($created_pages && is_array($created_pages)) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><strong><?php _e('Lectus Academy 테마가 활성화되었습니다!', 'lectus-academy'); ?></strong></p>
            <p><?php printf(__('다음 페이지가 자동으로 생성되었습니다: %s', 'lectus-academy'), implode(', ', $created_pages)); ?></p>
        </div>
        <?php
        delete_transient('lectus_academy_pages_created');
    }
}
add_action('admin_notices', 'lectus_academy_activation_notice');

/**
 * Add theme settings page with proper capability check
 * 
 * @return void
 */
function lectus_academy_add_theme_page() {
    // Only add menu if user has proper capabilities
    if (!current_user_can('edit_theme_options')) {
        return;
    }
    
    add_theme_page(
        __('Lectus Academy Settings', 'lectus-academy'),
        __('Theme Settings', 'lectus-academy'),
        'edit_theme_options',
        'lectus-academy-settings',
        'lectus_academy_settings_page'
    );
}
add_action('admin_menu', 'lectus_academy_add_theme_page');

/**
 * Register theme settings
 */
function lectus_academy_register_settings() {
    // Register settings
    register_setting('lectus_academy_settings', 'lectus_academy_general_settings');
    
    // Add settings section
    add_settings_section(
        'lectus_academy_general_section',
        __('일반 설정', 'lectus-academy'),
        'lectus_academy_general_section_callback',
        'lectus_academy_settings'
    );
    
    // Add settings fields
    add_settings_field(
        'site_title',
        __('사이트 제목', 'lectus-academy'),
        'lectus_academy_site_title_callback',
        'lectus_academy_settings',
        'lectus_academy_general_section'
    );
    
    add_settings_field(
        'site_description',
        __('사이트 설명', 'lectus-academy'),
        'lectus_academy_site_description_callback',
        'lectus_academy_settings',
        'lectus_academy_general_section'
    );
    
    add_settings_field(
        'footer_text',
        __('푸터 텍스트', 'lectus-academy'),
        'lectus_academy_footer_text_callback',
        'lectus_academy_settings',
        'lectus_academy_general_section'
    );
    
    add_settings_field(
        'enable_dark_mode',
        __('다크 모드 활성화', 'lectus-academy'),
        'lectus_academy_dark_mode_callback',
        'lectus_academy_settings',
        'lectus_academy_general_section'
    );
}
add_action('admin_init', 'lectus_academy_register_settings');

/**
 * General section callback
 */
function lectus_academy_general_section_callback() {
    echo '<p>' . __('테마의 기본 설정을 관리합니다.', 'lectus-academy') . '</p>';
}

/**
 * Site title field callback
 */
function lectus_academy_site_title_callback() {
    $options = get_option('lectus_academy_general_settings');
    $value = isset($options['site_title']) ? $options['site_title'] : get_bloginfo('name');
    ?>
    <input type="text" name="lectus_academy_general_settings[site_title]" 
           value="<?php echo esc_attr($value); ?>" 
           class="regular-text" />
    <p class="description"><?php _e('사이트 헤더에 표시될 제목입니다.', 'lectus-academy'); ?></p>
    <?php
}

/**
 * Site description field callback
 */
function lectus_academy_site_description_callback() {
    $options = get_option('lectus_academy_general_settings');
    $value = isset($options['site_description']) ? $options['site_description'] : get_bloginfo('description');
    ?>
    <textarea name="lectus_academy_general_settings[site_description]" 
              rows="3" cols="50"><?php echo esc_textarea($value); ?></textarea>
    <p class="description"><?php _e('사이트의 간단한 설명을 입력합니다.', 'lectus-academy'); ?></p>
    <?php
}

/**
 * Footer text field callback
 */
function lectus_academy_footer_text_callback() {
    $options = get_option('lectus_academy_general_settings');
    $value = isset($options['footer_text']) ? $options['footer_text'] : '© 2024 Lectus Academy. All rights reserved.';
    ?>
    <input type="text" name="lectus_academy_general_settings[footer_text]" 
           value="<?php echo esc_attr($value); ?>" 
           class="large-text" />
    <p class="description"><?php _e('사이트 푸터에 표시될 저작권 정보입니다.', 'lectus-academy'); ?></p>
    <?php
}

/**
 * Dark mode field callback
 */
function lectus_academy_dark_mode_callback() {
    $options = get_option('lectus_academy_general_settings');
    $checked = isset($options['enable_dark_mode']) && $options['enable_dark_mode'] ? 'checked' : '';
    ?>
    <label>
        <input type="checkbox" name="lectus_academy_general_settings[enable_dark_mode]" 
               value="1" <?php echo $checked; ?> />
        <?php _e('사용자가 다크 모드를 선택할 수 있도록 합니다.', 'lectus-academy'); ?>
    </label>
    <?php
}

/**
 * Theme settings page content with improved security
 * 
 * @return void
 */
function lectus_academy_settings_page() {
    // Sanitize tab parameter
    $allowed_tabs = array('general', 'test-restoration', 'customization');
    $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';
    
    // Validate tab
    if (!in_array($active_tab, $allowed_tabs, true)) {
        $active_tab = 'general';
    }
    ?>
    <div class="wrap lectus-academy-settings-wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <h2 class="nav-tab-wrapper lectus-theme-tabs">
            <a href="<?php echo admin_url('themes.php?page=lectus-academy-settings&tab=general'); ?>" class="theme-tab <?php echo $active_tab == 'general' ? 'theme-tab-active' : ''; ?>" data-tab="general">
                <?php _e('일반 설정', 'lectus-academy'); ?>
            </a>
            <a href="<?php echo admin_url('themes.php?page=lectus-academy-settings&tab=test-restoration'); ?>" class="theme-tab <?php echo $active_tab == 'test-restoration' ? 'theme-tab-active' : ''; ?>" data-tab="test-restoration">
                <?php _e('테스트 구성 복원', 'lectus-academy'); ?>
            </a>
            <a href="<?php echo admin_url('themes.php?page=lectus-academy-settings&tab=customization'); ?>" class="theme-tab <?php echo $active_tab == 'customization' ? 'theme-tab-active' : ''; ?>" data-tab="customization">
                <?php _e('커스터마이징', 'lectus-academy'); ?>
            </a>
        </h2>
        
        <?php if ($active_tab == 'general'): ?>
            <form action="options.php" method="post">
                <?php
                settings_fields('lectus_academy_settings');
                do_settings_sections('lectus_academy_settings');
                submit_button();
                ?>
            </form>
            
        <?php elseif ($active_tab == 'test-restoration'): ?>
            <?php Lectus_Academy_Test_Restoration::admin_page_content(); ?>
            
        <?php elseif ($active_tab == 'customization'): ?>
            <div class="lectus-customization-tab">
                <h3><?php _e('테마 커스터마이징', 'lectus-academy'); ?></h3>
                <p><?php _e('테마의 외관과 기능을 사용자 정의할 수 있습니다.', 'lectus-academy'); ?></p>
                
                <h4><?php _e('빠른 설정', 'lectus-academy'); ?></h4>
                <p>
                    <a href="<?php echo admin_url('customize.php'); ?>" class="button button-primary">
                        <?php _e('외관 커스터마이저 열기', 'lectus-academy'); ?>
                    </a>
                    <a href="<?php echo admin_url('nav-menus.php'); ?>" class="button button-secondary">
                        <?php _e('메뉴 관리', 'lectus-academy'); ?>
                    </a>
                    <a href="<?php echo admin_url('widgets.php'); ?>" class="button button-secondary">
                        <?php _e('위젯 관리', 'lectus-academy'); ?>
                    </a>
                </p>
                
                <h4><?php _e('테마 기능', 'lectus-academy'); ?></h4>
                <ul>
                    <li><?php _e('반응형 디자인 - 모든 기기에서 최적화된 표시', 'lectus-academy'); ?></li>
                    <li><?php _e('Lectus Class System 완전 통합', 'lectus-academy'); ?></li>
                    <li><?php _e('WooCommerce 지원 - 온라인 강의 판매', 'lectus-academy'); ?></li>
                    <li><?php _e('다크 모드 지원', 'lectus-academy'); ?></li>
                    <li><?php _e('SEO 최적화', 'lectus-academy'); ?></li>
                    <li><?php _e('접근성 표준 준수', 'lectus-academy'); ?></li>
                </ul>
                
                <h4><?php _e('시스템 정보', 'lectus-academy'); ?></h4>
                <table class="widefat">
                    <tr>
                        <td><?php _e('테마 버전', 'lectus-academy'); ?></td>
                        <td><?php echo wp_get_theme()->get('Version'); ?></td>
                    </tr>
                    <tr>
                        <td><?php _e('WordPress 버전', 'lectus-academy'); ?></td>
                        <td><?php echo get_bloginfo('version'); ?></td>
                    </tr>
                    <tr>
                        <td><?php _e('PHP 버전', 'lectus-academy'); ?></td>
                        <td><?php echo PHP_VERSION; ?></td>
                    </tr>
                    <tr>
                        <td><?php _e('Lectus Class System', 'lectus-academy'); ?></td>
                        <td><?php echo Lectus_Academy_Test_Restoration::is_lectus_plugin_active() ? 
                               __('활성화됨', 'lectus-academy') : __('비활성화됨', 'lectus-academy'); ?></td>
                    </tr>
                    <tr>
                        <td><?php _e('WooCommerce', 'lectus-academy'); ?></td>
                        <td><?php echo Lectus_Academy_Test_Restoration::is_woocommerce_active() ? 
                               __('활성화됨', 'lectus-academy') : __('비활성화됨', 'lectus-academy'); ?></td>
                    </tr>
                </table>
            </div>
            
        <?php endif; ?>
    </div>
    
    <style>
    .lectus-customization-tab h4 {
        margin-top: 30px;
        margin-bottom: 10px;
    }
    .lectus-customization-tab ul {
        margin-left: 20px;
    }
    .lectus-customization-tab ul li {
        margin-bottom: 5px;
    }
    .lectus-theme-tabs {
        margin-bottom: 20px;
        border-bottom: 1px solid #c3c4c7;
    }
    /* Custom theme tab styles to avoid conflict */
    .theme-tab {
        display: inline-block;
        margin: 0 8px -1px 0;
        padding: 9px 14px;
        font-size: 14px;
        line-height: 24px;
        color: #646970;
        background: #fff;
        border: 1px solid #c3c4c7;
        border-bottom: 1px solid #fff;
        text-decoration: none;
        transition: none;
    }
    .theme-tab:hover {
        background-color: #fff;
        color: #3c434a;
    }
    .theme-tab:focus {
        box-shadow: none;
        outline: 1px dotted #646970;
        outline-offset: -1px;
    }
    .theme-tab-active,
    .theme-tab-active:hover {
        background: #f0f0f1;
        border-bottom: 1px solid #f0f0f1;
        color: #000;
    }
    </style>
    <?php
}