# 🔌 Lectus Class System - API Reference

## PHP Classes API

### Lectus_Enrollment

#### Methods

##### `enroll($user_id, $course_id, $order_id = 0, $duration_days = 0)`
Enrolls a user in a course.

**Parameters:**
- `$user_id` (int) - User ID
- `$course_id` (int) - Course ID
- `$order_id` (int) - WooCommerce order ID (optional)
- `$duration_days` (int) - Access duration in days (0 = unlimited)

**Returns:** (bool) Success status

**Example:**
```php
$enrolled = Lectus_Enrollment::enroll(123, 456, 0, 365);
if ($enrolled) {
    echo "User enrolled successfully";
}
```

##### `unenroll($user_id, $course_id)`
Removes user enrollment from a course.

**Parameters:**
- `$user_id` (int) - User ID
- `$course_id` (int) - Course ID

**Returns:** (bool) Success status

##### `is_enrolled($user_id, $course_id)`
Checks if user is enrolled in a course.

**Parameters:**
- `$user_id` (int) - User ID
- `$course_id` (int) - Course ID

**Returns:** (bool) Enrollment status

##### `get_user_enrollments($user_id)`
Gets all enrollments for a user.

**Parameters:**
- `$user_id` (int) - User ID

**Returns:** (array) Array of enrollment objects

---

### Lectus_Progress

#### Methods

##### `update_progress($user_id, $course_id, $lesson_id, $progress)`
Updates lesson progress for a user.

**Parameters:**
- `$user_id` (int) - User ID
- `$course_id` (int) - Course ID
- `$lesson_id` (int) - Lesson ID
- `$progress` (int) - Progress percentage (0-100)

**Returns:** (bool) Success status

##### `complete_lesson($user_id, $course_id, $lesson_id)`
Marks a lesson as completed.

**Parameters:**
- `$user_id` (int) - User ID
- `$course_id` (int) - Course ID
- `$lesson_id` (int) - Lesson ID

**Returns:** (bool) Success status

##### `get_course_progress($user_id, $course_id)`
Gets overall course progress for a user.

**Parameters:**
- `$user_id` (int) - User ID
- `$course_id` (int) - Course ID

**Returns:** (int) Progress percentage

##### `is_course_completed($user_id, $course_id)`
Checks if user has completed a course.

**Parameters:**
- `$user_id` (int) - User ID
- `$course_id` (int) - Course ID

**Returns:** (bool) Completion status

##### `reset_course_progress($user_id, $course_id)`
Resets all progress for a user in a course.

**Parameters:**
- `$user_id` (int) - User ID
- `$course_id` (int) - Course ID

**Returns:** (bool) Success status

---

### Lectus_Certificate

#### Methods

##### `generate($user_id, $course_id)`
Generates a certificate for course completion.

**Parameters:**
- `$user_id` (int) - User ID
- `$course_id` (int) - Course ID

**Returns:** (int|false) Certificate ID or false on failure

##### `get_certificate($certificate_id)`
Gets certificate details by ID.

**Parameters:**
- `$certificate_id` (int) - Certificate ID

**Returns:** (object|null) Certificate object

##### `verify($certificate_number)`
Verifies a certificate by its number.

**Parameters:**
- `$certificate_number` (string) - Certificate number

**Returns:** (object|false) Certificate object or false if invalid

##### `get_certificate_url($certificate_id)`
Gets the download URL for a certificate.

**Parameters:**
- `$certificate_id` (int) - Certificate ID

**Returns:** (string) Certificate URL

---

### Lectus_QA

#### Methods

##### `submit_question($data)`
Submits a new question.

**Parameters:**
- `$data` (array) - Question data
  - `course_id` (int) - Course ID
  - `lesson_id` (int) - Lesson ID (optional)
  - `question` (string) - Question text
  - `user_id` (int) - User ID

**Returns:** (int|WP_Error) Question ID or error

##### `submit_answer($data)`
Submits an answer to a question.

**Parameters:**
- `$data` (array) - Answer data
  - `question_id` (int) - Question ID
  - `answer` (string) - Answer text
  - `user_id` (int) - User ID
  - `is_instructor` (bool) - Is instructor answer

**Returns:** (int|WP_Error) Answer ID or error

##### `vote($post_id, $user_id, $vote_type)`
Records a vote on a question or answer.

**Parameters:**
- `$post_id` (int) - Question or answer ID
- `$user_id` (int) - User ID
- `$vote_type` (string) - 'up' or 'down'

**Returns:** (bool) Success status

##### `get_questions($course_id, $lesson_id = null, $args = [])`
Gets questions for a course or lesson.

**Parameters:**
- `$course_id` (int) - Course ID
- `$lesson_id` (int) - Lesson ID (optional)
- `$args` (array) - Query arguments

**Returns:** (array) Array of question objects

---

### Lectus_Materials

#### Methods

##### `upload_material($data, $file = null)`
Uploads a course material.

**Parameters:**
- `$data` (array) - Material data
  - `course_id` (int) - Course ID
  - `lesson_id` (int) - Lesson ID (optional)
  - `title` (string) - Material title
  - `description` (string) - Description
  - `material_type` (string) - 'file' or 'link'
  - `external_url` (string) - URL for external links
- `$file` (array) - $_FILES array for file upload

**Returns:** (int|false) Material ID or false on failure

##### `get_materials($course_id, $lesson_id = null)`
Gets materials for a course or lesson.

**Parameters:**
- `$course_id` (int) - Course ID
- `$lesson_id` (int) - Lesson ID (optional)

**Returns:** (array) Array of material objects

##### `delete_material($material_id)`
Deletes a material.

**Parameters:**
- `$material_id` (int) - Material ID

**Returns:** (bool) Success status

##### `track_download($material_id)`
Tracks material download.

**Parameters:**
- `$material_id` (int) - Material ID

**Returns:** (void)

---

## REST API Endpoints

### Authentication
All REST API endpoints require authentication via WordPress REST API authentication methods.

### Endpoints

#### Get Course
```
GET /wp-json/lectus/v1/courses/{id}
```

**Response:**
```json
{
  "id": 123,
  "title": "Course Title",
  "description": "Course description",
  "instructor": 456,
  "duration": 30,
  "lessons": [789, 790],
  "enrolled_count": 50,
  "price": 99.99
}
```

#### Get User Progress
```
GET /wp-json/lectus/v1/progress/{user_id}/{course_id}
```

**Response:**
```json
{
  "user_id": 123,
  "course_id": 456,
  "overall_progress": 75,
  "lessons": [
    {
      "lesson_id": 789,
      "status": "completed",
      "progress": 100
    }
  ],
  "enrolled_at": "2025-01-01T00:00:00",
  "expires_at": "2026-01-01T00:00:00"
}
```

#### Submit Question
```
POST /wp-json/lectus/v1/qa/question
```

**Request Body:**
```json
{
  "course_id": 123,
  "lesson_id": 456,
  "question": "Question text here"
}
```

**Response:**
```json
{
  "id": 789,
  "message": "Question submitted successfully",
  "question": {
    "id": 789,
    "question": "Question text here",
    "user_id": 123,
    "created_at": "2025-01-13T10:00:00"
  }
}
```

#### Get Questions
```
GET /wp-json/lectus/v1/qa/questions/{course_id}
```

**Query Parameters:**
- `lesson_id` (int) - Filter by lesson
- `page` (int) - Page number
- `per_page` (int) - Items per page

**Response:**
```json
{
  "questions": [
    {
      "id": 123,
      "question": "Question text",
      "user": {
        "id": 456,
        "name": "John Doe"
      },
      "answers_count": 3,
      "votes": 5,
      "created_at": "2025-01-13T10:00:00"
    }
  ],
  "total": 50,
  "pages": 5
}
```

---

## JavaScript API

### Frontend Functions

#### lectus.updateProgress(lessonId, progress)
Updates lesson progress via AJAX.

**Parameters:**
- `lessonId` (number) - Lesson ID
- `progress` (number) - Progress percentage

**Returns:** Promise

**Example:**
```javascript
lectus.updateProgress(123, 50)
  .then(response => {
    console.log('Progress updated');
  })
  .catch(error => {
    console.error('Update failed:', error);
  });
```

#### lectus.completeLesson(lessonId)
Marks a lesson as completed.

**Parameters:**
- `lessonId` (number) - Lesson ID

**Returns:** Promise

#### lectus.enrollCourse(courseId)
Enrolls in a free course.

**Parameters:**
- `courseId` (number) - Course ID

**Returns:** Promise

#### lectus.submitQuestion(courseId, question, lessonId)
Submits a Q&A question.

**Parameters:**
- `courseId` (number) - Course ID
- `question` (string) - Question text
- `lessonId` (number) - Lesson ID (optional)

**Returns:** Promise

#### lectus.voteQA(postId, voteType)
Votes on a Q&A post.

**Parameters:**
- `postId` (number) - Question or answer ID
- `voteType` (string) - 'up' or 'down'

**Returns:** Promise

---

## Shortcode Parameters

### [lectus_courses]
Displays course listing.

**Parameters:**
- `category` (string) - Filter by category slug
- `level` (string) - Filter by difficulty level
- `limit` (int) - Number of courses to display
- `orderby` (string) - Order by field (date, title, price)
- `order` (string) - ASC or DESC
- `columns` (int) - Number of columns (1-4)

**Example:**
```
[lectus_courses category="programming" level="beginner" limit="6" columns="3"]
```

### [lectus_my_courses]
Displays user's enrolled courses.

**Parameters:**
- `status` (string) - Filter by status (active, completed, expired)
- `orderby` (string) - Order by field (enrolled_date, progress)

### [lectus_course_progress]
Displays course progress bar.

**Parameters:**
- `course_id` (int) - Course ID
- `show_percentage` (bool) - Show percentage text
- `show_label` (bool) - Show progress label

### [lectus_student_dashboard]
Displays complete student dashboard.

**Parameters:**
- `show_stats` (bool) - Show statistics section
- `show_certificates` (bool) - Show certificates section
- `show_recent` (bool) - Show recent activity

### [lectus_certificate_verify]
Displays certificate verification form.

**Parameters:**
- `show_form` (bool) - Show verification form
- `placeholder` (string) - Input placeholder text

---

## Error Codes

### Enrollment Errors
- `LECTUS_ERR_001` - User not found
- `LECTUS_ERR_002` - Course not found
- `LECTUS_ERR_003` - Already enrolled
- `LECTUS_ERR_004` - Enrollment expired

### Progress Errors
- `LECTUS_ERR_101` - Not enrolled in course
- `LECTUS_ERR_102` - Lesson not found
- `LECTUS_ERR_103` - Invalid progress value

### Certificate Errors
- `LECTUS_ERR_201` - Course not completed
- `LECTUS_ERR_202` - Certificate already exists
- `LECTUS_ERR_203` - Certificate generation failed

### Q&A Errors
- `LECTUS_ERR_301` - Rate limit exceeded
- `LECTUS_ERR_302` - Invalid question/answer
- `LECTUS_ERR_303` - Not authorized

### Material Errors
- `LECTUS_ERR_401` - Upload failed
- `LECTUS_ERR_402` - File type not allowed
- `LECTUS_ERR_403` - File size exceeded

---

## Constants

```php
// Plugin version
define('LECTUS_VERSION', '1.0.0');

// Plugin paths
define('LECTUS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LECTUS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Database table names
define('LECTUS_TABLE_ENROLLMENT', $wpdb->prefix . 'lectus_enrollment');
define('LECTUS_TABLE_PROGRESS', $wpdb->prefix . 'lectus_progress');
define('LECTUS_TABLE_CERTIFICATES', $wpdb->prefix . 'lectus_certificates');
define('LECTUS_TABLE_QA_QUESTIONS', $wpdb->prefix . 'lectus_qa_questions');
define('LECTUS_TABLE_QA_ANSWERS', $wpdb->prefix . 'lectus_qa_answers');
define('LECTUS_TABLE_MATERIALS', $wpdb->prefix . 'lectus_materials');

// Default settings
define('LECTUS_DEFAULT_ACCESS_DAYS', 365);
define('LECTUS_DEFAULT_COMPLETION_THRESHOLD', 80);
define('LECTUS_QA_RATE_LIMIT', 10);
```

---

For more detailed examples and use cases, see the [Developer Documentation](../DEVELOPER.md).