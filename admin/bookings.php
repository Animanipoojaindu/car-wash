<div class="wrap">
    <h1>Bookings Management</h1>
    
    <div class="carwash-admin-header">
        <div class="carwash-filters">
            <select id="status-filter" class="filter-select" data-filter="status">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
            
            <input type="text" id="search-bookings" class="search-input" placeholder="Search bookings...">
            
            <input type="date" id="date-from" class="filter-select" data-filter="date_from">
            <input type="date" id="date-to" class="filter-select" data-filter="date_to">
        </div>
        
        <div class="carwash-actions">
            <button id="export-bookings" class="button">Export CSV</button>
        </div>
    </div>
    
    <div id="bookings-list">
        <!-- Bookings will be loaded here via AJAX -->
    </div>
    
    <!-- Booking Details Modal -->
    <div id="booking-modal" class="carwash-modal" style="display: none;">
        <div class="carwash-modal-content">
            <div class="carwash-modal-header">
                <h2 id="booking-modal-title">Booking Details</h2>
                <span class="carwash-modal-close">&times;</span>
            </div>
            
            <div id="booking-details-content">
                <!-- Booking details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let bookings = [];
    let currentPage = 1;
    let totalPages = 1;
    
    // Load bookings
    function loadBookings(page = 1, filters = {}) {
        currentPage = page;
        
        let params = new URLSearchParams();
        params.append('page', page);
        params.append('per_page', 20);
        
        Object.keys(filters).forEach(key => {
            if (filters[key]) {
                params.append(key, filters[key]);
            }
        });
        
        $.ajax({
            url: carwash_admin.rest_url + 'bookings?' + params.toString(),
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', carwash_admin.rest_nonce);
            },
            success: function(response) {
                bookings = response.data || response;
                totalPages = Math.ceil((response.total || bookings.length) / 20);
                renderBookings();
                renderPagination();
            },
            error: function(xhr) {
                alert('Error loading bookings: ' + xhr.responseText);
            }
        });
    }
    
    // Render bookings table
    function renderBookings() {
        let html = `
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Booking #</th>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Vehicle</th>
                        <th>Service</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        if (bookings.length === 0) {
            html += '<tr><td colspan="9">No bookings found.</td></tr>';
        } else {
            bookings.forEach(function(booking) {
                html += `
                    <tr>
                        <td><strong>${booking.booking_number}</strong></td>
                        <td>${booking.customer_name}</td>
                        <td>
                            <div>${booking.customer_email}</div>
                            <div>${booking.customer_phone}</div>
                        </td>
                        <td>
                            <div>${booking.vehicle_type_name || 'N/A'}</div>
                            <small>${booking.vehicle_model || ''}</small>
                        </td>
                        <td>
                            <div>${booking.service_name || 'N/A'}</div>
                            <small>${booking.los_name || ''}</small>
                        </td>
                        <td class="price-display">$${parseFloat(booking.total_price).toFixed(2)}</td>
                        <td>
                            <select class="status-select" data-booking-id="${booking.id}">
                                <option value="pending" ${booking.booking_status === 'pending' ? 'selected' : ''}>Pending</option>
                                <option value="confirmed" ${booking.booking_status === 'confirmed' ? 'selected' : ''}>Confirmed</option>
                                <option value="in_progress" ${booking.booking_status === 'in_progress' ? 'selected' : ''}>In Progress</option>
                                <option value="completed" ${booking.booking_status === 'completed' ? 'selected' : ''}>Completed</option>
                                <option value="cancelled" ${booking.booking_status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                            </select>
                        </td>
                        <td>${formatDate(booking.created_at)}</td>
                        <td>
                            <button class="button button-small view-booking" data-id="${booking.id}">View</button>
                            <button class="button button-small send-email" data-id="${booking.id}">Email</button>
                            <button class="button button-small delete-booking" data-id="${booking.id}">Delete</button>
                        </td>
                    </tr>
                `;
            });
        }
        
        html += '</tbody></table>';
        $('#bookings-list').html(html);
    }
    
    // Render pagination
    function renderPagination() {
        if (totalPages <= 1) return;
        
        let html = '<div class="tablenav"><div class="tablenav-pages">';
        
        for (let i = 1; i <= totalPages; i++) {
            const activeClass = i === currentPage ? 'current' : '';
            html += `<a class="pagination-link ${activeClass}" data-page="${i}" href="#">${i}</a>`;
        }
        
        html += '</div></div>';
        $('#bookings-list').append(html);
    }
    
    // View booking details
    $(document).on('click', '.view-booking', function() {
        const bookingId = $(this).data('id');
        
        $.ajax({
            url: carwash_admin.rest_url + 'bookings/' + bookingId,
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', carwash_admin.rest_nonce);
            },
            success: function(booking) {
                renderBookingDetails(booking);
                $('#booking-modal').show();
            },
            error: function(xhr) {
                alert('Error loading booking details: ' + xhr.responseText);
            }
        });
    });
    
    // Render booking details
    function renderBookingDetails(booking) {
        const html = `
            <div class="booking-details">
                <div class="carwash-form-grid">
                    <div class="carwash-form-section">
                        <h3>Customer Information</h3>
                        <table class="form-table">
                            <tr><th>Name:</th><td>${booking.customer_name}</td></tr>
                            <tr><th>Email:</th><td><a href="mailto:${booking.customer_email}">${booking.customer_email}</a></td></tr>
                            <tr><th>Phone:</th><td><a href="tel:${booking.customer_phone}">${booking.customer_phone}</a></td></tr>
                            <tr><th>Address:</th><td>${booking.customer_address}</td></tr>
                        </table>
                    </div>
                    
                    <div class="carwash-form-section">
                        <h3>Vehicle Information</h3>
                        <table class="form-table">
                            <tr><th>Type:</th><td>${booking.vehicle_type_name || 'N/A'}</td></tr>
                            <tr><th>Sub-type:</th><td>${booking.vehicle_subtype_name || 'N/A'}</td></tr>
                            <tr><th>Model:</th><td>${booking.vehicle_model || 'N/A'}</td></tr>
                            <tr><th>Number:</th><td>${booking.vehicle_number || 'N/A'}</td></tr>
                        </table>
                    </div>
                    
                    <div class="carwash-form-section">
                        <h3>Service Details</h3>
                        <table class="form-table">
                            <tr><th>Service:</th><td>${booking.service_name || 'N/A'}</td></tr>
                            <tr><th>Level of Service:</th><td>${booking.los_name || 'N/A'}</td></tr>
                            <tr><th>Sanding:</th><td>${booking.include_sanding ? 'Yes' : 'No'}</td></tr>
                            <tr><th>Total Price:</th><td class="price-display">$${parseFloat(booking.total_price).toFixed(2)}</td></tr>
                        </table>
                    </div>
                    
                    <div class="carwash-form-section">
                        <h3>Booking Information</h3>
                        <table class="form-table">
                            <tr><th>Booking Number:</th><td><strong>${booking.booking_number}</strong></td></tr>
                            <tr><th>Status:</th><td><span class="status-badge status-${booking.booking_status}">${booking.booking_status.replace('_', ' ').toUpperCase()}</span></td></tr>
                            <tr><th>Preferred Date:</th><td>${booking.preferred_date || 'Not specified'}</td></tr>
                            <tr><th>Preferred Time:</th><td>${booking.preferred_time || 'Not specified'}</td></tr>
                            <tr><th>Created:</th><td>${formatDateTime(booking.created_at)}</td></tr>
                            <tr><th>Updated:</th><td>${formatDateTime(booking.updated_at)}</td></tr>
                        </table>
                        
                        ${booking.special_instructions ? `
                            <h4>Special Instructions</h4>
                            <p>${booking.special_instructions}</p>
                        ` : ''}
                    </div>
                </div>
                
                <div class="carwash-modal-footer">
                    <button class="button button-primary send-email" data-id="${booking.id}">Send Email</button>
                    <button class="button print-receipt" data-booking-number="${booking.booking_number}">Print Receipt</button>
                    <button class="button carwash-modal-close">Close</button>
                </div>
            </div>
        `;
        
        $('#booking-details-content').html(html);
    }
    
    // Update booking status
    $(document).on('change', '.status-select', function() {
        const select = $(this);
        const bookingId = select.data('booking-id');
        const newStatus = select.val();
        const oldStatus = select.data('old-status') || select.find('option:selected').data('old');
        
        if (confirm(`Change booking status to "${newStatus.replace('_', ' ')}"?`)) {
            $.ajax({
                url: carwash_admin.rest_url + 'bookings/' + bookingId + '/status',
                method: 'PUT',
                data: JSON.stringify({ status: newStatus }),
                contentType: 'application/json',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', carwash_admin.rest_nonce);
                },
                success: function(response) {
                    alert('Booking status updated successfully!');
                    select.data('old-status', newStatus);
                },
                error: function(xhr) {
                    alert('Error updating status: ' + xhr.responseText);
                    select.val(oldStatus); // Revert on error
                }
            });
        } else {
            select.val(oldStatus); // Revert if cancelled
        }
    });
    
    // Filter bookings
    $('.filter-select, .search-input').on('change input', function() {
        const filters = {};
        
        const status = $('#status-filter').val();
        const search = $('#search-bookings').val();
        const dateFrom = $('#date-from').val();
        const dateTo = $('#date-to').val();
        
        if (status) filters.status = status;
        if (search) filters.search = search;
        if (dateFrom) filters.date_from = dateFrom;
        if (dateTo) filters.date_to = dateTo;
        
        loadBookings(1, filters);
    });
    
    // Pagination
    $(document).on('click', '.pagination-link', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        loadBookings(page);
    });
    
    // Print receipt
    $(document).on('click', '.print-receipt', function() {
        const bookingNumber = $(this).data('booking-number');
        const receiptUrl = carwash_admin.rest_url.replace('/admin/', '/') + 'bookings/' + bookingNumber + '/receipt';
        
        $.ajax({
            url: receiptUrl,
            method: 'GET',
            success: function(response) {
                const printWindow = window.open('', '_blank');
                printWindow.document.write(response.receipt_html);
                printWindow.document.close();
                printWindow.print();
            },
            error: function(xhr) {
                alert('Error generating receipt: ' + xhr.responseText);
            }
        });
    });
    
    // Helper functions
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }
    
    function formatDateTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit'
        });
    }
    
    // Load initial data
    loadBookings();
});
</script>

