<?php
/**
 * Leobo Custom Booking System - Multi-Step Booking Form Template
 * Matches the exact design from the provided images
 * Enhanced with test mode capabilities for single-truth approach
 * 
 * @package LeoboCustomBookingSystem
 * @version 3.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$form_id = $args['form_id'] ?? 'leobo-booking-form';
$atts = $args['attributes'] ?? array();

// Check if this is test mode
$is_test_mode = isset($atts['test_mode']) && $atts['test_mode'];
$is_embedded_admin = isset($atts['embedded_admin']) && $atts['embedded_admin'];

// Test data for pre-filling (only used in test mode)
$test_data = array(
    'checkin_date' => '2025-08-25',
    'checkout_date' => '2025-08-28', 
    'adults' => '4',
    'children' => '2',
    'babies' => '1',
    'full_name' => 'John Test-Safari',
    'email' => 'john.test@example.com',
    'contact_number' => '+27 11 123 4567',
    'home_address' => '123 Test Street, Safari City',
    'country' => 'ZA',
    'how_heard' => 'google',
    'special_requests' => 'This is a test booking submission. Please celebrate dietary requirements and ensure all facilities are ready for our test stay.',
    'children_interests' => 'Stargazing, game viewing, and swimming'
);
?>

<?php if ($is_test_mode && !$is_embedded_admin): ?>
<!-- Test Mode Controls Panel -->
<div class="test-mode-panel">
    <div class="test-panel-header">
        <div class="test-badge">🧪 TEST MODE</div>
        <div class="test-info">
            <strong>Live Form with Test Data:</strong> This is the actual booking form with pre-filled test data for quick testing.
        </div>
        <button type="button" class="test-panel-toggle" onclick="toggleTestPanel()">
            <span class="toggle-text">Controls</span>
            <span class="toggle-icon">▼</span>
        </button>
    </div>
    <div class="test-panel-controls" id="test-panel-controls">
        <div class="test-controls-grid">
            <div class="test-control-group">
                <label>Quick Actions:</label>
                <div class="test-buttons">
                    <button type="button" class="test-btn" onclick="fillTestData()">📝 Fill Test Data</button>
                    <button type="button" class="test-btn" onclick="randomizeTestData()">🎲 Randomize Data</button>
                    <button type="button" class="test-btn" onclick="clearFormData()">🗑️ Clear Form</button>
                </div>
            </div>
            <div class="test-control-group">
                <label>Jump to Step:</label>
                <div class="test-buttons">
                    <button type="button" class="test-btn-small" onclick="goToStep(1)">1</button>
                    <button type="button" class="test-btn-small" onclick="goToStep(2)">2</button>
                    <button type="button" class="test-btn-small" onclick="goToStep(3)">3</button>
                    <button type="button" class="test-btn-small" onclick="goToStep(4)">4</button>
                </div>
            </div>
            <div class="test-control-group">
                <label>Test Scenarios:</label>
                <div class="test-buttons">
                    <button type="button" class="test-btn" onclick="loadFamilyScenario()">👨‍👩‍👧‍👦 Family</button>
                    <button type="button" class="test-btn" onclick="loadCoupleScenario()">💑 Couple</button>
                    <button type="button" class="test-btn" onclick="loadGroupScenario()">👥 Group</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div id="leobo-booking-system" class="leobo-booking-wrapper<?php echo $is_test_mode ? ' test-mode-active' : ''; ?>">
    
    <!-- Header Progress -->
    <div class="booking-header-progress">
        <div class="progress-step active" data-step="1">
            <span class="step-icon">📅</span>
            <span class="step-text">AVAILABILITY CHECK</span>
        </div>
        <div class="progress-step" data-step="2">
            <span class="step-icon">👁</span>
            <span class="step-text">TAILOR YOUR STAY</span>
        </div>
        <div class="progress-step" data-step="3">
            <span class="step-icon">👤</span>
            <span class="step-text">GUEST INFORMATION</span>
        </div>
        <div class="progress-step" data-step="4">
            <span class="step-icon">🧩</span>
            <span class="step-text">RESERVED & READY</span>
        </div>
    </div>

    <div class="booking-content-wrapper">
        
        <!-- Main Content Area -->
        <div class="booking-main-content">
            
            <form id="<?php echo esc_attr($form_id); ?>" class="leobo-booking-form" method="post">
                
                <!-- Step 1: Availability Check -->
                <div class="booking-step booking-step-1 active" data-step="1">
                    
                    <div class="step-content">
                        
                        <!-- Dates Section -->
                        <div class="form-section dates-section">
                            <h3 class="section-title">Dates</h3>
                            
                            <div class="date-input-wrapper">
                                <!-- Custom Date Picker -->
                                <div class="custom-date-picker">
                                    <div class="date-picker-trigger" id="date-picker-trigger">
                                        <div class="selected-dates">
                                            <div class="arrival-display">
                                                <span class="date-label">Arrival</span>
                                                <span class="date-value" id="arrival-display">Select date</span>
                                            </div>
                                            <div class="date-separator">→</div>
                                            <div class="departure-display">
                                                <span class="date-label">Departure</span>
                                                <span class="date-value" id="departure-display">Select date</span>
                                            </div>
                                        </div>
                                        <div class="date-picker-icon">📅</div>
                                    </div>
                                    
                                    <!-- Calendar Widget -->
                                    <div class="calendar-widget" id="calendar-widget" style="display: none;">
                                        <div class="calendar-header">
                                            <button type="button" class="calendar-nav prev-month" id="prev-month">‹</button>
                                            <div class="calendar-title" id="calendar-title">January 2024</div>
                                            <button type="button" class="calendar-nav next-month" id="next-month">›</button>
                                        </div>
                                        <div class="calendar-body">
                                            <div class="calendar-weekdays">
                                                <div class="weekday">Su</div>
                                                <div class="weekday">Mo</div>
                                                <div class="weekday">Tu</div>
                                                <div class="weekday">We</div>
                                                <div class="weekday">Th</div>
                                                <div class="weekday">Fr</div>
                                                <div class="weekday">Sa</div>
                                            </div>
                                            <div class="calendar-days" id="calendar-days">
                                                <!-- Days will be generated by JavaScript -->
                                            </div>
                                        </div>
                                        <div class="calendar-footer">
                                            <button type="button" class="btn btn-secondary" id="calendar-clear">Clear</button>
                                            <button type="button" class="btn btn-primary" id="calendar-apply">Apply</button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="date-info" id="date-range-info" style="display: none;">
                                    <span id="date-range-text"></span>
                                    <span id="nights-count"></span>
                                </div>
                                
                                <div class="date-availability-notice">
                                    📅 Different colors indicate seasonal pricing: <span style="color: #4caf50;">Green (Standard)</span>, <span style="color: #ff8f00;">Orange (Peak)</span>, <span style="color: #dc267f;">Pink (Christmas)</span>. Legend shows below calendar.
                                </div>
                                
                                <div class="date-flexible-option">
                                    <label class="checkbox-label">
                                        <input type="checkbox" id="flexible-dates" name="flexible_dates" />
                                        <span class="checkmark"></span>
                                        My dates are flexible
                                    </label>
                                </div>
                                
                                <!-- Hidden inputs for form submission -->
                                <input type="hidden" id="arrival-date" name="checkin_date" value="<?php echo $is_test_mode ? esc_attr($test_data['checkin_date']) : date('Y-m-d', strtotime('+7 days')); ?>" />
                                <input type="hidden" id="departure-date" name="checkout_date" value="<?php echo $is_test_mode ? esc_attr($test_data['checkout_date']) : date('Y-m-d', strtotime('+10 days')); ?>" />
                            </div>
                        </div>

                        <!-- Guests and What's Included Grid -->
                        <div class="guests-included-grid">
                            <!-- Guest Section -->
                            <div class="form-section guests-section">
                                
                                <!-- Adults -->
                                <div class="guest-type-row">
                                    <label class="guest-label">Adults</label>
                                    <div class="guest-counter">
                                        <button type="button" class="counter-btn minus" data-target="adults">-</button>
                                        <span class="counter-value" id="adults-count"><?php echo $is_test_mode ? esc_html($test_data['adults']) : '1'; ?></span>
                                        <button type="button" class="counter-btn plus" data-target="adults">+</button>
                                    </div>
                                </div>

                                <!-- Children -->
                                <div class="guest-type-row">
                                    <label class="guest-label">Children (4+ years)</label>
                                    <div class="guest-counter">
                                        <button type="button" class="counter-btn minus" data-target="children">-</button>
                                        <span class="counter-value" id="children-count"><?php echo $is_test_mode ? esc_html($test_data['children']) : '0'; ?></span>
                                        <button type="button" class="counter-btn plus" data-target="children">+</button>
                                    </div>
                                </div>

                                <!-- Babies -->
                                <div class="guest-type-row">
                                    <label class="guest-label">Baby (0-3 years)</label>
                                    <div class="guest-counter">
                                        <button type="button" class="counter-btn minus" data-target="babies">-</button>
                                        <span class="counter-value" id="babies-count"><?php echo $is_test_mode ? esc_html($test_data['babies']) : '0'; ?></span>
                                        <button type="button" class="counter-btn plus" data-target="babies">+</button>
                                    </div>
                                </div>
                                
                                <!-- Hidden inputs -->
                                <input type="hidden" id="adults" name="adults" value="<?php echo $is_test_mode ? esc_attr($test_data['adults']) : '1'; ?>" />
                                <input type="hidden" id="children" name="children" value="<?php echo $is_test_mode ? esc_attr($test_data['children']) : '0'; ?>" />
                                <input type="hidden" id="babies" name="babies" value="<?php echo $is_test_mode ? esc_attr($test_data['babies']) : '0'; ?>" />
                            </div>

                            <!-- What's Included Section -->
                            <div class="form-section included-section">
                                <h3 class="section-title">What's included in the price?</h3>
                                <ul class="included-list">
                                    <li>Exclusive use of entire 8000 hectare (20,000 acre) private reserve</li>
                                    <li>Full use of Observatory Villa with all facilities</li>
                                    <li>All meals, house drinks, wines and spirits included</li>
                                    <li>Chef, Butler, Game Ranger, and full staff</li>
                                    <li>Game Drives, Quad Bike Safari, Dirt Bike Safari, GS Moon Off-road Buggy</li>
                                    <li>Fishing, Day at Beach Camp, Afternoons at Mountain Lookout</li>
                                    <li>Rock Art Viewing, Clifftop Mountain Walks, Cave Walks</li>
                                    <li>Paint Balling, Clay Pigeon Shooting, Clifftop Camping, Hippo Camping</li>
                                    <li>Wood-fire Pizzas at Hippo Deck, Dinner at Romance Bush Deck</li>
                                    <li>Drinks at Bone Bar, Sunrise at River Lookout, Boma Dinners</li>
                                    <li>Research Grade Observatory with automated 20-inch telescope</li>
                                    <li>Heated Infinity Pool, Jacuzzi, 3D TV, Wi-Fi, Multi-room music system</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Step Navigation -->
                        <div class="step-navigation">
                            <button type="button" class="btn btn-primary next-step" data-next="2">
                                NEXT
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Tailor Your Stay -->
                <div class="booking-step booking-step-2" data-step="2">
                    <div class="step-content">
                        
                        <h2 class="step-title">Tailor your stay</h2>
                        <p class="step-subtitle">The following are optional extras available for your stay, at additional costs:</p>

                        <!-- Arrival Section -->
                        <div class="form-section">
                            <h3 class="section-title">ARRIVAL</h3>
                            <p class="section-subtitle">Transfer requests?</p>
                            
                            <div class="checkbox-group">
                                <label class="checkbox-option">
                                    <input type="checkbox" name="transfer[]" value="private_helicopter" />
                                    <span class="checkmark"></span>
                                    Private helicopter transfer
                                </label>
                                <label class="checkbox-option">
                                    <input type="checkbox" name="transfer[]" value="road_transfer" />
                                    <span class="checkmark"></span>
                                    Road transfer by Leobo
                                </label>
                            </div>
                        </div>

                        <!-- Helicopter Package Section -->
                        <div class="form-section">
                            <h3 class="section-title">HELICOPTER PACKAGE</h3>
                            <p class="section-subtitle">Would you like to add a helicopter adventure package?</p>
                            
                            <div class="radio-group">
                                <label class="radio-option">
                                    <input type="radio" name="helicopter_interested" value="yes" id="helicopter-yes" />
                                    <span class="radio-mark"></span>
                                    Yes, I'm interested
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="helicopter_interested" value="no" id="helicopter-no" checked />
                                    <span class="radio-mark"></span>
                                    No thanks
                                </label>
                            </div>
                            
                            <!-- Helicopter Package Options (shown conditionally) -->
                            <div id="helicopter-options" class="helicopter-options" style="display: none; margin-top: 20px;">
                                <div class="helicopter-info-box" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                                    <h4 style="margin: 0 0 10px 0; color: #495057;">🚁 Helicopter Adventure Package</h4>
                                    <p style="margin: 0; font-size: 14px; color: #6c757d;">
                                        Includes return helicopter transfer from OR Tambo, flying hours for scenic flights during your stay, 
                                        and pilot accommodation. Package rates vary by stay duration.
                                    </p>
                                </div>
                                
                                <div class="helicopter-package-grid">
                                    <label class="helicopter-package-option">
                                        <input type="radio" name="helicopter_package" value="3-nights" />
                                        <div class="package-details">
                                            <span class="package-title">3 Nights Package</span>
                                            <span class="package-hours">4 flying hours + 1 in-property hour</span>
                                            <span class="package-price">R 154,000</span>
                                        </div>
                                    </label>
                                    
                                    <label class="helicopter-package-option">
                                        <input type="radio" name="helicopter_package" value="4-nights" />
                                        <div class="package-details">
                                            <span class="package-title">4 Nights Package</span>
                                            <span class="package-hours">5 flying hours + 2 in-property hours</span>
                                            <span class="package-price">R 192,500</span>
                                        </div>
                                    </label>
                                    
                                    <label class="helicopter-package-option">
                                        <input type="radio" name="helicopter_package" value="5-nights" />
                                        <div class="package-details">
                                            <span class="package-title">5 Nights Package</span>
                                            <span class="package-hours">6 flying hours + 3 in-property hours</span>
                                            <span class="package-price">R 231,000</span>
                                        </div>
                                    </label>
                                    
                                    <label class="helicopter-package-option">
                                        <input type="radio" name="helicopter_package" value="6-nights" />
                                        <div class="package-details">
                                            <span class="package-title">6 Nights Package</span>
                                            <span class="package-hours">7 flying hours + 4 in-property hours</span>
                                            <span class="package-price">R 269,500</span>
                                        </div>
                                    </label>
                                    
                                    <label class="helicopter-package-option">
                                        <input type="radio" name="helicopter_package" value="7-nights" />
                                        <div class="package-details">
                                            <span class="package-title">7 Nights Package</span>
                                            <span class="package-hours">8 flying hours + 5 in-property hours</span>
                                            <span class="package-price">R 308,000</span>
                                        </div>
                                    </label>
                                </div>
                                
                                <div style="margin-top: 15px; padding: 10px; background: #e3f2fd; border-left: 4px solid #2196f3; font-size: 14px;">
                                    <strong>Additional flying time:</strong> R 38,500 per hour available upon request
                                </div>
                            </div>
                        </div>

                        <!-- Add-on Experiences Section -->
                        <div class="form-section">
                            <h3 class="section-title">ADD-ON EXPERIENCES</h3>
                            
                            <div class="experiences-grid">
                                <label class="experience-option">
                                    <input type="checkbox" name="experiences[]" value="mountaineering" />
                                    <span class="checkmark"></span>
                                    Mountaineering
                                </label>
                                <label class="experience-option">
                                    <input type="checkbox" name="experiences[]" value="starlit_safari" />
                                    <span class="checkmark"></span>
                                    Starlit safari
                                </label>
                                <label class="experience-option">
                                    <input type="checkbox" name="experiences[]" value="rhino_darting" />
                                    <span class="checkmark"></span>
                                    Rhino darting
                                </label>
                                <label class="experience-option">
                                    <input type="checkbox" name="experiences[]" value="big_five_game" />
                                    <span class="checkmark"></span>
                                    Big five game
                                </label>
                                <label class="experience-option">
                                    <input type="checkbox" name="experiences[]" value="horse_riding" />
                                    <span class="checkmark"></span>
                                    Horse riding
                                </label>
                                <label class="experience-option">
                                    <input type="checkbox" name="experiences[]" value="yoga_wellness" />
                                    <span class="checkmark"></span>
                                    Yoga and wellness experience
                                </label>
                            </div>
                        </div>

                        <!-- Occasion Section -->
                        <div class="form-section">
                            <h3 class="section-title">OCCASION?</h3>
                            
                            <div class="select-wrapper">
                                <select name="occasion" id="occasion">
                                    <option value="">Please select</option>
                                    <option value="birthday">Birthday</option>
                                    <option value="anniversary">Anniversary</option>
                                    <option value="honeymoon">Honeymoon</option>
                                    <option value="family_vacation">Family Vacation</option>
                                    <option value="corporate">Corporate Retreat</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <!-- Step Navigation -->
                        <div class="step-navigation">
                            <button type="button" class="btn btn-secondary back-step" data-back="1">
                                BACK
                            </button>
                            <button type="button" class="btn btn-primary next-step" data-next="3">
                                NEXT
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Guest Information -->
                <div class="booking-step booking-step-3" data-step="3">
                    <div class="step-content">
                        
                        <h2 class="step-title">Guest information</h2>
                        <p class="step-subtitle">You've selected your dates — now let's get to know you. A few essential details so we can tailor your stay.</p>

                        <!-- Your Information Section -->
                        <div class="form-section">
                            <h3 class="section-title">YOUR INFORMATION</h3>
                            
                            <div class="form-fields">
                                <div class="form-field">
                                    <input type="text" id="full-name" name="full_name" placeholder="Full name" 
                                           value="<?php echo $is_test_mode ? esc_attr($test_data['full_name']) : ''; ?>" required />
                                </div>
                                
                                <div class="form-field">
                                    <input type="email" id="email" name="email" placeholder="Email address" 
                                           value="<?php echo $is_test_mode ? esc_attr($test_data['email']) : ''; ?>" required />
                                </div>
                                
                                <div class="form-field">
                                    <input type="tel" id="contact-number" name="contact_number" placeholder="Contact number" 
                                           value="<?php echo $is_test_mode ? esc_attr($test_data['contact_number']) : ''; ?>" required />
                                </div>
                                
                                <div class="form-field">
                                    <input type="text" id="home-address" name="home_address" placeholder="Home address" 
                                           value="<?php echo $is_test_mode ? esc_attr($test_data['home_address']) : ''; ?>" />
                                </div>
                                
                                <div class="form-field">
                                    <select id="country" name="country">
                                        <option value="">Country of residence</option>
                                        <option value="US">United States</option>
                                        <option value="UK">United Kingdom</option>
                                        <option value="CA">Canada</option>
                                        <option value="AU">Australia</option>
                                        <option value="ZA" <?php echo ($is_test_mode && $test_data['country'] === 'ZA') ? 'selected' : ''; ?>>South Africa</option>
                                        <option value="DE">Germany</option>
                                        <option value="FR">France</option>
                                        <option value="IT">Italy</option>
                                        <option value="ES">Spain</option>
                                        <option value="NL">Netherlands</option>
                                        <option value="BE">Belgium</option>
                                        <option value="CH">Switzerland</option>
                                        <option value="AT">Austria</option>
                                        <option value="SE">Sweden</option>
                                        <option value="NO">Norway</option>
                                        <option value="DK">Denmark</option>
                                        <option value="FI">Finland</option>
                                        <option value="JP">Japan</option>
                                        <option value="SG">Singapore</option>
                                        <option value="HK">Hong Kong</option>
                                        <option value="AE">United Arab Emirates</option>
                                        <option value="BR">Brazil</option>
                                        <option value="AR">Argentina</option>
                                        <option value="IN">India</option>
                                        <option value="CN">China</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- How Did You Hear About Us -->
                        <div class="form-section">
                            <h3 class="section-title">HOW DID YOU HEAR ABOUT US?</h3>
                            
                            <div class="select-wrapper">
                                <select name="how_heard" id="how-heard">
                                    <option value="">Please select</option>
                                    <option value="google" <?php echo ($is_test_mode && $test_data['how_heard'] === 'google') ? 'selected' : ''; ?>>Google Search</option>
                                    <option value="social_media">Social Media</option>
                                    <option value="travel_agent">Travel Agent</option>
                                    <option value="word_of_mouth">Word of Mouth</option>
                                    <option value="magazine">Magazine/Publication</option>
                                    <option value="previous_guest">Previous Guest</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <!-- Special Requests Section -->
                        <div class="form-section">
                            <h3 class="section-title">SPECIAL REQUESTS</h3>
                            
                            <div class="form-field">
                                <textarea name="special_requests" id="special-requests" 
                                          placeholder="Anything else you'd like us to know?"
                                          rows="4"><?php echo $is_test_mode ? esc_textarea($test_data['special_requests']) : ''; ?></textarea>
                            </div>
                        </div>

                        <!-- Children's Interests Section -->
                        <div class="form-section">
                            <h3 class="section-title">CHILDRENS INTERESTS (IF APPLICABLE)</h3>
                            
                            <div class="form-field">
                                <textarea name="children_interests" id="children-interests" 
                                          placeholder="What do they love? Stargazing, horses, camping, quad biking?"
                                          rows="3"><?php echo $is_test_mode ? esc_textarea($test_data['children_interests']) : ''; ?></textarea>
                            </div>
                        </div>

                        <!-- Disclaimer -->
                        <div class="disclaimer-section">
                            <p class="disclaimer-text">
                                Live availability is shown in real time, but all bookings are subject to final confirmation from the Leobo team.
                            </p>
                        </div>

                        <!-- Step Navigation -->
                        <div class="step-navigation">
                            <button type="button" class="btn btn-secondary back-step" data-back="2">
                                BACK
                            </button>
                            <button type="submit" class="btn btn-primary submit-btn" id="submit-booking">
                                <?php echo $is_test_mode ? '🧪 SUBMIT TEST BOOKING' : 'SUBMIT'; ?>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Thank You -->
                <div class="booking-step booking-step-4" data-step="4">
                    <div class="step-content">
                        <div class="thank-you-content">
                            <h2 class="thank-you-title">Thank you!</h2>
                            <p class="thank-you-message">
                                The final step simply brings everything together and sends off the enquiry.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Hidden fields -->
                <input type="hidden" name="action" value="submit_booking_request">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('leobo_booking_system_nonce'); ?>">
                <input type="hidden" name="accommodation" value="Observatory Villa">
                <input type="hidden" name="calculated_total" value="0" id="calculated-total-field">
                <?php if ($is_test_mode): ?>
                <input type="hidden" name="is_test_submission" value="1">
                <?php endif; ?>
                
            </form>
        </div>

        <!-- Sidebar Summary -->
        <div class="booking-sidebar">
            <div class="sidebar-content">
                
                <?php if ($is_test_mode): ?>
                <!-- Test Mode Indicator -->
                <div class="test-mode-indicator">
                    <div class="test-mode-badge">🧪 TEST MODE</div>
                    <p class="test-mode-text"><?php echo $is_embedded_admin ? 'Admin test form' : 'Form pre-filled with test data'; ?></p>
                </div>
                <?php endif; ?>
                
                <!-- Guests Summary -->
                <div class="summary-section">
                    <h4 class="summary-title">GUESTS</h4>
                    <div class="guest-summary">
                        <div class="guest-item">
                            <span class="guest-count" id="sidebar-adults">-</span> adults
                        </div>
                        <div class="guest-item">
                            <span class="guest-count" id="sidebar-children">-</span> children (4+)
                        </div>
                        <div class="guest-item">
                            <span class="guest-count" id="sidebar-babies">-</span> babies (0-3)
                        </div>
                    </div>
                </div>

                <!-- Dates Summary -->
                <div class="summary-section">
                    <h4 class="summary-title">DATES</h4>
                    <div class="dates-summary" id="dates-summary">
                        <div class="date-placeholder">Select your dates</div>
                    </div>
                </div>

                <!-- Pricing Summary -->
                <div class="summary-section">
                    <h4 class="summary-title">ACCOMMODATION PRICE</h4>
                    <div class="price-display">
                        <div class="price-amount" id="accommodation-price">R 120,000</div>
                    </div>
                </div>

                <!-- Optional Extras (Step 2+) -->
                <div class="summary-section optional-extras" style="display: none;">
                    <h4 class="summary-title">OPTIONAL EXTRAS</h4>
                    <div class="extras-summary" id="extras-summary">
                        <div class="price-amount" id="extras-price">R 22,500</div>
                    </div>
                </div>

                <!-- Cost Breakdown (Step 2+) -->
                <div class="cost-breakdown">
                    <h4 class="breakdown-header">COST BREAKDOWN</h4>
                    
                    <!-- Season Information -->
                    <div class="breakdown-section season-info" id="breakdown-season-info" style="display: none;">
                        <div class="breakdown-season-header">
                            <span class="season-badge" id="breakdown-season-badge">Standard Season</span>
                            <span class="season-dates" id="breakdown-season-dates">Jan 6 - Feb 9</span>
                        </div>
                        <div class="breakdown-season-details">
                            <div class="season-rate" id="breakdown-season-rate">R 92,400 per night (up to 4 guests)</div>
                            <div class="season-nights" id="breakdown-season-nights">3 nights × R 92,400</div>
                        </div>
                    </div>
                    
                    <!-- Nightly Breakdown -->
                    <div class="breakdown-section nightly-breakdown" id="breakdown-nightly" style="display: none;">
                        <div class="breakdown-nightly-header">
                            <span class="nightly-title">Nightly Rate Details</span>
                            <button type="button" class="nightly-toggle" onclick="toggleNightlyDetails()">
                                <span class="toggle-text">Show Details</span>
                                <span class="toggle-icon">▼</span>
                            </button>
                        </div>
                        <div class="breakdown-nightly-details" id="breakdown-nightly-details" style="display: none;">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>
                    
                    <div class="breakdown-details">
                        <div class="breakdown-item" id="breakdown-base-accommodation">
                            <span class="breakdown-item-label">Base Accommodation</span>
                            <span class="breakdown-item-detail" id="breakdown-base-detail">Up to 4 guests, 0 nights</span>
                            <span class="breakdown-item-amount" id="breakdown-base-amount">R 0</span>
                        </div>
                        <div class="breakdown-item" id="breakdown-extra-adults" style="display: none;">
                            <span class="breakdown-item-label">Additional Adults</span>
                            <span class="breakdown-item-detail" id="breakdown-extra-adults-detail">0 adults</span>
                            <span class="breakdown-item-amount" id="breakdown-extra-adults-amount">R 0</span>
                        </div>
                        <div class="breakdown-item" id="breakdown-children" style="display: none;">
                            <span class="breakdown-item-label">Children (5-11 years)</span>
                            <span class="breakdown-item-detail" id="breakdown-children-detail">0 children</span>
                            <span class="breakdown-item-amount" id="breakdown-children-amount">R 0</span>
                        </div>
                        <div class="breakdown-item" id="breakdown-babies" style="display: none;">
                            <span class="breakdown-item-label">Babies (0-4 years)</span>
                            <span class="breakdown-item-detail" id="breakdown-babies-detail">0 babies</span>
                            <span class="breakdown-item-amount" id="breakdown-babies-amount">Free</span>
                        </div>
                        <div class="breakdown-item" id="breakdown-christmas-premium" style="display: none;">
                            <span class="breakdown-item-label">Premium Christmas Surcharge</span>
                            <span class="breakdown-item-detail" id="breakdown-christmas-detail">Dec 20 - Jan 1</span>
                            <span class="breakdown-item-amount" id="breakdown-christmas-amount">R 0</span>
                        </div>
                        <div class="breakdown-item" id="breakdown-helicopter" style="display: none;">
                            <span class="breakdown-item-label">Helicopter Package</span>
                            <span class="breakdown-item-detail" id="breakdown-helicopter-detail"></span>
                            <span class="breakdown-item-amount" id="breakdown-helicopter-amount">R 0</span>
                        </div>
                        <div class="breakdown-item" id="breakdown-transfer" style="display: none;">
                            <span class="breakdown-item-label">Transfer</span>
                            <span class="breakdown-item-detail"></span>
                            <span class="breakdown-item-amount" id="breakdown-transfer-amount">R 0</span>
                        </div>
                        <div class="breakdown-item" id="breakdown-experiences" style="display: none;">
                            <span class="breakdown-item-label">Experiences</span>
                            <span class="breakdown-item-detail"></span>
                            <span class="breakdown-item-amount" id="breakdown-experiences-amount">R 0</span>
                        </div>
                        <div class="breakdown-divider"></div>
                        <div class="breakdown-subtotal">
                            <span class="breakdown-subtotal-label">Subtotal</span>
                            <span class="breakdown-subtotal-amount" id="breakdown-subtotal-amount">R 0</span>
                        </div>
                    </div>
                </div>

                <!-- Total Costs (Step 2+) -->
                <div class="summary-section total-costs" style="display: none;">
                    <h4 class="summary-title">TOTAL COSTS</h4>
                    <div class="total-price-display">
                        <div class="price-amount total" id="total-price">R 144,500</div>
                    </div>
                </div>
                
            </div>
        </div>
        
    </div>
    
    <!-- Success/Error Messages -->
    <div id="booking-messages" class="booking-messages">
        <div id="error-message" class="message error-message" style="display: none;"></div>
        <div id="success-message" class="message success-message" style="display: none;"></div>
    </div>
    
</div>

<?php if ($is_test_mode): ?>
<style>
/* Test Mode Styling */
.test-mode-panel {
    background: linear-gradient(135deg, #2a2a2a 0%, #1a1a1a 100%);
    border: 2px solid #d4b896;
    border-radius: 8px;
    margin-bottom: 20px;
    overflow: hidden;
}

.test-panel-header {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 20px;
    background: rgba(212, 184, 150, 0.1);
}

.test-badge {
    background: #d4b896;
    color: #1a1a1a;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 14px;
    white-space: nowrap;
}

.test-info {
    flex: 1;
    color: #d4b896;
    font-size: 14px;
}

.test-panel-toggle {
    background: rgba(212, 184, 150, 0.2);
    border: 1px solid rgba(212, 184, 150, 0.3);
    color: #d4b896;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
    transition: all 0.3s ease;
}

.test-panel-toggle:hover {
    background: rgba(212, 184, 150, 0.3);
    transform: translateY(-1px);
}

.test-panel-controls {
    padding: 20px;
    border-top: 1px solid rgba(212, 184, 150, 0.2);
    display: none;
}

.test-panel-controls.active {
    display: block;
}

.test-controls-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 20px;
}

.test-control-group {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.test-control-group label {
    color: #d4b896;
    font-size: 14px;
    font-weight: 600;
}

.test-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.test-btn {
    background: rgba(212, 184, 150, 0.1);
    border: 1px solid rgba(212, 184, 150, 0.3);
    color: #d4b896;
    padding: 8px 16px;
    border-radius: 4px;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.test-btn:hover {
    background: rgba(212, 184, 150, 0.2);
    border-color: #d4b896;
    transform: translateY(-1px);
}

.test-btn-small {
    background: rgba(212, 184, 150, 0.1);
    border: 1px solid rgba(212, 184, 150, 0.3);
    color: #d4b896;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 32px;
}

.test-btn-small:hover {
    background: rgba(212, 184, 150, 0.2);
    transform: translateY(-1px);
}

.leobo-booking-wrapper.test-mode-active {
    border: 2px solid rgba(212, 184, 150, 0.3);
    border-radius: 8px;
    background: linear-gradient(45deg, rgba(212, 184, 150, 0.02) 0%, transparent 100%);
}

.test-mode-indicator {
    background: rgba(212, 184, 150, 0.1);
    border: 1px solid rgba(212, 184, 150, 0.3);
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 20px;
    text-align: center;
}

.test-mode-badge {
    background: #d4b896;
    color: #1a1a1a;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
    display: inline-block;
    margin-bottom: 5px;
}

.test-mode-text {
    color: #d4b896;
    font-size: 12px;
    margin: 0;
    opacity: 0.9;
}

/* Enhanced submit button for test mode */
.test-mode-active #submit-booking {
    background: linear-gradient(135deg, #d4b896 0%, #b8a082 100%);
    border: 2px solid #d4b896;
    position: relative;
    overflow: hidden;
    font-weight: 600;
}

.test-mode-active #submit-booking:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(212, 184, 150, 0.3);
}

/* Responsive adjustments */
@media (max-width: 1024px) {
    .test-controls-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 768px) {
    .test-panel-header {
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
    }
    
    .test-controls-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .test-buttons {
        justify-content: center;
    }
}
</style>

<script>
// Test Mode JavaScript - Store original test data
const originalTestData = <?php echo json_encode($test_data); ?>;
const isTestMode = <?php echo $is_test_mode ? 'true' : 'false'; ?>;
const isEmbeddedAdmin = <?php echo $is_embedded_admin ? 'true' : 'false'; ?>;

// Test panel toggle
function toggleTestPanel() {
    const controls = document.getElementById('test-panel-controls');
    const toggle = document.querySelector('.test-panel-toggle');
    const icon = toggle.querySelector('.toggle-icon');
    
    if (controls.classList.contains('active')) {
        controls.classList.remove('active');
        icon.textContent = '▼';
    } else {
        controls.classList.add('active');
        icon.textContent = '▲';
    }
}

// Fill form with test data
function fillTestData() {
    if (!isTestMode) return;
    
    // Fill dates
    document.getElementById('arrival-date').value = originalTestData.checkin_date;
    document.getElementById('departure-date').value = originalTestData.checkout_date;
    
    // Fill guests
    document.getElementById('adults').value = originalTestData.adults;
    document.getElementById('children').value = originalTestData.children;
    document.getElementById('babies').value = originalTestData.babies;
    document.getElementById('adults-count').textContent = originalTestData.adults;
    document.getElementById('children-count').textContent = originalTestData.children;
    document.getElementById('babies-count').textContent = originalTestData.babies;
    
    // Fill guest info
    document.getElementById('full-name').value = originalTestData.full_name;
    document.getElementById('email').value = originalTestData.email;
    document.getElementById('contact-number').value = originalTestData.contact_number;
    document.getElementById('home-address').value = originalTestData.home_address;
    document.getElementById('country').value = originalTestData.country;
    document.getElementById('how-heard').value = originalTestData.how_heard;
    document.getElementById('special-requests').value = originalTestData.special_requests;
    document.getElementById('children-interests').value = originalTestData.children_interests;
    
    showTestNotification('✅ Test data filled successfully!', 'success');
}

// Randomize test data
function randomizeTestData() {
    if (!isTestMode) return;
    
    // Generate random dates (next 15-60 days)
    const today = new Date();
    const checkinDays = Math.floor(Math.random() * 45) + 15;
    const stayDays = Math.floor(Math.random() * 7) + 2;
    
    const checkinDate = new Date(today);
    checkinDate.setDate(today.getDate() + checkinDays);
    
    const checkoutDate = new Date(checkinDate);
    checkoutDate.setDate(checkinDate.getDate() + stayDays);
    
    // Random guest counts
    const adults = Math.floor(Math.random() * 8) + 1;
    const children = Math.floor(Math.random() * 4);
    const babies = Math.floor(Math.random() * 2);
    
    // Random names
    const firstNames = ['Alex', 'Sarah', 'Michael', 'Emma', 'David', 'Lisa', 'Chris', 'Anna'];
    const lastNames = ['Safari-Test', 'Adventure-Seeker', 'Game-Viewer', 'Wildlife-Explorer', 'Bush-Walker'];
    const countries = ['US', 'UK', 'CA', 'AU', 'DE', 'FR', 'ZA', 'NL'];
    const hearAbout = ['google', 'social_media', 'travel_agent', 'word_of_mouth', 'magazine'];
    
    const firstName = firstNames[Math.floor(Math.random() * firstNames.length)];
    const lastName = lastNames[Math.floor(Math.random() * lastNames.length)];
    const country = countries[Math.floor(Math.random() * countries.length)];
    const hearSource = hearAbout[Math.floor(Math.random() * hearAbout.length)];
    
    // Update form fields
    document.getElementById('arrival-date').value = formatDateForInput(checkinDate);
    document.getElementById('departure-date').value = formatDateForInput(checkoutDate);
    
    document.getElementById('adults').value = adults;
    document.getElementById('children').value = children;
    document.getElementById('babies').value = babies;
    document.getElementById('adults-count').textContent = adults;
    document.getElementById('children-count').textContent = children;
    document.getElementById('babies-count').textContent = babies;
    
    document.getElementById('full-name').value = `${firstName} ${lastName}`;
    document.getElementById('email').value = `${firstName.toLowerCase()}.${lastName.toLowerCase()}@example.com`;
    document.getElementById('contact-number').value = `+27 ${Math.floor(Math.random() * 90) + 10} ${Math.floor(Math.random() * 900) + 100} ${Math.floor(Math.random() * 9000) + 1000}`;
    document.getElementById('home-address').value = `${Math.floor(Math.random() * 999) + 1} Test Street, ${firstName} City`;
    document.getElementById('country').value = country;
    document.getElementById('how-heard').value = hearSource;
    document.getElementById('special-requests').value = `Randomized test booking for ${firstName} ${lastName}. This is a test submission with random data for system testing purposes.`;
    
    showTestNotification('🎲 Test data randomized!', 'success');
}

// Clear form data
function clearFormData() {
    if (!isTestMode) return;
    
    // Clear all form fields
    document.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]), textarea, select').forEach(field => {
        field.value = '';
    });
    
    // Reset counters
    document.getElementById('adults-count').textContent = '1';
    document.getElementById('children-count').textContent = '0';
    document.getElementById('babies-count').textContent = '0';
    document.getElementById('adults').value = '1';
    document.getElementById('children').value = '0';
    document.getElementById('babies').value = '0';
    
    // Uncheck all checkboxes and radios
    document.querySelectorAll('input[type="checkbox"], input[type="radio"]').forEach(field => {
        field.checked = false;
    });
    
    showTestNotification('🗑️ Form cleared!', 'info');
}

// Jump to specific step
function goToStep(stepNumber) {
    if (!isTestMode) return;
    
    // Hide all steps
    document.querySelectorAll('.booking-step').forEach(step => {
        step.classList.remove('active');
    });
    
    // Show target step
    document.querySelector(`.booking-step-${stepNumber}`).classList.add('active');
    
    // Update progress
    document.querySelectorAll('.progress-step').forEach((step, index) => {
        if (index + 1 <= stepNumber) {
            step.classList.add('active');
        } else {
            step.classList.remove('active');
        }
    });
    
    showTestNotification(`📍 Jumped to step ${stepNumber}`, 'info');
}

// Test scenarios
function loadFamilyScenario() {
    if (!isTestMode) return;
    
    document.getElementById('adults').value = '2';
    document.getElementById('children').value = '2';
    document.getElementById('babies').value = '1';
    document.getElementById('adults-count').textContent = '2';
    document.getElementById('children-count').textContent = '2';
    document.getElementById('babies-count').textContent = '1';
    document.getElementById('full-name').value = 'Sarah Family-Test';
    document.getElementById('email').value = 'sarah.family@example.com';
    document.getElementById('special-requests').value = 'Family vacation with young children. Please ensure child-friendly activities and safety measures.';
    document.getElementById('children-interests').value = 'Swimming, animals, stargazing, simple nature walks';
    showTestNotification('👨‍👩‍👧‍👦 Family scenario loaded!', 'success');
}

function loadCoupleScenario() {
    if (!isTestMode) return;
    
    document.getElementById('adults').value = '2';
    document.getElementById('children').value = '0';
    document.getElementById('babies').value = '0';
    document.getElementById('adults-count').textContent = '2';
    document.getElementById('children-count').textContent = '0';
    document.getElementById('babies-count').textContent = '0';
    document.getElementById('full-name').value = 'Alex Couple-Test';
    document.getElementById('email').value = 'alex.couple@example.com';
    document.getElementById('special-requests').value = 'Romantic getaway for our anniversary. Looking for intimate dining and stargazing experiences.';
    document.getElementById('children-interests').value = '';
    showTestNotification('💑 Couple scenario loaded!', 'success');
}

function loadGroupScenario() {
    if (!isTestMode) return;
    
    document.getElementById('adults').value = '8';
    document.getElementById('children').value = '0';
    document.getElementById('babies').value = '0';
    document.getElementById('adults-count').textContent = '8';
    document.getElementById('children-count').textContent = '0';
    document.getElementById('babies-count').textContent = '0';
    document.getElementById('full-name').value = 'Michael Group-Test';
    document.getElementById('email').value = 'michael.group@example.com';
    document.getElementById('special-requests').value = 'Group retreat for 8 friends. Interested in adventure activities, group dining, and team experiences.';
    document.getElementById('children-interests').value = '';
    showTestNotification('👥 Group scenario loaded!', 'success');
}

// Utility functions
function formatDateForInput(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function showTestNotification(message, type = 'info') {
    // Create notification
    const notification = document.createElement('div');
    notification.className = `test-notification test-notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-message">${message}</span>
            <button class="notification-close" onclick="this.parentElement.parentElement.remove()">×</button>
        </div>
    `;
    
    // Style notification
    const colors = {
        success: { bg: '#d4edda', border: '#c3e6cb', text: '#155724' },
        info: { bg: '#d1ecf1', border: '#bee5eb', text: '#0c5460' },
        warning: { bg: '#fff3cd', border: '#ffeaa7', text: '#856404' }
    };
    
    const color = colors[type] || colors.info;
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: ${color.bg};
        border: 1px solid ${color.border};
        color: ${color.text};
        padding: 12px 16px;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10001;
        max-width: 350px;
        animation: slideInRight 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }
    }, 3000);
}

// Animation styles
const testAnimationStyles = document.createElement('style');
testAnimationStyles.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .notification-close {
        background: none;
        border: none;
        font-size: 18px;
        cursor: pointer;
        opacity: 0.7;
        padding: 0;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .notification-close:hover {
        opacity: 1;
    }
`;
document.head.appendChild(testAnimationStyles);

// Initialize test mode
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== PAGE LOAD DEBUG ===');
    console.log('Test mode:', isTestMode);
    console.log('Embedded admin:', isEmbeddedAdmin);
    
    // Check initial field values
    console.log('Initial checkin_date:', document.getElementById('arrival-date')?.value);
    console.log('Initial checkout_date:', document.getElementById('departure-date')?.value);
    console.log('Initial adults:', document.getElementById('adults')?.value);
    console.log('Initial accommodation:', document.querySelector('input[name="accommodation"]')?.value);
    
    if (isTestMode) {
        // Auto-fill test data on page load (only if not in embedded admin mode)
        setTimeout(() => {
            fillTestData();
            const message = isEmbeddedAdmin ? 
                '🧪 Admin test form ready!' : 
                '🧪 Test mode activated with pre-filled data!';
            showTestNotification(message, 'success');
        }, 500);
    }
    
    // Update calculated total before form submission
    document.getElementById('leobo-booking-form').addEventListener('submit', function(e) {
        alert('Form submission intercepted! Check console for details.');
        console.log('=== FORM SUBMISSION DEBUG ===');
        
        // Get the displayed total price and extract numeric value
        const totalPriceElement = document.getElementById('total-price');
        if (totalPriceElement) {
            const totalText = totalPriceElement.textContent || totalPriceElement.innerText;
            console.log('Total price element text:', totalText);
            // Extract numeric value from "R 144,500" format
            const totalValue = totalText.replace(/[^\d]/g, '');
            console.log('Extracted total value:', totalValue);
            document.getElementById('calculated-total-field').value = totalValue;
        } else {
            console.log('Total price element not found');
        }
        
        // Ensure helicopter selection consistency
        const helicopterPackageSelected = document.querySelector('input[name="helicopter_package"]:checked');
        if (helicopterPackageSelected) {
            console.log('Helicopter package selected:', helicopterPackageSelected.value);
            // If a helicopter package is selected, make sure helicopter_interested is set to "yes"
            const helicopterYes = document.getElementById('helicopter-yes');
            if (helicopterYes) {
                helicopterYes.checked = true;
                console.log('Set helicopter_interested to yes');
            }
        } else {
            console.log('No helicopter package selected');
        }
        
        // Debug: Log all form values by field name
        console.log('=== INDIVIDUAL FIELD VALUES ===');
        console.log('checkin_date:', document.getElementById('arrival-date')?.value || 'NOT FOUND');
        console.log('checkout_date:', document.getElementById('departure-date')?.value || 'NOT FOUND');
        console.log('adults:', document.getElementById('adults')?.value || 'NOT FOUND');
        console.log('children:', document.getElementById('children')?.value || 'NOT FOUND');
        console.log('babies:', document.getElementById('babies')?.value || 'NOT FOUND');
        console.log('full_name:', document.getElementById('full-name')?.value || 'NOT FOUND');
        console.log('email:', document.getElementById('email')?.value || 'NOT FOUND');
        console.log('contact_number:', document.getElementById('contact-number')?.value || 'NOT FOUND');
        console.log('special_requests:', document.getElementById('special-requests')?.value || 'NOT FOUND');
        console.log('calculated_total:', document.getElementById('calculated-total-field')?.value || 'NOT FOUND');
        
        // Debug: Log complete form data
        console.log('=== COMPLETE FORM DATA ===');
        const formData = new FormData(this);
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: "${value}"`);
        }
        
        console.log('=== END FORM SUBMISSION DEBUG ===');
    });
});
</script>
<?php endif; ?>
