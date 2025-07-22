<div class="carwash-booking-container" data-theme="<?php echo esc_attr($atts['theme']); ?>">
    <div class="carwash-booking-form">
        <!-- Progress Indicator -->
        <?php if ($atts['show_progress'] === 'true'): ?>
        <div class="carwash-progress">
            <div class="progress-steps">
                <div class="step active" data-step="1">
                    <div class="step-number">1</div>
                    <div class="step-label">Address</div>
                </div>
                <div class="step" data-step="2">
                    <div class="step-number">2</div>
                    <div class="step-label">Vehicle Type</div>
                </div>
                <div class="step" data-step="3">
                    <div class="step-number">3</div>
                    <div class="step-label">Vehicle Details</div>
                </div>
                <div class="step" data-step="4">
                    <div class="step-number">4</div>
                    <div class="step-label">Service Level</div>
                </div>
                <div class="step" data-step="5">
                    <div class="step-number">5</div>
                    <div class="step-label">Booking Details</div>
                </div>
                <div class="step" data-step="6">
                    <div class="step-number">6</div>
                    <div class="step-label">Summary</div>
                </div>
            </div>
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Step 1: Address -->
        <div class="form-step active" id="step-1">
            <div class="step-header">
                <h2>Where are you located?</h2>
                <p>Please enter your address in Canada for our car washing service.</p>
            </div>
            
            <div class="form-group">
                <label for="customer-address">Address *</label>
                <input type="text" id="customer-address" name="customer_address" 
                       placeholder="Start typing your address..." required>
                <div class="form-help">We use Google Maps to ensure accurate address selection within Canada.</div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-primary next-step" data-next="2">
                    Continue
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Step 2: Vehicle Type -->
        <div class="form-step" id="step-2">
            <div class="step-header">
                <h2>What type of vehicle do you have?</h2>
                <p>Select the category that best describes your vehicle.</p>
            </div>
            
            <div class="vehicle-types-grid" id="vehicle-types-container">
                <!-- Vehicle types will be loaded here -->
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary prev-step" data-prev="1">
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back
                </button>
                <button type="button" class="btn btn-primary next-step" data-next="3" disabled>
                    Continue
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Step 3: Vehicle Sub-type -->
        <div class="form-step" id="step-3">
            <div class="step-header">
                <h2>Vehicle Details</h2>
                <p>Please specify the sub-category and any additional details.</p>
            </div>
            
            <div class="vehicle-subtypes-grid" id="vehicle-subtypes-container">
                <!-- Vehicle subtypes will be loaded here -->
            </div>
            
            <div class="services-grid" id="services-container" style="display: none;">
                <!-- Services will be loaded here if applicable -->
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary prev-step" data-prev="2">
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back
                </button>
                <button type="button" class="btn btn-primary next-step" data-next="4" disabled>
                    Continue
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Step 4: Level of Service -->
        <div class="form-step" id="step-4">
            <div class="step-header">
                <h2>Choose Your Service Level</h2>
                <p>Select the level of service that meets your needs.</p>
            </div>
            
            <div class="los-grid" id="los-container">
                <!-- Levels of service will be loaded here -->
            </div>
            
            <div class="sanding-option" id="sanding-container" style="display: none;">
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="include-sanding" name="include_sanding">
                        <span class="checkmark"></span>
                        <span class="label-text">
                            <strong>Add Sanding Service</strong>
                            <span class="sanding-price"></span>
                        </span>
                    </label>
                </div>
            </div>
            
            <div class="price-preview" id="price-preview">
                <div class="price-breakdown">
                    <div class="price-line">
                        <span>Base Service:</span>
                        <span id="base-price">$0.00</span>
                    </div>
                    <div class="price-line" id="sanding-price-line" style="display: none;">
                        <span>Sanding Service:</span>
                        <span id="sanding-price-amount">$0.00</span>
                    </div>
                    <div class="price-total">
                        <span>Total:</span>
                        <span id="total-price">$0.00</span>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary prev-step" data-prev="3">
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back
                </button>
                <button type="button" class="btn btn-primary next-step" data-next="5" disabled>
                    Continue
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Step 5: Booking Details -->
        <div class="form-step" id="step-5">
            <div class="step-header">
                <h2>Your Information</h2>
                <p>Please provide your contact details and vehicle information.</p>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="customer-name">Full Name *</label>
                    <input type="text" id="customer-name" name="customer_name" required>
                </div>
                
                <div class="form-group">
                    <label for="customer-email">Email Address *</label>
                    <input type="email" id="customer-email" name="customer_email" required>
                </div>
                
                <div class="form-group">
                    <label for="customer-phone">Phone Number *</label>
                    <input type="tel" id="customer-phone" name="customer_phone" required>
                </div>
                
                <div class="form-group">
                    <label for="vehicle-number">Vehicle License Plate</label>
                    <input type="text" id="vehicle-number" name="vehicle_number">
                </div>
                
                <div class="form-group">
                    <label for="vehicle-model">Vehicle Make/Model</label>
                    <input type="text" id="vehicle-model" name="vehicle_model" 
                           placeholder="e.g., Toyota Camry 2020">
                </div>
                
                <div class="form-group">
                    <label for="preferred-date">Preferred Date</label>
                    <input type="date" id="preferred-date" name="preferred_date">
                </div>
                
                <div class="form-group">
                    <label for="preferred-time">Preferred Time</label>
                    <input type="time" id="preferred-time" name="preferred_time">
                </div>
                
                <div class="form-group full-width">
                    <label for="special-instructions">Special Instructions</label>
                    <textarea id="special-instructions" name="special_instructions" 
                              rows="3" placeholder="Any special requests or instructions..."></textarea>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary prev-step" data-prev="4">
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back
                </button>
                <button type="button" class="btn btn-primary next-step" data-next="6">
                    Review Order
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Step 6: Order Summary -->
        <div class="form-step" id="step-6">
            <div class="step-header">
                <h2>Order Summary</h2>
                <p>Please review your booking details before submitting.</p>
            </div>
            
            <div class="order-summary" id="order-summary">
                <!-- Order summary will be populated here -->
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary prev-step" data-prev="5">
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back
                </button>
                <button type="button" class="btn btn-success submit-booking" id="submit-booking">
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M20 6L9 17l-5-5"/>
                    </svg>
                    Submit Booking
                </button>
            </div>
        </div>
        
        <!-- Success Step -->
        <div class="form-step" id="step-success" style="display: none;">
            <div class="success-content">
                <div class="success-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M20 6L9 17l-5-5"/>
                    </svg>
                </div>
                <h2>Booking Confirmed!</h2>
                <p>Thank you for your booking. We've sent a confirmation email with all the details.</p>
                
                <div class="booking-confirmation" id="booking-confirmation">
                    <!-- Booking confirmation details will be shown here -->
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-primary print-receipt" id="print-receipt">
                        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                        </svg>
                        Print Receipt
                    </button>
                    <button type="button" class="btn btn-secondary new-booking" id="new-booking">
                        New Booking
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loading-overlay" style="display: none;">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Processing your booking...</p>
        </div>
    </div>
</div>

