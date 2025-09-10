<?php
/**
 * Test Restoration Functionality
 *
 * @package LectusAcademy
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Lectus_Academy_Test_Restoration
 * 
 * Handles test environment restoration and setup
 */
class Lectus_Academy_Test_Restoration {
    
    /**
     * Check if Lectus Class System plugin is active
     * 
     * @return bool
     */
    public static function is_lectus_plugin_active() {
        return class_exists('Lectus_Class_System');
    }
    
    /**
     * Check if WooCommerce is active
     * 
     * @return bool
     */
    public static function is_woocommerce_active() {
        return class_exists('WooCommerce');
    }
    
    /**
     * Create essential pages for the theme
     * 
     * @return array Created page names
     */
    public static function create_essential_pages() {
        $created_pages = array();
        
        // Define pages to create
        $pages = array(
            'student-dashboard' => array(
                'title' => '내 강의실',
                'content' => '[lectus_student_dashboard]',
                'template' => ''
            ),
            'courses' => array(
                'title' => '강의 목록',
                'content' => '',
                'template' => ''
            ),
            'instructors' => array(
                'title' => '강사 소개',
                'content' => '',
                'template' => ''
            ),
            'cart' => array(
                'title' => '장바구니',
                'content' => '[woocommerce_cart]',
                'template' => ''
            ),
            'checkout' => array(
                'title' => '결제',
                'content' => '[woocommerce_checkout]',
                'template' => ''
            ),
            'my-account' => array(
                'title' => '내 계정',
                'content' => '[woocommerce_my_account]',
                'template' => ''
            )
        );
        
        foreach ($pages as $slug => $page_data) {
            // Check if page already exists
            $existing_page = get_page_by_path($slug);
            
            if (!$existing_page) {
                $page_id = wp_insert_post(array(
                    'post_title' => $page_data['title'],
                    'post_content' => $page_data['content'],
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_name' => $slug,
                    'page_template' => $page_data['template']
                ));
                
                if (!is_wp_error($page_id)) {
                    $created_pages[] = $page_data['title'];
                }
            }
        }
        
        return $created_pages;
    }
    
    /**
     * Restore test data
     * 
     * @return array Result messages
     */
    public static function restore_test_data() {
        $results = array();
        
        // Check if Lectus plugin is active
        if (!self::is_lectus_plugin_active()) {
            $results[] = array(
                'type' => 'error',
                'message' => 'Lectus Class System 플러그인이 활성화되지 않았습니다.'
            );
            return $results;
        }
        
        // Create sample courses
        $sample_courses = array(
            array(
                'title' => 'WordPress 기초 과정',
                'content' => '워드프레스의 기초를 배우는 과정입니다.',
                'excerpt' => 'WordPress 기초부터 시작하는 입문 과정'
            ),
            array(
                'title' => 'PHP 프로그래밍 마스터',
                'content' => 'PHP 프로그래밍을 마스터하는 과정입니다.',
                'excerpt' => 'PHP 개발자가 되기 위한 완벽한 가이드'
            ),
            array(
                'title' => 'JavaScript 심화 과정',
                'content' => '자바스크립트 고급 기능을 배우는 과정입니다.',
                'excerpt' => 'ES6+와 모던 JavaScript 완벽 정리'
            )
        );
        
        foreach ($sample_courses as $course_data) {
            // Check if course already exists
            $existing = get_page_by_title($course_data['title'], OBJECT, 'coursesingle');
            
            if (!$existing) {
                $course_id = wp_insert_post(array(
                    'post_title' => $course_data['title'],
                    'post_content' => $course_data['content'],
                    'post_excerpt' => $course_data['excerpt'],
                    'post_status' => 'publish',
                    'post_type' => 'coursesingle'
                ));
                
                if (!is_wp_error($course_id)) {
                    $results[] = array(
                        'type' => 'success',
                        'message' => sprintf('강의 "%s" 생성 완료', $course_data['title'])
                    );
                    
                    // Add some sample lessons
                    for ($i = 1; $i <= 3; $i++) {
                        $lesson_id = wp_insert_post(array(
                            'post_title' => sprintf('레슨 %d: %s', $i, $course_data['title']),
                            'post_content' => sprintf('이것은 %s의 %d번째 레슨입니다.', $course_data['title'], $i),
                            'post_status' => 'publish',
                            'post_type' => 'lesson'
                        ));
                        
                        if (!is_wp_error($lesson_id)) {
                            update_post_meta($lesson_id, '_course_id', $course_id);
                        }
                    }
                }
            }
        }
        
        // Create sample users
        $sample_users = array(
            array(
                'user_login' => 'instructor1',
                'user_pass' => 'password123',
                'user_email' => 'instructor1@example.com',
                'display_name' => '김강사',
                'role' => 'lectus_instructor'
            ),
            array(
                'user_login' => 'student1',
                'user_pass' => 'password123',
                'user_email' => 'student1@example.com',
                'display_name' => '이학생',
                'role' => 'lectus_student'
            )
        );
        
        foreach ($sample_users as $user_data) {
            if (!username_exists($user_data['user_login'])) {
                $user_id = wp_create_user(
                    $user_data['user_login'],
                    $user_data['user_pass'],
                    $user_data['user_email']
                );
                
                if (!is_wp_error($user_id)) {
                    wp_update_user(array(
                        'ID' => $user_id,
                        'display_name' => $user_data['display_name']
                    ));
                    
                    $user = new WP_User($user_id);
                    $user->set_role($user_data['role']);
                    
                    $results[] = array(
                        'type' => 'success',
                        'message' => sprintf('사용자 "%s" 생성 완료', $user_data['display_name'])
                    );
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Render admin page content
     */
    public static function admin_page_content() {
        // Handle form submission
        if (isset($_POST['restore_test_data']) && check_admin_referer('lectus_restore_test_data')) {
            $results = self::restore_test_data();
            
            if (!empty($results)) {
                echo '<div class="notice notice-info is-dismissible">';
                foreach ($results as $result) {
                    $class = $result['type'] === 'error' ? 'error' : 'success';
                    echo '<p class="' . esc_attr($class) . '">' . esc_html($result['message']) . '</p>';
                }
                echo '</div>';
            }
        }
        
        if (isset($_POST['create_pages']) && check_admin_referer('lectus_create_pages')) {
            $created_pages = self::create_essential_pages();
            
            if (!empty($created_pages)) {
                echo '<div class="notice notice-success is-dismissible">';
                echo '<p>' . sprintf(__('다음 페이지가 생성되었습니다: %s', 'lectus-academy'), implode(', ', $created_pages)) . '</p>';
                echo '</div>';
            } else {
                echo '<div class="notice notice-info is-dismissible">';
                echo '<p>' . __('모든 필수 페이지가 이미 존재합니다.', 'lectus-academy') . '</p>';
                echo '</div>';
            }
        }
        ?>
        <div class="lectus-test-restoration">
            <h3><?php _e('테스트 환경 복원', 'lectus-academy'); ?></h3>
            <p><?php _e('개발 및 테스트를 위한 샘플 데이터를 생성합니다.', 'lectus-academy'); ?></p>
            
            <div class="card">
                <h4><?php _e('필수 페이지 생성', 'lectus-academy'); ?></h4>
                <p><?php _e('테마 작동에 필요한 기본 페이지들을 생성합니다.', 'lectus-academy'); ?></p>
                <form method="post" action="">
                    <?php wp_nonce_field('lectus_create_pages'); ?>
                    <p>
                        <input type="submit" name="create_pages" class="button button-primary" 
                               value="<?php esc_attr_e('페이지 생성', 'lectus-academy'); ?>" />
                    </p>
                </form>
            </div>
            
            <div class="card" style="margin-top: 20px;">
                <h4><?php _e('샘플 데이터 생성', 'lectus-academy'); ?></h4>
                <p><?php _e('테스트용 강의, 레슨, 사용자를 생성합니다.', 'lectus-academy'); ?></p>
                
                <?php if (!self::is_lectus_plugin_active()): ?>
                    <div class="notice notice-warning inline">
                        <p><?php _e('Lectus Class System 플러그인을 먼저 활성화해주세요.', 'lectus-academy'); ?></p>
                    </div>
                <?php else: ?>
                    <form method="post" action="">
                        <?php wp_nonce_field('lectus_restore_test_data'); ?>
                        <p>
                            <input type="submit" name="restore_test_data" class="button button-primary" 
                                   value="<?php esc_attr_e('샘플 데이터 생성', 'lectus-academy'); ?>" />
                        </p>
                    </form>
                <?php endif; ?>
            </div>
            
            <div class="card" style="margin-top: 20px;">
                <h4><?php _e('현재 상태', 'lectus-academy'); ?></h4>
                <table class="widefat">
                    <tr>
                        <td><?php _e('Lectus Class System', 'lectus-academy'); ?></td>
                        <td>
                            <?php if (self::is_lectus_plugin_active()): ?>
                                <span style="color: green;">✓ <?php _e('활성화됨', 'lectus-academy'); ?></span>
                            <?php else: ?>
                                <span style="color: red;">✗ <?php _e('비활성화됨', 'lectus-academy'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php _e('WooCommerce', 'lectus-academy'); ?></td>
                        <td>
                            <?php if (self::is_woocommerce_active()): ?>
                                <span style="color: green;">✓ <?php _e('활성화됨', 'lectus-academy'); ?></span>
                            <?php else: ?>
                                <span style="color: orange;">⚠ <?php _e('비활성화됨 (선택사항)', 'lectus-academy'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php _e('등록된 강의 수', 'lectus-academy'); ?></td>
                        <td><?php echo wp_count_posts('coursesingle')->publish; ?></td>
                    </tr>
                    <tr>
                        <td><?php _e('등록된 레슨 수', 'lectus-academy'); ?></td>
                        <td><?php echo wp_count_posts('lesson')->publish; ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <style>
        .lectus-test-restoration .card {
            background: #fff;
            border: 1px solid #c3c4c7;
            padding: 20px;
            max-width: 800px;
        }
        .lectus-test-restoration .card h4 {
            margin-top: 0;
        }
        </style>
        <?php
    }
}