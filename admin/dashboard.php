<div class="wrap">
    <h1>Car Wash Booking Dashboard</h1>
    
    <div class="carwash-dashboard-stats">
        <div class="carwash-stat-box">
            <h3>Total Bookings</h3>
            <div class="stat-number"><?php echo $total_bookings; ?></div>
        </div>
        
        <div class="carwash-stat-box">
            <h3>Pending Bookings</h3>
            <div class="stat-number pending"><?php echo $pending_bookings; ?></div>
        </div>
        
        <div class="carwash-stat-box">
            <h3>Confirmed Bookings</h3>
            <div class="stat-number confirmed"><?php echo $confirmed_bookings; ?></div>
        </div>
        
        <div class="carwash-stat-box">
            <h3>Completed Bookings</h3>
            <div class="stat-number completed"><?php echo $completed_bookings; ?></div>
        </div>
        
        <div class="carwash-stat-box">
            <h3>Total Revenue</h3>
            <div class="stat-number revenue">$<?php echo number_format($total_revenue, 2); ?></div>
        </div>
    </div>
    
    <div class="carwash-dashboard-content">
        <div class="carwash-recent-bookings">
            <h2>Recent Bookings</h2>
            
            <?php if (!empty($recent_bookings)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Booking #</th>
                            <th>Customer</th>
                            <th>Vehicle Type</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_bookings as $booking): ?>
                            <tr>
                                <td><?php echo esc_html($booking->booking_number); ?></td>
                                <td><?php echo esc_html($booking->customer_name); ?></td>
                                <td><?php echo esc_html($booking->vehicle_type_name); ?></td>
                                <td>$<?php echo number_format($booking->total_price, 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo esc_attr($booking->booking_status); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $booking->booking_status)); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($booking->created_at)); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=carwash-bookings&action=view&id=' . $booking->id); ?>" class="button button-small">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <p>
                    <a href="<?php echo admin_url('admin.php?page=carwash-bookings'); ?>" class="button button-primary">View All Bookings</a>
                </p>
            <?php else: ?>
                <p>No bookings found.</p>
            <?php endif; ?>
        </div>
        
        <div class="carwash-quick-actions">
            <h2>Quick Actions</h2>
            
            <div class="carwash-action-buttons">
                <a href="<?php echo admin_url('admin.php?page=carwash-vehicle-types'); ?>" class="button button-secondary">
                    <span class="dashicons dashicons-car"></span>
                    Manage Vehicle Types
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=carwash-services'); ?>" class="button button-secondary">
                    <span class="dashicons dashicons-admin-tools"></span>
                    Manage Services
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=carwash-los'); ?>" class="button button-secondary">
                    <span class="dashicons dashicons-star-filled"></span>
                    Manage Levels of Service
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=carwash-settings'); ?>" class="button button-secondary">
                    <span class="dashicons dashicons-admin-settings"></span>
                    Settings
                </a>
            </div>
        </div>
    </div>
</div>

