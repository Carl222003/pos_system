-- =====================================================
-- STOCK REQUESTS UPDATES DATABASE STRUCTURE
-- =====================================================

-- Create the main ingredient_requests table
CREATE TABLE IF NOT EXISTS `ingredient_requests` (
    `request_id` INT AUTO_INCREMENT PRIMARY KEY,
    `stockman_id` INT NOT NULL,
    `branch_id` INT NOT NULL,
    `ingredient_id` INT NOT NULL,
    `requested_quantity` DECIMAL(10,2) NOT NULL,
    `requested_unit` VARCHAR(50) NOT NULL,
    `request_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('PENDING', 'APPROVED', 'REJECTED') DEFAULT 'PENDING',
    `delivery_status` ENUM('PENDING', 'ON_DELIVERY', 'DELIVERED', 'RETURNED', 'CANCELLED') DEFAULT 'PENDING',
    `admin_notes` TEXT NULL,
    `stockman_delivery_notes` TEXT NULL,
    `delivery_date` DATETIME NULL,
    `updated_by_user_id` INT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `urgency_level` ENUM('LOW', 'MEDIUM', 'HIGH', 'CRITICAL') DEFAULT 'LOW',
    `priority` ENUM('NORMAL', 'HIGH', 'URGENT') DEFAULT 'NORMAL',
    `reason` TEXT NOT NULL,
    `additional_notes` TEXT NULL,
    INDEX `idx_stockman_id` (`stockman_id`),
    INDEX `idx_branch_id` (`branch_id`),
    INDEX `idx_ingredient_id` (`ingredient_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_delivery_status` (`delivery_status`),
    INDEX `idx_request_date` (`request_date`),
    INDEX `idx_updated_by` (`updated_by_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create stock_request_activity_log table for tracking all changes
CREATE TABLE IF NOT EXISTS `stock_request_activity_log` (
    `log_id` INT AUTO_INCREMENT PRIMARY KEY,
    `request_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `action` VARCHAR(100) NOT NULL,
    `old_status` VARCHAR(50) NULL,
    `new_status` VARCHAR(50) NULL,
    `old_delivery_status` VARCHAR(50) NULL,
    `new_delivery_status` VARCHAR(50) NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_request_id` (`request_id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create stock_request_notifications table
CREATE TABLE IF NOT EXISTS `stock_request_notifications` (
    `notification_id` INT AUTO_INCREMENT PRIMARY KEY,
    `request_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `notification_type` ENUM('REQUEST_CREATED', 'REQUEST_APPROVED', 'REQUEST_REJECTED', 'DELIVERY_UPDATED', 'DELIVERY_COMPLETED') NOT NULL,
    `message` TEXT NOT NULL,
    `is_read` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_request_id` (`request_id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_is_read` (`is_read`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key constraints
ALTER TABLE `ingredient_requests`
ADD CONSTRAINT `fk_ingredient_requests_stockman` 
    FOREIGN KEY (`stockman_id`) REFERENCES `pos_user`(`user_id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk_ingredient_requests_branch` 
    FOREIGN KEY (`branch_id`) REFERENCES `pos_branch`(`branch_id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk_ingredient_requests_ingredient` 
    FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients`(`ingredient_id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk_ingredient_requests_updated_by` 
    FOREIGN KEY (`updated_by_user_id`) REFERENCES `pos_user`(`user_id`) ON DELETE SET NULL;

ALTER TABLE `stock_request_activity_log`
ADD CONSTRAINT `fk_activity_log_request` 
    FOREIGN KEY (`request_id`) REFERENCES `ingredient_requests`(`request_id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk_activity_log_user` 
    FOREIGN KEY (`user_id`) REFERENCES `pos_user`(`user_id`) ON DELETE CASCADE;

ALTER TABLE `stock_request_notifications`
ADD CONSTRAINT `fk_notifications_request` 
    FOREIGN KEY (`request_id`) REFERENCES `ingredient_requests`(`request_id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk_notifications_user` 
    FOREIGN KEY (`user_id`) REFERENCES `pos_user`(`user_id`) ON DELETE CASCADE;

-- Create indexes for better performance
CREATE INDEX `idx_ingredient_requests_composite` ON `ingredient_requests` (`stockman_id`, `status`, `request_date`);
CREATE INDEX `idx_ingredient_requests_delivery` ON `ingredient_requests` (`delivery_status`, `status`);
CREATE INDEX `idx_activity_log_composite` ON `stock_request_activity_log` (`request_id`, `created_at`);
CREATE INDEX `idx_notifications_composite` ON `stock_request_notifications` (`user_id`, `is_read`, `created_at`);

-- Insert sample data for testing
INSERT INTO `ingredient_requests` (
    `stockman_id`, 
    `branch_id`, 
    `ingredient_id`, 
    `requested_quantity`, 
    `requested_unit`, 
    `request_date`, 
    `status`, 
    `delivery_status`, 
    `admin_notes`, 
    `urgency_level`, 
    `priority`, 
    `reason`
) VALUES 
-- Sample pending requests
(2, 1, 1, 11.00, 'pieces', '2025-08-31 12:12:00', 'PENDING', 'PENDING', NULL, 'MEDIUM', 'NORMAL', 'Running low on stock'),
(2, 1, 2, 2.00, 'pieces', '2025-08-31 12:12:00', 'PENDING', 'PENDING', NULL, 'LOW', 'NORMAL', 'Regular restocking'),
(2, 1, 1, 1.00, 'pieces', '2025-08-31 12:46:00', 'PENDING', 'PENDING', NULL, 'HIGH', 'HIGH', 'Critical shortage'),

-- Sample approved requests
(2, 1, 1, 10.00, 'pieces', '2025-08-26 12:21:00', 'APPROVED', 'PENDING', 'Approved for delivery on Monday', 'MEDIUM', 'NORMAL', 'Weekly restocking'),
(2, 1, 1, 5.00, 'pieces', '2025-08-26 12:13:00', 'APPROVED', 'PENDING', 'Approved - please deliver by end of day', 'HIGH', 'HIGH', 'Running out of stock'),
(2, 1, 2, 2.00, 'pieces', '2025-08-26 12:12:00', 'APPROVED', 'PENDING', 'Approved for immediate delivery', 'LOW', 'NORMAL', 'Regular restocking'),

-- Sample rejected request
(2, 1, 1, 5.00, 'pieces', '2025-08-26 12:06:00', 'REJECTED', 'CANCELLED', 'Insufficient stock available', 'MEDIUM', 'NORMAL', 'Regular restocking');

-- Insert sample activity log entries
INSERT INTO `stock_request_activity_log` (
    `request_id`, 
    `user_id`, 
    `action`, 
    `old_status`, 
    `new_status`, 
    `notes`
) VALUES 
(4, 1, 'REQUEST_APPROVED', 'PENDING', 'APPROVED', 'Approved for delivery on Monday'),
(5, 1, 'REQUEST_APPROVED', 'PENDING', 'APPROVED', 'Approved - please deliver by end of day'),
(6, 1, 'REQUEST_APPROVED', 'PENDING', 'APPROVED', 'Approved for immediate delivery'),
(7, 1, 'REQUEST_REJECTED', 'PENDING', 'REJECTED', 'Insufficient stock available');

-- Insert sample notifications
INSERT INTO `stock_request_notifications` (
    `request_id`, 
    `user_id`, 
    `notification_type`, 
    `message`
) VALUES 
(4, 2, 'REQUEST_APPROVED', 'Your request for 10 pieces of Coke Mismo has been approved'),
(5, 2, 'REQUEST_APPROVED', 'Your request for 5 pieces of Coke Mismo has been approved'),
(6, 2, 'REQUEST_APPROVED', 'Your request for 2 pieces of Water has been approved'),
(7, 2, 'REQUEST_REJECTED', 'Your request for 5 pieces of Coke Mismo has been rejected');

-- Create views for easier data access
CREATE VIEW `vw_stock_requests_summary` AS
SELECT 
    ir.request_id,
    ir.request_date,
    i.ingredient_name,
    ir.requested_quantity,
    ir.requested_unit,
    ir.status,
    ir.delivery_status,
    ir.admin_notes,
    ir.stockman_delivery_notes,
    ir.delivery_date,
    u1.username as stockman_name,
    u2.username as updated_by_name,
    b.branch_name,
    ir.urgency_level,
    ir.priority,
    ir.reason
FROM ingredient_requests ir
JOIN ingredients i ON ir.ingredient_id = i.ingredient_id
JOIN pos_user u1 ON ir.stockman_id = u1.user_id
LEFT JOIN pos_user u2 ON ir.updated_by_user_id = u2.user_id
JOIN pos_branch b ON ir.branch_id = b.branch_id
ORDER BY ir.request_date DESC;

-- Create view for pending requests
CREATE VIEW `vw_pending_requests` AS
SELECT * FROM vw_stock_requests_summary 
WHERE status = 'PENDING' 
ORDER BY 
    CASE urgency_level 
        WHEN 'CRITICAL' THEN 1 
        WHEN 'HIGH' THEN 2 
        WHEN 'MEDIUM' THEN 3 
        WHEN 'LOW' THEN 4 
    END,
    request_date ASC;

-- Create view for approved requests pending delivery
CREATE VIEW `vw_approved_pending_delivery` AS
SELECT * FROM vw_stock_requests_summary 
WHERE status = 'APPROVED' AND delivery_status = 'PENDING'
ORDER BY request_date ASC;

-- Create stored procedure for creating new requests
DELIMITER //
CREATE PROCEDURE `sp_create_stock_request`(
    IN p_stockman_id INT,
    IN p_branch_id INT,
    IN p_ingredient_id INT,
    IN p_requested_quantity DECIMAL(10,2),
    IN p_requested_unit VARCHAR(50),
    IN p_urgency_level ENUM('LOW', 'MEDIUM', 'HIGH', 'CRITICAL'),
    IN p_priority ENUM('NORMAL', 'HIGH', 'URGENT'),
    IN p_reason TEXT,
    IN p_additional_notes TEXT
)
BEGIN
    DECLARE new_request_id INT;
    
    -- Insert new request
    INSERT INTO ingredient_requests (
        stockman_id, branch_id, ingredient_id, requested_quantity, 
        requested_unit, urgency_level, priority, reason, additional_notes
    ) VALUES (
        p_stockman_id, p_branch_id, p_ingredient_id, p_requested_quantity,
        p_requested_unit, p_urgency_level, p_priority, p_reason, p_additional_notes
    );
    
    SET new_request_id = LAST_INSERT_ID();
    
    -- Log the activity
    INSERT INTO stock_request_activity_log (
        request_id, user_id, action, new_status, notes
    ) VALUES (
        new_request_id, p_stockman_id, 'REQUEST_CREATED', 'PENDING', 
        CONCAT('Request created for ', p_requested_quantity, ' ', p_requested_unit)
    );
    
    -- Create notification for admin
    INSERT INTO stock_request_notifications (
        request_id, user_id, notification_type, message
    ) VALUES (
        new_request_id, 1, 'REQUEST_CREATED', 
        CONCAT('New stock request created by stockman for ', p_requested_quantity, ' ', p_requested_unit)
    );
    
    SELECT new_request_id as request_id;
END //
DELIMITER ;

-- Create stored procedure for updating request status
DELIMITER //
CREATE PROCEDURE `sp_update_request_status`(
    IN p_request_id INT,
    IN p_admin_id INT,
    IN p_new_status ENUM('APPROVED', 'REJECTED'),
    IN p_admin_notes TEXT
)
BEGIN
    DECLARE old_status VARCHAR(50);
    DECLARE stockman_id_val INT;
    DECLARE ingredient_name_val VARCHAR(255);
    DECLARE requested_quantity_val DECIMAL(10,2);
    DECLARE requested_unit_val VARCHAR(50);
    
    -- Get current status and request details
    SELECT status, stockman_id, requested_quantity, requested_unit 
    INTO old_status, stockman_id_val, requested_quantity_val, requested_unit_val
    FROM ingredient_requests ir
    JOIN ingredients i ON ir.ingredient_id = i.ingredient_id
    WHERE ir.request_id = p_request_id;
    
    -- Get ingredient name
    SELECT ingredient_name INTO ingredient_name_val
    FROM ingredients i
    JOIN ingredient_requests ir ON i.ingredient_id = ir.ingredient_id
    WHERE ir.request_id = p_request_id;
    
    -- Update request status
    UPDATE ingredient_requests 
    SET status = p_new_status,
        admin_notes = p_admin_notes,
        updated_by_user_id = p_admin_id,
        delivery_status = CASE 
            WHEN p_new_status = 'REJECTED' THEN 'CANCELLED'
            ELSE delivery_status
        END
    WHERE request_id = p_request_id;
    
    -- Log the activity
    INSERT INTO stock_request_activity_log (
        request_id, user_id, action, old_status, new_status, notes
    ) VALUES (
        p_request_id, p_admin_id, 
        CASE WHEN p_new_status = 'APPROVED' THEN 'REQUEST_APPROVED' ELSE 'REQUEST_REJECTED' END,
        old_status, p_new_status, p_admin_notes
    );
    
    -- Create notification for stockman
    INSERT INTO stock_request_notifications (
        request_id, user_id, notification_type, message
    ) VALUES (
        p_request_id, stockman_id_val, 
        CASE WHEN p_new_status = 'APPROVED' THEN 'REQUEST_APPROVED' ELSE 'REQUEST_REJECTED' END,
        CONCAT('Your request for ', requested_quantity_val, ' ', requested_unit_val, ' of ', ingredient_name_val, ' has been ', LOWER(p_new_status))
    );
END //
DELIMITER ;

-- Create stored procedure for updating delivery status
DELIMITER //
CREATE PROCEDURE `sp_update_delivery_status`(
    IN p_request_id INT,
    IN p_stockman_id INT,
    IN p_delivery_status ENUM('DELIVERED', 'RETURNED', 'CANCELLED'),
    IN p_delivery_date DATETIME,
    IN p_delivery_notes TEXT
)
BEGIN
    DECLARE old_delivery_status VARCHAR(50);
    DECLARE current_status VARCHAR(50);
    
    -- Get current delivery status and request status
    SELECT delivery_status, status 
    INTO old_delivery_status, current_status
    FROM ingredient_requests 
    WHERE request_id = p_request_id;
    
    -- Only allow updates if request is approved and not already in final status
    IF current_status = 'APPROVED' AND old_delivery_status NOT IN ('DELIVERED', 'RETURNED', 'CANCELLED') THEN
        -- Update delivery status
        UPDATE ingredient_requests 
        SET delivery_status = p_delivery_status,
            delivery_date = p_delivery_date,
            stockman_delivery_notes = p_delivery_notes,
            updated_by_user_id = p_stockman_id
        WHERE request_id = p_request_id;
        
        -- Log the activity
        INSERT INTO stock_request_activity_log (
            request_id, user_id, action, old_delivery_status, new_delivery_status, notes
        ) VALUES (
            p_request_id, p_stockman_id, 'DELIVERY_UPDATED', old_delivery_status, p_delivery_status, p_delivery_notes
        );
        
        -- Create notification for admin
        INSERT INTO stock_request_notifications (
            request_id, user_id, notification_type, message
        ) VALUES (
            p_request_id, 1, 'DELIVERY_UPDATED', 
            CONCAT('Delivery status updated to ', UPPER(p_delivery_status))
        );
        
        SELECT 'SUCCESS' as result, 'Delivery status updated successfully' as message;
    ELSE
        SELECT 'ERROR' as result, 'Cannot update delivery status for this request' as message;
    END IF;
END //
DELIMITER ;

-- Create function to get request statistics
DELIMITER //
CREATE FUNCTION `fn_get_request_stats`(p_branch_id INT) 
RETURNS JSON
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE result JSON;
    
    SELECT JSON_OBJECT(
        'total_requests', COUNT(*),
        'pending_requests', SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END),
        'approved_requests', SUM(CASE WHEN status = 'APPROVED' THEN 1 ELSE 0 END),
        'rejected_requests', SUM(CASE WHEN status = 'REJECTED' THEN 1 ELSE 0 END),
        'pending_delivery', SUM(CASE WHEN status = 'APPROVED' AND delivery_status = 'PENDING' THEN 1 ELSE 0 END),
        'delivered', SUM(CASE WHEN delivery_status = 'DELIVERED' THEN 1 ELSE 0 END),
        'returned', SUM(CASE WHEN delivery_status = 'RETURNED' THEN 1 ELSE 0 END),
        'cancelled', SUM(CASE WHEN delivery_status = 'CANCELLED' THEN 1 ELSE 0 END)
    ) INTO result
    FROM ingredient_requests
    WHERE branch_id = p_branch_id;
    
    RETURN result;
END //
DELIMITER ;

-- Create trigger to update activity log on request status change
DELIMITER //
CREATE TRIGGER `tr_ingredient_requests_status_update`
AFTER UPDATE ON `ingredient_requests`
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO stock_request_activity_log (
            request_id, user_id, action, old_status, new_status, notes
        ) VALUES (
            NEW.request_id, 
            COALESCE(NEW.updated_by_user_id, OLD.updated_by_user_id),
            CONCAT('STATUS_CHANGED_TO_', UPPER(NEW.status)),
            OLD.status, 
            NEW.status,
            CASE 
                WHEN NEW.status = 'APPROVED' THEN 'Request approved by admin'
                WHEN NEW.status = 'REJECTED' THEN 'Request rejected by admin'
                ELSE 'Status updated'
            END
        );
    END IF;
    
    IF OLD.delivery_status != NEW.delivery_status THEN
        INSERT INTO stock_request_activity_log (
            request_id, user_id, action, old_delivery_status, new_delivery_status, notes
        ) VALUES (
            NEW.request_id, 
            COALESCE(NEW.updated_by_user_id, OLD.updated_by_user_id),
            CONCAT('DELIVERY_STATUS_CHANGED_TO_', UPPER(NEW.delivery_status)),
            OLD.delivery_status, 
            NEW.delivery_status,
            COALESCE(NEW.stockman_delivery_notes, 'Delivery status updated')
        );
    END IF;
END //
DELIMITER ;

-- Grant permissions (adjust as needed for your database setup)
-- GRANT SELECT, INSERT, UPDATE, DELETE ON ingredient_requests TO 'pos_user'@'localhost';
-- GRANT SELECT, INSERT ON stock_request_activity_log TO 'pos_user'@'localhost';
-- GRANT SELECT, INSERT, UPDATE ON stock_request_notifications TO 'pos_user'@'localhost';
-- GRANT EXECUTE ON PROCEDURE sp_create_stock_request TO 'pos_user'@'localhost';
-- GRANT EXECUTE ON PROCEDURE sp_update_request_status TO 'pos_user'@'localhost';
-- GRANT EXECUTE ON PROCEDURE sp_update_delivery_status TO 'pos_user'@'localhost';

-- Display completion message
SELECT 'Stock Requests Updates database structure created successfully!' as message;
