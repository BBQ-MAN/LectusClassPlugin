# 👨‍💼 Lectus Class System - Administrator Guide

## 📋 Table of Contents
1. [Getting Started](#getting-started)
2. [Course Management](#course-management)
3. [Student Management](#student-management)
4. [Content Creation](#content-creation)
5. [Settings Configuration](#settings-configuration)
6. [Reports & Analytics](#reports-analytics)
7. [Troubleshooting](#troubleshooting)

---

## 🚀 Getting Started

### First Time Setup

After activating the plugin, follow these steps:

1. **Navigate to Lectus Class** in WordPress admin menu
2. **Configure Basic Settings**:
   - Go to **Lectus Class → Settings**
   - Set default access duration (e.g., 365 days)
   - Configure completion threshold (e.g., 80%)
   - Enable/disable certificates
   - Configure email notifications

3. **Create User Roles** (Automatic):
   - `lectus_instructor` - Can create and manage courses
   - `lectus_student` - Can enroll and take courses

4. **Set Up Categories**:
   - Go to **Lectus Class → Single Courses → Categories**
   - Create course categories (Programming, Design, Business, etc.)
   - Create difficulty levels (Beginner, Intermediate, Advanced)

---

## 📚 Course Management

### Creating a Package Course

Package courses bundle multiple single courses together.

1. **Go to Lectus Class → Package Courses → Add New**
2. **Fill in Course Details**:
   - Title: Enter package name
   - Description: Detailed package description
   - Featured Image: Upload course thumbnail

3. **Configure Package Settings**:
   ```
   ✓ Maximum Students: Set enrollment limit
   ✓ Access Level: Public/Members only
   ✓ Price: Set package price
   ```

4. **Add Single Courses**:
   - After creating, edit the package
   - Select single courses to include
   - Save changes

### Creating a Single Course

1. **Go to Lectus Class → Single Courses → Add New**
2. **Enter Course Information**:
   - Title: Course name
   - Description: Course content and objectives
   - Category: Select appropriate category
   - Level: Choose difficulty level

3. **Course Settings** (in meta box):
   - **Duration**: Course length in days
   - **Access Mode**:
     - `Free`: Open to all
     - `Sequential`: Lessons in order
     - `Restricted`: Enrolled users only
   - **Completion Score**: Minimum % to pass
   - **Certificate**: Enable/disable

4. **Link to Package** (Optional):
   - Select parent package course
   - Save course

### Creating Lessons

1. **Go to Lectus Class → Lessons → Add New**
2. **Lesson Details**:
   - Title: Lesson name
   - Content: Lesson material
   - Parent Course: Select course

3. **Lesson Type**:
   - **Video**: YouTube/Vimeo URL
   - **Text**: Written content
   - **Quiz**: Questions and answers
   - **Assignment**: Downloadable tasks

4. **Lesson Settings**:
   - Duration: Estimated minutes
   - Completion Criteria:
     - `View`: Just viewing completes
     - `Time`: Must spend X minutes
     - `Quiz`: Must pass quiz

5. **Add Materials**:
   - Upload files (PDF, DOC, etc.)
   - Add external links
   - Set access levels

---

## 👥 Student Management

### Viewing Students

1. **Go to Lectus Class → Student Management**
2. **View Options**:
   - All students
   - Filter by course
   - Filter by status (Active/Inactive)

### Manual Enrollment

1. **Click "Enroll Student" button**
2. **Fill Enrollment Form**:
   ```
   User: [Select from dropdown]
   Course: [Select course]
   Duration: [Days of access]
   ```
3. **Click "Enroll"**

### Managing Enrollments

#### View Progress
1. Click student name
2. View detailed progress:
   - Lessons completed
   - Current progress %
   - Last activity

#### Extend Access
1. Find student in list
2. Click "Extend"
3. Enter additional days
4. Save changes

#### Reset Progress
1. Select student
2. Click "Reset Progress"
3. Confirm action

#### Unenroll Student
1. Find enrollment
2. Click "Unenroll"
3. Confirm removal

---

## 📝 Content Creation

### Using the Bulk Upload Feature

1. **Prepare CSV File**:
   ```csv
   Title,Type,Duration,Content
   "Introduction",video,10,"Welcome to the course"
   "Chapter 1",text,20,"Chapter content here"
   "Quiz 1",quiz,15,"Quiz questions"
   ```

2. **Upload Process**:
   - Go to course edit page
   - Click "Bulk Upload Lessons"
   - Select CSV file
   - Review and confirm
   - Lessons created automatically

### Adding Course Materials

1. **In Lesson Edit Page**:
   - Find "Course Materials" box
   - Choose type:
     - File Upload
     - External Link

2. **For Files**:
   - Click "Choose File"
   - Select file (max 50MB)
   - Add title and description
   - Set access level

3. **For External Links**:
   - Enter URL
   - Add title
   - Add description
   - Save material

### Creating Q&A Sections

Q&A is automatically enabled for courses. To manage:

1. **View Questions**:
   - Edit course/lesson
   - See Q&A section
   - Review submitted questions

2. **Moderate Content**:
   - Approve/reject questions
   - Mark instructor answers
   - Delete inappropriate content

---

## ⚙️ Settings Configuration

### General Settings

Navigate to **Lectus Class → Settings**:

#### Basic Tab
- **Access Duration**: Default days for course access
- **Completion Threshold**: Required % to complete course
- **Time Zone**: System time zone

#### Enrollment Tab
- **Auto-Assign Role**: Give student role on enrollment
- **Enrollment Confirmation**: Send email on enrollment
- **Allow Self-Enrollment**: Users can enroll themselves

#### Certificates Tab
- **Auto-Generate**: Create certificates automatically
- **Template**: Choose certificate design
- **Verification Page**: Set verification URL
- **PDF Settings**: Configure PDF generation

#### Emails Tab
- **Enable Notifications**: Turn on/off emails
- **Enrollment Email**:
  ```
  Subject: Welcome to {course_name}
  Body: Custom HTML template
  ```
- **Completion Email**:
  ```
  Subject: Congratulations on completing {course_name}
  Body: Custom HTML template
  ```

### Advanced Settings

#### Development Tools Tab
For testing and development:

1. **Generate Test Data**:
   - Categories & Levels
   - Package Courses (3)
   - Single Courses (6)
   - Lessons (10 per course)
   - Students (5)
   - Enrollments

2. **Create Test Pages**:
   - Course listing page
   - Student dashboard
   - My courses page
   - Certificate verification

3. **Rate Limit Settings**:
   - Q&A submission limits
   - Time windows
   - Reset options

#### System Tab
1. **View Logs**:
   - Error logs
   - Activity logs
   - Debug information

2. **Database Management**:
   - Optimize tables
   - Clear old data
   - Export data

3. **Cache Settings**:
   - Clear cache
   - Cache duration
   - Cache types

---

## 📊 Reports & Analytics

### Dashboard Overview

**Lectus Class → Dashboard** shows:
- Total students
- Active courses
- Completion rate
- Revenue (if WooCommerce)
- Recent activity

### Course Reports

**Lectus Class → Reports → Courses**:
- Enrollment numbers
- Completion rates
- Average progress
- Popular courses
- Drop-off points

### Student Reports

**Lectus Class → Reports → Students**:
- Active students
- Completion statistics
- Progress tracking
- Engagement metrics
- Certificate issued

### Financial Reports
(Requires WooCommerce)

**Lectus Class → Reports → Revenue**:
- Total revenue
- Revenue by course
- Revenue by period
- Refunds
- Pending payments

### Exporting Reports

1. Select report type
2. Choose date range
3. Select format:
   - CSV
   - Excel
   - PDF
4. Click "Export"

---

## 🔧 Troubleshooting

### Common Issues

#### Students Can't Access Course
**Check:**
- Enrollment status
- Access expiration
- Course access settings
- User login status

**Fix:**
1. Verify enrollment in Student Management
2. Extend access if expired
3. Check course access mode
4. Clear user session/cache

#### Certificates Not Generating
**Check:**
- Course completion threshold
- Certificate enabled for course
- Student progress percentage

**Fix:**
1. Lower threshold if too high
2. Enable certificates in course settings
3. Manually generate from student page

#### Q&A Not Showing
**Check:**
- User logged in
- Course/lesson published
- Rate limits

**Fix:**
1. Ensure user authentication
2. Publish course/lesson
3. Reset rate limits if needed

#### WooCommerce Product Issues
**Check:**
- WooCommerce active
- Product creation permissions
- Course has required fields

**Fix:**
1. Activate WooCommerce
2. Check user capabilities
3. Fill all required course fields

### Debug Mode

Enable debug logging:

1. **Edit wp-config.php**:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   ```

2. **View Logs**:
   - Go to Settings → System → View Logs
   - Check `/wp-content/debug.log`

### Getting Help

1. **Check Documentation**:
   - This guide
   - Developer docs
   - API reference

2. **Support Channels**:
   - GitHub Issues
   - Support email
   - Community forum

### Performance Tips

1. **Optimize Database**:
   - Run optimization monthly
   - Clean old logs
   - Remove expired enrollments

2. **Cache Settings**:
   - Enable object caching
   - Use CDN for materials
   - Optimize images

3. **Limit Features**:
   - Set reasonable rate limits
   - Limit file upload sizes
   - Archive old courses

---

## 📝 Best Practices

### Course Design
- Keep lessons under 20 minutes
- Mix content types (video, text, quiz)
- Provide clear learning objectives
- Include practical exercises

### Student Engagement
- Send regular progress emails
- Respond to Q&A promptly
- Offer certificates as motivation
- Create community forums

### Content Organization
- Use clear naming conventions
- Organize courses by category
- Maintain consistent difficulty levels
- Regular content updates

### Security
- Regular password updates
- Limit admin access
- Monitor user activity
- Regular backups

---

## 🎯 Quick Actions Reference

| Task | Location | Action |
|------|----------|--------|
| Create Course | Single Courses → Add New | Fill form and publish |
| Enroll Student | Student Management | Click "Enroll Student" |
| Generate Certificate | Student Management | Click student → Generate |
| View Reports | Reports → [Type] | Select date range |
| Change Settings | Settings → [Tab] | Update and save |
| Create Test Data | Settings → Development | Click generate buttons |
| View Logs | Settings → System | Click "View Logs" |
| Export Data | Reports → Export | Select format and download |

---

For technical details, see the [Developer Documentation](../DEVELOPER.md).
For API usage, see the [API Reference](API-REFERENCE.md).