<?php
require_once 'db_connect.php';
require_once 'auth_function.php';
checkAdminLogin();
include('header.php');
?>
<style>
.nav-tabs .nav-link.active {
    background-color: #8B4543;
    color: #fff;
    border: none;
}
.nav-tabs .nav-link {
    color: #800000;
    border: none;
    font-weight: 500;
}
/* Ensure inactive tabs are visible and clickable */
.nav-tabs .nav-link:not(.active) {
    color: #800000 !important;
    opacity: 1 !important;
    background: none !important;
    cursor: pointer !important;
}
.card-header {
    background: #f5f5f5;
    font-size: 1.1rem;
    font-weight: 600;
    color: #8B4543;
    border-bottom: 1px solid #e0e0e0;
}
.table {
    background: #fff;
    border-radius: 0.75rem;
    overflow: hidden;
}
.table thead th {
    background: #f8f9fa;
    color: #8B4543;
    font-weight: 600;
    border-bottom: 2px solid #e0e0e0;
}
.table-hover tbody tr:hover {
    background: #f3e9e8;
}
.btn-restore {
    background: #4A7C59 !important;
    color: #fff !important;
    border: none;
    border-radius: 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 0.4em;
    font-weight: 500;
    font-size: 1rem;
    padding: 0.5rem 1.25rem;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(74, 124, 89, 0.10);
    transition: background 0.2s, color 0.2s;
}
.btn-restore i {
    color: #fff !important;
    font-size: 1.2em;
}
.btn-restore:hover, .btn-restore:focus {
    background: #3a6247 !important;
    color: #fff !important;
    text-decoration: none;
}
.btn-restore:active {
    background: #2e4e39 !important;
    color: #fff !important;
}
/* Enhanced pagination buttons for archived products */
.archived-pagination-btn {
    background: #8B4543;
    color: #fff;
    border: none;
    border-radius: 2rem;
    font-size: 1.1rem;
    font-weight: 600;
    padding: 0.5rem 1.5rem;
    margin: 0 0.5rem;
    box-shadow: 0 0.1rem 0.5rem rgba(139, 69, 67, 0.08);
    display: inline-flex;
    align-items: center;
    gap: 0.5em;
    transition: background 0.2s, color 0.2s, box-shadow 0.2s;
}
.archived-pagination-btn:disabled {
    background: #e0e0e0;
    color: #b0b0b0;
    cursor: not-allowed;
    box-shadow: none;
}
.archived-pagination-btn:hover:not(:disabled),
.archived-pagination-btn:focus:not(:disabled) {
    background: #6a2e2b;
    color: #fff;
    box-shadow: 0 0.2rem 0.8rem rgba(139, 69, 67, 0.15);
    text-decoration: none;
    transform: scale(1.07);
    transition: background 0.2s, color 0.2s, box-shadow 0.2s, transform 0.15s;
}
/* Ripple effect for pagination buttons */
.archived-pagination-btn {
    position: relative;
    overflow: hidden;
}
.archived-pagination-btn .ripple {
    position: absolute;
    border-radius: 50%;
    transform: scale(0);
    animation: ripple-effect 0.5s linear;
    background-color: rgba(255,255,255,0.5);
    pointer-events: none;
    z-index: 2;
}
@keyframes ripple-effect {
    to {
        transform: scale(2.5);
        opacity: 0;
    }
}
.card.mb-4 {
    transition: box-shadow 0.25s, transform 0.18s;
    box-shadow: 0 2px 12px rgba(139, 69, 67, 0.07);
    border-radius: 1.1rem;
}
.card.mb-4:hover {
    box-shadow: 0 8px 32px rgba(139, 69, 67, 0.18);
    transform: translateY(-4px) scale(1.012);
    border: 1.5px solid #8B4543;
    background: #fdf7f6;
}
@keyframes card-flash {
    0% { box-shadow: 0 2px 12px rgba(139, 69, 67, 0.07), 0 0 0 0 #ffd6d1; }
    40% { box-shadow: 0 8px 32px rgba(139, 69, 67, 0.18), 0 0 0 8px #ffd6d1; }
    100% { box-shadow: 0 8px 32px rgba(139, 69, 67, 0.18), 0 0 0 0 #ffd6d1; }
}
.card.mb-4.flash {
    animation: card-flash 0.45s;
}
.section-title {
    color: #8B4543;
    font-size: 2.2rem;
    font-weight: 700;
    letter-spacing: 0.7px;
    margin-bottom: 1.7rem;
    margin-top: 1.2rem;
    display: flex;
    align-items: center;
    gap: 0.7rem;
    position: relative;
    background: none;
    border: none;
    animation: fadeInDown 0.7s;
}
.section-title .section-icon {
    font-size: 1.5em;
    color: #8B4543;
    opacity: 0.92;
}
.section-title::after {
    content: '';
    display: block;
    position: absolute;
    left: 0;
    bottom: -7px;
    width: 100%;
    height: 5px;
    border-radius: 3px;
    background: linear-gradient(90deg, #8B4543 0%, #b97a6a 100%);
    opacity: 0.18;
}
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-18px); }
    to { opacity: 1; transform: translateY(0); }
}
.nav-tabs {
    border-bottom: none;
    display: flex;
    justify-content: flex-start;
    gap: 2.5rem;
    background: none;
    margin-bottom: 1.5rem;
}
.nav-tabs .nav-item {
    margin-bottom: 0;
}
.nav-tabs .nav-link {
    color: #8B4543;
    border: none;
    font-weight: 500;
    border-radius: 1.5rem 1.5rem 0 0;
    background: none;
    font-size: 1.08rem;
    display: flex;
    align-items: center;
    gap: 0.5em;
    padding: 0.7rem 1.5rem 0.7rem 1.2rem;
    transition: background 0.18s, color 0.18s;
    box-shadow: none;
    margin-right: 0;
}
.nav-tabs .nav-link .tab-icon {
    font-size: 1.15em;
    margin-right: 0.3em;
    color: #8B4543;
    opacity: 0.92;
}
.nav-tabs .nav-link.active {
    background-color: #8B4543;
    color: #fff;
    border: none;
    box-shadow: 0 2px 12px rgba(139, 69, 67, 0.10);
    font-weight: 600;
    letter-spacing: 0.5px;
    z-index: 2;
}
.nav-tabs .nav-link.active .tab-icon {
    color: #fff;
    opacity: 1;
}
.nav-tabs .nav-link:not(.active):hover {
    background: #f3e9e8;
    color: #8B4543;
}
.nav-tabs .nav-link:not(.active) {
    color: #8B4543 !important;
    opacity: 1 !important;
    background: none !important;
    cursor: pointer !important;
}
.swal2-confirm-green {
    background-color: #4A7C59 !important;
    color: #fff !important;
    border-radius: 0.75rem !important;
    padding: 0.75rem 1.5rem !important;
    font-size: 1rem !important;
    font-weight: 500 !important;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(74, 124, 89, 0.15) !important;
}
.swal2-confirm-green:focus {
    box-shadow: 0 0 0 0.25rem rgba(74, 124, 89, 0.25) !important;
}
</style>
<div class="container-fluid px-4">
    <h1 class="section-title"><span class="section-icon"><i class="fas fa-archive"></i></span>Archived Lists</h1>
    <ul class="nav nav-tabs mb-3" id="archiveTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="cat-tab" data-bs-toggle="tab" data-bs-target="#cat" type="button" role="tab"><span class="tab-icon"><i class="fas fa-list-alt"></i></span>Categories</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="ingredient-tab" data-bs-toggle="tab" data-bs-target="#ingredient" type="button" role="tab"><span class="tab-icon"><i class="fas fa-carrot"></i></span>Ingredients</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="user-tab" data-bs-toggle="tab" data-bs-target="#user" type="button" role="tab"><span class="tab-icon"><i class="fas fa-user"></i></span>Users</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="prod-tab" data-bs-toggle="tab" data-bs-target="#prod" type="button" role="tab"><span class="tab-icon"><i class="fas fa-box-open"></i></span>Products</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="branch-tab" data-bs-toggle="tab" data-bs-target="#branch" type="button" role="tab"><span class="tab-icon"><i class="fas fa-store-alt"></i></span>Branches</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="request-tab" data-bs-toggle="tab" data-bs-target="#request" type="button" role="tab"><span class="tab-icon"><i class="fas fa-clipboard-list"></i></span>Request Stock</button>
        </li>
    </ul>
    <div class="tab-content" id="archiveTabsContent">
        <!-- Categories -->
        <div class="tab-pane fade show active" id="cat" role="tabpanel">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div><i class="fas fa-box-archive me-1"></i> Archived Category List</div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Category Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        if ($pdo->query("SHOW TABLES LIKE 'archive_category'")->rowCount()) {
                            $stmt = $pdo->prepare("SELECT * FROM archive_category");
                            $stmt->execute();
                            $archived = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if (count($archived) === 0) {
                                echo '<tr><td colspan="4" class="text-center text-muted">No archived categories found.</td></tr>';
                            } else {
                                foreach ($archived as $cat) {
                                    echo '<tr>';
                                    echo '<td>' . htmlspecialchars($cat['category_name']) . '</td>';
                                    echo '<td>' . htmlspecialchars($cat['description']) . '</td>';
                                    echo '<td><span class="badge bg-secondary">Archived</span></td>';
                                    echo '<td><button class="btn btn-restore btn-sm restore-btn" data-id="' . $cat['archive_id'] . '" data-type="category"><i class="fas fa-undo"></i> Restore</button></td>';
                                    echo '</tr>';
                                }
                            }
                        } else {
                            echo '<tr><td colspan="4" class="text-center text-danger">archive_category table does not exist.</td></tr>';
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Ingredients -->
        <div class="tab-pane fade" id="ingredient" role="tabpanel">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div><i class="fas fa-box-archive me-1"></i> Archived Ingredient List</div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Ingredient Name</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        if ($pdo->query("SHOW TABLES LIKE 'archive_ingredient'")->rowCount()) {
                            $stmt = $pdo->prepare("SELECT ai.*, pc.category_name FROM archive_ingredient ai LEFT JOIN pos_category pc ON ai.category_id = pc.category_id");
                            $stmt->execute();
                            $archived = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if (count($archived) === 0) {
                                echo '<tr><td colspan="6" class="text-center text-muted">No archived ingredients found.</td></tr>';
                            } else {
                                foreach ($archived as $ingredient) {
                                    echo '<tr>';
                                    echo '<td>' . htmlspecialchars($ingredient['ingredient_name']) . '</td>';
                                    echo '<td>' . htmlspecialchars($ingredient['category_name']) . '</td>';
                                    echo '<td>' . htmlspecialchars($ingredient['quantity']) . '</td>';
                                    echo '<td>' . htmlspecialchars($ingredient['unit']) . '</td>';
                                    echo '<td><span class="badge bg-secondary">Archived</span></td>';
                                    echo '<td><button class="btn btn-restore btn-sm restore-btn" data-id="' . $ingredient['archive_id'] . '" data-type="ingredient"><i class="fas fa-undo"></i> Restore</button></td>';
                                    echo '</tr>';
                                }
                            }
                        } else {
                            echo '<tr><td colspan="6" class="text-center text-danger">archive_ingredient table does not exist.</td></tr>';
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Users -->
        <div class="tab-pane fade" id="user" role="tabpanel">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div><i class="fas fa-box-archive me-1"></i> Archived User List</div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        if ($pdo->query("SHOW TABLES LIKE 'archive_user'")->rowCount()) {
                            $stmt = $pdo->prepare("SELECT * FROM archive_user");
                            $stmt->execute();
                            $archived = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if (count($archived) === 0) {
                                echo '<tr><td colspan="5" class="text-center text-muted">No archived users found.</td></tr>';
                            } else {
                                foreach ($archived as $user) {
                                    echo '<tr>';
                                    echo '<td>' . htmlspecialchars($user['user_name']) . '</td>';
                                    echo '<td>' . htmlspecialchars($user['user_email']) . '</td>';
                                    echo '<td>' . htmlspecialchars($user['user_type']) . '</td>';
                                    echo '<td><span class="badge bg-secondary">Archived</span></td>';
                                    echo '<td><button class="btn btn-restore btn-sm restore-btn" data-id="' . $user['archive_id'] . '" data-type="user"><i class="fas fa-undo"></i> Restore</button></td>';
                                    echo '</tr>';
                                }
                            }
                        } else {
                            echo '<tr><td colspan="5" class="text-center text-danger">archive_user table does not exist.</td></tr>';
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Products -->
        <div class="tab-pane fade" id="prod" role="tabpanel">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div><i class="fas fa-box-archive me-1"></i> Archived Product List</div>
                </div>
                <div class="card-body">
                    <div id="archivedProductTableContainer"></div>
                </div>
            </div>
        </div>
        <!-- Branches -->
        <div class="tab-pane fade" id="branch" role="tabpanel">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div><i class="fas fa-box-archive me-1"></i> Archived Branch List</div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        if ($pdo->query("SHOW TABLES LIKE 'archive_branch'")->rowCount()) {
                            $stmt = $pdo->prepare("SELECT * FROM archive_branch");
                            $stmt->execute();
                            $archived = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if (count($archived) === 0) {
                                echo '<tr><td colspan="5" class="text-center text-muted">No archived branches found.</td></tr>';
                            } else {
                                foreach ($archived as $branch) {
                                    echo '<tr>';
                                    echo '<td>' . htmlspecialchars($branch['branch_name']) . '</td>';
                                    echo '<td>' . htmlspecialchars($branch['branch_code']) . '</td>';
                                    echo '<td>' . htmlspecialchars($branch['contact_number']) . '</td>';
                                    echo '<td><span class="badge bg-secondary">Archived</span></td>';
                                    echo '<td><button class="btn btn-restore btn-sm restore-btn" data-id="' . $branch['archive_id'] . '" data-type="branch"><i class="fas fa-undo"></i> Restore</button></td>';
                                    echo '</tr>';
                                }
                            }
                        } else {
                            echo '<tr><td colspan="5" class="text-center text-danger">archive_branch table does not exist.</td></tr>';
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Request Stock -->
        <div class="tab-pane fade" id="request" role="tabpanel">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div><i class="fas fa-box-archive me-1"></i> Archived Request Stock List</div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Branch</th>
                                <th>Date Requested</th>
                                <th>Ingredients</th>
                                <th>Status</th>
                                <th>Delivery Status</th>
                                <th>Updated By</th>
                                <th>Archived By</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        if ($pdo->query("SHOW TABLES LIKE 'archive_ingredient_requests'")->rowCount()) {
                            $stmt = $pdo->prepare("
                                SELECT ar.*, b.branch_name, u1.user_name as updated_by_name, u2.user_name as archived_by_name
                                FROM archive_ingredient_requests ar 
                                LEFT JOIN pos_branch b ON ar.branch_id = b.branch_id 
                                LEFT JOIN pos_user u1 ON ar.updated_by = u1.user_id
                                LEFT JOIN pos_user u2 ON ar.archived_by = u2.user_id
                                ORDER BY ar.archived_at DESC
                            ");
                            $stmt->execute();
                            $archived = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if (count($archived) === 0) {
                                echo '<tr><td colspan="8" class="text-center text-muted">No archived request stocks found.</td></tr>';
                            } else {
                                foreach ($archived as $request) {
                                    // Parse ingredients JSON and get ingredient names
                                    $ingredients_list = [];
                                    $ingredients_json = json_decode($request['ingredients'], true);
                                    
                                    if ($ingredients_json && is_array($ingredients_json)) {
                                        foreach ($ingredients_json as $ingredient) {
                                            if (isset($ingredient['ingredient_id']) && isset($ingredient['quantity'])) {
                                                // Get ingredient name from database
                                                $stmt_ingredient = $pdo->prepare("SELECT ingredient_name, ingredient_unit FROM ingredients WHERE ingredient_id = ?");
                                                $stmt_ingredient->execute([$ingredient['ingredient_id']]);
                                                $ingredient_info = $stmt_ingredient->fetch(PDO::FETCH_ASSOC);
                                                
                                                if ($ingredient_info) {
                                                    $ingredients_list[] = $ingredient_info['ingredient_name'] . ' (' . $ingredient['quantity'] . ' ' . $ingredient_info['ingredient_unit'] . ')';
                                                } else {
                                                    $ingredients_list[] = 'Unknown Ingredient (ID: ' . $ingredient['ingredient_id'] . ') - ' . $ingredient['quantity'];
                                                }
                                            }
                                        }
                                    }
                                    
                                    $ingredients_display = !empty($ingredients_list) ? implode(', ', $ingredients_list) : 'No ingredients specified';
                                    
                                    // Format delivery status
                                    $delivery_status_badge = '';
                                    switch ($request['delivery_status'] ?? 'pending') {
                                        case 'pending':
                                            $delivery_status_badge = '<span class="badge bg-secondary">PENDING</span>';
                                            break;
                                        case 'on_delivery':
                                            $delivery_status_badge = '<span class="badge bg-info">ON DELIVERY</span>';
                                            break;
                                        case 'delivered':
                                            $delivery_status_badge = '<span class="badge bg-success">DELIVERED</span>';
                                            break;
                                        case 'returned':
                                            $delivery_status_badge = '<span class="badge bg-warning">RETURNED</span>';
                                            break;
                                        case 'cancelled':
                                            $delivery_status_badge = '<span class="badge bg-danger">CANCELLED</span>';
                                            break;
                                        default:
                                            $delivery_status_badge = '<span class="badge bg-secondary">PENDING</span>';
                                    }
                                    
                                    echo '<tr>';
                                    echo '<td>' . htmlspecialchars($request['branch_name'] ?: 'Unknown Branch') . '</td>';
                                    echo '<td>' . date('M j, Y g:i A', strtotime($request['request_date'])) . '</td>';
                                    echo '<td>' . htmlspecialchars($ingredients_display) . '</td>';
                                    echo '<td><span class="badge bg-' . ($request['status'] === 'approved' ? 'success' : ($request['status'] === 'rejected' ? 'danger' : 'warning')) . '">' . strtoupper($request['status']) . '</span></td>';
                                    echo '<td>' . $delivery_status_badge . '</td>';
                                    echo '<td>' . htmlspecialchars($request['updated_by_name'] ?: 'N/A') . '</td>';
                                    echo '<td>' . htmlspecialchars($request['archived_by_name'] ?: 'N/A') . '</td>';
                                    echo '<td><button class="btn btn-restore btn-sm restore-btn" data-id="' . $request['archive_id'] . '" data-type="request"><i class="fas fa-undo"></i> Restore</button></td>';
                                    echo '</tr>';
                                }
                            }
                        } else {
                            echo '<tr><td colspan="8" class="text-center text-danger">archive_ingredient_requests table does not exist.</td></tr>';
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Pagination for archived products
const archivedProducts = <?php
if ($pdo->query("SHOW TABLES LIKE 'archive_product'")->rowCount()) {
    $stmt = $pdo->prepare("SELECT ap.*, pc.category_name FROM archive_product ap LEFT JOIN pos_category pc ON ap.category_id = pc.category_id");
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} else {
    echo '[]';
}
?>;
const pageSize = 5;
let currentPage = 1;

function renderArchivedProductTable(page) {
    const start = (page - 1) * pageSize;
    const end = start + pageSize;
    const pageData = archivedProducts.slice(start, end);
    let html = `<table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Description</th>
                <th>Ingredients</th>
                <th>Status</th>
                <th>Image</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>`;
    if (pageData.length === 0) {
        html += `<tr><td colspan="8" class="text-center text-muted">No archived products found.</td></tr>`;
    } else {
        for (const prod of pageData) {
            html += `<tr>`;
            html += `<td>${prod.product_name ? prod.product_name : ''}</td>`;
            html += `<td>${prod.category_name ? prod.category_name : 'N/A'}</td>`;
            html += `<td>₱${parseFloat(prod.product_price).toFixed(2)}</td>`;
            html += `<td>${prod.description ? prod.description : ''}</td>`;
            html += `<td>${prod.ingredients ? prod.ingredients : ''}</td>`;
            html += `<td><span class="badge bg-secondary">Archived</span></td>`;
            html += `<td>`;
            if (prod.product_image) {
                html += `<img src="${prod.product_image}" class="product-image" style="width:40px;height:40px;object-fit:cover;border-radius:6px;">`;
            } else {
                html += 'No Image';
            }
            html += `</td>`;
            html += `<td><button class="btn btn-restore btn-sm restore-btn" data-id="${prod.archive_id}" data-type="product"><i class="fas fa-undo"></i> Restore</button></td>`;
            html += `</tr>`;
        }
    }
    html += `</tbody></table>`;
    // Pagination controls
    const totalPages = Math.ceil(archivedProducts.length / pageSize);
    html += `<div class="d-flex justify-content-between align-items-center mt-2">
        <button class="archived-pagination-btn" id="archivedPrevBtn" ${page === 1 ? 'disabled' : ''}><i class='fas fa-chevron-left'></i> Previous</button>
        <span>Page ${page} of ${totalPages}</span>
        <button class="archived-pagination-btn" id="archivedNextBtn" ${page === totalPages ? 'disabled' : ''}>Next <i class='fas fa-chevron-right'></i></button>
    </div>`;
    document.getElementById('archivedProductTableContainer').innerHTML = html;
    // Add event listeners
    document.getElementById('archivedPrevBtn').onclick = (e) => {
        if (currentPage > 1) {
            createRipple(e);
            setTimeout(() => {
                currentPage--;
                renderArchivedProductTable(currentPage);
            }, 180);
        }
    };
    document.getElementById('archivedNextBtn').onclick = (e) => {
        if (currentPage < totalPages) {
            createRipple(e);
            setTimeout(() => {
                currentPage++;
                renderArchivedProductTable(currentPage);
            }, 180);
        }
    };
    // Re-attach restore button logic
    document.querySelectorAll('.restore-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const type = this.getAttribute('data-type');
            let url = '';
            // Always call custom function for branch restore
            if (type === 'branch') {
                restoreBranchCustomAction(id);
                url = 'archive_branch.php';
            }
            if (type === 'product') url = 'archive_product.php';
            if (type === 'ingredient') url = 'archive_ingredient.php';
            Swal.fire({
                title: 'Restore?',
                text: 'This will move the record back to the active list.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4A7C59',
                cancelButtonColor: '#f8f9fa',
                confirmButtonText: '<i class="fas fa-undo me-2"></i>Yes, restore it!',
                cancelButtonText: '<i class="fas fa-times me-2"></i>Cancel',
                customClass: {
                    confirmButton: 'btn btn-restore btn-lg',
                    cancelButton: 'btn btn-light btn-lg'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(url, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'id=' + encodeURIComponent(id) + '&restore=1'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (type === 'branch') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Restored!',
                                    text: 'Branch has been restored.',
                                    showConfirmButton: true,
                                    confirmButtonText: 'OK',
                                    customClass: {
                                        confirmButton: 'swal2-confirm-green'
                                    }
                                });
                                // Remove the row from the table
                                const row = btn.closest('tr');
                                if (row) row.remove();
                            } else if (type === 'ingredient') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Restored!',
                                    text: 'Ingredient has been restored.',
                                    showConfirmButton: true,
                                    confirmButtonText: 'OK',
                                    customClass: {
                                        confirmButton: 'swal2-confirm-green'
                                    }
                                }).then(() => location.reload());
                            } else {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Restored!',
                                    text: 'Product has been restored.',
                                    showConfirmButton: true,
                                    confirmButtonText: 'OK',
                                    customClass: {
                                        confirmButton: 'swal2-confirm-green'
                                    }
                                }).then(() => location.reload());
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: data.message || 'Failed to restore record.'
                            });
                        }
                    });
                }
            });
        });
    });
    const card = document.querySelector('#prod .card.mb-4');
    if (card) {
        card.classList.remove('flash');
        void card.offsetWidth; // force reflow
        card.classList.add('flash');
    }
}
document.addEventListener('DOMContentLoaded', function() {
    renderArchivedProductTable(currentPage);
});
// Ripple effect function
function createRipple(e) {
    const button = e.currentTarget;
    const circle = document.createElement('span');
    circle.classList.add('ripple');
    const diameter = Math.max(button.clientWidth, button.clientHeight);
    circle.style.width = circle.style.height = `${diameter}px`;
    circle.style.left = `${e.offsetX - diameter / 2}px`;
    circle.style.top = `${e.offsetY - diameter / 2}px`;
    button.appendChild(circle);
    setTimeout(() => circle.remove(), 500);
}
// Restore button for archived categories
$(document).on('click', '.restore-btn[data-type="category"]', function() {
    var archiveId = $(this).data('id');
    // Custom function for category restore
    restoreCategoryCustomAction(archiveId);
    Swal.fire({
        title: 'Restore?',
        text: 'This will move the category back to the active list.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4A7C59',
        cancelButtonColor: '#f8f9fa',
        confirmButtonText: '<i class="fas fa-undo me-2"></i>Yes, restore it!',
        cancelButtonText: '<i class="fas fa-times me-2"></i>Cancel',
        customClass: {
            confirmButton: 'btn btn-restore btn-lg',
            cancelButton: 'btn btn-light btn-lg'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'archive_category.php',
                type: 'POST',
                data: { id: archiveId, restore: 1 },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Restored!',
                            text: 'Category has been restored.',
                            showConfirmButton: true,
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'swal2-confirm-green'
                            }
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Failed to restore category.'
                        });
                    }
                },
                error: function(xhr) {
                    let msg = 'An error occurred while restoring the category.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: msg
                    });
                }
            });
        }
    });
});
// Add this event handler for archiving products
$(document).on('click', '.archive-btn[data-type="product"]', function() {
    var archiveId = $(this).data('id');
    Swal.fire({
        title: 'Are you sure?',
        text: 'You can restore this product from the archive.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#B33A3A',
        cancelButtonColor: '#f8f9fa',
        confirmButtonText: '<i class="fas fa-box-archive me-2"></i>Yes, archive it!',
        cancelButtonText: '<i class="fas fa-times me-2"></i>Cancel',
        customClass: {
            confirmButton: 'swal2-confirm-archive',
            cancelButton: 'btn btn-light btn-lg'
        },
        buttonsStyling: false,
        padding: '2rem',
        width: 400,
        showClass: {
            popup: 'animate__animated animate__fadeInDown animate__faster'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutUp animate__faster'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'archive_product.php',
                type: 'POST',
                data: { product_id: archiveId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Remove the product from the archivedProducts array and refresh the table
                        const idx = archivedProducts.findIndex(p => p.archive_id == archiveId);
                        if (idx !== -1) {
                            archivedProducts.splice(idx, 1);
                        }
                        renderArchivedProductTable(currentPage);
                        Swal.fire({
                            icon: 'success',
                            title: 'Archived!',
                            text: 'Product has been archived successfully.',
                            showConfirmButton: true,
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'swal2-confirm-green'
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Failed to archive product.'
                        });
                    }
                },
                error: function(xhr) {
                    let msg = 'An error occurred while archiving the product.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: msg
                    });
                }
            });
        }
    });
});
// Custom function for Restore Branch button
function restoreBranchCustomAction(archiveId) {
    // Custom logic for branch restore goes here
    console.log('Restore button clicked for branch archive ID:', archiveId);
    // Example: send analytics, log, or trigger other actions
}
// Custom function for Restore Category button
function restoreCategoryCustomAction(archiveId) {
    // Custom logic for category restore goes here
    console.log('Restore button clicked for category archive ID:', archiveId);
    // Example: send analytics, log, or trigger other actions
}

// Restore button for archived request stocks
$(document).on('click', '.restore-btn[data-type="request"]', function() {
    var archiveId = $(this).data('id');
    Swal.fire({
        title: 'Restore?',
        text: 'This will move the request stock back to the active list.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4A7C59',
        cancelButtonColor: '#f8f9fa',
        confirmButtonText: '<i class="fas fa-undo me-2"></i>Yes, restore it!',
        cancelButtonText: '<i class="fas fa-times me-2"></i>Cancel',
        customClass: {
            confirmButton: 'btn btn-restore btn-lg',
            cancelButton: 'btn btn-light btn-lg'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'restore_ingredient_request.php',
                type: 'POST',
                data: { archive_id: archiveId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Restored!',
                            text: 'Request stock has been restored.',
                            showConfirmButton: true,
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'swal2-confirm-green'
                            }
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Failed to restore request stock.'
                        });
                    }
                },
                error: function(xhr) {
                    let msg = 'An error occurred while restoring the request stock.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: msg
                    });
                }
            });
        }
    });
});

// Restore button for archived users
$(document).on('click', '.restore-btn[data-type="user"]', function() {
    var archiveId = $(this).data('id');
    var $row = $(this).closest('tr'); // Store reference to the row
    Swal.fire({
        title: 'Restore User?',
        text: 'This will move the user back to the active user list.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4A7C59',
        cancelButtonColor: '#f8f9fa',
        confirmButtonText: '<i class="fas fa-undo me-2"></i>Yes, restore it!',
        cancelButtonText: '<i class="fas fa-times me-2"></i>Cancel',
        customClass: {
            confirmButton: 'btn btn-restore btn-lg',
            cancelButton: 'btn btn-light btn-lg'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'archive_user.php',
                type: 'POST',
                data: { id: archiveId, restore: 1 },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'User has been restored successfully.',
                            icon: 'success',
                            confirmButtonColor: '#4A7C59',
                            customClass: {
                                confirmButton: 'swal2-confirm-green'
                            }
                        }).then(() => {
                            // Remove the restored user row from the table
                            $row.fadeOut(300, function() {
                                $(this).remove();
                                // Check if table is now empty
                                refreshUserTable();
                            });
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.message || 'Failed to restore user.',
                            icon: 'error'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while restoring the user.',
                        icon: 'error'
                    });
                }
            });
        }
    });
});

// Function to refresh the user table content without reloading the page
function refreshUserTable() {
    const userTableBody = document.querySelector('#user tbody');
    if (userTableBody) {
        // Check if there are any remaining data rows (excluding the "no data" message row)
        const dataRows = userTableBody.querySelectorAll('tr:not(.text-center)');
        if (dataRows.length === 0) {
            // Show "No archived users found" message
            userTableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No archived users found.</td></tr>';
        }
    }
}
</script>
<?php include('footer.php'); ?> 