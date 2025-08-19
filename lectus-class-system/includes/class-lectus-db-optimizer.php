<?php
/**
 * Database Optimization for Lectus Class System
 * 
 * Handles database indexes, query optimization, and performance improvements
 * 
 * @package Lectus_Class_System
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_DB_Optimizer {
    
    /**
     * Initialize database optimizer
     */
    public static function init() {
        // Run optimization on plugin activation
        register_activation_hook(LECTUS_PLUGIN_FILE, array(__CLASS__, 'optimize_database'));
        
        // Add admin action for manual optimization
        add_action('admin_init', array(__CLASS__, 'handle_optimization_request'));
        
        // Schedule periodic optimization
        add_action('lectus_daily_optimization', array(__CLASS__, 'run_daily_optimization'));
        
        if (!wp_next_scheduled('lectus_daily_optimization')) {
            wp_schedule_event(time(), 'daily', 'lectus_daily_optimization');
        }
    }
    
    /**
     * Optimize database with indexes and improvements
     */
    public static function optimize_database() {
        global $wpdb;
        
        $results = array();
        
        // Add indexes for enrollment table
        $results['enrollment'] = self::optimize_enrollment_table();
        
        // Add indexes for progress table
        $results['progress'] = self::optimize_progress_table();
        
        // Add indexes for materials table
        $results['materials'] = self::optimize_materials_table();
        
        // Add indexes for Q&A tables
        $results['qa'] = self::optimize_qa_tables();
        
        // Add indexes for certificate table
        $results['certificates'] = self::optimize_certificates_table();
        
        // Optimize WordPress meta tables for our usage
        $results['meta'] = self::optimize_meta_tables();
        
        // Update optimization timestamp
        update_option('lectus_last_db_optimization', time());
        
        return $results;
    }
    
    /**
     * Optimize enrollment table
     */
    private static function optimize_enrollment_table() {
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_enrollment';
        $indexes_added = array();
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            return array('error' => 'Table does not exist');
        }
        
        // Composite index for user and course lookup
        if (!self::index_exists($table, 'idx_user_course')) {
            $wpdb->query("ALTER TABLE $table ADD INDEX idx_user_course (user_id, course_id)");
            $indexes_added[] = 'idx_user_course';
        }
        
        // Index for status and expiration queries
        if (!self::index_exists($table, 'idx_status_expires')) {
            $wpdb->query("ALTER TABLE $table ADD INDEX idx_status_expires (status, expires_at)");
            $indexes_added[] = 'idx_status_expires';
        }
        
        // Index for order lookup
        if (!self::index_exists($table, 'idx_order')) {
            $wpdb->query("ALTER TABLE $table ADD INDEX idx_order (order_id)");
            $indexes_added[] = 'idx_order';
        }
        
        // Index for enrolled date queries
        if (!self::index_exists($table, 'idx_enrolled_at')) {
            $wpdb->query("ALTER TABLE $table ADD INDEX idx_enrolled_at (enrolled_at)");
            $indexes_added[] = 'idx_enrolled_at';
        }
        
        return array(
            'indexes_added' => $indexes_added,
            'table' => $table
        );
    }
    
    /**
     * Optimize progress table
     */
    private static function optimize_progress_table() {
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_progress';
        $indexes_added = array();
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            return array('error' => 'Table does not exist');
        }
        
        // Composite index for user and course progress
        if (!self::index_exists($table, 'idx_user_course')) {
            $wpdb->query("ALTER TABLE $table ADD INDEX idx_user_course (user_id, course_id)");
            $indexes_added[] = 'idx_user_course';
        }
        
        // Composite index for user and lesson
        if (!self::index_exists($table, 'idx_user_lesson')) {
            $wpdb->query("ALTER TABLE $table ADD INDEX idx_user_lesson (user_id, lesson_id)");
            $indexes_added[] = 'idx_user_lesson';
        }
        
        // Index for status queries
        if (!self::index_exists($table, 'idx_status')) {
            $wpdb->query("ALTER TABLE $table ADD INDEX idx_status (status)");
            $indexes_added[] = 'idx_status';
        }
        
        // Index for completion date queries
        if (!self::index_exists($table, 'idx_completed_at')) {
            $wpdb->query("ALTER TABLE $table ADD INDEX idx_completed_at (completed_at)");
            $indexes_added[] = 'idx_completed_at';
        }
        
        // Composite index for course completion queries
        if (!self::index_exists($table, 'idx_course_status')) {
            $wpdb->query("ALTER TABLE $table ADD INDEX idx_course_status (course_id, status)");
            $indexes_added[] = 'idx_course_status';
        }
        
        return array(
            'indexes_added' => $indexes_added,
            'table' => $table
        );
    }
    
    /**
     * Optimize materials table
     */
    private static function optimize_materials_table() {
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_materials';
        $indexes_added = array();
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            return array('error' => 'Table does not exist');
        }
        
        // Index for lesson materials lookup
        if (!self::index_exists($table, 'idx_lesson_material')) {
            $wpdb->query("ALTER TABLE $table ADD INDEX idx_lesson_material (lesson_id, material_type)");
            $indexes_added[] = 'idx_lesson_material';
        }
        
        // Index for course materials lookup
        if (!self::index_exists($table, 'idx_course_material')) {
            $wpdb->query("ALTER TABLE $table ADD INDEX idx_course_material (course_id, material_type)");
            $indexes_added[] = 'idx_course_material';
        }
        
        // Index for access level queries
        if (!self::index_exists($table, 'idx_access_status')) {
            $wpdb->query("ALTER TABLE $table ADD INDEX idx_access_status (access_level, status)");
            $indexes_added[] = 'idx_access_status';
        }
        
        return array(
            'indexes_added' => $indexes_added,
            'table' => $table
        );
    }
    
    /**
     * Optimize Q&A tables
     */
    private static function optimize_qa_tables() {
        global $wpdb;
        $results = array();
        
        // Questions table
        $questions_table = $wpdb->prefix . 'lectus_qa_questions';
        $questions_indexes = array();
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$questions_table'") == $questions_table) {
            if (!self::index_exists($questions_table, 'idx_course_status')) {
                $wpdb->query("ALTER TABLE $questions_table ADD INDEX idx_course_status (course_id, status)");
                $questions_indexes[] = 'idx_course_status';
            }
            
            if (!self::index_exists($questions_table, 'idx_user_questions')) {
                $wpdb->query("ALTER TABLE $questions_table ADD INDEX idx_user_questions (user_id, created_at)");
                $questions_indexes[] = 'idx_user_questions';
            }
            
            if (!self::index_exists($questions_table, 'idx_lesson_questions')) {
                $wpdb->query("ALTER TABLE $questions_table ADD INDEX idx_lesson_questions (lesson_id, status)");
                $questions_indexes[] = 'idx_lesson_questions';
            }
        }
        
        $results['questions'] = array(
            'indexes_added' => $questions_indexes,
            'table' => $questions_table
        );
        
        // Answers table
        $answers_table = $wpdb->prefix . 'lectus_qa_answers';
        $answers_indexes = array();
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$answers_table'") == $answers_table) {
            if (!self::index_exists($answers_table, 'idx_question_answers')) {
                $wpdb->query("ALTER TABLE $answers_table ADD INDEX idx_question_answers (question_id, created_at)");
                $answers_indexes[] = 'idx_question_answers';
            }
            
            if (!self::index_exists($answers_table, 'idx_instructor_answers')) {
                $wpdb->query("ALTER TABLE $answers_table ADD INDEX idx_instructor_answers (is_instructor, created_at)");
                $answers_indexes[] = 'idx_instructor_answers';
            }
        }
        
        $results['answers'] = array(
            'indexes_added' => $answers_indexes,
            'table' => $answers_table
        );
        
        return $results;
    }
    
    /**
     * Optimize certificates table
     */
    private static function optimize_certificates_table() {
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_certificates';
        $indexes_added = array();
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            return array('error' => 'Table does not exist');
        }
        
        // Composite index for user certificates
        if (!self::index_exists($table, 'idx_user_course')) {
            $wpdb->query("ALTER TABLE $table ADD INDEX idx_user_course (user_id, course_id)");
            $indexes_added[] = 'idx_user_course';
        }
        
        // Unique index for certificate number
        if (!self::index_exists($table, 'idx_certificate_number')) {
            $wpdb->query("ALTER TABLE $table ADD UNIQUE INDEX idx_certificate_number (certificate_number)");
            $indexes_added[] = 'idx_certificate_number';
        }
        
        // Index for completion date queries
        if (!self::index_exists($table, 'idx_completion_date')) {
            $wpdb->query("ALTER TABLE $table ADD INDEX idx_completion_date (completion_date)");
            $indexes_added[] = 'idx_completion_date';
        }
        
        return array(
            'indexes_added' => $indexes_added,
            'table' => $table
        );
    }
    
    /**
     * Optimize WordPress meta tables for our usage
     */
    private static function optimize_meta_tables() {
        global $wpdb;
        $indexes_added = array();
        
        // Add index for our custom post meta queries
        $postmeta_table = $wpdb->postmeta;
        
        // Check if we frequently query specific meta keys
        $meta_keys = array(
            '_lectus_course_duration',
            '_lectus_course_capacity',
            '_lectus_completion_criteria',
            '_lectus_linked_courses'
        );
        
        foreach ($meta_keys as $meta_key) {
            $index_name = 'idx_lectus_' . substr(md5($meta_key), 0, 8);
            if (!self::index_exists($postmeta_table, $index_name)) {
                $wpdb->query($wpdb->prepare(
                    "ALTER TABLE $postmeta_table ADD INDEX $index_name (meta_key, meta_value(20))"
                ));
                $indexes_added[] = $index_name;
            }
        }
        
        return array(
            'indexes_added' => $indexes_added,
            'table' => $postmeta_table
        );
    }
    
    /**
     * Check if index exists on table
     */
    private static function index_exists($table, $index_name) {
        global $wpdb;
        
        $result = $wpdb->get_results("SHOW INDEX FROM $table WHERE Key_name = '$index_name'");
        return !empty($result);
    }
    
    /**
     * Run daily optimization tasks
     */
    public static function run_daily_optimization() {
        global $wpdb;
        
        // Optimize tables
        $tables = array(
            $wpdb->prefix . 'lectus_enrollment',
            $wpdb->prefix . 'lectus_progress',
            $wpdb->prefix . 'lectus_materials',
            $wpdb->prefix . 'lectus_qa_questions',
            $wpdb->prefix . 'lectus_qa_answers',
            $wpdb->prefix . 'lectus_certificates'
        );
        
        foreach ($tables as $table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") == $table) {
                $wpdb->query("OPTIMIZE TABLE $table");
            }
        }
        
        // Clean up orphaned records
        self::cleanup_orphaned_records();
        
        // Update statistics
        update_option('lectus_last_optimization', time());
    }
    
    /**
     * Clean up orphaned records
     */
    private static function cleanup_orphaned_records() {
        global $wpdb;
        
        // Clean orphaned progress records
        $wpdb->query(
            "DELETE p FROM {$wpdb->prefix}lectus_progress p
             LEFT JOIN {$wpdb->users} u ON p.user_id = u.ID
             WHERE u.ID IS NULL"
        );
        
        // Clean orphaned enrollment records
        $wpdb->query(
            "DELETE e FROM {$wpdb->prefix}lectus_enrollment e
             LEFT JOIN {$wpdb->users} u ON e.user_id = u.ID
             WHERE u.ID IS NULL"
        );
        
        // Clean orphaned materials for deleted posts
        $wpdb->query(
            "DELETE m FROM {$wpdb->prefix}lectus_materials m
             LEFT JOIN {$wpdb->posts} p ON m.course_id = p.ID
             WHERE p.ID IS NULL"
        );
    }
    
    /**
     * Handle manual optimization request
     */
    public static function handle_optimization_request() {
        if (isset($_POST['lectus_optimize_db']) && 
            current_user_can('manage_options') &&
            wp_verify_nonce($_POST['lectus_optimize_nonce'], 'lectus_optimize_db')) {
            
            $results = self::optimize_database();
            
            add_action('admin_notices', function() use ($results) {
                echo '<div class="notice notice-success is-dismissible">';
                echo '<p>' . __('Database optimization completed successfully.', 'lectus-class-system') . '</p>';
                echo '</div>';
            });
        }
    }
}