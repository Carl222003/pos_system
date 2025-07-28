<?php
require_once 'db_connect.php';
require_once 'auth_function.php';
checkAdminLogin();

$branch_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$branch = null;
$error = '';

if ($branch_id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM pos_branch WHERE branch_id = ?');
    $stmt->execute([$branch_id]);
    $branch = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$branch) {
        $error = 'Branch not found.';
    }
} else {
    $error = 'Invalid branch ID.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $branch) {
    $branch_name = $_POST['branch_name'] ?? '';
    $branch_code = $_POST['branch_code'] ?? '';
    $contact_number = $_POST['contact_number'] ?? '';
    $email = $_POST['email'] ?? '';
    $complete_address = $_POST['complete_address'] ?? '';
    $operating_hours = $_POST['operating_hours'] ?? '';
    $status = $_POST['status'] ?? 'Active';

    $update = $pdo->prepare('UPDATE pos_branch SET branch_name=?, branch_code=?, contact_number=?, email=?, complete_address=?, operating_hours=?, status=? WHERE branch_id=?');
    $update->execute([$branch_name, $branch_code, $contact_number, $email, $complete_address, $operating_hours, $status, $branch_id]);
    header('Location: branch_details.php?updated=1');
    exit;
}

include('header.php');
?>
<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4>Edit Branch</h4>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php elseif ($branch): ?>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Branch Name</label>
                    <input type="text" name="branch_name" class="form-control" value="<?php echo htmlspecialchars($branch['branch_name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Branch Code</label>
                    <input type="text" name="branch_code" class="form-control" value="<?php echo htmlspecialchars($branch['branch_code']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="contact_number" class="form-control" value="<?php echo htmlspecialchars($branch['contact_number']); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($branch['email']); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Complete Address</label>
                    <input type="text" name="complete_address" class="form-control" value="<?php echo htmlspecialchars($branch['complete_address']); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Operating Hours</label>
                    <input type="text" name="operating_hours" class="form-control" value="<?php echo htmlspecialchars($branch['operating_hours']); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="Active" <?php if ($branch['status'] === 'Active') echo 'selected'; ?>>Active</option>
                        <option value="Inactive" <?php if ($branch['status'] === 'Inactive') echo 'selected'; ?>>Inactive</option>
                        <option value="archived" <?php if ($branch['status'] === 'archived') echo 'selected'; ?>>Archived</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="branch_details.php" class="btn btn-secondary">Cancel</a>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include('footer.php'); ?> 