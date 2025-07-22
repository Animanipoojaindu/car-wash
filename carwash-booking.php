<?php
/**
 * Plugin Name: Car Wash Booking System
 * Plugin URI: https://example.com/carwash-booking
 * Description: A comprehensive multi-step booking system for car washing services with admin management capabilities.
 * Version: 1.0.1
 * Author: Manus AI
 * License: GPL v2 or later
 * Text Domain: carwash-booking
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access denied.');
}

// Check WordPress version
if (version_compare(get_bloginfo('version'), '5.0', '<')) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>Car Wash Booking System requires WordPress 5.0 or higher.</p></div>';
    });
    return;
}

// Check PHP version
if (version_compare(PHP_VERSION, '7.4', '<')) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>Car Wash Booking System requires PHP 7.4 or higher.</p></div>';
    });
    return;
}

// Define plugin constants
define('CARWASH_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CARWASH_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CARWASH_PLUGIN_VERSION', '1.0.1');
define('CARWASH_PLUGIN_FILE', __FILE__);

// Main plugin class
class CarWashBookingPlugin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('plugins_loaded', array($this, 'load_dependencies'));
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        register_uninstall_hook(__FILE__, array('CarWashBookingPlugin', 'uninstall'));
    }
    
    public function load_dependencies() {
        $files = array(
            'includes/class-database.php',
            'includes/class-api.php',
            'includes/class-admin.php',
            'includes/class-public.php',
            'includes/class-email.php',
            'includes/class-vehicle-types.php',
            'includes/class-services.php',
            'includes/class-bookings.php'
        );
        
        foreach ($files as $file) {
            $file_path = CARWASH_PLUGIN_PATH . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            } else {
                error_log("CarWash Plugin: Missing file - " . $file_path);
            }
        }
    }

    public function init() {
        // Load text domain for translations
        load_plugin_textdomain('carwash-booking', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Initialize components only if classes exist
        $this->init_hooks();
    }
    
    private function init_hooks() {
        try {
            // Initialize database
            if (class_exists('CarWash_Database')) {
                new CarWash_Database();
            }
            
            // Initialize API
            if (class_exists('CarWash_API')) {
                new CarWash_API();
            }
            
            // Initialize admin interface
            if (is_admin() && class_exists('CarWash_Admin')) {
                new CarWash_Admin();
            }
            
            // Initialize public interface
            if (class_exists('CarWash_Public')) {
                new CarWash_Public();
            }
            
            // Initialize email system
            if (class_exists('CarWash_Email')) {
                new CarWash_Email();
            }
            
            // Initialize data managers
            if (class_exists('CarWash_Vehicle_Types')) {
                new CarWash_Vehicle_Types();
            }
            if (class_exists('CarWash_Services')) {
                new CarWash_Services();
            }
            if (class_exists('CarWash_Bookings')) {
                new CarWash_Bookings();
            }
        } catch (Exception $e) {
            error_log('CarWash Plugin Init Error: ' . $e->getMessage());
        }
    }
    
    public function activate() {
        try {
            // Ensure database class is loaded
            $database_file = CARWASH_PLUGIN_PATH . 'includes/class-database.php';
            if (file_exists($database_file)) {
                require_once $database_file;
            } else {
                throw new Exception('Database class file not found');
            }
            
            // Check if class exists
            if (!class_exists('CarWash_Database')) {
                throw new Exception('CarWash_Database class not found');
            }
            
            // Create database tables
            CarWash_Database::create_tables();
            
            // Insert default data
            CarWash_Database::insert_default_data();
            
            // Set default options
            $this->set_default_options();
            
            // Add activation flag
            add_option('carwash_plugin_activated', true);
            
            // Flush rewrite rules
            flush_rewrite_rules();
            
        } catch (Exception $e) {
            // Log the error
            error_log('CarWash Plugin Activation Error: ' . $e->getMessage());
            
            // Show admin notice
            add_option('carwash_activation_error', $e->getMessage());
            
            // Deactivate plugin
            deactivate_plugins(plugin_basename(__FILE__));
            
            // Show error message
            wp_die('Plugin activation failed: ' . $e->getMessage() . '<br><a href="' . admin_url('plugins.php') . '">Back to Plugins</a>');
        }
    }
    
    public function deactivate() {
        // Remove activation flag
        delete_option('carwash_plugin_activated');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public static function uninstall() {
        // Remove all plugin options
        $options = array(
            'carwash_google_maps_api_key',
            'carwash_business_name',
            'carwash_business_email',
            'carwash_business_phone',
            'carwash_business_address',
            'carwash_email_from_name',
            'carwash_email_from_email',
            'carwash_booking_confirmation_subject',
            'carwash_booking_confirmation_template',
            'carwash_plugin_activated',
            'carwash_activation_error'
        );
        
        foreach ($options as $option) {
            delete_option($option);
        }
        
        // Drop database tables if requested
        if (get_option('carwash_remove_data_on_uninstall', false)) {
            if (class_exists('CarWash_Database')) {
                CarWash_Database::drop_tables();
            }
        }
    }
    
    private function set_default_options() {
        $default_settings = array(
            'google_maps_api_key' => '',
            'business_name' => 'Car Wash Pro',
            'business_email' => get_option('admin_email'),
            'business_phone' => '',
            'business_address' => '',
            'email_from_name' => get_option('blogname'),
            'email_from_email' => get_option('admin_email'),
            'booking_confirmation_subject' => 'Booking Confirmation - #{booking_number}',
            'booking_confirmation_template' => $this->get_default_email_template()
        );
        
        foreach ($default_settings as $key => $value) {
            add_option('carwash_' . $key, $value);
        }
    }
    
    private function get_default_email_template() {
        return '
        <h2>Booking Confirmation</h2>
        <p>Dear {customer_name},</p>
        <p>Thank you for your booking. Here are your booking details:</p>
        <ul>
            <li><strong>Booking Number:</strong> {booking_number}</li>
            <li><strong>Vehicle Type:</strong> {vehicle_type}</li>
            <li><strong>Service:</strong> {service_name}</li>
            <li><strong>Level of Service:</strong> {los_name}</li>
            <li><strong>Total Price:</strong> ${total_price}</li>
            <li><strong>Preferred Date:</strong> {preferred_date}</li>
            <li><strong>Preferred Time:</strong> {preferred_time}</li>
        </ul>
        <p>We will contact you soon to confirm the appointment.</p>
        <p>Best regards,<br>{business_name}</p>
        ';
    }
}

// Show activation error notice if exists
add_action('admin_notices', function() {
    $error = get_option('carwash_activation_error');
    if ($error) {
        echo '<div class="notice notice-error is-dismissible"><p><strong>Car Wash Plugin Error:</strong> ' . esc_html($error) . '</p></div>';
        delete_option('carwash_activation_error');
    }
});

// Initialize the plugin
CarWashBookingPlugin::get_instance();

