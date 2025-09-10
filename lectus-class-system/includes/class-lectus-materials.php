<?php
/**
 * Course Materials Management for Lectus Class System
 * 
 * Handles file uploads, downloads, and management for course materials
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Materials {
    
    /**
     * Initialize the materials system
     */
    public static function init() {
        // Create database table on init - Force check on every load to ensure table exists
        self::maybe_create_table();
        
        // AJAX handlers
        add_action('wp_ajax_lectus_upload_material', array(__CLASS__, 'ajax_upload_material'));
        add_action('wp_ajax_lectus_delete_material', array(__CLASS__, 'ajax_delete_material'));
        add_action('wp_ajax_lectus_download_material', array(__CLASS__, 'ajax_download_material'));
        add_action('wp_ajax_nopriv_lectus_download_material', array(__CLASS__, 'ajax_download_material'));
        add_action('wp_ajax_lectus_track_external_download', array(__CLASS__, 'ajax_track_external_download'));
        add_action('wp_ajax_nopriv_lectus_track_external_download', array(__CLASS__, 'ajax_track_external_download'));
        
        // Add meta boxes for course materials
        add_action('add_meta_boxes', array(__CLASS__, 'add_meta_boxes'));
        
        // Handle file download requests
        add_action('init', array(__CLASS__, 'handle_download_request'));
        
        // Shortcode for displaying materials
        add_shortcode('lectus_course_materials', array(__CLASS__, 'materials_shortcode'));
        
        // Clean up files when material is deleted
        add_action('deleted_post', array(__CLASS__, 'cleanup_materials_on_post_delete'));
    }
    
    /**
     * Check and create table if needed
     */
    private static function maybe_create_table() {
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_materials';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            self::create_table();
        } else {
            // Check if columns exist and add them if missing
            self::update_table_structure();
        }
    }
    
    /**
     * Update table structure to add missing columns
     */
    private static function update_table_structure() {
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_materials';
        
        // Check for material_type column
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $table LIKE 'material_type'");
        if (empty($column_exists)) {
            $wpdb->query("ALTER TABLE $table ADD COLUMN material_type enum('file','link') NOT NULL DEFAULT 'file' AFTER lesson_id");
        }
        
        // Check for external_url column
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $table LIKE 'external_url'");
        if (empty($column_exists)) {
            $wpdb->query("ALTER TABLE $table ADD COLUMN external_url varchar(1000) DEFAULT NULL AFTER file_url");
        }
    }
    
    /**
     * Create materials database table
     */
    public static function create_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'lectus_materials';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            course_id bigint(20) NOT NULL,
            lesson_id bigint(20) DEFAULT NULL,
            material_type enum('file','link') NOT NULL DEFAULT 'file',
            title varchar(255) NOT NULL,
            description text,
            file_name varchar(255) DEFAULT NULL,
            file_path varchar(500) DEFAULT NULL,
            file_url varchar(500) DEFAULT NULL,
            external_url varchar(1000) DEFAULT NULL,
            file_size bigint(20) DEFAULT NULL,
            file_type varchar(100) DEFAULT NULL,
            mime_type varchar(100) DEFAULT NULL,
            download_count int(11) DEFAULT 0,
            uploaded_by bigint(20) NOT NULL,
            access_level enum('all','enrolled','instructor') DEFAULT 'enrolled',
            status enum('active','inactive') DEFAULT 'active',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY course_id (course_id),
            KEY lesson_id (lesson_id),
            KEY uploaded_by (uploaded_by),
            KEY status (status),
            KEY material_type (material_type)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Create download tracking table
        $tracking_table = $wpdb->prefix . 'lectus_material_downloads';
        $sql_tracking = "CREATE TABLE IF NOT EXISTS $tracking_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            material_id bigint(20) NOT NULL,
            user_id bigint(20) NOT NULL,
            ip_address varchar(45),
            user_agent text,
            downloaded_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY material_id (material_id),
            KEY user_id (user_id),
            KEY downloaded_at (downloaded_at)
        ) $charset_collate;";
        
        dbDelta($sql_tracking);
    }
    
    /**
     * Add meta boxes for course materials
     */
    public static function add_meta_boxes() {
        add_meta_box(
            'lectus_course_materials',
            __('강의 자료', 'lectus-class-system'),
            array(__CLASS__, 'render_materials_meta_box'),
            array('coursesingle', 'lesson'),
            'normal',
            'default'
        );
    }
    
    /**
     * Render materials meta box
     */
    public static function render_materials_meta_box($post) {
        wp_nonce_field('lectus_materials_nonce', 'lectus_materials_nonce');
        
        // Get existing materials
        // For coursesingle, we pass course_id only
        // For lesson, we pass both course_id and lesson_id
        if ($post->post_type === 'lesson') {
            $course_id = get_post_meta($post->ID, '_course_id', true);
            $materials = self::get_materials($course_id, $post->ID);
        } else {
            $materials = self::get_materials($post->ID, null);
        }
        
        ?>
        <div id="lectus-materials-container">
            <div class="lectus-materials-upload">
                <h4><?php _e('새 자료 추가', 'lectus-class-system'); ?></h4>
                
                <!-- Material Type Selector -->
                <div class="material-type-selector">
                    <label class="material-type-option">
                        <input type="radio" name="material_type" value="file" checked />
                        <span><?php _e('파일 업로드', 'lectus-class-system'); ?></span>
                    </label>
                    <label class="material-type-option">
                        <input type="radio" name="material_type" value="link" />
                        <span><?php _e('외부 링크', 'lectus-class-system'); ?></span>
                    </label>
                </div>
                
                <div class="upload-form">
                    <!-- File Upload Section -->
                    <div id="file-upload-section" class="material-input-section">
                        <input type="file" id="lectus-material-file" multiple />
                    </div>
                    
                    <!-- External Link Section -->
                    <div id="external-link-section" class="material-input-section" style="display: none;">
                        <input type="url" id="external-url" placeholder="<?php esc_attr_e('외부 링크 URL (예: https://example.com/document.pdf)', 'lectus-class-system'); ?>" />
                    </div>
                    
                    <!-- Common Fields -->
                    <input type="text" id="material-title" placeholder="<?php esc_attr_e('자료 제목', 'lectus-class-system'); ?>" />
                    <textarea id="material-description" placeholder="<?php esc_attr_e('자료 설명 (선택사항)', 'lectus-class-system'); ?>"></textarea>
                    <select id="material-access-level">
                        <option value="enrolled"><?php _e('수강생만', 'lectus-class-system'); ?></option>
                        <option value="all"><?php _e('모든 사용자', 'lectus-class-system'); ?></option>
                        <option value="instructor"><?php _e('강사만', 'lectus-class-system'); ?></option>
                    </select>
                    <button type="button" class="button button-primary" id="upload-material-btn">
                        <?php _e('자료 추가', 'lectus-class-system'); ?>
                    </button>
                </div>
                <div id="upload-progress" style="display:none;">
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                    <span class="progress-text">0%</span>
                </div>
            </div>
            
            <div class="lectus-materials-list">
                <h4><?php _e('업로드된 자료', 'lectus-class-system'); ?></h4>
                <?php if ($materials): ?>
                    <table id="lectus-materials-table" class="wp-list-table widefat striped">
                        <thead>
                            <tr>
                                <th><?php _e('타입', 'lectus-class-system'); ?></th>
                                <th><?php _e('제목', 'lectus-class-system'); ?></th>
                                <th><?php _e('파일/링크', 'lectus-class-system'); ?></th>
                                <th><?php _e('크기', 'lectus-class-system'); ?></th>
                                <th><?php _e('다운로드', 'lectus-class-system'); ?></th>
                                <th><?php _e('접근 권한', 'lectus-class-system'); ?></th>
                                <th><?php _e('추가 날짜', 'lectus-class-system'); ?></th>
                                <th><?php _e('작업', 'lectus-class-system'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($materials as $material): ?>
                                <?php 
                                // Handle backward compatibility
                                $material_type = isset($material->material_type) ? $material->material_type : 'file';
                                ?>
                                <tr data-material-id="<?php echo esc_attr($material->id); ?>">
                                    <td>
                                        <?php if ($material_type === 'link'): ?>
                                            <span class="material-icon">🔗</span>
                                        <?php else: ?>
                                            <span class="material-icon">📁</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc_html($material->title); ?></td>
                                    <td>
                                        <?php if ($material_type === 'link'): ?>
                                            <a href="<?php echo esc_url($material->external_url); ?>" target="_blank" title="<?php echo esc_attr($material->external_url); ?>">
                                                <?php echo esc_html(substr($material->external_url, 0, 30)) . '...'; ?>
                                            </a>
                                        <?php else: ?>
                                            <?php echo esc_html($material->file_name); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($material_type === 'file' || !$material_type): ?>
                                            <?php echo $material->file_size ? self::format_file_size($material->file_size) : '-'; ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo intval($material->download_count); ?>회</td>
                                    <td><?php echo self::get_access_level_label($material->access_level); ?></td>
                                    <td><?php echo date_i18n(get_option('date_format'), strtotime($material->created_at)); ?></td>
                                    <td>
                                        <?php if ($material_type === 'link'): ?>
                                            <a href="<?php echo esc_url($material->external_url); ?>" target="_blank" class="button button-small">
                                                <?php _e('열기', 'lectus-class-system'); ?>
                                            </a>
                                        <?php else: ?>
                                            <a href="<?php echo esc_url($material->file_url); ?>" target="_blank" class="button button-small">
                                                <?php _e('보기', 'lectus-class-system'); ?>
                                            </a>
                                        <?php endif; ?>
                                        <button type="button" class="button button-small delete-material" data-material-id="<?php echo esc_attr($material->id); ?>">
                                            <?php _e('삭제', 'lectus-class-system'); ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p><?php _e('업로드된 자료가 없습니다.', 'lectus-class-system'); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <style>
            #lectus-materials-container {
                padding: 15px;
            }
            .lectus-materials-upload {
                background: #f9f9f9;
                padding: 15px;
                margin-bottom: 20px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            .material-type-selector {
                display: flex;
                gap: 20px;
                margin-bottom: 20px;
                padding: 10px;
                background: white;
                border-radius: 4px;
            }
            .material-type-option {
                display: flex;
                align-items: center;
                cursor: pointer;
            }
            .material-type-option input[type="radio"] {
                margin-right: 8px;
            }
            .material-type-option span {
                font-weight: 500;
            }
            .upload-form > * {
                margin-bottom: 10px;
                display: block;
                width: 100%;
            }
            .material-input-section {
                margin-bottom: 10px;
            }
            .upload-form input[type="file"],
            .upload-form input[type="url"] {
                padding: 8px;
                border: 1px solid #ddd;
                background: white;
                width: 100%;
            }
            .upload-form input[type="text"],
            .upload-form textarea,
            .upload-form select {
                padding: 8px;
                border: 1px solid #ddd;
            }
            .progress-bar {
                width: 100%;
                height: 20px;
                background: #f0f0f0;
                border-radius: 10px;
                overflow: hidden;
                margin: 10px 0;
            }
            .progress-fill {
                height: 100%;
                background: #4CAF50;
                width: 0%;
                transition: width 0.3s;
            }
            .material-icon {
                display: inline-block;
                width: 20px;
                text-align: center;
                margin-right: 5px;
            }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Toggle between file upload and external link
            $('input[name="material_type"]').on('change', function() {
                if ($(this).val() === 'file') {
                    $('#file-upload-section').show();
                    $('#external-link-section').hide();
                } else {
                    $('#file-upload-section').hide();
                    $('#external-link-section').show();
                }
            });
            
            // Upload/Add material
            $('#upload-material-btn').on('click', function() {
                var materialType = $('input[name="material_type"]:checked').val();
                var title = $('#material-title').val();
                
                console.log('Material type:', materialType);
                console.log('Title:', title);
                
                if (!title) {
                    alert('자료 제목을 입력해주세요.');
                    return;
                }
                
                var formData = new FormData();
                formData.append('action', 'lectus_upload_material');
                formData.append('nonce', '<?php echo wp_create_nonce('lectus-materials-nonce'); ?>');
                <?php if ($post->post_type === 'lesson'): ?>
                    formData.append('course_id', '<?php echo get_post_meta($post->ID, '_course_id', true); ?>');
                    formData.append('lesson_id', '<?php echo $post->ID; ?>');
                <?php else: ?>
                    formData.append('course_id', '<?php echo $post->ID; ?>');
                    formData.append('lesson_id', '');
                <?php endif; ?>
                formData.append('post_type', '<?php echo $post->post_type; ?>');
                formData.append('material_type', materialType);
                formData.append('title', title);
                formData.append('description', $('#material-description').val());
                formData.append('access_level', $('#material-access-level').val());
                
                if (materialType === 'file') {
                    var fileInput = $('#lectus-material-file')[0];
                    if (!fileInput.files.length) {
                        alert('파일을 선택해주세요.');
                        return;
                    }
                    // Add files
                    for (var i = 0; i < fileInput.files.length; i++) {
                        formData.append('files[]', fileInput.files[i]);
                    }
                } else {
                    var externalUrl = $('#external-url').val();
                    console.log('External URL input value:', externalUrl);
                    if (!externalUrl) {
                        alert('외부 링크 URL을 입력해주세요.');
                        return;
                    }
                    // Validate URL
                    if (!externalUrl.match(/^https?:\/\/.+/)) {
                        alert('올바른 URL 형식을 입력해주세요. (예: https://example.com)');
                        return;
                    }
                    formData.append('external_url', externalUrl);
                    console.log('Added external_url to FormData:', externalUrl);
                }
                
                // Log all FormData entries
                console.log('FormData contents:');
                for (var pair of formData.entries()) {
                    console.log(pair[0] + ': ' + pair[1]);
                }
                
                $('#upload-progress').show();
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function(evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = evt.loaded / evt.total * 100;
                                $('.progress-fill').css('width', percentComplete + '%');
                                $('.progress-text').text(Math.round(percentComplete) + '%');
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(response) {
                        console.log('Response:', response);
                        if (response.success) {
                            // Add new material to the table
                            if (response.data.files && response.data.files.length > 0) {
                                var newRows = '';
                                response.data.files.forEach(function(file) {
                                    var icon = file.material_type === 'link' ? '🔗' : '📎';
                                    var fileLink = file.material_type === 'link' ? file.external_url : file.file_url;
                                    var displayLink = fileLink;
                                    if (fileLink && fileLink.length > 30) {
                                        displayLink = fileLink.substring(0, 30) + '...';
                                    }
                                    var fileSize = file.file_size ? (file.file_size / 1024).toFixed(2) + ' KB' : '-';
                                    var accessLevel = file.access_level === 'enrolled' ? '수강생만' : 
                                                      file.access_level === 'public' ? '모든 사용자' : '강사만';
                                    var today = new Date().toLocaleDateString('ko-KR', {
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric'
                                    }).replace(/\. /g, '년 ').replace(/\.$/, '일');
                                    
                                    newRows += '<tr>' +
                                        '<td><span class="material-icon">' + icon + '</span></td>' +
                                        '<td>' + file.title + '</td>' +
                                        '<td>' + (fileLink ? '<a href="' + fileLink + '" target="_blank">' + displayLink + '</a>' : '-') + '</td>' +
                                        '<td>' + fileSize + '</td>' +
                                        '<td>0회</td>' +
                                        '<td>' + accessLevel + '</td>' +
                                        '<td>' + today + '</td>' +
                                        '<td>' +
                                            (fileLink ? '<a href="' + fileLink + '" target="_blank" class="button button-small">열기</a> ' : '') +
                                            '<button class="button button-small delete-material" data-material-id="' + file.id + '">삭제</button>' +
                                        '</td>' +
                                    '</tr>';
                                });
                                
                                // Add new rows to the table
                                var tbody = $('#lectus-materials-table tbody');
                                if (tbody.length > 0) {
                                    tbody.append(newRows);
                                } else {
                                    // If table doesn't exist (first material), create it
                                    var noMaterialsMsg = $('.lectus-materials-list p:contains("업로드된 자료가 없습니다")');
                                    if (noMaterialsMsg.length > 0) {
                                        var newTable = '<table id="lectus-materials-table" class="wp-list-table widefat fixed striped">' +
                                            '<thead><tr>' +
                                            '<th>타입</th>' +
                                            '<th>제목</th>' +
                                            '<th>파일/링크</th>' +
                                            '<th>크기</th>' +
                                            '<th>다운로드</th>' +
                                            '<th>접근 권한</th>' +
                                            '<th>추가 날짜</th>' +
                                            '<th>작업</th>' +
                                            '</tr></thead>' +
                                            '<tbody>' + newRows + '</tbody>' +
                                            '</table>';
                                        noMaterialsMsg.replaceWith(newTable);
                                    } else {
                                        // Fallback: try to find table by header content
                                        var table = $('table').filter(function() {
                                            return $(this).find('th:contains("타입")').length > 0;
                                        });
                                        if (table.length > 0) {
                                            table.find('tbody').append(newRows);
                                        }
                                    }
                                }
                                
                                // Reattach delete event handlers to new buttons
                                $('.delete-material').off('click').on('click', function() {
                                    if (!confirm('이 자료를 삭제하시겠습니까?')) {
                                        return;
                                    }
                                    
                                    var materialId = $(this).data('material-id');
                                    var row = $(this).closest('tr');
                                    
                                    $.ajax({
                                        url: ajaxurl,
                                        type: 'POST',
                                        data: {
                                            action: 'lectus_delete_material',
                                            nonce: '<?php echo wp_create_nonce('lectus-materials-nonce'); ?>',
                                            material_id: materialId
                                        },
                                        success: function(response) {
                                            if (response.success) {
                                                row.fadeOut(400, function() {
                                                    $(this).remove();
                                                });
                                            } else {
                                                alert('삭제 실패: ' + response.data.message);
                                            }
                                        }
                                    });
                                });
                            }
                            
                            // Clear input fields
                            $('#material-title').val('');
                            $('#material-description').val('');
                            $('#external-url').val('');
                            $('#lectus-material-file').val('');
                            
                            alert(response.data.message || '자료가 추가되었습니다.');
                        } else {
                            console.error('Error details:', response.data);
                            var errorMsg = '실패: ' + response.data.message;
                            if (response.data.error) {
                                errorMsg += '\n\n에러: ' + response.data.error;
                            }
                            if (response.data.debug) {
                                console.log('Debug info:', response.data.debug);
                            }
                            alert(errorMsg);
                        }
                        $('#upload-progress').hide();
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        console.error('Response:', xhr.responseText);
                        alert('처리 중 오류가 발생했습니다.\n\n' + error);
                        $('#upload-progress').hide();
                    }
                });
            });
            
            // Delete material
            $('.delete-material').on('click', function() {
                if (!confirm('이 자료를 삭제하시겠습니까?')) {
                    return;
                }
                
                var materialId = $(this).data('material-id');
                var row = $(this).closest('tr');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'lectus_delete_material',
                        nonce: '<?php echo wp_create_nonce('lectus-materials-nonce'); ?>',
                        material_id: materialId
                    },
                    success: function(response) {
                        if (response.success) {
                            row.fadeOut(400, function() {
                                $(this).remove();
                            });
                        } else {
                            alert('삭제 실패: ' + response.data.message);
                        }
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * AJAX handler for uploading materials
     */
    public static function ajax_upload_material() {
        // Debug log all POST data
        // Debug logging removed for production
        
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-materials-nonce')) {
            wp_send_json_error(array('message' => __('보안 검증 실패', 'lectus-class-system')));
        }
        
        // Check permissions
        if (!current_user_can('edit_posts')) {
            error_log('ERROR: User lacks permissions');
            wp_send_json_error(array('message' => __('권한이 없습니다.', 'lectus-class-system')));
        }
        
        $course_id = intval($_POST['course_id']);
        $lesson_id = isset($_POST['lesson_id']) && !empty($_POST['lesson_id']) ? intval($_POST['lesson_id']) : null;
        $material_type = isset($_POST['material_type']) ? sanitize_text_field($_POST['material_type']) : '';
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
        $access_level = isset($_POST['access_level']) ? sanitize_text_field($_POST['access_level']) : 'enrolled';
        $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';
        
        error_log("Parsed values - Course ID: $course_id, Lesson ID: $lesson_id, Type: $material_type, Title: $title");
        
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_materials';
        $uploaded_files = array();
        
        if ($material_type === 'file') {
            // Check for files
            if (empty($_FILES['files'])) {
                wp_send_json_error(array('message' => __('파일이 없습니다.', 'lectus-class-system')));
            }
            
            // Handle file upload
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            
            $upload_dir = wp_upload_dir();
            $materials_dir = $upload_dir['basedir'] . '/lectus-materials/' . $course_id;
            
            // Create directory if it doesn't exist
            if (!file_exists($materials_dir)) {
                wp_mkdir_p($materials_dir);
            }
            
            // Process each file
            foreach ($_FILES['files']['name'] as $key => $filename) {
                if ($_FILES['files']['error'][$key] !== UPLOAD_ERR_OK) {
                    continue;
                }
                
                $file = array(
                    'name' => $_FILES['files']['name'][$key],
                    'type' => $_FILES['files']['type'][$key],
                    'tmp_name' => $_FILES['files']['tmp_name'][$key],
                    'error' => $_FILES['files']['error'][$key],
                    'size' => $_FILES['files']['size'][$key]
                );
                
                // Generate unique filename
                $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $file_name_only = pathinfo($file['name'], PATHINFO_FILENAME);
                $unique_filename = sanitize_file_name($file_name_only) . '_' . uniqid() . '.' . $file_extension;
                $file_path = $materials_dir . '/' . $unique_filename;
                
                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $file_path)) {
                    $data = array(
                        'course_id' => $course_id,
                        'lesson_id' => $lesson_id,
                        'material_type' => 'file',
                        'title' => !empty($title) ? $title : $file_name_only,
                        'description' => $description,
                        'file_name' => $file['name'],
                        'file_path' => $file_path,
                        'file_url' => str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $file_path),
                        'file_size' => $file['size'],
                        'file_type' => $file_extension,
                        'mime_type' => $file['type'],
                        'uploaded_by' => get_current_user_id(),
                        'access_level' => $access_level,
                        'status' => 'active'
                    );
                    
                    $wpdb->insert($table, $data);
                    $uploaded_files[] = $data;
                }
            }
        } else {
            // Handle external link
            // Processing external link
            $external_url = isset($_POST['external_url']) ? esc_url_raw($_POST['external_url']) : '';
            // External URL from POST
            
            if (empty($external_url)) {
                error_log('ERROR: External URL is empty');
                wp_send_json_error(array('message' => __('외부 링크 URL이 필요합니다.', 'lectus-class-system')));
            }
            
            // Validate URL
            if (!filter_var($external_url, FILTER_VALIDATE_URL)) {
                wp_send_json_error(array('message' => __('올바른 URL 형식이 아닙니다.', 'lectus-class-system')));
            }
            
            // Extract file info from URL if possible
            $url_parts = parse_url($external_url);
            $path_parts = pathinfo($url_parts['path'] ?? '');
            $file_extension = $path_parts['extension'] ?? '';
            $file_name = $path_parts['basename'] ?? 'External Link';
            
            $data = array(
                'course_id' => $course_id,
                'lesson_id' => $lesson_id,
                'material_type' => 'link',
                'title' => !empty($title) ? $title : $file_name,
                'description' => $description,
                'file_name' => $file_name,
                'external_url' => $external_url,
                'file_type' => $file_extension,
                'uploaded_by' => get_current_user_id(),
                'access_level' => $access_level,
                'status' => 'active'
            );
            
            // Debug logging - Enhanced
            error_log('========== LECTUS MATERIALS DEBUG ==========');
            error_log('Material Type: ' . $material_type);
            error_log('External URL: ' . $external_url);
            error_log('Table Name: ' . $table);
            error_log('Data to insert: ' . print_r($data, true));
            
            // Check if table exists
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table'");
            if (!$table_exists) {
                error_log('ERROR: Table does not exist: ' . $table);
                wp_send_json_error(array(
                    'message' => __('테이블이 존재하지 않습니다', 'lectus-class-system'),
                    'error' => 'Table not found: ' . $table,
                    'debug' => array(
                        'table' => $table,
                        'exists' => false
                    )
                ));
                return;
            }
            
            $result = $wpdb->insert($table, $data);
            
            if ($result === false) {
                error_log('ERROR: Insert failed!');
                error_log('Last Error: ' . $wpdb->last_error);
                error_log('Last Query: ' . $wpdb->last_query);
                error_log('========== END DEBUG ==========');
                
                wp_send_json_error(array(
                    'message' => __('데이터베이스 저장 실패', 'lectus-class-system'),
                    'error' => $wpdb->last_error,
                    'query' => $wpdb->last_query,
                    'debug' => array(
                        'table' => $table,
                        'data' => $data,
                        'db_error' => $wpdb->last_error
                    )
                ));
            } else {
                $material_id = $wpdb->insert_id;
                error_log('SUCCESS: Insert successful, ID: ' . $material_id);
                error_log('========== END DEBUG ==========');
                
                $data['id'] = $material_id;
                $uploaded_files[] = $data;
            }
        }
        
        if (!empty($uploaded_files)) {
            wp_send_json_success(array(
                'message' => __('자료가 추가되었습니다.', 'lectus-class-system'),
                'files' => $uploaded_files
            ));
        } else {
            wp_send_json_error(array('message' => __('자료 추가 실패', 'lectus-class-system')));
        }
    }
    
    /**
     * AJAX handler for deleting materials
     */
    public static function ajax_delete_material() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-materials-nonce')) {
            wp_send_json_error(array('message' => __('보안 검증 실패', 'lectus-class-system')));
        }
        
        // Check permissions
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('권한이 없습니다.', 'lectus-class-system')));
        }
        
        $material_id = intval($_POST['material_id']);
        
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_materials';
        
        // Get material info
        $material = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $material_id
        ));
        
        if (!$material) {
            wp_send_json_error(array('message' => __('자료를 찾을 수 없습니다.', 'lectus-class-system')));
        }
        
        // Delete file
        if (file_exists($material->file_path)) {
            unlink($material->file_path);
        }
        
        // Delete from database
        $wpdb->delete($table, array('id' => $material_id));
        
        wp_send_json_success(array('message' => __('자료가 삭제되었습니다.', 'lectus-class-system')));
    }
    
    /**
     * AJAX handler for downloading materials
     */
    public static function ajax_download_material() {
        $material_id = isset($_GET['material_id']) ? intval($_GET['material_id']) : 0;
        
        if (!$material_id) {
            wp_die(__('잘못된 요청입니다.', 'lectus-class-system'));
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_materials';
        
        // Get material info
        $material = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d AND status = 'active'",
            $material_id
        ));
        
        if (!$material) {
            wp_die(__('자료를 찾을 수 없습니다.', 'lectus-class-system'));
        }
        
        // Check access permissions
        if (!self::can_download_material($material)) {
            wp_die(__('이 자료에 접근할 권한이 없습니다.', 'lectus-class-system'));
        }
        
        // Check if file exists
        if (!file_exists($material->file_path)) {
            wp_die(__('파일을 찾을 수 없습니다.', 'lectus-class-system'));
        }
        
        // Update download count
        $wpdb->update(
            $table,
            array('download_count' => $material->download_count + 1),
            array('id' => $material_id)
        );
        
        // Log download
        if (is_user_logged_in()) {
            $tracking_table = $wpdb->prefix . 'lectus_material_downloads';
            $wpdb->insert($tracking_table, array(
                'material_id' => $material_id,
                'user_id' => get_current_user_id(),
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT']
            ));
        }
        
        // Send file
        header('Content-Type: ' . $material->mime_type);
        header('Content-Disposition: attachment; filename="' . $material->file_name . '"');
        header('Content-Length: ' . $material->file_size);
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        readfile($material->file_path);
        exit;
    }
    
    /**
     * AJAX handler for tracking external link downloads
     */
    public static function ajax_track_external_download() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-track-download')) {
            wp_send_json_error(array('message' => __('보안 검증 실패', 'lectus-class-system')));
        }
        
        $material_id = isset($_POST['material_id']) ? intval($_POST['material_id']) : 0;
        
        if (!$material_id) {
            wp_send_json_error(array('message' => __('잘못된 요청입니다.', 'lectus-class-system')));
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_materials';
        
        // Get material info
        $material = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d AND status = 'active'",
            $material_id
        ));
        
        if (!$material) {
            wp_send_json_error(array('message' => __('자료를 찾을 수 없습니다.', 'lectus-class-system')));
        }
        
        // Check access permissions
        if (!self::can_download_material($material)) {
            wp_send_json_error(array('message' => __('이 자료에 접근할 권한이 없습니다.', 'lectus-class-system')));
        }
        
        // Update download count
        $wpdb->update(
            $table,
            array('download_count' => $material->download_count + 1),
            array('id' => $material_id)
        );
        
        // Log download
        if (is_user_logged_in()) {
            $tracking_table = $wpdb->prefix . 'lectus_material_downloads';
            $wpdb->insert($tracking_table, array(
                'material_id' => $material_id,
                'user_id' => get_current_user_id(),
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT']
            ));
        }
        
        wp_send_json_success(array(
            'message' => __('다운로드가 기록되었습니다.', 'lectus-class-system'),
            'count' => $material->download_count + 1
        ));
    }
    
    /**
     * Handle download requests from frontend
     */
    public static function handle_download_request() {
        if (isset($_GET['lectus_download']) && isset($_GET['material_id'])) {
            self::ajax_download_material();
        }
    }
    
    /**
     * Check if user can download material
     */
    private static function can_download_material($material) {
        // Admins can always download
        if (current_user_can('manage_options')) {
            return true;
        }
        
        // Check access level
        switch ($material->access_level) {
            case 'all':
                return true;
                
            case 'enrolled':
                if (!is_user_logged_in()) {
                    return false;
                }
                // Check if user is enrolled in the course
                $course_id = $material->lesson_id ? 
                    get_post_meta($material->lesson_id, '_course_id', true) : 
                    $material->course_id;
                    
                return Lectus_Enrollment::is_enrolled(get_current_user_id(), $course_id);
                
            case 'instructor':
                return current_user_can('edit_courses');
                
            default:
                return false;
        }
    }
    
    /**
     * Get materials for a course or lesson
     */
    public static function get_materials($course_id, $lesson_id = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_materials';
        
        if ($lesson_id) {
            $materials = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table WHERE lesson_id = %d AND status = 'active' ORDER BY created_at DESC",
                $lesson_id
            ));
        } else {
            $materials = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table WHERE course_id = %d AND lesson_id IS NULL AND status = 'active' ORDER BY created_at DESC",
                $course_id
            ));
        }
        
        // Ensure material_type exists for backward compatibility
        if ($materials) {
            foreach ($materials as &$material) {
                if (!isset($material->material_type)) {
                    $material->material_type = 'file'; // Default to file for old entries
                }
            }
        }
        
        return $materials;
    }
    
    /**
     * Format file size
     */
    private static function format_file_size($bytes) {
        $units = array('B', 'KB', 'MB', 'GB');
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * Get access level label
     */
    private static function get_access_level_label($level) {
        $labels = array(
            'all' => __('모든 사용자', 'lectus-class-system'),
            'enrolled' => __('수강생만', 'lectus-class-system'),
            'instructor' => __('강사만', 'lectus-class-system')
        );
        
        return isset($labels[$level]) ? $labels[$level] : $level;
    }
    
    /**
     * Shortcode for displaying course materials
     */
    public static function materials_shortcode($atts) {
        $atts = shortcode_atts(array(
            'course_id' => get_the_ID(),
            'show_all' => 'no'
        ), $atts);
        
        $course_id = intval($atts['course_id']);
        $materials = self::get_materials($course_id);
        
        if (empty($materials)) {
            return '<p>' . __('이 강의에는 다운로드 가능한 자료가 없습니다.', 'lectus-class-system') . '</p>';
        }
        
        ob_start();
        ?>
        <div class="lectus-materials-list">
            <h3><?php _e('강의 자료', 'lectus-class-system'); ?></h3>
            <table class="lectus-materials-table">
                <thead>
                    <tr>
                        <th><?php _e('제목', 'lectus-class-system'); ?></th>
                        <th><?php _e('설명', 'lectus-class-system'); ?></th>
                        <th><?php _e('크기', 'lectus-class-system'); ?></th>
                        <th><?php _e('다운로드', 'lectus-class-system'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materials as $material): ?>
                        <?php if (!self::can_download_material($material)) continue; ?>
                        <?php 
                        // Handle backward compatibility
                        $material_type = isset($material->material_type) ? $material->material_type : 'file';
                        ?>
                        <tr>
                            <td>
                                <?php if ($material_type === 'link'): ?>
                                    <span class="material-icon">🔗</span>
                                <?php else: ?>
                                    <span class="material-icon">📁</span>
                                <?php endif; ?>
                                <strong><?php echo esc_html($material->title); ?></strong>
                                <br>
                                <small>
                                    <?php if ($material_type === 'link'): ?>
                                        <?php 
                                        $domain = isset($material->external_url) ? parse_url($material->external_url, PHP_URL_HOST) : '';
                                        echo esc_html($domain);
                                        ?>
                                    <?php else: ?>
                                        <?php echo esc_html($material->file_name); ?>
                                    <?php endif; ?>
                                </small>
                            </td>
                            <td><?php echo esc_html($material->description ?: '-'); ?></td>
                            <td>
                                <?php if ($material_type === 'file'): ?>
                                    <?php echo isset($material->file_size) ? self::format_file_size($material->file_size) : '-'; ?>
                                <?php else: ?>
                                    <span style="color: #666;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($material_type === 'link' && !empty($material->external_url)): ?>
                                    <a href="<?php echo esc_url($material->external_url); ?>" 
                                       target="_blank" 
                                       class="button lectus-download-btn"
                                       onclick="lectusTrackDownload(<?php echo $material->id; ?>)">
                                        <?php _e('열기', 'lectus-class-system'); ?>
                                        <span class="download-count">(<?php echo intval($material->download_count); ?>)</span>
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo esc_url(add_query_arg(array(
                                        'lectus_download' => '1',
                                        'material_id' => $material->id
                                    ), home_url())); ?>" class="button lectus-download-btn">
                                        <?php _e('다운로드', 'lectus-class-system'); ?>
                                        <span class="download-count">(<?php echo intval($material->download_count); ?>)</span>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <style>
            .lectus-materials-list {
                margin: 20px 0;
            }
            .lectus-materials-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 15px;
            }
            .lectus-materials-table th,
            .lectus-materials-table td {
                padding: 10px;
                text-align: left;
                border: 1px solid #ddd;
            }
            .lectus-materials-table th {
                background: #f5f5f5;
                font-weight: bold;
            }
            .lectus-materials-table tr:hover {
                background: #f9f9f9;
            }
            .lectus-download-btn {
                display: inline-block;
                padding: 5px 15px;
                background: #007cba;
                color: white;
                text-decoration: none;
                border-radius: 3px;
            }
            .lectus-download-btn:hover {
                background: #005a87;
                color: white;
            }
            .download-count {
                opacity: 0.7;
                font-size: 0.9em;
            }
            .material-icon {
                display: inline-block;
                margin-right: 5px;
            }
        </style>
        
        <script>
        function lectusTrackDownload(materialId) {
            // Track external link clicks via AJAX
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'lectus_track_external_download',
                    material_id: materialId,
                    nonce: '<?php echo wp_create_nonce('lectus-track-download'); ?>'
                },
                success: function(response) {
                    // Update counter if needed
                    if (response.success && response.data.count) {
                        jQuery('.lectus-download-btn[onclick*="' + materialId + '"] .download-count').text('(' + response.data.count + ')');
                    }
                }
            });
            // Don't prevent the link from opening
            return true;
        }
        </script>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Clean up materials when post is deleted
     */
    public static function cleanup_materials_on_post_delete($post_id) {
        $post_type = get_post_type($post_id);
        
        if (!in_array($post_type, array('coursesingle', 'lesson'))) {
            return;
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_materials';
        
        // Get all materials for this post
        if ($post_type === 'lesson') {
            $materials = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table WHERE lesson_id = %d",
                $post_id
            ));
        } else {
            $materials = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table WHERE course_id = %d",
                $post_id
            ));
        }
        
        // Delete files and database entries
        foreach ($materials as $material) {
            if (file_exists($material->file_path)) {
                unlink($material->file_path);
            }
        }
        
        // Delete from database
        if ($post_type === 'lesson') {
            $wpdb->delete($table, array('lesson_id' => $post_id));
        } else {
            $wpdb->delete($table, array('course_id' => $post_id));
        }
    }
}