<?php
/**
 * Step 4: Guest Details Form
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Step 4: Guest Details -->
<div class="booking-step" id="step-guest-details">
    <h3 class="step-title">
        <span class="step-number">4</span>
        Guest Information
    </h3>
    
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="first-name">First Name</label>
            <input type="text" id="first-name" name="first_name" 
                   class="form-control" required>
            <div class="field-error" id="first-name-error"></div>
        </div>
        
        <div class="form-group col-md-6">
            <label for="last-name">Last Name</label>
            <input type="text" id="last-name" name="last_name" 
                   class="form-control" required>
            <div class="field-error" id="last-name-error"></div>
        </div>
    </div>
    
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" 
                   class="form-control" required>
            <div class="field-error" id="email-error"></div>
        </div>
        
        <div class="form-group col-md-6">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" 
                   class="form-control" required 
                   placeholder="+27 123 456 7890">
            <div class="field-error" id="phone-error"></div>
        </div>
    </div>
    
    <div class="form-group">
        <label for="special-requests">Special Requests</label>
        <textarea id="special-requests" name="special_requests" 
                  class="form-control" rows="4" 
                  placeholder="Dietary requirements, special occasions, accessibility needs..."></textarea>
    </div>
</div>
