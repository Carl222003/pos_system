CREATE TABLE IF NOT EXISTS archive_ingredient (
    archive_id INT AUTO_INCREMENT PRIMARY KEY,
    original_id INT NOT NULL,
    ingredient_name VARCHAR(255) NOT NULL,
    category_id INT,
    quantity DECIMAL(10,2) DEFAULT 0,
    unit VARCHAR(50),
    status VARCHAR(50) DEFAULT 'archived',
    archived_by INT,
    archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Add any other fields from your main ingredient table as needed
    notes TEXT,
    FOREIGN KEY (category_id) REFERENCES pos_category(category_id)
); 