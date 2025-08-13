<?php
/**
 * Bulk Upload System for Lectus Class System
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Bulk_Upload {
    
    public static function init() {
        // Admin menu
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'), 25);
        
        // AJAX handlers
        add_action('wp_ajax_lectus_bulk_upload_lessons', array(__CLASS__, 'ajax_bulk_upload_lessons'));
        add_action('wp_ajax_lectus_bulk_upload_students', array(__CLASS__, 'ajax_bulk_upload_students'));
        add_action('wp_ajax_lectus_bulk_enroll_students', array(__CLASS__, 'ajax_bulk_enroll_students'));
        
        // File upload handlers
        add_action('wp_ajax_lectus_process_csv_upload', array(__CLASS__, 'ajax_process_csv_upload'));
    }
    
    /**
     * Add admin menu for bulk upload
     */
    public static function add_admin_menu() {
        add_submenu_page(
            'lectus-class-system',
            __('벌크 업로드', 'lectus-class-system'),
            __('벌크 업로드', 'lectus-class-system'),
            'manage_options',
            'lectus-bulk-upload',
            array(__CLASS__, 'admin_page')
        );
    }
    
    /**
     * Admin page for bulk upload
     */
    public static function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('벌크 업로드', 'lectus-class-system'); ?></h1>
            
            <div class="nav-tab-wrapper">
                <a href="#lessons" class="nav-tab nav-tab-active"><?php _e('레슨 업로드', 'lectus-class-system'); ?></a>
                <a href="#students" class="nav-tab"><?php _e('학생 업로드', 'lectus-class-system'); ?></a>
                <a href="#enrollments" class="nav-tab"><?php _e('수강 등록', 'lectus-class-system'); ?></a>
            </div>
            
            <div id="lessons" class="tab-content">
                <h2><?php _e('레슨 벌크 업로드', 'lectus-class-system'); ?></h2>
                <p><?php _e('CSV 파일을 통해 여러 레슨을 한번에 업로드할 수 있습니다.', 'lectus-class-system'); ?></p>
                
                <div class="upload-section">
                    <h3><?php _e('CSV 파일 형식', 'lectus-class-system'); ?></h3>
                    <div class="csv-format-info">
                        <p><strong><?php _e('필수 컬럼:', 'lectus-class-system'); ?></strong></p>
                        <ul>
                            <li><code>title</code> - <?php _e('레슨 제목', 'lectus-class-system'); ?></li>
                            <li><code>content</code> - <?php _e('레슨 내용', 'lectus-class-system'); ?></li>
                            <li><code>course_id</code> - <?php _e('강의 ID', 'lectus-class-system'); ?></li>
                            <li><code>lesson_type</code> - <?php _e('레슨 타입 (video, text, quiz, assignment)', 'lectus-class-system'); ?></li>
                        </ul>
                        <p><strong><?php _e('선택적 컬럼:', 'lectus-class-system'); ?></strong></p>
                        <ul>
                            <li><code>duration</code> - <?php _e('예상 학습 시간 (분)', 'lectus-class-system'); ?></li>
                            <li><code>order</code> - <?php _e('정렬 순서', 'lectus-class-system'); ?></li>
                            <li><code>video_url</code> - <?php _e('비디오 URL (비디오 타입인 경우)', 'lectus-class-system'); ?></li>
                            <li><code>description</code> - <?php _e('레슨 설명', 'lectus-class-system'); ?></li>
                        </ul>
                        <a href="#" id="download-lesson-template" class="button"><?php _e('템플릿 다운로드', 'lectus-class-system'); ?></a>
                    </div>
                    
                    <form id="lesson-upload-form" method="post" enctype="multipart/form-data">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('강의 선택', 'lectus-class-system'); ?></th>
                                <td>
                                    <select name="course_id" required>
                                        <option value=""><?php _e('강의를 선택하세요', 'lectus-class-system'); ?></option>
                                        <?php
                                        $courses = get_posts(array(
                                            'post_type' => 'coursesingle',
                                            'posts_per_page' => -1,
                                            'orderby' => 'title',
                                            'order' => 'ASC'
                                        ));
                                        foreach ($courses as $course) {
                                            echo '<option value="' . $course->ID . '">' . esc_html($course->post_title) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('CSV 파일', 'lectus-class-system'); ?></th>
                                <td>
                                    <input type="file" name="csv_file" accept=".csv" required>
                                    <p class="description"><?php _e('UTF-8 인코딩된 CSV 파일만 지원됩니다.', 'lectus-class-system'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('옵션', 'lectus-class-system'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="skip_duplicates" value="1" checked>
                                        <?php _e('중복 제목 건너뛰기', 'lectus-class-system'); ?>
                                    </label><br>
                                    <label>
                                        <input type="checkbox" name="auto_publish" value="1" checked>
                                        <?php _e('자동으로 발행하기', 'lectus-class-system'); ?>
                                    </label>
                                </td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <input type="submit" class="button-primary" value="<?php _e('레슨 업로드', 'lectus-class-system'); ?>">
                        </p>
                    </form>
                    
                    <div id="upload-progress" style="display: none;">
                        <h3><?php _e('업로드 진행 상황', 'lectus-class-system'); ?></h3>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 0%;"></div>
                        </div>
                        <div id="progress-text">0%</div>
                        <div id="upload-results"></div>
                    </div>
                </div>
            </div>
            
            <div id="students" class="tab-content" style="display: none;">
                <h2><?php _e('학생 벌크 등록', 'lectus-class-system'); ?></h2>
                <p><?php _e('CSV 파일을 통해 여러 학생을 한번에 등록할 수 있습니다.', 'lectus-class-system'); ?></p>
                
                <div class="upload-section">
                    <h3><?php _e('CSV 파일 형식', 'lectus-class-system'); ?></h3>
                    <div class="csv-format-info">
                        <p><strong><?php _e('필수 컬럼:', 'lectus-class-system'); ?></strong></p>
                        <ul>
                            <li><code>username</code> - <?php _e('사용자명 (영문, 숫자)', 'lectus-class-system'); ?></li>
                            <li><code>email</code> - <?php _e('이메일 주소', 'lectus-class-system'); ?></li>
                            <li><code>password</code> - <?php _e('비밀번호', 'lectus-class-system'); ?></li>
                        </ul>
                        <p><strong><?php _e('선택적 컬럼:', 'lectus-class-system'); ?></strong></p>
                        <ul>
                            <li><code>first_name</code> - <?php _e('이름', 'lectus-class-system'); ?></li>
                            <li><code>last_name</code> - <?php _e('성', 'lectus-class-system'); ?></li>
                            <li><code>display_name</code> - <?php _e('표시 이름', 'lectus-class-system'); ?></li>
                            <li><code>phone</code> - <?php _e('전화번호', 'lectus-class-system'); ?></li>
                        </ul>
                        <a href="#" id="download-student-template" class="button"><?php _e('템플릿 다운로드', 'lectus-class-system'); ?></a>
                    </div>
                    
                    <form id="student-upload-form" method="post" enctype="multipart/form-data">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('CSV 파일', 'lectus-class-system'); ?></th>
                                <td>
                                    <input type="file" name="csv_file" accept=".csv" required>
                                    <p class="description"><?php _e('UTF-8 인코딩된 CSV 파일만 지원됩니다.', 'lectus-class-system'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('옵션', 'lectus-class-system'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="send_welcome_email" value="1">
                                        <?php _e('환영 이메일 발송', 'lectus-class-system'); ?>
                                    </label><br>
                                    <label>
                                        <input type="checkbox" name="auto_assign_student_role" value="1" checked>
                                        <?php _e('자동으로 학생 역할 할당', 'lectus-class-system'); ?>
                                    </label>
                                </td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <input type="submit" class="button-primary" value="<?php _e('학생 등록', 'lectus-class-system'); ?>">
                        </p>
                    </form>
                </div>
            </div>
            
            <div id="enrollments" class="tab-content" style="display: none;">
                <h2><?php _e('벌크 수강 등록', 'lectus-class-system'); ?></h2>
                <p><?php _e('기존 학생들을 여러 강의에 한번에 등록할 수 있습니다.', 'lectus-class-system'); ?></p>
                
                <div class="upload-section">
                    <h3><?php _e('CSV 파일 형식', 'lectus-class-system'); ?></h3>
                    <div class="csv-format-info">
                        <p><strong><?php _e('필수 컬럼:', 'lectus-class-system'); ?></strong></p>
                        <ul>
                            <li><code>user_email</code> - <?php _e('학생 이메일', 'lectus-class-system'); ?></li>
                            <li><code>course_id</code> - <?php _e('강의 ID', 'lectus-class-system'); ?></li>
                        </ul>
                        <p><strong><?php _e('선택적 컬럼:', 'lectus-class-system'); ?></strong></p>
                        <ul>
                            <li><code>duration</code> - <?php _e('수강 기간 (일)', 'lectus-class-system'); ?></li>
                            <li><code>start_date</code> - <?php _e('시작일 (YYYY-MM-DD)', 'lectus-class-system'); ?></li>
                        </ul>
                        <a href="#" id="download-enrollment-template" class="button"><?php _e('템플릿 다운로드', 'lectus-class-system'); ?></a>
                    </div>
                    
                    <form id="enrollment-upload-form" method="post" enctype="multipart/form-data">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('CSV 파일', 'lectus-class-system'); ?></th>
                                <td>
                                    <input type="file" name="csv_file" accept=".csv" required>
                                    <p class="description"><?php _e('UTF-8 인코딩된 CSV 파일만 지원됩니다.', 'lectus-class-system'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('옵션', 'lectus-class-system'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="send_enrollment_email" value="1" checked>
                                        <?php _e('등록 완료 이메일 발송', 'lectus-class-system'); ?>
                                    </label><br>
                                    <label>
                                        <input type="checkbox" name="skip_existing" value="1" checked>
                                        <?php _e('이미 등록된 학생 건너뛰기', 'lectus-class-system'); ?>
                                    </label>
                                </td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <input type="submit" class="button-primary" value="<?php _e('수강 등록', 'lectus-class-system'); ?>">
                        </p>
                    </form>
                </div>
            </div>
        </div>
        
        <style>
        .tab-content {
            background: white;
            border: 1px solid #ccd0d4;
            border-top: none;
            padding: 20px;
            margin-bottom: 20px;
        }
        .upload-section {
            margin-top: 20px;
        }
        .csv-format-info {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .csv-format-info ul {
            margin-left: 20px;
        }
        .csv-format-info code {
            background: #fff;
            padding: 2px 4px;
            border: 1px solid #ddd;
            border-radius: 3px;
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
            transition: width 0.3s ease;
        }
        #progress-text {
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
        }
        #upload-results {
            margin-top: 10px;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .result-item {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .result-success { color: #00a32a; }
        .result-error { color: #d63638; }
        .result-warning { color: #dba617; }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Tab switching
            $('.nav-tab').on('click', function(e) {
                e.preventDefault();
                $('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                $('.tab-content').hide();
                $($(this).attr('href')).show();
            });
            
            // Template downloads
            $('#download-lesson-template').on('click', function(e) {
                e.preventDefault();
                downloadTemplate('lessons');
            });
            
            $('#download-student-template').on('click', function(e) {
                e.preventDefault();
                downloadTemplate('students');
            });
            
            $('#download-enrollment-template').on('click', function(e) {
                e.preventDefault();
                downloadTemplate('enrollments');
            });
            
            // Form submissions
            $('#lesson-upload-form').on('submit', function(e) {
                e.preventDefault();
                uploadCSV(this, 'lessons');
            });
            
            $('#student-upload-form').on('submit', function(e) {
                e.preventDefault();
                uploadCSV(this, 'students');
            });
            
            $('#enrollment-upload-form').on('submit', function(e) {
                e.preventDefault();
                uploadCSV(this, 'enrollments');
            });
        });
        
        function downloadTemplate(type) {
            var csv_content = '';
            
            switch(type) {
                case 'lessons':
                    csv_content = 'title,content,course_id,lesson_type,duration,order,video_url,description\n';
                    csv_content += '샘플 레슨 1,이것은 샘플 레슨 내용입니다,1,video,30,1,https://youtube.com/watch?v=example,비디오 레슨 설명\n';
                    csv_content += '샘플 레슨 2,이것은 텍스트 레슨 내용입니다,1,text,15,2,,텍스트 레슨 설명';
                    break;
                case 'students':
                    csv_content = 'username,email,password,first_name,last_name,display_name,phone\n';
                    csv_content += 'student1,student1@example.com,password123,김,철수,김철수,010-1234-5678\n';
                    csv_content += 'student2,student2@example.com,password123,이,영희,이영희,010-2345-6789';
                    break;
                case 'enrollments':
                    csv_content = 'user_email,course_id,duration,start_date\n';
                    csv_content += 'student1@example.com,1,90,2024-01-01\n';
                    csv_content += 'student2@example.com,1,90,2024-01-01';
                    break;
            }
            
            downloadCSV(csv_content, type + '_template.csv');
        }
        
        function downloadCSV(csv_content, filename) {
            var blob = new Blob([csv_content], { type: 'text/csv;charset=utf-8;' });
            var link = document.createElement('a');
            var url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        
        function uploadCSV(form, type) {
            var formData = new FormData(form);
            formData.append('action', 'lectus_bulk_upload_' + type);
            formData.append('nonce', lectus_ajax.nonce);
            
            $('#upload-progress').show();
            $('.progress-fill').css('width', '0%');
            $('#progress-text').text('0%');
            $('#upload-results').html('');
            
            $.ajax({
                url: lectus_ajax.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            var percentComplete = (e.loaded / e.total) * 100;
                            $('.progress-fill').css('width', percentComplete + '%');
                            $('#progress-text').text(Math.round(percentComplete) + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    $('.progress-fill').css('width', '100%');
                    $('#progress-text').text('100%');
                    
                    if (response.success) {
                        $('#upload-results').html('<div class="result-item result-success">' + response.data.message + '</div>');
                        if (response.data.results) {
                            response.data.results.forEach(function(result) {
                                var className = result.success ? 'result-success' : 'result-error';
                                $('#upload-results').append('<div class="result-item ' + className + '">' + result.message + '</div>');
                            });
                        }
                        form.reset();
                    } else {
                        $('#upload-results').html('<div class="result-item result-error">' + response.data.message + '</div>');
                    }
                },
                error: function() {
                    $('#upload-results').html('<div class="result-item result-error">업로드 중 오류가 발생했습니다.</div>');
                }
            });
        }
        </script>
        <?php
    }
    
    /**
     * AJAX handler for bulk lesson upload
     */
    public static function ajax_bulk_upload_lessons() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-ajax-nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('publish_lessons')) {
            wp_send_json_error(array('message' => __('권한이 없습니다.', 'lectus-class-system')));
        }
        
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(array('message' => __('CSV 파일 업로드에 실패했습니다.', 'lectus-class-system')));
        }
        
        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        $skip_duplicates = isset($_POST['skip_duplicates']);
        $auto_publish = isset($_POST['auto_publish']);
        
        if (!$course_id) {
            wp_send_json_error(array('message' => __('강의를 선택해주세요.', 'lectus-class-system')));
        }
        
        $file_path = $_FILES['csv_file']['tmp_name'];
        $results = self::process_lesson_csv($file_path, $course_id, $skip_duplicates, $auto_publish);
        
        if ($results['success_count'] > 0) {
            wp_send_json_success(array(
                'message' => sprintf(__('%d개의 레슨이 성공적으로 업로드되었습니다.', 'lectus-class-system'), $results['success_count']),
                'results' => $results['details']
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('레슨 업로드에 실패했습니다.', 'lectus-class-system'),
                'results' => $results['details']
            ));
        }
    }
    
    /**
     * Process lesson CSV file
     */
    private static function process_lesson_csv($file_path, $course_id, $skip_duplicates = true, $auto_publish = true) {
        $results = array(
            'success_count' => 0,
            'error_count' => 0,
            'details' => array()
        );
        
        if (($handle = fopen($file_path, 'r')) !== FALSE) {
            $header = fgetcsv($handle);
            
            // Validate header
            $required_fields = array('title', 'content', 'lesson_type');
            $missing_fields = array_diff($required_fields, $header);
            
            if (!empty($missing_fields)) {
                $results['details'][] = array(
                    'success' => false,
                    'message' => sprintf(__('필수 필드가 누락되었습니다: %s', 'lectus-class-system'), implode(', ', $missing_fields))
                );
                fclose($handle);
                return $results;
            }
            
            $row_number = 1;
            while (($data = fgetcsv($handle)) !== FALSE) {
                $row_number++;
                
                if (count($data) < count($required_fields)) {
                    $results['details'][] = array(
                        'success' => false,
                        'message' => sprintf(__('행 %d: 데이터가 부족합니다.', 'lectus-class-system'), $row_number)
                    );
                    $results['error_count']++;
                    continue;
                }
                
                $lesson_data = array_combine($header, $data);
                
                // Skip if duplicate title exists and skip_duplicates is enabled
                if ($skip_duplicates) {
                    $existing = get_posts(array(
                        'post_type' => 'lesson',
                        'title' => $lesson_data['title'],
                        'post_status' => 'any',
                        'meta_query' => array(
                            array(
                                'key' => '_course_id',
                                'value' => $course_id
                            )
                        )
                    ));
                    
                    if (!empty($existing)) {
                        $results['details'][] = array(
                            'success' => false,
                            'message' => sprintf(__('행 %d: "%s" 제목이 이미 존재하여 건너뛰었습니다.', 'lectus-class-system'), $row_number, $lesson_data['title'])
                        );
                        continue;
                    }
                }
                
                // Create lesson
                $lesson_id = wp_insert_post(array(
                    'post_title' => sanitize_text_field($lesson_data['title']),
                    'post_content' => wp_kses_post($lesson_data['content']),
                    'post_type' => 'lesson',
                    'post_status' => $auto_publish ? 'publish' : 'draft',
                    'menu_order' => isset($lesson_data['order']) ? intval($lesson_data['order']) : $row_number
                ));
                
                if ($lesson_id && !is_wp_error($lesson_id)) {
                    // Set meta data
                    update_post_meta($lesson_id, '_course_id', $course_id);
                    update_post_meta($lesson_id, '_lesson_type', sanitize_text_field($lesson_data['lesson_type']));
                    
                    if (isset($lesson_data['duration'])) {
                        update_post_meta($lesson_id, '_lesson_duration', intval($lesson_data['duration']));
                    }
                    
                    if (isset($lesson_data['video_url']) && !empty($lesson_data['video_url'])) {
                        update_post_meta($lesson_id, '_video_url', esc_url_raw($lesson_data['video_url']));
                    }
                    
                    if (isset($lesson_data['description'])) {
                        update_post_meta($lesson_id, '_lesson_description', sanitize_text_field($lesson_data['description']));
                    }
                    
                    update_post_meta($lesson_id, '_completion_criteria', 'view');
                    
                    $results['success_count']++;
                    $results['details'][] = array(
                        'success' => true,
                        'message' => sprintf(__('행 %d: "%s" 레슨이 생성되었습니다.', 'lectus-class-system'), $row_number, $lesson_data['title'])
                    );
                } else {
                    $results['error_count']++;
                    $results['details'][] = array(
                        'success' => false,
                        'message' => sprintf(__('행 %d: "%s" 레슨 생성에 실패했습니다.', 'lectus-class-system'), $row_number, $lesson_data['title'])
                    );
                }
            }
            
            fclose($handle);
        }
        
        return $results;
    }
    
    /**
     * AJAX handler for bulk student upload
     */
    public static function ajax_bulk_upload_students() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-ajax-nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('create_users')) {
            wp_send_json_error(array('message' => __('권한이 없습니다.', 'lectus-class-system')));
        }
        
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(array('message' => __('CSV 파일 업로드에 실패했습니다.', 'lectus-class-system')));
        }
        
        $send_welcome_email = isset($_POST['send_welcome_email']);
        $auto_assign_student_role = isset($_POST['auto_assign_student_role']);
        
        $file_path = $_FILES['csv_file']['tmp_name'];
        $results = self::process_student_csv($file_path, $send_welcome_email, $auto_assign_student_role);
        
        if ($results['success_count'] > 0) {
            wp_send_json_success(array(
                'message' => sprintf(__('%d명의 학생이 성공적으로 등록되었습니다.', 'lectus-class-system'), $results['success_count']),
                'results' => $results['details']
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('학생 등록에 실패했습니다.', 'lectus-class-system'),
                'results' => $results['details']
            ));
        }
    }
    
    /**
     * Process student CSV file
     */
    private static function process_student_csv($file_path, $send_welcome_email = false, $auto_assign_student_role = true) {
        $results = array(
            'success_count' => 0,
            'error_count' => 0,
            'details' => array()
        );
        
        if (($handle = fopen($file_path, 'r')) !== FALSE) {
            $header = fgetcsv($handle);
            
            // Validate header
            $required_fields = array('username', 'email', 'password');
            $missing_fields = array_diff($required_fields, $header);
            
            if (!empty($missing_fields)) {
                $results['details'][] = array(
                    'success' => false,
                    'message' => sprintf(__('필수 필드가 누락되었습니다: %s', 'lectus-class-system'), implode(', ', $missing_fields))
                );
                fclose($handle);
                return $results;
            }
            
            $row_number = 1;
            while (($data = fgetcsv($handle)) !== FALSE) {
                $row_number++;
                
                if (count($data) < count($required_fields)) {
                    $results['details'][] = array(
                        'success' => false,
                        'message' => sprintf(__('행 %d: 데이터가 부족합니다.', 'lectus-class-system'), $row_number)
                    );
                    $results['error_count']++;
                    continue;
                }
                
                $student_data = array_combine($header, $data);
                
                // Check if username or email already exists
                if (username_exists($student_data['username']) || email_exists($student_data['email'])) {
                    $results['details'][] = array(
                        'success' => false,
                        'message' => sprintf(__('행 %d: 사용자명 또는 이메일이 이미 존재합니다.', 'lectus-class-system'), $row_number)
                    );
                    $results['error_count']++;
                    continue;
                }
                
                // Create user
                $user_id = wp_create_user(
                    sanitize_user($student_data['username']),
                    $student_data['password'],
                    sanitize_email($student_data['email'])
                );
                
                if ($user_id && !is_wp_error($user_id)) {
                    // Update user meta
                    if (isset($student_data['first_name'])) {
                        update_user_meta($user_id, 'first_name', sanitize_text_field($student_data['first_name']));
                    }
                    
                    if (isset($student_data['last_name'])) {
                        update_user_meta($user_id, 'last_name', sanitize_text_field($student_data['last_name']));
                    }
                    
                    if (isset($student_data['display_name'])) {
                        wp_update_user(array(
                            'ID' => $user_id,
                            'display_name' => sanitize_text_field($student_data['display_name'])
                        ));
                    }
                    
                    if (isset($student_data['phone'])) {
                        update_user_meta($user_id, 'phone', sanitize_text_field($student_data['phone']));
                    }
                    
                    // Assign student role
                    if ($auto_assign_student_role) {
                        $user = new WP_User($user_id);
                        $user->add_role('lectus_student');
                    }
                    
                    // Send welcome email if requested
                    if ($send_welcome_email) {
                        wp_new_user_notification($user_id, null, 'both');
                    }
                    
                    $results['success_count']++;
                    $results['details'][] = array(
                        'success' => true,
                        'message' => sprintf(__('행 %d: "%s" 학생이 등록되었습니다.', 'lectus-class-system'), $row_number, $student_data['username'])
                    );
                } else {
                    $results['error_count']++;
                    $error_message = is_wp_error($user_id) ? $user_id->get_error_message() : __('알 수 없는 오류', 'lectus-class-system');
                    $results['details'][] = array(
                        'success' => false,
                        'message' => sprintf(__('행 %d: "%s" 학생 등록에 실패했습니다. (%s)', 'lectus-class-system'), $row_number, $student_data['username'], $error_message)
                    );
                }
            }
            
            fclose($handle);
        }
        
        return $results;
    }
    
    /**
     * AJAX handler for bulk enrollment
     */
    public static function ajax_bulk_enroll_students() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lectus-ajax-nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_students')) {
            wp_send_json_error(array('message' => __('권한이 없습니다.', 'lectus-class-system')));
        }
        
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(array('message' => __('CSV 파일 업로드에 실패했습니다.', 'lectus-class-system')));
        }
        
        $send_enrollment_email = isset($_POST['send_enrollment_email']);
        $skip_existing = isset($_POST['skip_existing']);
        
        $file_path = $_FILES['csv_file']['tmp_name'];
        $results = self::process_enrollment_csv($file_path, $send_enrollment_email, $skip_existing);
        
        if ($results['success_count'] > 0) {
            wp_send_json_success(array(
                'message' => sprintf(__('%d건의 수강 등록이 완료되었습니다.', 'lectus-class-system'), $results['success_count']),
                'results' => $results['details']
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('수강 등록에 실패했습니다.', 'lectus-class-system'),
                'results' => $results['details']
            ));
        }
    }
    
    /**
     * Process enrollment CSV file
     */
    private static function process_enrollment_csv($file_path, $send_enrollment_email = true, $skip_existing = true) {
        $results = array(
            'success_count' => 0,
            'error_count' => 0,
            'details' => array()
        );
        
        if (($handle = fopen($file_path, 'r')) !== FALSE) {
            $header = fgetcsv($handle);
            
            // Validate header
            $required_fields = array('user_email', 'course_id');
            $missing_fields = array_diff($required_fields, $header);
            
            if (!empty($missing_fields)) {
                $results['details'][] = array(
                    'success' => false,
                    'message' => sprintf(__('필수 필드가 누락되었습니다: %s', 'lectus-class-system'), implode(', ', $missing_fields))
                );
                fclose($handle);
                return $results;
            }
            
            $row_number = 1;
            while (($data = fgetcsv($handle)) !== FALSE) {
                $row_number++;
                
                if (count($data) < count($required_fields)) {
                    $results['details'][] = array(
                        'success' => false,
                        'message' => sprintf(__('행 %d: 데이터가 부족합니다.', 'lectus-class-system'), $row_number)
                    );
                    $results['error_count']++;
                    continue;
                }
                
                $enrollment_data = array_combine($header, $data);
                
                // Get user by email
                $user = get_user_by('email', sanitize_email($enrollment_data['user_email']));
                if (!$user) {
                    $results['details'][] = array(
                        'success' => false,
                        'message' => sprintf(__('행 %d: 이메일 "%s"에 해당하는 사용자가 없습니다.', 'lectus-class-system'), $row_number, $enrollment_data['user_email'])
                    );
                    $results['error_count']++;
                    continue;
                }
                
                $course_id = intval($enrollment_data['course_id']);
                $course = get_post($course_id);
                if (!$course || $course->post_type !== 'coursesingle') {
                    $results['details'][] = array(
                        'success' => false,
                        'message' => sprintf(__('행 %d: 강의 ID %d가 유효하지 않습니다.', 'lectus-class-system'), $row_number, $course_id)
                    );
                    $results['error_count']++;
                    continue;
                }
                
                // Check if already enrolled
                if ($skip_existing && Lectus_Enrollment::is_enrolled($user->ID, $course_id)) {
                    $results['details'][] = array(
                        'success' => false,
                        'message' => sprintf(__('행 %d: "%s" 사용자는 이미 해당 강의에 등록되어 있습니다.', 'lectus-class-system'), $row_number, $user->user_email)
                    );
                    continue;
                }
                
                // Get duration
                $duration = isset($enrollment_data['duration']) ? intval($enrollment_data['duration']) : 0;
                if (!$duration) {
                    $duration = get_option('lectus_default_access_duration', 365);
                }
                
                // Enroll student
                $enrollment_id = Lectus_Enrollment::enroll($user->ID, $course_id, 0, $duration);
                
                if ($enrollment_id) {
                    $results['success_count']++;
                    $results['details'][] = array(
                        'success' => true,
                        'message' => sprintf(__('행 %d: "%s" 사용자가 "%s" 강의에 등록되었습니다.', 'lectus-class-system'), $row_number, $user->user_email, $course->post_title)
                    );
                } else {
                    $results['error_count']++;
                    $results['details'][] = array(
                        'success' => false,
                        'message' => sprintf(__('행 %d: "%s" 사용자의 수강 등록에 실패했습니다.', 'lectus-class-system'), $row_number, $user->user_email)
                    );
                }
            }
            
            fclose($handle);
        }
        
        return $results;
    }
}
?>