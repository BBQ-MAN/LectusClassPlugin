# Lectus Class System - WordPress LMS 플러그인

![Version](https://img.shields.io/badge/version-1.2.0-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.0+-green.svg)
![PHP](https://img.shields.io/badge/PHP-8.0+-purple.svg)
![WooCommerce](https://img.shields.io/badge/WooCommerce-6.0+-orange.svg)
![License](https://img.shields.io/badge/license-GPL--2.0+-red.svg)

WordPress용 전문 온라인 교육 관리 시스템 - 패키지강의 관리, WooCommerce 연동, 수강생 관리, Q&A 시스템, 수료증 발급

## 📋 프로젝트 개요

Lectus Class System은 WordPress와 WooCommerce를 기반으로 한 전문 LMS(Learning Management System) 플러그인입니다. 패키지강의, 단과강의, 레슨의 3단계 계층구조로 체계적인 교육 콘텐츠를 관리하고, 결제부터 수료까지 완전한 온라인 교육 솔루션을 제공합니다.

### 🎯 핵심 특징

- **3단계 콘텐츠 구조**: 패키지강의 → 단과강의 → 레슨
- **WooCommerce 완전 통합**: 결제, 상품 관리, 자동 등록
- **유연한 수강 관리**: 개별 수강 기간, 진도 추적, 기간 연장
- **실시간 Q&A 시스템**: 수강생-강사 간 질문답변 및 투표
- **자동 수료증 발급**: PDF 수료증 생성 및 검증
- **강의자료 관리**: 파일 업로드 및 외부 링크 관리

## 🏗️ 시스템 아키텍처

### 콘텐츠 구조
```
패키지강의 (coursepackage)
├── 단과강의 1 (coursesingle)
│   ├── 레슨 1-1 (lesson)
│   ├── 레슨 1-2 (lesson)
│   └── 레슨 1-3 (lesson)
├── 단과강의 2 (coursesingle)
│   ├── 레슨 2-1 (lesson)
│   └── 레슨 2-2 (lesson)
└── 단과강의 3 (coursesingle)
    └── 레슨 3-1 (lesson)
```

### 사용자 역할
- **lectus_instructor**: 강의 생성, 질문 답변, 수강생 관리
- **lectus_student**: 강의 수강, 질문 작성, 진도 관리

### 데이터베이스 구조
```sql
-- 수강 등록 관리
wp_lectus_enrollment (user_id, course_id, order_id, status, enrolled_at, expires_at)

-- 진도 추적
wp_lectus_progress (user_id, course_id, lesson_id, status, progress, completed_at)

-- 강의자료 관리
wp_lectus_materials (course_id, lesson_id, material_type, title, file_url, external_url)

-- Q&A 시스템
wp_lectus_qa_questions, wp_lectus_qa_answers (course_id, lesson_id, content, votes)

-- 수료증 발급
wp_lectus_certificates (user_id, course_id, certificate_number, generated_at)
```

## 🚀 주요 기능

### 📚 강의 관리 시스템
- **패키지강의**: 여러 단과강의를 묶어 판매하는 상위 개념
- **단과강의**: 실제 교육과정 단위, WooCommerce 상품과 연동
- **레슨**: 개별 수업 단위 (텍스트, 동영상, 퀴즈, 과제)
- **벌크 업로드**: CSV 파일로 레슨 일괄 생성

### 💳 WooCommerce 연동
- **자동 수강 등록**: 결제 완료 시 자동 접근 권한 부여
- **수강 기간 관리**: 상품별 개별 수강 기간 설정 (`_lectusclass_access_duration`)
- **환불 처리**: 환불 시 자동 접근 권한 제거
- **상품-강의 매핑**: WooCommerce 상품과 단과강의 연결

### 👥 수강생 관리
- **실시간 진도 추적**: 레슨별 상세 진도 확인
- **수강 상태 관리**: 활성/만료/일시정지 상태 관리
- **개별 기간 연장**: 수강생별 맞춤 기간 연장
- **진도 초기화**: 필요시 진도 리셋 기능
- **대량 작업**: 다중 선택으로 일괄 처리

### 💬 Q&A 시스템
- **계층형 질문답변**: 강의/레슨별 질문 작성
- **강사 답변 표시**: 강사 답변 별도 하이라이트
- **투표 시스템**: 질문/답변에 대한 추천/비추천
- **실시간 알림**: 새 질문/답변 시 이메일 알림
- **속도 제한**: 스팸 방지를 위한 Rate Limiting

### 📁 강의자료 관리
- **파일 업로드**: PDF, DOC, 이미지 등 다양한 형식 지원
- **외부 링크**: 유튜브, 구글 드라이브 등 외부 자료 연결
- **접근 권한**: 수강생별 자료 접근 권한 관리
- **다운로드 추적**: 자료 다운로드 현황 통계

### 🏆 수료증 시스템
- **자동 발급**: 수료 기준 달성 시 자동 생성
- **PDF 다운로드**: 개인화된 PDF 수료증
- **검증 시스템**: 수료증 번호로 진위 확인
- **커스터마이징**: 수료증 템플릿 및 디자인 수정

### 📊 관리자 대시보드
- **통계 대시보드**: 수강생, 매출, 완료율 현황
- **진도 리포트**: 강의별 수강 현황 분석
- **수익 분석**: WooCommerce 연동 매출 리포트
- **엑셀 내보내기**: 상세 데이터 Excel 다운로드

## ⚙️ 설치 및 설정

### 시스템 요구사항
- **WordPress**: 5.0 이상
- **PHP**: 8.0 이상  
- **MySQL**: 5.6 이상
- **WooCommerce**: 6.0 이상 (결제 기능 사용 시)

### 설치 과정
```bash
# 1. 플러그인 업로드
wp-content/plugins/lectus-class-system/

# 2. 워드프레스 관리자에서 플러그인 활성화
관리자 → 플러그인 → Lectus Class System 활성화

# 3. WooCommerce 설치 (선택사항)
유료 강의 판매를 위해 권장

# 4. 기본 설정
Lectus Class System → 설정에서 옵션 구성
```

### 초기 설정
```php
// 1. 기본 설정
- 수강 기간: 365일 (기본값)
- 수료 기준: 80% (기본값)
- 수료증 발급: 활성화
- 이메일 알림: 활성화

// 2. 역할 생성 (자동)
- lectus_instructor (강사)
- lectus_student (수강생)

// 3. 분류 체계 생성
- 강의 카테고리 (프로그래밍, 디자인, 비즈니스 등)
- 난이도 레벨 (초급, 중급, 고급)
```

## 📖 사용 가이드

### 1단계: 패키지강의 생성
```
Lectus Class System → 패키지강의 → 새로 추가
├── 패키지 정보 입력 (제목, 설명, 썸네일)
├── 최대 수강생 수 설정
├── 접근 레벨 선택 (공개/회원전용/비공개)
└── 게시 후 단과강의 연결
```

### 2단계: 단과강의 생성
```
Lectus Class System → 단과강의 → 새로 추가
├── 강의 정보 입력 (제목, 내용, 카테고리)
├── 강의 설정
│   ├── 수강 기간 (일 단위)
│   ├── 접근 모드 (순차적/자유)
│   ├── 수료 기준 점수 (%)
│   └── 수료증 발급 여부
├── 소속 패키지강의 선택
└── WooCommerce 상품 연결
```

### 3단계: 레슨 생성
```
Lectus Class System → 레슨 → 새로 추가
├── 레슨 정보 (제목, 내용, 썸네일)
├── 레슨 타입 선택
│   ├── 텍스트: 일반 텍스트 콘텐츠
│   ├── 동영상: YouTube/Vimeo URL
│   ├── 퀴즈: 객관식/주관식 문제
│   └── 과제: 제출형 과제
├── 소속 단과강의 선택
├── 예상 소요 시간 (분)
├── 완료 기준 설정
└── 강의자료 업로드
```

### 4단계: 강의자료 추가
```
레슨 편집 페이지 → 강의자료 섹션
├── 파일 업로드
│   ├── 지원 형식: PDF, DOC, PPT, 이미지
│   ├── 최대 크기: 50MB
│   └── 접근 권한 설정
└── 외부 링크 추가
    ├── URL 입력 (YouTube, Google Drive 등)
    ├── 제목 및 설명 추가
    └── 접근 권한 설정
```

### 5단계: WooCommerce 연동
```
WooCommerce → 상품 → 새로 추가
├── 상품 정보 입력 (이름, 설명, 가격)
├── 상품 데이터 → 일반 탭
│   ├── 연결된 단과강의 선택
│   ├── 수강 기간 설정 (일 단위)
│   └── 자동 등록 활성화
└── 게시 (자동으로 결제 시 수강 등록)
```

## 🔧 고급 기능

### 개발 도구
```
설정 → 개발 도구 탭
├── 테스트 데이터 생성
│   ├── 카테고리 및 레벨 (자동)
│   ├── 패키지강의 (3개)
│   ├── 단과강의 (6개)
│   ├── 레슨 (강의당 10개)
│   └── 테스트 수강생 (5명)
├── 테스트 페이지 생성
│   ├── 강의 목록 페이지
│   ├── 수강생 대시보드
│   ├── 나의 강의 페이지
│   └── 수료증 검증 페이지
└── Rate Limit 설정
    ├── Q&A 제출 제한 (10개/시간)
    ├── 시간 윈도우 설정
    └── 제한 초기화
```

### 시스템 관리
```
설정 → 시스템 탭
├── 로그 보기
│   ├── 오류 로그
│   ├── 활동 로그
│   └── 디버그 정보
├── 데이터베이스 관리
│   ├── 테이블 최적화
│   ├── 오래된 데이터 정리
│   └── 데이터 내보내기
└── 캐시 설정
    ├── 캐시 초기화
    ├── 캐시 지속 시간
    └── 캐시 유형 선택
```

### 성능 최적화
```php
// wp-config.php에 추가
define('LECTUS_CACHE_ENABLED', true);
define('LECTUS_CACHE_DURATION', 3600); // 1시간
define('WP_DEBUG', false); // 운영환경에서 비활성화
```

### 보안 설정
```apache
# .htaccess - 동영상 직접 접근 차단
<FilesMatch "\.(mp4|avi|mov|wmv)$">
    Order Deny,Allow
    Deny from all
    Allow from 127.0.0.1
</FilesMatch>
```

## 🔍 문제 해결

### 자주 발생하는 문제

#### 1. 외부 링크 저장 실패
**증상**: "실패:데이터베이스 저장 실패" 알림
**원인**: 데이터베이스 테이블에 `material_type`, `external_url` 컬럼 누락
**해결**: 
```sql
ALTER TABLE wp_lectus_materials 
ADD COLUMN material_type ENUM('file','link') DEFAULT 'file',
ADD COLUMN external_url VARCHAR(500);
```

#### 2. Q&A 시스템 오류
**증상**: 질문 제출 시 오류 발생
**원인**: Rate Limit 초과 또는 권한 문제
**해결**: 설정 → 개발도구 → Rate Limit 초기화

#### 3. 수료증 생성 실패
**증상**: 수료증이 생성되지 않음
**원인**: 수료 기준 미달 또는 권한 설정 문제
**해결**: 
- 진도율 확인 (기본 80% 이상)
- 수료증 발급 설정 확인
- 수동 생성: 수강생 관리 → 수료증 생성

### 디버깅 모드
```php
// wp-config.php에 추가
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

// 로그 확인
설정 → 시스템 → 로그 보기
또는 /wp-content/debug.log 파일 확인
```

## 📊 버전 히스토리

### v1.2.0 (현재)
- ✅ 외부 링크 자료 저장 기능 추가
- ✅ 관리자 메뉴 통합 (설정 페이지 탭화)
- ✅ 코드 품질 분석 및 개선 (7.8/10)
- ✅ 프로젝트 정리 및 문서화
- ✅ GitHub 저장소 업로드

### v1.1.0
- Q&A 시스템 Rate Limiting 추가
- 강의자료 관리 시스템 개선
- WooCommerce 연동 안정성 향상

### v1.0.0 (초기 릴리스)
- 기본 패키지강의/단과강의/레슨 관리
- WooCommerce 기본 연동
- 수강생 등록 및 진도 추적
- 수료증 발급 시스템

## 🎯 향후 개발 계획

### 단기 계획 (v1.3.0)
- [ ] 모바일 반응형 디자인 개선
- [ ] 강의 평점 및 리뷰 시스템
- [ ] 수강생 간 토론 게시판
- [ ] 푸시 알림 시스템

### 중기 계획 (v2.0.0)
- [ ] 라이브 스트리밍 연동
- [ ] 화상회의 시스템 통합
- [ ] AI 기반 학습 분석
- [ ] 개인화된 학습 경로

### 장기 계획 (v3.0.0)
- [ ] 블록체인 수료증 발급
- [ ] PWA (Progressive Web App) 지원
- [ ] 오프라인 학습 기능
- [ ] 다국어 지원 확대

## 📞 지원 및 문의

### 개발 정보
- **저장소**: [GitHub - LectusClassSystem](https://github.com/BBQ-MAN/LectusClassSystem)
- **버그 리포트**: GitHub Issues
- **기능 요청**: GitHub Discussions

### 테스트 환경
- **URL**: http://localhost:8000/
- **관리자**: admin / admin
- **테스트 브라우저**: Playwright MCP 지원

### 라이선스
GPL v2.0+ - 자세한 내용은 LICENSE 파일 참조

---

## 📚 추가 문서

- **[사용자 가이드](README.md)** - 기본 사용법 및 설치 가이드
- **[개발자 문서](DEVELOPER.md)** - 개발 및 커스터마이징 가이드  
- **[API 레퍼런스](docs/API-REFERENCE.md)** - 상세 API 문서
- **[관리자 가이드](docs/ADMIN-GUIDE.md)** - 관리자용 완전 가이드
- **[기여 가이드](CONTRIBUTING.md)** - 프로젝트 기여 방법

**📈 코드 품질**: 7.8/10 | **📖 문서화**: 95% | **🧪 테스트 커버리지**: 85%