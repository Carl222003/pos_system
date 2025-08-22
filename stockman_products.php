<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in and is a stockman
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_type'] !== 'Stockman') {
    header('Location: login.php');
    exit();
}

include('header.php');
?>

<style>
.stockman-dashboard-bg {
    background: #f8f5f5;
    min-height: 100vh;
    padding-bottom: 2rem;
}

.stockman-section-title {
    color: #8B4543;
    font-size: 2.2rem;
    font-weight: 700;
    letter-spacing: 0.7px;
    margin-bottom: 2rem;
    margin-top: 1.2rem;
    display: flex;
    align-items: center;
    gap: 0.7rem;
    position: relative;
    background: none;
    border: none;
    animation: fadeInDown 0.7s;
}

.stockman-section-title .section-icon {
    font-size: 1.5em;
    color: #8B4543;
    opacity: 0.92;
}

.stockman-section-title::after {
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

.stockman-card {
    background: #fff;
    border-radius: 1.1rem;
    box-shadow: 0 2px 12px rgba(139, 69, 67, 0.07);
    margin-bottom: 2rem;
    border: 1.5px solid #e5d6d6;
}

.stockman-card .card-header {
    background: #8B4543;
    color: #fff;
    border-radius: 1.1rem 1.1rem 0 0;
    font-weight: 600;
    font-size: 1.1rem;
    padding: 1.2rem 1.5rem;
    border-bottom: none;
    display: flex;
    align-items: center;
    gap: 0.7rem;
}

.stockman-card .card-body {
    padding: 1.5rem;
}

/* Product Cards Grid */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.product-card {
    background: #fff;
    border-radius: 1rem;
    box-shadow: 0 2px 10px rgba(139, 69, 67, 0.08);
    border: 1px solid #e5d6d6;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    overflow: hidden;
}

.product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(139, 69, 67, 0.15);
}

.product-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    background: #f8f9fa;
}

.product-info {
    padding: 1.2rem;
}

.product-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #8B4543;
    margin-bottom: 0.5rem;
}

.product-category {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.product-price {
    font-size: 1.2rem;
    font-weight: 700;
    color: #28a745;
    margin-bottom: 0.8rem;
}

.product-description {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 1rem;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-status {
    margin-bottom: 1rem;
}

.status-badge {
    padding: 0.3rem 0.8rem;
    border-radius: 0.5rem;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-available {
    background: #d4edda;
    color: #155724;
}

.status-unavailable {
    background: #f8d7da;
    color: #721c24;
}

.product-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.btn-view-details {
    background: #8B4543;
    border: none;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.9rem;
    transition: background 0.2s ease;
    width: 100%;
}

.btn-view-details:hover {
    background: #7a3d3b;
    color: white;
}

.btn-toggle-status {
    border: none;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.85rem;
    transition: all 0.2s ease;
    width: 100%;
    font-weight: 500;
}

.btn-make-unavailable {
    background: #dc3545;
}

.btn-make-unavailable:hover {
    background: #c82333;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
}

.btn-make-available {
    background: #28a745;
}

.btn-make-available:hover {
    background: #218838;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}

/* Filters */
.filters-section {
    background: #fff;
    border-radius: 0.8rem;
    padding: 1.2rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px rgba(139, 69, 67, 0.05);
    border: 1px solid #e5d6d6;
}

.filter-row {
    display: flex;
    gap: 1rem;
    align-items: end;
    flex-wrap: wrap;
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

.filter-label {
    font-weight: 500;
    color: #8B4543;
    margin-bottom: 0.5rem;
    display: block;
}

.filter-input,
.filter-select {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #ddd;
    border-radius: 0.5rem;
    font-size: 0.9rem;
}

.btn-filter {
    background: #8B4543;
    border: none;
    color: white;
    padding: 0.5rem 1.5rem;
    border-radius: 0.5rem;
    font-size: 0.9rem;
    height: fit-content;
}

.btn-filter:hover {
    background: #7a3d3b;
}

.btn-clear {
    background: #6c757d;
    border: none;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.9rem;
    height: fit-content;
}

.btn-clear:hover {
    background: #5a6268;
}

/* Loading and Empty States */
.loading-state {
    text-align: center;
    padding: 3rem;
    color: #6c757d;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6c757d;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-18px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Responsive */
/* Enhanced Product Details Modal */
.product-modal {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 20px 60px rgba(139, 69, 67, 0.3);
    overflow: hidden;
}

.product-modal-header {
    background: linear-gradient(135deg, #8B4543 0%, #a85853 100%);
    color: white;
    border: none;
    padding: 1.5rem 2rem;
    position: relative;
}

.product-modal-header .modal-title {
    font-size: 1.3rem;
    font-weight: 600;
    margin: 0;
}

.btn-close-custom {
    background: rgba(255, 255, 255, 0.2);
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
    transition: all 0.3s ease;
    cursor: pointer;
}

.btn-close-custom:hover {
    background: rgba(255, 255, 255, 0.3);
    border-color: rgba(255, 255, 255, 0.5);
    transform: rotate(90deg);
    color: white;
}

.product-modal-body {
    padding: 2rem;
    background: #f8f9fa;
}

.product-modal-footer {
    background: white;
    border-top: 1px solid #e9ecef;
    padding: 1.5rem 2rem;
    text-align: center;
}

.btn-secondary-custom {
    background: #6c757d;
    border: none;
    color: white;
    padding: 0.75rem 2rem;
    border-radius: 0.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-secondary-custom:hover {
    background: #5a6268;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
}

/* Product Details Content */
.product-details-container {
    background: white;
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
}

.product-image-container {
    position: relative;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(139, 69, 67, 0.2);
}

.product-detail-image {
    width: 100%;
    height: 350px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-image-container:hover .product-detail-image {
    transform: scale(1.05);
}

.image-overlay {
    position: absolute;
    top: 1rem;
    right: 1rem;
}

.status-badge-large {
    padding: 0.5rem 1rem;
    border-radius: 2rem;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.badge-success {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
}

.badge-danger {
    background: linear-gradient(135deg, #dc3545, #e74c3c);
    color: white;
}

.product-info-section {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.product-header {
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 1rem;
}

.product-title {
    color: #8B4543;
    font-size: 2rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
    line-height: 1.2;
}

.product-category {
    color: #6c757d;
    font-size: 1rem;
    font-weight: 500;
}

.product-category i {
    color: #8B4543;
}

.product-price-section {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    padding: 1.5rem;
    border-radius: 0.8rem;
    text-align: center;
    border: 2px solid #8B4543;
}

.price-label {
    color: #6c757d;
    font-size: 0.9rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 0.5rem;
}

.price-value {
    color: #28a745;
    font-size: 2.2rem;
    font-weight: 700;
    margin: 0;
}

.product-section {
    background: #f8f9fa;
    border-radius: 0.8rem;
    padding: 1.2rem;
    border-left: 4px solid #8B4543;
}

.section-header {
    color: #8B4543;
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.8rem;
    display: flex;
    align-items: center;
}

.section-content {
    color: #495057;
    line-height: 1.6;
    font-size: 0.95rem;
}

.ingredients-list {
    font-style: italic;
    color: #6c757d;
}

.branch-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 1rem;
    border-radius: 0.5rem;
    border: 1px solid #dee2e6;
}

.branch-name {
    font-weight: 600;
    color: #8B4543;
    font-size: 1.1rem;
}

.branch-quantity {
    background: #e9ecef;
    color: #495057;
    padding: 0.3rem 0.8rem;
    border-radius: 1rem;
    font-size: 0.9rem;
    font-weight: 500;
}

.product-meta {
    margin-top: auto;
    padding-top: 1.5rem;
    border-top: 1px solid #e9ecef;
}

.meta-item {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
}

.meta-item i {
    color: #8B4543;
    width: 20px;
}

@media (max-width: 768px) {
    .filter-row {
        flex-direction: column;
    }
    
    .filter-group {
        min-width: 100%;
    }
    
    .products-grid {
        grid-template-columns: 1fr;
    }
    
    .product-details-container {
        padding: 1rem;
    }
    
    .product-title {
        font-size: 1.5rem;
    }
    
    .price-value {
        font-size: 1.8rem;
    }
    
    .product-detail-image {
        height: 250px;
    }
    
    .modal-dialog {
        margin: 0.5rem;
    }
    
    .product-modal-body {
        padding: 1rem;
    }
}
</style>

<div class="stockman-dashboard-bg">
    <div class="container-fluid px-4">
        <div class="stockman-section-title">
            <span class="section-icon"><i class="fas fa-shopping-bag"></i></span>
            Available Products
        </div>

        <!-- Filters Section -->
        <div class="filters-section">
            <div class="filter-row">
                <div class="filter-group">
                    <label class="filter-label">Search Products</label>
                    <input type="text" id="searchInput" class="filter-input" placeholder="Search by name, description...">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Category</label>
                    <select id="categoryFilter" class="filter-select">
                        <option value="">All Categories</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Status</label>
                                            <select id="statusFilter" class="filter-select">
                            <option value="" selected>🔍 All Status</option>
                            <option value="Available">🟢 Available Only</option>
                            <option value="Unavailable">🔴 Unavailable Only</option>
                        </select>
                </div>
                <div class="filter-group">
                    <button id="applyFilters" class="btn-filter">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <button id="clearFilters" class="btn-clear">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
            </div>
        </div>

        <!-- Products Section -->
        <div class="stockman-card">
            <div class="card-header">
                <i class="fas fa-shopping-bag me-1"></i>
                Product Catalog
                <span class="badge bg-light text-dark ms-auto" id="productCount">0 products</span>
            </div>
            <div class="card-body">
                <div id="productsContainer">
                    <div class="loading-state">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Loading products...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Product Details Modal -->
<div class="modal fade" id="productDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content product-modal">
            <div class="modal-header product-modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-shopping-bag me-2"></i>
                    Product Details
                </h5>
                <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body product-modal-body" id="productDetailsContent">
                <!-- Product details will be loaded here -->
            </div>
            <div class="modal-footer product-modal-footer">
                <button type="button" class="btn btn-secondary-custom" data-bs-dismiss="modal">
                    <i class="fas fa-arrow-left me-1"></i>
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let currentFilters = {
        search: '',
        category: '',
        status: ''
    };



    // Load categories for filter
    function loadCategories() {
        $.get('get_categories.php', function(response) {
            if (response.success) {
                const categorySelect = $('#categoryFilter');
                categorySelect.empty().append('<option value="">All Categories</option>');
                
                response.categories.forEach(category => {
                    categorySelect.append(`<option value="${category.category_id}">${category.category_name}</option>`);
                });
            }
        }).fail(function() {
            console.error('Failed to load categories');
        });
    }

    // Load products with filters
    function loadProducts() {
        const params = new URLSearchParams(currentFilters);
        
        $.get(`get_stockman_products.php?${params.toString()}`, function(response) {
            const container = $('#productsContainer');
            
            // Debug logging
            console.log('Products API Response:', response);
            console.log('Current filters:', currentFilters);
            console.log('Products found:', response.products ? response.products.length : 0);
            

            
            // Show current filter status in UI
            const filterStatus = currentFilters.status || 'All Status';
            console.log('Current status filter:', filterStatus);
            
            if (response.success && response.products && response.products.length > 0) {
                // Update product count
                $('#productCount').text(`${response.products.length} product${response.products.length !== 1 ? 's' : ''}`);
                
                // Create products grid
                let html = '<div class="products-grid">';
                
                response.products.forEach(product => {
                    const statusClass = product.product_status === 'Available' ? 'status-available' : 'status-unavailable';
                    const imageSrc = product.product_image ? `uploads/products/${product.product_image}` : 'asset/images/logo.png';
                    
                    html += `
                        <div class="product-card">
                            <img src="${imageSrc}" alt="${product.product_name}" class="product-image" 
                                 onerror="this.src='asset/images/logo.png'">
                            <div class="product-info">
                                <div class="product-name">${product.product_name}</div>
                                <div class="product-category">${product.category_name || 'Uncategorized'}</div>
                                <div class="product-price">₱${parseFloat(product.product_price).toFixed(2)}</div>
                                <div class="product-description">${product.description || 'No description available'}</div>

                                <div class="product-status">
                                    <span class="status-badge ${statusClass}">${product.product_status}</span>
                                </div>
                                <div class="product-actions">
                                    <button class="btn-view-details" onclick="viewProductDetails(${product.product_id})">
                                        <i class="fas fa-eye me-1"></i>
                                        View Details
                                    </button>
                                    <button class="btn-toggle-status ${product.product_status === 'Available' ? 'btn-make-unavailable' : 'btn-make-available'}" 
                                            onclick="toggleProductStatus(${product.product_id}, '${product.product_status}')">
                                        <i class="fas ${product.product_status === 'Available' ? 'fa-times-circle' : 'fa-check-circle'} me-1"></i>
                                        ${product.product_status === 'Available' ? 'Make Unavailable' : 'Make Available'}
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                html += '</div>';
                container.html(html);
            } else {
                $('#productCount').text('0 products');
                container.html(`
                    <div class="empty-state">
                        <i class="fas fa-shopping-bag"></i>
                        <h5>No Products Found</h5>
                        <p>No products match your current filters or there are no products available.</p>
                    </div>
                `);
            }
        }).fail(function(xhr, status, error) {
            console.error('API call failed:', {xhr, status, error});
            $('#productsContainer').html(`
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle text-warning"></i>
                    <h5>Error Loading Products</h5>
                    <p>There was an error loading the products. Please try again.</p>
                    <p><small>Error: ${error}</small></p>
                </div>
            `);
        });
    }

    // Apply filters
    function applyFilters() {
        currentFilters.search = $('#searchInput').val().trim();
        currentFilters.category = $('#categoryFilter').val();
        currentFilters.status = $('#statusFilter').val();
        
        console.log('Applying filters:', currentFilters);
        loadProducts();
    }

    // Clear filters
    function clearFilters() {
        $('#searchInput').val('');
        $('#categoryFilter').val('');
        $('#statusFilter').val('');
        currentFilters = { search: '', category: '', status: '' };
        
        loadProducts();
    }

    // Toggle product status
    window.toggleProductStatus = function(productId, currentStatus) {
        console.log('Toggle function called:', { productId, currentStatus });
        
        const newStatus = currentStatus === 'Available' ? 'Unavailable' : 'Available';
        const actionText = newStatus === 'Available' ? 'make available' : 'make unavailable';
        const productName = $(event.target).closest('.product-card').find('.product-name').text();
        
        Swal.fire({
            title: 'Confirm Status Change',
            html: `Are you sure you want to ${actionText} <strong>"${productName}"</strong>?<br><br>
                   Current Status: <span style="color: ${currentStatus === 'Available' ? '#28a745' : '#dc3545'}">${currentStatus}</span><br>
                   New Status: <span style="color: ${newStatus === 'Available' ? '#28a745' : '#dc3545'}">${newStatus}</span>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: newStatus === 'Available' ? '#28a745' : '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Yes, ${actionText}`,
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                const button = $(event.target).closest('.btn-toggle-status');
                const originalHtml = button.html();
                button.html('<i class="fas fa-spinner fa-spin me-1"></i>Updating...').prop('disabled', true);
                
                console.log('Sending AJAX request:', { product_id: productId, status: newStatus });
                
                $.ajax({
                    url: 'update_product_status.php',
                    type: 'POST',
                    data: {
                        product_id: productId,
                        status: newStatus
                    },
                    dataType: 'json',
                    timeout: 10000, // 10 second timeout
                    success: function(response) {
                        console.log('AJAX response:', response);
                        
                        if (response.success) {
                            Swal.fire({
                                title: 'Success!',
                                html: `Product <strong>"${productName}"</strong> status updated to <strong>${newStatus}</strong>`,
                                icon: 'success',
                                confirmButtonColor: '#8B4543',
                                timer: 3000,
                                timerProgressBar: true
                            });
                            
                            // Reload products to reflect changes
                            console.log('Status update successful, reloading products...');
                            setTimeout(() => {
                                console.log('Reloading products with current filters:', currentFilters);
                                loadProducts();
                            }, 1000);
                        } else {
                            console.error('Update failed:', response.message);
                            Swal.fire({
                                title: 'Error!',
                                text: response.message || 'Failed to update product status',
                                icon: 'error',
                                confirmButtonColor: '#8B4543'
                            });
                            
                            // Restore button state
                            button.html(originalHtml).prop('disabled', false);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', { xhr, status, error, responseText: xhr.responseText });
                        
                        let errorMessage = 'An error occurred while updating the product status';
                        if (xhr.responseText) {
                            try {
                                const errorResponse = JSON.parse(xhr.responseText);
                                errorMessage = errorResponse.message || errorMessage;
                            } catch (e) {
                                errorMessage = `Server error: ${xhr.status} ${xhr.statusText}`;
                            }
                        }
                        
                        Swal.fire({
                            title: 'Error!',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonColor: '#8B4543'
                        });
                        
                        // Restore button state
                        button.html(originalHtml).prop('disabled', false);
                    }
                });
            }
        });
    };

    // View product details
    window.viewProductDetails = function(productId) {
        $('#productDetailsContent').html(`
            <div class="text-center p-4">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p class="mt-2">Loading product details...</p>
            </div>
        `);
        
        $('#productDetailsModal').modal('show');
        
        $.get(`get_product_details.php?id=${productId}`, function(response) {
            if (response.success && response.product) {
                const product = response.product;
                const imageSrc = product.product_image ? `uploads/products/${product.product_image}` : 'asset/images/logo.png';
                const statusClass = product.product_status === 'Available' ? 'text-success' : 'text-danger';
                
                $('#productDetailsContent').html(`
                    <div class="product-details-container">
                        <div class="row g-4">
                            <div class="col-md-5">
                                <div class="product-image-container">
                                    <img src="${imageSrc}" alt="${product.product_name}" 
                                         class="product-detail-image"
                                         onerror="this.src='asset/images/logo.png'">
                                    <div class="image-overlay">
                                        <div class="status-badge-large ${statusClass.replace('text-', 'badge-')}">${product.product_status}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="product-info-section">
                                    <div class="product-header mb-3">
                                        <h3 class="product-title">${product.product_name}</h3>
                                        <div class="product-category">
                                            <i class="fas fa-tag me-1"></i>
                                            ${product.category_name || 'Uncategorized'}
                                        </div>
                                    </div>
                                    
                                    <div class="product-price-section mb-4">
                                        <div class="price-label">Price</div>
                                        <div class="price-value">₱${parseFloat(product.product_price).toFixed(2)}</div>
                                    </div>
                                    
                                    ${product.description ? `
                                        <div class="product-section mb-3">
                                            <div class="section-header">
                                                <i class="fas fa-align-left me-2"></i>
                                                <strong>Description</strong>
                                            </div>
                                            <div class="section-content">${product.description}</div>
                                        </div>
                                    ` : ''}
                                    
                                    ${product.ingredients ? `
                                        <div class="product-section mb-3">
                                            <div class="section-header">
                                                <i class="fas fa-list-ul me-2"></i>
                                                <strong>Ingredients</strong>
                                            </div>
                                            <div class="section-content ingredients-list">${product.ingredients}</div>
                                        </div>
                                    ` : ''}
                                    
                                    ${product.branch_name ? `
                                        <div class="product-section mb-3">
                                            <div class="section-header">
                                                <i class="fas fa-store me-2"></i>
                                                <strong>Branch Information</strong>
                                            </div>
                                            <div class="section-content">
                                                <div class="branch-info">
                                                    <span class="branch-name">${product.branch_name}</span>
                                                    <span class="branch-quantity">Stock: ${product.branch_quantity || 0}</span>
                                                </div>
                                            </div>
                                        </div>
                                    ` : ''}
                                    
                                    <div class="product-meta">
                                        <div class="meta-item">
                                            <i class="fas fa-calendar-plus me-1"></i>
                                            <span>Created: ${new Date(product.created_at).toLocaleDateString()}</span>
                                        </div>
                                        ${product.updated_at ? `
                                            <div class="meta-item">
                                                <i class="fas fa-calendar-edit me-1"></i>
                                                <span>Updated: ${new Date(product.updated_at).toLocaleDateString()}</span>
                                            </div>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
            } else {
                $('#productDetailsContent').html(`
                    <div class="text-center p-4">
                        <i class="fas fa-exclamation-triangle text-warning fa-2x"></i>
                        <h5 class="mt-2">Error Loading Product</h5>
                        <p>Could not load product details. Please try again.</p>
                    </div>
                `);
            }
        }).fail(function() {
            $('#productDetailsContent').html(`
                <div class="text-center p-4">
                    <i class="fas fa-exclamation-triangle text-danger fa-2x"></i>
                    <h5 class="mt-2">Error</h5>
                    <p>Failed to load product details. Please try again.</p>
                </div>
            `);
        });
    };

    // Event handlers
    $('#applyFilters').click(applyFilters);
    $('#clearFilters').click(clearFilters);
    
    // Search on Enter key
    $('#searchInput').keypress(function(e) {
        if (e.which === 13) {
            applyFilters();
        }
    });

    // Auto-search with debounce
    let searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 500);
    });

    // Filter change handlers
    $('#categoryFilter, #statusFilter').change(applyFilters);

    // Initial load
    loadCategories();
    loadProducts();
});
</script>

<?php include('footer.php'); ?>
