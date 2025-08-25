-- Add quantity column to pos_product table
USE pos_db;

-- Add quantity column to pos_product table
ALTER TABLE pos_product 
ADD COLUMN product_quantity INT NOT NULL DEFAULT 0 
AFTER product_status;

-- Update existing products to have a default quantity of 10
UPDATE pos_product 
SET product_quantity = 10 
WHERE product_quantity = 0;

-- Add comment to document the column
ALTER TABLE pos_product 
MODIFY COLUMN product_quantity INT NOT NULL DEFAULT 0 
COMMENT 'Total stock quantity for this product across all branches';

-- Show the updated table structure
DESCRIBE pos_product;
