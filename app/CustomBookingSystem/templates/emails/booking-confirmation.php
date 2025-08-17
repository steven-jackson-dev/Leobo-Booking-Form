<?php
/**
 * Booking Confirmation Email Template (when admin confirms booking)
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$booking_id = $args['booking_id'];
$data = $args['booking_data'];
?>

<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <div style="background: #1a1a1a; color: white; padding: 20px; text-align: center;">
        <h1 style="margin: 0;">Leobo Private Reserve</h1>
        <p style="margin: 10px 0 0 0; opacity: 0.8;">Booking Confirmed</p>
    </div>
    
    <div style="padding: 30px; background: white;">
        <h2>Wonderful news, <?php echo esc_html($data['first_name']); ?>!</h2>
        
        <p>Your booking has been <strong>confirmed</strong>. We're excited to welcome you to Leobo Private Reserve!</p>
        
        <div style="background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #28a745;">
            <h3 style="margin-top: 0; color: #155724;">Confirmed Booking Details</h3>
            <p><strong>Booking Reference:</strong> #<?php echo esc_html($booking_id); ?></p>
            <p><strong>Dates:</strong> <?php echo esc_html(date('F j', strtotime($data['checkin_date']))); ?> - <?php echo esc_html(date('F j, Y', strtotime($data['checkout_date']))); ?></p>
            <p><strong>Guests:</strong> <?php echo esc_html($data['guests']); ?></p>
            <p><strong>Accommodation:</strong> <?php echo esc_html($data['accommodation']); ?></p>
            <p><strong>Total Amount:</strong> R <?php echo esc_html(number_format($data['calculated_total'], 2)); ?></p>
        </div>
        
        <h3>What's Next?</h3>
        <ul>
            <li>We'll send you detailed arrival information 48 hours before your check-in</li>
            <li>Please contact us if you have any special requirements</li>
            <li>Payment details will be provided separately</li>
        </ul>
        
        <p><strong>Contact us:</strong><br>
        Phone: <a href="tel:+27157931265">+27 (0)15 793 1265</a><br>
        Email: <a href="mailto:reservations@leobo.co.za">reservations@leobo.co.za</a></p>
        
        <p style="margin-top: 30px; font-style: italic;">We look forward to providing you with an unforgettable experience!</p>
    </div>
</div>
