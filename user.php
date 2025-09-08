<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

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

/* Search and Length Menu */
.dataTables_wrapper {
    padding: 1.5rem;
}

.dataTables_length select {
    padding: 0.5rem 2.5rem 0.5rem 1rem;
    font-size: 0.875rem;
    border: 1px solid var(--border-color);
}

/* Enhanced User Modal Styles */
.enhanced-user-modal {
    border: none;
    border-radius: 1.5rem;
    box-shadow: 
        0 20px 60px rgba(0, 0, 0, 0.15),
        0 8px 25px rgba(139, 69, 67, 0.1),
        0 0 0 1px rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(20px);
    background: rgba(255, 255, 255, 0.95);
    animation: modalSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(-20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.enhanced-user-modal .modal-header {
    border-radius: 1.5rem 1.5rem 0 0;
    background: linear-gradient(135deg, #8B4543 0%, #723937 50%, #5a2e2c 100%);
    padding: 2rem 2rem 1.5rem;
    border: none;
    color: white;
    position: relative;
    overflow: hidden;
}

.enhanced-user-modal .modal-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="10" cy="60" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="90" cy="40" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
    pointer-events: none;
}

.enhanced-user-modal .user-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.25) 0%, rgba(255, 255, 255, 0.15) 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(15px);
    border: 2px solid rgba(255, 255, 255, 0.4);
    box-shadow: 
        0 8px 25px rgba(0, 0, 0, 0.2),
        inset 0 1px 0 rgba(255, 255, 255, 0.3);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    z-index: 2;
}

.enhanced-user-modal .user-icon:hover {
    transform: scale(1.1) rotate(5deg);
    box-shadow: 
        0 12px 35px rgba(0, 0, 0, 0.3),
        inset 0 1px 0 rgba(255, 255, 255, 0.4),
        0 0 20px rgba(255, 255, 255, 0.2);
}

.enhanced-user-modal .user-icon i {
    font-size: 1.5rem;
    color: white;
}

.enhanced-user-modal .modal-title {
    font-weight: 700;
    color: white;
    font-size: 1.6rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    position: relative;
    z-index: 2;
    margin-bottom: 0.25rem;
}

.enhanced-user-modal .modal-header small {
    font-size: 0.9rem;
    opacity: 0.9;
    font-weight: 400;
    position: relative;
    z-index: 2;
}

.enhanced-user-modal .btn-close {
    color: white !important;
    opacity: 1 !important;
    filter: brightness(1.2) !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    position: relative !important;
    z-index: 1 !important;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.25) 0%, rgba(255, 255, 255, 0.15) 100%) !important;
    border-radius: 50% !important;
    width: 48px !important;
    height: 48px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    backdrop-filter: blur(20px) !important;
    border: 2px solid rgba(255, 255, 255, 0.4) !important;
    box-shadow: 
        0 8px 25px rgba(0, 0, 0, 0.2),
        inset 0 1px 0 rgba(255, 255, 255, 0.3),
        0 0 0 1px rgba(255, 255, 255, 0.1) !important;
    padding: 0 !important;
    font-size: 1.2rem !important;
    line-height: 1 !important;
    overflow: visible !important;
    white-space: nowrap !important;
    text-indent: 0 !important;
}

.enhanced-user-modal .btn-close .close-x {
    color: white !important;
    font-size: 2.5rem !important;
    font-weight: 700 !important;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3) !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    line-height: 1 !important;
    display: block !important;
    position: relative !important;
    z-index: 10 !important;
    opacity: 1 !important;
    visibility: visible !important;
}

.enhanced-user-modal .btn-close::before,
.enhanced-user-modal .btn-close::after {
    display: none !important;
    content: none !important;
}

.enhanced-user-modal .btn-close * {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.enhanced-user-modal .btn-close:hover {
    transform: scale(1.15) !important;
    filter: brightness(1.8) !important;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.4) 0%, rgba(255, 255, 255, 0.25) 100%) !important;
    border-color: rgba(255, 255, 255, 0.7) !important;
    box-shadow: 
        0 12px 35px rgba(0, 0, 0, 0.3),
        inset 0 1px 0 rgba(255, 255, 255, 0.4),
        0 0 0 2px rgba(255, 255, 255, 0.2),
        0 0 20px rgba(255, 255, 255, 0.3) !important;
}

.enhanced-user-modal .btn-close:hover .close-x {
    transform: scale(1.2) !important;
    text-shadow: 
        0 0 15px rgba(255, 255, 255, 0.9),
        0 3px 8px rgba(0, 0, 0, 0.5),
        0 0 20px rgba(255, 255, 255, 0.6) !important;
}

.enhanced-user-modal .modal-body {
    padding: 2.5rem;
    background: linear-gradient(135deg, #fafbfc 0%, #f8f9fa 100%);
    position: relative;
}

.enhanced-user-modal .modal-body::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent 0%, rgba(139, 69, 67, 0.2) 50%, transparent 100%);
}

/* Profile Section */
.profile-section {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 1.5rem;
    padding: 2.5rem;
    margin-bottom: 2.5rem;
    border: 1px solid rgba(139, 69, 67, 0.08);
    box-shadow: 
        0 8px 25px rgba(0, 0, 0, 0.08),
        0 2px 8px rgba(139, 69, 67, 0.05);
    position: relative;
    overflow: hidden;
}

.profile-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #8B4543 0%, #723937 50%, #8B4543 100%);
}

.profile-image-container {
    position: relative;
    display: inline-block;
    margin-bottom: 1rem;
}

.profile-image {
    width: 160px;
    height: 160px;
    border-radius: 50%;
    object-fit: cover;
    border: 5px solid white;
    box-shadow: 
        0 12px 35px rgba(0, 0, 0, 0.15),
        0 4px 15px rgba(139, 69, 67, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.8);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    z-index: 2;
}

.profile-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(139, 69, 67, 0.8);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.3s ease;
}

.profile-overlay i {
    color: white;
    font-size: 2rem;
}

.profile-image-container:hover .profile-overlay {
    opacity: 1;
}

.profile-image-container:hover .profile-image {
    transform: scale(1.05);
}

.user-name {
    color: var(--text-dark);
    font-weight: 700;
    font-size: 1.8rem;
    margin-bottom: 1rem;
    text-transform: capitalize;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    position: relative;
    z-index: 2;
}

.user-type-badge {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 2.5rem;
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    box-shadow: 
        0 4px 15px rgba(139, 69, 67, 0.3),
        0 2px 8px rgba(0, 0, 0, 0.1);
    position: relative;
    z-index: 2;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.user-type-badge:hover {
    transform: translateY(-2px);
    box-shadow: 
        0 6px 20px rgba(139, 69, 67, 0.4),
        0 4px 12px rgba(0, 0, 0, 0.15);
}

/* User type specific styling */
.user-type-badge[data-type="Cashier"] {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
}

.user-type-badge[data-type="Stockman"] {
    background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
}

.user-type-badge[data-type="Admin"] {
    background: linear-gradient(135deg, #6f42c1 0%, #8e44ad 100%);
}

/* User Information Grid */
.user-info-grid {
    display: flex;
    flex-direction: column;
    gap: 2.5rem;
    animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) 0.2s both;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Keyboard hints styling */
.keyboard-hints {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.keyboard-hints small {
    color: #6c757d;
    font-size: 0.85rem;
    font-weight: 500;
}

.keyboard-hints i {
    color: #8B4543;
    opacity: 0.7;
}

.info-section {
    background: linear-gradient(135deg, #ffffff 0%, #fafbfc 100%);
    border-radius: 1.25rem;
    padding: 2rem;
    border: 1px solid rgba(139, 69, 67, 0.08);
    box-shadow: 
        0 8px 25px rgba(0, 0, 0, 0.06),
        0 2px 8px rgba(139, 69, 67, 0.04);
    position: relative;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.info-section:hover {
    transform: translateY(-2px);
    box-shadow: 
        0 12px 35px rgba(0, 0, 0, 0.1),
        0 4px 15px rgba(139, 69, 67, 0.08);
}

.info-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #8B4543 0%, #723937 50%, #8B4543 100%);
    opacity: 0.8;
}

.section-title {
    color: #8B4543;
    font-weight: 700;
    font-size: 1.2rem;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    border-bottom: 2px solid rgba(139, 69, 67, 0.15);
    padding-bottom: 0.75rem;
    position: relative;
    z-index: 2;
}

.section-title i {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-right: 0.75rem;
    font-size: 1.1em;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
}

/* Status badge styling */
.info-value .badge {
    font-size: 0.8rem;
    font-weight: 600;
    padding: 0.5rem 1rem;
    border-radius: 2rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-value .badge.bg-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}

.info-value .badge.bg-danger {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
}

/* Responsive improvements */
@media (max-width: 768px) {
    .enhanced-user-modal .modal-dialog {
        margin: 1rem;
    }
    
    .enhanced-user-modal .modal-body {
        padding: 1.5rem;
    }
    
    .profile-section {
        padding: 1.5rem;
    }
    
    .profile-image {
        width: 120px;
        height: 120px;
    }
    
    .user-name {
        font-size: 1.5rem;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .info-section {
        padding: 1.5rem;
    }
}

.info-item {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 1rem;
    padding: 1.25rem;
    border-left: 4px solid #8B4543;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 
        0 2px 8px rgba(0, 0, 0, 0.04),
        0 1px 3px rgba(139, 69, 67, 0.05);
    position: relative;
    overflow: hidden;
}

.info-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(180deg, #8B4543 0%, #723937 100%);
    transition: all 0.3s ease;
}

.info-item:hover {
    background: linear-gradient(135deg, #ffffff 0%, #f0f2f5 100%);
    transform: translateX(8px) translateY(-2px);
    box-shadow: 
        0 8px 25px rgba(0, 0, 0, 0.08),
        0 4px 15px rgba(139, 69, 67, 0.1);
}

.info-item:hover::before {
    width: 6px;
    background: linear-gradient(180deg, #723937 0%, #8B4543 100%);
}

.info-label {
    color: #8B4543;
    font-weight: 700;
    font-size: 0.875rem;
    margin-bottom: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    display: flex;
    align-items: center;
    position: relative;
    z-index: 2;
}

.info-label i {
    margin-right: 0.5rem;
    font-size: 0.9em;
    opacity: 0.8;
}

.info-value {
    color: #2c3e50;
    font-size: 1.05rem;
    font-weight: 600;
    word-break: break-word;
    line-height: 1.4;
    position: relative;
    z-index: 2;
}

/* Modal Footer */
.enhanced-user-modal .modal-footer {
    padding: 2rem;
    border-top: 1px solid rgba(139, 69, 67, 0.1);
    background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f4 100%);
    border-radius: 0 0 1.5rem 1.5rem;
    position: relative;
}

.enhanced-user-modal .modal-footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent 0%, rgba(139, 69, 67, 0.2) 50%, transparent 100%);
}

.enhanced-user-modal .btn {
    padding: 0.6875rem 1.5rem;
    font-size: 0.9375rem;
    border-radius: 0.5rem;
    transition: all 0.2s ease-in-out;
}

.enhanced-user-modal .btn-primary {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    border: none;
    padding: 0.875rem 2rem;
    font-weight: 600;
    font-size: 1rem;
    border-radius: 0.75rem;
    box-shadow: 
        0 4px 15px rgba(139, 69, 67, 0.3),
        0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.enhanced-user-modal .btn-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.enhanced-user-modal .btn-primary:hover {
    background: linear-gradient(135deg, #723937 0%, #5a2e2c 100%);
    transform: translateY(-3px);
    box-shadow: 
        0 8px 25px rgba(139, 69, 67, 0.4),
        0 4px 15px rgba(0, 0, 0, 0.15);
}

.enhanced-user-modal .btn-primary:hover::before {
    left: 100%;
}

/* Enhanced Edit User Modal Styles */
.enhanced-edit-user-modal {
    border: none;
    border-radius: 1.5rem;
    box-shadow: 
        0 25px 80px rgba(0, 0, 0, 0.15),
        0 10px 30px rgba(139, 69, 67, 0.1),
        0 0 0 1px rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(20px);
    background: rgba(255, 255, 255, 0.95);
    animation: modalSlideIn 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    max-width: 90vw;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(-30px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.enhanced-edit-user-modal .modal-header {
    border-radius: 1.5rem 1.5rem 0 0;
    background: linear-gradient(135deg, #8B4543 0%, #723937 50%, #5a2e2c 100%);
    padding: 2rem 2rem 1.5rem;
    border: none;
    color: white;
    position: relative;
    overflow: hidden;
}

.enhanced-edit-user-modal .modal-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="10" cy="60" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="90" cy="40" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
    pointer-events: none;
}

.enhanced-edit-user-modal .edit-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.25) 0%, rgba(255, 255, 255, 0.15) 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(15px);
    border: 2px solid rgba(255, 255, 255, 0.4);
    box-shadow: 
        0 8px 25px rgba(0, 0, 0, 0.2),
        inset 0 1px 0 rgba(255, 255, 255, 0.3);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    z-index: 2;
}

.enhanced-edit-user-modal .edit-icon:hover {
    transform: scale(1.1) rotate(5deg);
    box-shadow: 
        0 12px 35px rgba(0, 0, 0, 0.3),
        inset 0 1px 0 rgba(255, 255, 255, 0.4),
        0 0 20px rgba(255, 255, 255, 0.2);
}

.enhanced-edit-user-modal .edit-icon i {
    font-size: 1.6rem;
    color: white;
    transition: all 0.3s ease;
}

.enhanced-edit-user-modal .modal-title {
    font-weight: 700;
    color: white;
    font-size: 1.6rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    position: relative;
    z-index: 2;
    margin-bottom: 0.25rem;
}

.enhanced-edit-user-modal .modal-header small {
    font-size: 0.9rem;
    opacity: 0.9;
    font-weight: 400;
    position: relative;
    z-index: 2;
}

.enhanced-edit-user-modal .btn-close {
    color: white !important;
    opacity: 1 !important;
    filter: brightness(1.2) !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    position: relative !important;
    z-index: 1 !important;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.25) 0%, rgba(255, 255, 255, 0.15) 100%) !important;
    border-radius: 50% !important;
    width: 48px !important;
    height: 48px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    backdrop-filter: blur(20px) !important;
    border: 2px solid rgba(255, 255, 255, 0.4) !important;
    box-shadow: 
        0 8px 25px rgba(0, 0, 0, 0.2),
        inset 0 1px 0 rgba(255, 255, 255, 0.3),
        0 0 0 1px rgba(255, 255, 255, 0.1) !important;
    padding: 0 !important;
    font-size: 1.2rem !important;
    line-height: 1 !important;
    overflow: visible !important;
    white-space: nowrap !important;
    text-indent: 0 !important;
}

.enhanced-edit-user-modal .btn-close .close-x {
    color: white !important;
    font-size: 2.5rem !important;
    font-weight: 700 !important;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3) !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    line-height: 1 !important;
    display: block !important;
    position: relative !important;
    z-index: 10 !important;
    opacity: 1 !important;
    visibility: visible !important;
}

.enhanced-edit-user-modal .btn-close::before,
.enhanced-edit-user-modal .btn-close::after {
    display: none !important;
    content: none !important;
}

.enhanced-edit-user-modal .btn-close * {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.enhanced-edit-user-modal .btn-close:hover {
    transform: scale(1.15) !important;
    filter: brightness(1.8) !important;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.4) 0%, rgba(255, 255, 255, 0.25) 100%) !important;
    border-color: rgba(255, 255, 255, 0.7) !important;
    box-shadow: 
        0 12px 35px rgba(0, 0, 0, 0.3),
        inset 0 1px 0 rgba(255, 255, 255, 0.4),
        0 0 0 2px rgba(255, 255, 255, 0.2),
        0 0 20px rgba(255, 255, 255, 0.3) !important;
}

.enhanced-edit-user-modal .btn-close:hover .close-x {
    transform: scale(1.2) !important;
    text-shadow: 
        0 0 15px rgba(255, 255, 255, 0.9),
        0 3px 8px rgba(0, 0, 0, 0.5),
        0 0 20px rgba(255, 255, 255, 0.6) !important;
}

.enhanced-edit-user-modal .modal-body {
    padding: 2.5rem;
    background: linear-gradient(135deg, #fafbfc 0%, #f8f9fa 100%);
    position: relative;
}

.enhanced-edit-user-modal .modal-body::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent 0%, rgba(139, 69, 67, 0.2) 50%, transparent 100%);
}

/* Form Sections */
.enhanced-edit-user-modal .form-section {
    background: linear-gradient(135deg, #ffffff 0%, #fafbfc 100%);
    border-radius: 1.25rem;
    padding: 2rem;
    border: 1px solid rgba(139, 69, 67, 0.08);
    box-shadow: 
        0 8px 25px rgba(0, 0, 0, 0.06),
        0 2px 8px rgba(139, 69, 67, 0.04);
    height: 100%;
    position: relative;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.enhanced-edit-user-modal .form-section:hover {
    transform: translateY(-2px);
    box-shadow: 
        0 12px 35px rgba(0, 0, 0, 0.1),
        0 4px 15px rgba(139, 69, 67, 0.08);
}

.enhanced-edit-user-modal .form-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #8B4543 0%, #723937 50%, #8B4543 100%);
    opacity: 0.8;
}

/* Profile Upload Section */
.profile-upload-section {
    text-align: center;
    margin-bottom: 2rem;
}

.current-profile {
    position: relative;
    display: inline-block;
    cursor: pointer;
    border-radius: 50%;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.current-profile-img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid white;
    box-shadow: 
        0 8px 25px rgba(0, 0, 0, 0.15),
        0 4px 15px rgba(139, 69, 67, 0.1);
    transition: all 0.3s ease;
}

.upload-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(139, 69, 67, 0.9) 0%, rgba(114, 57, 55, 0.9) 100%);
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.3s ease;
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
}

.upload-overlay i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.current-profile:hover .upload-overlay {
    opacity: 1;
}

.current-profile:hover .current-profile-img {
    transform: scale(1.05);
}

/* Enhanced Form Controls */
.enhanced-edit-user-modal .form-label {
    color: #8B4543;
    font-weight: 700;
    font-size: 0.95rem;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.enhanced-edit-user-modal .form-label i {
    margin-right: 0.5rem;
    font-size: 0.9em;
    opacity: 0.8;
}

.enhanced-edit-user-modal .form-control,
.enhanced-edit-user-modal .form-select {
    border: 2px solid rgba(139, 69, 67, 0.1);
    border-radius: 0.75rem;
    padding: 0.875rem 1rem;
    font-size: 1rem;
    font-weight: 500;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 
        0 2px 8px rgba(0, 0, 0, 0.04),
        inset 0 1px 0 rgba(255, 255, 255, 0.8);
}

.enhanced-edit-user-modal .form-control:focus,
.enhanced-edit-user-modal .form-select:focus {
    border-color: #8B4543;
    box-shadow: 
        0 0 0 0.25rem rgba(139, 69, 67, 0.15),
        0 4px 15px rgba(139, 69, 67, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.9);
    transform: translateY(-1px);
}

.enhanced-edit-user-modal .form-control:hover,
.enhanced-edit-user-modal .form-select:hover {
    border-color: rgba(139, 69, 67, 0.3);
    transform: translateY(-1px);
    box-shadow: 
        0 4px 15px rgba(0, 0, 0, 0.08),
        0 2px 8px rgba(139, 69, 67, 0.05);
}

.enhanced-edit-user-modal .form-text {
    color: #6c757d;
    font-size: 0.85rem;
    font-weight: 500;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    opacity: 0.8;
}

.enhanced-edit-user-modal .form-text i {
    margin-right: 0.5rem;
    color: #8B4543;
    font-size: 0.9em;
}

.enhanced-edit-user-modal .section-title {
    color: #8B4543;
    font-weight: 700;
    font-size: 1.2rem;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    border-bottom: 2px solid rgba(139, 69, 67, 0.15);
    padding-bottom: 0.75rem;
    position: relative;
    z-index: 2;
}

.enhanced-edit-user-modal .section-title i {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-right: 0.75rem;
    font-size: 1.1em;
}

/* Enhanced Button Styling */
.enhanced-edit-user-modal .btn {
    padding: 0.875rem 2rem;
    font-weight: 600;
    font-size: 1rem;
    border-radius: 0.75rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    border: none;
}

.enhanced-edit-user-modal .btn-primary {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    box-shadow: 
        0 4px 15px rgba(139, 69, 67, 0.3),
        0 2px 8px rgba(0, 0, 0, 0.1);
}

.enhanced-edit-user-modal .btn-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.enhanced-edit-user-modal .btn-primary:hover {
    background: linear-gradient(135deg, #723937 0%, #5a2e2c 100%);
    transform: translateY(-3px);
    box-shadow: 
        0 8px 25px rgba(139, 69, 67, 0.4),
        0 4px 15px rgba(0, 0, 0, 0.15);
}

.enhanced-edit-user-modal .btn-primary:hover::before {
    left: 100%;
}

.enhanced-edit-user-modal .btn-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white !important;
    box-shadow: 
        0 4px 15px rgba(108, 117, 125, 0.3),
        0 2px 8px rgba(0, 0, 0, 0.1);
}

.enhanced-edit-user-modal .btn-secondary:hover {
    background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
    color: white !important;
    transform: translateY(-3px);
    box-shadow: 
        0 8px 25px rgba(108, 117, 125, 0.4),
        0 4px 15px rgba(0, 0, 0, 0.15);
}

.enhanced-edit-user-modal .btn-secondary i {
    color: white !important;
}

/* Enhanced Modal Footer */
.enhanced-edit-user-modal .modal-footer {
    padding: 2rem;
    border-top: 1px solid rgba(139, 69, 67, 0.1);
    background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f4 100%);
    border-radius: 0 0 1.5rem 1.5rem;
    position: relative;
}

.enhanced-edit-user-modal .modal-footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent 0%, rgba(139, 69, 67, 0.2) 50%, transparent 100%);
}

/* Responsive Design */
@media (max-width: 1200px) {
    .enhanced-edit-user-modal {
        max-width: 95vw;
    }
}

@media (max-width: 768px) {
    .enhanced-edit-user-modal .modal-dialog {
        margin: 1rem;
        max-width: calc(100vw - 2rem);
    }
    
    .enhanced-edit-user-modal .modal-body {
        padding: 1.5rem;
    }
    
    .enhanced-edit-user-modal .form-section {
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .current-profile-img {
        width: 100px;
        height: 100px;
    }
    
    .enhanced-edit-user-modal .form-control,
    .enhanced-edit-user-modal .form-select {
        font-size: 0.95rem;
        padding: 0.75rem 0.875rem;
    }
    
    .enhanced-edit-user-modal .btn {
        padding: 0.75rem 1.5rem;
        font-size: 0.95rem;
    }
    
    .enhanced-edit-user-modal .modal-footer {
        padding: 1.5rem;
        flex-direction: column;
        gap: 1rem;
    }
    
    .enhanced-edit-user-modal .modal-footer .d-flex {
        flex-direction: column;
        gap: 1rem;
        width: 100%;
    }
    
    .enhanced-edit-user-modal .keyboard-hints {
        text-align: center;
    }
}

@media (max-width: 576px) {
    .enhanced-edit-user-modal .modal-header {
        padding: 1.5rem 1.5rem 1rem;
    }
    
    .enhanced-edit-user-modal .edit-icon {
        width: 48px;
        height: 48px;
    }
    
    .enhanced-edit-user-modal .modal-title {
        font-size: 1.4rem;
    }
    
    .enhanced-edit-user-modal .modal-body {
        padding: 1rem;
    }
    
    .enhanced-edit-user-modal .form-section {
        padding: 1rem;
    }
    
    .current-profile-img {
        width: 80px;
        height: 80px;
    }
    
    .upload-overlay {
        font-size: 0.8rem;
    }
    
    .upload-overlay i {
        font-size: 1.2rem;
    }
}

/* Enhanced Input Group Styling */
.enhanced-edit-user-modal .input-group {
    box-shadow: 
        0 2px 8px rgba(0, 0, 0, 0.04),
        inset 0 1px 0 rgba(255, 255, 255, 0.8);
    border-radius: 0.75rem;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.enhanced-edit-user-modal .input-group:focus-within {
    box-shadow: 
        0 0 0 0.25rem rgba(139, 69, 67, 0.15),
        0 4px 15px rgba(139, 69, 67, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.9);
    transform: translateY(-1px);
}

.enhanced-edit-user-modal .input-group .btn-outline-secondary {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 2px solid rgba(139, 69, 67, 0.1);
    border-left: none;
    color: #8B4543;
    font-weight: 600;
    transition: all 0.3s ease;
}

.enhanced-edit-user-modal .input-group .btn-outline-secondary:hover {
    background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
    color: #723937;
    transform: scale(1.05);
}

/* Form Animation */
.enhanced-edit-user-modal .form-section {
    animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) 0.3s both;
}

.enhanced-edit-user-modal .form-section:nth-child(2) {
    animation-delay: 0.4s;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Loading States */
.enhanced-edit-user-modal .form-control:disabled,
.enhanced-edit-user-modal .form-select:disabled {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    opacity: 0.7;
    cursor: not-allowed;
}

.enhanced-edit-user-modal .btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

/* Success/Error States */
.enhanced-edit-user-modal .form-control.is-valid {
    border-color: #28a745;
    box-shadow: 
        0 0 0 0.25rem rgba(40, 167, 69, 0.15),
        0 4px 15px rgba(40, 167, 69, 0.1);
}

.enhanced-edit-user-modal .form-control.is-invalid {
    border-color: #dc3545;
    box-shadow: 
        0 0 0 0.25rem rgba(220, 53, 69, 0.15),
        0 4px 15px rgba(220, 53, 69, 0.1);
}

/* Enhanced Textarea */
.enhanced-edit-user-modal textarea.form-control {
    resize: vertical;
    min-height: 100px;
    line-height: 1.5;
}

/* Enhanced Select Options */
.enhanced-edit-user-modal .form-select option {
    padding: 0.5rem;
    font-weight: 500;
}

/* Focus Management */
.enhanced-edit-user-modal .form-control:focus,
.enhanced-edit-user-modal .form-select:focus {
    outline: none;
}

/* Accessibility Improvements */
.enhanced-edit-user-modal .form-label:focus-within {
    color: #723937;
}

.enhanced-edit-user-modal .form-control:focus + .form-text {
    color: #8B4543;
    opacity: 1;
}

/* Profile Upload Section */
.profile-upload-section {
    text-align: center;
}

.current-profile {
    position: relative;
    display: inline-block;
    cursor: pointer;
    border-radius: 50%;
    overflow: hidden;
    transition: all 0.3s ease;
}

.current-profile-img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid white;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
}

.upload-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(139, 69, 67, 0.9);
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.3s ease;
    color: white;
}

.upload-overlay i {
    font-size: 1.5rem;
    margin-bottom: 0.25rem;
}

.upload-overlay span {
    font-size: 0.75rem;
    font-weight: 500;
}

.current-profile:hover .upload-overlay {
    opacity: 1;
}

.current-profile:hover .current-profile-img {
    transform: scale(1.05);
}

/* Form Controls */
.enhanced-edit-user-modal .form-control,
.enhanced-edit-user-modal .form-select {
    padding: 0.75rem 1rem;
    font-size: 0.9375rem;
    border-radius: 0.5rem;
    border: 1px solid #d9dee3;
    transition: all 0.2s ease-in-out;
}

.enhanced-edit-user-modal .form-control:focus,
.enhanced-edit-user-modal .form-select:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.25rem rgba(139, 69, 67, 0.25);
}

.enhanced-edit-user-modal .form-control-lg,
.enhanced-edit-user-modal .form-select-lg {
    padding: 1rem 1.25rem;
    font-size: 1rem;
}

.enhanced-edit-user-modal .form-label {
    font-weight: 500;
    color: #566a7f;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
}

.enhanced-edit-user-modal .form-label i {
    margin-right: 0.5rem;
    color: var(--primary-color);
}

.enhanced-edit-user-modal .form-text {
    color: #6c757d;
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: flex;
    align-items: center;
}

.enhanced-edit-user-modal .form-text i {
    margin-right: 0.25rem;
    color: var(--primary-color);
}

/* Input Group */
.enhanced-edit-user-modal .input-group .btn {
    border-color: #d9dee3;
    color: #6c757d;
}

.enhanced-edit-user-modal .input-group .btn:hover {
    background-color: #e9ecef;
    border-color: #d9dee3;
    color: #495057;
}

/* Modal Footer */
.enhanced-edit-user-modal .modal-footer {
    padding: 1.5rem;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
    background-color: #f8f9fa;
    border-radius: 0 0 1rem 1rem;
}

.enhanced-edit-user-modal .btn {
    padding: 0.6875rem 1.5rem;
    font-size: 0.9375rem;
    border-radius: 0.5rem;
    transition: all 0.2s ease-in-out;
}

.enhanced-edit-user-modal .btn-primary {
    background-color: #8B4543;
    border-color: #8B4543;
}

.enhanced-edit-user-modal .btn-primary:hover {
    background-color: #723937;
    border-color: #723937;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(139, 69, 67, 0.3);
}

.enhanced-edit-user-modal .btn-secondary {
    color: #566a7f;
    background-color: #f8f9fa;
    border-color: #d9dee3;
}

.enhanced-edit-user-modal .btn-secondary:hover {
    background-color: #e9ecef;
    border-color: #d9dee3;
    color: #566a7f;
}
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

.dataTables_filter input {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    background-color: white;
    min-width: 300px;
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

/* SweetAlert2 Custom Styles */
.swal2-popup {
    border-radius: 1rem;
    padding: 2rem;
}

.swal2-title {
    color: var(--text-dark);
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.swal2-html-container {
    color: #6c757d;
    font-size: 1rem;
    margin: 1rem 0;
}

.swal2-icon {
    border-color: var(--warning-color);
    color: var(--warning-color);
}

.swal2-confirm {
    background-color: var(--danger-color) !important;
    border-radius: 0.75rem !important;
    padding: 0.75rem 1.5rem !important;
    font-size: 1rem !important;
    font-weight: 500 !important;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(179, 58, 58, 0.15) !important;
}

.swal2-confirm:focus {
    box-shadow: 0 0 0 0.25rem rgba(179, 58, 58, 0.25) !important;
}

.swal2-cancel {
    background-color: #f8f9fa !important;
    color: var(--text-dark) !important;
    border-radius: 0.75rem !important;
    padding: 0.75rem 1.5rem !important;
    font-size: 1rem !important;
    font-weight: 500 !important;
    margin-right: 0.5rem !important;
}

.swal2-cancel:hover {
    background-color: #e9ecef !important;
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

    /* Normal Archive Modal Styles */
    .normal-archive-modal {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
        overflow: hidden;
        animation: modalSlideIn 0.3s ease-out;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: scale(0.95) translateY(-20px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .normal-archive-header {
        background: linear-gradient(135deg, #8B4543 0%, #b97a6a 50%, #d4a574 100%);
        border: none;
        padding: 1.5rem 2rem;
        position: relative;
        overflow: hidden;
    }

    .normal-archive-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="10" cy="60" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="90" cy="40" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        opacity: 0.2;
    }

    .archive-icon-container {
        width: 45px;
        height: 45px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .archive-icon-container i {
        font-size: 1.25rem;
        color: white;
    }

    .normal-archive-body {
        padding: 2rem;
        background: #ffffff;
    }

    .archive-icon-normal {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #8B4543, #b97a6a);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        box-shadow: 0 8px 25px rgba(139, 69, 67, 0.3);
    }

    .archive-icon-normal i {
        font-size: 2rem;
        color: white;
    }

    .archive-title {
        color: #2c3e50;
        font-weight: 600;
        font-size: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .archive-info-normal {
        background: #f8f9fa;
        border-radius: 0.75rem;
        padding: 1.5rem;
        border: 1px solid rgba(139, 69, 67, 0.1);
        margin-top: 1rem;
    }

    .info-item-normal {
        display: flex;
        align-items: center;
        padding: 0.5rem 0;
        font-weight: 500;
        color: #495057;
        font-size: 1rem;
    }

    .info-item-normal i {
        color: #8B4543;
        width: 20px;
    }

    .normal-archive-footer {
        background: #f8f9fa;
        border: none;
        padding: 1.5rem 2rem;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
    }

    .normal-archive-footer .btn {
        border-radius: 0.5rem;
        font-weight: 500;
        transition: all 0.2s ease;
        border: none;
        padding: 0.5rem 1.5rem;
    }

    .normal-archive-footer .btn-secondary {
        background: linear-gradient(135deg, #dc3545, #c82333);
        color: white;
    }

    .normal-archive-footer .btn-secondary:hover {
        background: linear-gradient(135deg, #c82333, #bd2130);
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
    }

    .btn-archive-confirm {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        border: none;
    }

    .btn-archive-confirm:hover {
        background: linear-gradient(135deg, #218838, #1e7e34);
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
    }
</style>

<div class="container-fluid px-4">
    <h1 class="section-title"><span class="section-icon"><i class="fas fa-users"></i></span>User Management</h1>
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <div>
                        <i class="fas fa-users me-1"></i>
                        User List
                    </div>
                </div>
                <div class="card-body">
                    <table id="userTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>NAME</th>
                                <th>EMAIL</th>
                                <th>TYPE</th>
                                <th>STATUS</th>
                                <th>ACTION</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced User Details Modal -->
<div class="modal fade" id="userDetailsModal" tabindex="-1" aria-labelledby="userDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content enhanced-user-modal">
            <!-- Enhanced Modal Header -->
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <div class="user-icon me-3">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="userDetailsModalLabel">User Details</h5>
                        <small class="text-light opacity-75">View comprehensive user information</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
                    <span class="close-x">×</span>
                </button>
            </div>
            
            <!-- Enhanced Modal Body -->
            <div class="modal-body">
                <!-- Profile Section -->
                <div class="profile-section text-center mb-4">
                    <div class="profile-image-container">
                        <img id="userProfileImage" src="" alt="Profile Image" class="profile-image">
                        <div class="profile-overlay">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                    <h4 class="user-name mt-3" id="userName"></h4>
                    <span class="user-type-badge" id="userType"></span>
                </div>

                <!-- User Information Grid -->
                <div class="user-info-grid">
                    <div class="info-section">
                        <h6 class="section-title">
                            <i class="fas fa-info-circle me-2"></i>Basic Information
                        </h6>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-envelope me-1"></i>Email
                                </div>
                                <div class="info-value" id="userEmail"></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-phone me-1"></i>Contact
                                </div>
                                <div class="info-value" id="userContact"></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-calendar me-1"></i>Created At
                                </div>
                                <div class="info-value" id="userCreatedAt"></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-toggle-on me-1"></i>Status
                                </div>
                                <div class="info-value" id="userStatus"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Cashier Details Section -->
                    <div class="info-section" id="cashierDetails" style="display: none;">
                        <h6 class="section-title">
                            <i class="fas fa-id-badge me-2"></i>Cashier Details
                        </h6>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-id-card me-1"></i>Employee ID
                                </div>
                                <div class="info-value" id="employeeId"></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-building me-1"></i>Branch
                                </div>
                                <div class="info-value" id="branchName"></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-calendar-check me-1"></i>Date Hired
                                </div>
                                <div class="info-value" id="dateHired"></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-phone-alt me-1"></i>Emergency Contact
                                </div>
                                <div class="info-value" id="emergencyContact"></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-phone-square me-1"></i>Emergency Number
                                </div>
                                <div class="info-value" id="emergencyNumber"></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-map-marker-alt me-1"></i>Address
                                </div>
                                <div class="info-value" id="address"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Stockman Details Section -->
                    <div class="info-section" id="stockmanDetails" style="display: none;">
                        <h6 class="section-title">
                            <i class="fas fa-boxes me-2"></i>Stockman Details
                        </h6>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-id-card me-1"></i>Employee ID
                                </div>
                                <div class="info-value" id="stockmanEmployeeId"></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-building me-1"></i>Branch
                                </div>
                                <div class="info-value" id="stockmanBranchName"></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-calendar-check me-1"></i>Date Hired
                                </div>
                                <div class="info-value" id="stockmanDateHired"></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-phone-alt me-1"></i>Emergency Contact
                                </div>
                                <div class="info-value" id="stockmanEmergencyContact"></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-phone-square me-1"></i>Emergency Number
                                </div>
                                <div class="info-value" id="stockmanEmergencyNumber"></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-map-marker-alt me-1"></i>Address
                                </div>
                                <div class="info-value" id="stockmanAddress"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Modal Footer -->
            <div class="modal-footer">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div class="keyboard-hints">
                        <small class="text-muted">
                            <i class="fas fa-keyboard me-1"></i>
                            Esc to close
                        </small>
                    </div>
                    <button type="button" class="btn btn-primary btn-lg" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content enhanced-edit-user-modal">
            <!-- Enhanced Modal Header -->
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <div class="edit-icon me-3">
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="editUserModalLabel">Edit User</h5>
                        <small class="text-light opacity-75">Update user information and settings</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
                    <span class="close-x">×</span>
                </button>
            </div>
            
            <!-- Enhanced Modal Body -->
            <div class="modal-body">
                <form id="editUserForm" enctype="multipart/form-data">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    
                    <div class="row g-4">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <div class="form-section">
                                <h6 class="section-title">
                                    <i class="fas fa-user me-2"></i>Basic Information
                                </h6>
                                
                                <!-- Profile Image Upload -->
                                <div class="profile-upload-section mb-4">
                                    <div class="current-profile">
                                        <img id="editProfileImage" src="" alt="Current Profile" class="current-profile-img">
                                        <div class="upload-overlay">
                                            <i class="fas fa-camera"></i>
                                            <span>Change Photo</span>
                                        </div>
                                    </div>
                                    <input type="file" id="profileImageInput" name="profile_image" accept="image/*" class="d-none">
                                </div>
                                
                                <div class="mb-4">
                                    <label for="edit_user_name" class="form-label">
                                        <i class="fas fa-user me-1"></i>Full Name
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="edit_user_name" name="user_name" required>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Enter the user's full name
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="edit_user_email" class="form-label">
                                        <i class="fas fa-envelope me-1"></i>Email Address
                                    </label>
                                    <input type="email" class="form-control form-control-lg" id="edit_user_email" name="user_email" required>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Enter a valid email address
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="edit_contact_number" class="form-label">
                                        <i class="fas fa-phone me-1"></i>Contact Number
                                    </label>
                                    <input type="tel" class="form-control form-control-lg" id="edit_contact_number" name="contact_number">
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Enter contact number (optional)
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="edit_branch_id" class="form-label">
                                        <i class="fas fa-building me-1"></i>Branch Assignment
                                    </label>
                                    <select class="form-select form-select-lg" id="edit_branch_id" name="branch_id">
                                        <option value="">No Branch Assigned</option>
                                        <?php 
                                        // Fetch active branches for the dropdown
                                        $branchStmt = $pdo->query("SELECT branch_id, branch_name, branch_code FROM pos_branch WHERE status = 'Active' ORDER BY branch_name");
                                        $branches = $branchStmt->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($branches as $branch): 
                                        ?>
                                            <option value="<?php echo $branch['branch_id']; ?>">
                                                <?php echo htmlspecialchars($branch['branch_name'] . ' (' . $branch['branch_code'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Change or assign the user's branch. Leave empty to remove branch assignment.
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Column -->
                        <div class="col-md-6">
                            <div class="form-section">
                                <h6 class="section-title">
                                    <i class="fas fa-cog me-2"></i>Account Settings
                                </h6>
                                
                                <div class="mb-4">
                                    <label for="edit_user_type" class="form-label">
                                        <i class="fas fa-user-tag me-1"></i>User Type
                                    </label>
                                    <select class="form-select form-select-lg" id="edit_user_type" name="user_type" required>
                                        <option value="Admin">👑 Admin</option>
                                        <option value="Cashier">💼 Cashier</option>
                                        <option value="Stockman">📦 Stockman</option>
                                    </select>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Select the user's role in the system
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="edit_user_status" class="form-label">
                                        <i class="fas fa-toggle-on me-1"></i>Status
                                    </label>
                                    <select class="form-select form-select-lg" id="edit_user_status" name="user_status" required>
                                        <option value="Active">🟢 Active</option>
                                        <option value="Inactive">🔴 Inactive</option>
                                    </select>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Active users can access the system
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="edit_user_password" class="form-label">
                                        <i class="fas fa-lock me-1"></i>New Password
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-lg" id="edit_user_password" name="user_password">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Leave blank to keep current password
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="edit_address" class="form-label">
                                        <i class="fas fa-map-marker-alt me-1"></i>Address
                                    </label>
                                    <textarea class="form-control" id="edit_address" name="address" rows="3" placeholder="Enter user's address..."></textarea>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Enter the user's address (optional)
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Enhanced Modal Footer -->
            <div class="modal-footer">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div class="keyboard-hints">
                        <small class="text-muted">
                            <i class="fas fa-keyboard me-1"></i>
                            Ctrl+S to save • Esc to close
                        </small>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-primary btn-lg" id="saveEditUserButton">
                            <i class="fas fa-save me-2"></i>Update User
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Normal Archive Confirmation Modal -->
<div class="modal fade" id="archiveConfirmationModal" tabindex="-1" aria-labelledby="archiveConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content normal-archive-modal">
            <div class="modal-header normal-archive-header">
                <div class="d-flex align-items-center">
                    <div class="archive-icon-container me-3">
                        <i class="fas fa-archive"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0 text-white" id="archiveConfirmationModalLabel">Archive User</h5>
                        <small class="text-white opacity-75">Secure archiving process</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body normal-archive-body">
                <div class="text-center">
                    <div class="archive-icon-normal mb-4">
                        <i class="fas fa-archive"></i>
                    </div>
                    <h5 class="archive-title mb-3" id="archiveConfirmationMessage">Are you sure you want to archive this item?</h5>
                    <div class="archive-info-normal">
                        <div class="row text-start">
                            <div class="col-md-6">
                                <div class="info-item-normal">
                                    <i class="fas fa-shield-alt me-2"></i>
                                    <span>Data will be safely stored</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item-normal">
                                    <i class="fas fa-undo me-2"></i>
                                    <span>Can be restored anytime</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer normal-archive-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-archive-confirm" id="confirmArchiveBtn">
                    <i class="fas fa-archive me-2"></i>Archive
                </button>
            </div>
        </div>
    </div>
</div>

<?php
include('footer.php');
?>

<script>
function showFeedbackModal(type, title, text) {
  Swal.fire({
    icon: type,
    title: title,
    text: text,
    confirmButtonText: 'OK',
    customClass: { confirmButton: 'swal2-confirm-archive' },
    buttonsStyling: false
  });
}

// Archive confirmation modal function
function showArchiveConfirmationModal(title, message, confirmCallback) {
    $('#archiveConfirmationModalLabel').text(title);
    $('#archiveConfirmationMessage').text(message);
    
    // Remove any existing event handlers
    $('#confirmArchiveBtn').off('click');
    
    // Add new event handler
    $('#confirmArchiveBtn').on('click', function() {
        $('#archiveConfirmationModal').modal('hide');
        if (typeof confirmCallback === 'function') {
            confirmCallback();
        }
    });
    
    // Show the modal
    $('#archiveConfirmationModal').modal('show');
}

$(document).ready(function() {
    $('#userTable').DataTable({
        "processing": true,
        "serverSide": true,
        "pageLength": 5,
        "pagingType": "simple", // Show only Previous/Next buttons
        "lengthChange": false,
        "dom": 'ftip',
        "ajax": {
            "url": "user_ajax.php",
            "type": "GET"
        },
        "columns": [
            { "data": "user_name" },
            { "data": "user_email" },
            { "data": "user_type" },
            { 
                "data": null,
                "render": function(data, type, row){
                    if(row.user_status === 'Active'){
                        return `<span class="badge bg-success">Active</span>`;
                    } else {
                        return `<span class="badge bg-danger">Inactive</span>`;
                    }
                }
            },
            {
                "data": null,
                "render": function(data, type, row){
                    return `
                    <div class="text-center">
                        <button class="btn btn-info btn-sm view-details" data-id="${row.user_id}" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-warning btn-sm edit-user-btn" data-id="${row.user_id}" title="Edit User">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-secondary btn-sm archive-user-btn" data-id="${row.user_id}" title="Archive User">
                            <i class="fas fa-archive"></i>
                        </button>
                    </div>`;
                }
            }
        ]
    });

    // Handle View Details Button Click
    $(document).on('click', '.view-details', function() {
        let userId = $(this).data('id');
        $.ajax({
            url: 'get_user_details.php',
            type: 'GET',
            data: { id: userId },
            success: function(response) {
                if (response.success) {
                    const user = response.data;
                    
                    // Debug: Log user data to console
                    console.log('User data received:', user);
                    console.log('User type:', user.user_type);
                    console.log('Stockman details:', user.stockman_details);
                    
                    // Set profile image
                    $('#userProfileImage').attr('src', user.profile_image ? user.profile_image : 'uploads/profiles/default.png');
                    
                    // Set user details
                    $('#userName').text(user.user_name);
                    $('#userEmail').text(user.user_email);
                    $('#userType').text(user.user_type).attr('data-type', user.user_type);
                    $('#userContact').text(user.contact_number || 'N/A');
                    $('#userStatus').html(user.user_status === 'Active' ? 
                        '<span class="badge bg-success">Active</span>' : 
                        '<span class="badge bg-danger">Inactive</span>');
                    $('#userCreatedAt').text(user.created_at);

                    // Handle cashier details
                    if (user.user_type === 'Cashier' && user.cashier_details) {
                        $('#cashierDetails').show();
                        $('#stockmanDetails').hide();
                        $('#employeeId').text(user.cashier_details.employee_id);
                        $('#branchName').text(user.cashier_details.branch_name);
                        $('#dateHired').text(user.cashier_details.date_hired);
                        $('#emergencyContact').text(user.cashier_details.emergency_contact);
                        $('#emergencyNumber').text(user.cashier_details.emergency_number);
                        $('#address').text(user.cashier_details.address);
                    } else {
                        $('#cashierDetails').hide();
                    }

                    // Handle stockman details
                    if (user.user_type === 'Stockman') {
                        $('#stockmanDetails').show();
                        $('#cashierDetails').hide();
                        
                        if (user.stockman_details) {
                            $('#stockmanEmployeeId').text(user.stockman_details.employee_id || 'N/A');
                            $('#stockmanBranchName').text(user.stockman_details.branch_name || 'N/A');
                            $('#stockmanDateHired').text(user.stockman_details.date_hired || 'N/A');
                            $('#stockmanEmergencyContact').text(user.stockman_details.emergency_contact || 'N/A');
                            $('#stockmanEmergencyNumber').text(user.stockman_details.emergency_number || 'N/A');
                            $('#stockmanAddress').text(user.stockman_details.address || 'N/A');
                        } else {
                            // If no stockman details, show default values
                            $('#stockmanEmployeeId').text('N/A');
                            $('#stockmanBranchName').text('N/A');
                            $('#stockmanDateHired').text('N/A');
                            $('#stockmanEmergencyContact').text('N/A');
                            $('#stockmanEmergencyNumber').text('N/A');
                            $('#stockmanAddress').text('N/A');
                        }
                    } else {
                        $('#stockmanDetails').hide();
                    }

                    // Show modal
                    $('#userDetailsModal').modal('show');
                } else {
                    alert('Failed to load user details');
                }
            },
            error: function() {
                alert('Error loading user details');
            }
        });
    });

    // Handle Edit User Button Click
    $(document).on('click', '.edit-user-btn', function() {
        let userId = $(this).data('id');
        $.ajax({
            url: 'get_user_details.php',
            type: 'GET',
            data: { id: userId },
            success: function(response) {
                if (response.success) {
                    const user = response.data;
                    
                    // Set user ID
                    $('#edit_user_id').val(user.user_id);
                    
                    // Set profile image
                    $('#editProfileImage').attr('src', user.profile_image ? user.profile_image : 'uploads/profiles/default.png');
                    
                    // Set form values
                    $('#edit_user_name').val(user.user_name);
                    $('#edit_user_email').val(user.user_email);
                    $('#edit_contact_number').val(user.contact_number || '');
                    $('#edit_branch_id').val(user.branch_id || '');
                    $('#edit_user_type').val(user.user_type);
                    $('#edit_user_status').val(user.user_status);
                    $('#edit_address').val(user.address || '');

                    // Show modal
                    $('#editUserModal').modal('show');
                } else {
                    alert('Failed to load user details');
                }
            },
            error: function() {
                alert('Error loading user details');
            }
        });
    });

    // Handle Profile Image Upload
    $('.current-profile').click(function() {
        $('#profileImageInput').click();
    });

    $('#profileImageInput').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#editProfileImage').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // Handle Password Toggle
    $('#togglePassword').click(function() {
        const passwordField = $('#edit_user_password');
        const icon = $(this).find('i');
        
        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Handle Save Edit User
    $('#saveEditUserButton').click(function() {
        const formData = new FormData($('#editUserForm')[0]);
        const userId = $('#edit_user_id').val();
        
        $.ajax({
            url: 'update_user.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#editUserModal').modal('hide');
                    $('#userTable').DataTable().ajax.reload();
                    
                    // Update user details modal if it's currently showing the same user
                    if ($('#userDetailsModal').hasClass('show') && $('#userName').text() === $('#edit_user_name').val()) {
                        // Refresh user details
                        $.ajax({
                            url: 'get_user_details.php',
                            type: 'GET',
                            data: { id: userId },
                            success: function(detailsResponse) {
                                if (detailsResponse.success) {
                                    const user = detailsResponse.data;
                                    
                                    // Update profile image
                                    $('#userProfileImage').attr('src', user.profile_image ? user.profile_image : 'uploads/profiles/default.png');
                                    
                                    // Update user details
                                    $('#userName').text(user.user_name);
                                    $('#userEmail').text(user.user_email);
                                    $('#userType').text(user.user_type);
                                    $('#userContact').text(user.contact_number || 'N/A');
                                    $('#userStatus').html(user.user_status === 'Active' ? 
                                        '<span class="badge bg-success">Active</span>' : 
                                        '<span class="badge bg-danger">Inactive</span>');
                                    $('#userCreatedAt').text(user.created_at);

                                    // Handle cashier details
                                    if (user.user_type === 'Cashier' && user.cashier_details) {
                                        $('#cashierDetails').show();
                                        $('#employeeId').text(user.cashier_details.employee_id);
                                        $('#branchName').text(user.cashier_details.branch_name);
                                        $('#dateHired').text(user.cashier_details.date_hired);
                                        $('#emergencyContact').text(user.cashier_details.emergency_contact);
                                        $('#emergencyNumber').text(user.cashier_details.emergency_number);
                                        $('#address').text(user.cashier_details.address);
                                    } else {
                                        $('#cashierDetails').hide();
                                    }
                                }
                            }
                        });
                    }
                    
                    showFeedbackModal('success', 'Updated!', 'User has been updated successfully.');
                } else {
                    showFeedbackModal('error', 'Error!', response.message || 'Failed to update user.');
                }
            },
            error: function() {
                showFeedbackModal('error', 'Error!', 'An error occurred while updating the user.');
            }
        });
    });

    // Archive user functionality
    $(document).on('click', '.archive-user-btn', function() {
        let userId = $(this).data('id');
        let userName = $(this).closest('tr').find('td:eq(1)').text();
        
        // Show confirmation modal
        showArchiveConfirmationModal(
            'Archive User',
            `Are you sure you want to archive the user "${userName}"?`,
            function() {
                // Confirm callback
                $.ajax({
                    url: 'archive_user.php',
                    type: 'POST',
                    data: { id: userId },
                    dataType: 'json',
                    success: function(response) {
                        console.log('AJAX Success Response:', response);
                        if (response && response.success) {
                            showFeedbackModal('success', 'Success!', 'User archived successfully!');
                            $('#userTable').DataTable().ajax.reload();
                        } else {
                            showFeedbackModal('error', 'Error!', response.message || 'Failed to archive user.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX Error:', xhr.responseText, status, error);
                        showFeedbackModal('error', 'Error!', 'An error occurred while archiving the user.');
                    }
                });
            }
        );
    });

});
</script>
