<?php
/**
 * Lectus Class System Plugin Activation Test
 * 플러그인 활성화 전 완전성 검증 스크립트
 */

// 직접 접근 방지
if (!defined('ABSPATH')) {
    // WordPress 환경 시뮬레이션 (실제 환경에서는 불필요)
    define('ABSPATH', dirname(__FILE__) . '/');
}

class Lectus_Plugin_Test {
    
    private $test_results = array();
    private $errors = array();
    
    public function __construct() {
        echo "<h1>Lectus Class System 플러그인 활성화 테스트</h1>\n";
        echo "<p>플러그인 구조와 파일의 완전성을 검증합니다.</p>\n";
    }
    
    /**
     * 전체 테스트 실행
     */
    public function run_all_tests() {
        $this->test_file_structure();
        $this->test_class_definitions();
        $this->test_database_setup();
        $this->test_hooks_and_actions();
        $this->test_admin_pages();
        $this->test_shortcodes();
        $this->test_templates();
        $this->test_qa_system();
        $this->test_bulk_upload();
        $this->display_results();
    }
    
    /**
     * 파일 구조 테스트
     */
    private function test_file_structure() {
        echo "<h2>1. 파일 구조 검증</h2>\n";
        
        $required_files = array(
            'lectus-class-system.php' => '메인 플러그인 파일',
            'includes/class-lectus-autoloader.php' => '오토로더',
            'includes/class-lectus-post-types.php' => '포스트 타입',
            'includes/class-lectus-taxonomies.php' => '택소노미',
            'includes/class-lectus-capabilities.php' => '권한 관리',
            'includes/class-lectus-ajax.php' => 'AJAX 핸들러',
            'includes/class-lectus-shortcodes.php' => '쇼트코드',
            'includes/class-lectus-templates.php' => '템플릿 핸들러',
            'includes/class-lectus-woocommerce.php' => 'WooCommerce 연동',
            'includes/class-lectus-student.php' => '학생 관리',
            'includes/class-lectus-progress.php' => '진도 관리',
            'includes/class-lectus-enrollment.php' => '등록 관리',
            'includes/class-lectus-certificate.php' => '수료증 시스템',
            'includes/class-lectus-qa.php' => 'Q&A 시스템',
            'includes/class-lectus-bulk-upload.php' => '벌크 업로드',
            'admin/class-lectus-admin.php' => '관리자 메인',
            'admin/class-lectus-admin-dashboard.php' => '관리자 대시보드',
            'admin/class-lectus-admin-settings.php' => '설정 페이지',
            'admin/class-lectus-admin-reports.php' => '보고서 페이지',
            'admin/lectus-test-data.php' => '테스트 데이터 생성기',
            'templates/student-dashboard.php' => '학생 대시보드 템플릿',
            'templates/test-pages.php' => '테스트 페이지 생성기'
        );
        
        $base_path = dirname(__FILE__) . '/';
        
        foreach ($required_files as $file => $description) {
            $file_path = $base_path . $file;
            if (file_exists($file_path)) {
                echo "✓ {$file} - {$description}\n";
                $this->test_results['files'][] = array('file' => $file, 'status' => 'OK');
            } else {
                echo "✗ {$file} - {$description} (누락됨)\n";
                $this->errors[] = "필수 파일 누락: {$file}";
                $this->test_results['files'][] = array('file' => $file, 'status' => 'MISSING');
            }
        }
        echo "\n";
    }
    
    /**
     * 클래스 정의 테스트
     */
    private function test_class_definitions() {
        echo "<h2>2. 클래스 정의 검증</h2>\n";
        
        $required_classes = array(
            'Lectus_Class_System' => '메인 플러그인 클래스',
            'Lectus_Autoloader' => '오토로더',
            'Lectus_Post_Types' => '포스트 타입 관리',
            'Lectus_Taxonomies' => '택소노미 관리',
            'Lectus_Capabilities' => '권한 관리',
            'Lectus_Ajax' => 'AJAX 핸들러',
            'Lectus_Shortcodes' => '쇼트코드 관리',
            'Lectus_Templates' => '템플릿 관리',
            'Lectus_WooCommerce' => 'WooCommerce 연동',
            'Lectus_Student' => '학생 관리',
            'Lectus_Progress' => '진도 관리',
            'Lectus_Enrollment' => '등록 관리',
            'Lectus_Certificate' => '수료증 관리',
            'Lectus_QA' => 'Q&A 시스템',
            'Lectus_Bulk_Upload' => '벌크 업로드 시스템',
            'Lectus_Admin' => '관리자 페이지',
            'Lectus_Admin_Dashboard' => '관리자 대시보드',
            'Lectus_Admin_Settings' => '설정 페이지',
            'Lectus_Admin_Reports' => '보고서 페이지'
        );
        
        foreach ($required_classes as $class_name => $description) {
            // 파일에서 클래스 정의 확인 (실제 환경에서는 class_exists 사용)
            $class_found = $this->check_class_in_files($class_name);
            
            if ($class_found) {
                echo "✓ {$class_name} - {$description}\n";
                $this->test_results['classes'][] = array('class' => $class_name, 'status' => 'OK');
            } else {
                echo "✗ {$class_name} - {$description} (정의되지 않음)\n";
                $this->errors[] = "클래스 정의 누락: {$class_name}";
                $this->test_results['classes'][] = array('class' => $class_name, 'status' => 'MISSING');
            }
        }
        echo "\n";
    }
    
    /**
     * 데이터베이스 설정 테스트
     */
    private function test_database_setup() {
        echo "<h2>3. 데이터베이스 설정 검증</h2>\n";
        
        $required_tables = array(
            'lectus_enrollment' => '수강 등록',
            'lectus_progress' => '학습 진도',
            'lectus_certificates' => '수료증',
            'lectus_qa' => 'Q&A',
            'lectus_qa_votes' => 'Q&A 투표'
        );
        
        // 테이블 생성 쿼리 확인
        $main_file = file_get_contents(dirname(__FILE__) . '/lectus-class-system.php');
        
        foreach ($required_tables as $table => $description) {
            if (strpos($main_file, $table) !== false) {
                echo "✓ {$table} - {$description} 테이블 설정 확인됨\n";
                $this->test_results['tables'][] = array('table' => $table, 'status' => 'OK');
            } else {
                echo "✗ {$table} - {$description} 테이블 설정 누락\n";
                $this->errors[] = "테이블 설정 누락: {$table}";
                $this->test_results['tables'][] = array('table' => $table, 'status' => 'MISSING');
            }
        }
        echo "\n";
    }
    
    /**
     * 훅과 액션 테스트
     */
    private function test_hooks_and_actions() {
        echo "<h2>4. 훅과 액션 검증</h2>\n";
        
        $required_hooks = array(
            'register_activation_hook' => '플러그인 활성화 훅',
            'register_deactivation_hook' => '플러그인 비활성화 훅',
            'init' => '초기화 액션',
            'plugins_loaded' => '플러그인 로드 액션',
            'admin_menu' => '관리자 메뉴 액션',
            'wp_ajax_lectus_submit_question' => 'Q&A 질문 AJAX',
            'wp_ajax_lectus_submit_answer' => 'Q&A 답변 AJAX',
            'wp_ajax_lectus_vote_qa' => 'Q&A 투표 AJAX'
        );
        
        $main_file = file_get_contents(dirname(__FILE__) . '/lectus-class-system.php');
        $qa_file = file_get_contents(dirname(__FILE__) . '/includes/class-lectus-qa.php');
        
        foreach ($required_hooks as $hook => $description) {
            $found_in_main = strpos($main_file, $hook) !== false;
            $found_in_qa = strpos($qa_file, $hook) !== false;
            
            if ($found_in_main || $found_in_qa) {
                echo "✓ {$hook} - {$description}\n";
                $this->test_results['hooks'][] = array('hook' => $hook, 'status' => 'OK');
            } else {
                echo "✗ {$hook} - {$description} (등록되지 않음)\n";
                $this->errors[] = "훅 등록 누락: {$hook}";
                $this->test_results['hooks'][] = array('hook' => $hook, 'status' => 'MISSING');
            }
        }
        echo "\n";
    }
    
    /**
     * 관리자 페이지 테스트
     */
    private function test_admin_pages() {
        echo "<h2>5. 관리자 페이지 검증</h2>\n";
        
        $required_pages = array(
            'lectus-class-system' => '메인 대시보드',
            'lectus-students' => '수강생 관리',
            'lectus-certificates' => '수료증 관리',
            'lectus-reports' => '보고서',
            'lectus-settings' => '설정',
            'lectus-test-data' => '테스트 데이터',
            'lectus-qa' => 'Q&A 관리'
        );
        
        $main_file = file_get_contents(dirname(__FILE__) . '/lectus-class-system.php');
        $qa_file = file_get_contents(dirname(__FILE__) . '/includes/class-lectus-qa.php');
        
        foreach ($required_pages as $page => $description) {
            $found_in_main = strpos($main_file, $page) !== false;
            $found_in_qa = strpos($qa_file, $page) !== false;
            
            if ($found_in_main || $found_in_qa) {
                echo "✓ {$page} - {$description}\n";
                $this->test_results['admin_pages'][] = array('page' => $page, 'status' => 'OK');
            } else {
                echo "✗ {$page} - {$description} (등록되지 않음)\n";
                $this->errors[] = "관리자 페이지 누락: {$page}";
                $this->test_results['admin_pages'][] = array('page' => $page, 'status' => 'MISSING');
            }
        }
        echo "\n";
    }
    
    /**
     * 쇼트코드 테스트
     */
    private function test_shortcodes() {
        echo "<h2>6. 쇼트코드 검증</h2>\n";
        
        $required_shortcodes = array(
            'lectus_courses' => '강의 목록',
            'lectus_my_courses' => '내 강의',
            'lectus_certificates' => '수료증 목록',
            'lectus_student_dashboard' => '학생 대시보드',
            'lectus_certificate_verify' => '수료증 검증',
            'lectus_qa' => 'Q&A 시스템'
        );
        
        // 쇼트코드 파일들 확인
        $shortcodes_file = dirname(__FILE__) . '/includes/class-lectus-shortcodes.php';
        $qa_file = dirname(__FILE__) . '/includes/class-lectus-qa.php';
        
        if (file_exists($shortcodes_file)) {
            $shortcodes_content = file_get_contents($shortcodes_file);
        } else {
            $shortcodes_content = '';
        }
        
        if (file_exists($qa_file)) {
            $qa_content = file_get_contents($qa_file);
        } else {
            $qa_content = '';
        }
        
        foreach ($required_shortcodes as $shortcode => $description) {
            $found_in_shortcodes = strpos($shortcodes_content, $shortcode) !== false;
            $found_in_qa = strpos($qa_content, $shortcode) !== false;
            
            if ($found_in_shortcodes || $found_in_qa) {
                echo "✓ [{$shortcode}] - {$description}\n";
                $this->test_results['shortcodes'][] = array('shortcode' => $shortcode, 'status' => 'OK');
            } else {
                echo "✗ [{$shortcode}] - {$description} (등록되지 않음)\n";
                $this->errors[] = "쇼트코드 누락: {$shortcode}";
                $this->test_results['shortcodes'][] = array('shortcode' => $shortcode, 'status' => 'MISSING');
            }
        }
        echo "\n";
    }
    
    /**
     * 템플릿 테스트
     */
    private function test_templates() {
        echo "<h2>7. 템플릿 파일 검증</h2>\n";
        
        $template_files = array(
            'templates/student-dashboard.php' => '학생 대시보드 템플릿',
            'templates/test-pages.php' => '테스트 페이지 생성기'
        );
        
        foreach ($template_files as $file => $description) {
            $file_path = dirname(__FILE__) . '/' . $file;
            if (file_exists($file_path)) {
                // 템플릿 내용 간단 검증
                $content = file_get_contents($file_path);
                if (strpos($content, '<?php') !== false && strlen($content) > 100) {
                    echo "✓ {$file} - {$description}\n";
                    $this->test_results['templates'][] = array('template' => $file, 'status' => 'OK');
                } else {
                    echo "⚠ {$file} - {$description} (내용 부족)\n";
                    $this->test_results['templates'][] = array('template' => $file, 'status' => 'WARNING');
                }
            } else {
                echo "✗ {$file} - {$description} (파일 없음)\n";
                $this->errors[] = "템플릿 파일 누락: {$file}";
                $this->test_results['templates'][] = array('template' => $file, 'status' => 'MISSING');
            }
        }
        echo "\n";
    }
    
    /**
     * Q&A 시스템 테스트
     */
    private function test_qa_system() {
        echo "<h2>8. Q&A 시스템 검증</h2>\n";
        
        $qa_file = dirname(__FILE__) . '/includes/class-lectus-qa.php';
        if (file_exists($qa_file)) {
            $qa_content = file_get_contents($qa_file);
            
            $qa_features = array(
                'submit_question' => '질문 등록 기능',
                'submit_answer' => '답변 등록 기능',
                'vote' => '투표 기능',
                'mark_best_answer' => '채택 기능',
                'create_table' => '테이블 생성',
                'create_votes_table' => '투표 테이블 생성',
                'qa_shortcode' => '쇼트코드 구현',
                'admin_page' => '관리자 페이지'
            );
            
            foreach ($qa_features as $feature => $description) {
                if (strpos($qa_content, $feature) !== false) {
                    echo "✓ {$feature} - {$description}\n";
                    $this->test_results['qa_features'][] = array('feature' => $feature, 'status' => 'OK');
                } else {
                    echo "✗ {$feature} - {$description} (구현되지 않음)\n";
                    $this->errors[] = "Q&A 기능 누락: {$feature}";
                    $this->test_results['qa_features'][] = array('feature' => $feature, 'status' => 'MISSING');
                }
            }
        } else {
            echo "✗ Q&A 시스템 파일이 존재하지 않습니다.\n";
            $this->errors[] = "Q&A 시스템 파일 누락";
        }
        echo "\n";
    }
    
    /**
     * 벌크 업로드 시스템 테스트
     */
    private function test_bulk_upload() {
        echo "<h2>9. 벌크 업로드 시스템 검증</h2>\n";
        
        $bulk_file = dirname(__FILE__) . '/includes/class-lectus-bulk-upload.php';
        if (file_exists($bulk_file)) {
            $bulk_content = file_get_contents($bulk_file);
            
            $bulk_features = array(
                'process_lesson_csv' => '레슨 CSV 처리',
                'process_student_csv' => '학생 CSV 처리', 
                'process_enrollment_csv' => '등록 CSV 처리',
                'download_template' => '템플릿 다운로드',
                'admin_page' => '관리자 페이지',
                'validate_csv_data' => '데이터 검증'
            );
            
            foreach ($bulk_features as $feature => $description) {
                if (strpos($bulk_content, $feature) !== false) {
                    echo "✓ {$feature} - {$description}\n";
                    $this->test_results['bulk_features'][] = array('feature' => $feature, 'status' => 'OK');
                } else {
                    echo "✗ {$feature} - {$description} (구현되지 않음)\n";
                    $this->errors[] = "벌크 업로드 기능 누락: {$feature}";
                    $this->test_results['bulk_features'][] = array('feature' => $feature, 'status' => 'MISSING');
                }
            }
        } else {
            echo "✗ 벌크 업로드 시스템 파일이 존재하지 않습니다.\n";
            $this->errors[] = "벌크 업로드 시스템 파일 누락";
        }
        echo "\n";
    }
    
    /**
     * 결과 표시
     */
    private function display_results() {
        echo "<h2>📊 테스트 결과 요약</h2>\n";
        
        $total_errors = count($this->errors);
        
        if ($total_errors == 0) {
            echo "<div style='color: green; font-weight: bold; padding: 10px; border: 2px solid green; background: #f0fff0;'>\n";
            echo "🎉 모든 테스트 통과! 플러그인 활성화 준비 완료\n";
            echo "</div>\n\n";
            
            echo "<h3>✅ 다음 단계</h3>\n";
            echo "1. WordPress 관리자 → 플러그인 → Lectus Class System 활성화\n";
            echo "2. Lectus Class System → 테스트 데이터 → 테스트 데이터 생성\n";
            echo "3. Lectus Class System → 테스트 페이지 생성\n";
            echo "4. 생성된 페이지들에서 쇼트코드 작동 확인\n";
            echo "5. 학생 계정으로 로그인하여 대시보드 테스트\n";
            echo "6. Q&A 시스템 작동 테스트\n";
            echo "7. 벌크 업로드 기능 테스트\n\n";
            
        } else {
            echo "<div style='color: red; font-weight: bold; padding: 10px; border: 2px solid red; background: #fff0f0;'>\n";
            echo "❌ {$total_errors}개의 오류 발견\n";
            echo "</div>\n\n";
            
            echo "<h3>🚨 발견된 오류들</h3>\n";
            foreach ($this->errors as $error) {
                echo "• {$error}\n";
            }
            echo "\n";
            
            echo "<h3>⚠️ 해결 방법</h3>\n";
            echo "1. 누락된 파일들을 생성하거나 복사하세요\n";
            echo "2. 클래스 정의가 누락된 파일들을 확인하세요\n";
            echo "3. 데이터베이스 테이블 생성 코드를 확인하세요\n";
            echo "4. 모든 오류를 해결한 후 다시 테스트하세요\n\n";
        }
        
        // 상세 테스트 결과
        echo "<h3>📋 상세 테스트 결과</h3>\n";
        foreach ($this->test_results as $category => $results) {
            echo "<h4>" . ucfirst($category) . "</h4>\n";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
            foreach ($results as $result) {
                $key = array_keys($result)[0];
                $status_color = $result['status'] == 'OK' ? 'green' : ($result['status'] == 'WARNING' ? 'orange' : 'red');
                echo "<tr><td>{$result[$key]}</td><td style='color: {$status_color};'>{$result['status']}</td></tr>\n";
            }
            echo "</table>\n\n";
        }
    }
    
    /**
     * 파일에서 클래스 정의 확인
     */
    private function check_class_in_files($class_name) {
        $search_pattern = "class {$class_name}";
        $directories = array('includes/', 'admin/', './');
        
        foreach ($directories as $dir) {
            $dir_path = dirname(__FILE__) . '/' . $dir;
            if (is_dir($dir_path)) {
                $files = glob($dir_path . '*.php');
                foreach ($files as $file) {
                    $content = file_get_contents($file);
                    if (strpos($content, $search_pattern) !== false) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
}

// 테스트 실행
$tester = new Lectus_Plugin_Test();
$tester->run_all_tests();

echo "\n=== 테스트 완료 ===\n";
echo "일시: " . date('Y-m-d H:i:s') . "\n";
echo "파일: " . __FILE__ . "\n";
?>