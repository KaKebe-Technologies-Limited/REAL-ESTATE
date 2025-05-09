-- Add subscription fields to property_owner table
ALTER TABLE property_owner
ADD COLUMN subscription_start_date TIMESTAMP NULL,
ADD COLUMN subscription_end_date TIMESTAMP NULL,
ADD COLUMN subscription_status ENUM('active', 'expired', 'pending') DEFAULT 'pending',
ADD COLUMN last_renewal_date TIMESTAMP NULL;

-- Create owner_subscriptions table to track subscription payments
CREATE TABLE IF NOT EXISTS owner_subscriptions (
    subscription_id INT AUTO_INCREMENT PRIMARY KEY, 
    owner_id INT UNSIGNED,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'UGX',
    transaction_id VARCHAR(100),
    order_tracking_id VARCHAR(100),
    merchant_reference VARCHAR(100),
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    subscription_months INT DEFAULT 4,
    subscription_start_date TIMESTAMP NULL,
    subscription_end_date TIMESTAMP NULL,
    FOREIGN KEY (owner_id) REFERENCES property_owner(owner_id) ON DELETE CASCADE
);

-- Update existing owners to have active subscriptions for 4 months from now
UPDATE property_owner 
SET 
    subscription_start_date = NOW(),
    subscription_end_date = DATE_ADD(NOW(), INTERVAL 4 MONTH),
    subscription_status = 'active',
    last_renewal_date = NOW()
WHERE payment_status = 'paid';
