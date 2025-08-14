<?php
/**
 * Certificate System for Lectus Class System
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Certificate {
    
    public static function init() {
        // Certificate generation hooks
        add_action('lectus_course_completed', array(__CLASS__, 'auto_generate_certificate'), 10, 2);
        
        // Certificate viewing endpoint for individual certificates only
        add_action('init', array(__CLASS__, 'add_rewrite_rules'));
        add_filter('query_vars', array(__CLASS__, 'add_query_vars'));
        add_action('template_redirect', array(__CLASS__, 'handle_certificate_view'));
    }
    
    public static function generate($user_id, $course_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_certificates';
        
        // Check if certificate already exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d AND course_id = %d",
            $user_id,
            $course_id
        ));
        
        if ($existing) {
            return $existing->id;
        }
        
        // Check if course is actually completed
        if (!Lectus_Progress::is_course_completed($user_id, $course_id)) {
            return false;
        }
        
        // Check if certificates are enabled for this course
        $certificate_enabled = get_post_meta($course_id, '_certificate_enabled', true);
        if (!$certificate_enabled) {
            return false;
        }
        
        // Generate unique certificate number
        $certificate_number = self::generate_certificate_number($user_id, $course_id);
        
        // Insert certificate record
        $result = $wpdb->insert(
            $table,
            array(
                'user_id' => $user_id,
                'course_id' => $course_id,
                'certificate_number' => $certificate_number,
                'issued_at' => current_time('mysql'),
                'pdf_url' => ''
            )
        );
        
        if ($result) {
            $certificate_id = $wpdb->insert_id;
            
            // Generate PDF (if library available)
            $pdf_url = self::generate_pdf($certificate_id);
            if ($pdf_url) {
                $wpdb->update(
                    $table,
                    array('pdf_url' => $pdf_url),
                    array('id' => $certificate_id)
                );
            }
            
            // Send certificate email
            self::send_certificate_email($user_id, $course_id, $certificate_id);
            
            // Trigger action
            do_action('lectus_certificate_generated', $user_id, $course_id, $certificate_id);
            
            return $certificate_id;
        }
        
        return false;
    }
    
    private static function generate_certificate_number($user_id, $course_id) {
        $prefix = 'LCS';
        $year = date('Y');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        
        return sprintf('%s-%s-%d%d-%s', $prefix, $year, $user_id, $course_id, $random);
    }
    
    public static function get_certificate($certificate_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_certificates';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $certificate_id
        ));
    }
    
    public static function get_certificate_by_number($certificate_number) {
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_certificates';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE certificate_number = %s",
            $certificate_number
        ));
    }
    
    public static function get_user_certificates($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_certificates';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d ORDER BY issued_at DESC",
            $user_id
        ));
    }
    
    public static function get_course_certificates($course_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'lectus_certificates';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE course_id = %d ORDER BY issued_at DESC",
            $course_id
        ));
    }
    
    public static function verify($certificate_number) {
        return self::get_certificate_by_number($certificate_number);
    }
    
    public static function get_certificate_url($certificate_id) {
        return home_url('/certificate/' . $certificate_id);
    }
    
    public static function auto_generate_certificate($user_id, $course_id) {
        // Check if auto-generation is enabled
        $auto_generate = get_option('lectus_auto_generate_certificates', 'yes');
        if ($auto_generate !== 'yes') {
            return;
        }
        
        // Generate certificate
        self::generate($user_id, $course_id);
    }
    
    public static function add_rewrite_rules() {
        // Individual certificate view only - list view uses WordPress pages with shortcodes
        add_rewrite_rule(
            '^certificate/([0-9]+)/?$',
            'index.php?lectus_certificate=$1',
            'top'
        );
    }
    
    public static function add_query_vars($vars) {
        $vars[] = 'lectus_certificate';
        return $vars;
    }
    
    public static function handle_certificate_view() {
        // Handle individual certificate view only
        $certificate_id = get_query_var('lectus_certificate');
        if (!$certificate_id) {
            return;
        }
        
        $certificate = self::get_certificate($certificate_id);
        if (!$certificate) {
            wp_die(__('수료증을 찾을 수 없습니다.', 'lectus-class-system'));
        }
        
        // Check permissions
        $user_id = get_current_user_id();
        if ($user_id != $certificate->user_id && !current_user_can('manage_students')) {
            wp_die(__('이 수료증을 볼 권한이 없습니다.', 'lectus-class-system'));
        }
        
        // Display certificate
        self::display_certificate($certificate);
        exit;
    }
    
    public static function display_certificate($certificate) {
        $user = get_user_by('id', $certificate->user_id);
        $course = get_post($certificate->course_id);
        
        if (!$user || !$course) {
            wp_die(__('수료증 정보를 불러올 수 없습니다.', 'lectus-class-system'));
        }
        
        // Get template
        $template = get_option('lectus_certificate_template', 'default');
        $template_file = LECTUS_PLUGIN_DIR . 'templates/certificate-' . $template . '.php';
        
        if (!file_exists($template_file)) {
            $template_file = LECTUS_PLUGIN_DIR . 'templates/certificate-default.php';
        }
        
        // Create default template if not exists
        if (!file_exists($template_file)) {
            self::create_default_certificate_template($template_file);
        }
        
        // Include template
        include $template_file;
    }
    
    // Note: Certificate list display is handled by the [lectus_certificates] shortcode
    // and WordPress pages. This keeps the system simple and maintainable.
    
    private static function create_default_certificate_template($file) {
        // Create the template content with proper escaping
        $content = '<!DOCTYPE html>' . "\n";
        $content .= '<html lang="ko">' . "\n";
        $content .= '<head>' . "\n";
        $content .= '    <meta charset="UTF-8">' . "\n";
        $content .= '    <meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";
        $content .= '    <title><?php _e("수료증", "lectus-class-system"); ?></title>' . "\n";
        $content .= '    <style>' . "\n";
        $content .= '        @page { size: A4 landscape; margin: 0; }' . "\n";
        $content .= '        body {' . "\n";
        $content .= '            font-family: "Noto Sans KR", sans-serif;' . "\n";
        $content .= '            margin: 0;' . "\n";
        $content .= '            padding: 0;' . "\n";
        $content .= '            display: flex;' . "\n";
        $content .= '            justify-content: center;' . "\n";
        $content .= '            align-items: center;' . "\n";
        $content .= '            min-height: 100vh;' . "\n";
        $content .= '            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);' . "\n";
        $content .= '        }' . "\n";
        $content .= '        .certificate {' . "\n";
        $content .= '            width: 1024px;' . "\n";
        $content .= '            background: white;' . "\n";
        $content .= '            padding: 60px;' . "\n";
        $content .= '            box-shadow: 0 20px 60px rgba(0,0,0,0.3);' . "\n";
        $content .= '            position: relative;' . "\n";
        $content .= '            border: 10px solid #gold;' . "\n";
        $content .= '        }' . "\n";
        $content .= '        .certificate::before {' . "\n";
        $content .= '            content: "";' . "\n";
        $content .= '            position: absolute;' . "\n";
        $content .= '            top: 20px;' . "\n";
        $content .= '            left: 20px;' . "\n";
        $content .= '            right: 20px;' . "\n";
        $content .= '            bottom: 20px;' . "\n";
        $content .= '            border: 2px solid #ddd;' . "\n";
        $content .= '        }' . "\n";
        $content .= '        .header { text-align: center; margin-bottom: 40px; }' . "\n";
        $content .= '        h1 { font-size: 48px; color: #333; margin: 0; font-weight: 300; letter-spacing: 5px; }' . "\n";
        $content .= '        .content { text-align: center; margin: 40px 0; }' . "\n";
        $content .= '        .recipient { font-size: 36px; color: #667eea; margin: 20px 0; font-weight: bold; }' . "\n";
        $content .= '        .course-title { font-size: 28px; color: #555; margin: 20px 0; }' . "\n";
        $content .= '        .description { font-size: 18px; color: #666; line-height: 1.6; margin: 30px auto; max-width: 600px; }' . "\n";
        $content .= '        .footer { display: flex; justify-content: space-between; margin-top: 60px; padding-top: 40px; border-top: 1px solid #eee; }' . "\n";
        $content .= '        .signature { text-align: center; flex: 1; }' . "\n";
        $content .= '        .signature-line { width: 200px; border-bottom: 1px solid #333; margin: 0 auto 10px; height: 40px; }' . "\n";
        $content .= '        .signature-name { font-size: 14px; color: #666; }' . "\n";
        $content .= '        .certificate-info { text-align: center; margin-top: 30px; font-size: 12px; color: #999; }' . "\n";
        $content .= '        .certificate-number { font-family: monospace; font-size: 14px; color: #666; margin-top: 10px; }' . "\n";
        $content .= '        .issued-date { font-size: 16px; color: #666; margin-top: 20px; }' . "\n";
        $content .= '        @media print { body { background: white; } .certificate { box-shadow: none; width: 100%; max-width: none; } }' . "\n";
        $content .= '    </style>' . "\n";
        $content .= '</head>' . "\n";
        $content .= '<body>' . "\n";
        $content .= '    <div class="certificate">' . "\n";
        $content .= '        <div class="header">' . "\n";
        $content .= '            <h1><?php _e("수료증", "lectus-class-system"); ?></h1>' . "\n";
        $content .= '        </div>' . "\n";
        $content .= '        <div class="content">' . "\n";
        $content .= '            <p class="description"><?php _e("이 수료증은 아래 명시된 과정을 성공적으로 완료하였음을 증명합니다", "lectus-class-system"); ?></p>' . "\n";
        $content .= '            <div class="recipient"><?php echo esc_html($user->display_name); ?></div>' . "\n";
        $content .= '            <p class="description"><?php _e("님께서", "lectus-class-system"); ?></p>' . "\n";
        $content .= '            <div class="course-title"><?php echo esc_html($course->post_title); ?></div>' . "\n";
        $content .= '            <p class="description"><?php _e("과정을 성공적으로 수료하였음을 증명합니다", "lectus-class-system"); ?></p>' . "\n";
        $content .= '            <div class="issued-date">' . "\n";
        $content .= '                <?php echo date_i18n("Y년 n월 j일", strtotime($certificate->issued_at)); ?>' . "\n";
        $content .= '            </div>' . "\n";
        $content .= '        </div>' . "\n";
        $content .= '        <div class="footer">' . "\n";
        $content .= '            <div class="signature">' . "\n";
        $content .= '                <div class="signature-line"></div>' . "\n";
        $content .= '                <div class="signature-name"><?php echo get_bloginfo("name"); ?></div>' . "\n";
        $content .= '            </div>' . "\n";
        $content .= '        </div>' . "\n";
        $content .= '        <div class="certificate-info">' . "\n";
        $content .= '            <div class="certificate-number">' . "\n";
        $content .= '                <?php _e("수료증 번호:", "lectus-class-system"); ?> <?php echo esc_html($certificate->certificate_number); ?>' . "\n";
        $content .= '            </div>' . "\n";
        $content .= '            <div>' . "\n";
        $content .= '                <?php _e("발급일:", "lectus-class-system"); ?> <?php echo date_i18n(get_option("date_format"), strtotime($certificate->issued_at)); ?>' . "\n";
        $content .= '            </div>' . "\n";
        $content .= '        </div>' . "\n";
        $content .= '    </div>' . "\n";
        $content .= '    <script>' . "\n";
        $content .= '    window.onload = function() {' . "\n";
        $content .= '        if (window.location.search.includes("download=pdf")) {' . "\n";
        $content .= '            window.print();' . "\n";
        $content .= '        }' . "\n";
        $content .= '    }' . "\n";
        $content .= '    </script>' . "\n";
        $content .= '</body>' . "\n";
        $content .= '</html>';
        
        file_put_contents($file, $content);
    }
    
    private static function generate_pdf($certificate_id) {
        // This would require a PDF library like TCPDF or mPDF
        // For now, we'll use the HTML version with print CSS
        return '';
    }
    
    private static function send_certificate_email($user_id, $course_id, $certificate_id) {
        $enable_emails = get_option('lectus_enable_email_notifications', 'yes');
        if ($enable_emails !== 'yes') {
            return;
        }
        
        $user = get_user_by('id', $user_id);
        $course = get_post($course_id);
        $certificate = self::get_certificate($certificate_id);
        
        if (!$user || !$course || !$certificate) {
            return;
        }
        
        $subject = get_option('lectus_completion_email_subject', __('축하합니다! 과정을 완료하셨습니다', 'lectus-class-system'));
        $subject = str_replace('{course_title}', $course->post_title, $subject);
        
        $certificate_url = self::get_certificate_url($certificate_id);
        
        $message = sprintf(
            __("안녕하세요 %s님,\n\n축하합니다! '%s' 과정을 성공적으로 완료하셨습니다.\n\n수료증을 다운로드하시려면 아래 링크를 클릭하세요:\n%s\n\n수료증 번호: %s\n\n감사합니다.", 'lectus-class-system'),
            $user->display_name,
            $course->post_title,
            $certificate_url,
            $certificate->certificate_number
        );
        
        wp_mail($user->user_email, $subject, $message);
    }
}