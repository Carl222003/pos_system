<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in and is either Admin or Stockman
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['user_type'] !== 'Admin' && $_SESSION['user_type'] !== 'Stockman') {
    header('Location: unauthorized.php');
    exit();
}

$confData = getConfigData($pdo);

include('header.php');
?>

<style>
/* Modern Card and Table Styling */
:root {
    --primary-color: #8B4543;
    --primary-dark: #723937;
    --primary-light: #A65D5D;
    --accent-color: #D4A59A;
    --text-light: #F3E9E7;
    --text-dark: #3C2A2A;
    --border-color: #C4B1B1;
    --hover-color: #F5EDED;
    --danger-color: #B33A3A;
    --success-color: #4A7C59;
    --warning-color: #C4804D;
}

.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(139, 69, 67, 0.15);
    border: none;
    border-radius: 0.75rem;
    background: #ffffff;
}

.card-header {
    background: var(--primary-color);
    color: var(--text-light);
    border-bottom: none;
    padding: 1.5rem;
    border-radius: 0.75rem 0.75rem 0 0;
}

.card-header i {
    color: var(--text-light);
}

.card-header h5 {
    color: var(--text-light);
    margin: 0;
}

.table {
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0 8px;
}

.table thead th {
    background-color: var(--hover-color);
    border: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    color: var(--primary-color);
    padding: 1rem;
    white-space: nowrap;
}

.table tbody tr {
    background: white;
    box-shadow: 0 2px 4px rgba(139, 69, 67, 0.05);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.table tbody tr:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(139, 69, 67, 0.1);
    background: var(--hover-color);
}

.table tbody td {
    padding: 1rem;
    border: none;
    background: transparent;
}

.table tbody tr td:first-child {
    border-top-left-radius: 0.5rem;
    border-bottom-left-radius: 0.5rem;
}

.table tbody tr td:last-child {
    border-top-right-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
}

/* Table Section Styles */
.table-section {
    margin-bottom: 2rem;
}

.table-section h5 {
    font-weight: 600;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e9ecef;
}

.table-section h5 i {
    margin-right: 0.5rem;
}

.pending-section h5 {
    color: #007bff;
    border-bottom-color: #007bff;
}

.processed-section h5 {
    color: #6c757d;
    border-bottom-color: #6c757d;
}

/* Action Button Styles */
.action-menu-btn {
    background: #8B4543 !important;
    color: white !important;
    border: none !important;
    border-radius: 0.5rem !important;
    width: 32px !important;
    height: 32px !important;
    padding: 0 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.2s ease !important;
}

.action-menu-btn:hover {
    background: #723937 !important;
    color: white !important;
    transform: scale(1.05) !important;
}

.action-menu-btn:focus {
    background: #8B4543 !important;
    color: white !important;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25) !important;
}

.dropdown-menu {
    border: none !important;
    border-radius: 0.5rem !important;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    padding: 0.5rem 0 !important;
    min-width: 160px !important;
}

.dropdown-item {
    padding: 0.5rem 1rem !important;
    font-size: 0.875rem !important;
    transition: all 0.2s ease !important;
}

.dropdown-item:hover {
    background-color: #f8f9fa !important;
    color: #212529 !important;
}

.dropdown-item i {
    width: 16px !important;
    text-align: center !important;
}

/* Manage Request Modal Styles */
.enhanced-manage-modal {
    border: none;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(139, 69, 67, 0.3);
}

/* Ensure modal is visible when shown */
#manageRequestModal.show {
    display: block !important;
}

#manageRequestModal.modal.show {
    display: block !important;
}

.enhanced-manage-modal .modal-header {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    color: white;
    border: none;
    padding: 2rem;
    position: relative;
}

.enhanced-manage-modal .modal-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="10" cy="60" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="90" cy="40" r="0.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
}

.manage-icon {
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    position: relative;
    z-index: 1;
}

.enhanced-manage-modal .modal-body {
    padding: 2rem;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

.enhanced-manage-modal .modal-footer {
    padding: 1.5rem 2rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-top: 1px solid rgba(139, 69, 67, 0.1);
}

.enhanced-manage-modal .btn-lg {
    padding: 1rem 2rem;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 0.75rem;
    transition: all 0.3s ease;
}

.enhanced-manage-modal .btn-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.enhanced-manage-modal .btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
    background: linear-gradient(135deg, #218838 0%, #1ea085 100%);
}

.enhanced-manage-modal .btn-danger {
    background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
    border: none;
    box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
}

.enhanced-manage-modal .btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(220, 53, 69, 0.4);
    background: linear-gradient(135deg, #c82333 0%, #d63384 100%);
}

/* Filter Chips Styles */
.filter-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.375rem 0.75rem;
    background: white;
    color: #8B4543;
    border: 1.5px solid #8B4543;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
    transition: all 0.2s ease;
    cursor: pointer;
}

.filter-chip:hover {
    background: #8B4543;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(139, 69, 67, 0.2);
}

.filter-chip .remove-filter {
    background: none;
    border: none;
    color: inherit;
    font-size: 0.8rem;
    cursor: pointer;
    padding: 0;
    width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.filter-chip:hover .remove-filter {
    background: rgba(255, 255, 255, 0.2);
}

.filter-chip .remove-filter:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1);
}

/* Animation for filter chips */
.filter-chip {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .filter-chip {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
    }
    
    #activeFiltersContainer {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
}

/* Enhanced Status Update Modal Styles */
.enhanced-status-modal {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.15);
    overflow: hidden;
}

.enhanced-status-modal .modal-header {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    border: none;
    padding: 2rem;
    color: white;
}

.enhanced-status-modal .modal-header .status-icon {
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    backdrop-filter: blur(10px);
}

.enhanced-status-modal .modal-title {
    font-weight: 700;
    font-size: 1.4rem;
}

.enhanced-status-modal .modal-body {
    padding: 2rem;
}

.enhanced-status-modal .modal-footer {
    border-top: 1px solid #e9ecef;
    padding: 1.5rem 2rem;
    background: #f8f9fa;
}

/* Section Headers */
.section-header {
    display: flex;
    align-items: center;
    font-weight: 600;
    color: #495057;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e9ecef;
}

.section-header i {
    color: #8B4543;
    font-size: 1.1rem;
}

/* Status Options */
.status-options {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.status-option {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 1rem;
    transition: all 0.3s ease;
    cursor: pointer;
}

.status-option:hover {
    border-color: #8B4543;
    background: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(139, 69, 67, 0.1);
}

.status-option.selected {
    border-color: #8B4543;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    box-shadow: 0 4px 12px rgba(139, 69, 67, 0.15);
}

.status-radio {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.status-input {
    display: none;
}

.status-label {
    display: flex;
    align-items: center;
    gap: 1rem;
    cursor: pointer;
    width: 100%;
    margin: 0;
}

.status-icon-approved,
.status-icon-rejected {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: white;
}

.status-icon-approved {
    background: linear-gradient(135deg, #4A7C59 0%, #5a9c62 100%);
}



.status-icon-rejected {
    background: linear-gradient(135deg, #B33A3A 0%, #dc3545 100%);
}

.status-content {
    flex: 1;
}

.status-title {
    font-weight: 600;
    color: #495057;
    font-size: 1.1rem;
    margin-bottom: 0.25rem;
}

.status-description {
    color: #6c757d;
    font-size: 0.9rem;
    line-height: 1.4;
}

/* Notes Section */
.notes-section {
    margin-top: 2rem;
}

.enhanced-textarea {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 1rem;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    resize: vertical;
}

.enhanced-textarea:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
    outline: none;
}

.enhanced-textarea.text-warning {
    border-color: #ffc107;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
}

.enhanced-textarea.text-danger {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.textarea-counter {
    text-align: right;
    color: #6c757d;
    font-size: 0.8rem;
    margin-top: 0.5rem;
}

/* Buttons */
.btn-update-status {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-update-status:hover:not(:disabled) {
    background: linear-gradient(135deg, #723937 0%, #5a2e2c 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(139, 69, 67, 0.3);
}

.btn-update-status:disabled {
    background: #6c757d;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.btn-outline-secondary {
    border: 2px solid #6c757d;
    color: #6c757d;
    background: transparent;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-outline-secondary:hover {
    background: #6c757d;
    color: white;
    transform: translateY(-1px);
}

/* Animation for status selection */
.status-option {
    position: relative;
    overflow: hidden;
}

.status-option::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(139, 69, 67, 0.1), transparent);
    transition: left 0.5s ease;
}

.status-option:hover::before {
    left: 100%;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .enhanced-status-modal .modal-body {
        padding: 1.5rem;
    }
    
    .enhanced-status-modal .modal-footer {
        padding: 1rem 1.5rem;
    }
    
    .status-options {
        gap: 0.75rem;
    }
    
    .status-option {
        padding: 0.75rem;
    }
}

/* Search and Length Menu */
.dataTables_wrapper {
    padding: 1.5rem;
}

/* Hide the "Show" dropdown and search */
.dataTables_length {
    display: none !important;
}

.dataTables_filter {
    display: none !important;
}

.dataTables_length select {
    padding: 0.5rem 2.5rem 0.5rem 1rem;
    font-size: 0.875rem;
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    background-color: white;
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%238B4543' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 1rem;
    transition: all 0.2s ease;
}

.dataTables_length select:hover {
    border-color: var(--primary-color);
}

.dataTables_filter {
    text-align: left !important;
    margin-bottom: 0.5rem;
    margin-top: 0.5rem;
    padding-left: 0;
}

.dataTables_filter label {
    display: inline-block;
    margin-right: 0.5rem;
    vertical-align: middle;
    font-weight: 500;
    color: var(--text-dark);
}

.dataTables_filter input {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    background-color: white;
    min-width: 280px;
    max-width: 320px;
    transition: all 0.2s ease;
}

.dataTables_filter input:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
}

/* Pagination */
.dataTables_paginate {
    margin-top: 1.5rem;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 0.5rem;
}

.dataTables_paginate .paginate_button {
    min-width: 36px;
    height: 36px;
    padding: 0;
    margin: 0 1px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.35rem;
    border: none;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-dark) !important;
    background-color: white;
    transition: all 0.2s ease;
}

.dataTables_paginate .paginate_button:hover {
    color: var(--primary-color) !important;
    background: var(--hover-color);
    border: none;
}

.dataTables_paginate .paginate_button.current {
    background: var(--primary-color);
    color: white !important;
    border: none;
    font-weight: 600;
}

.dataTables_paginate .paginate_button.disabled {
    color: var(--border-color) !important;
    border-color: var(--border-color);
    cursor: not-allowed;
    opacity: 0.5;
}

/* Ensure pagination buttons are clickable when enabled */
.dataTables_paginate .paginate_button:not(.disabled) {
    cursor: pointer;
    opacity: 1;
}

.dataTables_paginate .paginate_button:not(.disabled):hover {
    color: var(--primary-color) !important;
    background: var(--hover-color);
    border-color: var(--primary-color);
    transform: translateY(-1px);
}

/* Buttons */
.btn-success {
    background: var(--success-color);
    border: none;
    border-radius: 0.5rem;
    padding: 0.625rem 1.25rem;
    font-weight: 500;
    color: white;
    transition: all 0.2s ease;
}

.btn-success:hover {
    background: darken(var(--success-color), 10%);
    transform: translateY(-1px);
    box-shadow: 0 0.15rem 1.75rem 0 rgba(74, 124, 89, 0.15);
}

/* Status Badges */
.badge {
    padding: 0.5rem 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 0.35rem;
}

.badge.bg-success {
    background: var(--success-color) !important;
    color: white;
}

.badge.bg-danger {
    background: var(--danger-color) !important;
    color: white;
}

.badge.bg-warning {
    background: var(--warning-color) !important;
    color: white;
}

/* Action Buttons */
.btn-group .btn, .btn {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 0.35rem;
    margin: 0 0.125rem;
    border: none;
    transition: all 0.2s ease;
}

.btn-warning {
    background: var(--warning-color);
    color: white;
}

.btn-warning:hover {
    background: darken(var(--warning-color), 10%);
    transform: translateY(-1px);
    box-shadow: 0 0.15rem 1.75rem 0 rgba(196, 128, 77, 0.15);
}

.btn-danger {
    background: var(--danger-color);
    color: white;
}

.btn-danger:hover {
    background: darken(var(--danger-color), 10%);
    transform: translateY(-1px);
    box-shadow: 0 0.15rem 1.75rem 0 rgba(179, 58, 58, 0.15);
}

.btn-primary {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-dark);
    border-color: var(--primary-dark);
    transform: translateY(-1px);
    box-shadow: 0 0.15rem 1.75rem 0 rgba(139, 69, 67, 0.15);
}

/* Form Controls */
.form-select, .form-control {
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.form-select:focus, .form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
}

/* Modal Styling */
.modal-content {
    border: none;
    border-radius: 0.75rem;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(139, 69, 67, 0.15);
}

.modal-header {
    background: var(--primary-color);
    color: var(--text-light);
    border: none;
    padding: 1.5rem;
    border-radius: 0.75rem 0.75rem 0 0;
}

.modal-header .btn-close {
    color: var(--text-light);
    opacity: 0.8;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    background-color: var(--hover-color);
    border-top: 1px solid var(--border-color);
    padding: 1.25rem;
    border-radius: 0 0 0.75rem 0.75rem;
}

/* Info Text */
.dataTables_info {
    color: var(--text-dark);
    font-size: 0.875rem;
    padding-top: 1.5rem;
}

/* Breadcrumb */
.breadcrumb {
    padding: 0.75rem 1rem;
    background: var(--hover-color);
    border-radius: 0.35rem;
    margin-bottom: 1.5rem;
}

.breadcrumb-item a {
    color: var(--primary-color);
    text-decoration: none;
}

.breadcrumb-item.active {
    color: var(--text-dark);
}

/* Page Title */
h1 {
    color: var(--text-dark);
    font-weight: 400;
    margin-bottom: 1.5rem;
}

/* Filter Controls */
.form-select-sm {
    padding: 0.4rem 2rem 0.4rem 0.75rem;
    font-size: 0.875rem;
}

.gap-2 {
    gap: 0.5rem !important;
}

.big-section-title {
    color: #8B4543;
    font-size: 2.5rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.5rem;
    margin-top: 0.5rem;
    letter-spacing: 0.5px;
}
.big-section-icon {
    font-size: 2.5rem;
    color: #8B4543;
    display: flex;
    align-items: center;
}
.big-section-underline {
    border: none;
    border-top: 5px solid #e5d6d6;
    margin-top: -10px;
    margin-bottom: 20px;
    width: 100%;
}

/* Enhanced Filter Panel Styles */
#ingredientFilterPanel {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border: 1px solid #e0e0e0;
    background: #fff;
    z-index: 1050;
    max-height: 80vh;
    overflow-y: auto;
}

/* Ensure filter panel is always visible */
@media (max-width: 768px) {
    #ingredientFilterPanel {
        position: fixed !important;
        left: 50% !important;
        top: 50% !important;
        transform: translate(-50%, -50%) !important;
        width: 90vw !important;
        max-width: 400px !important;
        max-height: 80vh !important;
        overflow-y: auto !important;
    }
}

#ingredientFilterPanel .form-label {
        font-size: 0.875rem;
    margin-bottom: 0.25rem;
}

#ingredientFilterPanel .form-select,
#ingredientFilterPanel .form-control {
    font-size: 0.875rem;
    border-radius: 0.5rem;
    border: 1px solid #d1d5db;
}

#ingredientFilterPanel .form-select:focus,
#ingredientFilterPanel .form-control:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
}

#customDateRange {
    background: #f8f9fa;
    padding: 0.75rem;
    border-radius: 0.5rem;
    border: 1px solid #e9ecef;
}

#ingredientFilterBtn {
    transition: all 0.2s ease;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    background: white !important;
    color: #8B4543 !important;
}

#ingredientFilterBtn span {
    color: #8B4543 !important;
    font-weight: 600 !important;
    font-size: 14px !important;
    line-height: 1 !important;
}

#ingredientFilterBtn:hover {
    background: #8B4543 !important;
    color: white !important;
    transform: translateY(-1px);
}

#ingredientFilterBtn:hover span {
    color: white !important;
}
</style>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">
                                <i class="fas fa-clipboard-list me-1"></i>
                                Completed Requests
                            </h5>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex align-items-center gap-2" style="position: relative;">
                            <button id="ingredientFilterBtn" class="btn btn-outline-secondary d-flex align-items-center gap-2" style="border-radius: 25px; padding: 8px 16px; box-shadow: 0 2px 8px rgba(139,69,67,0.08); border: 1.5px solid #8B4543; color: #8B4543; font-weight: 600; transition: background 0.18s, color 0.18s; min-width: 120px; justify-content: center;" title="Show Filters">
                                <i class="fas fa-filter" style="color: #8B4543;"></i>
                                <span style="font-size: 14px; font-weight: 600;">Filter</span>
                            </button>
                                <div id="activeFiltersContainer" class="d-flex align-items-center gap-2 ms-2" style="display: none;">
                                    <!-- Individual filter chips will be displayed here -->
                        </div>
                                <div id="ingredientFilterPanel" class="card shadow-sm p-3" style="display: none; position: absolute; left: 0; top: 100%; min-width: 400px; z-index: 1050; border-radius: 1rem; border: 1px solid #e0e0e0; background: #fff; margin-top: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0" style="color: #8B4543; font-weight: 600;">Filter Options</h6>
                                        <button type="button" class="btn-close" id="closeFilterPanel" style="font-size: 0.8rem;"></button>
                                    </div>
                                    <div class="mb-2">
                                        <label for="filterBranchSelect" class="form-label mb-1" style="color: #8B4543; font-weight: 500;">Branch</label>
                                        <select id="filterBranchSelect" class="form-select">
                                            <option value="">All Branches</option>
                                            <!-- Branch options will be loaded dynamically -->
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label for="filterDateSelect" class="form-label mb-1" style="color: #8B4543; font-weight: 500;">Date</label>
                                        <select id="filterDateSelect" class="form-select">
                                            <option value="">All Dates</option>
                                            <option value="today">Today</option>
                                            <option value="yesterday">Yesterday</option>
                                            <option value="this_week">This Week</option>
                                            <option value="this_month">This Month</option>
                                            <option value="custom">Custom Range</option>
                                        </select>
                                    </div>
                                    <div class="mb-2" id="customDateRange" style="display: none;">
                                        <div class="row">
                                            <div class="col-6">
                                                <label for="filterDateFrom" class="form-label mb-1" style="color: #8B4543; font-weight: 500;">From</label>
                                                <input type="date" id="filterDateFrom" class="form-control">
                                            </div>
                                            <div class="col-6">
                                                <label for="filterDateTo" class="form-label mb-1" style="color: #8B4543; font-weight: 500;">To</label>
                                                <input type="date" id="filterDateTo" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label for="filterIngredientSelect" class="form-label mb-1" style="color: #8B4543; font-weight: 500;">Ingredient</label>
                                        <select id="filterIngredientSelect" class="form-select">
                                            <option value="">All Ingredients</option>
                                            <!-- Ingredient options will be loaded dynamically -->
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label for="filterStatusSelect" class="form-label mb-1" style="color: #8B4543; font-weight: 500;">Request Status</label>
                                        <select id="filterStatusSelect" class="form-select">
                                            <option value="">All Request Status</option>
                                            <option value="pending">Pending</option>
                                            <option value="approved">Approved</option>
                                            <option value="rejected">Rejected</option>
                                        </select>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button id="applyIngredientFilter" class="btn btn-primary flex-fill" style="background: #8B4543; border: none; border-radius: 0.7rem; font-weight: 600;">Apply Filter</button>
                                        <button id="resetIngredientFilter" class="btn btn-outline-secondary" style="border: 1.5px solid #6c757d; border-radius: 0.7rem; font-weight: 600;">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                                         <!-- Completed Requests Table -->
                     <div class="table-section pending-section">
                        <div class="table-responsive">
                             <table id="pendingRequestsTable" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Branch</th>
                                        <th>Date Requested</th>
                                        <th>Ingredients</th>
                                        <th>Request Status</th>
                                        <th>Notes</th>
                                        <th>Updated By</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Manage Request Modal -->
<div class="modal fade" id="manageRequestModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content enhanced-manage-modal">
            <div class="modal-header bg-gradient-primary">
                <div class="d-flex align-items-center">
                    <div class="manage-icon me-3">
                        <i class="fas fa-clipboard-list"></i>
            </div>
                    <div>
                        <h5 class="modal-title mb-0">Manage Request</h5>
                        <small class="text-white-50">Choose an action for this ingredient request</small>
        </div>
            </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <p class="mb-3">What would you like to do with this ingredient request?</p>
            </div>
                <div class="d-grid gap-3">
                    <button type="button" class="btn btn-success btn-lg approve-request-modal" id="approveRequestBtn">
                        <i class="fas fa-check me-2"></i>Approve Request
                    </button>
                    <button type="button" class="btn btn-danger btn-lg reject-request-modal" id="rejectRequestBtn">
                        <i class="fas fa-times me-2"></i>Reject Request
                    </button>
        </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
        </div>
    </div>
    </div>
</div>

<!-- Enhanced Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content enhanced-status-modal">
            <div class="modal-header bg-gradient-primary">
                <div class="d-flex align-items-center">
                    <div class="status-icon me-3">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0">Update Request Status</h5>
                        <small class="text-light opacity-75">Review and update ingredient request status</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="statusForm">
                    <input type="hidden" id="requestId">
                    
                    <!-- Status Selection Section -->
                    <div class="status-section mb-4">
                        <div class="section-header">
                            <i class="fas fa-flag me-2"></i>
                            <span>Request Status</span>
                            </div>
                        <div class="status-options">
                            <div class="status-option" data-status="approved">
                                <div class="status-radio">
                                    <input type="radio" name="requestStatus" id="statusApproved" value="approved" class="status-input">
                                    <label for="statusApproved" class="status-label">
                                        <div class="status-icon-approved">
                                            <i class="fas fa-check-circle"></i>
                        </div>
                                        <div class="status-content">
                                            <div class="status-title">Approve</div>
                                            <div class="status-description">Grant approval for this request</div>
                            </div>
                                    </label>
                        </div>
                    </div>

                            <div class="status-option" data-status="rejected">
                                <div class="status-radio">
                                    <input type="radio" name="requestStatus" id="statusRejected" value="rejected" class="status-input">
                                    <label for="statusRejected" class="status-label">
                                        <div class="status-icon-rejected">
                                            <i class="fas fa-times-circle"></i>
                            </div>
                                        <div class="status-content">
                                            <div class="status-title">Reject</div>
                                            <div class="status-description">Decline this request</div>
                                        </div>
                                    </label>
                                </div>
                        </div>
                    </div>
                </div>

                    <!-- Notes Section -->
                    <div class="notes-section">
                        <div class="section-header">
                            <i class="fas fa-comment-alt me-2"></i>
                            <span>Additional Notes</span>
                    </div>
                        <div class="form-group">
                            <textarea 
                                class="form-control enhanced-textarea" 
                                id="statusNotes" 
                                rows="4" 
                                placeholder="Enter any additional notes, reasons for approval/rejection, or special instructions..."
                            ></textarea>
                            <div class="textarea-counter">
                                <span id="notesCounter">0</span>/500 characters
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                        </button>
                <button type="button" class="btn btn-primary btn-update-status" id="updateStatus" disabled>
                    <i class="fas fa-save me-1"></i>Update Status
                        </button>
            </div>
        </div>
                    </div>
                </div>

<!-- Delivery Status Update Modal -->
<div class="modal fade" id="deliveryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-truck me-1"></i>
                    Update Delivery Status
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="deliveryForm">
                    <input type="hidden" id="deliveryRequestId">
                    <div class="mb-3">
                        <label class="form-label">Delivery Status</label>
                        <select class="form-select" id="deliveryStatus">
                            <option value="delivered">Delivered</option>
                            <option value="returned">Returned</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Delivery Date</label>
                        <input type="datetime-local" class="form-control" id="deliveryDate">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Delivery Notes</label>
                        <textarea class="form-control" id="deliveryNotes" rows="3" placeholder="Enter delivery notes, return reasons, or cancellation details..."></textarea>
                </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-info" id="updateDelivery">
                    <i class="fas fa-save me-1"></i>
                    Update Delivery
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Load branches dynamically
    function loadBranches() {
        $.ajax({
            url: 'get_branches.php',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const branchFilter = $('#filterBranchSelect');
                    // Keep the "All Branches" option
                    branchFilter.find('option:not([value=""])').remove();
                    
                    // Add branch options
                    response.data.forEach(function(branch) {
                        branchFilter.append(`<option value="${branch.branch_id}">${branch.branch_name} (${branch.branch_code})</option>`);
                    });
                } else {
                    console.error('Failed to load branches:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading branches:', error);
            }
        });
    }
    
    // Load ingredients dynamically
    function loadIngredients() {
        console.log('🔄 Loading ingredients...');
        $.ajax({
            url: 'get_all_ingredients.php',
            method: 'GET',
            success: function(response) {
                console.log('📦 Ingredients response:', response);
                if (response.success && response.data) {
                    const ingredientFilter = $('#filterIngredientSelect');
                    // Keep the "All Ingredients" option
                    ingredientFilter.find('option:not([value=""])').remove();
                    
                    console.log(`✅ Found ${response.data.length} ingredients`);
                    
                    // Add ingredient options
                    response.data.forEach(function(ingredient) {
                        ingredientFilter.append(`<option value="${ingredient.ingredient_id}">${ingredient.ingredient_name}</option>`);
                    });
                    
                    // Filter button text remains as "Filter"
                    // No need to show ingredient count
                } else {
                    console.error('❌ Failed to load ingredients:', response.message || 'Unknown error');
                    // Show error in the filter
                    const ingredientFilter = $('#filterIngredientSelect');
                    ingredientFilter.find('option:not([value=""])').remove();
                    ingredientFilter.append('<option value="" disabled>Error loading ingredients</option>');
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Error loading ingredients:', error);
                console.error('Response:', xhr.responseText);
                
                // Show error in the filter
                const ingredientFilter = $('#filterIngredientSelect');
                ingredientFilter.find('option:not([value=""])').remove();
                ingredientFilter.append('<option value="" disabled>Error loading ingredients</option>');
            }
        });
    }
    
    // Load branches and ingredients when page loads
    loadBranches();
    loadIngredients();
    
    // Add change event listeners to update filter chips
    $('#filterBranchSelect, #filterDateSelect, #filterIngredientSelect, #filterStatusSelect').on('change', function() {
        updateActiveFilterIndicator();
    });
    
    // Special handling for custom date inputs
    $('#filterDateFrom, #filterDateTo').on('change', function() {
        updateActiveFilterIndicator();
    });
    
    // Event delegation for remove filter buttons
    $(document).on('click', '.remove-filter', function() {
        const filterType = $(this).data('filter-type');
        removeFilter(filterType);
    });
    
         // Initialize Pending Requests DataTable
     const pendingTable = $('#pendingRequestsTable').DataTable({
        processing: true,
        serverSide: false, // Client-side processing
         pageLength: 10, // Show 10 records per page
         lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]], // Available page lengths
        paging: true, // Ensure pagination is enabled
        pagingType: 'simple', // Show only Previous/Next buttons
         searching: false, // Disable search functionality
        ordering: false, // Disable client-side sorting since we sort on server
        ajax: {
            url: 'ingredient_requests_ajax.php',
            type: 'POST',
            data: function(d) {
                d.branch = $('#filterBranchSelect').val();
                 d.status = $('#filterStatusSelect').val();
                d.ingredient = $('#filterIngredientSelect').val();
                 d.delivery_status = 'non-pending'; // Show only non-pending requests (approved/rejected)
                d.date_filter = $('#filterDateSelect').val();
                d.date_from = $('#filterDateFrom').val();
                d.date_to = $('#filterDateTo').val();
                 d.table_type = 'pending'; // Add table type identifier
                 console.log('Pending DataTable AJAX request:', d);
            },
            dataSrc: function(json) {
                 console.log('Pending DataTable AJAX response:', json);
                console.log('Total records:', json.recordsTotal);
                console.log('Filtered records:', json.recordsFiltered);
                 console.log('Data length:', json.data.length);
                return json.data || [];
            },
            error: function(xhr, error, thrown) {
                 console.error('Pending DataTable AJAX error:', error, thrown);
                console.error('Response:', xhr.responseText);
            }
        },
        columns: [
            { data: 'branch_name' },
            { 
                data: 'request_date',
                render: function(data) {
                    const date = new Date(data);
                    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
                },
                type: 'datetime'
            },
            { data: 'ingredients' },
            {
                data: 'status',
                render: function(data) {
                    const statusClasses = {
                        'pending': 'bg-warning',
                        'approved': 'bg-success',
                        'rejected': 'bg-danger'
                    };
                    return `<span class="badge ${statusClasses[data]}">${data.toUpperCase()}</span>`;
                }
            },
            {
                data: 'delivery_notes',
                render: function(data) {
                    if (data && data.trim() !== '') {
                        // Truncate long notes and show full text on hover
                        const truncatedText = data.length > 50 ? data.substring(0, 50) + '...' : data;
                        return `<span title="${data.replace(/"/g, '&quot;')}" style="cursor: help;">${truncatedText}</span>`;
                    } else {
                        return '<span class="text-muted">-</span>';
                    }
                }
            },
            { 
                data: 'updated_by',
                render: function(data) {
                    return data || 'N/A';
                }
            },
        ],
        order: [[1, 'desc']]
    });


    // Filter panel logic
    (function() {
        var filterBtn = document.getElementById('ingredientFilterBtn');
        var filterPanel = document.getElementById('ingredientFilterPanel');
        var applyBtn = document.getElementById('applyIngredientFilter');
        var resetBtn = document.getElementById('resetIngredientFilter');
        var closeBtn = document.getElementById('closeFilterPanel');
        var dateSelect = document.getElementById('filterDateSelect');
        var customDateRange = document.getElementById('customDateRange');

        if (filterBtn && filterPanel && applyBtn && dateSelect && customDateRange) {
        // Toggle filter panel
            filterBtn.addEventListener('click', function(e) {
                filterPanel.style.display = filterPanel.style.display === 'none' ? 'block' : 'none';
                if (filterPanel.style.display === 'block') {
                    // Ensure panel is visible
                    adjustFilterPanelPosition();
                    dateSelect.focus();
                }
                e.stopPropagation();
            });

            // Close filter panel
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                filterPanel.style.display = 'none';
        });
            }

            // Handle date filter change
        dateSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                customDateRange.style.display = 'block';
            } else {
                customDateRange.style.display = 'none';
            }
        });

        // Apply filter
        applyBtn.addEventListener('click', function() {
            // Reload the DataTable with new filter values
                 $('#pendingRequestsTable').DataTable().ajax.reload();
            filterPanel.style.display = 'none';
            
            // Show active filter indicator
            updateActiveFilterIndicator();
                
                // Show success message
                showFilterMessage('Filters applied successfully!', 'success');
        });

            // Reset filter
            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
            // Reset all filter values
            $('#filterBranchSelect').val('');
                    $('#filterDateSelect').val('');
            $('#filterIngredientSelect').val('');
                    $('#filterStatusSelect').val('');
            $('#filterDateFrom').val('');
            $('#filterDateTo').val('');
            $('#customDateRange').hide();
            
            // Reload the DataTable to show all data
                     $('#pendingRequestsTable').DataTable().ajax.reload();
            
            // Close the filter panel
            filterPanel.style.display = 'none';
            
            // Update active filter indicator (this will hide it)
            updateActiveFilterIndicator();
                    
                    // Show reset message
                    showFilterMessage('Filters reset successfully!', 'success');
                });
            }

            // Function to adjust filter panel position
            function adjustFilterPanelPosition() {
                var rect = filterBtn.getBoundingClientRect();
                var panel = filterPanel;
                
                // Check if panel would go off-screen to the right
                if (rect.left + 400 > window.innerWidth) {
                    panel.style.left = 'auto';
                    panel.style.right = '0';
                } else {
                    panel.style.left = '0';
                    panel.style.right = 'auto';
                }
                
                // Check if panel would go off-screen to the bottom
                if (rect.bottom + 400 > window.innerHeight) {
                    panel.style.top = 'auto';
                    panel.style.bottom = '100%';
                    panel.style.marginTop = '0';
                    panel.style.marginBottom = '10px';
                } else {
                    panel.style.top = '100%';
                    panel.style.bottom = 'auto';
                    panel.style.marginTop = '10px';
                    panel.style.marginBottom = '0';
                }
            }

            // Hide panel when clicking outside
            document.addEventListener('click', function(e) {
                if (!filterPanel.contains(e.target) && e.target !== filterBtn) {
                    filterPanel.style.display = 'none';
                }
            });

            // Prevent click inside panel from closing it
            filterPanel.addEventListener('click', function(e) {
                e.stopPropagation();
            });

            // Adjust position on window resize
            window.addEventListener('resize', function() {
                if (filterPanel.style.display === 'block') {
                    adjustFilterPanelPosition();
                }
            });
        }
    })();

    // Filter message function
    function showFilterMessage(message, type = 'success') {
        Swal.fire({
            icon: type,
            title: 'Filter Update',
            text: message,
            confirmButtonColor: '#8B4543',
            timer: 2000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    }

    // Update active filter indicator with individual chips
    function updateActiveFilterIndicator() {
        const container = $('#activeFiltersContainer');
        container.empty();
        
        let hasActiveFilters = false;
        
        // Check Branch filter
        const branchValue = $('#filterBranchSelect').val();
        if (branchValue) {
            const branchText = $('#filterBranchSelect option:selected').text();
            container.append(createFilterChip('branch', branchText, branchValue));
            hasActiveFilters = true;
        }
        
        // Check Date filter
        const dateValue = $('#filterDateSelect').val();
        if (dateValue) {
            let dateText = $('#filterDateSelect option:selected').text();
            if (dateValue === 'custom') {
                const fromDate = $('#filterDateFrom').val();
                const toDate = $('#filterDateTo').val();
                if (fromDate || toDate) {
                    dateText = `Custom: ${fromDate || 'Start'} - ${toDate || 'End'}`;
                    container.append(createFilterChip('date', dateText, 'custom'));
                    hasActiveFilters = true;
                }
            } else {
                container.append(createFilterChip('date', dateText, dateValue));
                hasActiveFilters = true;
            }
        }
        
        // Check Ingredient filter
        const ingredientValue = $('#filterIngredientSelect').val();
        if (ingredientValue) {
            const ingredientText = $('#filterIngredientSelect option:selected').text();
            container.append(createFilterChip('ingredient', ingredientText, ingredientValue));
            hasActiveFilters = true;
        }
        
        // Check Request Status filter
        const statusValue = $('#filterStatusSelect').val();
        if (statusValue) {
            const statusText = $('#filterStatusSelect option:selected').text();
            container.append(createFilterChip('request_status', statusText, statusValue));
            hasActiveFilters = true;
        }
        
        
        // Show/hide container
        if (hasActiveFilters) {
            container.show();
        } else {
            container.hide();
        }
    }
    
    // Create individual filter chip
    function createFilterChip(type, text, value) {
        return $(`
            <div class="filter-chip" data-filter-type="${type}" data-filter-value="${value}">
                <span>${text}</span>
                <button class="remove-filter" data-filter-type="${type}" title="Remove ${text} filter">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `);
    }

    // Remove individual filter
    function removeFilter(filterType) {
        console.log('🗑️ Removing filter:', filterType);
        
        switch(filterType) {
            case 'branch':
                $('#filterBranchSelect').val('').trigger('change');
                break;
            case 'date':
                $('#filterDateSelect').val('').trigger('change');
                $('#filterDateFrom').val('');
                $('#filterDateTo').val('');
                $('#customDateRange').hide();
                break;
            case 'ingredient':
                $('#filterIngredientSelect').val('').trigger('change');
                break;
            case 'request_status':
                $('#filterStatusSelect').val('').trigger('change');
                break;
        }
        
        // Update the display and refresh table
        updateActiveFilterIndicator();
        
        // Use the DataTable API to reload
         if ($.fn.DataTable.isDataTable('#pendingRequestsTable')) {
             $('#pendingRequestsTable').DataTable().ajax.reload();
        }
        
        console.log('✅ Filter removed and table refreshed');
    }

    // Auto-refresh every 30 seconds to show new requests
    setInterval(function() {
         pendingTable.ajax.reload(null, false); // false = stay on current page
    }, 30000);

    // Manual refresh on page focus (when user comes back to tab)
    $(window).focus(function() {
         pendingTable.ajax.reload(null, false);
     });

    // Add functionality to DataTable pagination buttons using proper event binding
    function showPaginationMessage(message, type = 'info') {
        Swal.fire({
            icon: type,
            title: 'Navigation',
            text: message,
            confirmButtonColor: '#8B4543',
            timer: 2000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    }

    // Bind pagination events after table is drawn
    pendingTable.on('draw.dt', function() {
        // Remove existing event handlers to prevent duplicates
        $('.paginate_button.previous').off('click');
        $('.paginate_button.next').off('click');
        
        // Add event handlers for previous button
        $('.paginate_button.previous').on('click', function(e) {
            e.preventDefault();
            if (!$(this).hasClass('disabled')) {
                pendingTable.page('previous').draw('page');
                showPaginationMessage('Previous page loaded');
            } else {
                showPaginationMessage('Already on first page', 'warning');
            }
        });
        
        // Add event handlers for next button
        $('.paginate_button.next').on('click', function(e) {
            e.preventDefault();
            if (!$(this).hasClass('disabled')) {
                pendingTable.page('next').draw('page');
                showPaginationMessage('Next page loaded');
            } else {
                showPaginationMessage('Already on last page', 'warning');
            }
        });
    });

    // Force table redraw to ensure pagination is properly initialized
    setTimeout(function() {
        pendingTable.draw();
        console.log('Table redrawn with pagination. Page info:', pendingTable.page.info());
    }, 1000);

    // Status update handler
    $(document).on('click', '.update-status', function() {
        const requestId = $(this).data('id');
        $('#requestId').val(requestId);
        $('#statusModal').modal('show');
    });

    // Store request ID when manage request modal opens
    $(document).on('click', '.manage-request-btn', function() {
        const requestId = $(this).data('id');
        console.log('Manage request button clicked, request ID:', requestId);
        
        // Check if modal exists
        const modal = $('#manageRequestModal');
        console.log('Modal element found:', modal.length > 0);
        
        if (modal.length > 0) {
            modal.data('request-id', requestId);
            
            // Try different ways to show the modal
            try {
                modal.modal('show');
                console.log('Modal should be showing now');
            } catch (error) {
                console.error('Error showing modal:', error);
                // Fallback: show alert
                alert('Request ID: ' + requestId + '\nModal functionality needs to be fixed.');
            }
        } else {
            console.error('Modal element not found!');
            alert('Modal element not found!');
        }
    });

    // Approve request handler (from modal)
    $(document).on('click', '.approve-request-modal', function() {
        const requestId = $('#manageRequestModal').data('request-id');
        
        Swal.fire({
            title: 'Approve Request',
            text: 'Are you sure you want to approve this ingredient request?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check me-2"></i>Yes, approve it!',
            cancelButtonText: '<i class="fas fa-times me-2"></i>Cancel',
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'update_ingredient_request.php',
                    type: 'POST',
                    data: {
                        request_id: requestId,
                        status: 'approved',
                        action: 'approve'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Request Approved!',
                                text: response.message || 'Ingredient request has been approved successfully.',
                                confirmButtonColor: '#28a745',
                                timer: 3000,
                                showConfirmButton: false
                            }).then(() => {
                                // Close the modal and refresh both tables
                                $('#manageRequestModal').modal('hide');
                                $('#pendingRequestsTable').DataTable().ajax.reload();
                                $('#processedRequestsTable').DataTable().ajax.reload();
                            });
                        } else {
            Swal.fire({
                icon: 'error',
                                title: 'Approval Failed',
                                text: response.message || 'Failed to approve the request.',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    },
                    error: function() {
        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred while approving the request.',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });
            }
        });
    });

    // Reject request handler (from modal)
    $(document).on('click', '.reject-request-modal', function() {
        const requestId = $('#manageRequestModal').data('request-id');
        
        Swal.fire({
            title: 'Reject Request',
            text: 'Are you sure you want to reject this ingredient request?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-times me-2"></i>Yes, reject it!',
            cancelButtonText: '<i class="fas fa-check me-2"></i>Cancel',
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
        $.ajax({
            url: 'update_ingredient_request.php',
            type: 'POST',
            data: {
                request_id: requestId,
                        status: 'rejected',
                        action: 'reject'
            },
                    dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                                title: 'Request Rejected!',
                                text: response.message || 'Ingredient request has been rejected successfully.',
                                confirmButtonColor: '#dc3545',
                        timer: 3000,
                        showConfirmButton: false
                    }).then(() => {
                                // Close the modal and refresh both tables
                                $('#manageRequestModal').modal('hide');
                                $('#pendingRequestsTable').DataTable().ajax.reload();
                                $('#processedRequestsTable').DataTable().ajax.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                                title: 'Rejection Failed',
                                text: response.message || 'Failed to reject the request.',
                                confirmButtonColor: '#dc3545'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred while rejecting the request.',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });
            }
        });
    });


    // Enhanced status modal functionality
    $('.status-option').click(function() {
        const status = $(this).data('status');
        const radioId = 'status' + status.charAt(0).toUpperCase() + status.slice(1);
        
        // Update radio button selection
        $('#' + radioId).prop('checked', true);
        
        // Update visual selection
        $('.status-option').removeClass('selected');
        $(this).addClass('selected');
        
        // Enable update button
        $('#updateStatus').prop('disabled', false);
    });
    
    // Character counter for notes
    $('#statusNotes').on('input', function() {
        const maxLength = 500;
        const currentLength = $(this).val().length;
        const remaining = maxLength - currentLength;
        
        $('#notesCounter').text(currentLength);
        
        if (currentLength > maxLength * 0.8) {
            $(this).addClass('text-warning');
        } else {
            $(this).removeClass('text-warning');
        }
        
        if (currentLength > maxLength) {
            $(this).addClass('text-danger');
        } else {
            $(this).removeClass('text-danger');
        }
    });
    
    // Reset modal when opened
    $('#statusModal').on('show.bs.modal', function() {
        // Reset form
        $('#statusForm')[0].reset();
        
        // Reset visual selection
        $('.status-option').removeClass('selected');
        
        // Reset character counter
        $('#notesCounter').text('0');
        
        // Disable update button until status is selected
        $('#updateStatus').prop('disabled', true);
        
        // Remove warning/error classes
        $('#statusNotes').removeClass('text-warning text-danger');
    });
    
    // Enhanced approval confirmation with inventory preview
    function confirmApproval(requestId, ingredientsData) {
        // Parse ingredients data
        let ingredients = [];
        try {
            ingredients = JSON.parse(ingredientsData);
        } catch (e) {
            console.error('Error parsing ingredients:', e);
            return false;
        }
        
        const title = 'Confirm Ingredient Request Approval';
        const icon = 'warning';
        const confirmButtonText = '<i class="fas fa-check me-2"></i>Approve Request';
        const confirmButtonColor = '#28a745';
        const actionDescription = 'You are about to approve this request. This will:';
        const actionList = [
            '<strong>Deduct</strong> the requested quantities from current inventory',
            'Update ingredient statuses (Available/Low Stock/Out of Stock)',
            'Log all stock movements for audit purposes'
        ];
        const noteText = '<strong>Note:</strong> This action cannot be undone. Please ensure you have sufficient stock before approving.';
        
        // Build confirmation message
        let message = '<div class="text-start">';
        message += `<h6 class="mb-3"><i class="fas fa-exclamation-triangle text-warning me-2"></i>${title}</h6>`;
        message += `<p class="mb-3">${actionDescription}</p>`;
        message += '<ul class="mb-3">';
        actionList.forEach(item => {
            message += `<li>${item}</li>`;
        });
        message += '</ul>';
        
        message += '<div class="alert alert-info">';
        message += '<h6 class="mb-2"><i class="fas fa-list me-2"></i>Ingredients:</h6>';
        message += '<div class="table-responsive">';
        message += '<table class="table table-sm table-bordered mb-0">';
        message += '<thead><tr><th>Ingredient</th><th>Quantity</th><th>Current Stock</th><th>After Action</th></tr></thead><tbody>';
        
        // Fetch current stock levels for each ingredient
        ingredients.forEach(function(ingredient) {
            const quantityClass = 'text-danger';
            const quantityPrefix = '-';
            const afterActionText = '[New Stock]';
            
            message += `<tr>
                <td>${ingredient.ingredient_name || 'Ingredient ID: ' + ingredient.ingredient_id}</td>
                <td class="${quantityClass}">${quantityPrefix}${ingredient.quantity}</td>
                <td class="text-primary">[Current Stock]</td>
                <td class="text-success">${afterActionText}</td>
            </tr>`;
        });
        
        message += '</tbody></table></div>';
        message += '</div>';
        
        message += `<p class="text-danger mb-0">${noteText}</p>`;
        message += '</div>';
        
        // Show confirmation dialog
        return Swal.fire({
            title: title,
            html: message,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: confirmButtonColor,
            cancelButtonColor: '#6c757d',
            confirmButtonText: confirmButtonText,
            cancelButtonText: '<i class="fas fa-times me-2"></i>Cancel',
            width: '600px',
            customClass: {
                popup: 'rounded-4'
            }
        });
    }

    // Enhanced approval function
    function processRequestAction(requestId, ingredientsData) {
        const actionText = 'Approval';
        const loadingText = 'Processing Approval...';
        const loadingDescription = 'Updating inventory and processing request...';
        
        confirmApproval(requestId, ingredientsData).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
        Swal.fire({
                    title: loadingText,
                    html: `<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i><p>${loadingDescription}</p></div>`,
            allowOutsideClick: false,
                    showConfirmButton: false
                });
                
                // Get notes if any
                let notes = '';
                const notesInput = document.querySelector(`#notes-${requestId}`);
                if (notesInput) {
                    notes = notesInput.value;
                }
                
                // Process action
        $.ajax({
            url: 'update_ingredient_request.php',
            type: 'POST',
            data: {
                request_id: requestId,
                        status: actionType,
                        notes: notes
            },
                    dataType: 'json',
            success: function(response) {
                if (response.success) {
                            const successIcon = 'success';
                            const successTitle = 'Request Approved!';
                            const successColor = '#28a745';
                            
                    Swal.fire({
                                icon: successIcon,
                                title: successTitle,
                        text: response.message,
                                confirmButtonColor: successColor,
                        timer: 3000,
                        showConfirmButton: false
                    }).then(() => {
                        // Refresh the table
                                 $('#pendingRequestsTable').DataTable().ajax.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                                title: `${actionText} Failed`,
                        text: response.message,
                                confirmButtonColor: '#dc3545'
                    });
                }
            },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                            title: 'System Error',
                            text: `An error occurred while processing the ${actionText.toLowerCase()}. Please try again.`,
                            confirmButtonColor: '#dc3545'
                });
            }
        });
            }
        });
    }

    // Update status submission
    $('#updateStatus').click(function() {
        const requestId = $('#requestId').val();
        const status = $('input[name="requestStatus"]:checked').val();
        const notes = $('#statusNotes').val();

        if (status === 'approved') {
            // Get ingredients data from the table row
            const row = pendingTable.row(`[data-id="${requestId}"]`).data();
            if (row && row.ingredients_raw) {
                processRequestAction(requestId, row.ingredients_raw);
                return;
            }
        }

        // For other statuses, proceed with normal update
        $.ajax({
            url: 'update_ingredient_request.php',
            method: 'POST',
            data: {
                request_id: requestId,
                status: status,
                notes: notes
            },
            success: function(response) {
                console.log(response);
                if (response.success) {
                    $('#statusModal').modal('hide');
                    pendingTable.ajax.reload();
                    // Show success message using SweetAlert
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Request status updated successfully',
                        confirmButtonColor: '#8B4543'
                    });
            } else {
                    // Show error message using SweetAlert
                Swal.fire({
                    icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Error updating status',
                    confirmButtonColor: '#8B4543'
                });
            }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                // Show error message using SweetAlert
            Swal.fire({
                icon: 'error',
                    title: 'Error!',
                    text: 'Failed to update status. Please try again.',
                confirmButtonColor: '#8B4543'
            });
            }
        });
    });

    // Update delivery submission
    $('#updateDelivery').click(function() {
        const requestId = $('#deliveryRequestId').val();
        const deliveryStatus = $('#deliveryStatus').val();
        const deliveryDate = $('#deliveryDate').val();
        const deliveryNotes = $('#deliveryNotes').val();

        $.ajax({
            url: 'update_delivery_status.php',
            method: 'POST',
            data: {
                request_id: requestId,
                delivery_status: deliveryStatus,
                delivery_date: deliveryDate,
                delivery_notes: deliveryNotes
            },
            success: function(response) {
                console.log(response);
                if (response.success) {
                    $('#deliveryModal').modal('hide');
                    pendingTable.ajax.reload();
                    // Show success message using SweetAlert
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Delivery status updated successfully',
                        confirmButtonColor: '#8B4543'
                    });
                } else {
                    // Show error message using SweetAlert
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Error updating delivery status',
                        confirmButtonColor: '#8B4543'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                // Show error message using SweetAlert
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to update delivery status. Please try again.',
                    confirmButtonColor: '#8B4543'
                });
            }
        });
    });
});
</script>

<?php include('footer.php'); ?>