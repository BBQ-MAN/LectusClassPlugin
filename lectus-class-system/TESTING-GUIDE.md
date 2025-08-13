# 🧪 Lectus Class System 테스트 가이드

## 개요

이 가이드는 Lectus Class System 플러그인의 모든 기능을 체계적으로 테스트하기 위한 단계별 절차를 제공합니다.

## 📋 사전 준비사항

### 필수 요구사항
- WordPress 5.0+ 설치됨
- PHP 8.0+ 환경
- WooCommerce 플러그인 (결제 기능 테스트용, 선택사항)
- 관리자 계정 접근 권한

### 테스트 환경 설정
1. WordPress 관리자로 로그인
2. 플러그인 → 새로 추가하지 말고, 플러그인 폴더에 직접 업로드
3. 플러그인 → 설치된 플러그인에서 "Lectus Class System" 활성화

## 🚀 1단계: 기본 활성화 테스트

### 1.1 플러그인 활성화
```
1. WordPress 관리자 → 플러그인
2. "Lectus Class System" 찾기
3. "활성화" 클릭
4. 오류 메시지 없이 활성화되는지 확인
```

### 1.2 메뉴 생성 확인
활성화 후 다음 메뉴가 나타나는지 확인:
- **Lectus Class** (메인 메뉴)
  - 대시보드
  - 패키지강의 
  - 단과강의
  - 레슨
  - 수강생 관리
  - 수료증
  - Q&A 관리
  - 보고서
  - 설정
  - 테스트 데이터

### 1.3 데이터베이스 테이블 확인
```sql
-- phpMyAdmin 또는 데이터베이스 도구에서 다음 테이블들이 생성되었는지 확인
SHOW TABLES LIKE 'wp_lectus_%';

-- 예상 결과:
-- wp_lectus_enrollment
-- wp_lectus_progress
-- wp_lectus_certificates
-- wp_lectus_qa
-- wp_lectus_qa_votes
```

## 🔧 2단계: 자동 테스트 스크립트 실행

### 2.1 플러그인 구조 테스트
```
브라우저에서 접근:
http://your-site.com/wp-content/plugins/lectus-class-system/plugin-activation-test.php
```

### 2.2 전체 시스템 테스트 (WordPress 환경 필요)
```php
// wp-load.php를 로드한 후
require_once('wp-load.php');
include('wp-content/plugins/lectus-class-system/full-system-test.php');
```

## 📊 3단계: 테스트 데이터 생성

### 3.1 테스트 데이터 생성
```
1. Lectus Class → 테스트 데이터
2. "테스트 데이터 생성" 버튼 클릭
3. 생성 완료 메시지 확인
```

**생성되는 데이터:**
- ✅ 카테고리 5개 (프로그래밍, 디자인, 비즈니스, 마케팅, 언어)
- ✅ 패키지강의 3개
- ✅ 단과강의 6개  
- ✅ 각 강의당 레슨 10개
- ✅ 테스트 학생 계정 5개 (student1~student5, 비밀번호: password123)
- ✅ 수강 등록 및 진도 데이터

### 3.2 테스트 페이지 생성
```
1. Lectus Class → 테스트 데이터
2. "테스트 페이지 생성" 버튼 클릭
3. 페이지 생성 완료 확인
```

**생성되는 페이지:**
- `/courses/` - 강의 목록
- `/my-courses/` - 내 강의
- `/my-certificates/` - 내 수료증
- `/student-dashboard/` - 학습 대시보드
- `/certificate-verify/` - 수료증 확인

## 🎯 4단계: 쇼트코드 기능 테스트

### 4.1 기본 쇼트코드 테스트
각 생성된 페이지 방문하여 다음 확인:

**강의 목록 페이지 (`/courses/`)**
- ✅ `[lectus_courses]` 쇼트코드가 강의 목록을 표시하는가?
- ✅ 강의 카드가 올바르게 렌더링되는가?
- ✅ 강의 클릭 시 상세 페이지로 이동하는가?

**내 강의 페이지 (`/my-courses/`)**  
- ✅ `[lectus_my_courses]` 쇼트코드가 작동하는가?
- ✅ 로그인하지 않은 경우 로그인 메시지 표시하는가?
- ✅ 로그인 후 등록된 강의 목록이 표시되는가?

**학생 대시보드 (`/student-dashboard/`)**
- ✅ `[lectus_student_dashboard]` 쇼트코드가 작동하는가?
- ✅ 통계 정보가 올바르게 표시되는가?
- ✅ 진행 중인 강의 목록이 표시되는가?

### 4.2 Q&A 쇼트코드 테스트
```
임시 페이지 생성 후 다음 쇼트코드 테스트:
[lectus_qa course_id="1" show_form="yes"]
```

**확인사항:**
- ✅ 질문 작성 폼이 표시되는가?
- ✅ 기존 질문 목록이 표시되는가?
- ✅ 답변 작성이 가능한가?

## 👤 5단계: 사용자 시나리오 테스트

### 5.1 관리자 기능 테스트
```
관리자 계정으로 로그인 후:

1. 대시보드 확인
   - 통계 위젯이 표시되는가?
   - 최근 활동이 표시되는가?

2. 강의 관리
   - 새 강의 생성이 가능한가?
   - 강의 설정이 저장되는가?

3. 수강생 관리
   - 수강생 목록이 표시되는가?
   - 수강생 등록/해지가 가능한가?

4. 보고서
   - 강의별 통계가 표시되는가?
   - 수강생 통계가 표시되는가?
```

### 5.2 학생 계정 테스트  
```
테스트 계정으로 로그인 (student1 / password123):

1. 학생 대시보드 접근
   - /student-dashboard/ 페이지 방문
   - 수강중인 강의 확인
   - 진도 표시 확인

2. 강의 수강
   - 강의 페이지 접근
   - 레슨 클릭하여 수강
   - 레슨 완료 버튼 작동 확인

3. 진도 업데이트
   - AJAX를 통한 실시간 진도 업데이트
   - 완료된 레슨 체크 표시 확인
```

## 🔄 6단계: AJAX 기능 테스트

### 6.1 진도 관리 AJAX
```javascript
// 브라우저 개발자 도구에서 테스트
jQuery.post(ajaxurl, {
    action: 'lectus_update_lesson_progress',
    nonce: lectus_ajax.nonce,
    lesson_id: 1,
    progress: 50
});
```

### 6.2 Q&A AJAX 테스트
```javascript
// 질문 등록 테스트
jQuery.post(ajaxurl, {
    action: 'lectus_submit_question',
    nonce: lectus_ajax.nonce,
    course_id: 1,
    title: '테스트 질문',
    content: '테스트 내용입니다.'
});
```

## 🛒 7단계: WooCommerce 연동 테스트 (선택사항)

### 7.1 상품 연결 설정
```
1. WooCommerce → 상품 → 새로 추가
2. 상품 데이터 탭에서 "Lectus Course Options" 확인
3. 강의 연결 설정
4. 상품 저장
```

### 7.2 구매 플로우 테스트
```
1. 상품 페이지에서 구매
2. 결제 완료
3. 자동 수강 등록 확인
4. My Account → 내 강의에서 확인
```

## 🔍 8단계: 벌크 업로드 테스트

### 8.1 CSV 템플릿 다운로드
```
1. Lectus Class → 수강생 관리 또는 해당 페이지
2. "CSV 템플릿 다운로드" 버튼 클릭
3. 템플릿 파일 다운로드 확인
```

### 8.2 CSV 업로드 테스트
```
1. 템플릿에 테스트 데이터 입력
2. CSV 파일 업로드
3. 업로드 결과 확인
4. 생성된 데이터 검증
```

## ✅ 9단계: 최종 검증 체크리스트

### 9.1 핵심 기능 체크
- [ ] 플러그인 활성화 시 오류 없음
- [ ] 모든 데이터베이스 테이블 생성됨
- [ ] 관리자 메뉴 정상 표시
- [ ] 포스트 타입 등록됨 (coursepackage, coursesingle, lesson)
- [ ] 사용자 역할 생성됨 (lectus_student)
- [ ] 쇼트코드 모두 작동
- [ ] 테스트 데이터 생성 성공
- [ ] AJAX 핸들러 작동

### 9.2 사용자 경험 체크
- [ ] 학생 대시보드 정상 표시
- [ ] 강의 목록 및 상세 페이지 작동
- [ ] 진도 업데이트 실시간 반영
- [ ] 수료증 자동 발급
- [ ] Q&A 시스템 작동
- [ ] 벌크 업로드 기능 작동

### 9.3 관리자 기능 체크  
- [ ] 관리자 대시보드 통계 표시
- [ ] 수강생 관리 기능 작동
- [ ] 보고서 데이터 정확성
- [ ] 설정 저장/로드 정상
- [ ] Q&A 관리 페이지 작동

## 🚨 문제 해결

### 일반적인 문제들

**1. 플러그인 활성화 실패**
```php
// 오류 로그 확인
tail -f wp-content/debug.log

// 또는 WordPress 디버그 모드 활성화
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

**2. 데이터베이스 테이블 생성 실패**
```sql
-- 수동으로 테이블 생성 (필요시)
-- lectus-class-system.php의 create_tables() 메서드 참조
```

**3. AJAX 요청 실패**  
```javascript
// 브라우저 콘솔에서 네트워크 탭 확인
// nonce 값 및 액션명 검증
```

**4. 쇼트코드 출력 안됨**
```php
// 쇼트코드 등록 확인
global $shortcode_tags;
var_dump($shortcode_tags);
```

## 📞 지원

테스트 중 문제가 발생하면 다음 정보를 포함하여 문의:

1. WordPress 버전
2. PHP 버전  
3. 오류 메시지 (정확한 텍스트)
4. 오류 발생 단계
5. 브라우저 콘솔 오류 (개발자 도구)

---

**테스트 완료 후 이 체크리스트를 저장해 두세요! 🎯**