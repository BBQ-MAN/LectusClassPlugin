<?php
/**
 * Settings Page for Lectus Class System
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Admin_Settings {
    
    public static function render_settings_page() {
        // Save settings
        if (isset($_POST['lectus_save_settings']) && wp_verify_nonce($_POST['lectus_settings_nonce'], 'lectus_save_settings')) {
            self::save_settings();
            echo '<div class="notice notice-success"><p>' . __('설정이 저장되었습니다.', 'lectus-class-system') . '</p></div>';
        }
        
        ?>
        <div class="wrap">
            <h1><?php _e('Lectus Class System 설정', 'lectus-class-system'); ?></h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('lectus_save_settings', 'lectus_settings_nonce'); ?>
                
                <h2 class="nav-tab-wrapper">
                    <a href="#general" class="nav-tab nav-tab-active"><?php _e('일반', 'lectus-class-system'); ?></a>
                    <a href="#enrollment" class="nav-tab"><?php _e('수강 등록', 'lectus-class-system'); ?></a>
                    <a href="#certificates" class="nav-tab"><?php _e('수료증', 'lectus-class-system'); ?></a>
                    <a href="#emails" class="nav-tab"><?php _e('이메일', 'lectus-class-system'); ?></a>
                    <a href="#advanced" class="nav-tab"><?php _e('고급 설정', 'lectus-class-system'); ?></a>
                    <a href="#development" class="nav-tab"><?php _e('개발 도구', 'lectus-class-system'); ?></a>
                    <a href="#system" class="nav-tab"><?php _e('시스템', 'lectus-class-system'); ?></a>
                </h2>
                
                <div id="general" class="tab-content">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="lectus_default_access_duration">
                                    <?php _e('기본 수강 기간', 'lectus-class-system'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="number" id="lectus_default_access_duration" 
                                       name="lectus_default_access_duration" 
                                       value="<?php echo get_option('lectus_default_access_duration', '365'); ?>" />
                                <span><?php _e('일 (0 = 무제한)', 'lectus-class-system'); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="lectus_completion_threshold">
                                    <?php _e('기본 수료 기준', 'lectus-class-system'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="number" id="lectus_completion_threshold" 
                                       name="lectus_completion_threshold" 
                                       value="<?php echo get_option('lectus_completion_threshold', '80'); ?>" 
                                       min="0" max="100" />
                                <span>%</span>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div id="enrollment" class="tab-content" style="display:none;">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <?php _e('자동 학생 역할 할당', 'lectus-class-system'); ?>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="lectus_auto_assign_student_role" 
                                           value="yes" <?php checked(get_option('lectus_auto_assign_student_role'), 'yes'); ?> />
                                    <?php _e('신규 사용자에게 자동으로 학생 역할 할당', 'lectus-class-system'); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div id="certificates" class="tab-content" style="display:none;">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <?php _e('수료증 자동 발급', 'lectus-class-system'); ?>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="lectus_auto_generate_certificates" 
                                           value="yes" <?php checked(get_option('lectus_auto_generate_certificates', 'yes'), 'yes'); ?> />
                                    <?php _e('강의 완료 시 자동으로 수료증 발급', 'lectus-class-system'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="lectus_certificate_template">
                                    <?php _e('수료증 템플릿', 'lectus-class-system'); ?>
                                </label>
                            </th>
                            <td>
                                <select id="lectus_certificate_template" name="lectus_certificate_template">
                                    <option value="default" <?php selected(get_option('lectus_certificate_template'), 'default'); ?>>
                                        <?php _e('기본', 'lectus-class-system'); ?>
                                    </option>
                                    <option value="modern" <?php selected(get_option('lectus_certificate_template'), 'modern'); ?>>
                                        <?php _e('모던', 'lectus-class-system'); ?>
                                    </option>
                                    <option value="classic" <?php selected(get_option('lectus_certificate_template'), 'classic'); ?>>
                                        <?php _e('클래식', 'lectus-class-system'); ?>
                                    </option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div id="emails" class="tab-content" style="display:none;">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <?php _e('이메일 알림 활성화', 'lectus-class-system'); ?>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="lectus_enable_email_notifications" 
                                           value="yes" <?php checked(get_option('lectus_enable_email_notifications', 'yes'), 'yes'); ?> />
                                    <?php _e('수강 등록 및 수료 이메일 발송', 'lectus-class-system'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="lectus_enrollment_email_subject">
                                    <?php _e('수강 등록 이메일 제목', 'lectus-class-system'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" id="lectus_enrollment_email_subject" 
                                       name="lectus_enrollment_email_subject" 
                                       value="<?php echo esc_attr(get_option('lectus_enrollment_email_subject', '수강 등록이 완료되었습니다')); ?>" 
                                       class="regular-text" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="lectus_completion_email_subject">
                                    <?php _e('수료 이메일 제목', 'lectus-class-system'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" id="lectus_completion_email_subject" 
                                       name="lectus_completion_email_subject" 
                                       value="<?php echo esc_attr(get_option('lectus_completion_email_subject', '축하합니다! 과정을 완료하셨습니다')); ?>" 
                                       class="regular-text" />
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- 고급 설정 탭 -->
                <div id="advanced" class="tab-content" style="display:none;">
                    <h3><?php _e('Rate Limit 설정', 'lectus-class-system'); ?></h3>
                    <?php self::render_rate_limit_section(); ?>
                    
                    <h3><?php _e('캐시 설정', 'lectus-class-system'); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <?php _e('캐시 사용', 'lectus-class-system'); ?>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="lectus_enable_cache" 
                                           value="yes" <?php checked(get_option('lectus_enable_cache', 'yes'), 'yes'); ?> />
                                    <?php _e('성능 향상을 위해 캐시 사용', 'lectus-class-system'); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- 개발 도구 탭 -->
                <div id="development" class="tab-content" style="display:none;">
                    <h3><?php _e('테스트 데이터 생성', 'lectus-class-system'); ?></h3>
                    <?php self::render_test_data_section(); ?>
                    
                    <h3><?php _e('테스트 페이지', 'lectus-class-system'); ?></h3>
                    <?php self::render_test_pages_section(); ?>
                    
                    <h3><?php _e('디버그 모드', 'lectus-class-system'); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <?php _e('디버그 모드', 'lectus-class-system'); ?>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="lectus_debug_mode" 
                                           value="yes" <?php checked(get_option('lectus_debug_mode', 'no'), 'yes'); ?> />
                                    <?php _e('디버그 모드 활성화 (개발 환경에서만 사용)', 'lectus-class-system'); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- 시스템 탭 -->
                <div id="system" class="tab-content" style="display:none;">
                    <h3><?php _e('시스템 정보', 'lectus-class-system'); ?></h3>
                    <?php self::render_system_info(); ?>
                    
                    <h3><?php _e('로그 관리', 'lectus-class-system'); ?></h3>
                    <?php self::render_logs_section(); ?>
                    
                    <h3><?php _e('데이터베이스 관리', 'lectus-class-system'); ?></h3>
                    <?php self::render_database_section(); ?>
                </div>
                
                <p class="submit">
                    <input type="submit" name="lectus_save_settings" class="button-primary" 
                           value="<?php _e('설정 저장', 'lectus-class-system'); ?>" />
                </p>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('.nav-tab').on('click', function(e) {
                e.preventDefault();
                $('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                $('.tab-content').hide();
                $($(this).attr('href')).show();
            });
        });
        </script>
        <?php
    }
    
    /**
     * Render Rate Limit section
     */
    private static function render_rate_limit_section() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'lectus_rate_limits';
        
        // Handle reset action
        if (isset($_POST['reset_rate_limit'])) {
            $user_id = intval($_POST['user_id']);
            $wpdb->delete($table_name, array('user_id' => $user_id));
            echo '<div class="notice notice-success is-dismissible"><p>Rate limit이 초기화되었습니다.</p></div>';
        }
        
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <?php _e('Q&A Rate Limit', 'lectus-class-system'); ?>
                </th>
                <td>
                    <input type="number" name="lectus_qa_rate_limit" 
                           value="<?php echo get_option('lectus_qa_rate_limit', '5'); ?>" 
                           min="1" max="100" />
                    <span><?php _e('질문/분', 'lectus-class-system'); ?></span>
                </td>
            </tr>
        </table>
        
        <h4><?php _e('사용자별 Rate Limit 상태', 'lectus-class-system'); ?></h4>
        <?php
        $limits = $wpdb->get_results("
            SELECT rl.*, u.user_login, u.user_email 
            FROM $table_name rl
            JOIN {$wpdb->users} u ON rl.user_id = u.ID
            ORDER BY rl.last_attempt DESC
            LIMIT 20
        ");
        
        if ($limits) {
            ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('사용자', 'lectus-class-system'); ?></th>
                        <th><?php _e('시도 횟수', 'lectus-class-system'); ?></th>
                        <th><?php _e('마지막 시도', 'lectus-class-system'); ?></th>
                        <th><?php _e('작업', 'lectus-class-system'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($limits as $limit): ?>
                    <tr>
                        <td><?php echo esc_html($limit->user_login); ?></td>
                        <td><?php echo $limit->attempt_count; ?></td>
                        <td><?php echo $limit->last_attempt; ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?php echo $limit->user_id; ?>">
                                <button type="submit" name="reset_rate_limit" class="button button-small">
                                    <?php _e('초기화', 'lectus-class-system'); ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php
        } else {
            echo '<p>' . __('활성 rate limit 기록이 없습니다.', 'lectus-class-system') . '</p>';
        }
    }
    
    /**
     * Render Test Data section
     */
    private static function render_test_data_section() {
        ?>
        <div class="test-data-section">
            <div class="notice notice-warning inline">
                <p><?php _e('⚠️ 주의: 이 기능은 개발 및 테스트 목적으로만 사용하세요. 실제 운영 사이트에서는 사용하지 마세요.', 'lectus-class-system'); ?></p>
            </div>
            
            <h4><?php _e('일괄 테스트 데이터 생성', 'lectus-class-system'); ?></h4>
            <p>
                <button type="button" class="button button-primary" onclick="generateAllTestData()">
                    <?php _e('전체 테스트 데이터 생성', 'lectus-class-system'); ?>
                </button>
                <span class="description"><?php _e('카테고리, 강의, 레슨, 학생 등 모든 테스트 데이터를 한번에 생성합니다.', 'lectus-class-system'); ?></span>
            </p>
            
            <h4><?php _e('개별 데이터 생성', 'lectus-class-system'); ?></h4>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('카테고리 및 난이도', 'lectus-class-system'); ?></th>
                    <td>
                        <button type="button" class="button" onclick="generateTestData('categories')">
                            <?php _e('생성', 'lectus-class-system'); ?>
                        </button>
                        <span class="description"><?php _e('프로그래밍, 디자인, 비즈니스 카테고리와 난이도를 생성합니다', 'lectus-class-system'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('패키지 강의', 'lectus-class-system'); ?></th>
                    <td>
                        <button type="button" class="button" onclick="generateTestData('package')">
                            <?php _e('3개 생성', 'lectus-class-system'); ?>
                        </button>
                        <span class="description"><?php _e('샘플 패키지 강의를 생성합니다', 'lectus-class-system'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('단과 강의', 'lectus-class-system'); ?></th>
                    <td>
                        <button type="button" class="button" onclick="generateTestData('single')">
                            <?php _e('6개 생성', 'lectus-class-system'); ?>
                        </button>
                        <span class="description"><?php _e('샘플 단과 강의를 생성합니다', 'lectus-class-system'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('레슨', 'lectus-class-system'); ?></th>
                    <td>
                        <button type="button" class="button" onclick="generateTestData('lessons')">
                            <?php _e('각 강의당 10개 생성', 'lectus-class-system'); ?>
                        </button>
                        <span class="description"><?php _e('각 강의에 샘플 레슨을 생성합니다', 'lectus-class-system'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('학생', 'lectus-class-system'); ?></th>
                    <td>
                        <button type="button" class="button" onclick="generateTestData('students')">
                            <?php _e('5명 생성', 'lectus-class-system'); ?>
                        </button>
                        <span class="description"><?php _e('테스트 학생 계정을 생성합니다 (test_student_1~5)', 'lectus-class-system'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('수강 등록', 'lectus-class-system'); ?></th>
                    <td>
                        <button type="button" class="button" onclick="generateTestData('enrollments')">
                            <?php _e('생성', 'lectus-class-system'); ?>
                        </button>
                        <span class="description"><?php _e('테스트 학생의 수강 등록 및 진도 데이터를 생성합니다', 'lectus-class-system'); ?></span>
                    </td>
                </tr>
                <?php if (class_exists('WooCommerce')): ?>
                <tr>
                    <th scope="row"><?php _e('WooCommerce 상품', 'lectus-class-system'); ?></th>
                    <td>
                        <button type="button" class="button" onclick="generateTestData('products')">
                            <?php _e('생성', 'lectus-class-system'); ?>
                        </button>
                        <span class="description"><?php _e('강의와 연결된 WooCommerce 상품을 생성합니다', 'lectus-class-system'); ?></span>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th scope="row"><?php _e('샘플 수료증', 'lectus-class-system'); ?></th>
                    <td>
                        <button type="button" class="button" onclick="generateTestData('certificates')">
                            <?php _e('생성', 'lectus-class-system'); ?>
                        </button>
                        <span class="description"><?php _e('완료된 강의에 대한 샘플 수료증을 생성합니다', 'lectus-class-system'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('테스트 페이지', 'lectus-class-system'); ?></th>
                    <td>
                        <button type="button" class="button" onclick="createTestPages()">
                            <?php _e('페이지 생성', 'lectus-class-system'); ?>
                        </button>
                        <span class="description"><?php _e('쇼트코드가 포함된 테스트 페이지를 생성합니다', 'lectus-class-system'); ?></span>
                    </td>
                </tr>
            </table>
            
            <h4><?php _e('테스트 페이지 생성', 'lectus-class-system'); ?></h4>
            <p><?php _e('플러그인 기능을 테스트할 수 있는 페이지를 생성합니다.', 'lectus-class-system'); ?></p>
            <p>
                <button type="button" class="button" onclick="createTestPages()">
                    <?php _e('테스트 페이지 생성', 'lectus-class-system'); ?>
                </button>
                <span class="description"><?php _e('강의 목록, 내 강의, 수료증 등의 페이지를 생성합니다', 'lectus-class-system'); ?></span>
            </p>
            
            <h4><?php _e('데이터 초기화', 'lectus-class-system'); ?></h4>
            <p class="submit">
                <button type="button" class="button button-secondary" onclick="if(confirm('모든 테스트 데이터를 삭제하시겠습니까?')) cleanTestData()">
                    <?php _e('테스트 데이터 삭제', 'lectus-class-system'); ?>
                </button>
                <span class="description" style="color: red;"><?php _e('⚠️ 주의: 모든 테스트 데이터가 삭제됩니다!', 'lectus-class-system'); ?></span>
            </p>
            
            <script>
            function generateTestData(type) {
                if (confirm('테스트 데이터를 생성하시겠습니까?')) {
                    jQuery.post(ajaxurl, {
                        action: 'lectus_generate_test_data',
                        type: type,
                        nonce: '<?php echo wp_create_nonce('lectus-test-data'); ?>'
                    }, function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            if (type === 'categories' || type === 'products') {
                                location.reload();
                            }
                        } else {
                            alert('오류: ' + response.data.message);
                        }
                    });
                }
            }
            
            function generateAllTestData() {
                if (confirm('모든 테스트 데이터를 생성하시겠습니까? 시간이 걸릴 수 있습니다.')) {
                    var types = ['categories', 'package', 'single', 'lessons', 'students', 'enrollments', 'products', 'certificates'];
                    var index = 0;
                    
                    function generateNext() {
                        if (index < types.length) {
                            jQuery.post(ajaxurl, {
                                action: 'lectus_generate_test_data',
                                type: types[index],
                                nonce: '<?php echo wp_create_nonce('lectus-test-data'); ?>'
                            }, function(response) {
                                console.log(types[index] + ': ' + (response.success ? 'Success' : 'Failed'));
                                index++;
                                generateNext();
                            });
                        } else {
                            alert('모든 테스트 데이터가 생성되었습니다.');
                            location.reload();
                        }
                    }
                    generateNext();
                }
            }
            
            function createTestPages() {
                if (confirm('테스트 페이지를 생성하시겠습니까?')) {
                    jQuery.post(ajaxurl, {
                        action: 'lectus_create_test_pages',
                        nonce: '<?php echo wp_create_nonce('lectus-test-pages'); ?>'
                    }, function(response) {
                        if (response.success) {
                            alert(response.data.message);
                        } else {
                            alert('오류: ' + response.data.message);
                        }
                    });
                }
            }
            
            function cleanTestData() {
                jQuery.post(ajaxurl, {
                    action: 'lectus_clean_test_data',
                    nonce: '<?php echo wp_create_nonce('lectus-clean-data'); ?>'
                }, function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert('오류: ' + response.data.message);
                    }
                });
            }
            </script>
        </div>
        <?php
    }
    
    /**
     * Render Test Pages section
     */
    private static function render_test_pages_section() {
        ?>
        <div class="test-pages-section">
            <p><?php _e('플러그인 기능을 테스트할 수 있는 페이지 목록입니다.', 'lectus-class-system'); ?></p>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('테스트 페이지', 'lectus-class-system'); ?></th>
                        <th><?php _e('설명', 'lectus-class-system'); ?></th>
                        <th><?php _e('작업', 'lectus-class-system'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php _e('수강 등록 테스트', 'lectus-class-system'); ?></td>
                        <td><?php _e('수강 등록 프로세스를 테스트합니다', 'lectus-class-system'); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=lectus-test&test=enrollment'); ?>" 
                               class="button button-small" target="_blank">
                                <?php _e('테스트', 'lectus-class-system'); ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td><?php _e('수료증 생성 테스트', 'lectus-class-system'); ?></td>
                        <td><?php _e('수료증 생성 및 다운로드를 테스트합니다', 'lectus-class-system'); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=lectus-test&test=certificate'); ?>" 
                               class="button button-small" target="_blank">
                                <?php _e('테스트', 'lectus-class-system'); ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td><?php _e('Q&A 시스템 테스트', 'lectus-class-system'); ?></td>
                        <td><?php _e('Q&A 질문/답변 기능을 테스트합니다', 'lectus-class-system'); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=lectus-test&test=qa'); ?>" 
                               class="button button-small" target="_blank">
                                <?php _e('테스트', 'lectus-class-system'); ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td><?php _e('이메일 알림 테스트', 'lectus-class-system'); ?></td>
                        <td><?php _e('이메일 알림 발송을 테스트합니다', 'lectus-class-system'); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=lectus-test&test=email'); ?>" 
                               class="button button-small" target="_blank">
                                <?php _e('테스트', 'lectus-class-system'); ?>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    /**
     * Render System Info
     */
    private static function render_system_info() {
        global $wpdb;
        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('WordPress 버전', 'lectus-class-system'); ?></th>
                <td><?php echo get_bloginfo('version'); ?></td>
            </tr>
            <tr>
                <th scope="row"><?php _e('PHP 버전', 'lectus-class-system'); ?></th>
                <td><?php echo phpversion(); ?></td>
            </tr>
            <tr>
                <th scope="row"><?php _e('MySQL 버전', 'lectus-class-system'); ?></th>
                <td><?php echo $wpdb->db_version(); ?></td>
            </tr>
            <tr>
                <th scope="row"><?php _e('플러그인 버전', 'lectus-class-system'); ?></th>
                <td><?php echo LECTUS_VERSION; ?></td>
            </tr>
            <tr>
                <th scope="row"><?php _e('WooCommerce', 'lectus-class-system'); ?></th>
                <td><?php echo class_exists('WooCommerce') ? __('설치됨', 'lectus-class-system') : __('설치 안됨', 'lectus-class-system'); ?></td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Render Logs section
     */
    private static function render_logs_section() {
        ?>
        <p><?php _e('시스템 로그를 확인하고 관리합니다.', 'lectus-class-system'); ?></p>
        <p>
            <a href="<?php echo admin_url('admin.php?page=lectus-logs'); ?>" 
               class="button" target="_blank">
                <?php _e('로그 보기', 'lectus-class-system'); ?>
            </a>
            <button type="button" class="button" onclick="clearLogs()">
                <?php _e('로그 삭제', 'lectus-class-system'); ?>
            </button>
        </p>
        
        <script>
        function clearLogs() {
            if (confirm('모든 로그를 삭제하시겠습니까?')) {
                jQuery.post(ajaxurl, {
                    action: 'lectus_clear_logs',
                    nonce: '<?php echo wp_create_nonce('lectus-clear-logs'); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('로그가 삭제되었습니다.');
                    }
                });
            }
        }
        </script>
        <?php
    }
    
    /**
     * Render Database section
     */
    private static function render_database_section() {
        global $wpdb;
        
        $tables = array(
            'lectus_progress' => __('진도 관리', 'lectus-class-system'),
            'lectus_enrollment' => __('수강 등록', 'lectus-class-system'),
            'lectus_certificates' => __('수료증', 'lectus-class-system'),
            'lectus_qa_questions' => __('Q&A 질문', 'lectus-class-system'),
            'lectus_qa_answers' => __('Q&A 답변', 'lectus-class-system'),
            'lectus_materials' => __('강의 자료', 'lectus-class-system'),
            'lectus_logs' => __('로그', 'lectus-class-system'),
            'lectus_rate_limits' => __('Rate Limits', 'lectus-class-system')
        );
        
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('테이블', 'lectus-class-system'); ?></th>
                    <th><?php _e('용도', 'lectus-class-system'); ?></th>
                    <th><?php _e('레코드 수', 'lectus-class-system'); ?></th>
                    <th><?php _e('크기', 'lectus-class-system'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tables as $table => $description): 
                    $full_table = $wpdb->prefix . $table;
                    $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table'") == $full_table;
                    
                    if ($exists) {
                        $count = $wpdb->get_var("SELECT COUNT(*) FROM $full_table");
                        $size = $wpdb->get_var("
                            SELECT ROUND(((data_length + index_length) / 1024), 2) AS size 
                            FROM information_schema.TABLES 
                            WHERE table_schema = DATABASE() 
                            AND table_name = '$full_table'
                        ");
                    }
                ?>
                <tr>
                    <td><?php echo $table; ?></td>
                    <td><?php echo $description; ?></td>
                    <td><?php echo $exists ? number_format($count) : '-'; ?></td>
                    <td><?php echo $exists ? $size . ' KB' : '-'; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <p>
            <button type="button" class="button" onclick="optimizeTables()">
                <?php _e('테이블 최적화', 'lectus-class-system'); ?>
            </button>
        </p>
        
        <script>
        function optimizeTables() {
            if (confirm('데이터베이스 테이블을 최적화하시겠습니까?')) {
                jQuery.post(ajaxurl, {
                    action: 'lectus_optimize_tables',
                    nonce: '<?php echo wp_create_nonce('lectus-optimize'); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('테이블이 최적화되었습니다.');
                        location.reload();
                    }
                });
            }
        }
        </script>
        <?php
    }
    
    private static function save_settings() {
        // General settings
        if (isset($_POST['lectus_default_access_duration'])) {
            update_option('lectus_default_access_duration', sanitize_text_field($_POST['lectus_default_access_duration']));
        }
        if (isset($_POST['lectus_completion_threshold'])) {
            update_option('lectus_completion_threshold', sanitize_text_field($_POST['lectus_completion_threshold']));
        }
        
        // Enrollment settings
        update_option('lectus_auto_assign_student_role', isset($_POST['lectus_auto_assign_student_role']) ? 'yes' : 'no');
        
        // Certificate settings
        update_option('lectus_auto_generate_certificates', isset($_POST['lectus_auto_generate_certificates']) ? 'yes' : 'no');
        if (isset($_POST['lectus_certificate_template'])) {
            update_option('lectus_certificate_template', sanitize_text_field($_POST['lectus_certificate_template']));
        }
        
        // Email settings
        update_option('lectus_enable_email_notifications', isset($_POST['lectus_enable_email_notifications']) ? 'yes' : 'no');
        if (isset($_POST['lectus_enrollment_email_subject'])) {
            update_option('lectus_enrollment_email_subject', sanitize_text_field($_POST['lectus_enrollment_email_subject']));
        }
        if (isset($_POST['lectus_completion_email_subject'])) {
            update_option('lectus_completion_email_subject', sanitize_text_field($_POST['lectus_completion_email_subject']));
        }
    }
}