<?php
/**
 * Step 2: Accommodation Selection
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Step 2: Accommodation -->
<div class="booking-step" id="step-accommodation">
    <h3 class="step-title">
        <span class="step-number">2</span>
        Choose Your Accommodation
    </h3>
    
    <div class="accommodation-grid" id="accommodation-options">
        <!-- Populated by JavaScript from ACF data -->
    </div>
    
    <input type="hidden" id="selected-accommodation" name="accommodation" required>
    <div class="field-error" id="accommodation-error"></div>
</div>
