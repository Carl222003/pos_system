<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $date_hired = $_POST['date_hired'];
    $emergency_contact = $_POST['emergency_contact'];
    $emergency_number = $_POST['emergency_number'];
    
    try {
        // Update stockman data
        $stmt = $pdo->prepare("
            UPDATE pos_user 
            SET date_hired = ?, 
                emergency_contact = ?, 
                emergency_number = ?
            WHERE user_id = ? AND user_type = 'Stockman'
        ");
        
        $result = $stmt->execute([$date_hired, $emergency_contact, $emergency_number, $user_id]);
        
        if ($result) {
            $success_message = "Stockman data updated successfully!";
        } else {
            $error_message = "Failed to update stockman data.";
        }
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Get all stockman users
$stmt = $pdo->prepare("
    SELECT u.*, b.branch_name 
    FROM pos_user u 
    LEFT JOIN pos_branch b ON b.branch_id = u.branch_id 
    WHERE u.user_type = 'Stockman' 
    ORDER BY u.created_at DESC
");
$stmt->execute();
$stockmen = $stmt->fetchAll(PDO::FETCH_ASSOC);

include('header.php');
?>

<style>
.update-form {
    background: white;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.stockman-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-left: 4px solid #17a2b8;
}

.stockman-card h5 {
    color: #17a2b8;
    margin-bottom: 15px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 5px;
}

.btn-update {
    background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
    border: none;
    color: white;
    padding: 8px 20px;
    border-radius: 5px;
    font-weight: 600;
}

.btn-update:hover {
    background: linear-gradient(135deg, #138496 0%, #1ea085 100%);
    transform: translateY(-1px);
}
</style>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-boxes me-2"></i>Update Stockman Data
        </h1>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Stockman Records - Update Missing Information
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($stockmen)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No stockman users found.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($stockmen as $stockman): ?>
                            <div class="stockman-card">
                                <h5>
                                    <i class="fas fa-user me-2"></i>
                                    <?php echo htmlspecialchars($stockman['user_name']); ?>
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Employee ID:</strong> <?php echo htmlspecialchars($stockman['employee_id']); ?></p>
                                        <p><strong>Branch:</strong> <?php echo htmlspecialchars($stockman['branch_name']); ?></p>
                                        <p><strong>Contact:</strong> <?php echo htmlspecialchars($stockman['contact_number']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Date Hired:</strong> 
                                            <?php echo $stockman['date_hired'] ? htmlspecialchars($stockman['date_hired']) : '<span class="text-danger">Not Set</span>'; ?>
                                        </p>
                                        <p><strong>Emergency Contact:</strong> 
                                            <?php echo $stockman['emergency_contact'] ? htmlspecialchars($stockman['emergency_contact']) : '<span class="text-danger">Not Provided</span>'; ?>
                                        </p>
                                        <p><strong>Emergency Number:</strong> 
                                            <?php echo $stockman['emergency_number'] ? htmlspecialchars($stockman['emergency_number']) : '<span class="text-danger">Not Provided</span>'; ?>
                                        </p>
                                    </div>
                                </div>

                                <form method="POST" class="update-form mt-3">
                                    <input type="hidden" name="user_id" value="<?php echo $stockman['user_id']; ?>">
                                    
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="date_hired_<?php echo $stockman['user_id']; ?>">Date Hired:</label>
                                                <input type="date" 
                                                       class="form-control" 
                                                       id="date_hired_<?php echo $stockman['user_id']; ?>"
                                                       name="date_hired" 
                                                       value="<?php echo $stockman['date_hired']; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="emergency_contact_<?php echo $stockman['user_id']; ?>">Emergency Contact:</label>
                                                <input type="text" 
                                                       class="form-control" 
                                                       id="emergency_contact_<?php echo $stockman['user_id']; ?>"
                                                       name="emergency_contact" 
                                                       value="<?php echo htmlspecialchars($stockman['emergency_contact'] ?? ''); ?>"
                                                       placeholder="Enter emergency contact name">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="emergency_number_<?php echo $stockman['user_id']; ?>">Emergency Number:</label>
                                                <input type="text" 
                                                       class="form-control" 
                                                       id="emergency_number_<?php echo $stockman['user_id']; ?>"
                                                       name="emergency_number" 
                                                       value="<?php echo htmlspecialchars($stockman['emergency_number'] ?? ''); ?>"
                                                       placeholder="Enter emergency phone number">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-update">
                                        <i class="fas fa-save me-2"></i>Update Data
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
