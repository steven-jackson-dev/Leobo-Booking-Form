<?php
/**
 * User Confirmation Email Template - Professional Design
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$booking_id = $args['booking_id'];
$data = $args['booking_data'];
$nights = (strtotime($data['checkout_date']) - strtotime($data['checkin_date'])) / (60 * 60 * 24);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Request Received - Leobo Private Reserve</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; min-height: 100vh;">
        <tr>
            <td align="center" valign="top" style="padding: 40px 20px;">
                <!-- Main Container -->
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); overflow: hidden;">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 32px; font-weight: 300; letter-spacing: 2px;">
                                LEOBO PRIVATE RESERVE
                            </h1>
                            <div style="height: 2px; background-color: rgba(255,255,255,0.3); margin: 20px auto; width: 100px;"></div>
                            <p style="color: rgba(255,255,255,0.9); margin: 0; font-size: 16px; font-weight: 300;">
                                Booking Request Received
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Main Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            
                            <!-- Greeting -->
                            <h2 style="color: #2c3e50; margin: 0 0 20px 0; font-size: 28px; font-weight: 300;">
                                Thank you, <?php echo esc_html($data['full_name']); ?>!
                            </h2>
                            
                            <p style="color: #555; font-size: 16px; line-height: 1.6; margin: 0 0 30px 0;">
                                We have received your booking request and our reservations team will contact you within 24 hours to confirm availability and finalize your luxury safari experience.
                            </p>
                            
                            <!-- Booking Summary Card -->
                            <div style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 8px; padding: 30px; margin: 30px 0; border-left: 4px solid #3498db;">
                                <h3 style="color: #2c3e50; margin: 0 0 25px 0; font-size: 20px; font-weight: 500;">
                                    📋 Booking Summary
                                </h3>
                                
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding: 8px 0; color: #666; font-weight: 500; width: 40%;">Booking Reference:</td>
                                        <td style="padding: 8px 0; color: #2c3e50; font-weight: 600;">#<?php echo esc_html($booking_id); ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #666; font-weight: 500;">Dates:</td>
                                        <td style="padding: 8px 0; color: #2c3e50; font-weight: 600;">
                                            <?php echo esc_html(date('j M', strtotime($data['checkin_date']))); ?> - <?php echo esc_html(date('j M Y', strtotime($data['checkout_date']))); ?>
                                            <span style="color: #7f8c8d; font-weight: 400;">(<?php echo $nights; ?> night<?php echo $nights != 1 ? 's' : ''; ?>)</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #666; font-weight: 500;">Guests:</td>
                                        <td style="padding: 8px 0; color: #2c3e50; font-weight: 600;">
                                            <?php echo esc_html($data['adults']); ?> Adult<?php echo $data['adults'] != 1 ? 's' : ''; ?>
                                            <?php if (!empty($data['children'])): ?>
                                                • <?php echo esc_html($data['children']); ?> Child<?php echo $data['children'] != 1 ? 'ren' : ''; ?>
                                            <?php endif; ?>
                                            <?php if (!empty($data['babies'])): ?>
                                                • <?php echo esc_html($data['babies']); ?> Bab<?php echo $data['babies'] != 1 ? 'ies' : 'y'; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #666; font-weight: 500;">Accommodation:</td>
                                        <td style="padding: 8px 0; color: #2c3e50; font-weight: 600;"><?php echo esc_html($data['accommodation']); ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #666; font-weight: 500;">Helicopter Package:</td>
                                        <td style="padding: 8px 0; color: #e74c3c; font-weight: 600;">
                                            <?php 
                                            if (!empty($data['helicopter_package']) && $data['helicopter_package'] !== 'null' && $data['helicopter_package'] !== null) {
                                                echo esc_html(ucwords(str_replace('_', ' ', $data['helicopter_package'])));
                                            } else {
                                                echo 'Not interested';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 0 0 0; color: #666; font-weight: 500; border-top: 1px solid #ddd;">Estimated Total:</td>
                                        <td style="padding: 12px 0 0 0; color: #27ae60; font-weight: 700; font-size: 18px; border-top: 1px solid #ddd;">
                                            R <?php echo esc_html(number_format($data['calculated_total'], 2)); ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            <!-- Additional Details -->
                            <?php if (!empty($data['transfer_options']) || !empty($data['experiences']) || !empty($data['occasion'])): ?>
                            <div style="background: #fff; border: 1px solid #e9ecef; border-radius: 8px; padding: 25px; margin: 25px 0;">
                                <h4 style="color: #2c3e50; margin: 0 0 15px 0; font-size: 16px; font-weight: 600;">
                                    ✨ Additional Preferences
                                </h4>
                                <?php if (!empty($data['transfer_options'])): ?>
                                <p style="margin: 8px 0; color: #555;"><strong>Transfer:</strong> <?php echo esc_html(ucwords(str_replace('_', ' ', $data['transfer_options']))); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($data['experiences'])): ?>
                                <p style="margin: 8px 0; color: #555;"><strong>Experiences:</strong> <?php echo esc_html(ucwords(str_replace(['_', ','], [' ', ', '], $data['experiences']))); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($data['occasion'])): ?>
                                <p style="margin: 8px 0; color: #555;"><strong>Occasion:</strong> <?php echo esc_html(ucwords(str_replace('_', ' ', $data['occasion']))); ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Special Requests -->
                            <?php if (!empty($data['special_requests'])): ?>
                            <div style="background: #fff7e6; border: 1px solid #ffd700; border-radius: 8px; padding: 20px; margin: 25px 0;">
                                <h4 style="color: #b8860b; margin: 0 0 10px 0; font-size: 16px; font-weight: 600;">
                                    💬 Special Requests
                                </h4>
                                <p style="margin: 0; color: #8b6914; line-height: 1.5;"><?php echo nl2br(esc_html($data['special_requests'])); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Children's Interests -->
                            <?php if (!empty($data['children_interests'])): ?>
                            <div style="background: #f0f8ff; border: 1px solid #87ceeb; border-radius: 8px; padding: 20px; margin: 25px 0;">
                                <h4 style="color: #4682b4; margin: 0 0 10px 0; font-size: 16px; font-weight: 600;">
                                    🎈 Children's Interests
                                </h4>
                                <p style="margin: 0; color: #2f4f4f; line-height: 1.5;"><?php echo nl2br(esc_html($data['children_interests'])); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Next Steps -->
                            <div style="background: linear-gradient(135deg, #e8f5e8 0%, #d4edda 100%); border-radius: 8px; padding: 25px; margin: 30px 0; border-left: 4px solid #28a745;">
                                <h4 style="color: #155724; margin: 0 0 15px 0; font-size: 18px; font-weight: 600;">
                                    🎯 What happens next?
                                </h4>
                                <ul style="margin: 0; padding-left: 20px; color: #155724; line-height: 1.6;">
                                    <li style="margin: 8px 0;">Our reservations team will review your request</li>
                                    <li style="margin: 8px 0;">We'll check availability for your selected dates</li>
                                    <li style="margin: 8px 0;">You'll receive a detailed proposal within 24 hours</li>
                                    <li style="margin: 8px 0;">We'll arrange payment and finalize your safari adventure</li>
                                </ul>
                            </div>
                            
                        </td>
                    </tr>
                    
                    <!-- Contact Section -->
                    <tr>
                        <td style="background: #f8f9fa; padding: 30px; border-top: 1px solid #e9ecef;">
                            <h4 style="color: #2c3e50; margin: 0 0 20px 0; font-size: 18px; text-align: center;">
                                📞 Get in Touch
                            </h4>
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="50%" style="text-align: center; padding: 10px;">
                                        <div style="color: #666; font-size: 14px; margin-bottom: 5px;">Phone</div>
                                        <a href="tel:+27157931265" style="color: #3498db; text-decoration: none; font-weight: 600;">
                                            +27 (0)15 793 1265
                                        </a>
                                    </td>
                                    <td width="50%" style="text-align: center; padding: 10px;">
                                        <div style="color: #666; font-size: 14px; margin-bottom: 5px;">Email</div>
                                        <a href="mailto:reservations@leobo.co.za" style="color: #3498db; text-decoration: none; font-weight: 600;">
                                            reservations@leobo.co.za
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background: #2c3e50; padding: 25px; text-align: center;">
                            <p style="color: rgba(255,255,255,0.7); margin: 0; font-size: 14px; line-height: 1.5;">
                                Thank you for choosing Leobo Private Reserve for your luxury safari experience.<br>
                                We look forward to hosting you in the heart of the African wilderness.
                            </p>
                            <div style="margin: 15px 0 0 0;">
                                <p style="color: rgba(255,255,255,0.5); margin: 0; font-size: 12px;">
                                    Leobo Private Reserve • Waterberg • South Africa
                                </p>
                            </div>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
