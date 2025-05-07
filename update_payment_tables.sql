-- Note: payment_status field already exists in property_owner table
-- Uncomment the following if it doesn't exist:
-- ALTER TABLE property_owner
-- ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER date_created;

-- Create owner_payments table to track payment transactions
CREATE TABLE IF NOT EXISTS owner_payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT UNSIGNED,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'UGX',
    transaction_id VARCHAR(100),
    order_tracking_id VARCHAR(100),
    merchant_reference VARCHAR(100),
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES property_owner(owner_id) ON DELETE CASCADE
);
