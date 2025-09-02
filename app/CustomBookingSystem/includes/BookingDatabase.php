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
        
        // Performance optimization: Only run schema updates when needed
        // Use WordPress transients to cache schema checks
        add_action('wp_loaded', array($this, 'maybe_update_schema'), 20);
    }
    
    /**
     * Performance optimization: Only run schema updates when needed
     */
    public function maybe_update_schema() {
        // Check schema version to avoid running expensive operations on every load
        $schema_version = get_option('leobo_booking_schema_version', '0');
        $current_version = '2.1.0';
        
        if (version_compare($schema_version, $current_version, '<')) {
            $this->create_table();
            update_option('leobo_booking_schema_version', $current_version);
        }
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
            adults int(11) NOT NULL DEFAULT 2,
            children int(11) NOT NULL DEFAULT 0,
            babies int(11) NOT NULL DEFAULT 0,
            accommodation varchar(255) NOT NULL,
            helicopter_package varchar(255) NULL,
            transfer_options text NULL,
            experiences text NULL,
            occasion varchar(100) NULL,
            full_name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(50) NOT NULL,
            home_address text NULL,
            country varchar(10) NULL,
            how_heard varchar(100) NULL,
            special_requests text,
            children_interests text NULL,
            flexible_dates tinyint(1) DEFAULT 0,
            total_amount decimal(10,2) NOT NULL,
            status varchar(50) DEFAULT 'pending',
            is_test_booking tinyint(1) DEFAULT 0,
            booking_source varchar(100) DEFAULT 'Online Form',
            test_submission_time datetime NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_checkin_date (checkin_date),
            INDEX idx_status (status),
            INDEX idx_created_at (created_at),
            INDEX idx_is_test (is_test_booking)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Migrate existing data if needed
        $this->migrate_name_fields();
    }
    
    /**
     * Migrate existing first_name and last_name to full_name and add new columns
     */
    private function migrate_name_fields() {
        global $wpdb;
        
        // Get all current columns
        $columns = $wpdb->get_results("SHOW COLUMNS FROM {$this->table_name}");
        $column_names = array_column($columns, 'Field');
        
        // Add new columns if they don't exist
        if (!in_array('transfer_options', $column_names)) {
            $wpdb->query("ALTER TABLE {$this->table_name} ADD COLUMN transfer_options text NULL AFTER helicopter_package");
            error_log('Added transfer_options column to booking table');
        }
        
        if (!in_array('experiences', $column_names)) {
            $wpdb->query("ALTER TABLE {$this->table_name} ADD COLUMN experiences text NULL AFTER transfer_options");
            error_log('Added experiences column to booking table');
        }
        
        if (!in_array('occasion', $column_names)) {
            $wpdb->query("ALTER TABLE {$this->table_name} ADD COLUMN occasion varchar(100) NULL AFTER experiences");
            error_log('Added occasion column to booking table');
        }
        
        if (!in_array('home_address', $column_names)) {
            $wpdb->query("ALTER TABLE {$this->table_name} ADD COLUMN home_address text NULL AFTER phone");
            error_log('Added home_address column to booking table');
        }
        
        if (!in_array('country', $column_names)) {
            $wpdb->query("ALTER TABLE {$this->table_name} ADD COLUMN country varchar(10) NULL AFTER home_address");
            error_log('Added country column to booking table');
        }
        
        if (!in_array('how_heard', $column_names)) {
            $wpdb->query("ALTER TABLE {$this->table_name} ADD COLUMN how_heard varchar(100) NULL AFTER country");
            error_log('Added how_heard column to booking table');
        }
        
        if (!in_array('children_interests', $column_names)) {
            $wpdb->query("ALTER TABLE {$this->table_name} ADD COLUMN children_interests text NULL AFTER special_requests");
            error_log('Added children_interests column to booking table');
        }
        
        if (!in_array('flexible_dates', $column_names)) {
            $wpdb->query("ALTER TABLE {$this->table_name} ADD COLUMN flexible_dates tinyint(1) DEFAULT 0 AFTER children_interests");
            error_log('Added flexible_dates column to booking table');
        }
        
        // Handle legacy name field migration
        $has_first_name = in_array('first_name', $column_names);
        $has_last_name = in_array('last_name', $column_names);
        $has_full_name = in_array('full_name', $column_names);
        
        // If we have old columns but no full_name column, we need to migrate
        if (($has_first_name || $has_last_name) && !$has_full_name) {
            // Add full_name column
            $wpdb->query("ALTER TABLE {$this->table_name} ADD COLUMN full_name varchar(255) NOT NULL DEFAULT '' AFTER occasion");
            
            // Migrate existing data
            if ($has_first_name && $has_last_name) {
                $wpdb->query("UPDATE {$this->table_name} SET full_name = TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) WHERE full_name = ''");
            } elseif ($has_first_name) {
                $wpdb->query("UPDATE {$this->table_name} SET full_name = first_name WHERE full_name = ''");
            } elseif ($has_last_name) {
                $wpdb->query("UPDATE {$this->table_name} SET full_name = last_name WHERE full_name = ''");
            }
            
            error_log('Migrated name fields to full_name');
        }
        
        // Clean up: rename contact_number to phone if it exists
        if (in_array('contact_number', $column_names) && !in_array('phone', $column_names)) {
            $wpdb->query("ALTER TABLE {$this->table_name} CHANGE contact_number phone varchar(50) NOT NULL");
            error_log('Renamed contact_number to phone');
        }
    }
    
    /**
     * Save a new booking request
     */
    public function save_booking($data) {
        global $wpdb;
        
        // Ensure table exists
        $this->create_table();
        
        // Prepare insert data
        $insert_data = array(
            'checkin_date' => $data['checkin_date'],
            'checkout_date' => $data['checkout_date'],
            'adults' => intval($data['adults'] ?? 2),
            'children' => intval($data['children'] ?? 0),
            'babies' => intval($data['babies'] ?? 0),
            'accommodation' => $data['accommodation'],
            'helicopter_package' => $data['helicopter_package'] ?? null,
            'transfer_options' => $data['transfer_options'] ?? null,
            'experiences' => $data['experiences'] ?? null,
            'occasion' => $data['occasion'] ?? null,
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'home_address' => $data['home_address'] ?? null,
            'country' => $data['country'] ?? null,
            'how_heard' => $data['how_heard'] ?? null,
            'special_requests' => $data['special_requests'] ?? '',
            'children_interests' => $data['children_interests'] ?? null,
            'flexible_dates' => isset($data['flexible_dates']) ? 1 : 0,
            'total_amount' => floatval($data['calculated_total'] ?? 0),
            'status' => 'pending',
            'created_at' => current_time('mysql')
        );
        
        // Add test booking fields if present
        if (isset($data['is_test_booking']) && $data['is_test_booking']) {
            $insert_data['is_test_booking'] = 1;
            $insert_data['booking_source'] = $data['booking_source'] ?? 'Test Form Submission';
            if (isset($data['test_submission_time'])) {
                $insert_data['test_submission_time'] = $data['test_submission_time'];
            }
        }
        
        $result = $wpdb->insert(
            $this->table_name,
            $insert_data,
            array(
                '%s', // checkin_date
                '%s', // checkout_date
                '%d', // adults
                '%d', // children
                '%d', // babies
                '%s', // accommodation
                '%s', // helicopter_package
                '%s', // transfer_options
                '%s', // experiences
                '%s', // occasion
                '%s', // full_name
                '%s', // email
                '%s', // phone
                '%s', // home_address
                '%s', // country
                '%s', // how_heard
                '%s', // special_requests
                '%s', // children_interests
                '%d', // flexible_dates
                '%f', // total_amount
                '%s', // status
                '%s'  // created_at
            )
        );
        
        if ($result === false) {
            error_log('Database insert error: ' . $wpdb->last_error);
        }
        
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
        
        // Check if table exists first
        if (!$this->table_exists()) {
            return array();
        }
        
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
     * Get recent test submissions
     */
    public function get_test_submissions($limit = 20) {
        global $wpdb;
        
        // Check if table exists first
        if (!$this->table_exists()) {
            return array();
        }
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE is_test_booking = 1 ORDER BY created_at DESC LIMIT %d",
                $limit
            )
        );
    }
    
    /**
     * Check if the bookings table exists
     */
    public function table_exists() {
        global $wpdb;
        
        $table_name = $wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'");
        return $table_name == $this->table_name;
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
    
    /**
     * Get bookings with filters for submissions page
     */
    public function get_bookings_with_filters($limit = 50, $status_filter = '', $test_filter = '', $search = '') {
        global $wpdb;
        
        $where_conditions = array();
        $where_values = array();
        
        // Status filter
        if (!empty($status_filter)) {
            $where_conditions[] = "status = %s";
            $where_values[] = $status_filter;
        }
        
        // Test mode filter
        if ($test_filter === 'test') {
            $where_conditions[] = "is_test_booking = 1";
        } elseif ($test_filter === 'live') {
            $where_conditions[] = "is_test_booking = 0";
        }
        
        // Search filter
        if (!empty($search)) {
            $where_conditions[] = "(full_name LIKE %s OR email LIKE %s OR phone LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }
        
        // Build WHERE clause
        $where_clause = '';
        if (!empty($where_conditions)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        }
        
        // Build query
        $query = "SELECT * FROM {$this->table_name} {$where_clause} ORDER BY created_at DESC LIMIT %d";
        $where_values[] = $limit;
        
        // Prepare and execute
        if (!empty($where_values)) {
            $prepared_query = $wpdb->prepare($query, $where_values);
        } else {
            $prepared_query = $wpdb->prepare($query, $limit);
        }
        
        return $wpdb->get_results($prepared_query);
    }
    
    /**
     * Get total submissions count
     */
    public function get_submissions_count() {
        global $wpdb;
        
        return $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
    }
    
    /**
     * Get test submissions count
     */
    public function get_test_submissions_count() {
        global $wpdb;
        
        return $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE is_test_booking = 1");
    }
    
    /**
     * Get booking by ID
     */
    public function get_booking_by_id($id) {
        global $wpdb;
        
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id)
        );
    }
    
    /**
     * Delete a booking
     */
    public function delete_booking($id) {
        global $wpdb;
        
        return $wpdb->delete(
            $this->table_name,
            array('id' => $id),
            array('%d')
        );
    }
}
