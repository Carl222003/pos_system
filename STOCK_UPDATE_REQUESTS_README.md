# Stock Update Requests System

## Overview
The Stock Update Requests system allows stockmen to request stock updates for ingredients and enables admins to approve, reject, or complete these requests. This system provides a streamlined workflow for managing stock updates across all branches.

## Features

### For Stockmen:
- **Submit Stock Update Requests**: Request to add, adjust, or correct ingredient stock levels
- **Urgency Levels**: Set urgency as Low, Medium, High, or Critical
- **Priority Settings**: Set priority as Normal, High, or Urgent
- **Request Tracking**: View all submitted requests with their current status
- **Request Management**: Cancel pending requests if needed
- **Detailed History**: View complete request history and admin responses

### For Admins:
- **Request Management**: View all stock update requests from all branches
- **Approval System**: Approve, reject, or complete requests
- **Stock Updates**: Directly update ingredient stock when completing requests
- **Filtering & Search**: Filter requests by status, urgency, branch, and stockman
- **Statistics Dashboard**: View comprehensive statistics and analytics
- **Response System**: Provide detailed responses to stockmen

## Database Tables

### 1. `stock_update_requests`
Main table storing all stock update requests:
- `request_id` (Primary Key)
- `stockman_id` (Foreign Key to pos_user)
- `ingredient_id` (Foreign Key to ingredients)
- `update_type` (add, adjust, correct)
- `quantity`, `unit`
- `urgency_level` (low, medium, high, critical)
- `priority` (normal, high, urgent)
- `reason`, `notes`
- `status` (pending, approved, rejected, completed)
- `admin_response`
- `request_date`, `response_date`
- `processed_by`, `processed_date`

### 2. `stock_update_logs`
Tracks all stock changes made through the system:
- `log_id` (Primary Key)
- `request_id` (Foreign Key)
- `ingredient_id` (Foreign Key)
- `old_quantity`, `new_quantity`, `change_amount`
- `change_type` (add, subtract, set)
- `updated_by` (Foreign Key to pos_user)
- `update_date`, `notes`

### 3. `admin_stock_notifications`
Manages notifications between admins and stockmen:
- `notification_id` (Primary Key)
- `request_id` (Foreign Key)
- `admin_id` (Foreign Key to pos_user)
- `notification_type` (new_request, urgent_request, request_approved, request_rejected)
- `message`, `is_read`
- `created_date`, `read_date`

## File Structure

### Stockman Interface:
- `request_stock_updates.php` - Main stockman interface
- `submit_stock_update_request.php` - Handle request submission
- `get_stock_update_requests.php` - Get stockman's requests
- `get_stock_update_stats.php` - Get stockman's statistics
- `get_stock_update_request_details.php` - Get request details
- `cancel_stock_update_request.php` - Cancel pending requests

### Admin Interface:
- `admin_stock_update_requests.php` - Main admin interface
- `get_all_stock_update_requests.php` - Get all requests
- `get_admin_stock_update_stats.php` - Get admin statistics
- `get_admin_stock_update_request_details.php` - Get detailed request info
- `admin_respond_to_stock_request.php` - Handle admin responses

### Database Setup:
- `create_stock_update_requests_table.php` - Create required database tables

## Workflow

### 1. Stockman Submits Request:
1. Stockman navigates to "Request Stock Updates"
2. Selects ingredient from their branch
3. Chooses update type (add, adjust, correct)
4. Sets quantity and unit
5. Sets urgency and priority levels
6. Provides reason and optional notes
7. Submits request

### 2. Admin Reviews Request:
1. Admin navigates to "Stock Update Requests" in admin menu
2. Views all pending requests (sorted by urgency)
3. Can filter by status, urgency, branch, or stockman
4. Reviews request details
5. Takes action: Approve, Reject, or Complete

### 3. Admin Responds:
1. **Approve**: Admin approves request and provides response message
2. **Reject**: Admin rejects request with explanation
3. **Complete**: Admin completes approved request and updates stock

### 4. Stockman Receives Response:
1. Stockman receives notification of admin response
2. Can view updated request status
3. Can see admin's response message
4. If completed, can see updated stock levels

## Security Features

- **Role-based Access**: Only stockmen can submit requests, only admins can approve/reject
- **Branch Isolation**: Stockmen can only request updates for ingredients in their branch
- **Request Validation**: Prevents duplicate pending requests for same ingredient
- **Audit Trail**: All actions are logged with timestamps and user information
- **Transaction Safety**: Database transactions ensure data consistency

## Integration

### Navigation Menu Updates:
- **Stockman Menu**: Added "Request Stock Updates" below "Request Ingredient"
- **Admin Menu**: Added "Stock Update Requests" in admin section

### Existing System Integration:
- Uses existing user authentication system
- Integrates with existing ingredient management
- Connects to existing branch system
- Utilizes existing activity logging
- Follows existing UI/UX patterns

## Usage Instructions

### For Stockmen:
1. Login as a stockman
2. Navigate to "Request Stock Updates" in the sidebar
3. Fill out the request form with required information
4. Submit the request
5. Monitor request status in the requests table
6. View admin responses and updated stock levels

### For Admins:
1. Login as an admin
2. Navigate to "Stock Update Requests" in the admin menu
3. Review pending requests (urgent requests are highlighted)
4. Use filters to find specific requests
5. Click "View" to see detailed request information
6. Use action buttons to approve, reject, or complete requests
7. Provide response messages for stockmen

## Benefits

1. **Streamlined Process**: Centralized system for stock update requests
2. **Better Control**: Admin oversight of all stock changes
3. **Audit Trail**: Complete history of all stock update requests
4. **Urgency Management**: Priority system for critical stock needs
5. **Communication**: Direct communication between stockmen and admins
6. **Data Integrity**: Prevents unauthorized stock changes
7. **Reporting**: Comprehensive statistics and analytics

## Future Enhancements

- Email notifications for urgent requests
- Bulk approval/rejection functionality
- Advanced reporting and analytics
- Mobile-responsive interface improvements
- Integration with inventory forecasting
- Automated stock level alerts
