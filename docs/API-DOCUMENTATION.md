# Lectus Class System API 문서

## 📋 목차

- [REST API 엔드포인트](#rest-api-엔드포인트)
- [PHP 클래스 레퍼런스](#php-클래스-레퍼런스)
- [WordPress 훅](#wordpress-훅)
- [JavaScript API](#javascript-api)
- [AJAX 엔드포인트](#ajax-엔드포인트)
- [데이터베이스 스키마](#데이터베이스-스키마)

## REST API 엔드포인트

### 기본 URL
```
https://your-domain.com/wp-json/lectus/v1/
```

### 인증
모든 API 요청은 WordPress REST API 인증을 사용합니다.

```javascript
// 기본 인증 헤더
{
  'X-WP-Nonce': lectus_api.nonce,
  'Content-Type': 'application/json'
}
```

### 강의 관련 API

#### GET /courses
모든 강의 목록 조회

**Parameters:**
- `type` (string): coursesingle, coursepackage
- `status` (string): publish, draft, private
- `per_page` (int): 페이지당 항목 수 (기본: 10)
- `page` (int): 페이지 번호
- `orderby` (string): date, title, menu_order
- `order` (string): ASC, DESC

**Response:**
```json
{
  "courses": [
    {
      "id": 123,
      "title": "강의 제목",
      "description": "강의 설명",
      "thumbnail": "https://...",
      "price": 50000,
      "duration": 365,
      "lesson_count": 10,
      "enrolled_count": 150
    }
  ],
  "total": 100,
  "pages": 10
}
```

#### GET /courses/{id}
특정 강의 상세 정보 조회

**Response:**
```json
{
  "id": 123,
  "title": "강의 제목",
  "content": "상세 설명",
  "instructor": {
    "id": 5,
    "name": "강사명",
    "avatar": "https://..."
  },
  "curriculum": [
    {
      "id": 456,
      "title": "레슨 제목",
      "type": "video",
      "duration": 30,
      "completed": false
    }
  ],
  "materials": [],
  "requirements": [],
  "outcomes": []
}
```

#### POST /courses
새 강의 생성 (관리자 권한 필요)

**Body:**
```json
{
  "title": "새 강의",
  "content": "강의 설명",
  "type": "coursesingle",
  "price": 50000,
  "duration": 365,
  "access_mode": "sequential"
}
```

### 수강 관련 API

#### GET /enrollments
사용자의 수강 목록 조회

**Parameters:**
- `user_id` (int): 사용자 ID (선택, 기본: 현재 사용자)
- `status` (string): active, expired, completed

**Response:**
```json
{
  "enrollments": [
    {
      "id": 789,
      "course_id": 123,
      "course_title": "강의 제목",
      "enrolled_at": "2024-01-01",
      "expires_at": "2025-01-01",
      "progress": 75,
      "status": "active"
    }
  ]
}
```

#### POST /enrollments
수강 등록

**Body:**
```json
{
  "course_id": 123,
  "user_id": 456,
  "duration": 365
}
```

#### DELETE /enrollments/{id}
수강 취소

### 진도 관련 API

#### GET /progress/{course_id}
강의 진도 조회

**Response:**
```json
{
  "course_id": 123,
  "overall_progress": 75,
  "completed_lessons": 15,
  "total_lessons": 20,
  "lessons": [
    {
      "lesson_id": 456,
      "status": "completed",
      "completed_at": "2024-01-15"
    }
  ]
}
```

#### POST /progress/complete
레슨 완료 처리

**Body:**
```json
{
  "lesson_id": 456,
  "course_id": 123
}
```

### Q&A 관련 API

#### GET /qa/questions
질문 목록 조회

**Parameters:**
- `course_id` (int): 강의 ID
- `lesson_id` (int): 레슨 ID (선택)
- `status` (string): open, closed, answered

#### POST /qa/questions
새 질문 작성

**Body:**
```json
{
  "course_id": 123,
  "lesson_id": 456,
  "title": "질문 제목",
  "content": "질문 내용"
}
```

#### POST /qa/answers
답변 작성

**Body:**
```json
{
  "question_id": 789,
  "content": "답변 내용"
}
```

## PHP 클래스 레퍼런스

### Lectus_Enrollment

수강 관리 클래스

```php
class Lectus_Enrollment {
    /**
     * 수강 상태 확인
     * @param int $user_id 사용자 ID
     * @param int $course_id 강의 ID
     * @return bool 수강 여부
     */
    public static function is_enrolled($user_id, $course_id);
    
    /**
     * 수강 등록
     * @param int $user_id 사용자 ID
     * @param int $course_id 강의 ID
     * @param int $order_id 주문 ID (선택)
     * @param int $duration 수강 기간 (일)
     * @return int|WP_Error 등록 ID 또는 에러
     */
    public static function enroll($user_id, $course_id, $order_id = 0, $duration = 365);
    
    /**
     * 수강 취소
     * @param int $user_id 사용자 ID
     * @param int $course_id 강의 ID
     * @return bool 성공 여부
     */
    public static function unenroll($user_id, $course_id);
    
    /**
     * 수강 기간 연장
     * @param int $user_id 사용자 ID
     * @param int $course_id 강의 ID
     * @param int $days 연장 일수
     * @return bool 성공 여부
     */
    public static function extend_enrollment($user_id, $course_id, $days);
    
    /**
     * 사용자의 모든 수강 정보 조회
     * @param int $user_id 사용자 ID
     * @return array 수강 목록
     */
    public static function get_user_enrollments($user_id);
}
```

### Lectus_Progress

진도 관리 클래스

```php
class Lectus_Progress {
    /**
     * 강의 진도율 조회
     * @param int $user_id 사용자 ID
     * @param int $course_id 강의 ID
     * @return int 진도율 (0-100)
     */
    public static function get_course_progress($user_id, $course_id);
    
    /**
     * 레슨 완료 처리
     * @param int $user_id 사용자 ID
     * @param int $course_id 강의 ID
     * @param int $lesson_id 레슨 ID
     * @return bool 성공 여부
     */
    public static function mark_lesson_complete($user_id, $course_id, $lesson_id);
    
    /**
     * 레슨 완료 상태 확인
     * @param int $user_id 사용자 ID
     * @param int $lesson_id 레슨 ID
     * @return bool 완료 여부
     */
    public static function is_lesson_completed($user_id, $lesson_id);
    
    /**
     * 진도 초기화
     * @param int $user_id 사용자 ID
     * @param int $course_id 강의 ID
     * @return bool 성공 여부
     */
    public static function reset_course_progress($user_id, $course_id);
}
```

### Lectus_Certificate

수료증 관리 클래스

```php
class Lectus_Certificate {
    /**
     * 수료증 생성
     * @param int $user_id 사용자 ID
     * @param int $course_id 강의 ID
     * @return int|WP_Error 수료증 ID 또는 에러
     */
    public static function generate($user_id, $course_id);
    
    /**
     * 수료증 URL 조회
     * @param int $certificate_id 수료증 ID
     * @return string PDF URL
     */
    public static function get_certificate_url($certificate_id);
    
    /**
     * 수료증 검증
     * @param string $certificate_number 수료증 번호
     * @return array|false 수료증 정보 또는 false
     */
    public static function verify($certificate_number);
    
    /**
     * 수료 가능 여부 확인
     * @param int $user_id 사용자 ID
     * @param int $course_id 강의 ID
     * @return bool 수료 가능 여부
     */
    public static function can_generate($user_id, $course_id);
}
```

### Lectus_Materials

강의자료 관리 클래스

```php
class Lectus_Materials {
    /**
     * 강의자료 추가
     * @param array $data 자료 정보
     * @return int|WP_Error 자료 ID 또는 에러
     */
    public static function add_material($data);
    
    /**
     * 강의자료 목록 조회
     * @param int $course_id 강의 ID
     * @param int $lesson_id 레슨 ID (선택)
     * @return array 자료 목록
     */
    public static function get_materials($course_id, $lesson_id = 0);
    
    /**
     * 강의자료 삭제
     * @param int $material_id 자료 ID
     * @return bool 성공 여부
     */
    public static function delete_material($material_id);
    
    /**
     * 다운로드 권한 확인
     * @param int $user_id 사용자 ID
     * @param int $material_id 자료 ID
     * @return bool 권한 여부
     */
    public static function can_download($user_id, $material_id);
}
```

## WordPress 훅

### 액션 훅

#### 수강 관련
```php
// 수강 등록 시
do_action('lectus_student_enrolled', $user_id, $course_id, $order_id);

// 수강 취소 시
do_action('lectus_student_unenrolled', $user_id, $course_id);

// 수강 기간 연장 시
do_action('lectus_enrollment_extended', $user_id, $course_id, $days);
```

#### 진도 관련
```php
// 레슨 완료 시
do_action('lectus_lesson_completed', $user_id, $course_id, $lesson_id);

// 강의 완료 시
do_action('lectus_course_completed', $user_id, $course_id);

// 진도 초기화 시
do_action('lectus_progress_reset', $user_id, $course_id);
```

#### 수료증 관련
```php
// 수료증 생성 시
do_action('lectus_certificate_generated', $user_id, $course_id, $certificate_id);

// 수료증 다운로드 시
do_action('lectus_certificate_downloaded', $certificate_id);
```

#### Q&A 관련
```php
// 질문 작성 시
do_action('lectus_question_submitted', $question_id, $course_id, $user_id);

// 답변 작성 시
do_action('lectus_answer_submitted', $answer_id, $question_id, $user_id);

// 베스트 답변 선정 시
do_action('lectus_best_answer_selected', $answer_id, $question_id);
```

### 필터 훅

#### 설정 관련
```php
// 기본 수강 기간 수정
apply_filters('lectus_default_duration', 365);

// 수료 기준 수정
apply_filters('lectus_completion_threshold', 80);

// 파일 업로드 제한
apply_filters('lectus_max_upload_size', 52428800); // 50MB
```

#### 콘텐츠 관련
```php
// 강의 목록 쿼리 수정
apply_filters('lectus_courses_query', $args);

// 레슨 콘텐츠 필터링
apply_filters('lectus_lesson_content', $content, $lesson_id);

// Q&A 콘텐츠 필터링
apply_filters('lectus_qa_content', $content);
```

#### 권한 관련
```php
// 강의 접근 권한
apply_filters('lectus_can_access_course', $can_access, $user_id, $course_id);

// 레슨 접근 권한
apply_filters('lectus_can_access_lesson', $can_access, $user_id, $lesson_id);

// 자료 다운로드 권한
apply_filters('lectus_can_download_material', $can_download, $user_id, $material_id);
```

## JavaScript API

### 초기화
```javascript
// Lectus 객체 초기화
Lectus.init({
    ajax_url: lectus_ajax.ajax_url,
    nonce: lectus_ajax.nonce,
    user_id: lectus_ajax.user_id
});
```

### 강의 관련
```javascript
// 강의 목록 조회
Lectus.courses.list({
    type: 'coursesingle',
    page: 1,
    per_page: 12
}).then(response => {
    console.log(response.courses);
});

// 강의 상세 조회
Lectus.courses.get(courseId).then(course => {
    console.log(course);
});

// 수강 신청
Lectus.courses.enroll(courseId).then(result => {
    if (result.success) {
        console.log('수강 신청 완료');
    }
});
```

### 진도 관련
```javascript
// 진도 조회
Lectus.progress.get(courseId).then(progress => {
    console.log('진도율:', progress.percentage);
});

// 레슨 완료
Lectus.progress.complete(lessonId).then(result => {
    if (result.success) {
        console.log('레슨 완료');
    }
});

// 진도 업데이트 이벤트
Lectus.progress.on('update', (data) => {
    console.log('진도 업데이트:', data.percentage);
});
```

### Q&A 관련
```javascript
// 질문 작성
Lectus.qa.submitQuestion({
    course_id: 123,
    title: '질문 제목',
    content: '질문 내용'
}).then(question => {
    console.log('질문 ID:', question.id);
});

// 답변 작성
Lectus.qa.submitAnswer({
    question_id: 456,
    content: '답변 내용'
}).then(answer => {
    console.log('답변 ID:', answer.id);
});

// 투표
Lectus.qa.vote(itemId, 'up').then(result => {
    console.log('투표 완료');
});
```

## AJAX 엔드포인트

### 기본 구조
```javascript
jQuery.ajax({
    url: lectus_ajax.ajax_url,
    type: 'POST',
    data: {
        action: 'lectus_action_name',
        nonce: lectus_ajax.nonce,
        // 추가 파라미터
    },
    success: function(response) {
        if (response.success) {
            // 성공 처리
        } else {
            // 에러 처리
        }
    }
});
```

### 주요 액션

#### lectus_submit_question
질문 제출

**Parameters:**
- `course_id` (int): 강의 ID
- `lesson_id` (int): 레슨 ID (선택)
- `title` (string): 질문 제목
- `content` (string): 질문 내용

#### lectus_submit_answer
답변 제출

**Parameters:**
- `question_id` (int): 질문 ID
- `content` (string): 답변 내용

#### lectus_complete_lesson
레슨 완료 처리

**Parameters:**
- `lesson_id` (int): 레슨 ID

#### lectus_create_product
WooCommerce 상품 생성

**Parameters:**
- `course_id` (int): 강의 ID
- `course_type` (string): coursesingle 또는 coursepackage

#### lectus_generate_certificate
수료증 생성

**Parameters:**
- `course_id` (int): 강의 ID

## 데이터베이스 스키마

### wp_lectus_enrollment
수강 등록 테이블

```sql
CREATE TABLE wp_lectus_enrollment (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT(20) UNSIGNED NOT NULL,
    course_id BIGINT(20) UNSIGNED NOT NULL,
    order_id BIGINT(20) UNSIGNED DEFAULT 0,
    status VARCHAR(20) DEFAULT 'active',
    enrolled_at DATETIME NOT NULL,
    expires_at DATETIME DEFAULT NULL,
    completed_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    KEY user_course (user_id, course_id),
    KEY status (status),
    KEY expires_at (expires_at)
);
```

### wp_lectus_progress
진도 관리 테이블

```sql
CREATE TABLE wp_lectus_progress (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT(20) UNSIGNED NOT NULL,
    course_id BIGINT(20) UNSIGNED NOT NULL,
    lesson_id BIGINT(20) UNSIGNED NOT NULL,
    status VARCHAR(20) DEFAULT 'not_started',
    progress INT DEFAULT 0,
    started_at DATETIME DEFAULT NULL,
    completed_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY user_lesson (user_id, lesson_id),
    KEY user_course (user_id, course_id),
    KEY status (status)
);
```

### wp_lectus_materials
강의자료 테이블

```sql
CREATE TABLE wp_lectus_materials (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    course_id BIGINT(20) UNSIGNED NOT NULL,
    lesson_id BIGINT(20) UNSIGNED DEFAULT 0,
    material_type ENUM('file','link') DEFAULT 'file',
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_url VARCHAR(500),
    external_url VARCHAR(500),
    file_size BIGINT(20) DEFAULT 0,
    download_count INT DEFAULT 0,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY course_id (course_id),
    KEY lesson_id (lesson_id)
);
```

### wp_lectus_qa_questions
Q&A 질문 테이블

```sql
CREATE TABLE wp_lectus_qa_questions (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    course_id BIGINT(20) UNSIGNED NOT NULL,
    lesson_id BIGINT(20) UNSIGNED DEFAULT 0,
    user_id BIGINT(20) UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'open',
    votes INT DEFAULT 0,
    answer_count INT DEFAULT 0,
    best_answer_id BIGINT(20) DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    KEY course_id (course_id),
    KEY user_id (user_id),
    KEY status (status)
);
```

### wp_lectus_qa_answers
Q&A 답변 테이블

```sql
CREATE TABLE wp_lectus_qa_answers (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    question_id BIGINT(20) UNSIGNED NOT NULL,
    user_id BIGINT(20) UNSIGNED NOT NULL,
    content TEXT NOT NULL,
    is_instructor TINYINT(1) DEFAULT 0,
    is_best TINYINT(1) DEFAULT 0,
    votes INT DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    KEY question_id (question_id),
    KEY user_id (user_id)
);
```

### wp_lectus_certificates
수료증 테이블

```sql
CREATE TABLE wp_lectus_certificates (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT(20) UNSIGNED NOT NULL,
    course_id BIGINT(20) UNSIGNED NOT NULL,
    certificate_number VARCHAR(50) UNIQUE NOT NULL,
    certificate_url VARCHAR(500),
    completion_date DATE NOT NULL,
    generated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY course_id (course_id),
    KEY certificate_number (certificate_number)
);
```

## 에러 코드

### HTTP 상태 코드
- `200`: 성공
- `201`: 생성 완료
- `400`: 잘못된 요청
- `401`: 인증 실패
- `403`: 권한 없음
- `404`: 리소스를 찾을 수 없음
- `429`: 요청 제한 초과
- `500`: 서버 오류

### 커스텀 에러 코드
- `LECTUS_001`: 수강 등록 실패
- `LECTUS_002`: 이미 수강 중인 강의
- `LECTUS_003`: 수강 기간 만료
- `LECTUS_004`: 진도 업데이트 실패
- `LECTUS_005`: 수료증 생성 실패
- `LECTUS_006`: 수료 기준 미달
- `LECTUS_007`: 파일 업로드 실패
- `LECTUS_008`: 권한 부족
- `LECTUS_009`: Rate Limit 초과
- `LECTUS_010`: 데이터베이스 오류

## 보안

### 인증 및 권한
- WordPress nonce 검증 필수
- 역할 기반 접근 제어 (RBAC)
- 사용자별 권한 확인

### 데이터 검증
```php
// 입력 데이터 검증 예시
$course_id = absint($_POST['course_id']);
$title = sanitize_text_field($_POST['title']);
$content = wp_kses_post($_POST['content']);
```

### SQL 인젝션 방지
```php
// Prepared statement 사용
$wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}lectus_enrollment 
     WHERE user_id = %d AND course_id = %d",
    $user_id,
    $course_id
);
```

### XSS 방지
```php
// 출력 시 이스케이프
echo esc_html($title);
echo esc_attr($attribute);
echo esc_url($url);
```

## 성능 최적화

### 캐싱
```php
// 트랜지언트 API 사용
$cache_key = 'lectus_course_' . $course_id;
$course = get_transient($cache_key);

if (false === $course) {
    $course = // 데이터베이스 조회
    set_transient($cache_key, $course, HOUR_IN_SECONDS);
}
```

### 페이지네이션
```php
// 대량 데이터 페이지 처리
$args = array(
    'posts_per_page' => 20,
    'paged' => get_query_var('paged') ? get_query_var('paged') : 1
);
```

### 지연 로딩
```javascript
// 이미지 지연 로딩
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                imageObserver.unobserve(img);
            }
        });
    });
    images.forEach(img => imageObserver.observe(img));
});
```

## 예제 코드

### 수강 등록 예제
```php
// 수강 등록 처리
function handle_course_enrollment($user_id, $course_id) {
    // 이미 수강 중인지 확인
    if (Lectus_Enrollment::is_enrolled($user_id, $course_id)) {
        return new WP_Error('already_enrolled', '이미 수강 중인 강의입니다.');
    }
    
    // 수강 등록
    $enrollment_id = Lectus_Enrollment::enroll($user_id, $course_id);
    
    if (is_wp_error($enrollment_id)) {
        return $enrollment_id;
    }
    
    // 환영 이메일 발송
    do_action('lectus_send_welcome_email', $user_id, $course_id);
    
    return $enrollment_id;
}
```

### 진도 업데이트 예제
```javascript
// 비디오 시청 완료 시 진도 업데이트
document.getElementById('lesson-video').addEventListener('ended', function() {
    Lectus.progress.complete(lessonId).then(result => {
        if (result.success) {
            // UI 업데이트
            document.querySelector('.progress-bar').style.width = result.progress + '%';
            
            // 다음 레슨으로 이동
            if (result.next_lesson) {
                window.location.href = result.next_lesson_url;
            }
        }
    });
});
```

### 커스텀 수료증 템플릿
```php
// 수료증 템플릿 커스터마이징
add_filter('lectus_certificate_template', function($template, $user_id, $course_id) {
    $user = get_userdata($user_id);
    $course = get_post($course_id);
    
    $template = str_replace('{user_name}', $user->display_name, $template);
    $template = str_replace('{course_title}', $course->post_title, $template);
    $template = str_replace('{completion_date}', date('Y년 m월 d일'), $template);
    
    return $template;
}, 10, 3);
```

---

**마지막 업데이트**: 2024년 12월
**API 버전**: v1.0.0
**문서 버전**: 1.0.0