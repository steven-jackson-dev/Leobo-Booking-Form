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
        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc_html($data['full_name']); ?></td>
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
        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Adults:</strong></td>
        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc_html($data['adults']); ?></td>
    </tr>
    <?php if (!empty($data['children'])): ?>
    <tr>
        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Children:</strong></td>
        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc_html($data['children']); ?></td>
    </tr>
    <?php endif; ?>
    <?php if (!empty($data['babies'])): ?>
    <tr style="background: #f8f9fa;">
        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Babies:</strong></td>
        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc_html($data['babies']); ?></td>
    </tr>
    <?php endif; ?>
    <?php if (!empty($data['home_address'])): ?>
    <tr>
        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Address:</strong></td>
        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc_html($data['home_address']); ?></td>
    </tr>
    <?php endif; ?>
    <?php if (!empty($data['country'])): ?>
    <tr style="background: #f8f9fa;">
        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Country:</strong></td>
        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc_html($data['country']); ?></td>
    </tr>
    <?php endif; ?>
    <?php if (!empty($data['how_heard'])): ?>
    <tr>
        <td style="padding: 10px; border: 1px solid #ddd;"><strong>How heard about us:</strong></td>
        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc_html($data['how_heard']); ?></td>
    </tr>
    <?php endif; ?>
    <tr>
        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Accommodation:</strong></td>
        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc_html($data['accommodation']); ?></td>
    </tr>
    <tr style="background: #f8f9fa;">
        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Total Amount:</strong></td>
        <td style="padding: 10px; border: 1px solid #ddd;">R <?php echo esc_html(number_format($data['calculated_total'], 2)); ?></td>
    </tr>
</table>

<?php if (!empty($data['helicopter_package']) || !empty($data['transfer_options']) || !empty($data['experiences']) || !empty($data['occasion'])): ?>
<h3>Booking Preferences & Extras</h3>
<table style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">
    <?php if (!empty($data['helicopter_package'])): ?>
    <tr>
        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Helicopter Package:</strong></td>
        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc_html($data['helicopter_package']); ?></td>
    </tr>
    <?php endif; ?>
    <?php if (!empty($data['transfer_options'])): ?>
    <tr style="background: #f8f9fa;">
        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Transfer Options:</strong></td>
        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc_html($data['transfer_options']); ?></td>
    </tr>
    <?php endif; ?>
    <?php if (!empty($data['experiences'])): ?>
    <tr>
        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Experiences:</strong></td>
        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc_html($data['experiences']); ?></td>
    </tr>
    <?php endif; ?>
    <?php if (!empty($data['occasion'])): ?>
    <tr style="background: #f8f9fa;">
        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Occasion:</strong></td>
        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc_html($data['occasion']); ?></td>
    </tr>
    <?php endif; ?>
</table>
<?php endif; ?>

<?php if (!empty($data['special_requests'])): ?>
<h3>Special Requests:</h3>
<p><?php echo nl2br(esc_html($data['special_requests'])); ?></p>
<?php endif; ?>

<?php if (!empty($data['children_interests'])): ?>
<h3>Children's Interests:</h3>
<p><?php echo nl2br(esc_html($data['children_interests'])); ?></p>
<?php endif; ?>

<p><a href="<?php echo esc_url(admin_url('admin.php?page=leobo-booking-admin&booking_id=' . $booking_id)); ?>">View in Admin</a></p>
