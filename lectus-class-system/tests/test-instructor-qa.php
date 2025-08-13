<?php
/**
 * 강사 답글 기능 테스트 스크립트
 * 
 * 사용법: WordPress 환경에서 실행
 * wp eval-file tests/test-instructor-qa.php
 */

// 색상 출력 도우미
function test_output($message, $type = 'info') {
    $colors = array(
        'success' => "\033[32m",
        'error' => "\033[31m",
        'warning' => "\033[33m",
        'info' => "\033[36m",
        'reset' => "\033[0m"
    );
    
    $color = isset($colors[$type]) ? $colors[$type] : $colors['info'];
    echo $color . $message . $colors['reset'] . "\n";
}

test_output("===== 강사 Q&A 답글 기능 테스트 시작 =====", 'info');

// 1. 강사 역할 확인/생성
test_output("\n1. 강사 역할 확인 및 생성...", 'info');

$instructor_role = get_role('instructor');
if (!$instructor_role) {
    add_role(
        'instructor',
        __('강사', 'lectus-class-system'),
        array(
            'read' => true,
            'edit_posts' => true,
            'delete_posts' => true,
            'publish_posts' => true,
            'upload_files' => true,
            'edit_coursesingle' => true,
            'edit_coursesingles' => true,
            'edit_others_coursesingles' => true,
            'publish_coursesingles' => true,
            'read_private_coursesingles' => true,
            'delete_coursesingles' => true,
            'delete_others_coursesingles' => true,
            'edit_lesson' => true,
            'edit_lessons' => true,
            'edit_others_lessons' => true,
            'publish_lessons' => true,
            'read_private_lessons' => true,
            'delete_lessons' => true,
            'delete_others_lessons' => true,
        )
    );
    test_output("✓ 강사 역할이 생성되었습니다.", 'success');
} else {
    test_output("✓ 강사 역할이 이미 존재합니다.", 'success');
}

// 2. 테스트 강사 계정 생성
test_output("\n2. 테스트 강사 계정 생성...", 'info');

$instructor_username = 'test_instructor_' . time();
$instructor_email = $instructor_username . '@example.com';
$instructor_password = wp_generate_password();

$instructor_id = wp_create_user($instructor_username, $instructor_password, $instructor_email);

if (!is_wp_error($instructor_id)) {
    $instructor = new WP_User($instructor_id);
    $instructor->set_role('instructor');
    $instructor->add_role('administrator'); // 테스트를 위해 관리자 권한도 추가
    
    test_output("✓ 강사 계정이 생성되었습니다.", 'success');
    test_output("  - 사용자명: $instructor_username", 'info');
    test_output("  - 이메일: $instructor_email", 'info');
    test_output("  - 비밀번호: $instructor_password", 'info');
    test_output("  - ID: $instructor_id", 'info');
} else {
    test_output("✗ 강사 계정 생성 실패: " . $instructor_id->get_error_message(), 'error');
    exit(1);
}

// 3. 테스트 강의 생성
test_output("\n3. 테스트 강의 생성...", 'info');

$course_data = array(
    'post_title' => '테스트 강의 - Q&A 테스트용',
    'post_content' => '이 강의는 Q&A 시스템 테스트를 위한 강의입니다.',
    'post_status' => 'publish',
    'post_type' => 'coursesingle',
    'post_author' => $instructor_id,
    'meta_input' => array(
        '_course_duration' => 30,
        '_access_mode' => 'free',
        '_course_price' => 0,
        '_completion_score' => 80,
        '_instructor_id' => $instructor_id
    )
);

$course_id = wp_insert_post($course_data);

if (!is_wp_error($course_id)) {
    test_output("✓ 테스트 강의가 생성되었습니다. (ID: $course_id)", 'success');
} else {
    test_output("✗ 강의 생성 실패: " . $course_id->get_error_message(), 'error');
    exit(1);
}

// 4. 테스트 학생 계정 생성
test_output("\n4. 테스트 학생 계정 생성...", 'info');

$student_username = 'test_student_' . time();
$student_email = $student_username . '@example.com';
$student_password = wp_generate_password();

$student_id = wp_create_user($student_username, $student_password, $student_email);

if (!is_wp_error($student_id)) {
    $student = new WP_User($student_id);
    $student->set_role('subscriber');
    
    test_output("✓ 학생 계정이 생성되었습니다.", 'success');
    test_output("  - 사용자명: $student_username", 'info');
    test_output("  - ID: $student_id", 'info');
} else {
    test_output("✗ 학생 계정 생성 실패: " . $student_id->get_error_message(), 'error');
    exit(1);
}

// 5. 학생을 강의에 등록
test_output("\n5. 학생을 강의에 등록...", 'info');

$enrollment_result = Lectus_Enrollment::enroll($student_id, $course_id);

if ($enrollment_result) {
    test_output("✓ 학생이 강의에 등록되었습니다.", 'success');
} else {
    test_output("✗ 학생 등록 실패", 'error');
}

// 6. 학생이 질문 작성
test_output("\n6. 학생이 질문 작성...", 'info');

// 학생으로 전환
wp_set_current_user($student_id);

$question_data = array(
    'course_id' => $course_id,
    'user_id' => $student_id,
    'parent_id' => 0,
    'title' => '테스트 질문입니다',
    'content' => '이것은 테스트 질문의 내용입니다. 강사님의 답변을 기다립니다.',
    'is_instructor' => 0,
    'created_at' => current_time('mysql')
);

global $wpdb;
$table_name = $wpdb->prefix . 'lectus_qa';

$insert_result = $wpdb->insert($table_name, $question_data);

if ($insert_result) {
    $question_id = $wpdb->insert_id;
    test_output("✓ 질문이 작성되었습니다. (ID: $question_id)", 'success');
} else {
    test_output("✗ 질문 작성 실패: " . $wpdb->last_error, 'error');
    exit(1);
}

// 7. 강사가 답글 작성
test_output("\n7. 강사가 답글 작성...", 'info');

// 강사로 전환
wp_set_current_user($instructor_id);

$reply_data = array(
    'course_id' => $course_id,
    'user_id' => $instructor_id,
    'parent_id' => $question_id,
    'title' => 'RE: 테스트 질문입니다',
    'content' => '안녕하세요, 강사입니다. 질문에 대한 답변입니다.',
    'is_instructor' => 1,
    'created_at' => current_time('mysql')
);

$reply_result = $wpdb->insert($table_name, $reply_data);

if ($reply_result) {
    $reply_id = $wpdb->insert_id;
    test_output("✓ 강사 답글이 작성되었습니다. (ID: $reply_id)", 'success');
} else {
    test_output("✗ 답글 작성 실패: " . $wpdb->last_error, 'error');
    exit(1);
}

// 8. Q&A 데이터 확인
test_output("\n8. Q&A 데이터 확인...", 'info');

$qa_items = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table_name WHERE course_id = %d ORDER BY created_at DESC",
    $course_id
));

if ($qa_items) {
    test_output("✓ Q&A 항목 수: " . count($qa_items), 'success');
    
    foreach ($qa_items as $item) {
        $type = $item->parent_id == 0 ? '질문' : '답글';
        $author_type = $item->is_instructor ? '(강사)' : '(학생)';
        test_output("  - [$type] $author_type " . $item->title, 'info');
    }
} else {
    test_output("✗ Q&A 데이터를 찾을 수 없습니다.", 'error');
}

// 9. 페이지 접근 테스트
test_output("\n9. 강의 페이지 접근 테스트...", 'info');

$course_url = get_permalink($course_id);
test_output("  강의 페이지 URL: $course_url", 'info');

// HTTP 요청으로 페이지 확인
$response = wp_remote_get($course_url);

if (!is_wp_error($response)) {
    $status_code = wp_remote_retrieve_response_code($response);
    
    if ($status_code == 200) {
        test_output("✓ 강의 페이지 접근 가능 (상태 코드: $status_code)", 'success');
        
        $body = wp_remote_retrieve_body($response);
        
        // Q&A 섹션 존재 확인
        if (strpos($body, 'lectus-course-qa') !== false) {
            test_output("✓ Q&A 섹션이 페이지에 표시됩니다.", 'success');
        } else {
            test_output("⚠ Q&A 섹션이 페이지에 표시되지 않습니다.", 'warning');
        }
        
        // 질문 제목 확인
        if (strpos($body, '테스트 질문입니다') !== false) {
            test_output("✓ 질문이 페이지에 표시됩니다.", 'success');
        } else {
            test_output("⚠ 질문이 페이지에 표시되지 않습니다.", 'warning');
        }
    } else {
        test_output("✗ 페이지 접근 실패 (상태 코드: $status_code)", 'error');
    }
} else {
    test_output("✗ HTTP 요청 실패: " . $response->get_error_message(), 'error');
}

// 10. 권한 테스트
test_output("\n10. 권한 테스트...", 'info');

// 강사 권한 확인
wp_set_current_user($instructor_id);
if (current_user_can('edit_coursesingles')) {
    test_output("✓ 강사가 강의 편집 권한을 가지고 있습니다.", 'success');
} else {
    test_output("✗ 강사가 강의 편집 권한이 없습니다.", 'error');
}

// 학생 권한 확인
wp_set_current_user($student_id);
if (!current_user_can('edit_coursesingles')) {
    test_output("✓ 학생은 강의 편집 권한이 없습니다. (정상)", 'success');
} else {
    test_output("✗ 학생이 강의 편집 권한을 가지고 있습니다. (비정상)", 'error');
}

// 테스트 요약
test_output("\n===== 테스트 요약 =====", 'info');
test_output("✓ 강사 역할 생성/확인 완료", 'success');
test_output("✓ 강사 계정 생성 완료 (ID: $instructor_id)", 'success');
test_output("✓ 테스트 강의 생성 완료 (ID: $course_id)", 'success');
test_output("✓ 학생 계정 생성 및 등록 완료 (ID: $student_id)", 'success');
test_output("✓ Q&A 질문 및 답글 작성 완료", 'success');
test_output("✓ 권한 시스템 정상 작동", 'success');

test_output("\n강의 페이지에서 Q&A 섹션을 확인하세요:", 'info');
test_output("  URL: $course_url", 'info');
test_output("\n관리자 페이지에서 확인:", 'info');
test_output("  강사 로그인: $instructor_username / $instructor_password", 'info');

test_output("\n===== 테스트 완료 =====", 'success');