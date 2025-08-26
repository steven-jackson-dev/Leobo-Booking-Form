<?php
/**
 * Leobo Custom Booking System - Content Configuration
 * All text content, labels, and messaging for the booking system
 * 
 * @package LeoboCustomBookingSystem
 * @version 1.0.0
 */

return array(
    // Step Navigation Labels
    'steps' => array(
        'dates' => 'Dates & Guests',
        'experiences' => 'Experiences', 
        'extras' => 'Extras',
        'guest_details' => 'Guest Details',
        'thank_you' => 'Thank You'
    ),
    
    // Step titles and subtitles (for template content)
    'step_titles' => array(
        '2' => array(
            'title' => 'Tailor your stay',
            'subtitle' => 'The following are optional extras available for your stay, at additional costs:'
        ),
        '3' => array(
            'title' => 'Guest information',
            'subtitle' => 'You\'ve selected your dates — now let\'s get to know you. A few essential details so we can tailor your stay.'
        )
    ),
    
    // Section headers
    'sections' => array(
        'dates' => 'Dates',
        'included' => 'What\'s included in the price?',
        'helicopter' => 'Helicopter Package',
        'experiences' => 'Add-on Experiences',
        'occasion' => 'Occasion?',
        'guest_info' => 'Your Information',
        'referral' => 'How did you hear about us?',
        'special_requests' => 'Special Requests',
        'children_interests' => 'Children\'s Interests (if applicable)'
    ),
    
    // Form fields and labels
    'fields' => array(
        'checkin_date' => 'Arrival',
        'checkout_date' => 'Departure',
        'select_date' => 'Select date',
        'please_select' => 'Please select',
        'country_residence' => 'Country of residence',
        'full_name' => 'Full name',
        'email' => 'Email address',
        'contact_number' => 'Contact number',
        'home_address' => 'Home address',
        'special_requests' => 'Anything else you\'d like us to know?',
        'children_interests' => 'What do they love? Stargazing, horses, camping, quad biking?'
    ),
    
    // Button text
    'buttons' => array(
        'next' => 'Next',
        'back' => 'Back',
        'submit' => 'Submit',
        'submit_test' => 'Submit Test Booking'
    ),
    
    // Success messages
    'success' => array(
        'title' => 'Thank you!',
        'message' => 'Your luxury safari inquiry has been submitted successfully. Our team will contact you within 24 hours to discuss your booking.',
        'booking_submitted' => 'Booking request submitted successfully!',
        'test_booking_submitted' => '🧪 Test booking submitted successfully! Check admin panel for new entry.'
    ),
    
    // Error messages
    'errors' => array(
        'required_field' => 'This field is required',
        'invalid_email' => 'Please enter a valid email address',
        'general_error' => 'An error occurred. Please try again.',
        'validation_error' => 'Please correct the errors below.',
        'pricing_calculation_error' => 'Unable to calculate pricing. Please try again.'
    ),
    
    // Test mode
    'test_mode' => array(
        'indicator' => '🧪 TEST MODE'
    ),
    
    // Loading States  
    'loading' => array(
        'calculating_price' => 'Calculating pricing...',
        'submitting_form' => 'Submitting your request...',
        'loading_calendar' => 'Loading calendar...',
        'processing' => 'Processing...'
    ),
    
    // Calendar Content
    'calendar' => array(
        'select_checkin' => 'Select check-in date',
        'select_checkout' => 'Select check-out date',
        'date_unavailable' => 'This date is not available',
        'minimum_stay_note' => 'Minimum stay: %d nights',
        'season_labels' => array(
            'standard' => 'Standard Season',
            'peak' => 'Peak Season',
            'christmas' => 'Christmas Season'
        )
    ),
    
    // Form Labels & Placeholders
    'form' => array(
        'required_indicator' => '*',
        'optional_label' => '(Optional)',
        'select_placeholder' => 'Please select...',
        'search_placeholder' => 'Search...',
        'no_results' => 'No results found',
        'clear_selection' => 'Clear selection'
    ),
    
    // Business information
    'business' => array(
        'name' => 'Leobo Private Reserve',
        'contact_email' => 'bookings@leobo.co.za',
        'phone' => '+27 (0) 83 700 2597'
    ),
    
    // Additional step content for future use
    'step_dates' => array(
        'title' => 'Select Your Dates',
        'subtitle' => 'Choose your preferred dates for your luxury safari experience',
        'checkin_label' => 'Check-in Date',
        'checkout_label' => 'Check-out Date',
        'guests_title' => 'Number of Guests',
        'adults_label' => 'Adults',
        'children_label' => 'Children (2-11 years)',
        'babies_label' => 'Babies (0-1 years)',
        'flexible_dates_label' => 'My dates are flexible',
        'next_button' => 'Continue to Experiences',
        'accommodation_note' => 'Observatory Villa accommodates up to 6 adults and 8 children',
        'validation' => array(
            'checkin_required' => 'Please select a check-in date',
            'checkout_required' => 'Please select a check-out date',
            'checkout_after_checkin' => 'Check-out date must be after check-in date',
            'minimum_nights' => 'Minimum stay is %d nights',
            'guests_required' => 'At least 1 adult is required',
            'max_adults_exceeded' => 'Maximum %d adults allowed',
            'max_children_exceeded' => 'Maximum %d children allowed'
        )
    ),
    
    // Step 2: Experiences
    'step_experiences' => array(
        'title' => 'Select Your Experiences',
        'subtitle' => 'Enhance your stay with our signature safari experiences',
        'select_all_label' => 'Select all experiences',
        'back_button' => 'Back to Dates',
        'next_button' => 'Continue to Extras',
        'experiences' => array(
            'big_five_game_drives' => array(
                'title' => 'Big Five Game Drives',
                'description' => 'Expert-guided drives to spot the Big Five in their natural habitat'
            ),
            'walking_safaris' => array(
                'title' => 'Walking Safaris',
                'description' => 'Get closer to nature with guided walking experiences'
            ),
            'bird_watching' => array(
                'title' => 'Bird Watching',
                'description' => 'Discover the incredible birdlife of the African bushveld'
            ),
            'star_gazing' => array(
                'title' => 'Star Gazing',
                'description' => 'Experience the magic of the African night sky'
            ),
            'cultural_experiences' => array(
                'title' => 'Cultural Experiences',
                'description' => 'Connect with local communities and traditions'
            ),
            'photography_tours' => array(
                'title' => 'Photography Tours',
                'description' => 'Capture stunning wildlife and landscape photography'
            ),
            'spa_treatments' => array(
                'title' => 'Spa Treatments',
                'description' => 'Relax and rejuvenate with our luxury spa services'
            ),
            'fishing' => array(
                'title' => 'Fishing',
                'description' => 'Enjoy peaceful fishing experiences in pristine waters'
            ),
            'mountaineering' => array(
                'title' => 'Mountaineering',
                'description' => 'Adventure climbing and hiking experiences'
            )
        )
    ),
    
    // Step 3: Extras
    'step_extras' => array(
        'title' => 'Additional Services',
        'subtitle' => 'Complete your luxury safari experience',
        'transfer_title' => 'Transfer Options',
        'transfer_subtitle' => 'How would you like to arrive at Leobo?',
        'helicopter_title' => 'Helicopter Package',
        'helicopter_subtitle' => 'Upgrade your arrival with a scenic helicopter transfer',
        'occasion_title' => 'Special Occasion',
        'occasion_subtitle' => 'Are you celebrating something special?',
        'back_button' => 'Back to Experiences',
        'next_button' => 'Continue to Guest Details',
        'transfers' => array(
            'road_transfer' => 'Road Transfer',
            'private_helicopter' => 'Private Helicopter',
            'charter_flight' => 'Charter Flight',
            'self_drive' => 'Self Drive'
        ),
        'helicopter_packages' => array(
            'scenic_flight' => 'Scenic Flight Package',
            'exclusive_charter' => 'Exclusive Charter',
            'photography_flight' => 'Photography Flight',
            'sunset_flight' => 'Sunset Flight'
        ),
        'occasions' => array(
            'honeymoon' => 'Honeymoon',
            'anniversary' => 'Anniversary',
            'birthday' => 'Birthday',
            'family_reunion' => 'Family Reunion',
            'corporate_retreat' => 'Corporate Retreat',
            'wellness_retreat' => 'Wellness Retreat'
        ),
        'helicopter_toggle' => array(
            'not_interested' => 'Not interested',
            'interested' => 'I\'m interested'
        )
    ),
    
    // Step 4: Guest Details
    'step_guest_details' => array(
        'title' => 'Guest Information',
        'subtitle' => 'Tell us about yourself so we can personalize your experience',
        'contact_title' => 'Contact Information',
        'preferences_title' => 'Preferences & Requests',
        'fields' => array(
            'full_name' => array(
                'label' => 'Full Name',
                'placeholder' => 'Enter your full name',
                'required' => true
            ),
            'email' => array(
                'label' => 'Email Address',
                'placeholder' => 'Enter your email address',
                'required' => true
            ),
            'contact_number' => array(
                'label' => 'Contact Number',
                'placeholder' => 'Enter your phone number',
                'required' => false
            ),
            'home_address' => array(
                'label' => 'Home Address',
                'placeholder' => 'Enter your home address',
                'required' => false
            ),
            'country' => array(
                'label' => 'Country',
                'placeholder' => 'Select your country',
                'required' => false
            ),
            'how_heard' => array(
                'label' => 'How did you hear about us?',
                'placeholder' => 'Select an option',
                'required' => false,
                'options' => array(
                    'website' => 'Website',
                    'social_media' => 'Social Media',
                    'referral' => 'Referral',
                    'travel_agent' => 'Travel Agent',
                    'repeat_guest' => 'Repeat Guest',
                    'other' => 'Other'
                )
            ),
            'special_requests' => array(
                'label' => 'Special Requests',
                'placeholder' => 'Any dietary requirements, accessibility needs, or special requests?',
                'required' => false
            ),
            'children_interests' => array(
                'label' => 'Children\'s Interests',
                'placeholder' => 'Tell us about your children\'s interests and ages for age-appropriate activities',
                'required' => false,
                'show_when' => 'children > 0'
            )
        ),
        'back_button' => 'Back to Extras',
        'submit_button' => 'Submit Booking Request',
        'validation' => array(
            'full_name_required' => 'Please enter your full name',
            'email_required' => 'Please enter your email address',
            'email_invalid' => 'Please enter a valid email address'
        )
    ),
    
    // Step 5: Thank You
    'step_thank_you' => array(
        'title' => 'Thank you!',
        'message' => 'Your luxury safari inquiry has been submitted successfully.<br/>Our team will contact you within 24 hours to discuss your booking.',
        'next_steps' => array(
            'title' => 'What happens next?',
            'steps' => array(
                '<br/>Our team will review your inquiry within 24 hours',
                'We\'ll contact you to discuss availability and customize your experience',
                'Once confirmed, we\'ll send detailed pre-arrival information',
                'Get ready for an unforgettable luxury safari experience!'
            )
        )
    ),
    
    // Sidebar Content
    'sidebar' => array(
        'guests_title' => 'GUESTS',
        'dates_title' => 'DATES',
        'accommodation_title' => 'ACCOMMODATION',
        'experiences_title' => 'EXPERIENCES',
        'cost_breakdown_title' => 'COST BREAKDOWN',
        'total_label' => 'TOTAL',
        'per_night_label' => 'per night',
        'nights_label' => 'nights',
        'guests_summary' => array(
            'adults' => 'Adults',
            'children' => 'Children', 
            'babies' => 'Babies'
        )
    ),
    
    // Loading States
    'loading' => array(
        'calculating_price' => 'Calculating pricing...',
        'submitting_form' => 'Submitting your request...',
        'loading_calendar' => 'Loading calendar...',
        'processing' => 'Processing...'
    ),
    
    // Calendar Content
    'calendar' => array(
        'select_checkin' => 'Select check-in date',
        'select_checkout' => 'Select check-out date',
        'date_unavailable' => 'This date is not available',
        'minimum_stay_note' => 'Minimum stay: %d nights',
        'season_labels' => array(
            'standard' => 'Standard Season',
            'peak' => 'Peak Season',
            'christmas' => 'Christmas Season'
        )
    ),
    
    // Email Templates (references)
    'emails' => array(
        'user_confirmation' => array(
            'subject' => 'Booking Request Received - Leobo Private Reserve',
            'greeting' => 'Thank you, %s!',
            'message' => 'We have received your luxury safari inquiry and will contact you within 24 hours.',
            'booking_reference' => 'Your booking reference number is: #%s'
        ),
        'admin_notification' => array(
            'subject' => 'New Booking Request #%s - Leobo Private Reserve',
            'greeting' => 'New Booking Alert',
            'message' => 'A new booking inquiry has been submitted and requires your attention.'
        )
    ),
    
    // Form Labels & Placeholders
    'form' => array(
        'required_indicator' => '*',
        'optional_label' => '(Optional)',
        'select_placeholder' => 'Please select...',
        'search_placeholder' => 'Search...',
        'no_results' => 'No results found',
        'clear_selection' => 'Clear selection'
    ),
    
    // Accessibility
    'accessibility' => array(
        'step_progress' => 'Step %d of %d: %s',
        'form_error' => 'Form has errors. Please review and correct.',
        'loading_content' => 'Loading content, please wait',
        'calendar_navigation' => 'Use arrow keys to navigate calendar',
        'required_field' => 'This field is required'
    )
);
?>
    
    // Step 1: Dates & Guests
    'step_dates' => array(
        'title' => 'Select Your Dates',
        'subtitle' => 'Choose your preferred dates for your luxury safari experience',
        'checkin_label' => 'Check-in Date',
        'checkout_label' => 'Check-out Date',
        'guests_title' => 'Number of Guests',
        'adults_label' => 'Adults',
        'children_label' => 'Children (2-11 years)',
        'babies_label' => 'Babies (0-1 years)',
        'flexible_dates_label' => 'My dates are flexible',
        'next_button' => 'Continue to Experiences',
        'accommodation_note' => 'Observatory Villa accommodates up to 6 adults and 8 children',
        'validation' => array(
            'checkin_required' => 'Please select a check-in date',
            'checkout_required' => 'Please select a check-out date',
            'checkout_after_checkin' => 'Check-out date must be after check-in date',
            'minimum_nights' => 'Minimum stay is %d nights',
            'guests_required' => 'At least 1 adult is required',
            'max_adults_exceeded' => 'Maximum %d adults allowed',
            'max_children_exceeded' => 'Maximum %d children allowed'
        )
    ),
    
    // Step 2: Experiences
    'step_experiences' => array(
        'title' => 'Select Your Experiences',
        'subtitle' => 'Enhance your stay with our signature safari experiences',
        'select_all_label' => 'Select all experiences',
        'back_button' => 'Back to Dates',
        'next_button' => 'Continue to Extras',
        'experiences' => array(
            'big_five_game_drives' => array(
                'title' => 'Big Five Game Drives',
                'description' => 'Expert-guided drives to spot the Big Five in their natural habitat'
            ),
            'walking_safaris' => array(
                'title' => 'Walking Safaris',
                'description' => 'Get closer to nature with guided walking experiences'
            ),
            'bird_watching' => array(
                'title' => 'Bird Watching',
                'description' => 'Discover the incredible birdlife of the African bushveld'
            ),
            'star_gazing' => array(
                'title' => 'Star Gazing',
                'description' => 'Experience the magic of the African night sky'
            ),
            'cultural_experiences' => array(
                'title' => 'Cultural Experiences',
                'description' => 'Connect with local communities and traditions'
            ),
            'photography_tours' => array(
                'title' => 'Photography Tours',
                'description' => 'Capture stunning wildlife and landscape photography'
            ),
            'spa_treatments' => array(
                'title' => 'Spa Treatments',
                'description' => 'Relax and rejuvenate with our luxury spa services'
            ),
            'fishing' => array(
                'title' => 'Fishing',
                'description' => 'Enjoy peaceful fishing experiences in pristine waters'
            ),
            'mountaineering' => array(
                'title' => 'Mountaineering',
                'description' => 'Adventure climbing and hiking experiences'
            )
        )
    ),
    
    // Step 3: Extras
    'step_extras' => array(
        'title' => 'Additional Services',
        'subtitle' => 'Complete your luxury safari experience',
        'transfer_title' => 'Transfer Options',
        'transfer_subtitle' => 'How would you like to arrive at Leobo?',
        'helicopter_title' => 'Helicopter Package',
        'helicopter_subtitle' => 'Upgrade your arrival with a scenic helicopter transfer',
        'occasion_title' => 'Special Occasion',
        'occasion_subtitle' => 'Are you celebrating something special?',
        'back_button' => 'Back to Experiences',
        'next_button' => 'Continue to Guest Details',
        'transfers' => array(
            'road_transfer' => 'Road Transfer',
            'private_helicopter' => 'Private Helicopter',
            'charter_flight' => 'Charter Flight',
            'self_drive' => 'Self Drive'
        ),
        'helicopter_packages' => array(
            'scenic_flight' => 'Scenic Flight Package',
            'exclusive_charter' => 'Exclusive Charter',
            'photography_flight' => 'Photography Flight',
            'sunset_flight' => 'Sunset Flight'
        ),
        'occasions' => array(
            'honeymoon' => 'Honeymoon',
            'anniversary' => 'Anniversary',
            'birthday' => 'Birthday',
            'family_reunion' => 'Family Reunion',
            'corporate_retreat' => 'Corporate Retreat',
            'wellness_retreat' => 'Wellness Retreat'
        ),
        'helicopter_toggle' => array(
            'not_interested' => 'Not interested',
            'interested' => 'I\'m interested'
        )
    ),
    
    // Step 4: Guest Details
    'step_guest_details' => array(
        'title' => 'Guest Information',
        'subtitle' => 'Tell us about yourself so we can personalize your experience',
        'contact_title' => 'Contact Information',
        'preferences_title' => 'Preferences & Requests',
        'fields' => array(
            'full_name' => array(
                'label' => 'Full Name',
                'placeholder' => 'Enter your full name',
                'required' => true
            ),
            'email' => array(
                'label' => 'Email Address',
                'placeholder' => 'Enter your email address',
                'required' => true
            ),
            'contact_number' => array(
                'label' => 'Contact Number',
                'placeholder' => 'Enter your phone number',
                'required' => false
            ),
            'home_address' => array(
                'label' => 'Home Address',
                'placeholder' => 'Enter your home address',
                'required' => false
            ),
            'country' => array(
                'label' => 'Country',
                'placeholder' => 'Select your country',
                'required' => false
            ),
            'how_heard' => array(
                'label' => 'How did you hear about us?',
                'placeholder' => 'Select an option',
                'required' => false,
                'options' => array(
                    'website' => 'Website',
                    'social_media' => 'Social Media',
                    'referral' => 'Referral',
                    'travel_agent' => 'Travel Agent',
                    'repeat_guest' => 'Repeat Guest',
                    'other' => 'Other'
                )
            ),
            'special_requests' => array(
                'label' => 'Special Requests',
                'placeholder' => 'Any dietary requirements, accessibility needs, or special requests?',
                'required' => false
            ),
            'children_interests' => array(
                'label' => 'Children\'s Interests',
                'placeholder' => 'Tell us about your children\'s interests and ages for age-appropriate activities',
                'required' => false,
                'show_when' => 'children > 0'
            )
        ),
        'back_button' => 'Back to Extras',
        'submit_button' => 'Submit Booking Request',
        'validation' => array(
            'full_name_required' => 'Please enter your full name',
            'email_required' => 'Please enter your email address',
            'email_invalid' => 'Please enter a valid email address'
        )
    ),
    
    // Step 5: Thank You
    'step_thank_you' => array(
        'title' => 'Thank you!',
        'message' => 'Your luxury safari inquiry has been submitted successfully. Our team will contact you within 24 hours to discuss your booking.',
        'next_steps' => array(
            'title' => 'What happens next?',
            'steps' => array(
                'Our team will review your inquiry within 24 hours',
                'We\'ll contact you to discuss availability and customize your experience',
                'Once confirmed, we\'ll send detailed pre-arrival information',
                'Get ready for an unforgettable luxury safari experience!'
            )
        )
    ),
    
    // Sidebar Content
    'sidebar' => array(
        'guests_title' => 'GUESTS',
        'dates_title' => 'DATES',
        'accommodation_title' => 'ACCOMMODATION',
        'experiences_title' => 'EXPERIENCES',
        'cost_breakdown_title' => 'COST BREAKDOWN',
        'total_label' => 'TOTAL',
        'per_night_label' => 'per night',
        'nights_label' => 'nights',
        'guests_summary' => array(
            'adults' => 'Adults',
            'children' => 'Children', 
            'babies' => 'Babies'
        )
    ),
    
    // Error Messages
    'errors' => array(
        'general_error' => 'An error occurred. Please try again.',
        'network_error' => 'Network error. Please check your connection and try again.',
        'validation_error' => 'Please correct the errors below.',
        'submission_error' => 'There was an error submitting your request. Please try again.',
        'calendar_load_error' => 'Unable to load calendar. Please refresh the page.',
        'pricing_calculation_error' => 'Unable to calculate pricing. Please try again.'
    ),
    
    // Success Messages
    'success' => array(
        'booking_submitted' => 'Booking request submitted successfully!',
        'test_booking_submitted' => '🧪 Test booking submitted successfully! Check admin panel for new entry.',
        'form_auto_saved' => 'Form data saved automatically',
        'pricing_updated' => 'Pricing updated successfully'
    ),
    
    // Loading States
    'loading' => array(
        'calculating_price' => 'Calculating pricing...',
        'submitting_form' => 'Submitting your request...',
        'loading_calendar' => 'Loading calendar...',
        'processing' => 'Processing...'
    ),
    
    // Test Mode Content
    'test_mode' => array(
        'indicator' => '🧪 TEST MODE',
        'admin_text' => 'Admin test form',
        'user_text' => 'Form pre-filled with test data',
        'buttons' => array(
            'fill_data' => 'Fill Test Data',
            'randomize' => 'Randomize Data',
            'family_scenario' => 'Family Scenario',
            'couple_scenario' => 'Couple Scenario',
            'group_scenario' => 'Group Scenario'
        ),
        'notifications' => array(
            'data_filled' => '✅ Test data filled successfully!',
            'data_randomized' => '🎲 Test data randomized!',
            'family_loaded' => '👨‍👩‍👧‍👦 Family scenario loaded!',
            'couple_loaded' => '💑 Couple scenario loaded!',
            'group_loaded' => '👥 Group scenario loaded!'
        )
    ),
    
    // Calendar Content
    'calendar' => array(
        'select_checkin' => 'Select check-in date',
        'select_checkout' => 'Select check-out date',
        'date_unavailable' => 'This date is not available',
        'minimum_stay_note' => 'Minimum stay: %d nights',
        'season_labels' => array(
            'standard' => 'Standard Season',
            'peak' => 'Peak Season',
            'christmas' => 'Christmas Season'
        )
    ),
    
    // Email Templates (references)
    'emails' => array(
        'user_confirmation' => array(
            'subject' => 'Booking Request Received - Leobo Private Reserve',
            'greeting' => 'Thank you, %s!',
            'message' => 'We have received your luxury safari inquiry and will contact you within 24 hours.',
            'booking_reference' => 'Your booking reference number is: #%s'
        ),
        'admin_notification' => array(
            'subject' => 'New Booking Request #%s - Leobo Private Reserve',
            'greeting' => 'New Booking Alert',
            'message' => 'A new booking inquiry has been submitted and requires your attention.'
        )
    ),
    
    // Form Labels & Placeholders
    'form' => array(
        'required_indicator' => '*',
        'optional_label' => '(Optional)',
        'select_placeholder' => 'Please select...',
        'search_placeholder' => 'Search...',
        'no_results' => 'No results found',
        'clear_selection' => 'Clear selection'
    ),
    
    // Accessibility
    'accessibility' => array(
        'step_progress' => 'Step %d of %d: %s',
        'form_error' => 'Form has errors. Please review and correct.',
        'loading_content' => 'Loading content, please wait',
        'calendar_navigation' => 'Use arrow keys to navigate calendar',
        'required_field' => 'This field is required'
    ),
    
    // Business Information
    'business' => array(
        'name' => 'Leobo Private Reserve',
        'tagline' => 'Luxury Safari Experience',
        'contact_time' => '24 hours',
        'accommodation_name' => 'Observatory Villa',
        'max_adults' => 6,
        'max_children' => 8,
        'max_babies' => 4,
        'minimum_nights' => 2
    )
);
