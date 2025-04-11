-- Create database
CREATE DATABASE IF NOT EXISTS real_estate;
USE real_estate;

-- Create cities table
CREATE TABLE IF NOT EXISTS cities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create areas table
CREATE TABLE IF NOT EXISTS areas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city_id INT,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE SET NULL
);

-- Create owners table
CREATE TABLE IF NOT EXISTS owners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create managers table
CREATE TABLE IF NOT EXISTS managers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create properties table
CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    property_type ENUM('rental', 'sale') NOT NULL,
    property_category VARCHAR(100),
    price DECIMAL(12, 2) NOT NULL,
    size DECIMAL(10, 2),
    bedrooms INT,
    bathrooms INT,
    garage BOOLEAN DEFAULT FALSE,
    year_built INT,
    status VARCHAR(50) DEFAULT 'available',
    is_featured BOOLEAN DEFAULT FALSE,
    amenities TEXT,
    location VARCHAR(255),
    city_id INT,
    area_id INT,
    owner_id INT,
    manager_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE SET NULL,
    FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE SET NULL,
    FOREIGN KEY (owner_id) REFERENCES owners(id) ON DELETE SET NULL,
    FOREIGN KEY (manager_id) REFERENCES managers(id) ON DELETE SET NULL
);

-- Create property_images table
CREATE TABLE IF NOT EXISTS property_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

-- Insert sample data for cities
INSERT INTO cities (name) VALUES 
('Kampala'),
('Entebbe'),
('Jinja'),
('Mbarara'),
('Gulu'),
('Wakiso');

-- Insert sample data for areas
INSERT INTO areas (city_id, name) VALUES 
(1, 'Bugolobi'),
(1, 'Kololo'),
(1, 'Nakasero'),
(1, 'Ntinda'),
(1, 'Bukoto'),
(6, 'Kira'),
(6, 'Nansana'),
(6, 'Kyaliwajjala'),
(2, 'Central Entebbe'),
(3, 'Jinja Central');

-- Insert sample data for owners
INSERT INTO owners (name, email, phone, address) VALUES 
('John Doe', 'john.doe@example.com', '+256701234567', 'Kampala, Uganda'),
('Jane Smith', 'jane.smith@example.com', '+256712345678', 'Entebbe, Uganda'),
('Robert Johnson', 'robert.johnson@example.com', '+256723456789', 'Jinja, Uganda');

-- Insert sample data for managers
INSERT INTO managers (name, email, phone, address) VALUES 
('Michael Brown', 'michael.brown@example.com', '+256734567890', 'Kampala, Uganda'),
('Sarah Wilson', 'sarah.wilson@example.com', '+256745678901', 'Wakiso, Uganda'),
('David Lee', 'david.lee@example.com', '+256756789012', 'Mbarara, Uganda');

-- Insert sample data for properties (sales)
INSERT INTO properties (title, description, property_type, property_category, price, size, bedrooms, bathrooms, garage, year_built, status, is_featured, amenities, location, city_id, area_id, owner_id, manager_id) VALUES 
('Gorgeous new house in Kira', '4bed, 4bath house in kira for sale. This beautiful property features modern amenities, spacious rooms, and a lovely garden.', 'sale', 'House', 411000000, 250, 4, 5, TRUE, 2022, 'available', TRUE, 'Swimming Pool,Garden,Security,Parking', 'Kira, Wakiso', 6, 6, 1, 1),
('Luxury Villa in Kololo', 'Stunning 5-bedroom villa with panoramic views of the city. Perfect for a family looking for comfort and luxury.', 'sale', 'Villa', 850000000, 400, 5, 6, TRUE, 2020, 'available', TRUE, 'Swimming Pool,Garden,Security,Parking,Gym,Home Theater', 'Kololo, Kampala', 1, 2, 2, 2),
('Modern Apartment in Nakasero', 'Contemporary 3-bedroom apartment in the heart of Nakasero. Close to shopping centers and restaurants.', 'sale', 'Apartment', 350000000, 150, 3, 3, TRUE, 2021, 'available', FALSE, 'Security,Parking,Gym,Rooftop Terrace', 'Nakasero, Kampala', 1, 3, 3, 3);

-- Insert sample data for properties (rentals)
INSERT INTO properties (title, description, property_type, property_category, price, size, bedrooms, bathrooms, garage, year_built, status, is_featured, amenities, location, city_id, area_id, owner_id, manager_id) VALUES 
('Luxury Apartment in Bugolobi', '2bed, 2bath apartment in Bugolobi for rent. Modern finishes, secure compound, and ample parking.', 'rental', 'Apartment', 1500000, 100, 2, 2, TRUE, 2019, 'available', TRUE, 'Security,Parking,Swimming Pool,Gym', 'Bugolobi, Kampala', 1, 1, 1, 1),
('Spacious Family Home in Ntinda', 'Beautiful 4-bedroom house perfect for a family. Quiet neighborhood with easy access to schools and shopping centers.', 'rental', 'House', 2500000, 200, 4, 3, TRUE, 2018, 'available', TRUE, 'Garden,Security,Parking', 'Ntinda, Kampala', 1, 4, 2, 2),
('Cozy Studio in Bukoto', 'Compact and comfortable studio apartment. Ideal for singles or couples. All utilities included.', 'rental', 'Studio', 800000, 50, 1, 1, FALSE, 2020, 'available', FALSE, 'Security,Parking,Internet', 'Bukoto, Kampala', 1, 5, 3, 3);

-- Insert sample data for property images
INSERT INTO property_images (property_id, image_path, is_primary) VALUES 
(1, 'assets/images/property1.jpg', TRUE),
(1, 'assets/images/property1-2.jpg', FALSE),
(1, 'assets/images/property1-3.jpg', FALSE),
(2, 'assets/images/property2.jpg', TRUE),
(2, 'assets/images/property2-2.jpg', FALSE),
(3, 'assets/images/property3.jpg', TRUE),
(4, 'assets/images/property4.jpg', TRUE),
(4, 'assets/images/property4-2.jpg', FALSE),
(5, 'assets/images/property5.jpg', TRUE),
(6, 'assets/images/property6.jpg', TRUE);
