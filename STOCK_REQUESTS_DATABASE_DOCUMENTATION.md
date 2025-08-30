# Stock Requests Updates Database Documentation

## Overview

The Stock Requests Updates system provides a comprehensive database structure for managing ingredient requests from stockmen to administrators, including approval workflows, delivery tracking, and activity logging.

## Database Schema

### 1. Main Tables

#### `ingredient_requests` - Core Request Table

**Purpose**: Stores all ingredient requests made by stockmen

**Columns**:
- `request_id` (INT, AUTO_INCREMENT, PRIMARY KEY) - Unique identifier
- `stockman_id` (INT, NOT NULL) - Foreign key to pos_user table
- `branch_id` (INT, NOT NULL) - Foreign key to pos_branch table
- `ingredient_id` (INT, NOT NULL) - Foreign key to ingredients table
- `requested_quantity` (DECIMAL(10,2), NOT NULL) - Quantity requested
- `requested_unit` (VARCHAR(50), NOT NULL) - Unit of measurement
- `request_date` (DATETIME, DEFAULT CURRENT_TIMESTAMP) - When request was made
- `status` (ENUM('PENDING', 'APPROVED', 'REJECTED'), DEFAULT 'PENDING') - Request status
- `delivery_status` (ENUM('PENDING', 'ON_DELIVERY', 'DELIVERED', 'RETURNED', 'CANCELLED'), DEFAULT 'PENDING') - Delivery status
- `admin_notes` (TEXT, NULL) - Notes from admin during approval/rejection
- `stockman_delivery_notes` (TEXT, NULL) - Notes from stockman during delivery update
- `delivery_date` (DATETIME, NULL) - When delivery was completed/updated
- `updated_by_user_id` (INT, NULL) - Who last updated the request
- `updated_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) - Last update timestamp
- `urgency_level` (ENUM('LOW', 'MEDIUM', 'HIGH', 'CRITICAL'), DEFAULT 'LOW') - Urgency of request
- `priority` (ENUM('NORMAL', 'HIGH', 'URGENT'), DEFAULT 'NORMAL') - Priority level
- `reason` (TEXT, NOT NULL) - Reason for the request
- `additional_notes` (TEXT, NULL) - Additional notes from stockman

**Indexes**:
- `idx_stockman_id` - For filtering by stockman
- `idx_branch_id` - For filtering by branch
- `idx_ingredient_id` - For filtering by ingredient
- `idx_status` - For filtering by status
- `idx_delivery_status` - For filtering by delivery status
- `idx_request_date` - For date-based queries
- `idx_updated_by` - For tracking who updated
- `idx_ingredient_requests_composite` - Composite index for common queries
- `idx_ingredient_requests_delivery` - Composite index for delivery queries

#### `stock_request_activity_log` - Activity Tracking

**Purpose**: Tracks all changes and activities related to requests

**Columns**:
- `log_id` (INT, AUTO_INCREMENT, PRIMARY KEY) - Unique identifier
- `request_id` (INT, NOT NULL) - Foreign key to ingredient_requests
- `user_id` (INT, NOT NULL) - Foreign key to pos_user table
- `action` (VARCHAR(100), NOT NULL) - Action performed
- `old_status` (VARCHAR(50), NULL) - Previous status
- `new_status` (VARCHAR(50), NULL) - New status
- `old_delivery_status` (VARCHAR(50), NULL) - Previous delivery status
- `new_delivery_status` (VARCHAR(50), NULL) - New delivery status
- `notes` (TEXT, NULL) - Additional notes
- `created_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP) - When activity occurred

**Indexes**:
- `idx_request_id` - For filtering by request
- `idx_user_id` - For filtering by user
- `idx_created_at` - For date-based queries
- `idx_activity_log_composite` - Composite index for common queries

#### `stock_request_notifications` - Notification System

**Purpose**: Manages notifications for users about request updates

**Columns**:
- `notification_id` (INT, AUTO_INCREMENT, PRIMARY KEY) - Unique identifier
- `request_id` (INT, NOT NULL) - Foreign key to ingredient_requests
- `user_id` (INT, NOT NULL) - Foreign key to pos_user table
- `notification_type` (ENUM('REQUEST_CREATED', 'REQUEST_APPROVED', 'REQUEST_REJECTED', 'DELIVERY_UPDATED', 'DELIVERY_COMPLETED'), NOT NULL) - Type of notification
- `message` (TEXT, NOT NULL) - Notification message
- `is_read` (BOOLEAN, DEFAULT FALSE) - Whether notification has been read
- `created_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP) - When notification was created

**Indexes**:
- `idx_request_id` - For filtering by request
- `idx_user_id` - For filtering by user
- `idx_is_read` - For filtering unread notifications
- `idx_created_at` - For date-based queries
- `idx_notifications_composite` - Composite index for common queries

### 2. Database Views

#### `vw_stock_requests_summary` - Complete Request Summary

**Purpose**: Provides a comprehensive view of all requests with related data

**Columns**: All columns from ingredient_requests plus:
- `ingredient_name` - Name of the ingredient
- `stockman_name` - Username of the stockman
- `updated_by_name` - Username of who last updated
- `branch_name` - Name of the branch

#### `vw_pending_requests` - Pending Requests Only

**Purpose**: Shows only pending requests, ordered by urgency

**Filter**: `status = 'PENDING'`
**Order**: By urgency level (CRITICAL → HIGH → MEDIUM → LOW), then by request date

#### `vw_approved_pending_delivery` - Approved Requests Awaiting Delivery

**Purpose**: Shows approved requests that are waiting for delivery

**Filter**: `status = 'APPROVED' AND delivery_status = 'PENDING'`
**Order**: By request date (oldest first)

### 3. Stored Procedures

#### `sp_create_stock_request` - Create New Request

**Parameters**:
- `p_stockman_id` (INT) - ID of the stockman
- `p_branch_id` (INT) - ID of the branch
- `p_ingredient_id` (INT) - ID of the ingredient
- `p_requested_quantity` (DECIMAL(10,2)) - Quantity requested
- `p_requested_unit` (VARCHAR(50)) - Unit of measurement
- `p_urgency_level` (ENUM) - Urgency level
- `p_priority` (ENUM) - Priority level
- `p_reason` (TEXT) - Reason for request
- `p_additional_notes` (TEXT) - Additional notes

**Actions**:
1. Inserts new request into `ingredient_requests`
2. Logs activity in `stock_request_activity_log`
3. Creates notification for admin in `stock_request_notifications`
4. Returns the new request ID

#### `sp_update_request_status` - Update Request Status

**Parameters**:
- `p_request_id` (INT) - ID of the request
- `p_admin_id` (INT) - ID of the admin
- `p_new_status` (ENUM) - New status (APPROVED/REJECTED)
- `p_admin_notes` (TEXT) - Notes from admin

**Actions**:
1. Updates request status and admin notes
2. Sets delivery status to 'CANCELLED' if rejected
3. Logs activity in `stock_request_activity_log`
4. Creates notification for stockman in `stock_request_notifications`

#### `sp_update_delivery_status` - Update Delivery Status

**Parameters**:
- `p_request_id` (INT) - ID of the request
- `p_stockman_id` (INT) - ID of the stockman
- `p_delivery_status` (ENUM) - New delivery status
- `p_delivery_date` (DATETIME) - Delivery date
- `p_delivery_notes` (TEXT) - Delivery notes

**Actions**:
1. Validates that request is approved and not in final status
2. Updates delivery status, date, and notes
3. Logs activity in `stock_request_activity_log`
4. Creates notification for admin in `stock_request_notifications`
5. Returns success/error message

### 4. Functions

#### `fn_get_request_stats` - Get Request Statistics

**Parameters**:
- `p_branch_id` (INT) - ID of the branch

**Returns**: JSON object with statistics:
```json
{
    "total_requests": 10,
    "pending_requests": 3,
    "approved_requests": 5,
    "rejected_requests": 2,
    "pending_delivery": 4,
    "delivered": 1,
    "returned": 0,
    "cancelled": 2
}
```

### 5. Triggers

#### `tr_ingredient_requests_status_update` - Automatic Activity Logging

**Purpose**: Automatically logs status changes in the activity log

**Triggers on**: UPDATE of `ingredient_requests` table

**Actions**:
1. Logs status changes (PENDING → APPROVED/REJECTED)
2. Logs delivery status changes
3. Includes relevant notes and user information

## Usage Examples

### 1. Create a New Request

```sql
CALL sp_create_stock_request(
    2,                          -- stockman_id
    1,                          -- branch_id
    1,                          -- ingredient_id
    10.00,                      -- requested_quantity
    'pieces',                   -- requested_unit
    'HIGH',                     -- urgency_level
    'URGENT',                   -- priority
    'Running low on stock',     -- reason
    'Need by end of day'        -- additional_notes
);
```

### 2. Approve a Request

```sql
CALL sp_update_request_status(
    1,                          -- request_id
    1,                          -- admin_id
    'APPROVED',                 -- new_status
    'Approved for delivery on Monday'  -- admin_notes
);
```

### 3. Update Delivery Status

```sql
CALL sp_update_delivery_status(
    1,                          -- request_id
    2,                          -- stockman_id
    'DELIVERED',                -- delivery_status
    '2025-08-31 14:30:00',     -- delivery_date
    'Delivered successfully'    -- delivery_notes
);
```

### 4. Get Request Statistics

```sql
SELECT fn_get_request_stats(1) as stats;
```

### 5. View Pending Requests

```sql
SELECT * FROM vw_pending_requests;
```

### 6. View Approved Requests Pending Delivery

```sql
SELECT * FROM vw_approved_pending_delivery;
```

## Sample Data

The database includes sample data for testing:

### Sample Requests
- **Pending Requests**: 3 requests in PENDING status
- **Approved Requests**: 3 requests in APPROVED status with PENDING delivery
- **Rejected Request**: 1 request in REJECTED status with CANCELLED delivery

### Sample Activity Logs
- Activity logs for all approved and rejected requests
- Tracks who performed each action and when

### Sample Notifications
- Notifications for stockmen about approved/rejected requests
- Notifications for admins about new requests

## Security Considerations

### 1. Foreign Key Constraints
- All foreign keys have appropriate CASCADE/SET NULL rules
- Prevents orphaned records
- Maintains referential integrity

### 2. Data Validation
- ENUM fields ensure valid status values
- NOT NULL constraints on required fields
- DECIMAL fields for precise quantity tracking

### 3. Audit Trail
- Complete activity logging for all changes
- Timestamps on all records
- User tracking for all modifications

## Performance Optimizations

### 1. Indexes
- Strategic indexes on frequently queried columns
- Composite indexes for common query patterns
- Indexes on foreign keys for join performance

### 2. Views
- Pre-joined views for common queries
- Reduces query complexity
- Improves maintainability

### 3. Stored Procedures
- Encapsulated business logic
- Reduced network traffic
- Consistent data manipulation

## Maintenance

### 1. Regular Tasks
- Monitor activity log size
- Archive old notifications
- Review and optimize indexes

### 2. Backup Considerations
- Include all tables in regular backups
- Consider point-in-time recovery for audit trail
- Test restore procedures regularly

## Integration Points

### 1. Existing Tables
- `pos_user` - User management
- `pos_branch` - Branch management
- `ingredients` - Ingredient catalog

### 2. PHP Integration
- Use stored procedures for data manipulation
- Use views for data retrieval
- Implement proper error handling

### 3. Frontend Integration
- AJAX calls to stored procedures
- Real-time updates using notifications
- Status-based UI updates

## Troubleshooting

### Common Issues

1. **Foreign Key Violations**
   - Ensure referenced records exist
   - Check CASCADE rules
   - Verify data integrity

2. **Performance Issues**
   - Monitor query execution plans
   - Check index usage
   - Optimize slow queries

3. **Data Consistency**
   - Use transactions for multi-step operations
   - Validate data before insertion
   - Check trigger performance

### Debug Queries

```sql
-- Check for orphaned records
SELECT ir.request_id, ir.stockman_id 
FROM ingredient_requests ir 
LEFT JOIN pos_user u ON ir.stockman_id = u.user_id 
WHERE u.user_id IS NULL;

-- Check activity log for specific request
SELECT * FROM stock_request_activity_log 
WHERE request_id = 1 
ORDER BY created_at DESC;

-- Check unread notifications
SELECT * FROM stock_request_notifications 
WHERE user_id = 1 AND is_read = FALSE;
```

## Future Enhancements

### 1. Additional Features
- Email notifications
- SMS alerts for critical requests
- Mobile app integration
- Advanced reporting

### 2. Performance Improvements
- Partitioning for large datasets
- Read replicas for reporting
- Caching layer
- Query optimization

### 3. Security Enhancements
- Row-level security
- Encryption for sensitive data
- Audit logging improvements
- Access control refinements
