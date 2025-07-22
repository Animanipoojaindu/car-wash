<?php

class CarWash_Vehicle_Types {
    
    public function __construct() {
        // This class can be extended for additional vehicle type specific functionality
    }
    
    public static function get_all($active_only = false) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_vehicle_types';
        
        $where_clause = $active_only ? 'WHERE is_active = 1' : '';
        
        return $wpdb->get_results("SELECT * FROM $table $where_clause ORDER BY sort_order ASC, name ASC");
    }
    
    public static function get_by_id($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_vehicle_types';
        
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }
    
    public static function get_by_slug($slug) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_vehicle_types';
        
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE slug = %s", $slug));
    }
    
    public static function create($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_vehicle_types';
        
        $defaults = array(
            'is_active' => true,
            'sort_order' => 0,
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
        $table = $wpdb->prefix . 'carwash_vehicle_types';
        
        $data['updated_at'] = current_time('mysql');
        
        return $wpdb->update($table, $data, array('id' => $id));
    }
    
    public static function delete($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_vehicle_types';
        
        // Soft delete by setting is_active to false
        return $wpdb->update($table, array('is_active' => false, 'updated_at' => current_time('mysql')), array('id' => $id));
    }
    
    public static function get_subtypes($vehicle_type_id, $active_only = false) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_vehicle_subtypes';
        
        $where_clause = "WHERE vehicle_type_id = %d";
        $where_values = array($vehicle_type_id);
        
        if ($active_only) {
            $where_clause .= " AND is_active = 1";
        }
        
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table $where_clause ORDER BY sort_order ASC, name ASC", $where_values));
    }
}

