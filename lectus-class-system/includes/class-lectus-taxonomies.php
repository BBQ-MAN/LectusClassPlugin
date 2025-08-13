<?php
/**
 * Taxonomies for Lectus Class System
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Taxonomies {
    
    public static function init() {
        // Register taxonomies immediately since we're already in the init hook
        self::register_taxonomies();
    }
    
    public static function register_taxonomies() {
        // Category for Courses
        register_taxonomy('course_category', array('coursesingle', 'coursepackage'), array(
            'labels' => array(
                'name' => __('강의 카테고리', 'lectus-class-system'),
                'singular_name' => __('강의 카테고리', 'lectus-class-system'),
                'search_items' => __('카테고리 검색', 'lectus-class-system'),
                'all_items' => __('모든 카테고리', 'lectus-class-system'),
                'parent_item' => __('상위 카테고리', 'lectus-class-system'),
                'parent_item_colon' => __('상위 카테고리:', 'lectus-class-system'),
                'edit_item' => __('카테고리 편집', 'lectus-class-system'),
                'update_item' => __('카테고리 업데이트', 'lectus-class-system'),
                'add_new_item' => __('새 카테고리 추가', 'lectus-class-system'),
                'new_item_name' => __('새 카테고리 이름', 'lectus-class-system'),
                'menu_name' => __('카테고리', 'lectus-class-system'),
            ),
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'course-category'),
            'show_in_rest' => true,
        ));
        
        // Tags for Courses
        register_taxonomy('course_tag', array('coursesingle', 'coursepackage'), array(
            'labels' => array(
                'name' => __('강의 태그', 'lectus-class-system'),
                'singular_name' => __('강의 태그', 'lectus-class-system'),
                'search_items' => __('태그 검색', 'lectus-class-system'),
                'popular_items' => __('인기 태그', 'lectus-class-system'),
                'all_items' => __('모든 태그', 'lectus-class-system'),
                'edit_item' => __('태그 편집', 'lectus-class-system'),
                'update_item' => __('태그 업데이트', 'lectus-class-system'),
                'add_new_item' => __('새 태그 추가', 'lectus-class-system'),
                'new_item_name' => __('새 태그 이름', 'lectus-class-system'),
                'separate_items_with_commas' => __('태그를 쉼표로 구분', 'lectus-class-system'),
                'add_or_remove_items' => __('태그 추가 또는 제거', 'lectus-class-system'),
                'choose_from_most_used' => __('가장 많이 사용된 태그 선택', 'lectus-class-system'),
                'menu_name' => __('태그', 'lectus-class-system'),
            ),
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'course-tag'),
            'show_in_rest' => true,
        ));
        
        // Difficulty Level
        register_taxonomy('course_level', array('coursesingle'), array(
            'labels' => array(
                'name' => __('난이도', 'lectus-class-system'),
                'singular_name' => __('난이도', 'lectus-class-system'),
                'search_items' => __('난이도 검색', 'lectus-class-system'),
                'all_items' => __('모든 난이도', 'lectus-class-system'),
                'edit_item' => __('난이도 편집', 'lectus-class-system'),
                'update_item' => __('난이도 업데이트', 'lectus-class-system'),
                'add_new_item' => __('새 난이도 추가', 'lectus-class-system'),
                'new_item_name' => __('새 난이도 이름', 'lectus-class-system'),
                'menu_name' => __('난이도', 'lectus-class-system'),
            ),
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'course-level'),
            'show_in_rest' => true,
        ));
    }
}