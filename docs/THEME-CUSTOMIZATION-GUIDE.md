# Lectus Academy 테마 커스터마이징 가이드

## 📋 목차

- [테마 구조 이해하기](#테마-구조-이해하기)
- [CSS 커스터마이징](#css-커스터마이징)
- [템플릿 수정하기](#템플릿-수정하기)
- [자식 테마 만들기](#자식-테마-만들기)
- [커스터마이저 사용하기](#커스터마이저-사용하기)
- [위젯 영역 관리](#위젯-영역-관리)
- [메뉴 커스터마이징](#메뉴-커스터마이징)
- [고급 커스터마이징](#고급-커스터마이징)

## 테마 구조 이해하기

### 디렉토리 구조
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
│   ├── customizer.php          # 커스터마이저 설정
│   ├── custom-functions.php    # 커스텀 함수
│   ├── template-tags.php       # 템플릿 태그
│   └── widgets.php             # 커스텀 위젯
├── template-parts/             # 템플릿 파트
│   └── content-course-card.php # 강의 카드
├── languages/                  # 번역 파일
└── 템플릿 파일들...
```

### 주요 템플릿 파일

| 파일명 | 용도 | 사용 위치 |
|--------|------|-----------|
| `style.css` | 테마 정보 및 기본 스타일 | 전체 사이트 |
| `functions.php` | 테마 기능 정의 | - |
| `index.php` | 기본 템플릿 | 블로그 페이지 |
| `front-page.php` | 홈페이지 | 메인 페이지 |
| `header.php` | 헤더 영역 | 모든 페이지 상단 |
| `footer.php` | 푸터 영역 | 모든 페이지 하단 |
| `single-coursesingle.php` | 단과강의 상세 | 강의 페이지 |
| `single-lesson.php` | 레슨 상세 | 레슨 페이지 |
| `archive-coursesingle.php` | 강의 목록 | 강의 아카이브 |
| `page-student-dashboard.php` | 수강생 대시보드 | 마이페이지 |

## CSS 커스터마이징

### CSS 변수 시스템

테마는 CSS 변수를 사용하여 쉽게 커스터마이징할 수 있습니다.

#### 색상 변경
```css
/* style.css */
:root {
    /* 메인 색상 */
    --primary-color: #30b2e5;      /* 기본 파란색 */
    --primary-dark: #2090c0;       /* 어두운 파란색 */
    --secondary-color: #524fa1;     /* 보조 보라색 */
    
    /* 텍스트 색상 */
    --text-primary: #1e1e1e;       /* 주 텍스트 */
    --text-secondary: #495057;     /* 보조 텍스트 */
    --text-light: #858a8d;         /* 연한 텍스트 */
    
    /* 배경 색상 */
    --bg-primary: #ffffff;         /* 주 배경 */
    --bg-secondary: #f8f9fa;       /* 보조 배경 */
    --bg-gray: #f1f3f5;           /* 회색 배경 */
    
    /* 테두리 색상 */
    --border-color: #dee2e6;       /* 기본 테두리 */
    --border-light: #e9ecef;       /* 연한 테두리 */
    
    /* 그림자 */
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.06);
    --shadow-md: 0 4px 6px rgba(0,0,0,0.08);
    --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
    
    /* 간격 */
    --spacing-xs: 0.5rem;
    --spacing-sm: 1rem;
    --spacing-md: 1.5rem;
    --spacing-lg: 2rem;
    --spacing-xl: 3rem;
}
```

#### 다크 모드 추가
```css
/* 다크 모드 스타일 */
@media (prefers-color-scheme: dark) {
    :root {
        --bg-primary: #1a1a1a;
        --bg-secondary: #2a2a2a;
        --text-primary: #ffffff;
        --text-secondary: #b0b0b0;
        --border-color: #404040;
    }
}

/* 수동 다크 모드 토글 */
body.dark-mode {
    --bg-primary: #1a1a1a;
    --bg-secondary: #2a2a2a;
    --text-primary: #ffffff;
    --text-secondary: #b0b0b0;
    --border-color: #404040;
}
```

### 레이아웃 커스터마이징

#### 컨테이너 너비 조정
```css
/* 기본 컨테이너 */
.container {
    max-width: 1200px;  /* 기본값 */
    margin: 0 auto;
    padding: 0 15px;
}

/* 와이드 레이아웃 */
.container-wide {
    max-width: 1400px;
}

/* 좁은 레이아웃 */
.container-narrow {
    max-width: 960px;
}
```

#### 그리드 시스템
```css
/* 강의 카드 그리드 */
.course-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: var(--spacing-md);
}

/* 2열 그리드 */
.course-grid-2 {
    grid-template-columns: repeat(2, 1fr);
}

/* 3열 그리드 */
.course-grid-3 {
    grid-template-columns: repeat(3, 1fr);
}

/* 4열 그리드 */
.course-grid-4 {
    grid-template-columns: repeat(4, 1fr);
}
```

### 컴포넌트 스타일링

#### 강의 카드 커스터마이징
```css
/* 강의 카드 기본 스타일 */
.course-card {
    background: var(--bg-primary);
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.3s, box-shadow 0.3s;
}

/* 호버 효과 변경 */
.course-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

/* 카드 이미지 비율 */
.course-card-image {
    aspect-ratio: 16/9;
    object-fit: cover;
}

/* 카드 뱃지 */
.course-card-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: var(--primary-color);
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
}
```

#### 버튼 스타일
```css
/* 기본 버튼 */
.btn {
    display: inline-block;
    padding: 10px 20px;
    border-radius: 4px;
    font-weight: 500;
    transition: all 0.3s;
    cursor: pointer;
}

/* 메인 버튼 */
.btn-primary {
    background: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-dark);
}

/* 아웃라인 버튼 */
.btn-outline {
    background: transparent;
    border: 2px solid var(--primary-color);
    color: var(--primary-color);
}

.btn-outline:hover {
    background: var(--primary-color);
    color: white;
}
```

## 템플릿 수정하기

### 헤더 커스터마이징

#### 헤더 레이아웃 변경
```php
<!-- header.php -->
<header class="site-header">
    <div class="header-top">
        <!-- 상단 바 추가 -->
        <div class="container">
            <div class="top-bar-content">
                <span>환영합니다!</span>
                <div class="top-bar-menu">
                    <?php
                    if (is_user_logged_in()) {
                        echo '<a href="' . wp_logout_url() . '">로그아웃</a>';
                    } else {
                        echo '<a href="' . wp_login_url() . '">로그인</a>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="header-main">
        <div class="container">
            <div class="header-content">
                <!-- 로고 -->
                <div class="site-logo">
                    <?php the_custom_logo(); ?>
                </div>
                
                <!-- 메인 메뉴 -->
                <nav class="main-navigation">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'menu_class' => 'primary-menu',
                        'container' => false
                    ));
                    ?>
                </nav>
                
                <!-- 검색 -->
                <div class="header-search">
                    <?php get_search_form(); ?>
                </div>
            </div>
        </div>
    </div>
</header>
```

### 강의 목록 템플릿 수정

#### 필터 추가
```php
<!-- archive-coursesingle.php -->
<div class="course-archive">
    <div class="container">
        <div class="archive-content">
            <!-- 필터 사이드바 -->
            <aside class="filter-sidebar">
                <h3>필터</h3>
                
                <!-- 카테고리 필터 -->
                <div class="filter-group">
                    <h4>카테고리</h4>
                    <?php
                    $categories = get_terms('course_category');
                    foreach ($categories as $category) {
                        echo '<label>';
                        echo '<input type="checkbox" value="' . $category->term_id . '">';
                        echo $category->name;
                        echo '</label>';
                    }
                    ?>
                </div>
                
                <!-- 레벨 필터 -->
                <div class="filter-group">
                    <h4>난이도</h4>
                    <label><input type="checkbox" value="beginner"> 초급</label>
                    <label><input type="checkbox" value="intermediate"> 중급</label>
                    <label><input type="checkbox" value="advanced"> 고급</label>
                </div>
                
                <!-- 가격 필터 -->
                <div class="filter-group">
                    <h4>가격</h4>
                    <label><input type="checkbox" value="free"> 무료</label>
                    <label><input type="checkbox" value="paid"> 유료</label>
                </div>
            </aside>
            
            <!-- 강의 목록 -->
            <main class="course-list">
                <!-- 정렬 옵션 -->
                <div class="sort-options">
                    <select id="course-sort">
                        <option value="newest">최신순</option>
                        <option value="popular">인기순</option>
                        <option value="rating">평점순</option>
                        <option value="price-low">가격 낮은순</option>
                        <option value="price-high">가격 높은순</option>
                    </select>
                </div>
                
                <!-- 강의 그리드 -->
                <div class="course-grid">
                    <?php
                    if (have_posts()) {
                        while (have_posts()) {
                            the_post();
                            get_template_part('template-parts/content', 'course-card');
                        }
                    }
                    ?>
                </div>
                
                <!-- 페이지네이션 -->
                <div class="pagination">
                    <?php
                    echo paginate_links(array(
                        'prev_text' => '이전',
                        'next_text' => '다음'
                    ));
                    ?>
                </div>
            </main>
        </div>
    </div>
</div>
```

## 자식 테마 만들기

### 자식 테마 생성 단계

#### 1. 디렉토리 생성
```
wp-content/themes/lectus-academy-child/
```

#### 2. style.css 생성
```css
/*
Theme Name: Lectus Academy Child
Theme URI: https://your-site.com
Description: Lectus Academy 자식 테마
Author: Your Name
Author URI: https://your-site.com
Template: lectus-academy-theme
Version: 1.0.0
Text Domain: lectus-academy-child
*/

/* 여기에 커스텀 스타일 추가 */
```

#### 3. functions.php 생성
```php
<?php
/**
 * Lectus Academy Child Theme Functions
 */

// 부모 테마 스타일 로드
add_action('wp_enqueue_scripts', 'lectus_child_enqueue_styles');
function lectus_child_enqueue_styles() {
    $parent_style = 'lectus-academy-style';
    
    wp_enqueue_style($parent_style, 
        get_template_directory_uri() . '/style.css');
    
    wp_enqueue_style('lectus-academy-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array($parent_style),
        wp_get_theme()->get('Version')
    );
}

// 커스텀 함수 추가
function my_custom_function() {
    // 커스텀 코드
}
```

### 템플릿 오버라이드

자식 테마에서 부모 테마의 템플릿을 오버라이드하려면 같은 이름의 파일을 생성합니다.

```
lectus-academy-child/
├── style.css
├── functions.php
├── header.php          # 부모 테마 header.php 오버라이드
├── single-coursesingle.php  # 강의 템플릿 오버라이드
└── template-parts/
    └── content-course-card.php  # 강의 카드 오버라이드
```

## 커스터마이저 사용하기

### 커스터마이저 접근
```
외모 → 사용자 정의하기
```

### 커스터마이저 섹션

#### 사이트 아이덴티티
- 사이트 제목
- 태그라인
- 로고 업로드
- 사이트 아이콘 (파비콘)

#### 색상 설정
- 주 색상 선택
- 보조 색상 선택
- 텍스트 색상
- 배경 색상

#### 헤더 설정
- 헤더 레이아웃 (기본/중앙정렬/분할)
- 고정 헤더 활성화
- 투명 헤더 옵션
- 검색바 표시/숨김

#### 푸터 설정
- 푸터 위젯 영역 수 (1-4)
- 저작권 텍스트
- 소셜 미디어 링크
- 맨 위로 버튼

#### 강의 설정
- 강의 목록 레이아웃 (그리드/리스트)
- 페이지당 강의 수
- 강의 카드 스타일
- 진도바 표시 옵션

### 커스터마이저 확장

#### 새 섹션 추가
```php
// inc/customizer.php
function lectus_academy_customize_register($wp_customize) {
    // 새 섹션 추가
    $wp_customize->add_section('lectus_advanced_settings', array(
        'title' => '고급 설정',
        'priority' => 200,
    ));
    
    // 설정 추가
    $wp_customize->add_setting('lectus_enable_animations', array(
        'default' => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    
    // 컨트롤 추가
    $wp_customize->add_control('lectus_enable_animations', array(
        'label' => '애니메이션 활성화',
        'section' => 'lectus_advanced_settings',
        'type' => 'checkbox',
    ));
}
add_action('customize_register', 'lectus_academy_customize_register');
```

## 위젯 영역 관리

### 기본 위젯 영역

| 위젯 영역 | 위치 | 용도 |
|-----------|------|------|
| `sidebar-1` | 사이드바 | 블로그 사이드바 |
| `sidebar-course` | 강의 사이드바 | 강의 페이지 사이드바 |
| `footer-1` | 푸터 1열 | 푸터 첫 번째 칸 |
| `footer-2` | 푸터 2열 | 푸터 두 번째 칸 |
| `footer-3` | 푸터 3열 | 푸터 세 번째 칸 |
| `footer-4` | 푸터 4열 | 푸터 네 번째 칸 |

### 새 위젯 영역 추가
```php
// functions.php
function lectus_academy_widgets_init() {
    register_sidebar(array(
        'name' => '홈페이지 배너',
        'id' => 'home-banner',
        'description' => '홈페이지 상단 배너 영역',
        'before_widget' => '<div class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ));
}
add_action('widgets_init', 'lectus_academy_widgets_init');
```

### 위젯 영역 표시
```php
// 템플릿 파일에서
<?php if (is_active_sidebar('home-banner')) : ?>
    <div class="home-banner-area">
        <?php dynamic_sidebar('home-banner'); ?>
    </div>
<?php endif; ?>
```

## 메뉴 커스터마이징

### 메뉴 위치

| 메뉴 위치 | ID | 설명 |
|-----------|-----|------|
| 주 메뉴 | `primary` | 헤더 메인 메뉴 |
| 푸터 메뉴 | `footer` | 푸터 링크 |
| 모바일 메뉴 | `mobile` | 모바일 전용 메뉴 |

### 메가 메뉴 구현
```php
// Walker 클래스 생성
class Lectus_Mega_Menu_Walker extends Walker_Nav_Menu {
    function start_lvl(&$output, $depth = 0, $args = null) {
        if ($depth === 0) {
            $output .= '<div class="mega-menu">';
            $output .= '<div class="mega-menu-content">';
        }
        $output .= '<ul class="sub-menu">';
    }
    
    function end_lvl(&$output, $depth = 0, $args = null) {
        $output .= '</ul>';
        if ($depth === 0) {
            $output .= '</div>';
            $output .= '</div>';
        }
    }
}

// 메뉴 출력 시 Walker 사용
wp_nav_menu(array(
    'theme_location' => 'primary',
    'walker' => new Lectus_Mega_Menu_Walker()
));
```

### 모바일 메뉴 스타일
```css
/* 모바일 메뉴 토글 */
.mobile-menu-toggle {
    display: none;
    background: transparent;
    border: none;
    cursor: pointer;
}

@media (max-width: 768px) {
    .mobile-menu-toggle {
        display: block;
    }
    
    .main-navigation {
        position: fixed;
        top: 0;
        right: -100%;
        width: 80%;
        height: 100vh;
        background: var(--bg-primary);
        transition: right 0.3s;
        z-index: 9999;
    }
    
    .main-navigation.active {
        right: 0;
    }
}
```

## 고급 커스터마이징

### 커스텀 포스트 타입 템플릿

#### 새 포스트 타입 템플릿
```php
<!-- single-custom-type.php -->
<?php get_header(); ?>

<div class="custom-type-single">
    <div class="container">
        <?php while (have_posts()) : the_post(); ?>
            <article>
                <h1><?php the_title(); ?></h1>
                <div class="content">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
</div>

<?php get_footer(); ?>
```

### AJAX 기능 추가

#### AJAX 핸들러
```javascript
// assets/js/custom-ajax.js
jQuery(document).ready(function($) {
    // 강의 필터링
    $('.filter-checkbox').on('change', function() {
        var filters = {
            categories: [],
            levels: [],
            prices: []
        };
        
        // 선택된 필터 수집
        $('.filter-checkbox:checked').each(function() {
            var type = $(this).data('type');
            var value = $(this).val();
            filters[type].push(value);
        });
        
        // AJAX 요청
        $.ajax({
            url: lectus_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'filter_courses',
                filters: filters,
                nonce: lectus_ajax.nonce
            },
            success: function(response) {
                $('.course-grid').html(response);
            }
        });
    });
});
```

#### PHP 핸들러
```php
// functions.php
function handle_filter_courses() {
    check_ajax_referer('lectus-nonce', 'nonce');
    
    $filters = $_POST['filters'];
    
    $args = array(
        'post_type' => 'coursesingle',
        'posts_per_page' => 12
    );
    
    // 필터 적용
    if (!empty($filters['categories'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'course_category',
            'terms' => $filters['categories']
        );
    }
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            get_template_part('template-parts/content', 'course-card');
        }
    }
    
    wp_die();
}
add_action('wp_ajax_filter_courses', 'handle_filter_courses');
add_action('wp_ajax_nopriv_filter_courses', 'handle_filter_courses');
```

### 페이지 빌더 통합

#### Elementor 위젯
```php
// inc/elementor-widgets.php
class Lectus_Course_Grid_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'lectus_course_grid';
    }
    
    public function get_title() {
        return '강의 그리드';
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $args = array(
            'post_type' => 'coursesingle',
            'posts_per_page' => $settings['posts_per_page']
        );
        
        $query = new WP_Query($args);
        
        echo '<div class="course-grid">';
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                get_template_part('template-parts/content', 'course-card');
            }
        }
        echo '</div>';
    }
}
```

### 성능 최적화

#### CSS/JS 최적화
```php
// functions.php
function lectus_academy_optimize_assets() {
    // 불필요한 스크립트 제거
    if (!is_page('checkout')) {
        wp_dequeue_script('wc-checkout');
    }
    
    // Critical CSS 인라인
    if (is_front_page()) {
        $critical_css = file_get_contents(
            get_template_directory() . '/assets/css/critical.css'
        );
        wp_add_inline_style('lectus-academy-style', $critical_css);
    }
}
add_action('wp_enqueue_scripts', 'lectus_academy_optimize_assets', 100);
```

## 문제 해결

### 일반적인 문제

#### 스타일이 적용되지 않음
```php
// 캐시 버스팅
function lectus_academy_version_assets($src) {
    $version = filemtime(get_template_directory() . parse_url($src, PHP_URL_PATH));
    return add_query_arg('ver', $version, $src);
}
add_filter('style_loader_src', 'lectus_academy_version_assets');
add_filter('script_loader_src', 'lectus_academy_version_assets');
```

#### 자식 테마가 작동하지 않음
```bash
# 파일 권한 확인
chmod -R 755 wp-content/themes/lectus-academy-child/

# 테마 이름 확인 (Template 필드가 부모 테마 폴더명과 일치해야 함)
```

## 추가 리소스

- [WordPress 테마 개발 핸드북](https://developer.wordpress.org/themes/)
- [WordPress 코덱스](https://codex.wordpress.org/)
- [CSS 변수 가이드](https://developer.mozilla.org/ko/docs/Web/CSS/Using_CSS_custom_properties)
- [Elementor 개발자 문서](https://developers.elementor.com/)

---

**마지막 업데이트**: 2024년 12월
**테마 버전**: 2.0.0
**문서 버전**: 1.0.0