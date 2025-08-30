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
/* Enhanced Stock Analytics Dashboard Styles */
.stockman-dashboard-bg {
    background: linear-gradient(135deg, #f8f5f5 0%, #f0f0f0 100%);
    min-height: 100vh;
    padding-bottom: 2rem;
}

.stockman-section-title {
    color: #8B4543;
    font-size: 2.5rem;
    font-weight: 800;
    letter-spacing: 0.5px;
    margin-bottom: 2rem;
    margin-top: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    position: relative;
    background: none;
    border: none;
    animation: fadeInDown 0.8s ease-out;
}

.stockman-section-title .section-icon {
    font-size: 2.5rem;
    color: #8B4543;
    opacity: 0.9;
    background: linear-gradient(135deg, #8B4543 0%, #b97a6a 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.stockman-section-title::after {
    content: '';
    display: block;
    position: absolute;
    left: 0;
    bottom: -10px;
    width: 100%;
    height: 6px;
    border-radius: 4px;
    background: linear-gradient(90deg, #8B4543 0%, #b97a6a 50%, #8B4543 100%);
    opacity: 0.25;
    animation: slideInLeft 1s ease-out 0.5s both;
}

/* Enhanced Analytics Overview Cards */
.stockman-overview-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
    animation: fadeInUp 0.8s ease-out 0.3s both;
}

.stockman-overview-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 1.5rem;
    box-shadow: 0 8px 32px rgba(139, 69, 67, 0.1);
    padding: 2rem;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    border-left: 8px solid #8B4543;
    position: relative;
    transition: all 0.3s ease;
    overflow: hidden;
}

.stockman-overview-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(139, 69, 67, 0.05), transparent);
    transition: left 0.6s ease;
}

.stockman-overview-card:hover::before {
    left: 100%;
}

.stockman-overview-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 16px 48px rgba(139, 69, 67, 0.15);
    border-left-color: #b97a6a;
}

.stockman-overview-card .icon {
    font-size: 3rem;
    color: #8B4543;
    opacity: 0.9;
    transition: all 0.3s ease;
}

.stockman-overview-card:hover .icon {
    transform: scale(1.1);
    color: #b97a6a;
}

.stockman-overview-card .card-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.stockman-overview-card .card-title {
    font-size: 1.1rem;
    color: #6c757d;
    font-weight: 600;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stockman-overview-card .card-value {
    font-size: 2.5rem;
    font-weight: 800;
    color: #8B4543;
    margin: 0;
    line-height: 1;
}

.stockman-overview-card .card-trend {
    font-size: 0.9rem;
    font-weight: 600;
    margin: 0;
}

.stockman-overview-card .card-trend.positive {
    color: #28a745;
}

.stockman-overview-card .card-trend.negative {
    color: #dc3545;
}

.stockman-overview-card .card-trend.neutral {
    color: #6c757d;
}

/* Enhanced Analytics Cards */
.stockman-card {
    background: #fff;
    border-radius: 1.5rem;
    box-shadow: 0 8px 32px rgba(139, 69, 67, 0.08);
    margin-bottom: 2rem;
    border: none;
    overflow: hidden;
    transition: all 0.3s ease;
}

.stockman-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(139, 69, 67, 0.12);
}

.stockman-card .card-header {
    background: linear-gradient(135deg, #8B4543 0%, #b97a6a 100%);
    color: #fff;
    border-radius: 0;
    font-weight: 700;
    font-size: 1.2rem;
    padding: 1.5rem 2rem;
    border-bottom: none;
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.stockman-card .card-header i {
    font-size: 1.3rem;
    opacity: 0.9;
}

.stockman-card .card-body {
    padding: 2rem;
}

/* Enhanced Analytics Grid Layout */
.analytics-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.analytics-chart-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.analytics-insights-section {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

/* Recommendation Items Styling */
.recommendation-item {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 8px;
    transition: all 0.3s ease;
}

.recommendation-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(139, 69, 67, 0.1);
}

.recommendation-item .border-danger {
    border-left-color: #dc3545 !important;
}

.recommendation-item .border-warning {
    border-left-color: #ffc107 !important;
}

.recommendation-item .border-info {
    border-left-color: #17a2b8 !important;
}

.recommendation-item .border-success {
    border-left-color: #28a745 !important;
}

/* Enhanced Chart Containers */
.chart-container {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    padding: 1.5rem;
    height: 300px;
    position: relative;
    border: 1px solid rgba(139, 69, 67, 0.1);
}

.chart-container canvas {
    border-radius: 8px;
}

/* Enhanced Analytics Cards with Hover Effects */
.stockman-card {
    background: #fff;
    border-radius: 1.5rem;
    box-shadow: 0 8px 32px rgba(139, 69, 67, 0.08);
    margin-bottom: 2rem;
    border: none;
    overflow: hidden;
    transition: all 0.3s ease;
    position: relative;
}

.stockman-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #8B4543 0%, #b97a6a 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.stockman-card:hover::before {
    opacity: 1;
}

.stockman-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(139, 69, 67, 0.12);
}

/* Section Headers */
.section-header {
    margin-bottom: 2rem;
}

.section-title {
    color: #8B4543;
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-title i {
    color: #8B4543;
    font-size: 1.5rem;
}

/* Category Cards Container */
.category-cards-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

/* Category Card */
.category-card {
    background: #fff;
    border-radius: 1rem;
    box-shadow: 0 4px 20px rgba(139, 69, 67, 0.1);
    padding: 1.5rem;
    border-left: 4px solid #8B4543;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.category-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(139, 69, 67, 0.05), transparent);
    transition: left 0.6s ease;
}

.category-card:hover::before {
    left: 100%;
}

.category-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(139, 69, 67, 0.15);
    border-left-color: #b97a6a;
}

.category-card-header {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    margin-bottom: 1rem;
}

.category-card-header i {
    color: #8B4543;
    font-size: 1.2rem;
}

.category-card-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #495057;
    margin: 0;
}

.category-card-subtitle {
    font-size: 0.9rem;
    color: #6c757d;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.category-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.8rem;
    margin-bottom: 1.5rem;
}

.stat-box {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 8px;
    padding: 0.8rem;
    text-align: center;
    border: 1px solid rgba(139, 69, 67, 0.1);
    min-width: 0; /* Allow shrinking */
    overflow: hidden; /* Prevent overflow */
}

.stat-label {
    font-size: 0.7rem;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 0.3rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.stat-value {
    font-size: 1.1rem;
    font-weight: 800;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-width: 0;
}

.stat-value.available {
    color: #28a745;
}

.stat-value.low-stock {
    color: #ffc107;
}

.stat-value.healthy {
    color: #17a2b8;
}

.stat-value.zero {
    color: #dc3545;
}

.category-card-actions {
    display: flex;
    justify-content: center;
}

.btn-view-items {
    background: linear-gradient(135deg, #8B4543 0%, #b97a6a 100%);
    border: none;
    color: white;
    border-radius: 8px;
    padding: 0.6rem 1.2rem;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 2px 8px rgba(139, 69, 67, 0.2);
}

.btn-view-items:hover {
    background: linear-gradient(135deg, #7a3d3b 0%, #a65d5d 100%);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(139, 69, 67, 0.3);
}

/* Enhanced Chart Containers */
.chart-container {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    padding: 1.5rem;
    height: 300px;
    position: relative;
}

.chart-container canvas {
    border-radius: 8px;
}

/* Analytics Insights Cards */
.insight-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 1rem;
    padding: 1.5rem;
    border-left: 4px solid #8B4543;
    transition: all 0.3s ease;
}

.insight-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(139, 69, 67, 0.1);
}

.insight-card .insight-title {
    font-size: 1rem;
    font-weight: 700;
    color: #8B4543;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.insight-card .insight-value {
    font-size: 1.5rem;
    font-weight: 800;
    color: #495057;
    margin-bottom: 0.5rem;
}

.insight-card .insight-description {
    font-size: 0.9rem;
    color: #6c757d;
    margin: 0;
}

/* Enhanced Tables */
.table {
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0 8px;
}

.table thead th {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: none;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 1px;
    color: #8B4543;
    padding: 1.2rem 1rem;
    white-space: nowrap;
    position: relative;
}

.table thead th::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 60%;
    height: 3px;
    background: linear-gradient(90deg, #8B4543 0%, #b97a6a 100%);
    border-radius: 2px;
    opacity: 0.3;
}

.table tbody tr {
    background: white;
    box-shadow: 0 4px 12px rgba(139, 69, 67, 0.05);
    transition: all 0.3s ease;
    border-radius: 12px;
}

.table tbody tr:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(139, 69, 67, 0.1);
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

.table tbody td {
    padding: 1.2rem 1rem;
    border: none;
    background: transparent;
    vertical-align: middle;
}

.table tbody tr td:first-child {
    border-top-left-radius: 12px;
    border-bottom-left-radius: 12px;
}

.table tbody tr td:last-child {
    border-top-right-radius: 12px;
    border-bottom-right-radius: 12px;
}

/* Enhanced Status Badges */
.badge {
    font-size: 0.75rem;
    padding: 0.6rem 1rem;
    border-radius: 20px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.badge.bg-warning {
    background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%) !important;
    color: #212529;
}

.badge.bg-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
    color: white;
}

.badge.bg-danger {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
    color: white;
}

.badge.bg-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%) !important;
    color: white;
}

.badge.bg-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
    color: white;
}

/* Enhanced Buttons */
.btn-maroon {
    background: linear-gradient(135deg, #8B4543 0%, #b97a6a 100%);
    border: none;
    color: white;
    border-radius: 12px;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 16px rgba(139, 69, 67, 0.2);
}

.btn-maroon:hover {
    background: linear-gradient(135deg, #7a3d3b 0%, #a65d5d 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(139, 69, 67, 0.3);
}

.btn-group .btn {
    border-radius: 8px;
    margin: 0 0.2rem;
    transition: all 0.3s ease;
}

.btn-group .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Loading States */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 1.5rem;
    z-index: 10;
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #8B4543;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

/* Animations */
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

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

@keyframes slideInLeft {
    from {
        width: 0;
        opacity: 0;
    }
    to {
        width: 100%;
        opacity: 0.25;
    }
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 1200px) {
    .stockman-overview-cards {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }
    
    .analytics-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .analytics-chart-section {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
}

@media (max-width: 768px) {
    .stockman-section-title {
        font-size: 2rem;
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
    
    .stockman-overview-cards {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .stockman-overview-card {
        padding: 1.5rem;
    }
    
    .stockman-overview-card .card-value {
        font-size: 2rem;
    }
    
    .stockman-card .card-body {
        padding: 1.5rem;
    }
    
    .table-responsive {
        border-radius: 12px;
        overflow: hidden;
    }
    
    .category-cards-container {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .category-stats {
        gap: 0.5rem;
    }
    
    .stat-box {
        padding: 0.6rem;
    }
    
    .stat-label {
        font-size: 0.65rem;
    }
    
    .stat-value {
        font-size: 1rem;
    }
}

/* DataTables Customization */
.dataTables_wrapper {
    padding: 0;
}

.dataTables_filter input {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 0.5rem 1rem;
    transition: all 0.3s ease;
}

.dataTables_filter input:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
    outline: none;
}

.dataTables_length select {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 0.5rem 2rem 0.5rem 1rem;
    transition: all 0.3s ease;
}

.dataTables_length select:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
    outline: none;
}

.dataTables_paginate .paginate_button {
    border-radius: 8px;
    margin: 0 0.2rem;
    transition: all 0.3s ease;
}

.dataTables_paginate .paginate_button.current {
    background: linear-gradient(135deg, #8B4543 0%, #b97a6a 100%);
    border-color: #8B4543;
}

.dataTables_paginate .paginate_button:hover:not(.current) {
    background: #f8f9fa;
    border-color: #8B4543;
    color: #8B4543 !important;
}

/* Empty State Styling */
.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: #6c757d;
}

.empty-state i {
    font-size: 4rem;
    color: #dee2e6;
    margin-bottom: 1rem;
}

.empty-state h5 {
    color: #8B4543;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #6c757d;
    margin-bottom: 0;
}
</style>

<div class="stockman-dashboard-bg">
    <div class="container-fluid px-4">
        <div class="stockman-section-title">
            <span class="section-icon"><i class="fas fa-chart-line"></i></span>
            Stock Analytics Dashboard
        </div>
        
        <!-- Analytics Overview Cards -->
        <div class="stockman-overview-cards">
            <div class="stockman-overview-card">
                <span class="icon"><i class="fas fa-boxes"></i></span>
                <div class="card-content">
                    <span class="card-title">Total Items</span>
                    <span class="card-value" id="totalItems">0</span>
                    <span class="card-trend positive" id="totalItemsTrend">+5% this week</span>
                </div>
            </div>
            <div class="stockman-overview-card">
                <span class="icon"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="card-content">
                    <span class="card-title">Low Stock Items</span>
                    <span class="card-value" id="lowStockItems">0</span>
                    <span class="card-trend negative" id="lowStockTrend">+2 this week</span>
                </div>
            </div>
            <div class="stockman-overview-card">
                <span class="icon"><i class="fas fa-exchange-alt"></i></span>
                <div class="card-content">
                    <span class="card-title">Stock Movements</span>
                    <span class="card-value" id="stockMovements">0</span>
                    <span class="card-trend positive" id="movementsTrend">+12% this week</span>
                </div>
            </div>
            <div class="stockman-overview-card">
                <span class="icon"><i class="fas fa-clock"></i></span>
                <div class="card-content">
                    <span class="card-title">Expiring Items</span>
                    <span class="card-value" id="expiringItems">0</span>
                    <span class="card-trend neutral" id="expiringTrend">No change</span>
                </div>
            </div>
            <div class="stockman-overview-card">
                <span class="icon"><i class="fas fa-chart-line"></i></span>
                <div class="card-content">
                    <span class="card-title">Stock Turnover</span>
                    <span class="card-value" id="stockTurnover">0%</span>
                    <span class="card-trend positive" id="turnoverTrend">+8% this month</span>
            </div>
        </div>
        
        </div>
        
        <!-- Enhanced Analytics Grid -->
        <div class="analytics-grid">
            <!-- Charts Section -->
            <div class="analytics-chart-section">
                <!-- Stock Status Chart -->
                <div class="stockman-card">
                    <div class="card-header">
                        <i class="fas fa-chart-pie me-1"></i>
                        Stock Status Distribution
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="stockStatusChart" width="100%" height="300"></canvas>
                        </div>
                        </div>
                    </div>
                
                <!-- Stock Movement Trends -->
                <div class="stockman-card">
                    <div class="card-header">
                        <i class="fas fa-chart-line me-1"></i>
                        Stock Movement Trends
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="stockMovementChart" width="100%" height="300"></canvas>
                        </div>
                </div>
            </div>
            
                <!-- Category Performance -->
                <div class="stockman-card">
                    <div class="card-header">
                        <i class="fas fa-chart-bar me-1"></i>
                        Category Performance
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="categoryChart" width="100%" height="300"></canvas>
                    </div>
                    </div>
                </div>
                
                <!-- Stock Turnover Analysis -->
                <div class="stockman-card">
                    <div class="card-header">
                        <i class="fas fa-sync-alt me-1"></i>
                        Stock Turnover Analysis
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="turnoverChart" width="100%" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Analytics Insights Section -->
            <div class="analytics-insights-section">
                <!-- Critical Stock Alerts -->
                <div class="stockman-card">
                    <div class="card-header">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        Critical Stock Alerts
                    </div>
                    <div class="card-body">
                        <div id="criticalAlerts">
                            <!-- Critical alerts will be loaded here -->
                        </div>
                    </div>
                </div>
                
                <!-- Stock Performance Insights -->
                <div class="stockman-card">
                    <div class="card-header">
                        <i class="fas fa-lightbulb me-1"></i>
                        Stock Insights
                    </div>
                    <div class="card-body">
                        <div class="insight-card">
                            <div class="insight-title">
                                <i class="fas fa-trending-up"></i>
                                Fastest Moving Items
                            </div>
                            <div class="insight-value" id="fastestMoving">0</div>
                            <div class="insight-description">Items with highest turnover rate</div>
                        </div>
                        
                        <div class="insight-card">
                            <div class="insight-title">
                                <i class="fas fa-trending-down"></i>
                                Slowest Moving Items
                            </div>
                            <div class="insight-value" id="slowestMoving">0</div>
                            <div class="insight-description">Items with lowest turnover rate</div>
                        </div>
                        
                        <div class="insight-card">
                            <div class="insight-title">
                                <i class="fas fa-calendar-alt"></i>
                                Expiring Soon
                            </div>
                            <div class="insight-value" id="expiringSoon">0</div>
                            <div class="insight-description">Items expiring within 30 days</div>
                        </div>
                    </div>
                </div>
                
                <!-- Reorder Recommendations -->
                <div class="stockman-card">
                    <div class="card-header">
                        <i class="fas fa-shopping-cart me-1"></i>
                        Reorder Recommendations
                    </div>
                    <div class="card-body">
                        <div id="reorderRecommendations">
                            <!-- Reorder recommendations will be loaded here -->
                        </div>
                    </div>
                </div>
                
                <!-- Stock Value Analytics -->
                <div class="stockman-card">
                    <div class="card-header">
                        <i class="fas fa-coins me-1"></i>
                        Stock Value Analytics
                    </div>
                    <div class="card-body">
                        <div class="insight-card">
                            <div class="insight-title">
                                <i class="fas fa-chart-pie"></i>
                                Total Stock Value
                            </div>
                            <div class="insight-value" id="totalStockValue">₱0</div>
                            <div class="insight-description">Current inventory value</div>
                        </div>
                        
                        <div class="insight-card">
                            <div class="insight-title">
                                <i class="fas fa-percentage"></i>
                                Turnover Rate
                            </div>
                            <div class="insight-value" id="turnoverRate">0%</div>
                            <div class="insight-description">Monthly stock turnover</div>
                        </div>
                        
                        <div class="insight-card">
                            <div class="insight-title">
                                <i class="fas fa-clock"></i>
                                Average Age
                            </div>
                            <div class="insight-value" id="averageAge">0 days</div>
                            <div class="insight-description">Average stock age</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Inventory by Category Section -->
        <div class="row mb-4">
            <div class="col-12">
              
                <div class="category-cards-container" id="categoryCardsContainer">
                    <!-- Category cards will be loaded here dynamically -->
                </div>
            </div>
        </div>
        
        <!-- Stock Requests Updates table removed -->
    </div>
</div>

<script>
$(document).ready(function() {
    // DataTable initialization removed

    // Initialize stock status chart
    const ctx = document.getElementById('stockStatusChart').getContext('2d');
    const stockStatusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Adequate', 'Low Stock', 'Out of Stock'],
            datasets: [{
                data: [0, 0, 0],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });

    // Initialize stock movement chart
    const ctx2 = document.getElementById('stockMovementChart').getContext('2d');
    const stockMovementChart = new Chart(ctx2, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Stock Movements',
                data: [0, 0, 0, 0, 0, 0, 0],
                borderColor: '#8B4543',
                backgroundColor: 'rgba(139, 69, 67, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Initialize category performance chart
    const ctx3 = document.getElementById('categoryChart').getContext('2d');
    const categoryChart = new Chart(ctx3, {
        type: 'bar',
        data: {
            labels: ['Category 1', 'Category 2', 'Category 3'],
            datasets: [{
                label: 'Items Count',
                data: [0, 0, 0],
                backgroundColor: [
                    'rgba(139, 69, 67, 0.8)',
                    'rgba(185, 122, 106, 0.8)',
                    'rgba(220, 53, 69, 0.8)'
                ],
                borderColor: [
                    '#8B4543',
                    '#b97a6a',
                    '#dc3545'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Initialize turnover analysis chart
    const ctx4 = document.getElementById('turnoverChart').getContext('2d');
    const turnoverChart = new Chart(ctx4, {
        type: 'doughnut',
        data: {
            labels: ['High Turnover', 'Medium Turnover', 'Low Turnover'],
            datasets: [{
                data: [0, 0, 0],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });

    // Function to update dashboard data
    function updateDashboard() {
        // Update overview cards
        $.get('get_stockman_analytics.php', function(response) {
            $('#totalItems').text(response.total_items);
            $('#lowStockItems').text(response.low_stock_items);
            $('#stockMovements').text(response.stock_movements);
            $('#expiringItems').text(response.expiring_items);
            $('#stockTurnover').text(response.stock_turnover + '%');

            // Update trends
            $('#totalItemsTrend').text(response.total_items_trend);
            $('#lowStockTrend').text(response.low_stock_trend);
            $('#movementsTrend').text(response.movements_trend);
            $('#expiringTrend').text(response.expiring_trend);
            $('#turnoverTrend').text(response.turnover_trend);

            // Update stock status chart
            stockStatusChart.data.datasets[0].data = [
                response.adequate_stock,
                response.low_stock,
                response.out_of_stock
            ];
            stockStatusChart.update();

            // Update stock movement chart
            stockMovementChart.data.datasets[0].data = response.weekly_movements;
            stockMovementChart.update();

            // Update insights
            $('#fastestMoving').text(response.fastest_moving);
            $('#slowestMoving').text(response.slowest_moving);
            $('#expiringSoon').text(response.expiring_soon);

            // Update stock value analytics
            $('#totalStockValue').text(response.total_stock_value);
            $('#turnoverRate').text(response.turnover_rate + '%');
            $('#averageAge').text(response.average_age + ' days');

            // Update charts
            categoryChart.data.labels = response.category_labels;
            categoryChart.data.datasets[0].data = response.category_data;
            categoryChart.update();

            turnoverChart.data.datasets[0].data = [
                response.high_turnover,
                response.medium_turnover,
                response.low_turnover
            ];
            turnoverChart.update();

            // Update critical alerts
            updateCriticalAlerts(response.critical_alerts);

            // Update reorder recommendations
            updateReorderRecommendations(response.reorder_recommendations);

            // Update category cards
            updateCategoryCards(response.category_cards);
        });

        // Requests table update removed
    }

    // Function to update critical alerts
    function updateCriticalAlerts(alerts) {
        const container = $('#criticalAlerts');
        container.empty();

        if (alerts.length === 0) {
            container.append(`
                <div class="text-center text-muted py-3">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <p>No critical alerts at this time</p>
                </div>
            `);
        } else {
            alerts.forEach(alert => {
                container.append(`
                    <div class="alert alert-${alert.severity} d-flex align-items-center mb-2" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <div>
                            <strong>${alert.title}</strong><br>
                            <small>${alert.description}</small>
                        </div>
                    </div>
                `);
            });
        }
    }

    // Function to update reorder recommendations
    function updateReorderRecommendations(recommendations) {
        const container = $('#reorderRecommendations');
        container.empty();

        if (recommendations.length === 0) {
            container.append(`
                <div class="text-center text-muted py-3">
                    <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                    <p>No reorder recommendations at this time</p>
                </div>
            `);
        } else {
            recommendations.forEach(rec => {
                container.append(`
                    <div class="recommendation-item mb-2 p-2 border-start border-${rec.priority} border-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong class="text-${rec.priority}">${rec.item_name}</strong>
                                <br>
                                <small class="text-muted">Current: ${rec.current_stock} | Recommended: ${rec.recommended_quantity}</small>
                            </div>
                            <span class="badge bg-${rec.priority}">${rec.priority.toUpperCase()}</span>
                        </div>
                        <small class="text-muted">${rec.reason}</small>
                    </div>
                `);
            });
        }
    }

    // Function to update category cards
    function updateCategoryCards(categoryCards) {
        const container = $('#categoryCardsContainer');
        container.empty();

        if (categoryCards.length === 0) {
            container.append(`
                <div class="text-center text-muted py-4">
                    <i class="fas fa-tags fa-3x mb-3"></i>
                    <h5>No Categories Found</h5>
                    <p>No inventory categories available at this time.</p>
                </div>
            `);
        } else {
            categoryCards.forEach(category => {
                const availableClass = category.available > 0 ? 'available' : 'zero';
                const lowStockClass = category.low_stock > 0 ? 'low-stock' : 'zero';
                const healthyClass = category.healthy > 0 ? 'healthy' : 'zero';
                
                container.append(`
                    <div class="category-card">
                        <div class="category-card-header">
                            <i class="fas fa-tag"></i>
                            <div>
                                <div class="category-card-title">${category.category_name}</div>
                                <div class="category-card-subtitle">
                                    <i class="fas fa-boxes"></i>
                                    ${category.total_items} items
                                </div>
                            </div>
                        </div>
                        
                        <div class="category-stats">
                            <div class="stat-box">
                                <div class="stat-label">Available</div>
                                <div class="stat-value ${availableClass}">${category.available}</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-label">Low Stock</div>
                                <div class="stat-value ${lowStockClass}">${category.low_stock}</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-label">Healthy</div>
                                <div class="stat-value ${healthyClass}">${category.healthy}</div>
                            </div>
                        </div>
                        
                        <div class="category-card-actions">
                            <button class="btn-view-items" onclick="viewCategoryItems('${category.category_id}')">
                                <i class="fas fa-chevron-down"></i>
                                View Items
                            </button>
                        </div>
                    </div>
                `);
            });
        }
    }

    // Action functions
    // updateDeliveryStatus function removed

    window.viewCategoryItems = function(categoryId) {
        // Show category items modal
        $('#categoryItemsModalBody').html('<div class="text-center p-4"><div class="loading-spinner"></div><p class="mt-2">Loading category items...</p></div>');
        $('#categoryItemsModal').modal('show');
        $.get('get_category_items.php', { category_id: categoryId }, function(data) {
            $('#categoryItemsModalBody').html(data);
        });
    };

    // Initial load
    updateDashboard();

    // Refresh data every 5 minutes
    setInterval(updateDashboard, 300000);
    
    // Delivery modal event handlers and update delivery submission removed
});
</script>

<!-- Category Items Modal -->
<div class="modal fade" id="categoryItemsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-tags me-1"></i>
                    Category Items
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="categoryItemsModalBody">
                <!-- Category items will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Delivery Status Update Modal removed -->

<?php include('footer.php'); ?> 