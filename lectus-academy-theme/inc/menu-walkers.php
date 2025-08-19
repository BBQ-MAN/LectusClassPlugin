<?php
/**
 * Custom Menu Walker Classes for Lectus Academy Theme
 *
 * @package LectusAcademy
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Top Menu Walker for header top menu
 */
class Lectus_Top_Menu_Walker extends Walker_Nav_Menu {
    
    // Start Level
    function start_lvl(&$output, $depth = 0, $args = null) {
        // No sub-menus for top menu
    }

    // End Level
    function end_lvl(&$output, $depth = 0, $args = null) {
        // No sub-menus for top menu
    }

    // Start Element
    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $classes[] = 'top-link text-sm text-gray-600 hover:text-lectus-primary transition-colors';
        
        // Get icon based on menu item title or custom field
        $icon = 'fa-graduation-cap'; // Default icon
        if (strpos(strtolower($item->title), '커리어') !== false || strpos(strtolower($item->title), 'career') !== false) {
            $icon = 'fa-briefcase';
        }
        
        $attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
        $attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
        $attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
        $attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';
        $attributes .= ' class="' . join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args)) . '"';
        
        $item_output = '<a' . $attributes . '>';
        $item_output .= '<i class="fas ' . $icon . '"></i> ';
        $item_output .= apply_filters('the_title', $item->title, $item->ID);
        $item_output .= '</a>';
        
        $output .= $item_output;
    }

    // End Element
    function end_el(&$output, $item, $depth = 0, $args = null) {
        $output .= "\n";
    }
}

/**
 * Main Menu Walker for header main navigation
 */
class Lectus_Main_Menu_Walker extends Walker_Nav_Menu {
    
    // Start Level
    function start_lvl(&$output, $depth = 0, $args = null) {
        // No sub-menus for main menu
    }

    // End Level
    function end_lvl(&$output, $depth = 0, $args = null) {
        // No sub-menus for main menu
    }

    // Start Element
    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $classes[] = 'btn btn-ghost text-gray-700 hover:text-lectus-primary transition-colors';
        
        $attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
        $attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
        $attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
        $attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';
        $attributes .= ' class="' . join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args)) . '"';
        
        $item_output = '<a' . $attributes . '>';
        $item_output .= apply_filters('the_title', $item->title, $item->ID);
        $item_output .= '</a>';
        
        $output .= $item_output;
    }

    // End Element
    function end_el(&$output, $item, $depth = 0, $args = null) {
        $output .= "\n";
    }
}

/**
 * Category Menu Walker for category navigation
 */
class Lectus_Category_Menu_Walker extends Walker_Nav_Menu {
    
    // Start Level
    function start_lvl(&$output, $depth = 0, $args = null) {
        // No sub-menus for category menu
    }

    // End Level
    function end_lvl(&$output, $depth = 0, $args = null) {
        // No sub-menus for category menu
    }

    // Start Element
    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        
        // Check if this is the "all courses" item
        $is_all = (strpos(strtolower($item->title), '전체') !== false || strpos(strtolower($item->title), 'all') !== false);
        
        if ($is_all) {
            $classes[] = 'category-link flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-50 text-lectus-primary font-medium transition-colors';
        } else {
            $classes[] = 'category-link flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-gray-100 text-gray-700 transition-colors';
        }
        
        // Determine icon based on menu item title or custom field
        $icon = $this->get_category_icon($item->title);
        
        $attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
        $attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
        $attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
        $attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';
        $attributes .= ' class="' . join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args)) . '"';
        
        $item_output = '<li class="category-item">';
        $item_output .= '<a' . $attributes . '>';
        $item_output .= '<i class="fas ' . $icon . ' category-icon text-sm"></i> ';
        $item_output .= '<span>' . apply_filters('the_title', $item->title, $item->ID) . '</span>';
        $item_output .= '</a>';
        $item_output .= '</li>';
        
        $output .= $item_output;
    }

    // End Element
    function end_el(&$output, $item, $depth = 0, $args = null) {
        $output .= "\n";
    }
    
    /**
     * Get icon for category based on title
     */
    private function get_category_icon($title) {
        $title_lower = strtolower($title);
        
        $icon_map = array(
            '전체' => 'fa-layer-group',
            'all' => 'fa-layer-group',
            'new' => 'fa-sparkles',
            '신규' => 'fa-sparkles',
            'drawing' => 'fa-pencil-ruler',
            '드로잉' => 'fa-pencil-ruler',
            'modeling' => 'fa-cube',
            '모델링' => 'fa-cube',
            'rendering' => 'fa-image',
            '렌더링' => 'fa-image',
            'retouching' => 'fa-magic',
            '리터칭' => 'fa-magic',
            'bim' => 'fa-building',
            'ai' => 'fa-robot',
            'object' => 'fa-shapes',
            '오브젝트' => 'fa-shapes',
            '프로그래밍' => 'fa-code',
            'programming' => 'fa-code',
            'development' => 'fa-code',
            '디자인' => 'fa-palette',
            'design' => 'fa-palette',
            '비즈니스' => 'fa-briefcase',
            'business' => 'fa-briefcase',
            '마케팅' => 'fa-bullhorn',
            'marketing' => 'fa-bullhorn',
            'it' => 'fa-server',
            '사진' => 'fa-camera',
            'photo' => 'fa-camera',
            '음악' => 'fa-music',
            'music' => 'fa-music',
            '언어' => 'fa-language',
            'language' => 'fa-language',
            '더보기' => 'fa-ellipsis-h',
            'more' => 'fa-ellipsis-h',
        );
        
        foreach ($icon_map as $keyword => $icon) {
            if (strpos($title_lower, $keyword) !== false) {
                return $icon;
            }
        }
        
        return 'fa-folder'; // Default icon
    }
}

/**
 * Footer Menu Walker
 */
class Lectus_Footer_Menu_Walker extends Walker_Nav_Menu {
    
    // Start Level
    function start_lvl(&$output, $depth = 0, $args = null) {
        // No sub-menus for footer menu
    }

    // End Level
    function end_lvl(&$output, $depth = 0, $args = null) {
        // No sub-menus for footer menu
    }

    // Start Element
    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $classes[] = 'text-gray-400 hover:text-white transition-colors';
        
        $attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
        $attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
        $attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
        $attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';
        $attributes .= ' class="' . join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args)) . '"';
        
        $item_output = '<li>';
        $item_output .= '<a' . $attributes . '>';
        $item_output .= apply_filters('the_title', $item->title, $item->ID);
        $item_output .= '</a>';
        $item_output .= '</li>';
        
        $output .= $item_output;
    }

    // End Element
    function end_el(&$output, $item, $depth = 0, $args = null) {
        $output .= "\n";
    }
}