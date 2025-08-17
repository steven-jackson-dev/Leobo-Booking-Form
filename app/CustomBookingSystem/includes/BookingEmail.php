<?php
/**
 * Leobo Custom Booking System - Email Handler
 * Handles all email notifications with template support
 * 
 * @package LeoboCustomBookingSystem
 * @version 1.0.0
 */

class BookingEmail {
    
    private $template_path;
    
    public function __construct() {
        $this->template_path = dirname(__FILE__) . '/../templates/emails/';
    }
    
    /**
     * Send admin notification email
     */
    public function send_admin_notification($booking_id, $booking_data, $prefix = '') {
        $subject = $prefix . ' New Booking Request #' . $booking_id . ' - Leobo Private Reserve';
        $message = $this->get_email_template('admin-notification', array(
            'booking_id' => $booking_id,
            'booking_data' => $booking_data,
            'prefix' => $prefix
        ));
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        error_log('Sending admin notification email to: ' . apply_filters('leobo_booking_admin_email', 'reservations@leobo.co.za'));
        
        $result = wp_mail(
            apply_filters('leobo_booking_admin_email', 'reservations@leobo.co.za'),
            $subject,
            $message,
            $headers
        );
        
        error_log('Admin email send result: ' . ($result ? 'SUCCESS' : 'FAILED'));
        return $result;
    }
    
    /**
     * Send user confirmation email
     */
    public function send_user_confirmation($booking_id, $booking_data, $prefix = '') {
        $subject = $prefix . ' Booking Request Received - Leobo Private Reserve';
        $message = $this->get_email_template('user-confirmation', array(
            'booking_id' => $booking_id,
            'booking_data' => $booking_data,
            'prefix' => $prefix
        ));
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        error_log('Sending user confirmation email to: ' . $booking_data['email']);
        
        $result = wp_mail(
            $booking_data['email'],
            $subject,
            $message,
            $headers
        );
        
        error_log('User email send result: ' . ($result ? 'SUCCESS' : 'FAILED'));
        return $result;
    }
    
    /**
     * Send booking confirmation email (when admin confirms)
     */
    public function send_booking_confirmation($booking_id, $booking_data) {
        $subject = 'Booking Confirmed #' . $booking_id . ' - Leobo Private Reserve';
        $message = $this->get_email_template('booking-confirmation', array(
            'booking_id' => $booking_id,
            'booking_data' => $booking_data
        ));
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        return wp_mail(
            $booking_data['email'],
            $subject,
            $message,
            $headers
        );
    }
    
    /**
     * Send booking cancellation email
     */
    public function send_cancellation_notification($booking_id, $booking_data, $reason = '') {
        $subject = 'Booking Cancelled #' . $booking_id . ' - Leobo Private Reserve';
        $message = $this->get_email_template('booking-cancellation', array(
            'booking_id' => $booking_id,
            'booking_data' => $booking_data,
            'reason' => $reason
        ));
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        return wp_mail(
            $booking_data['email'],
            $subject,
            $message,
            $headers
        );
    }
    
    /**
     * Get email template content
     */
    private function get_email_template($template_name, $args = array()) {
        $template_file = $this->template_path . $template_name . '.php';
        
        if (!file_exists($template_file)) {
            error_log("Leobo Booking: Email template not found: {$template_file}");
            return $this->get_fallback_template($template_name, $args);
        }
        
        ob_start();
        include $template_file;
        $content = ob_get_clean();
        
        // Apply filters for customization
        return apply_filters("leobo_booking_{$template_name}_email_template", $content, $args);
    }
    
    /**
     * Fallback email template for missing templates
     */
    private function get_fallback_template($template_name, $args) {
        $booking_id = $args['booking_id'] ?? 'N/A';
        $booking_data = $args['booking_data'] ?? array();
        
        $content = "<h2>Leobo Private Reserve - {$template_name}</h2>";
        $content .= "<p>Booking Reference: #{$booking_id}</p>";
        
        if (!empty($booking_data)) {
            $content .= "<p>Guest: " . esc_html($booking_data['full_name'] ?? '') . "</p>";
            $content .= "<p>Email: " . esc_html($booking_data['email'] ?? '') . "</p>";
            $content .= "<p>Check-in: " . esc_html($booking_data['checkin_date'] ?? '') . "</p>";
            $content .= "<p>Check-out: " . esc_html($booking_data['checkout_date'] ?? '') . "</p>";
            $content .= "<p>Adults: " . esc_html($booking_data['adults'] ?? '') . "</p>";
            if (!empty($booking_data['children'])) {
                $content .= "<p>Children: " . esc_html($booking_data['children']) . "</p>";
            }
            if (!empty($booking_data['special_requests'])) {
                $content .= "<p>Special Requests: " . esc_html($booking_data['special_requests']) . "</p>";
            }
        }
        
        return $content;
    }
    
    /**
     * Test email functionality
     */
    public function test_email($recipient = null) {
        $test_recipient = $recipient ?: get_option('admin_email');
        
        $subject = 'Leobo Booking System - Email Test';
        $message = '<h2>Email Test Successful</h2><p>Your booking system email functionality is working correctly.</p>';
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        return wp_mail($test_recipient, $subject, $message, $headers);
    }
}
