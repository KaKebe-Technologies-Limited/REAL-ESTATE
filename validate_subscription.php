<?php
/**
 * Subscription validation functions for property owners
 * 
 * This file contains functions to check and validate owner subscriptions
 */

require_once 'config.php';
require_once 'log_activity.php';

/**
 * Check if an owner's subscription is valid
 * 
 * @param int $owner_id The owner ID to check
 * @return bool True if subscription is active, false otherwise
 */
function isSubscriptionValid($owner_id) {
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    if ($conn->connect_error) {
        error_log("Connection failed: " . $conn->connect_error);
        return false;
    }
    
    // Get owner subscription status
    $query = "SELECT subscription_status, subscription_end_date FROM property_owner WHERE owner_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $owner_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $owner = $result->fetch_assoc();
        
        // If subscription is active and end date is in the future
        if ($owner['subscription_status'] === 'active' && strtotime($owner['subscription_end_date']) > time()) {
            $stmt->close();
            $conn->close();
            return true;
        }
        
        // If subscription has expired, update the status
        if ($owner['subscription_status'] === 'active' && strtotime($owner['subscription_end_date']) <= time()) {
            updateSubscriptionStatus($owner_id, 'expired', $conn);
        }
    }
    
    $stmt->close();
    $conn->close();
    return false;
}

/**
 * Update an owner's subscription status
 * 
 * @param int $owner_id The owner ID
 * @param string $status The new status ('active', 'expired', 'pending')
 * @param mysqli $conn Optional database connection
 * @return bool True if update was successful, false otherwise
 */
function updateSubscriptionStatus($owner_id, $status, $conn = null) {
    $close_conn = false;
    
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
        $close_conn = true;
        
        if ($conn->connect_error) {
            error_log("Connection failed: " . $conn->connect_error);
            return false;
        }
    }
    
    $query = "UPDATE property_owner SET subscription_status = ? WHERE owner_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('si', $status, $owner_id);
    $result = $stmt->execute();
    
    if ($result) {
        // Log the activity
        $activity_type = ($status === 'active') ? 'subscription_activated' : 'subscription_expired';
        $title = ($status === 'active') ? 'Subscription Activated' : 'Subscription Expired';
        $description = "Owner ID: $owner_id - Subscription status changed to $status";
        $icon = ($status === 'active') ? 'fas fa-check-circle' : 'fas fa-times-circle';
        $bg_class = ($status === 'active') ? 'bg-success' : 'bg-danger';
        
        logActivity($activity_type, $title, $description, $icon, $bg_class);
    }
    
    $stmt->close();
    
    if ($close_conn) {
        $conn->close();
    }
    
    return $result;
}

/**
 * Renew an owner's subscription for a specified number of months
 * 
 * @param int $owner_id The owner ID
 * @param int $months Number of months to extend subscription
 * @param string $transaction_id Payment transaction ID
 * @param string $payment_method Payment method used
 * @return bool True if renewal was successful, false otherwise
 */
function renewSubscription($owner_id, $months = 4, $transaction_id = null, $payment_method = null) {
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    if ($conn->connect_error) {
        error_log("Connection failed: " . $conn->connect_error);
        return false;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Get current subscription info
        $query = "SELECT subscription_end_date, subscription_status FROM property_owner WHERE owner_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $owner_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $owner = $result->fetch_assoc();
        $stmt->close();
        
        // Calculate new subscription dates
        $start_date = date('Y-m-d H:i:s');
        $base_date = ($owner && $owner['subscription_status'] === 'active' && strtotime($owner['subscription_end_date']) > time()) 
            ? $owner['subscription_end_date'] 
            : $start_date;
        $end_date = date('Y-m-d H:i:s', strtotime($base_date . " +$months months"));
        
        // Update owner subscription
        $update_query = "UPDATE property_owner SET 
                        subscription_start_date = ?, 
                        subscription_end_date = ?, 
                        subscription_status = 'active',
                        last_renewal_date = NOW(),
                        payment_status = 'paid'
                        WHERE owner_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param('ssi', $start_date, $end_date, $owner_id);
        $update_result = $update_stmt->execute();
        $update_stmt->close();
        
        if (!$update_result) {
            throw new Exception("Failed to update owner subscription");
        }
        
        // Record subscription payment
        if ($transaction_id) {
            $amount = 30000; // Default amount in UGX
            $currency = 'UGX';
            
            $payment_query = "INSERT INTO owner_subscriptions (
                            owner_id, 
                            amount, 
                            currency, 
                            transaction_id, 
                            payment_method, 
                            payment_status, 
                            subscription_months,
                            subscription_start_date,
                            subscription_end_date
                        ) VALUES (?, ?, ?, ?, ?, 'completed', ?, ?, ?)";
            $payment_stmt = $conn->prepare($payment_query);
            $payment_stmt->bind_param('idssssss', 
                $owner_id, 
                $amount, 
                $currency, 
                $transaction_id, 
                $payment_method, 
                $months,
                $start_date,
                $end_date
            );
            $payment_result = $payment_stmt->execute();
            $payment_stmt->close();
            
            if (!$payment_result) {
                throw new Exception("Failed to record subscription payment");
            }
        }
        
        // Log the activity
        $owner_query = "SELECT CONCAT(first_name, ' ', last_name) as owner_name FROM property_owner WHERE owner_id = ?";
        $owner_stmt = $conn->prepare($owner_query);
        $owner_stmt->bind_param('i', $owner_id);
        $owner_stmt->execute();
        $owner_result = $owner_stmt->get_result();
        $owner_data = $owner_result->fetch_assoc();
        $owner_name = $owner_data ? $owner_data['owner_name'] : "Owner #$owner_id";
        $owner_stmt->close();
        
        logActivity(
            'subscription_renewed', 
            'Subscription Renewed', 
            "Owner $owner_name renewed subscription for $months months until " . date('Y-m-d', strtotime($end_date)), 
            'fas fa-sync', 
            'bg-success'
        );
        
        // Commit transaction
        $conn->commit();
        $conn->close();
        return true;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Subscription renewal error: " . $e->getMessage());
        $conn->close();
        return false;
    }
}
?>
