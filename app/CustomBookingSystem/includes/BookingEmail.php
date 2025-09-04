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
        
        // Enhanced headers for SendGrid compatibility
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Leobo Booking System <noreply@leobo.co.za>',
            'Reply-To: ' . (!empty($booking_data['email']) ? $booking_data['email'] : 'noreply@leobo.co.za')
        );
        
        $admin_email = apply_filters('leobo_booking_admin_email', 'reservations@leobo.co.za');
        
        $result = wp_mail(
            $admin_email,
            $subject,
            $message,
            $headers
        );
        
        // Log the result for debugging
        if (!$result) {
            error_log('Leobo Booking: Admin notification email failed for booking #' . $booking_id . ' to ' . $admin_email);
        } else {
            error_log('Leobo Booking: Admin notification email sent successfully for booking #' . $booking_id . ' to ' . $admin_email);
        }
        
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
        
        // Enhanced headers for SendGrid compatibility
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Leobo Private Reserve <reservations@leobo.co.za>',
            'Reply-To: reservations@leobo.co.za'
        );
        
        // Ensure we have a valid email address
        $recipient_email = !empty($booking_data['email']) ? $booking_data['email'] : 'reservations@leobo.co.za';
        
        $result = wp_mail(
            $recipient_email,
            $subject,
            $message,
            $headers
        );
        
        // Log the result for debugging
        if (!$result) {
            error_log('Leobo Booking: User confirmation email failed for booking #' . $booking_id . ' to ' . $recipient_email);
        } else {
            error_log('Leobo Booking: User confirmation email sent successfully for booking #' . $booking_id . ' to ' . $recipient_email);
        }
        
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
