<?php
/**
 * Course Items Management Class
 * 
 * Handles both sections and lessons in a unified way
 * 
 * @package Lectus_Class_System
 * @since 1.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Course_Items {
    
    /**
     * Initialize the course items system
     */
    public static function init() {
        add_action('wp_ajax_lectus_get_course_items', array(__CLASS__, 'ajax_get_course_items'));
        add_action('wp_ajax_lectus_add_section_item', array(__CLASS__, 'ajax_add_section_item'));
        add_action('wp_ajax_lectus_remove_section_item', array(__CLASS__, 'ajax_remove_section_item'));
        add_action('wp_ajax_lectus_update_section_item', array(__CLASS__, 'ajax_update_section_item'));
        add_action('wp_ajax_lectus_reorder_course_items', array(__CLASS__, 'ajax_reorder_course_items'));
        add_action('wp_ajax_lectus_toggle_section', array(__CLASS__, 'ajax_toggle_section'));
    }
    
    /**
     * Get all items (sections and lessons) for a course
     */
    public static function get_course_items($course_id) {
        global $wpdb;
        
        $items = array();
        
        // Get all lessons for this course
        $lessons = get_posts(array(
            'post_type' => 'lesson',
            'meta_key' => '_course_id',
            'meta_value' => $course_id,
            'posts_per_page' => -1,
            'orderby' => 'menu_order',
            'order' => 'ASC'
        ));
        
        // Get all sections for this course
        $sections_table = $wpdb->prefix . 'lectus_sections';
        $sections = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $sections_table 
            WHERE course_id = %d 
            ORDER BY display_order ASC",
            $course_id
        ));
        
        // Combine and sort by display order
        foreach ($lessons as $lesson) {
            $display_order = get_post_meta($lesson->ID, '_display_order', true);
            if (!$display_order) {
                $display_order = $lesson->menu_order * 10; // Convert menu_order to display_order
                update_post_meta($lesson->ID, '_display_order', $display_order);
            }
            
            $items[] = array(
                'type' => 'lesson',
                'id' => $lesson->ID,
                'title' => $lesson->post_title,
                'display_order' => intval($display_order),
                'lesson_type' => get_post_meta($lesson->ID, '_lesson_type', true),
                'duration' => get_post_meta($lesson->ID, '_lesson_duration', true),
                'edit_url' => get_edit_post_link($lesson->ID)
            );
        }
        
        foreach ($sections as $section) {
            $items[] = array(
                'type' => 'section',
                'id' => $section->id,
                'title' => $section->title,
                'description' => $section->description,
                'display_order' => intval($section->display_order),
                'is_collapsed' => get_option('lectus_section_collapsed_' . $section->id, false)
            );
        }
        
        // Sort all items by display_order
        usort($items, function($a, $b) {
            return $a['display_order'] - $b['display_order'];
        });
        
        return $items;
    }
    
    /**
     * Get items organized by sections
     */
    public static function get_items_by_sections($course_id) {
        $items = self::get_course_items($course_id);
        $organized = array();
        $current_section = null;
        
        foreach ($items as $item) {
            if ($item['type'] === 'section') {
                $current_section = $item;
                $current_section['lessons'] = array();
                $organized[] = &$current_section;
            } elseif ($item['type'] === 'lesson') {
                if ($current_section !== null && isset($organized[count($organized) - 1])) {
                    $organized[count($organized) - 1]['lessons'][] = $item;
                } else {
                    // Lesson without section
                    if (empty($organized) || $organized[0]['type'] !== 'unsectioned') {
                        array_unshift($organized, array(
                            'type' => 'unsectioned',
                            'id' => 0,
                            'title' => '미분류 레슨',
                            'lessons' => array()
                        ));
                    }
                    $organized[0]['lessons'][] = $item;
                }
            }
        }
        
        return $organized;
    }
    
    /**
     * Add a new section marker
     */
    public static function add_section($course_id, $title, $description = '', $display_order = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'lectus_sections';
        
        // If no display order specified, put it at the end
        if ($display_order === null) {
            $max_order = $wpdb->get_var($wpdb->prepare(
                "SELECT MAX(display_order) FROM $table_name WHERE course_id = %d",
                $course_id
            ));
            
            // Also check lessons
            $max_lesson_order = $wpdb->get_var($wpdb->prepare(
                "SELECT MAX(CAST(meta_value AS UNSIGNED)) FROM {$wpdb->postmeta} pm
                JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                WHERE pm.meta_key = '_display_order' 
                AND p.post_type = 'lesson'
                AND p.ID IN (
                    SELECT post_id FROM {$wpdb->postmeta} 
                    WHERE meta_key = '_course_id' AND meta_value = %s
                )",
                $course_id
            ));
            
            $display_order = max($max_order, $max_lesson_order) + 10;
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
     * Update display order for all items
     */
    public static function update_display_orders($items) {
        global $wpdb;
        
        foreach ($items as $order => $item) {
            $display_order = ($order + 1) * 10;
            
            if ($item['type'] === 'lesson') {
                update_post_meta($item['id'], '_display_order', $display_order);
                wp_update_post(array(
                    'ID' => $item['id'],
                    'menu_order' => $order + 1
                ));
            } elseif ($item['type'] === 'section') {
                $wpdb->update(
                    $wpdb->prefix . 'lectus_sections',
                    array(
                        'display_order' => $display_order,
                        'updated_at' => current_time('mysql')
                    ),
                    array('id' => $item['id']),
                    array('%d', '%s'),
                    array('%d')
                );
            }
        }
        
        return true;
    }
    
    /**
     * AJAX handler to get course items
     */
    public static function ajax_get_course_items() {
        check_ajax_referer('lectus-ajax-nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die(__('권한이 없습니다.', 'lectus-class-system'));
        }
        
        $course_id = intval($_POST['course_id']);
        
        if (!$course_id) {
            wp_send_json_error(array('message' => '코스 ID가 필요합니다.'));
        }
        
        $items = self::get_course_items($course_id);
        $organized = self::get_items_by_sections($course_id);
        
        wp_send_json_success(array(
            'items' => $items,
            'organized' => $organized
        ));
    }
    
    /**
     * AJAX handler to add a section
     */
    public static function ajax_add_section_item() {
        check_ajax_referer('lectus-ajax-nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die(__('권한이 없습니다.', 'lectus-class-system'));
        }
        
        $course_id = intval($_POST['course_id']);
        $title = sanitize_text_field($_POST['title']);
        $description = sanitize_textarea_field($_POST['description'] ?? '');
        $after_item = isset($_POST['after_item']) ? $_POST['after_item'] : null;
        
        if (!$course_id || !$title) {
            wp_send_json_error(array('message' => '필수 정보가 누락되었습니다.'));
        }
        
        // Calculate display order based on after_item
        $display_order = null;
        if ($after_item) {
            $after_parts = explode('-', $after_item);
            $after_type = $after_parts[0];
            $after_id = intval($after_parts[1]);
            
            if ($after_type === 'lesson') {
                $after_order = get_post_meta($after_id, '_display_order', true);
            } else {
                global $wpdb;
                $after_order = $wpdb->get_var($wpdb->prepare(
                    "SELECT display_order FROM {$wpdb->prefix}lectus_sections WHERE id = %d",
                    $after_id
                ));
            }
            
            $display_order = intval($after_order) + 5;
        }
        
        $section_id = self::add_section($course_id, $title, $description, $display_order);
        
        if ($section_id) {
            wp_send_json_success(array(
                'section_id' => $section_id,
                'message' => '섹션이 추가되었습니다.'
            ));
        } else {
            wp_send_json_error(array('message' => '섹션 추가에 실패했습니다.'));
        }
    }
    
    /**
     * AJAX handler to remove a section
     */
    public static function ajax_remove_section_item() {
        check_ajax_referer('lectus-ajax-nonce', 'nonce');
        
        if (!current_user_can('delete_posts')) {
            wp_die(__('권한이 없습니다.', 'lectus-class-system'));
        }
        
        $section_id = intval($_POST['section_id']);
        
        if (!$section_id) {
            wp_send_json_error(array('message' => '섹션 ID가 필요합니다.'));
        }
        
        global $wpdb;
        $result = $wpdb->delete(
            $wpdb->prefix . 'lectus_sections',
            array('id' => $section_id),
            array('%d')
        );
        
        if ($result) {
            wp_send_json_success(array('message' => '섹션이 삭제되었습니다.'));
        } else {
            wp_send_json_error(array('message' => '섹션 삭제에 실패했습니다.'));
        }
    }
    
    /**
     * AJAX handler to update a section
     */
    public static function ajax_update_section_item() {
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
        
        global $wpdb;
        $result = $wpdb->update(
            $wpdb->prefix . 'lectus_sections',
            array(
                'title' => $title,
                'description' => $description,
                'updated_at' => current_time('mysql')
            ),
            array('id' => $section_id),
            array('%s', '%s', '%s'),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success(array('message' => '섹션이 업데이트되었습니다.'));
        } else {
            wp_send_json_error(array('message' => '섹션 업데이트에 실패했습니다.'));
        }
    }
    
    /**
     * AJAX handler to reorder items
     */
    public static function ajax_reorder_course_items() {
        check_ajax_referer('lectus-ajax-nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die(__('권한이 없습니다.', 'lectus-class-system'));
        }
        
        $items = $_POST['items'];
        
        if (!is_array($items)) {
            wp_send_json_error(array('message' => '잘못된 데이터 형식입니다.'));
        }
        
        $result = self::update_display_orders($items);
        
        if ($result) {
            wp_send_json_success(array('message' => '순서가 업데이트되었습니다.'));
        } else {
            wp_send_json_error(array('message' => '순서 업데이트에 실패했습니다.'));
        }
    }
    
    /**
     * AJAX handler to toggle section collapse state
     */
    public static function ajax_toggle_section() {
        check_ajax_referer('lectus-ajax-nonce', 'nonce');
        
        $section_id = intval($_POST['section_id']);
        $collapsed = $_POST['collapsed'] === 'true';
        
        update_option('lectus_section_collapsed_' . $section_id, $collapsed);
        
        wp_send_json_success();
    }
}