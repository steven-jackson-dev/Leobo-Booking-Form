<?php
/**
 * Step 1: Dates & Guests Selection
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Step 1: Dates & Guests -->
<div class="booking-step" id="step-dates">
    <h3 class="step-title">
        <span class="step-number">1</span>
        Select Your Dates
    </h3>
    
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="checkin-date">Check-in Date</label>
            <input type="text" id="checkin-date" name="checkin_date" 
                   class="form-control datepicker" readonly required>
            <div class="field-error" id="checkin-error"></div>
        </div>
        
        <div class="form-group col-md-6">
            <label for="checkout-date">Check-out Date</label>
            <input type="text" id="checkout-date" name="checkout_date" 
                   class="form-control datepicker" readonly required>
            <div class="field-error" id="checkout-error"></div>
        </div>
    </div>
    
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="guests">Number of Guests</label>
            <select id="guests" name="guests" class="form-control" required>
                <?php for ($i = 1; $i <= 12; $i++): ?>
                <option value="<?php echo $i; ?>" <?php echo $i == 2 ? 'selected' : ''; ?>>
                    <?php echo $i; ?> Guest<?php echo $i > 1 ? 's' : ''; ?>
                </option>
                <?php endfor; ?>
            </select>
        </div>
        
        <div class="form-group col-md-6">
            <label for="nights">Nights</label>
            <input type="text" id="nights" class="form-control" readonly>
        </div>
    </div>
    
    <!-- Visual Calendar -->
    <div class="booking-calendar-container">
        <h4>Select your dates on the calendar</h4>
        <div id="booking-calendar"></div>
        <div class="calendar-legend">
            <span class="legend-item available">
                <span class="legend-color"></span> Available
            </span>
            <span class="legend-item blocked">
                <span class="legend-color"></span> Unavailable
            </span>
            <span class="legend-item selected">
                <span class="legend-color"></span> Selected
            </span>
        </div>
    </div>
</div>
