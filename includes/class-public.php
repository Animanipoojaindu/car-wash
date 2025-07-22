<?php

class CarWash_Public {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_scripts'));
        add_shortcode('carwash_booking_form', array($this, 'booking_form_shortcode'));
        add_action('wp_ajax_carwash_submit_booking', array($this, 'handle_ajax_booking'));
        add_action('wp_ajax_nopriv_carwash_submit_booking', array($this, 'handle_ajax_booking'));
    }
    
    public function enqueue_public_scripts() {
        // Only enqueue on pages with the shortcode
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'carwash_booking_form')) {
            wp_enqueue_script('carwash-public-js', CARWASH_PLUGIN_URL . 'assets/js/public.js', array('jquery'), CARWASH_PLUGIN_VERSION, true);
            wp_enqueue_style('carwash-public-css', CARWASH_PLUGIN_URL . 'assets/css/public.css', array(), CARWASH_PLUGIN_VERSION);
            
            // Enqueue Google Maps API if key is available
            $google_maps_key = get_option('carwash_google_maps_api_key');
            if ($google_maps_key) {
                wp_enqueue_script('google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . $google_maps_key . '&libraries=places', array(), null, true);
            }
            
            // Enqueue intl-tel-input for phone number formatting
            wp_enqueue_script('intl-tel-input', 'https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/js/intlTelInput.min.js', array(), '18.1.1', true);
            wp_enqueue_style('intl-tel-input-css', 'https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/css/intlTelInput.css', array(), '18.1.1');
            
            wp_localize_script('carwash-public-js', 'carwash_public', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('carwash_public_nonce'),
                'rest_url' => rest_url('carwash/v1/'),
                'google_maps_key' => $google_maps_key
            ));
        }
    }
    
    public function booking_form_shortcode($atts) {
        $atts = shortcode_atts(array(
            'theme' => 'light',
            'show_progress' => 'true'
        ), $atts);
        
        ob_start();
        include CARWASH_PLUGIN_PATH . 'public/booking-form.php';
        return ob_get_clean();
    }
    
    public function handle_ajax_booking() {
        check_ajax_referer('carwash_public_nonce', 'nonce');
        
        // This is a fallback for non-REST API submissions
        // The main submission should go through the REST API
        
        wp_send_json_error('Please use the REST API endpoint for booking submissions.');
    }
}

