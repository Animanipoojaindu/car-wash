<?php

class CarWash_Bookings {
    
    public function __construct() {
        // This class can be extended for additional booking specific functionality
    }
    
    public static function get_all($filters = array()) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_bookings';
        
        $where_conditions = array();
        $where_values = array();
        
        if (!empty($filters['status'])) {
            $where_conditions[] = "booking_status = %s";
            $where_values[] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $where_conditions[] = "DATE(created_at) >= %s";
            $where_values[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where_conditions[] = "DATE(created_at) <= %s";
            $where_values[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $where_conditions[] = "(customer_name LIKE %s OR customer_email LIKE %s OR booking_number LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($filters['search']) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        $query = "SELECT b.*, vt.name as vehicle_type_name, vs.name as vehicle_subtype_name, s.name as service_name, l.name as los_name 
                  FROM $table b 
                  LEFT JOIN {$wpdb->prefix}carwash_vehicle_types vt ON b.vehicle_type_id = vt.id 
                  LEFT JOIN {$wpdb->prefix}carwash_vehicle_subtypes vs ON b.vehicle_subtype_id = vs.id 
                  LEFT JOIN {$wpdb->prefix}carwash_services s ON b.service_id = s.id 
                  LEFT JOIN {$wpdb->prefix}carwash_los l ON b.los_id = l.id 
                  $where_clause 
                  ORDER BY b.created_at DESC";
        
        if (!empty($where_values)) {
            return $wpdb->get_results($wpdb->prepare($query, $where_values));
        } else {
            return $wpdb->get_results($query);
        }
    }
    
    public static function get_by_id($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_bookings';
        
        $query = "SELECT b.*, vt.name as vehicle_type_name, vs.name as vehicle_subtype_name, s.name as service_name, l.name as los_name 
                  FROM $table b 
                  LEFT JOIN {$wpdb->prefix}carwash_vehicle_types vt ON b.vehicle_type_id = vt.id 
                  LEFT JOIN {$wpdb->prefix}carwash_vehicle_subtypes vs ON b.vehicle_subtype_id = vs.id 
                  LEFT JOIN {$wpdb->prefix}carwash_services s ON b.service_id = s.id 
                  LEFT JOIN {$wpdb->prefix}carwash_los l ON b.los_id = l.id 
                  WHERE b.id = %d";
        
        return $wpdb->get_row($wpdb->prepare($query, $id));
    }
    
    public static function get_by_booking_number($booking_number) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_bookings';
        
        $query = "SELECT b.*, vt.name as vehicle_type_name, vs.name as vehicle_subtype_name, s.name as service_name, l.name as los_name 
                  FROM $table b 
                  LEFT JOIN {$wpdb->prefix}carwash_vehicle_types vt ON b.vehicle_type_id = vt.id 
                  LEFT JOIN {$wpdb->prefix}carwash_vehicle_subtypes vs ON b.vehicle_subtype_id = vs.id 
                  LEFT JOIN {$wpdb->prefix}carwash_services s ON b.service_id = s.id 
                  LEFT JOIN {$wpdb->prefix}carwash_los l ON b.los_id = l.id 
                  WHERE b.booking_number = %s";
        
        return $wpdb->get_row($wpdb->prepare($query, $booking_number));
    }
    
    public static function create($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_bookings';
        
        $defaults = array(
            'booking_status' => 'pending',
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        );
        
        $data = wp_parse_args($data, $defaults);
        
        $result = $wpdb->insert($table, $data);
        
        if ($result === false) {
            return false;
        }
        
        return $wpdb->insert_id;
    }
    
    public static function update($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_bookings';
        
        $data['updated_at'] = current_time('mysql');
        
        return $wpdb->update($table, $data, array('id' => $id));
    }
    
    public static function update_status($id, $status) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_bookings';
        
        $result = $wpdb->update(
            $table, 
            array(
                'booking_status' => $status,
                'updated_at' => current_time('mysql')
            ), 
            array('id' => $id)
        );
        
        if ($result !== false) {
            // Send status update email
            CarWash_Email::send_status_update($id, $status);
        }
        
        return $result;
    }
    
    public static function delete($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_bookings';
        
        return $wpdb->delete($table, array('id' => $id));
    }
    
    public static function generate_booking_number() {
        $booking_number = 'CW' . date('Y') . str_pad(wp_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Ensure uniqueness
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_bookings';
        
        while ($wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE booking_number = %s", $booking_number))) {
            $booking_number = 'CW' . date('Y') . str_pad(wp_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        }
        
        return $booking_number;
    }
    
    public static function get_statistics() {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_bookings';
        
        $stats = array();
        
        $stats['total'] = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        $stats['pending'] = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE booking_status = 'pending'");
        $stats['confirmed'] = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE booking_status = 'confirmed'");
        $stats['completed'] = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE booking_status = 'completed'");
        $stats['cancelled'] = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE booking_status = 'cancelled'");
        $stats['total_revenue'] = $wpdb->get_var("SELECT SUM(total_price) FROM $table WHERE booking_status IN ('confirmed', 'completed')");
        
        return $stats;
    }
}

