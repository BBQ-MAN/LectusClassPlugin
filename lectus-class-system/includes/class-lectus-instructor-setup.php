<?php
/**
 * Instructor Setup and Verification
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Instructor_Setup {
    
    /**
     * Initialize instructor setup
     */
    public static function init() {
        // Check and fix instructor roles on admin init
        add_action('admin_init', array(__CLASS__, 'verify_instructor_roles'));
        
        // Add admin notice if instructor role issues detected
        add_action('admin_notices', array(__CLASS__, 'show_instructor_setup_notice'));
    }
    
    /**
     * Verify and fix instructor roles
     */
    public static function verify_instructor_roles() {
        // Only run for admins
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Check if we need to run the fix
        if (get_transient('lectus_instructor_roles_checked')) {
            return;
        }
        
        // Get the testinstruct user
        $instructor_user = get_user_by('login', 'testinstruct');
        
        if ($instructor_user) {
            // Check if user has instructor role
            if (!in_array('lectus_instructor', $instructor_user->roles)) {
                // Add instructor role
                $instructor_user->add_role('lectus_instructor');
                
                // Set a flag that we fixed the role
                update_option('lectus_instructor_role_fixed', true);
                
                // Log the action
                error_log('Lectus: Added lectus_instructor role to testinstruct user');
            }
            
            // Ensure the instructor has necessary capabilities
            self::ensure_instructor_capabilities($instructor_user);
        }
        
        // Set transient to avoid checking every page load
        set_transient('lectus_instructor_roles_checked', true, DAY_IN_SECONDS);
    }
    
    /**
     * Ensure instructor has all necessary capabilities
     */
    private static function ensure_instructor_capabilities($user) {
        // Get the instructor role
        $instructor_role = get_role('lectus_instructor');
        
        if (!$instructor_role) {
            // Create the role if it doesn't exist
            Lectus_Capabilities::create_roles();
            $instructor_role = get_role('lectus_instructor');
        }
        
        // Ensure basic capabilities
        if ($instructor_role) {
            // Basic WordPress capabilities
            $instructor_role->add_cap('read');
            $instructor_role->add_cap('edit_posts');
            $instructor_role->add_cap('delete_posts');
            $instructor_role->add_cap('publish_posts');
            $instructor_role->add_cap('upload_files');
            
            // Course management capabilities
            $instructor_role->add_cap('edit_courses');
            $instructor_role->add_cap('publish_courses');
            $instructor_role->add_cap('delete_courses');
            $instructor_role->add_cap('edit_published_courses');
            $instructor_role->add_cap('delete_published_courses');
            
            // Lesson management capabilities
            $instructor_role->add_cap('edit_lessons');
            $instructor_role->add_cap('publish_lessons');
            $instructor_role->add_cap('delete_lessons');
            $instructor_role->add_cap('edit_published_lessons');
            $instructor_role->add_cap('delete_published_lessons');
            
            // Student management capabilities
            $instructor_role->add_cap('view_students');
            $instructor_role->add_cap('manage_students');
            $instructor_role->add_cap('view_reports');
            
            // Q&A management capabilities
            $instructor_role->add_cap('manage_qa');
            $instructor_role->add_cap('moderate_qa');
            $instructor_role->add_cap('answer_questions');
            $instructor_role->add_cap('delete_qa');
        }
    }
    
    /**
     * Show admin notice if instructor setup was performed
     */
    public static function show_instructor_setup_notice() {
        if (get_option('lectus_instructor_role_fixed')) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e('강사 역할이 성공적으로 설정되었습니다. testinstruct 계정으로 이제 강사 센터에 접근할 수 있습니다.', 'lectus-class-system'); ?></p>
            </div>
            <?php
            // Remove the flag after showing
            delete_option('lectus_instructor_role_fixed');
        }
    }
    
    /**
     * Manual fix for instructor accounts
     */
    public static function fix_instructor_account($username) {
        $user = get_user_by('login', $username);
        
        if (!$user) {
            return new WP_Error('user_not_found', __('사용자를 찾을 수 없습니다.', 'lectus-class-system'));
        }
        
        // Remove all roles first
        $user->set_role('');
        
        // Add instructor role
        $user->add_role('lectus_instructor');
        
        // Ensure capabilities
        self::ensure_instructor_capabilities($user);
        
        // Clear transient to force re-check
        delete_transient('lectus_instructor_roles_checked');
        
        return true;
    }
    
    /**
     * Create test instructor account if needed
     */
    public static function create_test_instructor() {
        $username = 'testinstruct';
        $user = get_user_by('login', $username);
        
        if (!$user) {
            // Create the user
            $user_id = wp_create_user(
                $username,
                'Ti5(*bSEHV8ziYWleN(JfM06',
                'instructor@example.com'
            );
            
            if (!is_wp_error($user_id)) {
                $user = get_user_by('id', $user_id);
                $user->set_role('lectus_instructor');
                
                // Update user meta
                update_user_meta($user_id, 'first_name', '테스트');
                update_user_meta($user_id, 'last_name', '강사');
                update_user_meta($user_id, 'display_name', '테스트 강사');
                
                return $user_id;
            }
        } else {
            // Ensure the user has instructor role
            if (!in_array('lectus_instructor', $user->roles)) {
                $user->add_role('lectus_instructor');
            }
            
            return $user->ID;
        }
        
        return false;
    }
}