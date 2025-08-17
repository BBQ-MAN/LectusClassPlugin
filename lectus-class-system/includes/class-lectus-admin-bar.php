<?php
/**
 * Admin Bar Customization for Lectus Class System
 * 
 * Adds edit links to WordPress admin bar for courses and lessons
 * 
 * @package LectusClassSystem
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Admin_Bar {
    
    /**
     * Initialize admin bar customizations
     */
    public static function init() {
        // Add admin bar items
        add_action('admin_bar_menu', array(__CLASS__, 'add_admin_bar_items'), 100);
        
        // Add custom CSS for admin bar
        add_action('wp_head', array(__CLASS__, 'admin_bar_styles'));
        add_action('admin_head', array(__CLASS__, 'admin_bar_styles'));
    }
    
    /**
     * Add custom items to admin bar
     * 
     * @param WP_Admin_Bar $wp_admin_bar
     */
    public static function add_admin_bar_items($wp_admin_bar) {
        // Check if user can edit posts
        if (!current_user_can('edit_posts')) {
            return;
        }
        
        // Check if we're on a single course or lesson page
        if (is_singular('coursesingle') || is_singular('coursepackage') || is_singular('lesson')) {
            self::add_edit_links($wp_admin_bar);
        }
        
        // Add quick access menu for administrators
        if (current_user_can('manage_options')) {
            self::add_lectus_menu($wp_admin_bar);
        }
    }
    
    /**
     * Add edit links for current post
     * 
     * @param WP_Admin_Bar $wp_admin_bar
     */
    private static function add_edit_links($wp_admin_bar) {
        global $post;
        
        if (!$post) {
            return;
        }
        
        $post_type_obj = get_post_type_object($post->post_type);
        if (!$post_type_obj) {
            return;
        }
        
        // Get the appropriate icon and label
        $icon = '';
        $label = '';
        $additional_items = array();
        
        switch ($post->post_type) {
            case 'coursesingle':
                $icon = '📚';
                $label = __('단과강의 편집', 'lectus-class-system');
                
                // Add related items
                $package_id = get_post_meta($post->ID, '_package_id', true);
                if ($package_id) {
                    $additional_items[] = array(
                        'id' => 'edit-package',
                        'title' => '📦 ' . __('패키지강의 편집', 'lectus-class-system'),
                        'href' => get_edit_post_link($package_id),
                        'meta' => array('target' => '_blank')
                    );
                }
                
                // Add lessons management
                $additional_items[] = array(
                    'id' => 'manage-lessons',
                    'title' => '📝 ' . __('레슨 관리', 'lectus-class-system'),
                    'href' => admin_url('edit.php?post_type=lesson&course_id=' . $post->ID),
                );
                
                // Add students management
                $additional_items[] = array(
                    'id' => 'manage-students',
                    'title' => '👥 ' . __('수강생 관리', 'lectus-class-system'),
                    'href' => admin_url('admin.php?page=lectus-students&course_id=' . $post->ID),
                );
                
                // Add materials management
                $additional_items[] = array(
                    'id' => 'manage-materials',
                    'title' => '📁 ' . __('강의자료 관리', 'lectus-class-system'),
                    'href' => admin_url('admin.php?page=lectus-materials&course_id=' . $post->ID),
                );
                break;
                
            case 'coursepackage':
                $icon = '📦';
                $label = __('패키지강의 편집', 'lectus-class-system');
                
                // Add courses management
                $additional_items[] = array(
                    'id' => 'manage-courses',
                    'title' => '📚 ' . __('단과강의 관리', 'lectus-class-system'),
                    'href' => admin_url('edit.php?post_type=coursesingle&package_id=' . $post->ID),
                );
                break;
                
            case 'lesson':
                $icon = '📝';
                $label = __('레슨 편집', 'lectus-class-system');
                
                // Add parent course edit link
                $course_id = get_post_meta($post->ID, '_course_id', true);
                if ($course_id) {
                    $course = get_post($course_id);
                    if ($course) {
                        $additional_items[] = array(
                            'id' => 'edit-course',
                            'title' => '📚 ' . sprintf(__('%s 편집', 'lectus-class-system'), $course->post_title),
                            'href' => get_edit_post_link($course_id),
                            'meta' => array('target' => '_blank')
                        );
                        
                        // Add navigation to other lessons
                        $additional_items[] = array(
                            'id' => 'view-lessons',
                            'title' => '📋 ' . __('전체 레슨 보기', 'lectus-class-system'),
                            'href' => admin_url('edit.php?post_type=lesson&course_id=' . $course_id),
                        );
                    }
                }
                
                // Add Q&A management
                $additional_items[] = array(
                    'id' => 'manage-qa',
                    'title' => '💬 ' . __('Q&A 관리', 'lectus-class-system'),
                    'href' => admin_url('admin.php?page=lectus-qa&lesson_id=' . $post->ID),
                );
                break;
        }
        
        // Add main edit button
        $wp_admin_bar->add_node(array(
            'id' => 'lectus-edit-post',
            'title' => $icon . ' ' . $label,
            'href' => get_edit_post_link($post->ID),
            'meta' => array(
                'class' => 'lectus-admin-bar-edit',
                'title' => sprintf(__('%s 편집', 'lectus-class-system'), $post->post_title),
            )
        ));
        
        // Add additional items as submenu
        foreach ($additional_items as $item) {
            $item['parent'] = 'lectus-edit-post';
            $wp_admin_bar->add_node($item);
        }
        
        // Add view statistics
        if (current_user_can('manage_options')) {
            $wp_admin_bar->add_node(array(
                'parent' => 'lectus-edit-post',
                'id' => 'view-stats',
                'title' => '📊 ' . __('통계 보기', 'lectus-class-system'),
                'href' => admin_url('admin.php?page=lectus-reports&post_id=' . $post->ID),
            ));
        }
    }
    
    /**
     * Add Lectus quick access menu
     * 
     * @param WP_Admin_Bar $wp_admin_bar
     */
    private static function add_lectus_menu($wp_admin_bar) {
        // Main menu
        $wp_admin_bar->add_node(array(
            'id' => 'lectus-menu',
            'title' => '🎓 ' . __('Lectus', 'lectus-class-system'),
            'href' => admin_url('admin.php?page=lectus-class-system'),
            'meta' => array(
                'class' => 'lectus-admin-bar-menu',
                'title' => __('Lectus Class System', 'lectus-class-system'),
            )
        ));
        
        // Submenu items
        $menu_items = array(
            array(
                'id' => 'lectus-packages',
                'title' => '📦 ' . __('패키지강의', 'lectus-class-system'),
                'href' => admin_url('edit.php?post_type=coursepackage'),
            ),
            array(
                'id' => 'lectus-courses',
                'title' => '📚 ' . __('단과강의', 'lectus-class-system'),
                'href' => admin_url('edit.php?post_type=coursesingle'),
            ),
            array(
                'id' => 'lectus-lessons',
                'title' => '📝 ' . __('레슨', 'lectus-class-system'),
                'href' => admin_url('edit.php?post_type=lesson'),
            ),
            array(
                'id' => 'lectus-students',
                'title' => '👥 ' . __('수강생', 'lectus-class-system'),
                'href' => admin_url('admin.php?page=lectus-students'),
            ),
            array(
                'id' => 'lectus-certificates',
                'title' => '🏆 ' . __('수료증', 'lectus-class-system'),
                'href' => admin_url('admin.php?page=lectus-certificates'),
            ),
            array(
                'id' => 'lectus-qa',
                'title' => '💬 ' . __('Q&A', 'lectus-class-system'),
                'href' => admin_url('admin.php?page=lectus-qa'),
            ),
            array(
                'id' => 'lectus-reports',
                'title' => '📊 ' . __('리포트', 'lectus-class-system'),
                'href' => admin_url('admin.php?page=lectus-reports'),
            ),
            array(
                'id' => 'lectus-settings',
                'title' => '⚙️ ' . __('설정', 'lectus-class-system'),
                'href' => admin_url('admin.php?page=lectus-settings'),
            ),
        );
        
        foreach ($menu_items as $item) {
            $item['parent'] = 'lectus-menu';
            $wp_admin_bar->add_node($item);
        }
        
        // Add separator
        $wp_admin_bar->add_node(array(
            'parent' => 'lectus-menu',
            'id' => 'lectus-separator',
            'title' => '<hr style="margin: 5px 0; border: none; border-top: 1px solid #555;">',
            'meta' => array('html' => true)
        ));
        
        // Add quick create menu
        $wp_admin_bar->add_node(array(
            'parent' => 'lectus-menu',
            'id' => 'lectus-create-new',
            'title' => '➕ ' . __('새로 만들기', 'lectus-class-system'),
        ));
        
        $create_items = array(
            array(
                'id' => 'new-package',
                'title' => __('패키지강의', 'lectus-class-system'),
                'href' => admin_url('post-new.php?post_type=coursepackage'),
            ),
            array(
                'id' => 'new-course',
                'title' => __('단과강의', 'lectus-class-system'),
                'href' => admin_url('post-new.php?post_type=coursesingle'),
            ),
            array(
                'id' => 'new-lesson',
                'title' => __('레슨', 'lectus-class-system'),
                'href' => admin_url('post-new.php?post_type=lesson'),
            ),
        );
        
        foreach ($create_items as $item) {
            $item['parent'] = 'lectus-create-new';
            $wp_admin_bar->add_node($item);
        }
    }
    
    /**
     * Add custom CSS for admin bar items
     */
    public static function admin_bar_styles() {
        if (!is_admin_bar_showing()) {
            return;
        }
        ?>
        <style type="text/css">
            #wpadminbar .lectus-admin-bar-edit > a {
                background-color: #2271b1 !important;
                color: #fff !important;
                font-weight: 600;
            }
            
            #wpadminbar .lectus-admin-bar-edit > a:hover {
                background-color: #135e96 !important;
            }
            
            #wpadminbar .lectus-admin-bar-menu > a {
                background-color: #30b2e5 !important;
                color: #fff !important;
            }
            
            #wpadminbar .lectus-admin-bar-menu > a:hover {
                background-color: #2196c8 !important;
            }
            
            #wpadminbar .lectus-admin-bar-edit .ab-sub-wrapper,
            #wpadminbar .lectus-admin-bar-menu .ab-sub-wrapper {
                background-color: #32373c !important;
            }
            
            #wpadminbar .lectus-admin-bar-edit .ab-submenu a,
            #wpadminbar .lectus-admin-bar-menu .ab-submenu a {
                color: #eee !important;
            }
            
            #wpadminbar .lectus-admin-bar-edit .ab-submenu a:hover,
            #wpadminbar .lectus-admin-bar-menu .ab-submenu a:hover {
                background-color: #464b50 !important;
                color: #fff !important;
            }
            
            /* Responsive adjustments */
            @media screen and (max-width: 782px) {
                #wpadminbar .lectus-admin-bar-edit > a,
                #wpadminbar .lectus-admin-bar-menu > a {
                    font-size: 14px;
                    padding: 0 10px;
                }
            }
        </style>
        <?php
    }
}