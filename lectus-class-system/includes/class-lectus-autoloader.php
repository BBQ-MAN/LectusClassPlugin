<?php
/**
 * Autoloader for Lectus Class System
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Autoloader {
    
    private static $prefix = 'Lectus_';
    
    public static function init() {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }
    
    public static function autoload($class_name) {
        // Check if class name starts with our prefix
        if (strpos($class_name, self::$prefix) !== 0) {
            return;
        }
        
        // Convert class name to file name
        $file_name = 'class-' . str_replace('_', '-', strtolower($class_name)) . '.php';
        
        // Define possible paths
        $paths = array(
            LECTUS_PLUGIN_DIR . 'includes/',
            LECTUS_PLUGIN_DIR . 'admin/',
            LECTUS_PLUGIN_DIR . 'public/',
            LECTUS_PLUGIN_DIR . 'includes/abstracts/',
            LECTUS_PLUGIN_DIR . 'includes/interfaces/',
            LECTUS_PLUGIN_DIR . 'includes/traits/',
        );
        
        // Try to load file from each path
        foreach ($paths as $path) {
            $file = $path . $file_name;
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
}