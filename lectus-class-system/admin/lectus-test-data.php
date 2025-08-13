<?php
/**
 * Test Data Generator - Admin Page
 * 
 * This file creates an admin page for generating test data
 */

// Menu is now integrated into settings page
// This file is kept for the test data generation function only

function lectus_test_data_page() {
    if (isset($_POST['generate_test_data']) && wp_verify_nonce($_POST['test_data_nonce'], 'generate_test_data')) {
        lectus_generate_test_data();
    }
    ?>
    <div class="wrap">
        <h1><?php _e('테스트 데이터 생성', 'lectus-class-system'); ?></h1>
        
        <div class="notice notice-warning">
            <p><?php _e('⚠️ 주의: 이 기능은 개발 및 테스트 목적으로만 사용하세요. 실제 운영 사이트에서는 사용하지 마세요.', 'lectus-class-system'); ?></p>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field('generate_test_data', 'test_data_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('생성할 데이터', 'lectus-class-system'); ?></th>
                    <td>
                        <label><input type="checkbox" name="create_categories" value="1" checked /> <?php _e('카테고리 및 난이도', 'lectus-class-system'); ?></label><br>
                        <label><input type="checkbox" name="create_packages" value="1" checked /> <?php _e('패키지강의 (3개)', 'lectus-class-system'); ?></label><br>
                        <label><input type="checkbox" name="create_courses" value="1" checked /> <?php _e('단과강의 (6개)', 'lectus-class-system'); ?></label><br>
                        <label><input type="checkbox" name="create_lessons" value="1" checked /> <?php _e('레슨 (각 강의당 10개)', 'lectus-class-system'); ?></label><br>
                        <label><input type="checkbox" name="create_students" value="1" checked /> <?php _e('테스트 학생 계정 (5개)', 'lectus-class-system'); ?></label><br>
                        <label><input type="checkbox" name="create_enrollments" value="1" checked /> <?php _e('수강 등록 및 진도 데이터', 'lectus-class-system'); ?></label><br>
                        <label><input type="checkbox" name="create_products" value="1" checked /> <?php _e('WooCommerce 상품', 'lectus-class-system'); ?></label><br>
                        <label><input type="checkbox" name="create_certificates" value="1" checked /> <?php _e('샘플 수료증', 'lectus-class-system'); ?></label><br>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="generate_test_data" class="button button-primary" value="<?php _e('테스트 데이터 생성', 'lectus-class-system'); ?>" onclick="return confirm('정말로 테스트 데이터를 생성하시겠습니까?');" />
            </p>
        </form>
        
        <hr>
        
        <h2><?php _e('생성된 테스트 계정', 'lectus-class-system'); ?></h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th><?php _e('사용자명', 'lectus-class-system'); ?></th>
                    <th><?php _e('비밀번호', 'lectus-class-system'); ?></th>
                    <th><?php _e('역할', 'lectus-class-system'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>admin</td>
                    <td>admin</td>
                    <td><?php _e('관리자', 'lectus-class-system'); ?></td>
                </tr>
                <tr>
                    <td>student1 ~ student5</td>
                    <td>password123</td>
                    <td><?php _e('수강생', 'lectus-class-system'); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php
}

function lectus_generate_test_data() {
    $created = array();
    
    // 1. Create Categories
    if (isset($_POST['create_categories'])) {
        $categories = array(
            '프로그래밍' => array('slug' => 'programming'),
            '디자인' => array('slug' => 'design'),
            '비즈니스' => array('slug' => 'business'),
            '마케팅' => array('slug' => 'marketing'),
            '언어' => array('slug' => 'language')
        );
        
        foreach ($categories as $name => $args) {
            if (!term_exists($name, 'course_category')) {
                wp_insert_term($name, 'course_category', $args);
                $created['categories'][] = $name;
            }
        }
        
        // Create levels
        $levels = array(
            '초급' => array('slug' => 'beginner'),
            '중급' => array('slug' => 'intermediate'),
            '고급' => array('slug' => 'advanced')
        );
        
        foreach ($levels as $name => $args) {
            if (!term_exists($name, 'course_level')) {
                wp_insert_term($name, 'course_level', $args);
                $created['levels'][] = $name;
            }
        }
    }
    
    // 2. Create Packages
    $package_ids = array();
    if (isset($_POST['create_packages'])) {
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
        
        foreach ($packages as $package) {
            $post_id = wp_insert_post(array(
                'post_title' => $package['title'],
                'post_content' => $package['content'],
                'post_type' => 'coursepackage',
                'post_status' => 'publish'
            ));
            
            if ($post_id && !is_wp_error($post_id)) {
                foreach ($package['meta'] as $key => $value) {
                    update_post_meta($post_id, $key, $value);
                }
                $package_ids[] = $post_id;
                $created['packages'][] = $package['title'];
            }
        }
    }
    
    // 3. Create Courses
    $course_ids = array();
    if (isset($_POST['create_courses'])) {
        $courses = array(
            array(
                'title' => 'HTML/CSS 기초부터 실무까지',
                'content' => 'HTML과 CSS의 기초 개념부터 반응형 웹 디자인까지 완벽하게 마스터합니다.',
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
                'meta' => array(
                    '_course_duration' => 30,
                    '_access_mode' => 'free',
                    '_completion_score' => 75,
                    '_certificate_enabled' => '1'
                )
            )
        );
        
        foreach ($courses as $index => $course) {
            $post_id = wp_insert_post(array(
                'post_title' => $course['title'],
                'post_content' => $course['content'],
                'post_type' => 'coursesingle',
                'post_status' => 'publish'
            ));
            
            if ($post_id && !is_wp_error($post_id)) {
                foreach ($course['meta'] as $key => $value) {
                    update_post_meta($post_id, $key, $value);
                }
                
                // Link to package
                if (!empty($package_ids) && $index < 3) {
                    update_post_meta($post_id, '_package_id', $package_ids[0]);
                    $pkg_courses = get_post_meta($package_ids[0], '_package_courses', true) ?: array();
                    $pkg_courses[] = $post_id;
                    update_post_meta($package_ids[0], '_package_courses', $pkg_courses);
                }
                
                $course_ids[] = $post_id;
                $created['courses'][] = $course['title'];
            }
        }
    }
    
    // 4. Create Lessons
    if (isset($_POST['create_lessons']) && !empty($course_ids)) {
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
        
        $lesson_count = 0;
        foreach ($course_ids as $course_id) {
            foreach ($lesson_templates as $index => $lesson) {
                $lesson_id = wp_insert_post(array(
                    'post_title' => $lesson['title'],
                    'post_content' => '이것은 테스트 레슨 콘텐츠입니다.',
                    'post_type' => 'lesson',
                    'post_status' => 'publish',
                    'menu_order' => $index + 1
                ));
                
                if ($lesson_id && !is_wp_error($lesson_id)) {
                    update_post_meta($lesson_id, '_course_id', $course_id);
                    update_post_meta($lesson_id, '_lesson_type', $lesson['type']);
                    update_post_meta($lesson_id, '_lesson_duration', $lesson['duration']);
                    update_post_meta($lesson_id, '_completion_criteria', 'view');
                    
                    if ($lesson['type'] === 'video') {
                        update_post_meta($lesson_id, '_video_url', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ');
                    }
                    $lesson_count++;
                }
            }
        }
        $created['lessons'] = $lesson_count;
    }
    
    // 5. Create Students
    $student_ids = array();
    if (isset($_POST['create_students'])) {
        $students = array(
            array('username' => 'student1', 'email' => 'student1@test.com', 'name' => '김철수'),
            array('username' => 'student2', 'email' => 'student2@test.com', 'name' => '이영희'),
            array('username' => 'student3', 'email' => 'student3@test.com', 'name' => '박민수'),
            array('username' => 'student4', 'email' => 'student4@test.com', 'name' => '정지원'),
            array('username' => 'student5', 'email' => 'student5@test.com', 'name' => '최유진')
        );
        
        foreach ($students as $student) {
            if (!username_exists($student['username'])) {
                $user_id = wp_create_user($student['username'], 'password123', $student['email']);
                if ($user_id && !is_wp_error($user_id)) {
                    wp_update_user(array(
                        'ID' => $user_id,
                        'display_name' => $student['name']
                    ));
                    
                    $user = new WP_User($user_id);
                    $user->add_role('lectus_student');
                    
                    $student_ids[] = $user_id;
                    $created['students'][] = $student['username'];
                }
            }
        }
    }
    
    // 6. Create Enrollments
    if (isset($_POST['create_enrollments']) && !empty($student_ids) && !empty($course_ids)) {
        foreach ($student_ids as $student_id) {
            $num_courses = rand(1, min(3, count($course_ids)));
            $selected_courses = array_rand(array_flip($course_ids), $num_courses);
            if (!is_array($selected_courses)) {
                $selected_courses = array($selected_courses);
            }
            
            foreach ($selected_courses as $course_id) {
                Lectus_Enrollment::enroll($student_id, $course_id, 0, 90);
                
                // Add some progress
                $lessons = get_posts(array(
                    'post_type' => 'lesson',
                    'meta_key' => '_course_id',
                    'meta_value' => $course_id,
                    'posts_per_page' => -1
                ));
                
                $progress_count = rand(0, count($lessons));
                for ($i = 0; $i < $progress_count; $i++) {
                    if (isset($lessons[$i])) {
                        Lectus_Progress::complete_lesson($student_id, $course_id, $lessons[$i]->ID);
                    }
                }
            }
        }
        $created['enrollments'] = count($student_ids) * 2;
    }
    
    // Show success message
    if (!empty($created)) {
        echo '<div class="notice notice-success"><p>';
        echo '<strong>테스트 데이터가 성공적으로 생성되었습니다!</strong><br>';
        foreach ($created as $type => $items) {
            if (is_array($items)) {
                echo ucfirst($type) . ': ' . count($items) . '개<br>';
            } else {
                echo ucfirst($type) . ': ' . $items . '개<br>';
            }
        }
        echo '</p></div>';
    }
}