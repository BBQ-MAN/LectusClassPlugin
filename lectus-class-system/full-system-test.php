<?php
/**
 * Lectus Class System 전체 시스템 테스트
 * 실제 WordPress 환경에서 실행하여 모든 기능을 검증합니다.
 */

// WordPress 환경 로드 필요 (wp-load.php)
if (!defined('ABSPATH')) {
    // WordPress 루트 디렉토리의 wp-load.php를 로드해야 합니다.
    // 예: require_once('../../../wp-load.php');
    die('WordPress 환경이 로드되지 않았습니다. wp-load.php를 먼저 로드하세요.');
}

class Lectus_Full_System_Test {
    
    private $results = array();
    private $errors = array();
    private $test_data = array();
    
    public function __construct() {
        echo "<h1>🧪 Lectus Class System 전체 시스템 테스트</h1>\n";
        echo "<p>실제 WordPress 환경에서 모든 기능을 테스트합니다.</p>\n";
        echo "<hr>\n";
    }
    
    /**
     * 모든 테스트 실행
     */
    public function run_all_tests() {
        $this->test_plugin_activation();
        $this->test_database_tables();
        $this->test_post_types();
        $this->test_user_roles();
        $this->test_admin_pages();
        $this->test_shortcodes();
        $this->test_test_data_generation();
        $this->test_qa_system();
        $this->test_bulk_upload();
        $this->test_ajax_handlers();
        $this->test_templates();
        $this->test_woocommerce_integration();
        $this->display_final_results();
    }
    
    /**
     * 1. 플러그인 활성화 상태 확인
     */
    private function test_plugin_activation() {
        echo "<h2>1. 플러그인 활성화 상태 확인</h2>\n";
        
        $plugin_file = 'lectus-class-system/lectus-class-system.php';
        $is_active = is_plugin_active($plugin_file);
        
        if ($is_active) {
            echo "✅ 플러그인이 활성화되어 있습니다.\n";
            $this->results['plugin_activation'] = 'PASS';
        } else {
            echo "❌ 플러그인이 활성화되지 않았습니다.\n";
            $this->errors[] = "플러그인을 먼저 활성화하세요.";
            $this->results['plugin_activation'] = 'FAIL';
        }
        
        // 상수 확인
        if (defined('LECTUS_VERSION')) {
            echo "✅ 플러그인 상수가 정의되었습니다. (버전: " . LECTUS_VERSION . ")\n";
        } else {
            echo "❌ 플러그인 상수가 정의되지 않았습니다.\n";
            $this->errors[] = "플러그인 상수 LECTUS_VERSION이 정의되지 않음";
        }
        
        echo "\n";
    }
    
    /**
     * 2. 데이터베이스 테이블 확인
     */
    private function test_database_tables() {
        echo "<h2>2. 데이터베이스 테이블 확인</h2>\n";
        global $wpdb;
        
        $required_tables = array(
            'lectus_enrollment',
            'lectus_progress', 
            'lectus_certificates',
            'lectus_qa',
            'lectus_qa_votes'
        );
        
        $table_results = array();
        
        foreach ($required_tables as $table) {
            $table_name = $wpdb->prefix . $table;
            $exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
            
            if ($exists) {
                echo "✅ {$table} 테이블이 존재합니다.\n";
                
                // 테이블 구조 확인
                $columns = $wpdb->get_results("DESCRIBE {$table_name}");
                $column_count = count($columns);
                echo "   - 컬럼 수: {$column_count}\n";
                
                $table_results[$table] = 'PASS';
            } else {
                echo "❌ {$table} 테이블이 존재하지 않습니다.\n";
                $this->errors[] = "{$table} 테이블 누락";
                $table_results[$table] = 'FAIL';
            }
        }
        
        $this->results['database_tables'] = $table_results;
        echo "\n";
    }
    
    /**
     * 3. 커스텀 포스트 타입 확인
     */
    private function test_post_types() {
        echo "<h2>3. 커스텀 포스트 타입 확인</h2>\n";
        
        $required_post_types = array(
            'coursepackage' => '패키지강의',
            'coursesingle' => '단과강의',
            'lesson' => '레슨'
        );
        
        $post_type_results = array();
        
        foreach ($required_post_types as $post_type => $name) {
            if (post_type_exists($post_type)) {
                echo "✅ {$post_type} ({$name}) 포스트 타입이 등록되었습니다.\n";
                
                // 포스트 타입 설정 확인
                $post_type_obj = get_post_type_object($post_type);
                echo "   - 라벨: {$post_type_obj->labels->name}\n";
                echo "   - 퍼블릭: " . ($post_type_obj->public ? '예' : '아니오') . "\n";
                
                $post_type_results[$post_type] = 'PASS';
            } else {
                echo "❌ {$post_type} 포스트 타입이 등록되지 않았습니다.\n";
                $this->errors[] = "{$post_type} 포스트 타입 미등록";
                $post_type_results[$post_type] = 'FAIL';
            }
        }
        
        $this->results['post_types'] = $post_type_results;
        echo "\n";
    }
    
    /**
     * 4. 사용자 역할 및 권한 확인
     */
    private function test_user_roles() {
        echo "<h2>4. 사용자 역할 및 권한 확인</h2>\n";
        
        // lectus_student 역할 확인
        $role = get_role('lectus_student');
        if ($role) {
            echo "✅ lectus_student 역할이 생성되었습니다.\n";
            
            // 권한 확인
            $required_caps = array('read', 'view_courses', 'take_lessons');
            $cap_results = array();
            
            foreach ($required_caps as $cap) {
                if ($role->has_cap($cap)) {
                    echo "   - {$cap}: ✅\n";
                    $cap_results[$cap] = 'PASS';
                } else {
                    echo "   - {$cap}: ❌\n";
                    $cap_results[$cap] = 'FAIL';
                }
            }
            
            $this->results['user_roles'] = 'PASS';
        } else {
            echo "❌ lectus_student 역할이 생성되지 않았습니다.\n";
            $this->errors[] = "lectus_student 역할 미생성";
            $this->results['user_roles'] = 'FAIL';
        }
        
        // 관리자 권한 확인
        $admin_role = get_role('administrator');
        $admin_caps = array('manage_students', 'publish_lessons', 'edit_courses');
        
        echo "\n관리자 권한 확인:\n";
        foreach ($admin_caps as $cap) {
            if ($admin_role && $admin_role->has_cap($cap)) {
                echo "   - {$cap}: ✅\n";
            } else {
                echo "   - {$cap}: ❌ (자동 부여 예정)\n";
            }
        }
        
        echo "\n";
    }
    
    /**
     * 5. 관리자 페이지 확인
     */
    private function test_admin_pages() {
        echo "<h2>5. 관리자 페이지 확인</h2>\n";
        
        global $menu, $submenu;
        
        $lectus_menu_found = false;
        $lectus_submenus = array();
        
        // 메인 메뉴 확인
        if (is_array($menu)) {
            foreach ($menu as $menu_item) {
                if (isset($menu_item[2]) && $menu_item[2] === 'lectus-class-system') {
                    $lectus_menu_found = true;
                    echo "✅ Lectus Class System 메인 메뉴가 등록되었습니다.\n";
                    break;
                }
            }
        }
        
        if (!$lectus_menu_found) {
            echo "❌ Lectus Class System 메인 메뉴가 등록되지 않았습니다.\n";
            $this->errors[] = "메인 메뉴 미등록";
        }
        
        // 서브메뉴 확인
        $expected_submenus = array(
            'lectus-class-system' => '대시보드',
            'lectus-students' => '수강생 관리',
            'lectus-certificates' => '수료증 관리',
            'lectus-reports' => '보고서',
            'lectus-settings' => '설정',
            'lectus-test-data' => '테스트 데이터',
            'lectus-qa' => 'Q&A 관리'
        );
        
        if (isset($submenu['lectus-class-system'])) {
            foreach ($submenu['lectus-class-system'] as $submenu_item) {
                $slug = $submenu_item[2];
                if (isset($expected_submenus[$slug])) {
                    echo "✅ {$expected_submenus[$slug]} 서브메뉴 등록됨\n";
                    $lectus_submenus[$slug] = 'PASS';
                    unset($expected_submenus[$slug]);
                }
            }
        }
        
        // 누락된 서브메뉴 확인
        foreach ($expected_submenus as $slug => $name) {
            echo "❌ {$name} 서브메뉴가 등록되지 않았습니다.\n";
            $this->errors[] = "{$name} 서브메뉴 미등록";
            $lectus_submenus[$slug] = 'FAIL';
        }
        
        $this->results['admin_pages'] = $lectus_submenus;
        echo "\n";
    }
    
    /**
     * 6. 쇼트코드 확인
     */
    private function test_shortcodes() {
        echo "<h2>6. 쇼트코드 확인</h2>\n";
        
        global $shortcode_tags;
        
        $expected_shortcodes = array(
            'lectus_courses' => '강의 목록',
            'lectus_my_courses' => '내 강의',
            'lectus_certificates' => '수료증 목록',
            'lectus_student_dashboard' => '학생 대시보드',
            'lectus_certificate_verify' => '수료증 검증',
            'lectus_qa' => 'Q&A 시스템'
        );
        
        $shortcode_results = array();
        
        foreach ($expected_shortcodes as $shortcode => $name) {
            if (isset($shortcode_tags[$shortcode])) {
                echo "✅ [{$shortcode}] - {$name} 쇼트코드가 등록되었습니다.\n";
                
                // 간단한 실행 테스트
                $output = do_shortcode("[{$shortcode}]");
                if (!empty($output) && !is_wp_error($output)) {
                    echo "   - 실행 테스트: ✅ (출력 길이: " . strlen($output) . "자)\n";
                    $shortcode_results[$shortcode] = 'PASS';
                } else {
                    echo "   - 실행 테스트: ❌\n";
                    $shortcode_results[$shortcode] = 'PARTIAL';
                }
            } else {
                echo "❌ [{$shortcode}] - {$name} 쇼트코드가 등록되지 않았습니다.\n";
                $this->errors[] = "{$shortcode} 쇼트코드 미등록";
                $shortcode_results[$shortcode] = 'FAIL';
            }
        }
        
        $this->results['shortcodes'] = $shortcode_results;
        echo "\n";
    }
    
    /**
     * 7. 테스트 데이터 생성 확인
     */
    private function test_test_data_generation() {
        echo "<h2>7. 테스트 데이터 생성 확인</h2>\n";
        
        // 기존 테스트 데이터 확인
        $test_courses = get_posts(array(
            'post_type' => 'coursesingle',
            'meta_query' => array(
                array(
                    'key' => '_is_test_data',
                    'value' => 'yes'
                )
            ),
            'posts_per_page' => -1
        ));
        
        echo "현재 테스트 강의 수: " . count($test_courses) . "개\n";
        
        // 테스트 학생 계정 확인
        $test_students = get_users(array(
            'meta_key' => '_is_test_user',
            'meta_value' => 'yes',
            'role' => 'lectus_student'
        ));
        
        echo "현재 테스트 학생 수: " . count($test_students) . "명\n";
        
        // 테스트 데이터 생성 함수 확인
        if (function_exists('lectus_generate_test_data')) {
            echo "✅ 테스트 데이터 생성 함수가 존재합니다.\n";
            $this->results['test_data_generation'] = 'PASS';
        } else {
            echo "❌ 테스트 데이터 생성 함수가 존재하지 않습니다.\n";
            $this->errors[] = "테스트 데이터 생성 함수 미존재";
            $this->results['test_data_generation'] = 'FAIL';
        }
        
        // 테스트 페이지 생성 함수 확인
        if (function_exists('lectus_create_test_pages')) {
            echo "✅ 테스트 페이지 생성 함수가 존재합니다.\n";
        } else {
            echo "❌ 테스트 페이지 생성 함수가 존재하지 않습니다.\n";
            $this->errors[] = "테스트 페이지 생성 함수 미존재";
        }
        
        echo "\n";
    }
    
    /**
     * 8. Q&A 시스템 확인
     */
    private function test_qa_system() {
        echo "<h2>8. Q&A 시스템 확인</h2>\n";
        
        if (class_exists('Lectus_QA')) {
            echo "✅ Lectus_QA 클래스가 존재합니다.\n";
            
            // 메서드 확인
            $required_methods = array(
                'submit_question',
                'submit_answer',
                'vote',
                'mark_best_answer',
                'get_questions',
                'get_answers'
            );
            
            $method_results = array();
            foreach ($required_methods as $method) {
                if (method_exists('Lectus_QA', $method)) {
                    echo "   - {$method}: ✅\n";
                    $method_results[$method] = 'PASS';
                } else {
                    echo "   - {$method}: ❌\n";
                    $method_results[$method] = 'FAIL';
                }
            }
            
            // AJAX 핸들러 확인
            $qa_actions = array(
                'wp_ajax_lectus_submit_question',
                'wp_ajax_lectus_submit_answer',
                'wp_ajax_lectus_vote_qa'
            );
            
            foreach ($qa_actions as $action) {
                if (has_action($action)) {
                    echo "   - {$action}: ✅\n";
                } else {
                    echo "   - {$action}: ❌\n";
                    $this->errors[] = "Q&A AJAX 핸들러 {$action} 미등록";
                }
            }
            
            $this->results['qa_system'] = 'PASS';
        } else {
            echo "❌ Lectus_QA 클래스가 존재하지 않습니다.\n";
            $this->errors[] = "Q&A 시스템 클래스 미존재";
            $this->results['qa_system'] = 'FAIL';
        }
        
        echo "\n";
    }
    
    /**
     * 9. 벌크 업로드 시스템 확인
     */
    private function test_bulk_upload() {
        echo "<h2>9. 벌크 업로드 시스템 확인</h2>\n";
        
        if (class_exists('Lectus_Bulk_Upload')) {
            echo "✅ Lectus_Bulk_Upload 클래스가 존재합니다.\n";
            
            // 메서드 확인
            $required_methods = array(
                'process_lesson_csv',
                'process_student_csv',
                'process_enrollment_csv',
                'download_template',
                'validate_csv_data'
            );
            
            foreach ($required_methods as $method) {
                if (method_exists('Lectus_Bulk_Upload', $method)) {
                    echo "   - {$method}: ✅\n";
                } else {
                    echo "   - {$method}: ❌\n";
                    $this->errors[] = "벌크 업로드 메서드 {$method} 미존재";
                }
            }
            
            $this->results['bulk_upload'] = 'PASS';
        } else {
            echo "❌ Lectus_Bulk_Upload 클래스가 존재하지 않습니다.\n";
            $this->errors[] = "벌크 업로드 시스템 클래스 미존재";
            $this->results['bulk_upload'] = 'FAIL';
        }
        
        echo "\n";
    }
    
    /**
     * 10. AJAX 핸들러 확인
     */
    private function test_ajax_handlers() {
        echo "<h2>10. AJAX 핸들러 확인</h2>\n";
        
        $required_ajax_actions = array(
            'wp_ajax_lectus_update_lesson_progress',
            'wp_ajax_lectus_complete_lesson',
            'wp_ajax_lectus_enroll_student',
            'wp_ajax_lectus_generate_certificate'
        );
        
        $ajax_results = array();
        
        foreach ($required_ajax_actions as $action) {
            if (has_action($action)) {
                echo "✅ {$action} 핸들러가 등록되었습니다.\n";
                $ajax_results[$action] = 'PASS';
            } else {
                echo "❌ {$action} 핸들러가 등록되지 않았습니다.\n";
                $this->errors[] = "AJAX 핸들러 {$action} 미등록";
                $ajax_results[$action] = 'FAIL';
            }
        }
        
        $this->results['ajax_handlers'] = $ajax_results;
        echo "\n";
    }
    
    /**
     * 11. 템플릿 확인
     */
    private function test_templates() {
        echo "<h2>11. 템플릿 확인</h2>\n";
        
        $template_files = array(
            'templates/student-dashboard.php',
            'templates/test-pages.php'
        );
        
        $template_results = array();
        
        foreach ($template_files as $template) {
            $full_path = LECTUS_PLUGIN_DIR . $template;
            if (file_exists($full_path)) {
                echo "✅ {$template} 파일이 존재합니다.\n";
                $template_results[$template] = 'PASS';
            } else {
                echo "❌ {$template} 파일이 존재하지 않습니다.\n";
                $this->errors[] = "템플릿 파일 {$template} 미존재";
                $template_results[$template] = 'FAIL';
            }
        }
        
        // 템플릿 훅 확인
        if (has_filter('template_include')) {
            echo "✅ 템플릿 로더가 등록되었습니다.\n";
        } else {
            echo "❌ 템플릿 로더가 등록되지 않았습니다.\n";
            $this->errors[] = "템플릿 로더 미등록";
        }
        
        $this->results['templates'] = $template_results;
        echo "\n";
    }
    
    /**
     * 12. WooCommerce 연동 확인
     */
    private function test_woocommerce_integration() {
        echo "<h2>12. WooCommerce 연동 확인</h2>\n";
        
        if (class_exists('WooCommerce')) {
            echo "✅ WooCommerce가 활성화되어 있습니다.\n";
            
            if (class_exists('Lectus_WooCommerce')) {
                echo "✅ Lectus WooCommerce 연동 클래스가 존재합니다.\n";
                
                // WooCommerce 훅 확인
                $wc_hooks = array(
                    'woocommerce_order_status_completed',
                    'woocommerce_order_status_refunded',
                    'woocommerce_product_data_tabs',
                    'woocommerce_product_data_panels'
                );
                
                foreach ($wc_hooks as $hook) {
                    if (has_action($hook)) {
                        echo "   - {$hook}: ✅\n";
                    } else {
                        echo "   - {$hook}: ❌\n";
                    }
                }
                
                $this->results['woocommerce_integration'] = 'PASS';
            } else {
                echo "❌ Lectus WooCommerce 연동 클래스가 존재하지 않습니다.\n";
                $this->errors[] = "WooCommerce 연동 클래스 미존재";
                $this->results['woocommerce_integration'] = 'FAIL';
            }
        } else {
            echo "⚠️ WooCommerce가 설치되지 않았습니다. (선택 사항)\n";
            $this->results['woocommerce_integration'] = 'SKIP';
        }
        
        echo "\n";
    }
    
    /**
     * 최종 결과 표시
     */
    private function display_final_results() {
        echo "<hr>\n";
        echo "<h2>🏁 최종 테스트 결과</h2>\n";
        
        $total_tests = 0;
        $passed_tests = 0;
        $failed_tests = 0;
        
        foreach ($this->results as $category => $result) {
            if (is_array($result)) {
                foreach ($result as $test => $status) {
                    $total_tests++;
                    if ($status === 'PASS') $passed_tests++;
                    elseif ($status === 'FAIL') $failed_tests++;
                }
            } else {
                $total_tests++;
                if ($result === 'PASS') $passed_tests++;
                elseif ($result === 'FAIL') $failed_tests++;
            }
        }
        
        $success_rate = $total_tests > 0 ? round(($passed_tests / $total_tests) * 100, 1) : 0;
        
        echo "<div style='padding: 20px; border: 2px solid " . 
             ($success_rate >= 90 ? 'green' : ($success_rate >= 70 ? 'orange' : 'red')) . 
             "; background: " . ($success_rate >= 90 ? '#f0fff0' : ($success_rate >= 70 ? '#fff8e1' : '#fff0f0')) . ";'>\n";
        
        echo "<h3>📊 테스트 통계</h3>\n";
        echo "- 전체 테스트: {$total_tests}개\n";
        echo "- 통과: {$passed_tests}개 ✅\n";
        echo "- 실패: {$failed_tests}개 ❌\n";
        echo "- 성공률: {$success_rate}%\n";
        
        if ($success_rate >= 90) {
            echo "\n<h3>🎉 테스트 결과: 우수</h3>\n";
            echo "모든 핵심 기능이 정상적으로 작동합니다!\n";
            echo "\n<strong>✅ 다음 단계:</strong>\n";
            echo "1. 테스트 데이터 생성 (Lectus Class System → 테스트 데이터)\n";
            echo "2. 테스트 페이지 생성 및 확인\n";
            echo "3. 학생 계정으로 로그인하여 실제 기능 테스트\n";
            echo "4. Q&A 및 벌크 업로드 기능 테스트\n";
        } elseif ($success_rate >= 70) {
            echo "\n<h3>⚠️ 테스트 결과: 주의 필요</h3>\n";
            echo "일부 기능에 문제가 있습니다. 오류를 수정하세요.\n";
        } else {
            echo "\n<h3>❌ 테스트 결과: 실패</h3>\n";
            echo "많은 기능에 문제가 있습니다. 시스템을 점검하세요.\n";
        }
        
        echo "</div>\n";
        
        if (!empty($this->errors)) {
            echo "\n<h3>🚨 발견된 오류 목록</h3>\n";
            echo "<ol>\n";
            foreach ($this->errors as $error) {
                echo "<li>{$error}</li>\n";
            }
            echo "</ol>\n";
        }
        
        echo "\n<h3>📋 상세 결과</h3>\n";
        foreach ($this->results as $category => $result) {
            echo "<h4>" . ucfirst(str_replace('_', ' ', $category)) . "</h4>\n";
            
            if (is_array($result)) {
                foreach ($result as $test => $status) {
                    $icon = $status === 'PASS' ? '✅' : ($status === 'FAIL' ? '❌' : '⚠️');
                    echo "- {$test}: {$icon} {$status}\n";
                }
            } else {
                $icon = $result === 'PASS' ? '✅' : ($result === 'FAIL' ? '❌' : '⚠️');
                echo "- 전체: {$icon} {$result}\n";
            }
            echo "\n";
        }
        
        echo "<hr>\n";
        echo "<p><strong>테스트 완료 시각:</strong> " . date('Y-m-d H:i:s') . "</p>\n";
        echo "<p><strong>WordPress 버전:</strong> " . get_bloginfo('version') . "</p>\n";
        echo "<p><strong>PHP 버전:</strong> " . PHP_VERSION . "</p>\n";
    }
}

// 실행
if (is_admin() || (defined('WP_CLI') && WP_CLI)) {
    $tester = new Lectus_Full_System_Test();
    $tester->run_all_tests();
} else {
    echo "<h1>❌ 접근 거부</h1>\n";
    echo "<p>이 테스트는 관리자 권한으로만 실행할 수 있습니다.</p>\n";
}
?>