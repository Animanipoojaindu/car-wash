<?php

class CarWash_Email {
    
    public static function send_booking_confirmation($booking_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'carwash_bookings';
        $booking = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $booking_id));
        
        if (!$booking) {
            return false;
        }
        
        // Get related data
        $vehicle_type = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}carwash_vehicle_types WHERE id = %d", $booking->vehicle_type_id));
        $vehicle_subtype = $booking->vehicle_subtype_id ? $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}carwash_vehicle_subtypes WHERE id = %d", $booking->vehicle_subtype_id)) : '';
        $service = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}carwash_services WHERE id = %d", $booking->service_id));
        $los = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}carwash_los WHERE id = %d", $booking->los_id));
        
        // Get email settings
        $from_name = get_option('carwash_email_from_name', get_option('blogname'));
        $from_email = get_option('carwash_email_from_email', get_option('admin_email'));
        $subject_template = get_option('carwash_booking_confirmation_subject', 'Booking Confirmation - #{booking_number}');
        $email_template = get_option('carwash_booking_confirmation_template', self::get_default_email_template());
        
        // Replace placeholders
        $placeholders = array(
            '{customer_name}' => $booking->customer_name,
            '{booking_number}' => $booking->booking_number,
            '{vehicle_type}' => $vehicle_type,
            '{vehicle_subtype}' => $vehicle_subtype,
            '{service_name}' => $service,
            '{los_name}' => $los,
            '{total_price}' => number_format($booking->total_price, 2),
            '{preferred_date}' => $booking->preferred_date ? date('F j, Y', strtotime($booking->preferred_date)) : 'Not specified',
            '{preferred_time}' => $booking->preferred_time ? date('g:i A', strtotime($booking->preferred_time)) : 'Not specified',
            '{business_name}' => get_option('carwash_business_name', get_option('blogname')),
            '{business_email}' => get_option('carwash_business_email', get_option('admin_email')),
            '{business_phone}' => get_option('carwash_business_phone', ''),
            '{sanding_option}' => $booking->include_sanding ? 'Yes' : 'No'
        );
        
        $subject = str_replace(array_keys($placeholders), array_values($placeholders), $subject_template);
        $message = str_replace(array_keys($placeholders), array_values($placeholders), $email_template);
        
        // Set headers
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>'
        );
        
        // Send email
        $sent = wp_mail($booking->customer_email, $subject, $message, $headers);
        
        // Also send notification to admin
        $admin_subject = 'New Booking Received - ' . $booking->booking_number;
        $admin_message = self::get_admin_notification_template($booking, $vehicle_type, $vehicle_subtype, $service, $los);
        wp_mail(get_option('carwash_business_email', get_option('admin_email')), $admin_subject, $admin_message, $headers);
        
        return $sent;
    }
    
    public static function send_status_update($booking_id, $new_status) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'carwash_bookings';
        $booking = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $booking_id));
        
        if (!$booking) {
            return false;
        }
        
        $status_messages = array(
            'confirmed' => 'Your booking has been confirmed!',
            'in_progress' => 'Your vehicle service is now in progress.',
            'completed' => 'Your vehicle service has been completed.',
            'cancelled' => 'Your booking has been cancelled.'
        );
        
        $message = isset($status_messages[$new_status]) ? $status_messages[$new_status] : 'Your booking status has been updated.';
        
        $from_name = get_option('carwash_email_from_name', get_option('blogname'));
        $from_email = get_option('carwash_email_from_email', get_option('admin_email'));
        
        $subject = 'Booking Status Update - ' . $booking->booking_number;
        $email_content = "
        <h2>Booking Status Update</h2>
        <p>Dear {$booking->customer_name},</p>
        <p>{$message}</p>
        <p><strong>Booking Number:</strong> {$booking->booking_number}</p>
        <p><strong>New Status:</strong> " . ucfirst(str_replace('_', ' ', $new_status)) . "</p>
        <p>If you have any questions, please contact us.</p>
        <p>Best regards,<br>" . get_option('carwash_business_name', get_option('blogname')) . "</p>
        ";
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>'
        );
        
        return wp_mail($booking->customer_email, $subject, $email_content, $headers);
    }
    
    public static function generate_receipt_html($booking, $vehicle_type, $vehicle_subtype, $service, $los) {
        $business_name = get_option('carwash_business_name', get_option('blogname'));
        $business_address = get_option('carwash_business_address', '');
        $business_phone = get_option('carwash_business_phone', '');
        $business_email = get_option('carwash_business_email', get_option('admin_email'));
        
        $receipt_html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Booking Receipt</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .receipt-header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 20px; }
                .receipt-title { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
                .business-info { font-size: 14px; color: #666; }
                .booking-info { margin-bottom: 20px; }
                .info-row { display: flex; justify-content: space-between; margin-bottom: 8px; }
                .info-label { font-weight: bold; }
                .total-section { border-top: 2px solid #333; padding-top: 15px; margin-top: 20px; }
                .total-amount { font-size: 18px; font-weight: bold; text-align: right; }
                .print-button { text-align: center; margin-top: 30px; }
                .btn-print { background-color: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
                @media print { .print-button { display: none; } }
            </style>
        </head>
        <body>
            <div class='receipt-header'>
                <div class='receipt-title'>{$business_name}</div>
                <div class='business-info'>
                    " . ($business_address ? $business_address . "<br>" : "") . "
                    " . ($business_phone ? "Phone: " . $business_phone . "<br>" : "") . "
                    Email: {$business_email}
                </div>
            </div>
            
            <h2>Booking Receipt</h2>
            
            <div class='booking-info'>
                <div class='info-row'>
                    <span class='info-label'>Booking Number:</span>
                    <span>{$booking->booking_number}</span>
                </div>
                <div class='info-row'>
                    <span class='info-label'>Date:</span>
                    <span>" . date('F j, Y g:i A', strtotime($booking->created_at)) . "</span>
                </div>
                <div class='info-row'>
                    <span class='info-label'>Status:</span>
                    <span>" . ucfirst(str_replace('_', ' ', $booking->booking_status)) . "</span>
                </div>
            </div>
            
            <h3>Customer Information</h3>
            <div class='booking-info'>
                <div class='info-row'>
                    <span class='info-label'>Name:</span>
                    <span>{$booking->customer_name}</span>
                </div>
                <div class='info-row'>
                    <span class='info-label'>Email:</span>
                    <span>{$booking->customer_email}</span>
                </div>
                <div class='info-row'>
                    <span class='info-label'>Phone:</span>
                    <span>{$booking->customer_phone}</span>
                </div>
                <div class='info-row'>
                    <span class='info-label'>Address:</span>
                    <span>{$booking->customer_address}</span>
                </div>
            </div>
            
            <h3>Vehicle Information</h3>
            <div class='booking-info'>
                <div class='info-row'>
                    <span class='info-label'>Vehicle Type:</span>
                    <span>{$vehicle_type}" . ($vehicle_subtype ? " - {$vehicle_subtype}" : "") . "</span>
                </div>
                " . ($booking->vehicle_number ? "
                <div class='info-row'>
                    <span class='info-label'>Vehicle Number:</span>
                    <span>{$booking->vehicle_number}</span>
                </div>" : "") . "
                " . ($booking->vehicle_model ? "
                <div class='info-row'>
                    <span class='info-label'>Vehicle Model:</span>
                    <span>{$booking->vehicle_model}</span>
                </div>" : "") . "
            </div>
            
            <h3>Service Details</h3>
            <div class='booking-info'>
                <div class='info-row'>
                    <span class='info-label'>Service:</span>
                    <span>{$service}</span>
                </div>
                <div class='info-row'>
                    <span class='info-label'>Level of Service:</span>
                    <span>{$los}</span>
                </div>
                " . ($booking->include_sanding ? "
                <div class='info-row'>
                    <span class='info-label'>Sanding Option:</span>
                    <span>Yes</span>
                </div>" : "") . "
                " . ($booking->preferred_date ? "
                <div class='info-row'>
                    <span class='info-label'>Preferred Date:</span>
                    <span>" . date('F j, Y', strtotime($booking->preferred_date)) . "</span>
                </div>" : "") . "
                " . ($booking->preferred_time ? "
                <div class='info-row'>
                    <span class='info-label'>Preferred Time:</span>
                    <span>" . date('g:i A', strtotime($booking->preferred_time)) . "</span>
                </div>" : "") . "
                " . ($booking->special_instructions ? "
                <div class='info-row'>
                    <span class='info-label'>Special Instructions:</span>
                    <span>{$booking->special_instructions}</span>
                </div>" : "") . "
            </div>
            
            <div class='total-section'>
                <div class='total-amount'>
                    Total Amount: $" . number_format($booking->total_price, 2) . "
                </div>
            </div>
            
            <div class='print-button'>
                <button class='btn-print' onclick='window.print()'>Print Receipt</button>
            </div>
        </body>
        </html>";
        
        return $receipt_html;
    }
    
    private static function get_default_email_template() {
        return '
        <h2>Booking Confirmation</h2>
        <p>Dear {customer_name},</p>
        <p>Thank you for your booking. Here are your booking details:</p>
        <ul>
            <li><strong>Booking Number:</strong> {booking_number}</li>
            <li><strong>Vehicle Type:</strong> {vehicle_type} {vehicle_subtype}</li>
            <li><strong>Service:</strong> {service_name}</li>
            <li><strong>Level of Service:</strong> {los_name}</li>
            <li><strong>Sanding Option:</strong> {sanding_option}</li>
            <li><strong>Total Price:</strong> ${total_price}</li>
            <li><strong>Preferred Date:</strong> {preferred_date}</li>
            <li><strong>Preferred Time:</strong> {preferred_time}</li>
        </ul>
        <p>We will contact you soon to confirm the appointment.</p>
        <p>Best regards,<br>{business_name}</p>
        ';
    }
    
    private static function get_admin_notification_template($booking, $vehicle_type, $vehicle_subtype, $service, $los) {
        return "
        <h2>New Booking Received</h2>
        <p>A new booking has been submitted:</p>
        <ul>
            <li><strong>Booking Number:</strong> {$booking->booking_number}</li>
            <li><strong>Customer:</strong> {$booking->customer_name}</li>
            <li><strong>Email:</strong> {$booking->customer_email}</li>
            <li><strong>Phone:</strong> {$booking->customer_phone}</li>
            <li><strong>Vehicle Type:</strong> {$vehicle_type} {$vehicle_subtype}</li>
            <li><strong>Service:</strong> {$service}</li>
            <li><strong>Level of Service:</strong> {$los}</li>
            <li><strong>Total Price:</strong> $" . number_format($booking->total_price, 2) . "</li>
            <li><strong>Preferred Date:</strong> " . ($booking->preferred_date ? date('F j, Y', strtotime($booking->preferred_date)) : 'Not specified') . "</li>
            <li><strong>Preferred Time:</strong> " . ($booking->preferred_time ? date('g:i A', strtotime($booking->preferred_time)) : 'Not specified') . "</li>
        </ul>
        <p>Please log in to the admin panel to manage this booking.</p>
        ";
    }
}

