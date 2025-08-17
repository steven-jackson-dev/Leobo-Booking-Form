/**
 * Leobo Custom Booking System - Frontend JavaScript
 * Handles dynamic pricing, calendar integration, and real-time updates
 * 
 * @package LeoboCustomBookingSystem
 * @version 1.0.0
 */

class LeoboCustomBookingSystem {
    constructor() {
        this.selectedDates = {
            checkin: null,
            checkout: null
        };
        this.selectedAccommodation = null;
        this.selectedPackages = [];
        this.currentPricing = null;
        this.blockedDates = [];
        this.isCalculating = false;
        
        this.init();
    }
    
    init() {
        console.log('LeoboCustomBookingSystem initialized');
        console.log('AJAX URL:', this.ajaxUrl);
        console.log('Blocked dates:', this.blockedDates);
        this.setupEventListeners();
        this.initializeCalendar();
        this.loadAccommodationOptions();
        this.loadPackageOptions();
        this.updateBlockedDates();
        
        // Set initial state
        this.updateSummary();
    }
    
    setupEventListeners() {
        const form = document.getElementById('leobo-booking-form');
        if (!form) return;
        
        // Date selection
        jQuery('#checkin-date, #checkout-date').on('change', () => {
            this.handleDateChange();
        });
        
        // Guest count change
        jQuery('#guests').on('change', () => {
            this.updatePricing();
            this.updateSummary();
        });
        
        // Accommodation selection
        jQuery(document).on('click', '.accommodation-option', (e) => {
            this.selectAccommodation(e.currentTarget);
        });
        
        // Package selection
        jQuery(document).on('change', '.package-checkbox', (e) => {
            this.togglePackage(e.target);
        });
        
        // Form submission
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.submitBooking();
        });
        
        // Real-time validation
        this.setupFieldValidation();
    }
    
    initializeCalendar() {
        jQuery('#booking-calendar').datepicker({
            numberOfMonths: 2,
            minDate: 0,
            maxDate: '+1y',
            beforeShowDay: (date) => this.beforeShowDay(date),
            onSelect: (dateText, inst) => this.onCalendarSelect(dateText, inst),
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true
        });
    }
    
    beforeShowDay(date) {
        const dateString = this.formatDate(date);
        const isBlocked = this.blockedDates.includes(dateString);
        const isSelected = this.isDateInRange(date);
        
        let cssClass = 'available-date';
        let tooltip = 'Available';
        
        if (isBlocked) {
            cssClass = 'blocked-date';
            tooltip = 'Not available';
            return [false, cssClass, tooltip];
        }
        
        if (isSelected) {
            cssClass = 'selected-date';
            tooltip = 'Selected';
        }
        
        return [true, cssClass, tooltip];
    }
    
    onCalendarSelect(dateText, inst) {
        const selectedDate = new Date(dateText);
        
        if (!this.selectedDates.checkin || (this.selectedDates.checkin && this.selectedDates.checkout)) {
            // Start new selection
            this.selectedDates.checkin = selectedDate;
            this.selectedDates.checkout = null;
            jQuery('#checkin-date').val(dateText);
            jQuery('#checkout-date').val('');
        } else if (selectedDate > this.selectedDates.checkin) {
            // Set checkout date
            this.selectedDates.checkout = selectedDate;
            jQuery('#checkout-date').val(dateText);
        } else {
            // Selected date is before checkin, reset
            this.selectedDates.checkin = selectedDate;
            this.selectedDates.checkout = null;
            jQuery('#checkin-date').val(dateText);
            jQuery('#checkout-date').val('');
        }
        
        this.updateCalendarDisplay();
        this.handleDateChange();
    }
    
    isDateInRange(date) {
        if (!this.selectedDates.checkin) return false;
        
        const checkDate = new Date(date);
        
        if (this.selectedDates.checkout) {
            return checkDate >= this.selectedDates.checkin && checkDate < this.selectedDates.checkout;
        } else {
            return checkDate.getTime() === this.selectedDates.checkin.getTime();
        }
    }
    
    updateCalendarDisplay() {
        jQuery('#booking-calendar').datepicker('refresh');
    }
    
    handleDateChange() {
        const checkin = jQuery('#checkin-date').val();
        const checkout = jQuery('#checkout-date').val();
        
        if (checkin && checkout) {
            // Calculate nights
            const nights = this.calculateNights(checkin, checkout);
            jQuery('#nights').val(nights + ' night' + (nights !== 1 ? 's' : ''));
            
            // Validate date range for blocked dates
            if (this.hasBlockedDatesInRange(checkin, checkout)) {
                this.showError('checkin-error', 'Selected date range contains unavailable dates.');
                return;
            } else {
                this.clearError('checkin-error');
                this.clearError('checkout-error');
            }
            
            // Update pricing
            this.updatePricing();
        }
        
        this.updateSummary();
    }
    
    calculateNights(checkin, checkout) {
        const checkinDate = new Date(checkin);
        const checkoutDate = new Date(checkout);
        const timeDiff = checkoutDate.getTime() - checkinDate.getTime();
        return Math.ceil(timeDiff / (1000 * 3600 * 24));
    }
    
    hasBlockedDatesInRange(checkin, checkout) {
        const start = new Date(checkin);
        const end = new Date(checkout);
        const current = new Date(start);
        
        while (current < end) {
            const dateString = this.formatDate(current);
            if (this.blockedDates.includes(dateString)) {
                return true;
            }
            current.setDate(current.getDate() + 1);
        }
        
        return false;
    }
    
    async loadAccommodationOptions() {
        const container = document.getElementById('accommodation-options');
        if (!container) return;
        
        const accommodations = leobo_booking_system.accommodations;
        
        let html = '';
        accommodations.forEach(accommodation => {
            html += `
                <div class="accommodation-option" data-id="${accommodation.id}">
                    <div class="accommodation-image">
                        <img src="${accommodation.image}" alt="${accommodation.name}" 
                             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2Y4ZjlmYSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBkeT0iLjNlbSIgZmlsbD0iIzY2NiIgdGV4dC1hbmNob3I9Im1pZGRsZSI+SW1hZ2U8L3RleHQ+PC9zdmc+'">
                    </div>
                    <div class="accommodation-details">
                        <h4 class="accommodation-name">${accommodation.name}</h4>
                        <p class="accommodation-description">${accommodation.description}</p>
                        <div class="accommodation-info">
                            <span class="capacity">Up to ${accommodation.capacity} guests</span>
                            <span class="base-rate">From R${accommodation.base_rate.toLocaleString()}/night</span>
                        </div>
                    </div>
                    <div class="accommodation-select">
                        <span class="select-indicator">Select</span>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }
    
    selectAccommodation(element) {
        // Remove previous selection
        document.querySelectorAll('.accommodation-option').forEach(option => {
            option.classList.remove('selected');
        });
        
        // Select current
        element.classList.add('selected');
        this.selectedAccommodation = element.dataset.id;
        document.getElementById('selected-accommodation').value = this.selectedAccommodation;
        
        this.clearError('accommodation-error');
        this.updatePricing();
        this.updateSummary();
    }
    
    async loadPackageOptions() {
        const container = document.getElementById('package-options');
        if (!container) return;
        
        const packages = leobo_booking_system.packages;
        
        let html = '';
        packages.forEach(pkg => {
            const priceText = this.getPackagePriceText(pkg);
            
            html += `
                <div class="package-option">
                    <div class="package-checkbox-container">
                        <input type="checkbox" class="package-checkbox" 
                               data-id="${pkg.id}" id="package-${pkg.id}">
                        <label for="package-${pkg.id}"></label>
                    </div>
                    <div class="package-image">
                        <img src="${pkg.image}" alt="${pkg.name}"
                             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2Y4ZjlmYSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBkeT0iLjNlbSIgZmlsbD0iIzY2NiIgdGV4dC1hbmNob3I9Im1pZGRsZSI+SW1hZ2U8L3RleHQ+PC9zdmc+'" >
                    </div>
                    <div class="package-details">
                        <h5 class="package-name">${pkg.name}</h5>
                        <p class="package-description">${pkg.description}</p>
                        <span class="package-price">${priceText}</span>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }
    
    getPackagePriceText(pkg) {
        const price = pkg.price.toLocaleString();
        
        switch (pkg.price_type) {
            case 'per_night':
                return `R${price} per night`;
            case 'per_person':
                return `R${price} per person`;
            case 'per_person_per_night':
                return `R${price} per person/night`;
            default:
                return `R${price}`;
        }
    }
    
    togglePackage(checkbox) {
        const packageId = checkbox.dataset.id;
        
        if (checkbox.checked) {
            if (!this.selectedPackages.includes(packageId)) {
                this.selectedPackages.push(packageId);
            }
        } else {
            this.selectedPackages = this.selectedPackages.filter(id => id !== packageId);
        }
        
        document.getElementById('selected-packages').value = JSON.stringify(this.selectedPackages);
        this.updatePricing();
        this.updateSummary();
    }
    
    async updatePricing() {
        if (this.isCalculating) return;
        
        const checkin = jQuery('#checkin-date').val();
        const checkout = jQuery('#checkout-date').val();
        const guests = jQuery('#guests').val();
        
        console.log('Updating pricing with:', { checkin, checkout, guests, accommodation: this.selectedAccommodation, packages: this.selectedPackages });
        
        if (!checkin || !checkout || !this.selectedAccommodation) {
            this.currentPricing = null;
            this.updateSummary();
            return;
        }
        
        this.isCalculating = true;
        this.showCalculatingState();
        
        try {
            const requestData = {
                action: 'calculate_booking_price',
                nonce: leobo_booking_system.nonce,
                checkin_date: checkin,
                checkout_date: checkout,
                guests: guests,
                accommodation_id: this.selectedAccommodation,
                packages: JSON.stringify(this.selectedPackages)
            };
            
            console.log('Sending AJAX request:', requestData);
            
            const response = await fetch(leobo_booking_system.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(requestData)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('AJAX response:', data);
            
            if (data.success && data.data) {
                this.currentPricing = data.data;
                const totalElement = document.getElementById('calculated-total');
                if (totalElement && this.currentPricing.grand_total) {
                    totalElement.value = this.currentPricing.grand_total;
                }
                this.clearError('pricing-error');
            } else {
                const errorMessage = data.data || 'Error calculating pricing. Please check your selection and try again.';
                console.error('Pricing calculation failed:', errorMessage);
                this.showError('pricing-error', errorMessage);
                this.currentPricing = null;
            }
        } catch (error) {
            console.error('Pricing calculation error:', error);
            this.showError('pricing-error', 'Unable to calculate pricing. Please try again.');
        } finally {
            this.isCalculating = false;
            this.hideCalculatingState();
            this.updateSummary();
        }
    }
    
    updateSummary() {
        this.updateAccommodationSummary();
        this.updateDatesSummary();
        this.updateGuestsSummary();
        this.updatePricingBreakdown();
        this.updateTotalPrice();
    }
    
    updateAccommodationSummary() {
        const element = document.getElementById('summary-accommodation');
        if (!element) return;
        
        const valueElement = element.querySelector('.summary-value');
        
        if (this.selectedAccommodation) {
            const accommodation = leobo_booking_system.accommodations.find(
                acc => acc.id === this.selectedAccommodation
            );
            if (accommodation) {
                valueElement.textContent = accommodation.name;
            }
        } else {
            valueElement.textContent = 'Please select accommodation';
        }
    }
    
    updateDatesSummary() {
        const element = document.getElementById('summary-dates');
        if (!element) return;
        
        const valueElement = element.querySelector('.summary-value');
        const checkin = jQuery('#checkin-date').val();
        const checkout = jQuery('#checkout-date').val();
        
        if (checkin && checkout) {
            const checkinFormatted = this.formatDateForDisplay(checkin);
            const checkoutFormatted = this.formatDateForDisplay(checkout);
            const nights = this.calculateNights(checkin, checkout);
            
            valueElement.textContent = `${checkinFormatted} - ${checkoutFormatted} (${nights} night${nights !== 1 ? 's' : ''})`;
        } else {
            valueElement.textContent = 'Please select dates';
        }
    }
    
    updateGuestsSummary() {
        const element = document.getElementById('summary-guests');
        if (!element) return;
        
        const valueElement = element.querySelector('.summary-value');
        const guests = jQuery('#guests').val();
        
        valueElement.textContent = `${guests} Guest${guests != 1 ? 's' : ''}`;
    }
    
    updatePricingBreakdown() {
        const element = document.getElementById('pricing-breakdown');
        if (!element) return;
        
        if (!this.currentPricing) {
            element.innerHTML = '';
            return;
        }
        
        let html = '';
        
        // Accommodation breakdown
        if (this.currentPricing.nightly_breakdown && Array.isArray(this.currentPricing.nightly_breakdown)) {
            html += '<div class="breakdown-section"><h4>Accommodation</h4>';
            
            this.currentPricing.nightly_breakdown.forEach(night => {
                try {
                    const date = this.formatDateForDisplay(night.date);
                    const rate = parseFloat(night.nightly_rate || night.total || 0);
                    html += `
                        <div class="breakdown-item">
                            <span class="breakdown-date">${date}</span>
                            <span class="breakdown-amount">R${rate.toLocaleString()}</span>
                        </div>
                    `;
                } catch (error) {
                    console.error('Error displaying nightly breakdown:', error);
                }
            });
            
            try {
                const accommodationTotal = parseFloat(this.currentPricing.accommodation_total || 0);
                html += `
                    <div class="breakdown-subtotal">
                        <span>Accommodation Subtotal:</span>
                        <span>R${accommodationTotal.toLocaleString()}</span>
                    </div>
                </div>`;
            } catch (error) {
                console.error('Error displaying accommodation total:', error);
                html += '</div>';
            }
        }
        
        // Package breakdown
        if (this.currentPricing.package_breakdown && this.currentPricing.package_breakdown.length > 0) {
            html += '<div class="breakdown-section"><h4>Packages & Add-ons</h4>';
            
            this.currentPricing.package_breakdown.forEach(pkg => {
                html += `
                    <div class="breakdown-item">
                        <span class="breakdown-name">${pkg.name}</span>
                        <span class="breakdown-amount">R${pkg.cost.toLocaleString()}</span>
                    </div>
                `;
            });
            
            html += `
                <div class="breakdown-subtotal">
                    <span>Packages Subtotal:</span>
                    <span>R${this.currentPricing.package_total.toLocaleString()}</span>
                </div>
            </div>`;
        }
        
        element.innerHTML = html;
    }
    
    updateTotalPrice() {
        const element = document.getElementById('total-price');
        if (!element) return;
        
        try {
            if (this.currentPricing && this.currentPricing.grand_total) {
                const total = parseFloat(this.currentPricing.grand_total) || 0;
                element.textContent = `R ${total.toLocaleString()}`;
            } else {
                element.textContent = 'R 0';
            }
        } catch (error) {
            console.error('Error updating total price:', error);
            element.textContent = 'Price unavailable';
        }
    }
    
    showCalculatingState() {
        const button = document.getElementById('submit-booking');
        if (button) {
            button.disabled = true;
            button.querySelector('.btn-text').textContent = 'Calculating...';
        }
        
        document.getElementById('total-price').textContent = 'Calculating...';
    }
    
    hideCalculatingState() {
        const button = document.getElementById('submit-booking');
        if (button) {
            button.disabled = false;
            button.querySelector('.btn-text').textContent = 'Request Booking';
        }
    }
    
    async submitBooking() {
        if (!this.validateForm()) {
            return;
        }
        
        const button = document.getElementById('submit-booking');
        const btnText = button.querySelector('.btn-text');
        const btnLoader = button.querySelector('.btn-loader');
        
        // Show loading state
        button.disabled = true;
        btnText.style.display = 'none';
        btnLoader.style.display = 'inline';
        
        try {
            const formData = new FormData(document.getElementById('leobo-booking-form'));
            
            const response = await fetch(leobo_booking_system.ajax_url, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccessMessage(data.data.message, data.data.booking_id);
                this.resetForm();
            } else {
                this.showErrorMessage(data.data || 'Failed to submit booking request');
            }
        } catch (error) {
            console.error('Booking submission error:', error);
            this.showErrorMessage('Network error. Please check your connection and try again.');
        } finally {
            // Reset button state
            button.disabled = false;
            btnText.style.display = 'inline';
            btnLoader.style.display = 'none';
        }
    }
    
    validateForm() {
        let isValid = true;
        
        // Clear previous errors
        document.querySelectorAll('.field-error').forEach(error => {
            error.textContent = '';
        });
        
        // Check required fields
        const requiredFields = [
            { id: 'checkin-date', error: 'checkin-error', message: 'Check-in date is required' },
            { id: 'checkout-date', error: 'checkout-error', message: 'Check-out date is required' },
            { id: 'selected-accommodation', error: 'accommodation-error', message: 'Please select accommodation' },
            { id: 'first-name', error: 'first-name-error', message: 'First name is required' },
            { id: 'last-name', error: 'last-name-error', message: 'Last name is required' },
            { id: 'email', error: 'email-error', message: 'Email address is required' },
            { id: 'phone', error: 'phone-error', message: 'Phone number is required' }
        ];
        
        requiredFields.forEach(field => {
            const element = document.getElementById(field.id);
            if (!element || !element.value.trim()) {
                this.showError(field.error, field.message);
                isValid = false;
            }
        });
        
        // Validate email format
        const email = document.getElementById('email').value;
        if (email && !this.isValidEmail(email)) {
            this.showError('email-error', 'Please enter a valid email address');
            isValid = false;
        }
        
        // Check if pricing is calculated
        if (!this.currentPricing) {
            this.showErrorMessage('Please complete your booking selection to see pricing');
            isValid = false;
        }
        
        return isValid;
    }
    
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    showError(errorId, message) {
        const errorElement = document.getElementById(errorId);
        if (errorElement) {
            errorElement.textContent = message;
        }
    }
    
    clearError(errorId) {
        const errorElement = document.getElementById(errorId);
        if (errorElement) {
            errorElement.textContent = '';
        }
    }
    
    showSuccessMessage(message, bookingId) {
        const messagesContainer = document.getElementById('booking-messages');
        const messageContent = messagesContainer.querySelector('.message-content');
        
        messageContent.innerHTML = `
            <div class="success-message">
                <h3>Booking Request Submitted!</h3>
                <p>${message}</p>
                <p><strong>Booking Reference:</strong> #${bookingId}</p>
                <p>We'll contact you within 24 hours to confirm your reservation.</p>
            </div>
        `;
        
        messagesContainer.style.display = 'block';
        messagesContainer.scrollIntoView({ behavior: 'smooth' });
    }
    
    showErrorMessage(message) {
        const messagesContainer = document.getElementById('booking-messages');
        const messageContent = messagesContainer.querySelector('.message-content');
        
        messageContent.innerHTML = `
            <div class="error-message">
                <h3>Booking Error</h3>
                <p>${message}</p>
                <p>Please try again or contact us directly for assistance.</p>
            </div>
        `;
        
        messagesContainer.style.display = 'block';
        messagesContainer.scrollIntoView({ behavior: 'smooth' });
    }
    
    resetForm() {
        document.getElementById('leobo-booking-form').reset();
        this.selectedDates = { checkin: null, checkout: null };
        this.selectedAccommodation = null;
        this.selectedPackages = [];
        this.currentPricing = null;
        
        // Reset visual selections
        document.querySelectorAll('.accommodation-option').forEach(option => {
            option.classList.remove('selected');
        });
        
        document.querySelectorAll('.package-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        
        this.updateCalendarDisplay();
        this.updateSummary();
    }
    
    setupFieldValidation() {
        // Real-time email validation
        jQuery('#email').on('blur', function() {
            const email = jQuery(this).val();
            if (email && !window.leoboBookingSystem.isValidEmail(email)) {
                window.leoboBookingSystem.showError('email-error', 'Please enter a valid email address');
            } else {
                window.leoboBookingSystem.clearError('email-error');
            }
        });
        
        // Real-time name validation
        jQuery('#first-name, #last-name').on('input', function() {
            const fieldId = jQuery(this).attr('id');
            const errorId = fieldId + '-error';
            
            if (jQuery(this).val().trim()) {
                window.leoboBookingSystem.clearError(errorId);
            }
        });
    }
    
    updateBlockedDates() {
        this.blockedDates = leobo_booking_system.blocked_dates || [];
    }
    
    formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    formatDateForDisplay(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            weekday: 'short',
            month: 'short',
            day: 'numeric'
        });
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (typeof leobo_booking_system !== 'undefined') {
        window.leoboBookingSystem = new LeoboCustomBookingSystem();
    }
});
