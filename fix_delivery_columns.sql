-- Fix delivery status columns for ingredient_requests table
-- Run this SQL script in your database to add the missing columns

-- Check if delivery_status column exists, if not add it
ALTER TABLE ingredient_requests 
ADD COLUMN IF NOT EXISTS delivery_status ENUM('pending', 'on_delivery', 'delivered', 'returned', 'cancelled') DEFAULT 'pending' 
AFTER status;

-- Check if delivery_date column exists, if not add it
ALTER TABLE ingredient_requests 
ADD COLUMN IF NOT EXISTS delivery_date TIMESTAMP NULL 
AFTER delivery_status;

-- Check if delivery_notes column exists, if not add it
ALTER TABLE ingredient_requests 
ADD COLUMN IF NOT EXISTS delivery_notes TEXT NULL 
AFTER delivery_date;

-- Update existing records to have 'pending' delivery status if they don't have one
UPDATE ingredient_requests 
SET delivery_status = 'pending' 
WHERE delivery_status IS NULL; 