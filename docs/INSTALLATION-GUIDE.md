# Lectus Class System 설치 가이드

## 📋 목차

- [시스템 요구사항](#시스템-요구사항)
- [설치 전 준비사항](#설치-전-준비사항)
- [설치 방법](#설치-방법)
- [초기 설정](#초기-설정)
- [문제 해결](#문제-해결)
- [업데이트 방법](#업데이트-방법)

## 시스템 요구사항

### 최소 요구사항

| 구성 요소 | 최소 버전 | 권장 버전 |
|-----------|-----------|-----------|
| **WordPress** | 5.0 | 6.0+ |
| **PHP** | 8.0 | 8.2+ |
| **MySQL** | 5.7 | 8.0+ |
| **MariaDB** (대체) | 10.2 | 10.5+ |
| **웹서버** | Apache 2.4 / Nginx 1.18 | Apache 2.4+ / Nginx 1.20+ |
| **메모리** | 256MB PHP Memory | 512MB+ |
| **디스크 공간** | 100MB | 500MB+ |

### PHP 확장 모듈 (필수)

```bash
# 필수 PHP 확장 모듈 확인
php -m
```

필수 모듈:
- `mysqli` - MySQL 데이터베이스 연결
- `gd` 또는 `imagick` - 이미지 처리 (수료증 생성)
- `curl` - 외부 API 통신
- `mbstring` - 멀티바이트 문자열 처리
- `zip` - 파일 압축/해제
- `json` - JSON 데이터 처리
- `xml` - XML 파싱

### 브라우저 호환성

| 브라우저 | 최소 버전 |
|----------|-----------|
| Chrome | 90+ |
| Firefox | 88+ |
| Safari | 14+ |
| Edge | 90+ |

## 설치 전 준비사항

### 1. WordPress 설치 확인

```bash
# WordPress 버전 확인 (WP-CLI)
wp core version

# 또는 관리자 대시보드에서
# 대시보드 → 업데이트에서 확인
```

### 2. 백업 생성

```bash
# 데이터베이스 백업
wp db export backup-$(date +%Y%m%d).sql

# 파일 백업
tar -czf wordpress-backup-$(date +%Y%m%d).tar.gz /path/to/wordpress/
```

### 3. 권한 설정

```bash
# 디렉토리 권한
find /path/to/wordpress -type d -exec chmod 755 {} \;

# 파일 권한
find /path/to/wordpress -type f -exec chmod 644 {} \;

# wp-content 쓰기 권한
chmod -R 775 /path/to/wordpress/wp-content/
```

## 설치 방법

### 방법 1: Docker를 이용한 자동 설치 (권장)

#### Docker 및 Docker Compose 설치
```bash
# Docker 설치 (Ubuntu/Debian)
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh

# Docker Compose 설치
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
```

#### Lectus Class System 설치
```bash
# 1. 프로젝트 클론
git clone https://github.com/BBQ-MAN/LectusClassSystem.git
cd LectusClassSystem

# 2. 환경 설정 파일 생성
cp .env.example .env

# 3. .env 파일 편집
nano .env
```

`.env` 파일 설정:
```env
# WordPress 설정
WORDPRESS_DB_HOST=db:3306
WORDPRESS_DB_USER=wordpress
WORDPRESS_DB_PASSWORD=your_password
WORDPRESS_DB_NAME=wordpress

# MySQL 설정
MYSQL_ROOT_PASSWORD=root_password
MYSQL_DATABASE=wordpress
MYSQL_USER=wordpress
MYSQL_PASSWORD=your_password

# 사이트 설정
WORDPRESS_URL=http://localhost:8000
WORDPRESS_TITLE=Lectus Academy
WORDPRESS_ADMIN_USER=admin
WORDPRESS_ADMIN_PASSWORD=admin_password
WORDPRESS_ADMIN_EMAIL=admin@example.com
```

#### Docker 컨테이너 실행
```bash
# 컨테이너 시작
docker-compose up -d

# 로그 확인
docker-compose logs -f

# 상태 확인
docker-compose ps
```

#### 브라우저 접속
```
http://localhost:8000 - 사이트
http://localhost:8000/wp-admin - 관리자
```

### 방법 2: 수동 설치

#### 1. 파일 다운로드
```bash
# 최신 릴리스 다운로드
wget https://github.com/BBQ-MAN/LectusClassSystem/releases/latest/download/lectus-class-system.zip
wget https://github.com/BBQ-MAN/LectusClassSystem/releases/latest/download/lectus-academy-theme.zip

# 또는 Git 클론
git clone https://github.com/BBQ-MAN/LectusClassSystem.git
```

#### 2. 플러그인 설치
```bash
# 압축 해제
unzip lectus-class-system.zip

# 플러그인 디렉토리로 이동
mv lectus-class-system /path/to/wordpress/wp-content/plugins/

# 권한 설정
chmod -R 755 /path/to/wordpress/wp-content/plugins/lectus-class-system/
```

#### 3. 테마 설치
```bash
# 압축 해제
unzip lectus-academy-theme.zip

# 테마 디렉토리로 이동
mv lectus-academy-theme /path/to/wordpress/wp-content/themes/

# 권한 설정
chmod -R 755 /path/to/wordpress/wp-content/themes/lectus-academy-theme/
```

#### 4. WordPress 관리자에서 활성화
1. WordPress 관리자 로그인
2. **플러그인 → 설치된 플러그인**
3. "Lectus Class System" 찾아서 **활성화**
4. **외모 → 테마**
5. "Lectus Academy" 테마 **활성화**

### 방법 3: WP-CLI를 이용한 설치

```bash
# 플러그인 설치 및 활성화
wp plugin install /path/to/lectus-class-system.zip --activate

# 테마 설치 및 활성화
wp theme install /path/to/lectus-academy-theme.zip --activate

# WooCommerce 설치 (필요한 경우)
wp plugin install woocommerce --activate
```

### 방법 4: Composer를 이용한 설치

`composer.json` 파일:
```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/BBQ-MAN/LectusClassSystem"
        }
    ],
    "require": {
        "lectus/class-system": "^1.0",
        "woocommerce/woocommerce": "^8.0"
    }
}
```

```bash
# Composer 설치
composer install

# 자동 로더 생성
composer dump-autoload
```

## 초기 설정

### 1. 데이터베이스 테이블 생성

플러그인 활성화 시 자동으로 생성되지만, 수동으로 생성이 필요한 경우:

```sql
-- wp_lectus_enrollment 테이블
CREATE TABLE IF NOT EXISTS `wp_lectus_enrollment` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT(20) UNSIGNED NOT NULL,
    `course_id` BIGINT(20) UNSIGNED NOT NULL,
    `order_id` BIGINT(20) UNSIGNED DEFAULT 0,
    `status` VARCHAR(20) DEFAULT 'active',
    `enrolled_at` DATETIME NOT NULL,
    `expires_at` DATETIME DEFAULT NULL,
    `completed_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `user_course` (`user_id`, `course_id`),
    KEY `status` (`status`),
    KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- wp_lectus_progress 테이블
CREATE TABLE IF NOT EXISTS `wp_lectus_progress` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT(20) UNSIGNED NOT NULL,
    `course_id` BIGINT(20) UNSIGNED NOT NULL,
    `lesson_id` BIGINT(20) UNSIGNED NOT NULL,
    `status` VARCHAR(20) DEFAULT 'not_started',
    `progress` INT DEFAULT 0,
    `started_at` DATETIME DEFAULT NULL,
    `completed_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `user_lesson` (`user_id`, `lesson_id`),
    KEY `user_course` (`user_id`, `course_id`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 추가 테이블들...
```

### 2. 기본 설정 구성

#### 플러그인 설정
```
WordPress 관리자 → Lectus Class System → 설정
```

**일반 설정:**
- ✅ 플러그인 활성화
- 기본 수강 기간: `365` 일
- 수료 기준: `80` %
- 시간대: `Asia/Seoul`

**이메일 설정:**
- ✅ 이메일 알림 활성화
- 발신자 이름: `Lectus Academy`
- 발신자 이메일: `noreply@your-domain.com`
- ✅ 수강 등록 알림
- ✅ 수료증 발급 알림
- ✅ Q&A 답변 알림

**파일 업로드 설정:**
- 최대 파일 크기: `50` MB
- 허용 파일 형식: `pdf,doc,docx,ppt,pptx,xls,xlsx,zip,jpg,png,mp4,mp3`
- 업로드 경로: `/wp-content/uploads/lectus/`

### 3. WooCommerce 설정 (선택사항)

#### WooCommerce 설치
```bash
wp plugin install woocommerce --activate
```

#### WooCommerce 초기 설정
1. **WooCommerce → 설정**
2. **일반 탭:**
   - 판매 위치: 대한민국
   - 통화: 원(₩)
   - 세금 계산: 필요시 활성화

3. **결제 탭:**
   - 결제 수단 활성화 (신용카드, 계좌이체 등)

4. **고급 탭 → 기능:**
   - ✅ High-Performance Order Storage (HPOS) 활성화

### 4. 사용자 역할 설정

플러그인이 자동으로 생성하는 역할:
- `lectus_instructor` - 강사
- `lectus_student` - 수강생

#### 권한 확인
```php
// functions.php에 추가하여 권한 확인
add_action('init', function() {
    $instructor = get_role('lectus_instructor');
    if ($instructor) {
        // 강사 권한 추가
        $instructor->add_cap('edit_courses');
        $instructor->add_cap('publish_courses');
        $instructor->add_cap('manage_qa');
    }
    
    $student = get_role('lectus_student');
    if ($student) {
        // 수강생 권한
        $student->add_cap('view_courses');
        $student->add_cap('submit_qa');
    }
});
```

### 5. 페이지 생성

필수 페이지 자동 생성:
```
Lectus Class System → 설정 → 개발 도구 → 테스트 페이지 생성
```

생성되는 페이지:
- 강의 목록 - `[lectus_courses]`
- 내 강의 - `[lectus_my_courses]`
- 수강생 대시보드 - `[lectus_student_dashboard]`
- 수료증 - `[lectus_certificates]`

### 6. 메뉴 설정

```
외모 → 메뉴
```

추천 메뉴 구조:
```
- 홈
- 강의
  - 전체 강의
  - 패키지 강의
  - 단과 강의
  - 무료 강의
- 수강생
  - 내 강의
  - 대시보드
  - 수료증
- 커뮤니티
  - Q&A
  - 공지사항
- 고객센터
```

### 7. 테스트 데이터 생성

```
Lectus Class System → 설정 → 개발 도구 → 테스트 데이터 생성
```

생성되는 테스트 데이터:
- 카테고리 5개
- 패키지강의 3개
- 단과강의 6개
- 레슨 60개 (강의당 10개)
- 테스트 수강생 5명
- Q&A 샘플 데이터

## 문제 해결

### 일반적인 문제

#### 1. 플러그인 활성화 실패

**증상:** "플러그인을 활성화하는 중 치명적인 오류가 발생했습니다"

**해결방법:**
```bash
# PHP 버전 확인
php -v

# 에러 로그 확인
tail -f /path/to/wordpress/wp-content/debug.log

# 디버그 모드 활성화 (wp-config.php)
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

#### 2. 데이터베이스 테이블 생성 실패

**증상:** "Table doesn't exist" 오류

**해결방법:**
```sql
-- 수동으로 테이블 생성
SOURCE /path/to/lectus-class-system/install/database.sql;

-- 또는 WP-CLI 사용
wp eval "require_once 'wp-content/plugins/lectus-class-system/includes/class-lectus-activator.php'; Lectus_Activator::activate();"
```

#### 3. 파일 업로드 실패

**증상:** "업로드한 파일을 이동할 수 없습니다"

**해결방법:**
```bash
# 업로드 디렉토리 생성
mkdir -p /path/to/wordpress/wp-content/uploads/lectus

# 권한 설정
chmod -R 775 /path/to/wordpress/wp-content/uploads/
chown -R www-data:www-data /path/to/wordpress/wp-content/uploads/
```

#### 4. WooCommerce 연동 문제

**증상:** "WooCommerce가 필요합니다" 메시지

**해결방법:**
```bash
# WooCommerce 설치
wp plugin install woocommerce --activate

# HPOS 호환성 확인
wp wc hpos status
```

#### 5. 수료증 생성 실패

**증상:** PDF 생성 오류

**해결방법:**
```bash
# GD 라이브러리 설치 (Ubuntu/Debian)
sudo apt-get install php8.0-gd

# 또는 ImageMagick
sudo apt-get install php8.0-imagick

# PHP 재시작
sudo service php8.0-fpm restart
```

### 권한 문제

```bash
# WordPress 파일 권한 수정 스크립트
#!/bin/bash

WP_ROOT="/path/to/wordpress"
WP_OWNER="www-data"
WP_GROUP="www-data"

# 디렉토리 권한
find ${WP_ROOT} -type d -exec chmod 755 {} \;

# 파일 권한
find ${WP_ROOT} -type f -exec chmod 644 {} \;

# wp-content 권한
chmod -R 775 ${WP_ROOT}/wp-content

# 소유권 변경
chown -R ${WP_OWNER}:${WP_GROUP} ${WP_ROOT}
```

### 성능 문제

#### 1. 느린 페이지 로딩

**해결방법:**
```php
// wp-config.php
define('WP_CACHE', true);
define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');
```

#### 2. 데이터베이스 최적화
```sql
-- 인덱스 추가
ALTER TABLE wp_lectus_enrollment ADD INDEX idx_user_status (user_id, status);
ALTER TABLE wp_lectus_progress ADD INDEX idx_course_progress (course_id, progress);

-- 테이블 최적화
OPTIMIZE TABLE wp_lectus_enrollment;
OPTIMIZE TABLE wp_lectus_progress;
```

## 업데이트 방법

### 자동 업데이트

```
WordPress 관리자 → 대시보드 → 업데이트
```

### 수동 업데이트

#### 1. 백업 생성
```bash
# 전체 백업
wp db export backup-before-update.sql
tar -czf wordpress-backup.tar.gz /path/to/wordpress/
```

#### 2. 새 버전 다운로드
```bash
wget https://github.com/BBQ-MAN/LectusClassSystem/releases/latest/download/lectus-class-system.zip
```

#### 3. 기존 파일 교체
```bash
# 기존 플러그인 백업
mv wp-content/plugins/lectus-class-system wp-content/plugins/lectus-class-system.bak

# 새 버전 설치
unzip lectus-class-system.zip -d wp-content/plugins/
```

#### 4. 데이터베이스 업데이트
```
WordPress 관리자 → Lectus Class System → 설정 → 데이터베이스 업데이트
```

### 버전별 업그레이드 가이드

#### v1.0.0 → v1.1.0
```sql
-- 새 컬럼 추가
ALTER TABLE wp_lectus_materials 
ADD COLUMN material_type ENUM('file','link') DEFAULT 'file',
ADD COLUMN external_url VARCHAR(500);
```

#### v1.1.0 → v1.2.0
```php
// 새 기능 활성화
update_option('lectus_enable_live_streaming', true);
update_option('lectus_enable_mobile_app', true);
```

## 제거 방법

### 완전 제거

```bash
# 1. 플러그인 비활성화
wp plugin deactivate lectus-class-system

# 2. 데이터 백업 (선택사항)
wp db export lectus-backup.sql --tables=$(wp db tables 'wp_lectus_*' --format=csv)

# 3. 플러그인 삭제
wp plugin delete lectus-class-system

# 4. 테마 삭제
wp theme delete lectus-academy-theme

# 5. 데이터베이스 테이블 삭제 (주의!)
wp db query "DROP TABLE IF EXISTS wp_lectus_enrollment, wp_lectus_progress, wp_lectus_materials, wp_lectus_qa_questions, wp_lectus_qa_answers, wp_lectus_certificates;"
```

## 지원 및 도움말

### 공식 문서
- [사용자 가이드](USER-GUIDE.md)
- [개발자 문서](../lectus-class-system/DEVELOPER.md)
- [API 레퍼런스](API-DOCUMENTATION.md)

### 커뮤니티 지원
- GitHub Issues: https://github.com/BBQ-MAN/LectusClassSystem/issues
- WordPress 포럼: https://wordpress.org/support/plugin/lectus-class-system

### 상업적 지원
- 이메일: support@lectus.kr
- 전화: 02-1234-5678
- 설치 대행 서비스 available

---

**마지막 업데이트**: 2024년 12월
**문서 버전**: 1.0.0
**플러그인 버전**: 1.0.0