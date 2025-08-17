<?php
/**
 * Admin Email Notification Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$booking_id = $args['booking_id'];
$data = $args['booking_data'];
?>

<h2>New Booking Request #<?php echo esc_html($booking_id); ?></h2>

<table style="border-collapse: collapse; width: 100%;">
    <tr style="background: #f8f9fa;">
        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Booking ID:</strong></td>
        <td style="padding: 10px; border: 1px solid #ddd;">#<?php echo esc_html($booking_id); ?></td>
    </tr>
    <tr>
        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Guest Name:</strong></td>
        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc_html($data['first_name'] . ' ' . $data['last_name']); ?></td>
    </tr>
    <tr style="background: #f8f9fa;">
        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Email:</strong></td>
        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc_html($data['email']); ?></td>
    </tr>
    <tr>
        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Phone:</strong></td>
        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc_html($data['phone']); ?></td>
    </tr>
    <tr style="background: #f8f9fa;">
        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Check-in:</strong></td>
        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc_html(date('F j, Y', strtotime($data['checkin_date']))); ?></td>
    </tr>
    <tr>
        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Check-out:</strong></td>
        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc_html(date('F j, Y', strtotime($data['checkout_date']))); ?></td>
    </tr>
    <tr style="background: #f8f9fa;">
        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Guests:</strong></td>
        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc_html($data['guests']); ?></td>
    </tr>
    <tr>
        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Accommodation:</strong></td>
        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc_html($data['accommodation']); ?></td>
    </tr>
    <tr style="background: #f8f9fa;">
        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Total Amount:</strong></td>
        <td style="padding: 10px; border: 1px solid #ddd;">R <?php echo esc_html(number_format($data['calculated_total'], 2)); ?></td>
    </tr>
</table>

<?php if (!empty($data['special_requests'])): ?>
<h3>Special Requests:</h3>
<p><?php echo nl2br(esc_html($data['special_requests'])); ?></p>
<?php endif; ?>

<p><a href="<?php echo esc_url(admin_url('admin.php?page=leobo-booking-admin&booking_id=' . $booking_id)); ?>">View in Admin</a></p>
