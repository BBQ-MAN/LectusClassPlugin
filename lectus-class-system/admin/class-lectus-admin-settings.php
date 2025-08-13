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