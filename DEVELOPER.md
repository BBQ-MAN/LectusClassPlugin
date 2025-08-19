# Lectus Class System - Developer Guide

## 📚 개발 환경 설정

### 시스템 요구사항
- **WordPress**: 5.0+ (6.0+ 권장)
- **PHP**: 8.0+ (8.2+ 권장) 
- **MySQL**: 5.7+ / MariaDB 10.0+
- **WooCommerce**: 6.0+ (유료 강의 판매 시)
- **Node.js**: 16+ (개발 도구용)
- **Composer**: 2.0+ (PHP 의존성 관리)

### 개발 환경 구축

#### 1. Local Development with Docker
```bash
# Docker Compose로 개발 환경 시작
docker-compose up -d

# 컨테이너 접속
docker exec -it lectus-wordpress bash

# WP-CLI 사용
wp plugin activate lectus-class-system
```

#### 2. Manual Setup
```bash
# 1. 저장소 클론
git clone https://github.com/BBQ-MAN/LectusClassSystem.git

# 2. WordPress 플러그인 디렉토리로 이동
cd /path/to/wordpress/wp-content/plugins/

# 3. 심볼릭 링크 생성
ln -s /path/to/LectusClassSystem/lectus-class-system lectus-class-system

# 4. 의존성 설치
cd lectus-class-system
composer install
npm install

# 5. 플러그인 활성화
wp plugin activate lectus-class-system
```

### 디버그 모드 설정
```php
// wp-config.php에 추가
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', true);
define('SAVEQUERIES', true);
```

## 🏗️ 아키텍처 개요

### 플러그인 구조
```
lectus-class-system/
├── includes/            # 핵심 클래스
│   ├── class-lectus-*.php
│   └── traits/         # 재사용 가능한 트레이트
├── admin/              # 관리자 기능
├── public/             # 프론트엔드 기능
├── templates/          # 템플릿 파일
├── assets/             # CSS, JS, 이미지
├── languages/          # 번역 파일
└── tests/              # 테스트 코드
```

### 핵심 클래스 구조
```php
// 메인 플러그인 클래스
class Lectus_Class_System {
    // 싱글톤 패턴 사용
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}

// 기능별 클래스 분리
├── Lectus_Post_Types      # 커스텀 포스트 타입
├── Lectus_Enrollment      # 수강 등록 관리
├── Lectus_Progress        # 진도 추적
├── Lectus_Certificate     # 수료증 발급
├── Lectus_WooCommerce     # WooCommerce 통합
├── Lectus_Ajax           # AJAX 핸들러
├── Lectus_QA             # Q&A 시스템
└── Lectus_Materials      # 강의자료 관리
```

## 💻 코딩 표준

### PHP 코딩 표준
```php
// WordPress 코딩 표준 준수
// PSR-12 권장사항 참고

// 1. 네이밍 컨벤션
class Lectus_Class_Name {}      // 클래스명: Pascal_Case
function lectus_function_name() {} // 함수명: snake_case
$variable_name = '';              // 변수명: snake_case
const CONSTANT_NAME = '';         // 상수: UPPER_CASE

// 2. 들여쓰기: 탭 사용
if (condition) {
	// 탭으로 들여쓰기
	do_something();
}

// 3. 보안: 모든 입력 검증
$value = sanitize_text_field($_POST['field']);
$id = absint($_GET['id']);

// 4. 국제화: 모든 텍스트 번역 가능
__('Text', 'lectus-class-system');
_e('Text', 'lectus-class-system');

// 5. 데이터베이스: 준비된 쿼리 사용
$wpdb->prepare("SELECT * FROM {$wpdb->prefix}table WHERE id = %d", $id);
```

### JavaScript 코딩 표준
```javascript
// ES6+ 문법 사용
// jQuery 의존성 최소화 (순수 JavaScript 선호)

// 1. 변수 선언
const constantValue = 'value';  // 상수
let variableName = '';          // 변수

// 2. 함수 선언
const functionName = (param) => {
    // Arrow function 선호
};

// 3. 이벤트 처리
document.addEventListener('DOMContentLoaded', () => {
    // DOM 준비 후 실행
});

// 4. AJAX 요청
fetch(lectus_ajax.ajaxurl, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: new URLSearchParams({
        action: 'lectus_action',
        nonce: lectus_ajax.nonce,
        data: value
    })
})
.then(response => response.json())
.then(data => {
    // 처리
});
```

### CSS/SCSS 코딩 표준
```scss
// BEM 방법론 사용
.lectus-block {}
.lectus-block__element {}
.lectus-block--modifier {}

// 네스팅 최소화 (최대 3단계)
.lectus-container {
    .header {
        .title {
            // 최대 여기까지
        }
    }
}

// 변수 사용
:root {
    --lectus-primary-color: #007cba;
    --lectus-spacing: 1rem;
}
```

## 🔌 API 레퍼런스

### 수강 관리 API
```php
// 수강 등록
Lectus_Enrollment::enroll($user_id, $course_id, $order_id, $duration);

// 수강 확인
Lectus_Enrollment::is_enrolled($user_id, $course_id);

// 수강 취소
Lectus_Enrollment::unenroll($user_id, $course_id);

// 수강 기간 연장
Lectus_Enrollment::extend_enrollment($user_id, $course_id, $days);

// 수강 목록 조회
Lectus_Enrollment::get_user_enrollments($user_id);
```

### 진도 관리 API
```php
// 레슨 완료
Lectus_Progress::mark_lesson_complete($user_id, $course_id, $lesson_id);

// 진도율 조회
Lectus_Progress::get_course_progress($user_id, $course_id);

// 진도 초기화
Lectus_Progress::reset_course_progress($user_id, $course_id);

// 완료한 레슨 목록
Lectus_Progress::get_completed_lessons($user_id, $course_id);
```

### WooCommerce 통합 API
```php
// 상품-강의 연결
update_post_meta($product_id, '_lectus_course_ids', $course_ids);

// 수강 기간 설정
update_post_meta($product_id, '_lectusclass_access_duration', $days);

// 패키지 타입 자동 감지
$is_package = count($course_ids) > 1;
```

### AJAX 엔드포인트
```javascript
// 사용 가능한 AJAX 액션
const actions = {
    'lectus_update_lesson_progress': '진도 업데이트',
    'lectus_complete_lesson': '레슨 완료',
    'lectus_enroll_student': '수강 등록',
    'lectus_unenroll_student': '수강 취소',
    'lectus_submit_question': '질문 등록',
    'lectus_submit_answer': '답변 등록',
    'lectus_vote_qa': '투표',
    'lectus_generate_certificate': '수료증 생성'
};
```

## 🧪 테스트

### PHPUnit 테스트
```bash
# 테스트 환경 설정
bash bin/install-wp-tests.sh wordpress_test root '' localhost latest

# 전체 테스트 실행
composer test

# 특정 테스트 실행
./vendor/bin/phpunit tests/test-enrollment.php

# 커버리지 리포트 생성
./vendor/bin/phpunit --coverage-html coverage
```

### Playwright E2E 테스트
```bash
# 의존성 설치
npm install
npx playwright install

# 테스트 실행
npm test

# UI 모드로 실행
npm run test:ui

# 특정 브라우저로 테스트
npm run test:chrome
npm run test:firefox
```

### 테스트 작성 예제
```php
// PHPUnit 테스트
class Test_Lectus_Enrollment extends WP_UnitTestCase {
    public function test_enroll_student() {
        $user_id = $this->factory->user->create();
        $course_id = $this->factory->post->create([
            'post_type' => 'coursesingle'
        ]);
        
        $result = Lectus_Enrollment::enroll($user_id, $course_id, 0, 365);
        
        $this->assertTrue($result);
        $this->assertTrue(Lectus_Enrollment::is_enrolled($user_id, $course_id));
    }
}
```

```javascript
// Playwright E2E 테스트
test('사용자가 강의를 구매하고 수강할 수 있다', async ({ page }) => {
    await page.goto('/shop');
    await page.click('[data-product-id="123"]');
    await page.click('.single_add_to_cart_button');
    await page.goto('/checkout');
    // ... 결제 프로세스
    await expect(page.locator('.course-access')).toBeVisible();
});
```

## 🔧 빌드 및 배포

### 개발 빌드
```bash
# 개발 모드로 실행 (watch)
npm run dev

# SCSS 컴파일
npm run build:css

# JavaScript 번들링
npm run build:js

# 전체 빌드
npm run build
```

### 프로덕션 빌드
```bash
# 프로덕션 빌드
npm run build:prod

# 배포 패키지 생성
npm run package

# 버전 업데이트
npm version patch|minor|major
```

### 배포 체크리스트
- [ ] 모든 테스트 통과
- [ ] 코드 리뷰 완료
- [ ] CHANGELOG.md 업데이트
- [ ] 버전 번호 업데이트
- [ ] 문서 업데이트
- [ ] 호환성 테스트
- [ ] 성능 테스트
- [ ] 보안 검사

## 🐛 디버깅

### 로그 확인
```php
// 커스텀 로그 작성
error_log('Debug: ' . print_r($variable, true));

// Lectus 로거 사용
Lectus_Logger::log('error', 'Error message', ['context' => $data]);

// 로그 파일 위치
// wp-content/debug.log
// wp-content/uploads/lectus-logs/
```

### 데이터베이스 쿼리 디버깅
```php
// 쿼리 로깅 활성화 (wp-config.php)
define('SAVEQUERIES', true);

// 실행된 쿼리 확인
global $wpdb;
print_r($wpdb->queries);
```

### JavaScript 디버깅
```javascript
// 콘솔 로깅
console.log('Debug:', variable);
console.table(arrayData);
console.time('operation');
// ... 코드 실행
console.timeEnd('operation');

// 브레이크포인트
debugger;

// 네트워크 모니터링
// Chrome DevTools > Network 탭
```

## 🤝 기여 가이드라인

### 브랜치 전략
```bash
main          # 안정된 프로덕션 코드
├── develop   # 개발 통합 브랜치
├── feature/* # 새 기능 개발
├── bugfix/*  # 버그 수정
└── hotfix/*  # 긴급 수정
```

### 커밋 메시지 형식
```
type(scope): subject

body

footer
```

**Type**:
- `feat`: 새로운 기능
- `fix`: 버그 수정
- `docs`: 문서 수정
- `style`: 코드 포맷팅
- `refactor`: 리팩토링
- `test`: 테스트 추가
- `chore`: 빌드, 설정 변경

**예제**:
```
feat(enrollment): Add bulk enrollment feature

- Implement CSV upload for bulk student enrollment
- Add progress indicator for large batches
- Include validation and error reporting

Closes #123
```

### Pull Request 프로세스
1. Feature 브랜치 생성
2. 코드 작성 및 테스트
3. 커밋 및 푸시
4. Pull Request 생성
5. 코드 리뷰
6. 테스트 통과 확인
7. 머지

## 📚 추가 리소스

### 공식 문서
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WooCommerce Developer Docs](https://woocommerce.github.io/code-reference/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)

### 유용한 도구
- [WP-CLI](https://wp-cli.org/) - WordPress 명령줄 인터페이스
- [Query Monitor](https://wordpress.org/plugins/query-monitor/) - 디버깅 플러그인
- [Debug Bar](https://wordpress.org/plugins/debug-bar/) - 디버그 정보 표시
- [Postman](https://www.postman.com/) - API 테스트

### 커뮤니티
- [GitHub Issues](https://github.com/BBQ-MAN/LectusClassSystem/issues)
- [GitHub Discussions](https://github.com/BBQ-MAN/LectusClassSystem/discussions)
- [WordPress 개발자 포럼](https://wordpress.org/support/forum/wp-advanced/)

## 📝 라이선스

이 프로젝트는 GPL v2.0 이상의 라이선스로 배포됩니다.

---

**Last Updated**: 2025-08-19  
**Version**: 1.2.0