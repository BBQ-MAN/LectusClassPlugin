<?php
/**
 * Template Handler for Lectus Class System
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Templates {
    
    public static function init() {
        add_filter('template_include', array(__CLASS__, 'template_loader'));
        add_filter('the_content', array(__CLASS__, 'course_content'));
        add_filter('the_content', array(__CLASS__, 'lesson_content'));
    }
    
    public static function template_loader($template) {
        // Check for student dashboard page
        if (is_page()) {
            $page_slug = get_post_field('post_name', get_the_ID());
            
            // Check for various possible student dashboard slugs
            if (in_array($page_slug, array('my-classroom', 'student-dashboard', 'my-courses', '내강의실'))) {
                $custom_template = self::locate_template('page-student-dashboard.php');
                if ($custom_template) {
                    return $custom_template;
                }
            }
        }
        
        if (is_singular('coursepackage')) {
            $custom_template = self::locate_template('single-coursepackage.php');
            if ($custom_template) {
                return $custom_template;
            }
        }
        
        if (is_singular('coursesingle')) {
            $custom_template = self::locate_template('single-coursesingle.php');
            if ($custom_template) {
                return $custom_template;
            }
        }
        
        if (is_singular('lesson')) {
            $custom_template = self::locate_template('single-lesson.php');
            if ($custom_template) {
                return $custom_template;
            }
        }
        
        if (is_post_type_archive('coursepackage')) {
            $custom_template = self::locate_template('archive-coursepackage.php');
            if ($custom_template) {
                return $custom_template;
            }
        }
        
        if (is_post_type_archive('coursesingle')) {
            $custom_template = self::locate_template('archive-coursesingle.php');
            if ($custom_template) {
                return $custom_template;
            }
        }
        
        return $template;
    }
    
    public static function locate_template($template_name) {
        // Check in theme directory first
        $theme_template = get_stylesheet_directory() . '/lectus-class-system/' . $template_name;
        if (file_exists($theme_template)) {
            return $theme_template;
        }
        
        // Check in plugin directory
        $plugin_template = LECTUS_PLUGIN_DIR . 'templates/' . $template_name;
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
        
        return false;
    }
    
    public static function course_content($content) {
        if (!is_singular('coursesingle')) {
            return $content;
        }
        
        // Check if we're using a full page template from plugin
        if (file_exists(LECTUS_PLUGIN_DIR . 'templates/single-coursesingle.php')) {
            // Plugin template handles everything, don't modify content
            return $content;
        }
        
        // Check if theme has its own template - if so, don't modify content
        $theme = wp_get_theme();
        if ($theme->get('Name') === 'Lectus Academy' || 
            file_exists(get_stylesheet_directory() . '/single-coursesingle.php')) {
            // Theme handles its own layout
            return $content;
        }
        
        $course_id = get_the_ID();
        $user_id = get_current_user_id();
        
        ob_start();
        
        // Course meta information
        echo '<div class="lectus-course-meta">';
        
        // Duration
        $duration = get_post_meta($course_id, '_course_duration', true);
        if ($duration) {
            echo '<p class="course-duration">' . sprintf(__('수강 기간: %d일', 'lectus-class-system'), $duration) . '</p>';
        }
        
        // Access mode
        $access_mode = get_post_meta($course_id, '_access_mode', true);
        $mode_label = $access_mode === 'sequential' ? __('순차적 진행', 'lectus-class-system') : __('자유 진행', 'lectus-class-system');
        echo '<p class="course-access-mode">' . sprintf(__('진행 방식: %s', 'lectus-class-system'), $mode_label) . '</p>';
        
        // Enrollment status
        if ($user_id) {
            if (Lectus_Enrollment::is_enrolled($user_id, $course_id)) {
                $progress = Lectus_Progress::get_course_progress($user_id, $course_id);
                echo '<div class="course-progress">';
                echo '<p>' . __('수강 중', 'lectus-class-system') . '</p>';
                echo '<div class="progress-bar">';
                echo '<div class="progress-fill" style="width: ' . $progress . '%;"></div>';
                echo '<span>' . $progress . '%</span>';
                echo '</div>';
                echo '</div>';
            } else {
                echo do_shortcode('[lectus_enroll_button course_id="' . $course_id . '"]');
            }
        } else {
            echo '<p><a href="' . wp_login_url(get_permalink()) . '">' . __('로그인하여 수강 신청', 'lectus-class-system') . '</a></p>';
        }
        
        echo '</div>';
        
        // Course content
        echo '<div class="lectus-course-content">';
        echo $content;
        echo '</div>';
        
        // Lessons list with new unified system
        echo '<div class="lectus-course-lessons">';
        echo '<h2>' . __('강의 목차', 'lectus-class-system') . '</h2>';
        
        // Get course items using new unified system
        require_once LECTUS_PLUGIN_DIR . 'includes/class-lectus-course-items.php';
        $items = Lectus_Course_Items::get_course_items($course_id);
        
        if (!empty($items)) {
            $current_section = null;
            $section_lesson_number = 0;
            $prev_lesson_id = null;
            
            // First, build an array of all lesson IDs in order for sequential check
            $all_lesson_ids = array();
            foreach ($items as $item) {
                if ($item['type'] === 'lesson') {
                    $all_lesson_ids[] = $item['id'];
                }
            }
            
            foreach ($items as $item) {
                if ($item['type'] === 'section') {
                    // Close previous section if any
                    if ($current_section !== null) {
                        echo '</ol></div>';
                    }
                    
                    // Reset lesson number for new section
                    $section_lesson_number = 0;
                    
                    // Start new section
                    echo '<div class="lectus-section">';
                    echo '<h3 class="section-title">' . esc_html($item['title']) . '</h3>';
                    if (!empty($item['description'])) {
                        echo '<p class="section-description">' . esc_html($item['description']) . '</p>';
                    }
                    echo '<ol class="lessons-list">';
                    $current_section = $item;
                    
                } elseif ($item['type'] === 'lesson') {
                    // If no section started yet, create a default one
                    if ($current_section === null) {
                        echo '<div class="lectus-section unsectioned">';
                        echo '<ol class="lessons-list">';
                        $current_section = array('type' => 'unsectioned');
                        $section_lesson_number = 0;
                    }
                    
                    $section_lesson_number++;
                    $lesson_completed = false;
                    $lesson_locked = false;
                    
                    if ($user_id && Lectus_Enrollment::is_enrolled($user_id, $course_id)) {
                        $lesson_completed = Lectus_Progress::is_lesson_completed($user_id, $course_id, $item['id']);
                        
                        // Check if lesson is locked (sequential mode)
                        if ($access_mode === 'sequential' && $prev_lesson_id !== null) {
                            if (!Lectus_Progress::is_lesson_completed($user_id, $course_id, $prev_lesson_id)) {
                                $lesson_locked = true;
                            }
                        }
                    }
                    
                    echo '<li class="lesson-item' . ($lesson_completed ? ' completed' : '') . ($lesson_locked ? ' locked' : '') . '">';
                    
                    if ($lesson_locked) {
                        echo '<span class="lesson-title">' . esc_html($item['title']) . '</span>';
                        echo '<span class="lesson-status">' . __('잠김', 'lectus-class-system') . '</span>';
                    } else {
                        echo '<a href="' . get_permalink($item['id']) . '">' . esc_html($item['title']) . '</a>';
                        if ($lesson_completed) {
                            echo '<span class="lesson-status">✓</span>';
                        }
                    }
                    
                    if (!empty($item['duration'])) {
                        echo '<span class="lesson-duration">' . $item['duration'] . __('분', 'lectus-class-system') . '</span>';
                    }
                    
                    echo '</li>';
                    
                    $prev_lesson_id = $item['id'];
                }
            }
            
            // Close last section if any
            if ($current_section !== null) {
                echo '</ol></div>';
            }
        } else {
            echo '<p>' . __('레슨이 없습니다.', 'lectus-class-system') . '</p>';
        }
        
        echo '</div>';
        
        // Q&A Section
        echo '<div class="lectus-course-qa">';
        echo '<h2>' . __('질문과 답변', 'lectus-class-system') . '</h2>';
        
        if ($user_id && Lectus_Enrollment::is_enrolled($user_id, $course_id)) {
            // Q&A shortcode (폼과 목록을 모두 포함)
            echo do_shortcode('[lectus_qa course_id="' . $course_id . '" show_form="yes"]');
        } else {
            echo '<p>' . __('질문과 답변을 보려면 강의에 등록하세요.', 'lectus-class-system') . '</p>';
        }
        
        echo '</div>';
        
        // Course Materials Section
        echo '<div class="lectus-course-materials">';
        echo '<h2>' . __('강의 자료', 'lectus-class-system') . '</h2>';
        echo do_shortcode('[lectus_course_materials course_id="' . $course_id . '"]');
        echo '</div>';
        
        return ob_get_clean();
    }
    
    public static function lesson_content($content) {
        if (!is_singular('lesson')) {
            return $content;
        }
        
        // Check if we're using a full page template from plugin
        if (file_exists(LECTUS_PLUGIN_DIR . 'templates/single-lesson.php')) {
            // Plugin template handles everything, don't modify content
            return $content;
        }
        
        // Check if theme has custom lesson template - if so, don't modify content
        if (self::theme_has_lesson_template()) {
            return $content;
        }
        
        $lesson_id = get_the_ID();
        $course_id = get_post_meta($lesson_id, '_course_id', true);
        $user_id = get_current_user_id();
        
        // Check enrollment
        if (!$user_id || !Lectus_Enrollment::is_enrolled($user_id, $course_id)) {
            return '<div class="lectus-access-denied">' . 
                   '<p>' . __('이 레슨을 보려면 강의에 등록해야 합니다.', 'lectus-class-system') . '</p>' .
                   '<a href="' . get_permalink($course_id) . '" class="button">' . __('강의 페이지로 이동', 'lectus-class-system') . '</a>' .
                   '</div>';
        }
        
        ob_start();
        
        // Get course items and find current lesson's section
        require_once LECTUS_PLUGIN_DIR . 'includes/class-lectus-course-items.php';
        $items = Lectus_Course_Items::get_course_items($course_id);
        
        $current_section = null;
        $section_lesson_number = 0;
        $lesson_in_section = false;
        
        foreach ($items as $item) {
            if ($item['type'] === 'section') {
                $current_section = $item;
                $section_lesson_number = 0;
            } elseif ($item['type'] === 'lesson') {
                $section_lesson_number++;
                if ($item['id'] == $lesson_id) {
                    $lesson_in_section = true;
                    break;
                }
            }
        }
        
        // Navigation with section info
        echo '<div class="lectus-lesson-navigation">';
        echo '<a href="' . get_permalink($course_id) . '">← ' . __('강의 목록으로', 'lectus-class-system') . '</a>';
        
        if ($current_section && $lesson_in_section) {
            echo '<span class="lesson-breadcrumb"> / ' . esc_html($current_section['title']) . ' / 레슨 ' . $section_lesson_number . '</span>';
        }
        echo '</div>';
        
        // Lesson type specific content
        $lesson_type = get_post_meta($lesson_id, '_lesson_type', true);
        
        echo '<div class="lectus-lesson-content lesson-type-' . esc_attr($lesson_type) . '">';
        
        if ($lesson_type === 'video') {
            $video_url = get_post_meta($lesson_id, '_video_url', true);
            if ($video_url) {
                echo '<div class="lesson-video">';
                echo wp_oembed_get($video_url);
                echo '</div>';
            }
        }
        
        echo $content;
        
        // Mark as complete button
        $is_completed = Lectus_Progress::is_lesson_completed($user_id, $course_id, $lesson_id);
        
        echo '<div class="lesson-actions">';
        if (!$is_completed) {
            echo '<button class="lectus-complete-lesson" data-lesson-id="' . $lesson_id . '">' . 
                 __('레슨 완료하기', 'lectus-class-system') . '</button>';
        } else {
            echo '<p class="lesson-completed">✓ ' . __('완료됨', 'lectus-class-system') . '</p>';
        }
        echo '</div>';
        
        echo '</div>';
        
        // Next/Previous navigation
        $lessons = get_posts(array(
            'post_type' => 'lesson',
            'meta_key' => '_course_id',
            'meta_value' => $course_id,
            'posts_per_page' => -1,
            'orderby' => 'menu_order',
            'order' => 'ASC'
        ));
        
        $current_index = -1;
        foreach ($lessons as $index => $lesson) {
            if ($lesson->ID == $lesson_id) {
                $current_index = $index;
                break;
            }
        }
        
        echo '<div class="lectus-lesson-pagination">';
        if ($current_index > 0) {
            $prev_lesson = $lessons[$current_index - 1];
            echo '<a href="' . get_permalink($prev_lesson->ID) . '" class="prev-lesson">← ' . 
                 __('이전 레슨', 'lectus-class-system') . '</a>';
        }
        
        if ($current_index < count($lessons) - 1) {
            $next_lesson = $lessons[$current_index + 1];
            echo '<a href="' . get_permalink($next_lesson->ID) . '" class="next-lesson">' . 
                 __('다음 레슨', 'lectus-class-system') . ' →</a>';
        }
        echo '</div>';
        
        return ob_get_clean();
    }
    
    /**
     * Check if the current theme has a custom lesson template
     * This prevents duplicate content when themes provide their own lesson layouts
     */
    private static function theme_has_lesson_template() {
        // Check if theme has single-lesson.php template
        $theme_template = get_stylesheet_directory() . '/single-lesson.php';
        if (file_exists($theme_template)) {
            return true;
        }
        
        // Check if theme has lectus-class-system/single-lesson.php template
        $lectus_theme_template = get_stylesheet_directory() . '/lectus-class-system/single-lesson.php';
        if (file_exists($lectus_theme_template)) {
            return true;
        }
        
        return false;
    }
}