<?php
/**
 * Login Redirect Handler for Lectus Class System
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Login_Redirect {
    
    public static function init() {
        // Login redirect hook
        add_filter('login_redirect', array(__CLASS__, 'custom_login_redirect'), 10, 3);
        
        // Admin access restriction - no longer needed as instructors need admin access
        // add_action('admin_init', array(__CLASS__, 'restrict_admin_access'));
        
        // Custom logout redirect
        add_action('wp_logout', array(__CLASS__, 'custom_logout_redirect'));
    }
    
    /**
     * Custom login redirect based on user role
     */
    public static function custom_login_redirect($redirect_to, $request, $user) {
        // If error or not a WP_User object, return default
        if (isset($user->errors) && !empty($user->errors)) {
            return $redirect_to;
        }
        
        if (!is_a($user, 'WP_User')) {
            return $redirect_to;
        }
        
        // Check user roles
        if (in_array('lectus_instructor', $user->roles)) {
            // Redirect instructor to instructor center
            return admin_url('admin.php?page=lectus-instructor-center');
        } elseif (in_array('lectus_student', $user->roles)) {
            // Redirect student to my courses page
            $my_courses_page = get_option('lectus_my_courses_page_id');
            if ($my_courses_page) {
                return get_permalink($my_courses_page);
            }
            // Fallback to home page with courses
            return home_url('/my-courses/');
        } elseif (in_array('administrator', $user->roles)) {
            // Redirect admin to Lectus dashboard
            return admin_url('admin.php?page=lectus-class-system');
        }
        
        // Default redirect
        return $redirect_to;
    }
    
    /**
     * Restrict admin access for certain roles
     */
    public static function restrict_admin_access() {
        // Allow AJAX requests
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }
        
        $user = wp_get_current_user();
        
        // If user is a student, redirect them away from admin
        if (in_array('lectus_student', $user->roles) && !in_array('administrator', $user->roles)) {
            // Check if they're trying to access admin area
            if (is_admin()) {
                // Allow access to profile page
                global $pagenow;
                $allowed_pages = array('profile.php', 'admin-ajax.php');
                
                if (!in_array($pagenow, $allowed_pages)) {
                    // Redirect to my courses page
                    $my_courses_page = get_option('lectus_my_courses_page_id');
                    if ($my_courses_page) {
                        wp_redirect(get_permalink($my_courses_page));
                    } else {
                        wp_redirect(home_url('/my-courses/'));
                    }
                    exit;
                }
            }
        }
        
        // For instructors, ensure they can only access allowed pages
        if (in_array('lectus_instructor', $user->roles) && !in_array('administrator', $user->roles)) {
            if (is_admin()) {
                global $pagenow;
                
                // Allowed pages for instructors
                $allowed_pages = array(
                    'profile.php',
                    'admin-ajax.php',
                    'admin.php',
                    'edit.php',
                    'post.php',
                    'post-new.php',
                    'upload.php',
                    'media-upload.php'
                );
                
                // Check if current page is allowed
                if (!in_array($pagenow, $allowed_pages)) {
                    // Check if it's a Lectus page
                    if (isset($_GET['page'])) {
                        $page = $_GET['page'];
                        $allowed_lectus_pages = array(
                            'lectus-instructor-center',
                            'lectus-instructor-dashboard',
                            'lectus-instructor-qa',
                            'lectus-instructor-qa-menu',
                            'lectus-instructor-courses',
                            'lectus-instructor-my-courses',
                            'lectus-instructor-students',
                            'lectus-instructor-my-students',
                            'lectus-instructor-reports',
                            'lectus-instructor-my-reports'
                        );
                        
                        if (!in_array($page, $allowed_lectus_pages)) {
                            wp_redirect(admin_url('admin.php?page=lectus-instructor-center'));
                            exit;
                        }
                    } else {
                        // If accessing edit.php, check post type
                        if ($pagenow == 'edit.php' && isset($_GET['post_type'])) {
                            $allowed_post_types = array('coursesingle', 'lesson', 'coursepackage');
                            if (!in_array($_GET['post_type'], $allowed_post_types)) {
                                wp_redirect(admin_url('admin.php?page=lectus-instructor-center'));
                                exit;
                            }
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Custom logout redirect
     */
    public static function custom_logout_redirect() {
        $redirect_url = home_url();
        
        // Check if there's a custom logout page
        $logout_page = get_option('lectus_logout_redirect_page');
        if ($logout_page) {
            $redirect_url = get_permalink($logout_page);
        }
        
        wp_redirect($redirect_url);
        exit;
    }
    
    /**
     * Get dashboard URL for user
     */
    public static function get_user_dashboard_url($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return home_url();
        }
        
        if (in_array('lectus_instructor', $user->roles)) {
            return admin_url('admin.php?page=lectus-instructor-center');
        } elseif (in_array('lectus_student', $user->roles)) {
            $my_courses_page = get_option('lectus_my_courses_page_id');
            if ($my_courses_page) {
                return get_permalink($my_courses_page);
            }
            return home_url('/my-courses/');
        } elseif (in_array('administrator', $user->roles)) {
            return admin_url('admin.php?page=lectus-class-system');
        }
        
        return home_url();
    }
}