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
    border: none;
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

/* Enhanced Add Category Modal Styling */
.enhanced-add-category-modal {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: none;
    border-radius: 1rem;
    box-shadow: 0 20px 60px rgba(139, 69, 67, 0.3);
    backdrop-filter: blur(10px);
    animation: modalSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.enhanced-add-category-modal .modal-header {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    border-bottom: none;
    padding: 1.5rem 2rem;
    border-radius: 1rem 1rem 0 0;
    position: relative;
    overflow: hidden;
}

.enhanced-add-category-modal .modal-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
    animation: shimmer 3s infinite;
}

.enhanced-add-category-modal .add-icon {
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    animation: pulse 2s infinite;
}

.enhanced-add-category-modal .modal-title {
    color: white;
    font-weight: 700;
    font-size: 1.5rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.enhanced-add-category-modal .btn-close {
    width: 32px;
    height: 32px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    border: none;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    animation: glow 2s ease-in-out infinite;
}

.enhanced-add-category-modal .btn-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1) rotate(45deg);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.enhanced-add-category-modal .close-x {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 1.2rem;
    font-weight: 700;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
}

.enhanced-add-category-modal .modal-body {
    padding: 2rem;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

.enhanced-add-category-modal .form-section {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(139, 69, 67, 0.1);
    border: 1px solid rgba(139, 69, 67, 0.1);
    transition: all 0.3s ease;
}

.enhanced-add-category-modal .form-section:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(139, 69, 67, 0.15);
}

.enhanced-add-category-modal .section-title {
    color: #8B4543;
    font-weight: 700;
    font-size: 1.1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid rgba(139, 69, 67, 0.2);
    position: relative;
}

.enhanced-add-category-modal .section-title::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 50px;
    height: 2px;
    background: linear-gradient(90deg, #8B4543, #A65D5D);
    animation: shimmer 2s infinite;
}

.enhanced-add-category-modal .form-label {
    color: #2c3e50;
    font-weight: 600;
    font-size: 0.95rem;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
}

.enhanced-add-category-modal .form-label i {
    color: #8B4543;
    margin-right: 0.5rem;
    font-size: 1rem;
}

.enhanced-add-category-modal .form-control,
.enhanced-add-category-modal .form-select {
    border: 2px solid rgba(139, 69, 67, 0.2);
    border-radius: 0.75rem;
    padding: 0.875rem 1rem;
    font-size: 1rem;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.enhanced-add-category-modal .form-control:focus,
.enhanced-add-category-modal .form-select:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25), 0 8px 25px rgba(139, 69, 67, 0.15);
    transform: translateY(-2px);
    background: white;
}

.enhanced-add-category-modal .form-text {
    color: #6c757d;
    font-size: 0.85rem;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
}

.enhanced-add-category-modal .form-text i {
    color: #8B4543;
    margin-right: 0.25rem;
}

/* Category Preview Styling */
.enhanced-add-category-modal .category-preview {
    margin-top: 1.5rem;
}

.enhanced-add-category-modal .preview-title {
    color: #8B4543;
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
}

.enhanced-add-category-modal .preview-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 2px solid rgba(139, 69, 67, 0.2);
    border-radius: 0.75rem;
    padding: 1.25rem;
    transition: all 0.3s ease;
}

.enhanced-add-category-modal .preview-card:hover {
    border-color: rgba(139, 69, 67, 0.4);
    box-shadow: 0 4px 15px rgba(139, 69, 67, 0.1);
}

.enhanced-add-category-modal .preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid rgba(139, 69, 67, 0.2);
}

.enhanced-add-category-modal .preview-name {
    font-weight: 700;
    font-size: 1.1rem;
    color: #2c3e50;
}

.enhanced-add-category-modal .preview-status {
    font-size: 0.9rem;
    font-weight: 600;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    background: rgba(139, 69, 67, 0.1);
    color: #8B4543;
}

.enhanced-add-category-modal .preview-description {
    color: #6c757d;
    font-size: 0.9rem;
    line-height: 1.6;
    font-style: italic;
}

/* Modal Footer Styling */
.enhanced-add-category-modal .modal-footer {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-top: 1px solid rgba(139, 69, 67, 0.1);
    padding: 1.5rem 2rem;
    border-radius: 0 0 1rem 1rem;
}

.enhanced-add-category-modal .btn {
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    border-radius: 0.75rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    border: none;
    font-size: 1rem;
}

.enhanced-add-category-modal .btn-primary {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(139, 69, 67, 0.3);
}

.enhanced-add-category-modal .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(139, 69, 67, 0.4);
    background: linear-gradient(135deg, #723937 0%, #5a2e2c 100%);
}

.enhanced-add-category-modal .btn-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
}

.enhanced-add-category-modal .btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(108, 117, 125, 0.4);
    background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
}

/* Keyboard Hints Styling */
.enhanced-add-category-modal .keyboard-hints {
    display: flex;
    align-items: center;
}

.enhanced-add-category-modal .keyboard-hints small {
    font-size: 0.8rem;
    color: #6c757d;
    opacity: 0.8;
    transition: all 0.3s ease;
}

.enhanced-add-category-modal .keyboard-hints:hover small {
    opacity: 1;
    color: #8B4543;
}

.enhanced-add-category-modal .keyboard-hints i {
    font-size: 0.9rem;
    margin-right: 0.25rem;
}

/* Preview Animation */
.enhanced-add-category-modal .preview-updated {
    animation: previewUpdate 0.3s ease-in-out;
}

@keyframes previewUpdate {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); }
}

/* Enhanced Edit Category Modal Styles */
.enhanced-category-modal {
    border: none;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(139, 69, 67, 0.3);
}

.enhanced-category-modal .modal-header {
    background: linear-gradient(135deg, #8B4543 0%, #a55a58 100%);
    color: white;
    border: none;
    padding: 2rem;
    position: relative;
}

.enhanced-category-modal .modal-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="10" cy="60" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="90" cy="40" r="0.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
}

.edit-icon {
    width: 50px;
    height: 50px;
    background: rgba(255,255,255,0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    backdrop-filter: blur(10px);
}

.enhanced-category-modal .modal-title {
    font-weight: 600;
    color: white;
    font-size: 1.4rem;
}

.enhanced-category-modal .btn-close {
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

.enhanced-category-modal .btn-close .close-x {
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

/* Override Bootstrap's default btn-close behavior */
.enhanced-category-modal .btn-close::before,
.enhanced-category-modal .btn-close::after {
    display: none !important;
    content: none !important;
}

/* Ensure the button content is visible */
.enhanced-category-modal .btn-close * {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}



.enhanced-category-modal .btn-close:hover {
    transform: scale(1.15);
    filter: brightness(1.8);
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.4) 0%, rgba(255, 255, 255, 0.25) 100%);
    border-color: rgba(255, 255, 255, 0.7);
    box-shadow: 
        0 12px 35px rgba(0, 0, 0, 0.3),
        inset 0 1px 0 rgba(255, 255, 255, 0.4),
        0 0 0 2px rgba(255, 255, 255, 0.2),
        0 0 20px rgba(255, 255, 255, 0.3);
    animation: categoryCloseButtonPulse 0.6s ease-out;
}

.enhanced-category-modal .btn-close:hover .close-x {
    transform: scale(1.2);
    text-shadow: 
        0 0 15px rgba(255, 255, 255, 0.9),
        0 3px 8px rgba(0, 0, 0, 0.5),
        0 0 20px rgba(255, 255, 255, 0.6);
}



.enhanced-category-modal .btn-close:active {
    transform: scale(0.95) rotate(90deg);
    transition: all 0.1s ease;
}

.enhanced-category-modal .btn-close:focus {
    outline: none;
    box-shadow: 
        0 4px 15px rgba(0, 0, 0, 0.1),
        0 0 0 3px rgba(255, 255, 255, 0.5);
}

.enhanced-category-modal .btn-close:focus-visible {
    outline: 2px solid rgba(255, 255, 255, 0.8);
    outline-offset: 2px;
}

@keyframes categoryCloseButtonPulse {
    0% {
        box-shadow: 
            0 8px 25px rgba(0, 0, 0, 0.2),
            0 0 0 0 rgba(0, 0, 0, 0.7);
    }
    70% {
        box-shadow: 
            0 8px 25px rgba(0, 0, 0, 0.2),
            0 0 0 10px rgba(0, 0, 0, 0);
    }
    100% {
        box-shadow: 
            0 8px 25px rgba(0, 0, 0, 0.2),
            0 0 0 0 rgba(0, 0, 0, 0);
    }
}

@keyframes categoryCloseButtonEntrance {
    0% {
        opacity: 0;
        transform: scale(0.5) rotate(-180deg);
    }
    50% {
        opacity: 0.7;
        transform: scale(1.1) rotate(-90deg);
    }
    100% {
        opacity: 1;
        transform: scale(1) rotate(0deg);
    }
}

#editCategoryModal.show .btn-close {
    animation: categoryCloseButtonEntrance 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.enhanced-category-modal .modal-body {
    padding: 2rem;
    background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
}

/* Form Section Styling */
.form-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    border: 1px solid rgba(139, 69, 67, 0.1);
    margin-bottom: 1.5rem;
}

.section-title {
    color: #8B4543;
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid rgba(139, 69, 67, 0.1);
    display: flex;
    align-items: center;
}

.section-title i {
    color: #8B4543;
    margin-right: 0.5rem;
}

/* Enhanced Form Controls */
.enhanced-category-modal .form-control,
.enhanced-category-modal .form-select {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
}

.enhanced-category-modal .form-control:focus,
.enhanced-category-modal .form-select:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.3rem rgba(139, 69, 67, 0.15);
    transform: translateY(-1px);
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

.enhanced-category-modal .form-control-lg,
.enhanced-category-modal .form-select-lg {
    padding: 1rem 1.25rem;
    font-size: 1.1rem;
}

.enhanced-category-modal .form-label {
    color: #8B4543;
    font-weight: 600;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
}

.enhanced-category-modal .form-label i {
    margin-right: 0.5rem;
    color: #8B4543;
}

.enhanced-category-modal .form-text {
    color: #6c757d;
    font-size: 0.875rem;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
}

.enhanced-category-modal .form-text i {
    margin-right: 0.25rem;
    color: #8B4543;
}

/* Category Statistics section removed for cleaner layout */

/* Enhanced Modal Footer */
.enhanced-category-modal .modal-footer {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-top: 1px solid rgba(139, 69, 67, 0.1);
    padding: 1.5rem 2rem;
}

.keyboard-hints {
    color: #6c757d;
    font-size: 0.875rem;
}

.enhanced-category-modal .btn {
    border-radius: 8px;
    font-weight: 600;
    padding: 0.75rem 1.5rem;
    transition: all 0.3s ease;
    border: none;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.enhanced-category-modal .btn-lg {
    padding: 1rem 2rem;
    font-size: 1rem;
}

.enhanced-category-modal .btn-primary {
    background: linear-gradient(135deg, #8B4543 0%, #a55a58 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(139, 69, 67, 0.3);
}

.enhanced-category-modal .btn-primary:hover {
    background: linear-gradient(135deg, #723836 0%, #8B4543 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(139, 69, 67, 0.4);
}

.enhanced-category-modal .btn-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
}

.enhanced-category-modal .btn-secondary:hover {
    background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(108, 117, 125, 0.4);
}

/* Loading and Success States */
.btn-loading {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #8B4543 0%, #a55a58 100%) !important;
    box-shadow: 0 8px 25px rgba(139, 69, 67, 0.4) !important;
    transform: scale(0.95);
}

.btn-loading::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: loadingShimmer 1.5s infinite;
}

@keyframes loadingShimmer {
    0% { left: -100%; }
    100% { left: 100%; }
}

.success-checkmark {
    display: inline-block;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #28a745;
    position: relative;
    animation: checkmark 0.5s ease-in-out;
}

.success-checkmark::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-weight: bold;
    font-size: 12px;
}

@keyframes checkmark {
    0% { transform: scale(0); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

/* Responsive Design */
@media (max-width: 768px) {
    .enhanced-category-modal .modal-body {
        padding: 1.5rem;
    }
    
    .enhanced-category-modal .modal-header {
        padding: 1.5rem;
    }
    
    .enhanced-category-modal .modal-title {
        font-size: 1.2rem;
    }
    
    /* Statistics responsive styles removed */
    
    .enhanced-category-modal .modal-footer {
        padding: 1rem 1.5rem;
        flex-direction: column;
        gap: 1rem;
    }
    
    .keyboard-hints {
        text-align: center;
    }
}

/* Enhanced Form Validation Visual Feedback */
.enhanced-category-modal .form-control.is-valid {
    border-color: #28a745;
    box-shadow: 0 0 0 0.3rem rgba(40, 167, 69, 0.25);
    background: linear-gradient(135deg, #f8fff9 0%, #e8f5e8 100%);
}

.enhanced-category-modal .form-control.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.3rem rgba(220, 53, 69, 0.25);
    background: linear-gradient(135deg, #fff8f8 0%, #f5e8e8 100%);
}

/* Row Update Highlight Animation */
.row-updated {
    animation: rowUpdateHighlight 3s ease-in-out;
    background: linear-gradient(90deg, rgba(40, 167, 69, 0.1) 0%, rgba(40, 167, 69, 0.05) 100%) !important;
}

@keyframes rowUpdateHighlight {
    0% { 
        background: linear-gradient(90deg, rgba(40, 167, 69, 0.2) 0%, rgba(40, 167, 69, 0.1) 100%);
        transform: scale(1.02);
    }
    50% { 
        background: linear-gradient(90deg, rgba(40, 167, 69, 0.15) 0%, rgba(40, 167, 69, 0.08) 100%);
        transform: scale(1.01);
    }
    100% { 
        background: linear-gradient(90deg, rgba(40, 167, 69, 0.1) 0%, rgba(40, 167, 69, 0.05) 100%);
        transform: scale(1);
    }
}

/* Enhanced Notification System */
.notification {
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.2);
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
}

/* Shake Animation for Error */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}

.animated.shake {
    animation: shake 0.6s ease-in-out;
}

/* Loading Spinner Enhancement */
.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255,255,255,.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
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

/* Custom Modal Styles */
.modal-content {
    border-radius: 1rem;
}

.modal-header {
    border-radius: 1rem 1rem 0 0;
}

.bg-maroon {
    background-color: #8B4543;
}

.btn-maroon {
    background-color: #8B4543;
    color: white;
}

.btn-maroon:hover {
    background-color: #723937;
    color: white;
}

/* Form Control Styles */
.form-control, .form-select {
    border-radius: 0.75rem;
    padding: 0.75rem 1rem;
    background-color: white;
    transition: all 0.2s ease-in-out;
}

.form-control:focus, .form-select:focus {
    box-shadow: 0 0 0 0.25rem rgba(139, 69, 67, 0.25);
    border-color: rgba(139, 69, 67, 0.5);
}

/* Button Styles */
.btn-lg {
    border-radius: 0.75rem;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    transition: all 0.2s ease-in-out;
}

.btn-light {
    background-color: #f8f9fa;
    border: none;
}

.btn-light:hover {
    background-color: #e9ecef;
}

/* Modal Animation */
.modal.fade .modal-dialog {
    transform: scale(0.95);
    transition: transform 0.2s ease-out;
}

.modal.show .modal-dialog {
    transform: scale(1);
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

.swal2-confirm-archive {
    background-color: #B33A3A !important;
    color: #fff !important;
    border-radius: 0.75rem !important;
    padding: 0.75rem 1.5rem !important;
    font-size: 1rem !important;
    font-weight: 500 !important;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(179, 58, 58, 0.15) !important;
    display: inline-flex !important;
    align-items: center;
    gap: 0.4em;
}
.swal2-confirm-archive:focus {
    box-shadow: 0 0 0 0.25rem rgba(179, 58, 58, 0.25) !important;
}

/* Modal Styling */
.modal-content {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.modal-header {
    border-radius: 1rem 1rem 0 0;
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    padding: 1.5rem;
    border: none;
    color: white;
}

.modal-header .btn-close {
    filter: brightness(0) invert(1);
    opacity: 0.75;
}

.modal-body {
    padding: 2rem;
    background-color: #fff;
}

.modal-footer {
    padding: 1.5rem;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
    background-color: #f8f9fa;
    border-radius: 0 0 1rem 1rem;
}

.form-label {
    font-weight: 500;
    color: #566a7f;
    margin-bottom: 0.5rem;
}

.form-control, .form-select {
    padding: 0.75rem 1rem;
    font-size: 0.9375rem;
    border-radius: 0.5rem;
    border: 1px solid #d9dee3;
    transition: all 0.2s ease-in-out;
}

.form-control:focus, .form-select:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.25rem rgba(139, 69, 67, 0.25);
}

.btn {
    padding: 0.6875rem 1.5rem;
    font-size: 0.9375rem;
    border-radius: 0.5rem;
    transition: all 0.2s ease-in-out;
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
    color: #566a7f;
    background-color: #f8f9fa;
    border-color: #d9dee3;
}

.btn-secondary:hover {
    background-color: #e9ecef;
    border-color: #d9dee3;
    color: #566a7f;
}

.btn-archive {
    background: #6c757d !important;
    color: #fff !important;
    border: none;
    border-radius: 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 0.4em;
    font-weight: 500;
    font-size: 1rem;
    padding: 0.5rem 1.25rem;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(108, 117, 125, 0.10);
    transition: background 0.2s, color 0.2s;
}
.btn-archive i {
    color: #fff !important;
    font-size: 1.2em;
}
.btn-archive:hover, .btn-archive:focus {
    background: #5a6268 !important;
    color: #fff !important;
    text-decoration: none;
}
.btn-archive:active {
    background: #545b62 !important;
    color: #fff !important;
}

.btn-edit {
    background: #C4804D !important;
    color: #fff !important;
    border: none;
    border-radius: 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 0.4em;
    font-weight: 500;
    font-size: 1rem;
    padding: 0.5rem 1.25rem;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(196, 128, 77, 0.10);
    transition: background 0.2s, color 0.2s;
    margin-right: 0.75rem;
}
.btn-edit i {
    color: #fff !important;
    font-size: 1.2em;
}
.btn-edit:hover, .btn-edit:focus {
    background: #a96a3d !important;
    color: #fff !important;
    text-decoration: none;
}
.btn-edit:active {
    background: #8a5632 !important;
    color: #fff !important;
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
</style>

<div class="container-fluid px-4">
    <h1 class="section-title"><span class="section-icon"><i class="fas fa-list-alt"></i></span>Category Management</h1>
        <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-list me-1"></i>
                        Category List
            </div>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="fas fa-plus me-1"></i> Add Category
                    </button>
    </div>
    <div class="card-body">
                    <table id="categoryTable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Category Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content enhanced-add-category-modal">
            <!-- Enhanced Modal Header -->
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <div class="add-icon me-3">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="addCategoryModalLabel">Add Category</h5>
                        <small class="text-light opacity-75">Create a new product category</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close">
                    <span class="close-x">×</span>
                </button>
            </div>

            <!-- Enhanced Modal Body -->
            <div class="modal-body">
                <form id="addCategoryForm">
                    <div class="row g-4">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <div class="form-section">
                                <h6 class="section-title">
                                    <i class="fas fa-info-circle me-2"></i>Basic Information
                                </h6>
                                
                                <div class="mb-4">
                                    <label for="category_name" class="form-label">
                                        <i class="fas fa-tag me-1"></i>Category Name
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="category_name" name="category_name" placeholder="Enter category name" required>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Enter a unique category name for your products
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="category_status" class="form-label">
                                        <i class="fas fa-toggle-on me-1"></i>Status
                                    </label>
                                    <select class="form-select form-select-lg" id="category_status" name="category_status" required>
                                        <option value="active">🟢 Active</option>
                                        <option value="inactive">🔴 Inactive</option>
                                    </select>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Choose whether this category should be active or inactive
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Column -->
                        <div class="col-md-6">
                            <div class="form-section">
                                <h6 class="section-title">
                                    <i class="fas fa-align-left me-2"></i>Category Details
                                </h6>
                                
                                <div class="mb-4">
                                    <label for="description" class="form-label">
                                        <i class="fas fa-file-text me-1"></i>Description
                                    </label>
                                    <textarea class="form-control form-control-lg" id="description" name="description" rows="6" placeholder="Enter category description..."></textarea>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Provide a detailed description of this category
                                    </div>
                                </div>
                                
                                <div class="category-preview">
                                    <h6 class="preview-title">
                                        <i class="fas fa-eye me-2"></i>Category Preview
                                    </h6>
                                    <div class="preview-card">
                                        <div class="preview-header">
                                            <span class="preview-name" id="previewName">Category Name</span>
                                            <span class="preview-status" id="previewStatus">🟢 Active</span>
                                        </div>
                                        <div class="preview-description" id="previewDescription">
                                            Category description will appear here...
                                        </div>
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
                        <button type="button" class="btn btn-primary btn-lg" id="saveCategory">
                            <i class="fas fa-plus me-2"></i>Add Category
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content enhanced-category-modal">
            <!-- Enhanced Modal Header -->
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <div class="edit-icon me-3">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="editCategoryModalLabel">Edit Category</h5>
                        <small class="text-light opacity-75">Update category information</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
                    <span class="close-x">×</span>
                </button>
            </div>
            
            <!-- Enhanced Modal Body -->
            <div class="modal-body">
                <form id="editCategoryForm">
                    <input type="hidden" id="edit_category_id" name="category_id">
                    
                    <div class="row g-4">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <div class="form-section">
                                <h6 class="section-title">
                                    <i class="fas fa-info-circle me-2"></i>Basic Information
                                </h6>
                                <div class="mb-4">
                                    <label for="edit_category_name" class="form-label">
                                        <i class="fas fa-tag me-1"></i>Category Name
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="edit_category_name" name="category_name" required>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Enter a unique category name
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="edit_category_status" class="form-label">
                                        <i class="fas fa-toggle-on me-1"></i>Status
                                    </label>
                                    <select class="form-select form-select-lg" id="edit_category_status" name="category_status" required>
                                        <option value="active">🟢 Active</option>
                                        <option value="inactive">🔴 Inactive</option>
                                    </select>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Active categories are visible to users
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Column -->
                        <div class="col-md-6">
                            <div class="form-section">
                                <h6 class="section-title">
                                    <i class="fas fa-align-left me-2"></i>Category Details
                                </h6>
                                <div class="mb-4">
                                    <label for="edit_description" class="form-label">
                                        <i class="fas fa-file-text me-1"></i>Description
                                    </label>
                                    <textarea class="form-control" id="edit_description" name="description" rows="6" placeholder="Enter detailed description of this category..."></textarea>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Optional: Provide additional context about this category
                                    </div>
                                </div>
                                
                                <!-- Category Statistics Preview -->
                                <div class="category-stats-preview">
                                    <h6 class="section-title">
                                        <i class="fas fa-chart-bar me-2"></i>Category Statistics
                                    </h6>
                                    <div class="stats-grid">
                                        <div class="stat-item">
                                            <div class="stat-icon">
                                                <i class="fas fa-box"></i>
                                            </div>
                                            <div class="stat-content">
                                                <div class="stat-value" id="productCount">0</div>
                                                <div class="stat-label">Products</div>
                                            </div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-icon">
                                                <i class="fas fa-eye"></i>
                                            </div>
                                            <div class="stat-content">
                                                <div class="stat-value" id="viewCount">0</div>
                                                <div class="stat-label">Views</div>
                                            </div>
                                        </div>
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
                        <button type="button" class="btn btn-primary btn-lg" id="saveEditButton">
                            <i class="fas fa-save me-2"></i>Update Category
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

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
$(document).ready(function() {
    $('#categoryTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "category_ajax.php",
            "type": "GET"
        },
        "columns": [
            { "data": "category_name" },
            { 
                "data": "description",
                "render": function(data, type, row) {
                    return data ? data : '<em class="text-muted">No description</em>';
                }
            },
            { 
                "data": null,
                "render": function(data, type, row) {
                    if(row.status === 'active'){
                        return '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Active</span>';
                    } else {
                        return '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Inactive</span>';
                    }
                }
            },
            {
                "data": null,
                "render": function(data, type, row) {
                    return `
                    <div class="btn-group">
                        <button type="button" class="btn btn-edit btn-sm edit-btn" data-id="${row.category_id}" title="Edit Category">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-archive btn-sm archive-btn" data-id="${row.category_id}" title="Archive Category">
                            <i class="fas fa-box-archive"></i>
                        </button>
                    </div>`;
                }
            }
        ],
        "order": [[0, "desc"]],
        "pageLength": 5,
        "pagingType": "simple", // Show only Previous/Next buttons
        "ordering": false, // Disable sorting functionality
        "responsive": true,
        "language": {
            "emptyTable": "No categories found",
            "info": "Showing _START_ to _END_ of _TOTAL_ categories",
            "infoEmpty": "Showing 0 to 0 of 0 categories",
            "infoFiltered": "(filtered from _MAX_ total categories)"
        }
    });

    // Handle Save Category Button Click
    $('#saveCategory').click(function() {
        let formData = {
            category_name: $('#category_name').val(),
            description: $('#description').val(),
            category_status: $('#category_status').val()
        };

        $.ajax({
            url: 'add_category.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Close modal properly
                    const modal = bootstrap.Modal.getInstance($('#addCategoryModal'));
                    modal.hide();
                    
                    // Remove modal backdrop manually if it persists
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    
                    // Clear form
                    $('#addCategoryForm')[0].reset();
                    
                    // Reload table
                    $('#categoryTable').DataTable().ajax.reload();
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Category added successfully',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Error adding category'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Error occurred while adding category'
                });
            }
        });
    });

    // Handle modal hidden event
    $('#addCategoryModal').on('hidden.bs.modal', function () {
        // Clear form when modal is closed
        $('#addCategoryForm')[0].reset();
        // Remove any remaining backdrop
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    });

    // Handle Delete Button Click
    $(document).on('click', '.archive-btn', function() {
        let categoryId = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You can restore this category from the archive.",
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
                url: 'archive_category.php',
                type: 'POST',
                data: { id: categoryId },
                dataType: 'json',
                success: function(response) {
                        console.log('Archive response:', response); // Debugging line
                        if (response.success) {
                            // Force DataTable to reload and remove archived row
                            $('#categoryTable').DataTable().ajax.reload(null, false);
                            showFeedbackModal('success', 'Archived!', 'Category has been archived successfully.');
                        } else {
                            showFeedbackModal('error', 'Error!', response.message || 'Failed to archive category.');
                        }
                    },
                    error: function(xhr) {
                        let msg = 'An error occurred while archiving the category.';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        showFeedbackModal('error', 'Error!', msg);
                    }
                });
            }
        });
    });

    // Enhanced Edit Button Click with Statistics
    $(document).on('click', '.edit-btn', function() {
        let categoryId = $(this).data('id');
        let categoryName = $(this).closest('tr').find('td:eq(0)').text(); // Get category name from table
        
        // Show loading state in modal
        $('#editCategoryModal').modal('show');
        $('#editCategoryModal .modal-body').html(`
            <div class="text-center py-5">
                <div class="loading-spinner" style="width: 40px; height: 40px; margin: 0 auto 1rem;"></div>
                <p class="text-muted">Loading category details...</p>
            </div>
        `);
        
        // Fetch category data with enhanced error handling
        $.ajax({
            url: 'edit_category.php',
            type: 'GET',
            data: { id: categoryId },
            dataType: 'json',
            timeout: 10000,
            success: function(response) {
                if (response.success) {
                    let category = response.data;
                    
                    // Restore modal body content
                    $('#editCategoryModal .modal-body').html(`
                        <form id="editCategoryForm">
                            <input type="hidden" id="edit_category_id" name="category_id">
                            
                            <div class="row g-4">
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <div class="form-section">
                                        <h6 class="section-title">
                                            <i class="fas fa-info-circle me-2"></i>Basic Information
                                        </h6>
                                        <div class="mb-4">
                                            <label for="edit_category_name" class="form-label">
                                                <i class="fas fa-tag me-1"></i>Category Name
                                            </label>
                                            <input type="text" class="form-control form-control-lg" id="edit_category_name" name="category_name" required>
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Enter a unique category name
                                            </div>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label for="edit_category_status" class="form-label">
                                                <i class="fas fa-toggle-on me-1"></i>Status
                                            </label>
                                            <select class="form-select form-select-lg" id="edit_category_status" name="category_status" required>
                                                <option value="active">🟢 Active</option>
                                                <option value="inactive">🔴 Inactive</option>
                                            </select>
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Active categories are visible to users
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <div class="form-section">
                                        <h6 class="section-title">
                                            <i class="fas fa-align-left me-2"></i>Category Details
                                        </h6>
                                        <div class="mb-4">
                                            <label for="edit_description" class="form-label">
                                                <i class="fas fa-file-text me-1"></i>Description
                                            </label>
                                            <textarea class="form-control" id="edit_description" name="description" rows="8" placeholder="Enter detailed description of this category..."></textarea>
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Optional: Provide additional context about this category
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    `);
                    
                    // Populate form fields
                    $('#edit_category_id').val(category.category_id);
                    $('#edit_category_name').val(category.category_name);
                    $('#edit_description').val(category.description);
                    $('#edit_category_status').val(category.status);
                    
                    // Category statistics removed for cleaner layout
                    
                    // Add form validation listeners
                    addFormValidationListeners();
                    
                } else {
                    $('#editCategoryModal').modal('hide');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to fetch category details',
                        customClass: {
                            popup: 'animate__animated animate__fadeInDown animate__faster'
                        }
                    });
                }
            },
            error: function(xhr, status, error) {
                $('#editCategoryModal').modal('hide');
                
                let errorMessage = 'An error occurred while fetching category details.';
                if (status === 'timeout') {
                    errorMessage = 'Request timed out. Please try again.';
                } else if (xhr.status === 0) {
                    errorMessage = 'Network error. Please check your connection and try again.';
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Connection Error!',
                    text: errorMessage,
                    customClass: {
                        popup: 'animate__animated animate__fadeInDown animate__faster'
                    }
                });
            }
        });
    });
    
    // Category statistics function removed
    
    // Live preview functionality for Add Category modal
    $('#addCategoryModal').on('show.bs.modal', function() {
        // Initialize preview
        updateCategoryPreview();
        
        // Add live preview listeners
        $('#category_name').on('input', updateCategoryPreview);
        $('#description').on('input', updateCategoryPreview);
        $('#category_status').on('change', updateCategoryPreview);
    });
    
    function updateCategoryPreview() {
        const name = $('#category_name').val() || 'Category Name';
        const description = $('#description').val() || 'Category description will appear here...';
        const status = $('#category_status').val();
        
        $('#previewName').text(name);
        $('#previewDescription').text(description);
        $('#previewStatus').text(status === 'active' ? '🟢 Active' : '🔴 Inactive');
        
        // Add animation to preview
        $('.preview-card').addClass('preview-updated');
        setTimeout(() => {
            $('.preview-card').removeClass('preview-updated');
        }, 300);
    }
    
    // Function to add form validation listeners
    function addFormValidationListeners() {
        $('#editCategoryForm').on('input', 'input, select, textarea', function() {
            var $field = $(this);
            var value = $field.val();
            
            // Remove previous validation classes
            $field.removeClass('is-valid is-invalid');
            
            // Add validation based on field type
            if ($field.attr('required') && !value) {
                $field.addClass('is-invalid');
            } else if (value) {
                $field.addClass('is-valid');
            }
            
            // Special validation for category name
            if ($field.attr('name') === 'category_name') {
                if (value && value.length < 2) {
                    $field.addClass('is-invalid');
                    $field.removeClass('is-valid');
                }
            }
        });
    }

    // Enhanced Save Edit Button Click with Auto-Update
    $('#saveEditButton').click(function() {
        let $btn = $(this);
        let originalText = $btn.html();
        let categoryId = $('#edit_category_id').val();
        
        // Show loading state with enhanced animation
        $btn.html('<span class="loading-spinner"></span> Updating Category...');
        $btn.prop('disabled', true);
        $btn.addClass('btn-loading');
        
        let form = $('#editCategoryForm');
        let formData = form.serialize();
        
        // Add timestamp to prevent caching
        formData += '&timestamp=' + new Date().getTime();
        
        $.ajax({
            url: 'edit_category.php',
            type: 'POST',
            data: formData,
            timeout: 10000, // 10 second timeout
            success: function(response) {
                // Reset button state
                $btn.removeClass('btn-loading');
                $btn.prop('disabled', false);
                
                if (response.success) {
                    // Show success animation
                    $btn.html('<span class="success-checkmark"></span> Updated!');
                    
                    // Auto-hide modal after short delay
                    setTimeout(function() {
                        $('#editCategoryModal').modal('hide');
                        $('body').removeClass('modal-open');
                        $('.modal-backdrop').remove();
                        
                        // Show success notification
                        showNotification('Category updated successfully!', 'success');
                        
                        // Auto-reload table with smooth animation
                        $('#categoryTable').DataTable().ajax.reload(function() {
                            // Highlight the updated row
                            highlightUpdatedCategoryRow(categoryId);
                        });
                        
                        // Reset button after animation
                        setTimeout(function() {
                            $btn.html(originalText);
                        }, 1000);
                    }, 1500);
                    
                } else {
                    // Reset button
                    $btn.html(originalText);
                    
                    Swal.fire({
                        title: 'Update Failed!',
                        text: response.message || 'Failed to update category.',
                        icon: 'error',
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'animated shake'
                        }
                    });
                }
            },
            error: function(xhr, status, error) {
                // Reset button
                $btn.removeClass('btn-loading');
                $btn.html(originalText);
                $btn.prop('disabled', false);
                
                let errorMessage = 'An error occurred while updating the category.';
                
                if (status === 'timeout') {
                    errorMessage = 'Request timed out. Please check your connection and try again.';
                } else if (xhr.status === 0) {
                    errorMessage = 'Network error. Please check your connection and try again.';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    title: 'Connection Error!',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'animated shake'
                    }
                });
            }
        });
    });
    
    // Function to highlight updated category row
    function highlightUpdatedCategoryRow(categoryId) {
        setTimeout(function() {
            var $row = $(`tr[data-category-id="${categoryId}"]`);
            if ($row.length) {
                $row.addClass('row-updated');
                setTimeout(function() {
                    $row.removeClass('row-updated');
                }, 3000);
            }
        }, 500);
    }
    
    // Enhanced form validation with real-time feedback
    $('#editCategoryForm').on('input', 'input, select, textarea', function() {
        var $field = $(this);
        var value = $field.val();
        
        // Remove previous validation classes
        $field.removeClass('is-valid is-invalid');
        
        // Add validation based on field type
        if ($field.attr('required') && !value) {
            $field.addClass('is-invalid');
        } else if (value) {
            $field.addClass('is-valid');
        }
        
        // Special validation for category name
        if ($field.attr('name') === 'category_name') {
            if (value && value.length < 2) {
                $field.addClass('is-invalid');
                $field.removeClass('is-valid');
            }
        }
        
        // Auto-save draft (every 30 seconds)
        clearTimeout(window.autoSaveTimer);
        window.autoSaveTimer = setTimeout(function() {
            saveCategoryDraft();
        }, 30000);
    });
    
    // Auto-save draft function
    function saveCategoryDraft() {
        var formData = $('#editCategoryForm').serialize();
        formData += '&action=save_draft';
        
        $.ajax({
            url: 'edit_category.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                // Show subtle notification
                showNotification('Draft saved automatically', 'info');
            },
            error: function() {
                // Silent fail for auto-save
            }
        });
    }
    
    // Notification system
    function showNotification(message, type = 'info') {
        var icon = {
            'success': 'fas fa-check-circle',
            'error': 'fas fa-exclamation-circle',
            'warning': 'fas fa-exclamation-triangle',
            'info': 'fas fa-info-circle'
        }[type] || 'fas fa-info-circle';
        
        var color = {
            'success': '#28a745',
            'error': '#dc3545',
            'warning': '#ffc107',
            'info': '#17a2b8'
        }[type] || '#17a2b8';
        
        var notification = $(`
            <div class="notification" style="
                position: fixed;
                top: 20px;
                right: 20px;
                background: white;
                border-left: 4px solid ${color};
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                padding: 1rem 1.5rem;
                border-radius: 8px;
                z-index: 9999;
                max-width: 300px;
                transform: translateX(100%);
                transition: transform 0.3s ease;
            ">
                <div class="d-flex align-items-center">
                    <i class="${icon} me-2" style="color: ${color};"></i>
                    <span>${message}</span>
                </div>
            </div>
        `);
        
        $('body').append(notification);
        
        // Animate in
        setTimeout(function() {
            notification.css('transform', 'translateX(0)');
        }, 100);
        
        // Auto remove after 3 seconds
        setTimeout(function() {
            notification.css('transform', 'translateX(100%)');
            setTimeout(function() {
                notification.remove();
            }, 300);
        }, 3000);
    }

    // Reset form when modal is closed
    $('#editCategoryModal').on('hidden.bs.modal', function() {
        $('#editCategoryForm')[0].reset();
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
    });
    
    // Add keyboard shortcuts for modal
    $(document).on('keydown', function(e) {
        // Only when edit modal is open
        if ($('#editCategoryModal').hasClass('show')) {
            // Ctrl/Cmd + S to save
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                $('#saveEditButton').click();
            }
            // ESC key to close modal
            if (e.key === 'Escape') {
                $('#editCategoryModal').modal('hide');
            }
        }
    });
    
    // Add modal hidden event handlers
    $('#editCategoryModal').on('hidden.bs.modal', function () {
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
        
        // Reset form when modal is closed
        $('#editCategoryForm')[0].reset();
        
        // Clear any validation states
        $('#editCategoryForm .form-control, #editCategoryForm .form-select').removeClass('is-valid is-invalid');
    });
    
    // Enhanced form validation with real-time feedback
    $('#editCategoryForm').on('input', 'input, select, textarea', function() {
        var $field = $(this);
        var value = $field.val();
        
        // Remove previous validation classes
        $field.removeClass('is-valid is-invalid');
        
        // Add validation based on field type
        if ($field.attr('required') && !value) {
            $field.addClass('is-invalid');
        } else if (value) {
            $field.addClass('is-valid');
        }
        
        // Special validation for category name
        if ($field.attr('name') === 'category_name') {
            if (value && value.length < 2) {
                $field.addClass('is-invalid');
                $field.removeClass('is-valid');
            }
        }
        
        // Auto-save draft (every 30 seconds)
        clearTimeout(window.autoSaveTimer);
        window.autoSaveTimer = setTimeout(function() {
            saveCategoryDraft();
        }, 30000);
    });
    
    // Auto-save draft function
    function saveCategoryDraft() {
        var formData = $('#editCategoryForm').serialize();
        formData += '&action=save_draft';
        
        $.ajax({
            url: 'edit_category.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                // Show subtle notification
                showNotification('Draft saved automatically', 'info');
            },
            error: function() {
                // Silent fail for auto-save
            }
        });
    }
    
    // Notification system
    function showNotification(message, type = 'info') {
        var icon = {
            'success': 'fas fa-check-circle',
            'error': 'fas fa-exclamation-circle',
            'warning': 'fas fa-exclamation-triangle',
            'info': 'fas fa-info-circle'
        }[type] || 'fas fa-info-circle';
        
        var color = {
            'success': '#28a745',
            'error': '#dc3545',
            'warning': '#ffc107',
            'info': '#17a2b8'
        }[type] || '#17a2b8';
        
        var notification = $(`
            <div class="notification" style="
                position: fixed;
                top: 20px;
                right: 20px;
                background: white;
                border-left: 4px solid ${color};
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                padding: 1rem 1.5rem;
                border-radius: 8px;
                z-index: 9999;
                max-width: 300px;
                transform: translateX(100%);
                transition: transform 0.3s ease;
            ">
                <div class="d-flex align-items-center">
                    <i class="${icon} me-2" style="color: ${color};"></i>
                    <span>${message}</span>
                </div>
            </div>
        `);
        
        $('body').append(notification);
        
        // Animate in
        setTimeout(function() {
            notification.css('transform', 'translateX(0)');
        }, 100);
        
        // Auto remove after 3 seconds
        setTimeout(function() {
            notification.css('transform', 'translateX(100%)');
            setTimeout(function() {
                notification.remove();
            }, 300);
        }, 3000);
    }
});
</script>
