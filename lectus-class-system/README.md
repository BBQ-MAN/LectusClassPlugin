# Lectus Class System - WordPress LMS 플러그인

![Version](https://img.shields.io/badge/version-1.2.0-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.0+-green.svg)
![PHP](https://img.shields.io/badge/PHP-8.0+-purple.svg)
![WooCommerce](https://img.shields.io/badge/WooCommerce-6.0+-orange.svg)
![License](https://img.shields.io/badge/license-GPL--2.0+-red.svg)

WordPress용 전문 온라인 교육 관리 시스템(LMS) 플러그인입니다. 패키지강의 관리, WooCommerce 연동, 수강생 관리, Q&A 시스템, 수료증 발급 등 완전한 온라인 교육 솔루션을 제공합니다.

## 📋 목차

- [핵심 기능](#-핵심-기능)
- [설치 방법](#-설치-방법)
- [시스템 요구사항](#-시스템-요구사항)
- [사용 가이드](#-사용-가이드)
- [디렉토리 구조](#-디렉토리-구조)
- [개발 가이드](#-개발-가이드)
- [향후 개발 계획](#-향후-개발-계획)
- [라이선스](#-라이선스)

## 🎯 핵심 기능

### 📚 강의 관리 시스템
- **WooCommerce 통합 구조**: 상품 → 단과강의 → 레슨
- **유연한 콘텐츠 타입**: 텍스트, 동영상, 퀴즈, 과제
- **벌크 업로드**: CSV 파일로 레슨 일괄 생성
- **강의자료 관리**: 파일 업로드 및 외부 링크 지원
- **순차적/자유 학습 모드**: 강의별 진행 방식 설정
- **검색 가능한 강의 선택**: 카테고리별 그룹핑 지원

### 💳 WooCommerce 완전 통합
- **패키지 상품 시스템**: 단일/패키지 자동 구분
- **커스텀 상품 페이지**: 강의 전용 템플릿 제공
- **자동 수강 등록**: 결제 완료 시 즉시 접근 권한 부여
- **수강 기간 관리**: 상품별 개별 수강 기간 설정
- **환불 처리**: 환불 시 자동 접근 권한 제거
- **결제 게이트웨이**: 모든 WooCommerce 결제 방법 지원
- **HPOS 호환**: High-Performance Order Storage 완벽 지원

### 👥 수강생 관리
- **실시간 진도 추적**: 레슨별 상세 진도율 확인
- **수강 상태 관리**: 활성/만료/일시정지 상태 제어
- **개별 기간 연장**: 수강생별 맞춤 기간 조정
- **진도 초기화**: 필요시 진도 리셋 기능
- **대량 작업**: 다중 선택으로 일괄 처리

### 💬 Q&A 시스템
- **계층형 질문답변**: 강의/레슨별 구조화된 질문
- **강사 답변 표시**: 공식 답변 하이라이트
- **투표 시스템**: 유용한 질문/답변 추천
- **베스트 답변**: 최고의 답변 선정 기능
- **Rate Limiting**: 스팸 방지 기능

### 🏆 수료증 시스템
- **자동 발급**: 수료 기준 달성 시 자동 생성
- **PDF 다운로드**: 개인화된 PDF 수료증
- **검증 시스템**: 수료증 번호로 진위 확인
- **커스터마이징**: 템플릿 디자인 수정 가능
- **QR 코드**: 온라인 검증용 QR 코드 포함

### 📊 관리자 대시보드
- **통계 대시보드**: 수강생, 매출, 완료율 현황
- **진도 리포트**: 강의별 수강 현황 분석
- **수익 분석**: WooCommerce 연동 매출 리포트
- **활동 로그**: 상세한 시스템 활동 추적
- **Excel 내보내기**: 상세 데이터 다운로드

## 💻 설치 방법

### 자동 설치 (권장)
```
1. WordPress 관리자 → 플러그인 → 새로 추가
2. "Lectus Class System" 검색
3. 설치 후 활성화
```

### 수동 설치
```bash
# 1. 플러그인 다운로드
wget https://github.com/BBQ-MAN/LectusClassSystem/releases/latest/download/lectus-class-system.zip

# 2. 압축 해제 및 업로드
unzip lectus-class-system.zip
mv lectus-class-system/ /path/to/wordpress/wp-content/plugins/

# 3. WordPress 관리자에서 활성화
```

### 개발 환경 설치
```bash
# 저장소 클론
git clone https://github.com/BBQ-MAN/LectusClassSystem.git

# WordPress 플러그인 디렉토리로 이동
cd /path/to/wordpress/wp-content/plugins/

# 심볼릭 링크 생성
ln -s /path/to/LectusClassSystem/lectus-class-system lectus-class-system

# WP-CLI로 활성화
wp plugin activate lectus-class-system
```

## ⚙️ 시스템 요구사항

### 최소 요구사항
- WordPress 5.0+
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.0+
- WooCommerce 6.0+ (유료 강의 판매 시)

### 권장 사양
- WordPress 6.0+
- PHP 8.2+
- MySQL 8.0+ / MariaDB 10.5+
- SSL 인증서 (결제 처리용)
- 최소 256MB PHP 메모리

## 📖 사용 가이드

### 초기 설정

#### 1. 플러그인 활성화
```
플러그인 → Lectus Class System → 활성화
```

#### 2. 기본 설정
```
Lectus Class System → 설정
├── 일반 설정
│   ├── 기본 수강 기간: 365일
│   ├── 수료 기준: 80%
│   └── 시간대: Asia/Seoul
├── 이메일 설정
│   ├── 알림 활성화
│   └── 템플릿 커스터마이징
└── 개발 도구
    └── 테스트 데이터 생성
```

#### 3. 역할 및 권한
플러그인 활성화 시 자동 생성:
- `lectus_instructor`: 강사 역할
- `lectus_student`: 수강생 역할

### 강의 생성 워크플로우

#### 단계 1: WooCommerce 상품 생성
```
WooCommerce → 상품 → 새로 추가
├── 상품 정보 입력
├── 가격 설정
├── 단과강의 연결 (Lectus 강의 탭)
├── 수강 기간 설정
└── 게시 (자동으로 단일/패키지 구분)
```

#### 단계 2: 단과강의 생성
```
Lectus Class System → 단과강의 → 새로 추가
├── 강의 정보
├── 수강 기간 설정 (일 단위)
├── 패키지강의 연결
├── 접근 모드 (순차적/자유)
└── WooCommerce 상품 매핑
```

#### 단계 3: 레슨 추가
```
Lectus Class System → 레슨 → 새로 추가
├── 레슨 콘텐츠
├── 타입 선택 (텍스트/동영상/퀴즈/과제)
├── 소속 단과강의 선택
├── 예상 소요 시간
└── 강의자료 업로드
```

### 숏코드 사용법

#### 강의 목록 표시
```php
[lectus_courses type="coursesingle" columns="3" limit="12"]
```

#### 수강 신청 버튼
```php
[lectus_enroll_button course_id="123" text="지금 수강하기"]
```

#### 학생 대시보드
```php
[lectus_my_courses]
```

#### 수료증 목록
```php
[lectus_certificates]
```

#### Q&A 섹션
```php
[lectus_qa course_id="123" show_form="yes"]
```

## 📁 디렉토리 구조

```
lectus-class-system/
├── admin/                    # 관리자 기능
│   ├── class-lectus-admin.php
│   ├── class-lectus-admin-dashboard.php
│   ├── class-lectus-admin-reports.php
│   └── class-lectus-admin-settings.php
├── includes/                 # 핵심 클래스
│   ├── class-lectus-ajax.php
│   ├── class-lectus-enrollment.php
│   ├── class-lectus-materials.php
│   ├── class-lectus-post-types.php
│   ├── class-lectus-progress.php
│   ├── class-lectus-qa.php
│   ├── class-lectus-shortcodes.php
│   ├── class-lectus-student.php
│   ├── class-lectus-templates.php
│   └── class-lectus-woocommerce.php
├── assets/                   # 정적 리소스
│   ├── css/
│   └── js/
├── templates/                # 템플릿 파일
│   ├── certificate-default.php
│   ├── single-product-course.php
│   └── student-dashboard.php
├── languages/                # 번역 파일
├── tests/                    # 테스트 파일
│   ├── playwright/
│   └── phpunit/
└── lectus-class-system.php   # 메인 플러그인 파일
```

## 🔧 개발 가이드

### 액션 훅

```php
// 수강 등록 시
do_action('lectus_student_enrolled', $user_id, $course_id, $order_id);

// 레슨 완료 시
do_action('lectus_lesson_completed', $user_id, $course_id, $lesson_id);

// 수료증 발급 시
do_action('lectus_certificate_generated', $user_id, $course_id, $certificate_id);

// 질문 제출 시
do_action('lectus_question_submitted', $question_id, $course_id, $user_id);
```

### 필터 훅

```php
// 수강 기간 수정
apply_filters('lectus_enrollment_duration', $days, $course_id, $user_id);

// 수료증 데이터 커스터마이징
apply_filters('lectus_certificate_data', $data, $user_id, $course_id);

// Q&A 콘텐츠 필터링
apply_filters('lectus_qa_content_filter', $content);
```

### PHP 함수

#### 수강 관리
```php
// 수강 상태 확인
Lectus_Enrollment::is_enrolled($user_id, $course_id);

// 수강생 등록
Lectus_Enrollment::enroll($user_id, $course_id, $order_id, $duration);

// 수강 취소
Lectus_Enrollment::unenroll($user_id, $course_id);

// 수강 기간 연장
Lectus_Enrollment::extend_enrollment($user_id, $course_id, $days);
```

#### 진도 추적
```php
// 강의 진도 조회
Lectus_Progress::get_course_progress($user_id, $course_id);

// 레슨 완료 처리
Lectus_Progress::mark_lesson_complete($user_id, $course_id, $lesson_id);

// 진도 초기화
Lectus_Progress::reset_course_progress($user_id, $course_id);
```

#### 수료증 발급
```php
// 수료증 생성
Lectus_Certificate::generate($user_id, $course_id);

// 수료증 URL 조회
Lectus_Certificate::get_certificate_url($certificate_id);

// 수료증 검증
Lectus_Certificate::verify($certificate_number);
```

### AJAX 엔드포인트

```javascript
// 질문 제출
jQuery.ajax({
    url: lectus_ajax.ajax_url,
    type: 'POST',
    data: {
        action: 'lectus_submit_question',
        nonce: lectus_ajax.nonce,
        course_id: 123,
        title: '질문 제목',
        content: '질문 내용'
    }
});

// 답변 제출
jQuery.ajax({
    url: lectus_ajax.ajax_url,
    type: 'POST',
    data: {
        action: 'lectus_submit_answer',
        nonce: lectus_ajax.nonce,
        question_id: 456,
        content: '답변 내용'
    }
});

// 레슨 완료
jQuery.ajax({
    url: lectus_ajax.ajax_url,
    type: 'POST',
    data: {
        action: 'lectus_complete_lesson',
        nonce: lectus_ajax.nonce,
        lesson_id: 789
    }
});
```

### 코딩 표준

WordPress 코딩 표준을 따릅니다:
- [WordPress PHP 코딩 표준](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
- [WordPress JavaScript 코딩 표준](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/)
- [WordPress CSS 코딩 표준](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/)

## 🧪 테스트

### PHP 단위 테스트
```bash
# PHPUnit 설치
composer install

# 테스트 실행
./vendor/bin/phpunit

# 특정 테스트 실행
./vendor/bin/phpunit tests/test-enrollment.php
```

### Playwright E2E 테스트
```bash
# 테스트 디렉토리로 이동
cd tests

# 의존성 설치
npm install
npm run install-browsers

# 환경 설정
cp .env.example .env
# .env 파일에 WordPress 인증 정보 입력

# 테스트 실행
npm test

# 특정 테스트 실행
npm test woocommerce-integration.spec.js

# 헤드리스 모드로 실행
npm run test:headed

# 리포트 생성
npm run report
```

### 수동 테스트
```bash
# 테스트 데이터 생성
wp eval-file admin/lectus-test-data.php

# Q&A 디버깅
wp eval-file tests/test-qa-debug.php

# 강사 테스트
wp eval-file tests/test-instructor-qa.php
```

## 🚀 향후 개발 계획

### v1.3.0 (2025 Q1)
- [ ] 🎥 **라이브 스트리밍 연동**
  - Zoom/YouTube Live 통합
  - 실시간 채팅 기능
  - 자동 녹화 및 업로드
- [ ] 📱 **모바일 앱 지원**
  - React Native 앱 개발
  - 오프라인 학습 지원
  - 푸시 알림 기능
- [ ] 🌍 **다국어 지원 확대**
  - 영어, 일본어, 중국어 번역
  - 자동 번역 API 통합
  - 언어별 콘텐츠 관리
- [ ] 📊 **고급 분석 대시보드**
  - 학습 패턴 분석
  - 이탈율 추적
  - 예측 분석 기능

### v1.4.0 (2025 Q2)
- [ ] 🤖 **AI 기반 학습 추천**
  - 개인화된 학습 경로
  - 자동 콘텐츠 추천
  - ChatGPT 통합 튜터
- [ ] 💬 **실시간 채팅 시스템**
  - WebSocket 기반 채팅
  - 그룹 스터디 룸
  - 1:1 멘토링 채팅
- [ ] 🎮 **게이미피케이션 요소**
  - 포인트/배지 시스템
  - 리더보드
  - 성취도 보상
- [ ] 📧 **마케팅 자동화 통합**
  - Mailchimp 연동
  - 자동 이메일 캠페인
  - 세그먼트별 타겟팅

### v2.0.0 (2025 Q3)
- [ ] 🔗 **블록체인 수료증**
  - NFT 수료증 발급
  - 블록체인 검증
  - 디지털 자격증 지갑
- [ ] 👥 **소셜 러닝 기능**
  - 스터디 그룹 형성
  - 피어 리뷰 시스템
  - 협업 프로젝트
- [ ] 📚 **콘텐츠 마켓플레이스**
  - 강사 콘텐츠 판매
  - 수익 분배 시스템
  - 콘텐츠 큐레이션
- [ ] 🎯 **개인화된 학습 경로**
  - AI 기반 커리큘럼
  - 적응형 학습 속도
  - 개인별 목표 설정

### 장기 로드맵 (2026+)
- [ ] 🌐 **SaaS 버전 출시**
  - 멀티테넌트 아키텍처
  - 클라우드 호스팅
  - 구독 모델
- [ ] 🏢 **기업용 LMS 패키지**
  - SCORM 지원
  - SSO 통합
  - 기업 관리 도구
- [ ] 🔌 **주요 LMS 플랫폼 연동**
  - Moodle 통합
  - Canvas 연동
  - Blackboard 호환
- [ ] 📱 **PWA 지원**
  - 오프라인 우선 설계
  - 앱 스토어 배포
  - 네이티브 기능 지원

## 🤝 기여하기

프로젝트에 기여하고 싶으신가요? 환영합니다!

1. 프로젝트 포크 (Fork)
2. 기능 브랜치 생성 (`git checkout -b feature/AmazingFeature`)
3. 변경사항 커밋 (`git commit -m 'Add some AmazingFeature'`)
4. 브랜치에 푸시 (`git push origin feature/AmazingFeature`)
5. Pull Request 생성

자세한 내용은 [CONTRIBUTING.md](../CONTRIBUTING.md)를 참조하세요.

## 📞 지원

- **문서**: [개발자 가이드](DEVELOPER.md) | [관리자 가이드](docs/ADMIN-GUIDE.md) | [API 레퍼런스](docs/API-REFERENCE.md)
- **이슈**: [GitHub Issues](https://github.com/BBQ-MAN/LectusClassSystem/issues)
- **토론**: [GitHub Discussions](https://github.com/BBQ-MAN/LectusClassSystem/discussions)
- **이메일**: support@lectus-lms.com

## 🔧 문제 해결

### 일반적인 문제

#### 플러그인 활성화 실패
```bash
# PHP 버전 확인
php -v  # 8.0 이상이어야 함

# WordPress 버전 확인
wp core version  # 5.0 이상이어야 함

# 오류 로그 확인
tail -f wp-content/debug.log
```

#### WooCommerce 통합 문제
```bash
# WooCommerce 활성화 확인
wp plugin list --status=active

# HPOS 호환성 확인
# WooCommerce → 설정 → 고급 → 기능
# High-Performance Order Storage 활성화
```

#### Q&A 시스템 오류
```php
// 데이터베이스 테이블 재생성
wp eval "Lectus_QA::create_table();"

// AJAX 엔드포인트 확인
wp eval "print_r(has_action('wp_ajax_lectus_submit_question'));"
```

#### 수료증 생성 실패
```bash
# 쓰기 권한 확인
chmod 755 wp-content/uploads/lectus-certificates/

# GD 라이브러리 확인
php -m | grep gd
```

### 디버그 모드
```php
// wp-config.php에 추가
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', true);
```

## 📄 라이선스

이 프로젝트는 GPL v2.0 이상의 라이선스로 배포됩니다. 자세한 내용은 [LICENSE](LICENSE) 파일을 참조하세요.

## 👥 크레딧

- Lectus Team이 개발
- WordPress와 WooCommerce 기반
- Dashicons 아이콘 사용
- Playwright로 테스트

## 📈 변경 로그

최신 변경사항은 [CHANGELOG.md](../CHANGELOG.md)를 참조하세요.

### Version 1.2.0 (2025-08-19)
- WooCommerce 패키지 상품 시스템 완전 구현
- 커스텀 상품 페이지 템플릿 추가
- 검색 가능한 강의 선택 UI
- 강사 Q&A 관리 시스템 개선
- jQuery 의존성 제거 (순수 JavaScript)

### Version 1.1.0 (2025-08-13)
- 강의 섹션 관리 시스템
- 강사 센터 대시보드
- 수강생 관리 기능 강화

### Version 1.0.0 (2025-08-07)
- 초기 릴리스
- 핵심 LMS 기능
- WooCommerce 통합
- Q&A 시스템
- 수료증 생성

---

**전 세계 교육자와 학습자를 위해 ❤️로 만들었습니다.**