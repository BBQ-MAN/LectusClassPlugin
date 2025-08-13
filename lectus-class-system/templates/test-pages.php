<?php
/**
 * Test Pages Creator for Lectus Class System
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create test pages with shortcodes
 */
function lectus_create_test_pages() {
    $pages = array(
        array(
            'title' => '강의 목록',
            'slug' => 'courses',
            'content' => '[lectus_courses]'
        ),
        array(
            'title' => '내 강의',
            'slug' => 'my-courses', 
            'content' => '[lectus_my_courses]'
        ),
        array(
            'title' => '내 수료증',
            'slug' => 'my-certificates',
            'content' => '[lectus_certificates]'
        ),
        array(
            'title' => '학습 대시보드',
            'slug' => 'student-dashboard',
            'content' => '[lectus_student_dashboard]'
        ),
        array(
            'title' => '수료증 확인',
            'slug' => 'certificate-verify',
            'content' => '
<h3>수료증 진위 확인</h3>
<p>수료증 번호를 입력하여 진위를 확인하세요.</p>
<form method="get" action="">
    <label for="cert_number">수료증 번호:</label><br>
    <input type="text" id="cert_number" name="cert_number" placeholder="예: LCS-2024-123456-ABC123" style="width: 300px; padding: 8px; margin: 10px 0;"><br>
    <input type="submit" value="확인" class="button">
</form>

[lectus_certificate_verify]
            '
        )
    );
    
    $created_pages = array();
    
    foreach ($pages as $page_data) {
        // Check if page already exists
        $existing_page = get_page_by_path($page_data['slug']);
        if ($existing_page) {
            continue;
        }
        
        // Create page
        $page_id = wp_insert_post(array(
            'post_title' => $page_data['title'],
            'post_name' => $page_data['slug'],
            'post_content' => $page_data['content'],
            'post_status' => 'publish',
            'post_type' => 'page',
            'comment_status' => 'closed',
            'ping_status' => 'closed'
        ));
        
        if ($page_id && !is_wp_error($page_id)) {
            $created_pages[] = array(
                'title' => $page_data['title'],
                'url' => get_permalink($page_id),
                'id' => $page_id
            );
        }
    }
    
    return $created_pages;
}

/**
 * NOTE: Menu registration removed - this functionality is now integrated into settings page
 * The functions below are called from the settings page only
 */

function lectus_test_pages_admin_page() {
    if (isset($_POST['create_test_pages']) && wp_verify_nonce($_POST['test_pages_nonce'], 'create_test_pages')) {
        $created_pages = lectus_create_test_pages();
        
        if (!empty($created_pages)) {
            echo '<div class="notice notice-success"><p>';
            echo '<strong>테스트 페이지가 성공적으로 생성되었습니다!</strong><br>';
            foreach ($created_pages as $page) {
                echo sprintf(
                    '✓ <a href="%s" target="_blank">%s</a><br>',
                    esc_url($page['url']),
                    esc_html($page['title'])
                );
            }
            echo '</p></div>';
        } else {
            echo '<div class="notice notice-info"><p>모든 테스트 페이지가 이미 존재합니다.</p></div>';
        }
    }
    
    ?>
    <div class="wrap">
        <h1><?php _e('테스트 페이지 생성', 'lectus-class-system'); ?></h1>
        
        <div class="notice notice-info">
            <p><?php _e('이 기능은 강의 시스템의 쇼트코드가 포함된 테스트 페이지를 자동으로 생성합니다.', 'lectus-class-system'); ?></p>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field('create_test_pages', 'test_pages_nonce'); ?>
            
            <h2>생성될 페이지 목록</h2>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>페이지 제목</th>
                        <th>슬러그</th>
                        <th>쇼트코드</th>
                        <th>설명</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>강의 목록</td>
                        <td>/courses/</td>
                        <td>[lectus_courses]</td>
                        <td>모든 강의를 표시하는 페이지</td>
                    </tr>
                    <tr>
                        <td>내 강의</td>
                        <td>/my-courses/</td>
                        <td>[lectus_my_courses]</td>
                        <td>로그인한 사용자의 수강 강의 목록</td>
                    </tr>
                    <tr>
                        <td>내 수료증</td>
                        <td>/my-certificates/</td>
                        <td>[lectus_certificates]</td>
                        <td>로그인한 사용자의 수료증 목록</td>
                    </tr>
                    <tr>
                        <td>학습 대시보드</td>
                        <td>/student-dashboard/</td>
                        <td>[lectus_student_dashboard]</td>
                        <td>학생 대시보드 (진도, 통계 등)</td>
                    </tr>
                    <tr>
                        <td>수료증 확인</td>
                        <td>/certificate-verify/</td>
                        <td>[lectus_certificate_verify]</td>
                        <td>수료증 진위 확인 페이지</td>
                    </tr>
                </tbody>
            </table>
            
            <p class="submit">
                <input type="submit" name="create_test_pages" class="button button-primary" 
                       value="<?php _e('테스트 페이지 생성', 'lectus-class-system'); ?>" 
                       onclick="return confirm('테스트 페이지를 생성하시겠습니까?');" />
            </p>
        </form>
        
        <hr>
        
        <h2>기존 테스트 페이지</h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th>페이지</th>
                    <th>상태</th>
                    <th>작업</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $test_slugs = array('courses', 'my-courses', 'my-certificates', 'student-dashboard', 'certificate-verify');
                foreach ($test_slugs as $slug) {
                    $page = get_page_by_path($slug);
                    if ($page) {
                        echo '<tr>';
                        echo '<td>' . esc_html($page->post_title) . '</td>';
                        echo '<td><span style="color: green;">✓ 존재함</span></td>';
                        echo '<td>';
                        echo '<a href="' . get_permalink($page->ID) . '" target="_blank" class="button">보기</a> ';
                        echo '<a href="' . get_edit_post_link($page->ID) . '" class="button">편집</a>';
                        echo '</td>';
                        echo '</tr>';
                    } else {
                        echo '<tr>';
                        echo '<td>/' . esc_html($slug) . '/</td>';
                        echo '<td><span style="color: red;">✗ 없음</span></td>';
                        echo '<td>-</td>';
                        echo '</tr>';
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}
?>