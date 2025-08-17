<?php
/**
 * Course Curriculum Template
 * 
 * Displays course curriculum with sections and lessons
 * 
 * @package Lectus_Class_System
 * @since 1.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$course_id = get_the_ID();
$user_id = get_current_user_id();

// Get course items using new unified system
require_once LECTUS_PLUGIN_DIR . 'includes/class-lectus-course-items.php';
$items = Lectus_Course_Items::get_course_items($course_id);

// Get access mode
$access_mode = get_post_meta($course_id, '_access_mode', true);

?>

<div class="lectus-course-curriculum">
    <h2><?php _e('커리큘럼', 'lectus-class-system'); ?></h2>
    
    <?php if (!empty($items)): ?>
        <?php
        $current_section = null;
        $section_lesson_number = 0;
        $prev_lesson_id = null;
        $total_lessons = 0;
        $completed_lessons = 0;
        
        // Count total lessons
        foreach ($items as $item) {
            if ($item['type'] === 'lesson') {
                $total_lessons++;
                if ($user_id && Lectus_Enrollment::is_enrolled($user_id, $course_id)) {
                    if (Lectus_Progress::is_lesson_completed($user_id, $course_id, $item['id'])) {
                        $completed_lessons++;
                    }
                }
            }
        }
        ?>
        
        <!-- Overall Progress -->
        <?php if ($user_id && Lectus_Enrollment::is_enrolled($user_id, $course_id) && $total_lessons > 0): ?>
        <div class="curriculum-progress">
            <div class="progress-info">
                <span><?php echo sprintf(__('진도율: %d / %d 레슨 완료', 'lectus-class-system'), $completed_lessons, $total_lessons); ?></span>
                <span><?php echo round(($completed_lessons / $total_lessons) * 100); ?>%</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo ($completed_lessons / $total_lessons) * 100; ?>%;"></div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Curriculum List -->
        <div class="curriculum-list">
            <?php foreach ($items as $item): ?>
                <?php if ($item['type'] === 'section'): ?>
                    <?php
                    // Close previous section if any
                    if ($current_section !== null) {
                        echo '</ul></div>';
                    }
                    
                    // Reset lesson number for new section
                    $section_lesson_number = 0;
                    $current_section = $item;
                    ?>
                    
                    <div class="curriculum-section">
                        <div class="section-header">
                            <h3 class="section-title">
                                <i class="fas fa-folder"></i>
                                <?php echo esc_html($item['title']); ?>
                            </h3>
                            <?php if (!empty($item['description'])): ?>
                                <p class="section-description"><?php echo esc_html($item['description']); ?></p>
                            <?php endif; ?>
                        </div>
                        <ul class="section-lessons">
                        
                <?php elseif ($item['type'] === 'lesson'): ?>
                    <?php
                    // If no section started yet, create a default one
                    if ($current_section === null) {
                        echo '<div class="curriculum-section unsectioned">';
                        echo '<ul class="section-lessons">';
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
                    } else {
                        $lesson_locked = true; // Lock all lessons for non-enrolled users
                    }
                    
                    $prev_lesson_id = $item['id'];
                    ?>
                    
                    <li class="lesson-item <?php echo $lesson_completed ? 'completed' : ''; ?> <?php echo $lesson_locked ? 'locked' : ''; ?>">
                        <div class="lesson-info">
                            <span class="lesson-number"><?php echo $section_lesson_number; ?></span>
                            
                            <?php if ($lesson_locked && !($user_id && Lectus_Enrollment::is_enrolled($user_id, $course_id))): ?>
                                <span class="lesson-title"><?php echo esc_html($item['title']); ?></span>
                                <span class="lesson-status locked">
                                    <i class="fas fa-lock"></i>
                                    <?php _e('수강 신청 필요', 'lectus-class-system'); ?>
                                </span>
                            <?php elseif ($lesson_locked): ?>
                                <span class="lesson-title"><?php echo esc_html($item['title']); ?></span>
                                <span class="lesson-status locked">
                                    <i class="fas fa-lock"></i>
                                    <?php _e('이전 레슨 완료 필요', 'lectus-class-system'); ?>
                                </span>
                            <?php else: ?>
                                <a href="<?php echo get_permalink($item['id']); ?>" class="lesson-title">
                                    <?php echo esc_html($item['title']); ?>
                                </a>
                                <?php if ($lesson_completed): ?>
                                    <span class="lesson-status completed">
                                        <i class="fas fa-check-circle"></i>
                                        <?php _e('완료', 'lectus-class-system'); ?>
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php if (!empty($item['duration'])): ?>
                                <span class="lesson-duration">
                                    <i class="far fa-clock"></i>
                                    <?php echo $item['duration']; ?>분
                                </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($item['lesson_type'])): ?>
                                <span class="lesson-type type-<?php echo esc_attr($item['lesson_type']); ?>">
                                    <?php
                                    $type_labels = array(
                                        'video' => __('동영상', 'lectus-class-system'),
                                        'text' => __('텍스트', 'lectus-class-system'),
                                        'quiz' => __('퀴즈', 'lectus-class-system'),
                                        'assignment' => __('과제', 'lectus-class-system')
                                    );
                                    echo isset($type_labels[$item['lesson_type']]) ? $type_labels[$item['lesson_type']] : $item['lesson_type'];
                                    ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </li>
                    
                <?php endif; ?>
            <?php endforeach; ?>
            
            <?php
            // Close last section if any
            if ($current_section !== null) {
                echo '</ul></div>';
            }
            ?>
        </div>
        
    <?php else: ?>
        <p class="no-curriculum"><?php _e('커리큘럼이 아직 준비되지 않았습니다.', 'lectus-class-system'); ?></p>
    <?php endif; ?>
</div>

<style>
.lectus-course-curriculum {
    margin: 30px 0;
}

.curriculum-progress {
    margin-bottom: 30px;
    padding: 20px;
    background: #f5f5f5;
    border-radius: 8px;
}

.curriculum-progress .progress-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 14px;
}

.curriculum-progress .progress-bar {
    height: 10px;
    background: #e0e0e0;
    border-radius: 5px;
    overflow: hidden;
}

.curriculum-progress .progress-fill {
    height: 100%;
    background: #4caf50;
    transition: width 0.3s ease;
}

.curriculum-section {
    margin-bottom: 30px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
}

.curriculum-section.unsectioned {
    border: none;
}

.section-header {
    padding: 15px 20px;
    background: #f9f9f9;
    border-bottom: 1px solid #e0e0e0;
}

.section-title {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #333;
}

.section-title i {
    margin-right: 8px;
    color: #666;
}

.section-description {
    margin: 5px 0 0;
    font-size: 14px;
    color: #666;
}

.section-lessons {
    list-style: none;
    margin: 0;
    padding: 0;
}

.lesson-item {
    padding: 15px 20px;
    border-bottom: 1px solid #f0f0f0;
    transition: background 0.2s;
}

.lesson-item:last-child {
    border-bottom: none;
}

.lesson-item:hover {
    background: #fafafa;
}

.lesson-item.completed {
    background: #f0f8f0;
}

.lesson-item.locked {
    opacity: 0.6;
}

.lesson-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.lesson-number {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e0e0e0;
    border-radius: 50%;
    font-weight: 600;
    font-size: 14px;
}

.lesson-item.completed .lesson-number {
    background: #4caf50;
    color: white;
}

.lesson-title {
    flex: 1;
    font-weight: 500;
    color: #333;
    text-decoration: none;
}

.lesson-title:hover {
    color: #0073aa;
}

.lesson-status {
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.lesson-status.completed {
    color: #4caf50;
}

.lesson-status.locked {
    color: #999;
}

.lesson-duration {
    font-size: 13px;
    color: #666;
}

.lesson-type {
    font-size: 12px;
    padding: 3px 8px;
    border-radius: 4px;
    background: #e0e0e0;
    color: #666;
}

.lesson-type.type-video {
    background: #e3f2fd;
    color: #1976d2;
}

.lesson-type.type-quiz {
    background: #fff3e0;
    color: #f57c00;
}

.lesson-type.type-assignment {
    background: #f3e5f5;
    color: #7b1fa2;
}

.no-curriculum {
    padding: 40px;
    text-align: center;
    color: #999;
    background: #f5f5f5;
    border-radius: 8px;
}
</style>