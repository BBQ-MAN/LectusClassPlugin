# Lectus Class System - WordPress LMS Plugin

A comprehensive Learning Management System (LMS) plugin for WordPress with WooCommerce integration, designed for online education platforms.

## 📋 Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [API Reference](#api-reference)
- [Development](#development)
- [Testing](#testing)
- [Troubleshooting](#troubleshooting)
- [License](#license)

## ✨ Features

### Core LMS Features
- **Course Management**: Create and manage package courses and single courses
- **Lesson System**: Organize content with sequential or free access modes
- **Student Enrollment**: Automated enrollment with progress tracking
- **Certificate Generation**: Customizable completion certificates
- **Progress Tracking**: Real-time student progress monitoring

### WooCommerce Integration
- **Product Auto-Creation**: Convert courses to WooCommerce products
- **Payment Processing**: Secure payment through WooCommerce
- **Auto-Enrollment**: Automatic course access upon purchase
- **HPOS Compatibility**: Full support for High-Performance Order Storage

### Interactive Learning
- **Q&A System**: Course-specific questions and answers
- **Voting System**: Community-driven content quality
- **Instructor Replies**: Direct instructor engagement
- **Best Answer Selection**: Highlight valuable contributions

### Administrative Tools
- **Bulk Upload**: CSV import for lessons
- **Student Management**: Comprehensive student administration
- **Reporting Dashboard**: Analytics and insights
- **Activity Logging**: Detailed system activity tracking

## 📦 Requirements

- WordPress 5.0 or higher
- PHP 8.0 or higher
- MySQL 5.7 or higher
- WooCommerce 6.0 or higher (for payment features)

## 🚀 Installation

### Method 1: WordPress Admin Upload

1. Download the plugin ZIP file
2. Navigate to **WordPress Admin > Plugins > Add New**
3. Click **Upload Plugin** and select the ZIP file
4. Click **Install Now** and then **Activate**

### Method 2: FTP Upload

1. Extract the plugin ZIP file
2. Upload the `lectus-class-system` folder to `/wp-content/plugins/`
3. Navigate to **WordPress Admin > Plugins**
4. Find "Lectus Class System" and click **Activate**

### Method 3: Development Installation

```bash
# Clone the repository
git clone https://github.com/yourusername/lectus-class-system.git

# Navigate to WordPress plugins directory
cd /path/to/wordpress/wp-content/plugins/

# Create symbolic link
ln -s /path/to/lectus-class-system lectus-class-system

# Activate via WP-CLI
wp plugin activate lectus-class-system
```

## ⚙️ Configuration

### Initial Setup

1. **Activate the Plugin**
   - Navigate to **Plugins** menu
   - Activate "Lectus Class System"

2. **Configure Basic Settings**
   - Go to **Lectus Class > Settings**
   - Configure email notifications
   - Set default access duration
   - Enable/disable features

3. **WooCommerce Integration**
   - Ensure WooCommerce is installed and activated
   - Configure payment gateways
   - Set up tax and shipping (if applicable)

### User Roles

The plugin creates and uses the following roles:

- **Administrator**: Full system access
- **Instructor**: Course creation and management
- **Student**: Course enrollment and participation

## 📖 Usage

### Creating Courses

#### Package Course
```php
// Navigate to: Lectus Class > Package Courses > Add New
// Fill in:
- Title: Course package name
- Description: Package details
- Featured Image: Course thumbnail
- Package Courses: Select included single courses
- Price: Set package price
```

#### Single Course
```php
// Navigate to: Lectus Class > Single Courses > Add New
// Fill in:
- Title: Course name
- Content: Course description
- Duration: Access period in days
- Access Mode: Sequential or Free
- Price: Course price (0 for free)
```

#### Lessons
```php
// Navigate to: Lectus Class > Lessons > Add New
// Fill in:
- Title: Lesson name
- Content: Lesson material
- Course: Parent course
- Type: Text, Video, Quiz, or Assignment
- Duration: Estimated time in minutes
```

### Managing Students

```php
// Navigate to: Lectus Class > Student Management
// Actions available:
- View enrollments
- Track progress
- Reset progress
- Generate certificates
- Export data (CSV)
```

### Using Shortcodes

#### Display Course List
```php
[lectus_courses type="coursesingle" columns="3" limit="12"]
```

#### Show Enrollment Button
```php
[lectus_enroll_button course_id="123" text="Enroll Now"]
```

#### Display Student Dashboard
```php
[lectus_my_courses]
```

#### Show Certificates
```php
[lectus_certificates]
```

#### Q&A Section
```php
[lectus_qa course_id="123" show_form="yes"]
```

## 🔌 API Reference

### Hooks

#### Actions
```php
// After student enrollment
do_action('lectus_student_enrolled', $user_id, $course_id, $order_id);

// After lesson completion
do_action('lectus_lesson_completed', $user_id, $course_id, $lesson_id);

// After certificate generation
do_action('lectus_certificate_generated', $user_id, $course_id, $certificate_id);

// After question submission
do_action('lectus_question_submitted', $question_id, $course_id, $user_id);
```

#### Filters
```php
// Modify enrollment duration
apply_filters('lectus_enrollment_duration', $days, $course_id, $user_id);

// Customize certificate data
apply_filters('lectus_certificate_data', $data, $user_id, $course_id);

// Filter Q&A content
apply_filters('lectus_qa_content_filter', $content);
```

### PHP Functions

#### Enrollment Management
```php
// Check enrollment status
Lectus_Enrollment::is_enrolled($user_id, $course_id);

// Enroll a student
Lectus_Enrollment::enroll($user_id, $course_id, $order_id, $duration);

// Unenroll a student
Lectus_Enrollment::unenroll($user_id, $course_id);

// Extend enrollment
Lectus_Enrollment::extend_enrollment($user_id, $course_id, $days);
```

#### Progress Tracking
```php
// Get course progress
Lectus_Progress::get_course_progress($user_id, $course_id);

// Mark lesson complete
Lectus_Progress::mark_lesson_complete($user_id, $course_id, $lesson_id);

// Reset progress
Lectus_Progress::reset_course_progress($user_id, $course_id);
```

#### Certificate Generation
```php
// Generate certificate
Lectus_Certificate::generate($user_id, $course_id);

// Get certificate URL
Lectus_Certificate::get_certificate_url($certificate_id);

// Verify certificate
Lectus_Certificate::verify($certificate_number);
```

#### WooCommerce Integration
```php
// Check if course has product
Lectus_WooCommerce::course_has_product($course_id);

// Get product URL
Lectus_WooCommerce::get_course_product_url($course_id);
```

### AJAX Endpoints

```javascript
// Submit question
action: 'lectus_submit_question'
data: {
    nonce: lectus_ajax.nonce,
    course_id: 123,
    title: 'Question title',
    content: 'Question content'
}

// Submit answer
action: 'lectus_submit_answer'
data: {
    nonce: lectus_ajax.nonce,
    question_id: 456,
    content: 'Answer content'
}

// Complete lesson
action: 'lectus_complete_lesson'
data: {
    nonce: lectus_ajax.nonce,
    lesson_id: 789
}

// Create WooCommerce product
action: 'lectus_create_product'
data: {
    nonce: lectus_ajax.nonce,
    course_id: 123,
    course_type: 'coursesingle'
}
```

## 🛠️ Development

### Project Structure
```
lectus-class-system/
├── admin/                  # Admin functionality
│   ├── class-lectus-admin.php
│   ├── class-lectus-admin-dashboard.php
│   ├── class-lectus-admin-reports.php
│   └── class-lectus-admin-settings.php
├── assets/                 # CSS and JavaScript
│   ├── css/
│   │   ├── admin.css
│   │   └── frontend.css
│   └── js/
│       ├── admin.js
│       └── frontend.js
├── includes/              # Core functionality
│   ├── class-lectus-ajax.php
│   ├── class-lectus-enrollment.php
│   ├── class-lectus-progress.php
│   ├── class-lectus-certificate.php
│   ├── class-lectus-qa.php
│   ├── class-lectus-woocommerce.php
│   └── ...
├── templates/             # Template files
│   └── student-dashboard.php
├── tests/                 # Test files
│   ├── playwright/
│   └── phpunit/
└── lectus-class-system.php  # Main plugin file
```

### Coding Standards

This plugin follows WordPress Coding Standards:

- PHP: [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
- JavaScript: [WordPress JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/)
- CSS: [WordPress CSS Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/)

### Building Assets

```bash
# Install dependencies
npm install

# Development build
npm run dev

# Production build
npm run build

# Watch for changes
npm run watch
```

## 🧪 Testing

### PHP Unit Tests

```bash
# Install PHPUnit
composer install

# Run tests
./vendor/bin/phpunit

# Run specific test
./vendor/bin/phpunit tests/test-enrollment.php
```

### Playwright E2E Tests

```bash
# Navigate to tests directory
cd tests

# Install dependencies
npm install
npm run install-browsers

# Set up environment
cp .env.example .env
# Edit .env with your WordPress credentials

# Run tests
npm test

# Run specific test
npm test woocommerce-integration.spec.js

# Run in headed mode
npm run test:headed

# Generate report
npm run report
```

### Manual Testing

```bash
# Run test data generator
wp eval-file admin/lectus-test-data.php

# Run Q&A debugging
wp eval-file tests/test-qa-debug.php

# Run instructor test
wp eval-file tests/test-instructor-qa.php
```

## 🔧 Troubleshooting

### Common Issues

#### Plugin Activation Fails
```php
// Check PHP version
php -v  # Should be 8.0+

// Check WordPress version
wp core version  # Should be 5.0+

// Check error logs
tail -f wp-content/debug.log
```

#### WooCommerce Integration Not Working
```php
// Verify WooCommerce is active
wp plugin list --status=active

// Check HPOS compatibility
// Go to: WooCommerce > Settings > Advanced > Features
// Enable: High-Performance Order Storage
```

#### Q&A System Issues
```php
// Rebuild database tables
wp eval "Lectus_QA::create_table();"

// Check AJAX endpoints
wp eval "print_r(has_action('wp_ajax_lectus_submit_question'));"
```

#### Certificate Generation Fails
```php
// Check write permissions
chmod 755 wp-content/uploads/lectus-certificates/

// Verify GD library
php -m | grep gd
```

### Debug Mode

Enable WordPress debug mode for detailed error information:

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', true);
```

### Support

For support and bug reports:

1. Check the [documentation](https://example.com/docs)
2. Search [existing issues](https://github.com/yourusername/lectus-class-system/issues)
3. Create a [new issue](https://github.com/yourusername/lectus-class-system/issues/new)

## 📄 License

GPL-2.0+ License. See [LICENSE](LICENSE) file for details.

## 🤝 Contributing

Contributions are welcome! Please read our [Contributing Guidelines](CONTRIBUTING.md) before submitting PRs.

## 👥 Credits

- Developed by Lectus Team
- Built with WordPress and WooCommerce
- Icons from Dashicons
- Tested with Playwright

## 📈 Changelog

### Version 1.0.0 (2024)
- Initial release
- Core LMS functionality
- WooCommerce integration
- Q&A system
- Certificate generation
- Student management
- Bulk upload system
- Comprehensive testing suite

---

Made with ❤️ for educators and learners worldwide.