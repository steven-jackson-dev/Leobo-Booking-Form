<?php
/**
 * Leobo Custom Booking System - Pricing Calculator
 * Handles all pricing calculations and business logic based on ACF structure
 * 
 * @package LeoboCustomBookingSystem
 * @version 2.0.0
 */

class LeoboBookingPricing {
    
    private $seasons_data;
    private $packages_data;
    private $guest_rules;
    private $minimum_nights_rules;
    
    public function __construct() {
        $this->load_data();
    }
    
    private function load_data() {
        $this->seasons_data = $this->get_seasons_data();
        $this->packages_data = $this->get_packages_data();
        $this->guest_rules = $this->get_guest_rules();
        $this->minimum_nights_rules = $this->get_minimum_nights_rules();
    }
    
    /**
     * Calculate complete pricing for a booking based on ACF structure
     */
    public function calculate_pricing($checkin, $checkout, $adults, $children, $accommodation_id = null, $helicopter_package = null) {
        try {
            // Parse dates
            $checkin_date = DateTime::createFromFormat('Y-m-d', $checkin);
            $checkout_date = DateTime::createFromFormat('Y-m-d', $checkout);
            
            if (!$checkin_date || !$checkout_date) {
                return array('error' => 'Invalid date format');
            }
            
            // Calculate nights
            $nights = $checkin_date->diff($checkout_date)->days;
            
            if ($nights <= 0) {
                return array('error' => 'Invalid date range');
            }
            
            // Validate guest counts
            $validation = $this->validate_guest_count($adults, $children);
            if (isset($validation['error'])) {
                return $validation;
            }
            
            // Check minimum nights requirement
            $min_nights = $this->get_minimum_nights_for_date($checkin_date);
            if ($nights < $min_nights) {
                return array('error' => "Minimum {$min_nights} nights required for this period");
            }
            
            // Calculate accommodation costs
            $accommodation_total = 0;
            $nightly_breakdown = array();
            
            $current_date = clone $checkin_date;
            for ($i = 0; $i < $nights; $i++) {
                $nightly_cost = $this->calculate_nightly_rate($current_date, $adults, $children);
                $accommodation_total += $nightly_cost['total'];
                
                $nightly_breakdown[] = array(
                    'date' => $current_date->format('Y-m-d'),
                    'season' => $nightly_cost['season'],
                    'base_rate' => $nightly_cost['base_rate'],
                    'extra_adult_cost' => $nightly_cost['extra_adult_cost'],
                    'extra_child_cost' => $nightly_cost['extra_child_cost'],
                    'surcharge' => $nightly_cost['surcharge'],
                    'total' => $nightly_cost['total'],
                    'nightly_rate' => $nightly_cost['total'] // For JavaScript compatibility
                );
                
                $current_date->add(new DateInterval('P1D'));
            }
            
            // Calculate helicopter package cost
            $helicopter_total = 0;
            $package_breakdown = array(); // Renamed for JavaScript compatibility
            
            if ($helicopter_package) {
                $package_cost = $this->calculate_helicopter_package($nights, $helicopter_package);
                if (isset($package_cost['error'])) {
                    return $package_cost;
                }
                $helicopter_total = $package_cost['total'];
                
                // Convert to expected JavaScript structure
                $package_breakdown[] = array(
                    'id' => 'helicopter-package',
                    'name' => $package_cost['breakdown']['package_name'],
                    'cost' => $package_cost['total'],
                    'type' => 'flat_rate'
                );
            }
            
            $grand_total = $accommodation_total + $helicopter_total;
            
            return array(
                'success' => true,
                'accommodation_total' => $accommodation_total,
                'helicopter_total' => $helicopter_total,
                'package_total' => $helicopter_total, // Alias for JavaScript compatibility
                'grand_total' => $grand_total,
                'nights' => $nights,
                'adults' => $adults,
                'children' => $children,
                'minimum_nights' => $min_nights,
                'nightly_breakdown' => $nightly_breakdown,
                'package_breakdown' => $package_breakdown, // JavaScript expects this name
                'helicopter_breakdown' => $helicopter_breakdown ?? array(),
                'currency' => 'R'
            );
            
        } catch (Exception $e) {
            return array('error' => 'Calculation error: ' . $e->getMessage());
        }
    }
    
    /**
     * Calculate nightly rate based on season and guest count
     */
    private function calculate_nightly_rate($date, $adults, $children) {
        $season = $this->detect_season($date);
        $season_data = $this->seasons_data[$season];
        
        // Base rate (typically includes 2 adults) - provide fallback if ACF not configured
        $base_rate = $season_data['base_rate'] ?: 12500;
        
        // Calculate extra adults (assuming base rate includes 2 adults)
        $included_adults = 2;
        $extra_adults = max(0, $adults - $included_adults);
        $extra_adult_cost = $extra_adults * ($season_data['extra_adult'] ?: 2500);
        
        // Calculate paying children (5-11 years old)
        // Assuming all children passed are in the paying age range
        $extra_child_cost = $children * ($season_data['extra_child'] ?: 1250);
        
        // Christmas surcharge
        $surcharge = 0;
        if ($season === 'christmas' && isset($season_data['surcharge'])) {
            $surcharge = $season_data['surcharge'] ?: 0;
        }
        
        $total = $base_rate + $extra_adult_cost + $extra_child_cost + $surcharge;
        
        return array(
            'season' => $season,
            'base_rate' => $base_rate,
            'extra_adult_cost' => $extra_adult_cost,
            'extra_child_cost' => $extra_child_cost,
            'surcharge' => $surcharge,
            'total' => $total
        );
    }
    
    /**
     * Detect season for a specific date
     */
    private function detect_season($date) {
        $date_string = $date->format('d/m/Y');
        
        // Check Christmas dates first (highest priority)
        foreach ($this->seasons_data['christmas']['dates'] as $range) {
            if ($this->date_in_range($date_string, $range['christmas_start'], $range['christmas_end'])) {
                return 'christmas';
            }
        }
        
        // Check peak dates
        foreach ($this->seasons_data['peak']['dates'] as $range) {
            if ($this->date_in_range($date_string, $range['peak_start'], $range['peak_end'])) {
                return 'peak';
            }
        }
        
        // Check standard dates
        foreach ($this->seasons_data['standard']['dates'] as $range) {
            if ($this->date_in_range($date_string, $range['standard_start'], $range['standard_end'])) {
                return 'standard';
            }
        }
        
        // Default to standard if no specific season found
        return 'standard';
    }
    
    /**
     * Check if date is within a range
     */
    private function date_in_range($date, $start, $end) {
        $date_obj = DateTime::createFromFormat('d/m/Y', $date);
        $start_obj = DateTime::createFromFormat('d/m/Y', $start);
        $end_obj = DateTime::createFromFormat('d/m/Y', $end);
        
        return $date_obj && $start_obj && $end_obj && 
               $date_obj >= $start_obj && $date_obj <= $end_obj;
    }
    
    /**
     * Calculate helicopter package cost
     */
    private function calculate_helicopter_package($nights, $package_request) {
        // Find matching package for number of nights
        $matching_package = null;
        foreach ($this->packages_data as $package) {
            if ($package['heli_nights'] == $nights) {
                $matching_package = $package;
                break;
            }
        }
        
        if (!$matching_package) {
            return array('error' => "No helicopter package available for {$nights} nights");
        }
        
        $package_rate = $matching_package['heli_package_rate'];
        $included_hours = $matching_package['heli_flying_hours'];
        $in_property_hours = $matching_package['heli_in_property_hours'];
        
        // Calculate additional hours if requested
        $additional_hours = 0;
        $additional_cost = 0;
        if (isset($package_request['additional_hours']) && $package_request['additional_hours'] > 0) {
            $additional_hours = $package_request['additional_hours'];
            $additional_hour_rate = get_field('heli_additional_hour', 'option') ?: 0;
            $additional_cost = $additional_hours * $additional_hour_rate;
        }
        
        $total = $package_rate + $additional_cost;
        
        return array(
            'total' => $total,
            'breakdown' => array(
                'package_name' => "Helicopter Package ({$nights} nights)",
                'package_rate' => $package_rate,
                'included_flying_hours' => $included_hours,
                'in_property_hours' => $in_property_hours,
                'additional_hours' => $additional_hours,
                'additional_cost' => $additional_cost,
                'total' => $total
            )
        );
    }
    
    /**
     * Validate guest count against capacity rules
     */
    private function validate_guest_count($adults, $children) {
        if ($adults > $this->guest_rules['max_adults']) {
            return array('error' => "Maximum {$this->guest_rules['max_adults']} adults allowed");
        }
        
        if ($children > $this->guest_rules['max_children']) {
            return array('error' => "Maximum {$this->guest_rules['max_children']} children allowed");
        }
        
        if ($adults < 1) {
            return array('error' => 'At least 1 adult required');
        }
        
        return array('valid' => true);
    }
    
    /**
     * Get minimum nights requirement for a specific date
     */
    private function get_minimum_nights_for_date($date) {
        $date_string = $date->format('d/m/Y');
        
        // Check half-terms first
        foreach ($this->minimum_nights_rules['half_terms'] as $period) {
            if ($this->date_in_range($date_string, $period['half_term_start'], $period['half_term_end'])) {
                return $period['half_term_min_nights'];
            }
        }
        
        // Check special minimums
        foreach ($this->minimum_nights_rules['special'] as $period) {
            if ($this->date_in_range($date_string, $period['special_min_start'], $period['special_min_end'])) {
                return $period['special_min_value'];
            }
        }
        
        // Return default
        return $this->minimum_nights_rules['default'];
    }
    
    /**
     * Get accommodations data (placeholder - would come from separate ACF fields)
     */
    public function get_accommodations_data() {
        // This would typically come from a post type or ACF field
        // For now, return a basic structure matching Leobo's setup
        return array(
            'luxury-tent' => array(
                'name' => 'Luxury Tent',
                'base_guests' => 2,
                'max_guests' => 4
            ),
            'villa' => array(
                'name' => 'Villa',
                'base_guests' => 4,
                'max_guests' => 8
            )
        );
    }

    /**
     * Get all seasons data from ACF options page
     */
    public function get_seasons_data() {
        return array(
            'standard' => array(
                'dates' => get_field('standard_season_dates', 'option') ?: array(),
                'base_rate' => get_field('standard_base_rate', 'option') ?: 12500,
                'extra_adult' => get_field('standard_extra_adult', 'option') ?: 2500,
                'extra_child' => get_field('standard_extra_child', 'option') ?: 1250
            ),
            'peak' => array(
                'dates' => get_field('peak_season_dates', 'option') ?: array(),
                'base_rate' => get_field('peak_base_rate', 'option') ?: 18500,
                'extra_adult' => get_field('peak_extra_adult', 'option') ?: 3500,
                'extra_child' => get_field('peak_extra_child', 'option') ?: 1750
            ),
            'christmas' => array(
                'dates' => get_field('christmas_dates', 'option') ?: array(),
                'base_rate' => get_field('christmas_base_rate', 'option') ?: 25000,
                'extra_adult' => get_field('christmas_extra_adult', 'option') ?: 5000,
                'extra_child' => get_field('christmas_extra_child', 'option') ?: 2500,
                'surcharge' => get_field('christmas_surcharge', 'option') ?: 5000
            )
        );
    }

    /**
     * Get helicopter packages data from ACF
     */
    public function get_packages_data() {
        return get_field('helicopter_packages', 'option') ?: array();
    }

    /**
     * Get guest capacity and age rules from ACF
     */
    public function get_guest_rules() {
        return array(
            'max_adults' => get_field('max_adults', 'option') ?: 8,
            'max_children' => get_field('max_children', 'option') ?: 4,
            'children_free_age' => get_field('children_free_age', 'option') ?: 4
        );
    }

    /**
     * Get minimum nights configuration from ACF
     */
    public function get_minimum_nights_rules() {
        return array(
            'default' => get_field('default_min_nights', 'option') ?: 3,
            'special' => get_field('special_min_nights', 'option') ?: array(),
            'half_terms' => get_field('half_terms', 'option') ?: array()
        );
    }
    
    /**
     * Get all data for JavaScript frontend
     */
    public function get_frontend_data() {
        // Convert accommodations to expected format
        $accommodations_data = $this->get_accommodations_data();
        $accommodations = array();
        foreach ($accommodations_data as $id => $accommodation) {
            $accommodations[] = array(
                'id' => $id,
                'name' => $accommodation['name'],
                'description' => 'Luxury accommodation with premium amenities',
                'capacity' => $accommodation['max_guests'],
                'base_rate' => $this->seasons_data['standard']['base_rate'] ?: 12500,
                'image' => 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2Y4ZjlmYSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBkeT0iLjNlbSIgZmlsbD0iIzY2NiIgdGV4dC1hbmNob3I9Im1pZGRsZSI+QWNjb21tb2RhdGlvbjwvdGV4dD48L3N2Zz4='
            );
        }
        
        // Convert helicopter packages to expected format  
        $packages = array();
        if (!empty($this->packages_data)) {
            foreach ($this->packages_data as $package) {
                $packages[] = array(
                    'id' => 'helicopter-' . $package['heli_nights'] . '-nights',
                    'name' => "Helicopter Package ({$package['heli_nights']} nights)",
                    'description' => "Includes {$package['heli_flying_hours']} flying hours and {$package['heli_in_property_hours']} in-property hours",
                    'price' => $package['heli_package_rate'],
                    'price_type' => 'flat_rate',
                    'nights' => $package['heli_nights']
                );
            }
        }
        
        return array(
            'accommodations' => $accommodations,
            'packages' => $packages,
            'seasons' => $this->seasons_data,
            'guest_rules' => $this->guest_rules,
            'minimum_nights' => $this->minimum_nights_rules,
            'currency' => 'R'
        );
    }
}
