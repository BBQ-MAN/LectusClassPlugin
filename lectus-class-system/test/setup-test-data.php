<?php
/**
 * Test Data Setup Script for Lectus Class System
 * 
 * Run this script to generate dummy data for testing
 */

// Load WordPress
$wp_load_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';
if (!file_exists($wp_load_path)) {
    die("Could not find wp-load.php at: $wp_load_path\n");
}
require_once($wp_load_path);

// Check if running from CLI or admin
if (!current_user_can('manage_options')) {
    die('You must be an administrator to run this script.');
}

echo "Starting Lectus Class System Test Data Generation...\n\n";

// 1. Create Course Categories
echo "Creating course categories...\n";
$categories = array(
    '프로그래밍' => array('slug' => 'programming'),
    '디자인' => array('slug' => 'design'),
    '비즈니스' => array('slug' => 'business'),
    '마케팅' => array('slug' => 'marketing'),
    '언어' => array('slug' => 'language')
);

$category_ids = array();
foreach ($categories as $name => $args) {
    $term = wp_insert_term($name, 'course_category', $args);
    if (!is_wp_error($term)) {
        $category_ids[] = $term['term_id'];
        echo "  - Created category: $name\n";
    }
}

// 2. Create difficulty levels
echo "\nCreating difficulty levels...\n";
$levels = array(
    '초급' => array('slug' => 'beginner'),
    '중급' => array('slug' => 'intermediate'),
    '고급' => array('slug' => 'advanced')
);

$level_ids = array();
foreach ($levels as $name => $args) {
    $term = wp_insert_term($name, 'course_level', $args);
    if (!is_wp_error($term)) {
        $level_ids[] = $term['term_id'];
        echo "  - Created level: $name\n";
    }
}

// 3. Create Package Courses
echo "\nCreating package courses...\n";
$packages = array(
    array(
        'title' => '풀스택 웹 개발 마스터 패키지',
        'content' => '프론트엔드부터 백엔드까지 웹 개발의 모든 것을 마스터하는 종합 패키지입니다.',
        'meta' => array(
            '_max_students' => 100,
            '_access_level' => 'members',
            '_price' => 500000
        )
    ),
    array(
        'title' => 'UI/UX 디자인 완성 패키지',
        'content' => 'UI/UX 디자인의 기초부터 실무까지 완벽하게 마스터하는 패키지입니다.',
        'meta' => array(
            '_max_students' => 50,
            '_access_level' => 'members',
            '_price' => 400000
        )
    ),
    array(
        'title' => '디지털 마케팅 종합 패키지',
        'content' => 'SEO, SNS 마케팅, 콘텐츠 마케팅 등 디지털 마케팅의 모든 것을 배우는 패키지입니다.',
        'meta' => array(
            '_max_students' => 80,
            '_access_level' => 'public',
            '_price' => 350000
        )
    )
);

$package_ids = array();
foreach ($packages as $package) {
    $post_id = wp_insert_post(array(
        'post_title' => $package['title'],
        'post_content' => $package['content'],
        'post_type' => 'coursepackage',
        'post_status' => 'publish'
    ));
    
    if ($post_id) {
        foreach ($package['meta'] as $key => $value) {
            update_post_meta($post_id, $key, $value);
        }
        $package_ids[] = $post_id;
        echo "  - Created package: {$package['title']}\n";
    }
}

// 4. Create Single Courses
echo "\nCreating single courses...\n";
$courses = array(
    array(
        'title' => 'HTML/CSS 기초부터 실무까지',
        'content' => 'HTML과 CSS의 기초 개념부터 반응형 웹 디자인까지 완벽하게 마스터합니다.',
        'package' => 0, // First package
        'category' => 0, // Programming
        'level' => 0, // Beginner
        'meta' => array(
            '_course_duration' => 30,
            '_access_mode' => 'free',
            '_completion_score' => 80,
            '_certificate_enabled' => '1'
        )
    ),
    array(
        'title' => 'JavaScript 완전 정복',
        'content' => '자바스크립트의 기초부터 ES6+, 비동기 프로그래밍까지 완벽하게 학습합니다.',
        'package' => 0,
        'category' => 0,
        'level' => 1, // Intermediate
        'meta' => array(
            '_course_duration' => 45,
            '_access_mode' => 'sequential',
            '_completion_score' => 85,
            '_certificate_enabled' => '1'
        )
    ),
    array(
        'title' => 'React.js 실무 프로젝트',
        'content' => 'React.js를 활용한 실무 프로젝트를 진행하며 실전 경험을 쌓습니다.',
        'package' => 0,
        'category' => 0,
        'level' => 2, // Advanced
        'meta' => array(
            '_course_duration' => 60,
            '_access_mode' => 'free',
            '_completion_score' => 90,
            '_certificate_enabled' => '1'
        )
    ),
    array(
        'title' => 'UI 디자인 기초',
        'content' => 'UI 디자인의 기본 원칙과 도구 사용법을 학습합니다.',
        'package' => 1,
        'category' => 1, // Design
        'level' => 0,
        'meta' => array(
            '_course_duration' => 30,
            '_access_mode' => 'free',
            '_completion_score' => 80,
            '_certificate_enabled' => '1'
        )
    ),
    array(
        'title' => 'UX 리서치와 프로토타이핑',
        'content' => '사용자 경험 리서치 방법론과 프로토타입 제작 기법을 배웁니다.',
        'package' => 1,
        'category' => 1,
        'level' => 1,
        'meta' => array(
            '_course_duration' => 45,
            '_access_mode' => 'free',
            '_completion_score' => 85,
            '_certificate_enabled' => '1'
        )
    ),
    array(
        'title' => 'SEO 마케팅 전략',
        'content' => '검색엔진 최적화를 통한 효과적인 마케팅 전략을 학습합니다.',
        'package' => 2,
        'category' => 3, // Marketing
        'level' => 0,
        'meta' => array(
            '_course_duration' => 30,
            '_access_mode' => 'free',
            '_completion_score' => 75,
            '_certificate_enabled' => '1'
        )
    )
);

$course_ids = array();
foreach ($courses as $index => $course) {
    $post_id = wp_insert_post(array(
        'post_title' => $course['title'],
        'post_content' => $course['content'],
        'post_type' => 'coursesingle',
        'post_status' => 'publish'
    ));
    
    if ($post_id) {
        // Set package
        if (isset($package_ids[$course['package']])) {
            update_post_meta($post_id, '_package_id', $package_ids[$course['package']]);
            
            // Update package with courses
            $pkg_courses = get_post_meta($package_ids[$course['package']], '_package_courses', true) ?: array();
            $pkg_courses[] = $post_id;
            update_post_meta($package_ids[$course['package']], '_package_courses', $pkg_courses);
        }
        
        // Set meta
        foreach ($course['meta'] as $key => $value) {
            update_post_meta($post_id, $key, $value);
        }
        
        // Set category
        if (isset($category_ids[$course['category']])) {
            wp_set_object_terms($post_id, $category_ids[$course['category']], 'course_category');
        }
        
        // Set level
        if (isset($level_ids[$course['level']])) {
            wp_set_object_terms($post_id, $level_ids[$course['level']], 'course_level');
        }
        
        $course_ids[] = $post_id;
        echo "  - Created course: {$course['title']}\n";
    }
}

// 5. Create Lessons for each course
echo "\nCreating lessons...\n";
$lesson_templates = array(
    array('title' => '강의 소개 및 학습 목표', 'type' => 'text', 'duration' => 10),
    array('title' => '개발 환경 설정', 'type' => 'video', 'duration' => 20),
    array('title' => '기본 개념 이해하기', 'type' => 'video', 'duration' => 30),
    array('title' => '실습 예제 따라하기', 'type' => 'video', 'duration' => 40),
    array('title' => '심화 학습', 'type' => 'video', 'duration' => 35),
    array('title' => '퀴즈: 학습 내용 점검', 'type' => 'quiz', 'duration' => 15),
    array('title' => '프로젝트 과제', 'type' => 'assignment', 'duration' => 60),
    array('title' => '코드 리뷰 및 피드백', 'type' => 'text', 'duration' => 25),
    array('title' => '실무 팁과 베스트 프랙티스', 'type' => 'video', 'duration' => 30),
    array('title' => '마무리 및 다음 단계', 'type' => 'text', 'duration' => 15)
);

foreach ($course_ids as $course_id) {
    $course_title = get_the_title($course_id);
    echo "  Creating lessons for: $course_title\n";
    
    foreach ($lesson_templates as $index => $lesson) {
        $lesson_id = wp_insert_post(array(
            'post_title' => $lesson['title'],
            'post_content' => "이것은 '{$course_title}' 강의의 '{$lesson['title']}' 레슨입니다. 여기에는 실제 학습 내용이 포함됩니다.",
            'post_type' => 'lesson',
            'post_status' => 'publish',
            'menu_order' => $index + 1
        ));
        
        if ($lesson_id) {
            update_post_meta($lesson_id, '_course_id', $course_id);
            update_post_meta($lesson_id, '_lesson_type', $lesson['type']);
            update_post_meta($lesson_id, '_lesson_duration', $lesson['duration']);
            update_post_meta($lesson_id, '_completion_criteria', 'view');
            
            if ($lesson['type'] === 'video') {
                update_post_meta($lesson_id, '_video_url', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ');
            }
        }
    }
}

// 6. Create test students
echo "\nCreating test students...\n";
$students = array(
    array('username' => 'student1', 'email' => 'student1@test.com', 'name' => '김철수'),
    array('username' => 'student2', 'email' => 'student2@test.com', 'name' => '이영희'),
    array('username' => 'student3', 'email' => 'student3@test.com', 'name' => '박민수'),
    array('username' => 'student4', 'email' => 'student4@test.com', 'name' => '정지원'),
    array('username' => 'student5', 'email' => 'student5@test.com', 'name' => '최유진')
);

$student_ids = array();
foreach ($students as $student) {
    if (!username_exists($student['username'])) {
        $user_id = wp_create_user($student['username'], 'password123', $student['email']);
        if ($user_id) {
            wp_update_user(array(
                'ID' => $user_id,
                'display_name' => $student['name']
            ));
            
            $user = new WP_User($user_id);
            $user->add_role('lectus_student');
            
            $student_ids[] = $user_id;
            echo "  - Created student: {$student['name']} ({$student['username']})\n";
        }
    }
}

// 7. Enroll students in courses
echo "\nEnrolling students in courses...\n";
if (!empty($student_ids) && !empty($course_ids)) {
    foreach ($student_ids as $index => $student_id) {
        // Enroll each student in 1-3 courses
        $num_courses = rand(1, 3);
        $enrolled_courses = array_rand(array_flip($course_ids), $num_courses);
        if (!is_array($enrolled_courses)) {
            $enrolled_courses = array($enrolled_courses);
        }
        
        foreach ($enrolled_courses as $course_id) {
            $result = Lectus_Enrollment::enroll($student_id, $course_id, 0, 90);
            if ($result) {
                $student_name = get_userdata($student_id)->display_name;
                $course_name = get_the_title($course_id);
                echo "  - Enrolled $student_name in $course_name\n";
                
                // Add some progress
                $lessons = get_posts(array(
                    'post_type' => 'lesson',
                    'meta_key' => '_course_id',
                    'meta_value' => $course_id,
                    'posts_per_page' => -1,
                    'orderby' => 'menu_order',
                    'order' => 'ASC'
                ));
                
                $progress_count = rand(0, count($lessons));
                for ($i = 0; $i < $progress_count; $i++) {
                    if (isset($lessons[$i])) {
                        Lectus_Progress::complete_lesson($student_id, $course_id, $lessons[$i]->ID);
                    }
                }
            }
        }
    }
}

// 8. Create WooCommerce products (if WooCommerce is active)
if (class_exists('WooCommerce')) {
    echo "\nCreating WooCommerce products...\n";
    
    foreach ($course_ids as $index => $course_id) {
        $course = get_post($course_id);
        
        $product_id = wp_insert_post(array(
            'post_title' => $course->post_title . ' - 수강권',
            'post_content' => $course->post_content,
            'post_type' => 'product',
            'post_status' => 'publish'
        ));
        
        if ($product_id) {
            // Set product type
            wp_set_object_terms($product_id, 'simple', 'product_type');
            
            // Set product data
            update_post_meta($product_id, '_regular_price', ($index + 1) * 50000);
            update_post_meta($product_id, '_price', ($index + 1) * 50000);
            update_post_meta($product_id, '_virtual', 'yes');
            update_post_meta($product_id, '_sold_individually', 'yes');
            
            // Link to course
            update_post_meta($product_id, '_lectus_course_id', $course_id);
            update_post_meta($product_id, '_lectus_access_duration', 90);
            update_post_meta($product_id, '_lectus_auto_enroll', 'yes');
            
            // Update course with product ID
            update_post_meta($course_id, '_wc_product_id', $product_id);
            
            echo "  - Created product for: {$course->post_title}\n";
        }
    }
}

// 9. Generate some certificates for completed courses
echo "\nGenerating sample certificates...\n";
if (!empty($student_ids) && !empty($course_ids)) {
    // Generate certificates for first 2 students who completed courses
    for ($i = 0; $i < min(2, count($student_ids)); $i++) {
        $student_id = $student_ids[$i];
        $course_id = $course_ids[0]; // First course
        
        // Mark course as completed
        $lessons = get_posts(array(
            'post_type' => 'lesson',
            'meta_key' => '_course_id',
            'meta_value' => $course_id,
            'posts_per_page' => -1
        ));
        
        foreach ($lessons as $lesson) {
            Lectus_Progress::complete_lesson($student_id, $course_id, $lesson->ID);
        }
        
        // Generate certificate
        $cert_id = Lectus_Certificate::generate($student_id, $course_id);
        if ($cert_id) {
            $student_name = get_userdata($student_id)->display_name;
            $course_name = get_the_title($course_id);
            echo "  - Generated certificate for $student_name - $course_name\n";
        }
    }
}

echo "\n✅ Test data generation completed successfully!\n";
echo "\n📋 Summary:\n";
echo "  - Categories: " . count($category_ids) . "\n";
echo "  - Difficulty Levels: " . count($level_ids) . "\n";
echo "  - Package Courses: " . count($package_ids) . "\n";
echo "  - Single Courses: " . count($course_ids) . "\n";
echo "  - Lessons: " . (count($course_ids) * count($lesson_templates)) . "\n";
echo "  - Students: " . count($student_ids) . "\n";
if (class_exists('WooCommerce')) {
    echo "  - WooCommerce Products: " . count($course_ids) . "\n";
}

echo "\n🔑 Test Credentials:\n";
echo "  Admin: admin / admin\n";
echo "  Students: student1-5 / password123\n";

echo "\n🌐 URLs:\n";
echo "  Homepage: http://localhost:8000/\n";
echo "  Admin Dashboard: http://localhost:8000/wp-admin/\n";
echo "  Lectus Dashboard: http://localhost:8000/wp-admin/admin.php?page=lectus-class-system\n";

echo "\n✨ Lectus Class System is ready for testing!\n";