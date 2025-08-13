# Lectus Class System - WooCommerce Integration Tests

이 디렉토리에는 Lectus Class System의 WooCommerce 통합 기능을 테스트하는 Playwright 테스트들이 포함되어 있습니다.

## 📋 테스트 범위

### 1. 관리자 상품 생성 테스트
- ✅ 상품이 없는 강의에 대해 "상품 생성" 버튼 표시 확인
- ✅ 상품 생성 버튼 클릭 시 WooCommerce 상품 생성
- ✅ 기존 상품이 있는 강의에 대해 "상품 보기" 링크 표시
- ✅ 상품 생성 후 WooCommerce 관리자에서 상품 확인

### 2. 프론트엔드 구매 프로세스 테스트
- ✅ 강의 목록에서 구매 버튼 표시 확인
- ✅ 구매 버튼 클릭 시 상품 페이지로 리디렉션
- ✅ 무료 강의 수강 신청 버튼 동작
- ✅ 로그인/로그아웃 상태에 따른 버튼 표시 변경

### 3. 오류 처리 및 엣지 케이스 테스트
- ✅ 잘못된 강의 ID 처리
- ✅ 중복 상품 생성 방지
- ✅ 네트워크 오류 처리
- ✅ AJAX 요청 실패 시 복구

### 4. 접근성 및 사용자 경험 테스트
- ✅ ARIA 속성 및 키보드 네비게이션
- ✅ 로딩 상태 표시
- ✅ 모바일 반응형 동작

## 🛠 설정 및 실행

### 1. 사전 요구사항

```bash
# Node.js 16+ 설치 확인
node --version

# Playwright 설치
npm install
npm run install-browsers
```

### 2. 환경 설정

```bash
# 환경변수 파일 복사
cp .env.example .env

# .env 파일 편집하여 WordPress URL과 인증 정보 설정
WORDPRESS_URL=http://localhost:8000
ADMIN_USER=admin
ADMIN_PASS=password
TEST_USER=testuser
TEST_PASS=testpass
```

### 3. WordPress 환경 준비

테스트 실행 전에 다음 사항을 확인하세요:

- ✅ WordPress가 실행 중이고 접근 가능한 상태
- ✅ Lectus Class System 플러그인이 활성화됨
- ✅ WooCommerce 플러그인이 활성화됨
- ✅ 관리자 계정과 테스트 사용자 계정이 생성됨
- ✅ 최소 1개 이상의 강의(coursesingle 또는 coursepackage)가 생성됨

### 4. 테스트 실행

```bash
# 모든 테스트 실행
npm test

# 헤드리스 모드에서 실행 (브라우저 UI 표시)
npm run test:headed

# 디버그 모드 실행
npm run test:debug

# UI 모드 실행 (대화형)
npm run test:ui

# 특정 브라우저에서만 실행
npm run test:chromium
npm run test:firefox
npm run test:webkit

# 모바일 브라우저 테스트
npm run test:mobile

# 테스트 리포트 보기
npm run report
```

## 📊 테스트 결과

테스트 실행 후 다음과 같은 결과를 확인할 수 있습니다:

- **HTML 리포트**: `test-results/index.html`
- **JSON 결과**: `test-results.json`
- **스크린샷**: `test-results/` 디렉토리
- **비디오 녹화**: 실패한 테스트의 경우 자동 녹화

## 🔧 테스트 케이스 상세

### Admin Product Creation Tests

```javascript
test('Should display "상품 생성" button for courses without products')
test('Should create WooCommerce product when button is clicked')
test('Should show "상품 보기" link for courses with existing products')
```

### Frontend Purchase Process Tests

```javascript
test('Should display purchase buttons on course list')
test('Should handle purchase button click and redirect to product page')
test('Should handle free enrollment button click')
```

### Error Handling Tests

```javascript
test('Should handle invalid course ID gracefully')
test('Should prevent duplicate product creation')
test('Should handle network errors gracefully')
```

### Accessibility Tests

```javascript
test('Should have proper ARIA attributes and keyboard navigation')
test('Should show loading states during AJAX requests')
```

## 🐛 디버깅 가이드

### 테스트 실패 시 확인사항

1. **WordPress 연결 확인**
   ```bash
   curl -I http://localhost:8000
   ```

2. **관리자 로그인 확인**
   - WordPress 관리자 페이지에 수동 로그인 가능한지 확인

3. **플러그인 활성화 확인**
   - Lectus Class System 플러그인 활성화 상태
   - WooCommerce 플러그인 활성화 상태

4. **강의 데이터 확인**
   - 최소 1개 이상의 강의가 생성되어 있는지 확인
   - 강의에 필요한 메타 데이터(_course_price 등)가 설정되어 있는지 확인

### 일반적인 문제 해결

**1. 브라우저 설치 오류**
```bash
npm run install-browsers
```

**2. 타임아웃 오류**
- `.env` 파일에서 `TEST_TIMEOUT` 값 증가
- WordPress 서버 성능 확인

**3. AJAX 요청 실패**
- WordPress nonce 검증 확인
- 플러그인 JavaScript 파일 로드 확인

## 📝 테스트 작성 가이드

새로운 테스트를 추가할 때 다음 구조를 따르세요:

```javascript
test.describe('Feature Group', () => {
  test.beforeEach(async ({ page }) => {
    // 각 테스트 전 설정
  });

  test('Should do something specific', async ({ page }) => {
    // 1. Arrange: 테스트 환경 준비
    await page.goto('/target-page');
    
    // 2. Act: 테스트 액션 수행
    await page.click('.target-button');
    
    // 3. Assert: 결과 검증
    await expect(page.locator('.result')).toBeVisible();
  });
});
```

## 🚀 CI/CD 통합

GitHub Actions, Jenkins 등에서 사용할 수 있는 CI 명령어:

```bash
# CI 환경에서 실행
CI=true npm test

# Docker 환경에서 실행
docker run --rm -v $(pwd):/work -w /work mcr.microsoft.com/playwright:v1.40.0-focal npm test
```

## 📞 지원 및 문의

테스트 관련 문의사항이 있으시면 다음을 참고하세요:

- [Playwright 공식 문서](https://playwright.dev/)
- [WordPress 테스팅 가이드](https://make.wordpress.org/core/handbook/testing/)
- [WooCommerce 개발자 문서](https://woocommerce.github.io/code-reference/)

---

**마지막 업데이트**: 2024년 현재  
**테스트 프레임워크**: Playwright v1.40.0  
**호환성**: WordPress 5.0+, WooCommerce 6.0+