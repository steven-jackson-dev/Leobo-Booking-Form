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
        this.guestRules = this.config.guest_rules || {
            max_adults: 6,
            max_children: 4,
            children_free_age: 4
        };
        
        this.formData = {
            guests: {
                adults: 1, // Default to 1 (minimum required)
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
                transfer: [],
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
        this.updateSidebarSummary();
        this.setupDatePickerModal();
        this.updateProgressIndicator();
        this.updateCostBreakdown(); // Initialize cost breakdown
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
    
    // Date Selection Methods
    showDatePicker() {
        const modal = document.getElementById('date-picker-modal');
        if (modal) {
            modal.style.display = 'flex';
            this.initializeHotelDatepicker();
        }
    }
    
    hideDatePicker() {
        const modal = document.getElementById('date-picker-modal');
        if (modal) {
            modal.style.display = 'none';
        }
    }
    
    initializeHotelDatepicker() {
        console.log('Initializing Hotel Datepicker...');
        console.log('Available globals:', {
            HotelDatepicker: typeof window.HotelDatepicker,
            hotelDatepicker: typeof window.hotelDatepicker,
            fecha: typeof window.fecha,
            windowKeys: Object.keys(window).filter(k => k.toLowerCase().includes('hotel'))
        });
        
        const dateInput = document.getElementById('date_range_input');
        if (!dateInput || dateInput._hotelDatepicker) return;
        
        // Get blocked dates from server
        const blockedDates = window.leobo_booking_system?.blocked_dates || [];
        
        // Convert blocked dates to Date objects for Hotel Datepicker
        const disabledDates = blockedDates.map(dateStr => new Date(dateStr));
        
        // Check if Hotel Datepicker is available
        if (typeof window.HotelDatepicker === 'undefined' && typeof window.hotelDatepicker === 'undefined') {
            console.error('Hotel Datepicker library not loaded, using fallback');
            // Use fallback to basic date inputs
            this.setupBasicDateInputs(dateInput);
            return;
        }
        
        try {
            // Initialize Hotel Datepicker (try both possible constructors)
            const DatepickerConstructor = window.HotelDatepicker || window.hotelDatepicker;
            this.hotelDatepicker = new DatepickerConstructor(dateInput, {
            format: 'YYYY-MM-DD',
            separator: ' - ',
            startOfWeek: 'monday',
            startDate: new Date(),
            endDate: false,
            minNights: this.config.acf_config?.minimum_nights_standard || 3,
            maxNights: 0,
            selectForward: false,
            disabledDates: disabledDates,
            enableCheckout: false,
            autoClose: false,
            showTopbar: true,
            topbarPosition: 'top',
            moveBothMonths: true,
            inline: false,
            clearButton: true,
            submitButton: false,
            animationSpeed: '0.3s',
            hoveringTooltip: function(nights, startTime, hoverTime) {
                return nights + (nights === 1 ? ' night' : ' nights');
            },
            i18n: {
                selected: 'Your stay:',
                night: 'Night',
                nights: 'Nights',
                button: 'Close',
                clearButton: 'Clear',
                'checkin-disabled': 'Check-in disabled',
                'checkout-disabled': 'Check-out disabled',
                'info-more': 'Please select a date range of at least ' + (this.config.acf_config?.minimum_nights_standard || 3) + ' nights',
                'info-default': 'Please select check-in and check-out dates',
                'aria-application': 'Calendar',
                'aria-close-button': 'Close the datepicker',
                'aria-clear-button': 'Clear the selected dates'
            },
            onSelectRange: (startDate, endDate) => {
                this.selectDateRange(startDate, endDate);
            },
            onOpenDatepicker: () => {
                // Optional: Add any open event handling
            }
        });
        
        // Mark as initialized
        dateInput._hotelDatepicker = this.hotelDatepicker;
        console.log('Hotel Datepicker initialized successfully');
        
        } catch (error) {
            console.error('Failed to initialize Hotel Datepicker:', error);
            this.setupBasicDateInputs(dateInput);
        }
    }
    
    setupBasicDateInputs(dateInput) {
        console.log('Setting up basic date inputs fallback');
        // Simple fallback - convert to two separate date inputs
        dateInput.type = 'date';
        dateInput.placeholder = 'Select check-in date';
        
        // Add change handler for basic functionality
        dateInput.addEventListener('change', (e) => {
            const selectedDate = new Date(e.target.value);
            if (selectedDate) {
                // Set a default checkout date (add minimum nights)
                const minNights = this.config.acf_config?.minimum_nights_standard || 3;
                const checkoutDate = new Date(selectedDate);
                checkoutDate.setDate(checkoutDate.getDate() + minNights);
                
                this.selectDateRange(selectedDate, checkoutDate);
            }
        });
    }
    
    selectDateRange(checkin, checkout) {
        const nights = this.calculateNights(checkin, checkout);
        const minNights = this.config.acf_config?.minimum_nights_standard || 3;
        
        // Validate minimum nights
        if (nights < minNights) {
            alert(`Minimum stay is ${minNights} nights. Please select a longer period.`);
            return;
        }
        
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
        if (!date) return '';
        if (typeof date === 'string') return date; // Already formatted
        if (!(date instanceof Date)) {
            date = new Date(date);
        }
        if (isNaN(date.getTime())) return '';
        return date.toISOString().split('T')[0];
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
                <div class="nights-count">(${nights} nights)</div>
                <div class="season-info" id="season-info" style="display: none;">
                    <span class="season-label" id="season-display"></span>
                </div>
            `;
            
            // Update season information if pricing data is available
            this.updateSeasonDisplay();
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
    
    // Pricing Methods
    updatePricing() {
        // Only calculate if we have dates and guests
        if (!this.formData.dates.checkin || !this.formData.dates.checkout) {
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
            this.showPricingError('Failed to calculate pricing. Please try again.');
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
            
            // Calculate guest breakdown from nightly breakdown data
            const breakdown = this.calculateGuestBreakdown();
            
            // Debug logging
            console.log('Pricing breakdown data:', this.formData.pricing.breakdown);
            console.log('Calculated breakdown:', breakdown);
            
            // Update base accommodation (includes 2 adults)
            const baseAmount = document.getElementById('breakdown-base-amount');
            const baseDetail = document.getElementById('breakdown-base-detail');
            if (baseAmount && breakdown.baseTotal > 0) {
                // Ensure we have a valid number before formatting
                const baseValue = isNaN(breakdown.baseTotal) ? 0 : breakdown.baseTotal;
                baseAmount.textContent = `R ${this.formatPrice(baseValue)}`;
                if (baseDetail && this.formData.pricing.nights) {
                    baseDetail.textContent = `2 adults, ${this.formData.pricing.nights} night${this.formData.pricing.nights !== 1 ? 's' : ''}`;
                }
            }
            
            // Update extra adults breakdown
            const extraAdultsAmount = document.getElementById('breakdown-extra-adults-amount');
            const extraAdultsDetail = document.getElementById('breakdown-extra-adults-detail');
            if (extraAdultsAmount && breakdown.extraAdults > 0) {
                const extraAdultValue = isNaN(breakdown.extraAdultTotal) ? 0 : breakdown.extraAdultTotal;
                extraAdultsAmount.textContent = `R ${this.formatPrice(extraAdultValue)}`;
                extraAdultsDetail.textContent = `${breakdown.extraAdults} additional adult${breakdown.extraAdults !== 1 ? 's' : ''}`;
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
                } else {
                    childrenAmount.textContent = 'Free';
                }
                childrenDetail.textContent = `${this.formData.guests.children} child${this.formData.guests.children !== 1 ? 'ren' : ''}`;
                document.getElementById('breakdown-children')?.style.setProperty('display', 'flex');
            } else {
                document.getElementById('breakdown-children')?.style.setProperty('display', 'none');
            }
            
            // Update babies breakdown (always free)
            const babiesAmount = document.getElementById('breakdown-babies-amount');
            const babiesDetail = document.getElementById('breakdown-babies-detail');
            if (babiesAmount && this.formData.guests.babies > 0) {
                babiesAmount.textContent = 'Free';
                babiesDetail.textContent = `${this.formData.guests.babies} bab${this.formData.guests.babies !== 1 ? 'ies' : 'y'}`;
                document.getElementById('breakdown-babies')?.style.setProperty('display', 'flex');
            } else {
                document.getElementById('breakdown-babies')?.style.setProperty('display', 'none');
            }
            
            // Update helicopter package breakdown
            const helicopterAmount = document.getElementById('breakdown-helicopter-amount');
            if (helicopterAmount) {
                if (this.formData.pricing.extras > 0) {
                    helicopterAmount.textContent = `R ${this.formatPrice(this.formData.pricing.extras)}`;
                    document.getElementById('breakdown-helicopter')?.style.setProperty('display', 'flex');
                } else {
                    document.getElementById('breakdown-helicopter')?.style.setProperty('display', 'none');
                }
            }
            
            // Update transfer breakdown (if available in future)
            const transferAmount = document.getElementById('breakdown-transfer-amount');
            if (transferAmount) {
                // For now, transfers are not separately tracked in pricing data
                document.getElementById('breakdown-transfer')?.style.setProperty('display', 'none');
            }
            
            // Update experiences breakdown (if available in future)
            const experiencesAmount = document.getElementById('breakdown-experiences-amount');
            if (experiencesAmount) {
                // For now, experiences are not separately tracked in pricing data
                document.getElementById('breakdown-experiences')?.style.setProperty('display', 'none');
            }
            
            // Update subtotal
            const subtotalAmount = document.getElementById('breakdown-subtotal-amount');
            if (subtotalAmount) {
                subtotalAmount.textContent = `R ${this.formatPrice(this.formData.pricing.total)}`;
            }
        } else {
            breakdownDetails.classList.remove('has-pricing');
        }
    }
    
    calculateGuestBreakdown() {
        // Calculate guest-specific costs from nightly breakdown data
        let baseTotal = 0;
        let extraAdultTotal = 0;
        let childrenTotal = 0;
        
        if (this.formData.pricing.breakdown && this.formData.pricing.breakdown.length > 0) {
            this.formData.pricing.breakdown.forEach(night => {
                // Ensure we're working with numbers, not strings
                baseTotal += parseFloat(night.base_rate) || 0;
                extraAdultTotal += parseFloat(night.extra_adult_cost) || 0;
                childrenTotal += parseFloat(night.extra_child_cost) || 0;
            });
        }
        
        // Calculate extra adults count (assuming base rate includes 2 adults)
        const includedAdults = 2;
        const extraAdults = Math.max(0, this.formData.guests.adults - includedAdults);
        
        return {
            baseTotal: Math.round(baseTotal * 100) / 100, // Ensure proper rounding
            extraAdults: extraAdults,
            extraAdultTotal: Math.round(extraAdultTotal * 100) / 100,
            childrenTotal: Math.round(childrenTotal * 100) / 100
        };
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
