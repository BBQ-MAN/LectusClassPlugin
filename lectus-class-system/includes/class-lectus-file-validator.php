<?php
/**
 * File Validation and Security for Lectus Class System
 * 
 * Provides comprehensive file upload validation and security measures
 * 
 * @package Lectus_Class_System
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_File_Validator {
    
    /**
     * Allowed file extensions and their MIME types
     */
    private static $allowed_types = array(
        // Documents
        'pdf' => array('application/pdf'),
        'doc' => array('application/msword'),
        'docx' => array('application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
        'ppt' => array('application/vnd.ms-powerpoint'),
        'pptx' => array('application/vnd.openxmlformats-officedocument.presentationml.presentation'),
        'xls' => array('application/vnd.ms-excel'),
        'xlsx' => array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        'txt' => array('text/plain'),
        
        // Images
        'jpg' => array('image/jpeg', 'image/pjpeg'),
        'jpeg' => array('image/jpeg', 'image/pjpeg'),
        'png' => array('image/png'),
        'gif' => array('image/gif'),
        'svg' => array('image/svg+xml'),
        
        // Archives
        'zip' => array('application/zip', 'application/x-zip-compressed'),
        'rar' => array('application/x-rar-compressed'),
        
        // Videos (for reference, usually external)
        'mp4' => array('video/mp4'),
        'avi' => array('video/x-msvideo'),
        'mov' => array('video/quicktime'),
    );
    
    /**
     * Maximum file size in bytes (50MB default)
     */
    const MAX_FILE_SIZE = 52428800; // 50MB
    
    /**
     * Validate uploaded file
     * 
     * @param array $file $_FILES array element
     * @param array $allowed_extensions Optional custom allowed extensions
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public static function validate_upload($file, $allowed_extensions = null) {
        // Check if file was uploaded
        if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return new WP_Error(
                'upload_error',
                self::get_upload_error_message($file['error'])
            );
        }
        
        // Check file size
        if ($file['size'] > self::MAX_FILE_SIZE) {
            return new WP_Error(
                'file_too_large',
                sprintf(
                    __('File size exceeds maximum allowed size of %s', 'lectus-class-system'),
                    size_format(self::MAX_FILE_SIZE)
                )
            );
        }
        
        // Check if file is empty
        if ($file['size'] === 0) {
            return new WP_Error(
                'empty_file',
                __('The uploaded file is empty', 'lectus-class-system')
            );
        }
        
        // Get file extension
        $file_info = pathinfo($file['name']);
        $extension = strtolower($file_info['extension'] ?? '');
        
        // Check extension
        $allowed = $allowed_extensions ?: array_keys(self::$allowed_types);
        if (!in_array($extension, $allowed, true)) {
            return new WP_Error(
                'invalid_file_type',
                sprintf(
                    __('File type %s is not allowed. Allowed types: %s', 'lectus-class-system'),
                    $extension,
                    implode(', ', $allowed)
                )
            );
        }
        
        // Verify MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!self::validate_mime_type($extension, $mime_type)) {
            return new WP_Error(
                'mime_mismatch',
                __('File type does not match its extension', 'lectus-class-system')
            );
        }
        
        // Additional security checks
        if (!self::is_safe_file($file['tmp_name'], $extension)) {
            return new WP_Error(
                'security_violation',
                __('File failed security validation', 'lectus-class-system')
            );
        }
        
        return true;
    }
    
    /**
     * Validate MIME type against extension
     * 
     * @param string $extension File extension
     * @param string $mime_type Detected MIME type
     * @return bool
     */
    private static function validate_mime_type($extension, $mime_type) {
        if (!isset(self::$allowed_types[$extension])) {
            return false;
        }
        
        return in_array($mime_type, self::$allowed_types[$extension], true);
    }
    
    /**
     * Perform additional security checks on file
     * 
     * @param string $file_path Path to uploaded file
     * @param string $extension File extension
     * @return bool
     */
    private static function is_safe_file($file_path, $extension) {
        // Check for PHP code in file
        if (self::contains_php_code($file_path)) {
            return false;
        }
        
        // Check for executable content
        if (self::is_executable($extension)) {
            return false;
        }
        
        // Scan for malware patterns (basic check)
        if (self::contains_malware_patterns($file_path)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if file contains PHP code
     * 
     * @param string $file_path Path to file
     * @return bool
     */
    private static function contains_php_code($file_path) {
        $content = file_get_contents($file_path, false, null, 0, 1024);
        
        $php_patterns = array(
            '<?php',
            '<?=',
            '<script language="php"',
            '<? ',
            '<%'
        );
        
        foreach ($php_patterns as $pattern) {
            if (stripos($content, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if extension is executable
     * 
     * @param string $extension File extension
     * @return bool
     */
    private static function is_executable($extension) {
        $executable_extensions = array(
            'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 
            'js', 'jar', 'msi', 'app', 'deb', 'rpm', 'dmg'
        );
        
        return in_array($extension, $executable_extensions, true);
    }
    
    /**
     * Check for common malware patterns
     * 
     * @param string $file_path Path to file
     * @return bool
     */
    private static function contains_malware_patterns($file_path) {
        $content = file_get_contents($file_path, false, null, 0, 4096);
        
        $malware_patterns = array(
            'eval(',
            'base64_decode(',
            'system(',
            'exec(',
            'shell_exec(',
            'passthru(',
            'phpinfo(',
            'file_get_contents(',
            'file_put_contents(',
            'fopen(',
            'readfile('
        );
        
        foreach ($malware_patterns as $pattern) {
            if (stripos($content, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get upload error message
     * 
     * @param int $error_code Upload error code
     * @return string
     */
    private static function get_upload_error_message($error_code) {
        $errors = array(
            UPLOAD_ERR_INI_SIZE => __('File exceeds upload_max_filesize directive', 'lectus-class-system'),
            UPLOAD_ERR_FORM_SIZE => __('File exceeds MAX_FILE_SIZE directive', 'lectus-class-system'),
            UPLOAD_ERR_PARTIAL => __('File was only partially uploaded', 'lectus-class-system'),
            UPLOAD_ERR_NO_FILE => __('No file was uploaded', 'lectus-class-system'),
            UPLOAD_ERR_NO_TMP_DIR => __('Missing temporary folder', 'lectus-class-system'),
            UPLOAD_ERR_CANT_WRITE => __('Failed to write file to disk', 'lectus-class-system'),
            UPLOAD_ERR_EXTENSION => __('Upload stopped by extension', 'lectus-class-system'),
        );
        
        return $errors[$error_code] ?? __('Unknown upload error', 'lectus-class-system');
    }
    
    /**
     * Sanitize filename for storage
     * 
     * @param string $filename Original filename
     * @return string Sanitized filename
     */
    public static function sanitize_filename($filename) {
        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        // Add timestamp to prevent conflicts
        $info = pathinfo($filename);
        $name = $info['filename'] ?? 'file';
        $ext = $info['extension'] ?? '';
        
        return sprintf(
            '%s_%s.%s',
            substr(sanitize_title($name), 0, 50),
            uniqid(),
            $ext
        );
    }
    
    /**
     * Get allowed file types for display
     * 
     * @return array
     */
    public static function get_allowed_types() {
        return array_keys(self::$allowed_types);
    }
    
    /**
     * Get maximum file size formatted
     * 
     * @return string
     */
    public static function get_max_file_size_formatted() {
        return size_format(self::MAX_FILE_SIZE);
    }
}