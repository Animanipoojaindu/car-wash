<div class="wrap">
    <h1>Levels of Service Management</h1>
    
    <div class="carwash-admin-header">
        <button id="add-los" class="button button-primary">Add New Level of Service</button>
    </div>
    
    <div id="los-list">
        <!-- LOS will be loaded here via AJAX -->
    </div>
    
    <!-- Add/Edit LOS Modal -->
    <div id="los-modal" class="carwash-modal" style="display: none;">
        <div class="carwash-modal-content">
            <div class="carwash-modal-header">
                <h2 id="los-modal-title">Add Level of Service</h2>
                <span class="carwash-modal-close">&times;</span>
            </div>
            
            <form id="los-form" data-type="los">
                <input type="hidden" id="los-id" name="id">
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="los-vehicle-type">Vehicle Type *</label>
                        </th>
                        <td>
                            <select id="los-vehicle-type" name="vehicle_type_id" required>
                                <option value="">Select Vehicle Type</option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="los-name">LOS Name *</label>
                        </th>
                        <td>
                            <input type="text" id="los-name" name="name" class="regular-text" required>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="los-slug">Slug *</label>
                        </th>
                        <td>
                            <input type="text" id="los-slug" name="slug" class="regular-text" required>
                            <p class="description">URL-friendly version of the name.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="los-description">Description</label>
                        </th>
                        <td>
                            <textarea id="los-description" name="description" rows="3" class="large-text"></textarea>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="los-price-modifier">Price Modifier *</label>
                        </th>
                        <td>
                            <input type="number" id="los-price-modifier" name="price_modifier" step="0.01" min="0" value="1.00" class="regular-text" required>
                            <p class="description">Multiplier for the base service price (e.g., 1.5 = 150% of base price).</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="los-has-sanding">Sanding Option</label>
                        </th>
                        <td>
                            <input type="checkbox" id="los-has-sanding" name="has_sanding_option" value="1">
                            <label for="los-has-sanding">Offer sanding as an add-on service</label>
                        </td>
                    </tr>
                    
                    <tr id="sanding-price-row" style="display: none;">
                        <th scope="row">
                            <label for="los-sanding-price">Sanding Price Add-on</label>
                        </th>
                        <td>
                            <input type="number" id="los-sanding-price" name="sanding_price_add" step="0.01" min="0" value="0.00" class="regular-text">
                            <p class="description">Additional price for sanding service.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="los-sort">Sort Order</label>
                        </th>
                        <td>
                            <input type="number" id="los-sort" name="sort_order" value="0" min="0" class="small-text">
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="los-active">Active</label>
                        </th>
                        <td>
                            <input type="checkbox" id="los-active" name="is_active" value="1" checked>
                            <label for="los-active">Enable this level of service</label>
                        </td>
                    </tr>
                </table>
                
                <div class="carwash-modal-footer">
                    <button type="submit" class="button button-primary">Save Level of Service</button>
                    <button type="button" class="button carwash-modal-close">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let losData = [];
    let vehicleTypes = [];
    
    // Load initial data
    function loadData() {
        loadVehicleTypes();
        loadLOS();
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
        $('#los-vehicle-type').html(options);
    }
    
    // Load LOS
    function loadLOS() {
        $.ajax({
            url: carwash_admin.rest_url + 'los',
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', carwash_admin.rest_nonce);
            },
            success: function(response) {
                losData = response.data || response;
                renderLOS();
            },
            error: function(xhr) {
                alert('Error loading levels of service: ' + xhr.responseText);
            }
        });
    }
    
    // Render LOS table
    function renderLOS() {
        let html = `
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Vehicle Type</th>
                        <th>Price Modifier</th>
                        <th>Sanding Option</th>
                        <th>Sort Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        if (losData.length === 0) {
            html += '<tr><td colspan="7">No levels of service found.</td></tr>';
        } else {
            losData.forEach(function(los) {
                const vehicleType = vehicleTypes.find(vt => vt.id == los.vehicle_type_id);
                html += `
                    <tr>
                        <td><strong>${los.name}</strong></td>
                        <td>${vehicleType ? vehicleType.name : 'Unknown'}</td>
                        <td>${parseFloat(los.price_modifier).toFixed(2)}x</td>
                        <td>
                            ${los.has_sanding_option ? 
                                `Yes (+$${parseFloat(los.sanding_price_add).toFixed(2)})` : 
                                'No'
                            }
                        </td>
                        <td>${los.sort_order}</td>
                        <td>
                            <span class="status-badge ${los.is_active ? 'status-active' : 'status-inactive'}">
                                ${los.is_active ? 'Active' : 'Inactive'}
                            </span>
                        </td>
                        <td>
                            <button class="button button-small edit-los" data-id="${los.id}">Edit</button>
                            <button class="button button-small delete-item" data-type="los" data-id="${los.id}">Delete</button>
                        </td>
                    </tr>
                `;
            });
        }
        
        html += '</tbody></table>';
        $('#los-list').html(html);
    }
    
    // Add new LOS
    $('#add-los').click(function() {
        $('#los-modal-title').text('Add Level of Service');
        $('#los-form')[0].reset();
        $('#los-id').val('');
        $('#los-active').prop('checked', true);
        $('#los-price-modifier').val('1.00');
        $('#sanding-price-row').hide();
        populateVehicleTypeDropdown();
        $('#los-modal').show();
    });
    
    // Edit LOS
    $(document).on('click', '.edit-los', function() {
        const id = $(this).data('id');
        const los = losData.find(l => l.id == id);
        
        if (los) {
            $('#los-modal-title').text('Edit Level of Service');
            $('#los-id').val(los.id);
            $('#los-name').val(los.name);
            $('#los-slug').val(los.slug);
            $('#los-description').val(los.description || '');
            $('#los-price-modifier').val(los.price_modifier);
            $('#los-has-sanding').prop('checked', los.has_sanding_option);
            $('#los-sanding-price').val(los.sanding_price_add || '0.00');
            $('#los-sort').val(los.sort_order);
            $('#los-active').prop('checked', los.is_active);
            
            populateVehicleTypeDropdown();
            $('#los-vehicle-type').val(los.vehicle_type_id);
            
            if (los.has_sanding_option) {
                $('#sanding-price-row').show();
            }
            
            $('#los-modal').show();
        }
    });
    
    // Toggle sanding price field
    $('#los-has-sanding').on('change', function() {
        if ($(this).is(':checked')) {
            $('#sanding-price-row').show();
        } else {
            $('#sanding-price-row').hide();
            $('#los-sanding-price').val('0.00');
        }
    });
    
    // Auto-generate slug from name
    $('#los-name').on('input', function() {
        if (!$('#los-id').val()) { // Only for new items
            const slug = $(this).val().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
            $('#los-slug').val(slug);
        }
    });
    
    // Load initial data
    loadData();
});
</script>

