-- Update phone field in property_owner table to VARCHAR to support country codes
ALTER TABLE property_owner
MODIFY COLUMN phone VARCHAR(20) NOT NULL;
