<?php
/**
 * Leobo Booking System - Performance Monitor
 * 
 * This script helps monitor the performance impact of the booking system
 * and provides debugging information for optimization efforts.
 * 
 * Add this to your theme's functions.php temporarily to monitor performance:
 * require_once get_template_directory() . '/app/CustomBookingSystem/performance-monitor.php';
 */

// Only run in debug mode
if (!defined('WP_DEBUG') || !WP_DEBUG) {
    return;
}

class LeoboBookingPerformanceMonitor {
    
    private $start_time;
    private $memory_start;
    
    public function __construct() {
        $this->start_time = microtime(true);
        $this->memory_start = memory_get_usage();
        
        // Hook into various WordPress actions to monitor performance
        add_action('init', array($this, 'monitor_init'), 999);
        add_action('wp_loaded', array($this, 'monitor_wp_loaded'), 999);
        add_action('wp_footer', array($this, 'output_performance_stats'), 999);
        add_action('admin_footer', array($this, 'output_performance_stats'), 999);
    }
    
    public function monitor_init() {
        $this->log_checkpoint('init_complete', 'WordPress init action completed');
    }
    
    public function monitor_wp_loaded() {
        $this->log_checkpoint('wp_loaded', 'WordPress fully loaded');
    }
    
    public function output_performance_stats() {
        if (!is_super_admin()) {
            return;
        }
        
        $total_time = microtime(true) - $this->start_time;
        $total_memory = memory_get_usage() - $this->memory_start;
        $peak_memory = memory_get_peak_usage();
        $db_queries = get_num_queries();
        
        $booking_loaded = isset($GLOBALS['leobo_booking_system']);
        $should_load = function_exists('leobo_should_load_booking_system') ? 
                      leobo_should_load_booking_system() : 'Unknown';
        
        echo '
        <div style="
            position: fixed; 
            bottom: 10px; 
            right: 10px; 
            background: #333; 
            color: #fff; 
            padding: 15px; 
            border-radius: 5px; 
            font-family: monospace; 
            font-size: 11px;
            z-index: 99999;
            max-width: 350px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        ">
            <h4 style="margin: 0 0 10px 0; color: #d4b896;">🔍 Leobo Performance Monitor</h4>
            
            <div><strong>Page Load Stats:</strong></div>
            <div>⏱️ Total Time: ' . round($total_time * 1000, 2) . 'ms</div>
            <div>💾 Memory Used: ' . $this->format_bytes($total_memory) . '</div>
            <div>📊 Peak Memory: ' . $this->format_bytes($peak_memory) . '</div>
            <div>🗄️ DB Queries: ' . $db_queries . '</div>
            
            <hr style="border: 1px solid #555; margin: 10px 0;">
            
            <div><strong>Booking System:</strong></div>
            <div>📍 Should Load: ' . ($should_load ? 'YES' : 'NO') . '</div>
            <div>🎯 Actually Loaded: ' . ($booking_loaded ? 'YES' : 'NO') . '</div>
            <div>📄 Page Type: ' . $this->get_page_type() . '</div>
            
            ' . ($booking_loaded ? '
            <div style="color: #ff9800;">⚠️ Booking system is loaded</div>
            ' : '
            <div style="color: #4caf50;">✅ Booking system saved resources</div>
            ') . '
            
            <div style="margin-top: 10px; font-size: 10px; color: #ccc;">
                URL: ' . esc_html($_SERVER['REQUEST_URI']) . '
            </div>
        </div>';
    }
    
    private function log_checkpoint($name, $description) {
        $time = microtime(true) - $this->start_time;
        $memory = memory_get_usage() - $this->memory_start;
        
        error_log("Leobo Performance [{$name}]: {$description} - Time: " . round($time * 1000, 2) . "ms, Memory: " . $this->format_bytes($memory));
    }
    
    private function format_bytes($bytes) {
        $units = array('B', 'KB', 'MB', 'GB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    private function get_page_type() {
        if (is_admin()) return 'Admin';
        if (is_home()) return 'Home';
        if (is_single()) return 'Single Post';
        if (is_page()) return 'Page';
        if (is_category()) return 'Category';
        if (is_archive()) return 'Archive';
        return 'Other';
    }
}

// Initialize the performance monitor
new LeoboBookingPerformanceMonitor();