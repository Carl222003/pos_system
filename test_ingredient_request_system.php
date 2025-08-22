<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// This is a test page to demonstrate the ingredient request system
// It shows how stockman requests work and how admin approval affects inventory

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Test Ingredient Request System</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
    <style>
        .test-section { margin: 2rem 0; padding: 1.5rem; border: 1px solid #dee2e6; border-radius: 0.5rem; }
        .test-section h3 { color: #8B4543; margin-bottom: 1rem; }
        .stock-info { background: #f8f9fa; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0; }
        .request-info { background: #e3f2fd; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0; }
        .success { color: #28a745; }
        .warning { color: #ffc107; }
        .danger { color: #dc3545; }
        .info { color: #17a2b8; }
        .workflow-step { background: #f8f9fa; padding: 1rem; border-radius: 0.5rem; margin: 0.5rem 0; border-left: 4px solid #8B4543; }
        .workflow-step h6 { color: #8B4543; margin-bottom: 0.5rem; }
    </style>
</head>
<body>
    <div class='container mt-4'>
        <h1 class='text-center mb-4'>
            <i class='fas fa-flask text-primary me-2'></i>
            Ingredient Request System Test
        </h1>
        
        <div class='alert alert-info'>
            <h5><i class='fas fa-info-circle me-2'></i>System Overview</h5>
            <p>This test demonstrates how the ingredient request system works:</p>
            <ul>
                <li><strong>Stockman</strong> submits ingredient requests</li>
                <li><strong>Admin</strong> reviews and approves requests</li>
                <li><strong>System</strong> automatically deducts ingredients from inventory</li>
                <li><strong>Stockman</strong> can return unused ingredients</li>
                <li><strong>System</strong> automatically restores quantities back to inventory</li>
                <li><strong>Audit trail</strong> is maintained for all stock movements</li>
            </ul>
        </div>";

// Test 1: Show current ingredient inventory
echo "<div class='test-section'>
    <h3><i class='fas fa-boxes me-2'></i>Current Ingredient Inventory</h3>";

try {
    $stmt = $pdo->query("
        SELECT i.ingredient_id, i.ingredient_name, i.ingredient_quantity, i.ingredient_unit, 
               i.ingredient_status, b.branch_name
        FROM ingredients i
        LEFT JOIN pos_branch b ON i.branch_id = b.branch_id
        WHERE i.ingredient_status != 'archived'
        ORDER BY b.branch_name, i.ingredient_name
        LIMIT 10
    ");
    
    $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($ingredients) {
        echo "<div class='table-responsive'>
            <table class='table table-striped table-bordered'>
                <thead class='table-dark'>
                    <tr>
                        <th>Ingredient</th>
                        <th>Current Stock</th>
                        <th>Unit</th>
                        <th>Status</th>
                        <th>Branch</th>
                    </tr>
                </thead>
                <tbody>";
        
        foreach ($ingredients as $ingredient) {
            $statusClass = '';
            switch($ingredient['ingredient_status']) {
                case 'Available': $statusClass = 'success'; break;
                case 'Low Stock': $statusClass = 'warning'; break;
                case 'Out of Stock': $statusClass = 'danger'; break;
                default: $statusClass = 'info';
            }
            
            echo "<tr>
                <td><strong>{$ingredient['ingredient_name']}</strong></td>
                <td class='{$statusClass}'><strong>{$ingredient['ingredient_quantity']}</strong></td>
                <td>{$ingredient['ingredient_unit']}</td>
                <td><span class='badge bg-{$statusClass}'>{$ingredient['ingredient_status']}</span></td>
                <td>{$ingredient['branch_name']}</td>
            </tr>";
        }
        
        echo "</tbody></table></div>";
    } else {
        echo "<div class='alert alert-warning'>No ingredients found in inventory.</div>";
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error fetching ingredients: " . $e->getMessage() . "</div>";
}

echo "</div>";

// Test 2: Show recent ingredient requests
echo "<div class='test-section'>
    <h3><i class='fas fa-clipboard-list me-2'></i>Recent Ingredient Requests</h3>";

try {
    $stmt = $pdo->query("
        SELECT r.request_id, r.request_date, r.status, r.ingredients, r.notes,
               b.branch_name, u.user_name as updated_by
        FROM ingredient_requests r
        LEFT JOIN pos_branch b ON r.branch_id = b.branch_id
        LEFT JOIN pos_user u ON r.updated_by = u.user_id
        ORDER BY r.request_date DESC
        LIMIT 5
    ");
    
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($requests) {
        echo "<div class='table-responsive'>
            <table class='table table-striped table-bordered'>
                <thead class='table-dark'>
                    <tr>
                        <th>Request ID</th>
                        <th>Date</th>
                        <th>Branch</th>
                        <th>Ingredients</th>
                        <th>Status</th>
                        <th>Updated By</th>
                    </tr>
                </thead>
                <tbody>";
        
        foreach ($requests as $request) {
            $statusClass = '';
            switch($request['status']) {
                case 'pending': $statusClass = 'warning'; break;
                case 'approved': $statusClass = 'success'; break;
                case 'returned': $statusClass = 'info'; break;
                case 'rejected': $statusClass = 'danger'; break;
                default: $statusClass = 'secondary';
            }
            
            // Parse ingredients JSON
            $ingredientsList = '';
            try {
                $ingredientsData = json_decode($request['ingredients'], true);
                if ($ingredientsData && is_array($ingredientsData)) {
                    foreach ($ingredientsData as $ingredient) {
                        $ingredientsList .= "• {$ingredient['quantity']} units (ID: {$ingredient['ingredient_id']})<br>";
                    }
                }
            } catch (Exception $e) {
                $ingredientsList = "Error parsing ingredients";
            }
            
            echo "<tr>
                <td><strong>#{$request['request_id']}</strong></td>
                <td>" . date('M d, Y H:i', strtotime($request['request_date'])) . "</td>
                <td>{$request['branch_name']}</td>
                <td>{$ingredientsList}</td>
                <td><span class='badge bg-{$statusClass}'>{$request['status']}</span></td>
                <td>{$request['updated_by'] ?? 'N/A'}</td>
            </tr>";
        }
        
        echo "</tbody></table></div>";
    } else {
        echo "<div class='alert alert-warning'>No ingredient requests found.</div>";
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error fetching requests: " . $e->getMessage() . "</div>";
}

echo "</div>";

// Test 3: Show stock movements (if table exists)
echo "<div class='test-section'>
    <h3><i class='fas fa-chart-line me-2'></i>Recent Stock Movements</h3>";

try {
    // Check if stock_movements table exists
    $tableExists = $pdo->query("SHOW TABLES LIKE 'stock_movements'")->rowCount() > 0;
    
    if ($tableExists) {
        $stmt = $pdo->query("
            SELECT sm.movement_id, sm.movement_type, sm.quantity, sm.reason, sm.movement_date,
                   i.ingredient_name, b.branch_name, u.user_name
            FROM stock_movements sm
            LEFT JOIN ingredients i ON sm.ingredient_id = i.ingredient_id
            LEFT JOIN pos_branch b ON sm.branch_id = b.branch_id
            LEFT JOIN pos_user u ON sm.performed_by = u.user_id
            ORDER BY sm.movement_date DESC
            LIMIT 5
        ");
        
        $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($movements) {
            echo "<div class='table-responsive'>
                <table class='table table-striped table-bordered'>
                    <thead class='table-dark'>
                        <tr>
                            <th>Movement ID</th>
                            <th>Type</th>
                            <th>Ingredient</th>
                            <th>Quantity</th>
                            <th>Reason</th>
                            <th>Branch</th>
                            <th>Performed By</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>";
            
            foreach ($movements as $movement) {
                $typeClass = '';
                switch($movement['movement_type']) {
                    case 'addition': $typeClass = 'success'; break;
                    case 'deduction': $typeClass = 'danger'; break;
                    case 'return': $typeClass = 'info'; break;
                    case 'adjustment': $typeClass = 'warning'; break;
                    default: $typeClass = 'info';
                }
                
                echo "<tr>
                    <td><strong>#{$movement['movement_id']}</strong></td>
                    <td><span class='badge bg-{$typeClass}'>{$movement['movement_type']}</span></td>
                    <td>{$movement['ingredient_name']}</td>
                    <td class='{$typeClass}'><strong>{$movement['quantity']}</strong></td>
                    <td>{$movement['reason']}</td>
                    <td>{$movement['branch_name']}</td>
                    <td>{$movement['user_name']}</td>
                    <td>" . date('M d, Y H:i', strtotime($movement['movement_date'])) . "</td>
                </tr>";
            }
            
            echo "</tbody></table></div>";
        } else {
            echo "<div class='alert alert-warning'>No stock movements found.</div>";
        }
    } else {
        echo "<div class='alert alert-info'>Stock movements table not yet created. It will be created automatically when the first request is approved.</div>";
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error fetching stock movements: " . $e->getMessage() . "</div>";
}

echo "</div>";

// Test 4: Show how the system works with the complete workflow
echo "<div class='test-section'>
    <h3><i class='fas fa-cogs me-2'></i>Complete Ingredient Request Workflow</h3>
    
    <div class='workflow-step'>
        <h6><i class='fas fa-user-tie me-2'></i>Step 1: Stockman Request</h6>
        <p>Stockman identifies low stock ingredients and submits a request via the system.</p>
    </div>
    
    <div class='workflow-step'>
        <h6><i class='fas fa-user-shield me-2'></i>Step 2: Admin Review</h6>
        <p>Admin reviews the ingredient request, checks current inventory levels, and decides to approve or reject.</p>
    </div>
    
    <div class='workflow-step'>
        <h6><i class='fas fa-check-circle me-2'></i>Step 3: Approval & Deduction</h6>
        <p>If approved, the system automatically deducts the requested quantities from inventory and updates ingredient statuses.</p>
    </div>
    
    <div class='workflow-step'>
        <h6><i class='fas fa-undo-alt me-2'></i>Step 4: Return Processing</h6>
        <p>If stockman returns unused ingredients, admin can process the return, which automatically restores quantities back to inventory.</p>
    </div>
    
    <div class='workflow-step'>
        <h6><i class='fas fa-chart-line me-2'></i>Step 5: Audit Trail</h6>
        <p>All stock movements (deductions and returns) are logged with timestamps, reasons, and user information for complete audit trail.</p>
    </div>
    
    <div class='alert alert-success mt-3'>
        <h6><i class='fas fa-check-circle me-2'></i>Key Benefits of the Complete System</h6>
        <ul class='mb-0'>
            <li><strong>Full Inventory Cycle:</strong> Request → Approval → Deduction → Return → Restoration</li>
            <li><strong>Automatic Inventory Management:</strong> No manual stock updates needed for any operation</li>
            <li><strong>Complete Audit Trail:</strong> All stock movements logged with full context</li>
            <li><strong>Real-time Updates:</strong> Inventory levels updated immediately for all operations</li>
            <li><strong>Status Management:</strong> Automatic status updates based on quantities</li>
            <li><strong>Return Processing:</strong> Easy restoration of returned ingredients to inventory</li>
        </ul>
    </div>
</div>";

// Test 5: Show test actions
echo "<div class='test-section'>
    <h3><i class='fas fa-play-circle me-2'></i>Test Actions</h3>
    
    <div class='row'>
        <div class='col-md-4'>
            <div class='card'>
                <div class='card-header bg-warning text-dark'>
                    <h5><i class='fas fa-flask me-2'></i>Create Request</h5>
                </div>
                <div class='card-body'>
                    <p>To test the system, you can:</p>
                    <ol>
                        <li>Login as a Stockman</li>
                        <li>Go to 'Request Stock' page</li>
                        <li>Select ingredients and quantities</li>
                        <li>Submit the request</li>
                    </ol>
                    <a href='request_stock.php' class='btn btn-warning'>
                        <i class='fas fa-plus me-2'></i>Create Request
                    </a>
                </div>
            </div>
        </div>
        
        <div class='col-md-4'>
            <div class='card'>
                <div class='card-header bg-success text-white'>
                    <h5><i class='fas fa-clipboard-check me-2'></i>Review & Approve</h5>
                </div>
                <div class='card-body'>
                    <p>To approve requests:</p>
                    <ol>
                        <li>Login as Admin</li>
                        <li>Go to 'List of Request' page</li>
                        <li>Review pending requests</li>
                        <li>Approve with notes</li>
                    </ol>
                    <a href='ingredient_requests.php' class='btn btn-success'>
                        <i class='fas fa-list me-2'></i>View Requests
                    </a>
                </div>
            </div>
        </div>
        
        <div class='col-md-4'>
            <div class='card'>
                <div class='card-header bg-info text-white'>
                    <h5><i class='fas fa-undo-alt me-2'></i>Process Returns</h5>
                </div>
                <div class='card-body'>
                    <p>To process returns:</p>
                    <ol>
                        <li>Login as Admin</li>
                        <li>Go to 'List of Request' page</li>
                        <li>Find approved requests</li>
                        <li>Click 'Process Return'</li>
                    </ol>
                    <a href='ingredient_requests.php' class='btn btn-info'>
                        <i class='fas fa-undo me-2'></i>Process Returns
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>";

echo "<div class='alert alert-success text-center mt-4'>
    <h5><i class='fas fa-rocket me-2'></i>Complete System Ready!</h5>
    <p>The ingredient request system is now fully functional with automatic stock deduction AND return processing!</p>
    <p><strong>Complete Workflow:</strong> Request → Approval → Deduction → Return → Restoration</p>
</div>";

echo "</div>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?>
