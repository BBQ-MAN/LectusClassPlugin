<?php
/**
 * Q&A 문제 진단 스크립트
 * 
 * 사용법: wp eval-file tests/diagnose-qa-issue.php
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

debug_output("===== Q&A 문제 진단 시작 =====", 'info');

global $wpdb;
$table_name = $wpdb->prefix . 'lectus_qa';

// 1. 테이블 존재 및 구조 확인
debug_output("\n1. 테이블 확인...", 'info');
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
if ($table_exists) {
    debug_output("✓ 테이블이 존재합니다: $table_name", 'success');
    
    // 테이블 구조 확인
    $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name");
    debug_output("\n테이블 컬럼:", 'info');
    foreach ($columns as $column) {
        debug_output("  - {$column->Field}: {$column->Type}", 'info');
    }
} else {
    debug_output("✗ 테이블이 없습니다!", 'error');
    debug_output("  테이블을 생성합니다...", 'warning');
    Lectus_QA::create_table();
}

// 2. 테스트 질문 직접 삽입
debug_output("\n2. 테스트 질문 직접 삽입...", 'info');

$test_data = array(
    'course_id' => 15,  // 테스트 코스 ID
    'lesson_id' => null,
    'user_id' => 1,     // 관리자
    'type' => 'question',
    'title' => '진단 테스트 질문',
    'content' => '이것은 진단을 위한 테스트 질문입니다.',
    'status' => 'approved',
    'created_at' => current_time('mysql')
);

$result = $wpdb->insert($table_name, $test_data);

if ($result === false) {
    debug_output("✗ 삽입 실패!", 'error');
    debug_output("  에러: " . $wpdb->last_error, 'error');
    debug_output("  쿼리: " . $wpdb->last_query, 'error');
} else {
    $insert_id = $wpdb->insert_id;
    debug_output("✓ 테스트 질문 삽입 성공! ID: $insert_id", 'success');
}

// 3. submit_question 함수 테스트
debug_output("\n3. submit_question 함수 테스트...", 'info');

$title = "함수 테스트 질문";
$content = "submit_question 함수를 통한 테스트입니다. 이 내용은 충분히 깁니다.";

$question_id = Lectus_QA::submit_question(15, null, 1, $title, $content);

if ($question_id) {
    debug_output("✓ submit_question 성공! ID: $question_id", 'success');
} else {
    debug_output("✗ submit_question 실패!", 'error');
    debug_output("  Last error: " . $wpdb->last_error, 'error');
}

// 4. Sanitization 테스트
debug_output("\n4. Sanitization 테스트...", 'info');

$test_contents = array(
    "일반 텍스트",
    "줄바꿈이\n포함된\n텍스트",
    "여러    공백이     있는 텍스트",
    "<script>alert('xss')</script>위험한 텍스트",
    "아주 긴 텍스트입니다. " . str_repeat("테스트 ", 10)
);

foreach ($test_contents as $i => $test_content) {
    debug_output("\n테스트 " . ($i + 1) . ": " . substr($test_content, 0, 30) . "...", 'info');
    
    // Private 메소드이므로 Reflection 사용
    $reflection = new ReflectionClass('Lectus_QA');
    $method = $reflection->getMethod('sanitize_qa_content');
    $method->setAccessible(true);
    
    $sanitized = $method->invoke(null, $test_content);
    debug_output("  원본 길이: " . strlen($test_content), 'info');
    debug_output("  처리 후 길이: " . strlen($sanitized), 'info');
    
    if (empty($sanitized)) {
        debug_output("  ⚠ 내용이 비어있게 됨!", 'warning');
    } else {
        debug_output("  ✓ 내용 유지됨", 'success');
    }
}

// 5. 현재 데이터 확인
debug_output("\n5. 현재 저장된 질문 확인...", 'info');

$questions = $wpdb->get_results(
    "SELECT id, course_id, user_id, title, created_at 
     FROM $table_name 
     WHERE type = 'question' 
     ORDER BY created_at DESC 
     LIMIT 5"
);

if ($questions) {
    debug_output("최근 질문 " . count($questions) . "개:", 'success');
    foreach ($questions as $q) {
        debug_output("  - ID: {$q->id}, Course: {$q->course_id}, Title: {$q->title}", 'info');
    }
} else {
    debug_output("저장된 질문이 없습니다.", 'warning');
}

// 6. 권한 및 사용자 확인
debug_output("\n6. 현재 사용자 권한 확인...", 'info');

$current_user = wp_get_current_user();
if ($current_user->ID) {
    debug_output("현재 사용자: {$current_user->user_login} (ID: {$current_user->ID})", 'info');
    if (current_user_can('manage_options')) {
        debug_output("✓ 관리자 권한 있음", 'success');
    }
} else {
    debug_output("⚠ 로그인되지 않음", 'warning');
}

// 7. Rate Limit 확인
debug_output("\n7. Rate Limit 상태...", 'info');

$user_id = $current_user->ID ?: 1;
$transient_key = 'lectus_qa_rate_limit_' . $user_id . '_question';
$attempts = get_transient($transient_key);

if ($attempts) {
    debug_output("현재 시도 횟수: $attempts", 'info');
    if ($attempts >= 30) {
        debug_output("⚠ Rate Limit에 도달했습니다!", 'warning');
    }
} else {
    debug_output("✓ Rate Limit 카운터 없음 (정상)", 'success');
}

// 요약
debug_output("\n===== 진단 요약 =====", 'info');

$issues = array();

if (!$table_exists) {
    $issues[] = "Q&A 테이블이 없음";
}

if (isset($result) && $result === false) {
    $issues[] = "데이터베이스 삽입 실패";
}

if ($attempts >= 30) {
    $issues[] = "Rate Limit 초과";
}

if (empty($issues)) {
    debug_output("✓ 모든 검사 통과! Q&A 시스템이 정상적으로 작동해야 합니다.", 'success');
} else {
    debug_output("✗ 발견된 문제:", 'error');
    foreach ($issues as $issue) {
        debug_output("  - $issue", 'error');
    }
}

debug_output("\n===== 진단 완료 =====", 'success');