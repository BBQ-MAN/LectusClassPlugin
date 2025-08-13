<?php
/**
 * Q&A 시스템 디버깅 스크립트
 * 
 * 사용법: wp eval-file tests/test-qa-debug.php
 */

// 색상 출력 도우미
function debug_output($message, $type = 'info') {
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

debug_output("===== Q&A 시스템 디버깅 시작 =====", 'info');

global $wpdb;
$table_name = $wpdb->prefix . 'lectus_qa';
$votes_table = $wpdb->prefix . 'lectus_qa_votes';

// 1. 테이블 존재 확인
debug_output("\n1. 테이블 존재 확인...", 'info');

$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
if ($table_exists) {
    debug_output("✓ Q&A 테이블이 존재합니다: $table_name", 'success');
} else {
    debug_output("✗ Q&A 테이블이 없습니다: $table_name", 'error');
    debug_output("  테이블을 생성합니다...", 'warning');
    
    // 테이블 생성
    Lectus_QA::create_table();
    
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
    if ($table_exists) {
        debug_output("✓ Q&A 테이블이 생성되었습니다", 'success');
    } else {
        debug_output("✗ 테이블 생성 실패", 'error');
        exit(1);
    }
}

// 2. 테이블 구조 확인
debug_output("\n2. 테이블 구조 확인...", 'info');

$columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name");
debug_output("  컬럼 목록:", 'info');
foreach ($columns as $column) {
    debug_output("  - {$column->Field}: {$column->Type} {$column->Null} {$column->Default}", 'info');
}

// is_instructor 필드 확인
$has_is_instructor = false;
foreach ($columns as $column) {
    if ($column->Field === 'is_instructor') {
        $has_is_instructor = true;
        break;
    }
}

if (!$has_is_instructor) {
    debug_output("\n⚠ 'is_instructor' 필드가 없습니다. 이 필드는 필요하지 않을 수 있습니다.", 'warning');
    debug_output("  강사 여부는 user_id와 WordPress 역할로 판단할 수 있습니다.", 'info');
}

// 3. 테스트 데이터 삽입
debug_output("\n3. 테스트 질문 등록...", 'info');

// 테스트용 강의 찾기 또는 생성
$test_course = get_posts(array(
    'post_type' => 'coursesingle',
    'posts_per_page' => 1,
    'post_status' => 'publish'
));

if (empty($test_course)) {
    debug_output("  테스트 강의를 생성합니다...", 'warning');
    $course_id = wp_insert_post(array(
        'post_title' => 'Q&A 테스트 강의',
        'post_content' => '테스트 강의입니다.',
        'post_type' => 'coursesingle',
        'post_status' => 'publish'
    ));
} else {
    $course_id = $test_course[0]->ID;
}

debug_output("  사용할 강의 ID: $course_id", 'info');

// 테스트 사용자 (관리자 사용)
$user_id = 1; // 기본 관리자
$user = get_user_by('id', $user_id);
if ($user) {
    debug_output("  사용자: {$user->user_login} (ID: $user_id)", 'info');
} else {
    debug_output("✗ 사용자를 찾을 수 없습니다", 'error');
    exit(1);
}

// 테스트 질문 데이터
$test_question = array(
    'course_id' => $course_id,
    'lesson_id' => null,
    'user_id' => $user_id,
    'type' => 'question',
    'title' => '테스트 질문 ' . time(),
    'content' => '이것은 테스트 질문의 내용입니다. 시간: ' . current_time('mysql'),
    'status' => 'approved',
    'votes' => 0,
    'is_best_answer' => 0,
    'created_at' => current_time('mysql')
);

debug_output("\n  삽입할 데이터:", 'info');
foreach ($test_question as $key => $value) {
    debug_output("    $key: $value", 'info');
}

// 데이터 삽입
$result = $wpdb->insert($table_name, $test_question);

if ($result === false) {
    debug_output("\n✗ 질문 삽입 실패", 'error');
    debug_output("  에러: " . $wpdb->last_error, 'error');
    debug_output("  쿼리: " . $wpdb->last_query, 'error');
} else {
    $question_id = $wpdb->insert_id;
    debug_output("\n✓ 질문이 성공적으로 삽입되었습니다. ID: $question_id", 'success');
}

// 4. submit_question 함수 테스트
debug_output("\n4. submit_question 함수 테스트...", 'info');

$function_test_id = Lectus_QA::submit_question(
    $course_id,
    null,
    $user_id,
    '함수 테스트 질문 ' . time(),
    '이것은 submit_question 함수를 통한 테스트입니다.'
);

if ($function_test_id) {
    debug_output("✓ submit_question 함수 성공. ID: $function_test_id", 'success');
} else {
    debug_output("✗ submit_question 함수 실패", 'error');
}

// 5. 데이터 조회
debug_output("\n5. 저장된 Q&A 데이터 조회...", 'info');

$questions = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table_name WHERE course_id = %d AND type = 'question' ORDER BY created_at DESC LIMIT 5",
    $course_id
));

if ($questions) {
    debug_output("  최근 질문 " . count($questions) . "개:", 'success');
    foreach ($questions as $q) {
        debug_output("    - ID: {$q->id}, 제목: {$q->title}, 작성시간: {$q->created_at}", 'info');
    }
} else {
    debug_output("  질문이 없습니다.", 'warning');
}

// 6. AJAX 엔드포인트 확인
debug_output("\n6. AJAX 엔드포인트 확인...", 'info');

$ajax_url = admin_url('admin-ajax.php');
debug_output("  AJAX URL: $ajax_url", 'info');

// AJAX 액션 확인
global $wp_filter;
$ajax_actions = array(
    'wp_ajax_lectus_submit_question',
    'wp_ajax_nopriv_lectus_submit_question',
    'wp_ajax_lectus_submit_answer',
    'wp_ajax_lectus_vote_qa'
);

foreach ($ajax_actions as $action) {
    if (isset($wp_filter[$action])) {
        debug_output("  ✓ $action 핸들러가 등록되어 있습니다", 'success');
    } else {
        debug_output("  ✗ $action 핸들러가 등록되지 않았습니다", 'error');
    }
}

// 7. 사용자 등록 상태 확인
debug_output("\n7. 사용자 등록 상태 확인...", 'info');

if (class_exists('Lectus_Enrollment')) {
    $is_enrolled = Lectus_Enrollment::is_enrolled($user_id, $course_id);
    if ($is_enrolled) {
        debug_output("  ✓ 사용자가 강의에 등록되어 있습니다", 'success');
    } else {
        debug_output("  ⚠ 사용자가 강의에 등록되어 있지 않습니다", 'warning');
        
        // 관리자인지 확인
        if (user_can($user_id, 'manage_options')) {
            debug_output("  ✓ 하지만 관리자이므로 질문 가능합니다", 'success');
        } else {
            debug_output("  ✗ 관리자도 아니므로 질문할 수 없습니다", 'error');
        }
    }
}

// 8. Nonce 확인
debug_output("\n8. Nonce 생성 테스트...", 'info');

$test_nonce = wp_create_nonce('lectus-ajax-nonce');
debug_output("  생성된 nonce: $test_nonce", 'info');

$verify = wp_verify_nonce($test_nonce, 'lectus-ajax-nonce');
if ($verify) {
    debug_output("  ✓ Nonce 검증 성공", 'success');
} else {
    debug_output("  ✗ Nonce 검증 실패", 'error');
}

// 요약
debug_output("\n===== 디버깅 요약 =====", 'info');
debug_output("✓ 테이블 상태: " . ($table_exists ? "정상" : "문제있음"), $table_exists ? 'success' : 'error');
debug_output("✓ 데이터 삽입: " . (isset($question_id) && $question_id ? "정상" : "문제있음"), (isset($question_id) && $question_id) ? 'success' : 'error');
debug_output("✓ 함수 테스트: " . ($function_test_id ? "정상" : "문제있음"), $function_test_id ? 'success' : 'error');
debug_output("✓ AJAX 핸들러: " . (isset($wp_filter['wp_ajax_lectus_submit_question']) ? "정상" : "문제있음"), isset($wp_filter['wp_ajax_lectus_submit_question']) ? 'success' : 'error');

debug_output("\n===== 디버깅 완료 =====", 'success');