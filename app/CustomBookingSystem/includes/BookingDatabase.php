<?php
/**
 * Leobo Custom Booking System - Database Handler
 * Handles all database operations for booking requests
 * 
 * @package LeoboCustomBookingSystem
 * @version 1.0.0
 */

class LeoboBookingDatabase {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'leobo_booking_requests';
    }
    
    /**
     * Create the booking requests table
     */
    public function create_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            checkin_date date NOT NULL,
            checkout_date date NOT NULL,
            guests int(11) NOT NULL,
            accommodation varchar(255) NOT NULL,
            selected_packages text,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(50) NOT NULL,
            special_requests text,
            total_amount decimal(10,2) NOT NULL,
            status varchar(50) DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_checkin_date (checkin_date),
            INDEX idx_status (status),
            INDEX idx_created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Save a new booking request
     */
    public function save_booking($data) {
        global $wpdb;
        
        // Ensure table exists
        $this->create_table();
        
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'checkin_date' => $data['checkin_date'],
                'checkout_date' => $data['checkout_date'],
                'guests' => $data['guests'],
                'accommodation' => $data['accommodation'],
                'selected_packages' => $data['selected_packages'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'special_requests' => $data['special_requests'],
                'total_amount' => $data['calculated_total'],
                'status' => 'pending',
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%s', '%s')
        );
        
        return $result ? $wpdb->insert_id : false;
    }
    
    /**
     * Get booking by ID
     */
    public function get_booking($booking_id) {
        global $wpdb;
        
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $booking_id)
        );
    }
    
    /**
     * Get recent bookings with pagination
     */
    public function get_bookings($limit = 50, $offset = 0, $status = null) {
        global $wpdb;
        
        $where_clause = '';
        if ($status) {
            $where_clause = $wpdb->prepare(' WHERE status = %s', $status);
        }
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name}{$where_clause} ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $limit,
                $offset
            )
        );
    }
    
    /**
     * Update booking status
     */
    public function update_status($booking_id, $status) {
        global $wpdb;
        
        return $wpdb->update(
            $this->table_name,
            array('status' => $status),
            array('id' => $booking_id),
            array('%s'),
            array('%d')
        );
    }
    
    /**
     * Get booking statistics
     */
    public function get_stats() {
        global $wpdb;
        
        $stats = array();
        
        // Total bookings
        $stats['total'] = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        
        // Pending bookings
        $stats['pending'] = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$this->table_name} WHERE status = %s", 'pending')
        );
        
        // This month's bookings
        $stats['this_month'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE MONTH(created_at) = %d AND YEAR(created_at) = %d",
                date('n'),
                date('Y')
            )
        );
        
        // Total revenue (confirmed bookings only)
        $stats['revenue'] = $wpdb->get_var(
            $wpdb->prepare("SELECT SUM(total_amount) FROM {$this->table_name} WHERE status IN (%s, %s)", 'confirmed', 'completed')
        );
        
        return $stats;
    }
    
    /**
     * Delete old booking requests (cleanup function)
     */
    public function cleanup_old_bookings($days = 365) {
        global $wpdb;
        
        return $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$this->table_name} WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY) AND status IN ('cancelled', 'expired')",
                $days
            )
        );
    }
}
