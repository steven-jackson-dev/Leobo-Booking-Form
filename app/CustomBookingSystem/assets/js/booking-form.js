/**
 * Leobo Booking System - Exact Design Match JavaScript
 * Handles functionality for the multi-step booking form
 */

class LeoboBookingForm {
    constructor() {
        this.currentStep = 1;
        this.totalSteps = 4;
        this.formData = {
            guests: {
                adults: 3,
                children: 4,
                babies: 2
            },
            dates: {
                checkin: null,
                checkout: null,
                flexible: false
            },
            accommodation: null,
            extras: {
                transfer: [],
                helicopter_package: null,
                experiences: [],
                occasion: null
            },
            contact: {},
            pricing: {
                accommodation: 120000,
                extras: 22500,
                total: 144500
            }
        };
        
        this.init();
    }
    
    init() {
        console.log('LeoboBookingForm initialized');
        this.setupEventListeners();
        this.updateSidebarSummary();
        this.setupDatePicker();
        this.updateProgressIndicator();
    }
    
    setupEventListeners() {
        // Step navigation
        this.setupStepNavigation();
        
        // Guest counters
        this.setupGuestCounters();
        
        // Date selection
        this.setupDateSelection();
        
        // Form inputs
        this.setupFormInputs();
        
        // Form submission
        this.setupFormSubmission();
    }
    
    setupStepNavigation() {
        // Next step buttons
        document.querySelectorAll('.next-step').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const nextStep = parseInt(button.dataset.next);
                this.goToStep(nextStep);
            });
        });
        
        // Back step buttons
        document.querySelectorAll('.back-step').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const backStep = parseInt(button.dataset.back);
                this.goToStep(backStep);
            });
        });
        
        // Previous step buttons (legacy support)
        document.querySelectorAll('.prev-step').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const prevStep = parseInt(button.dataset.prev);
                this.goToStep(prevStep);
            });
        });
        
        // Progress step clicks
        document.querySelectorAll('.progress-step').forEach((step, index) => {
            step.addEventListener('click', () => {
                const stepNumber = index + 1;
                if (stepNumber <= this.currentStep) {
                    this.goToStep(stepNumber);
                }
            });
        });
    }
    
    setupGuestCounters() {
        document.querySelectorAll('.counter-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const target = button.dataset.target;
                const isIncrement = button.classList.contains('plus');
                this.updateGuestCount(target, isIncrement);
            });
        });
    }
    
    setupDateSelection() {
        const dateSelector = document.getElementById('date-selector');
        if (dateSelector) {
            dateSelector.addEventListener('click', () => {
                this.showDatePicker();
            });
        }
        
        // Flexible dates checkbox
        const flexibleDates = document.getElementById('flexible-dates');
        if (flexibleDates) {
            flexibleDates.addEventListener('change', (e) => {
                this.formData.dates.flexible = e.target.checked;
            });
        }
    }
    
    setupFormInputs() {
        // Transfer options
        document.querySelectorAll('input[name="transfer[]"]').forEach(input => {
            input.addEventListener('change', (e) => {
                this.updateTransferSelection(e.target.value, e.target.checked);
            });
        });
        
        // Helicopter package
        document.querySelectorAll('input[name="helicopter_package"]').forEach(input => {
            input.addEventListener('change', (e) => {
                this.formData.extras.helicopter_package = e.target.value;
                this.updatePricing();
            });
        });
        
        // Experiences
        document.querySelectorAll('input[name="experiences[]"]').forEach(input => {
            input.addEventListener('change', (e) => {
                this.updateExperienceSelection(e.target.value, e.target.checked);
            });
        });
        
        // Occasion select
        const occasionSelect = document.getElementById('occasion');
        if (occasionSelect) {
            occasionSelect.addEventListener('change', (e) => {
                this.formData.extras.occasion = e.target.value;
            });
        }
        
        // Contact form inputs
        const contactInputs = [
            'full-name', 'email', 'contact-number', 
            'home-address', 'country', 'how-heard',
            'special-requests', 'children-interests'
        ];
        
        contactInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                input.addEventListener('input', (e) => {
                    this.formData.contact[inputId] = e.target.value;
                });
            }
        });
    }
    
    setupFormSubmission() {
        const form = document.getElementById('leobo-booking-form');
        const submitBtn = document.getElementById('submit-booking');
        
        if (form && submitBtn) {
            submitBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.submitForm();
            });
        }
    }
    
    // Step Navigation Methods
    goToStep(stepNumber) {
        if (stepNumber < 1 || stepNumber > this.totalSteps) return;
        
        // Hide current step
        const currentStepElement = document.querySelector(`.booking-step-${this.currentStep}`);
        if (currentStepElement) {
            currentStepElement.classList.remove('active');
        }
        
        // Show new step
        const newStepElement = document.querySelector(`.booking-step-${stepNumber}`);
        if (newStepElement) {
            newStepElement.classList.add('active');
        }
        
        // Update current step
        this.currentStep = stepNumber;
        
        // Update progress indicator
        this.updateProgressIndicator();
        
        // Handle step-specific actions
        this.handleStepChange(stepNumber);
        
        // Scroll to top smoothly
        document.querySelector('.leobo-booking-wrapper').scrollIntoView({ 
            behavior: 'smooth',
            block: 'start'
        });
    }
    
    updateProgressIndicator() {
        const progressSteps = document.querySelectorAll('.progress-step');
        progressSteps.forEach((step, index) => {
            const stepNumber = index + 1;
            step.classList.remove('active', 'completed');
            
            if (stepNumber < this.currentStep) {
                step.classList.add('completed');
            } else if (stepNumber === this.currentStep) {
                step.classList.add('active');
            }
        });
    }
    
    handleStepChange(stepNumber) {
        switch(stepNumber) {
            case 2:
                this.showOptionalExtras();
                break;
            case 3:
                // Focus on first input
                const firstInput = document.getElementById('full-name');
                if (firstInput) {
                    setTimeout(() => firstInput.focus(), 300);
                }
                break;
            case 4:
                this.showThankYou();
                break;
        }
    }
    
    // Guest Counter Methods
    updateGuestCount(type, increment) {
        const currentValue = this.formData.guests[type];
        let newValue = increment ? currentValue + 1 : currentValue - 1;
        
        // Set limits
        const limits = {
            adults: { min: 1, max: 12 },
            children: { min: 0, max: 8 },
            babies: { min: 0, max: 4 }
        };
        
        newValue = Math.max(limits[type].min, Math.min(limits[type].max, newValue));
        
        if (newValue !== currentValue) {
            this.formData.guests[type] = newValue;
            
            // Update display
            const countDisplay = document.getElementById(`${type}-count`);
            const hiddenInput = document.getElementById(type);
            
            if (countDisplay) countDisplay.textContent = newValue;
            if (hiddenInput) hiddenInput.value = newValue;
            
            // Update counter buttons
            this.updateCounterButtons(type, newValue, limits[type]);
            
            // Update sidebar summary
            this.updateSidebarSummary();
            
            // Recalculate pricing
            this.updatePricing();
        }
    }
    
    updateCounterButtons(type, value, limits) {
        const minusBtn = document.querySelector(`.counter-btn.minus[data-target="${type}"]`);
        const plusBtn = document.querySelector(`.counter-btn.plus[data-target="${type}"]`);
        
        if (minusBtn) {
            minusBtn.disabled = value <= limits.min;
        }
        
        if (plusBtn) {
            plusBtn.disabled = value >= limits.max;
        }
    }
    
    // Date Selection Methods
    showDatePicker() {
        const modal = document.getElementById('date-picker-modal');
        if (modal) {
            modal.style.display = 'flex';
            this.initializeFlatpickr();
        }
    }
    
    hideDatePicker() {
        const modal = document.getElementById('date-picker-modal');
        if (modal) {
            modal.style.display = 'none';
        }
    }
    
    initializeFlatpickr() {
        const dateInput = document.getElementById('date_range_input');
        if (!dateInput || dateInput._flatpickr) return;
        
        // Get blocked dates from server
        const blockedDates = window.leobo_booking_system?.blocked_dates || [];
        
        flatpickr(dateInput, {
            mode: "range",
            dateFormat: "Y-m-d",
            minDate: "today",
            showMonths: 2,
            inline: true,
            disable: blockedDates,
            onChange: (selectedDates, dateStr, instance) => {
                if (selectedDates.length === 2) {
                    this.selectDateRange(selectedDates[0], selectedDates[1]);
                }
            }
        });
    }
    
    selectDateRange(checkin, checkout) {
        this.formData.dates.checkin = checkin;
        this.formData.dates.checkout = checkout;
        
        // Update hidden inputs
        const checkinInput = document.getElementById('checkin-date');
        const checkoutInput = document.getElementById('checkout-date');
        
        if (checkinInput) checkinInput.value = this.formatDate(checkin);
        if (checkoutInput) checkoutInput.value = this.formatDate(checkout);
        
        // Update display
        this.updateDateDisplay(checkin, checkout);
        
        // Update sidebar
        this.updateSidebarSummary();
        
        // Calculate pricing
        this.updatePricing();
        
        // Hide date picker after a delay
        setTimeout(() => {
            this.hideDatePicker();
        }, 1000);
    }
    
    updateDateDisplay(checkin, checkout) {
        const dateText = document.querySelector('.date-text');
        const dateSubtext = document.getElementById('date-range-info');
        const rangeText = document.getElementById('date-range-text');
        
        if (dateText && checkin && checkout) {
            const checkinFormatted = this.formatDisplayDate(checkin);
            const checkoutFormatted = this.formatDisplayDate(checkout);
            const nights = this.calculateNights(checkin, checkout);
            
            dateText.textContent = `${checkinFormatted} - ${checkoutFormatted}`;
            
            if (dateSubtext && rangeText) {
                rangeText.textContent = `${checkinFormatted} to ${checkoutFormatted}`;
                dateSubtext.style.display = 'block';
            }
        }
    }
    
    formatDate(date) {
        return date.toISOString().split('T')[0];
    }
    
    formatDisplayDate(date) {
        return date.toLocaleDateString('en-GB', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        });
    }
    
    calculateNights(checkin, checkout) {
        const timeDiff = checkout.getTime() - checkin.getTime();
        return Math.ceil(timeDiff / (1000 * 3600 * 24));
    }
    
    // Extras Selection Methods
    updateTransferSelection(value, checked) {
        if (checked) {
            if (!this.formData.extras.transfer.includes(value)) {
                this.formData.extras.transfer.push(value);
            }
        } else {
            this.formData.extras.transfer = this.formData.extras.transfer.filter(t => t !== value);
        }
        this.updatePricing();
    }
    
    updateExperienceSelection(value, checked) {
        if (checked) {
            if (!this.formData.extras.experiences.includes(value)) {
                this.formData.extras.experiences.push(value);
            }
        } else {
            this.formData.extras.experiences = this.formData.extras.experiences.filter(e => e !== value);
        }
        this.updatePricing();
    }
    
    showOptionalExtras() {
        const optionalExtras = document.querySelector('.optional-extras');
        const totalCosts = document.querySelector('.total-costs');
        
        if (optionalExtras) optionalExtras.style.display = 'block';
        if (totalCosts) totalCosts.style.display = 'block';
    }
    
    // Sidebar Summary Methods
    updateSidebarSummary() {
        // Update guest counts
        const sidebarAdults = document.getElementById('sidebar-adults');
        const sidebarChildren = document.getElementById('sidebar-children');
        const sidebarBabies = document.getElementById('sidebar-babies');
        
        if (sidebarAdults) sidebarAdults.textContent = this.formData.guests.adults;
        if (sidebarChildren) sidebarChildren.textContent = this.formData.guests.children;
        if (sidebarBabies) sidebarBabies.textContent = this.formData.guests.babies;
        
        // Update dates
        this.updateSidebarDates();
    }
    
    updateSidebarDates() {
        const datesDisplay = document.getElementById('dates-summary');
        
        if (datesDisplay && this.formData.dates.checkin && this.formData.dates.checkout) {
            const checkinFormatted = this.formatDisplayDate(this.formData.dates.checkin);
            const checkoutFormatted = this.formatDisplayDate(this.formData.dates.checkout);
            const nights = this.calculateNights(this.formData.dates.checkin, this.formData.dates.checkout);
            
            datesDisplay.innerHTML = `
                <div class="date-range">${checkinFormatted} - ${checkoutFormatted}</div>
                <div class="nights-count">(${nights} nights)</div>
            `;
        }
    }
    
    // Pricing Methods
    updatePricing() {
        // This would normally make an AJAX call to calculate real pricing
        // For now, we'll simulate the pricing calculation
        
        let extrasTotal = 0;
        
        // Calculate extras
        if (this.formData.extras.helicopter_package === 'yes') {
            extrasTotal += 15000;
        }
        
        extrasTotal += this.formData.extras.transfer.length * 2500;
        extrasTotal += this.formData.extras.experiences.length * 1500;
        
        this.formData.pricing.extras = extrasTotal;
        this.formData.pricing.total = this.formData.pricing.accommodation + extrasTotal;
        
        // Update display
        this.updatePricingDisplay();
    }
    
    updatePricingDisplay() {
        const accommodationPrice = document.getElementById('accommodation-price');
        const extrasPrice = document.getElementById('extras-price');
        const totalPrice = document.getElementById('total-price');
        
        if (accommodationPrice) {
            accommodationPrice.textContent = `R ${this.formatPrice(this.formData.pricing.accommodation)}`;
        }
        
        if (extrasPrice) {
            extrasPrice.textContent = `R ${this.formatPrice(this.formData.pricing.extras)}`;
        }
        
        if (totalPrice) {
            totalPrice.textContent = `R ${this.formatPrice(this.formData.pricing.total)}`;
        }
    }
    
    formatPrice(amount) {
        return new Intl.NumberFormat('en-ZA').format(amount);
    }
    
    // Form Submission
    submitForm() {
        // Validate form
        if (!this.validateForm()) {
            return;
        }
        
        // Show loading state
        this.showLoadingState();
        
        // Prepare form data for submission
        const submissionData = this.prepareSubmissionData();
        
        // Submit via AJAX
        this.sendFormData(submissionData);
    }
    
    validateForm() {
        // Basic validation
        const requiredFields = ['full-name', 'email', 'contact-number'];
        let isValid = true;
        
        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field && !field.value.trim()) {
                this.showFieldError(field, 'This field is required');
                isValid = false;
            }
        });
        
        // Validate dates
        if (!this.formData.dates.checkin || !this.formData.dates.checkout) {
            this.showError('Please select your travel dates');
            isValid = false;
        }
        
        return isValid;
    }
    
    prepareSubmissionData() {
        return {
            action: 'submit_booking_request',
            nonce: window.leobo_booking_system?.nonce || '',
            ...this.formData
        };
    }
    
    sendFormData(data) {
        fetch(window.leobo_booking_system?.ajax_url || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(data)
        })
        .then(response => response.json())
        .then(result => {
            this.hideLoadingState();
            
            if (result.success) {
                this.goToStep(4); // Thank you page
            } else {
                this.showError(result.data || 'An error occurred. Please try again.');
            }
        })
        .catch(error => {
            this.hideLoadingState();
            this.showError('An error occurred. Please try again.');
            console.error('Submission error:', error);
        });
    }
    
    showThankYou() {
        // Any additional thank you page setup
        console.log('Thank you page displayed');
    }
    
    // Utility Methods
    showLoadingState() {
        const submitBtn = document.getElementById('submit-booking');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="btn-loading">Submitting...</span>';
        }
    }
    
    hideLoadingState() {
        const submitBtn = document.getElementById('submit-booking');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'SUBMIT';
        }
    }
    
    showError(message) {
        const errorContainer = document.getElementById('error-message');
        if (errorContainer) {
            errorContainer.textContent = message;
            errorContainer.style.display = 'block';
            
            // Hide after 5 seconds
            setTimeout(() => {
                errorContainer.style.display = 'none';
            }, 5000);
        }
    }
    
    showFieldError(field, message) {
        field.style.borderColor = '#dc3545';
        
        // Remove error styling after user starts typing
        const removeError = () => {
            field.style.borderColor = '';
            field.removeEventListener('input', removeError);
        };
        
        field.addEventListener('input', removeError);
    }
    
    // Date picker modal event handlers
    setupDatePickerModal() {
        const modal = document.getElementById('date-picker-modal');
        const overlay = modal?.querySelector('.modal-overlay');
        const closeBtn = modal?.querySelector('.modal-close');
        const cancelBtn = modal?.querySelector('.cancel-dates');
        const confirmBtn = modal?.querySelector('.confirm-dates');
        
        [overlay, closeBtn, cancelBtn].forEach(element => {
            if (element) {
                element.addEventListener('click', () => this.hideDatePicker());
            }
        });
        
        if (confirmBtn) {
            confirmBtn.addEventListener('click', () => this.hideDatePicker());
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new LeoboBookingForm();
});
