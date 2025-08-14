# Lectus Academy - WordPress LMS 테마

![Version](https://img.shields.io/badge/version-2.0.0-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.0+-green.svg)
![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)
![License](https://img.shields.io/badge/license-GPL--2.0+-red.svg)
![Responsive](https://img.shields.io/badge/responsive-yes-green.svg)

Lectus Class System LMS 플러그인을 위해 특별히 설계된 현대적이고 반응형 WordPress 테마입니다. Inflearn 스타일의 깔끔한 디자인과 직관적인 UI/UX를 제공합니다.

## 📋 목차

- [핵심 특징](#-핵심-특징)
- [설치 방법](#-설치-방법)
- [시스템 요구사항](#-시스템-요구사항)
- [테마 구조](#-테마-구조)
- [커스터마이징](#-커스터마이징)
- [템플릿 파일](#-템플릿-파일)
- [개발 가이드](#-개발-가이드)
- [향후 개발 계획](#-향후-개발-계획)
- [라이선스](#-라이선스)

## 🎨 핵심 특징

### 디자인 & UI/UX
- **Inflearn 스타일 디자인**: 깔끔하고 모던한 한국형 교육 플랫폼 UI
- **완전 반응형**: 모바일, 태블릿, 데스크톱 완벽 지원
- **다크 모드**: 눈의 피로를 줄이는 다크 테마 지원
- **맞춤형 컬러 스킴**: CSS 변수를 통한 쉬운 색상 커스터마이징
- **스무스 애니메이션**: 부드러운 전환 효과와 인터랙션

### LMS 최적화
- **Lectus Class System 완벽 통합**: 플러그인과 100% 호환
- **강의 전용 레이아웃**: 강의 목록, 상세 페이지 최적화
- **학습 진도 표시**: 시각적인 진도 바와 완료 상태
- **강사 프로필**: 전문적인 강사 소개 페이지
- **수강생 대시보드**: 개인화된 학습 관리 인터페이스

### 기능 요소
- **헤더 메가 메뉴**: 카테고리별 강의 네비게이션
- **고급 검색**: 필터링과 정렬이 가능한 강의 검색
- **소셜 공유**: 강의 공유 버튼 내장
- **리뷰 시스템**: 별점과 리뷰 표시
- **위시리스트**: 관심 강의 저장 기능

### 성능 최적화
- **빠른 로딩**: 최적화된 CSS/JS 로딩
- **지연 로딩**: 이미지와 비디오 지연 로딩
- **캐싱 최적화**: 브라우저 캐싱 활용
- **CDN 지원**: 정적 자원 CDN 배포 지원
- **SEO 최적화**: 구조화된 데이터와 메타 태그

## 💻 설치 방법

### 자동 설치 (권장)
```
1. WordPress 관리자 → 외모 → 테마 → 새로 추가
2. "Lectus Academy" 검색
3. 설치 후 활성화
```

### 수동 설치
```bash
# 1. 테마 다운로드
wget https://github.com/BBQ-MAN/LectusClassSystem/releases/latest/download/lectus-academy-theme.zip

# 2. 압축 해제 및 업로드
unzip lectus-academy-theme.zip
mv lectus-academy-theme/ /path/to/wordpress/wp-content/themes/

# 3. WordPress 관리자에서 활성화
외모 → 테마 → Lectus Academy → 활성화
```

### 개발 환경 설치
```bash
# 저장소 클론
git clone https://github.com/BBQ-MAN/LectusClassSystem.git

# 테마 디렉토리로 이동
cd /path/to/wordpress/wp-content/themes/

# 심볼릭 링크 생성
ln -s /path/to/LectusClassSystem/lectus-academy-theme lectus-academy-theme

# WP-CLI로 활성화
wp theme activate lectus-academy-theme
```

## ⚙️ 시스템 요구사항

### 최소 요구사항
- WordPress 5.0+
- PHP 7.4+
- Lectus Class System 플러그인 1.0+
- 모던 브라우저 (Chrome 90+, Firefox 88+, Safari 14+, Edge 90+)

### 권장 사양
- WordPress 6.0+
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.2+
- SSL 인증서 활성화

## 📁 테마 구조

```
lectus-academy-theme/
├── assets/                      # 정적 리소스
│   ├── css/
│   │   ├── style.css           # 메인 스타일시트
│   │   └── responsive.css      # 반응형 스타일
│   ├── js/
│   │   ├── main.js             # 메인 JavaScript
│   │   └── header.js           # 헤더 기능
│   ├── images/                 # 테마 이미지
│   └── fonts/                  # 웹폰트
├── inc/                        # PHP 포함 파일
│   ├── customizer.php          # 테마 커스터마이저
│   ├── custom-functions.php    # 커스텀 함수
│   ├── template-tags.php       # 템플릿 태그
│   └── widgets.php             # 커스텀 위젯
├── template-parts/             # 템플릿 파트
│   └── content-course-card.php # 강의 카드 템플릿
├── languages/                  # 번역 파일
├── style.css                   # 테마 정보 및 기본 스타일
├── functions.php               # 테마 함수
├── index.php                   # 메인 템플릿
├── header.php                  # 헤더 템플릿
├── footer.php                  # 푸터 템플릿
├── front-page.php              # 홈페이지 템플릿
├── page.php                    # 페이지 템플릿
├── single-coursesingle.php     # 단과강의 상세 템플릿
├── single-lesson.php           # 레슨 상세 템플릿
├── archive-coursesingle.php    # 강의 목록 템플릿
├── page-student-dashboard.php  # 수강생 대시보드
├── sidebar.php                 # 기본 사이드바
└── sidebar-course.php          # 강의 전용 사이드바
```

## 🎨 커스터마이징

### CSS 변수 활용
```css
/* style.css의 CSS 변수 수정 */
:root {
    --primary-color: #30b2e5;      /* 메인 색상 */
    --primary-dark: #2090c0;       /* 메인 다크 색상 */
    --secondary-color: #524fa1;     /* 보조 색상 */
    --text-primary: #1e1e1e;       /* 주 텍스트 색상 */
    --text-secondary: #495057;     /* 보조 텍스트 색상 */
    --bg-primary: #ffffff;         /* 주 배경색 */
    --bg-secondary: #f8f9fa;       /* 보조 배경색 */
    --border-color: #dee2e6;       /* 테두리 색상 */
}
```

### 테마 커스터마이저
```
외모 → 사용자 정의하기
├── 사이트 아이덴티티
│   ├── 로고
│   ├── 사이트 제목
│   └── 태그라인
├── 색상
│   ├── 주 색상
│   ├── 보조 색상
│   └── 배경색
├── 헤더 설정
│   ├── 헤더 레이아웃
│   ├── 메뉴 스타일
│   └── 검색 바 표시
├── 푸터 설정
│   ├── 푸터 위젯 영역
│   ├── 저작권 텍스트
│   └── 소셜 링크
└── 강의 설정
    ├── 목록 레이아웃
    ├── 카드 스타일
    └── 진도 표시 방식
```

### 자식 테마 생성
```php
// 자식 테마 style.css
/*
Theme Name: Lectus Academy Child
Template: lectus-academy-theme
*/

// 자식 테마 functions.php
<?php
add_action('wp_enqueue_scripts', 'lectus_child_styles');
function lectus_child_styles() {
    wp_enqueue_style('parent-style', 
        get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style',
        get_stylesheet_uri(),
        array('parent-style')
    );
}
```

## 📄 템플릿 파일

### 홈페이지 (front-page.php)
- 히어로 섹션
- 추천 강의 슬라이더
- 카테고리별 강의 목록
- 강사 소개
- 수강 후기

### 강의 목록 (archive-coursesingle.php)
- 필터링 사이드바
- 그리드/리스트 뷰 전환
- 정렬 옵션
- 페이지네이션

### 강의 상세 (single-coursesingle.php)
- 강의 소개 비디오
- 커리큘럼 목록
- 강사 정보
- 수강 후기
- 관련 강의

### 레슨 페이지 (single-lesson.php)
- 비디오 플레이어
- 레슨 콘텐츠
- 진도 체크
- 이전/다음 레슨 네비게이션
- Q&A 섹션

### 수강생 대시보드 (page-student-dashboard.php)
- 수강 중인 강의
- 학습 진도
- 수료증
- 프로필 관리

## 🔧 개발 가이드

### 액션 훅
```php
// 헤더 커스터마이징
add_action('lectus_academy_header', 'my_custom_header');

// 푸터 커스터마이징
add_action('lectus_academy_footer', 'my_custom_footer');

// 강의 카드 커스터마이징
add_action('lectus_academy_course_card', 'my_course_card');
```

### 필터 훅
```php
// 강의 목록 개수 변경
add_filter('lectus_academy_courses_per_page', function() {
    return 12;
});

// 헤더 클래스 추가
add_filter('lectus_academy_header_class', function($classes) {
    $classes[] = 'my-custom-class';
    return $classes;
});
```

### JavaScript API
```javascript
// 테마 초기화
LectusAcademy.init();

// 헤더 스크롤 이벤트
LectusAcademy.header.onScroll(function() {
    // 스크롤 시 실행할 코드
});

// 강의 카드 호버 효과
LectusAcademy.course.hover('.course-card', {
    scale: 1.05,
    shadow: true
});
```

### AJAX 엔드포인트
```javascript
// 강의 필터링
jQuery.ajax({
    url: lectus_academy.ajax_url,
    type: 'POST',
    data: {
        action: 'filter_courses',
        category: 'programming',
        level: 'beginner'
    }
});

// 위시리스트 추가
jQuery.ajax({
    url: lectus_academy.ajax_url,
    type: 'POST',
    data: {
        action: 'add_to_wishlist',
        course_id: 123
    }
});
```

## 🚀 향후 개발 계획

### v2.1.0 (2025 Q1)
- [ ] 🌙 **다크 모드 자동 전환**
  - 시스템 설정 연동
  - 시간대별 자동 전환
  - 사용자 선호 저장
- [ ] 📱 **PWA 지원**
  - 오프라인 캐싱
  - 홈 화면 추가
  - 푸시 알림
- [ ] 🎨 **추가 레이아웃 옵션**
  - 매거진 스타일
  - 미니멀 스타일
  - 기업용 레이아웃

### v2.2.0 (2025 Q2)
- [ ] ♿ **접근성 개선**
  - WCAG 2.1 AAA 준수
  - 스크린 리더 최적화
  - 키보드 네비게이션 강화
- [ ] 🚀 **성능 최적화**
  - Critical CSS 인라인
  - 리소스 힌트 구현
  - 이미지 최적화 자동화
- [ ] 🔌 **플러그인 통합**
  - Elementor 지원
  - Gutenberg 블록
  - WPBakery 호환

### v3.0.0 (2025 Q3)
- [ ] 🎯 **AI 기반 개인화**
  - 추천 강의 표시
  - 학습 패턴 분석
  - 맞춤형 UI 조정
- [ ] 🌍 **다국어 지원**
  - RTL 언어 지원
  - 언어별 폰트 최적화
  - 자동 번역 통합
- [ ] 📊 **고급 분석**
  - 히트맵 통합
  - A/B 테스팅
  - 사용자 행동 추적

### 장기 로드맵
- [ ] 🎮 **인터랙티브 요소**
  - 3D 효과
  - 패럴랙스 스크롤
  - 마이크로 인터랙션
- [ ] 🏢 **화이트 라벨**
  - 브랜딩 커스터마이징
  - 멀티 스킨 지원
  - 테마 빌더
- [ ] 🔗 **외부 서비스 연동**
  - Zoom 웨비나
  - Google Meet
  - Slack 통합

## 🤝 기여하기

테마 개선에 기여하고 싶으신가요? 환영합니다!

1. 프로젝트 포크
2. 기능 브랜치 생성 (`git checkout -b feature/NewFeature`)
3. 변경사항 커밋 (`git commit -m 'Add NewFeature'`)
4. 브랜치 푸시 (`git push origin feature/NewFeature`)
5. Pull Request 생성

## 📞 지원

- **문서**: [테마 가이드](docs/THEME-GUIDE.md)
- **이슈**: [GitHub Issues](https://github.com/BBQ-MAN/LectusClassSystem/issues)
- **포럼**: [WordPress.org Support](https://wordpress.org/support/theme/lectus-academy)

## 🔧 문제 해결

### 일반적인 문제

#### 테마 활성화 실패
```bash
# PHP 버전 확인
php -v  # 7.4 이상

# 파일 권한 확인
chmod -R 755 wp-content/themes/lectus-academy-theme/
```

#### 스타일이 적용되지 않음
```php
// wp-config.php에서 캐시 비우기
define('WP_CACHE', false);

// 브라우저 캐시 삭제
Ctrl + Shift + R (Windows/Linux)
Cmd + Shift + R (Mac)
```

#### Lectus Class System과 호환 문제
```bash
# 플러그인 버전 확인
wp plugin list --name=lectus-class-system

# 최신 버전으로 업데이트
wp plugin update lectus-class-system
```

## 📄 라이선스

GPL v2.0 이상. 자세한 내용은 [LICENSE](LICENSE) 파일 참조.

## 👥 크레딧

- Lectus Team 디자인 및 개발
- Inflearn UI/UX 영감
- WordPress 커뮤니티
- 오픈소스 라이브러리

## 📈 변경 로그

### Version 2.0.0 (2024-12)
- Inflearn 스타일 디자인 적용
- 반응형 레이아웃 완성
- Lectus Class System 완벽 통합
- 성능 최적화
- 다크 모드 추가

### Version 1.0.0 (2024-10)
- 초기 릴리스
- 기본 테마 구조
- LMS 기본 템플릿

---

**교육의 미래를 디자인합니다 🎨**