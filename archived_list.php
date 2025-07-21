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
</style>
<div class="container-fluid px-4">
    <h1 class="section-title"><span class="section-icon"><i class="fas fa-archive"></i></span>Archived Lists</h1>
    <ul class="nav nav-tabs mb-3" id="archiveTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="cat-tab" data-bs-toggle="tab" data-bs-target="#cat" type="button" role="tab"><span class="tab-icon"><i class="fas fa-list-alt"></i></span>Categories</button>
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
            if (type === 'product') url = 'archive_product.php';
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
                            Swal.fire({
                                icon: 'success',
                                title: 'Restored!',
                                text: 'Record has been restored.',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => location.reload());
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
</script>
<?php include('footer.php'); ?> 