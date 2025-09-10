<?php
/**
 * Template for displaying single lesson (lesson/lecture page)
 *
 * @package LectusClassSystem
 */

get_header();

// Get lesson data
$lesson_id = get_the_ID();
$course_id = get_post_meta($lesson_id, '_course_id', true);
$user_id = get_current_user_id();

// Check enrollment
if (!$user_id || !class_exists('Lectus_Enrollment') || !Lectus_Enrollment::is_enrolled($user_id, $course_id)) {
    ?>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
            <i class="fas fa-lock text-6xl text-gray-400 mb-4"></i>
            <h2 class="text-2xl font-bold mb-4">수강 권한이 없습니다</h2>
            <p class="text-gray-600 mb-6">이 레슨을 보려면 강의에 등록해야 합니다.</p>
            <a href="<?php echo get_permalink($course_id); ?>" 
               class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                강의 페이지로 이동
            </a>
        </div>
    </div>
    <?php
    get_footer();
    return;
}

// Get course info
$course_title = get_the_title($course_id);
$course_access_mode = get_post_meta($course_id, '_access_mode', true);

// Get lesson meta
$lesson_type = get_post_meta($lesson_id, '_lesson_type', true) ?: 'text';
$video_url = get_post_meta($lesson_id, '_video_url', true);
$lesson_duration = get_post_meta($lesson_id, '_lesson_duration', true);

// Get course items for navigation
require_once LECTUS_PLUGIN_DIR . 'includes/class-lectus-course-items.php';
$course_items = Lectus_Course_Items::get_course_items($course_id);

// Find current lesson position and navigation
$current_section = null;
$section_lesson_number = 0;
$all_lessons = array();
$current_lesson_index = -1;
$lesson_in_section = false;

foreach ($course_items as $item) {
    if ($item['type'] === 'section') {
        $current_section = $item;
        $section_lesson_number = 0;
    } elseif ($item['type'] === 'lesson') {
        $all_lessons[] = array(
            'id' => $item['id'],
            'title' => $item['title'],
            'section' => $current_section
        );
        $section_lesson_number++;
        
        if ($item['id'] == $lesson_id) {
            $lesson_in_section = true;
            $current_lesson_index = count($all_lessons) - 1;
        }
    }
}

// Get previous and next lessons
$prev_lesson = ($current_lesson_index > 0) ? $all_lessons[$current_lesson_index - 1] : null;
$next_lesson = ($current_lesson_index < count($all_lessons) - 1) ? $all_lessons[$current_lesson_index + 1] : null;

// Check if current lesson is completed
$is_completed = false;
if (class_exists('Lectus_Progress')) {
    $is_completed = Lectus_Progress::is_lesson_completed($user_id, $course_id, $lesson_id);
}

// Check if next lesson is locked (sequential mode)
$next_locked = false;
if ($course_access_mode === 'sequential' && $next_lesson && !$is_completed) {
    $next_locked = true;
}
?>

<div class="min-h-screen bg-gray-900">
    <!-- Top Navigation Bar -->
    <div class="bg-gray-800 border-b border-gray-700">
        <div class="max-w-screen-2xl mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <!-- Left side -->
                <div class="flex items-center gap-4">
                    <a href="<?php echo get_permalink($course_id); ?>" 
                       class="text-gray-400 hover:text-white transition">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div>
                        <div class="text-xs text-gray-400"><?php echo esc_html($course_title); ?></div>
                        <h1 class="text-white font-medium"><?php the_title(); ?></h1>
                    </div>
                </div>
                
                <!-- Right side -->
                <div class="flex items-center gap-4">
                    <!-- Progress indicator -->
                    <div class="text-sm text-gray-400">
                        <?php echo ($current_lesson_index + 1); ?> / <?php echo count($all_lessons); ?>
                    </div>
                    
                    <!-- Navigation buttons -->
                    <div class="flex items-center gap-2">
                        <?php if ($prev_lesson): ?>
                            <a href="<?php echo get_permalink($prev_lesson['id']); ?>" 
                               class="px-3 py-1 bg-gray-700 text-white rounded hover:bg-gray-600 transition text-sm">
                                <i class="fas fa-chevron-left mr-1"></i> 이전
                            </a>
                        <?php else: ?>
                            <button disabled class="px-3 py-1 bg-gray-700 text-gray-500 rounded cursor-not-allowed text-sm">
                                <i class="fas fa-chevron-left mr-1"></i> 이전
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($next_lesson): ?>
                            <?php if ($next_locked): ?>
                                <button disabled class="px-3 py-1 bg-gray-700 text-gray-500 rounded cursor-not-allowed text-sm">
                                    다음 <i class="fas fa-chevron-right ml-1"></i>
                                    <i class="fas fa-lock ml-1 text-xs"></i>
                                </button>
                            <?php else: ?>
                                <a href="<?php echo get_permalink($next_lesson['id']); ?>" 
                                   class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition text-sm">
                                    다음 <i class="fas fa-chevron-right ml-1"></i>
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <button disabled class="px-3 py-1 bg-gray-700 text-gray-500 rounded cursor-not-allowed text-sm">
                                다음 <i class="fas fa-chevron-right ml-1"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content Area -->
    <div class="flex h-[calc(100vh-4rem)]">
        <!-- Video/Content Area (Left) -->
        <div class="flex-1 bg-black flex flex-col">
            <?php if ($lesson_type === 'video' && $video_url): ?>
                <!-- Video Player -->
                <div class="flex-1 flex items-center justify-center">
                    <div class="w-full max-w-6xl px-4">
                        <?php
                        // Use WordPress oEmbed for video
                        $embed = wp_oembed_get($video_url);
                        if ($embed) {
                            // Wrap embed in responsive container
                            echo '<div class="relative pb-[56.25%] h-0">';
                            echo '<div class="absolute top-0 left-0 w-full h-full">';
                            echo $embed;
                            echo '</div>';
                            echo '</div>';
                        } else {
                            // Fallback for non-oEmbed URLs
                            echo '<video controls class="w-full h-auto">';
                            echo '<source src="' . esc_url($video_url) . '" type="video/mp4">';
                            echo '브라우저가 비디오를 지원하지 않습니다.';
                            echo '</video>';
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Lesson Description Below Video -->
                <?php if (get_the_content()): ?>
                <div class="bg-gray-800 border-t border-gray-700 p-6">
                    <div class="max-w-6xl mx-auto">
                        <div class="prose prose-invert max-w-none">
                            <?php the_content(); ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- Text/Article Content -->
                <div class="flex-1 overflow-y-auto bg-white">
                    <div class="max-w-4xl mx-auto px-6 py-8">
                        <h1 class="text-3xl font-bold mb-6"><?php the_title(); ?></h1>
                        <div class="prose max-w-none">
                            <?php the_content(); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Bottom Action Bar -->
            <div class="bg-gray-800 border-t border-gray-700 p-4">
                <div class="max-w-6xl mx-auto flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <?php if (!$is_completed): ?>
                            <button class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition lectus-complete-lesson" 
                                    data-lesson-id="<?php echo $lesson_id; ?>"
                                    data-course-id="<?php echo $course_id; ?>">
                                <i class="fas fa-check mr-2"></i> 레슨 완료하기
                            </button>
                        <?php else: ?>
                            <div class="px-6 py-2 bg-gray-700 text-green-400 rounded-lg">
                                <i class="fas fa-check-circle mr-2"></i> 완료됨
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($lesson_duration): ?>
                            <span class="text-gray-400 text-sm">
                                <i class="far fa-clock mr-1"></i> <?php echo $lesson_duration; ?>분
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <button class="p-2 text-gray-400 hover:text-white transition" title="북마크">
                            <i class="far fa-bookmark"></i>
                        </button>
                        <button class="p-2 text-gray-400 hover:text-white transition" title="노트">
                            <i class="far fa-sticky-note"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar (Right) -->
        <div class="w-96 bg-gray-800 border-l border-gray-700 flex flex-col">
            <!-- Tabs -->
            <div class="border-b border-gray-700">
                <div class="flex">
                    <button class="flex-1 px-4 py-3 text-white border-b-2 border-blue-500 lesson-sidebar-tab active" 
                            data-tab="curriculum">
                        커리큘럼
                    </button>
                    <button class="flex-1 px-4 py-3 text-gray-400 hover:text-white lesson-sidebar-tab" 
                            data-tab="notes">
                        노트
                    </button>
                    <button class="flex-1 px-4 py-3 text-gray-400 hover:text-white lesson-sidebar-tab" 
                            data-tab="qa">
                        Q&A
                    </button>
                </div>
            </div>
            
            <!-- Tab Contents -->
            <div class="flex-1 overflow-y-auto">
                <!-- Curriculum Tab -->
                <div class="lesson-sidebar-content" id="curriculum-content">
                    <?php
                    $current_section = null;
                    $section_number = 0;
                    $lesson_number = 0;
                    ?>
                    
                    <?php foreach ($course_items as $item): ?>
                        <?php if ($item['type'] === 'section'): ?>
                            <?php $section_number++; ?>
                            <div class="border-b border-gray-700">
                                <div class="px-4 py-3 bg-gray-900">
                                    <h3 class="text-white font-medium">
                                        섹션 <?php echo $section_number; ?>. <?php echo esc_html($item['title']); ?>
                                    </h3>
                                </div>
                            </div>
                            <?php $lesson_number = 0; ?>
                        <?php elseif ($item['type'] === 'lesson'): ?>
                            <?php 
                            $lesson_number++;
                            $is_current = ($item['id'] == $lesson_id);
                            $is_completed_lesson = Lectus_Progress::is_lesson_completed($user_id, $course_id, $item['id']);
                            $is_locked = false;
                            
                            // Check if locked in sequential mode
                            if ($course_access_mode === 'sequential' && isset($prev_item_id)) {
                                if (!Lectus_Progress::is_lesson_completed($user_id, $course_id, $prev_item_id)) {
                                    $is_locked = true;
                                }
                            }
                            ?>
                            
                            <a href="<?php echo $is_locked ? '#' : get_permalink($item['id']); ?>" 
                               class="block px-4 py-3 hover:bg-gray-700 transition <?php echo $is_current ? 'bg-gray-700 border-l-4 border-blue-500' : ''; ?> <?php echo $is_locked ? 'cursor-not-allowed opacity-50' : ''; ?>">
                                <div class="flex items-start gap-3">
                                    <div class="mt-1">
                                        <?php if ($is_completed_lesson): ?>
                                            <i class="fas fa-check-circle text-green-500"></i>
                                        <?php elseif ($is_current): ?>
                                            <i class="fas fa-play-circle text-blue-500"></i>
                                        <?php elseif ($is_locked): ?>
                                            <i class="fas fa-lock text-gray-500"></i>
                                        <?php else: ?>
                                            <i class="far fa-circle text-gray-500"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-white <?php echo $is_current ? 'font-medium' : ''; ?>">
                                            <?php echo sprintf('%02d', $lesson_number); ?>. <?php echo esc_html($item['title']); ?>
                                        </div>
                                        <?php if (!empty($item['duration'])): ?>
                                            <div class="text-xs text-gray-400 mt-1">
                                                <?php echo $item['duration']; ?>분
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>
                            
                            <?php $prev_item_id = $item['id']; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                
                <!-- Notes Tab -->
                <div class="lesson-sidebar-content hidden" id="notes-content">
                    <div class="p-4">
                        <div class="mb-4">
                            <textarea class="w-full h-32 p-3 bg-gray-700 text-white rounded-lg resize-none" 
                                      placeholder="이 레슨에 대한 노트를 작성하세요..."></textarea>
                        </div>
                        <button class="w-full py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            노트 저장
                        </button>
                        
                        <div class="mt-6">
                            <h4 class="text-white font-medium mb-3">저장된 노트</h4>
                            <p class="text-gray-400 text-sm">아직 작성된 노트가 없습니다.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Q&A Tab -->
                <div class="lesson-sidebar-content hidden" id="qa-content">
                    <div class="p-4">
                        <button class="w-full py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition mb-4">
                            <i class="fas fa-plus mr-2"></i> 질문하기
                        </button>
                        
                        <div class="space-y-4">
                            <p class="text-gray-400 text-sm">이 레슨에 대한 질문이 없습니다.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Sidebar tab switching
    $('.lesson-sidebar-tab').on('click', function() {
        var tab = $(this).data('tab');
        
        // Update tab buttons
        $('.lesson-sidebar-tab').removeClass('active text-white border-b-2 border-blue-500').addClass('text-gray-400');
        $(this).addClass('active text-white border-b-2 border-blue-500').removeClass('text-gray-400');
        
        // Show/hide content
        $('.lesson-sidebar-content').addClass('hidden');
        $('#' + tab + '-content').removeClass('hidden');
    });
    
    // Complete lesson button
    $('.lectus-complete-lesson').on('click', function() {
        var $btn = $(this);
        var lessonId = $btn.data('lesson-id');
        var courseId = $btn.data('course-id');
        
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> 처리중...');
        
        $.ajax({
            url: lectus_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'lectus_complete_lesson',
                lesson_id: lessonId,
                course_id: courseId,
                nonce: lectus_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $btn.replaceWith('<div class="px-6 py-2 bg-gray-700 text-green-400 rounded-lg"><i class="fas fa-check-circle mr-2"></i> 완료됨</div>');
                    
                    // Update curriculum sidebar
                    location.reload(); // Simple reload for now
                } else {
                    alert(response.data || '오류가 발생했습니다.');
                    $btn.prop('disabled', false).html('<i class="fas fa-check mr-2"></i> 레슨 완료하기');
                }
            },
            error: function() {
                alert('오류가 발생했습니다.');
                $btn.prop('disabled', false).html('<i class="fas fa-check mr-2"></i> 레슨 완료하기');
            }
        });
    });
});
</script>

<?php get_footer(); ?>