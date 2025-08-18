<?php
/**
 * Leobo Custom Booking System - Content Manager
 * Manages all text content and provides easy access to strings
 * 
 * @package LeoboCustomBookingSystem
 * @version 1.0.0
 */

class LeoboBookingContent {
    
    private static $content = null;
    
    /**
     * Load content configuration
     */
    private static function loadContent() {
        if (self::$content === null) {
            $content_file = dirname(__FILE__) . '/content-config.php';
            if (file_exists($content_file)) {
                self::$content = include $content_file;
            } else {
                self::$content = array();
                error_log('Leobo Booking: Content configuration file not found');
            }
        }
        return self::$content;
    }
    
    /**
     * Get content by key path (e.g., 'step_dates.title' or 'errors.general_error')
     * 
     * @param string $key Dot-notation key path
     * @param mixed $default Default value if key not found
     * @param array $replacements Sprintf replacements for dynamic content
     * @return mixed
     */
    public static function get($key, $default = '', $replacements = array()) {
        $content = self::loadContent();
        $keys = explode('.', $key);
        $value = $content;
        
        foreach ($keys as $k) {
            if (is_array($value) && isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }
        
        // Apply sprintf replacements if provided
        if (!empty($replacements) && is_string($value)) {
            $value = vsprintf($value, $replacements);
        }
        
        return $value;
    }
    
    /**
     * Get all content for a section
     * 
     * @param string $section Section name (e.g., 'step_dates', 'errors')
     * @return array
     */
    public static function getSection($section) {
        $content = self::loadContent();
        return isset($content[$section]) ? $content[$section] : array();
    }
    
    /**
     * Get step navigation labels
     * 
     * @return array
     */
    public static function getStepLabels() {
        return self::get('steps', array());
    }
    
    /**
     * Get experience options with titles and descriptions
     * 
     * @return array
     */
    public static function getExperiences() {
        return self::get('step_experiences.experiences', array());
    }
    
    /**
     * Get transfer options
     * 
     * @return array
     */
    public static function getTransferOptions() {
        return self::get('step_extras.transfers', array());
    }
    
    /**
     * Get helicopter package options
     * 
     * @return array
     */
    public static function getHelicopterPackages() {
        return self::get('step_extras.helicopter_packages', array());
    }
    
    /**
     * Get special occasion options
     * 
     * @return array
     */
    public static function getOccasionOptions() {
        return self::get('step_extras.occasions', array());
    }
    
    /**
     * Get validation messages for a specific step
     * 
     * @param string $step Step name
     * @return array
     */
    public static function getValidationMessages($step) {
        return self::get("step_{$step}.validation", array());
    }
    
    /**
     * Get error message
     * 
     * @param string $error_key Error key
     * @param string $default Default message
     * @return string
     */
    public static function getError($error_key, $default = 'An error occurred') {
        return self::get("errors.{$error_key}", $default);
    }
    
    /**
     * Get success message
     * 
     * @param string $success_key Success key
     * @param string $default Default message
     * @return string
     */
    public static function getSuccess($success_key, $default = 'Success') {
        return self::get("success.{$success_key}", $default);
    }
    
    /**
     * Get loading message
     * 
     * @param string $loading_key Loading key
     * @param string $default Default message
     * @return string
     */
    public static function getLoading($loading_key, $default = 'Loading...') {
        return self::get("loading.{$loading_key}", $default);
    }
    
    /**
     * Get field configuration for guest details step
     * 
     * @param string $field_name Field name
     * @return array
     */
    public static function getFieldConfig($field_name) {
        return self::get("step_guest_details.fields.{$field_name}", array());
    }
    
    /**
     * Get business information
     * 
     * @param string $info_key Info key
     * @param mixed $default Default value
     * @return mixed
     */
    public static function getBusinessInfo($info_key, $default = '') {
        return self::get("business.{$info_key}", $default);
    }
    
    /**
     * Get all content (for debugging or export)
     * 
     * @return array
     */
    public static function getAllContent() {
        return self::loadContent();
    }
    
    /**
     * Check if content key exists
     * 
     * @param string $key Dot-notation key path
     * @return bool
     */
    public static function has($key) {
        $content = self::loadContent();
        $keys = explode('.', $key);
        $value = $content;
        
        foreach ($keys as $k) {
            if (is_array($value) && isset($value[$k])) {
                $value = $value[$k];
            } else {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Format currency value
     * 
     * @param float $amount Amount to format
     * @param string $currency Currency symbol
     * @return string
     */
    public static function formatCurrency($amount, $currency = 'R') {
        return $currency . ' ' . number_format($amount, 2);
    }
    
    /**
     * Format guest count
     * 
     * @param int $adults Number of adults
     * @param int $children Number of children
     * @param int $babies Number of babies
     * @return string
     */
    public static function formatGuestCount($adults, $children = 0, $babies = 0) {
        $parts = array();
        
        if ($adults > 0) {
            $parts[] = $adults . ' ' . ($adults === 1 ? 'Adult' : 'Adults');
        }
        
        if ($children > 0) {
            $parts[] = $children . ' ' . ($children === 1 ? 'Child' : 'Children');
        }
        
        if ($babies > 0) {
            $parts[] = $babies . ' ' . ($babies === 1 ? 'Baby' : 'Babies');
        }
        
        return implode(', ', $parts);
    }
    
    /**
     * Format night count
     * 
     * @param int $nights Number of nights
     * @return string
     */
    public static function formatNights($nights) {
        return $nights . ' ' . ($nights === 1 ? 'night' : 'nights');
    }
}
