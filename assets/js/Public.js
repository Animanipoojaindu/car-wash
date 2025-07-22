jQuery(document).ready(function($) {
    'use strict';
    
    // Global variables
    window.CarWashBooking = {
        currentStep: 1,
        totalSteps: 6,
        formData: {},
        vehicleTypes: [],
        vehicleSubtypes: [],
        services: [],
        los: [],
        selectedData: {
            vehicleType: null,
            vehicleSubtype: null,
            service: null,
            los: null,
            includeSanding: false
        },
        
        // Initialize booking form
        init: function() {
            this.bindEvents();
            this.initializeGoogleMaps();
            this.initializePhoneInput();
            this.loadVehicleTypes();
            this.updateProgress();
        },
        
        // Bind event handlers
        bindEvents: function() {
            // Navigation buttons
            $('.next-step').on('click', this.nextStep.bind(this));
            $('.prev-step').on('click', this.prevStep.bind(this));
            
            // Form submission
            $('.submit-booking').on('click', this.submitBooking.bind(this));
            
            // Vehicle type selection
            $(document).on('click', '.vehicle-option', this.selectVehicleType.bind(this));
            $(document).on('click', '.vehicle-subtype-option', this.selectVehicleSubtype.bind(this));
            $(document).on('click', '.service-option', this.selectService.bind(this));
            $(document).on('click', '.los-option', this.selectLOS.bind(this));
            
            // Sanding option
            $('#include-sanding').on('change', this.toggleSanding.bind(this));
            
            // Form validation
            $('input, textarea, select').on('blur', this.validateField.bind(this));
            
            // Print receipt
            $('.print-receipt').on('click', this.printReceipt.bind(this));
            
            // New booking
            $('.new-booking').on('click', this.resetForm.bind(this));
        },
        
        // Initialize Google Maps for address autocomplete
        initializeGoogleMaps: function() {
            if (typeof google !== 'undefined' && google.maps && google.maps.places) {
                const addressInput = document.getElementById('customer-address');
                if (addressInput) {
                    const autocomplete = new google.maps.places.Autocomplete(addressInput, {
                        componentRestrictions: { country: 'ca' },
                        fields: ['formatted_address', 'geometry']
                    });
                    
                    autocomplete.addListener('place_changed', function() {
                        const place = autocomplete.getPlace();
                        if (place.formatted_address) {
                            CarWashBooking.formData.customerAddress = place.formatted_address;
                        }
                    });
                }
            }
        },
        
        // Initialize international phone input
        initializePhoneInput: function() {
            if (typeof intlTelInput !== 'undefined') {
                const phoneInput = document.getElementById('customer-phone');
                if (phoneInput) {
                    window.iti = intlTelInput(phoneInput, {
                        initialCountry: 'ca',
                        preferredCountries: ['ca', 'us'],
                        utilsScript: 'https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/js/utils.js'
                    });
                }
            }
        },
        
        // Load vehicle types from API
        loadVehicleTypes: function() {
            $.ajax({
                url: carwash_public.rest_url + 'vehicle-types',
                method: 'GET',
                success: function(response) {
                    CarWashBooking.vehicleTypes = response;
                    CarWashBooking.renderVehicleTypes();
                },
                error: function(xhr) {
                    console.error('Error loading vehicle types:', xhr);
                    CarWashBooking.showError('Failed to load vehicle types. Please refresh the page.');
                }
            });
        },
        
        // Render vehicle types
        renderVehicleTypes: function() {
            const container = $('#vehicle-types-container');
            let html = '';
            
            this.vehicleTypes.forEach(function(type) {
                html += `
                    <div class="vehicle-option" data-id="${type.id}" data-slug="${type.slug}">
                        <div class="option-image">
                            ${type.image_url ? `<img src="${type.image_url}" alt="${type.name}">` : '🚗'}
                        </div>
                        <div class="option-name">${type.name}</div>
                        <div class="option-description">${type.description || ''}</div>
                    </div>
                `;
            });
            
            container.html(html);
        },
        
        // Select vehicle type
        selectVehicleType: function(e) {
            const option = $(e.currentTarget);
            const typeId = option.data('id');
            const typeData = this.vehicleTypes.find(t => t.id == typeId);
            
            $('.vehicle-option').removeClass('selected');
            option.addClass('selected');
            
            this.selectedData.vehicleType = typeData;
            this.enableNextButton(2);
            
            // Load subtypes for this vehicle type
            this.loadVehicleSubtypes(typeId);
        },
        
        // Load vehicle subtypes
        loadVehicleSubtypes: function(vehicleTypeId) {
            $.ajax({
                url: carwash_public.rest_url + 'vehicle-subtypes/' + vehicleTypeId,
                method: 'GET',
                success: function(response) {
                    CarWashBooking.vehicleSubtypes = response;
                    CarWashBooking.renderVehicleSubtypes();
                },
                error: function(xhr) {
                    console.error('Error loading vehicle subtypes:', xhr);
                }
            });
        },
        
        // Render vehicle subtypes
        renderVehicleSubtypes: function() {
            const container = $('#vehicle-subtypes-container');
            let html = '';
            
            if (this.vehicleSubtypes.length === 0) {
                // No subtypes, load services directly
                this.loadServices(this.selectedData.vehicleType.id);
                return;
            }
            
            this.vehicleSubtypes.forEach(function(subtype) {
                html += `
                    <div class="vehicle-subtype-option" data-id="${subtype.id}" data-slug="${subtype.slug}">
                        <div class="option-image">
                            ${subtype.image_url ? `<img src="${subtype.image_url}" alt="${subtype.name}">` : '🚙'}
                        </div>
                        <div class="option-name">${subtype.name}</div>
                        <div class="option-description">${subtype.description || ''}</div>
                    </div>
                `;
            });
            
            container.html(html);
        },
        
        // Select vehicle subtype
        selectVehicleSubtype: function(e) {
            const option = $(e.currentTarget);
            const subtypeId = option.data('id');
            const subtypeData = this.vehicleSubtypes.find(s => s.id == subtypeId);
            
            $('.vehicle-subtype-option').removeClass('selected');
            option.addClass('selected');
            
            this.selectedData.vehicleSubtype = subtypeData;
            
            // Load services for this combination
            this.loadServices(this.selectedData.vehicleType.id, subtypeId);
        },
        
        // Load services
        loadServices: function(vehicleTypeId, subtypeId) {
            let url = carwash_public.rest_url + 'services/' + vehicleTypeId;
            if (subtypeId) {
                url += '/' + subtypeId;
            }
            
            $.ajax({
                url: url,
                method: 'GET',
                success: function(response) {
                    CarWashBooking.services = response;
                    CarWashBooking.renderServices();
                    
                    // If only one service or motorcycle (flat rate), auto-select
                    if (response.length === 1 || CarWashBooking.selectedData.vehicleType.slug === 'motorcycle') {
                        CarWashBooking.autoSelectService(response[0]);
                    }
                },
                error: function(xhr) {
                    console.error('Error loading services:', xhr);
                }
            });
        },
        
        // Render services
        renderServices: function() {
            const container = $('#services-container');
            
            if (this.services.length === 0) {
                container.hide();
                this.enableNextButton(3);
                return;
            }
            
            let html = '<h3>Available Services</h3>';
            
            this.services.forEach(function(service) {
                html += `
                    <div class="service-option" data-id="${service.id}">
                        <div class="option-name">${service.name}</div>
                        <div class="option-description">${service.description || ''}</div>
                        <div class="option-price">$${parseFloat(service.base_price).toFixed(2)}</div>
                    </div>
                `;
            });
            
            container.html(html).show();
        },
        
        // Auto-select service (for motorcycle or single service)
        autoSelectService: function(service) {
            this.selectedData.service = service;
            $('.service-option[data-id="' + service.id + '"]').addClass('selected');
            this.enableNextButton(3);
        },
        
        // Select service
        selectService: function(e) {
            const option = $(e.currentTarget);
            const serviceId = option.data('id');
            const serviceData = this.services.find(s => s.id == serviceId);
            
            $('.service-option').removeClass('selected');
            option.addClass('selected');
            
            this.selectedData.service = serviceData;
            this.enableNextButton(3);
        },
        
        // Load levels of service
        loadLOS: function(vehicleTypeId) {
            $.ajax({
                url: carwash_public.rest_url + 'los/' + vehicleTypeId,
                method: 'GET',
                success: function(response) {
                    CarWashBooking.los = response;
                    CarWashBooking.renderLOS();
                },
                error: function(xhr) {
                    console.error('Error loading levels of service:', xhr);
                }
            });
        },
        
        // Render levels of service
        renderLOS: function() {
            const container = $('#los-container');
            let html = '';
            
            this.los.forEach(function(los) {
                const basePrice = CarWashBooking.selectedData.service ? 
                    parseFloat(CarWashBooking.selectedData.service.base_price) : 0;
                const totalPrice = basePrice * parseFloat(los.price_modifier);
                
                html += `
                    <div class="los-option" data-id="${los.id}">
                        <div class="option-name">${los.name}</div>
                        <div class="option-description">${los.description || ''}</div>
                        <div class="option-price">$${totalPrice.toFixed(2)}</div>
                    </div>
                `;
            });
            
            container.html(html);
        },
        
        // Select level of service
        selectLOS: function(e) {
            const option = $(e.currentTarget);
            const losId = option.data('id');
            const losData = this.los.find(l => l.id == losId);
            
            $('.los-option').removeClass('selected');
            option.addClass('selected');
            
            this.selectedData.los = losData;
            
            // Show sanding option if available
            if (losData.has_sanding_option) {
                $('#sanding-container').show();
                $('.sanding-price').text('(+$' + parseFloat(losData.sanding_price_add).toFixed(2) + ')');
            } else {
                $('#sanding-container').hide();
                $('#include-sanding').prop('checked', false);
                this.selectedData.includeSanding = false;
            }
            
            this.updatePricePreview();
            this.enableNextButton(4);
        },
        
        // Toggle sanding option
        toggleSanding: function(e) {
            this.selectedData.includeSanding = $(e.target).is(':checked');
            this.updatePricePreview();
        },
        
        // Update price preview
        updatePricePreview: function() {
            if (!this.selectedData.service || !this.selectedData.los) return;
            
            const basePrice = parseFloat(this.selectedData.service.base_price);
            const modifier = parseFloat(this.selectedData.los.price_modifier);
            const servicePrice = basePrice * modifier;
            const sandingPrice = this.selectedData.includeSanding ? 
                parseFloat(this.selectedData.los.sanding_price_add || 0) : 0;
            const totalPrice = servicePrice + sandingPrice;
            
            $('#base-price').text('$' + servicePrice.toFixed(2));
            
            if (sandingPrice > 0) {
                $('#sanding-price-line').show();
                $('#sanding-price-amount').text('$' + sandingPrice.toFixed(2));
            } else {
                $('#sanding-price-line').hide();
            }
            
            $('#total-price').text('$' + totalPrice.toFixed(2));
            $('#price-preview').show();
        },
        
        // Navigate to next step
        nextStep: function(e) {
            const nextStep = parseInt($(e.target).data('next'));
            
            if (this.validateCurrentStep()) {
                this.goToStep(nextStep);
                
                // Load data for specific steps
                if (nextStep === 4 && this.selectedData.vehicleType) {
                    this.loadLOS(this.selectedData.vehicleType.id);
                } else if (nextStep === 6) {
                    this.generateOrderSummary();
                }
            }
        },
        
        // Navigate to previous step
        prevStep: function(e) {
            const prevStep = parseInt($(e.target).data('prev'));
            this.goToStep(prevStep);
        },
        
        // Go to specific step
        goToStep: function(stepNumber) {
            $('.form-step').removeClass('active');
            $('#step-' + stepNumber).addClass('active');
            
            this.currentStep = stepNumber;
            this.updateProgress();
        },
        
        // Update progress indicator
        updateProgress: function() {
            const progressPercent = (this.currentStep / this.totalSteps) * 100;
            $('.progress-fill').css('width', progressPercent + '%');
            
            $('.step').removeClass('active completed');
            $('.step').each(function(index) {
                const stepNum = index + 1;
                if (stepNum < CarWashBooking.currentStep) {
                    $(this).addClass('completed');
                } else if (stepNum === CarWashBooking.currentStep) {
                    $(this).addClass('active');
                }
            });
        },
        
        // Enable next button for specific step
        enableNextButton: function(step) {
            $(`#step-${step} .next-step`).prop('disabled', false);
        },
        
        // Validate current step
        validateCurrentStep: function() {
            switch (this.currentStep) {
                case 1:
                    return this.validateAddress();
                case 2:
                    return this.selectedData.vehicleType !== null;
                case 3:
                    return this.selectedData.vehicleSubtype !== null || this.vehicleSubtypes.length === 0;
                case 4:
                    return this.selectedData.los !== null;
                case 5:
                    return this.validateBookingDetails();
                default:
                    return true;
            }
        },
        
        // Validate address
        validateAddress: function() {
            const address = $('#customer-address').val().trim();
            if (!address) {
                this.showFieldError('#customer-address', 'Please enter your address');
                return false;
            }
            this.formData.customerAddress = address;
            return true;
        },
        
        // Validate booking details
        validateBookingDetails: function() {
            let isValid = true;
            
            // Required fields
            const requiredFields = [
                { id: '#customer-name', name: 'name' },
                { id: '#customer-email', name: 'email' },
                { id: '#customer-phone', name: 'phone' }
            ];
            
            requiredFields.forEach(field => {
                const value = $(field.id).val().trim();
                if (!value) {
                    this.showFieldError(field.id, `Please enter your ${field.name}`);
                    isValid = false;
                } else {
                    this.formData[field.id.replace('#customer-', 'customer')] = value;
                }
            });
            
            // Email validation
            const email = $('#customer-email').val().trim();
            if (email && !this.isValidEmail(email)) {
                this.showFieldError('#customer-email', 'Please enter a valid email address');
                isValid = false;
            }
            
            // Phone validation
            if (window.iti && !window.iti.isValidNumber()) {
                this.showFieldError('#customer-phone', 'Please enter a valid phone number');
                isValid = false;
            }
            
            // Optional fields
            this.formData.vehicleNumber = $('#vehicle-number').val().trim();
            this.formData.vehicleModel = $('#vehicle-model').val().trim();
            this.formData.preferredDate = $('#preferred-date').val();
            this.formData.preferredTime = $('#preferred-time').val();
            this.formData.specialInstructions = $('#special-instructions').val().trim();
            
            return isValid;
        },
        
        // Validate individual field
        validateField: function(e) {
            const field = $(e.target);
            const value = field.val().trim();
            
            // Remove existing error state
            field.closest('.form-group').removeClass('error');
            field.siblings('.error-message').remove();
            
            // Validate based on field type
            if (field.attr('required') && !value) {
                this.showFieldError(field, 'This field is required');
            } else if (field.attr('type') === 'email' && value && !this.isValidEmail(value)) {
                this.showFieldError(field, 'Please enter a valid email address');
            }
        },
        
        // Show field error
        showFieldError: function(field, message) {
            const $field = $(field);
            $field.closest('.form-group').addClass('error');
            $field.after(`<div class="error-message">${message}</div>`);
        },
        
        // Validate email format
        isValidEmail: function(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },
        
        // Generate order summary
        generateOrderSummary: function() {
            const summary = $('#order-summary');
            
            let html = `
                <div class="summary-section">
                    <h3>Customer Information</h3>
                    <div class="summary-item">
                        <span class="label">Name:</span>
                        <span class="value">${this.formData.customerName || ''}</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Email:</span>
                        <span class="value">${this.formData.customerEmail || ''}</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Phone:</span>
                        <span class="value">${this.formData.customerPhone || ''}</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Address:</span>
                        <span class="value">${this.formData.customerAddress || ''}</span>
                    </div>
                </div>
                
                <div class="summary-section">
                    <h3>Vehicle Information</h3>
                    <div class="summary-item">
                        <span class="label">Type:</span>
                        <span class="value">${this.selectedData.vehicleType?.name || ''}</span>
                    </div>
            `;
            
            if (this.selectedData.vehicleSubtype) {
                html += `
                    <div class="summary-item">
                        <span class="label">Sub-type:</span>
                        <span class="value">${this.selectedData.vehicleSubtype.name}</span>
                    </div>
                `;
            }
            
            if (this.formData.vehicleModel) {
                html += `
                    <div class="summary-item">
                        <span class="label">Model:</span>
                        <span class="value">${this.formData.vehicleModel}</span>
                    </div>
                `;
            }
            
            html += `
                </div>
                
                <div class="summary-section">
                    <h3>Service Details</h3>
            `;
            
            if (this.selectedData.service) {
                html += `
                    <div class="summary-item">
                        <span class="label">Service:</span>
                        <span class="value">${this.selectedData.service.name}</span>
                    </div>
                `;
            }
            
            if (this.selectedData.los) {
                html += `
                    <div class="summary-item">
                        <span class="label">Level of Service:</span>
                        <span class="value">${this.selectedData.los.name}</span>
                    </div>
                `;
            }
            
            if (this.selectedData.includeSanding) {
                html += `
                    <div class="summary-item">
                        <span class="label">Sanding Service:</span>
                        <span class="value">Yes</span>
                    </div>
                `;
            }
            
            if (this.formData.preferredDate) {
                html += `
                    <div class="summary-item">
                        <span class="label">Preferred Date:</span>
                        <span class="value">${this.formData.preferredDate}</span>
                    </div>
                `;
            }
            
            if (this.formData.preferredTime) {
                html += `
                    <div class="summary-item">
                        <span class="label">Preferred Time:</span>
                        <span class="value">${this.formData.preferredTime}</span>
                    </div>
                `;
            }
            
            html += '</div>';
            
            // Calculate total price
            const basePrice = this.selectedData.service ? parseFloat(this.selectedData.service.base_price) : 0;
            const modifier = this.selectedData.los ? parseFloat(this.selectedData.los.price_modifier) : 1;
            const sandingPrice = this.selectedData.includeSanding && this.selectedData.los ? 
                parseFloat(this.selectedData.los.sanding_price_add || 0) : 0;
            const totalPrice = (basePrice * modifier) + sandingPrice;
            
            html += `<div class="summary-total">Total: $${totalPrice.toFixed(2)}</div>`;
            
            summary.html(html);
        },
        
        // Submit booking
        submitBooking: function() {
            this.showLoading();
            
            const bookingData = {
                customer_name: this.formData.customerName,
                customer_email: this.formData.customerEmail,
                customer_phone: this.formData.customerPhone,
                customer_address: this.formData.customerAddress,
                vehicle_number: this.formData.vehicleNumber || '',
                vehicle_model: this.formData.vehicleModel || '',
                vehicle_type_id: this.selectedData.vehicleType.id,
                vehicle_subtype_id: this.selectedData.vehicleSubtype?.id || null,
                service_id: this.selectedData.service?.id || null,
                los_id: this.selectedData.los.id,
                include_sanding: this.selectedData.includeSanding,
                preferred_date: this.formData.preferredDate || null,
                preferred_time: this.formData.preferredTime || null,
                special_instructions: this.formData.specialInstructions || ''
            };
            
            $.ajax({
                url: carwash_public.rest_url + 'bookings/submit',
                method: 'POST',
                data: JSON.stringify(bookingData),
                contentType: 'application/json',
                success: function(response) {
                    CarWashBooking.hideLoading();
                    CarWashBooking.showSuccessPage(response);
                },
                error: function(xhr) {
                    CarWashBooking.hideLoading();
                    console.error('Booking submission error:', xhr);
                    CarWashBooking.showError('Failed to submit booking. Please try again.');
                }
            });
        },
        
        // Show success page
        showSuccessPage: function(response) {
            $('.form-step').removeClass('active');
            $('#step-success').show();
            
            const confirmation = $('#booking-confirmation');
            const html = `
                <div class="confirmation-number">Booking #${response.booking_number}</div>
                <div class="confirmation-item">
                    <span class="label">Total Amount:</span>
                    <span class="value">$${parseFloat(response.total_price).toFixed(2)}</span>
                </div>
                <div class="confirmation-item">
                    <span class="label">Email Sent:</span>
                    <span class="value">${response.email_sent ? 'Yes' : 'No'}</span>
                </div>
            `;
            
            confirmation.html(html);
            
            // Store booking number for receipt printing
            this.bookingNumber = response.booking_number;
            
            // Update progress to show completion
            this.currentStep = this.totalSteps;
            this.updateProgress();
        },
        
        // Print receipt
        printReceipt: function() {
            if (!this.bookingNumber) return;
            
            $.ajax({
                url: carwash_public.rest_url + 'bookings/' + this.bookingNumber + '/receipt',
                method: 'GET',
                success: function(response) {
                    const printWindow = window.open('', '_blank');
                    printWindow.document.write(response.receipt_html);
                    printWindow.document.close();
                    printWindow.print();
                },
                error: function(xhr) {
                    console.error('Error generating receipt:', xhr);
                    CarWashBooking.showError('Failed to generate receipt.');
                }
            });
        },
        
        // Reset form for new booking
        resetForm: function() {
            this.currentStep = 1;
            this.formData = {};
            this.selectedData = {
                vehicleType: null,
                vehicleSubtype: null,
                service: null,
                los: null,
                includeSanding: false
            };
            
            // Reset form fields
            $('input, textarea, select').val('');
            $('.selected').removeClass('selected');
            $('.form-step').removeClass('active');
            $('#step-1').addClass('active');
            $('#step-success').hide();
            
            // Reset progress
            this.updateProgress();
            
            // Reload vehicle types
            this.loadVehicleTypes();
        },
        
        // Show loading overlay
        showLoading: function() {
            $('#loading-overlay').show();
        },
        
        // Hide loading overlay
        hideLoading: function() {
            $('#loading-overlay').hide();
        },
        
        // Show error message
        showError: function(message) {
            alert(message); // Replace with better error handling
        }
    };
    
    // Initialize the booking form
    CarWashBooking.init();
    
    // Make CarWashBooking globally available
    window.CarWashBooking = CarWashBooking;
});

