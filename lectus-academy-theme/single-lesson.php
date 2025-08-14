<?php
/**
 * Template for displaying single lesson
 *
 * @package LectusAcademy
 */

get_header();

if (have_posts()) :
    while (have_posts()) :
        the_post();
        
        $lesson_id = get_the_ID();
        $course_id = get_post_meta($lesson_id, '_course_id', true);
        $course = get_post($course_id);
        
        // Check if user has access
        $is_enrolled = lectus_academy_is_enrolled($course_id);
        
        if (!$is_enrolled && !current_user_can('manage_options')) {
            ?>
            <div class="container">
                <div class="alert alert-warning">
                    <h2><?php esc_html_e('Access Restricted', 'lectus-academy'); ?></h2>
                    <p><?php esc_html_e('You need to be enrolled in this course to access this lesson.', 'lectus-academy'); ?></p>
                    <a href="<?php echo esc_url(get_permalink($course_id)); ?>" class="btn btn-primary">
                        <?php esc_html_e('View Course', 'lectus-academy'); ?>
                    </a>
                </div>
            </div>
            <?php
            get_footer();
            return;
        }
        
        // Get lesson details
        $lesson_type = get_post_meta($lesson_id, '_lesson_type', true) ?: 'text';
        $lesson_duration = get_post_meta($lesson_id, '_lesson_duration', true);
        $video_url = get_post_meta($lesson_id, '_video_url', true);
        $lessons = lectus_academy_get_course_lessons($course_id);
        
        // Get current lesson index
        $current_index = 0;
        foreach ($lessons as $index => $lesson) {
            if ($lesson->ID == $lesson_id) {
                $current_index = $index;
                break;
            }
        }
        
        $prev_lesson = $current_index > 0 ? $lessons[$current_index - 1] : null;
        $next_lesson = $current_index < count($lessons) - 1 ? $lessons[$current_index + 1] : null;
        ?>

        <main id="primary" class="site-main single-lesson-page">
            <div class="lesson-layout">
                <!-- Main Content -->
                <div class="lesson-content">
                    <!-- Breadcrumb -->
                    <nav class="breadcrumb">
                        <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'lectus-academy'); ?></a>
                        <span>/</span>
                        <a href="<?php echo esc_url(get_post_type_archive_link('coursesingle')); ?>"><?php esc_html_e('Courses', 'lectus-academy'); ?></a>
                        <span>/</span>
                        <a href="<?php echo esc_url(get_permalink($course_id)); ?>"><?php echo esc_html($course->post_title); ?></a>
                        <span>/</span>
                        <span class="current"><?php the_title(); ?></span>
                    </nav>

                    <h1 class="lesson-title"><?php the_title(); ?></h1>
                    
                    <div class="lesson-meta">
                        <span class="lesson-type">
                            <?php if ($lesson_type == 'video') : ?>
                                <i class="fas fa-video"></i> <?php esc_html_e('Video Lesson', 'lectus-academy'); ?>
                            <?php elseif ($lesson_type == 'quiz') : ?>
                                <i class="fas fa-question-circle"></i> <?php esc_html_e('Quiz', 'lectus-academy'); ?>
                            <?php elseif ($lesson_type == 'assignment') : ?>
                                <i class="fas fa-tasks"></i> <?php esc_html_e('Assignment', 'lectus-academy'); ?>
                            <?php else : ?>
                                <i class="fas fa-file-alt"></i> <?php esc_html_e('Text Lesson', 'lectus-academy'); ?>
                            <?php endif; ?>
                        </span>
                        <?php if ($lesson_duration) : ?>
                        <span class="lesson-duration">
                            <i class="fas fa-clock"></i>
                            <?php echo esc_html($lesson_duration); ?> <?php esc_html_e('minutes', 'lectus-academy'); ?>
                        </span>
                        <?php endif; ?>
                    </div>

                    <!-- Video Player (if video lesson) -->
                    <?php if ($lesson_type == 'video' && $video_url) : ?>
                    <div class="lesson-video">
                        <?php
                        // Check if YouTube or Vimeo
                        if (strpos($video_url, 'youtube.com') !== false || strpos($video_url, 'youtu.be') !== false) {
                            // Extract YouTube video ID
                            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $video_url, $matches);
                            if (isset($matches[1])) {
                                echo '<iframe src="https://www.youtube.com/embed/' . esc_attr($matches[1]) . '" frameborder="0" allowfullscreen></iframe>';
                            }
                        } elseif (strpos($video_url, 'vimeo.com') !== false) {
                            // Extract Vimeo video ID
                            preg_match('/vimeo\.com\/(\d+)/', $video_url, $matches);
                            if (isset($matches[1])) {
                                echo '<iframe src="https://player.vimeo.com/video/' . esc_attr($matches[1]) . '" frameborder="0" allowfullscreen></iframe>';
                            }
                        } else {
                            // Regular video file
                            echo '<video controls><source src="' . esc_url($video_url) . '" type="video/mp4"></video>';
                        }
                        ?>
                    </div>
                    <?php endif; ?>

                    <!-- Lesson Content -->
                    <div class="lesson-text-content">
                        <?php the_content(); ?>
                    </div>

                    <!-- Course Materials -->
                    <?php if (class_exists('Lectus_Materials')) :
                        $materials = Lectus_Materials::get_materials($course_id, $lesson_id);
                        if (!empty($materials)) : ?>
                            <div class="lesson-materials">
                                <h3><?php esc_html_e('Course Materials', 'lectus-academy'); ?></h3>
                                <ul class="materials-list">
                                    <?php foreach ($materials as $material) : ?>
                                        <li>
                                            <?php if ($material->material_type == 'file') : ?>
                                                <a href="<?php echo esc_url($material->file_url); ?>" download>
                                                    <i class="fas fa-download"></i>
                                                    <?php echo esc_html($material->title); ?>
                                                </a>
                                            <?php else : ?>
                                                <a href="<?php echo esc_url($material->external_url); ?>" target="_blank">
                                                    <i class="fas fa-external-link-alt"></i>
                                                    <?php echo esc_html($material->title); ?>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($material->description) : ?>
                                                <small><?php echo esc_html($material->description); ?></small>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif;
                    endif; ?>

                    <!-- Lesson Navigation -->
                    <div class="lesson-navigation">
                        <div class="lesson-nav-buttons">
                            <?php if ($prev_lesson) : ?>
                                <a href="<?php echo esc_url(get_permalink($prev_lesson->ID)); ?>" class="btn btn-outline">
                                    <i class="fas fa-chevron-left"></i>
                                    <?php esc_html_e('Previous Lesson', 'lectus-academy'); ?>
                                </a>
                            <?php endif; ?>
                            
                            <button id="complete-lesson" class="btn btn-success" data-lesson-id="<?php echo esc_attr($lesson_id); ?>" data-course-id="<?php echo esc_attr($course_id); ?>">
                                <i class="fas fa-check"></i>
                                <?php esc_html_e('Mark as Complete', 'lectus-academy'); ?>
                            </button>
                            
                            <?php if ($next_lesson) : ?>
                                <a href="<?php echo esc_url(get_permalink($next_lesson->ID)); ?>" class="btn btn-primary">
                                    <?php esc_html_e('Next Lesson', 'lectus-academy'); ?>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php else : ?>
                                <a href="<?php echo esc_url(get_permalink($course_id)); ?>" class="btn btn-primary">
                                    <?php esc_html_e('Back to Course', 'lectus-academy'); ?>
                                    <i class="fas fa-graduation-cap"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Q&A Section -->
                    <?php if (class_exists('Lectus_QA')) : ?>
                    <div class="qa-section">
                        <h3><?php esc_html_e('Questions & Answers', 'lectus-academy'); ?></h3>
                        
                        <!-- Q&A Form -->
                        <div class="qa-form">
                            <form id="lesson-qa-form" data-course-id="<?php echo esc_attr($course_id); ?>" data-lesson-id="<?php echo esc_attr($lesson_id); ?>">
                                <textarea name="question" placeholder="<?php esc_attr_e('Have a question about this lesson?', 'lectus-academy'); ?>" required></textarea>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i>
                                    <?php esc_html_e('Ask Question', 'lectus-academy'); ?>
                                </button>
                            </form>
                        </div>

                        <!-- Q&A List -->
                        <div class="qa-list">
                            <?php
                            $questions = Lectus_QA::get_questions($course_id, $lesson_id);
                            if (!empty($questions)) :
                                foreach ($questions as $question) :
                                    get_template_part('template-parts/qa', 'item', array('question' => $question));
                                endforeach;
                            endif;
                            ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="lesson-sidebar">
                    <div class="course-info-card">
                        <h4><?php echo esc_html($course->post_title); ?></h4>
                        
                        <!-- Course Progress -->
                        <div class="course-progress">
                            <?php
                            $progress = lectus_academy_get_course_progress($course_id);
                            ?>
                            <div class="progress-info">
                                <span><?php esc_html_e('Course Progress', 'lectus-academy'); ?></span>
                                <span><?php echo esc_html($progress); ?>%</span>
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: <?php echo esc_attr($progress); ?>%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Lesson List -->
                    <div class="lesson-list-card">
                        <h4><?php esc_html_e('Course Content', 'lectus-academy'); ?></h4>
                        <ul class="lesson-list">
                            <?php
                            foreach ($lessons as $index => $lesson) :
                                $is_current = $lesson->ID == $lesson_id;
                                $is_completed = false;
                                
                                if (class_exists('Lectus_Progress')) {
                                    $lesson_progress = Lectus_Progress::get_lesson_progress(get_current_user_id(), $course_id, $lesson->ID);
                                    $is_completed = $lesson_progress >= 100;
                                }
                                ?>
                                <li class="lesson-list-item <?php echo $is_current ? 'current' : ''; ?> <?php echo $is_completed ? 'completed' : ''; ?>">
                                    <a href="<?php echo esc_url(get_permalink($lesson->ID)); ?>">
                                        <span class="lesson-number"><?php echo esc_html($index + 1); ?></span>
                                        <span class="lesson-title"><?php echo esc_html($lesson->post_title); ?></span>
                                        <?php if ($is_completed) : ?>
                                            <i class="fas fa-check-circle"></i>
                                        <?php elseif ($is_current) : ?>
                                            <i class="fas fa-play-circle"></i>
                                        <?php endif; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Get Certificate -->
                    <?php
                    if (class_exists('Lectus_Progress') && Lectus_Progress::is_course_completed(get_current_user_id(), $course_id)) : ?>
                        <div class="certificate-card">
                            <h4><?php esc_html_e('Course Completed!', 'lectus-academy'); ?></h4>
                            <p><?php esc_html_e('Congratulations on completing this course!', 'lectus-academy'); ?></p>
                            <a href="<?php echo esc_url(home_url('/certificates')); ?>" class="btn btn-success btn-block">
                                <i class="fas fa-certificate"></i>
                                <?php esc_html_e('Get Certificate', 'lectus-academy'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>

    <?php
    endwhile;
endif;

get_footer();