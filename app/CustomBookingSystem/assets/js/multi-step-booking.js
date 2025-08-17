/**
 * Leobo Custom Booking System - Multi-Step Dark Theme JavaScript
 * Handles step navigation, guest counters, date selection, and form validation
 */

class LeoboMultiStepBooking {
    constructor() {
        this.currentStep = 1;
        this.totalSteps = 4;
        this.selectedDates = {
            checkin: null,
            checkout: null
        };
        this.guests = {
            adults: 1,
            children: 0,
            babies: 0
        };
        this.selectedAccommodation = null;
        this.selectedPackages = [];
        this.formData = {};
        this.isCalculating = false;
        
        this.init();
    }
    
    init() {
        console.log('LeoboMultiStepBooking initialized');
        this.setupEventListeners();
        this.initializeDatePicker();
        this.updateProgressIndicator();
        this.loadInitialData();
    }
    
    setupEventListeners() {
        // Step navigation
        this.setupStepNavigation();
        
        // Guest counters
        this.setupGuestCounters();
        
        // Date selection
        this.setupDateSelection();
        
        // Form validation
        this.setupFormValidation();
        
        // Contact preferences
        this.setupContactPreferences();
        
        // Form submission
        this.setupFormSubmission();
    }
    
    setupStepNavigation() {
        // Next step buttons
        document.querySelectorAll('.next-step').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const nextStep = parseInt(button.dataset.next);
                if (this.validateCurrentStep()) {
                    this.goToStep(nextStep);
                }
            });
        });
        
        // Previous step buttons
        document.querySelectorAll('.prev-step').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const prevStep = parseInt(button.dataset.prev);
                this.goToStep(prevStep);
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
            dateSelector.addEventListener('click', (e) => {
                e.preventDefault();
                this.showDatePicker();
            });
        }
    }
    
    setupFormValidation() {
        // Real-time validation for required fields
        const requiredFields = document.querySelectorAll('input[required], select[required]');
        requiredFields.forEach(field => {
            field.addEventListener('blur', () => {
                this.validateField(field);
            });
            
            field.addEventListener('input', () => {
                this.clearFieldError(field);
            });
        });
    }
    
    setupContactPreferences() {
        const contactCheckboxes = document.querySelectorAll('input[name=\"contact_method[]\"]');
        contactCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                this.validateContactMethod();
            });
        });
    }
    
    setupFormSubmission() {
        const form = document.getElementById('leobo-booking-form');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitForm();
            });
        }
    }
    
    // Step Navigation Methods
    goToStep(stepNumber) {
        if (stepNumber < 1 || stepNumber > this.totalSteps) return;
        
        // Hide current step
        const currentStepElement = document.querySelector(`.form-step[data-step=\"${this.currentStep}\"]`);
        if (currentStepElement) {
            currentStepElement.classList.remove('active');
        }
        
        // Show new step
        const newStepElement = document.querySelector(`.form-step[data-step=\"${stepNumber}\"]`);
        if (newStepElement) {
            newStepElement.classList.add('active');
        }
        
        // Update current step
        this.currentStep = stepNumber;
        
        // Update progress indicator
        this.updateProgressIndicator();
        
        // Handle step-specific actions
        this.handleStepChange(stepNumber);
        
        // Scroll to top
        document.querySelector('.leobo-booking-wrapper').scrollIntoView({ 
            behavior: 'smooth',
            block: 'start'
        });
    }
    
    updateProgressIndicator() {
        const steps = document.querySelectorAll('.progress-step');
        steps.forEach((step, index) => {
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
                this.loadAccommodationOptions();
                break;
            case 3:
                // Pre-fill any available data
                break;
            case 4:
                this.updateBookingSummary();
                this.calculateFinalPricing();
                break;
        }
    }
    
    // Guest Counter Methods
    updateGuestCount(type, increment) {
        const currentValue = this.guests[type];
        let newValue = increment ? currentValue + 1 : currentValue - 1;
        
        // Set limits
        const limits = {
            adults: { min: 1, max: 12 },
            children: { min: 0, max: 8 },
            babies: { min: 0, max: 4 }
        };
        
        newValue = Math.max(limits[type].min, Math.min(limits[type].max, newValue));
        
        if (newValue !== currentValue) {
            this.guests[type] = newValue;
            
            // Update display
            const countDisplay = document.getElementById(`${type}-count`);
            const hiddenInput = document.getElementById(type);
            
            if (countDisplay) countDisplay.textContent = newValue;
            if (hiddenInput) hiddenInput.value = newValue;
            
            // Update counter buttons
            this.updateCounterButtons(type);
            
            // Update summary
            this.updateGuestSummary();
            
            // Recalculate pricing if dates are selected
            if (this.selectedDates.checkin && this.selectedDates.checkout) {
                this.calculatePricing();
            }
        }
    }
    
    updateCounterButtons(type) {
        const minusBtn = document.querySelector(`.counter-btn.minus[data-target=\"${type}\"]`);
        const plusBtn = document.querySelector(`.counter-btn.plus[data-target=\"${type}\"]`);
        
        const limits = {
            adults: { min: 1, max: 12 },
            children: { min: 0, max: 8 },
            babies: { min: 0, max: 4 }
        };
        
        if (minusBtn) {
            minusBtn.disabled = this.guests[type] <= limits[type].min;
        }
        
        if (plusBtn) {
            plusBtn.disabled = this.guests[type] >= limits[type].max;
        }
    }
    
    // Date Selection Methods
    showDatePicker() {
        // Create or show date picker modal
        let modal = document.getElementById('date-picker-modal');
        if (!modal) {
            this.createDatePickerModal();
            modal = document.getElementById('date-picker-modal');
        }
        
        modal.style.display = 'flex';
        this.initializeDatePickerCalendar();
    }
    
    createDatePickerModal() {
        // This would create the date picker modal if it doesn't exist
        // For now, we'll use a simple implementation
        console.log('Date picker modal would be created here');
    }
    
    initializeDatePickerCalendar() {
        // Initialize the calendar widget
        // This would integrate with a calendar library like Flatpickr or custom implementation
        console.log('Calendar widget would be initialized here');
    }
    
    selectDateRange(checkin, checkout) {
        this.selectedDates.checkin = checkin;
        this.selectedDates.checkout = checkout;
        
        // Update hidden inputs
        const checkinInput = document.getElementById('checkin-date');
        const checkoutInput = document.getElementById('checkout-date');
        
        if (checkinInput) checkinInput.value = checkin;
        if (checkoutInput) checkoutInput.value = checkout;
        
        // Update display
        this.updateDateDisplay();
        
        // Calculate pricing
        this.calculatePricing();
        
        // Hide date picker
        const modal = document.getElementById('date-picker-modal');
        if (modal) modal.style.display = 'none';
    }
    
    updateDateDisplay() {
        const dateText = document.querySelector('.date-text');
        if (dateText && this.selectedDates.checkin && this.selectedDates.checkout) {
            const checkinFormatted = this.formatDate(this.selectedDates.checkin);
            const checkoutFormatted = this.formatDate(this.selectedDates.checkout);
            dateText.textContent = `${checkinFormatted} - ${checkoutFormatted}`;
        }
    }
    
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }
}