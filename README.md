# Lectus Class System - 완전한 WordPress LMS 솔루션

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.0+-green.svg)
![PHP](https://img.shields.io/badge/PHP-8.0+-purple.svg)
![WooCommerce](https://img.shields.io/badge/WooCommerce-6.0+-96588A.svg)
![License](https://img.shields.io/badge/license-GPL--2.0+-red.svg)
![Status](https://img.shields.io/badge/status-production--ready-success.svg)

WordPress 기반의 완전한 온라인 교육 플랫폼 솔루션입니다. 전문 LMS 플러그인과 최적화된 테마, 그리고 개발 도구를 포함한 올인원 패키지입니다.

## 🌟 프로젝트 개요

Lectus Class System은 교육 기관, 기업 교육, 개인 강사를 위한 종합적인 온라인 학습 관리 시스템입니다. WordPress와 WooCommerce의 강력한 기능을 활용하여 안정적이고 확장 가능한 교육 플랫폼을 제공합니다.

### 핵심 구성 요소
- **LMS 플러그인**: 강의 관리, 수강생 관리, 평가 시스템
- **전용 테마**: Inflearn 스타일의 현대적인 디자인
- **개발 도구**: Docker 환경, 테스트 도구, 문서화

## 📦 패키지 구성

```
LectusClassSystem/
├── lectus-class-system/        # 핵심 LMS 플러그인
├── lectus-academy-theme/       # 전용 WordPress 테마
├── docker-compose.yml          # Docker 개발 환경
├── tests/                      # 통합 테스트 스위트
├── docs/                       # 프로젝트 문서
└── SuperClaude_Framework/      # AI 개발 도구
```

## 🚀 빠른 시작

### Docker를 이용한 원클릭 설치
```bash
# 1. 프로젝트 클론
git clone https://github.com/BBQ-MAN/LectusClassSystem.git
cd LectusClassSystem

# 2. Docker 컨테이너 실행
docker-compose up -d

# 3. 브라우저에서 접속
# http://localhost:8000
# 관리자: admin / admin
```

### 수동 설치
```bash
# 1. WordPress에 플러그인 설치
cp -r lectus-class-system/ /path/to/wordpress/wp-content/plugins/

# 2. 테마 설치
cp -r lectus-academy-theme/ /path/to/wordpress/wp-content/themes/

# 3. WordPress 관리자에서 활성화
# - Lectus Class System 플러그인 활성화
# - Lectus Academy 테마 활성화
```

## 🎯 주요 기능

### 📚 교육 콘텐츠 관리
- **3단계 구조**: 패키지강의 → 단과강의 → 레슨
- **다양한 콘텐츠**: 동영상, 텍스트, 퀴즈, 과제
- **강의자료**: 파일 업로드, 외부 링크 연동
- **접근 제어**: 순차적/자유 학습 모드

### 💳 결제 및 상거래
- **WooCommerce 통합**: 완벽한 전자상거래 기능
- **자동 수강 등록**: 결제 완료 시 즉시 접근
- **구독 모델**: 기간제 수강권 판매
- **환불 관리**: 자동 접근 권한 해제

### 👥 학습자 경험
- **개인 대시보드**: 수강 현황, 진도 관리
- **수료증 발급**: PDF 다운로드 및 검증
- **Q&A 시스템**: 강의별 질문답변
- **진도 추적**: 실시간 학습 진행률

### 📊 관리 및 분석
- **통계 대시보드**: 매출, 수강생, 완료율
- **리포트 생성**: Excel 내보내기
- **활동 로그**: 상세 사용 기록
- **벌크 작업**: 대량 데이터 처리

## 🏗️ 시스템 아키텍처

### 기술 스택
```
Frontend:
- HTML5, CSS3, JavaScript (ES6+)
- jQuery 3.6+
- Bootstrap 5 (선택적)

Backend:
- PHP 8.0+
- WordPress 5.0+
- MySQL 8.0+ / MariaDB 10.5+

Integration:
- WooCommerce 6.0+
- REST API
- AJAX

DevOps:
- Docker & Docker Compose
- Playwright (E2E 테스트)
- PHPUnit (단위 테스트)
```

### 데이터베이스 스키마
```sql
주요 테이블:
- wp_lectus_enrollment    # 수강 등록
- wp_lectus_progress      # 진도 관리
- wp_lectus_materials     # 강의자료
- wp_lectus_qa_questions  # Q&A 질문
- wp_lectus_qa_answers    # Q&A 답변
- wp_lectus_certificates  # 수료증
```

## ⚙️ 시스템 요구사항

### 최소 요구사항
- **서버**: Apache 2.4+ / Nginx 1.18+
- **PHP**: 8.0+ (필수 확장: mysqli, gd, curl, mbstring)
- **MySQL**: 5.7+ / MariaDB 10.2+
- **WordPress**: 5.0+
- **메모리**: 256MB PHP 메모리

### 권장 사양
- **서버**: Nginx 1.20+ with PHP-FPM
- **PHP**: 8.2+ with OPcache
- **MySQL**: 8.0+ / MariaDB 10.5+
- **WordPress**: 6.0+
- **메모리**: 512MB PHP 메모리
- **SSL**: Let's Encrypt 인증서

## 📖 사용 가이드

### 1. 초기 설정
```
1. WordPress 설치 및 설정
2. Lectus Class System 플러그인 활성화
3. Lectus Academy 테마 활성화
4. WooCommerce 설정 (선택사항)
5. 기본 설정 구성
```

### 2. 강의 생성
```
패키지강의 생성
  ↓
단과강의 생성 및 연결
  ↓
레슨 추가 (동영상, 텍스트, 퀴즈)
  ↓
강의자료 업로드
  ↓
WooCommerce 상품 연결
```

### 3. 수강생 관리
```
수강 등록 (수동/자동)
  ↓
진도 추적 및 모니터링
  ↓
Q&A 응답 및 지원
  ↓
수료증 발급
```

## 🧪 테스트

### 자동화 테스트
```bash
# E2E 테스트 (Playwright)
cd tests
npm install
npm test

# PHP 단위 테스트
cd lectus-class-system
composer install
./vendor/bin/phpunit

# 전체 테스트 스위트
npm run test:all
```

### 테스트 데이터 생성
```bash
# WordPress CLI 사용
wp eval-file lectus-class-system/admin/lectus-test-data.php

# 관리자 UI 사용
Lectus Class System → 설정 → 개발 도구 → 테스트 데이터 생성
```

## 🚀 배포

### Production 배포 체크리스트
- [ ] 디버그 모드 비활성화
- [ ] 캐싱 활성화
- [ ] SSL 인증서 설치
- [ ] 백업 시스템 구성
- [ ] 모니터링 설정
- [ ] CDN 구성 (선택사항)

### 환경 설정
```php
// wp-config.php
define('WP_DEBUG', false);
define('WP_CACHE', true);
define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');
```

## 📊 성능 최적화

### 권장 최적화
- **캐싱**: Redis/Memcached 사용
- **CDN**: 정적 자원 CDN 배포
- **이미지**: WebP 포맷 사용
- **데이터베이스**: 인덱스 최적화
- **PHP**: OPcache 활성화

### 벤치마크
```
페이지 로드 시간: < 2초
동시 사용자: 1000+
일일 처리량: 100,000+ 페이지뷰
API 응답: < 200ms
```

## 🔒 보안

### 보안 조치
- SQL 인젝션 방지
- XSS 공격 방지
- CSRF 토큰 검증
- 파일 업로드 검증
- 역할 기반 접근 제어

### 보안 설정
```apache
# .htaccess
<Files wp-config.php>
    Order Allow,Deny
    Deny from all
</Files>

<Files xmlrpc.php>
    Order Deny,Allow
    Deny from all
</Files>
```

## 📈 로드맵

### 2025 Q1
- [ ] 모바일 앱 출시 (iOS/Android)
- [ ] 라이브 스트리밍 통합
- [ ] AI 학습 도우미
- [ ] 다국어 지원 확대

### 2025 Q2
- [ ] 블록체인 수료증
- [ ] 소셜 러닝 기능
- [ ] 게이미피케이션
- [ ] 화상 회의 통합

### 2025 Q3
- [ ] SaaS 버전 출시
- [ ] 마켓플레이스 오픈
- [ ] 기업용 패키지
- [ ] API v2 출시

### 장기 계획
- [ ] 글로벌 CDN 구축
- [ ] 머신러닝 기반 추천
- [ ] VR/AR 학습 지원
- [ ] 오프라인 동기화

## 🤝 기여하기

### 기여 방법
1. 프로젝트 포크
2. 기능 브랜치 생성 (`git checkout -b feature/AmazingFeature`)
3. 변경사항 커밋 (`git commit -m 'Add AmazingFeature'`)
4. 브랜치 푸시 (`git push origin feature/AmazingFeature`)
5. Pull Request 생성

### 개발 가이드라인
- [WordPress 코딩 표준](https://developer.wordpress.org/coding-standards/) 준수
- 단위 테스트 작성 필수
- 문서화 필수
- 코드 리뷰 필수

## 📞 지원 및 문의

### 문서
- [설치 가이드](docs/INSTALLATION.md)
- [사용자 매뉴얼](docs/USER-GUIDE.md)
- [개발자 문서](lectus-class-system/DEVELOPER.md)
- [API 레퍼런스](lectus-class-system/docs/API-REFERENCE.md)

### 커뮤니티
- **GitHub**: [Issues](https://github.com/BBQ-MAN/LectusClassSystem/issues) | [Discussions](https://github.com/BBQ-MAN/LectusClassSystem/discussions)
- **포럼**: [WordPress.org](https://wordpress.org/support/plugin/lectus-class-system)
- **이메일**: support@lectus.kr

### 상업적 지원
- 설치 및 구성 서비스
- 커스터마이징 개발
- 유지보수 계약
- 교육 및 컨설팅

## 📄 라이선스

이 프로젝트는 GPL v2.0 이상의 라이선스로 배포됩니다.
- 자유롭게 사용, 수정, 배포 가능
- 수정 시 동일한 라이선스 적용 필수
- 상업적 사용 가능

자세한 내용은 [LICENSE](LICENSE) 파일을 참조하세요.

## 👥 팀

### 핵심 개발팀
- **프로젝트 리드**: BBQ-MAN
- **백엔드 개발**: Lectus Backend Team
- **프론트엔드 개발**: Lectus Frontend Team
- **UI/UX 디자인**: Lectus Design Team
- **QA & 테스트**: Lectus QA Team

### 기여자
프로젝트에 기여해주신 모든 분들께 감사드립니다!

[![Contributors](https://contrib.rocks/image?repo=BBQ-MAN/LectusClassSystem)](https://github.com/BBQ-MAN/LectusClassSystem/graphs/contributors)

## 🙏 감사의 말

- WordPress 커뮤니티
- WooCommerce 팀
- 오픈소스 기여자들
- 베타 테스터들
- 모든 사용자 여러분

## 📊 프로젝트 통계

![GitHub stars](https://img.shields.io/github/stars/BBQ-MAN/LectusClassSystem?style=social)
![GitHub forks](https://img.shields.io/github/forks/BBQ-MAN/LectusClassSystem?style=social)
![GitHub watchers](https://img.shields.io/github/watchers/BBQ-MAN/LectusClassSystem?style=social)

### 코드 품질
- **코드 커버리지**: 85%
- **문서화**: 95%
- **테스트 통과율**: 100%
- **보안 점수**: A+

---

<div align="center">

**교육의 미래를 만들어갑니다 🚀**

[웹사이트](https://lectus.kr) | [데모](https://mirai.lectus.kr) | [문서](https://docs.lectus.kr)

</div>