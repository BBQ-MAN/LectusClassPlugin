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

        <main id="primary" class="site-main mt-20 min-h-screen bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 py-8 grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-3 space-y-6">
                    <!-- Breadcrumb -->
                    <nav class="flex items-center space-x-2 text-sm text-gray-600 mb-6">
                        <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'lectus-academy'); ?></a>
                        <span>/</span>
                        <a href="<?php echo esc_url(get_post_type_archive_link('coursesingle')); ?>"><?php esc_html_e('Courses', 'lectus-academy'); ?></a>
                        <span>/</span>
                        <a href="<?php echo esc_url(get_permalink($course_id)); ?>"><?php echo esc_html($course->post_title); ?></a>
                        <span>/</span>
                        <span class="current"><?php the_title(); ?></span>
                    </nav>

                    <h1 class="text-3xl font-bold text-gray-900 mb-4"><?php the_title(); ?></h1>
                    
                    <div class="flex items-center gap-6 text-sm text-gray-600 mb-6 p-4 bg-white rounded-lg border">
                        <span class="flex items-center gap-2">
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
                        <span class="flex items-center gap-2">
                            <i class="fas fa-clock"></i>
                            <?php echo esc_html($lesson_duration); ?> <?php esc_html_e('minutes', 'lectus-academy'); ?>
                        </span>
                        <?php endif; ?>
                    </div>

                    <!-- Video Player (if video lesson) -->
                    <?php if ($lesson_type == 'video' && $video_url) : ?>
                    <div class="mb-8 bg-black rounded-lg overflow-hidden aspect-video">
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
                    <div class="bg-white rounded-lg p-6 shadow-sm">
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
                        <div class="flex items-center justify-between gap-4 flex-wrap">
                            <?php if ($prev_lesson) : ?>
                                <a href="<?php echo esc_url(get_permalink($prev_lesson->ID)); ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700 font-medium">
                                    <i class="fas fa-chevron-left"></i>
                                    <?php esc_html_e('Previous Lesson', 'lectus-academy'); ?>
                                </a>
                            <?php endif; ?>
                            
                            <button id="complete-lesson" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium" data-lesson-id="<?php echo esc_attr($lesson_id); ?>" data-course-id="<?php echo esc_attr($course_id); ?>">
                                <i class="fas fa-check"></i>
                                <?php esc_html_e('Mark as Complete', 'lectus-academy'); ?>
                            </button>
                            
                            <?php if ($next_lesson) : ?>
                                <a href="<?php echo esc_url(get_permalink($next_lesson->ID)); ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                                    <?php esc_html_e('Next Lesson', 'lectus-academy'); ?>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php else : ?>
                                <a href="<?php echo esc_url(get_permalink($course_id)); ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                                    <?php esc_html_e('Back to Course', 'lectus-academy'); ?>
                                    <i class="fas fa-graduation-cap"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Q&A Section -->
                    <?php if (class_exists('Lectus_QA')) : ?>
                    <div class="bg-white rounded-lg p-6 shadow-sm mt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6"><?php esc_html_e('Questions & Answers', 'lectus-academy'); ?></h3>
                        
                        <!-- Q&A Form -->
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                            <form id="lesson-qa-form" data-course-id="<?php echo esc_attr($course_id); ?>" data-lesson-id="<?php echo esc_attr($lesson_id); ?>">
                                <textarea name="question" placeholder="<?php esc_attr_e('Have a question about this lesson?', 'lectus-academy'); ?>" required class="w-full p-3 border border-gray-300 rounded-lg resize-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" rows="3"></textarea>
                                <button type="submit" class="mt-3 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                                    <i class="fas fa-paper-plane"></i>
                                    <?php esc_html_e('Ask Question', 'lectus-academy'); ?>
                                </button>
                            </form>
                        </div>

                        <!-- Q&A List -->
                        <div class="space-y-4">
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
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-lg p-6 shadow-sm">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4"><?php echo esc_html($course->post_title); ?></h4>
                        
                        <!-- Course Progress -->
                        <div class="mb-4">
                            <?php
                            $progress = lectus_academy_get_course_progress($course_id);
                            ?>
                            <div class="flex justify-between items-center mb-2 text-sm">
                                <span><?php esc_html_e('Course Progress', 'lectus-academy'); ?></span>
                                <span><?php echo esc_html($progress); ?>%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: <?php echo esc_attr($progress); ?>%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Lesson List -->
                    <div class="bg-white rounded-lg p-6 shadow-sm">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4"><?php esc_html_e('Course Content', 'lectus-academy'); ?></h4>
                        <ul class="space-y-2">
                            <?php
                            foreach ($lessons as $index => $lesson) :
                                $is_current = $lesson->ID == $lesson_id;
                                $is_completed = false;
                                
                                if (class_exists('Lectus_Progress')) {
                                    $lesson_progress = Lectus_Progress::get_lesson_progress(get_current_user_id(), $course_id, $lesson->ID);
                                    $is_completed = $lesson_progress >= 100;
                                }
                                ?>
                                <li class="border border-gray-200 rounded-lg overflow-hidden <?php echo $is_current ? 'bg-blue-50 border-blue-500' : ''; ?> <?php echo $is_completed ? 'opacity-75' : ''; ?>">
                                    <a href="<?php echo esc_url(get_permalink($lesson->ID)); ?>" class="flex items-center gap-3 p-3 hover:bg-gray-50 transition-colors <?php echo $is_current ? 'text-blue-600' : 'text-gray-700'; ?>">
                                        <span class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium <?php echo $is_current ? 'bg-blue-600 text-white' : ($is_completed ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-700'); ?>"><?php echo esc_html($index + 1); ?></span>
                                        <span class="flex-1 font-medium"><?php echo esc_html($lesson->post_title); ?></span>
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
                        <div class="bg-gradient-to-br from-green-50 to-blue-50 border border-green-200 rounded-lg p-6 shadow-sm">
                            <h4 class="text-lg font-semibold text-green-700 mb-2"><?php esc_html_e('Course Completed!', 'lectus-academy'); ?></h4>
                            <p class="text-green-600 mb-4"><?php esc_html_e('Congratulations on completing this course!', 'lectus-academy'); ?></p>
                            <a href="<?php echo esc_url(home_url('/certificates')); ?>" class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
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