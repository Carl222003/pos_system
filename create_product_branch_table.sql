-- Create product_branch table to link products with branches
CREATE TABLE IF NOT EXISTS product_branch (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    branch_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_product_branch (product_id, branch_id),
    FOREIGN KEY (product_id) REFERENCES pos_product(product_id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES pos_branch(branch_id) ON DELETE CASCADE
);

-- Add index for better performance
CREATE INDEX idx_product_branch_product ON product_branch(product_id);
CREATE INDEX idx_product_branch_branch ON product_branch(branch_id); 