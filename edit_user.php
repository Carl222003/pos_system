<?php 

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

$message = '';
$user_id = (isset($_GET['id'])) ? $_GET['id'] :'';
$user_name = '';
$user_email = '';
$user_status = 'Active';

// Fetch active branches for the dropdown
$stmt = $pdo->query("SELECT branch_id, branch_name, branch_code FROM pos_branch WHERE status = 'Active' ORDER BY branch_name");
$all_branches = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch the current user data
if (!empty($user_id)) {
    $stmt = $pdo->prepare("SELECT * FROM pos_user WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $user_name = $user["user_name"];
        $user_email = $user["user_email"];
        $user_status = $user["user_status"];
        $contact_number = $user["contact_number"] ?? '';
        $profile_image = $user["profile_image"] ?? '';
        $user_type = $user["user_type"] ?? '';
        $branch_id = $user["branch_id"] ?? '';
        $employee_id = $user["employee_id"] ?? '';
        $date_hired = $user["date_hired"] ?? '';
        $emergency_contact = $user["emergency_contact"] ?? '';
        $emergency_number = $user["emergency_number"] ?? '';
        $address = $user["address"] ?? '';
        $notes = $user["notes"] ?? '';
        
        // Filter branches based on user type
        if ($user_type === 'Stockman') {
            // Exclude main branch for stockman
            $branches = array_filter($all_branches, function($branch) {
                return !stripos($branch['branch_name'], 'main') && $branch['branch_code'] !== 'BR-QZF8K0';
            });
        } else {
            // For other user types, show all branches
            $branches = $all_branches;
        }
    } else {
        $message = 'User not found.';
        $branches = $all_branches; // Fallback to all branches
    }
} else {
    $branches = $all_branches; // Fallback to all branches
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Fetch current user data for fallback
    $stmt = $pdo->prepare("SELECT * FROM pos_user WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);

    $user_name = trim($_POST['user_name']) !== '' ? trim($_POST['user_name']) : $current['user_name'];
    $user_email = trim($_POST['user_email']) !== '' ? trim($_POST['user_email']) : $current['user_email'];
    $contact_number = trim($_POST['contact_number']) !== '' ? trim($_POST['contact_number']) : $current['contact_number'];
    $address = trim($_POST['address']) !== '' ? trim($_POST['address']) : $current['address'];
    $branch_id = isset($_POST['branch_id']) ? $_POST['branch_id'] : $current['branch_id'];

    // Handle password
    $user_password = trim($_POST['user_password']);
    $update_password = !empty($user_password);

    // Handle profile image
    $profile_image = $current['profile_image'];
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $upload_name = 'profile_' . time() . '.' . $ext;
            $upload_path = 'uploads/profiles/' . $upload_name;
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                $profile_image = $upload_path;
            }
        }
    }

    // Validate email format if changed
    if ($user_email !== $current['user_email'] && !filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format.';
    } else {
        // Check if email already exists for another user
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pos_user WHERE user_email = :user_email AND user_id != :user_id");
        $stmt->execute(['user_email' => $user_email, 'user_id' => $user_id]);
        $count = $stmt->fetchColumn();
        if ($count > 0) {
            $message = 'Email already exists.';
        } else {
            // Update the database
            try {
                $sql = "UPDATE pos_user SET user_name = :user_name, user_email = :user_email, contact_number = :contact_number, address = :address, profile_image = :profile_image, branch_id = :branch_id";
                $params = [
                    'user_name' => $user_name,
                    'user_email' => $user_email,
                    'contact_number' => $contact_number,
                    'address' => $address,
                    'profile_image' => $profile_image,
                    'branch_id' => $branch_id,
                    'user_id' => $user_id
                ];
                if ($update_password) {
                    $sql .= ", user_password = :user_password";
                    $params['user_password'] = password_hash($user_password, PASSWORD_DEFAULT);
                }
                $sql .= " WHERE user_id = :user_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                header('location:user.php');
            } catch (PDOException $e) {
                $message = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Edit User</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="user.php">User Management</a></li>
        <li class="breadcrumb-item active">Edit User</li>
    </ol>
    
    <style>
    .form-label {
        font-weight: 500;
        color: #566a7f;
    }
    
    .form-control, .form-select {
        border-radius: 0.5rem;
        border: 1px solid #d9dee3;
        padding: 0.5rem 1rem;
        font-size: 0.9375rem;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #8B4543;
        box-shadow: 0 0 0 0.25rem rgba(139, 69, 67, 0.25);
    }
    
    .form-text {
        font-size: 0.875rem;
        color: #6c757d;
    }
    
    .btn-primary {
        background-color: #8B4543;
        border-color: #8B4543;
    }
    
    .btn-primary:hover {
        background-color: #723937;
        border-color: #723937;
    }
    
    .btn-secondary {
        background-color: #8592a3;
        border-color: #8592a3;
    }
    
    .btn-secondary:hover {
        background-color: #6d788d;
        border-color: #6d788d;
    }
    </style>
    <?php if(isset($message) && $message !== ''): ?>
        <div class="alert alert-danger"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <!-- Debug Information (remove this after testing) -->
    <?php if (isset($_GET['debug']) && $_GET['debug'] == '1'): ?>
        <div class="alert alert-info">
            <strong>Debug Info:</strong><br>
            Branches loaded: <?php echo count($branches); ?><br>
            Current branch_id: <?php echo $branch_id; ?><br>
            User ID: <?php echo $user_id; ?>
        </div>
    <?php endif; ?>
    <form method="post" action="edit_user.php?id=<?php echo htmlspecialchars($user_id); ?>" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6">
                <h5 class="mb-4">Basic Information</h5>
                <div class="form-group mb-3">
                    <label for="user_name" class="form-label">Full Name*</label>
                    <input type="text" class="form-control" id="user_name" name="user_name" value="<?php echo htmlspecialchars($user_name); ?>">
                </div>
                <div class="form-group mb-3">
                    <label for="user_email" class="form-label">Email Address*</label>
                    <input type="email" class="form-control" id="user_email" name="user_email" value="<?php echo htmlspecialchars($user_email); ?>">
                </div>
                <div class="form-group mb-3">
                    <label for="user_password" class="form-label">Password (leave blank to keep current)</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="user_password" name="user_password">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label for="contact_number" class="form-label">Contact Number*</label>
                    <input type="tel" class="form-control" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($contact_number); ?>">
                </div>
                <div class="form-group mb-3">
                    <label for="branch_id" class="form-label">
                        <i class="fas fa-building me-1"></i>Branch Assignment
                    </label>
                    <select class="form-select" id="branch_id" name="branch_id" style="border: 1px solid #d9dee3; border-radius: 0.5rem; padding: 0.5rem 1rem;">
                        <option value="">No Branch Assigned</option>
                        <?php if (!empty($branches)): ?>
                            <?php foreach ($branches as $branch): ?>
                                <option value="<?php echo $branch['branch_id']; ?>" <?php echo ($branch_id == $branch['branch_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($branch['branch_name'] . ' (' . $branch['branch_code'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled>No branches available</option>
                        <?php endif; ?>
                    </select>
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1"></i>
                        Change or assign the user's branch. Leave empty to remove branch assignment.
                        <?php if (isset($user_type) && $user_type === 'Stockman'): ?>
                            <br><strong>Note:</strong> Main branch is not available for stockmen as admin handles inventory management there.
                        <?php endif; ?>
                    </div>
                    <?php if (empty($branches)): ?>
                        <div class="alert alert-warning mt-2">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            No active branches found. Please create branches first.
                        </div>
                    <?php endif; ?>
                </div>
                <div class="form-group mb-3">
                    <label for="profile_image" class="form-label">Profile Image</label>
                    <?php if (!empty($profile_image)): ?>
                        <div class="mb-2"><img src="<?php echo $profile_image; ?>" alt="Profile Image" style="max-height: 80px;"></div>
                    <?php endif; ?>
                    <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                </div>
            </div>
            <div class="col-md-6">
                <h5 class="mb-4">Additional Information</h5>
                <div class="form-group mb-3">
                    <label for="address" class="form-label">Complete Address*</label>
                    <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($address); ?></textarea>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Update User
            </button>
            <a href="user.php" class="btn btn-secondary">
                <i class="fas fa-times me-1"></i> Cancel
            </a>
        </div>
    </form>
</div>

<?php
include('footer.php');
?>