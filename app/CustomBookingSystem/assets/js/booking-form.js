/**
 * Leobo Booking System - Exact Design Match JavaScript
 * Handles functionality for the multi-step booking form
 */

class LeoboBookingForm {
    constructor() {
        this.currentStep = 1;
        this.totalSteps = 4;
        
        // Get configuration from localized data
        this.config = leobo_booking_system || {};
        
        // Debug: Check if configuration is loaded
        if (typeof leobo_booking_system === 'undefined') {
            console.error('leobo_booking_system not found! Check script localization.');
        } else {
            console.log('Leobo booking system configuration loaded:', this.config);
        }
        
        this.guestRules = this.config.guest_rules || {
            max_adults: 6,
            max_children: 4,
            children_free_age: 4
        };
        
        this.formData = {
            guests: {
                adults: 1, // Default to 1 adult minimum
                children: 0, // Default to 0 (optional)
                babies: 0 // Default to 0 (optional)
            },
            dates: {
                checkin: null,
                checkout: null,
                flexible: false
            },
            accommodation: null,
            extras: {
                transfer: 'no_transfer', // Default radio button selection
                helicopter_package: null,
                experiences: [],
                occasion: null
            },
            contact: {},
            pricing: {
                accommodation: 0, // Will be calculated dynamically
                extras: 0,
                total: 0,
                nights: 0,
                breakdown: []
            }
        };
        
        this.init();
    }
    
    init() {
        console.log('LeoboBookingForm initialized');
        this.setupEventListeners();
        this.initializeGuestCounters(); // Initialize guest counters with correct values
        this.initializePlaceholders(); // Set initial placeholder text
        this.setupHelicopterToggle(); // Initialize helicopter package toggle
        this.updateProgressIndicator();
        this.updateCostBreakdown(); // Initialize cost breakdown
        this.updateSidebarSummary(); // Ensure sidebar shows correct initial state
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
        
        // Initialize default selections
        this.initializeDefaultSelections();
        
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
    
    initializeGuestCounters() {
        // Set initial counter states and button states
        const guestTypes = ['adults', 'children', 'babies'];
        
        guestTypes.forEach(type => {
            const currentValue = this.formData.guests[type];
            
            // Update display
            const countDisplay = document.getElementById(`${type}-count`);
            const hiddenInput = document.getElementById(type);
            
            if (countDisplay) countDisplay.textContent = currentValue;
            if (hiddenInput) hiddenInput.value = currentValue;
            
            // Set up limits and button states
            const limits = {
                adults: { min: 1, max: this.config.acf_config?.adults_max || 12 },
                children: { min: 0, max: this.config.acf_config?.children_max || 8 },
                babies: { min: 0, max: this.config.acf_config?.babies_max || 4 }
            };
            
            this.updateCounterButtons(type, currentValue, limits[type]);
        });
        
        // Don't trigger pricing calculation on initialization - let placeholders show
        // this.updatePricing();
    }
    
    initializePlaceholders() {
        // Set initial placeholder text for pricing elements
        const accommodationPrice = document.getElementById('accommodation-price');
        const extrasPrice = document.getElementById('extras-price');
        const totalPrice = document.getElementById('total-price');
        
        if (accommodationPrice) {
            accommodationPrice.textContent = 'Select dates to see pricing';
        }
        
        if (extrasPrice) {
            extrasPrice.textContent = '-';
        }
        
        if (totalPrice) {
            totalPrice.textContent = 'Select dates and guests';
        }
    }
    
    setupDateSelection() {
        // Initialize calendar properties
        this.calendar = {
            currentDate: new Date(),
            selectedStartDate: null,
            selectedEndDate: null,
            isOpen: false,
            isSelectingRange: false
        };
        
        // Get season dates from configuration
        const seasonDates = this.config.season_dates || {};
        console.log('Season dates loaded:', seasonDates);
        
        // Initialize custom date picker
        this.initializeCustomDatePicker();
        
        // Flexible dates checkbox
        const flexibleDates = document.getElementById('flexible-dates');
        if (flexibleDates) {
            flexibleDates.addEventListener('change', (e) => {
                this.formData.dates.flexible = e.target.checked;
            });
        }
    }
    
    initializeCustomDatePicker() {
        const trigger = document.getElementById('date-picker-trigger');
        const calendar = document.getElementById('calendar-widget');
        const prevBtn = document.getElementById('prev-month');
        const nextBtn = document.getElementById('next-month');
        const clearBtn = document.getElementById('calendar-clear');
        const applyBtn = document.getElementById('calendar-apply');
        
        if (!trigger || !calendar) return;
        
        // Toggle calendar visibility
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleCalendar();
        });
        
        // Month navigation
        if (prevBtn) {
            prevBtn.addEventListener('click', () => this.previousMonth());
        }
        if (nextBtn) {
            nextBtn.addEventListener('click', () => this.nextMonth());
        }
        
        // Calendar actions
        if (clearBtn) {
            clearBtn.addEventListener('click', () => this.clearDates());
        }
        if (applyBtn) {
            applyBtn.addEventListener('click', () => this.applyDates());
        }
        
        // Close calendar when clicking outside
        document.addEventListener('click', (e) => {
            if (!calendar.contains(e.target) && !trigger.contains(e.target)) {
                this.closeCalendar();
            }
        });
        
        // Initialize calendar display
        this.renderCalendar();
    }
    
    toggleCalendar() {
        const calendar = document.getElementById('calendar-widget');
        const trigger = document.getElementById('date-picker-trigger');
        
        if (this.calendar.isOpen) {
            this.closeCalendar();
        } else {
            this.openCalendar();
        }
    }
    
    openCalendar() {
        const calendar = document.getElementById('calendar-widget');
        const trigger = document.getElementById('date-picker-trigger');
        
        if (calendar && trigger) {
            calendar.style.display = 'block';
            trigger.classList.add('active');
            this.calendar.isOpen = true;
            this.renderCalendar();
        }
    }
    
    closeCalendar() {
        const calendar = document.getElementById('calendar-widget');
        const trigger = document.getElementById('date-picker-trigger');
        
        if (calendar && trigger) {
            calendar.style.display = 'none';
            trigger.classList.remove('active');
            this.calendar.isOpen = false;
        }
    }
    
    previousMonth() {
        this.calendar.currentDate.setMonth(this.calendar.currentDate.getMonth() - 1);
        this.renderCalendar();
    }
    
    nextMonth() {
        this.calendar.currentDate.setMonth(this.calendar.currentDate.getMonth() + 1);
        this.renderCalendar();
    }
    
    renderCalendar() {
        const calendarTitle = document.getElementById('calendar-title');
        const calendarDays = document.getElementById('calendar-days');
        
        if (!calendarTitle || !calendarDays) return;
        
        // Update title
        const monthNames = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        
        calendarTitle.textContent = `${monthNames[this.calendar.currentDate.getMonth()]} ${this.calendar.currentDate.getFullYear()}`;
        
        // Clear previous days
        calendarDays.innerHTML = '';
        
        // Get first day of month and number of days
        const firstDay = new Date(this.calendar.currentDate.getFullYear(), this.calendar.currentDate.getMonth(), 1);
        const lastDay = new Date(this.calendar.currentDate.getFullYear(), this.calendar.currentDate.getMonth() + 1, 0);
        const startDate = new Date(firstDay);
        startDate.setDate(startDate.getDate() - firstDay.getDay()); // Start from Sunday
        
        // Generate calendar days
        const today = new Date();
        const seasonDates = this.config.season_dates || {};
        const blockedDates = this.config.blocked_dates || [];
        
        for (let i = 0; i < 42; i++) { // 6 weeks
            const date = new Date(startDate);
            date.setDate(startDate.getDate() + i);
            
            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-day';
            dayElement.textContent = date.getDate();
            
            const dateString = this.formatDate(date);
            
            // Add classes based on date status - PRIORITIZE BLOCKED DATES
            if (date.getMonth() !== this.calendar.currentDate.getMonth()) {
                dayElement.classList.add('other-month');
            } else if (blockedDates.includes(dateString)) {
                // CHECK BLOCKED DATES FIRST - they take priority over past/future
                dayElement.classList.add('blocked');
            } else if (date < today) {
                dayElement.classList.add('past');
            } else {
                dayElement.classList.add('available');
                
                // Add seasonal color coding
                const season = this.getDateSeason(dateString, seasonDates);
                if (season) {
                    dayElement.classList.add(`season-${season}`);
                }
            }
            
            // Add selection classes
            if (this.calendar.selectedStartDate && this.isSameDate(date, this.calendar.selectedStartDate)) {
                dayElement.classList.add('selected', 'range-start');
            } else if (this.calendar.selectedEndDate && this.isSameDate(date, this.calendar.selectedEndDate)) {
                dayElement.classList.add('selected', 'range-end');
            } else if (this.calendar.selectedStartDate && this.calendar.selectedEndDate && 
                       date > this.calendar.selectedStartDate && date < this.calendar.selectedEndDate) {
                dayElement.classList.add('in-range');
            }
            
            // Add click handler for available dates only (not blocked)
            if (dayElement.classList.contains('available')) {
                dayElement.addEventListener('click', () => this.selectDate(new Date(date)));
            }
            
            calendarDays.appendChild(dayElement);
        }
        
        // Render the season legend if it doesn't exist
        this.renderSeasonLegend();
    }
    
    selectDate(date) {
        // Check if date is blocked
        const dateString = this.formatDate(date);
        const blockedDates = this.config.blocked_dates || [];
        
        if (blockedDates.includes(dateString)) {
            alert('This date is not available for booking. Please select a different date.');
            return;
        }
        
        if (!this.calendar.selectedStartDate || 
            (this.calendar.selectedStartDate && this.calendar.selectedEndDate)) {
            // Start new selection
            this.calendar.selectedStartDate = new Date(date);
            this.calendar.selectedEndDate = null;
            this.calendar.isSelectingRange = true;
        } else if (this.calendar.selectedStartDate && !this.calendar.selectedEndDate) {
            // Complete range selection
            let startDate, endDate;
            
            if (date <= this.calendar.selectedStartDate) {
                // If selected date is before start date, make it the new start date
                startDate = new Date(date);
                endDate = this.calendar.selectedStartDate;
            } else {
                startDate = this.calendar.selectedStartDate;
                endDate = new Date(date);
            }
            
            // Check if any dates in the range are blocked
            if (this.hasBlockedDatesInRange(startDate, endDate)) {
                alert('Your selected date range contains unavailable dates. Please select a different range.');
                return;
            }
            
            this.calendar.selectedStartDate = startDate;
            this.calendar.selectedEndDate = endDate;
            this.calendar.isSelectingRange = false;
            
            // If both dates are selected, update pricing
            if (this.calendar.selectedStartDate && this.calendar.selectedEndDate) {
                // Update form data temporarily for pricing calculation
                this.formData.dates.checkin = this.formatDate(this.calendar.selectedStartDate);
                this.formData.dates.checkout = this.formatDate(this.calendar.selectedEndDate);
                this.updatePricing();
            }
        }
        
        this.renderCalendar();
        this.updateDateDisplay();
        this.updateSidebarSummary(); // Add sidebar update
    }
    
    // Helper method to check if a date range contains blocked dates
    hasBlockedDatesInRange(startDate, endDate) {
        const blockedDates = this.config.blocked_dates || [];
        const current = new Date(startDate);
        
        while (current <= endDate) {
            const dateString = this.formatDate(current);
            if (blockedDates.includes(dateString)) {
                return true;
            }
            current.setDate(current.getDate() + 1);
        }
        
        return false;
    }
    
    clearDates() {
        this.calendar.selectedStartDate = null;
        this.calendar.selectedEndDate = null;
        this.calendar.isSelectingRange = false;
        
        // Clear form data
        this.formData.dates.checkin = null;
        this.formData.dates.checkout = null;
        
        // Clear hidden inputs
        const arrivalInput = document.getElementById('arrival-date');
        const departureInput = document.getElementById('departure-date');
        if (arrivalInput) arrivalInput.value = '';
        if (departureInput) departureInput.value = '';
        
        // Reset pricing display to placeholders
        this.initializePlaceholders();
        
        this.renderCalendar();
        this.updateDateDisplay();
        this.updateSidebarSummary(); // Add sidebar update
    }
    
    applyDates() {
        if (this.calendar.selectedStartDate && this.calendar.selectedEndDate) {
            // Update form data
            this.formData.dates.checkin = this.formatDate(this.calendar.selectedStartDate);
            this.formData.dates.checkout = this.formatDate(this.calendar.selectedEndDate);
            
            // Update hidden inputs
            const arrivalInput = document.getElementById('arrival-date');
            const departureInput = document.getElementById('departure-date');
            if (arrivalInput) arrivalInput.value = this.formData.dates.checkin;
            if (departureInput) departureInput.value = this.formData.dates.checkout;
            
            this.closeCalendar();
            this.updateSidebarSummary();
            this.updatePricing();
        } else {
            alert('Please select both arrival and departure dates.');
        }
    }
    
    setupFormInputs() {
        // Transfer options (radio buttons)
        document.querySelectorAll('input[name="transfer"]').forEach(input => {
            input.addEventListener('change', (e) => {
                this.updateTransferSelection(e.target.value);
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
    
    setupHelicopterToggle() {
        // Handle helicopter interest toggle
        const helicopterYes = document.getElementById('helicopter-yes');
        const helicopterNo = document.getElementById('helicopter-no');
        const helicopterOptions = document.getElementById('helicopter-options');
        
        if (helicopterYes && helicopterNo && helicopterOptions) {
            helicopterYes.addEventListener('change', () => {
                if (helicopterYes.checked) {
                    helicopterOptions.style.display = 'block';
                    // Smooth animation
                    setTimeout(() => {
                        helicopterOptions.style.opacity = '1';
                    }, 10);
                }
            });
            
            helicopterNo.addEventListener('change', () => {
                if (helicopterNo.checked) {
                    helicopterOptions.style.opacity = '0';
                    setTimeout(() => {
                        helicopterOptions.style.display = 'none';
                        // Clear any selected helicopter package
                        document.querySelectorAll('input[name="helicopter_package"]').forEach(input => {
                            input.checked = false;
                        });
                        this.formData.extras.helicopter_package = null;
                        this.updatePricing();
                    }, 300);
                }
            });
        }
    }
    
    initializeDefaultSelections() {
        // Initialize transfer selection based on checked radio button
        const checkedTransfer = document.querySelector('input[name="transfer"]:checked');
        if (checkedTransfer) {
            this.formData.extras.transfer = checkedTransfer.value;
        }
        // If no transfer option is checked, ensure default is set
        else {
            const defaultTransfer = document.querySelector('input[name="transfer"][value="no_transfer"]');
            if (defaultTransfer) {
                defaultTransfer.checked = true;
                this.formData.extras.transfer = 'no_transfer';
            }
        }
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
        
        // Always update cost breakdown when step changes (for real-time updates)
        this.updateCostBreakdown();
    }
    
    // Guest Counter Methods
    updateGuestCount(type, increment) {
        const currentValue = this.formData.guests[type];
        let newValue = increment ? currentValue + 1 : currentValue - 1;
        
        // Use ACF configured limits
        const limits = {
            adults: { min: 1, max: this.config.acf_config?.adults_max || 12 },
            children: { min: 0, max: this.config.acf_config?.children_max || 8 },
            babies: { min: 0, max: this.config.acf_config?.babies_max || 4 }
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
    
    // Date Selection Methods - Now using simple date inputs
    
    selectDateRange(checkin, checkout) {
        // Convert string dates to Date objects if needed
        const checkinDate = checkin instanceof Date ? checkin : new Date(checkin);
        const checkoutDate = checkout instanceof Date ? checkout : new Date(checkout);
        
        const nights = this.calculateNights(checkinDate, checkoutDate);
        const minNights = this.config.acf_config?.minimum_nights_standard || 3;
        
        // Validate minimum nights
        if (nights < minNights) {
            alert(`Minimum stay is ${minNights} nights. Please select a longer period.`);
            return;
        }
        
        this.formData.dates.checkin = checkinDate;
        this.formData.dates.checkout = checkoutDate;
        
        // Update the date input values
        const arrivalInput = document.getElementById('arrival-date');
        const departureInput = document.getElementById('departure-date');
        
        if (arrivalInput) arrivalInput.value = this.formatDate(checkinDate);
        if (departureInput) departureInput.value = this.formatDate(checkoutDate);
        
        // Update display
        this.updateDateDisplay();
        
        // Update sidebar
        this.updateSidebarSummary();
        
        // Calculate pricing
        this.updatePricing();
    }
    
    updateDateDisplay() {
        const arrivalDisplay = document.getElementById('arrival-display');
        const departureDisplay = document.getElementById('departure-display');
        const dateInfo = document.getElementById('date-range-info');
        const rangeText = document.getElementById('date-range-text');
        const nightsCount = document.getElementById('nights-count');
        
        // Update the date picker trigger display
        if (this.calendar.selectedStartDate) {
            const arrivalFormatted = this.formatDisplayDate(this.calendar.selectedStartDate);
            arrivalDisplay.textContent = arrivalFormatted;
            arrivalDisplay.classList.remove('placeholder');
            
            // Update form data for consistency
            this.formData.dates.checkin = this.formatDate(this.calendar.selectedStartDate);
        } else {
            arrivalDisplay.textContent = 'Select date';
            arrivalDisplay.classList.add('placeholder');
            this.formData.dates.checkin = null;
        }
        
        if (this.calendar.selectedEndDate) {
            const departureFormatted = this.formatDisplayDate(this.calendar.selectedEndDate);
            departureDisplay.textContent = departureFormatted;
            departureDisplay.classList.remove('placeholder');
            
            // Update form data for consistency
            this.formData.dates.checkout = this.formatDate(this.calendar.selectedEndDate);
        } else {
            departureDisplay.textContent = 'Select date';
            departureDisplay.classList.add('placeholder');
            this.formData.dates.checkout = null;
        }
        
        // Update date range info
        if (this.calendar.selectedStartDate && this.calendar.selectedEndDate) {
            const checkin = this.formatDate(this.calendar.selectedStartDate);
            const checkout = this.formatDate(this.calendar.selectedEndDate);
            const nights = this.calculateNights(checkin, checkout);
            
            if (rangeText) {
                rangeText.textContent = `${nights} night${nights !== 1 ? 's' : ''} selected`;
            }
            if (nightsCount) {
                nightsCount.textContent = `(${this.formatDisplayDate(this.calendar.selectedStartDate)} → ${this.formatDisplayDate(this.calendar.selectedEndDate)})`;
            }
            if (dateInfo) {
                dateInfo.style.display = 'block';
            }
        } else {
            if (dateInfo) {
                dateInfo.style.display = 'none';
            }
        }
    }
    
    formatDate(date) {
        if (!date) return '';
        if (typeof date === 'string') return date; // Already formatted
        if (!(date instanceof Date)) {
            date = new Date(date);
        }
        if (isNaN(date.getTime())) return '';
        
        // Use timezone-safe formatting to avoid UTC conversion issues
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    formatDisplayDate(date) {
        if (!date) return '';
        if (!(date instanceof Date)) {
            date = new Date(date);
        }
        if (isNaN(date.getTime())) return '';
        return date.toLocaleDateString('en-GB', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        });
    }
    
    calculateNights(checkin, checkout) {
        if (!checkin || !checkout) return 0;
        
        // Ensure we have Date objects
        if (!(checkin instanceof Date)) {
            checkin = new Date(checkin);
        }
        if (!(checkout instanceof Date)) {
            checkout = new Date(checkout);
        }
        
        if (isNaN(checkin.getTime()) || isNaN(checkout.getTime())) return 0;
        
        const timeDiff = checkout.getTime() - checkin.getTime();
        return Math.ceil(timeDiff / (1000 * 3600 * 24));
    }
    
    // Calendar helper methods
    isSameDate(date1, date2) {
        if (!date1 || !date2) return false;
        return date1.getFullYear() === date2.getFullYear() &&
               date1.getMonth() === date2.getMonth() &&
               date1.getDate() === date2.getDate();
    }
    
    getDateSeason(dateString, seasonDates) {
        if (!dateString || !seasonDates) return null;
        
        // Check Christmas season first (highest priority)
        if (seasonDates.christmas && seasonDates.christmas.length > 0) {
            for (const range of seasonDates.christmas) {
                if (this.isDateInRange(dateString, range.start, range.end)) {
                    return 'christmas';
                }
            }
        }
        
        // Check Peak season
        if (seasonDates.peak && seasonDates.peak.length > 0) {
            for (const range of seasonDates.peak) {
                if (this.isDateInRange(dateString, range.start, range.end)) {
                    return 'peak';
                }
            }
        }
        
        // Check Standard season
        if (seasonDates.standard && seasonDates.standard.length > 0) {
            for (const range of seasonDates.standard) {
                if (this.isDateInRange(dateString, range.start, range.end)) {
                    return 'standard';
                }
            }
        }
        
        return 'standard'; // Default to standard if no match
    }
    
    isDateInRange(dateString, startDate, endDate) {
        if (!dateString || !startDate || !endDate) return false;
        
        const date = new Date(dateString);
        const start = new Date(startDate);
        const end = new Date(endDate);
        
        return date >= start && date <= end;
    }
    
    renderSeasonLegend() {
        // Check if legend already exists
        let legend = document.getElementById('season-legend');
        if (legend) return;
        
        // Find the calendar widget to add legend after
        const calendarWidget = document.getElementById('calendar-widget');
        if (!calendarWidget) return;
        
        // Create legend element
        legend = document.createElement('div');
        legend.id = 'season-legend';
        legend.className = 'season-legend';
        legend.innerHTML = `
            <div class="legend-title">Calendar Legend</div>
            <div class="legend-items">
                <div class="legend-item">
                    <div class="legend-color season-standard"></div>
                    <span>Standard Season</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color season-peak"></div>
                    <span>Peak Season</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color season-christmas"></div>
                    <span>Christmas Season</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color blocked"></div>
                    <span>Unavailable</span>
                </div>
            </div>
        `;
        
        // Insert after calendar widget
        calendarWidget.parentNode.insertBefore(legend, calendarWidget.nextSibling);
    }

    
    // Extras Selection Methods
    updateTransferSelection(value) {
        // For radio buttons, just set the single selected value
        this.formData.extras.transfer = value;
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
        // Update guest counts with better display logic
        const sidebarAdults = document.getElementById('sidebar-adults');
        const sidebarChildren = document.getElementById('sidebar-children');
        const sidebarBabies = document.getElementById('sidebar-babies');
        
        if (sidebarAdults) {
            sidebarAdults.textContent = this.formData.guests.adults;
        }
        if (sidebarChildren) {
            sidebarChildren.textContent = this.formData.guests.children > 0 ? this.formData.guests.children : '-';
        }
        if (sidebarBabies) {
            sidebarBabies.textContent = this.formData.guests.babies > 0 ? this.formData.guests.babies : '-';
        }
        
        // Update dates
        this.updateSidebarDates();
    }
    
    updateSidebarDates() {
        const datesDisplay = document.getElementById('dates-summary');
        
        if (!datesDisplay) return;
        
        // Check if we have both dates selected
        if (this.formData.dates.checkin && this.formData.dates.checkout) {
            // Ensure dates are Date objects for formatting
            const checkinDate = this.formData.dates.checkin instanceof Date 
                ? this.formData.dates.checkin
                : new Date(this.formData.dates.checkin);
                
            const checkoutDate = this.formData.dates.checkout instanceof Date 
                ? this.formData.dates.checkout
                : new Date(this.formData.dates.checkout);
            
            const checkinFormatted = this.formatDisplayDate(checkinDate);
            const checkoutFormatted = this.formatDisplayDate(checkoutDate);
            const nights = this.calculateNights(checkinDate, checkoutDate);
            
            datesDisplay.innerHTML = `
                <div class="date-range">${checkinFormatted} - ${checkoutFormatted}</div>
                <div class="nights-count">(${nights} night${nights !== 1 ? 's' : ''})</div>
                <div class="season-info" id="season-info" style="display: none;">
                    <span class="season-label" id="season-display"></span>
                </div>
            `;
            
            // Update season information if pricing data is available
            this.updateSeasonDisplay();
        } else if (this.formData.dates.checkin && !this.formData.dates.checkout) {
            // Only arrival date selected
            const checkinDate = this.formData.dates.checkin instanceof Date 
                ? this.formData.dates.checkin
                : new Date(this.formData.dates.checkin);
            const checkinFormatted = this.formatDisplayDate(checkinDate);
            
            datesDisplay.innerHTML = `
                <div class="date-range">${checkinFormatted} - <span class="date-placeholder">Select departure</span></div>
                <div class="nights-count">(- nights)</div>
            `;
        } else {
            // No dates selected - show placeholder
            datesDisplay.innerHTML = `
                <div class="date-placeholder">Select your dates</div>
            `;
        }
    }
    
    updateSeasonDisplay() {
        const seasonInfo = document.getElementById('season-info');
        const seasonDisplay = document.getElementById('season-display');
        
        if (!seasonInfo || !seasonDisplay) return;
        
        if (this.formData.pricing.breakdown && this.formData.pricing.breakdown.length > 0) {
            const seasonAnalysis = this.analyzeSeasons();
            
            if (seasonAnalysis.seasonText) {
                seasonDisplay.textContent = seasonAnalysis.seasonText;
                seasonInfo.className = `season-info ${seasonAnalysis.cssClass}`;
                seasonInfo.style.display = 'block';
            } else {
                seasonInfo.style.display = 'none';
            }
        } else {
            seasonInfo.style.display = 'none';
        }
    }
    
    analyzeSeasons() {
        if (!this.formData.pricing.breakdown || this.formData.pricing.breakdown.length === 0) {
            return { seasonText: null, cssClass: '' };
        }
        
        // Count seasons in the date range
        const seasonCounts = {
            christmas: 0,
            peak: 0,
            standard: 0
        };
        
        const totalNights = this.formData.pricing.breakdown.length;
        
        this.formData.pricing.breakdown.forEach(night => {
            const season = night.season || 'standard';
            if (seasonCounts.hasOwnProperty(season)) {
                seasonCounts[season]++;
            }
        });
        
        // Determine predominant season and generate display text
        const seasons = Object.entries(seasonCounts)
            .filter(([season, count]) => count > 0)
            .sort(([,a], [,b]) => b - a);
        
        if (seasons.length === 0) {
            return { seasonText: 'Standard Season', cssClass: 'standard' };
        }
        
        if (seasons.length === 1) {
            // Single season
            const [season] = seasons[0];
            return {
                seasonText: this.formatSeasonName(season),
                cssClass: season
            };
        } else {
            // Mixed seasons - show the predominant one
            const [predominantSeason, predominantCount] = seasons[0];
            
            if (predominantCount === totalNights) {
                // Actually all the same season
                return {
                    seasonText: this.formatSeasonName(predominantSeason),
                    cssClass: predominantSeason
                };
            } else {
                // Mixed seasons - show primary + "Mixed"
                const percentage = Math.round((predominantCount / totalNights) * 100);
                return {
                    seasonText: `${this.formatSeasonName(predominantSeason)} (${percentage}%) + Mixed`,
                    cssClass: 'mixed'
                };
            }
        }
    }
    
    formatSeasonName(season) {
        const seasonNames = {
            christmas: 'Christmas Season',
            peak: 'Peak Season', 
            standard: 'Standard Season'
        };
        return seasonNames[season] || 'Standard Season';
    }
    
    formatSeasonLetter(season) {
        const seasonLetters = {
            christmas: 'C',
            peak: 'P', 
            standard: 'S'
        };
        return seasonLetters[season] || 'S';
    }
    
    // Pricing Methods
    updatePricing() {
        // Check if leobo_booking_system is available
        if (typeof leobo_booking_system === 'undefined') {
            console.error('Leobo booking system configuration not loaded');
            this.showPricingError('Configuration error. Please refresh the page.');
            return;
        }
        
        // Only calculate if we have dates and at least 1 adult
        if (!this.formData.dates.checkin || !this.formData.dates.checkout || this.formData.guests.adults < 1) {
            return;
        }
        
        // Ensure dates are properly formatted as strings
        const checkinDate = this.formData.dates.checkin instanceof Date 
            ? this.formatDate(this.formData.dates.checkin)
            : this.formData.dates.checkin;
            
        const checkoutDate = this.formData.dates.checkout instanceof Date 
            ? this.formatDate(this.formData.dates.checkout)
            : this.formData.dates.checkout;
        
        // Prepare data for AJAX call
        const pricingData = {
            action: 'calculate_booking_price',
            nonce: leobo_booking_system.nonce,
            checkin_date: checkinDate,
            checkout_date: checkoutDate,
            adults: this.formData.guests.adults,
            children: this.formData.guests.children,
            helicopter_package: this.formData.extras.helicopter_package
        };
        
        // Show loading state
        this.showPricingLoader(true);
        
        // Make AJAX call to calculate pricing
        fetch(leobo_booking_system.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(pricingData)
        })
        .then(response => response.json())
        .then(data => {
            this.showPricingLoader(false);
            
            if (data.success) {
                // Update pricing data with real calculations
                this.formData.pricing.accommodation = data.data.accommodation_total;
                this.formData.pricing.extras = data.data.helicopter_total || 0;
                this.formData.pricing.total = data.data.grand_total;
                this.formData.pricing.nights = data.data.nights;
                this.formData.pricing.breakdown = data.data.nightly_breakdown;
                
                // Update display
                this.updatePricingDisplay();
                this.updateDatesSummary();
                
                // Show optional extras section if we're past step 1
                if (this.currentStep >= 2) {
                    this.showOptionalExtras();
                }
            } else {
                console.error('Pricing calculation error:', data.data);
                this.showPricingError(data.data);
            }
        })
        .catch(error => {
            this.showPricingLoader(false);
            console.error('AJAX error:', error);
            console.error('AJAX URL:', leobo_booking_system.ajax_url);
            console.error('Pricing data sent:', pricingData);
            this.showPricingError('Network error. Please check your connection and try again.');
        });
    }
    
    showPricingLoader(show) {
        const accommodationPrice = document.getElementById('accommodation-price');
        const extrasPrice = document.getElementById('extras-price');
        const totalPrice = document.getElementById('total-price');
        
        if (show) {
            if (accommodationPrice) accommodationPrice.textContent = 'Calculating...';
            if (extrasPrice) extrasPrice.textContent = 'Calculating...';
            if (totalPrice) totalPrice.textContent = 'Calculating...';
        }
    }
    
    showPricingError(message) {
        const accommodationPrice = document.getElementById('accommodation-price');
        const extrasPrice = document.getElementById('extras-price');
        const totalPrice = document.getElementById('total-price');
        
        // Display the actual error message instead of generic text
        if (accommodationPrice) {
            accommodationPrice.textContent = message || 'Error calculating price';
            accommodationPrice.style.color = '#d63638'; // WordPress error red
            accommodationPrice.style.fontSize = '14px';
        }
        
        if (extrasPrice) {
            extrasPrice.textContent = '-';
            extrasPrice.style.color = '';
        }
        
        if (totalPrice) {
            totalPrice.textContent = 'Unable to calculate';
            totalPrice.style.color = '#d63638';
            totalPrice.style.fontSize = '14px';
        }
        
        // Log for debugging
        console.error('Pricing error:', message);
    }
    
    showOptionalExtras() {
        const optionalExtras = document.querySelector('.optional-extras');
        const totalCosts = document.querySelector('.total-costs');
        
        if (optionalExtras) {
            optionalExtras.style.display = 'block';
        }
        
        if (totalCosts) {
            totalCosts.style.display = 'block';
        }
    }
    
    updateDatesSummary() {
        const datesSummary = document.getElementById('dates-summary');
        if (!datesSummary || !this.formData.dates.checkin || !this.formData.dates.checkout) {
            return;
        }
        
        const checkinDate = new Date(this.formData.dates.checkin);
        const checkoutDate = new Date(this.formData.dates.checkout);
        
        const checkinFormatted = checkinDate.toLocaleDateString('en-GB', { 
            day: 'numeric', 
            month: 'short' 
        });
        const checkoutFormatted = checkoutDate.toLocaleDateString('en-GB', { 
            day: 'numeric', 
            month: 'short' 
        });
        
        const nights = this.formData.pricing.nights || Math.ceil((checkoutDate - checkinDate) / (1000 * 60 * 60 * 24));
        
        datesSummary.innerHTML = `
            <div class="date-range">${checkinFormatted} - ${checkoutFormatted}</div>
            <div class="nights-count">(${nights} nights)</div>
        `;
    }
    
    updatePricingDisplay() {
        const accommodationPrice = document.getElementById('accommodation-price');
        const extrasPrice = document.getElementById('extras-price');
        const totalPrice = document.getElementById('total-price');
        
        // Check if we have valid dates and pricing should be shown
        const hasValidBooking = this.formData.dates.checkin && this.formData.dates.checkout;
        
        if (accommodationPrice) {
            if (hasValidBooking && this.formData.pricing.accommodation > 0) {
                accommodationPrice.textContent = `R ${this.formatPrice(this.formData.pricing.accommodation)}`;
                // Reset styling for successful pricing
                accommodationPrice.style.color = '';
                accommodationPrice.style.fontSize = '';
            } else {
                accommodationPrice.textContent = 'Select dates to see pricing';
                accommodationPrice.style.color = '';
                accommodationPrice.style.fontSize = '';
            }
        }
        
        if (extrasPrice) {
            if (hasValidBooking && this.formData.pricing.extras > 0) {
                extrasPrice.textContent = `R ${this.formatPrice(this.formData.pricing.extras)}`;
                extrasPrice.style.color = '';
            } else {
                extrasPrice.textContent = '-';
                extrasPrice.style.color = '';
            }
        }
        
        if (totalPrice) {
            if (hasValidBooking && this.formData.pricing.total > 0) {
                totalPrice.textContent = `R ${this.formatPrice(this.formData.pricing.total)}`;
                // Reset styling for successful pricing
                totalPrice.style.color = '';
                totalPrice.style.fontSize = '';
            } else {
                totalPrice.textContent = 'Select dates and guests';
                totalPrice.style.color = '';
                totalPrice.style.fontSize = '';
            }
        }
        
        // Update cost breakdown
        this.updateCostBreakdown();
        
        // Update season display in sidebar
        this.updateSeasonDisplay();
    }
    
    updateCostBreakdown() {
        const breakdownDetails = document.querySelector('.breakdown-details');
        if (!breakdownDetails) return;
        
        const hasValidBooking = this.formData.dates.checkin && this.formData.dates.checkout;
        const hasPricing = hasValidBooking && this.formData.pricing.total > 0;
        
        if (hasPricing && this.formData.pricing.breakdown && this.formData.pricing.breakdown.length > 0) {
            breakdownDetails.classList.add('has-pricing');
            
            // Update season information
            this.updateSeasonBreakdown();
            
            // Update nightly breakdown details
            this.updateNightlyBreakdown();
            
            // Calculate guest breakdown from nightly breakdown data
            const breakdown = this.calculateGuestBreakdown();
            
            // Debug logging
            console.log('Pricing breakdown data:', this.formData.pricing.breakdown);
            console.log('Calculated breakdown:', breakdown);
            
            // Update base accommodation (includes up to 4 guests)
            const baseAmount = document.getElementById('breakdown-base-amount');
            const baseDetail = document.getElementById('breakdown-base-detail');
            if (baseAmount && breakdown.baseTotal > 0) {
                const baseValue = isNaN(breakdown.baseTotal) ? 0 : breakdown.baseTotal;
                baseAmount.textContent = `R ${this.formatPrice(baseValue)}`;
                if (baseDetail && this.formData.pricing.nights) {
                    baseDetail.textContent = `Up to 4 guests, ${this.formData.pricing.nights} night${this.formData.pricing.nights !== 1 ? 's' : ''}`;
                }
            }
            
            // Update extra adults breakdown
            const extraAdultsAmount = document.getElementById('breakdown-extra-adults-amount');
            const extraAdultsDetail = document.getElementById('breakdown-extra-adults-detail');
            if (extraAdultsAmount && breakdown.extraAdults > 0) {
                const extraAdultValue = isNaN(breakdown.extraAdultTotal) ? 0 : breakdown.extraAdultTotal;
                extraAdultsAmount.textContent = `R ${this.formatPrice(extraAdultValue)}`;
                extraAdultsDetail.textContent = `${breakdown.extraAdults} additional adult${breakdown.extraAdults !== 1 ? 's' : ''} × ${this.formData.pricing.nights} nights`;
                document.getElementById('breakdown-extra-adults')?.style.setProperty('display', 'flex');
            } else {
                document.getElementById('breakdown-extra-adults')?.style.setProperty('display', 'none');
            }
            
            // Update children breakdown
            const childrenAmount = document.getElementById('breakdown-children-amount');
            const childrenDetail = document.getElementById('breakdown-children-detail');
            if (childrenAmount && this.formData.guests.children > 0) {
                const childrenValue = isNaN(breakdown.childrenTotal) ? 0 : breakdown.childrenTotal;
                if (childrenValue > 0) {
                    childrenAmount.textContent = `R ${this.formatPrice(childrenValue)}`;
                    childrenDetail.textContent = `${this.formData.guests.children} child${this.formData.guests.children !== 1 ? 'ren' : ''} × ${this.formData.pricing.nights} nights`;
                } else {
                    childrenAmount.textContent = 'Included';
                    childrenDetail.textContent = `${this.formData.guests.children} child${this.formData.guests.children !== 1 ? 'ren' : ''} (included in base rate)`;
                }
                document.getElementById('breakdown-children')?.style.setProperty('display', 'flex');
            } else {
                document.getElementById('breakdown-children')?.style.setProperty('display', 'none');
            }
            
            // Update babies breakdown (always free)
            const babiesAmount = document.getElementById('breakdown-babies-amount');
            const babiesDetail = document.getElementById('breakdown-babies-detail');
            if (babiesAmount && this.formData.guests.babies > 0) {
                babiesAmount.textContent = 'Free';
                babiesDetail.textContent = `${this.formData.guests.babies} bab${this.formData.guests.babies !== 1 ? 'ies' : 'y'} (0-4 years stay free)`;
                document.getElementById('breakdown-babies')?.style.setProperty('display', 'flex');
            } else {
                document.getElementById('breakdown-babies')?.style.setProperty('display', 'none');
            }
            
            // Update Christmas premium surcharge
            this.updateChristmasPremium(breakdown);
            
            // Update helicopter package breakdown - only show if explicitly selected
            const helicopterAmount = document.getElementById('breakdown-helicopter-amount');
            const helicopterDetail = document.getElementById('breakdown-helicopter-detail');
            const helicopterItem = document.getElementById('breakdown-helicopter');
            
            if (helicopterAmount && helicopterItem) {
                // Check if helicopter package is actually selected (not just pricing > 0)
                const helicopterSelected = this.formData.extras.helicopter_package && 
                                         this.formData.extras.helicopter_package !== null && 
                                         this.formData.extras.helicopter_package !== 'no';
                
                if (helicopterSelected && this.formData.pricing.extras > 0) {
                    helicopterAmount.textContent = `R ${this.formatPrice(this.formData.pricing.extras)}`;
                    if (helicopterDetail) {
                        helicopterDetail.textContent = `${this.formData.pricing.nights} nights package`;
                    }
                    helicopterItem.style.display = 'flex';
                } else {
                    helicopterItem.style.display = 'none';
                }
            }
            
            // Update subtotal
            const subtotalAmount = document.getElementById('breakdown-subtotal-amount');
            if (subtotalAmount) {
                subtotalAmount.textContent = `R ${this.formatPrice(this.formData.pricing.total)}`;
            }
        } else {
            breakdownDetails.classList.remove('has-pricing');
            // Hide enhanced sections when no pricing
            document.getElementById('breakdown-season-info')?.style.setProperty('display', 'none');
            document.getElementById('breakdown-nightly')?.style.setProperty('display', 'none');
        }
    }
    
    calculateGuestBreakdown() {
        // Calculate guest-specific costs from nightly breakdown data
        let baseTotal = 0;
        let extraAdultTotal = 0;
        let childrenTotal = 0;
        let christmasTotal = 0;
        
        if (this.formData.pricing.breakdown && this.formData.pricing.breakdown.length > 0) {
            this.formData.pricing.breakdown.forEach(night => {
                // Ensure we're working with numbers, not strings
                baseTotal += parseFloat(night.base_rate) || 0;
                extraAdultTotal += parseFloat(night.extra_adult_cost) || 0;
                childrenTotal += parseFloat(night.extra_child_cost) || 0;
                christmasTotal += parseFloat(night.surcharge) || 0;
            });
        }
        
        // Calculate number of extra adults beyond the 4 guests included in base rate
        const totalGuests = this.formData.guests.adults + this.formData.guests.children;
        const extraAdults = Math.max(0, this.formData.guests.adults - Math.min(4, totalGuests));
        
        return {
            baseTotal: baseTotal,
            extraAdultTotal: extraAdultTotal,
            childrenTotal: childrenTotal,
            christmasTotal: christmasTotal,
            extraAdults: extraAdults
        };
    }
    
    updateSeasonBreakdown() {
        const seasonInfo = document.getElementById('breakdown-season-info');
        const seasonBadge = document.getElementById('breakdown-season-badge');
        const seasonDates = document.getElementById('breakdown-season-dates');
        const seasonRate = document.getElementById('breakdown-season-rate');
        const seasonNights = document.getElementById('breakdown-season-nights');
        
        if (!seasonInfo || !this.formData.pricing.breakdown || this.formData.pricing.breakdown.length === 0) {
            return;
        }
        
        // Analyze the seasons in the booking
        const seasonAnalysis = this.analyzeSeasons();
        
        if (seasonAnalysis.seasonText) {
            seasonInfo.style.display = 'block';
            
            // Update season badge
            if (seasonBadge) {
                seasonBadge.textContent = seasonAnalysis.seasonText;
                seasonBadge.className = `season-badge ${seasonAnalysis.cssClass}`;
            }
            
            // Update season dates
            if (seasonDates && this.formData.dates.checkin && this.formData.dates.checkout) {
                const checkinDate = new Date(this.formData.dates.checkin);
                const checkoutDate = new Date(this.formData.dates.checkout);
                const checkinStr = checkinDate.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
                const checkoutStr = checkoutDate.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
                seasonDates.textContent = `${checkinStr} - ${checkoutStr}`;
            }
            
            // Calculate average base rate
            if (seasonRate && seasonNights) {
                const totalBaseRate = this.formData.pricing.breakdown.reduce((sum, night) => {
                    return sum + (parseFloat(night.base_rate) || 0);
                }, 0);
                const avgRate = totalBaseRate / this.formData.pricing.breakdown.length;
                
                seasonRate.textContent = `R ${this.formatPrice(avgRate)} per night (up to 4 guests)`;
                seasonNights.textContent = `${this.formData.pricing.nights} nights × R ${this.formatPrice(avgRate)}`;
            }
        } else {
            seasonInfo.style.display = 'none';
        }
    }
    
    updateNightlyBreakdown() {
        const nightlySection = document.getElementById('breakdown-nightly');
        const nightlyDetails = document.getElementById('breakdown-nightly-details');
        
        if (!nightlySection || !nightlyDetails || !this.formData.pricing.breakdown || this.formData.pricing.breakdown.length === 0) {
            return;
        }
        
        nightlySection.style.display = 'block';
        
        // Clear existing details
        nightlyDetails.innerHTML = '';
        
        // Add each night's breakdown
        this.formData.pricing.breakdown.forEach((night, index) => {
            const nightDate = new Date(night.date);
            const dateStr = nightDate.toLocaleDateString('en-GB', { 
                weekday: 'short', 
                day: 'numeric', 
                month: 'short' 
            });
            
            const nightlyItem = document.createElement('div');
            nightlyItem.className = 'nightly-item';
            
            const hasChristmasExtra = parseFloat(night.surcharge) > 0;
            const christmasIndicator = hasChristmasExtra ? '<span class="christmas-premium-indicator">+50k</span>' : '';
            
            nightlyItem.innerHTML = `
                <span class="nightly-date">${dateStr}</span>
                <span class="nightly-season ${night.season || 'standard'}">${this.formatSeasonLetter(night.season || 'standard')}</span>
                <span class="nightly-rate">R ${this.formatPrice(night.total)}${christmasIndicator}</span>
            `;
            
            nightlyDetails.appendChild(nightlyItem);
        });
    }
    
    updateChristmasPremium(breakdown) {
        const christmasItem = document.getElementById('breakdown-christmas-premium');
        const christmasAmount = document.getElementById('breakdown-christmas-amount');
        const christmasDetail = document.getElementById('breakdown-christmas-detail');
        
        if (breakdown.christmasTotal > 0) {
            if (christmasAmount) {
                christmasAmount.textContent = `R ${this.formatPrice(breakdown.christmasTotal)}`;
            }
            if (christmasDetail) {
                const christmasNights = this.formData.pricing.breakdown.filter(night => 
                    parseFloat(night.surcharge) > 0
                ).length;
                christmasDetail.textContent = `${christmasNights} night${christmasNights !== 1 ? 's' : ''} @ R 50,000/night`;
            }
            if (christmasItem) {
                christmasItem.style.display = 'flex';
                christmasItem.classList.add('premium-item');
            }
        } else {
            if (christmasItem) {
                christmasItem.style.display = 'none';
                christmasItem.classList.remove('premium-item');
            }
        }
    }
    
    formatPrice(amount) {
        // Ensure we have a valid number
        const numericAmount = parseFloat(amount);
        if (isNaN(numericAmount)) {
            console.warn('formatPrice received invalid amount:', amount);
            return '0';
        }
        return new Intl.NumberFormat('en-ZA').format(numericAmount);
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
        // Check if this is a test mode submission
        const isTestMode = document.querySelector('input[name="is_test_submission"]')?.value === '1' || 
                          this.formData.contact['full-name']?.includes('Test') ||
                          document.getElementById('full-name')?.value?.includes('Test');
        
        // Collect transfer options
        // Collect transfer option (radio button)
        const transferOption = document.querySelector('input[name="transfer"]:checked');
        const transfer = transferOption ? transferOption.value : 'no_transfer';
        
        // Collect experiences
        const experiences = [];
        document.querySelectorAll('input[name="experiences[]"]:checked').forEach(input => {
            experiences.push(input.value);
        });
        
        const submissionData = {
            action: 'submit_booking_request',
            nonce: window.leobo_booking_system?.nonce || '',
            // Flatten the nested formData structure to match PHP expectations
            checkin_date: this.formData.dates.checkin,
            checkout_date: this.formData.dates.checkout,
            adults: this.formData.guests.adults,
            children: this.formData.guests.children,
            babies: this.formData.guests.babies,
            accommodation: 'Observatory Villa',
            helicopter_package: this.formData.extras.helicopter_package,
            transfer: transfer, // Single selected transfer option
            experiences: experiences, // Array of selected experiences
            occasion: this.formData.extras.occasion || document.getElementById('occasion')?.value || '',
            flexible_dates: document.getElementById('flexible-dates')?.checked ? '1' : '0',
            full_name: this.formData.contact['full-name'] || document.getElementById('full-name')?.value,
            email: this.formData.contact['email'] || document.getElementById('email')?.value,
            contact_number: this.formData.contact['contact-number'] || document.getElementById('contact-number')?.value,
            home_address: this.formData.contact['home-address'] || document.getElementById('home-address')?.value || '',
            country: this.formData.contact['country'] || document.getElementById('country')?.value || '',
            how_heard: this.formData.contact['how-heard'] || document.getElementById('how-heard')?.value || '',
            special_requests: document.getElementById('special-requests')?.value || '',
            children_interests: document.getElementById('children-interests')?.value || '',
            calculated_total: this.formData.pricing.total || 0
        };
        
        // Add test submission flag if detected
        if (isTestMode) {
            submissionData.is_test_submission = '1';
        }
        
        // Add debugging
        console.log('=== EXTERNAL JS SUBMISSION DATA ===');
        console.log('Is Test Mode:', isTestMode);
        console.log('Transfer option:', transfer);
        console.log('Experiences:', experiences);
        console.log('Raw this.formData:', this.formData);
        console.log('Flattened submissionData:', submissionData);
        console.log('=== END EXTERNAL JS DEBUG ===');
        
        return submissionData;
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
}

// Global function to toggle nightly details
function toggleNightlyDetails() {
    const details = document.getElementById('breakdown-nightly-details');
    const toggle = document.querySelector('.nightly-toggle');
    
    if (details && toggle) {
        if (details.style.display === 'none' || !details.style.display) {
            details.style.display = 'block';
            toggle.classList.add('expanded');
            toggle.querySelector('.toggle-text').textContent = 'Hide Details';
        } else {
            details.style.display = 'none';
            toggle.classList.remove('expanded');
            toggle.querySelector('.toggle-text').textContent = 'Show Details';
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Add a small delay to ensure WordPress localization is complete
    setTimeout(() => {
        if (typeof leobo_booking_system === 'undefined') {
            console.error('leobo_booking_system not available after delay. Check script dependencies.');
        }
        new LeoboBookingForm();
    }, 100);
});
