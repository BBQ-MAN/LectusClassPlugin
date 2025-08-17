<?php
/**
 * Theme Setup and Activation
 * 
 * Handles theme activation, deactivation, and initial setup
 * 
 * @package LectusAcademy
 * @since 2.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Academy_Theme_Setup {
    
    /**
     * Run on theme activation
     */
    public static function activate() {
        // Set theme activation flag
        update_option('lectus_academy_theme_activated', true);
        
        // Create default pages
        self::create_default_pages();
        
        // Setup default theme options
        self::setup_default_options();
        
        // Create default menus
        self::create_default_menus();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Create default pages on activation
     */
    private static function create_default_pages() {
        $default_pages = array(
            // Core LMS pages
            array(
                'title' => '강의',
                'slug' => 'courses',
                'content' => '[lectus_courses]',
                'template' => 'page-courses.php'
            ),
            array(
                'title' => '내 강의',
                'slug' => 'my-courses',
                'content' => '[lectus_my_courses]',
                'template' => 'page-my-courses.php'
            ),
            array(
                'title' => '학습 대시보드',
                'slug' => 'student-dashboard',
                'content' => '[lectus_student_dashboard]',
                'template' => 'page-dashboard.php'
            ),
            array(
                'title' => '수료증',
                'slug' => 'certificates',
                'content' => '[lectus_certificates]',
                'template' => 'page-certificates.php'
            ),
            array(
                'title' => '수료증 확인',
                'slug' => 'verify-certificate',
                'content' => '[lectus_certificate_verify]',
                'template' => 'page-verify.php'
            ),
            
            // Utility pages
            array(
                'title' => '로그인',
                'slug' => 'login',
                'content' => '[lectus_login_form]',
                'template' => 'page-login.php'
            ),
            array(
                'title' => '회원가입',
                'slug' => 'register',
                'content' => '[lectus_registration_form]',
                'template' => 'page-register.php'
            ),
            array(
                'title' => '내 프로필',
                'slug' => 'profile',
                'content' => '[lectus_user_profile]',
                'template' => 'page-profile.php'
            )
        );
        
        $created_pages = array();
        
        foreach ($default_pages as $page_data) {
            // Check if page exists
            $existing_page = get_page_by_path($page_data['slug']);
            
            if (!$existing_page) {
                $page_id = wp_insert_post(array(
                    'post_title' => $page_data['title'],
                    'post_name' => $page_data['slug'],
                    'post_content' => $page_data['content'],
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_author' => get_current_user_id() ?: 1,
                    'comment_status' => 'closed',
                    'ping_status' => 'closed'
                ));
                
                if ($page_id && !is_wp_error($page_id)) {
                    // Set page template
                    if (isset($page_data['template'])) {
                        update_post_meta($page_id, '_wp_page_template', $page_data['template']);
                    }
                    
                    $created_pages[] = $page_data['title'];
                }
            }
        }
        
        // Store created pages for admin notice
        if (!empty($created_pages)) {
            set_transient('lectus_academy_created_pages', $created_pages, 300);
        }
        
        return $created_pages;
    }
    
    /**
     * Setup default theme options
     */
    private static function setup_default_options() {
        // Default theme settings
        $default_settings = array(
            'site_title' => get_bloginfo('name'),
            'site_description' => get_bloginfo('description'),
            'footer_text' => '© ' . date('Y') . ' ' . get_bloginfo('name') . '. All rights reserved.',
            'enable_dark_mode' => false,
            'primary_color' => '#30b2e5',
            'secondary_color' => '#524fa1'
        );
        
        // Only set if not already exists
        $existing_settings = get_option('lectus_academy_general_settings', array());
        $merged_settings = wp_parse_args($existing_settings, $default_settings);
        update_option('lectus_academy_general_settings', $merged_settings);
        
        // Set homepage
        $homepage = get_page_by_path('home');
        if (!$homepage) {
            // Create homepage if it doesn't exist
            $homepage_id = wp_insert_post(array(
                'post_title' => 'Home',
                'post_name' => 'home',
                'post_content' => self::get_homepage_content(),
                'post_status' => 'publish',
                'post_type' => 'page',
                'comment_status' => 'closed',
                'ping_status' => 'closed'
            ));
            
            if ($homepage_id && !is_wp_error($homepage_id)) {
                update_option('page_on_front', $homepage_id);
                update_option('show_on_front', 'page');
            }
        }
    }
    
    /**
     * Get default homepage content
     */
    private static function get_homepage_content() {
        return '
<!-- Hero Section -->
<div class="hero-section">
    <h1>최고의 온라인 교육 플랫폼</h1>
    <p>Lectus Academy에서 새로운 지식과 기술을 배워보세요</p>
    [lectus_search_form]
</div>

<!-- Featured Courses -->
<h2>인기 강의</h2>
[lectus_featured_courses limit="8"]

<!-- Course Categories -->
<h2>카테고리별 강의</h2>
[lectus_course_categories]

<!-- Instructor CTA -->
<div class="instructor-cta">
    <h2>강사가 되어보세요</h2>
    <p>당신의 지식과 경험을 공유하고 수익을 창출하세요</p>
    <a href="/instructor-register" class="button">강사 신청하기</a>
</div>
';
    }
    
    /**
     * Create default menus
     */
    private static function create_default_menus() {
        // Check if Primary Menu already exists
        $primary_menu_name = 'Primary Menu';
        $primary_menu = wp_get_nav_menu_object($primary_menu_name);
        
        if ($primary_menu) {
            // Menu exists, use its ID
            $menu_id = $primary_menu->term_id;
        } else {
            // Try to create the menu
            $menu_id = wp_create_nav_menu($primary_menu_name);
            
            // If creation fails due to name conflict, try alternative name
            if (is_wp_error($menu_id)) {
                $primary_menu_name = 'Lectus Primary Menu';
                $menu_id = wp_create_nav_menu($primary_menu_name);
            }
        }
        
        if ($menu_id && !is_wp_error($menu_id)) {
            // Add menu items
            $menu_items = array(
                array('title' => '홈', 'url' => home_url()),
                array('title' => '강의', 'page' => 'courses'),
                array('title' => '내 강의', 'page' => 'my-courses'),
                array('title' => '대시보드', 'page' => 'student-dashboard'),
            );
            
            foreach ($menu_items as $item) {
                if (isset($item['page'])) {
                    $page = get_page_by_path($item['page']);
                    if ($page) {
                        wp_update_nav_menu_item($menu_id, 0, array(
                            'menu-item-title' => $item['title'],
                            'menu-item-object' => 'page',
                            'menu-item-object-id' => $page->ID,
                            'menu-item-type' => 'post_type',
                            'menu-item-status' => 'publish'
                        ));
                    }
                } else {
                    wp_update_nav_menu_item($menu_id, 0, array(
                        'menu-item-title' => $item['title'],
                        'menu-item-url' => $item['url'],
                        'menu-item-type' => 'custom',
                        'menu-item-status' => 'publish'
                    ));
                }
            }
            
            // Assign to theme location
            $locations = get_theme_mod('nav_menu_locations', array());
            $locations['primary'] = $menu_id;
            set_theme_mod('nav_menu_locations', $locations);
        }
    }
    
    /**
     * Restore missing pages
     */
    public static function restore_missing_pages() {
        // Check if restoration is requested
        if (!isset($_GET['restore_pages']) || $_GET['restore_pages'] !== '1') {
            return;
        }
        
        // Verify nonce for security
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'restore_pages_nonce')) {
            return;
        }
        
        // Check user capability
        if (!current_user_can('edit_theme_options')) {
            return;
        }
        
        // Create missing pages
        $created_pages = self::create_default_pages();
        
        // Set transient for admin notice
        if (!empty($created_pages)) {
            set_transient('lectus_academy_restored_pages', $created_pages, 300);
        } else {
            set_transient('lectus_academy_no_pages_created', true, 300);
        }
        
        // Redirect to remove query parameters
        wp_redirect(admin_url('themes.php?page=lectus-academy-settings&tab=test-restoration'));
        exit;
    }
    
    /**
     * Show activation notice
     */
    public static function activation_notice() {
        // Check if theme was just activated
        if (get_option('lectus_academy_theme_activated')) {
            ?>
            <div class="notice notice-success is-dismissible">
                <h3><?php _e('Lectus Academy 테마가 성공적으로 활성화되었습니다!', 'lectus-academy'); ?></h3>
                <?php
                $created_pages = get_transient('lectus_academy_created_pages');
                if ($created_pages && is_array($created_pages)) {
                    echo '<p>' . sprintf(__('다음 페이지가 자동 생성되었습니다: %s', 'lectus-academy'), implode(', ', $created_pages)) . '</p>';
                    delete_transient('lectus_academy_created_pages');
                }
                ?>
                <p>
                    <a href="<?php echo admin_url('themes.php?page=lectus-academy-settings'); ?>" class="button button-primary">
                        <?php _e('테마 설정으로 이동', 'lectus-academy'); ?>
                    </a>
                    <a href="<?php echo home_url(); ?>" class="button" target="_blank">
                        <?php _e('사이트 보기', 'lectus-academy'); ?>
                    </a>
                </p>
            </div>
            <?php
            
            // Remove the activation flag
            delete_option('lectus_academy_theme_activated');
        }
        
        // Show restoration success message
        $restored_pages = get_transient('lectus_academy_restored_pages');
        if ($restored_pages && is_array($restored_pages)) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo sprintf(__('다음 페이지가 복원되었습니다: %s', 'lectus-academy'), implode(', ', $restored_pages)); ?></p>
            </div>
            <?php
            delete_transient('lectus_academy_restored_pages');
        }
        
        if (get_transient('lectus_academy_no_pages_created')) {
            ?>
            <div class="notice notice-info is-dismissible">
                <p><?php _e('모든 필수 페이지가 이미 존재합니다.', 'lectus-academy'); ?></p>
            </div>
            <?php
            delete_transient('lectus_academy_no_pages_created');
        }
    }
    
    /**
     * Check and show missing pages notice
     */
    public static function check_required_pages() {
        $required_pages = array('courses', 'my-courses', 'student-dashboard');
        $missing_pages = array();
        
        foreach ($required_pages as $slug) {
            if (!get_page_by_path($slug)) {
                $missing_pages[] = $slug;
            }
        }
        
        if (!empty($missing_pages) && current_user_can('edit_theme_options')) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong><?php _e('Lectus Academy:', 'lectus-academy'); ?></strong>
                    <?php _e('일부 필수 페이지가 누락되었습니다.', 'lectus-academy'); ?>
                </p>
                <p>
                    <?php 
                    $restore_url = wp_nonce_url(
                        admin_url('themes.php?page=lectus-academy-settings&tab=test-restoration&restore_pages=1'),
                        'restore_pages_nonce'
                    );
                    ?>
                    <a href="<?php echo esc_url($restore_url); ?>" class="button">
                        <?php _e('페이지 복원하기', 'lectus-academy'); ?>
                    </a>
                </p>
            </div>
            <?php
        }
    }
}

// Hook into theme activation
add_action('after_switch_theme', array('Lectus_Academy_Theme_Setup', 'activate'));
add_action('admin_notices', array('Lectus_Academy_Theme_Setup', 'activation_notice'));
add_action('admin_notices', array('Lectus_Academy_Theme_Setup', 'check_required_pages'));
add_action('admin_init', array('Lectus_Academy_Theme_Setup', 'restore_missing_pages'));