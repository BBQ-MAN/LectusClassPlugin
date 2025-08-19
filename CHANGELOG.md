# Changelog

All notable changes to the Lectus Class System project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.0] - 2025-08-19

### Added
- WooCommerce 패키지 상품 시스템 완전 구현
- 커스텀 상품 페이지 템플릿 (single-product-course.php)
- 검색 가능한 강의 선택 UI with 카테고리 그룹핑
- 강사 Q&A 관리 시스템 (class-lectus-instructor-qa.php)
- 패키지 상품 카드 컴포넌트
- 탭 기반 상품 페이지 디자인
- 자동 단일/패키지 상품 구분 로직

### Changed
- 패키지강의 Post Type 제거 → WooCommerce 상품으로 통합
- 강의 선택 UI를 multi-select에서 searchable checkbox로 개선
- jQuery 의존성 제거 (순수 JavaScript 사용)
- 수강생 관리 페이지 UI/UX 개선

### Removed
- 임의의 패키지 유형 필드 (bundle/subscription/lifetime)
- 사용하지 않는 coursepackage custom post type

### Fixed
- 외부 링크 강의자료 저장 오류
- Q&A 시스템 Rate Limiting 문제
- 상품 페이지 JavaScript 오류

## [1.1.0] - 2025-08-13

### Added
- 강의 섹션 관리 시스템
- 강사 센터 대시보드
- 수강생 관리 기능 강화
- 실시간 진도 추적
- 벌크 작업 기능

### Changed
- 대시보드를 '내 강의실'로 통합
- 강의 목록 페이지 UI 개선
- 수강 기간 관리 로직 개선

### Fixed
- 수료증 발급 오류
- 진도 계산 버그
- 환불 시 접근 권한 처리

## [1.0.0] - 2025-08-07

### Initial Release
- 기본 LMS 플러그인 구조
- WooCommerce 기본 연동
- 패키지강의/단과강의/레슨 관리
- 수강생 등록 및 진도 추적
- Q&A 시스템
- 수료증 발급 시스템
- 강의자료 관리 (파일 업로드)
- 관리자 대시보드
- 기본 테마 통합

## [0.9.0-beta] - 2025-07-15

### Beta Release
- 프로토타입 개발
- 기본 기능 구현
- 테스트 환경 구축
- Docker 개발 환경

---

## Version History

### Versioning Rules
- **Major (X.0.0)**: Breaking changes, major architecture changes
- **Minor (0.X.0)**: New features, significant improvements
- **Patch (0.0.X)**: Bug fixes, minor improvements

### Support Policy
- Current version: Full support
- Previous minor version: Security updates only
- Older versions: No support

### Upgrade Guide
For upgrade instructions, please refer to [UPGRADE.md](docs/UPGRADE.md)

### Breaking Changes
#### From 1.1.x to 1.2.0
- `coursepackage` post type removed - migrate to WooCommerce products
- Package type field removed - use categories instead
- Multi-select course selection replaced with checkbox UI

#### From 1.0.x to 1.1.0
- Dashboard structure changed
- Database schema updates required

### Deprecation Notices
- `coursepackage` custom post type (removed in 1.2.0)
- `_package_type` meta field (removed in 1.2.0)
- jQuery-based interactions (migrated to vanilla JS in 1.2.0)

### Migration Scripts
Migration scripts are available in `/migrations/` directory:
- `migrate-1.0-to-1.1.php`
- `migrate-1.1-to-1.2.php`

---

## Contributors
- BBQ-MAN (Project Lead)
- Claude AI (Development Assistant)
- Community Contributors

## Links
- [GitHub Repository](https://github.com/BBQ-MAN/LectusClassSystem)
- [Documentation](https://docs.lectus.kr)
- [Support](https://github.com/BBQ-MAN/LectusClassSystem/issues)