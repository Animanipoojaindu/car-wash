jQuery(document).ready(function($) {
    'use strict';
    
    // Global variables
    window.CarWashAdmin = {
        data: {
            vehicleTypes: [],
            services: [],
            los: [],
            bookings: []
        },
        
        // Initialize admin functionality
        init: function() {
            this.bindEvents();
            this.loadInitialData();
        },
        
        // Bind event handlers
        bindEvents: function() {
            // Modal close events
            $(document).on('click', '.carwash-modal-close', this.closeModal);
            $(document).on('click', '.carwash-modal', function(e) {
                if (e.target === this) {
                    CarWashAdmin.closeModal();
                }
            });
            
            // Form submission events
            $(document).on('submit', '.carwash-form', this.handleFormSubmit);
            
            // Delete confirmation events
            $(document).on('click', '.delete-item', this.confirmDelete);
            
            // Status update events
            $(document).on('change', '.status-select', this.updateStatus);
            
            // Search and filter events
            $(document).on('input', '.search-input', this.debounce(this.handleSearch, 300));
            $(document).on('change', '.filter-select', this.handleFilter);
            
            // Pagination events
            $(document).on('click', '.pagination-link', this.handlePagination);
        },
        
        // Load initial data based on current page
        loadInitialData: function() {
            const page = this.getCurrentPage();
            
            switch(page) {
                case 'carwash-vehicle-types':
                    this.loadVehicleTypes();
                    break;
                case 'carwash-services':
                    this.loadServices();
                    break;
                case 'carwash-los':
                    this.loadLOS();
                    break;
                case 'carwash-bookings':
                    this.loadBookings();
                    break;
            }
        },
        
        // Get current admin page
        getCurrentPage: function() {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('page');
        },
        
        // API request helper
        apiRequest: function(endpoint, method, data, callback) {
            const settings = {
                url: carwash_admin.rest_url + endpoint,
                method: method || 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', carwash_admin.rest_nonce);
                },
                success: callback || function() {},
                error: function(xhr) {
                    console.error('API Error:', xhr);
                    CarWashAdmin.showNotification('Error: ' + (xhr.responseJSON?.message || xhr.responseText), 'error');
                }
            };
            
            if (data && (method === 'POST' || method === 'PUT')) {
                settings.data = JSON.stringify(data);
                settings.contentType = 'application/json';
            }
            
            $.ajax(settings);
        },
        
        // Load vehicle types
        loadVehicleTypes: function() {
            this.showLoading('#vehicle-types-list');
            
            this.apiRequest('vehicle-types', 'GET', null, function(response) {
                CarWashAdmin.data.vehicleTypes = response.data || response;
                CarWashAdmin.renderVehicleTypes();
            });
        },
        
        // Render vehicle types table
        renderVehicleTypes: function() {
            const types = this.data.vehicleTypes;
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
            
            if (types.length === 0) {
                html += '<tr><td colspan="6">No vehicle types found.</td></tr>';
            } else {
                types.forEach(function(type) {
                    html += `
                        <tr>
                            <td><strong>${type.name}</strong></td>
                            <td><code>${type.slug}</code></td>
                            <td>${type.description || '<em>No description</em>'}</td>
                            <td>${type.sort_order}</td>
                            <td>
                                <span class="status-badge ${type.is_active ? 'status-active' : 'status-inactive'}">
                                    ${type.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </td>
                            <td>
                                <button class="button button-small edit-vehicle-type" data-id="${type.id}">Edit</button>
                                <button class="button button-small manage-subtypes" data-id="${type.id}" data-name="${type.name}">Sub-types</button>
                                <button class="button button-small delete-item" data-type="vehicle-type" data-id="${type.id}">Delete</button>
                            </td>
                        </tr>
                    `;
                });
            }
            
            html += '</tbody></table>';
            $('#vehicle-types-list').html(html);
        },
        
        // Load services
        loadServices: function() {
            this.showLoading('#services-list');
            
            this.apiRequest('services', 'GET', null, function(response) {
                CarWashAdmin.data.services = response.data || response;
                CarWashAdmin.renderServices();
            });
        },
        
        // Load levels of service
        loadLOS: function() {
            this.showLoading('#los-list');
            
            this.apiRequest('los', 'GET', null, function(response) {
                CarWashAdmin.data.los = response.data || response;
                CarWashAdmin.renderLOS();
            });
        },
        
        // Load bookings
        loadBookings: function(page, filters) {
            this.showLoading('#bookings-list');
            
            let endpoint = 'bookings';
            const params = new URLSearchParams();
            
            if (page) params.append('page', page);
            if (filters) {
                Object.keys(filters).forEach(key => {
                    if (filters[key]) params.append(key, filters[key]);
                });
            }
            
            if (params.toString()) {
                endpoint += '?' + params.toString();
            }
            
            this.apiRequest(endpoint, 'GET', null, function(response) {
                CarWashAdmin.data.bookings = response.data || response;
                CarWashAdmin.renderBookings(response);
            });
        },
        
        // Show loading indicator
        showLoading: function(selector) {
            $(selector).html('<div class="carwash-loading">Loading...</div>');
        },
        
        // Show notification
        showNotification: function(message, type) {
            type = type || 'success';
            const className = type === 'error' ? 'carwash-error' : 'carwash-success';
            
            const notification = $(`<div class="${className}">${message}</div>`);
            $('.wrap h1').after(notification);
            
            setTimeout(function() {
                notification.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        },
        
        // Close modal
        closeModal: function() {
            $('.carwash-modal').hide();
        },
        
        // Handle form submission
        handleFormSubmit: function(e) {
            e.preventDefault();
            
            const form = $(this);
            const formData = new FormData(this);
            const data = {};
            
            // Convert FormData to object
            for (let [key, value] of formData.entries()) {
                if (key === 'is_active') {
                    data[key] = value === '1';
                } else if (key === 'sort_order' || key === 'price_modifier' || key === 'base_price') {
                    data[key] = parseFloat(value) || 0;
                } else {
                    data[key] = value;
                }
            }
            
            // Determine endpoint and method
            const id = data.id;
            const isEdit = id && id !== '';
            const formType = form.data('type') || 'vehicle-type';
            
            let endpoint = '';
            switch(formType) {
                case 'vehicle-type':
                    endpoint = 'vehicle-types';
                    break;
                case 'service':
                    endpoint = 'services';
                    break;
                case 'los':
                    endpoint = 'los';
                    break;
            }
            
            if (isEdit) {
                endpoint += '/' + id;
            }
            
            const method = isEdit ? 'PUT' : 'POST';
            
            // Remove id from data for POST requests
            if (!isEdit) {
                delete data.id;
            }
            
            CarWashAdmin.apiRequest(endpoint, method, data, function(response) {
                CarWashAdmin.closeModal();
                CarWashAdmin.showNotification(
                    isEdit ? 'Item updated successfully!' : 'Item created successfully!'
                );
                
                // Reload appropriate data
                switch(formType) {
                    case 'vehicle-type':
                        CarWashAdmin.loadVehicleTypes();
                        break;
                    case 'service':
                        CarWashAdmin.loadServices();
                        break;
                    case 'los':
                        CarWashAdmin.loadLOS();
                        break;
                }
            });
        },
        
        // Confirm delete action
        confirmDelete: function(e) {
            e.preventDefault();
            
            const button = $(this);
            const type = button.data('type');
            const id = button.data('id');
            const name = button.data('name') || 'this item';
            
            if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
                CarWashAdmin.deleteItem(type, id);
            }
        },
        
        // Delete item
        deleteItem: function(type, id) {
            let endpoint = '';
            switch(type) {
                case 'vehicle-type':
                    endpoint = 'vehicle-types';
                    break;
                case 'service':
                    endpoint = 'services';
                    break;
                case 'los':
                    endpoint = 'los';
                    break;
                case 'booking':
                    endpoint = 'bookings';
                    break;
            }
            
            this.apiRequest(endpoint + '/' + id, 'DELETE', null, function(response) {
                CarWashAdmin.showNotification('Item deleted successfully!');
                
                // Reload appropriate data
                switch(type) {
                    case 'vehicle-type':
                        CarWashAdmin.loadVehicleTypes();
                        break;
                    case 'service':
                        CarWashAdmin.loadServices();
                        break;
                    case 'los':
                        CarWashAdmin.loadLOS();
                        break;
                    case 'booking':
                        CarWashAdmin.loadBookings();
                        break;
                }
            });
        },
        
        // Update status
        updateStatus: function() {
            const select = $(this);
            const bookingId = select.data('booking-id');
            const newStatus = select.val();
            
            CarWashAdmin.apiRequest('bookings/' + bookingId + '/status', 'PUT', {
                status: newStatus
            }, function(response) {
                CarWashAdmin.showNotification('Booking status updated successfully!');
            });
        },
        
        // Handle search
        handleSearch: function() {
            const searchTerm = $(this).val();
            const currentPage = CarWashAdmin.getCurrentPage();
            
            // Implement search based on current page
            switch(currentPage) {
                case 'carwash-bookings':
                    CarWashAdmin.loadBookings(1, { search: searchTerm });
                    break;
                // Add other pages as needed
            }
        },
        
        // Handle filter
        handleFilter: function() {
            const filter = $(this);
            const filterType = filter.data('filter');
            const filterValue = filter.val();
            const currentPage = CarWashAdmin.getCurrentPage();
            
            const filters = {};
            filters[filterType] = filterValue;
            
            switch(currentPage) {
                case 'carwash-bookings':
                    CarWashAdmin.loadBookings(1, filters);
                    break;
            }
        },
        
        // Handle pagination
        handlePagination: function(e) {
            e.preventDefault();
            
            const link = $(this);
            const page = link.data('page');
            const currentPage = CarWashAdmin.getCurrentPage();
            
            switch(currentPage) {
                case 'carwash-bookings':
                    CarWashAdmin.loadBookings(page);
                    break;
            }
        },
        
        // Debounce function
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = function() {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },
        
        // Format currency
        formatCurrency: function(amount) {
            return '$' + parseFloat(amount).toFixed(2);
        },
        
        // Format date
        formatDate: function(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        },
        
        // Format time
        formatTime: function(timeString) {
            if (!timeString) return '';
            const time = new Date('2000-01-01 ' + timeString);
            return time.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
        }
    };
    
    // Initialize admin functionality
    CarWashAdmin.init();
    
    // Make CarWashAdmin globally available
    window.CarWashAdmin = CarWashAdmin;
});

