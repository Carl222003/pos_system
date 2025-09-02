-- Create order status log table for tracking order status changes
CREATE TABLE IF NOT EXISTS `pos_order_status_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `order_id` (`order_id`),
  KEY `user_id` (`user_id`),
  KEY `changed_at` (`changed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add updated_at column to pos_orders if it doesn't exist
ALTER TABLE `pos_orders` 
ADD COLUMN IF NOT EXISTS `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP;

-- Update existing orders to have a default status if none exists
UPDATE `pos_orders` SET `status` = 'PENDING' WHERE `status` IS NULL OR `status` = '';

-- Ensure order_type column exists and has proper values
ALTER TABLE `pos_orders` 
MODIFY COLUMN `order_type` varchar(50) DEFAULT 'TAKE OUT';

-- Update existing order types to match KDS requirements
UPDATE `pos_orders` SET `order_type` = 'DINE IN' WHERE `order_type` IN ('DINE-IN', 'DINEIN', 'DINE IN');
UPDATE `pos_orders` SET `order_type` = 'TAKE OUT' WHERE `order_type` IN ('TAKEOUT', 'TAKE-OUT', 'TAKE OUT');
UPDATE `pos_orders` SET `order_type` = 'DELIVERY' WHERE `order_type` IN ('DELIVER', 'DELIVERY');
UPDATE `pos_orders` SET `order_type` = 'DRIVE THRU' WHERE `order_type` IN ('DRIVE-THRU', 'DRIVETHRU', 'DRIVE THRU');
