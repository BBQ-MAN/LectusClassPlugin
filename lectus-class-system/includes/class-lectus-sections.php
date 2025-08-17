<?php
/**
 * Sections Management Class
 * 
 * Handles course sections for organizing lessons
 * 
 * @package Lectus_Class_System
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Sections {
    
    /**
     * Initialize the sections system
     */
    public static function init() {
        add_action('wp_ajax_lectus_add_section', array(__CLASS__, 'ajax_add_section'));
        add_action('wp_ajax_lectus_update_section', array(__CLASS__, 'ajax_update_section'));
        add_action('wp_ajax_lectus_delete_section', array(__CLASS__, 'ajax_delete_section'));
        add_action('wp_ajax_lectus_reorder_sections', array(__CLASS__, 'ajax_reorder_sections'));
        add_action('wp_ajax_lectus_get_sections', array(__CLASS__, 'ajax_get_sections'));
        add_action('wp_ajax_lectus_assign_lesson_to_section', array(__CLASS__, 'ajax_assign_lesson_to_section'));
    }
    
    /**
     * Get all sections for a course
     */
    public static function get_course_sections($course_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'lectus_sections';
        
        $sections = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name 
            WHERE course_id = %d 
            ORDER BY display_order ASC, id ASC",
            $course_id
        ));
        
        return $sections;
    }
    
    /**
     * Get a single section
     */
    public static function get_section($section_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'lectus_sections';
        
        $section = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $section_id
        ));
        
        return $section;
    }
    
    /**
     * Add a new section
     */
    public static function add_section($course_id, $title, $description = '', $display_order = 0) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'lectus_sections';
        
        // If no display order specified, put it at the end
        if ($display_order == 0) {
            $max_order = $wpdb->get_var($wpdb->prepare(
                "SELECT MAX(display_order) FROM $table_name WHERE course_id = %d",
                $course_id
            ));
            $display_order = ($max_order ? $max_order : 0) + 10;
        }
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'course_id' => $course_id,
                'title' => $title,
                'description' => $description,
                'display_order' => $display_order,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%d', '%s', '%s')
        );
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Update a section
     */
    public static function update_section($section_id, $data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'lectus_sections';
        
        $update_data = array();
        $format = array();
        
        if (isset($data['title'])) {
            $update_data['title'] = $data['title'];
            $format[] = '%s';
        }
        
        if (isset($data['description'])) {
            $update_data['description'] = $data['description'];
            $format[] = '%s';
        }
        
        if (isset($data['display_order'])) {
            $update_data['display_order'] = $data['display_order'];
            $format[] = '%d';
        }
        
        $update_data['updated_at'] = current_time('mysql');
        $format[] = '%s';
        
        $result = $wpdb->update(
            $table_name,
            $update_data,
            array('id' => $section_id),
            $format,
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Delete a section
     */
    public static function delete_section($section_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'lectus_sections';
        
        // First, unassign all lessons from this section
        $lessons = self::get_section_lessons($section_id);
        foreach ($lessons as $lesson) {
            delete_post_meta($lesson->ID, '_lesson_section_id');
        }
        
        // Delete the section
        $result = $wpdb->delete(
            $table_name,
            array('id' => $section_id),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Get lessons in a section
     */
    public static function get_section_lessons($section_id) {
        $args = array(
            'post_type' => 'lesson',
            'posts_per_page' => -1,
            'meta_key' => '_lesson_section_id',
            'meta_value' => $section_id,
            'orderby' => 'menu_order',
            'order' => 'ASC'
        );
        
        $lessons = get_posts($args);
        
        return $lessons;
    }
    
    /**
     * Get lessons without a section for a course
     */
    public static function get_unsectioned_lessons($course_id) {
        $args = array(
            'post_type' => 'lesson',
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_course_id',
                    'value' => $course_id,
                    'compare' => '='
                ),
                array(
                    'relation' => 'OR',
                    array(
                        'key' => '_lesson_section_id',
                        'compare' => 'NOT EXISTS'
                    ),
                    array(
                        'key' => '_lesson_section_id',
                        'value' => '',
                        'compare' => '='
                    ),
                    array(
                        'key' => '_lesson_section_id',
                        'value' => '0',
                        'compare' => '='
                    )
                )
            ),
            'orderby' => 'menu_order',
            'order' => 'ASC'
        );
        
        $lessons = get_posts($args);
        
        return $lessons;
    }
    
    /**
     * Assign a lesson to a section
     */
    public static function assign_lesson_to_section($lesson_id, $section_id) {
        if ($section_id == 0) {
            // Remove from section
            delete_post_meta($lesson_id, '_lesson_section_id');
        } else {
            // Assign to section
            update_post_meta($lesson_id, '_lesson_section_id', $section_id);
        }
        
        return true;
    }
    
    /**
     * Reorder sections
     */
    public static function reorder_sections($section_orders) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'lectus_sections';
        
        foreach ($section_orders as $section_id => $order) {
            $wpdb->update(
                $table_name,
                array(
                    'display_order' => $order,
                    'updated_at' => current_time('mysql')
                ),
                array('id' => $section_id),
                array('%d', '%s'),
                array('%d')
            );
        }
        
        return true;
    }
    
    /**
     * AJAX handler for adding a section
     */
    public static function ajax_add_section() {
        check_ajax_referer('lectus-ajax-nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die(__('권한이 없습니다.', 'lectus-class-system'));
        }
        
        $course_id = intval($_POST['course_id']);
        $title = sanitize_text_field($_POST['title']);
        $description = sanitize_textarea_field($_POST['description'] ?? '');
        
        if (!$course_id || !$title) {
            wp_send_json_error(array('message' => '필수 정보가 누락되었습니다.'));
        }
        
        $section_id = self::add_section($course_id, $title, $description);
        
        if ($section_id) {
            $section = self::get_section($section_id);
            wp_send_json_success(array(
                'section' => $section,
                'message' => '섹션이 추가되었습니다.'
            ));
        } else {
            wp_send_json_error(array('message' => '섹션 추가에 실패했습니다.'));
        }
    }
    
    /**
     * AJAX handler for updating a section
     */
    public static function ajax_update_section() {
        check_ajax_referer('lectus-ajax-nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die(__('권한이 없습니다.', 'lectus-class-system'));
        }
        
        $section_id = intval($_POST['section_id']);
        $title = sanitize_text_field($_POST['title']);
        $description = sanitize_textarea_field($_POST['description'] ?? '');
        
        if (!$section_id || !$title) {
            wp_send_json_error(array('message' => '필수 정보가 누락되었습니다.'));
        }
        
        $result = self::update_section($section_id, array(
            'title' => $title,
            'description' => $description
        ));
        
        if ($result) {
            $section = self::get_section($section_id);
            wp_send_json_success(array(
                'section' => $section,
                'message' => '섹션이 업데이트되었습니다.'
            ));
        } else {
            wp_send_json_error(array('message' => '섹션 업데이트에 실패했습니다.'));
        }
    }
    
    /**
     * AJAX handler for deleting a section
     */
    public static function ajax_delete_section() {
        check_ajax_referer('lectus-ajax-nonce', 'nonce');
        
        if (!current_user_can('delete_posts')) {
            wp_die(__('권한이 없습니다.', 'lectus-class-system'));
        }
        
        $section_id = intval($_POST['section_id']);
        
        if (!$section_id) {
            wp_send_json_error(array('message' => '섹션 ID가 필요합니다.'));
        }
        
        $result = self::delete_section($section_id);
        
        if ($result) {
            wp_send_json_success(array('message' => '섹션이 삭제되었습니다.'));
        } else {
            wp_send_json_error(array('message' => '섹션 삭제에 실패했습니다.'));
        }
    }
    
    /**
     * AJAX handler for reordering sections
     */
    public static function ajax_reorder_sections() {
        check_ajax_referer('lectus-ajax-nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die(__('권한이 없습니다.', 'lectus-class-system'));
        }
        
        $section_orders = $_POST['section_orders'];
        
        if (!is_array($section_orders)) {
            wp_send_json_error(array('message' => '잘못된 데이터 형식입니다.'));
        }
        
        $result = self::reorder_sections($section_orders);
        
        if ($result) {
            wp_send_json_success(array('message' => '섹션 순서가 업데이트되었습니다.'));
        } else {
            wp_send_json_error(array('message' => '섹션 순서 업데이트에 실패했습니다.'));
        }
    }
    
    /**
     * AJAX handler for getting sections
     */
    public static function ajax_get_sections() {
        check_ajax_referer('lectus-ajax-nonce', 'nonce');
        
        $course_id = intval($_POST['course_id']);
        
        if (!$course_id) {
            wp_send_json_error(array('message' => '코스 ID가 필요합니다.'));
        }
        
        $sections = self::get_course_sections($course_id);
        $sections_with_lessons = array();
        
        foreach ($sections as $section) {
            $section->lessons = self::get_section_lessons($section->id);
            $sections_with_lessons[] = $section;
        }
        
        // Also get unsectioned lessons
        $unsectioned_lessons = self::get_unsectioned_lessons($course_id);
        
        wp_send_json_success(array(
            'sections' => $sections_with_lessons,
            'unsectioned_lessons' => $unsectioned_lessons
        ));
    }
    
    /**
     * AJAX handler for assigning a lesson to a section
     */
    public static function ajax_assign_lesson_to_section() {
        check_ajax_referer('lectus-ajax-nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die(__('권한이 없습니다.', 'lectus-class-system'));
        }
        
        $lesson_id = intval($_POST['lesson_id']);
        $section_id = intval($_POST['section_id']);
        
        if (!$lesson_id) {
            wp_send_json_error(array('message' => '레슨 ID가 필요합니다.'));
        }
        
        $result = self::assign_lesson_to_section($lesson_id, $section_id);
        
        if ($result) {
            wp_send_json_success(array('message' => '레슨이 섹션에 할당되었습니다.'));
        } else {
            wp_send_json_error(array('message' => '레슨 할당에 실패했습니다.'));
        }
    }
}