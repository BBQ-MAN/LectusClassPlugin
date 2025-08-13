<?php
/**
 * Student Management for Lectus Class System
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Student {
    
    public static function init() {
        // User profile hooks
        add_action('show_user_profile', array(__CLASS__, 'add_user_fields'));
        add_action('edit_user_profile', array(__CLASS__, 'add_user_fields'));
        add_action('personal_options_update', array(__CLASS__, 'save_user_fields'));
        add_action('edit_user_profile_update', array(__CLASS__, 'save_user_fields'));
        
        // Student registration
        add_action('user_register', array(__CLASS__, 'handle_user_registration'));
        
        // Dashboard widgets
        add_action('wp_dashboard_setup', array(__CLASS__, 'add_dashboard_widgets'));
    }
    
    public static function add_user_fields($user) {
        if (!current_user_can('manage_students')) {
            return;
        }
        ?>
        <h3><?php _e('Lectus Class System 정보', 'lectus-class-system'); ?></h3>
        <table class="form-table">
            <tr>
                <th><label><?php _e('등록된 강의', 'lectus-class-system'); ?></label></th>
                <td>
                    <?php
                    $enrollments = Lectus_Enrollment::get_user_enrollments($user->ID);
                    if (!empty($enrollments)) {
                        echo '<ul>';
                        foreach ($enrollments as $enrollment) {
                            $course = get_post($enrollment->course_id);
                            if ($course) {
                                $progress = Lectus_Progress::get_course_progress($user->ID, $enrollment->course_id);
                                echo '<li>';
                                echo '<strong>' . esc_html($course->post_title) . '</strong>';
                                echo ' - ' . sprintf(__('진도: %d%%', 'lectus-class-system'), $progress);
                                echo ' - ' . sprintf(__('상태: %s', 'lectus-class-system'), Lectus_Enrollment::get_status_label($enrollment->status));
                                echo '</li>';
                            }
                        }
                        echo '</ul>';
                    } else {
                        echo '<p>' . __('등록된 강의가 없습니다.', 'lectus-class-system') . '</p>';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th><label><?php _e('수료증', 'lectus-class-system'); ?></label></th>
                <td>
                    <?php
                    $certificates = Lectus_Certificate::get_user_certificates($user->ID);
                    if (!empty($certificates)) {
                        echo '<ul>';
                        foreach ($certificates as $certificate) {
                            $course = get_post($certificate->course_id);
                            if ($course) {
                                echo '<li>';
                                echo '<strong>' . esc_html($course->post_title) . '</strong>';
                                echo ' - ' . __('수료증 번호: ', 'lectus-class-system') . esc_html($certificate->certificate_number);
                                echo ' - ' . date_i18n(get_option('date_format'), strtotime($certificate->issued_at));
                                echo '</li>';
                            }
                        }
                        echo '</ul>';
                    } else {
                        echo '<p>' . __('발급된 수료증이 없습니다.', 'lectus-class-system') . '</p>';
                    }
                    ?>
                </td>
            </tr>
        </table>
        <?php
    }
    
    public static function save_user_fields($user_id) {
        if (!current_user_can('manage_students')) {
            return;
        }
        
        // Custom fields can be saved here if needed
    }
    
    public static function handle_user_registration($user_id) {
        // Assign student role if configured
        $auto_assign_student_role = get_option('lectus_auto_assign_student_role', 'no');
        if ($auto_assign_student_role === 'yes') {
            $user = new WP_User($user_id);
            $user->add_role('lectus_student');
        }
        
        // Send welcome email
        do_action('lectus_student_registered', $user_id);
    }
    
    public static function add_dashboard_widgets() {
        if (current_user_can('view_courses')) {
            wp_add_dashboard_widget(
                'lectus_student_progress',
                __('내 학습 진도', 'lectus-class-system'),
                array(__CLASS__, 'dashboard_progress_widget')
            );
        }
    }
    
    public static function dashboard_progress_widget() {
        $user_id = get_current_user_id();
        $enrollments = Lectus_Enrollment::get_user_enrollments($user_id, 'active');
        
        if (empty($enrollments)) {
            echo '<p>' . __('등록된 강의가 없습니다.', 'lectus-class-system') . '</p>';
            return;
        }
        
        echo '<table style="width: 100%;">';
        foreach ($enrollments as $enrollment) {
            $course = get_post($enrollment->course_id);
            if (!$course) continue;
            
            $progress = Lectus_Progress::get_course_progress($user_id, $enrollment->course_id);
            
            echo '<tr>';
            echo '<td><a href="' . get_permalink($course->ID) . '">' . esc_html($course->post_title) . '</a></td>';
            echo '<td style="width: 150px;">';
            echo '<div style="background: #f0f0f0; height: 20px; border-radius: 10px; overflow: hidden;">';
            echo '<div style="background: #4CAF50; height: 100%; width: ' . $progress . '%;"></div>';
            echo '</div>';
            echo '</td>';
            echo '<td style="width: 50px; text-align: right;">' . $progress . '%</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
    
    public static function get_student_stats($user_id) {
        global $wpdb;
        
        $stats = array(
            'total_courses' => 0,
            'completed_courses' => 0,
            'in_progress_courses' => 0,
            'total_certificates' => 0,
            'total_learning_time' => 0,
            'average_progress' => 0
        );
        
        // Get enrollment stats
        $enrollment_table = $wpdb->prefix . 'lectus_enrollment';
        $enrollments = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $enrollment_table WHERE user_id = %d",
            $user_id
        ));
        
        $total_progress = 0;
        foreach ($enrollments as $enrollment) {
            if ($enrollment->status === 'active') {
                $stats['total_courses']++;
                
                $progress = Lectus_Progress::get_course_progress($user_id, $enrollment->course_id);
                $total_progress += $progress;
                
                if ($progress >= 100) {
                    $stats['completed_courses']++;
                } else if ($progress > 0) {
                    $stats['in_progress_courses']++;
                }
            }
        }
        
        // Calculate average progress
        if ($stats['total_courses'] > 0) {
            $stats['average_progress'] = round($total_progress / $stats['total_courses']);
        }
        
        // Get certificate count
        $certificate_table = $wpdb->prefix . 'lectus_certificates';
        $stats['total_certificates'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $certificate_table WHERE user_id = %d",
            $user_id
        ));
        
        return $stats;
    }
    
    public static function get_recent_activity($user_id, $limit = 10) {
        global $wpdb;
        
        $progress_table = $wpdb->prefix . 'lectus_progress';
        
        $activities = $wpdb->get_results($wpdb->prepare(
            "SELECT p.*, c.post_title as course_title, l.post_title as lesson_title
             FROM $progress_table p
             LEFT JOIN {$wpdb->posts} c ON p.course_id = c.ID
             LEFT JOIN {$wpdb->posts} l ON p.lesson_id = l.ID
             WHERE p.user_id = %d
             ORDER BY GREATEST(COALESCE(p.started_at, '1970-01-01'), COALESCE(p.completed_at, '1970-01-01')) DESC
             LIMIT %d",
            $user_id,
            $limit
        ));
        
        return $activities;
    }
}