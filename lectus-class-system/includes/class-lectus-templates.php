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
        
        // Lessons list
        echo '<div class="lectus-course-lessons">';
        echo '<h2>' . __('강의 목차', 'lectus-class-system') . '</h2>';
        
        $lessons = get_posts(array(
            'post_type' => 'lesson',
            'meta_key' => '_course_id',
            'meta_value' => $course_id,
            'posts_per_page' => -1,
            'orderby' => 'menu_order',
            'order' => 'ASC'
        ));
        
        if ($lessons) {
            echo '<ol class="lessons-list">';
            foreach ($lessons as $index => $lesson) {
                $lesson_completed = false;
                $lesson_locked = false;
                
                if ($user_id && Lectus_Enrollment::is_enrolled($user_id, $course_id)) {
                    $lesson_completed = Lectus_Progress::is_lesson_completed($user_id, $course_id, $lesson->ID);
                    
                    // Check if lesson is locked (sequential mode)
                    if ($access_mode === 'sequential' && $index > 0) {
                        $prev_lesson = $lessons[$index - 1];
                        if (!Lectus_Progress::is_lesson_completed($user_id, $course_id, $prev_lesson->ID)) {
                            $lesson_locked = true;
                        }
                    }
                }
                
                echo '<li class="lesson-item' . ($lesson_completed ? ' completed' : '') . ($lesson_locked ? ' locked' : '') . '">';
                
                if ($lesson_locked) {
                    echo '<span class="lesson-title">' . esc_html($lesson->post_title) . '</span>';
                    echo '<span class="lesson-status">' . __('잠김', 'lectus-class-system') . '</span>';
                } else {
                    echo '<a href="' . get_permalink($lesson->ID) . '">' . esc_html($lesson->post_title) . '</a>';
                    if ($lesson_completed) {
                        echo '<span class="lesson-status">✓</span>';
                    }
                }
                
                $duration = get_post_meta($lesson->ID, '_lesson_duration', true);
                if ($duration) {
                    echo '<span class="lesson-duration">' . $duration . __('분', 'lectus-class-system') . '</span>';
                }
                
                echo '</li>';
            }
            echo '</ol>';
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
        
        // Navigation
        echo '<div class="lectus-lesson-navigation">';
        echo '<a href="' . get_permalink($course_id) . '">← ' . __('강의 목록으로', 'lectus-class-system') . '</a>';
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
}