<?php
/**
 * Admin Email Notification Template - Professional Design
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$booking_id = $args['booking_id'];
$data = $args['booking_data'];
$nights = (strtotime($data['checkout_date']) - strtotime($data['checkin_date'])) / (60 * 60 * 24);
$submission_time = current_time('j M Y \a\t g:i A');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Booking Request #<?php echo esc_html($booking_id); ?></title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8f9fa; min-height: 100vh;">
        <tr>
            <td align="center" valign="top" style="padding: 30px 20px;">
                <!-- Main Container -->
                <table width="700" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden;">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); padding: 25px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 600;">
                                🚨 NEW BOOKING REQUEST
                            </h1>
                            <p style="color: rgba(255,255,255,0.9); margin: 8px 0 0 0; font-size: 14px;">
                                Request #<?php echo esc_html($booking_id); ?> • Received <?php echo $submission_time; ?>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Priority Info Bar -->
                    <tr>
                        <td style="background: #fff3cd; padding: 15px 30px; border-bottom: 3px solid #ffc107;">
                            <p style="margin: 0; color: #856404; font-weight: 600; text-align: center;">
                                ⏰ Response Required: Contact guest within 24 hours
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Guest Information -->
                    <tr>
                        <td style="padding: 30px;">
                            <div style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 8px; padding: 25px; margin-bottom: 25px;">
                                <h2 style="color: #2c3e50; margin: 0 0 20px 0; font-size: 20px; font-weight: 600; border-bottom: 2px solid #3498db; padding-bottom: 10px;">
                                    👤 Guest Information
                                </h2>
                                
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td width="30%" style="padding: 10px 0; color: #666; font-weight: 600; vertical-align: top;">Name:</td>
                                        <td style="padding: 10px 0; color: #2c3e50; font-weight: 700; font-size: 16px;"><?php echo esc_html($data['full_name']); ?></td>
                                    </tr>
                                    <tr style="background: rgba(52, 152, 219, 0.05);">
                                        <td style="padding: 10px 0; color: #666; font-weight: 600; vertical-align: top;">Email:</td>
                                        <td style="padding: 10px 0;">
                                            <a href="mailto:<?php echo esc_attr($data['email']); ?>" style="color: #3498db; text-decoration: none; font-weight: 600;">
                                                <?php echo esc_html($data['email']); ?>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px 0; color: #666; font-weight: 600; vertical-align: top;">Phone:</td>
                                        <td style="padding: 10px 0;">
                                            <a href="tel:<?php echo esc_attr($data['phone']); ?>" style="color: #27ae60; text-decoration: none; font-weight: 600;">
                                                <?php echo esc_html($data['phone']); ?>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php if (!empty($data['home_address'])): ?>
                                    <tr style="background: rgba(52, 152, 219, 0.05);">
                                        <td style="padding: 10px 0; color: #666; font-weight: 600; vertical-align: top;">Address:</td>
                                        <td style="padding: 10px 0; color: #2c3e50;"><?php echo esc_html($data['home_address']); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($data['country'])): ?>
                                    <tr>
                                        <td style="padding: 10px 0; color: #666; font-weight: 600; vertical-align: top;">Country:</td>
                                        <td style="padding: 10px 0; color: #2c3e50; font-weight: 600;"><?php echo esc_html($data['country']); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($data['how_heard'])): ?>
                                    <tr style="background: rgba(52, 152, 219, 0.05);">
                                        <td style="padding: 10px 0; color: #666; font-weight: 600; vertical-align: top;">How heard:</td>
                                        <td style="padding: 10px 0; color: #2c3e50;"><?php echo esc_html(ucwords(str_replace('_', ' ', $data['how_heard']))); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                            
                            <!-- Booking Details -->
                            <div style="background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%); border-radius: 8px; padding: 25px; margin-bottom: 25px; border-left: 4px solid #e74c3c;">
                                <h2 style="color: #c53030; margin: 0 0 20px 0; font-size: 20px; font-weight: 600; border-bottom: 2px solid #e74c3c; padding-bottom: 10px;">
                                    📅 Booking Details
                                </h2>
                                
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td width="30%" style="padding: 10px 0; color: #666; font-weight: 600;">Check-in:</td>
                                        <td style="padding: 10px 0; color: #c53030; font-weight: 700; font-size: 16px;">
                                            <?php echo esc_html(date('l, j F Y', strtotime($data['checkin_date']))); ?>
                                        </td>
                                    </tr>
                                    <tr style="background: rgba(197, 48, 48, 0.1);">
                                        <td style="padding: 10px 0; color: #666; font-weight: 600;">Check-out:</td>
                                        <td style="padding: 10px 0; color: #c53030; font-weight: 700; font-size: 16px;">
                                            <?php echo esc_html(date('l, j F Y', strtotime($data['checkout_date']))); ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px 0; color: #666; font-weight: 600;">Duration:</td>
                                        <td style="padding: 10px 0; color: #c53030; font-weight: 700;">
                                            <?php echo $nights; ?> night<?php echo $nights != 1 ? 's' : ''; ?>
                                        </td>
                                    </tr>
                                    <tr style="background: rgba(197, 48, 48, 0.1);">
                                        <td style="padding: 10px 0; color: #666; font-weight: 600;">Adults:</td>
                                        <td style="padding: 10px 0; color: #c53030; font-weight: 700;"><?php echo esc_html($data['adults']); ?></td>
                                    </tr>
                                    <?php if (!empty($data['children'])): ?>
                                    <tr>
                                        <td style="padding: 10px 0; color: #666; font-weight: 600;">Children:</td>
                                        <td style="padding: 10px 0; color: #c53030; font-weight: 700;"><?php echo esc_html($data['children']); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($data['babies'])): ?>
                                    <tr style="background: rgba(197, 48, 48, 0.1);">
                                        <td style="padding: 10px 0; color: #666; font-weight: 600;">Babies:</td>
                                        <td style="padding: 10px 0; color: #c53030; font-weight: 700;"><?php echo esc_html($data['babies']); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td style="padding: 10px 0; color: #666; font-weight: 600;">Accommodation:</td>
                                        <td style="padding: 10px 0; color: #c53030; font-weight: 700; font-size: 16px;"><?php echo esc_html($data['accommodation']); ?></td>
                                    </tr>
                                    <tr style="background: rgba(197, 48, 48, 0.1); border-top: 2px solid #e74c3c;">
                                        <td style="padding: 15px 0 10px 0; color: #666; font-weight: 600; font-size: 16px;">Estimated Total:</td>
                                        <td style="padding: 15px 0 10px 0; color: #27ae60; font-weight: 800; font-size: 20px;">
                                            R <?php echo esc_html(number_format($data['calculated_total'], 2)); ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            <!-- Extras & Preferences -->
                            <div style="background: linear-gradient(135deg, #f0f8ff 0%, #e6f3ff 100%); border-radius: 8px; padding: 25px; margin-bottom: 25px; border-left: 4px solid #3498db;">
                                <h3 style="color: #2980b9; margin: 0 0 20px 0; font-size: 18px; font-weight: 600; border-bottom: 2px solid #3498db; padding-bottom: 10px;">
                                    ✨ Extras & Preferences
                                </h3>
                                
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td width="30%" style="padding: 8px 0; color: #666; font-weight: 600;">Helicopter Package:</td>
                                        <td style="padding: 8px 0; color: #e74c3c; font-weight: 700;">
                                            <?php 
                                            if (!empty($data['helicopter_package']) && $data['helicopter_package'] !== 'null' && $data['helicopter_package'] !== null) {
                                                echo esc_html(ucwords(str_replace('_', ' ', $data['helicopter_package'])));
                                            } else {
                                                echo 'Not interested';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php if (!empty($data['transfer_options'])): ?>
                                    <tr style="background: rgba(52, 152, 219, 0.05);">
                                        <td style="padding: 8px 0; color: #666; font-weight: 600;">Transfer Options:</td>
                                        <td style="padding: 8px 0; color: #2c3e50; font-weight: 600;"><?php echo esc_html(ucwords(str_replace('_', ' ', $data['transfer_options']))); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($data['experiences'])): ?>
                                    <tr>
                                        <td style="padding: 8px 0; color: #666; font-weight: 600;">Experiences:</td>
                                        <td style="padding: 8px 0; color: #2c3e50; font-weight: 600;"><?php echo esc_html(ucwords(str_replace(['_', ','], [' ', ', '], $data['experiences']))); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($data['occasion'])): ?>
                                    <tr style="background: rgba(52, 152, 219, 0.05);">
                                        <td style="padding: 8px 0; color: #666; font-weight: 600;">Occasion:</td>
                                        <td style="padding: 8px 0; color: #e67e22; font-weight: 700;">
                                            <?php echo esc_html(ucwords(str_replace('_', ' ', $data['occasion']))); ?>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                            
                            <!-- Special Requests -->
                            <?php if (!empty($data['special_requests'])): ?>
                            <div style="background: #fff7e6; border: 1px solid #ffd700; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                                <h4 style="color: #b8860b; margin: 0 0 15px 0; font-size: 16px; font-weight: 600;">
                                    💬 Special Requests
                                </h4>
                                <div style="background: white; padding: 15px; border-radius: 4px; border-left: 3px solid #ffd700;">
                                    <p style="margin: 0; color: #333; line-height: 1.6; font-style: italic;">
                                        "<?php echo nl2br(esc_html($data['special_requests'])); ?>"
                                    </p>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Children's Interests -->
                            <?php if (!empty($data['children_interests'])): ?>
                            <div style="background: #f0f8ff; border: 1px solid #87ceeb; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                                <h4 style="color: #4682b4; margin: 0 0 15px 0; font-size: 16px; font-weight: 600;">
                                    🎈 Children's Interests
                                </h4>
                                <div style="background: white; padding: 15px; border-radius: 4px; border-left: 3px solid #87ceeb;">
                                    <p style="margin: 0; color: #333; line-height: 1.6; font-style: italic;">
                                        "<?php echo nl2br(esc_html($data['children_interests'])); ?>"
                                    </p>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Action Buttons -->
                            <div style="text-align: center; margin: 30px 0;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td width="50%" style="padding: 10px;">
                                            <a href="mailto:<?php echo esc_attr($data['email']); ?>?subject=Re: Booking Request #<?php echo esc_attr($booking_id); ?>" 
                                               style="display: inline-block; background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; padding: 12px 25px; text-decoration: none; border-radius: 6px; font-weight: 600; text-align: center; min-width: 120px;">
                                                📧 Reply to Guest
                                            </a>
                                        </td>
                                        <td width="50%" style="padding: 10px;">
                                            <a href="<?php echo esc_url(admin_url('admin.php?page=leobo-booking-admin&booking_id=' . $booking_id)); ?>" 
                                               style="display: inline-block; background: linear-gradient(135deg, #27ae60 0%, #229954 100%); color: white; padding: 12px 25px; text-decoration: none; border-radius: 6px; font-weight: 600; text-align: center; min-width: 120px;">
                                                🔍 View in Admin
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background: #2c3e50; padding: 20px; text-align: center;">
                            <p style="color: rgba(255,255,255,0.8); margin: 0; font-size: 14px;">
                                Leobo Private Reserve Admin System • <?php echo date('Y'); ?>
                            </p>
                            <p style="color: rgba(255,255,255,0.6); margin: 5px 0 0 0; font-size: 12px;">
                                This email was automatically generated from the booking system
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
