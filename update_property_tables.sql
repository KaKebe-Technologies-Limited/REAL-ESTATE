-- Add bedrooms, bathrooms, and description fields to rental_property table
ALTER TABLE rental_property
ADD COLUMN bedrooms INT DEFAULT 0 AFTER property_size,
ADD COLUMN bathrooms FLOAT DEFAULT 0 AFTER bedrooms,
ADD COLUMN description TEXT AFTER property_name;

-- Add bedrooms, bathrooms, and description fields to sales_property table
ALTER TABLE sales_property
ADD COLUMN bedrooms INT DEFAULT 0 AFTER property_size,
ADD COLUMN bathrooms FLOAT DEFAULT 0 AFTER bedrooms,
ADD COLUMN description TEXT AFTER property_name;

-- Update existing records with default values
UPDATE rental_property SET description = 'No description available' WHERE description IS NULL;
UPDATE sales_property SET description = 'No description available' WHERE description IS NULL;
