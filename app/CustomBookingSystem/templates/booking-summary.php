<?php
/**
 * Booking Summary Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Pricing Summary -->
<div class="booking-summary" id="booking-summary">
    <h3 class="summary-title">Booking Summary</h3>
    
    <div class="summary-details" id="summary-details">
        <div class="summary-row" id="summary-accommodation">
            <span class="summary-label">Accommodation:</span>
            <span class="summary-value">Please select dates and accommodation</span>
        </div>
        
        <div class="summary-row" id="summary-dates">
            <span class="summary-label">Dates:</span>
            <span class="summary-value">Please select dates</span>
        </div>
        
        <div class="summary-row" id="summary-guests">
            <span class="summary-label">Guests:</span>
            <span class="summary-value">2 Guests</span>
        </div>
        
        <div class="summary-breakdown" id="pricing-breakdown">
            <!-- Populated by JavaScript -->
        </div>
        
        <div class="summary-total">
            <span class="total-label">Total Price:</span>
            <span class="total-amount" id="total-price">R 0</span>
        </div>
    </div>
    
    <div class="booking-actions">
        <button type="submit" class="btn-primary btn-submit" id="submit-booking">
            <span class="btn-text">Request Booking</span>
            <span class="btn-loader" style="display: none;">Processing...</span>
        </button>
        
        <p class="booking-note">
            This is a booking request. We'll confirm availability and contact you within 24 hours.
        </p>
    </div>
</div>
