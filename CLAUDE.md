# Lectus Class System - WordPress 교육 관리 플러그인

![Version](https://img.shields.io/badge/version-1.2.0-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.0+-green.svg)
![PHP](https://img.shields.io/badge/PHP-8.0+-purple.svg)
![WooCommerce](https://img.shields.io/badge/WooCommerce-6.0+-orange.svg)
![License](https://img.shields.io/badge/license-GPL--2.0+-red.svg)

워드프레스용 전문 교육 서비스 플러그인 - 패키지강의 관리, WooCommerce 연동, 수강생 관리, 수료증 발급

## 📋 개요

Lectus Class System는 WordPress와 WooCommerce를 기반으로 한 전문 교육 서비스 관리 플러그인입니다. 패키지강의, 단과강의, 레슨을 체계적으로 관리하고, 수강생 등록부터 수료증 발급까지 완전한 온라인 교육 솔루션을 제공합니다.

### 🎯 주요 기능

- **체계적 콘텐츠 관리**: 패키지강의 → 단과강의 → 레슨 3단계 구조
- **WooCommerce 완전 통합**: 결제, 상품 관리, 자동 수강 등록
- **유연한 수강 기간 설정**: 상품별 개별 수강 기간 설정
- **포괄적 수강생 관리**: 진도 추적, 상태 관리, 기간 연장
- **자동 수료증 발급**: 완주 시 PDF 수료증 자동 생성
- **관리자 친화적 인터페이스**: 직관적인 관리 대시보드


## 🚀 핵심 기능

### 📚 단과강의 관리 시스템
- **패키지강의 → 단과강의 → 레슨** 계층 구조
- 상품별 유연한 수강 기간 설정 (일 단위/무제한)
- 순차적/자유 진행 모드
- 다양한 콘텐츠 타입 지원 (텍스트, 동영상, 퀴즈, 과제)

### 💳 WooCommerce 완벽 연동
- 결제 완료 시 자동 단과강의 및 패키지 강의에 포함된 단과 강의 접근 권한 부여
- 환불 시 자동 접근 권한 제거
- **상품별 수강 기간 설정** (`_lectusclass_access_duration` 메타필드)
- 상품별 단과강의 매핑

### 👥 수강생 관리
- 실시간 학습 진도 추적
- 수강생별 진도 초기화/리셋
- 수강 일시정지/재개 기능 
- 대량 수강생 관리 도구
- **개별 수강 기간 연장 기능** 

### 🏆 수료증 시스템
- 자동 수료증 발급
- 커스터마이징 가능한 템플릿
- PDF/HTML 다운로드
- 수료증 진위 확인

### 📊 분석 및 리포팅
- 단과강의별 통계 대시보드
- 수강생 성과 분석
- 매출 리포트
- 엑셀 내보내기

### 강의 질문 답변 시스템
- 사용자 역할 중 학생 강사로 나뉨
- 가입시 기본적으로 학생으로 등록
- 특정 강의에서 수강생이 문의하면 강사가 답변해주는 시스템
- 강의에 강사를 할당해서 해당 강사는 질문 답변 전용페이지에 접속해서 답변

## 🛠️ 설치 요구사항

- **WordPress**: 5.0 이상
- **PHP**: 8.0 이상
- **WooCommerce**: 6.0 이상 (결제 기능 사용 시)
- **MySQL**: 5.6 이상

## ⚡ 빠른 설치

1. **플러그인 업로드**
   ```bash
   # FTP 또는 관리자 패널을 통해 업로드
   wp-content/plugins/lectus-class-system/
   ```

2. **플러그인 활성화**
   - 워드프레스 관리자 → 플러그인 → Lectus Class System 활성화

3. **WooCommerce 설치** (선택사항)
   ```bash
   # 유료 단과강의 판매를 위해 필요
   플러그인 → 새로 추가 → WooCommerce 검색 및 설치
   ```

4. **기본 설정**
   - Lectus Class System → 설정에서 기본 옵션 구성

## 📖 사용 가이드

### 1단계: 패키지강의 생성
```
Lectus Class System → 패키지강의 → 새로 추가
- 패키지강의 제목 및 설명 입력
- 최대 수강생 수 설정
- 접근 레벨 선택 (공개/회원전용/비공개)
- 단과강의 리스트에서 선택해서 패키지 구성
```

### 2단계: 단과강의 생성  
```
Lectus Class System → 단과강의 → 새로 추가
- 단과강의 제목 및 내용 입력
- 소속 패키지강의 선택
- 수강 기간 설정
- 접근 모드 선택 (순차적/자유)
- 수료 기준 점수 설정
- 벌크 업로드 기능 : csv 레슨 리스트를 업로드하면 하나의 단과강의로 자동 생성
```

### 3단계: 레슨 생성
```
Lectus Class System → 레슨 → 새로 추가  
- 레슨 제목 및 내용 입력
- 소속 단과강의 선택
- 레슨 타입 선택 (텍스트/동영상/퀴즈/과제)
- 예상 소요 시간 입력
- 완료 기준 설정
- 벌크 업로드 기능 : csv 레슨 리스트를 업로드
```

### 4단계: WooCommerce 연동
```
WooCommerce → 상품 → 새로 추가
- 상품 정보 입력
- 상품 데이터 → 연결된 단과강의 선택
- 자동 접근 권한 부여 활성화
```

### 5단계: 수강생 관리
```
Lectus Class System → 수강생 관리
- 수강생 목록 확인
- 진도 추적 및 관리
- 일괄 작업 실행
```

## 🎨 커스터마이징

### 템플릿 커스터마이징

### 훅과 필터 활용

### 쇼트코드 사용

## 🔧 고급 설정

### 성능 최적화
```php
// wp-config.php에 추가
define('EDUCLASS_CACHE_ENABLED', true);
define('EDUCLASS_CACHE_DURATION', 3600); // 1시간
```

### 보안 설정
```php
// 동영상 다운로드 방지 (htaccess)
<FilesMatch "\.(mp4|avi|mov|wmv)$">
    Order Deny,Allow
    Deny from all
    Allow from 127.0.0.1
</FilesMatch>
```

### 다국어 지원
# 언어 파일 위치
wp-content/plugins/lectus-class-system/languages/

# 지원 언어
- 한국어 (ko_KR) - 기본
- 영어 (en_US)

### 디버깅 모드


### 커스텀 포스트 타입
- `coursepackage` - 패키지강의 (묶음 단위)
- `coursesingle` - 단과강의 (실제 교육과정)  
- `lesson` - 레슨 (개별 수업 단위)

### v1.0.0 (초기 릴리스)
- 기본 패키지강의/단과강의/레슨 관리
- WooCommerce 기본 연동  
- 수강생 관리자 등록,제거 및 진도 추적
- 수료증 발급 시스템

## 🎯 향후 개발 계획
- [ ] 단과강의 리뷰 및 평점 시스템
- [ ] 수강생 간 토론 게시판
- [ ] 모바일 앱 API 지원
- [ ] 다국어 지원 확대
- [ ] 라이브 스트리밍 연동
- [ ] 화상회의 시스템 통합
- [ ] AI 기반 학습 분석
- [ ] 개인화된 학습 경로
- [ ] 화상회의 완전 통합
- [ ] 블록체인 수료증
- [ ] PWA 지원
- [ ] 오프라인 학습 기능


### 테스트 서버 정보 (playwright mcp 사용)
- [localhost:8000](http://localhost:8000/)
- admin/admin