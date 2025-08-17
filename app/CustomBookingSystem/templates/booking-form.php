<?php
/**
 * Leobo Custom Booking System - Multi-Step Booking Form Template
 * Matches the exact design from the provided images
 * 
 * @package LeoboCustomBookingSystem
 * @version 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$form_id = $args['form_id'] ?? 'leobo-booking-form';
$atts = $args['attributes'] ?? array();
?>

<div id="leobo-booking-system" class="leobo-booking-wrapper">
    
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
                                <div class="date-selector-button" id="date-selector">
                                    <span class="calendar-icon">📅</span>
                                    <span class="date-text">Select dates</span>
                                    <div class="date-subtext" id="date-range-info" style="display: none;">
                                        You have selected <span id="date-range-text"></span>
                                    </div>
                                </div>
                                
                                <div class="date-flexible-option">
                                    <label class="checkbox-label">
                                        <input type="checkbox" id="flexible-dates" name="flexible_dates" />
                                        <span class="checkmark"></span>
                                        My dates are flexible
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Hidden date inputs -->
                            <input type="hidden" id="checkin-date" name="checkin_date" />
                            <input type="hidden" id="checkout-date" name="checkout_date" />
                        </div>

                        <!-- Guest Section -->
                        <div class="form-section guests-section">
                            
                            <!-- Adults -->
                            <div class="guest-type-row">
                                <label class="guest-label">Adults</label>
                                <div class="guest-counter">
                                    <button type="button" class="counter-btn minus" data-target="adults">-</button>
                                    <span class="counter-value" id="adults-count">3</span>
                                    <button type="button" class="counter-btn plus" data-target="adults">+</button>
                                </div>
                            </div>

                            <!-- Children -->
                            <div class="guest-type-row">
                                <label class="guest-label">Children (4+ years)</label>
                                <div class="guest-counter">
                                    <button type="button" class="counter-btn minus" data-target="children">-</button>
                                    <span class="counter-value" id="children-count">4</span>
                                    <button type="button" class="counter-btn plus" data-target="children">+</button>
                                </div>
                            </div>

                            <!-- Babies -->
                            <div class="guest-type-row">
                                <label class="guest-label">Baby (0-3 years)</label>
                                <div class="guest-counter">
                                    <button type="button" class="counter-btn minus" data-target="babies">-</button>
                                    <span class="counter-value" id="babies-count">2</span>
                                    <button type="button" class="counter-btn plus" data-target="babies">+</button>
                                </div>
                            </div>
                            
                            <!-- Hidden inputs -->
                            <input type="hidden" id="adults" name="adults" value="3" />
                            <input type="hidden" id="children" name="children" value="4" />
                            <input type="hidden" id="babies" name="babies" value="2" />
                        </div>

                        <!-- What's Included Section -->
                        <div class="form-section included-section">
                            <h3 class="section-title">What's included in the price?</h3>
                            <ul class="included-list">
                                <li>Unlimited quad bikes, buggies, and game vehicles</li>
                                <li>Stargazing with a research-grade telescope</li>
                                <li>River kayaking + natural freshwater swimming</li>
                                <li>Private chef + all meals</li>
                                <li>Housekeeping + full staff</li>
                                <li>Full access to the 20,000-acre reserve</li>
                                <li>Exclusive use of The Observatory Villa</li>
                            </ul>
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
                            <p class="section-subtitle">Includes pickup and unlimited use?</p>
                            
                            <div class="radio-group">
                                <label class="radio-option">
                                    <input type="radio" name="helicopter_package" value="yes" />
                                    <span class="radio-mark"></span>
                                    Yes
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="helicopter_package" value="no" />
                                    <span class="radio-mark"></span>
                                    No
                                </label>
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
                                    <input type="text" id="full-name" name="full_name" placeholder="Full name" required />
                                </div>
                                
                                <div class="form-field">
                                    <input type="email" id="email" name="email" placeholder="Email address" required />
                                </div>
                                
                                <div class="form-field">
                                    <input type="tel" id="contact-number" name="contact_number" placeholder="Contact number" required />
                                </div>
                                
                                <div class="form-field">
                                    <input type="text" id="home-address" name="home_address" placeholder="Home address" required />
                                </div>
                                
                                <div class="form-field">
                                    <select id="country" name="country" required>
                                        <option value="">Country of residence</option>
                                        <option value="US">United States</option>
                                        <option value="UK">United Kingdom</option>
                                        <option value="CA">Canada</option>
                                        <option value="AU">Australia</option>
                                        <option value="ZA">South Africa</option>
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
                                    <option value="google">Google Search</option>
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
                                          rows="4"></textarea>
                            </div>
                        </div>

                        <!-- Children's Interests Section -->
                        <div class="form-section">
                            <h3 class="section-title">CHILDRENS INTERESTS (IF APPLICABLE)</h3>
                            
                            <div class="form-field">
                                <textarea name="children_interests" id="children-interests" 
                                          placeholder="What do they love? Stargazing, horses, camping, quad biking?"
                                          rows="3"></textarea>
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
                                SUBMIT
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
                
            </form>
        </div>

        <!-- Sidebar Summary -->
        <div class="booking-sidebar">
            <div class="sidebar-content">
                
                <!-- Guests Summary -->
                <div class="summary-section">
                    <h4 class="summary-title">GUESTS</h4>
                    <div class="guest-summary">
                        <div class="guest-item">
                            <span class="guest-count" id="sidebar-adults">3</span> adults
                        </div>
                        <div class="guest-item">
                            <span class="guest-count" id="sidebar-children">4</span> children (4+)
                        </div>
                        <div class="guest-item">
                            <span class="guest-count" id="sidebar-babies">2</span> babies (0-3)
                        </div>
                    </div>
                </div>

                <!-- Dates Summary -->
                <div class="summary-section">
                    <h4 class="summary-title">DATES</h4>
                    <div class="dates-summary" id="dates-summary">
                        <div class="date-range">12th Jan - 5th Feb</div>
                        <div class="nights-count">(16 nights)</div>
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
    
    <!-- Date Picker Modal -->
    <div id="date-picker-modal" class="date-picker-modal" style="display: none;">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Select your dates</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <input type="text" id="date_range_input" name="date_range" />
                <div class="flatpickr-container"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary cancel-dates">Cancel</button>
                <button type="button" class="btn btn-primary confirm-dates">Confirm Dates</button>
            </div>
        </div>
    </div>
    
    <!-- Success/Error Messages -->
    <div id="booking-messages" class="booking-messages">
        <div id="error-message" class="message error-message" style="display: none;"></div>
        <div id="success-message" class="message success-message" style="display: none;"></div>
    </div>
    
</div>
