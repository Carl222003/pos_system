-- Add new columns to ingredients table if they don't exist
ALTER TABLE ingredients 
ADD COLUMN IF NOT EXISTS minimum_stock DECIMAL(10,2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS supplier_name VARCHAR(255),
ADD COLUMN IF NOT EXISTS supplier_contact VARCHAR(100),
ADD COLUMN IF NOT EXISTS storage_location VARCHAR(255),
ADD COLUMN IF NOT EXISTS cost_per_unit DECIMAL(10,2);

-- Update existing records to have default values
UPDATE ingredients SET minimum_stock = 0 WHERE minimum_stock IS NULL; 