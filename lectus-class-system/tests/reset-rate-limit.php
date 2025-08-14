<?php
/**
 * Rate Limit 초기화 스크립트
 * 
 * 사용법: wp eval-file tests/reset-rate-limit.php
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

debug_output("===== Q&A Rate Limit 초기화 =====", 'info');

// 현재 사용자 확인
$user_id = get_current_user_id();
if (!$user_id) {
    $user_id = 1; // 기본 관리자
}

$user = get_user_by('id', $user_id);
debug_output("현재 사용자: {$user->user_login} (ID: $user_id)", 'info');

// Rate limit transient 키
$question_key = 'lectus_qa_rate_limit_' . $user_id . '_question';
$answer_key = 'lectus_qa_rate_limit_' . $user_id . '_answer';

// 현재 카운터 확인
$question_attempts = get_transient($question_key);
$answer_attempts = get_transient($answer_key);

debug_output("\n현재 상태:", 'info');
debug_output("  질문 시도 횟수: " . ($question_attempts ? $question_attempts : 0), 'info');
debug_output("  답변 시도 횟수: " . ($answer_attempts ? $answer_attempts : 0), 'info');

// Rate limit 초기화
delete_transient($question_key);
delete_transient($answer_key);

debug_output("\n✓ Rate limit이 초기화되었습니다.", 'success');

// 모든 사용자의 rate limit 초기화 (선택사항)
$reset_all = false; // true로 변경하면 모든 사용자 초기화

if ($reset_all) {
    global $wpdb;
    
    debug_output("\n모든 사용자의 rate limit 초기화 중...", 'warning');
    
    // lectus_qa_rate_limit으로 시작하는 모든 transient 삭제
    $wpdb->query(
        "DELETE FROM {$wpdb->options} 
         WHERE option_name LIKE '_transient_lectus_qa_rate_limit_%' 
         OR option_name LIKE '_transient_timeout_lectus_qa_rate_limit_%'"
    );
    
    debug_output("✓ 모든 사용자의 rate limit이 초기화되었습니다.", 'success');
}

// 새로운 설정 확인
debug_output("\n새로운 Rate Limit 설정:", 'info');
debug_output("  관리자: 무제한", 'success');
debug_output("  일반 사용자: 10분당 질문 30개, 답변 50개", 'info');

// 관리자 권한 확인
if (current_user_can('manage_options')) {
    debug_output("\n✓ 현재 사용자는 관리자이므로 rate limit이 적용되지 않습니다.", 'success');
} else {
    debug_output("\n⚠ 현재 사용자는 일반 사용자이므로 rate limit이 적용됩니다.", 'warning');
}

debug_output("\n===== 초기화 완료 =====", 'success');
debug_output("이제 Q&A를 다시 제출할 수 있습니다.", 'info');