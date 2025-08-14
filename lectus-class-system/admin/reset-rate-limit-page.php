<?php
/**
 * Rate Limit 관리 페이지
 * 
 * 관리자 메뉴에서 Rate Limit을 관리할 수 있는 페이지
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Check admin permissions
if (!current_user_can('manage_options')) {
    wp_die(__('권한이 없습니다.', 'lectus-class-system'));
}

// Handle form submission
if (isset($_POST['reset_rate_limit'])) {
    check_admin_referer('lectus_reset_rate_limit');
    
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    
    if ($user_id) {
        // Reset specific user
        delete_transient('lectus_qa_rate_limit_' . $user_id . '_question');
        delete_transient('lectus_qa_rate_limit_' . $user_id . '_answer');
        $message = sprintf(__('사용자 ID %d의 Rate Limit이 초기화되었습니다.', 'lectus-class-system'), $user_id);
    } else {
        // Reset all users
        global $wpdb;
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_lectus_qa_rate_limit_%' 
             OR option_name LIKE '_transient_timeout_lectus_qa_rate_limit_%'"
        );
        $message = __('모든 사용자의 Rate Limit이 초기화되었습니다.', 'lectus-class-system');
    }
    
    echo '<div class="notice notice-success"><p>' . esc_html($message) . '</p></div>';
}

// Get current rate limit status
global $wpdb;
$rate_limits = $wpdb->get_results(
    "SELECT option_name, option_value 
     FROM {$wpdb->options} 
     WHERE option_name LIKE '_transient_lectus_qa_rate_limit_%'
     AND option_name NOT LIKE '_transient_timeout_%'
     ORDER BY option_name"
);

?>
<div class="wrap">
    <h1><?php _e('Q&A Rate Limit 관리', 'lectus-class-system'); ?></h1>
    
    <div class="card">
        <h2><?php _e('현재 설정', 'lectus-class-system'); ?></h2>
        <table class="form-table">
            <tr>
                <th><?php _e('관리자', 'lectus-class-system'); ?></th>
                <td><strong style="color: green;"><?php _e('무제한', 'lectus-class-system'); ?></strong></td>
            </tr>
            <tr>
                <th><?php _e('일반 사용자', 'lectus-class-system'); ?></th>
                <td>
                    <?php _e('10분당 질문 30개, 답변 50개', 'lectus-class-system'); ?>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="card">
        <h2><?php _e('현재 Rate Limit 상태', 'lectus-class-system'); ?></h2>
        <?php if ($rate_limits): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('사용자 ID', 'lectus-class-system'); ?></th>
                        <th><?php _e('유형', 'lectus-class-system'); ?></th>
                        <th><?php _e('시도 횟수', 'lectus-class-system'); ?></th>
                        <th><?php _e('사용자명', 'lectus-class-system'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rate_limits as $limit): 
                        // Parse the transient key
                        if (preg_match('/lectus_qa_rate_limit_(\d+)_(\w+)/', $limit->option_name, $matches)) {
                            $user_id = $matches[1];
                            $type = $matches[2];
                            $user = get_user_by('id', $user_id);
                            ?>
                            <tr>
                                <td><?php echo esc_html($user_id); ?></td>
                                <td><?php echo esc_html($type === 'question' ? '질문' : '답변'); ?></td>
                                <td>
                                    <span style="color: <?php echo $limit->option_value >= 5 ? 'red' : 'black'; ?>">
                                        <?php echo esc_html($limit->option_value); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    if ($user) {
                                        echo esc_html($user->user_login . ' (' . $user->display_name . ')');
                                    } else {
                                        echo __('알 수 없는 사용자', 'lectus-class-system');
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><?php _e('현재 활성화된 Rate Limit이 없습니다.', 'lectus-class-system'); ?></p>
        <?php endif; ?>
    </div>
    
    <div class="card">
        <h2><?php _e('Rate Limit 초기화', 'lectus-class-system'); ?></h2>
        <form method="post" action="">
            <?php wp_nonce_field('lectus_reset_rate_limit'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="user_id"><?php _e('사용자 ID', 'lectus-class-system'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="user_id" name="user_id" value="" class="small-text" />
                        <p class="description">
                            <?php _e('특정 사용자의 Rate Limit만 초기화하려면 사용자 ID를 입력하세요. 비워두면 모든 사용자가 초기화됩니다.', 'lectus-class-system'); ?>
                        </p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" name="reset_rate_limit" class="button button-primary">
                    <?php _e('Rate Limit 초기화', 'lectus-class-system'); ?>
                </button>
            </p>
        </form>
    </div>
    
    <div class="card">
        <h2><?php _e('문제 해결 가이드', 'lectus-class-system'); ?></h2>
        <ul>
            <li><strong>429 오류 (Too Many Requests):</strong> Rate Limit에 도달했습니다. 위에서 초기화하세요.</li>
            <li><strong>403 오류:</strong> Nonce 검증 실패. 페이지를 새로고침하세요.</li>
            <li><strong>네트워크 오류:</strong> AJAX URL이 올바른지 확인하세요.</li>
        </ul>
    </div>
</div>