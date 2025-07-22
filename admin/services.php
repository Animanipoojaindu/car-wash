<div class="wrap">
    <h1>Services Management</h1>
    
    <div class="carwash-admin-header">
        <button id="add-service" class="button button-primary">Add New Service</button>
    </div>
    
    <div id="services-list">
        <!-- Services will be loaded here via AJAX -->
    </div>
    
    <!-- Add/Edit Service Modal -->
    <div id="service-modal" class="carwash-modal" style="display: none;">
        <div class="carwash-modal-content">
            <div class="carwash-modal-header">
                <h2 id="service-modal-title">Add Service</h2>
                <span class="carwash-modal-close">&times;</span>
            </div>
            
            <form id="service-form" data-type="service">
                <input type="hidden" id="service-id" name="id">
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="service-vehicle-type">Vehicle Type *</label>
                        </th>
                        <td>
                            <select id="service-vehicle-type" name="vehicle_type_id" required>
                                <option value="">Select Vehicle Type</option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="service-vehicle-subtype">Vehicle Sub-type</label>
                        </th>
                        <td>
                            <select id="service-vehicle-subtype" name="vehicle_subtype_id">
                                <option value="">Any Sub-type</option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="service-name">Service Name *</label>
                        </th>
                        <td>
                            <input type="text" id="service-name" name="name" class="regular-text" required>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="service-slug">Slug *</label>
                        </th>
                        <td>
                            <input type="text" id="service-slug" name="slug" class="regular-text" required>
                            <p class="description">URL-friendly version of the name.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="service-description">Description</label>
                        </th>
                        <td>
                            <textarea id="service-description" name="description" rows="3" class="large-text"></textarea>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="service-base-price">Base Price *</label>
                        </th>
                        <td>
                            <input type="number" id="service-base-price" name="base_price" step="0.01" min="0" class="regular-text" required>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="service-sort">Sort Order</label>
                        </th>
                        <td>
                            <input type="number" id="service-sort" name="sort_order" value="0" min="0" class="small-text">
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="service-active">Active</label>
                        </th>
                        <td>
                            <input type="checkbox" id="service-active" name="is_active" value="1" checked>
                            <label for="service-active">Enable this service</label>
                        </td>
                    </tr>
                </table>
                
                <div class="carwash-modal-footer">
                    <button type="submit" class="button button-primary">Save Service</button>
                    <button type="button" class="button carwash-modal-close">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let services = [];
    let vehicleTypes = [];
    let vehicleSubtypes = [];
    
    // Load initial data
    function loadData() {
        loadVehicleTypes();
        loadServices();
    }
    
    // Load vehicle types for dropdown
    function loadVehicleTypes() {
        $.ajax({
            url: carwash_admin.rest_url + 'vehicle-types',
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', carwash_admin.rest_nonce);
            },
            success: function(response) {
                vehicleTypes = response.data || response;
                populateVehicleTypeDropdown();
            }
        });
    }
    
    // Populate vehicle type dropdown
    function populateVehicleTypeDropdown() {
        let options = '<option value="">Select Vehicle Type</option>';
        vehicleTypes.forEach(function(type) {
            if (type.is_active) {
                options += `<option value="${type.id}">${type.name}</option>`;
            }
        });
        $('#service-vehicle-type').html(options);
    }
    
    // Load vehicle subtypes when vehicle type changes
    $('#service-vehicle-type').on('change', function() {
        const vehicleTypeId = $(this).val();
        if (vehicleTypeId) {
            loadVehicleSubtypes(vehicleTypeId);
        } else {
            $('#service-vehicle-subtype').html('<option value="">Any Sub-type</option>');
        }
    });
    
    // Load vehicle subtypes
    function loadVehicleSubtypes(vehicleTypeId) {
        $.ajax({
            url: carwash_admin.rest_url.replace('/admin/', '/') + 'vehicle-subtypes/' + vehicleTypeId,
            method: 'GET',
            success: function(response) {
                vehicleSubtypes = response;
                populateVehicleSubtypeDropdown();
            }
        });
    }
    
    // Populate vehicle subtype dropdown
    function populateVehicleSubtypeDropdown() {
        let options = '<option value="">Any Sub-type</option>';
        vehicleSubtypes.forEach(function(subtype) {
            options += `<option value="${subtype.id}">${subtype.name}</option>`;
        });
        $('#service-vehicle-subtype').html(options);
    }
    
    // Load services
    function loadServices() {
        $.ajax({
            url: carwash_admin.rest_url + 'services',
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', carwash_admin.rest_nonce);
            },
            success: function(response) {
                services = response.data || response;
                renderServices();
            },
            error: function(xhr) {
                alert('Error loading services: ' + xhr.responseText);
            }
        });
    }
    
    // Render services table
    function renderServices() {
        let html = `
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Vehicle Type</th>
                        <th>Sub-type</th>
                        <th>Base Price</th>
                        <th>Sort Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        if (services.length === 0) {
            html += '<tr><td colspan="7">No services found.</td></tr>';
        } else {
            services.forEach(function(service) {
                const vehicleType = vehicleTypes.find(vt => vt.id == service.vehicle_type_id);
                html += `
                    <tr>
                        <td><strong>${service.name}</strong></td>
                        <td>${vehicleType ? vehicleType.name : 'Unknown'}</td>
                        <td>${service.vehicle_subtype_name || 'Any'}</td>
                        <td class="price-display">$${parseFloat(service.base_price).toFixed(2)}</td>
                        <td>${service.sort_order}</td>
                        <td>
                            <span class="status-badge ${service.is_active ? 'status-active' : 'status-inactive'}">
                                ${service.is_active ? 'Active' : 'Inactive'}
                            </span>
                        </td>
                        <td>
                            <button class="button button-small edit-service" data-id="${service.id}">Edit</button>
                            <button class="button button-small delete-item" data-type="service" data-id="${service.id}">Delete</button>
                        </td>
                    </tr>
                `;
            });
        }
        
        html += '</tbody></table>';
        $('#services-list').html(html);
    }
    
    // Add new service
    $('#add-service').click(function() {
        $('#service-modal-title').text('Add Service');
        $('#service-form')[0].reset();
        $('#service-id').val('');
        $('#service-active').prop('checked', true);
        populateVehicleTypeDropdown();
        $('#service-modal').show();
    });
    
    // Edit service
    $(document).on('click', '.edit-service', function() {
        const id = $(this).data('id');
        const service = services.find(s => s.id == id);
        
        if (service) {
            $('#service-modal-title').text('Edit Service');
            $('#service-id').val(service.id);
            $('#service-name').val(service.name);
            $('#service-slug').val(service.slug);
            $('#service-description').val(service.description || '');
            $('#service-base-price').val(service.base_price);
            $('#service-sort').val(service.sort_order);
            $('#service-active').prop('checked', service.is_active);
            
            populateVehicleTypeDropdown();
            $('#service-vehicle-type').val(service.vehicle_type_id);
            
            if (service.vehicle_type_id) {
                loadVehicleSubtypes(service.vehicle_type_id);
                setTimeout(function() {
                    $('#service-vehicle-subtype').val(service.vehicle_subtype_id || '');
                }, 500);
            }
            
            $('#service-modal').show();
        }
    });
    
    // Auto-generate slug from name
    $('#service-name').on('input', function() {
        if (!$('#service-id').val()) { // Only for new items
            const slug = $(this).val().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
            $('#service-slug').val(slug);
        }
    });
    
    // Load initial data
    loadData();
});
</script>

