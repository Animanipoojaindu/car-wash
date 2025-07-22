<div class="wrap">
    <h1>Car Wash Booking Settings</h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('carwash_settings_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="carwash_google_maps_api_key">Google Maps API Key</label>
                </th>
                <td>
                    <input type="text" id="carwash_google_maps_api_key" name="carwash_google_maps_api_key" 
                           value="<?php echo esc_attr(get_option('carwash_google_maps_api_key')); ?>" class="regular-text">
                    <p class="description">Required for address autocomplete functionality. Get your API key from Google Cloud Console.</p>
                </td>
            </tr>
        </table>
        
        <h2>Business Information</h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="carwash_business_name">Business Name</label>
                </th>
                <td>
                    <input type="text" id="carwash_business_name" name="carwash_business_name" 
                           value="<?php echo esc_attr(get_option('carwash_business_name')); ?>" class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="carwash_business_email">Business Email</label>
                </th>
                <td>
                    <input type="email" id="carwash_business_email" name="carwash_business_email" 
                           value="<?php echo esc_attr(get_option('carwash_business_email')); ?>" class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="carwash_business_phone">Business Phone</label>
                </th>
                <td>
                    <input type="text" id="carwash_business_phone" name="carwash_business_phone" 
                           value="<?php echo esc_attr(get_option('carwash_business_phone')); ?>" class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="carwash_business_address">Business Address</label>
                </th>
                <td>
                    <textarea id="carwash_business_address" name="carwash_business_address" 
                              rows="3" class="large-text"><?php echo esc_textarea(get_option('carwash_business_address')); ?></textarea>
                </td>
            </tr>
        </table>
        
        <h2>Email Settings</h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="carwash_email_from_name">From Name</label>
                </th>
                <td>
                    <input type="text" id="carwash_email_from_name" name="carwash_email_from_name" 
                           value="<?php echo esc_attr(get_option('carwash_email_from_name')); ?>" class="regular-text">
                    <p class="description">Name that appears in the "From" field of emails.</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="carwash_email_from_email">From Email</label>
                </th>
                <td>
                    <input type="email" id="carwash_email_from_email" name="carwash_email_from_email" 
                           value="<?php echo esc_attr(get_option('carwash_email_from_email')); ?>" class="regular-text">
                    <p class="description">Email address that appears in the "From" field of emails.</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="carwash_booking_confirmation_subject">Booking Confirmation Subject</label>
                </th>
                <td>
                    <input type="text" id="carwash_booking_confirmation_subject" name="carwash_booking_confirmation_subject" 
                           value="<?php echo esc_attr(get_option('carwash_booking_confirmation_subject')); ?>" class="large-text">
                    <p class="description">Available placeholders: {booking_number}, {customer_name}, {business_name}</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="carwash_booking_confirmation_template">Booking Confirmation Template</label>
                </th>
                <td>
                    <?php
                    wp_editor(
                        get_option('carwash_booking_confirmation_template'),
                        'carwash_booking_confirmation_template',
                        array(
                            'textarea_name' => 'carwash_booking_confirmation_template',
                            'textarea_rows' => 15,
                            'media_buttons' => false,
                            'teeny' => true
                        )
                    );
                    ?>
                    <p class="description">
                        Available placeholders: {customer_name}, {booking_number}, {vehicle_type}, {vehicle_subtype}, 
                        {service_name}, {los_name}, {total_price}, {preferred_date}, {preferred_time}, {business_name}, 
                        {business_email}, {business_phone}, {sanding_option}
                    </p>
                </td>
            </tr>
        </table>
        
        <?php submit_button('Save Settings'); ?>
    </form>
    
    <div class="carwash-settings-help">
        <h2>Help & Documentation</h2>
        
        <div class="carwash-help-section">
            <h3>Google Maps API Setup</h3>
            <ol>
                <li>Go to the <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a></li>
                <li>Create a new project or select an existing one</li>
                <li>Enable the "Places API" and "Maps JavaScript API"</li>
                <li>Create credentials (API Key)</li>
                <li>Restrict the API key to your domain for security</li>
                <li>Copy the API key and paste it in the field above</li>
            </ol>
        </div>
        
        <div class="carwash-help-section">
            <h3>Email Template Placeholders</h3>
            <p>You can use the following placeholders in your email templates:</p>
            <ul>
                <li><code>{customer_name}</code> - Customer's full name</li>
                <li><code>{booking_number}</code> - Unique booking reference number</li>
                <li><code>{vehicle_type}</code> - Selected vehicle type (e.g., Automotive)</li>
                <li><code>{vehicle_subtype}</code> - Selected vehicle subtype (e.g., Modern)</li>
                <li><code>{service_name}</code> - Selected service name</li>
                <li><code>{los_name}</code> - Selected level of service</li>
                <li><code>{total_price}</code> - Total booking price</li>
                <li><code>{preferred_date}</code> - Customer's preferred date</li>
                <li><code>{preferred_time}</code> - Customer's preferred time</li>
                <li><code>{business_name}</code> - Your business name</li>
                <li><code>{business_email}</code> - Your business email</li>
                <li><code>{business_phone}</code> - Your business phone</li>
                <li><code>{sanding_option}</code> - Whether sanding was selected (Yes/No)</li>
            </ul>
        </div>
        
        <div class="carwash-help-section">
            <h3>Shortcode Usage</h3>
            <p>To display the booking form on any page or post, use the following shortcode:</p>
            <code>[carwash_booking_form]</code>
            
            <p>You can also customize the form appearance with these attributes:</p>
            <ul>
                <li><code>[carwash_booking_form theme="light"]</code> - Use light theme</li>
                <li><code>[carwash_booking_form theme="dark"]</code> - Use dark theme</li>
                <li><code>[carwash_booking_form show_progress="false"]</code> - Hide progress indicator</li>
            </ul>
        </div>
    </div>
</div>

<style>
.carwash-settings-help {
    margin-top: 40px;
    padding: 20px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.carwash-help-section {
    margin-bottom: 30px;
}

.carwash-help-section h3 {
    margin-top: 0;
    color: #333;
}

.carwash-help-section code {
    background: #fff;
    padding: 2px 6px;
    border: 1px solid #ddd;
    border-radius: 3px;
    font-family: monospace;
}

.carwash-help-section ol,
.carwash-help-section ul {
    margin-left: 20px;
}

.carwash-help-section li {
    margin-bottom: 5px;
}
</style>

