<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkOrderAccess();

// Fetch category for the dropdown
$categorys = $pdo->query("SELECT category_id, category_name FROM pos_category WHERE status = 'active'")->fetchAll(PDO::FETCH_ASSOC);

$confData = getConfigData($pdo);

include('header.php');
?>

<!-- Add the custom CSS file -->
<link rel="stylesheet" href="asset/css/order-custom.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://fonts.cdnfonts.com/css/cooper-black" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
/* Enhanced POS System Design */
:root {
    --primary-color: #8B4543;
    --primary-dark: #723937;
    --primary-light: #A65D5D;
    --accent-color: #D4A59A;
    --success-color: #28a745;
    --warning-color: #ffc107;
    --danger-color: #dc3545;
    --info-color: #17a2b8;
    --light-color: #f8f9fa;
    --dark-color: #343a40;
    --border-color: #dee2e6;
    --shadow-light: 0 2px 10px rgba(0, 0, 0, 0.1);
    --shadow-medium: 0 4px 20px rgba(0, 0, 0, 0.15);
    --shadow-heavy: 0 8px 30px rgba(0, 0, 0, 0.2);
    --gradient-primary: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    --gradient-success: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    --gradient-warning: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    --gradient-danger: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
}

body {
    font-family: 'Inter', sans-serif;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    min-height: 100vh;
}

/* Enhanced Sidebar */
.sidebar {
    background: var(--gradient-primary);
    box-shadow: var(--shadow-heavy);
    border-radius: 0 1rem 1rem 0;
    backdrop-filter: blur(10px);
}

.sidebar .navbar-brand {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 1rem;
    padding: 1rem;
    margin: 1rem;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.sidebar .nav-link {
    color: rgba(255, 255, 255, 0.9) !important;
    border-radius: 0.75rem;
    margin: 0.25rem 1rem;
    padding: 0.75rem 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.sidebar .nav-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
    transition: left 0.5s;
}

.sidebar .nav-link:hover::before {
    left: 100%;
}

.sidebar .nav-link:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateX(5px);
    box-shadow: var(--shadow-light);
}

.sidebar .nav-link.active {
    background: rgba(255, 255, 255, 0.2);
    box-shadow: var(--shadow-medium);
    transform: translateX(5px);
}

.sidebar .nav-link i {
    margin-right: 0.75rem;
    font-size: 1.1rem;
    width: 20px;
    text-align: center;
}

/* Enhanced Main Content */
.main-content {
    background: transparent;
    padding: 2rem;
}

/* Enhanced Menu Categories */
.menu-categories {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: var(--shadow-light);
    margin-bottom: 2rem;
    border: 1px solid var(--border-color);
}

.category-btn {
    background: white;
    border: 2px solid var(--border-color);
    border-radius: 0.75rem;
    padding: 0.75rem 1.5rem;
    margin: 0.25rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 600;
    color: var(--dark-color);
    position: relative;
    overflow: hidden;
}

.category-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: var(--gradient-primary);
    transform: scaleX(0);
    transform-origin: right;
    transition: transform 0.3s ease;
    z-index: -1;
}

.category-btn:hover::before,
.category-btn.active::before {
    transform: scaleX(1);
    transform-origin: left;
}

.category-btn:hover,
.category-btn.active {
    color: white;
    border-color: var(--primary-color);
    transform: translateY(-2px);
    box-shadow: var(--shadow-medium);
}

.category-btn i {
    margin-right: 0.5rem;
    font-size: 1rem;
}

.category-count {
    background: rgba(255, 255, 255, 0.2);
    color: inherit;
    padding: 0.25rem 0.5rem;
    border-radius: 1rem;
    font-size: 0.8rem;
    margin-left: 0.5rem;
}

/* Enhanced Menu Items Grid */
.menu-items-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
    padding: 1rem 0;
}

.menu-item-card {
    background: white;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: var(--shadow-light);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid var(--border-color);
    position: relative;
}

.menu-item-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-heavy);
}

.menu-item-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--gradient-primary);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.menu-item-card:hover::before {
    transform: scaleX(1);
}

.menu-item-image {
    height: 200px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.menu-item-image::before {
    content: attr(data-name);
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 1.5rem;
    font-weight: 700;
    color: rgba(139, 69, 67, 0.3);
    text-align: center;
    width: 100%;
    padding: 1rem;
}

.menu-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.menu-item-card:hover .menu-item-image img {
    transform: scale(1.05);
}

.menu-item-content {
    padding: 1.5rem;
}

.menu-item-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--dark-color);
    margin-bottom: 0.5rem;
    line-height: 1.3;
}

.menu-item-description {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 1rem;
    line-height: 1.4;
}

.menu-item-price {
    font-size: 1.3rem;
    font-weight: 800;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

/* Enhanced Quantity Controls */
.quantity-controls {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--light-color);
    border-radius: 0.75rem;
    padding: 0.5rem;
    border: 2px solid var(--border-color);
    transition: all 0.3s ease;
}

.quantity-controls:focus-within {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
}

.quantity-btn {
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 0.5rem;
    background: var(--gradient-primary);
    color: white;
    font-weight: 700;
    font-size: 1.1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.quantity-btn:hover {
    transform: scale(1.1);
    box-shadow: var(--shadow-medium);
}

.quantity-btn:active {
    transform: scale(0.95);
}

.quantity-display {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--dark-color);
    min-width: 40px;
    text-align: center;
    padding: 0.5rem;
}

/* Enhanced Order Section */
.order-section {
    background: white;
    border-radius: 1rem;
    box-shadow: var(--shadow-medium);
    border: 1px solid var(--border-color);
    overflow: hidden;
}

.order-header {
    background: var(--gradient-primary);
    color: white;
    padding: 1.5rem;
    text-align: center;
}

.order-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.order-info {
    font-size: 0.9rem;
    opacity: 0.9;
}

.order-info p {
    margin-bottom: 0.25rem;
}

/* Enhanced Service Types */
.service-types {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
    padding: 1.5rem;
    background: var(--light-color);
}

.service-type {
    background: white;
    border: 2px solid var(--border-color);
    border-radius: 0.75rem;
    padding: 1rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.service-type::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: var(--gradient-primary);
    transform: scaleX(0);
    transform-origin: right;
    transition: transform 0.3s ease;
    z-index: -1;
}

.service-type:hover::before,
.service-type.active::before {
    transform: scaleX(1);
    transform-origin: left;
}

.service-type:hover,
.service-type.active {
    color: white;
    border-color: var(--primary-color);
    transform: translateY(-2px);
    box-shadow: var(--shadow-medium);
}

.service-icon {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    transition: all 0.3s ease;
}

.service-type:hover .service-icon,
.service-type.active .service-icon {
    transform: scale(1.1);
}

.service-type span {
    font-weight: 600;
    font-size: 0.9rem;
}

/* Enhanced Order Items */
.order-items-header {
    background: var(--light-color);
    border-bottom: 2px solid var(--border-color);
}

.order-items-header .row {
    font-weight: 600;
    color: var(--dark-color);
    padding: 1rem;
}

.order-items {
    max-height: 300px;
    overflow-y: auto;
    padding: 1rem;
}

.order-item {
    background: white;
    border: 1px solid var(--border-color);
    border-radius: 0.75rem;
    padding: 1rem;
    margin-bottom: 0.75rem;
    transition: all 0.3s ease;
}

.order-item:hover {
    box-shadow: var(--shadow-light);
    transform: translateX(5px);
}

/* Enhanced Payment Summary */
.payment-summary {
    background: var(--light-color);
    padding: 1.5rem;
    border-top: 2px solid var(--border-color);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--border-color);
    font-weight: 500;
}

.summary-row:last-child {
    border-bottom: none;
}

.summary-row.total {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--primary-color);
    border-top: 2px solid var(--primary-color);
    margin-top: 0.5rem;
    padding-top: 1rem;
}

/* Enhanced Discount Section */
.discount-section {
    background: var(--light-color);
    padding: 1.5rem;
    border-top: 1px solid var(--border-color);
}

.discount-options {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
}

.discount-option {
    background: white;
    border: 2px solid var(--border-color);
    border-radius: 0.75rem;
    padding: 1rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.discount-option::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: var(--gradient-success);
    transform: scaleX(0);
    transform-origin: right;
    transition: transform 0.3s ease;
    z-index: -1;
}

.discount-option:hover::before,
.discount-option.active::before {
    transform: scaleX(1);
    transform-origin: left;
}

.discount-option:hover,
.discount-option.active {
    color: white;
    border-color: var(--success-color);
    transform: translateY(-2px);
    box-shadow: var(--shadow-medium);
}

.discount-option i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    display: block;
}

.discount-option div {
    font-weight: 600;
    font-size: 0.9rem;
}

/* Enhanced Payment Methods */
.payment-methods {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
    padding: 1.5rem;
    background: var(--light-color);
    border-top: 1px solid var(--border-color);
}

.payment-method {
    background: white;
    border: 2px solid var(--border-color);
    border-radius: 0.75rem;
    padding: 1rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.payment-method::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: var(--gradient-primary);
    transform: scaleX(0);
    transform-origin: right;
    transition: transform 0.3s ease;
    z-index: -1;
}

.payment-method:hover::before,
.payment-method.active::before {
    transform: scaleX(1);
    transform-origin: left;
}

.payment-method:hover,
.payment-method.active {
    color: white;
    border-color: var(--primary-color);
    transform: translateY(-2px);
    box-shadow: var(--shadow-medium);
}

.payment-method i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    display: block;
}

.payment-method div {
    font-weight: 600;
    font-size: 0.9rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .menu-items-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1rem;
    }
    
    .service-types,
    .discount-options,
    .payment-methods {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .main-content {
        padding: 1rem;
    }
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.menu-item-card {
    animation: fadeInUp 0.6s ease-out;
}

.order-section {
    animation: slideInRight 0.6s ease-out;
}

/* Scrollbar Styling */
.order-items::-webkit-scrollbar {
    width: 6px;
}

.order-items::-webkit-scrollbar-track {
    background: var(--light-color);
    border-radius: 3px;
}

.order-items::-webkit-scrollbar-thumb {
    background: var(--primary-color);
    border-radius: 3px;
}

.order-items::-webkit-scrollbar-thumb:hover {
    background: var(--primary-dark);
}
</style>

<!-- Add print-specific stylesheet -->
<style media="print">
    @page {
        size: 80mm auto;
        margin: 0;
        padding: 0;
    }
    
    body * {
        visibility: hidden;
    }
    
    #print-area, #print-area * {
        visibility: visible;
    }
    
    #print-area {
        position: absolute;
        left: 50%;
        top: 0;
        transform: translateX(-50%);
        width: 72mm;
        margin: 0;
        padding: 0;
        background: white !important;
        font-family: 'Arial', sans-serif;
        font-size: 9pt;
        line-height: 1.3;
        color: #000;
    }

    .receipt-container {
        width: 100%;
        padding: 3mm;
        margin: 0 auto;
        background: white !important;
        box-shadow: none !important;
    }

    /* Enhanced Header Section */
    .receipt-header {
        text-align: center;
        margin-bottom: 3mm;
        padding-bottom: 2mm;
        border-bottom: 1px dashed #000;
    }

    .receipt-header img.receipt-logo {
        width: 40px;
        height: auto;
        margin-bottom: 2mm;
    }

    .receipt-header h1 {
        font-family: 'Cooper Black', serif;
        font-size: 16pt;
        margin: 1mm 0;
        color: #000;
        text-transform: uppercase;
        letter-spacing: 0.5mm;
    }

    .receipt-header p {
        font-size: 8pt;
        margin: 0.5mm 0;
        line-height: 1.2;
    }

    /* Enhanced Order Details Section */
    .receipt-section {
        margin: 3mm 0;
        padding: 1.5mm 0;
        border-bottom: 1px dashed #000;
    }

    .receipt-section h2 {
        font-size: 9pt;
        font-weight: bold;
        margin: 0 0 1.5mm 0;
        padding-bottom: 1mm;
        border-bottom: 1px solid #000;
        text-transform: uppercase;
        letter-spacing: 0.3mm;
    }

    .order-info {
        width: 100%;
        border-collapse: collapse;
        margin: 1.5mm 0;
    }

    .order-info td {
        padding: 0.8mm 1.5mm;
        font-size: 8pt;
        line-height: 1.2;
        vertical-align: top;
    }

    .order-info td:first-child {
        width: 35%;
        font-weight: bold;
    }

    /* Enhanced Items Section */
    .item-details {
        width: 100%;
        border-collapse: collapse;
        margin: 1.5mm 0;
    }

    .item-details td {
        padding: 1.2mm 1mm;
        vertical-align: top;
        border-bottom: 1px dotted #ccc;
    }

    .item-name {
        font-size: 8pt;
        color: #000;
        padding-right: 2mm;
    }

    .item-name small {
        font-size: 7pt;
        color: #666;
        display: block;
        margin-top: 0.8mm;
    }

    .item-price {
        font-size: 8pt;
        text-align: right;
        font-weight: bold;
        white-space: nowrap;
    }

    /* Enhanced Totals Section */
    .totals-section {
        width: 100%;
        border-collapse: collapse;
        margin: 1.5mm 0;
    }

    .totals-section td {
        padding: 0.8mm;
        font-size: 8pt;
        line-height: 1.2;
    }

    .totals-section td:first-child {
        text-align: right;
        padding-right: 2mm;
    }

    .totals-section td:last-child {
        text-align: right;
        width: 22mm;
        font-weight: bold;
        white-space: nowrap;
    }

    .total-row td {
        font-size: 10pt;
        font-weight: bold;
        padding-top: 1.5mm;
        border-top: 1px solid #000;
    }

    /* Enhanced Footer Section */
    .receipt-footer {
        text-align: center;
        margin-top: 3mm;
        padding-top: 2mm;
        border-top: 1px dashed #000;
    }

    .order-number {
        font-family: monospace;
        font-size: 8pt;
        background: none;
        padding: 1.5mm;
        margin: 1.5mm 0;
        border: 1px dashed #000;
        letter-spacing: 0.2mm;
    }

    .thank-you {
        font-size: 11pt;
        font-weight: bold;
        margin: 1.5mm 0;
        text-transform: uppercase;
        letter-spacing: 0.3mm;
    }

    .receipt-footer p {
        font-size: 7pt;
        color: #000;
        margin: 0.8mm 0;
        line-height: 1.2;
    }

    .print-date {
        font-size: 6pt;
        color: #666;
        margin-top: 2mm;
        font-style: italic;
    }

    /* Hide Icons in Print */
    .fas, .fab {
        display: none;
    }

    /* Utility Classes */
    .text-right {
        text-align: right;
    }

    .text-center {
        text-align: center;
    }

    .font-bold {
        font-weight: bold;
    }

    /* QR Code Styles */
    .qr-code {
        margin: 2mm auto;
        text-align: center;
    }

    .qr-code img {
        width: 20mm;
        height: 20mm;
    }
</style>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-8">
            <!-- Order Creation Interface -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-shopping-cart me-1"></i>
                    Menu Items
                </div>
                <div class="card-body">
            <!-- Categories Menu -->
            <div class="category-menu">
                <button type="button" class="category-btn active" onclick="load_category_product('all')">
                    <i class="fas fa-utensils"></i>
                    All Menu
                    <small id="all-menu-count">Loading...</small>
                </button>
                        <?php foreach($categorys as $category): ?>
                            <button type="button" class="category-btn" onclick="load_category_product('<?php echo $category['category_id']; ?>')">
                                <i class="fas fa-<?php 
                                    switch(strtolower($category['category_name'])) {
                                        case 'coffee': echo 'coffee'; break;
                                        case 'tea': echo 'mug-hot'; break;
                                        case 'mocktail': echo 'glass-martini-alt'; break;
                                        case 'rice': echo 'bowl-rice'; break;
                                        case 'pasta': echo 'wheat-awn'; break;
                                        case 'burger': echo 'hamburger'; break;
                                        default: echo 'utensils';
                                    }
                                ?>"></i>
                                <?php echo $category['category_name']; ?>
                                <small id="category-count-<?php echo $category['category_id']; ?>">Loading...</small>
                </button>
                        <?php endforeach; ?>
            </div>

            <!-- Menu Section Title -->
            <h5 class="mb-3" id="menu-title">All Menu</h5>
            
            <!-- Branch Information for Cashiers -->
            <div id="branch-info" class="alert alert-info mb-3" style="display: none;">
                <i class="fas fa-store me-2"></i>
                <span id="branch-info-text">Loading branch information...</span>
                <br>
                <small class="text-muted" id="branch-product-count">Loading product count...</small>
            </div>
            
            <div class="row" id="dynamic_item">
                <!-- Items will be loaded here -->
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card order-section">
                <div class="order-header">
                    <h5 class="order-title">Order Details</h5>
                    <div class="order-info">
                        <p class="mb-1">Order #: ORD-<?php echo date('Ymd-His'); ?></p>
                        <p class="mb-1">Date: <?php echo date('Y-m-d H:i:s'); ?></p>
                        <p class="mb-1">Cashier: <?php echo $_SESSION['user_name']; ?></p>
                    </div>
                </div>
                
                <div class="service-types">
                    <div class="service-type" onclick="setServiceType('dine-in')">
                        <div class="service-icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <span>Dine in</span>
                    </div>
                    <div class="service-type" onclick="setServiceType('takeout')">
                        <div class="service-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <span>Takeout</span>
                    </div>
                    <div class="service-type" onclick="setServiceType('delivery')">
                        <div class="service-icon">
                            <i class="fas fa-motorcycle"></i>
                        </div>
                        <span>Delivery</span>
                    </div>
                    <div class="service-type" onclick="setServiceType('grab')">
                        <div class="service-icon">
                            <i class="fas fa-motorcycle grab-icon"></i>
                        </div>
                        <span>Grab</span>
                    </div>
                </div>

                <div class="order-items-header">
                    <div class="row mx-0 py-2 bg-light border-bottom">
                        <div class="col-6">Item</div>
                        <div class="col-2 text-center">Qty</div>
                        <div class="col-4 text-end">Total</div>
                    </div>
                </div>

                <div class="order-items" id="order_item_details">
                    <!-- Order items will be loaded here -->
                </div>

                <div class="payment-summary">
                    <div class="summary-row">
                        <span>Items Count</span>
                        <span id="items_count">0</span>
                    </div>
                    <div class="summary-row">
                        <span>Sub Total</span>
                        <span id="order_gross_total">₱0.00</span>
                    </div>
                    <div class="summary-row">
                        <span>Tax (<?php echo floatval($confData['tax_rate']); ?>%)</span>
                        <span id="order_taxes">₱0.00</span>
                    </div>
                    <div class="summary-row discount-row" style="display: none;">
                        <span>Discount (20%)</span>
                        <span id="order_discount">₱0.00</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total Payment</span>
                        <span id="order_net_total">₱0.00</span>
                    </div>
                </div>

                <!-- Add Discount Selection Section -->
                <div class="discount-section mx-3 mb-3">
                    <div class="discount-options">
                        <div class="discount-option" onclick="setDiscount('none')">
                            <i class="fas fa-ban"></i>
                            <div>No Discount</div>
                        </div>
                        <div class="discount-option" onclick="setDiscount('pwd')">
                            <i class="fas fa-wheelchair"></i>
                            <div>PWD</div>
                        </div>
                        <div class="discount-option" onclick="setDiscount('senior')">
                            <i class="fas fa-user-clock"></i>
                            <div>Senior</div>
                        </div>
                    </div>
                </div>

                <div class="payment-methods">
                    <div class="payment-method" onclick="setPaymentMethod('credit_card')">
                        <i class="fas fa-credit-card"></i>
                        <div>Credit Card</div>
                    </div>
                    <div class="payment-method" onclick="setPaymentMethod('cash')">
                        <i class="fas fa-money-bill-wave"></i>
                        <div>Cash</div>
                    </div>
                    <div class="payment-method" onclick="setPaymentMethod('e_wallet')">
                        <i class="fas fa-qrcode"></i>
                        <div>E-Wallet</div>
                    </div>
                </div>

                <!-- Add Cash Payment Input Section -->
                <div id="cash-payment-section" class="mx-3 mb-3" style="display: none;">
                    <div class="payment-input-group">
                        <label>Amount Due</label>
                        <div class="payment-input">
                            <span class="currency">₱</span>
                            <input type="text" id="amount-due" readonly>
                        </div>
                    </div>
                    <div class="payment-input-group">
                        <label>Cash Amount</label>
                        <div class="payment-input">
                            <span class="currency">₱</span>
                            <input type="number" id="cash-amount" placeholder="0.00" oninput="calculateChange()">
                        </div>
                    </div>
                    <div class="payment-input-group">
                        <label>Change</label>
                        <div class="payment-input">
                            <span class="currency">₱</span>
                            <input type="text" id="change-amount" readonly>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mx-3 mb-3">
                    <button type="button" class="btn-place-order flex-grow-1" id="order_btn" onclick="createOrder()">
                        Take Order
                </button>
                </div>
            </div>
        </div>
    </div>
</div>
    
    <!-- Add Preview Area -->
    <div class="row mt-4" id="preview-area" style="display: none;">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt me-2"></i>Receipt Preview
                    </h5>
                </div>
                <div class="card-body" style="background: #f8f9fa;">
                    <div class="preview-container" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <div id="preview-content" style="max-width: 400px; margin: 0 auto;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="print-area" class="d-none">
    <div class="receipt-container">
        <!-- Print template will be populated by JavaScript -->
    </div>
</div>

<!-- Add Alert Modal -->
<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="alertModalLabel">
                    <i class="fas fa-exclamation-circle me-2"></i>Order Alert
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <i class="fas fa-shopping-cart text-danger" style="font-size: 48px;"></i>
                </div>
                <p class="text-center mb-0" style="font-size: 18px; color: #2c3e50;">
                    Please add items to the cart before placing an order.
                </p>
            </div>
            <div class="modal-footer justify-content-center border-0 pb-4">
                <button type="button" class="btn btn-danger px-4 py-2" data-bs-dismiss="modal">
                    <i class="fas fa-check me-2"></i>OK
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Print Preview Modal -->
<div id="print-preview-modal" class="modal fade" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-receipt me-2"></i>Receipt Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="hidePreview()"></button>
            </div>
            <div class="modal-body">
                <div class="preview-container">
                    <div id="print-preview-content"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" onclick="hidePreview()" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
                <button type="button" class="btn btn-primary" onclick="confirmPrint()">
                    <i class="fas fa-print me-2"></i>Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imagePreviewModalLabel">Product Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImage" src="" alt="Product Image" class="img-fluid rounded" style="max-height: 400px;">
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --primary-red: #e74c3c;
    --primary-red-dark: #c0392b;
    --primary-red-light: #ff6b6b;
    --accent-red: #ff7675;
    --light-red: #ffeded;
    --dark-red: #8b0000;
}

/* Enhanced Category Menu */
.category-menu {
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    padding: 15px;
    gap: 12px;
    background: linear-gradient(to right, #ffffff, var(--light-red));
    border-radius: 15px;
    scrollbar-width: thin;
    scrollbar-color: var(--primary-red) #f0f0f0;
}

.category-menu::-webkit-scrollbar {
    height: 6px;
}

.category-menu::-webkit-scrollbar-track {
    background: #f0f0f0;
    border-radius: 10px;
}

.category-menu::-webkit-scrollbar-thumb {
    background: var(--primary-red);
    border-radius: 10px;
}

.category-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 15px 25px;
    border: none;
    border-radius: 12px;
    background: white;
    color: #2c3e50;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 4px rgba(231, 76, 60, 0.1);
    white-space: nowrap;
    min-width: 120px;
    position: relative;
    overflow: hidden;
}

.category-btn:before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, var(--primary-red), var(--primary-red-light));
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 1;
    border-radius: 12px;
}

.category-btn i {
    font-size: 24px;
    margin-bottom: 8px;
    position: relative;
    z-index: 2;
    transition: transform 0.3s ease;
}

.category-btn span {
    position: relative;
    z-index: 2;
}

.category-btn small {
    font-size: 11px;
    color: #666;
    margin-top: 4px;
    position: relative;
    z-index: 2;
}

.category-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(231, 76, 60, 0.2);
}

.category-btn:hover:before {
    opacity: 1;
}

.category-btn:hover i,
.category-btn:hover span,
.category-btn:hover small {
    color: white;
}

.category-btn.active {
    background: linear-gradient(45deg, var(--primary-red), var(--primary-red-light));
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
}

.category-btn.active i,
.category-btn.active span,
.category-btn.active small {
    color: white;
}

/* Enhanced Item Cards */
.item-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 6px rgba(231, 76, 60, 0.1);
    position: relative;
    cursor: pointer;
    border: 1px solid var(--light-red);
}

.item-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(231, 76, 60, 0.15);
    border-color: var(--primary-red-light);
}

.item-image-container {
    position: relative;
    padding-top: 75%;
    overflow: hidden;
}

.item-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.item-card:hover .item-image {
    transform: scale(1.1);
}

.item-info {
    padding: 20px;
    position: relative;
}

.item-name {
    font-size: 16px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 8px;
    line-height: 1.4;
}

.item-description {
    font-size: 13px;
    color: #666;
    margin-bottom: 15px;
    line-height: 1.6;
}

.item-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 15px;
    border-top: 1px dashed rgba(0, 0, 0, 0.1);
}

.price-section {
    display: flex;
    align-items: baseline;
}

.currency {
    font-size: 14px;
    font-weight: 600;
    color: #2c3e50;
    margin-right: 2px;
}

.item-price {
    font-size: 18px;
    font-weight: 700;
    color: var(--primary-red);
}

/* Enhanced Quantity Controls */
.quantity-controls {
    display: flex;
    align-items: center;
    background: #f8f9fa;
    border-radius: 25px;
    padding: 4px;
    gap: 8px;
}

.quantity-btn {
    width: 28px;
    height: 28px;
    border: none;
    border-radius: 50%;
    background: white;
    color: var(--primary-red);
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 4px rgba(231, 76, 60, 0.1);
}

.quantity-btn:hover {
    background: var(--primary-red);
    color: white;
    transform: scale(1.1);
}

.quantity-value {
    font-size: 14px;
    font-weight: 600;
    color: #2c3e50;
    min-width: 20px;
    text-align: center;
}

/* Enhanced Order Section */
.order-section {
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(231, 76, 60, 0.15);
    background: white;
    position: sticky;
    top: 20px;
    border: 1px solid var(--light-red);
}

.order-header {
    background: linear-gradient(45deg, var(--primary-red), var(--primary-red-light));
    padding: 20px;
    color: white;
}

.order-title {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 15px;
    letter-spacing: 0.5px;
}

.order-info {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 15px;
    margin-top: 15px;
}

.order-info p {
    color: rgba(255, 255, 255, 0.9);
    margin: 5px 0;
    font-size: 14px;
}

/* Enhanced Service Types */
.service-types {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 12px;
    margin-bottom: 20px;
}

.service-type {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 16px 8px;
    background: white;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    overflow: hidden;
}

.service-type::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(139, 69, 67, 0.1), rgba(139, 69, 67, 0.05));
    opacity: 0;
    transition: opacity 0.3s ease;
}

.service-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 50%;
    margin-bottom: 8px;
    transition: all 0.3s ease;
}

.service-type i {
    font-size: 20px;
    color: #8B4543;
    transition: all 0.3s ease;
}

.service-type span {
    font-size: 14px;
    font-weight: 600;
    color: #2c3e50;
    transition: all 0.3s ease;
    text-align: center;
}

.service-type:hover {
    transform: translateY(-2px);
    border-color: rgba(139, 69, 67, 0.3);
    box-shadow: 0 4px 12px rgba(139, 69, 67, 0.1);
}

.service-type:hover::before {
    opacity: 1;
}

.service-type:hover .service-icon {
    background: #8B4543;
}

.service-type:hover i {
    color: white;
    transform: scale(1.1);
}

.service-type.active {
    background: #8B4543;
    border-color: #8B4543;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(139, 69, 67, 0.2);
}

.service-type.active .service-icon {
    background: rgba(255, 255, 255, 0.2);
}

.service-type.active i {
    color: white;
}

.service-type.active span {
    color: white;
}

.grab-icon {
    color: #00B14F !important;
}

.service-type:hover .grab-icon,
.service-type.active .grab-icon {
    color: white !important;
}

@media (max-width: 768px) {
    .service-types {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        padding: 12px;
    }
    
    .service-icon {
        width: 40px;
        height: 40px;
    }
    
    .service-type i {
        font-size: 18px;
    }
    
    .service-type span {
        font-size: 13px;
    }
}

/* Enhanced Payment Summary */
.payment-summary {
    padding: 20px;
    background: linear-gradient(to bottom, #ffffff, #f8f9fa);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    font-size: 14px;
    color: #2c3e50;
}

.summary-row.total {
    margin-top: 10px;
    padding-top: 15px;
    border-top: 2px dashed var(--light-red);
    font-size: 18px;
    font-weight: 700;
    color: var(--primary-red);
}

/* Enhanced Payment Methods */
.payment-methods {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    padding: 20px;
    margin: 10px 15px 15px 15px;
    background: linear-gradient(to bottom, #ffffff, #f8f9fa);
    border-radius: 15px;
    border: 2px solid #e1e7ef;
}

.payment-method {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px 15px;
    background: white;
    border: 2px solid var(--light-red);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.payment-method:before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(to right, var(--primary-red), var(--primary-red-light));
    opacity: 0;
    transition: opacity 0.3s ease;
}

.payment-method:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(231, 76, 60, 0.1);
    border-color: var(--primary-red);
}

.payment-method:hover:before {
    opacity: 1;
}

.payment-method.active {
    background: var(--primary-red);
    color: white;
    border-color: var(--primary-red);
    box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
}

.payment-method.active:before {
    opacity: 1;
    background: linear-gradient(to right, #ffffff33, #ffffff66);
}

.payment-method i {
    font-size: 28px;
    margin-bottom: 10px;
    transition: transform 0.3s ease;
}

.payment-method:hover i {
    transform: scale(1.1);
}

.payment-method div {
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 0.5px;
}

/* Enhanced Cash Payment Section */
#cash-payment-section {
    background: linear-gradient(to bottom, #ffffff, #f8f9fa);
    border-radius: 15px;
    padding: 25px;
    margin: 20px 15px 10px 15px;
    border: 2px solid #e1e7ef;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}

.payment-input-group {
    margin-bottom: 15px;
    position: relative;
}

.payment-input-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.payment-input {
    display: flex;
    align-items: center;
    background: white;
    border: 2px solid var(--light-red);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

.payment-input:focus-within {
    border-color: var(--primary-red);
    box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.15);
}

.payment-input .currency {
    padding: 15px 18px;
    background: #f8f9fa;
    color: #2c3e50;
    font-weight: 600;
    font-size: 16px;
    border-right: 2px solid #e1e7ef;
}

.payment-input input {
    flex: 1;
    border: none;
    padding: 15px 18px;
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
    width: 100%;
    outline: none;
    background: white;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes slideIn {
    from { transform: translateX(-20px); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

.item-card {
    animation: fadeIn 0.5s ease forwards;
}

.order-item {
    animation: slideIn 0.3s ease forwards;
}

/* Loading Animation */
.loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid var(--light-red);
    border-top: 4px solid var(--primary-red);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .category-menu {
        padding: 10px;
    }
    
    .category-btn {
        padding: 12px 20px;
        min-width: 100px;
    }
    
    .service-types {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .order-section {
        margin-top: 20px;
        position: static;
    }
    
    .payment-methods {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .item-card {
        margin-bottom: 15px;
    }
}

@media (max-width: 576px) {
    .category-btn {
        min-width: 90px;
        padding: 10px 15px;
    }
    
    .category-btn i {
        font-size: 20px;
    }
    
    .service-types {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .payment-methods {
        grid-template-columns: 1fr;
    }
    
    .payment-input .currency {
        padding: 12px 15px;
    }
    
    .payment-input input {
        padding: 12px 15px;
        font-size: 16px;
    }
}

/* Print Preview Modal Styles */
#print-preview-modal .modal-content {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

#print-preview-modal .modal-header {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: white;
    border-radius: 15px 15px 0 0;
    padding: 1.5rem;
    border-bottom: none;
}

#print-preview-modal .modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    display: flex;
    align-items: center;
}

#print-preview-modal .modal-title i {
    font-size: 1.4rem;
}

#print-preview-modal .btn-close {
    background-color: rgba(255, 255, 255, 0.8);
    transition: background-color 0.2s;
}

#print-preview-modal .btn-close:hover {
    background-color: white;
}

#print-preview-modal .modal-body {
    background: #f8f9fa;
    padding: 2rem;
}

#print-preview-modal .preview-container {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    max-height: 70vh;
    overflow-y: auto;
}

#print-preview-modal .preview-container::-webkit-scrollbar {
    width: 8px;
}

#print-preview-modal .preview-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

#print-preview-modal .preview-container::-webkit-scrollbar-thumb {
    background: #c0392b;
    border-radius: 4px;
}

#print-preview-modal .preview-container::-webkit-scrollbar-thumb:hover {
    background: #e74c3c;
}

#print-preview-modal #print-preview-content {
    max-width: 80mm;
    margin: 0 auto;
    font-family: 'Arial', sans-serif;
}

#print-preview-modal .modal-footer {
    background: white;
    border-top: 1px solid #dee2e6;
    border-radius: 0 0 15px 15px;
    padding: 1.25rem;
}

#print-preview-modal .btn {
    padding: 0.6rem 1.2rem;
    font-weight: 500;
    border-radius: 8px;
    transition: all 0.2s;
}

#print-preview-modal .btn:hover {
    transform: translateY(-1px);
}

#print-preview-modal .btn-outline-secondary {
    color: #6c757d;
    border-color: #6c757d;
}

#print-preview-modal .btn-outline-secondary:hover {
    color: white;
    background-color: #6c757d;
}

#print-preview-modal .btn-primary {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    border: none;
}

#print-preview-modal .btn-primary:hover {
    background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
}

/* Receipt Content Styles */
.receipt-container {
    color: #2c3e50;
}

.receipt-header {
    text-align: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px dashed #e74c3c;
}

.receipt-header img.receipt-logo {
    width: 60px;
    height: auto;
    margin-bottom: 10px;
}

.receipt-header h1 {
    font-family: 'Cooper Black', serif;
    font-size: 24px;
    color: #e74c3c;
    margin: 5px 0;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.receipt-section {
    margin: 15px 0;
    padding: 10px 0;
    border-bottom: 1px dashed #bdc3c7;
}

.receipt-section h2 {
    font-size: 14px;
    font-weight: bold;
    color: #e74c3c;
    margin-bottom: 10px;
    text-transform: uppercase;
}

.order-info, .item-details, .totals-section {
    width: 100%;
    border-collapse: collapse;
}

.order-info td, .item-details td, .totals-section td {
    padding: 5px 0;
}

.item-name {
    color: #2c3e50;
    font-size: 12px;
}

.item-price {
    color: #e74c3c;
    font-weight: bold;
    text-align: right;
}

.total-row td {
    color: #e74c3c;
    font-weight: bold;
    font-size: 14px;
    padding-top: 10px;
    border-top: 2px solid #e74c3c;
}

.receipt-footer {
    text-align: center;
    margin-top: 20px;
    padding-top: 15px;
    border-top: 2px dashed #e74c3c;
}

.order-number {
    font-family: monospace;
    font-size: 12px;
    background: #f8f9fa;
    padding: 8px;
    margin: 10px 0;
    border: 1px dashed #e74c3c;
    color: #e74c3c;
}

.thank-you {
    font-size: 16px;
    font-weight: bold;
    color: #e74c3c;
    margin: 10px 0;
    text-transform: uppercase;
}

.social-media, .proof-text {
    font-size: 10px;
    color: #7f8c8d;
    margin: 5px 0;
    line-height: 1.4;
}

.print-date {
    font-size: 8px;
    color: #95a5a6;
    margin-top: 10px;
    font-style: italic;
}

/* Add these styles to your existing CSS */
.discount-section {
    margin-top: 15px;
}

.discount-options {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    padding: 15px;
    background: linear-gradient(to bottom, #ffffff, #f8f9fa);
    border-radius: 15px;
    border: 2px solid #e1e7ef;
}

.discount-option {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 15px 10px;
    background: white;
    border: 2px solid var(--light-red);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.discount-option:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(231, 76, 60, 0.1);
    border-color: var(--primary-red);
}

.discount-option.active {
    background: var(--primary-red);
    color: white;
    border-color: var(--primary-red);
}

.discount-option i {
    font-size: 24px;
    margin-bottom: 8px;
}

.discount-option div {
    font-size: 14px;
    font-weight: 600;
}

.discount-row {
    color: #27ae60;
    font-weight: 600;
}

/* Add these styles to your existing CSS */
.payment-input input.negative {
    color: #e74c3c !important;
}

.payment-input input.positive {
    color: #2ecc71 !important;
}

/* Add styles for clickable images */
.order-item-img {
    cursor: pointer;
    transition: transform 0.2s ease;
}

.order-item-img:hover {
    transform: scale(1.1);
}

/* Add styles for the image preview modal */
#imagePreviewModal .modal-content {
    background: rgba(255, 255, 255, 0.95);
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

#imagePreviewModal .modal-header {
    border-bottom: none;
    padding: 20px;
}

#imagePreviewModal .modal-body {
    padding: 0 20px 20px 20px;
}

#imagePreviewModal #previewImage {
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}
</style>

<script>

// Load category counts when page loads
loadCategoryCounts();
loadBranchInfo();
load_category_product();

function loadCategoryCounts() {
    // Load counts for all categories including "All Menu"
    const categories = ['all', ...<?php echo json_encode(array_column($categorys, 'category_id')); ?>];
    
    categories.forEach(categoryId => {
        loadCategoryCount(categoryId);
    });
}

function loadCategoryCount(categoryId) {
    fetch('order_ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            category_id: categoryId,
            action: 'get_products'
        })
    })
    .then(response => response.json())
    .then(data => {
        const count = Array.isArray(data) ? data.length : 0;
        const countElement = categoryId === 'all' 
            ? document.getElementById('all-menu-count') 
            : document.getElementById(`category-count-${categoryId}`);
        
        if (countElement) {
            countElement.textContent = `${count} items`;
        }
    })
    .catch(error => {
        console.error('Error loading category count:', error);
        const countElement = categoryId === 'all' 
            ? document.getElementById('all-menu-count') 
            : document.getElementById(`category-count-${categoryId}`);
        
        if (countElement) {
            countElement.textContent = '0 items';
        }
    });
}

function loadBranchInfo() {
    fetch('order_ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'get_branch_info'
        })
    })
    .then(response => response.json())
    .then(data => {
        const branchInfo = document.getElementById('branch-info');
        const branchInfoText = document.getElementById('branch-info-text');
        
        if (data.branch_name) {
            branchInfoText.textContent = `You're viewing products from: ${data.branch_name} (${data.branch_code})`;
            
            // Load total product count for this branch
            loadTotalBranchProductCount(data.branch_name);
            
            branchInfo.style.display = 'block';
        } else {
            branchInfo.style.display = 'none';
        }
    })
    .catch(error => {
        console.error('Error loading branch info:', error);
        document.getElementById('branch-info').style.display = 'none';
    });
}

function loadTotalBranchProductCount(branchName) {
    fetch('order_ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'get_products',
            category_id: 'all'
        })
    })
    .then(response => response.json())
    .then(data => {
        const count = Array.isArray(data) ? data.length : 0;
        const countElement = document.getElementById('branch-product-count');
        if (countElement) {
            countElement.textContent = `${count} products available in ${branchName}`;
        }
    })
    .catch(error => {
        console.error('Error loading total product count:', error);
        const countElement = document.getElementById('branch-product-count');
        if (countElement) {
            countElement.textContent = 'Unable to load product count';
        }
    });
}

function load_category_product(category_id = 'all')
{
    // Update menu title based on selected category
    const menuTitle = document.getElementById('menu-title');
    if (category_id === 'all') {
        menuTitle.textContent = 'All Menu';
    } else {
        // Find the category button with this ID and get its text
        const categoryBtn = document.querySelector(`.category-btn[onclick*="${category_id}"]`);
        menuTitle.textContent = categoryBtn ? categoryBtn.textContent.split('\n')[0].trim() + ' Menu' : 'Menu Items';
    }

    // Show loading state
    document.getElementById('dynamic_item').innerHTML = '<div class="col-12 text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

    fetch('order_ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            category_id: category_id,
            action: 'get_products'
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        let html = '';
        if(data && data.length > 0) {
            data.forEach(product => {
                // Handle image path properly
                let imageUrl = product.product_image || 'asset/images/default-food.jpg';
                const description = product.product_description || 'Delicious food with special sauce';
                const itemId = product.product_id;
                const itemName = product.product_name.replace(/'/g, "\\'");
                const itemPrice = parseFloat(product.product_price);

                html += `
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="item-card">
                        <div class="item-image-container">
                            <img src="${imageUrl}" 
                                 alt="${itemName}" 
                                 class="item-image"
                                 onerror="this.onerror=null; this.src='asset/images/default-food.jpg';">
                        </div>
                        <div class="item-info">
                            <h3 class="item-name">${itemName}</h3>
                            <p class="item-description">${description}</p>
                            <div class="item-footer">
                                <div class="price-section">
                                    <span class="currency">₱</span>
                                    <span class="item-price">${itemPrice.toFixed(2)}</span>
                                </div>
                                <div class="quantity-controls">
                                    <button class="quantity-btn" onclick="event.stopPropagation(); handleQuantityChange('${itemId}', '${itemName}', -1, ${itemPrice}, '${imageUrl}')">−</button>
                                    <span class="quantity-value" id="quantity_${itemId}">0</span>
                                    <button class="quantity-btn" onclick="event.stopPropagation(); handleQuantityChange('${itemId}', '${itemName}', 1, ${itemPrice}, '${imageUrl}')">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
            });
        } else {
            if (category_id === 'all') {
                html = '<div class="col-12"><div class="alert alert-warning text-center"><i class="fas fa-exclamation-triangle me-2"></i>No products are currently available in your assigned branch. Please contact your administrator.</div></div>';
            } else {
                html = '<div class="col-12"><div class="alert alert-info text-center"><i class="fas fa-info-circle me-2"></i>No items found in this category for your branch</div></div>';
            }
        }
        document.getElementById('dynamic_item').innerHTML = html;
        
        // Update quantities from cart
        cart.forEach(item => {
            const quantityElement = document.getElementById(`quantity_${item.id}`);
            if (quantityElement) {
                quantityElement.textContent = item.quantity;
            }
        });
        
        // Update active category button
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.classList.remove('active');
            if ((category_id === 'all' && btn.textContent.includes('All Menu')) || 
                btn.getAttribute('onclick').includes(`'${category_id}'`)) {
                btn.classList.add('active');
            }
        });
        
        // Update category counts after loading products
        loadCategoryCounts();
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('dynamic_item').innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Error loading products. Please try again later.
                </div>
            </div>`;
    });
}

// Cart Management Variables
let cart = [];
let total = 0;
let cur = "<?php echo $confData['currency']; ?>";
let taxPer = parseFloat("<?php echo $confData['tax_rate']; ?>");

function loadCart() {
    const storedCart = localStorage.getItem('cart');
    if (storedCart) {
        cart = JSON.parse(storedCart);
        updateCart();
    }
}

function saveCart() {
    localStorage.setItem('cart', JSON.stringify(cart));
}

function updateCart() {
    const cartItems = document.getElementById('order_item_details');
    let html = '';
    let taxAmt = 0;
    total = 0;
    let itemCount = 0;
    
    cart.forEach(cartItem => {
        total += cartItem.price * cartItem.quantity;
        itemCount += cartItem.quantity;

        const itemTotal = cartItem.price * cartItem.quantity;
        html += `
        <div class="order-item">
            <div class="d-flex align-items-center flex-grow-1">
                <img src="${cartItem.image}" 
                     class="order-item-img" 
                     alt="${cartItem.name}"
                     onerror="this.onerror=null; this.src='asset/images/default-food.jpg';"
                     onclick="showImagePreview('${cartItem.image}', '${cartItem.name}')">
                <div class="order-item-info">
                    <div class="order-item-name">${cartItem.name}</div>
                    <div class="order-item-price">₱${parseFloat(cartItem.price).toFixed(2)} × ${cartItem.quantity}</div>
                </div>
                <div class="text-end" style="min-width: 80px;">
                    ₱${parseFloat(itemTotal).toFixed(2)}
            </div>
            </div>
            <div class="quantity-controls ms-2">
                <button class="quantity-btn" onclick="changeQuantity('${cartItem.id}', ${cartItem.quantity - 1})">−</button>
                <div class="quantity-value">${cartItem.quantity}</div>
                <button class="quantity-btn" onclick="changeQuantity('${cartItem.id}', ${cartItem.quantity + 1})">+</button>
            </div>
        </div>`;
    });

    taxAmt = parseFloat(total) * taxPer / 100;
    cartItems.innerHTML = html || '<div class="text-center py-4 text-muted">No items in cart</div>';

    document.getElementById('items_count').innerText = itemCount;
    document.getElementById('order_gross_total').innerText = '₱' + parseFloat(total).toFixed(2);
    document.getElementById('order_taxes').innerText = '₱' + parseFloat(taxAmt).toFixed(2);
    total = parseFloat(total) + parseFloat(taxAmt);
    document.getElementById('order_net_total').innerText = '₱' + parseFloat(total).toFixed(2);

    const cashSection = document.getElementById('cash-payment-section');
    if (cashSection.style.display === 'block') {
        document.getElementById('amount-due').value = parseFloat(total).toFixed(2);
        calculateChange();
    }
}

function changeQuantity(itemId, newQuantity) {
    const item = cart.find(cartItem => cartItem.id === itemId);
    
    if (item) {
        if (newQuantity < 1) {
            removeFromCart(itemId);
        } else {
            item.quantity = newQuantity;
            saveCart();
            updateCart();
        }
    }
}

function removeFromCart(itemId) {
    cart = cart.filter(cartItem => cartItem.id !== itemId);
    saveCart();
    updateCart();
}

// Load the cart from localStorage when the page is loaded
window.onload = loadCart;

function resetOrder(){
    load_category_product();
    cart = []; // Empty the cart array
    localStorage.removeItem('cart'); // Remove the cart from localStorage
    updateCart(); // Refresh the cart display
    document.getElementById('preview-area').style.display = 'none'; // Hide the preview area
}

// Update the setPaymentMethod function
function setPaymentMethod(method) {
    // Update active state of payment method buttons
    document.querySelectorAll('.payment-method').forEach(btn => {
        btn.classList.remove('active');
        if (btn.textContent.trim().toLowerCase() === method.replace('_', ' ')) {
            btn.classList.add('active');
        }
    });
    
    // Show/hide cash payment section and handle input states
    const cashSection = document.getElementById('cash-payment-section');
    const orderBtn = document.getElementById('order_btn');
    
    if (method === 'cash') {
        cashSection.style.display = 'block';
        const amountDue = document.getElementById('order_net_total').innerText.replace(/[^\d.]/g, '');
        document.getElementById('amount-due').value = parseFloat(amountDue).toFixed(2);
        document.getElementById('cash-amount').value = '';
        document.getElementById('cash-amount').classList.remove('insufficient', 'sufficient');
        document.getElementById('change-amount').value = '0.00';
        setTimeout(() => document.getElementById('cash-amount').focus(), 100);
        orderBtn.disabled = true;
        } else {
        cashSection.style.display = 'none';
        orderBtn.disabled = false;
    }
    
    // Store selected payment method
    localStorage.setItem('selectedPaymentMethod', method);
}

// Update the calculateChange function
function calculateChange() {
    const amountDue = parseFloat(document.getElementById('amount-due').value) || 0;
    const cashInput = document.getElementById('cash-amount');
    const cashAmount = parseFloat(cashInput.value) || 0;
    const change = cashAmount - amountDue;
    
    const changeInput = document.getElementById('change-amount');
    changeInput.value = Math.abs(change).toFixed(2);
    
    // Add color based on comparison with amount due
    if (cashAmount < amountDue) {
        cashInput.style.color = '#e74c3c'; // Red color for insufficient amount
    } else if (cashAmount >= amountDue) {
        cashInput.style.color = '#2ecc71'; // Green color for sufficient amount
    } else {
        cashInput.style.color = ''; // Default color for empty or zero
    }
    
    // Enable/disable the order button based on cash amount
    const orderBtn = document.getElementById('order_btn');
    orderBtn.disabled = cashAmount < amountDue;
}

async function createOrder() {
    try {
        // Get values from local storage or set defaults
        const paymentMethod = localStorage.getItem('selectedPaymentMethod') || 'cash';
        const serviceType = localStorage.getItem('selectedServiceType') || 'dine-in';
        const discountType = localStorage.getItem('selectedDiscount') || 'none';
        
        // Get values from DOM elements
        const subtotal = parseFloat(document.getElementById('order_gross_total').innerText.replace(/[^\d.]/g, '')) || 0;
        const taxAmount = parseFloat(document.getElementById('order_taxes').innerText.replace(/[^\d.]/g, '')) || 0;
        const discountAmount = parseFloat(document.getElementById('order_discount').innerText.replace(/[^\d.]/g, '')) || 0;
        const orderTotal = parseFloat(document.getElementById('order_net_total').innerText.replace(/[^\d.]/g, '')) || 0;

        // Validate cash amount if payment method is cash
        if (paymentMethod === 'cash') {
            const cashAmount = parseFloat(document.getElementById('cash-amount').value) || 0;
            if (cashAmount < orderTotal) {
                showModal('Error', 'Cash amount is insufficient');
                return;
            }
        }

        // Generate order number using current date and time
        const now = new Date();
        const orderNumber = `ORD${now.getFullYear()}${String(now.getMonth() + 1).padStart(2, '0')}${String(now.getDate()).padStart(2, '0')}${String(now.getHours()).padStart(2, '0')}${String(now.getMinutes()).padStart(2, '0')}${String(now.getSeconds()).padStart(2, '0')}`;

        // Get cart items
        const cartItems = JSON.parse(localStorage.getItem('cart')) || [];
        if (cartItems.length === 0) {
            showModal('Error', 'Cart is empty');
            return;
        }

        // Prepare order data
        const orderData = {
            order_number: orderNumber,
            items: cartItems.map(item => ({
                product_id: item.id,
                product_qty: item.quantity,
                product_price: item.price
            })),
            subtotal: subtotal,
            tax_amount: taxAmount,
            discount_amount: discountAmount,
            discount_type: discountType,
            order_total: orderTotal,
            payment_method: paymentMethod,
            service_type: serviceType
        };

        // Send order to server
        const response = await fetch('order_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(orderData)
        });

        const result = await response.json();

        if (result.success) {
            // Clear cart and related storage
            localStorage.removeItem('cart');
            localStorage.removeItem('selectedDiscount');
            localStorage.removeItem('selectedPaymentMethod');
            localStorage.removeItem('selectedServiceType');
            
            // Show success message
            showModal('Success', 'Order created successfully!', () => {
                window.location.href = `print_order.php?id=${result.order_id}`;
            });
        } else {
            showModal('Error', result.error || 'Failed to create order');
        }
    } catch (error) {
        console.error('Error creating order:', error);
        showModal('Error', 'An unexpected error occurred');
    }
}

// Helper function to show modal
function showModal(title, message, callback = null) {
    const modalTitle = document.querySelector('#alertModal .modal-title');
    const modalBody = document.querySelector('#alertModal .modal-body');
    const modal = new bootstrap.Modal(document.getElementById('alertModal'));
    
    modalTitle.textContent = title;
    modalBody.textContent = message;
    
    if (callback) {
        modal._element.addEventListener('hidden.bs.modal', callback, { once: true });
    }
    
    modal.show();
}

// Initialize when page loads
window.addEventListener('load', function() {
    loadCart();
    const savedPaymentMethod = localStorage.getItem('selectedPaymentMethod') || 'credit_card';
    const savedType = localStorage.getItem('selectedServiceType') || 'dine-in';
    const savedDiscount = localStorage.getItem('selectedDiscount') || 'none';
    setPaymentMethod(savedPaymentMethod);
    setServiceType(savedType);
    setDiscount(savedDiscount);
});

// Update the handleQuantityChange function
function handleQuantityChange(itemId, itemName, change, itemPrice, itemImage) {
    const item = cart.find(cartItem => cartItem.id === itemId);
    const newQuantity = item ? item.quantity + change : (change > 0 ? 1 : 0);
    
    if (newQuantity <= 0) {
        cart = cart.filter(cartItem => cartItem.id !== itemId);
    } else {
        if (item) {
            item.quantity = newQuantity;
        } else {
            cart.push({ 
                id: itemId,
                name: itemName, 
                price: itemPrice, 
                quantity: newQuantity,
                image: itemImage
            });
        }
    }
    
    const quantityElement = document.getElementById(`quantity_${itemId}`);
    if (quantityElement) {
        quantityElement.textContent = newQuantity;
    }
    
    saveCart();
    updateCart();
}

// Add this new JavaScript function for handling service type selection
function setServiceType(type) {
    // Update active state of service type buttons
    document.querySelectorAll('.service-type').forEach(btn => {
        btn.classList.remove('active');
        if (btn.querySelector('span').textContent.toLowerCase() === type.replace('-', ' ')) {
            btn.classList.add('active');
        }
    });
    
    // Store selected service type
    localStorage.setItem('selectedServiceType', type);
}

// Add event listener for when the modal is closed
document.addEventListener('DOMContentLoaded', function() {
    const previewModal = document.getElementById('print-preview-modal');
    previewModal.addEventListener('hidden.bs.modal', function () {
        // Hide the preview area when modal is closed
        document.getElementById('preview-area').style.display = 'none';
    });
});

// Add hidePreview function
function hidePreview() {
    document.getElementById('preview-area').style.display = 'none';
}

// Add these functions to your existing JavaScript
function setDiscount(type) {
    // Update active state of discount options
    document.querySelectorAll('.discount-option').forEach(btn => {
        btn.classList.remove('active');
        if (btn.querySelector('div').textContent.toLowerCase() === type.replace('_', ' ')) {
            btn.classList.add('active');
        }
    });
    
    // Store selected discount type
    localStorage.setItem('selectedDiscount', type);
    
    // Update cart totals
    updateCart();
}

// Add function to show image preview
function showImagePreview(imageUrl, productName) {
    const modal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
    const previewImage = document.getElementById('previewImage');
    const modalTitle = document.getElementById('imagePreviewModalLabel');
    
    previewImage.src = imageUrl;
    previewImage.onerror = function() {
        this.onerror = null;
        this.src = 'asset/images/default-food.jpg';
    };
    modalTitle.textContent = productName;
    
    modal.show();
}

</script>

<?php include('footer.php'); ?>