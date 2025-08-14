<?php
/**
 * Recreate materials table with proper structure
 * 
 * Usage: Run this file directly in browser or via WP-CLI:
 * wp eval-file tests/recreate-materials-table.php
 */

// Include WordPress if running directly
if (!defined('ABSPATH')) {
    $wp_load_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';
    if (file_exists($wp_load_path)) {
        require_once $wp_load_path;
    } else {
        die('Cannot find wp-load.php');
    }
}

global $wpdb;

echo "<pre>";
echo "===== Materials Table Recreation Script =====\n\n";

$table_name = $wpdb->prefix . 'lectus_materials';

// 1. Check current table
echo "1. Checking existing table...\n";
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");

if ($table_exists) {
    echo "   Table exists. Backing up data...\n";
    
    // Backup existing data
    $existing_data = $wpdb->get_results("SELECT * FROM $table_name");
    echo "   Found " . count($existing_data) . " existing records\n";
    
    // Drop existing table
    echo "   Dropping existing table...\n";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
} else {
    echo "   Table does not exist\n";
    $existing_data = array();
}

// 2. Create new table with correct structure
echo "\n2. Creating new table with correct structure...\n";

$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE $table_name (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    course_id bigint(20) NOT NULL,
    lesson_id bigint(20) DEFAULT NULL,
    material_type enum('file','link') NOT NULL DEFAULT 'file',
    title varchar(255) NOT NULL,
    description text,
    file_name varchar(255) DEFAULT NULL,
    file_path varchar(500) DEFAULT NULL,
    file_url varchar(500) DEFAULT NULL,
    external_url varchar(1000) DEFAULT NULL,
    file_size bigint(20) DEFAULT NULL,
    file_type varchar(100) DEFAULT NULL,
    mime_type varchar(100) DEFAULT NULL,
    download_count int(11) DEFAULT 0,
    uploaded_by bigint(20) NOT NULL,
    access_level enum('all','enrolled','instructor') DEFAULT 'enrolled',
    status enum('active','inactive') DEFAULT 'active',
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY course_id (course_id),
    KEY lesson_id (lesson_id),
    KEY uploaded_by (uploaded_by),
    KEY status (status),
    KEY material_type (material_type)
) $charset_collate;";

// Execute SQL directly
$result = $wpdb->query($sql);

if ($result === false) {
    echo "   ✗ Failed to create table!\n";
    echo "   Error: " . $wpdb->last_error . "\n";
} else {
    echo "   ✓ Table created successfully!\n";
}

// 3. Verify table structure
echo "\n3. Verifying table structure...\n";
$columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name");

$required_columns = ['material_type', 'external_url'];
$found_columns = array();

foreach ($columns as $column) {
    $found_columns[] = $column->Field;
    if (in_array($column->Field, $required_columns)) {
        echo "   ✓ Found required column: {$column->Field} ({$column->Type})\n";
    }
}

// Check if all required columns exist
$missing = array_diff($required_columns, $found_columns);
if (!empty($missing)) {
    echo "   ✗ Missing columns: " . implode(', ', $missing) . "\n";
} else {
    echo "   ✓ All required columns present\n";
}

// 4. Restore backup data if any
if (!empty($existing_data)) {
    echo "\n4. Restoring backup data...\n";
    $restored = 0;
    $failed = 0;
    
    foreach ($existing_data as $item) {
        $data = array(
            'course_id' => $item->course_id,
            'lesson_id' => isset($item->lesson_id) ? $item->lesson_id : null,
            'material_type' => isset($item->material_type) ? $item->material_type : 'file',
            'title' => $item->title,
            'description' => isset($item->description) ? $item->description : '',
            'file_name' => isset($item->file_name) ? $item->file_name : '',
            'file_path' => isset($item->file_path) ? $item->file_path : null,
            'file_url' => isset($item->file_url) ? $item->file_url : null,
            'external_url' => isset($item->external_url) ? $item->external_url : null,
            'file_size' => isset($item->file_size) ? $item->file_size : null,
            'file_type' => isset($item->file_type) ? $item->file_type : null,
            'mime_type' => isset($item->mime_type) ? $item->mime_type : null,
            'download_count' => isset($item->download_count) ? $item->download_count : 0,
            'uploaded_by' => $item->uploaded_by,
            'access_level' => isset($item->access_level) ? $item->access_level : 'enrolled',
            'status' => isset($item->status) ? $item->status : 'active'
        );
        
        $result = $wpdb->insert($table_name, $data);
        if ($result !== false) {
            $restored++;
        } else {
            $failed++;
            echo "   Failed to restore: " . $item->title . "\n";
        }
    }
    
    echo "   Restored: $restored records\n";
    if ($failed > 0) {
        echo "   Failed: $failed records\n";
    }
}

// 5. Test inserting a sample external link
echo "\n5. Testing external link insertion...\n";

// Get any course ID for testing
$course = get_posts(array(
    'post_type' => 'coursesingle',
    'posts_per_page' => 1,
    'post_status' => 'publish'
));

if (!empty($course)) {
    $course_id = $course[0]->ID;
    
    $test_data = array(
        'course_id' => $course_id,
        'material_type' => 'link',
        'title' => 'Test External Link - ' . date('Y-m-d H:i:s'),
        'description' => 'This is a test external link',
        'file_name' => 'google.com',
        'external_url' => 'https://www.google.com/test.pdf',
        'uploaded_by' => 1,
        'access_level' => 'enrolled',
        'status' => 'active'
    );
    
    $result = $wpdb->insert($table_name, $test_data);
    
    if ($result === false) {
        echo "   ✗ Test insert failed!\n";
        echo "   Error: " . $wpdb->last_error . "\n";
    } else {
        $test_id = $wpdb->insert_id;
        echo "   ✓ Test insert successful! ID: $test_id\n";
        
        // Verify it was saved
        $verify = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $test_id");
        if ($verify && $verify->external_url) {
            echo "   ✓ Verified: External URL saved correctly\n";
        } else {
            echo "   ✗ Verification failed!\n";
        }
    }
} else {
    echo "   No course found for testing\n";
}

// 6. Show current materials
echo "\n6. Current materials in database:\n";
$materials = $wpdb->get_results("SELECT id, title, material_type, external_url, file_url FROM $table_name ORDER BY created_at DESC LIMIT 10");

if ($materials) {
    foreach ($materials as $m) {
        $type_icon = ($m->material_type === 'link') ? '🔗' : '📁';
        echo "   $type_icon ID: {$m->id}, Title: {$m->title}, Type: {$m->material_type}\n";
        if ($m->material_type === 'link' && $m->external_url) {
            echo "      URL: {$m->external_url}\n";
        }
    }
} else {
    echo "   No materials found\n";
}

echo "\n===== Script Complete =====\n";
echo "The materials table has been recreated with the correct structure.\n";
echo "You should now be able to add both file uploads and external links.\n";
echo "</pre>";