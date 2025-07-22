<div class="wrap">
    <h1>Vehicle Types Management</h1>
    
    <div class="carwash-admin-header">
        <button id="add-vehicle-type" class="button button-primary">Add New Vehicle Type</button>
    </div>
    
    <div id="vehicle-types-list">
        <!-- Vehicle types will be loaded here via AJAX -->
    </div>
    
    <!-- Add/Edit Vehicle Type Modal -->
    <div id="vehicle-type-modal" class="carwash-modal" style="display: none;">
        <div class="carwash-modal-content">
            <div class="carwash-modal-header">
                <h2 id="modal-title">Add Vehicle Type</h2>
                <span class="carwash-modal-close">&times;</span>
            </div>
            
            <form id="vehicle-type-form">
                <input type="hidden" id="vehicle-type-id" name="id">
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="vehicle-type-name">Name *</label>
                        </th>
                        <td>
                            <input type="text" id="vehicle-type-name" name="name" class="regular-text" required>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="vehicle-type-slug">Slug *</label>
                        </th>
                        <td>
                            <input type="text" id="vehicle-type-slug" name="slug" class="regular-text" required>
                            <p class="description">URL-friendly version of the name. Will be auto-generated if left empty.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="vehicle-type-description">Description</label>
                        </th>
                        <td>
                            <textarea id="vehicle-type-description" name="description" rows="3" class="large-text"></textarea>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="vehicle-type-image">Image URL</label>
                        </th>
                        <td>
                            <input type="url" id="vehicle-type-image" name="image_url" class="regular-text">
                            <button type="button" id="upload-image" class="button">Upload Image</button>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="vehicle-type-sort">Sort Order</label>
                        </th>
                        <td>
                            <input type="number" id="vehicle-type-sort" name="sort_order" value="0" min="0" class="small-text">
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="vehicle-type-active">Active</label>
                        </th>
                        <td>
                            <input type="checkbox" id="vehicle-type-active" name="is_active" value="1" checked>
                            <label for="vehicle-type-active">Enable this vehicle type</label>
                        </td>
                    </tr>
                </table>
                
                <div class="carwash-modal-footer">
                    <button type="submit" class="button button-primary">Save Vehicle Type</button>
                    <button type="button" class="button carwash-modal-close">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Sub-types Management Modal -->
    <div id="subtypes-modal" class="carwash-modal" style="display: none;">
        <div class="carwash-modal-content">
            <div class="carwash-modal-header">
                <h2 id="subtypes-modal-title">Manage Sub-types</h2>
                <span class="carwash-modal-close">&times;</span>
            </div>
            
            <div id="subtypes-content">
                <!-- Sub-types content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let vehicleTypes = [];
    
    // Load vehicle types
    function loadVehicleTypes() {
        $.ajax({
            url: carwash_admin.rest_url + 'vehicle-types',
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', carwash_admin.rest_nonce);
            },
            success: function(response) {
                vehicleTypes = response.data;
                renderVehicleTypes();
            },
            error: function(xhr) {
                alert('Error loading vehicle types: ' + xhr.responseText);
            }
        });
    }
    
    // Render vehicle types table
    function renderVehicleTypes() {
        let html = `
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th>Sort Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        if (vehicleTypes.length === 0) {
            html += '<tr><td colspan="6">No vehicle types found.</td></tr>';
        } else {
            vehicleTypes.forEach(function(type) {
                html += `
                    <tr>
                        <td>${type.name}</td>
                        <td>${type.slug}</td>
                        <td>${type.description || ''}</td>
                        <td>${type.sort_order}</td>
                        <td>
                            <span class="status-badge ${type.is_active ? 'status-active' : 'status-inactive'}">
                                ${type.is_active ? 'Active' : 'Inactive'}
                            </span>
                        </td>
                        <td>
                            <button class="button button-small edit-vehicle-type" data-id="${type.id}">Edit</button>
                            <button class="button button-small manage-subtypes" data-id="${type.id}" data-name="${type.name}">Sub-types</button>
                            <button class="button button-small delete-vehicle-type" data-id="${type.id}">Delete</button>
                        </td>
                    </tr>
                `;
            });
        }
        
        html += '</tbody></table>';
        $('#vehicle-types-list').html(html);
    }
    
    // Add new vehicle type
    $('#add-vehicle-type').click(function() {
        $('#modal-title').text('Add Vehicle Type');
        $('#vehicle-type-form')[0].reset();
        $('#vehicle-type-id').val('');
        $('#vehicle-type-active').prop('checked', true);
        $('#vehicle-type-modal').show();
    });
    
    // Edit vehicle type
    $(document).on('click', '.edit-vehicle-type', function() {
        const id = $(this).data('id');
        const type = vehicleTypes.find(t => t.id == id);
        
        if (type) {
            $('#modal-title').text('Edit Vehicle Type');
            $('#vehicle-type-id').val(type.id);
            $('#vehicle-type-name').val(type.name);
            $('#vehicle-type-slug').val(type.slug);
            $('#vehicle-type-description').val(type.description || '');
            $('#vehicle-type-image').val(type.image_url || '');
            $('#vehicle-type-sort').val(type.sort_order);
            $('#vehicle-type-active').prop('checked', type.is_active);
            $('#vehicle-type-modal').show();
        }
    });
    
    // Save vehicle type
    $('#vehicle-type-form').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const id = formData.get('id');
        const isEdit = id && id !== '';
        
        const data = {
            name: formData.get('name'),
            slug: formData.get('slug') || formData.get('name').toLowerCase().replace(/[^a-z0-9]+/g, '-'),
            description: formData.get('description'),
            image_url: formData.get('image_url'),
            sort_order: parseInt(formData.get('sort_order')) || 0,
            is_active: formData.get('is_active') === '1'
        };
        
        $.ajax({
            url: carwash_admin.rest_url + 'vehicle-types' + (isEdit ? '/' + id : ''),
            method: isEdit ? 'PUT' : 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', carwash_admin.rest_nonce);
            },
            success: function(response) {
                $('#vehicle-type-modal').hide();
                loadVehicleTypes();
                alert(isEdit ? 'Vehicle type updated successfully!' : 'Vehicle type created successfully!');
            },
            error: function(xhr) {
                alert('Error saving vehicle type: ' + xhr.responseText);
            }
        });
    });
    
    // Delete vehicle type
    $(document).on('click', '.delete-vehicle-type', function() {
        const id = $(this).data('id');
        
        if (confirm('Are you sure you want to delete this vehicle type?')) {
            $.ajax({
                url: carwash_admin.rest_url + 'vehicle-types/' + id,
                method: 'DELETE',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', carwash_admin.rest_nonce);
                },
                success: function(response) {
                    loadVehicleTypes();
                    alert('Vehicle type deleted successfully!');
                },
                error: function(xhr) {
                    alert('Error deleting vehicle type: ' + xhr.responseText);
                }
            });
        }
    });
    
    // Close modal
    $('.carwash-modal-close').click(function() {
        $(this).closest('.carwash-modal').hide();
    });
    
    // Auto-generate slug from name
    $('#vehicle-type-name').on('input', function() {
        if (!$('#vehicle-type-id').val()) { // Only for new items
            const slug = $(this).val().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
            $('#vehicle-type-slug').val(slug);
        }
    });
    
    // Load initial data
    loadVehicleTypes();
});
</script>

