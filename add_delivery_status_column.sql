-- Add delivery_status column to ingredient_requests table
ALTER TABLE ingredient_requests 
ADD COLUMN delivery_status ENUM('pending', 'on_delivery', 'delivered', 'returned', 'cancelled') DEFAULT 'pending' 
AFTER status;

-- Add delivery_date column to track when delivery was completed
ALTER TABLE ingredient_requests 
ADD COLUMN delivery_date TIMESTAMP NULL 
AFTER delivery_status;

-- Add delivery_notes column for delivery-related notes
ALTER TABLE ingredient_requests 
ADD COLUMN delivery_notes TEXT NULL 
AFTER delivery_date; 