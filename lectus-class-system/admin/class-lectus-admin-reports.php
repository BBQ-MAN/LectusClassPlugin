<?php
/**
 * Reports Page for Lectus Class System
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Admin_Reports {
    
    public static function render_reports_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('보고서', 'lectus-class-system'); ?></h1>
            
            <div class="lectus-reports">
                <h2><?php _e('강의별 통계', 'lectus-class-system'); ?></h2>
                <?php self::render_course_stats(); ?>
                
                <h2><?php _e('수강생 통계', 'lectus-class-system'); ?></h2>
                <?php self::render_student_stats(); ?>
                
                <h2><?php _e('매출 보고서', 'lectus-class-system'); ?></h2>
                <?php self::render_revenue_report(); ?>
            </div>
        </div>
        <?php
    }
    
    private static function render_course_stats() {
        $courses = get_posts(array(
            'post_type' => 'coursesingle',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('강의명', 'lectus-class-system'); ?></th>
                    <th><?php _e('수강생 수', 'lectus-class-system'); ?></th>
                    <th><?php _e('평균 진도', 'lectus-class-system'); ?></th>
                    <th><?php _e('완료율', 'lectus-class-system'); ?></th>
                    <th><?php _e('수료증 발급', 'lectus-class-system'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course): 
                    $stats = Lectus_Progress::get_course_stats($course->ID);
                    $certificates = Lectus_Certificate::get_course_certificates($course->ID);
                ?>
                    <tr>
                        <td>
                            <strong>
                                <a href="<?php echo get_edit_post_link($course->ID); ?>">
                                    <?php echo esc_html($course->post_title); ?>
                                </a>
                            </strong>
                        </td>
                        <td><?php echo $stats['total_students']; ?></td>
                        <td><?php echo $stats['average_progress']; ?>%</td>
                        <td><?php echo $stats['completion_rate']; ?>%</td>
                        <td><?php echo count($certificates); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }
    
    private static function render_student_stats() {
        global $wpdb;
        
        // Get top students
        $enrollment_table = $wpdb->prefix . 'lectus_enrollment';
        $progress_table = $wpdb->prefix . 'lectus_progress';
        
        $top_students = $wpdb->get_results(
            "SELECT u.ID, u.display_name, 
                    COUNT(DISTINCT e.course_id) as enrolled_courses,
                    COUNT(DISTINCT p.lesson_id) as completed_lessons
             FROM {$wpdb->users} u
             INNER JOIN $enrollment_table e ON u.ID = e.user_id
             LEFT JOIN $progress_table p ON u.ID = p.user_id AND p.status = 'completed'
             WHERE e.status = 'active'
             GROUP BY u.ID
             ORDER BY completed_lessons DESC
             LIMIT 10"
        );
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('수강생', 'lectus-class-system'); ?></th>
                    <th><?php _e('등록 강의', 'lectus-class-system'); ?></th>
                    <th><?php _e('완료 레슨', 'lectus-class-system'); ?></th>
                    <th><?php _e('수료증', 'lectus-class-system'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($top_students as $student): 
                    $certificates = Lectus_Certificate::get_user_certificates($student->ID);
                ?>
                    <tr>
                        <td>
                            <strong>
                                <a href="<?php echo get_edit_user_link($student->ID); ?>">
                                    <?php echo esc_html($student->display_name); ?>
                                </a>
                            </strong>
                        </td>
                        <td><?php echo $student->enrolled_courses; ?></td>
                        <td><?php echo $student->completed_lessons; ?></td>
                        <td><?php echo count($certificates); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }
    
    private static function render_revenue_report() {
        if (!class_exists('WooCommerce')) {
            echo '<p>' . __('WooCommerce가 설치되지 않았습니다.', 'lectus-class-system') . '</p>';
            return;
        }
        
        global $wpdb;
        
        // Get products linked to courses
        $products = get_posts(array(
            'post_type' => 'product',
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => '_lectus_course_id',
                    'compare' => 'EXISTS'
                ),
                array(
                    'key' => '_lectus_package_id',
                    'compare' => 'EXISTS'
                )
            ),
            'posts_per_page' => -1
        ));
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('상품명', 'lectus-class-system'); ?></th>
                    <th><?php _e('연결된 강의', 'lectus-class-system'); ?></th>
                    <th><?php _e('판매 수량', 'lectus-class-system'); ?></th>
                    <th><?php _e('총 매출', 'lectus-class-system'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): 
                    $wc_product = wc_get_product($product->ID);
                    $course_id = get_post_meta($product->ID, '_lectus_course_id', true);
                    $package_id = get_post_meta($product->ID, '_lectus_package_id', true);
                    
                    $linked_item = null;
                    if ($course_id) {
                        $linked_item = get_post($course_id);
                    } elseif ($package_id) {
                        $linked_item = get_post($package_id);
                    }
                    
                    // Get sales data
                    $total_sales = get_post_meta($product->ID, 'total_sales', true) ?: 0;
                    $revenue = $total_sales * $wc_product->get_price();
                ?>
                    <tr>
                        <td>
                            <strong>
                                <a href="<?php echo get_edit_post_link($product->ID); ?>">
                                    <?php echo esc_html($product->post_title); ?>
                                </a>
                            </strong>
                        </td>
                        <td>
                            <?php if ($linked_item): ?>
                                <a href="<?php echo get_edit_post_link($linked_item->ID); ?>">
                                    <?php echo esc_html($linked_item->post_title); ?>
                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><?php echo $total_sales; ?></td>
                        <td><?php echo wc_price($revenue); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }
}