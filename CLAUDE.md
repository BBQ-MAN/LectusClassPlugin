# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

# Lectus Class System - WordPress LMS Plugin

WordPress LMS plugin with WooCommerce integration for online education. Manages course packages, enrollments, progress tracking, Q&A system, and certificates.

## Common Development Commands

### Local Development
```bash
# Start Docker environment
docker-compose up -d

# Access WordPress
# URL: http://localhost:8000
# Admin: admin / admin
# phpMyAdmin: http://localhost:8081

# Enter WordPress container
docker exec -it lectusclasssystem_wordpress_1 bash

# Enter WP-CLI container
docker exec -it lectusclasssystem_wpcli_1 wp [command]
```

### Build & Development (in lectus-class-system/)
```bash
# Install dependencies
npm install

# Watch CSS changes (Tailwind)
npm run watch        # Watch all CSS files
npm run watch:css    # Watch frontend CSS
npm run watch:admin  # Watch admin CSS

# Production build
npm run build        # Build all CSS files with minification

# Development mode
npm run dev          # Alias for watch
```

### Testing
```bash
# Run Playwright tests (in lectus-class-system/tests/)
npm test                  # Run all tests
npm run test:headed       # Run with browser UI
npm run test:debug        # Debug mode
npm run test:ui          # Interactive UI mode

# Generate test data
wp eval-file lectus-class-system/admin/lectus-test-data.php
# Or via Admin UI: Lectus Class System → Settings → Dev Tools → Generate Test Data
```

## High-Level Architecture

### Project Structure
```
LectusClassSystem/
├── lectus-class-system/         # Core LMS plugin
│   ├── admin/                   # Admin functionality
│   ├── assets/                  # CSS/JS assets (uses Tailwind CSS)
│   ├── includes/                # Core classes and business logic
│   ├── templates/               # Template files
│   └── tests/                   # Playwright tests
├── lectus-academy-theme/        # WordPress theme (Inflearn-style)
├── docker-compose.yml           # Docker development environment
└── docs/                        # Documentation

```

### Content Hierarchy
- **WooCommerce Products**: Can be single courses or course packages
- **Course Single** (coursesingle): Individual course with lessons
- **Lesson** (lesson): Individual learning units within a course
- **Course Package**: WooCommerce product containing multiple courses

### Key Database Tables
```sql
wp_lectus_enrollment     -- User course enrollments
wp_lectus_progress       -- Lesson progress tracking
wp_lectus_materials      -- Course materials (files and external links)
wp_lectus_qa_questions   -- Q&A questions
wp_lectus_qa_answers     -- Q&A answers
wp_lectus_certificates   -- Certificate records
```

### User Roles
- **lectus_instructor**: Can create courses, answer questions, manage students
- **lectus_student**: Can enroll, track progress, ask questions

## Core Plugin Architecture

### Main Classes and Responsibilities

**Core System**
- `Lectus_Class_System` (lectus-class-system.php): Main plugin singleton, handles initialization
- `Lectus_Post_Types`: Registers coursesingle and lesson post types
- `Lectus_Taxonomies`: Manages course categories and taxonomies
- `Lectus_Capabilities`: Handles user roles and permissions

**WooCommerce Integration**
- `Lectus_WooCommerce`: Product creation, order handling, enrollment automation
- Handles both single courses and package products
- Auto-enrolls users on successful payment
- Manages access duration via `_lectusclass_access_duration` meta

**Student Management**
- `Lectus_Enrollment`: Manages course enrollments and access
- `Lectus_Progress`: Tracks lesson completion and progress
- `Lectus_Student`: Student dashboard and profile management
- `Lectus_Certificate`: PDF certificate generation and verification

**Content & Materials**
- `Lectus_Materials`: File uploads and external link management
- `Lectus_QA`: Q&A system with voting and rate limiting
- `Lectus_Rate_Limiter`: Prevents spam submissions
- `Lectus_Sections`: Course curriculum organization

**Admin & UI**
- `Lectus_Admin`: Admin interface and settings
- `Lectus_Admin_Dashboard`: Statistics and reports
- `Lectus_Templates`: Template loading and rendering
- `Lectus_Shortcodes`: Shortcode handlers for frontend display

### AJAX Endpoints
All AJAX handlers use WordPress nonce verification:
- `lectus_update_lesson_progress`: Update lesson progress
- `lectus_complete_lesson`: Mark lesson as complete
- `lectus_enroll_course`: Manual enrollment
- `lectus_submit_question`: Q&A question submission
- `lectus_upload_material`: Material upload
- `lectus_save_external_link`: Save external resource link

## Key Development Patterns

### Plugin Initialization Flow
1. Plugin loads via `lectus-class-system.php`
2. Autoloader registers class files from `includes/`
3. Post types and taxonomies register on `init` hook
4. WooCommerce integration loads if WooCommerce is active
5. Admin menus register on `admin_menu` hook
6. Frontend assets enqueue on `wp_enqueue_scripts`

### WooCommerce Product Integration
- Products can be linked to single courses via `_course_id` meta
- Package products store multiple course IDs in `_package_course_ids` meta
- Access duration stored in `_lectusclass_access_duration` meta (days)
- Enrollment triggers on `woocommerce_order_status_completed` hook
- Unenrollment triggers on `woocommerce_order_status_refunded` hook

### Frontend Template Loading
Templates load in this priority order:
1. Theme directory: `lectus/template-name.php`
2. Plugin directory: `templates/template-name.php`
3. Uses `Lectus_Templates::get_template()` for loading

## Important Notes

### Security Considerations
- All AJAX requests require nonce verification (`wp_verify_nonce`)
- File uploads validate against allowed mime types
- User capabilities checked before all admin operations
- SQL queries use prepared statements via `$wpdb->prepare()`

### Performance Optimization
- Uses WordPress transients for caching frequently accessed data
- Lazy loads course materials only when needed
- Implements pagination for large data sets
- Rate limiting on Q&A submissions to prevent spam

### Known Issues & Solutions
- **External link save failure**: Ensure `material_type` and `external_url` columns exist in `wp_lectus_materials` table
- **Q&A submission errors**: Check rate limit settings or reset via Settings → Dev Tools
- **Certificate generation failure**: Verify progress is above completion threshold (default 80%)

### Debugging & Troubleshooting

```php
// Enable debug mode in wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

// View logs at /wp-content/debug.log
```

### Common Database Fixes

```sql
-- Fix missing material columns
ALTER TABLE wp_lectus_materials 
ADD COLUMN material_type ENUM('file','link') DEFAULT 'file',
ADD COLUMN external_url VARCHAR(500);

-- Reset rate limiting
DELETE FROM wp_options WHERE option_name LIKE 'lectus_rate_limit_%';
```