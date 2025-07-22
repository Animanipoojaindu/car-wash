<?php

class CarWash_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Car Wash Booking',
            'Car Wash',
            'manage_options',
            'carwash-booking',
            array($this, 'admin_dashboard'),
            'dashicons-car',
            30
        );
        
        add_submenu_page(
            'carwash-booking',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'carwash-booking',
            array($this, 'admin_dashboard')
        );
        
        add_submenu_page(
            'carwash-booking',
            'Bookings',
            'Bookings',
            'manage_options',
            'carwash-bookings',
            array($this, 'admin_bookings')
        );
        
        add_submenu_page(
            'carwash-booking',
            'Vehicle Types',
            'Vehicle Types',
            'manage_options',
            'carwash-vehicle-types',
            array($this, 'admin_vehicle_types')
        );
        
        add_submenu_page(
            'carwash-booking',
            'Services',
            'Services',
            'manage_options',
            'carwash-services',
            array($this, 'admin_services')
        );
        
        add_submenu_page(
            'carwash-booking',
            'Levels of Service',
            'Levels of Service',
            'manage_options',
            'carwash-los',
            array($this, 'admin_los')
        );
        
        add_submenu_page(
            'carwash-booking',
            'Settings',
            'Settings',
            'manage_options',
            'carwash-settings',
            array($this, 'admin_settings')
        );
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'carwash') === false) {
            return;
        }
        
        wp_enqueue_script('carwash-admin-js', CARWASH_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), CARWASH_PLUGIN_VERSION, true);
        wp_enqueue_style('carwash-admin-css', CARWASH_PLUGIN_URL . 'assets/css/admin.css', array(), CARWASH_PLUGIN_VERSION);
        
        wp_localize_script('carwash-admin-js', 'carwash_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('carwash_admin_nonce'),
            'rest_url' => rest_url('carwash/v1/admin/'),
            'rest_nonce' => wp_create_nonce('wp_rest')
        ));
    }
    
    public function register_settings() {
        register_setting('carwash_settings', 'carwash_google_maps_api_key');
        register_setting('carwash_settings', 'carwash_business_name');
        register_setting('carwash_settings', 'carwash_business_email');
        register_setting('carwash_settings', 'carwash_business_phone');
        register_setting('carwash_settings', 'carwash_business_address');
        register_setting('carwash_settings', 'carwash_email_from_name');
        register_setting('carwash_settings', 'carwash_email_from_email');
        register_setting('carwash_settings', 'carwash_booking_confirmation_subject');
        register_setting('carwash_settings', 'carwash_booking_confirmation_template');
    }
    
    public function admin_dashboard() {
        global $wpdb;
        
        // Get statistics
        $bookings_table = $wpdb->prefix . 'carwash_bookings';
        $total_bookings = $wpdb->get_var("SELECT COUNT(*) FROM $bookings_table");
        $pending_bookings = $wpdb->get_var("SELECT COUNT(*) FROM $bookings_table WHERE booking_status = 'pending'");
        $confirmed_bookings = $wpdb->get_var("SELECT COUNT(*) FROM $bookings_table WHERE booking_status = 'confirmed'");
        $completed_bookings = $wpdb->get_var("SELECT COUNT(*) FROM $bookings_table WHERE booking_status = 'completed'");
        $total_revenue = $wpdb->get_var("SELECT SUM(total_price) FROM $bookings_table WHERE booking_status IN ('confirmed', 'completed')");
        
        // Get recent bookings
        $recent_bookings = $wpdb->get_results("
            SELECT b.*, vt.name as vehicle_type_name 
            FROM $bookings_table b 
            LEFT JOIN {$wpdb->prefix}carwash_vehicle_types vt ON b.vehicle_type_id = vt.id 
            ORDER BY b.created_at DESC 
            LIMIT 10
        ");
        
        include CARWASH_PLUGIN_PATH . 'admin/dashboard.php';
    }
    
    public function admin_bookings() {
        include CARWASH_PLUGIN_PATH . 'admin/bookings.php';
    }
    
    public function admin_vehicle_types() {
        include CARWASH_PLUGIN_PATH . 'admin/vehicle-types.php';
    }
    
    public function admin_services() {
        include CARWASH_PLUGIN_PATH . 'admin/services.php';
    }
    
    public function admin_los() {
        include CARWASH_PLUGIN_PATH . 'admin/los.php';
    }
    
    public function admin_settings() {
        if (isset($_POST['submit'])) {
            update_option('carwash_google_maps_api_key', sanitize_text_field($_POST['carwash_google_maps_api_key']));
            update_option('carwash_business_name', sanitize_text_field($_POST['carwash_business_name']));
            update_option('carwash_business_email', sanitize_email($_POST['carwash_business_email']));
            update_option('carwash_business_phone', sanitize_text_field($_POST['carwash_business_phone']));
            update_option('carwash_business_address', sanitize_textarea_field($_POST['carwash_business_address']));
            update_option('carwash_email_from_name', sanitize_text_field($_POST['carwash_email_from_name']));
            update_option('carwash_email_from_email', sanitize_email($_POST['carwash_email_from_email']));
            update_option('carwash_booking_confirmation_subject', sanitize_text_field($_POST['carwash_booking_confirmation_subject']));
            update_option('carwash_booking_confirmation_template', wp_kses_post($_POST['carwash_booking_confirmation_template']));
            
            echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
        }
        
        include CARWASH_PLUGIN_PATH . 'admin/settings.php';
    }
}

