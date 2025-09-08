<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

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

/* Modal Design Styles (copied from Add Category) */
.modal-header {
    border-radius: 0.5rem 0.5rem 0 0;
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
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
    background-color: white;
    transition: all 0.2s ease-in-out;
    font-size: 0.9rem;
}

.form-control:focus, .form-select:focus {
    box-shadow: 0 0 0 0.25rem rgba(139, 69, 67, 0.25);
    border-color: rgba(139, 69, 67, 0.5);
}

/* Button Styles */
.btn-lg {
    border-radius: 0.375rem;
    padding: 0.5rem 1.25rem;
    font-weight: 500;
    transition: all 0.2s ease-in-out;
    font-size: 0.9rem;
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

/* Modal Backdrop Fix */
.modal-backdrop {
    z-index: 1040;
}

.modal {
    z-index: 1050;
}

/* Enhanced Add Ingredient Modal Styling */
.enhanced-add-ingredient-modal {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: none;
    border-radius: 1rem;
    box-shadow: 0 20px 60px rgba(139, 69, 67, 0.3);
    backdrop-filter: blur(10px);
    animation: modalSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.enhanced-add-ingredient-modal .modal-header {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    border-bottom: none;
    padding: 1.5rem 2rem;
    border-radius: 1rem 1rem 0 0;
    position: relative;
    overflow: hidden;
}

.enhanced-add-ingredient-modal .modal-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
    animation: shimmer 3s infinite;
}

.enhanced-add-ingredient-modal .add-icon {
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

.enhanced-add-ingredient-modal .modal-title {
    color: white;
    font-weight: 700;
    font-size: 1.5rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.enhanced-add-ingredient-modal .btn-close {
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

.enhanced-add-ingredient-modal .btn-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1) rotate(45deg);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.enhanced-add-ingredient-modal .close-x {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 1.2rem;
    font-weight: 700;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
}

.enhanced-add-ingredient-modal .modal-body {
    padding: 2rem;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

.enhanced-add-ingredient-modal .form-section {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(139, 69, 67, 0.1);
    border: 1px solid rgba(139, 69, 67, 0.1);
    transition: all 0.3s ease;
}

.enhanced-add-ingredient-modal .form-section:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(139, 69, 67, 0.15);
}

.enhanced-add-ingredient-modal .section-title {
    color: #8B4543;
    font-weight: 700;
    font-size: 1.1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid rgba(139, 69, 67, 0.2);
    position: relative;
}

.enhanced-add-ingredient-modal .section-title::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 50px;
    height: 2px;
    background: linear-gradient(90deg, #8B4543, #A65D5D);
    animation: shimmer 2s infinite;
}

.enhanced-add-ingredient-modal .form-label {
    color: #2c3e50;
    font-weight: 600;
    font-size: 0.95rem;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
}

.enhanced-add-ingredient-modal .form-label i {
    color: #8B4543;
    margin-right: 0.5rem;
    font-size: 1rem;
}

.enhanced-add-ingredient-modal .form-control,
.enhanced-add-ingredient-modal .form-select {
    border: 2px solid rgba(139, 69, 67, 0.2);
    border-radius: 0.75rem;
    padding: 0.875rem 1rem;
    font-size: 1rem;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.enhanced-add-ingredient-modal .form-control:focus,
.enhanced-add-ingredient-modal .form-select:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25), 0 8px 25px rgba(139, 69, 67, 0.15);
    transform: translateY(-2px);
    background: white;
}

.enhanced-add-ingredient-modal .form-control[readonly] {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    color: #6c757d;
    cursor: not-allowed;
}

.enhanced-add-ingredient-modal .form-text {
    color: #6c757d;
    font-size: 0.85rem;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
}

.enhanced-add-ingredient-modal .form-text i {
    color: #8B4543;
    margin-right: 0.25rem;
}

/* Modal Footer Styling */
.enhanced-add-ingredient-modal .modal-footer {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-top: 1px solid rgba(139, 69, 67, 0.1);
    padding: 1.5rem 2rem;
    border-radius: 0 0 1rem 1rem;
}

.enhanced-add-ingredient-modal .btn {
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    border-radius: 0.75rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    border: none;
    font-size: 1rem;
}

.enhanced-add-ingredient-modal .btn-primary {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(139, 69, 67, 0.3);
}

.enhanced-add-ingredient-modal .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(139, 69, 67, 0.4);
    background: linear-gradient(135deg, #723937 0%, #5a2e2c 100%);
}

.enhanced-add-ingredient-modal .btn-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
}

.enhanced-add-ingredient-modal .btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(108, 117, 125, 0.4);
    background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
}

/* Keyboard Hints Styling */
.enhanced-add-ingredient-modal .keyboard-hints {
    display: flex;
    align-items: center;
}

.enhanced-add-ingredient-modal .keyboard-hints small {
    font-size: 0.8rem;
    color: #6c757d;
    opacity: 0.8;
    transition: all 0.3s ease;
}

.enhanced-add-ingredient-modal .keyboard-hints:hover small {
    opacity: 1;
    color: #8B4543;
}

.enhanced-add-ingredient-modal .keyboard-hints i {
    font-size: 0.9rem;
    margin-right: 0.25rem;
}

/* Animations */
@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

@keyframes glow {
    0%, 100% { box-shadow: 0 0 5px rgba(255, 255, 255, 0.3); }
    50% { box-shadow: 0 0 20px rgba(255, 255, 255, 0.6); }
}

/* Enhanced Import Modal Styling */
.enhanced-import-modal {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: none;
    border-radius: 1rem;
    box-shadow: 0 20px 60px rgba(139, 69, 67, 0.3);
    backdrop-filter: blur(10px);
    animation: modalSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.enhanced-import-modal .modal-header {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    border-bottom: none;
    padding: 1.5rem 2rem;
    border-radius: 1rem 1rem 0 0;
    position: relative;
    overflow: hidden;
}

.enhanced-import-modal .modal-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
    animation: shimmer 3s infinite;
}

.enhanced-import-modal .import-icon {
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

.enhanced-import-modal .modal-title {
    color: white;
    font-weight: 700;
    font-size: 1.5rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.enhanced-import-modal .btn-close {
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

.enhanced-import-modal .btn-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1) rotate(45deg);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.enhanced-import-modal .close-x {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 1.2rem;
    font-weight: 700;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
}

.enhanced-import-modal .modal-body {
    padding: 2rem;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

.enhanced-import-modal .import-section,
.enhanced-import-modal .template-section {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(139, 69, 67, 0.1);
    border: 1px solid rgba(139, 69, 67, 0.1);
    transition: all 0.3s ease;
    height: 100%;
}

.enhanced-import-modal .import-section:hover,
.enhanced-import-modal .template-section:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(139, 69, 67, 0.15);
}

.enhanced-import-modal .section-title {
    color: #8B4543;
    font-weight: 700;
    font-size: 1.1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid rgba(139, 69, 67, 0.2);
    position: relative;
}

.enhanced-import-modal .section-title::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 50px;
    height: 2px;
    background: linear-gradient(90deg, #8B4543, #A65D5D);
    animation: shimmer 2s infinite;
}

/* Upload Area Styling */
.enhanced-import-modal .upload-area-large {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 3px dashed rgba(139, 69, 67, 0.3);
    border-radius: 1rem;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.enhanced-import-modal .upload-area-large:hover {
    border-color: rgba(139, 69, 67, 0.6);
    background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
    transform: translateY(-2px);
}

.enhanced-import-modal .upload-icon-large {
    font-size: 3rem;
    color: #8B4543;
    margin-bottom: 1rem;
    opacity: 0.8;
}

.enhanced-import-modal .upload-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.enhanced-import-modal .upload-description {
    color: #6c757d;
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.enhanced-import-modal .upload-formats {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.enhanced-import-modal .format-badge {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.8rem;
    font-weight: 600;
}

.enhanced-import-modal .file-input-large {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

/* Template Section Styling */
.enhanced-import-modal .template-info {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 0.75rem;
    padding: 1.5rem;
    border: 1px solid rgba(139, 69, 67, 0.2);
}

.enhanced-import-modal .template-header {
    color: #2c3e50;
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
}

.enhanced-import-modal .columns-grid-landscape {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.enhanced-import-modal .column-badge {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    color: white;
    padding: 0.5rem;
    border-radius: 0.5rem;
    font-size: 0.8rem;
    font-weight: 600;
    text-align: center;
    box-shadow: 0 2px 8px rgba(139, 69, 67, 0.2);
}

.enhanced-import-modal .template-download {
    text-align: center;
    padding-top: 1rem;
    border-top: 1px solid rgba(139, 69, 67, 0.2);
}

.enhanced-import-modal .download-link a {
    color: #8B4543;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.enhanced-import-modal .download-link a:hover {
    color: #723937;
    text-decoration: underline;
}

/* Landscape Modal Styling */
.landscape-modal {
    max-width: 1200px !important;
    min-height: 600px;
}

.landscape-modal .modal-body {
    padding: 2rem;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    min-height: 500px;
}

.landscape-modal .import-section,
.landscape-modal .template-section {
    height: 100%;
    display: flex;
    flex-direction: column;
}

/* Modal Footer Styling */
.enhanced-import-modal .modal-footer {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-top: 1px solid rgba(139, 69, 67, 0.1);
    padding: 1.5rem 2rem;
    border-radius: 0 0 1rem 1rem;
}

.enhanced-import-modal .btn {
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    border-radius: 0.75rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    border: none;
    font-size: 1rem;
}

.enhanced-import-modal .btn-primary {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(139, 69, 67, 0.3);
}

.enhanced-import-modal .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(139, 69, 67, 0.4);
    background: linear-gradient(135deg, #723937 0%, #5a2e2c 100%);
}

.enhanced-import-modal .btn-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
}

.enhanced-import-modal .btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(108, 117, 125, 0.4);
    background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
}

/* Keyboard Hints Styling */
.enhanced-import-modal .keyboard-hints {
    display: flex;
    align-items: center;
}

.enhanced-import-modal .keyboard-hints small {
    font-size: 0.8rem;
    color: #6c757d;
    opacity: 0.8;
    transition: all 0.3s ease;
}

.enhanced-import-modal .keyboard-hints:hover small {
    opacity: 1;
    color: #8B4543;
}

.enhanced-import-modal .keyboard-hints i {
    font-size: 0.9rem;
    margin-right: 0.25rem;
}

/* Ensure proper modal cleanup */
body.modal-open {
    overflow: hidden;
}

body:not(.modal-open) {
    overflow: auto;
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

.action-buttons {
    display: flex;
    gap: 0.5rem; /* Adjust the gap as needed */
    justify-content: center;
    align-items: center;
}
.action-buttons .btn {
    margin: 0; /* Remove any default margin */
    padding: 0.4rem 0.7rem; /* Consistent button size */
}

.table td, .table th {
    padding: 1rem 0.75rem;
    vertical-align: middle;
}

.table td img {
    display: block;
    margin: 0 auto;           /* Center horizontally */
    margin-top: 4px;          /* Add a little space above */
    margin-bottom: 4px;       /* Add a little space below */
    width: 48px;              /* Consistent size */
    height: 48px;
    object-fit: cover;
    border-radius: 10px;      /* Soft corners */
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border: 2px solid #f3e6e6;
    background: #fff;
}

/* View Button Styles */
.btn-view {
    background: var(--primary-color) !important;
    color: white !important;
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin: 0 2px;
}

.btn-view:hover, .btn-view:focus {
    background: var(--primary-dark) !important;
    color: white !important;
    transform: translateY(-1px);
    box-shadow: 0 0.15rem 1.75rem 0 rgba(139, 69, 67, 0.15);
}

.btn-view:active {
    transform: translateY(0);
}

.btn-view i {
    margin: 0;
    font-size: 0.875rem;
}

.btn-edit {
    background: #C4804D !important;
    color: #fff !important;
    border: none;
    border-radius: 0.75rem;
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin: 0 2px;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(196, 128, 77, 0.10);
    transition: background 0.2s, color 0.2s;
}
.btn-edit i {
    color: #fff !important;
    font-size: 0.875rem;
    margin: 0;
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

    /* Date validation styling */
    .form-control.is-invalid {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }

    .form-control.is-invalid:focus {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }
</style>

<div class="container-fluid px-4">
    <h1 class="section-title"><span class="section-icon"><i class="fas fa-carrot"></i></span>Ingredient Management</h1>
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-mortar-pestle me-1"></i>
                        Ingredient List
                    </div>
                    <div>
                        <a href="#" class="btn btn-success" id="addIngredientBtn">
                            <i class="fas fa-plus me-1"></i> Add Ingredient
                        </a>
                        <a href="#" class="btn btn-primary ms-2" id="importCsvBtn">
                            <i class="fas fa-file-import me-1"></i> Insert CSV
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table id="ingredientTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <!--<th>ID</th>-->
                                <th>Category</th>
                                <th>Ingredient Name</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Consume Before</th>
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

<!-- Edit Ingredient Modal -->
<div class="modal fade" id="editIngredientModal" tabindex="-1" aria-labelledby="editIngredientModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-fullscreen-lg-down modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body" id="editIngredientModalBody">
        <!-- AJAX-loaded content here -->
      </div>
    </div>
  </div>
</div>

<!-- View Ingredient Modal -->
<div class="modal fade" id="viewIngredientModal" tabindex="-1" aria-labelledby="viewIngredientModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-maroon text-white">
        <h5 class="modal-title" id="viewIngredientModalLabel">
          <i class="fas fa-eye me-2"></i>Ingredient Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="viewIngredientModalBody">
        <!-- Content will be loaded here -->
      </div>
    </div>
  </div>
</div>

<!-- Enhanced Add Ingredient Modal -->
<div class="modal fade" id="addIngredientModal" tabindex="-1" aria-labelledby="addIngredientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content enhanced-add-ingredient-modal">
            <!-- Enhanced Modal Header -->
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <div class="add-icon me-3">
                        <i class="fas fa-carrot"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="addIngredientModalLabel">Add Ingredient</h5>
                        <small class="text-light opacity-75">Add new ingredient to inventory</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close">
                    <span class="close-x">×</span>
                </button>
            </div>

            <!-- Enhanced Modal Body -->
            <div class="modal-body">
                <form id="addIngredientForm">
                    <div class="row g-4">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <div class="form-section">
                                <h6 class="section-title">
                                    <i class="fas fa-info-circle me-2"></i>Basic Information
                                </h6>
                                
                                <div class="mb-4">
                                    <label for="category_id" class="form-label">
                                        <i class="fas fa-tag me-1"></i>Category Name
                                    </label>
                                    <select name="category_id" id="category_id" class="form-select form-select-lg" required>
                                        <option value="">Select Category</option>
                                        <?php 
                                        $categories = $pdo->query("SELECT category_id, category_name FROM pos_category WHERE status = 'active' ORDER BY category_name")->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($categories as $category): ?>
                                            <option value="<?php echo htmlspecialchars($category['category_id']); ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Select the ingredient category
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="ingredient_name" class="form-label">
                                        <i class="fas fa-carrot me-1"></i>Ingredient Name
                                    </label>
                                    <input type="text" name="ingredient_name" id="ingredient_name" class="form-control form-control-lg" placeholder="Enter ingredient name" required>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Enter the name of the ingredient
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="ingredient_quantity" class="form-label">
                                        <i class="fas fa-weight-hanging me-1"></i>Quantity
                                    </label>
                                    <input type="number" name="ingredient_quantity" id="ingredient_quantity" class="form-control form-control-lg" min="0" step="0.01" placeholder="0.00" required>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Enter the quantity of the ingredient
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="minimum_stock" class="form-label">
                                        <i class="fas fa-exclamation-triangle me-1" style="color: #dc3545;"></i>Minimum Stock Threshold
                                    </label>
                                    <input type="number" name="minimum_stock" id="minimum_stock" class="form-control form-control-lg" min="0" step="0.01" placeholder="0.00" required>
                                    <div class="form-text">
                                        <i class="fas fa-bell me-1" style="color: #ffc107;"></i>
                                        <strong>Alert System:</strong> When current stock falls below this threshold, stockman and admin will be automatically notified
                                    </div>
                                    <div class="alert alert-warning mt-2" style="font-size: 0.85rem;">
                                        <i class="fas fa-lightbulb me-1"></i>
                                        <strong>Tip:</strong> Set this to 10-20% of your maximum capacity to ensure timely restocking
                                    </div>
                                </div>
                                

                                
                                <div class="mb-4">
                                    <label for="date_added" class="form-label">
                                        <i class="fas fa-calendar-plus me-1"></i>Date Added
                                    </label>
                                    <input type="date" name="date_added" id="date_added" class="form-control form-control-lg" value="<?php echo date('Y-m-d'); ?>" readonly>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Date when ingredient was added (auto-filled)
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Column -->
                        <div class="col-md-6">
                            <div class="form-section">
                                <h6 class="section-title">
                                    <i class="fas fa-cog me-2"></i>Details & Status
                                </h6>
                                
                                <div class="mb-4">
                                    <label for="ingredient_unit" class="form-label">
                                        <i class="fas fa-ruler me-1"></i>Unit
                                    </label>
                                    <input type="text" name="ingredient_unit" id="ingredient_unit" class="form-control form-control-lg" placeholder="e.g., kg, liters, pieces" required>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Unit of measurement (kg, liters, pieces, etc.)
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="consume_before" class="form-label">
                                        <i class="fas fa-calendar-times me-1"></i>Consume Before Date
                                    </label>
                                    <input type="date" name="consume_before" id="consume_before" class="form-control form-control-lg" required>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Expiry date or best before date (must be after date added)
                                    </div>
                                    <div id="consume_before_error" class="text-danger" style="display: none;">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Consume before date must be after the date added
                                    </div>
                                </div>
                                

                                
                                <div class="mb-4">
                                    <label for="ingredient_status" class="form-label">
                                        <i class="fas fa-toggle-on me-1"></i>Status
                                    </label>
                                    <select name="ingredient_status" id="ingredient_status" class="form-select form-select-lg" required>
                                        <option value="Available">🟢 Available</option>
                                        <option value="Out of Stock">🔴 Out of Stock</option>
                                        <option value="Low Stock">🟡 Low Stock</option>
                                    </select>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Current availability status of the ingredient
                                    </div>
                                </div>
                                

                                
                                <div class="mb-4">
                                    <label for="notes" class="form-label">
                                        <i class="fas fa-sticky-note me-1"></i>Notes
                                    </label>
                                    <textarea name="notes" id="notes" class="form-control form-control-lg" rows="3" placeholder="Additional notes about the ingredient"></textarea>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Any additional information about the ingredient
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
                        <button type="button" class="btn btn-primary btn-lg" id="saveIngredient">
                            <i class="fas fa-plus me-2"></i>Add Ingredient
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Import CSV/Excel Modal -->
<div class="modal fade" id="importCsvModal" tabindex="-1" aria-labelledby="importCsvModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content enhanced-import-modal landscape-modal">
      <!-- Enhanced Modal Header -->
      <div class="modal-header">
        <div class="d-flex align-items-center">
          <div class="import-icon me-3">
            <i class="fas fa-file-import"></i>
          </div>
          <div>
            <h5 class="modal-title mb-0" id="importCsvModalLabel">Import Ingredients from CSV</h5>
            <small class="text-light opacity-75">Bulk import ingredients from CSV/Excel file</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close">
          <span class="close-x">×</span>
        </button>
      </div>
      
      <!-- Enhanced Modal Body -->
      <div class="modal-body">
        <form id="importCsvForm" enctype="multipart/form-data">
          <div class="row g-4">
            <!-- Left Column - File Upload -->
            <div class="col-lg-6">
              <div class="import-section">
                <h6 class="section-title">
                  <i class="fas fa-upload me-2"></i>File Upload
                </h6>
                
                <div class="upload-area-large">
                  <div class="upload-icon-large">
                    <i class="fas fa-cloud-upload-alt"></i>
                  </div>
                  <div class="upload-title">Choose your CSV/Excel file</div>
                  <div class="upload-description">
                    Drag and drop your file here or click to browse
                  </div>
                  <div class="upload-formats">
                    <span class="format-badge">CSV</span>
                    <span class="format-badge">XLSX</span>
                    <span class="format-badge">XLS</span>
                  </div>
                  <input type="file" class="file-input-large" id="csvFile" name="csvFile" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" required>
                </div>
              </div>
            </div>
            
            <!-- Right Column - Template Requirements -->
            <div class="col-lg-6">
              <div class="template-section">
                <h6 class="section-title">
                  <i class="fas fa-info-circle me-2"></i>Template Requirements
                </h6>
                
                <div class="template-info">
                  <div class="template-header">
                    <i class="fas fa-table me-2"></i>
                    Required columns for your CSV file:
                  </div>
                  
                  <div class="columns-grid-landscape">
                    <div class="column-badge">category_id</div>
                    <div class="column-badge">ingredient_name</div>
                    <div class="column-badge">ingredient_quantity</div>
                    <div class="column-badge">ingredient_unit</div>
                    <div class="column-badge">branch_id</div>
                    <div class="column-badge">ingredient_status</div>
                    <div class="column-badge">consume_before</div>
                    <div class="column-badge">[notes]</div>
                  </div>
                  
                  <div class="template-download">
                    <div class="download-link">
                      <i class="fas fa-download me-2"></i>
                      <a href="sample_ingredients_import.csv" download>Download Sample CSV Template</a>
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
              Esc to close
            </small>
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
              <i class="fas fa-times me-2"></i>Cancel
            </button>
            <button type="submit" class="btn btn-primary btn-lg" form="importCsvForm">
              <i class="fas fa-file-import me-2"></i>Import Ingredients
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Compact Archive Confirmation Modal -->
<div class="modal fade" id="archiveConfirmationModal" tabindex="-1" aria-labelledby="archiveConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content normal-archive-modal">
            <div class="modal-header normal-archive-header">
                <div class="d-flex align-items-center">
                    <div class="archive-icon-container me-2">
                        <i class="fas fa-archive"></i>
                    </div>
                    <div>
                        <h6 class="modal-title mb-0 text-white" id="archiveConfirmationModalLabel">Archive Ingredient</h6>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body normal-archive-body">
                <div class="text-center">
                    <div class="archive-icon-normal mb-3">
                        <i class="fas fa-archive"></i>
                    </div>
                    <h6 class="archive-title mb-3" id="archiveConfirmationMessage">Are you sure you want to archive this item?</h6>
                    <div class="archive-info-normal">
                        <div class="info-item-normal">
                            <i class="fas fa-shield-alt me-2"></i>
                            <span>Data will be safely stored</span>
                        </div>
                        <div class="info-item-normal">
                            <i class="fas fa-undo me-2"></i>
                            <span>Can be restored anytime</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer normal-archive-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-archive-confirm btn-sm" id="confirmArchiveBtn">
                    <i class="fas fa-archive me-1"></i>Archive
                </button>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<!-- Add SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* Compact Archive Modal Styles */
.normal-archive-modal {
    border: none;
    border-radius: 0.75rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    overflow: hidden;
    animation: modalSlideIn 0.3s ease-out;
    max-width: 400px;
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
    padding: 1rem 1.5rem;
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
    width: 35px;
    height: 35px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.archive-icon-container i {
    font-size: 1rem;
    color: white;
}

.normal-archive-body {
    padding: 1.5rem;
    background: #ffffff;
}

.archive-icon-normal {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #8B4543, #b97a6a);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    box-shadow: 0 5px 15px rgba(139, 69, 67, 0.3);
}

.archive-icon-normal i {
    font-size: 1.5rem;
    color: white;
}

.archive-title {
    color: #2c3e50;
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 1rem;
}

.archive-info-normal {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1rem;
    border: 1px solid rgba(139, 69, 67, 0.1);
    margin-top: 0.5rem;
}

.info-item-normal {
    display: flex;
    align-items: center;
    padding: 0.25rem 0;
    font-weight: 500;
    color: #495057;
    font-size: 0.875rem;
}

.info-item-normal i {
    color: #8B4543;
    width: 16px;
}

.normal-archive-footer {
    background: #f8f9fa;
    border: none;
    padding: 1rem 1.5rem;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
}

.normal-archive-footer .btn {
    border-radius: 0.5rem;
    font-weight: 500;
    transition: all 0.2s ease;
    border: none;
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

<script>
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
    $('#ingredientTable').DataTable({
        "processing": true,
        "serverSide": false, // Changed to false to load all data at once
        "pageLength": 5, // Show 5 records per page by default
        "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]], // Multiple options including "All"
        "ajax": {
            "url": "get_all_ingredients.php", // New endpoint for all data
            "type": "GET"
        },
        "columns": [
            //{ "data": "ingredient_id" },
            { "data": "category_name" },
            { "data": "ingredient_name" },
            { "data": "ingredient_quantity" },
            { "data": "ingredient_unit" },
            { 
                "data": "consume_before",
                "render": function(data, type, row) {
                    if (data) {
                        const date = new Date(data);
                        const today = new Date();
                        const diffTime = date - today;
                        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                        
                        let badgeClass = 'badge bg-success';
                        if (diffDays <= 7) {
                            badgeClass = 'badge bg-danger';
                        } else if (diffDays <= 30) {
                            badgeClass = 'badge bg-warning';
                        }
                        
                        return `<span class="${badgeClass}">${date.toLocaleDateString()}</span>`;
                    }
                    return '<span class="badge bg-secondary">No date</span>';
                }
            },
            { 
                "data": "ingredient_status",
                "render": function(data, type, row) {
                    const currentStock = parseFloat(row.ingredient_quantity) || 0;
                    const threshold = parseFloat(row.minimum_stock) || 0;
                    const consumeBefore = row.consume_before;
                    
                    let badgeClass = 'badge bg-success';
                    let icon = '🟢';
                    let status = data;
                    let alertIndicator = '';
                    
                    // Check if expired first
                    let isExpired = false;
                    if (consumeBefore) {
                        const expiryDate = new Date(consumeBefore);
                        const today = new Date();
                        isExpired = expiryDate <= today;
                    }
                    
                    // Check status based on expiration and stock
                    if (currentStock <= 0 || isExpired) {
                        badgeClass = 'badge bg-danger';
                        icon = '🔴';
                        status = isExpired ? 'Expired' : 'Out of Stock';
                        alertIndicator = '<i class="fas fa-bell text-danger ms-1" title="Critical Alert!"></i>';
                    } else if (currentStock <= threshold) {
                        badgeClass = 'badge bg-warning';
                        icon = '🟡';
                        status = 'Below Threshold';
                        alertIndicator = '<i class="fas fa-bell text-warning ms-1" title="Low Stock Alert!"></i>';
                    } else if (data === 'Low Stock') {
                        badgeClass = 'badge bg-warning';
                        icon = '🟡';
                        alertIndicator = '<i class="fas fa-bell text-warning ms-1" title="Manual Low Stock"></i>';
                    }
                    
                    return `
                        <div>
                            <span class="${badgeClass}">${icon} ${status}</span>
                            ${alertIndicator}
                            ${threshold > 0 ? `<br><small class="text-muted">Threshold: ${threshold}</small>` : ''}
                        </div>
                    `;
                }
            },
            {
                "data": null,
                "render": function(data, type, row) {
                    return `
                        <div class="text-center">
                            <button class="btn btn-view btn-sm view-ingredient-btn" data-id="${row.ingredient_id}" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-edit btn-sm edit-ingredient-btn" data-id="${row.ingredient_id}" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-secondary btn-sm archive-ingredient-btn" data-id="${row.ingredient_id}" title="Archive">
                                <i class="fas fa-archive"></i>
                            </button>
                        </div>`;
                }
            }
        ],
        "dom": '<"top"lf>rt<"bottom"ip><"clear">', // Show length menu, search, pagination, and info
        "paging": true, // Enable pagination
        "pagingType": "simple", // Show only Previous/Next buttons
        "info": true, // Show "Showing X to Y of Z entries" info
        "searching": true, // Keep search functionality
        "ordering": false, // Disable sorting functionality
        "lengthChange": true // Show the "Show X entries" dropdown
    });

});

$(document).on('click', '.edit-ingredient-btn', function(e) {
    e.preventDefault();
    var ingredientId = $(this).data('id');
    $('#editIngredientModalBody').html('<div class="text-center p-4">Loading...</div>');
    $('#editIngredientModal').modal('show');
    $.get('edit_ingredient.php', { id: ingredientId, modal: 1 }, function(data) {
        $('#editIngredientModalBody').html(data);
    });
});

// View ingredient button handler
$(document).on('click', '.view-ingredient-btn', function(e) {
    e.preventDefault();
    var ingredientId = $(this).data('id');
    $('#viewIngredientModalBody').html('<div class="text-center p-4">Loading...</div>');
    $('#viewIngredientModal').modal('show');
    $.get('view_ingredient.php', { id: ingredientId }, function(data) {
        $('#viewIngredientModalBody').html(data);
    });
});

// Handle view ingredient modal close button
$(document).on('click', '#viewIngredientModal .btn-outline-secondary', function(e) {
    e.preventDefault();
    $('#viewIngredientModal').modal('hide');
    $('body').removeClass('modal-open');
    $('.modal-backdrop').remove();
});



$(document).on('submit', '#editIngredientModalBody form', function(e) {
    e.preventDefault();
    var form = $(this);
    var formData = form.serialize();
    
    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Properly hide modal and remove backdrop
                $('#editIngredientModal').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
                
                // Refresh the DataTable
                $('#ingredientTable').DataTable().ajax.reload();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: response.message,
                    confirmButtonColor: '#8B4543'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: response.message,
                    confirmButtonColor: '#8B4543'
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while updating the ingredient.',
                confirmButtonColor: '#8B4543'
            });
        }
    });
});
// Intercept Cancel button
$(document).on('click', '#editIngredientModalBody .btn-cancel', function(e) {
    e.preventDefault();
    Swal.fire({
        icon: 'warning',
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        showCancelButton: true,
        confirmButtonColor: '#B33A3A',
        cancelButtonColor: '#8B4543',
        confirmButtonText: 'Yes, cancel!',
        cancelButtonText: 'No, keep editing',
        customClass: {popup: 'rounded-4'}
    }).then((result) => {
        if (result.dismiss === Swal.DismissReason.cancel) {
            // Re-open the edit modal or form here
            $('#editIngredientModal').modal('show');
        }
    });
});

$(document).on('click', '#addIngredientBtn', function(e) {
    e.preventDefault();
    $('#addIngredientModal').modal('show');
});

// Add modal hidden event handlers
$('#addIngredientModal').on('hidden.bs.modal', function () {
    $('body').removeClass('modal-open');
    $('.modal-backdrop').remove();
});

$('#editIngredientModal').on('hidden.bs.modal', function () {
    $('body').removeClass('modal-open');
    $('.modal-backdrop').remove();
});

$('#viewIngredientModal').on('hidden.bs.modal', function () {
    $('body').removeClass('modal-open');
    $('.modal-backdrop').remove();
});



// Date validation function
function validateConsumeBefore() {
    const dateAdded = document.getElementById('date_added').value;
    const consumeBefore = document.getElementById('consume_before').value;
    const errorDiv = document.getElementById('consume_before_error');
    const consumeBeforeInput = document.getElementById('consume_before');
    
    if (dateAdded && consumeBefore) {
        const addedDate = new Date(dateAdded);
        const consumeDate = new Date(consumeBefore);
        
        if (consumeDate <= addedDate) {
            errorDiv.style.display = 'block';
            consumeBeforeInput.classList.add('is-invalid');
            return false;
        } else {
            errorDiv.style.display = 'none';
            consumeBeforeInput.classList.remove('is-invalid');
            return true;
        }
    }
    
    return true;
}

// Add event listeners for date validation
$(document).ready(function() {
    $('#date_added, #consume_before').on('change', function() {
        validateConsumeBefore();
    });
    
    // Set minimum date for consume_before based on date_added
    $('#date_added').on('change', function() {
        const dateAdded = $(this).val();
        if (dateAdded) {
            $('#consume_before').attr('min', dateAdded);
        }
    });
    
    // Set initial minimum date
    const currentDate = $('#date_added').val();
    if (currentDate) {
        $('#consume_before').attr('min', currentDate);
    }
});

// Handle Add Ingredient button click
$('#saveIngredient').click(function() {
    // Validate dates before submission
    if (!validateConsumeBefore()) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Date',
            text: 'Consume before date must be after the date added.',
            confirmButtonColor: '#8B4543'
        });
        return;
    }
    
    var formData = $('#addIngredientForm').serialize();
    
    $.ajax({
        url: 'add_ingredient.php',
        type: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                // Properly hide modal and remove backdrop
                $('#addIngredientModal').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
                
                $('#addIngredientForm')[0].reset();
                $('#ingredientTable').DataTable().ajax.reload();
                Swal.fire('Success', 'Ingredient saved successfully!', 'success');
            } else {
                Swal.fire('Error', response.message || 'Failed to add ingredient.', 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'An error occurred while processing your request.', 'error');
        }
    });
});

$(document).on('click', '#importCsvBtn', function(e) {
    e.preventDefault();
    $('#importCsvModal').modal('show');
});

$('#importCsvForm').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    $.ajax({
        url: 'import_ingredients.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                $('#importCsvModal').modal('hide');
                $('#ingredientTable').DataTable().ajax.reload();
                Swal.fire('Success', 'Ingredients imported!', 'success');
            } else {
                Swal.fire('Error', response.message || 'Failed to import ingredients.', 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Failed to import ingredients.', 'error');
        }
    });

    // Archive ingredient functionality
    $(document).on('click', '.archive-ingredient-btn', function() {
        let ingredientId = $(this).data('id');
        let ingredientName = $(this).closest('tr').find('td:eq(0)').text();
        
        // Show confirmation modal
        showArchiveConfirmationModal(
            'Archive Ingredient',
            `Are you sure you want to archive the ingredient "${ingredientName}"?`,
            function() {
                // Confirm callback
                $.ajax({
                    url: 'archive_ingredient.php',
                    type: 'POST',
                    data: { id: ingredientId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success', 'Ingredient archived successfully!', 'success');
                            ingredientTable.ajax.reload();
                        } else {
                            Swal.fire('Error', response.message || 'Failed to archive ingredient.', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'An error occurred while archiving the ingredient.', 'error');
                    }
                });
            }
        );
    });
});
</script>
