-- Create branch-ingredient relationship table
USE pos;

-- Create branch_ingredient table to manage branch-specific ingredients
CREATE TABLE IF NOT EXISTS branch_ingredient (
    branch_ingredient_id INT PRIMARY KEY AUTO_INCREMENT,
    branch_id INT NOT NULL,
    ingredient_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    minimum_stock INT NOT NULL DEFAULT 5,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_branch_ingredient (branch_id, ingredient_id),
    FOREIGN KEY (branch_id) REFERENCES pos_branch(branch_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(ingredient_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add comment to document the table
ALTER TABLE branch_ingredient 
COMMENT = 'Manages branch-specific ingredient assignments and quantities';

-- Show the table structure
DESCRIBE branch_ingredient;
