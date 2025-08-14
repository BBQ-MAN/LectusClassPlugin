# 👨‍💻 Lectus Class System - Developer Documentation

## 📁 Project Structure

```
lectus-class-system/
├── admin/                  # Admin functionality
│   ├── class-lectus-admin.php
│   ├── class-lectus-admin-dashboard.php
│   ├── class-lectus-admin-reports.php
│   └── class-lectus-admin-settings.php
├── assets/                 # Frontend assets
│   ├── css/
│   │   ├── admin.css
│   │   └── frontend.css
│   └── js/
│       ├── admin.js
│       └── frontend.js
├── includes/              # Core classes
│   ├── class-lectus-ajax.php
│   ├── class-lectus-autoloader.php
│   ├── class-lectus-bulk-upload.php
│   ├── class-lectus-capabilities.php
│   ├── class-lectus-certificate.php
│   ├── class-lectus-enrollment.php
│   ├── class-lectus-logger.php
│   ├── class-lectus-materials.php
│   ├── class-lectus-post-types.php
│   ├── class-lectus-progress.php
│   ├── class-lectus-qa.php
│   ├── class-lectus-shortcodes.php
│   ├── class-lectus-student.php
│   ├── class-lectus-taxonomies.php
│   ├── class-lectus-templates.php
│   └── class-lectus-woocommerce.php
├── languages/             # Translation files
├── templates/             # Template files
│   ├── single-course.php
│   ├── single-lesson.php
│   ├── student-dashboard.php
│   └── test-pages.php
├── tests/                 # Test files
└── lectus-class-system.php  # Main plugin file
```

## 🏗️ Architecture Overview

### Design Patterns

#### Singleton Pattern
Main plugin class uses singleton for single instance:
```php
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

#### Factory Pattern
Post types and taxonomies use factory pattern:
```php
Lectus_Post_Types::register_post_type('coursepackage', $args);
Lectus_Taxonomies::register_taxonomy('course_category', $args);
```

#### Observer Pattern
WordPress hooks and filters implement observer pattern:
```php
add_action('init', array($this, 'init'));
add_filter('the_content', array($this, 'filter_content'));
```

### Class Responsibilities

| Class | Responsibility | Key Methods |
|-------|---------------|-------------|
| `Lectus_Ajax` | AJAX request handling | `update_lesson_progress()`, `complete_lesson()` |
| `Lectus_Enrollment` | Student enrollment management | `enroll()`, `unenroll()`, `is_enrolled()` |
| `Lectus_Progress` | Progress tracking | `update_progress()`, `get_progress()` |
| `Lectus_Certificate` | Certificate generation | `generate()`, `verify()` |
| `Lectus_QA` | Q&A system | `submit_question()`, `submit_answer()` |
| `Lectus_Materials` | Course materials | `upload_material()`, `get_materials()` |
| `Lectus_WooCommerce` | WooCommerce integration | `create_product()`, `handle_order()` |

## 🔌 Hooks & Filters Reference

### Actions

#### Enrollment Actions
```php
// When student is enrolled
do_action('lectus_student_enrolled', $user_id, $course_id, $order_id);

// When student is unenrolled
do_action('lectus_student_unenrolled', $user_id, $course_id);

// Before enrollment check
do_action('lectus_before_enrollment_check', $user_id, $course_id);
```

#### Progress Actions
```php
// When lesson is completed
do_action('lectus_lesson_completed', $user_id, $course_id, $lesson_id);

// When course is completed
do_action('lectus_course_completed', $user_id, $course_id);

// When progress is updated
do_action('lectus_progress_updated', $user_id, $course_id, $lesson_id, $progress);
```

#### Certificate Actions
```php
// When certificate is generated
do_action('lectus_certificate_generated', $certificate_id, $user_id, $course_id);

// Before certificate generation
do_action('lectus_before_certificate_generation', $user_id, $course_id);
```

#### Q&A Actions
```php
// When question is submitted
do_action('lectus_qa_question_submitted', $question_id, $user_id);

// When answer is submitted
do_action('lectus_qa_answer_submitted', $answer_id, $question_id, $user_id);

// When vote is cast
do_action('lectus_qa_vote_cast', $post_id, $user_id, $vote_type);
```

### Filters

#### Access Control Filters
```php
// Filter course access permission
$can_access = apply_filters('lectus_can_access_course', $can_access, $user_id, $course_id);

// Filter lesson access permission
$can_access = apply_filters('lectus_can_access_lesson', $can_access, $user_id, $lesson_id);

// Filter enrollment duration
$duration = apply_filters('lectus_enrollment_duration', $duration, $course_id, $user_id);
```

#### Content Filters
```php
// Filter course content
$content = apply_filters('lectus_course_content', $content, $course_id);

// Filter lesson content
$content = apply_filters('lectus_lesson_content', $content, $lesson_id);

// Filter certificate data
$data = apply_filters('lectus_certificate_data', $data, $user_id, $course_id);
```

#### Display Filters
```php
// Filter progress display
$progress_html = apply_filters('lectus_progress_display', $progress_html, $progress);

// Filter course list
$courses = apply_filters('lectus_course_list', $courses, $args);

// Filter student dashboard
$dashboard = apply_filters('lectus_student_dashboard', $dashboard, $user_id);
```

## 💾 Database Schema

### Custom Tables

#### lectus_enrollment
```sql
CREATE TABLE {prefix}_lectus_enrollment (
    id BIGINT(20) PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT(20) NOT NULL,
    course_id BIGINT(20) NOT NULL,
    order_id BIGINT(20) DEFAULT NULL,
    status VARCHAR(20) DEFAULT 'active',
    enrolled_at DATETIME NOT NULL,
    expires_at DATETIME DEFAULT NULL,
    KEY user_id (user_id),
    KEY course_id (course_id),
    KEY order_id (order_id)
);
```

#### lectus_progress
```sql
CREATE TABLE {prefix}_lectus_progress (
    id BIGINT(20) PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT(20) NOT NULL,
    course_id BIGINT(20) NOT NULL,
    lesson_id BIGINT(20) NOT NULL,
    status VARCHAR(20) DEFAULT 'not_started',
    progress INT(3) DEFAULT 0,
    started_at DATETIME DEFAULT NULL,
    completed_at DATETIME DEFAULT NULL,
    KEY user_id (user_id),
    KEY course_id (course_id),
    KEY lesson_id (lesson_id)
);
```

#### lectus_materials
```sql
CREATE TABLE {prefix}_lectus_materials (
    id BIGINT(20) PRIMARY KEY AUTO_INCREMENT,
    course_id BIGINT(20) NOT NULL,
    lesson_id BIGINT(20) DEFAULT NULL,
    material_type ENUM('file','link') DEFAULT 'file',
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_url VARCHAR(500),
    external_url VARCHAR(500),
    file_size BIGINT(20),
    mime_type VARCHAR(100),
    access_level VARCHAR(50) DEFAULT 'enrolled',
    download_count INT(11) DEFAULT 0,
    uploaded_by BIGINT(20) NOT NULL,
    uploaded_at DATETIME NOT NULL,
    KEY course_id (course_id),
    KEY lesson_id (lesson_id)
);
```

## 🔐 Security Best Practices

### Nonce Verification
All AJAX requests verify nonces:
```php
if (!wp_verify_nonce($_POST['nonce'], 'lectus-ajax-nonce')) {
    wp_send_json_error(array('message' => 'Security check failed'));
}
```

### Capability Checks
All admin actions check capabilities:
```php
if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}
```

### Data Sanitization
All user input is sanitized:
```php
$course_id = absint($_POST['course_id']);
$title = sanitize_text_field($_POST['title']);
$content = wp_kses_post($_POST['content']);
$url = esc_url_raw($_POST['url']);
```

### SQL Injection Prevention
All database queries use prepared statements:
```php
$wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}lectus_enrollment 
     WHERE user_id = %d AND course_id = %d",
    $user_id, $course_id
);
```

## 🎯 AJAX Endpoints

### Frontend AJAX
```javascript
// Update lesson progress
jQuery.ajax({
    url: lectus_ajax.ajaxurl,
    method: 'POST',
    data: {
        action: 'lectus_update_lesson_progress',
        nonce: lectus_ajax.nonce,
        lesson_id: 123,
        progress: 50
    }
});

// Submit Q&A question
jQuery.ajax({
    url: lectus_ajax.ajaxurl,
    method: 'POST',
    data: {
        action: 'lectus_submit_question',
        nonce: lectus_ajax.nonce,
        course_id: 123,
        question: 'Question text'
    }
});
```

### Available AJAX Actions
- `lectus_update_lesson_progress`
- `lectus_complete_lesson`
- `lectus_enroll_student`
- `lectus_unenroll_student`
- `lectus_reset_progress`
- `lectus_generate_certificate`
- `lectus_submit_question`
- `lectus_submit_answer`
- `lectus_vote_qa`
- `lectus_upload_material`
- `lectus_delete_material`

## 🧪 Testing Guide

### Unit Testing
```php
class Test_Lectus_Enrollment extends WP_UnitTestCase {
    public function test_enrollment_creation() {
        $user_id = $this->factory->user->create();
        $course_id = $this->factory->post->create([
            'post_type' => 'coursesingle'
        ]);
        
        $result = Lectus_Enrollment::enroll($user_id, $course_id);
        $this->assertTrue($result);
        $this->assertTrue(Lectus_Enrollment::is_enrolled($user_id, $course_id));
    }
}
```

### Integration Testing
```javascript
// Playwright test example
test('Course enrollment flow', async ({ page }) => {
    await page.goto('/courses/test-course');
    await page.click('[data-action="enroll"]');
    await expect(page.locator('.enrollment-success')).toBeVisible();
});
```

## 🚀 Performance Optimization

### Caching Strategy
```php
// Use WordPress transients for caching
$cache_key = 'lectus_course_' . $course_id;
$cached = get_transient($cache_key);

if (false === $cached) {
    $data = expensive_operation();
    set_transient($cache_key, $data, HOUR_IN_SECONDS);
}
```

### Database Optimization
- Proper indexing on foreign keys
- Use JOIN instead of multiple queries
- Batch operations when possible
- Regular table optimization

### Asset Loading
```php
// Load assets only where needed
public function enqueue_scripts($hook) {
    if ('toplevel_page_lectus-class-system' !== $hook) {
        return;
    }
    wp_enqueue_script('lectus-admin');
}
```

## 📦 Deployment Checklist

### Pre-Deployment
- [ ] Remove all debug code
- [ ] Update version number
- [ ] Test on staging environment
- [ ] Run security scan
- [ ] Optimize database queries
- [ ] Minify CSS/JS files
- [ ] Update documentation
- [ ] Create database backup

### Post-Deployment
- [ ] Verify all features working
- [ ] Monitor error logs
- [ ] Check performance metrics
- [ ] Test payment integration
- [ ] Verify email notifications
- [ ] Monitor user feedback

## 🔧 Troubleshooting

### Common Issues

#### Database Table Missing
```php
// Force table creation
Lectus_Materials::create_table();
Lectus_QA::create_table();
```

#### Permission Issues
```php
// Reset capabilities
Lectus_Capabilities::remove_roles();
Lectus_Capabilities::create_roles();
```

#### Cache Issues
```php
// Clear all plugin caches
wp_cache_flush();
delete_transient('lectus_*');
```

## 📚 Additional Resources

- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WooCommerce Developer Docs](https://woocommerce.github.io/code-reference/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [Plugin Security Best Practices](https://developer.wordpress.org/plugins/security/)

---

For more information, visit the [main documentation](README.md) or [contribute on GitHub](https://github.com/BBQ-MAN/LectusClassSystem).