<?php
/**
 * Constants and Configuration for Lectus Class System
 * 
 * Centralizes all magic numbers and configuration values
 * 
 * @package Lectus_Class_System
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class to define all plugin constants
 */
class Lectus_Constants {
    
    /**
     * Initialize constants
     */
    public static function init() {
        self::define_core_constants();
        self::define_enrollment_constants();
        self::define_progress_constants();
        self::define_qa_constants();
        self::define_material_constants();
        self::define_certificate_constants();
        self::define_cache_constants();
        self::define_pagination_constants();
        self::define_validation_constants();
    }
    
    /**
     * Define core plugin constants
     */
    private static function define_core_constants() {
        // Plugin version (defined in main file)
        if (!defined('LECTUS_VERSION')) {
            define('LECTUS_VERSION', '1.2.0');
        }
        
        // Debug mode
        if (!defined('LECTUS_DEBUG')) {
            define('LECTUS_DEBUG', WP_DEBUG);
        }
        
        // Plugin environment
        if (!defined('LECTUS_ENV')) {
            define('LECTUS_ENV', wp_get_environment_type());
        }
    }
    
    /**
     * Define enrollment related constants
     */
    private static function define_enrollment_constants() {
        // Default enrollment duration in days
        if (!defined('LECTUS_DEFAULT_ACCESS_DAYS')) {
            define('LECTUS_DEFAULT_ACCESS_DAYS', 365);
        }
        
        // Maximum enrollment duration in days
        if (!defined('LECTUS_MAX_ACCESS_DAYS')) {
            define('LECTUS_MAX_ACCESS_DAYS', 1095); // 3 years
        }
        
        // Minimum enrollment duration in days
        if (!defined('LECTUS_MIN_ACCESS_DAYS')) {
            define('LECTUS_MIN_ACCESS_DAYS', 1);
        }
        
        // Default enrollment status
        if (!defined('LECTUS_DEFAULT_ENROLLMENT_STATUS')) {
            define('LECTUS_DEFAULT_ENROLLMENT_STATUS', 'active');
        }
        
        // Maximum enrollments per user
        if (!defined('LECTUS_MAX_ENROLLMENTS_PER_USER')) {
            define('LECTUS_MAX_ENROLLMENTS_PER_USER', 100);
        }
    }
    
    /**
     * Define progress related constants
     */
    private static function define_progress_constants() {
        // Course completion threshold percentage
        if (!defined('LECTUS_COMPLETION_THRESHOLD')) {
            define('LECTUS_COMPLETION_THRESHOLD', 80);
        }
        
        // Lesson completion threshold percentage
        if (!defined('LECTUS_LESSON_COMPLETION_THRESHOLD')) {
            define('LECTUS_LESSON_COMPLETION_THRESHOLD', 90);
        }
        
        // Progress update interval in seconds
        if (!defined('LECTUS_PROGRESS_UPDATE_INTERVAL')) {
            define('LECTUS_PROGRESS_UPDATE_INTERVAL', 30);
        }
        
        // Video watch percentage for completion
        if (!defined('LECTUS_VIDEO_COMPLETION_PERCENTAGE')) {
            define('LECTUS_VIDEO_COMPLETION_PERCENTAGE', 90);
        }
        
        // Quiz pass percentage
        if (!defined('LECTUS_QUIZ_PASS_PERCENTAGE')) {
            define('LECTUS_QUIZ_PASS_PERCENTAGE', 70);
        }
    }
    
    /**
     * Define Q&A related constants
     */
    private static function define_qa_constants() {
        // Rate limiting for Q&A
        if (!defined('LECTUS_QA_HOURLY_LIMIT')) {
            define('LECTUS_QA_HOURLY_LIMIT', 10);
        }
        
        // Maximum question length
        if (!defined('LECTUS_QA_MAX_QUESTION_LENGTH')) {
            define('LECTUS_QA_MAX_QUESTION_LENGTH', 1000);
        }
        
        // Maximum answer length
        if (!defined('LECTUS_QA_MAX_ANSWER_LENGTH')) {
            define('LECTUS_QA_MAX_ANSWER_LENGTH', 5000);
        }
        
        // Minimum question length
        if (!defined('LECTUS_QA_MIN_QUESTION_LENGTH')) {
            define('LECTUS_QA_MIN_QUESTION_LENGTH', 10);
        }
        
        // Vote limit per user per day
        if (!defined('LECTUS_QA_DAILY_VOTE_LIMIT')) {
            define('LECTUS_QA_DAILY_VOTE_LIMIT', 50);
        }
        
        // Points for accepted answer
        if (!defined('LECTUS_QA_ACCEPTED_ANSWER_POINTS')) {
            define('LECTUS_QA_ACCEPTED_ANSWER_POINTS', 15);
        }
    }
    
    /**
     * Define material related constants
     */
    private static function define_material_constants() {
        // Maximum file upload size in bytes (50MB)
        if (!defined('LECTUS_MAX_UPLOAD_SIZE')) {
            define('LECTUS_MAX_UPLOAD_SIZE', 52428800);
        }
        
        // Maximum files per lesson
        if (!defined('LECTUS_MAX_FILES_PER_LESSON')) {
            define('LECTUS_MAX_FILES_PER_LESSON', 20);
        }
        
        // Material download limit per day
        if (!defined('LECTUS_DAILY_DOWNLOAD_LIMIT')) {
            define('LECTUS_DAILY_DOWNLOAD_LIMIT', 100);
        }
        
        // External link maximum length
        if (!defined('LECTUS_MAX_EXTERNAL_URL_LENGTH')) {
            define('LECTUS_MAX_EXTERNAL_URL_LENGTH', 1000);
        }
        
        // Material cache duration in seconds
        if (!defined('LECTUS_MATERIAL_CACHE_DURATION')) {
            define('LECTUS_MATERIAL_CACHE_DURATION', 3600);
        }
    }
    
    /**
     * Define certificate related constants
     */
    private static function define_certificate_constants() {
        // Certificate number prefix
        if (!defined('LECTUS_CERTIFICATE_PREFIX')) {
            define('LECTUS_CERTIFICATE_PREFIX', 'LCS');
        }
        
        // Certificate validity period in days (0 = forever)
        if (!defined('LECTUS_CERTIFICATE_VALIDITY_DAYS')) {
            define('LECTUS_CERTIFICATE_VALIDITY_DAYS', 0);
        }
        
        // Minimum score for certificate
        if (!defined('LECTUS_CERTIFICATE_MIN_SCORE')) {
            define('LECTUS_CERTIFICATE_MIN_SCORE', 80);
        }
        
        // Certificate generation timeout in seconds
        if (!defined('LECTUS_CERTIFICATE_GENERATION_TIMEOUT')) {
            define('LECTUS_CERTIFICATE_GENERATION_TIMEOUT', 30);
        }
    }
    
    /**
     * Define cache related constants
     */
    private static function define_cache_constants() {
        // General cache duration in seconds
        if (!defined('LECTUS_CACHE_DURATION')) {
            define('LECTUS_CACHE_DURATION', 3600); // 1 hour
        }
        
        // Course list cache duration
        if (!defined('LECTUS_COURSE_CACHE_DURATION')) {
            define('LECTUS_COURSE_CACHE_DURATION', 7200); // 2 hours
        }
        
        // Student progress cache duration
        if (!defined('LECTUS_PROGRESS_CACHE_DURATION')) {
            define('LECTUS_PROGRESS_CACHE_DURATION', 300); // 5 minutes
        }
        
        // Statistics cache duration
        if (!defined('LECTUS_STATS_CACHE_DURATION')) {
            define('LECTUS_STATS_CACHE_DURATION', 1800); // 30 minutes
        }
        
        // Enable object caching
        if (!defined('LECTUS_ENABLE_OBJECT_CACHE')) {
            define('LECTUS_ENABLE_OBJECT_CACHE', true);
        }
    }
    
    /**
     * Define pagination constants
     */
    private static function define_pagination_constants() {
        // Default items per page
        if (!defined('LECTUS_ITEMS_PER_PAGE')) {
            define('LECTUS_ITEMS_PER_PAGE', 20);
        }
        
        // Students per page in admin
        if (!defined('LECTUS_STUDENTS_PER_PAGE')) {
            define('LECTUS_STUDENTS_PER_PAGE', 50);
        }
        
        // Questions per page
        if (!defined('LECTUS_QUESTIONS_PER_PAGE')) {
            define('LECTUS_QUESTIONS_PER_PAGE', 10);
        }
        
        // Lessons per page
        if (!defined('LECTUS_LESSONS_PER_PAGE')) {
            define('LECTUS_LESSONS_PER_PAGE', 15);
        }
        
        // Maximum pagination links
        if (!defined('LECTUS_MAX_PAGINATION_LINKS')) {
            define('LECTUS_MAX_PAGINATION_LINKS', 5);
        }
    }
    
    /**
     * Define validation constants
     */
    private static function define_validation_constants() {
        // Minimum password length
        if (!defined('LECTUS_MIN_PASSWORD_LENGTH')) {
            define('LECTUS_MIN_PASSWORD_LENGTH', 8);
        }
        
        // Maximum username length
        if (!defined('LECTUS_MAX_USERNAME_LENGTH')) {
            define('LECTUS_MAX_USERNAME_LENGTH', 50);
        }
        
        // Course title maximum length
        if (!defined('LECTUS_MAX_COURSE_TITLE_LENGTH')) {
            define('LECTUS_MAX_COURSE_TITLE_LENGTH', 200);
        }
        
        // Course description maximum length
        if (!defined('LECTUS_MAX_COURSE_DESCRIPTION_LENGTH')) {
            define('LECTUS_MAX_COURSE_DESCRIPTION_LENGTH', 5000);
        }
        
        // Session timeout in seconds
        if (!defined('LECTUS_SESSION_TIMEOUT')) {
            define('LECTUS_SESSION_TIMEOUT', 3600); // 1 hour
        }
    }
    
    /**
     * Get all constants as array
     * 
     * @return array
     */
    public static function get_all_constants() {
        $constants = array();
        $all_constants = get_defined_constants(true);
        
        if (isset($all_constants['user'])) {
            foreach ($all_constants['user'] as $name => $value) {
                if (strpos($name, 'LECTUS_') === 0) {
                    $constants[$name] = $value;
                }
            }
        }
        
        return $constants;
    }
    
    /**
     * Get constant value with fallback
     * 
     * @param string $constant_name Constant name
     * @param mixed $default Default value if constant not defined
     * @return mixed
     */
    public static function get($constant_name, $default = null) {
        return defined($constant_name) ? constant($constant_name) : $default;
    }
    
    /**
     * Check if constant is defined
     * 
     * @param string $constant_name Constant name
     * @return bool
     */
    public static function has($constant_name) {
        return defined($constant_name);
    }
}

// Initialize constants
add_action('plugins_loaded', array('Lectus_Constants', 'init'), 1);