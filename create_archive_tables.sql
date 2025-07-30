-- Create archive table for ingredient requests
CREATE TABLE IF NOT EXISTS archive_ingredient_requests (
    archive_id INT PRIMARY KEY AUTO_INCREMENT,
    original_id INT NOT NULL,
    branch_id INT,
    request_date DATETIME,
    ingredients TEXT,
    status VARCHAR(50),
    delivery_status ENUM('pending', 'on_delivery', 'delivered', 'returned', 'cancelled') DEFAULT 'pending',
    delivery_date TIMESTAMP NULL,
    delivery_notes TEXT,
    notes TEXT,
    updated_by INT,
    updated_at TIMESTAMP NULL,
    archived_by INT,
    archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_original_id (original_id),
    INDEX idx_branch_id (branch_id),
    INDEX idx_archived_at (archived_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 