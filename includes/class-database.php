<?php

class CarWash_Database {
    
    public function __construct() {
        // Constructor can be used for initialization if needed
    }
    
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Vehicle Types Table
        $table_vehicle_types = $wpdb->prefix . 'carwash_vehicle_types';
        $sql_vehicle_types = "CREATE TABLE $table_vehicle_types (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            slug varchar(100) NOT NULL,
            description text,
            image_url varchar(255),
            is_active boolean DEFAULT TRUE,
            sort_order int(11) DEFAULT 0,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug)
        ) $charset_collate;";
        
        // Vehicle Sub-Types Table
        $table_vehicle_subtypes = $wpdb->prefix . 'carwash_vehicle_subtypes';
        $sql_vehicle_subtypes = "CREATE TABLE $table_vehicle_subtypes (
            id int(11) NOT NULL AUTO_INCREMENT,
            vehicle_type_id int(11) NOT NULL,
            name varchar(100) NOT NULL,
            slug varchar(100) NOT NULL,
            description text,
            image_url varchar(255),
            is_active boolean DEFAULT TRUE,
            sort_order int(11) DEFAULT 0,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_subtype (vehicle_type_id, slug),
            FOREIGN KEY (vehicle_type_id) REFERENCES $table_vehicle_types(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        // Services Table
        $table_services = $wpdb->prefix . 'carwash_services';
        $sql_services = "CREATE TABLE $table_services (
            id int(11) NOT NULL AUTO_INCREMENT,
            vehicle_type_id int(11) NOT NULL,
            vehicle_subtype_id int(11),
            name varchar(100) NOT NULL,
            slug varchar(100) NOT NULL,
            description text,
            base_price decimal(10,2) DEFAULT 0.00,
            is_active boolean DEFAULT TRUE,
            sort_order int(11) DEFAULT 0,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            FOREIGN KEY (vehicle_type_id) REFERENCES $table_vehicle_types(id) ON DELETE CASCADE,
            FOREIGN KEY (vehicle_subtype_id) REFERENCES $table_vehicle_subtypes(id) ON DELETE SET NULL
        ) $charset_collate;";
        
        // Levels of Service Table
        $table_los = $wpdb->prefix . 'carwash_los';
        $sql_los = "CREATE TABLE $table_los (
            id int(11) NOT NULL AUTO_INCREMENT,
            vehicle_type_id int(11) NOT NULL,
            name varchar(100) NOT NULL,
            slug varchar(100) NOT NULL,
            description text,
            price_modifier decimal(5,2) DEFAULT 1.00,
            has_sanding_option boolean DEFAULT FALSE,
            sanding_price_add decimal(10,2) DEFAULT 0.00,
            is_active boolean DEFAULT TRUE,
            sort_order int(11) DEFAULT 0,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_los (vehicle_type_id, slug),
            FOREIGN KEY (vehicle_type_id) REFERENCES $table_vehicle_types(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        // Bookings Table
        $table_bookings = $wpdb->prefix . 'carwash_bookings';
        $sql_bookings = "CREATE TABLE $table_bookings (
            id int(11) NOT NULL AUTO_INCREMENT,
            booking_number varchar(20) NOT NULL,
            customer_name varchar(100) NOT NULL,
            customer_email varchar(100) NOT NULL,
            customer_phone varchar(20) NOT NULL,
            customer_address text NOT NULL,
            vehicle_number varchar(50),
            vehicle_model varchar(100),
            vehicle_type_id int(11) NOT NULL,
            vehicle_subtype_id int(11),
            service_id int(11),
            los_id int(11) NOT NULL,
            include_sanding boolean DEFAULT FALSE,
            total_price decimal(10,2) NOT NULL,
            booking_status enum('pending', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
            preferred_date date,
            preferred_time time,
            special_instructions text,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY booking_number (booking_number),
            FOREIGN KEY (vehicle_type_id) REFERENCES $table_vehicle_types(id),
            FOREIGN KEY (vehicle_subtype_id) REFERENCES $table_vehicle_subtypes(id),
            FOREIGN KEY (service_id) REFERENCES $table_services(id),
            FOREIGN KEY (los_id) REFERENCES $table_los(id)
        ) $charset_collate;";
        
        // Settings Table
        $table_settings = $wpdb->prefix . 'carwash_settings';
        $sql_settings = "CREATE TABLE $table_settings (
            id int(11) NOT NULL AUTO_INCREMENT,
            setting_key varchar(100) NOT NULL,
            setting_value longtext,
            setting_type enum('string', 'number', 'boolean', 'json') DEFAULT 'string',
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY setting_key (setting_key)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($sql_vehicle_types);
        dbDelta($sql_vehicle_subtypes);
        dbDelta($sql_services);
        dbDelta($sql_los);
        dbDelta($sql_bookings);
        dbDelta($sql_settings);
    }
    
    public static function insert_default_data() {
        global $wpdb;
        
        // Insert default vehicle types
        $vehicle_types = array(
            array('name' => 'Automotive', 'slug' => 'automotive', 'sort_order' => 1),
            array('name' => 'Marine', 'slug' => 'marine', 'sort_order' => 2),
            array('name' => 'Aviation', 'slug' => 'aviation', 'sort_order' => 3),
            array('name' => 'Truck (Heavy)', 'slug' => 'truck-heavy', 'sort_order' => 4),
            array('name' => 'Motorcycle', 'slug' => 'motorcycle', 'sort_order' => 5),
            array('name' => 'RV', 'slug' => 'rv', 'sort_order' => 6)
        );
        
        $table_vehicle_types = $wpdb->prefix . 'carwash_vehicle_types';
        foreach ($vehicle_types as $type) {
            $wpdb->insert($table_vehicle_types, $type);
        }
        
        // Get inserted vehicle type IDs
        $automotive_id = $wpdb->get_var("SELECT id FROM $table_vehicle_types WHERE slug = 'automotive'");
        $marine_id = $wpdb->get_var("SELECT id FROM $table_vehicle_types WHERE slug = 'marine'");
        $aviation_id = $wpdb->get_var("SELECT id FROM $table_vehicle_types WHERE slug = 'aviation'");
        $truck_id = $wpdb->get_var("SELECT id FROM $table_vehicle_types WHERE slug = 'truck-heavy'");
        $motorcycle_id = $wpdb->get_var("SELECT id FROM $table_vehicle_types WHERE slug = 'motorcycle'");
        $rv_id = $wpdb->get_var("SELECT id FROM $table_vehicle_types WHERE slug = 'rv'");
        
        // Insert default vehicle subtypes
        $vehicle_subtypes = array(
            // Automotive
            array('vehicle_type_id' => $automotive_id, 'name' => 'Modern', 'slug' => 'modern', 'sort_order' => 1),
            array('vehicle_type_id' => $automotive_id, 'name' => 'Classic', 'slug' => 'classic', 'sort_order' => 2),
            // Marine
            array('vehicle_type_id' => $marine_id, 'name' => 'Lake', 'slug' => 'lake', 'sort_order' => 1),
            array('vehicle_type_id' => $marine_id, 'name' => 'Ocean', 'slug' => 'ocean', 'sort_order' => 2),
            // Aviation
            array('vehicle_type_id' => $aviation_id, 'name' => '2-4 seats', 'slug' => '2-4-seats', 'sort_order' => 1),
            array('vehicle_type_id' => $aviation_id, 'name' => '4-8 seats', 'slug' => '4-8-seats', 'sort_order' => 2),
            array('vehicle_type_id' => $aviation_id, 'name' => '8+ Seats', 'slug' => '8-plus-seats', 'sort_order' => 3),
            // Truck
            array('vehicle_type_id' => $truck_id, 'name' => 'Day Cab', 'slug' => 'day-cab', 'sort_order' => 1),
            array('vehicle_type_id' => $truck_id, 'name' => 'Sleeper', 'slug' => 'sleeper', 'sort_order' => 2),
            // RV
            array('vehicle_type_id' => $rv_id, 'name' => 'Motorhome', 'slug' => 'motorhome', 'sort_order' => 1),
            array('vehicle_type_id' => $rv_id, 'name' => 'Trailer', 'slug' => 'trailer', 'sort_order' => 2)
        );
        
        $table_vehicle_subtypes = $wpdb->prefix . 'carwash_vehicle_subtypes';
        foreach ($vehicle_subtypes as $subtype) {
            $wpdb->insert($table_vehicle_subtypes, $subtype);
        }
        
        // Insert default levels of service
        $los_data = array(
            // Automotive LOS
            array('vehicle_type_id' => $automotive_id, 'name' => 'Maintenance', 'slug' => 'maintenance', 'price_modifier' => 1.00, 'sort_order' => 1),
            array('vehicle_type_id' => $automotive_id, 'name' => 'Cut and Polish', 'slug' => 'cut-polish', 'price_modifier' => 1.50, 'sort_order' => 2),
            array('vehicle_type_id' => $automotive_id, 'name' => 'Show Finish', 'slug' => 'show-finish', 'price_modifier' => 2.00, 'has_sanding_option' => true, 'sanding_price_add' => 100.00, 'sort_order' => 3),
            // Marine LOS
            array('vehicle_type_id' => $marine_id, 'name' => 'Maintenance', 'slug' => 'maintenance', 'price_modifier' => 1.00, 'sort_order' => 1),
            array('vehicle_type_id' => $marine_id, 'name' => 'Cut and Polish', 'slug' => 'cut-polish', 'price_modifier' => 1.50, 'sort_order' => 2),
            array('vehicle_type_id' => $marine_id, 'name' => 'Sanded', 'slug' => 'sanded', 'price_modifier' => 2.50, 'sort_order' => 3),
            // Truck LOS
            array('vehicle_type_id' => $truck_id, 'name' => 'Maintenance', 'slug' => 'maintenance', 'price_modifier' => 1.00, 'sort_order' => 1),
            array('vehicle_type_id' => $truck_id, 'name' => 'Cut and Polish', 'slug' => 'cut-polish', 'price_modifier' => 1.50, 'sort_order' => 2),
            array('vehicle_type_id' => $truck_id, 'name' => 'Show Finish', 'slug' => 'show-finish', 'price_modifier' => 2.00, 'has_sanding_option' => true, 'sanding_price_add' => 150.00, 'sort_order' => 3),
            // RV LOS
            array('vehicle_type_id' => $rv_id, 'name' => 'Maintenance', 'slug' => 'maintenance', 'price_modifier' => 1.00, 'sort_order' => 1),
            array('vehicle_type_id' => $rv_id, 'name' => 'Cut and Polish', 'slug' => 'cut-polish', 'price_modifier' => 1.50, 'sort_order' => 2),
            array('vehicle_type_id' => $rv_id, 'name' => 'Sanded', 'slug' => 'sanded', 'price_modifier' => 2.50, 'sort_order' => 3)
        );
        
        $table_los = $wpdb->prefix . 'carwash_los';
        foreach ($los_data as $los) {
            $wpdb->insert($table_los, $los);
        }
        
        // Insert default services with base prices
        $services_data = array(
            // Automotive services
            array('vehicle_type_id' => $automotive_id, 'name' => 'Standard Wash', 'slug' => 'standard-wash', 'base_price' => 150.00, 'sort_order' => 1),
            array('vehicle_type_id' => $automotive_id, 'name' => 'Premium Wash', 'slug' => 'premium-wash', 'base_price' => 250.00, 'sort_order' => 2),
            // Marine services
            array('vehicle_type_id' => $marine_id, 'name' => 'Hull Cleaning', 'slug' => 'hull-cleaning', 'base_price' => 300.00, 'sort_order' => 1),
            array('vehicle_type_id' => $marine_id, 'name' => 'Full Detail', 'slug' => 'full-detail', 'base_price' => 500.00, 'sort_order' => 2),
            // Aviation services
            array('vehicle_type_id' => $aviation_id, 'name' => 'Exterior Wash', 'slug' => 'exterior-wash', 'base_price' => 400.00, 'sort_order' => 1),
            array('vehicle_type_id' => $aviation_id, 'name' => 'Complete Detail', 'slug' => 'complete-detail', 'base_price' => 800.00, 'sort_order' => 2),
            // Truck services
            array('vehicle_type_id' => $truck_id, 'name' => 'Basic Wash', 'slug' => 'basic-wash', 'base_price' => 200.00, 'sort_order' => 1),
            array('vehicle_type_id' => $truck_id, 'name' => 'Deep Clean', 'slug' => 'deep-clean', 'base_price' => 350.00, 'sort_order' => 2),
            // Motorcycle services
            array('vehicle_type_id' => $motorcycle_id, 'name' => 'Flat Rate Service', 'slug' => 'flat-rate', 'base_price' => 500.00, 'sort_order' => 1),
            // RV services
            array('vehicle_type_id' => $rv_id, 'name' => 'Standard Clean', 'slug' => 'standard-clean', 'base_price' => 300.00, 'sort_order' => 1),
            array('vehicle_type_id' => $rv_id, 'name' => 'Premium Detail', 'slug' => 'premium-detail', 'base_price' => 500.00, 'sort_order' => 2)
        );
        
        $table_services = $wpdb->prefix . 'carwash_services';
        foreach ($services_data as $service) {
            $wpdb->insert($table_services, $service);
        }
    }
    
    public static function drop_tables() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'carwash_bookings',
            $wpdb->prefix . 'carwash_los',
            $wpdb->prefix . 'carwash_services',
            $wpdb->prefix . 'carwash_vehicle_subtypes',
            $wpdb->prefix . 'carwash_vehicle_types',
            $wpdb->prefix . 'carwash_settings'
        );
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }
}

