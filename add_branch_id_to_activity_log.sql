-- Add branch_id column to pos_activity_log table if it doesn't exist
ALTER TABLE pos_activity_log 
ADD COLUMN IF NOT EXISTS branch_id INT NULL 
AFTER user_id;

-- Add foreign key constraint if it doesn't exist
-- Note: This will only work if the constraint doesn't already exist
-- ALTER TABLE pos_activity_log 
-- ADD CONSTRAINT fk_activity_log_branch 
-- FOREIGN KEY (branch_id) REFERENCES pos_branch(branch_id) 
-- ON DELETE SET NULL; 