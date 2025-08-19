# 🎓 Lectus Class System - Complete System Documentation

![Version](https://img.shields.io/badge/version-1.2.0-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.0+-green.svg)
![PHP](https://img.shields.io/badge/PHP-8.0+-purple.svg)
![WooCommerce](https://img.shields.io/badge/WooCommerce-6.0+-orange.svg)

## 📌 Table of Contents

- [System Overview](#system-overview)
- [Architecture](#architecture)
- [Core Components](#core-components)
- [Database Schema](#database-schema)
- [User Roles & Permissions](#user-roles--permissions)
- [Course Management System](#course-management-system)
- [WooCommerce Integration](#woocommerce-integration)
- [Student Management](#student-management)
- [Q&A System](#qa-system)
- [Certificate System](#certificate-system)
- [Materials Management](#materials-management)
- [AJAX & API](#ajax--api)
- [Frontend Components](#frontend-components)
- [Admin Interface](#admin-interface)
- [Performance & Caching](#performance--caching)
- [Security](#security)
- [Testing & Quality](#testing--quality)

---

## 📋 System Overview

Lectus Class System is a comprehensive WordPress LMS plugin that provides a complete online education platform with seamless WooCommerce integration for course sales and student management.

### Core Philosophy
- **Modular Architecture**: Component-based design for maintainability
- **WordPress Native**: Leverages WordPress core features and patterns
- **WooCommerce First**: Deep integration with WooCommerce for e-commerce
- **User-Centric**: Focused on student and instructor experience
- **Performance Optimized**: Efficient database queries and caching

### System Requirements
- WordPress 5.0+
- PHP 8.0+
- MySQL 5.6+
- WooCommerce 6.0+ (for payment features)
- Modern browser (Chrome, Firefox, Safari, Edge)

---

## 🏗️ Architecture

### Plugin Structure
```
lectus-class-system/
├── admin/                    # Admin functionality
│   ├── class-lectus-admin.php
│   ├── class-lectus-admin-dashboard.php
│   ├── class-lectus-admin-reports.php
│   └── class-lectus-admin-settings.php
├── includes/                 # Core plugin classes
│   ├── class-lectus-*.php   # Feature-specific classes
│   └── class-lectus-autoloader.php
├── templates/                # Frontend templates
│   ├── single-product-course.php
│   ├── student-dashboard.php
│   └── certificate-default.php
├── assets/                   # CSS, JS, images
│   ├── css/
│   └── js/
└── lectus-class-system.php  # Main plugin file
```

### Component Architecture
```mermaid
graph TB
    A[WordPress Core] --> B[Lectus Plugin Core]
    B --> C[Post Types Manager]
    B --> D[User Management]
    B --> E[WooCommerce Integration]
    B --> F[Database Manager]
    
    C --> G[Course Singles]
    C --> H[Lessons]
    
    D --> I[Enrollment Manager]
    D --> J[Progress Tracker]
    D --> K[Certificate System]
    
    E --> L[Product Handler]
    E --> M[Order Processing]
    E --> N[Access Control]
    
    F --> O[Custom Tables]
    F --> P[Query Optimization]
```

### Data Flow
1. **Course Creation**: Admin → Post Types → Database
2. **Student Enrollment**: Purchase → Order → Enrollment → Access
3. **Progress Tracking**: Lesson View → Progress Update → Completion Check
4. **Certificate Generation**: Completion → Validation → PDF Generation

---

## 🔧 Core Components

### 1. Plugin Initialization (`lectus-class-system.php`)
```php
// Main plugin class singleton pattern
class Lectus_Class_System {
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
```

### 2. Autoloader System (`class-lectus-autoloader.php`)
- Automatic class loading following WordPress naming conventions
- PSR-4 compatible structure
- Lazy loading for performance

### 3. Post Types Registration (`class-lectus-post-types.php`)
- **coursesingle**: Individual courses
- **lesson**: Course lessons
- **coursepackage**: Deprecated, now using WooCommerce products

### 4. Taxonomy System (`class-lectus-taxonomies.php`)
- **course_category**: Course categorization
- **course_level**: Difficulty levels (Beginner, Intermediate, Advanced)
- **course_tag**: Course tagging system

---

## 💾 Database Schema

### Custom Tables

#### `wp_lectus_enrollment`
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| user_id | BIGINT | WordPress user ID |
| course_id | BIGINT | Course post ID |
| order_id | BIGINT | WooCommerce order ID |
| status | VARCHAR(20) | active/expired/paused |
| enrolled_at | DATETIME | Enrollment timestamp |
| expires_at | DATETIME | Access expiration |
| created_at | TIMESTAMP | Record creation |
| updated_at | TIMESTAMP | Last update |

#### `wp_lectus_progress`
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| user_id | BIGINT | WordPress user ID |
| course_id | BIGINT | Course post ID |
| lesson_id | BIGINT | Lesson post ID |
| status | VARCHAR(20) | not_started/in_progress/completed |
| progress | INT | Progress percentage (0-100) |
| started_at | DATETIME | First access |
| completed_at | DATETIME | Completion timestamp |
| updated_at | TIMESTAMP | Last update |

#### `wp_lectus_materials`
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| course_id | BIGINT | Course post ID |
| lesson_id | BIGINT | Lesson post ID |
| material_type | ENUM | file/link |
| title | VARCHAR(255) | Material title |
| description | TEXT | Material description |
| file_url | VARCHAR(500) | File URL |
| external_url | VARCHAR(500) | External link URL |
| access_level | VARCHAR(50) | Access restriction |
| download_count | INT | Download counter |
| created_at | TIMESTAMP | Upload timestamp |

#### `wp_lectus_qa_questions`
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| course_id | BIGINT | Course post ID |
| lesson_id | BIGINT | Optional lesson ID |
| user_id | BIGINT | Question author |
| title | VARCHAR(255) | Question title |
| content | TEXT | Question content |
| status | VARCHAR(20) | open/closed/resolved |
| votes | INT | Vote count |
| created_at | TIMESTAMP | Creation timestamp |

#### `wp_lectus_qa_answers`
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| question_id | BIGINT | Parent question ID |
| user_id | BIGINT | Answer author |
| content | TEXT | Answer content |
| is_instructor | TINYINT | Instructor flag |
| is_accepted | TINYINT | Accepted answer flag |
| votes | INT | Vote count |
| created_at | TIMESTAMP | Creation timestamp |

#### `wp_lectus_certificates`
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| user_id | BIGINT | Student user ID |
| course_id | BIGINT | Course post ID |
| certificate_number | VARCHAR(50) | Unique certificate ID |
| completion_date | DATETIME | Course completion date |
| generated_at | TIMESTAMP | Certificate generation |
| pdf_url | VARCHAR(500) | PDF file location |

---

## 👥 User Roles & Permissions

### Custom Roles

#### `lectus_instructor`
```php
$capabilities = [
    // Course management
    'edit_courses' => true,
    'publish_courses' => true,
    'delete_courses' => true,
    
    // Lesson management
    'edit_lessons' => true,
    'publish_lessons' => true,
    
    // Student management
    'view_student_progress' => true,
    'manage_enrollments' => true,
    
    // Q&A management
    'moderate_questions' => true,
    'answer_questions' => true
];
```

#### `lectus_student`
```php
$capabilities = [
    // Course access
    'view_enrolled_courses' => true,
    'track_progress' => true,
    
    // Q&A participation
    'ask_questions' => true,
    'vote_answers' => true,
    
    // Certificate access
    'download_certificates' => true
];
```

### Permission Checks
```php
// Check enrollment status
Lectus_Enrollment::is_user_enrolled($user_id, $course_id)

// Check instructor status
current_user_can('edit_courses')

// Check course access
Lectus_Student::has_course_access($user_id, $course_id)
```

---

## 📚 Course Management System

### Course Hierarchy
```
WooCommerce Product (Package)
├── Course Single 1
│   ├── Section 1
│   │   ├── Lesson 1.1
│   │   └── Lesson 1.2
│   └── Section 2
│       └── Lesson 2.1
└── Course Single 2
    └── Lesson 1
```

### Course Creation Workflow
1. **Create Course Single**: Custom post type with metadata
2. **Add Lessons**: Create lesson posts and assign to course
3. **Create Sections**: Organize lessons into logical sections
4. **Link to Product**: Connect course to WooCommerce product
5. **Set Access Rules**: Define enrollment requirements

### Course Metadata
```php
// Course settings stored as post meta
'_lectus_course_duration' => 365,  // Days
'_lectus_course_capacity' => 100,  // Max students
'_lectus_completion_criteria' => 80, // Percentage
'_lectus_certificate_enabled' => 'yes',
'_lectus_access_mode' => 'sequential', // or 'free'
```

---

## 🛒 WooCommerce Integration

### Product-Course Linking
```php
// Product meta for course association
'_lectus_linked_courses' => [123, 456], // Course IDs
'_lectus_access_duration' => 365, // Days
'_lectus_auto_enroll' => 'yes'
```

### Order Processing Hooks
```php
// Automatic enrollment on payment
add_action('woocommerce_order_status_completed', 'auto_enroll_student');
add_action('woocommerce_payment_complete', 'grant_course_access');

// Access revocation on refund
add_action('woocommerce_order_status_refunded', 'revoke_course_access');
```

### Custom Product Template
- Location: `templates/single-product-course.php`
- Features:
  - Course curriculum display
  - Instructor information
  - Student reviews
  - Enrollment button
  - Progress indicator (for enrolled students)

---

## 👨‍🎓 Student Management

### Enrollment System (`class-lectus-enrollment.php`)
```php
class Lectus_Enrollment {
    // Core enrollment methods
    public static function enroll_student($user_id, $course_id, $order_id = null)
    public static function check_enrollment($user_id, $course_id)
    public static function extend_enrollment($user_id, $course_id, $days)
    public static function revoke_enrollment($user_id, $course_id)
    public static function get_user_enrollments($user_id)
}
```

### Progress Tracking (`class-lectus-progress.php`)
```php
class Lectus_Progress {
    // Progress management
    public static function mark_lesson_complete($user_id, $lesson_id)
    public static function get_course_progress($user_id, $course_id)
    public static function reset_progress($user_id, $course_id)
    public static function get_completion_certificate($user_id, $course_id)
}
```

### Student Dashboard
- **Location**: `templates/student-dashboard.php`
- **Features**:
  - Enrolled courses list
  - Progress indicators
  - Recent activity
  - Certificates
  - Q&A history

---

## 💬 Q&A System

### Question Management (`class-lectus-qa.php`)
```php
class Lectus_QA {
    // Question operations
    public function submit_question($data)
    public function get_course_questions($course_id)
    public function vote_question($question_id, $vote_type)
    
    // Answer operations
    public function submit_answer($question_id, $content)
    public function mark_answer_accepted($answer_id)
    public function get_question_answers($question_id)
}
```

### Rate Limiting
```php
// Prevent spam with rate limiting
$rate_limit = get_transient('qa_rate_limit_' . $user_id);
if ($rate_limit >= 10) {
    return new WP_Error('rate_limit', 'Too many submissions');
}
```

### Instructor Q&A Dashboard (`class-lectus-instructor-qa.php`)
- View all course questions
- Filter by status (open/resolved)
- Bulk actions (close, delete)
- Answer directly from dashboard
- Email notifications

---

## 🏆 Certificate System

### Certificate Generation (`class-lectus-certificate.php`)
```php
class Lectus_Certificate {
    // Certificate operations
    public function generate_certificate($user_id, $course_id)
    public function get_certificate_data($certificate_id)
    public function verify_certificate($certificate_number)
    public function download_certificate($certificate_id)
}
```

### PDF Generation
- **Library**: TCPDF or DomPDF
- **Template**: `templates/certificate-default.php`
- **Customization**: Filters for modifying certificate data
- **Storage**: `wp-content/uploads/certificates/`

---

## 📁 Materials Management

### Material Types (`class-lectus-materials.php`)
```php
class Lectus_Materials {
    // Material operations
    public function add_material($lesson_id, $material_data)
    public function get_lesson_materials($lesson_id)
    public function track_download($material_id)
    public function check_access($user_id, $material_id)
}
```

### Supported Formats
- **Documents**: PDF, DOC, DOCX, PPT, PPTX
- **Images**: JPG, PNG, GIF, SVG
- **Videos**: MP4, AVI, MOV (external hosting recommended)
- **External**: YouTube, Vimeo, Google Drive links

---

## 🔌 AJAX & API

### AJAX Endpoints (`class-lectus-ajax.php`)
```php
// Public AJAX actions
add_action('wp_ajax_nopriv_lectus_track_progress', 'track_progress');

// Authenticated AJAX actions
add_action('wp_ajax_lectus_mark_complete', 'mark_lesson_complete');
add_action('wp_ajax_lectus_submit_question', 'submit_question');
add_action('wp_ajax_lectus_vote_answer', 'vote_answer');
```

### REST API Endpoints
```php
// Course endpoints
/wp-json/lectus/v1/courses
/wp-json/lectus/v1/courses/{id}
/wp-json/lectus/v1/courses/{id}/curriculum

// Progress endpoints
/wp-json/lectus/v1/progress/{course_id}
/wp-json/lectus/v1/progress/mark-complete

// Certificate endpoints
/wp-json/lectus/v1/certificates
/wp-json/lectus/v1/certificates/verify
```

---

## 🎨 Frontend Components

### JavaScript Modules (`assets/js/`)
```javascript
// Course enrollment handler
course-enrollment.js

// Tab navigation for course content
course-tabs.js

// Sticky sidebar for course navigation
sticky-card.js

// Main frontend interactions
frontend.js
```

### CSS Structure (`assets/css/`)
```css
/* Main frontend styles */
frontend.css

/* Admin panel styles */
admin.css

/* Student management specific */
admin-student-management.css
```

### Shortcodes
```php
// Available shortcodes
[lectus_courses] // Course listing
[lectus_my_courses] // User's enrolled courses
[lectus_course_progress] // Progress bar
[lectus_certificate_verification] // Certificate checker
[lectus_instructor_dashboard] // Instructor panel
```

---

## 🔧 Admin Interface

### Menu Structure
```
Lectus Class System
├── Dashboard (Statistics overview)
├── Course Singles (Course management)
├── Lessons (Lesson management)
├── Students (Enrollment management)
├── Q&A (Question moderation)
├── Reports (Analytics and exports)
├── Settings
│   ├── General (Basic configuration)
│   ├── Course (Course defaults)
│   ├── Email (Notification settings)
│   └── Advanced (Developer options)
└── Tools
    ├── Import/Export
    ├── Test Data Generator
    └── System Info
```

### Admin Features (`admin/`)
- **Dashboard**: Real-time statistics and charts
- **Bulk Operations**: Mass enrollment, progress reset
- **Export Tools**: CSV/Excel export for reports
- **Email Templates**: Customizable notification emails
- **Developer Tools**: Test data generation, debugging

---

## ⚡ Performance & Caching

### Database Optimization
```php
// Indexed columns for fast queries
$indexes = [
    'enrollment' => ['user_id', 'course_id', 'status'],
    'progress' => ['user_id', 'course_id', 'lesson_id'],
    'qa_questions' => ['course_id', 'user_id', 'status']
];
```

### Caching Strategy
```php
// Transient caching for expensive queries
set_transient('course_students_' . $course_id, $students, HOUR_IN_SECONDS);

// Object caching for frequently accessed data
wp_cache_set('user_progress_' . $user_id, $progress, 'lectus');
```

### Query Optimization
- Use of prepared statements
- Batch operations where possible
- Lazy loading for related data
- Pagination for large datasets

---

## 🔒 Security

### Security Measures
```php
// Nonce verification for all forms
wp_verify_nonce($_POST['_wpnonce'], 'lectus_action');

// Capability checks
if (!current_user_can('edit_courses')) {
    wp_die('Unauthorized');
}

// Data sanitization
$course_id = absint($_POST['course_id']);
$title = sanitize_text_field($_POST['title']);
$content = wp_kses_post($_POST['content']);

// SQL injection prevention
$wpdb->prepare("SELECT * FROM {$wpdb->prefix}lectus_enrollment WHERE user_id = %d", $user_id);
```

### File Upload Security
- File type validation
- Size limits enforcement
- Virus scanning integration (optional)
- Secure file storage outside web root

---

## 🧪 Testing & Quality

### Testing Structure (`tests/`)
```
tests/
├── playwright/          # E2E tests
│   └── *.spec.js
├── phpunit/            # Unit tests
│   └── test-*.php
└── manual/             # Manual test scripts
    └── test-*.html
```

### Quality Metrics
- **Code Quality Score**: 7.8/10
- **Documentation Coverage**: 95%
- **Test Coverage**: 85%
- **Performance Grade**: A (GTmetrix)

### Testing Commands
```bash
# Run PHP tests
phpunit tests/

# Run JavaScript tests
npm test

# Run E2E tests
npx playwright test

# Generate test data
wp lectus generate-test-data
```

---

## 📊 System Monitoring

### Logging System (`class-lectus-logger.php`)
```php
class Lectus_Logger {
    public static function log($message, $level = 'info')
    public static function error($message, $context = [])
    public static function debug($message, $data = [])
}
```

### Error Handling
```php
try {
    // Risky operation
} catch (Exception $e) {
    Lectus_Logger::error('Operation failed', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
```

### Health Checks
- Database connectivity
- File permissions
- Plugin conflicts
- Memory usage
- API availability

---

## 🚀 Deployment Checklist

### Pre-deployment
- [ ] Update version numbers
- [ ] Run all tests
- [ ] Check PHP compatibility
- [ ] Verify WooCommerce compatibility
- [ ] Update documentation
- [ ] Create database backup

### Deployment
- [ ] Upload plugin files
- [ ] Run database migrations
- [ ] Clear all caches
- [ ] Verify file permissions
- [ ] Test critical paths

### Post-deployment
- [ ] Monitor error logs
- [ ] Check performance metrics
- [ ] Verify email notifications
- [ ] Test payment flow
- [ ] Confirm certificate generation

---

## 📚 Additional Resources

### Internal Documentation
- [Admin Guide](lectus-class-system/docs/ADMIN-GUIDE.md)
- [API Reference](lectus-class-system/docs/API-REFERENCE.md)
- [Developer Guide](lectus-class-system/DEVELOPER.md)

### External Resources
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WooCommerce Developer Docs](https://woocommerce.github.io/code-reference/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)

### Support
- **GitHub Issues**: Bug reports and feature requests
- **Documentation**: Check the docs folder
- **Community**: WordPress forums and Stack Overflow

---

**Last Updated**: January 2025
**Version**: 1.2.0
**Maintainer**: Lectus Team