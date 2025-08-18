<?php
/**
 * User Roles and Capabilities for Lectus Class System
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Capabilities {
    
    public static function init() {
        // Add capabilities to existing roles
        add_action('admin_init', array(__CLASS__, 'add_capabilities'));
    }
    
    public static function create_roles() {
        // Create Instructor role
        add_role(
            'lectus_instructor',
            __('강사', 'lectus-class-system'),
            array(
                'read' => true,
                'edit_posts' => true,
                'delete_posts' => true,
                'publish_posts' => true,
                'upload_files' => true,
                
                // Course capabilities
                'edit_courses' => true,
                'publish_courses' => true,
                'delete_courses' => true,
                'edit_published_courses' => true,
                'delete_published_courses' => true,
                
                // Lesson capabilities
                'edit_lessons' => true,
                'publish_lessons' => true,
                'delete_lessons' => true,
                'edit_published_lessons' => true,
                'delete_published_lessons' => true,
                
                // Student management
                'view_students' => true,
                'manage_students' => true,
                'view_reports' => true,
                
                // Q&A management
                'manage_qa' => true,
                'moderate_qa' => true,
                'answer_questions' => true,
                'delete_qa' => true,
            )
        );
        
        // Create Student role
        add_role(
            'lectus_student',
            __('수강생', 'lectus-class-system'),
            array(
                'read' => true,
                'view_courses' => true,
                'view_lessons' => true,
                'submit_assignments' => true,
                'view_certificates' => true,
            )
        );
    }
    
    public static function add_capabilities() {
        // Get roles
        $administrator = get_role('administrator');
        $editor = get_role('editor');
        
        // Admin capabilities
        if ($administrator) {
            // Course Package capabilities
            $administrator->add_cap('edit_coursepackages');
            $administrator->add_cap('edit_others_coursepackages');
            $administrator->add_cap('publish_coursepackages');
            $administrator->add_cap('read_private_coursepackages');
            $administrator->add_cap('delete_coursepackages');
            $administrator->add_cap('delete_private_coursepackages');
            $administrator->add_cap('delete_published_coursepackages');
            $administrator->add_cap('delete_others_coursepackages');
            $administrator->add_cap('edit_private_coursepackages');
            $administrator->add_cap('edit_published_coursepackages');
            
            // Course capabilities
            $administrator->add_cap('edit_courses');
            $administrator->add_cap('edit_others_courses');
            $administrator->add_cap('publish_courses');
            $administrator->add_cap('read_private_courses');
            $administrator->add_cap('delete_courses');
            $administrator->add_cap('delete_private_courses');
            $administrator->add_cap('delete_published_courses');
            $administrator->add_cap('delete_others_courses');
            $administrator->add_cap('edit_private_courses');
            $administrator->add_cap('edit_published_courses');
            
            // Lesson capabilities
            $administrator->add_cap('edit_lessons');
            $administrator->add_cap('edit_others_lessons');
            $administrator->add_cap('publish_lessons');
            $administrator->add_cap('read_private_lessons');
            $administrator->add_cap('delete_lessons');
            $administrator->add_cap('delete_private_lessons');
            $administrator->add_cap('delete_published_lessons');
            $administrator->add_cap('delete_others_lessons');
            $administrator->add_cap('edit_private_lessons');
            $administrator->add_cap('edit_published_lessons');
            
            // Student management
            $administrator->add_cap('manage_students');
            $administrator->add_cap('view_students');
            $administrator->add_cap('edit_students');
            $administrator->add_cap('delete_students');
            
            // Reports
            $administrator->add_cap('view_reports');
            $administrator->add_cap('export_reports');
            
            // Settings
            $administrator->add_cap('manage_lectus_settings');
            
            // Q&A management
            $administrator->add_cap('manage_qa');
            $administrator->add_cap('moderate_qa');
            $administrator->add_cap('answer_questions');
            $administrator->add_cap('delete_qa');
        }
        
        // Editor capabilities
        if ($editor) {
            $editor->add_cap('edit_courses');
            $editor->add_cap('edit_others_courses');
            $editor->add_cap('publish_courses');
            $editor->add_cap('delete_courses');
            $editor->add_cap('edit_published_courses');
            
            $editor->add_cap('edit_lessons');
            $editor->add_cap('edit_others_lessons');
            $editor->add_cap('publish_lessons');
            $editor->add_cap('delete_lessons');
            $editor->add_cap('edit_published_lessons');
            
            $editor->add_cap('view_students');
            $editor->add_cap('view_reports');
        }
    }
    
    public static function remove_roles() {
        remove_role('lectus_instructor');
        remove_role('lectus_student');
    }
}