<?php
/**
 * Instructor Menu and Toolbar Management
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Instructor_Menu {
    
    public static function init() {
        // Add instructor menu
        add_action('admin_menu', array(__CLASS__, 'add_instructor_menu'), 5);
        
        // Add toolbar items for instructors
        add_action('admin_bar_menu', array(__CLASS__, 'add_instructor_toolbar_items'), 100);
        
        // Add dropdown menu to user account menu
        add_action('admin_bar_menu', array(__CLASS__, 'add_user_dropdown_items'), 999);
        
        // Frontend header menu for instructors
        add_action('wp_head', array(__CLASS__, 'add_frontend_instructor_menu'));
    }
    
    /**
     * Add instructor menu in admin
     */
    public static function add_instructor_menu() {
        $user = wp_get_current_user();
        
        // Check if user is instructor
        if (!in_array('lectus_instructor', $user->roles) && !current_user_can('manage_options')) {
            return;
        }
        
        // Add main menu for instructors
        add_menu_page(
            __('강사 센터', 'lectus-class-system'),
            __('강사 센터', 'lectus-class-system'),
            'read', // Basic capability that instructors have
            'lectus-instructor-center',
            array(__CLASS__, 'render_instructor_center'),
            'dashicons-welcome-learn-more',
            30
        );
        
        // Add dashboard submenu
        add_submenu_page(
            'lectus-instructor-center',
            __('대시보드', 'lectus-class-system'),
            __('대시보드', 'lectus-class-system'),
            'read',
            'lectus-instructor-center',
            array(__CLASS__, 'render_instructor_center')
        );
        
        // Add my courses submenu
        add_submenu_page(
            'lectus-instructor-center',
            __('내 강의', 'lectus-class-system'),
            __('내 강의', 'lectus-class-system'),
            'read',
            'lectus-instructor-my-courses',
            array('Lectus_Instructor_Dashboard', 'render_courses_page')
        );
        
        // Add Q&A management submenu
        add_submenu_page(
            'lectus-instructor-center',
            __('Q&A 관리', 'lectus-class-system'),
            __('Q&A 관리', 'lectus-class-system'),
            'read',
            'lectus-instructor-qa-menu',
            array('Lectus_Instructor_QA', 'render_instructor_page')
        );
        
        // Add students submenu
        add_submenu_page(
            'lectus-instructor-center',
            __('수강생 관리', 'lectus-class-system'),
            __('수강생 관리', 'lectus-class-system'),
            'read',
            'lectus-instructor-my-students',
            array('Lectus_Instructor_Dashboard', 'render_students_page')
        );
        
        // Add reports submenu
        add_submenu_page(
            'lectus-instructor-center',
            __('리포트', 'lectus-class-system'),
            __('리포트', 'lectus-class-system'),
            'read',
            'lectus-instructor-my-reports',
            array('Lectus_Instructor_Dashboard', 'render_reports_page')
        );
    }
    
    /**
     * Render instructor center main page
     */
    public static function render_instructor_center() {
        // Redirect to dashboard
        Lectus_Instructor_Dashboard::render_dashboard_page();
    }
    
    /**
     * Add instructor items to admin toolbar
     */
    public static function add_instructor_toolbar_items($wp_admin_bar) {
        $user = wp_get_current_user();
        
        // Check if user is instructor
        if (!in_array('lectus_instructor', $user->roles)) {
            return;
        }
        
        // Add instructor quick menu
        $wp_admin_bar->add_node(array(
            'id'    => 'instructor-quick-menu',
            'title' => '<span class="ab-icon dashicons dashicons-welcome-learn-more"></span>' . __('강사 메뉴', 'lectus-class-system'),
            'href'  => admin_url('admin.php?page=lectus-instructor-center'),
            'meta'  => array(
                'title' => __('강사 빠른 메뉴', 'lectus-class-system'),
            ),
        ));
        
        // Add sub-items
        $wp_admin_bar->add_node(array(
            'id'     => 'instructor-dashboard',
            'parent' => 'instructor-quick-menu',
            'title'  => __('대시보드', 'lectus-class-system'),
            'href'   => admin_url('admin.php?page=lectus-instructor-center'),
        ));
        
        $wp_admin_bar->add_node(array(
            'id'     => 'instructor-courses',
            'parent' => 'instructor-quick-menu',
            'title'  => __('내 강의', 'lectus-class-system'),
            'href'   => admin_url('admin.php?page=lectus-instructor-my-courses'),
        ));
        
        $wp_admin_bar->add_node(array(
            'id'     => 'instructor-qa',
            'parent' => 'instructor-quick-menu',
            'title'  => __('Q&A 관리', 'lectus-class-system'),
            'href'   => admin_url('admin.php?page=lectus-instructor-qa-menu'),
        ));
        
        $wp_admin_bar->add_node(array(
            'id'     => 'instructor-students',
            'parent' => 'instructor-quick-menu',
            'title'  => __('수강생', 'lectus-class-system'),
            'href'   => admin_url('admin.php?page=lectus-instructor-my-students'),
        ));
        
        // Add notification badge for unanswered questions
        $unanswered = self::get_unanswered_questions_count();
        if ($unanswered > 0) {
            $wp_admin_bar->add_node(array(
                'id'     => 'instructor-notifications',
                'parent' => 'instructor-quick-menu',
                'title'  => sprintf(__('미답변 질문 <span style="background:#d63638;color:#fff;padding:2px 6px;border-radius:10px;font-size:11px;">%d</span>', 'lectus-class-system'), $unanswered),
                'href'   => admin_url('admin.php?page=lectus-instructor-qa-menu&status=unanswered'),
            ));
        }
    }
    
    /**
     * Add items to user dropdown menu
     */
    public static function add_user_dropdown_items($wp_admin_bar) {
        $user = wp_get_current_user();
        
        // Check if user is instructor
        if (!in_array('lectus_instructor', $user->roles)) {
            return;
        }
        
        // Add separator
        $wp_admin_bar->add_node(array(
            'id'     => 'instructor-separator',
            'parent' => 'user-actions',
            'title'  => '<hr style="margin: 5px 0; border: none; border-top: 1px solid #ccc;">',
            'meta'   => array('tabindex' => -1),
        ));
        
        // Add instructor center link
        $wp_admin_bar->add_node(array(
            'id'     => 'instructor-center-link',
            'parent' => 'user-actions',
            'title'  => '📚 ' . __('강사 센터', 'lectus-class-system'),
            'href'   => admin_url('admin.php?page=lectus-instructor-center'),
        ));
        
        // Add my courses link
        $wp_admin_bar->add_node(array(
            'id'     => 'my-courses-link',
            'parent' => 'user-actions',
            'title'  => '📖 ' . __('내 강의 관리', 'lectus-class-system'),
            'href'   => admin_url('admin.php?page=lectus-instructor-my-courses'),
        ));
        
        // Add Q&A link with notification
        $unanswered = self::get_unanswered_questions_count();
        $qa_title = '💬 ' . __('Q&A 관리', 'lectus-class-system');
        if ($unanswered > 0) {
            $qa_title .= sprintf(' <span style="background:#d63638;color:#fff;padding:1px 5px;border-radius:10px;font-size:10px;margin-left:5px;">%d</span>', $unanswered);
        }
        
        $wp_admin_bar->add_node(array(
            'id'     => 'qa-management-link',
            'parent' => 'user-actions',
            'title'  => $qa_title,
            'href'   => admin_url('admin.php?page=lectus-instructor-qa-menu'),
        ));
        
        // Add students link
        $wp_admin_bar->add_node(array(
            'id'     => 'my-students-link',
            'parent' => 'user-actions',
            'title'  => '👥 ' . __('수강생 관리', 'lectus-class-system'),
            'href'   => admin_url('admin.php?page=lectus-instructor-my-students'),
        ));
    }
    
    /**
     * Add frontend instructor menu
     */
    public static function add_frontend_instructor_menu() {
        if (!is_user_logged_in()) {
            return;
        }
        
        $user = wp_get_current_user();
        
        // Check if user is instructor
        if (!in_array('lectus_instructor', $user->roles)) {
            return;
        }
        
        ?>
        <style>
            .lectus-instructor-menu {
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 9999;
            }
            
            .lectus-instructor-toggle {
                background: #2271b1;
                color: white;
                padding: 10px 20px;
                border-radius: 25px;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.2);
                transition: all 0.3s;
            }
            
            .lectus-instructor-toggle:hover {
                background: #135e96;
                transform: translateY(-2px);
                box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            }
            
            .lectus-instructor-dropdown {
                display: none;
                position: absolute;
                bottom: 60px;
                right: 0;
                background: white;
                border-radius: 8px;
                box-shadow: 0 5px 20px rgba(0,0,0,0.2);
                min-width: 200px;
                overflow: hidden;
            }
            
            .lectus-instructor-dropdown.active {
                display: block;
            }
            
            .lectus-instructor-dropdown a {
                display: block;
                padding: 12px 20px;
                color: #333;
                text-decoration: none;
                border-bottom: 1px solid #eee;
                transition: background 0.2s;
            }
            
            .lectus-instructor-dropdown a:hover {
                background: #f0f0f0;
            }
            
            .lectus-instructor-dropdown a:last-child {
                border-bottom: none;
            }
            
            .instructor-badge {
                background: #d63638;
                color: white;
                padding: 2px 6px;
                border-radius: 10px;
                font-size: 11px;
                margin-left: 5px;
            }
        </style>
        
        <div class="lectus-instructor-menu">
            <div class="lectus-instructor-toggle" onclick="toggleInstructorMenu()">
                <span class="dashicons dashicons-welcome-learn-more"></span>
                <span><?php _e('강사 메뉴', 'lectus-class-system'); ?></span>
            </div>
            <div class="lectus-instructor-dropdown" id="instructorDropdown">
                <a href="<?php echo admin_url('admin.php?page=lectus-instructor-center'); ?>">
                    📊 <?php _e('대시보드', 'lectus-class-system'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=lectus-instructor-my-courses'); ?>">
                    📚 <?php _e('내 강의', 'lectus-class-system'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=lectus-instructor-qa-menu'); ?>">
                    💬 <?php _e('Q&A 관리', 'lectus-class-system'); ?>
                    <?php 
                    $unanswered = self::get_unanswered_questions_count();
                    if ($unanswered > 0): 
                    ?>
                        <span class="instructor-badge"><?php echo $unanswered; ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=lectus-instructor-my-students'); ?>">
                    👥 <?php _e('수강생', 'lectus-class-system'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=lectus-instructor-my-reports'); ?>">
                    📈 <?php _e('리포트', 'lectus-class-system'); ?>
                </a>
            </div>
        </div>
        
        <script>
            function toggleInstructorMenu() {
                var dropdown = document.getElementById('instructorDropdown');
                dropdown.classList.toggle('active');
            }
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                var menu = document.querySelector('.lectus-instructor-menu');
                if (!menu.contains(event.target)) {
                    document.getElementById('instructorDropdown').classList.remove('active');
                }
            });
        </script>
        <?php
    }
    
    /**
     * Get unanswered questions count for current instructor
     */
    private static function get_unanswered_questions_count() {
        global $wpdb;
        
        $user_id = get_current_user_id();
        $is_admin = current_user_can('manage_options');
        
        // Get instructor's courses
        if ($is_admin) {
            $courses = get_posts(array(
                'post_type' => 'coursesingle',
                'posts_per_page' => -1,
                'fields' => 'ids'
            ));
        } else {
            $courses = get_posts(array(
                'post_type' => 'coursesingle',
                'meta_key' => '_course_instructor_id',
                'meta_value' => $user_id,
                'posts_per_page' => -1,
                'fields' => 'ids'
            ));
        }
        
        if (empty($courses)) {
            return 0;
        }
        
        $qa_table = $wpdb->prefix . 'lectus_qa_questions';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$qa_table}'") != $qa_table) {
            return 0;
        }
        
        $placeholders = implode(',', array_fill(0, count($courses), '%d'));
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$qa_table}
            WHERE course_id IN ({$placeholders})
            AND status = 'pending'",
            ...$courses
        ));
        
        return intval($count);
    }
}