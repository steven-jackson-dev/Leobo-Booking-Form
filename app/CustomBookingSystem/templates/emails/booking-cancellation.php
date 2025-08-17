<?php
/**
 * Booking Cancellation Email Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$booking_id = $args['booking_id'];
$data = $args['booking_data'];
$reason = $args['reason'] ?? '';
?>

<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <div style="background: #dc3545; color: white; padding: 20px; text-align: center;">
        <h1 style="margin: 0;">Leobo Private Reserve</h1>
        <p style="margin: 10px 0 0 0; opacity: 0.8;">Booking Cancellation</p>
    </div>
    
    <div style="padding: 30px; background: white;">
        <h2>Dear <?php echo esc_html($data['first_name']); ?>,</h2>
        
        <p>We regret to inform you that your booking has been cancelled.</p>
        
        <div style="background: #f8d7da; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #dc3545;">
            <h3 style="margin-top: 0; color: #721c24;">Cancelled Booking Details</h3>
            <p><strong>Booking Reference:</strong> #<?php echo esc_html($booking_id); ?></p>
            <p><strong>Dates:</strong> <?php echo esc_html(date('F j', strtotime($data['checkin_date']))); ?> - <?php echo esc_html(date('F j, Y', strtotime($data['checkout_date']))); ?></p>
            <p><strong>Guests:</strong> <?php echo esc_html($data['guests']); ?></p>
            <p><strong>Accommodation:</strong> <?php echo esc_html($data['accommodation']); ?></p>
        </div>
        
        <?php if (!empty($reason)): ?>
        <h3>Reason for Cancellation:</h3>
        <p><?php echo nl2br(esc_html($reason)); ?></p>
        <?php endif; ?>
        
        <p>If you have any questions about this cancellation or would like to make a new booking, please don't hesitate to contact us.</p>
        
        <p><strong>Contact us:</strong><br>
        Phone: <a href="tel:+27157931265">+27 (0)15 793 1265</a><br>
        Email: <a href="mailto:reservations@leobo.co.za">reservations@leobo.co.za</a></p>
        
        <p style="margin-top: 30px;">We apologize for any inconvenience and hope to welcome you to Leobo Private Reserve in the future.</p>
    </div>
</div>
