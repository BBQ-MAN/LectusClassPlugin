<?php
/**
 * Database Upgrade Handler
 * 
 * Handles database schema updates and migrations
 * 
 * @package Lectus_Class_System
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_DB_Upgrade {
    
    /**
     * Current database version
     */
    const DB_VERSION = '1.2.0';
    
    /**
     * Option name for database version
     */
    const VERSION_OPTION = 'lectus_db_version';
    
    /**
     * Check and perform database upgrades if needed
     */
    public static function check_db_upgrade() {
        $current_version = get_option(self::VERSION_OPTION, '1.0.0');
        
        if (version_compare($current_version, self::DB_VERSION, '<')) {
            self::upgrade_database($current_version);
        }
    }
    
    /**
     * Perform database upgrades
     */
    private static function upgrade_database($from_version) {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Upgrade to 1.2.0 - Add sections table
        if (version_compare($from_version, '1.2.0', '<')) {
            self::upgrade_to_1_2_0();
        }
        
        // Update database version
        update_option(self::VERSION_OPTION, self::DB_VERSION);
    }
    
    /**
     * Upgrade to version 1.2.0
     * Adds sections table for organizing lessons
     */
    private static function upgrade_to_1_2_0() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Create sections table
        $table_sections = $wpdb->prefix . 'lectus_sections';
        $sql_sections = "CREATE TABLE IF NOT EXISTS $table_sections (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            course_id bigint(20) NOT NULL,
            title varchar(255) NOT NULL,
            description text,
            display_order int(11) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY course_id (course_id),
            KEY display_order (display_order)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_sections);
        
        // Log the upgrade
        Lectus_Logger::info('Database upgraded to version 1.2.0', 'db_upgrade', array(
            'from_version' => get_option(self::VERSION_OPTION, '1.0.0'),
            'to_version' => '1.2.0',
            'tables_created' => array('lectus_sections')
        ));
    }
    
    /**
     * Create all tables from scratch
     * Used during plugin activation
     */
    public static function create_all_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Student progress table
        $table_progress = $wpdb->prefix . 'lectus_progress';
        $sql_progress = "CREATE TABLE IF NOT EXISTS $table_progress (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            course_id bigint(20) NOT NULL,
            lesson_id bigint(20) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'not_started',
            progress int(3) NOT NULL DEFAULT 0,
            started_at datetime DEFAULT NULL,
            completed_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY course_id (course_id),
            KEY lesson_id (lesson_id)
        ) $charset_collate;";
        
        // Student enrollment table
        $table_enrollment = $wpdb->prefix . 'lectus_enrollment';
        $sql_enrollment = "CREATE TABLE IF NOT EXISTS $table_enrollment (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            course_id bigint(20) NOT NULL,
            order_id bigint(20) DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            enrolled_at datetime NOT NULL,
            expires_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY course_id (course_id),
            KEY order_id (order_id)
        ) $charset_collate;";
        
        // Certificates table
        $table_certificates = $wpdb->prefix . 'lectus_certificates';
        $sql_certificates = "CREATE TABLE IF NOT EXISTS $table_certificates (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            course_id bigint(20) NOT NULL,
            certificate_number varchar(50) NOT NULL,
            issued_at datetime NOT NULL,
            pdf_url varchar(255) DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY certificate_number (certificate_number),
            KEY user_id (user_id),
            KEY course_id (course_id)
        ) $charset_collate;";
        
        // Sections table (new in 1.2.0)
        $table_sections = $wpdb->prefix . 'lectus_sections';
        $sql_sections = "CREATE TABLE IF NOT EXISTS $table_sections (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            course_id bigint(20) NOT NULL,
            title varchar(255) NOT NULL,
            description text,
            display_order int(11) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY course_id (course_id),
            KEY display_order (display_order)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_progress);
        dbDelta($sql_enrollment);
        dbDelta($sql_certificates);
        dbDelta($sql_sections);
        
        // Create Q&A tables
        Lectus_QA::create_table();
        
        // Create Materials tables
        Lectus_Materials::create_table();
        Lectus_QA::create_votes_table();
        
        // Update database version
        update_option(self::VERSION_OPTION, self::DB_VERSION);
    }
}