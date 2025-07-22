<?php

class CarWash_Services {
    
    public function __construct() {
        // This class can be extended for additional service specific functionality
    }
    
    public static function get_all($active_only = false) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_services';
        
        $where_clause = $active_only ? 'WHERE is_active = 1' : '';
        
        return $wpdb->get_results("SELECT * FROM $table $where_clause ORDER BY sort_order ASC, name ASC");
    }
    
    public static function get_by_vehicle_type($vehicle_type_id, $vehicle_subtype_id = null, $active_only = false) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_services';
        
        $where_clause = "WHERE vehicle_type_id = %d";
        $where_values = array($vehicle_type_id);
        
        if ($vehicle_subtype_id) {
            $where_clause .= " AND (vehicle_subtype_id = %d OR vehicle_subtype_id IS NULL)";
            $where_values[] = $vehicle_subtype_id;
        }
        
        if ($active_only) {
            $where_clause .= " AND is_active = 1";
        }
        
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table $where_clause ORDER BY sort_order ASC, name ASC", $where_values));
    }
    
    public static function get_by_id($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_services';
        
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }
    
    public static function create($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_services';
        
        $defaults = array(
            'is_active' => true,
            'sort_order' => 0,
            'base_price' => 0.00,
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
        $table = $wpdb->prefix . 'carwash_services';
        
        $data['updated_at'] = current_time('mysql');
        
        return $wpdb->update($table, $data, array('id' => $id));
    }
    
    public static function delete($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_services';
        
        // Soft delete by setting is_active to false
        return $wpdb->update($table, array('is_active' => false, 'updated_at' => current_time('mysql')), array('id' => $id));
    }
}

