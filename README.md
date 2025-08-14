# 📚 Lectus Class System - WordPress LMS Plugin

[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-8.0%2B-purple.svg)](https://php.net/)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-6.0%2B-96588A.svg)](https://woocommerce.com/)
[![License](https://img.shields.io/badge/License-GPL%20v2-green.svg)](http://www.gnu.org/licenses/gpl-2.0.txt)

A comprehensive Learning Management System (LMS) plugin for WordPress with WooCommerce integration, designed for professional educational services.

## ✨ Features

### 📖 Course Management
- **Package Courses**: Bundle multiple single courses into comprehensive packages
- **Single Courses**: Individual courses with lessons and materials
- **Lessons**: Video, text, quiz, and assignment lesson types
- **Course Materials**: File uploads and external links support
- **Access Control**: Public, members-only, or sequential access modes

### 👥 Student Management
- **Enrollment System**: Manual and automated enrollment via WooCommerce
- **Progress Tracking**: Detailed progress monitoring per student
- **Student Dashboard**: Personalized learning interface
- **Access Duration**: Configurable course access periods

### 🎓 Certification System
- **Automatic Generation**: Certificates upon course completion
- **Custom Templates**: Configurable certificate designs
- **Verification System**: Unique certificate numbers with verification
- **PDF Generation**: Downloadable PDF certificates

### 💬 Q&A System
- **Course Q&A**: Interactive questions and answers per course/lesson
- **Voting System**: Upvote/downvote for quality content
- **Rate Limiting**: Prevent spam with configurable limits
- **Instructor Responses**: Dedicated instructor answer marking

### 💰 WooCommerce Integration
- **Product Creation**: Automatic WooCommerce product generation
- **Payment Processing**: Seamless payment integration
- **Order Management**: Automatic enrollment on purchase
- **Subscription Support**: Recurring payment options

### 📊 Reporting & Analytics
- **Progress Reports**: Detailed student progress tracking
- **Course Analytics**: Enrollment and completion statistics
- **Revenue Reports**: Financial performance tracking
- **Export Options**: Data export capabilities

## 🚀 Installation

### Requirements
- WordPress 5.0 or higher
- PHP 8.0 or higher
- MySQL 5.6 or higher
- WooCommerce 6.0+ (optional, for payment features)

### Installation Steps

1. **Download the Plugin**
   ```bash
   git clone https://github.com/BBQ-MAN/LectusClassSystem.git
   ```

2. **Upload to WordPress**
   - Copy the `lectus-class-system` folder to `/wp-content/plugins/`
   - Or upload via WordPress Admin → Plugins → Add New → Upload Plugin

3. **Activate the Plugin**
   - Go to WordPress Admin → Plugins
   - Find "Lectus Class System" and click "Activate"

4. **Initial Configuration**
   - Navigate to Lectus Class → Settings
   - Configure basic settings:
     - Default access duration
     - Completion threshold
     - Certificate settings
     - Email notifications

## 📖 Quick Start Guide

### Creating Your First Course

1. **Create a Package Course** (Optional)
   - Go to Lectus Class → Package Courses → Add New
   - Enter course title and description
   - Set access level and pricing

2. **Create a Single Course**
   - Go to Lectus Class → Single Courses → Add New
   - Enter course details
   - Configure course settings:
     - Duration
     - Access mode
     - Completion score
     - Certificate enabled

3. **Add Lessons**
   - Go to Lectus Class → Lessons → Add New
   - Select parent course
   - Choose lesson type (video, text, quiz, assignment)
   - Add lesson content and materials

4. **Create WooCommerce Product** (Optional)
   - From the course edit page, click "Create Product"
   - Product will be automatically linked to the course
   - Configure pricing and payment options

### Managing Students

1. **View Enrollments**
   - Go to Lectus Class → Student Management
   - View all enrolled students by course

2. **Manual Enrollment**
   - Click "Enroll Student" button
   - Select user and course
   - Set access duration

3. **Track Progress**
   - View individual student progress
   - Monitor lesson completion
   - Generate progress reports

## 🔧 Configuration

### General Settings

Navigate to **Lectus Class → Settings** to configure:

#### Access Settings
- `Default Access Duration`: Days of course access (default: 365)
- `Completion Threshold`: Required completion percentage (default: 80%)
- `Sequential Access`: Force sequential lesson completion

#### Certificate Settings
- `Enable Certificates`: Auto-generate certificates
- `Certificate Template`: Choose template design
- `Verification URL`: Public certificate verification page

#### Email Notifications
- `Enable Notifications`: Send automated emails
- `Enrollment Email`: Customize enrollment confirmation
- `Completion Email`: Customize completion congratulations

#### Development Tools (Settings → Development Tools Tab)
- Generate test data
- Create test pages
- View system logs
- Optimize database

## 👨‍💻 Developer Documentation

### Hooks & Filters

#### Actions
```php
// Course enrollment
do_action('lectus_student_enrolled', $user_id, $course_id, $order_id);

// Course completion
do_action('lectus_course_completed', $user_id, $course_id);

// Lesson completion
do_action('lectus_lesson_completed', $user_id, $course_id, $lesson_id);

// Certificate generation
do_action('lectus_certificate_generated', $certificate_id, $user_id, $course_id);
```

#### Filters
```php
// Modify enrollment duration
apply_filters('lectus_enrollment_duration', $days, $course_id, $user_id);

// Customize certificate data
apply_filters('lectus_certificate_data', $data, $user_id, $course_id);

// Modify course access
apply_filters('lectus_can_access_course', $can_access, $user_id, $course_id);
```

### Database Tables

The plugin creates the following custom tables:

- `{prefix}_lectus_enrollment` - Student enrollments
- `{prefix}_lectus_progress` - Learning progress
- `{prefix}_lectus_certificates` - Generated certificates
- `{prefix}_lectus_qa_questions` - Q&A questions
- `{prefix}_lectus_qa_answers` - Q&A answers
- `{prefix}_lectus_qa_votes` - Vote tracking
- `{prefix}_lectus_materials` - Course materials
- `{prefix}_lectus_logs` - System logs
- `{prefix}_lectus_rate_limits` - Rate limiting

### Custom Post Types

- `coursepackage` - Package courses
- `coursesingle` - Single courses
- `lesson` - Course lessons

### User Roles

- `lectus_instructor` - Course instructors
- `lectus_student` - Enrolled students

### REST API Endpoints

```php
// Get course details
GET /wp-json/lectus/v1/courses/{id}

// Get student progress
GET /wp-json/lectus/v1/progress/{user_id}/{course_id}

// Submit Q&A question
POST /wp-json/lectus/v1/qa/question
```

## 🎨 Shortcodes

### Display Courses
```
[lectus_courses]
[lectus_courses category="programming" limit="10"]
```

### Student Dashboard
```
[lectus_student_dashboard]
```

### My Courses
```
[lectus_my_courses]
```

### Certificates
```
[lectus_certificates]
```

### Certificate Verification
```
[lectus_certificate_verify]
```

### Course Progress
```
[lectus_course_progress course_id="123"]
```

## 🧪 Testing

### Running Tests

1. **Setup Test Environment**
   ```bash
   cd lectus-class-system/tests
   npm install
   ```

2. **Run Tests**
   ```bash
   # All tests
   npm test
   
   # Specific test suite
   npm test -- woocommerce-integration
   ```

### Test Data Generation

1. Go to **Settings → Development Tools**
2. Click "Generate Test Data" for each type:
   - Categories & Levels
   - Package Courses
   - Single Courses  
   - Lessons
   - Students
   - Enrollments

## 🐛 Troubleshooting

### Common Issues

#### Q&A Not Displaying
- Check if user is logged in
- Verify course/lesson ID is correct
- Check rate limiting settings

#### Certificates Not Generating
- Ensure course completion threshold is met
- Check certificate settings are enabled
- Verify write permissions for upload directory

#### WooCommerce Product Creation Failed
- Ensure WooCommerce is installed and active
- Check user permissions
- Verify course has required fields

### Debug Mode

Enable WordPress debug mode in `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

View logs at: **Lectus Class → Settings → System → View Logs**

## 📝 Changelog

### Version 1.0.0 (2025-01-13)
- Initial release
- Core LMS functionality
- WooCommerce integration
- Q&A system implementation
- Certificate generation
- Student management
- Progress tracking
- Materials system with external links

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### Development Setup

1. Clone the repository
2. Install dependencies: `npm install`
3. Create a local WordPress environment
4. Activate the plugin
5. Run tests: `npm test`

## 📄 License

This plugin is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.
```

## 💬 Support

- **Documentation**: [GitHub Wiki](https://github.com/BBQ-MAN/LectusClassSystem/wiki)
- **Issues**: [GitHub Issues](https://github.com/BBQ-MAN/LectusClassSystem/issues)
- **Email**: support@example.com

## 👏 Credits

Developed by the Lectus Team

Special thanks to:
- WordPress Community
- WooCommerce Team
- All contributors

---

Made with ❤️ for educators and learners worldwide