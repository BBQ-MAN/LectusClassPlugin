<?php
/**
 * Custom Post Types for Lectus Class System
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Post_Types {
    
    public static function init() {
        // Register post types immediately instead of waiting for init hook
        // since we're already inside the init hook from the main plugin
        self::register_post_types();
        
        add_action('add_meta_boxes', array(__CLASS__, 'add_meta_boxes'));
        add_action('save_post', array(__CLASS__, 'save_meta_boxes'), 10, 2);
        
        // Custom columns - removed coursepackage columns
        add_filter('manage_coursesingle_posts_columns', array(__CLASS__, 'course_columns'));
        add_action('manage_coursesingle_posts_custom_column', array(__CLASS__, 'course_custom_column'), 10, 2);
        
        add_filter('manage_lesson_posts_columns', array(__CLASS__, 'lesson_columns'));
        add_action('manage_lesson_posts_custom_column', array(__CLASS__, 'lesson_custom_column'), 10, 2);
    }
    
    public static function register_post_types() {
        // Note: Course Package is now handled as WooCommerce products with multiple courses
        
        // Register Single Course Post Type
        register_post_type('coursesingle', array(
            'labels' => array(
                'name' => __('단과강의', 'lectus-class-system'),
                'singular_name' => __('단과강의', 'lectus-class-system'),
                'add_new' => __('새 단과강의 추가', 'lectus-class-system'),
                'add_new_item' => __('새 단과강의 추가', 'lectus-class-system'),
                'edit_item' => __('단과강의 편집', 'lectus-class-system'),
                'new_item' => __('새 단과강의', 'lectus-class-system'),
                'view_item' => __('단과강의 보기', 'lectus-class-system'),
                'search_items' => __('단과강의 검색', 'lectus-class-system'),
                'not_found' => __('단과강의를 찾을 수 없습니다', 'lectus-class-system'),
                'not_found_in_trash' => __('휴지통에 단과강의가 없습니다', 'lectus-class-system'),
                'all_items' => __('모든 단과강의', 'lectus-class-system'),
                'menu_name' => __('단과강의', 'lectus-class-system'),
            ),
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => false,
            'query_var' => true,
            'rewrite' => array('slug' => 'course'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
            'show_in_rest' => true,
        ));
        
        // Register Lesson Post Type
        register_post_type('lesson', array(
            'labels' => array(
                'name' => __('레슨', 'lectus-class-system'),
                'singular_name' => __('레슨', 'lectus-class-system'),
                'add_new' => __('새 레슨 추가', 'lectus-class-system'),
                'add_new_item' => __('새 레슨 추가', 'lectus-class-system'),
                'edit_item' => __('레슨 편집', 'lectus-class-system'),
                'new_item' => __('새 레슨', 'lectus-class-system'),
                'view_item' => __('레슨 보기', 'lectus-class-system'),
                'search_items' => __('레슨 검색', 'lectus-class-system'),
                'not_found' => __('레슨을 찾을 수 없습니다', 'lectus-class-system'),
                'not_found_in_trash' => __('휴지통에 레슨이 없습니다', 'lectus-class-system'),
                'all_items' => __('모든 레슨', 'lectus-class-system'),
                'menu_name' => __('레슨', 'lectus-class-system'),
            ),
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => false,
            'query_var' => true,
            'rewrite' => array('slug' => 'lesson'),
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'editor', 'thumbnail'),
            'show_in_rest' => true,
        ));
    }
    
    public static function add_meta_boxes() {
        // Single Course Meta Boxes
        add_meta_box(
            'coursesingle_details',
            __('단과강의 설정', 'lectus-class-system'),
            array(__CLASS__, 'render_course_meta_box'),
            'coursesingle',
            'normal',
            'high'
        );
        
        add_meta_box(
            'coursesingle_lessons',
            __('레슨 관리', 'lectus-class-system'),
            array(__CLASS__, 'render_course_lessons_meta_box'),
            'coursesingle',
            'normal',
            'high'
        );
        
        // Add Instructor Assignment Meta Box
        add_meta_box(
            'coursesingle_instructor',
            __('강사 할당', 'lectus-class-system'),
            array(__CLASS__, 'render_course_instructor_meta_box'),
            'coursesingle',
            'side',
            'high'
        );
        
        // Lesson Meta Boxes
        add_meta_box(
            'lesson_details',
            __('레슨 설정', 'lectus-class-system'),
            array(__CLASS__, 'render_lesson_meta_box'),
            'lesson',
            'normal',
            'high'
        );
    }
    
    public static function render_package_meta_box($post) {
        wp_nonce_field('lectus_save_package_meta', 'lectus_package_meta_nonce');
        
        $max_students = get_post_meta($post->ID, '_max_students', true);
        $access_level = get_post_meta($post->ID, '_access_level', true);
        $price = get_post_meta($post->ID, '_price', true);
        ?>
        <table class="form-table">
            <tr>
                <th><label for="max_students"><?php _e('최대 수강생 수', 'lectus-class-system'); ?></label></th>
                <td>
                    <input type="number" id="max_students" name="max_students" value="<?php echo esc_attr($max_students); ?>" class="regular-text" />
                    <p class="description"><?php _e('0 또는 비워두면 무제한', 'lectus-class-system'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="access_level"><?php _e('접근 레벨', 'lectus-class-system'); ?></label></th>
                <td>
                    <select id="access_level" name="access_level">
                        <option value="public" <?php selected($access_level, 'public'); ?>><?php _e('공개', 'lectus-class-system'); ?></option>
                        <option value="members" <?php selected($access_level, 'members'); ?>><?php _e('회원전용', 'lectus-class-system'); ?></option>
                        <option value="private" <?php selected($access_level, 'private'); ?>><?php _e('비공개', 'lectus-class-system'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="price"><?php _e('가격', 'lectus-class-system'); ?></label></th>
                <td>
                    <input type="number" id="price" name="price" value="<?php echo esc_attr($price); ?>" class="regular-text" step="1000" />
                    <p class="description"><?php _e('WooCommerce 상품과 연동 시 자동 동기화됩니다', 'lectus-class-system'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    public static function render_package_courses_meta_box($post) {
        $selected_courses = get_post_meta($post->ID, '_package_courses', true) ?: array();
        $courses = get_posts(array(
            'post_type' => 'coursesingle',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));
        ?>
        <div class="lectus-courses-selector">
            <p><?php _e('이 패키지에 포함할 단과강의를 선택하세요:', 'lectus-class-system'); ?></p>
            <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
                <?php foreach ($courses as $course): ?>
                    <label style="display: block; margin-bottom: 5px;">
                        <input type="checkbox" name="package_courses[]" value="<?php echo $course->ID; ?>" 
                               <?php checked(in_array($course->ID, $selected_courses)); ?>>
                        <?php echo esc_html($course->post_title); ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
    
    public static function render_course_meta_box($post) {
        wp_nonce_field('lectus_save_course_meta', 'lectus_course_meta_nonce');
        
        $package_id = get_post_meta($post->ID, '_package_id', true);
        $duration = get_post_meta($post->ID, '_course_duration', true);
        $access_mode = get_post_meta($post->ID, '_access_mode', true);
        $completion_score = get_post_meta($post->ID, '_completion_score', true);
        $certificate_enabled = get_post_meta($post->ID, '_certificate_enabled', true);
        
        $packages = get_posts(array(
            'post_type' => 'coursepackage',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));
        ?>
        <table class="form-table">
            <tr>
                <th><label for="package_id"><?php _e('소속 패키지강의', 'lectus-class-system'); ?></label></th>
                <td>
                    <select id="package_id" name="package_id">
                        <option value=""><?php _e('선택하세요', 'lectus-class-system'); ?></option>
                        <?php foreach ($packages as $package): ?>
                            <option value="<?php echo $package->ID; ?>" <?php selected($package_id, $package->ID); ?>>
                                <?php echo esc_html($package->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="course_duration"><?php _e('수강 기간', 'lectus-class-system'); ?></label></th>
                <td>
                    <input type="number" id="course_duration" name="course_duration" value="<?php echo esc_attr($duration); ?>" class="small-text" />
                    <span><?php _e('일 (0 또는 비워두면 무제한)', 'lectus-class-system'); ?></span>
                </td>
            </tr>
            <tr>
                <th><label for="access_mode"><?php _e('접근 모드', 'lectus-class-system'); ?></label></th>
                <td>
                    <select id="access_mode" name="access_mode">
                        <option value="free" <?php selected($access_mode, 'free'); ?>><?php _e('자유 진행', 'lectus-class-system'); ?></option>
                        <option value="sequential" <?php selected($access_mode, 'sequential'); ?>><?php _e('순차적 진행', 'lectus-class-system'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="completion_score"><?php _e('수료 기준 점수', 'lectus-class-system'); ?></label></th>
                <td>
                    <input type="number" id="completion_score" name="completion_score" value="<?php echo esc_attr($completion_score ?: 80); ?>" class="small-text" min="0" max="100" />
                    <span>%</span>
                </td>
            </tr>
            <tr>
                <th><label for="certificate_enabled"><?php _e('수료증 발급', 'lectus-class-system'); ?></label></th>
                <td>
                    <label>
                        <input type="checkbox" id="certificate_enabled" name="certificate_enabled" value="1" <?php checked($certificate_enabled, '1'); ?> />
                        <?php _e('이 단과강의 완료 시 수료증 발급', 'lectus-class-system'); ?>
                    </label>
                </td>
            </tr>
        </table>
        <?php
    }
    
    public static function render_course_instructor_meta_box($post) {
        $instructor_id = get_post_meta($post->ID, '_course_instructor_id', true);
        
        // Get all users with instructor or administrator role
        $instructors = get_users(array(
            'role__in' => array('administrator', 'lectus_instructor'),
            'orderby' => 'display_name',
            'order' => 'ASC'
        ));
        ?>
        <div class="lectus-instructor-selector">
            <p>
                <label for="course_instructor_id"><?php _e('강사 선택:', 'lectus-class-system'); ?></label>
            </p>
            <select id="course_instructor_id" name="course_instructor_id" style="width: 100%;">
                <option value=""><?php _e('-- 강사 없음 --', 'lectus-class-system'); ?></option>
                <?php foreach ($instructors as $instructor): ?>
                    <option value="<?php echo $instructor->ID; ?>" <?php selected($instructor_id, $instructor->ID); ?>>
                        <?php echo esc_html($instructor->display_name); ?> (<?php echo esc_html($instructor->user_email); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="description">
                <?php _e('이 강의의 강사를 선택하세요. 강사는 이 강의의 Q&A를 관리할 수 있습니다.', 'lectus-class-system'); ?>
            </p>
        </div>
        <?php
    }
    
    public static function render_course_lessons_meta_box($post) {
        // Use new unified items system
        require_once LECTUS_PLUGIN_DIR . 'includes/class-lectus-course-items.php';
        $items = Lectus_Course_Items::get_course_items($post->ID);
        ?>
        <div class="lectus-course-items-manager">
            <style>
                .course-items-list {
                    margin: 20px 0;
                }
                .course-item {
                    display: flex;
                    align-items: center;
                    padding: 10px;
                    margin: 5px 0;
                    background: white;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    cursor: move;
                }
                .course-item.section {
                    background: #f0f8ff;
                    border-left: 4px solid #0073aa;
                    font-weight: bold;
                }
                .course-item.lesson {
                    margin-left: 20px;
                    border-left: 4px solid #28a745;
                }
                .course-item.dragging {
                    opacity: 0.5;
                }
                .course-item .item-handle {
                    cursor: move;
                    margin-right: 10px;
                    color: #999;
                }
                .course-item .item-title {
                    flex-grow: 1;
                }
                .course-item .item-actions {
                    display: flex;
                    gap: 5px;
                }
                .course-item .item-meta {
                    color: #666;
                    font-size: 0.9em;
                    margin-left: 10px;
                }
                .add-section-inline {
                    display: none;
                    padding: 15px;
                    margin: 10px 0;
                    background: #f9f9f9;
                    border: 1px dashed #999;
                    border-radius: 4px;
                }
                .add-section-inline.active {
                    display: block;
                }
                .drop-zone {
                    height: 40px;
                    margin: 5px 0;
                    border: 2px dashed transparent;
                    transition: all 0.3s;
                }
                .drop-zone.active {
                    border-color: #0073aa;
                    background: #e3f2fd;
                }
            </style>
            
            <div class="toolbar" style="margin-bottom: 20px;">
                <a href="<?php echo admin_url('post-new.php?post_type=lesson&course_id=' . $post->ID); ?>" class="button button-primary">
                    <?php _e('새 레슨 추가', 'lectus-class-system'); ?>
                </a>
                <button type="button" class="button add-section-btn">
                    <?php _e('섹션 구분선 추가', 'lectus-class-system'); ?>
                </button>
                <button type="button" class="button" onclick="lectusShowBulkUpload()">
                    <?php _e('CSV 벌크 업로드', 'lectus-class-system'); ?>
                </button>
                <span style="margin-left: 20px; color: #666;">
                    <?php _e('드래그하여 순서를 변경할 수 있습니다', 'lectus-class-system'); ?>
                </span>
            </div>
            
            <div class="add-section-inline">
                <h4><?php _e('새 섹션 추가', 'lectus-class-system'); ?></h4>
                <input type="text" class="section-title-input regular-text" placeholder="<?php _e('섹션 제목', 'lectus-class-system'); ?>" />
                <input type="text" class="section-desc-input regular-text" placeholder="<?php _e('섹션 설명 (선택)', 'lectus-class-system'); ?>" style="margin-left: 10px;" />
                <button type="button" class="button button-primary save-section-btn" style="margin-left: 10px;">
                    <?php _e('저장', 'lectus-class-system'); ?>
                </button>
                <button type="button" class="button cancel-section-btn">
                    <?php _e('취소', 'lectus-class-system'); ?>
                </button>
            </div>
            
            <div class="course-items-list" id="course-items-list">
                <?php 
                $current_section = null;
                $section_lesson_index = 0;
                
                foreach ($items as $item): 
                ?>
                    <?php if ($item['type'] === 'section'): 
                        // Reset lesson index for new section
                        $section_lesson_index = 0;
                    ?>
                        <div class="course-item section" data-type="section" data-id="<?php echo $item['id']; ?>" data-order="<?php echo $item['display_order']; ?>">
                            <span class="item-handle">☰</span>
                            <span class="item-title">
                                📁 <?php echo esc_html($item['title']); ?>
                                <?php if (!empty($item['description'])): ?>
                                    <span class="item-meta">(<?php echo esc_html($item['description']); ?>)</span>
                                <?php endif; ?>
                            </span>
                            <div class="item-actions">
                                <button type="button" class="button button-small edit-section-btn" 
                                        data-id="<?php echo $item['id']; ?>"
                                        data-title="<?php echo esc_attr($item['title']); ?>"
                                        data-description="<?php echo esc_attr($item['description'] ?? ''); ?>">
                                    <?php _e('편집', 'lectus-class-system'); ?>
                                </button>
                                <button type="button" class="button button-small delete-section-btn" 
                                        data-id="<?php echo $item['id']; ?>">
                                    <?php _e('삭제', 'lectus-class-system'); ?>
                                </button>
                            </div>
                        </div>
                    <?php elseif ($item['type'] === 'lesson'): 
                        $section_lesson_index++;
                    ?>
                        <div class="course-item lesson" data-type="lesson" data-id="<?php echo $item['id']; ?>" data-order="<?php echo $item['display_order']; ?>">
                            <span class="item-handle">☰</span>
                            <span class="item-title">
                                <?php echo $section_lesson_index; ?>. <?php echo esc_html($item['title']); ?>
                                <span class="item-meta">
                                    (<?php echo esc_html($item['lesson_type'] ?? 'text'); ?>, 
                                    <?php echo esc_html($item['duration'] ?? '0'); ?>분)
                                </span>
                            </span>
                            <div class="item-actions">
                                <a href="<?php echo esc_url($item['edit_url']); ?>" class="button button-small">
                                    <?php _e('편집', 'lectus-class-system'); ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                
                <?php if (empty($items)): ?>
                    <p style="padding: 20px; text-align: center; color: #999;">
                        <?php _e('아직 레슨이나 섹션이 없습니다.', 'lectus-class-system'); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            var courseId = <?php echo $post->ID; ?>;
            var isDragging = false;
            
            // Make items sortable
            $('#course-items-list').sortable({
                handle: '.item-handle',
                placeholder: 'drop-zone active',
                start: function(e, ui) {
                    isDragging = true;
                    ui.placeholder.height(ui.item.height());
                },
                stop: function(e, ui) {
                    isDragging = false;
                    
                    // Get new order
                    var items = [];
                    $('#course-items-list .course-item').each(function() {
                        items.push({
                            type: $(this).data('type'),
                            id: $(this).data('id')
                        });
                    });
                    
                    // Save new order
                    $.ajax({
                        url: lectus_ajax.ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'lectus_reorder_course_items',
                            nonce: lectus_ajax.nonce,
                            items: items
                        },
                        success: function(response) {
                            if (response.success) {
                                console.log('Order saved');
                            }
                        }
                    });
                }
            });
            
            // Add section button
            $('.add-section-btn').on('click', function() {
                $('.add-section-inline').addClass('active');
                $('.section-title-input').focus();
            });
            
            // Cancel section
            $('.cancel-section-btn').on('click', function() {
                $('.add-section-inline').removeClass('active');
                $('.section-title-input').val('');
                $('.section-desc-input').val('');
            });
            
            // Save section
            $('.save-section-btn').on('click', function() {
                var title = $('.section-title-input').val().trim();
                var description = $('.section-desc-input').val().trim();
                
                if (!title) {
                    alert('섹션 제목을 입력해주세요.');
                    return;
                }
                
                $.ajax({
                    url: lectus_ajax.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'lectus_add_section_item',
                        nonce: lectus_ajax.nonce,
                        course_id: courseId,
                        title: title,
                        description: description
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.data.message || '섹션 추가에 실패했습니다.');
                        }
                    }
                });
            });
            
            // Edit section
            $(document).on('click', '.edit-section-btn', function() {
                var id = $(this).data('id');
                var title = $(this).data('title');
                var description = $(this).data('description');
                
                var newTitle = prompt('섹션 제목:', title);
                if (newTitle && newTitle !== title) {
                    var newDesc = prompt('섹션 설명 (선택):', description || '');
                    
                    $.ajax({
                        url: lectus_ajax.ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'lectus_update_section_item',
                            nonce: lectus_ajax.nonce,
                            section_id: id,
                            title: newTitle,
                            description: newDesc || ''
                        },
                        success: function(response) {
                            if (response.success) {
                                location.reload();
                            } else {
                                alert(response.data.message || '섹션 수정에 실패했습니다.');
                            }
                        }
                    });
                }
            });
            
            // Delete section
            $(document).on('click', '.delete-section-btn', function() {
                if (!confirm('이 섹션을 삭제하시겠습니까?')) {
                    return;
                }
                
                var id = $(this).data('id');
                
                $.ajax({
                    url: lectus_ajax.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'lectus_remove_section_item',
                        nonce: lectus_ajax.nonce,
                        section_id: id
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.data.message || '섹션 삭제에 실패했습니다.');
                        }
                    }
                });
            });
        });
        
        function lectusShowBulkUpload() {
            alert('CSV 벌크 업로드 기능은 곧 추가됩니다.');
        }
        </script>
        <?php
    }
    
    public static function render_course_lessons_meta_box_old($post) {
        // Get sections for this course
        $sections = Lectus_Sections::get_course_sections($post->ID);
        
        // Get unsectioned lessons
        $unsectioned_lessons = Lectus_Sections::get_unsectioned_lessons($post->ID);
        
        // Fallback: If no lessons found through sections method, get all lessons for this course
        if (empty($sections) && empty($unsectioned_lessons)) {
            $all_lessons = get_posts(array(
                'post_type' => 'lesson',
                'meta_key' => '_course_id',
                'meta_value' => $post->ID,
                'posts_per_page' => -1,
                'orderby' => 'menu_order',
                'order' => 'ASC'
            ));
            
            // If we found lessons this way, they are all unsectioned
            if (!empty($all_lessons)) {
                $unsectioned_lessons = $all_lessons;
            }
        }
        ?>
        <div class="lectus-lessons-manager">
            <div style="margin-bottom: 10px;">
                <a href="<?php echo admin_url('post-new.php?post_type=lesson&course_id=' . $post->ID); ?>" class="button button-primary">
                    <?php _e('새 레슨 추가', 'lectus-class-system'); ?>
                </a>
                <button type="button" class="button" onclick="lectusShowBulkUpload()">
                    <?php _e('CSV 벌크 업로드', 'lectus-class-system'); ?>
                </button>
                <button type="button" class="button button-secondary" id="add-section-btn">
                    <?php _e('섹션 추가', 'lectus-class-system'); ?>
                </button>
            </div>
            
            <!-- Section Add Form (Hidden by default) -->
            <div id="add-section-form" style="display:none; margin-bottom: 20px; padding: 15px; background: #f0f0f0; border: 1px solid #ddd;">
                <h4><?php _e('새 섹션 추가', 'lectus-class-system'); ?></h4>
                <table class="form-table">
                    <tr>
                        <th><label for="section-title"><?php _e('섹션 제목', 'lectus-class-system'); ?></label></th>
                        <td>
                            <input type="text" id="section-title" class="regular-text" placeholder="<?php _e('섹션 제목을 입력하세요', 'lectus-class-system'); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th><label for="section-description"><?php _e('설명 (선택)', 'lectus-class-system'); ?></label></th>
                        <td>
                            <textarea id="section-description" rows="3" class="large-text" placeholder="<?php _e('섹션 설명을 입력하세요', 'lectus-class-system'); ?>"></textarea>
                        </td>
                    </tr>
                </table>
                <button type="button" class="button button-primary" id="save-section-btn">
                    <?php _e('섹션 저장', 'lectus-class-system'); ?>
                </button>
                <button type="button" class="button" id="cancel-section-btn">
                    <?php _e('취소', 'lectus-class-system'); ?>
                </button>
            </div>
            
            <!-- Sections and Lessons Display -->
            <div id="sections-container" style="margin-top: 20px;">
                <?php 
                $lesson_counter = 1;
                
                // Display sections and their lessons
                if (!empty($sections)): 
                    foreach ($sections as $section): 
                        $section_lessons = Lectus_Sections::get_section_lessons($section->id);
                ?>
                    <div class="section-wrapper" data-section-id="<?php echo $section->id; ?>" style="margin-bottom: 30px; border: 1px solid #ddd; padding: 15px; background: #fff;">
                        <div class="section-header" style="margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #eee;">
                            <h3 style="margin: 0; display: inline-block;">
                                <?php echo esc_html($section->title); ?>
                            </h3>
                            <span class="section-actions" style="float: right;">
                                <button type="button" class="button button-small edit-section-btn" data-section-id="<?php echo $section->id; ?>" data-title="<?php echo esc_attr($section->title); ?>" data-description="<?php echo esc_attr($section->description); ?>">
                                    <?php _e('편집', 'lectus-class-system'); ?>
                                </button>
                                <button type="button" class="button button-small delete-section-btn" data-section-id="<?php echo $section->id; ?>" style="color: #a00;">
                                    <?php _e('삭제', 'lectus-class-system'); ?>
                                </button>
                            </span>
                            <div style="clear: both;"></div>
                            <?php if (!empty($section->description)): ?>
                                <p style="margin: 5px 0 0; color: #666;"><?php echo esc_html($section->description); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($section_lessons)): ?>
                            <table class="wp-list-table widefat fixed striped">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;"><?php _e('순서', 'lectus-class-system'); ?></th>
                                        <th><?php _e('레슨 제목', 'lectus-class-system'); ?></th>
                                        <th style="width: 120px;"><?php _e('타입', 'lectus-class-system'); ?></th>
                                        <th style="width: 100px;"><?php _e('소요 시간', 'lectus-class-system'); ?></th>
                                        <th style="width: 150px;"><?php _e('작업', 'lectus-class-system'); ?></th>
                                    </tr>
                                </thead>
                                <tbody class="section-lessons" data-section-id="<?php echo $section->id; ?>">
                                    <?php foreach ($section_lessons as $lesson): 
                                        $lesson_type = get_post_meta($lesson->ID, '_lesson_type', true);
                                        $duration = get_post_meta($lesson->ID, '_lesson_duration', true);
                                    ?>
                                        <tr class="lesson-row" data-lesson-id="<?php echo $lesson->ID; ?>">
                                            <td><?php echo $lesson_counter++; ?></td>
                                            <td>
                                                <strong>
                                                    <a href="<?php echo get_edit_post_link($lesson->ID); ?>">
                                                        <?php echo esc_html($lesson->post_title); ?>
                                                    </a>
                                                </strong>
                                            </td>
                                            <td><?php echo esc_html($lesson_type); ?></td>
                                            <td><?php echo esc_html($duration); ?> <?php _e('분', 'lectus-class-system'); ?></td>
                                            <td>
                                                <a href="<?php echo get_edit_post_link($lesson->ID); ?>" class="button button-small">
                                                    <?php _e('편집', 'lectus-class-system'); ?>
                                                </a>
                                                <button type="button" class="button button-small remove-from-section-btn" data-lesson-id="<?php echo $lesson->ID; ?>">
                                                    <?php _e('섹션 제거', 'lectus-class-system'); ?>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p style="padding: 10px; background: #f9f9f9;"><?php _e('이 섹션에 레슨이 없습니다.', 'lectus-class-system'); ?></p>
                        <?php endif; ?>
                    </div>
                <?php 
                    endforeach;
                endif; 
                ?>
                
                <!-- Unsectioned Lessons -->
                <?php if (!empty($unsectioned_lessons)): ?>
                    <div class="unsectioned-wrapper" style="margin-bottom: 30px; border: 1px solid #ddd; padding: 15px; background: #f9f9f9;">
                        <div class="section-header" style="margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #ddd;">
                            <h3 style="margin: 0; color: #666;">
                                <?php _e('섹션 미지정 레슨', 'lectus-class-system'); ?>
                            </h3>
                        </div>
                        
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th style="width: 50px;"><?php _e('순서', 'lectus-class-system'); ?></th>
                                    <th><?php _e('레슨 제목', 'lectus-class-system'); ?></th>
                                    <th style="width: 120px;"><?php _e('타입', 'lectus-class-system'); ?></th>
                                    <th style="width: 100px;"><?php _e('소요 시간', 'lectus-class-system'); ?></th>
                                    <th style="width: 200px;"><?php _e('작업', 'lectus-class-system'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="unsectioned-lessons">
                                <?php foreach ($unsectioned_lessons as $lesson): 
                                    $lesson_type = get_post_meta($lesson->ID, '_lesson_type', true);
                                    $duration = get_post_meta($lesson->ID, '_lesson_duration', true);
                                ?>
                                    <tr class="lesson-row" data-lesson-id="<?php echo $lesson->ID; ?>">
                                        <td><?php echo $lesson_counter++; ?></td>
                                        <td>
                                            <strong>
                                                <a href="<?php echo get_edit_post_link($lesson->ID); ?>">
                                                    <?php echo esc_html($lesson->post_title); ?>
                                                </a>
                                            </strong>
                                        </td>
                                        <td><?php echo esc_html($lesson_type); ?></td>
                                        <td><?php echo esc_html($duration); ?> <?php _e('분', 'lectus-class-system'); ?></td>
                                        <td>
                                            <a href="<?php echo get_edit_post_link($lesson->ID); ?>" class="button button-small">
                                                <?php _e('편집', 'lectus-class-system'); ?>
                                            </a>
                                            <select class="assign-to-section" data-lesson-id="<?php echo $lesson->ID; ?>" style="margin-left: 5px;">
                                                <option value=""><?php _e('섹션 선택...', 'lectus-class-system'); ?></option>
                                                <?php foreach ($sections as $section): ?>
                                                    <option value="<?php echo $section->id; ?>"><?php echo esc_html($section->title); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                
                <?php if (empty($sections) && empty($unsectioned_lessons)): ?>
                    <p><?php _e('아직 섹션이나 레슨이 없습니다.', 'lectus-class-system'); ?></p>
                    
                    <!-- Debug information -->
                    <?php if (defined('WP_DEBUG') && WP_DEBUG): ?>
                        <div style="margin-top: 10px; padding: 10px; background: #f0f0f0; border: 1px solid #ccc; font-size: 12px;">
                            <strong>디버그 정보:</strong><br>
                            코스 ID: <?php echo $post->ID; ?><br>
                            섹션 수: <?php echo count($sections); ?><br>
                            미지정 레슨 수: <?php echo count($unsectioned_lessons); ?><br>
                            
                            <?php
                            // Check all lessons for this course
                            $debug_lessons = get_posts(array(
                                'post_type' => 'lesson',
                                'meta_key' => '_course_id',
                                'meta_value' => $post->ID,
                                'posts_per_page' => -1
                            ));
                            ?>
                            전체 레슨 수 (직접 조회): <?php echo count($debug_lessons); ?><br>
                            
                            <?php if (!empty($debug_lessons)): ?>
                                레슨 목록:<br>
                                <?php foreach ($debug_lessons as $lesson): ?>
                                    - <?php echo $lesson->post_title; ?> (ID: <?php echo $lesson->ID; ?>)<br>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <script>
        function lectusShowBulkUpload() {
            // Bulk upload functionality - redirect to bulk upload page
            alert('CSV 벌크 업로드 기능은 곧 추가됩니다.');
        }
        
        jQuery(document).ready(function($) {
            var courseId = <?php echo $post->ID; ?>;
            
            // Toggle section add form
            $('#add-section-btn').on('click', function() {
                $('#add-section-form').slideToggle();
                $('#section-title').focus();
            });
            
            $('#cancel-section-btn').on('click', function() {
                $('#add-section-form').slideUp();
                $('#section-title').val('');
                $('#section-description').val('');
            });
            
            // Save new section
            $('#save-section-btn').on('click', function() {
                var title = $('#section-title').val().trim();
                var description = $('#section-description').val().trim();
                
                if (!title) {
                    alert('섹션 제목을 입력해주세요.');
                    return;
                }
                
                $.ajax({
                    url: lectus_ajax.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'lectus_add_section',
                        nonce: lectus_ajax.nonce,
                        course_id: courseId,
                        title: title,
                        description: description
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            location.reload(); // Reload to show new section
                        } else {
                            alert(response.data.message || '섹션 추가에 실패했습니다.');
                        }
                    },
                    error: function() {
                        alert('서버 오류가 발생했습니다.');
                    }
                });
            });
            
            // Edit section
            $('.edit-section-btn').on('click', function() {
                var sectionId = $(this).data('section-id');
                var title = $(this).data('title');
                var description = $(this).data('description');
                
                var newTitle = prompt('섹션 제목을 수정하세요:', title);
                if (newTitle && newTitle !== title) {
                    var newDescription = prompt('섹션 설명을 수정하세요 (선택):', description || '');
                    
                    $.ajax({
                        url: lectus_ajax.ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'lectus_update_section',
                            nonce: lectus_ajax.nonce,
                            section_id: sectionId,
                            title: newTitle,
                            description: newDescription || ''
                        },
                        success: function(response) {
                            if (response.success) {
                                alert(response.data.message);
                                location.reload();
                            } else {
                                alert(response.data.message || '섹션 수정에 실패했습니다.');
                            }
                        }
                    });
                }
            });
            
            // Delete section
            $('.delete-section-btn').on('click', function() {
                if (!confirm('이 섹션을 삭제하시겠습니까? 섹션에 속한 레슨은 미지정 상태가 됩니다.')) {
                    return;
                }
                
                var sectionId = $(this).data('section-id');
                
                $.ajax({
                    url: lectus_ajax.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'lectus_delete_section',
                        nonce: lectus_ajax.nonce,
                        section_id: sectionId
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            location.reload();
                        } else {
                            alert(response.data.message || '섹션 삭제에 실패했습니다.');
                        }
                    }
                });
            });
            
            // Assign lesson to section
            $('.assign-to-section').on('change', function() {
                var lessonId = $(this).data('lesson-id');
                var sectionId = $(this).val();
                
                if (sectionId) {
                    $.ajax({
                        url: lectus_ajax.ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'lectus_assign_lesson_to_section',
                            nonce: lectus_ajax.nonce,
                            lesson_id: lessonId,
                            section_id: sectionId
                        },
                        success: function(response) {
                            if (response.success) {
                                alert(response.data.message);
                                location.reload();
                            } else {
                                alert(response.data.message || '레슨 할당에 실패했습니다.');
                            }
                        }
                    });
                }
            });
            
            // Remove lesson from section
            $('.remove-from-section-btn').on('click', function() {
                if (!confirm('이 레슨을 섹션에서 제거하시겠습니까?')) {
                    return;
                }
                
                var lessonId = $(this).data('lesson-id');
                
                $.ajax({
                    url: lectus_ajax.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'lectus_assign_lesson_to_section',
                        nonce: lectus_ajax.nonce,
                        lesson_id: lessonId,
                        section_id: 0 // 0 means remove from section
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('레슨이 섹션에서 제거되었습니다.');
                            location.reload();
                        } else {
                            alert(response.data.message || '레슨 제거에 실패했습니다.');
                        }
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    public static function render_lesson_meta_box($post) {
        wp_nonce_field('lectus_save_lesson_meta', 'lectus_lesson_meta_nonce');
        
        $course_id = get_post_meta($post->ID, '_course_id', true);
        $lesson_type = get_post_meta($post->ID, '_lesson_type', true);
        $duration = get_post_meta($post->ID, '_lesson_duration', true);
        $video_url = get_post_meta($post->ID, '_video_url', true);
        $completion_criteria = get_post_meta($post->ID, '_completion_criteria', true);
        
        // Get course_id from URL if creating new lesson
        if (!$course_id && isset($_GET['course_id'])) {
            $course_id = intval($_GET['course_id']);
        }
        
        $courses = get_posts(array(
            'post_type' => 'coursesingle',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));
        ?>
        <table class="form-table">
            <tr>
                <th><label for="course_id"><?php _e('소속 단과강의', 'lectus-class-system'); ?></label></th>
                <td>
                    <select id="course_id" name="course_id" required>
                        <option value=""><?php _e('선택하세요', 'lectus-class-system'); ?></option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course->ID; ?>" <?php selected($course_id, $course->ID); ?>>
                                <?php echo esc_html($course->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="lesson_type"><?php _e('레슨 타입', 'lectus-class-system'); ?></label></th>
                <td>
                    <select id="lesson_type" name="lesson_type">
                        <option value="text" <?php selected($lesson_type, 'text'); ?>><?php _e('텍스트', 'lectus-class-system'); ?></option>
                        <option value="video" <?php selected($lesson_type, 'video'); ?>><?php _e('동영상', 'lectus-class-system'); ?></option>
                        <option value="quiz" <?php selected($lesson_type, 'quiz'); ?>><?php _e('퀴즈', 'lectus-class-system'); ?></option>
                        <option value="assignment" <?php selected($lesson_type, 'assignment'); ?>><?php _e('과제', 'lectus-class-system'); ?></option>
                    </select>
                </td>
            </tr>
            <tr class="video-url-row" <?php echo ($lesson_type !== 'video') ? 'style="display:none;"' : ''; ?>>
                <th><label for="video_url"><?php _e('동영상 URL', 'lectus-class-system'); ?></label></th>
                <td>
                    <input type="url" id="video_url" name="video_url" value="<?php echo esc_attr($video_url); ?>" class="large-text" />
                    <p class="description"><?php _e('YouTube, Vimeo 또는 직접 업로드한 동영상 URL', 'lectus-class-system'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="lesson_duration"><?php _e('예상 소요 시간', 'lectus-class-system'); ?></label></th>
                <td>
                    <input type="number" id="lesson_duration" name="lesson_duration" value="<?php echo esc_attr($duration); ?>" class="small-text" />
                    <span><?php _e('분', 'lectus-class-system'); ?></span>
                </td>
            </tr>
            <tr>
                <th><label for="completion_criteria"><?php _e('완료 기준', 'lectus-class-system'); ?></label></th>
                <td>
                    <select id="completion_criteria" name="completion_criteria">
                        <option value="view" <?php selected($completion_criteria, 'view'); ?>><?php _e('콘텐츠 열람', 'lectus-class-system'); ?></option>
                        <option value="time" <?php selected($completion_criteria, 'time'); ?>><?php _e('최소 시간 체류', 'lectus-class-system'); ?></option>
                        <option value="manual" <?php selected($completion_criteria, 'manual'); ?>><?php _e('수동 완료 처리', 'lectus-class-system'); ?></option>
                    </select>
                </td>
            </tr>
        </table>
        
        <script>
        jQuery(document).ready(function($) {
            $('#lesson_type').on('change', function() {
                if ($(this).val() === 'video') {
                    $('.video-url-row').show();
                } else {
                    $('.video-url-row').hide();
                }
            });
        });
        </script>
        <?php
    }
    
    public static function save_meta_boxes($post_id, $post) {
        // Check if our nonce is set and verify it
        if ($post->post_type === 'coursepackage') {
            if (!isset($_POST['lectus_package_meta_nonce']) || 
                !wp_verify_nonce($_POST['lectus_package_meta_nonce'], 'lectus_save_package_meta')) {
                return;
            }
            
            // Save package meta
            if (isset($_POST['max_students'])) {
                update_post_meta($post_id, '_max_students', sanitize_text_field($_POST['max_students']));
            }
            if (isset($_POST['access_level'])) {
                update_post_meta($post_id, '_access_level', sanitize_text_field($_POST['access_level']));
            }
            if (isset($_POST['price'])) {
                update_post_meta($post_id, '_price', sanitize_text_field($_POST['price']));
            }
            if (isset($_POST['package_courses'])) {
                update_post_meta($post_id, '_package_courses', array_map('intval', $_POST['package_courses']));
            } else {
                update_post_meta($post_id, '_package_courses', array());
            }
        }
        
        if ($post->post_type === 'coursesingle') {
            if (!isset($_POST['lectus_course_meta_nonce']) || 
                !wp_verify_nonce($_POST['lectus_course_meta_nonce'], 'lectus_save_course_meta')) {
                return;
            }
            
            // Save course meta
            if (isset($_POST['package_id'])) {
                update_post_meta($post_id, '_package_id', sanitize_text_field($_POST['package_id']));
            }
            if (isset($_POST['course_duration'])) {
                update_post_meta($post_id, '_course_duration', sanitize_text_field($_POST['course_duration']));
            }
            if (isset($_POST['access_mode'])) {
                update_post_meta($post_id, '_access_mode', sanitize_text_field($_POST['access_mode']));
            }
            if (isset($_POST['completion_score'])) {
                update_post_meta($post_id, '_completion_score', sanitize_text_field($_POST['completion_score']));
            }
            update_post_meta($post_id, '_certificate_enabled', isset($_POST['certificate_enabled']) ? '1' : '0');
            
            // Save instructor assignment
            if (isset($_POST['course_instructor_id'])) {
                update_post_meta($post_id, '_course_instructor_id', absint($_POST['course_instructor_id']));
            }
        }
        
        if ($post->post_type === 'lesson') {
            if (!isset($_POST['lectus_lesson_meta_nonce']) || 
                !wp_verify_nonce($_POST['lectus_lesson_meta_nonce'], 'lectus_save_lesson_meta')) {
                return;
            }
            
            // Save lesson meta
            if (isset($_POST['course_id'])) {
                update_post_meta($post_id, '_course_id', sanitize_text_field($_POST['course_id']));
            }
            if (isset($_POST['lesson_type'])) {
                update_post_meta($post_id, '_lesson_type', sanitize_text_field($_POST['lesson_type']));
            }
            if (isset($_POST['lesson_duration'])) {
                update_post_meta($post_id, '_lesson_duration', sanitize_text_field($_POST['lesson_duration']));
            }
            if (isset($_POST['video_url'])) {
                update_post_meta($post_id, '_video_url', esc_url_raw($_POST['video_url']));
            }
            if (isset($_POST['completion_criteria'])) {
                update_post_meta($post_id, '_completion_criteria', sanitize_text_field($_POST['completion_criteria']));
            }
        }
    }
    
    // Custom columns for admin list tables
    public static function package_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['courses_count'] = __('포함 단과강의', 'lectus-class-system');
        $new_columns['max_students'] = __('최대 수강생', 'lectus-class-system');
        $new_columns['access_level'] = __('접근 레벨', 'lectus-class-system');
        $new_columns['date'] = $columns['date'];
        return $new_columns;
    }
    
    public static function package_custom_column($column, $post_id) {
        switch ($column) {
            case 'courses_count':
                $courses = get_post_meta($post_id, '_package_courses', true);
                echo is_array($courses) ? count($courses) : 0;
                break;
            case 'max_students':
                $max = get_post_meta($post_id, '_max_students', true);
                echo $max ? $max : __('무제한', 'lectus-class-system');
                break;
            case 'access_level':
                $level = get_post_meta($post_id, '_access_level', true);
                $levels = array(
                    'public' => __('공개', 'lectus-class-system'),
                    'members' => __('회원전용', 'lectus-class-system'),
                    'private' => __('비공개', 'lectus-class-system')
                );
                echo isset($levels[$level]) ? $levels[$level] : '-';
                break;
        }
    }
    
    public static function course_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['package'] = __('패키지', 'lectus-class-system');
        $new_columns['instructor'] = __('강사', 'lectus-class-system');
        $new_columns['lessons_count'] = __('레슨 수', 'lectus-class-system');
        $new_columns['duration'] = __('수강 기간', 'lectus-class-system');
        $new_columns['enrolled'] = __('수강생', 'lectus-class-system');
        $new_columns['date'] = $columns['date'];
        return $new_columns;
    }
    
    public static function course_custom_column($column, $post_id) {
        switch ($column) {
            case 'package':
                $package_id = get_post_meta($post_id, '_package_id', true);
                if ($package_id) {
                    $package = get_post($package_id);
                    if ($package) {
                        echo '<a href="' . get_edit_post_link($package_id) . '">' . esc_html($package->post_title) . '</a>';
                    }
                } else {
                    echo '-';
                }
                break;
            case 'lessons_count':
                $lessons = get_posts(array(
                    'post_type' => 'lesson',
                    'meta_key' => '_course_id',
                    'meta_value' => $post_id,
                    'posts_per_page' => -1
                ));
                echo count($lessons);
                break;
            case 'instructor':
                $instructor_id = get_post_meta($post_id, '_course_instructor_id', true);
                if ($instructor_id) {
                    $instructor = get_user_by('id', $instructor_id);
                    if ($instructor) {
                        echo esc_html($instructor->display_name);
                    } else {
                        echo __('미할당', 'lectus-class-system');
                    }
                } else {
                    echo __('미할당', 'lectus-class-system');
                }
                break;
            case 'duration':
                $duration = get_post_meta($post_id, '_course_duration', true);
                echo $duration ? $duration . __('일', 'lectus-class-system') : __('무제한', 'lectus-class-system');
                break;
            case 'enrolled':
                global $wpdb;
                $table = $wpdb->prefix . 'lectus_enrollment';
                $count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table WHERE course_id = %d AND status = 'active'",
                    $post_id
                ));
                echo $count ?: 0;
                break;
        }
    }
    
    public static function lesson_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['course'] = __('단과강의', 'lectus-class-system');
        $new_columns['type'] = __('타입', 'lectus-class-system');
        $new_columns['duration'] = __('소요 시간', 'lectus-class-system');
        $new_columns['date'] = $columns['date'];
        return $new_columns;
    }
    
    public static function lesson_custom_column($column, $post_id) {
        switch ($column) {
            case 'course':
                $course_id = get_post_meta($post_id, '_course_id', true);
                if ($course_id) {
                    $course = get_post($course_id);
                    if ($course) {
                        echo '<a href="' . get_edit_post_link($course_id) . '">' . esc_html($course->post_title) . '</a>';
                    }
                } else {
                    echo '-';
                }
                break;
            case 'type':
                $type = get_post_meta($post_id, '_lesson_type', true);
                $types = array(
                    'text' => __('텍스트', 'lectus-class-system'),
                    'video' => __('동영상', 'lectus-class-system'),
                    'quiz' => __('퀴즈', 'lectus-class-system'),
                    'assignment' => __('과제', 'lectus-class-system')
                );
                echo isset($types[$type]) ? $types[$type] : '-';
                break;
            case 'duration':
                $duration = get_post_meta($post_id, '_lesson_duration', true);
                echo $duration ? $duration . __('분', 'lectus-class-system') : '-';
                break;
        }
    }
}