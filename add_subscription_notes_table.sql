-- Create owner_subscription_notes table to track admin notes on subscription extensions
CREATE TABLE IF NOT EXISTS owner_subscription_notes (
    note_id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT UNSIGNED, 
    admin_id INT UNSIGNED,
    note TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES property_owner(owner_id) ON DELETE CASCADE
);
