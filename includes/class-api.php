<?php

class CarWash_API {
    
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    public function register_routes() {
        // Admin API routes
        $this->register_admin_routes();
        
        // Public API routes
        $this->register_public_routes();
    }
    
    private function register_admin_routes() {
        $namespace = 'carwash/v1/admin';
        
        // Vehicle Types routes
        register_rest_route($namespace, '/vehicle-types', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_vehicle_types'),
                'permission_callback' => array($this, 'admin_permissions_check')
            ),
            array(
                'methods' => 'POST',
                'callback' => array($this, 'create_vehicle_type'),
                'permission_callback' => array($this, 'admin_permissions_check')
            )
        ));
        
        register_rest_route($namespace, '/vehicle-types/(?P<id>\d+)', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_vehicle_type'),
                'permission_callback' => array($this, 'admin_permissions_check')
            ),
            array(
                'methods' => 'PUT',
                'callback' => array($this, 'update_vehicle_type'),
                'permission_callback' => array($this, 'admin_permissions_check')
            ),
            array(
                'methods' => 'DELETE',
                'callback' => array($this, 'delete_vehicle_type'),
                'permission_callback' => array($this, 'admin_permissions_check')
            )
        ));
        
        // Vehicle Subtypes routes
        register_rest_route($namespace, '/vehicle-subtypes', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_vehicle_subtypes'),
                'permission_callback' => array($this, 'admin_permissions_check')
            ),
            array(
                'methods' => 'POST',
                'callback' => array($this, 'create_vehicle_subtype'),
                'permission_callback' => array($this, 'admin_permissions_check')
            )
        ));
        
        register_rest_route($namespace, '/vehicle-subtypes/(?P<id>\d+)', array(
            array(
                'methods' => 'PUT',
                'callback' => array($this, 'update_vehicle_subtype'),
                'permission_callback' => array($this, 'admin_permissions_check')
            ),
            array(
                'methods' => 'DELETE',
                'callback' => array($this, 'delete_vehicle_subtype'),
                'permission_callback' => array($this, 'admin_permissions_check')
            )
        ));
        
        // Services routes
        register_rest_route($namespace, '/services', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_services'),
                'permission_callback' => array($this, 'admin_permissions_check')
            ),
            array(
                'methods' => 'POST',
                'callback' => array($this, 'create_service'),
                'permission_callback' => array($this, 'admin_permissions_check')
            )
        ));
        
        register_rest_route($namespace, '/services/(?P<id>\d+)', array(
            array(
                'methods' => 'PUT',
                'callback' => array($this, 'update_service'),
                'permission_callback' => array($this, 'admin_permissions_check')
            ),
            array(
                'methods' => 'DELETE',
                'callback' => array($this, 'delete_service'),
                'permission_callback' => array($this, 'admin_permissions_check')
            )
        ));
        
        // LOS routes
        register_rest_route($namespace, '/los', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_los'),
                'permission_callback' => array($this, 'admin_permissions_check')
            ),
            array(
                'methods' => 'POST',
                'callback' => array($this, 'create_los'),
                'permission_callback' => array($this, 'admin_permissions_check')
            )
        ));
        
        register_rest_route($namespace, '/los/(?P<id>\d+)', array(
            array(
                'methods' => 'PUT',
                'callback' => array($this, 'update_los'),
                'permission_callback' => array($this, 'admin_permissions_check')
            ),
            array(
                'methods' => 'DELETE',
                'callback' => array($this, 'delete_los'),
                'permission_callback' => array($this, 'admin_permissions_check')
            )
        ));
        
        // Bookings routes
        register_rest_route($namespace, '/bookings', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_bookings'),
            'permission_callback' => array($this, 'admin_permissions_check')
        ));
        
        register_rest_route($namespace, '/bookings/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_booking'),
            'permission_callback' => array($this, 'admin_permissions_check')
        ));
        
        register_rest_route($namespace, '/bookings/(?P<id>\d+)/status', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_booking_status'),
            'permission_callback' => array($this, 'admin_permissions_check')
        ));
    }
    
    private function register_public_routes() {
        $namespace = 'carwash/v1';
        
        // Public data routes
        register_rest_route($namespace, '/vehicle-types', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_public_vehicle_types'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route($namespace, '/vehicle-subtypes/(?P<vehicle_type_id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_public_vehicle_subtypes'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route($namespace, '/services/(?P<vehicle_type_id>\d+)(?:/(?P<subtype_id>\d+))?', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_public_services'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route($namespace, '/los/(?P<vehicle_type_id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_public_los'),
            'permission_callback' => '__return_true'
        ));
        
        // Booking routes
        register_rest_route($namespace, '/bookings/calculate-price', array(
            'methods' => 'POST',
            'callback' => array($this, 'calculate_price'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route($namespace, '/bookings/submit', array(
            'methods' => 'POST',
            'callback' => array($this, 'submit_booking'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route($namespace, '/bookings/(?P<booking_number>[a-zA-Z0-9]+)/receipt', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_booking_receipt'),
            'permission_callback' => '__return_true'
        ));
    }
    
    // Permission callbacks
    public function admin_permissions_check() {
        return current_user_can('manage_options');
    }
    
    // Admin API methods - Vehicle Types
    public function get_vehicle_types($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_vehicle_types';
        
        $page = $request->get_param('page') ?: 1;
        $per_page = $request->get_param('per_page') ?: 10;
        $search = $request->get_param('search');
        $status = $request->get_param('status');
        
        $where_conditions = array();
        $where_values = array();
        
        if ($search) {
            $where_conditions[] = "name LIKE %s";
            $where_values[] = '%' . $wpdb->esc_like($search) . '%';
        }
        
        if ($status !== null) {
            $where_conditions[] = "is_active = %d";
            $where_values[] = $status === 'active' ? 1 : 0;
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        $offset = ($page - 1) * $per_page;
        
        $query = "SELECT * FROM $table $where_clause ORDER BY sort_order ASC, name ASC LIMIT %d OFFSET %d";
        $where_values[] = $per_page;
        $where_values[] = $offset;
        
        $results = $wpdb->get_results($wpdb->prepare($query, $where_values));
        
        // Get total count
        $count_query = "SELECT COUNT(*) FROM $table $where_clause";
        $total = $wpdb->get_var($wpdb->prepare($count_query, array_slice($where_values, 0, -2)));
        
        return new WP_REST_Response(array(
            'data' => $results,
            'total' => (int) $total,
            'page' => (int) $page,
            'per_page' => (int) $per_page
        ), 200);
    }
    
    public function get_vehicle_type($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_vehicle_types';
        $id = (int) $request->get_param('id');
        
        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
        
        if (!$result) {
            return new WP_Error('not_found', 'Vehicle type not found', array('status' => 404));
        }
        
        return new WP_REST_Response($result, 200);
    }
    
    public function create_vehicle_type($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_vehicle_types';
        
        $data = array(
            'name' => sanitize_text_field($request->get_param('name')),
            'slug' => sanitize_title($request->get_param('slug')),
            'description' => sanitize_textarea_field($request->get_param('description')),
            'image_url' => esc_url_raw($request->get_param('image_url')),
            'sort_order' => (int) $request->get_param('sort_order')
        );
        
        $result = $wpdb->insert($table, $data);
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to create vehicle type', array('status' => 500));
        }
        
        $data['id'] = $wpdb->insert_id;
        return new WP_REST_Response($data, 201);
    }
    
    public function update_vehicle_type($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_vehicle_types';
        $id = (int) $request->get_param('id');
        
        $data = array();
        if ($request->get_param('name')) {
            $data['name'] = sanitize_text_field($request->get_param('name'));
        }
        if ($request->get_param('slug')) {
            $data['slug'] = sanitize_title($request->get_param('slug'));
        }
        if ($request->get_param('description') !== null) {
            $data['description'] = sanitize_textarea_field($request->get_param('description'));
        }
        if ($request->get_param('image_url') !== null) {
            $data['image_url'] = esc_url_raw($request->get_param('image_url'));
        }
        if ($request->get_param('sort_order') !== null) {
            $data['sort_order'] = (int) $request->get_param('sort_order');
        }
        if ($request->get_param('is_active') !== null) {
            $data['is_active'] = (bool) $request->get_param('is_active');
        }
        
        $result = $wpdb->update($table, $data, array('id' => $id));
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to update vehicle type', array('status' => 500));
        }
        
        return new WP_REST_Response(array('message' => 'Vehicle type updated successfully'), 200);
    }
    
    public function delete_vehicle_type($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_vehicle_types';
        $id = (int) $request->get_param('id');
        
        $result = $wpdb->delete($table, array('id' => $id));
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to delete vehicle type', array('status' => 500));
        }
        
        return new WP_REST_Response(array('message' => 'Vehicle type deleted successfully'), 200);
    }
    
    // Admin API methods - Services
    public function get_services($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_services';
        
        $page = $request->get_param('page') ?: 1;
        $per_page = $request->get_param('per_page') ?: 10;
        $search = $request->get_param('search');
        $vehicle_type_id = $request->get_param('vehicle_type_id');
        
        $where_conditions = array();
        $where_values = array();
        
        if ($search) {
            $where_conditions[] = "s.name LIKE %s";
            $where_values[] = '%' . $wpdb->esc_like($search) . '%';
        }
        
        if ($vehicle_type_id) {
            $where_conditions[] = "s.vehicle_type_id = %d";
            $where_values[] = (int) $vehicle_type_id;
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        $offset = ($page - 1) * $per_page;
        
        $query = "SELECT s.*, vt.name as vehicle_type_name, vs.name as vehicle_subtype_name 
                  FROM $table s 
                  LEFT JOIN {$wpdb->prefix}carwash_vehicle_types vt ON s.vehicle_type_id = vt.id 
                  LEFT JOIN {$wpdb->prefix}carwash_vehicle_subtypes vs ON s.vehicle_subtype_id = vs.id 
                  $where_clause 
                  ORDER BY s.sort_order ASC, s.name ASC 
                  LIMIT %d OFFSET %d";
        $where_values[] = $per_page;
        $where_values[] = $offset;
        
        $results = $wpdb->get_results($wpdb->prepare($query, $where_values));
        
        // Get total count
        $count_query = "SELECT COUNT(*) FROM $table s $where_clause";
        $total = $wpdb->get_var($wpdb->prepare($count_query, array_slice($where_values, 0, -2)));
        
        return new WP_REST_Response(array(
            'data' => $results,
            'total' => (int) $total,
            'page' => (int) $page,
            'per_page' => (int) $per_page
        ), 200);
    }
    
    public function create_service($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_services';
        
        $data = array(
            'vehicle_type_id' => (int) $request->get_param('vehicle_type_id'),
            'vehicle_subtype_id' => $request->get_param('vehicle_subtype_id') ? (int) $request->get_param('vehicle_subtype_id') : null,
            'name' => sanitize_text_field($request->get_param('name')),
            'slug' => sanitize_title($request->get_param('slug')),
            'description' => sanitize_textarea_field($request->get_param('description')),
            'base_price' => floatval($request->get_param('base_price')),
            'sort_order' => (int) $request->get_param('sort_order')
        );
        
        $result = $wpdb->insert($table, $data);
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to create service', array('status' => 500));
        }
        
        $data['id'] = $wpdb->insert_id;
        return new WP_REST_Response($data, 201);
    }
    
    public function update_service($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_services';
        $id = (int) $request->get_param('id');
        
        $data = array();
        if ($request->get_param('vehicle_type_id')) {
            $data['vehicle_type_id'] = (int) $request->get_param('vehicle_type_id');
        }
        if ($request->get_param('vehicle_subtype_id') !== null) {
            $data['vehicle_subtype_id'] = $request->get_param('vehicle_subtype_id') ? (int) $request->get_param('vehicle_subtype_id') : null;
        }
        if ($request->get_param('name')) {
            $data['name'] = sanitize_text_field($request->get_param('name'));
        }
        if ($request->get_param('slug')) {
            $data['slug'] = sanitize_title($request->get_param('slug'));
        }
        if ($request->get_param('description') !== null) {
            $data['description'] = sanitize_textarea_field($request->get_param('description'));
        }
        if ($request->get_param('base_price') !== null) {
            $data['base_price'] = floatval($request->get_param('base_price'));
        }
        if ($request->get_param('sort_order') !== null) {
            $data['sort_order'] = (int) $request->get_param('sort_order');
        }
        if ($request->get_param('is_active') !== null) {
            $data['is_active'] = (bool) $request->get_param('is_active');
        }
        
        $result = $wpdb->update($table, $data, array('id' => $id));
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to update service', array('status' => 500));
        }
        
        return new WP_REST_Response(array('message' => 'Service updated successfully'), 200);
    }
    
    public function delete_service($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_services';
        $id = (int) $request->get_param('id');
        
        $result = $wpdb->delete($table, array('id' => $id));
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to delete service', array('status' => 500));
        }
        
        return new WP_REST_Response(array('message' => 'Service deleted successfully'), 200);
    }
    
    // Admin API methods - LOS (Levels of Service)
    public function get_los($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_los';
        
        $page = $request->get_param('page') ?: 1;
        $per_page = $request->get_param('per_page') ?: 10;
        $search = $request->get_param('search');
        $vehicle_type_id = $request->get_param('vehicle_type_id');
        
        $where_conditions = array();
        $where_values = array();
        
        if ($search) {
            $where_conditions[] = "l.name LIKE %s";
            $where_values[] = '%' . $wpdb->esc_like($search) . '%';
        }
        
        if ($vehicle_type_id) {
            $where_conditions[] = "l.vehicle_type_id = %d";
            $where_values[] = (int) $vehicle_type_id;
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        $offset = ($page - 1) * $per_page;
        
        $query = "SELECT l.*, vt.name as vehicle_type_name 
                  FROM $table l 
                  LEFT JOIN {$wpdb->prefix}carwash_vehicle_types vt ON l.vehicle_type_id = vt.id 
                  $where_clause 
                  ORDER BY l.sort_order ASC, l.name ASC 
                  LIMIT %d OFFSET %d";
        $where_values[] = $per_page;
        $where_values[] = $offset;
        
        $results = $wpdb->get_results($wpdb->prepare($query, $where_values));
        
        // Get total count
        $count_query = "SELECT COUNT(*) FROM $table l $where_clause";
        $total = $wpdb->get_var($wpdb->prepare($count_query, array_slice($where_values, 0, -2)));
        
        return new WP_REST_Response(array(
            'data' => $results,
            'total' => (int) $total,
            'page' => (int) $page,
            'per_page' => (int) $per_page
        ), 200);
    }
    
    public function create_los($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_los';
        
        $data = array(
            'vehicle_type_id' => (int) $request->get_param('vehicle_type_id'),
            'name' => sanitize_text_field($request->get_param('name')),
            'slug' => sanitize_title($request->get_param('slug')),
            'description' => sanitize_textarea_field($request->get_param('description')),
            'price_modifier' => floatval($request->get_param('price_modifier')),
            'has_sanding_option' => (bool) $request->get_param('has_sanding_option'),
            'sanding_price_add' => floatval($request->get_param('sanding_price_add')),
            'sort_order' => (int) $request->get_param('sort_order')
        );
        
        $result = $wpdb->insert($table, $data);
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to create level of service', array('status' => 500));
        }
        
        $data['id'] = $wpdb->insert_id;
        return new WP_REST_Response($data, 201);
    }
    
    public function update_los($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_los';
        $id = (int) $request->get_param('id');
        
        $data = array();
        if ($request->get_param('vehicle_type_id')) {
            $data['vehicle_type_id'] = (int) $request->get_param('vehicle_type_id');
        }
        if ($request->get_param('name')) {
            $data['name'] = sanitize_text_field($request->get_param('name'));
        }
        if ($request->get_param('slug')) {
            $data['slug'] = sanitize_title($request->get_param('slug'));
        }
        if ($request->get_param('description') !== null) {
            $data['description'] = sanitize_textarea_field($request->get_param('description'));
        }
        if ($request->get_param('price_modifier') !== null) {
            $data['price_modifier'] = floatval($request->get_param('price_modifier'));
        }
        if ($request->get_param('has_sanding_option') !== null) {
            $data['has_sanding_option'] = (bool) $request->get_param('has_sanding_option');
        }
        if ($request->get_param('sanding_price_add') !== null) {
            $data['sanding_price_add'] = floatval($request->get_param('sanding_price_add'));
        }
        if ($request->get_param('sort_order') !== null) {
            $data['sort_order'] = (int) $request->get_param('sort_order');
        }
        if ($request->get_param('is_active') !== null) {
            $data['is_active'] = (bool) $request->get_param('is_active');
        }
        
        $result = $wpdb->update($table, $data, array('id' => $id));
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to update level of service', array('status' => 500));
        }
        
        return new WP_REST_Response(array('message' => 'Level of service updated successfully'), 200);
    }
    
    public function delete_los($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_los';
        $id = (int) $request->get_param('id');
        
        $result = $wpdb->delete($table, array('id' => $id));
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to delete level of service', array('status' => 500));
        }
        
        return new WP_REST_Response(array('message' => 'Level of service deleted successfully'), 200);
    }
    
    // Admin API methods - Vehicle Subtypes
    public function get_vehicle_subtypes($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_vehicle_subtypes';
        
        $page = $request->get_param('page') ?: 1;
        $per_page = $request->get_param('per_page') ?: 10;
        $search = $request->get_param('search');
        $vehicle_type_id = $request->get_param('vehicle_type_id');
        
        $where_conditions = array();
        $where_values = array();
        
        if ($search) {
            $where_conditions[] = "vs.name LIKE %s";
            $where_values[] = '%' . $wpdb->esc_like($search) . '%';
        }
        
        if ($vehicle_type_id) {
            $where_conditions[] = "vs.vehicle_type_id = %d";
            $where_values[] = (int) $vehicle_type_id;
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        $offset = ($page - 1) * $per_page;
        
        $query = "SELECT vs.*, vt.name as vehicle_type_name 
                  FROM $table vs 
                  LEFT JOIN {$wpdb->prefix}carwash_vehicle_types vt ON vs.vehicle_type_id = vt.id 
                  $where_clause 
                  ORDER BY vs.sort_order ASC, vs.name ASC 
                  LIMIT %d OFFSET %d";
        $where_values[] = $per_page;
        $where_values[] = $offset;
        
        $results = $wpdb->get_results($wpdb->prepare($query, $where_values));
        
        // Get total count
        $count_query = "SELECT COUNT(*) FROM $table vs $where_clause";
        $total = $wpdb->get_var($wpdb->prepare($count_query, array_slice($where_values, 0, -2)));
        
        return new WP_REST_Response(array(
            'data' => $results,
            'total' => (int) $total,
            'page' => (int) $page,
            'per_page' => (int) $per_page
        ), 200);
    }
    
    public function create_vehicle_subtype($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_vehicle_subtypes';
        
        $data = array(
            'vehicle_type_id' => (int) $request->get_param('vehicle_type_id'),
            'name' => sanitize_text_field($request->get_param('name')),
            'slug' => sanitize_title($request->get_param('slug')),
            'description' => sanitize_textarea_field($request->get_param('description')),
            'image_url' => esc_url_raw($request->get_param('image_url')),
            'sort_order' => (int) $request->get_param('sort_order')
        );
        
        $result = $wpdb->insert($table, $data);
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to create vehicle subtype', array('status' => 500));
        }
        
        $data['id'] = $wpdb->insert_id;
        return new WP_REST_Response($data, 201);
    }
    
    public function update_vehicle_subtype($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_vehicle_subtypes';
        $id = (int) $request->get_param('id');
        
        $data = array();
        if ($request->get_param('vehicle_type_id')) {
            $data['vehicle_type_id'] = (int) $request->get_param('vehicle_type_id');
        }
        if ($request->get_param('name')) {
            $data['name'] = sanitize_text_field($request->get_param('name'));
        }
        if ($request->get_param('slug')) {
            $data['slug'] = sanitize_title($request->get_param('slug'));
        }
        if ($request->get_param('description') !== null) {
            $data['description'] = sanitize_textarea_field($request->get_param('description'));
        }
        if ($request->get_param('image_url') !== null) {
            $data['image_url'] = esc_url_raw($request->get_param('image_url'));
        }
        if ($request->get_param('sort_order') !== null) {
            $data['sort_order'] = (int) $request->get_param('sort_order');
        }
        if ($request->get_param('is_active') !== null) {
            $data['is_active'] = (bool) $request->get_param('is_active');
        }
        
        $result = $wpdb->update($table, $data, array('id' => $id));
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to update vehicle subtype', array('status' => 500));
        }
        
        return new WP_REST_Response(array('message' => 'Vehicle subtype updated successfully'), 200);
    }
    
    public function delete_vehicle_subtype($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_vehicle_subtypes';
        $id = (int) $request->get_param('id');
        
        $result = $wpdb->delete($table, array('id' => $id));
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to delete vehicle subtype', array('status' => 500));
        }
        
        return new WP_REST_Response(array('message' => 'Vehicle subtype deleted successfully'), 200);
    }
    
    // Admin API methods - Bookings
    public function get_bookings($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_bookings';
        
        $page = $request->get_param('page') ?: 1;
        $per_page = $request->get_param('per_page') ?: 10;
        $search = $request->get_param('search');
        $status = $request->get_param('status');
        
        $where_conditions = array();
        $where_values = array();
        
        if ($search) {
            $where_conditions[] = "(b.booking_number LIKE %s OR b.customer_name LIKE %s OR b.customer_email LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }
        
        if ($status) {
            $where_conditions[] = "b.booking_status = %s";
            $where_values[] = $status;
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        $offset = ($page - 1) * $per_page;
        
        $query = "SELECT b.*, vt.name as vehicle_type_name, vs.name as vehicle_subtype_name, 
                         s.name as service_name, l.name as los_name 
                  FROM $table b 
                  LEFT JOIN {$wpdb->prefix}carwash_vehicle_types vt ON b.vehicle_type_id = vt.id 
                  LEFT JOIN {$wpdb->prefix}carwash_vehicle_subtypes vs ON b.vehicle_subtype_id = vs.id 
                  LEFT JOIN {$wpdb->prefix}carwash_services s ON b.service_id = s.id 
                  LEFT JOIN {$wpdb->prefix}carwash_los l ON b.los_id = l.id 
                  $where_clause 
                  ORDER BY b.created_at DESC 
                  LIMIT %d OFFSET %d";
        $where_values[] = $per_page;
        $where_values[] = $offset;
        
        $results = $wpdb->get_results($wpdb->prepare($query, $where_values));
        
        // Get total count
        $count_query = "SELECT COUNT(*) FROM $table b $where_clause";
        $total = $wpdb->get_var($wpdb->prepare($count_query, array_slice($where_values, 0, -2)));
        
        return new WP_REST_Response(array(
            'data' => $results,
            'total' => (int) $total,
            'page' => (int) $page,
            'per_page' => (int) $per_page
        ), 200);
    }
    
    public function get_booking($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_bookings';
        $id = (int) $request->get_param('id');
        
        $query = "SELECT b.*, vt.name as vehicle_type_name, vs.name as vehicle_subtype_name, 
                         s.name as service_name, l.name as los_name 
                  FROM $table b 
                  LEFT JOIN {$wpdb->prefix}carwash_vehicle_types vt ON b.vehicle_type_id = vt.id 
                  LEFT JOIN {$wpdb->prefix}carwash_vehicle_subtypes vs ON b.vehicle_subtype_id = vs.id 
                  LEFT JOIN {$wpdb->prefix}carwash_services s ON b.service_id = s.id 
                  LEFT JOIN {$wpdb->prefix}carwash_los l ON b.los_id = l.id 
                  WHERE b.id = %d";
        
        $result = $wpdb->get_row($wpdb->prepare($query, $id));
        
        if (!$result) {
            return new WP_Error('not_found', 'Booking not found', array('status' => 404));
        }
        
        return new WP_REST_Response($result, 200);
    }
    
    public function update_booking_status($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_bookings';
        $id = (int) $request->get_param('id');
        $status = sanitize_text_field($request->get_param('status'));
        
        $valid_statuses = array('pending', 'confirmed', 'in_progress', 'completed', 'cancelled');
        if (!in_array($status, $valid_statuses)) {
            return new WP_Error('invalid_status', 'Invalid booking status', array('status' => 400));
        }
        
        $result = $wpdb->update($table, array('booking_status' => $status), array('id' => $id));
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to update booking status', array('status' => 500));
        }
        
        return new WP_REST_Response(array('message' => 'Booking status updated successfully'), 200);
    }
    
    // Public API methods
    public function get_public_vehicle_types($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_vehicle_types';
        
        $results = $wpdb->get_results("SELECT * FROM $table WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
        
        return new WP_REST_Response($results, 200);
    }
    
    public function get_public_vehicle_subtypes($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_vehicle_subtypes';
        $vehicle_type_id = (int) $request->get_param('vehicle_type_id');
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE vehicle_type_id = %d AND is_active = 1 ORDER BY sort_order ASC, name ASC",
            $vehicle_type_id
        ));
        
        return new WP_REST_Response($results, 200);
    }
    
    public function get_public_services($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_services';
        $vehicle_type_id = (int) $request->get_param('vehicle_type_id');
        $subtype_id = $request->get_param('subtype_id') ? (int) $request->get_param('subtype_id') : null;
        
        $where_conditions = array('vehicle_type_id = %d', 'is_active = 1');
        $where_values = array($vehicle_type_id);
        
        if ($subtype_id) {
            $where_conditions[] = '(vehicle_subtype_id = %d OR vehicle_subtype_id IS NULL)';
            $where_values[] = $subtype_id;
        } else {
            $where_conditions[] = 'vehicle_subtype_id IS NULL';
        }
        
        $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table $where_clause ORDER BY sort_order ASC, name ASC",
            $where_values
        ));
        
        return new WP_REST_Response($results, 200);
    }
    
    public function get_public_los($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'carwash_los';
        $vehicle_type_id = (int) $request->get_param('vehicle_type_id');
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE vehicle_type_id = %d AND is_active = 1 ORDER BY sort_order ASC, name ASC",
            $vehicle_type_id
        ));
        
        return new WP_REST_Response($results, 200);
    }
    
    public function calculate_price($request) {
        global $wpdb;
        
        $service_id = (int) $request->get_param('service_id');
        $los_id = (int) $request->get_param('los_id');
        $include_sanding = (bool) $request->get_param('include_sanding');
        
        // Get service details
        $service = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}carwash_services WHERE id = %d",
            $service_id
        ));
        
        if (!$service) {
            return new WP_Error('service_not_found', 'Service not found', array('status' => 404));
        }
        
        // Get LOS details
        $los = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}carwash_los WHERE id = %d",
            $los_id
        ));
        
        if (!$los) {
            return new WP_Error('los_not_found', 'Level of service not found', array('status' => 404));
        }
        
        // Calculate total price
        $base_price = floatval($service->base_price);
        $modifier = floatval($los->price_modifier);
        $sanding_price = $include_sanding && $los->has_sanding_option ? floatval($los->sanding_price_add) : 0;
        
        $total_price = ($base_price * $modifier) + $sanding_price;
        
        return new WP_REST_Response(array(
            'base_price' => $base_price,
            'price_modifier' => $modifier,
            'sanding_price' => $sanding_price,
            'total_price' => $total_price
        ), 200);
    }
    
    public function submit_booking($request) {
        global $wpdb;
        
        // Generate booking number
        $booking_number = 'CW' . date('Y') . str_pad(wp_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Ensure booking number is unique
        $table = $wpdb->prefix . 'carwash_bookings';
        while ($wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE booking_number = %s", $booking_number))) {
            $booking_number = 'CW' . date('Y') . str_pad(wp_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        }
        
        // Calculate total price
        $price_calculation = $this->calculate_price($request);
        if (is_wp_error($price_calculation)) {
            return $price_calculation;
        }
        $total_price = $price_calculation->data['total_price'];
        
        $booking_data = array(
            'booking_number' => $booking_number,
            'customer_name' => sanitize_text_field($request->get_param('customer_name')),
            'customer_email' => sanitize_email($request->get_param('customer_email')),
            'customer_phone' => sanitize_text_field($request->get_param('customer_phone')),
            'customer_address' => sanitize_textarea_field($request->get_param('customer_address')),
            'vehicle_number' => sanitize_text_field($request->get_param('vehicle_number')),
            'vehicle_model' => sanitize_text_field($request->get_param('vehicle_model')),
            'vehicle_type_id' => (int) $request->get_param('vehicle_type_id'),
            'vehicle_subtype_id' => $request->get_param('vehicle_subtype_id') ? (int) $request->get_param('vehicle_subtype_id') : null,
            'service_id' => (int) $request->get_param('service_id'),
            'los_id' => (int) $request->get_param('los_id'),
            'include_sanding' => (bool) $request->get_param('include_sanding'),
            'total_price' => $total_price,
            'preferred_date' => sanitize_text_field($request->get_param('preferred_date')),
            'preferred_time' => sanitize_text_field($request->get_param('preferred_time')),
            'special_instructions' => sanitize_textarea_field($request->get_param('special_instructions'))
        );
        
        $result = $wpdb->insert($table, $booking_data);
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to create booking', array('status' => 500));
        }
        
        $booking_id = $wpdb->insert_id;
        
        // Send confirmation email (if email class exists)
        $email_sent = false;
        if (class_exists('CarWash_Email')) {
            $email_sent = CarWash_Email::send_booking_confirmation($booking_id);
        }
        
        return new WP_REST_Response(array(
            'booking_number' => $booking_number,
            'booking_id' => $booking_id,
            'total_price' => $total_price,
            'email_sent' => $email_sent
        ), 201);
    }
    
    public function get_booking_receipt($request) {
        global $wpdb;
        $booking_number = sanitize_text_field($request->get_param('booking_number'));
        
        $table = $wpdb->prefix . 'carwash_bookings';
        $booking = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE booking_number = %s", $booking_number));
        
        if (!$booking) {
            return new WP_Error('booking_not_found', 'Booking not found', array('status' => 404));
        }
        
        // Get related data
        $vehicle_type = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}carwash_vehicle_types WHERE id = %d", $booking->vehicle_type_id));
        $vehicle_subtype = $booking->vehicle_subtype_id ? $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}carwash_vehicle_subtypes WHERE id = %d", $booking->vehicle_subtype_id)) : '';
        $service = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}carwash_services WHERE id = %d", $booking->service_id));
        $los = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}carwash_los WHERE id = %d", $booking->los_id));
        
        $receipt_html = '';
        if (class_exists('CarWash_Email')) {
            $receipt_html = CarWash_Email::generate_receipt_html($booking, $vehicle_type, $vehicle_subtype, $service, $los);
        }
        
        return new WP_REST_Response(array('receipt_html' => $receipt_html), 200);
    }
}

