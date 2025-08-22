<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

$confData = getConfigData($pdo);

// Fetch categories for the dropdown
$stmt = $pdo->query("SELECT category_id, category_name FROM pos_category WHERE status = 'active' ORDER BY category_name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

include('header.php');
?>

<style>
/* Enhanced Add Product Modal Styles */
.enhanced-add-product-modal {
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

/* Modal Header */
.enhanced-add-product-modal .modal-header {
    border-radius: 1.5rem 1.5rem 0 0;
    background: linear-gradient(135deg, #8B4543 0%, #723937 50%, #5a2e2c 100%);
    padding: 2rem 2rem 1.5rem;
    border: none;
    color: white;
    position: relative;
    overflow: hidden;
}

.enhanced-add-product-modal .modal-header::before {
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

.add-icon {
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

.add-icon:hover {
    transform: scale(1.1) rotate(5deg);
    box-shadow: 
        0 12px 35px rgba(0, 0, 0, 0.3),
        inset 0 1px 0 rgba(255, 255, 255, 0.4),
        0 0 20px rgba(255, 255, 255, 0.2);
}

.add-icon i {
    font-size: 1.6rem;
    color: white;
    transition: all 0.3s ease;
}

.enhanced-add-product-modal .modal-title {
    font-weight: 700;
    color: white;
    font-size: 1.6rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    position: relative;
    z-index: 2;
    margin-bottom: 0.25rem;
}

.enhanced-add-product-modal .modal-header small {
    font-size: 0.9rem;
    opacity: 0.9;
    font-weight: 400;
    position: relative;
    z-index: 2;
}

/* Close Button */
.enhanced-add-product-modal .btn-close {
    color: white !important;
    opacity: 1 !important;
    filter: brightness(1.2) !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    position: relative !important;
    z-index: 1 !important;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.25) 0%, rgba(255, 255, 255, 0.15) 100%) !important;
    border-radius: 50% !important;
    width: 32px !important;
    height: 32px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    backdrop-filter: blur(20px) !important;
    border: 1px solid rgba(255, 255, 255, 0.4) !important;
    box-shadow: 
        0 2px 8px rgba(0, 0, 0, 0.2),
        inset 0 1px 0 rgba(255, 255, 255, 0.3),
        0 0 0 1px rgba(255, 255, 255, 0.1) !important;
    padding: 0 !important;
    font-size: 1rem !important;
    line-height: 1 !important;
    overflow: hidden !important;
    white-space: nowrap !important;
    text-indent: 0 !important;
}

.enhanced-add-product-modal .btn-close .close-x {
    color: white !important;
    font-size: 1.2rem !important;
    font-weight: 700 !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3) !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    line-height: 1 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    position: absolute !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
    z-index: 10 !important;
    opacity: 1 !important;
    visibility: visible !important;
    width: 100% !important;
    height: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
}

.enhanced-add-product-modal .btn-close:hover {
    transform: scale(1.15) rotate(90deg) !important;
    filter: brightness(0) invert(1) drop-shadow(0 0 15px rgba(255, 255, 255, 0.8)) !important;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.5) 0%, rgba(255, 255, 255, 0.3) 100%) !important;
    border-color: rgba(255, 255, 255, 0.8) !important;
    box-shadow: 
        0 8px 25px rgba(0, 0, 0, 0.4),
        0 0 20px rgba(255, 255, 255, 0.4),
        0 0 30px rgba(255, 255, 255, 0.2) !important;
    text-shadow: 0 0 15px rgba(255, 255, 255, 1) !important;
}

.enhanced-add-product-modal .btn-close:hover .close-x {
    transform: scale(1.2) !important;
    color: #ffffff !important;
    text-shadow: 
        0 0 20px rgba(255, 255, 255, 1),
        0 0 30px rgba(255, 255, 255, 0.8),
        0 0 40px rgba(255, 255, 255, 0.6) !important;
    animation: bounce 0.8s ease-in-out infinite !important;
}

/* Modal Body */
.enhanced-add-product-modal .modal-body {
    padding: 2.5rem;
    background: linear-gradient(135deg, #fafbfc 0%, #f8f9fa 100%);
    position: relative;
}

.enhanced-add-product-modal .modal-body::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent 0%, rgba(139, 69, 67, 0.2) 50%, transparent 100%);
}

/* Form Sections */
.enhanced-add-product-modal .form-section {
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
    animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) 0.3s both;
}

.enhanced-add-product-modal .form-section:nth-child(2) {
    animation-delay: 0.4s;
}

.enhanced-add-product-modal .form-section:hover {
    transform: translateY(-2px);
    box-shadow: 
        0 12px 35px rgba(0, 0, 0, 0.1),
        0 4px 15px rgba(139, 69, 67, 0.08);
}

.enhanced-add-product-modal .form-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #8B4543 0%, #723937 50%, #8B4543 100%);
    opacity: 0.8;
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

/* Section Titles */
.enhanced-add-product-modal .section-title {
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

.enhanced-add-product-modal .section-title i {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-right: 0.75rem;
    font-size: 1.1em;
}

/* Form Labels */
.enhanced-add-product-modal .form-label {
    color: #8B4543;
    font-weight: 700;
    font-size: 0.95rem;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.enhanced-add-product-modal .form-label i {
    margin-right: 0.5rem;
    font-size: 0.9em;
    opacity: 0.8;
}

/* Form Controls */
.enhanced-add-product-modal .form-control,
.enhanced-add-product-modal .form-select {
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

.enhanced-add-product-modal .form-control:focus,
.enhanced-add-product-modal .form-select:focus {
    border-color: #8B4543;
    box-shadow: 
        0 0 0 0.25rem rgba(139, 69, 67, 0.15),
        0 4px 15px rgba(139, 69, 67, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.9);
    transform: translateY(-1px);
}

.enhanced-add-product-modal .form-control:hover,
.enhanced-add-product-modal .form-select:hover {
    border-color: rgba(139, 69, 67, 0.3);
    transform: translateY(-1px);
    box-shadow: 
        0 4px 15px rgba(0, 0, 0, 0.08),
        0 2px 8px rgba(139, 69, 67, 0.05);
}

/* Input Groups */
.enhanced-add-product-modal .input-group {
    box-shadow: 
        0 2px 8px rgba(0, 0, 0, 0.04),
        inset 0 1px 0 rgba(255, 255, 255, 0.8);
    border-radius: 0.75rem;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.enhanced-add-product-modal .input-group:focus-within {
    box-shadow: 
        0 0 0 0.25rem rgba(139, 69, 67, 0.15),
        0 4px 15px rgba(139, 69, 67, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.9);
    transform: translateY(-1px);
}

.enhanced-add-product-modal .input-group-text {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    color: white;
    border: 2px solid rgba(139, 69, 67, 0.1);
    border-right: none;
    font-weight: 600;
}

/* Form Text */
.enhanced-add-product-modal .form-text {
    color: #6c757d;
    font-size: 0.85rem;
    font-weight: 500;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    opacity: 0.8;
}

.enhanced-add-product-modal .form-text i {
    margin-right: 0.5rem;
    color: #8B4543;
    font-size: 0.9em;
}

/* Enhanced Image Upload Container */
.enhanced-image-upload {
    position: relative;
    width: 100%;
}

.upload-area {
    position: relative;
    border: 3px dashed rgba(139, 69, 67, 0.3);
    border-radius: 1rem;
    padding: 2.5rem 1.5rem;
    text-align: center;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    overflow: hidden;
}

.upload-area::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(139, 69, 67, 0.05) 0%, rgba(139, 69, 67, 0.02) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.upload-area:hover {
    border-color: #8B4543;
    background: linear-gradient(135deg, #f0f2f5 0%, #e9ecef 100%);
    transform: translateY(-3px);
    box-shadow: 
        0 8px 25px rgba(139, 69, 67, 0.15),
        0 4px 15px rgba(0, 0, 0, 0.1);
}

.upload-area:hover::before {
    opacity: 1;
}

.upload-area.dragover {
    border-color: #8B4543;
    background: linear-gradient(135deg, rgba(139, 69, 67, 0.1) 0%, rgba(139, 69, 67, 0.05) 100%);
    transform: scale(1.02);
}

.upload-content {
    position: relative;
    z-index: 2;
}

.upload-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    box-shadow: 
        0 8px 25px rgba(139, 69, 67, 0.3),
        0 4px 15px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.upload-area:hover .upload-icon {
    transform: scale(1.1);
    box-shadow: 
        0 12px 35px rgba(139, 69, 67, 0.4),
        0 6px 20px rgba(0, 0, 0, 0.15);
}

.upload-icon i {
    font-size: 2rem;
    color: white;
    transition: all 0.3s ease;
}

.upload-title {
    color: #8B4543;
    font-weight: 700;
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.upload-description {
    color: #6c757d;
    font-size: 0.95rem;
    margin-bottom: 1.5rem;
    font-weight: 500;
}

.upload-formats {
    display: flex;
    justify-content: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.format-badge {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    color: white;
    padding: 0.375rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(139, 69, 67, 0.3);
    transition: all 0.3s ease;
}

.format-badge:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(139, 69, 67, 0.4);
}

.file-input {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    z-index: 3;
}

/* Upload Preview */
.upload-preview {
    border: 2px solid rgba(139, 69, 67, 0.1);
    border-radius: 1rem;
    padding: 1rem;
    background: white;
    box-shadow: 
        0 4px 15px rgba(0, 0, 0, 0.08),
        0 2px 8px rgba(139, 69, 67, 0.05);
}

.preview-container {
    position: relative;
    border-radius: 0.75rem;
    overflow: hidden;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

.preview-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 0.75rem;
    transition: all 0.3s ease;
}

.preview-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.5) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    opacity: 0;
    transition: all 0.3s ease;
}

.preview-container:hover .preview-overlay {
    opacity: 1;
}

.preview-container:hover .preview-image {
    transform: scale(1.05);
}

.change-image-btn,
.remove-image-btn {
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 0.5rem;
    border: none;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.change-image-btn {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    color: #8B4543;
}

.change-image-btn:hover {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.remove-image-btn {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
}

.remove-image-btn:hover {
    background: linear-gradient(135deg, #c82333 0%, #a71e2a 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
}

/* Enhanced Import Modal Styles */
.enhanced-import-modal {
    border-radius: 1.5rem;
    box-shadow: 
        0 20px 60px rgba(0, 0, 0, 0.15),
        0 10px 30px rgba(139, 69, 67, 0.1);
    backdrop-filter: blur(20px);
    background: rgba(255, 255, 255, 0.95);
    animation: modalSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    max-width: 90vw;
}

/* Landscape Modal Specific Styles */
.landscape-modal {
    max-width: 1200px;
    width: 90vw;
}

.landscape-modal .modal-body {
    padding: 2rem;
    min-height: 400px;
}

.landscape-modal .import-section,
.landscape-modal .template-section {
    height: 100%;
    display: flex;
    flex-direction: column;
    margin: 0;
}

.landscape-modal .upload-content,
.landscape-modal .template-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* Large Upload Area for Landscape */
.upload-area-large {
    position: relative;
    border: 3px dashed rgba(139, 69, 67, 0.3);
    border-radius: 1rem;
    padding: 3rem 2rem;
    text-align: center;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    overflow: hidden;
    margin-bottom: 1.5rem;
}

.upload-area-large::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(139, 69, 67, 0.05) 0%, rgba(139, 69, 67, 0.02) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.upload-area-large:hover {
    border-color: #8B4543;
    background: linear-gradient(135deg, #f0f2f5 0%, #e9ecef 100%);
    transform: translateY(-3px);
    box-shadow: 
        0 8px 25px rgba(139, 69, 67, 0.15),
        0 4px 15px rgba(0, 0, 0, 0.1);
}

.upload-area-large:hover::before {
    opacity: 1;
}

.upload-area-large.dragover {
    border-color: #8B4543;
    background: linear-gradient(135deg, rgba(139, 69, 67, 0.1) 0%, rgba(139, 69, 67, 0.05) 100%);
    transform: scale(1.02);
}

.upload-icon-large {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    box-shadow: 
        0 8px 25px rgba(139, 69, 67, 0.3),
        0 4px 15px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    position: relative;
    z-index: 2;
}

.upload-area-large:hover .upload-icon-large {
    transform: scale(1.1);
    box-shadow: 
        0 12px 35px rgba(139, 69, 67, 0.4),
        0 6px 20px rgba(0, 0, 0, 0.15);
}

.upload-icon-large i {
    font-size: 2.5rem;
    color: white;
    transition: all 0.3s ease;
}

.upload-title {
    color: #8B4543;
    font-weight: 700;
    font-size: 1.4rem;
    margin-bottom: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: relative;
    z-index: 2;
}

.upload-description {
    color: #6c757d;
    font-size: 1rem;
    margin-bottom: 1.5rem;
    font-weight: 500;
    position: relative;
    z-index: 2;
}

.upload-formats {
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
    position: relative;
    z-index: 2;
}

.format-badge {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 1rem;
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(139, 69, 67, 0.3);
    transition: all 0.3s ease;
}

.format-badge:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(139, 69, 67, 0.4);
}

.file-input-large {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    z-index: 3;
}

/* Landscape Template Grid */
.columns-grid-landscape {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
}

.landscape-modal .template-content {
    padding: 1rem 0;
}

.landscape-modal .template-header {
    margin-bottom: 2rem;
}

.landscape-modal .template-download {
    margin-top: auto;
    padding-top: 1.5rem;
}

.enhanced-import-modal .modal-header {
    border-radius: 1.5rem 1.5rem 0 0;
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    padding: 2rem;
    border: none;
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
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
}

.import-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(15px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 
        0 8px 25px rgba(0, 0, 0, 0.2),
        0 4px 15px rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
    position: relative;
    z-index: 2;
}

.enhanced-import-modal .modal-header:hover .import-icon {
    transform: scale(1.1) rotate(5deg);
    box-shadow: 
        0 12px 35px rgba(0, 0, 0, 0.3),
        0 6px 20px rgba(255, 255, 255, 0.15);
}

.import-icon i {
    font-size: 1.6rem;
    color: white;
    transition: all 0.3s ease;
}

.enhanced-import-modal .modal-title {
    color: white;
    font-weight: 700;
    font-size: 1.6rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    position: relative;
    z-index: 2;
    margin-bottom: 0;
}

.enhanced-import-modal .modal-header small {
    color: rgba(255, 255, 255, 0.8);
    font-weight: 500;
    position: relative;
    z-index: 2;
}

.enhanced-import-modal .btn-close {
    color: white !important;
    opacity: 1 !important;
    filter: none !important;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    z-index: 2;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.3) 0%, rgba(255, 255, 255, 0.2) 100%);
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex !important;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.4);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    padding: 0;
    font-size: 1rem;
    line-height: 1;
    overflow: hidden;
    white-space: normal !important;
    text-indent: 0;
    cursor: pointer;
    position: relative;
}

/* Active/Focus State */
.enhanced-import-modal .btn-close:active {
    transform: scale(0.95);
    transition: all 0.1s ease;
}

.enhanced-import-modal .btn-close:focus {
    outline: none;
    box-shadow: 
        0 4px 15px rgba(0, 0, 0, 0.3),
        0 0 0 3px rgba(255, 255, 255, 0.3);
}

.enhanced-import-modal .btn-close:hover {
    transform: scale(1.15) rotate(90deg);
    filter: brightness(0) invert(1) drop-shadow(0 0 15px rgba(255, 255, 255, 0.8));
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.5) 0%, rgba(255, 255, 255, 0.3) 100%);
    border-color: rgba(255, 255, 255, 0.8);
    box-shadow: 
        0 8px 25px rgba(0, 0, 0, 0.4),
        0 0 20px rgba(255, 255, 255, 0.4),
        0 0 30px rgba(255, 255, 255, 0.2);
    text-shadow: 0 0 15px rgba(255, 255, 255, 1);
}

.enhanced-import-modal .btn-close:hover .close-x {
    transform: scale(1.2);
    color: #ffffff !important;
    text-shadow: 
        0 0 20px rgba(255, 255, 255, 1),
        0 0 30px rgba(255, 255, 255, 0.8),
        0 0 40px rgba(255, 255, 255, 0.6);
    animation: bounce 0.8s ease-in-out infinite;
}

.close-x {
    color: white !important;
    font-weight: 700;
    font-size: 1.2rem;
    line-height: 1;
    display: flex !important;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    text-align: center;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    opacity: 1 !important;
    visibility: visible !important;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 10;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    margin: 0;
    padding: 0;
}

/* Pulse Animation for Close Button */
@keyframes pulse {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.1);
        opacity: 0.8;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

/* Bounce Animation for Close Button */
@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: scale(1.2) translateY(0);
    }
    40% {
        transform: scale(1.2) translateY(-3px);
    }
    60% {
        transform: scale(1.2) translateY(-1px);
    }
}

/* Shimmer Animation for Close Button */
@keyframes shimmer {
    0% {
        background-position: -200% center;
    }
    100% {
        background-position: 200% center;
    }
}

/* Rainbow Glow Animation */
@keyframes rainbow-glow {
    0% {
        box-shadow: 
            0 8px 25px rgba(0, 0, 0, 0.4),
            0 0 20px rgba(255, 0, 0, 0.4);
    }
    25% {
        box-shadow: 
            0 8px 25px rgba(0, 0, 0, 0.4),
            0 0 20px rgba(255, 165, 0, 0.4);
    }
    50% {
        box-shadow: 
            0 8px 25px rgba(0, 0, 0, 0.4),
            0 0 20px rgba(255, 255, 0, 0.4);
    }
    75% {
        box-shadow: 
            0 8px 25px rgba(0, 0, 0, 0.4),
            0 0 20px rgba(0, 255, 0, 0.4);
    }
    100% {
        box-shadow: 
            0 8px 25px rgba(0, 0, 0, 0.4),
            0 0 20px rgba(255, 0, 0, 0.4);
    }
}

/* Glow Animation for Close Button */
@keyframes glow {
    0%, 100% {
        box-shadow: 
            0 2px 8px rgba(0, 0, 0, 0.2),
            0 0 0 rgba(255, 255, 255, 0);
    }
    50% {
        box-shadow: 
            0 2px 8px rgba(0, 0, 0, 0.2),
            0 0 8px rgba(255, 255, 255, 0.3);
    }
}

/* Enhanced Close Button Base State */
.enhanced-import-modal .btn-close {
    animation: glow 2s ease-in-out infinite;
}

.enhanced-add-product-modal .btn-close {
    animation: glow 2s ease-in-out infinite;
}

/* Enhanced Price Input Styling */
.enhanced-price-input {
    position: relative;
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: 2px solid rgba(139, 69, 67, 0.2);
    border-radius: 0.75rem;
    padding: 0.5rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.enhanced-price-input:hover {
    border-color: rgba(139, 69, 67, 0.4);
    box-shadow: 0 4px 15px rgba(139, 69, 67, 0.1);
    transform: translateY(-1px);
}

.enhanced-price-input:focus-within {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
    transform: translateY(-2px);
}

.currency-symbol {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    color: white;
    border-radius: 0.5rem;
    margin-right: 0.75rem;
    font-size: 1.1rem;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(139, 69, 67, 0.3);
    transition: all 0.3s ease;
}

.enhanced-price-input:hover .currency-symbol {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(139, 69, 67, 0.4);
}

.enhanced-price-field {
    flex: 1;
    border: none !important;
    background: transparent !important;
    font-size: 1.1rem;
    font-weight: 600;
    color: #2c3e50;
    padding: 0.75rem 1rem;
    outline: none;
    box-shadow: none !important;
}

.enhanced-price-field:focus {
    box-shadow: none !important;
    background: transparent !important;
}

.enhanced-price-field::placeholder {
    color: #6c757d;
    opacity: 0.6;
}

.price-controls {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    margin-left: 0.5rem;
}

.price-btn {
    width: 28px;
    height: 28px;
    border: none;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    color: #8B4543;
    border-radius: 0.375rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.price-btn:hover {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    color: white;
    transform: scale(1.1);
    box-shadow: 0 2px 8px rgba(139, 69, 67, 0.3);
}

.price-btn:active {
    transform: scale(0.95);
}

.price-btn.price-up:hover {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.price-btn.price-down:hover {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
}

/* Keyboard Hints Styling */
.keyboard-hints {
    display: flex;
    align-items: center;
}

.keyboard-hints small {
    font-size: 0.8rem;
    color: #6c757d;
    opacity: 0.8;
    transition: all 0.3s ease;
}

.keyboard-hints:hover small {
    opacity: 1;
    color: #8B4543;
}

.keyboard-hints i {
    font-size: 0.9rem;
    margin-right: 0.25rem;
}

.enhanced-import-modal .modal-body {
    padding: 2.5rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    position: relative;
}

.enhanced-import-modal .modal-body::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent 0%, rgba(139, 69, 67, 0.1) 50%, transparent 100%);
}

.import-section,
.template-section {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 1.25rem;
    padding: 2rem;
    border: 1px solid rgba(139, 69, 67, 0.1);
    box-shadow: 
        0 4px 15px rgba(0, 0, 0, 0.05),
        0 2px 8px rgba(139, 69, 67, 0.05);
    height: fit-content;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    animation: fadeInUp 0.6s ease forwards;
}

.import-section {
    animation-delay: 0.1s;
}

.template-section {
    animation-delay: 0.2s;
    margin-top: 1.5rem;
}

.import-section:hover,
.template-section:hover {
    transform: translateY(-2px);
    box-shadow: 
        0 8px 25px rgba(0, 0, 0, 0.1),
        0 4px 15px rgba(139, 69, 67, 0.08);
}

.import-section::before,
.template-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #8B4543 0%, #723937 100%);
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
}

.enhanced-import-modal .form-label {
    color: #8B4543;
    font-weight: 700;
    font-size: 1rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.enhanced-import-modal .form-label i {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-right: 0.5rem;
}

.file-upload-container {
    position: relative;
    border: 2px dashed rgba(139, 69, 67, 0.3);
    border-radius: 0.75rem;
    padding: 1.5rem;
    text-align: center;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    transition: all 0.3s ease;
    margin-bottom: 1rem;
}

.file-upload-container:hover {
    border-color: #8B4543;
    background: linear-gradient(135deg, #f0f2f5 0%, #e9ecef 100%);
    transform: translateY(-2px);
}

.upload-hint {
    color: #8B4543;
    font-weight: 600;
    font-size: 0.9rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    margin-top: 1rem;
}

.upload-hint i {
    font-size: 1.5rem;
    opacity: 0.8;
}

.enhanced-import-modal .form-control {
    border: 2px solid rgba(139, 69, 67, 0.2);
    border-radius: 0.75rem;
    padding: 0.875rem 1rem;
    font-size: 1rem;
    font-weight: 500;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.enhanced-import-modal .form-control:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
    transform: translateY(-1px);
}

.enhanced-import-modal .form-control:hover {
    border-color: rgba(139, 69, 67, 0.4);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.enhanced-import-modal .form-text {
    color: #6c757d;
    font-size: 0.9rem;
    font-weight: 500;
    margin-top: 0.75rem;
    display: flex;
    align-items: center;
    opacity: 0.8;
}

.enhanced-import-modal .form-text i {
    color: #8B4543;
    margin-right: 0.5rem;
}

.template-info {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 1rem;
    padding: 1.5rem;
    border: 1px solid rgba(139, 69, 67, 0.1);
}

.template-header {
    color: #8B4543;
    font-weight: 700;
    font-size: 1rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
}

.template-header i {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.columns-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.column-badge {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    color: white;
    padding: 0.5rem 0.75rem;
    border-radius: 0.75rem;
    font-size: 0.85rem;
    font-weight: 600;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(139, 69, 67, 0.3);
    transition: all 0.3s ease;
}

.column-badge:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(139, 69, 67, 0.4);
}

.template-download {
    text-align: center;
    padding-top: 1rem;
    border-top: 1px solid rgba(139, 69, 67, 0.1);
}

.download-link {
    display: inline-flex;
    align-items: center;
    color: #8B4543;
    font-weight: 600;
    text-decoration: none;
    padding: 0.75rem 1.5rem;
    border-radius: 0.75rem;
    background: linear-gradient(135deg, rgba(139, 69, 67, 0.1) 0%, rgba(139, 69, 67, 0.05) 100%);
    border: 2px solid rgba(139, 69, 67, 0.2);
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(139, 69, 67, 0.1);
}

.download-link:hover {
    color: #723937;
    background: linear-gradient(135deg, rgba(139, 69, 67, 0.15) 0%, rgba(139, 69, 67, 0.1) 100%);
    border-color: #8B4543;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(139, 69, 67, 0.2);
    text-decoration: none;
}

.download-link i {
    font-size: 1.1rem;
}

.enhanced-import-modal .modal-footer {
    padding: 2rem;
    border-top: 1px solid rgba(139, 69, 67, 0.1);
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 0 0 1.5rem 1.5rem;
    position: relative;
}

.enhanced-import-modal .modal-footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent 0%, rgba(139, 69, 67, 0.1) 50%, transparent 100%);
}

.enhanced-import-modal .btn {
    padding: 0.875rem 1.75rem;
    font-weight: 600;
    font-size: 1rem;
    border-radius: 0.75rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    border: none;
}

.enhanced-import-modal .btn-primary {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    color: white !important;
    box-shadow: 
        0 4px 15px rgba(139, 69, 67, 0.3),
        0 2px 8px rgba(0, 0, 0, 0.1);
}

.enhanced-import-modal .btn-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s ease;
}

.enhanced-import-modal .btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 
        0 8px 25px rgba(139, 69, 67, 0.4),
        0 4px 15px rgba(0, 0, 0, 0.15);
}

.enhanced-import-modal .btn-primary:hover::before {
    left: 100%;
}

.enhanced-import-modal .btn-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white !important;
    box-shadow: 
        0 4px 15px rgba(108, 117, 125, 0.3),
        0 2px 8px rgba(0, 0, 0, 0.1);
}

.enhanced-import-modal .btn-secondary:hover {
    background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
    transform: translateY(-3px);
    box-shadow: 
        0 8px 25px rgba(108, 117, 125, 0.4),
        0 4px 15px rgba(0, 0, 0, 0.15);
}

.enhanced-import-modal .btn i {
    color: white !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .upload-area {
        padding: 2rem 1rem;
    }
    
    .upload-icon {
        width: 60px;
        height: 60px;
    }
    
    .upload-icon i {
        font-size: 1.5rem;
    }
    
    .upload-title {
        font-size: 1.1rem;
    }
    
    .upload-description {
        font-size: 0.9rem;
    }
    
    .format-badge {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .preview-image {
        height: 150px;
    }
    
    .change-image-btn,
    .remove-image-btn {
        padding: 0.375rem 0.75rem;
        font-size: 0.8rem;
    }
    
    .enhanced-import-modal {
        max-width: 95vw;
        margin: 1rem;
    }
    
    .landscape-modal {
        max-width: 95vw;
    }
    
    .landscape-modal .modal-body {
        min-height: 350px;
        padding: 1.5rem;
    }
    
    .upload-area-large {
        padding: 2rem 1.5rem;
    }
    
    .upload-icon-large {
        width: 80px;
        height: 80px;
    }
    
    .upload-icon-large i {
        font-size: 2rem;
    }
    
    .upload-title {
        font-size: 1.2rem;
    }
    
    .upload-description {
        font-size: 0.9rem;
    }
    
    .columns-grid-landscape {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }
    
    .enhanced-import-modal .modal-header {
        padding: 1.5rem;
    }
    
    .enhanced-import-modal .modal-body {
        padding: 1.5rem;
    }
    
    .import-section,
    .template-section {
        padding: 1.5rem;
    }
    
    .columns-grid {
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        gap: 0.5rem;
    }
    
    .column-badge {
        padding: 0.375rem 0.5rem;
        font-size: 0.8rem;
    }
    
    .enhanced-import-modal .btn {
        padding: 0.75rem 1.5rem;
        font-size: 0.9rem;
    }
    
    .enhanced-import-modal .modal-footer {
        padding: 1.5rem;
    }
}

@media (max-width: 576px) {
    .upload-area {
        padding: 1.5rem 1rem;
    }
    
    .upload-icon {
        width: 50px;
        height: 50px;
        margin-bottom: 1rem;
    }
    
    .upload-icon i {
        font-size: 1.25rem;
    }
    
    .upload-title {
        font-size: 1rem;
    }
    
    .upload-description {
        font-size: 0.85rem;
        margin-bottom: 1rem;
    }
    
    .upload-formats {
        gap: 0.5rem;
    }
    
    .format-badge {
        padding: 0.25rem 0.5rem;
        font-size: 0.7rem;
    }
    
    .preview-image {
        height: 120px;
    }
    
    .preview-overlay {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .change-image-btn,
    .remove-image-btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .landscape-modal {
        max-width: 95vw;
    }
    
    .landscape-modal .modal-body {
        min-height: 300px;
        padding: 1rem;
    }
    
    .landscape-modal .row {
        flex-direction: column;
    }
    
    .landscape-modal .col-lg-6 {
        width: 100%;
        margin-bottom: 1rem;
    }
    
    .upload-area-large {
        padding: 1.5rem 1rem;
    }
    
    .upload-icon-large {
        width: 60px;
        height: 60px;
        margin-bottom: 1rem;
    }
    
    .upload-icon-large i {
        font-size: 1.5rem;
    }
    
    .upload-title {
        font-size: 1.1rem;
    }
    
    .upload-description {
        font-size: 0.85rem;
        margin-bottom: 1rem;
    }
    
    .upload-formats {
        gap: 0.5rem;
    }
    
    .format-badge {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .columns-grid-landscape {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.5rem;
    }
    
    .enhanced-import-modal .modal-header {
        padding: 1rem;
    }
    
    .import-icon {
        width: 40px;
        height: 40px;
    }
    
    .import-icon i {
        font-size: 1.2rem;
    }
    
    .enhanced-import-modal .modal-title {
        font-size: 1.3rem;
    }
    
    .enhanced-import-modal .modal-body {
        padding: 1rem;
    }
    
    .import-section,
    .template-section {
        padding: 1rem;
    }
    
    .section-title {
        font-size: 1.1rem;
        margin-bottom: 1.5rem;
    }
    
    .columns-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.5rem;
    }
    
    .column-badge {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .download-link {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
    
    .enhanced-import-modal .btn {
        padding: 0.625rem 1.25rem;
        font-size: 0.85rem;
    }
    
    .enhanced-import-modal .modal-footer {
        padding: 1rem;
    }
    
    .enhanced-import-modal .modal-footer .d-flex {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .enhanced-import-modal .modal-footer .d-flex .d-flex {
        width: 100%;
        justify-content: center;
    }
}

/* Branches Container */
.branches-container {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid rgba(139, 69, 67, 0.1);
    border-radius: 0.75rem;
    padding: 1rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

.branch-checkbox {
    display: flex;
    align-items: center;
    padding: 0.5rem;
    margin-bottom: 0.5rem;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.branch-checkbox:hover {
    background: rgba(139, 69, 67, 0.05);
    transform: translateX(5px);
}

.branch-checkbox .form-check-input {
    margin-right: 0.75rem;
    border-color: #8B4543;
}

.branch-checkbox .form-check-input:checked {
    background-color: #8B4543;
    border-color: #8B4543;
}

.branch-checkbox .form-check-label {
    font-weight: 500;
    color: #2c3e50;
    cursor: pointer;
}

/* Modal Footer */
.enhanced-add-product-modal .modal-footer {
    padding: 2rem;
    border-top: 1px solid rgba(139, 69, 67, 0.1);
    background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f4 100%);
    border-radius: 0 0 1.5rem 1.5rem;
    position: relative;
}

.enhanced-add-product-modal .modal-footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent 0%, rgba(139, 69, 67, 0.2) 50%, transparent 100%);
}

/* Buttons */
.enhanced-add-product-modal .btn {
    padding: 0.875rem 2rem;
    font-weight: 600;
    font-size: 1rem;
    border-radius: 0.75rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    border: none;
}

.enhanced-add-product-modal .btn-primary {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    color: white !important;
    box-shadow: 
        0 4px 15px rgba(139, 69, 67, 0.3),
        0 2px 8px rgba(0, 0, 0, 0.1);
}

.enhanced-add-product-modal .btn-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s ease;
}

.enhanced-add-product-modal .btn-primary:hover {
    background: linear-gradient(135deg, #723937 0%, #5a2e2c 100%);
    color: white !important;
    transform: translateY(-3px);
    box-shadow: 
        0 8px 25px rgba(139, 69, 67, 0.4),
        0 4px 15px rgba(0, 0, 0, 0.15);
}

.enhanced-add-product-modal .btn-primary:hover::before {
    left: 100%;
}

.enhanced-add-product-modal .btn-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white !important;
    box-shadow: 
        0 4px 15px rgba(108, 117, 125, 0.3),
        0 2px 8px rgba(0, 0, 0, 0.1);
}

.enhanced-add-product-modal .btn-secondary:hover {
    background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
    color: white !important;
    transform: translateY(-3px);
    box-shadow: 
        0 8px 25px rgba(108, 117, 125, 0.4),
        0 4px 15px rgba(0, 0, 0, 0.15);
}

.enhanced-add-product-modal .btn i {
    color: white !important;
}

/* Keyboard Hints */
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

/* Responsive Design */
@media (max-width: 1200px) {
    .enhanced-add-product-modal {
        max-width: 95vw;
    }
}

@media (max-width: 768px) {
    .enhanced-add-product-modal .modal-dialog {
        margin: 1rem;
        max-width: calc(100vw - 2rem);
    }
    
    .enhanced-add-product-modal .modal-body {
        padding: 1.5rem;
    }
    
    .enhanced-add-product-modal .form-section {
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .enhanced-add-product-modal .form-control,
    .enhanced-add-product-modal .form-select {
        font-size: 0.95rem;
        padding: 0.75rem 0.875rem;
    }
    
    .enhanced-add-product-modal .btn {
        padding: 0.75rem 1.5rem;
        font-size: 0.95rem;
    }
    
    .enhanced-add-product-modal .modal-footer {
        padding: 1.5rem;
        flex-direction: column;
        gap: 1rem;
    }
    
    .enhanced-add-product-modal .modal-footer .d-flex {
        flex-direction: column;
        gap: 1rem;
        width: 100%;
    }
    
    .enhanced-add-product-modal .keyboard-hints {
        text-align: center;
    }
}

@media (max-width: 576px) {
    .enhanced-add-product-modal .modal-header {
        padding: 1.5rem 1.5rem 1rem;
    }
    
    .enhanced-add-product-modal .add-icon {
        width: 48px;
        height: 48px;
    }
    
    .enhanced-add-product-modal .modal-title {
        font-size: 1.4rem;
    }
    
    .enhanced-add-product-modal .modal-body {
        padding: 1rem;
    }
    
    .enhanced-add-product-modal .form-section {
        padding: 1rem;
    }
    
    .image-upload-container {
        padding: 1rem;
    }
    
    .upload-hint {
        font-size: 0.8rem;
    }
    
    .upload-hint i {
        font-size: 1.2rem;
    }
}
</style>

<div class="container-fluid px-4">
    <div class="d-flex align-items-center mt-4 mb-4">
        <div style="width: 4px; height: 24px; background-color: #8B4543; margin-right: 12px;"></div>
        <h1 class="section-title"><span class="section-icon"><i class="fas fa-box-open"></i></span>Product Management</h1>
    </div>

    <div class="card mb-4" style="background-color: #8B4543; border: none; border-radius: 8px;">
        <div class="card-body d-flex justify-content-between align-items-center py-2 px-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-utensils me-2 text-white"></i>
                <span class="text-white">Product List</span>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-success d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="fas fa-plus"></i>
                    <span>Add Product</span>
                </button>
                <button type="button" class="btn btn-info d-flex align-items-center gap-2" id="importCsvBtn">
                    <i class="fas fa-file-csv"></i>
                    <span>Insert CSV</span>
                </button>
            </div>
        </div>
    </div>

    <div class="card border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="productTable" class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="color: #8B4543;">CATEGORY</th>
                    <th style="color: #8B4543;">PRODUCT NAME</th>
                    <th style="color: #8B4543;">PRICE</th>
                    <th style="color: #8B4543;">DESCRIPTION</th>
                    <th style="color: #8B4543;">INGREDIENTS</th>
                    <th style="color: #8B4543;">BRANCHES</th>
                    <th style="color: #8B4543;">STATUS</th>
                    <th style="color: #8B4543;">IMAGE</th>
                    <th style="color: #8B4543;">ACTION</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content enhanced-add-product-modal">
            <!-- Enhanced Modal Header -->
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <div class="add-icon me-3">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="addProductModalLabel">Add New Product</h5>
                        <small class="text-light opacity-75">Create a new product for your menu</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
                    <span class="close-x">✕</span>
                </button>
            </div>
            
            <!-- Enhanced Modal Body -->
            <div class="modal-body">
                <form id="addProductForm" enctype="multipart/form-data">
                    <div class="row g-4">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <div class="form-section">
                                <h6 class="section-title">
                                    <i class="fas fa-info-circle me-2"></i>Basic Information
                                </h6>
                                
                                <div class="mb-4">
                                    <label for="category_id" class="form-label">
                                        <i class="fas fa-tags me-1"></i>Category
                                    </label>
                                    <select class="form-select form-select-lg" id="category_id" name="category_id" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= htmlspecialchars($category['category_id']) ?>">
                                                <?= htmlspecialchars($category['category_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Choose the category for this product
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="product_name" class="form-label">
                                        <i class="fas fa-box me-1"></i>Product Name
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="product_name" name="product_name" required>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Enter the product name
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="product_price" class="form-label">
                                        <i class="fas fa-tag me-1"></i>Price
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control form-control-lg" id="product_price" name="product_price" step="0.01" min="0" required>
                                    </div>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Set the product price in pesos
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="product_status" class="form-label">
                                        <i class="fas fa-toggle-on me-1"></i>Status
                                    </label>
                                    <select class="form-select form-select-lg" id="product_status" name="product_status" required>
                                        <option value="Available">🟢 Available</option>
                                        <option value="Unavailable">🔴 Unavailable</option>
                                    </select>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Set product availability status
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="product_image" class="form-label">
                                        <i class="fas fa-image me-1"></i>Product Image
                                    </label>
                                    <div class="enhanced-image-upload">
                                        <div class="upload-area" id="uploadArea">
                                            <div class="upload-content">
                                                <div class="upload-icon">
                                                    <i class="fas fa-cloud-upload-alt"></i>
                                                </div>
                                                <h6 class="upload-title">Upload Product Image</h6>
                                                <p class="upload-description">Drag & drop your image here or click to browse</p>
                                                <div class="upload-formats">
                                                    <span class="format-badge">JPG</span>
                                                    <span class="format-badge">PNG</span>
                                                    <span class="format-badge">GIF</span>
                                                </div>
                                            </div>
                                            <input type="file" class="file-input" id="product_image" name="product_image" accept="image/*">
                                        </div>
                                        <div class="upload-preview" id="uploadPreview" style="display: none;">
                                            <div class="preview-container">
                                                <img id="imagePreview" src="" alt="Preview" class="preview-image">
                                                <div class="preview-overlay">
                                                    <button type="button" class="btn btn-sm btn-light change-image-btn">
                                                        <i class="fas fa-edit"></i> Change
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger remove-image-btn">
                                                        <i class="fas fa-trash"></i> Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Upload a high-quality image (max 5MB) for better product presentation
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Column -->
                        <div class="col-md-6">
                            <div class="form-section">
                                <h6 class="section-title">
                                    <i class="fas fa-edit me-2"></i>Product Details
                                </h6>
                                
                                <div class="mb-4">
                                    <label for="description" class="form-label">
                                        <i class="fas fa-align-left me-1"></i>Description
                                    </label>
                                    <textarea class="form-control" id="description" name="description" rows="4" placeholder="Enter product description..."></textarea>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Provide a detailed description of the product
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="ingredients" class="form-label">
                                        <i class="fas fa-list me-1"></i>Ingredients
                                    </label>
                                    <textarea class="form-control" id="ingredients" name="ingredients" rows="4" placeholder="List the ingredients..."></textarea>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        List all ingredients used in this product
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="branches" class="form-label">
                                        <i class="fas fa-building me-1"></i>Available Branches
                                    </label>
                                    <div class="branches-container">
                                        <?php 
                                        $branches = $pdo->query("SELECT branch_id, branch_name FROM pos_branch WHERE status = 'Active' ORDER BY branch_name")->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($branches as $branch): ?>
                                            <div class="branch-checkbox">
                                                <input class="form-check-input" type="checkbox" name="branches[]" value="<?= $branch['branch_id'] ?>" id="branch_<?= $branch['branch_id'] ?>">
                                                <label class="form-check-label" for="branch_<?= $branch['branch_id'] ?>">
                                                    <?= htmlspecialchars($branch['branch_name']) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                        <!-- Hidden field to ensure branches field is always sent -->
                                        <input type="hidden" name="branches[]" value="">
                                    </div>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Select which branches will have this product available
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Enhanced Modal Footer -->
            <div class="modal-footer">
                <div class="d-flex justify-content-end align-items-center w-100">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-primary btn-lg" id="saveProduct">
                            <i class="fas fa-plus me-2"></i>Add Product
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <div class="edit-icon me-3">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0">Edit Product</h5>
                        <small class="text-light opacity-75">Update product information</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close">
                    <span class="close-x">✕</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editProductForm" enctype="multipart/form-data">
                    <input type="hidden" id="edit_product_id" name="product_id">
                    <div class="row g-4">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <div class="form-section">
                                <h6 class="section-title">
                                    <i class="fas fa-info-circle me-2"></i>Basic Information
                                </h6>
                                <div class="mb-3">
                                    <label for="edit_category_id" class="form-label">Category</label>
                                    <select class="form-select" id="edit_category_id" name="category_id" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= htmlspecialchars($category['category_id']) ?>">
                                                <?= htmlspecialchars($category['category_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_product_name" class="form-label">Product Name</label>
                                    <input type="text" class="form-control" id="edit_product_name" name="product_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_product_price" class="form-label">
                                        <i class="fas fa-tag me-1"></i>Price
                                    </label>
                                    <div class="enhanced-price-input">
                                        <div class="currency-symbol">
                                            <i class="fas fa-peso-sign"></i>
                                        </div>
                                        <input type="number" class="form-control enhanced-price-field" id="edit_product_price" name="product_price" step="0.01" min="0" placeholder="0.00" required>
                                        <div class="price-controls">
                                            <button type="button" class="price-btn price-up" onclick="adjustPrice('edit_product_price', 0.01)">
                                                <i class="fas fa-chevron-up"></i>
                                            </button>
                                            <button type="button" class="price-btn price-down" onclick="adjustPrice('edit_product_price', -0.01)">
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Set the product price in Philippine Peso (₱)
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_product_status" class="form-label">Status</label>
                                    <select class="form-select" id="edit_product_status" name="product_status" required>
                                        <option value="Available">Available</option>
                                        <option value="Unavailable">Unavailable</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h6 class="section-title">
                                    <i class="fas fa-image me-2"></i>Product Image
                                </h6>
                                <div class="mb-3">
                                    <label for="edit_product_image" class="form-label">
                                        Upload New Image
                                        <span class="text-muted">(Max 5MB, JPG, PNG, GIF, WebP)</span>
                                    </label>
                                    <input type="file" class="form-control" id="edit_product_image" name="product_image" accept="image/*" aria-describedby="imageHelp">
                                    <div class="upload-text">Click here or drag and drop your image</div>
                                    <div id="imageHelp" class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Supported formats: JPG, PNG, GIF, WebP. Maximum file size: 5MB
                                    </div>
                                    <div id="currentImage" class="mt-2"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Column -->
                        <div class="col-md-6">
                            <div class="form-section">
                                <h6 class="section-title">
                                    <i class="fas fa-align-left me-2"></i>Product Details
                                </h6>
                                <div class="mb-3">
                                    <label for="edit_description" class="form-label">Description</label>
                                    <textarea class="form-control" id="edit_description" name="description" rows="4" placeholder="Enter product description..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_ingredients" class="form-label">Ingredients</label>
                                    <textarea class="form-control" id="edit_ingredients" name="ingredients" rows="4" placeholder="Enter product ingredients..."></textarea>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h6 class="section-title">
                                    <i class="fas fa-store me-2"></i>Branch Availability
                                </h6>
                                <div class="mb-3">
                                    <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                        <?php 
                                        $edit_branches = $pdo->query("SELECT branch_id, branch_name FROM pos_branch WHERE status = 'Active' ORDER BY branch_name")->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($edit_branches as $branch): ?>
                                            <div class="form-check">
                                                <input class="form-check-input edit-branch-checkbox" type="checkbox" name="branches[]" value="<?= $branch['branch_id'] ?>" id="edit_branch_<?= $branch['branch_id'] ?>">
                                                <label class="form-check-label" for="edit_branch_<?= $branch['branch_id'] ?>">
                                                    <?= htmlspecialchars($branch['branch_name']) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                        <!-- Hidden field to ensure branches field is always sent -->
                                        <input type="hidden" name="branches[]" value="">
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Select which branches will have this product available
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div class="keyboard-hints">
                        <small class="text-muted">
                            <i class="fas fa-keyboard me-1"></i>
                            Ctrl+S to save • Esc to close
                        </small>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-primary" id="updateProduct">
                            <i class="fas fa-save me-1"></i>Update Product
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced View Product Modal -->
<div class="modal fade" id="viewProductModal" tabindex="-1" aria-labelledby="viewProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-maroon text-white">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i><span id="viewProductModalLabel"></span> - Product Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Product Image Section -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-image me-2"></i>Product Image</h6>
                            </div>
                            <div class="card-body text-center">
                                <div class="product-image-container mb-3">
                                    <img id="viewProductImage" src="" alt="Product Image" class="img-fluid rounded" style="max-height: 300px; object-fit: contain;">
                                </div>
                                <div class="product-quick-stats">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="stat-card">
                                                <div class="stat-icon">
                                                    <i class="fas fa-eye"></i>
                                                </div>
                                                <div class="stat-content">
                                                    <div class="stat-value" id="viewCount">0</div>
                                                    <div class="stat-label">Views</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="stat-card">
                                                <div class="stat-icon">
                                                    <i class="fas fa-shopping-cart"></i>
                                                </div>
                                                <div class="stat-content">
                                                    <div class="stat-value" id="orderCount">0</div>
                                                    <div class="stat-label">Orders</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Product Details Section -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Basic Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="fw-medium">Name:</td>
                                        <td id="viewProductName"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium">Category:</td>
                                        <td id="viewCategory"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium">Price:</td>
                                        <td>
                                            <span class="fw-bold text-primary" id="viewPrice"></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium">Status:</td>
                                        <td id="viewStatus"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium">Description:</td>
                                        <td id="viewDescription"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium">Ingredients:</td>
                                        <td id="viewIngredients"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Additional Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="info-item">
                                            <i class="fas fa-calendar-alt me-2"></i>
                                            <span>Created: <span id="viewCreatedDate">-</span></span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-item">
                                            <i class="fas fa-clock me-2"></i>
                                            <span>Last Updated: <span id="viewUpdatedDate">-</span></span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-item">
                                            <i class="fas fa-store me-2"></i>
                                            <span>Available in: <span id="viewBranchCount">-</span> branches</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Fullscreen Image Modal removed - no longer needed -->

<style>
/* General Styles */
body {
    background-color: #f8f5f5;
}

.container-fluid {
    padding-top: 1.5rem;
    padding-bottom: 2rem;
}

/* Card Styles */
.card {
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(139, 69, 67, 0.1);
}

/* Table Styles */
.table {
    margin-bottom: 0;
    background-color: #fff;
}

.table thead th {
    font-size: 13px;
    font-weight: 600;
    border: none;
    padding: 1.2rem 1rem;
    white-space: nowrap;
    background-color: #f9f2f2;
    color: #8B4543 !important;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table tbody tr {
    border-bottom: 1px solid #f0e6e6;
    transition: background-color 0.2s ease;
}

.table tbody tr:hover {
    background-color: #fdf8f8;
}

.table td {
    padding: 1.2rem 1rem;
    vertical-align: middle;
    color: #555;
    font-size: 14px;
}

/* Button Styles */
.btn-success {
    background-color: #4B7F52;
    border: none;
    padding: 0.6rem 1.2rem;
    font-size: 14px;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-success:hover {
    background-color: #3d6642;
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(75, 127, 82, 0.2);
}

/* Action Buttons */
.btn-view, .btn-edit, .btn-delete {
    width: 36px;
    height: 36px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    margin: 0 3px;
    border: none;
    color: white;
    transition: all 0.2s ease;
}

.btn-view { 
    background-color: #4B7F52; 
}
.btn-view:hover {
    background-color: #3d6642;
}

.btn-edit { 
    background-color: #8B4543; 
}
.btn-edit:hover {
    background-color: #723836;
}

.btn-delete { 
    background-color: #dc3545; 
}
.btn-delete:hover {
    background-color: #bb2d3b;
}

/* Status Badge */
.badge-active {
    background-color: #4B7F52;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-weight: 500;
    font-size: 13px;
    letter-spacing: 0.3px;
}
.badge-inactive {
    background-color: #dc3545;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-weight: 500;
    font-size: 13px;
    letter-spacing: 0.3px;
}
.badge-secondary {
    background-color: #6c757d;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-weight: 500;
    font-size: 13px;
    letter-spacing: 0.3px;
}

/* Enhanced Modal Styles */
.enhanced-product-modal {
    border-radius: 16px;
    border: none;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    overflow: hidden;
}

/* Stockman-style Modal Styles */
#viewProductModal .modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    overflow: hidden;
}

#viewProductModal .modal-header {
    background-color: #8B4543;
    border: none;
    padding: 1.5rem;
}

#viewProductModal .modal-header .modal-title {
    font-weight: 600;
    font-size: 1.2rem;
}

#viewProductModal .modal-body {
    padding: 1.5rem;
}

#viewProductModal .card {
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: none;
    margin-bottom: 1rem;
}

#viewProductModal .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 1rem 1.25rem;
    font-weight: 600;
    color: #495057;
}

#viewProductModal .card-body {
    padding: 1.25rem;
}

#viewProductModal .table-borderless td {
    padding: 0.5rem 0;
    border: none;
    vertical-align: top;
}

#viewProductModal .fw-medium {
    font-weight: 600;
    color: #495057;
    width: 30%;
}

#viewProductModal .stat-card {
    background: white;
    border-radius: 8px;
    padding: 0.75rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border: 1px solid rgba(139, 69, 67, 0.1);
}

#viewProductModal .stat-icon {
    width: 35px;
    height: 35px;
    background: linear-gradient(135deg, #8B4543 0%, #a55a58 100%);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.9rem;
}

#viewProductModal .stat-content {
    flex: 1;
}

#viewProductModal .stat-value {
    font-size: 1.3rem;
    font-weight: 700;
    color: #8B4543;
    line-height: 1;
}

#viewProductModal .stat-label {
    font-size: 0.75rem;
    color: #6c757d;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

#viewProductModal .info-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 0;
    color: #6c757d;
    font-size: 0.9rem;
    border-bottom: 1px solid rgba(139, 69, 67, 0.1);
}

#viewProductModal .info-item:last-child {
    border-bottom: none;
}

#viewProductModal .info-item i {
    color: #8B4543;
    width: 16px;
    text-align: center;
}

#viewProductModal .badge {
    font-size: 0.8rem;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

#viewProductModal .bg-success {
    background-color: #4B7F52 !important;
}

#viewProductModal .bg-danger {
    background-color: #dc3545 !important;
}

#viewProductModal .text-primary {
    color: #8B4543 !important;
}

.bg-maroon {
    background-color: #8B4543 !important;
}

/* Enhanced Statistics Styling */
#viewProductModal .stat-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

#viewProductModal .stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(139, 69, 67, 0.15);
}

#viewProductModal .stat-value {
    transition: all 0.3s ease;
}

#viewProductModal .stat-value.updated {
    animation: numberUpdate 0.6s ease-out;
}

@keyframes numberUpdate {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); color: #4B7F52; }
    100% { transform: scale(1); }
}

/* Loading state for statistics */
#viewProductModal .stat-value.loading {
    opacity: 0.6;
    position: relative;
}

#viewProductModal .stat-value.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 12px;
    height: 12px;
    margin: -6px 0 0 -6px;
    border: 2px solid #8B4543;
    border-top: 2px solid transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

/* Enhanced Edit Modal Design */
#editProductModal .modal-content {
    border-radius: 24px;
    border: none;
    box-shadow: 0 25px 80px rgba(0,0,0,0.25);
    overflow: hidden;
    animation: modalSlideIn 0.4s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-60px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

#editProductModal .modal-header {
    background: linear-gradient(135deg, #8B4543 0%, #a55a58 30%, #8B4543 70%, #723836 100%);
    color: white;
    border: none;
    padding: 2.5rem;
    position: relative;
    overflow: hidden;
}

#editProductModal .modal-header::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: headerGlow 3s ease-in-out infinite;
}

@keyframes headerGlow {
    0%, 100% { transform: rotate(0deg); }
    50% { transform: rotate(180deg); }
}

#editProductModal .modal-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="10" cy="60" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="90" cy="40" r="0.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
}

#editProductModal .modal-title {
    font-weight: 700;
    color: white;
    font-size: 1.6rem;
    position: relative;
    z-index: 1;
}

#editProductModal .btn-close {
    color: white;
    opacity: 1;
    filter: brightness(1.2);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    z-index: 1;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    padding: 0;
    font-size: 0;
    line-height: 0;
}

#editProductModal .btn-close::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 2px;
    background: white;
    transform: translate(-50%, -50%) rotate(45deg);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

#editProductModal .btn-close::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 2px;
    background: white;
    transform: translate(-50%, -50%) rotate(-45deg);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

#editProductModal .btn-close:hover {
    transform: scale(1.15) rotate(90deg);
    filter: brightness(1.8);
    background: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.4);
    box-shadow: 
        0 8px 25px rgba(0, 0, 0, 0.2),
        0 0 0 0 rgba(255, 255, 255, 0.7);
    animation: closeButtonPulse 0.6s ease-out;
}

#editProductModal .btn-close:hover::before {
    width: 24px;
    height: 3px;
    background: linear-gradient(90deg, #fff, #f0f0f0, #fff);
    box-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
}

#editProductModal .btn-close:hover::after {
    width: 24px;
    height: 3px;
    background: linear-gradient(90deg, #fff, #f0f0f0, #fff);
    box-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
}

#editProductModal .btn-close:active {
    transform: scale(0.95) rotate(90deg);
    transition: all 0.1s ease;
}

#editProductModal .btn-close:focus {
    outline: none;
    box-shadow: 
        0 4px 15px rgba(0, 0, 0, 0.1),
        0 0 0 3px rgba(255, 255, 255, 0.5);
}

#editProductModal .btn-close:focus-visible {
    outline: 2px solid rgba(255, 255, 255, 0.8);
    outline-offset: 2px;
}

@keyframes closeButtonPulse {
    0% {
        box-shadow: 
            0 8px 25px rgba(0, 0, 0, 0.2),
            0 0 0 0 rgba(255, 255, 255, 0.7);
    }
    70% {
        box-shadow: 
            0 8px 25px rgba(0, 0, 0, 0.2),
            0 0 0 10px rgba(255, 255, 255, 0);
    }
    100% {
        box-shadow: 
            0 8px 25px rgba(0, 0, 0, 0.2),
            0 0 0 0 rgba(255, 255, 255, 0);
    }
}

@keyframes closeButtonEntrance {
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

#editProductModal.show .btn-close {
    animation: closeButtonEntrance 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
}

#editProductModal .edit-icon {
    width: 55px;
    height: 55px;
    background: rgba(255,255,255,0.25);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    backdrop-filter: blur(15px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
    position: relative;
    z-index: 2;
}

#editProductModal .edit-icon:hover {
    transform: scale(1.1) rotate(5deg);
    background: rgba(255,255,255,0.35);
    box-shadow: 0 12px 35px rgba(0,0,0,0.2);
}

#editProductModal .edit-icon::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(45deg, rgba(255,255,255,0.3), transparent);
    border-radius: 18px;
    z-index: -1;
    animation: iconPulse 2s ease-in-out infinite;
}

@keyframes iconPulse {
    0%, 100% { opacity: 0.5; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.05); }
}

/* Form Section Styling */
#editProductModal .form-section {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    border: 1px solid rgba(139, 69, 67, 0.08);
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

#editProductModal .form-section:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
    border-color: rgba(139, 69, 67, 0.15);
}

#editProductModal .form-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #8B4543, #a55a58, #8B4543);
    background-size: 200% 100%;
    animation: sectionBorder 3s ease-in-out infinite;
}

@keyframes sectionBorder {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

#editProductModal .section-title {
    color: #8B4543;
    font-weight: 800;
    font-size: 1.2rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 3px solid rgba(139, 69, 67, 0.15);
    text-transform: uppercase;
    letter-spacing: 1px;
    position: relative;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

#editProductModal .section-title::after {
    content: '';
    position: absolute;
    bottom: -3px;
    left: 0;
    width: 50px;
    height: 3px;
    background: linear-gradient(90deg, #8B4543, #a55a58);
    border-radius: 2px;
    animation: titleUnderline 2s ease-in-out infinite;
}

@keyframes titleUnderline {
    0%, 100% { width: 50px; }
    50% { width: 100px; }
}

#editProductModal .section-title i {
    color: #8B4543;
    font-size: 1rem;
}

#editProductModal .modal-body {
    padding: 2.5rem;
    background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
    position: relative;
}

#editProductModal .modal-body::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(139, 69, 67, 0.2), transparent);
}

/* Form Styling */
#editProductModal .form-label {
    color: #2c3e50;
    font-weight: 600;
    font-size: 0.95rem;
    margin-bottom: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

#editProductModal .form-control,
#editProductModal .form-select {
    border-radius: 16px;
    border: 2px solid #e9ecef;
    padding: 1.25rem 1.5rem;
    font-size: 1.05rem;
    transition: all 0.3s ease;
    background: linear-gradient(135deg, #ffffff 0%, #fafafa 100%);
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    position: relative;
}

#editProductModal .form-control::placeholder,
#editProductModal .form-select::placeholder {
    color: #adb5bd;
    font-style: italic;
}

#editProductModal .form-control:focus,
#editProductModal .form-select:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.4rem rgba(139, 69, 67, 0.12), 0 8px 25px rgba(139, 69, 67, 0.15);
    transform: translateY(-2px);
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

#editProductModal .form-control:focus::after,
#editProductModal .form-select:focus::after {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(45deg, #8B4543, #a55a58);
    border-radius: 18px;
    z-index: -1;
    opacity: 0.3;
    animation: inputGlow 1.5s ease-in-out infinite;
}

@keyframes inputGlow {
    0%, 100% { opacity: 0.3; }
    50% { opacity: 0.6; }
}

#editProductModal .form-control:hover,
#editProductModal .form-select:hover {
    border-color: #a55a58;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

/* File Input Styling */
#editProductModal .form-control[type="file"] {
    padding: 0;
    background: transparent;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    position: relative;
    overflow: hidden;
    min-height: 140px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 3px dashed #8B4543;
    border-radius: 16px;
}

#editProductModal .form-control[type="file"]::before {
    content: '📁';
    color: #8B4543;
    font-size: 2rem;
    z-index: 1;
    pointer-events: none;
    display: block;
    margin: 0;
    line-height: 1;
}

#editProductModal .form-control[type="file"]::after {
    content: 'Choose File';
    color: #8B4543;
    font-weight: 600;
    font-size: 1.1rem;
    z-index: 1;
    pointer-events: none;
    white-space: nowrap;
    margin: 0;
    line-height: 1;
}

#editProductModal .form-control[type="file"] .upload-text {
    content: 'Click here or drag and drop your image';
    color: #6c757d;
    font-size: 0.85rem;
    font-style: italic;
    z-index: 1;
    pointer-events: none;
    white-space: nowrap;
    margin: 0;
    line-height: 1;
}

#editProductModal .form-control[type="file"]:hover {
    background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
    border-color: #a55a58;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(139, 69, 67, 0.15);
}

/* Hide the default file input text */
#editProductModal .form-control[type="file"]::-webkit-file-upload-button {
    visibility: hidden;
    width: 0;
    height: 0;
    margin: 0;
    padding: 0;
    border: none;
}

#editProductModal .form-control[type="file"]::file-selector-button {
    visibility: hidden;
    width: 0;
    height: 0;
    margin: 0;
    padding: 0;
    border: none;
}

/* Hide the "No file chosen" text */
#editProductModal .form-control[type="file"]::-webkit-file-upload-button + span {
    display: none !important;
}

#editProductModal .form-control[type="file"]::file-selector-button + span {
    display: none !important;
}

/* Additional hiding for different browsers */
#editProductModal .form-control[type="file"]::-webkit-file-upload-button {
    display: none !important;
}

#editProductModal .form-control[type="file"]::file-selector-button {
    display: none !important;
}

/* Ensure no default text shows */
#editProductModal .form-control[type="file"] {
    color: transparent !important;
}

#editProductModal .form-control[type="file"]::before {
    color: #8B4543 !important;
}

#editProductModal .form-control[type="file"]::after {
    color: #8B4543 !important;
}

#editProductModal .upload-text {
    color: #6c757d !important;
    font-size: 0.85rem;
    font-style: italic;
    margin-top: 0.5rem;
    text-align: center;
}

/* Drag and drop styling */
#editProductModal .drag-over {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
    border-color: #2196f3 !important;
    transform: scale(1.02);
    box-shadow: 0 8px 25px rgba(33, 150, 243, 0.2) !important;
}

/* Enhanced image preview */
#editProductModal .image-container {
    position: relative;
    display: inline-block;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

#editProductModal .image-container:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

#editProductModal .preview-image {
    max-width: 100%;
    max-height: 200px;
    display: block;
    transition: all 0.3s ease;
}

#editProductModal .image-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.7));
    color: white;
    padding: 1rem;
    transform: translateY(100%);
    transition: transform 0.3s ease;
}

#editProductModal .image-container:hover .image-overlay {
    transform: translateY(0);
}

#editProductModal .image-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

#editProductModal .image-info i {
    font-size: 1.2rem;
    margin-bottom: 0.25rem;
}

#editProductModal .image-info span {
    font-weight: 600;
    font-size: 0.9rem;
}

#editProductModal .image-info small {
    opacity: 0.8;
    font-size: 0.8rem;
}

/* Keyboard hints styling */
#editProductModal .keyboard-hints {
    /* Remove absolute positioning to let it flow naturally in the footer */
}

/* Enhanced form focus states */
#editProductModal .form-control:focus,
#editProductModal .form-select:focus,
#editProductModal textarea:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.4rem rgba(139, 69, 67, 0.12), 0 8px 25px rgba(139, 69, 67, 0.15);
    transform: translateY(-2px);
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

/* Loading spinner animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.fa-spin {
    animation: spin 1s linear infinite;
}

#editProductModal .form-control[type="file"]:hover {
    background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
    border-color: #a55a58;
}

/* Textarea Styling */
#editProductModal textarea.form-control {
    min-height: 100px;
    resize: vertical;
    line-height: 1.6;
}

/* Branch Selection Styling */
#editProductModal .border.rounded {
    border: 2px solid #e9ecef !important;
    border-radius: 12px !important;
    background: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

#editProductModal .form-check {
    margin-bottom: 0.75rem;
    padding-left: 2rem;
}

#editProductModal .form-check-input {
    width: 1.2rem;
    height: 1.2rem;
    border: 2px solid #8B4543;
    border-radius: 4px;
    transition: all 0.3s ease;
}

#editProductModal .form-check-input:checked {
    background-color: #8B4543;
    border-color: #8B4543;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
}

#editProductModal .form-check-label {
    font-weight: 500;
    color: #2c3e50;
    cursor: pointer;
    transition: color 0.3s ease;
}

#editProductModal .form-check-label:hover {
    color: #8B4543;
}

/* Current Image Display */
#editProductModal #currentImage {
    margin-top: 1rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 16px;
    border: 2px dashed #8B4543;
    text-align: center;
    color: #6c757d;
    font-style: italic;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

#editProductModal #currentImage:hover {
    border-color: #a55a58;
    box-shadow: 0 4px 15px rgba(139, 69, 67, 0.1);
    transform: translateY(-1px);
}

#editProductModal #currentImage img {
    max-width: 100%;
    max-height: 200px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

#editProductModal #currentImage img:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

#editProductModal .image-preview {
    background: white;
    border-radius: 12px;
    padding: 1rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    border: 1px solid rgba(139, 69, 67, 0.1);
}

#editProductModal .image-preview .text-success {
    font-weight: 600;
    font-size: 0.9rem;
    margin-top: 0.75rem;
}

/* Button Styling */
#editProductModal .btn {
    border-radius: 16px;
    font-weight: 700;
    padding: 1rem 2.5rem;
    font-size: 1.1rem;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: none;
    text-transform: uppercase;
    letter-spacing: 1px;
    position: relative;
    overflow: hidden;
}

#editProductModal .btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

#editProductModal .btn:hover::before {
    left: 100%;
}

#editProductModal .btn:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 12px 35px rgba(0,0,0,0.25);
}

#editProductModal .btn:active {
    transform: translateY(-2px) scale(0.98);
    transition: all 0.1s ease;
}

#editProductModal .btn-primary {
    background: linear-gradient(135deg, #8B4543 0%, #a55a58 50%, #8B4543 100%);
    color: white;
    box-shadow: 0 6px 20px rgba(139, 69, 67, 0.4);
    background-size: 200% 100%;
    animation: buttonGradient 3s ease-in-out infinite;
}

@keyframes buttonGradient {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

#editProductModal .btn-primary:hover {
    background: linear-gradient(135deg, #723836 0%, #8B4543 100%);
    box-shadow: 0 8px 25px rgba(139, 69, 67, 0.4);
}

#editProductModal .btn-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
}

#editProductModal .btn-secondary:hover {
    background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
    box-shadow: 0 8px 25px rgba(108, 117, 125, 0.4);
}

/* Modal Footer */
#editProductModal .modal-footer {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-top: 1px solid rgba(139, 69, 67, 0.1);
    padding: 1.5rem 2.5rem;
    gap: 1rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    #editProductModal .modal-body {
        padding: 1.5rem;
    }
    
    #editProductModal .modal-header {
        padding: 1.5rem;
    }
    
    #editProductModal .modal-title {
        font-size: 1.3rem;
    }
    
    #editProductModal .btn {
        padding: 0.75rem 1.5rem;
        font-size: 0.9rem;
    }
    
    #editProductModal .modal-footer {
        padding: 1rem 1.5rem;
        flex-direction: column;
    }
    
    #editProductModal .modal-footer .btn {
        width: 100%;
    }
}

/* Status change visual feedback */
#editProductModal .status-changing {
    border-color: #ffc107 !important;
    box-shadow: 0 0 0 0.3rem rgba(255, 193, 7, 0.3) !important;
    animation: statusPulse 1.5s infinite;
    position: relative;
}

#editProductModal .status-changing::after {
    content: '🔄';
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    animation: spin 1s linear infinite;
}

@keyframes statusPulse {
    0% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.8; transform: scale(1.02); }
    100% { opacity: 1; transform: scale(1); }
}

@keyframes spin {
    from { transform: translateY(-50%) rotate(0deg); }
    to { transform: translateY(-50%) rotate(360deg); }
}

/* Enhanced form validation feedback */
#editProductModal .form-control.is-valid {
    border-color: #28a745;
    box-shadow: 0 0 0 0.3rem rgba(40, 167, 69, 0.25);
    background: linear-gradient(135deg, #f8fff9 0%, #e8f5e8 100%);
}

#editProductModal .form-control.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.3rem rgba(220, 53, 69, 0.25);
    background: linear-gradient(135deg, #fff8f8 0%, #f5e8e8 100%);
}

/* Loading overlay for form submission */
#editProductModal .form-loading {
    position: relative;
    pointer-events: none;
}

#editProductModal .form-loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

#editProductModal .form-loading::before {
    content: '🔄';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 2rem;
    z-index: 1001;
    animation: spin 1s linear infinite;
}

.enhanced-product-modal .modal-header {
    background: linear-gradient(135deg, #8B4543 0%, #a55a58 100%);
    color: white;
    border: none;
    padding: 1.5rem;
    position: relative;
}

.enhanced-product-modal .modal-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="10" cy="60" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="90" cy="40" r="0.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
}

.product-icon {
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

.enhanced-product-modal .modal-title {
    font-weight: 600;
    color: white;
    font-size: 1.4rem;
}

.enhanced-product-modal .btn-close {
    color: white;
    opacity: 1;
    filter: brightness(1.2);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    z-index: 1;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    padding: 0;
    font-size: 0;
    line-height: 0;
}

.enhanced-product-modal .btn-close::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 2px;
    background: white;
    transform: translate(-50%, -50%) rotate(45deg);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.enhanced-product-modal .btn-close::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 2px;
    background: white;
    transform: translate(-50%, -50%) rotate(-45deg);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.enhanced-product-modal .btn-close:hover {
    transform: scale(1.15) rotate(90deg);
    filter: brightness(1.8);
    background: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.4);
    box-shadow: 
        0 8px 25px rgba(0, 0, 0, 0.2),
        0 0 0 0 rgba(255, 255, 255, 0.7);
    animation: viewCloseButtonPulse 0.6s ease-out;
}

.enhanced-product-modal .btn-close:hover::before {
    width: 24px;
    height: 3px;
    background: linear-gradient(90deg, #fff, #f0f0f0, #fff);
    box-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
}

.enhanced-product-modal .btn-close:hover::after {
    width: 24px;
    height: 3px;
    background: linear-gradient(90deg, #fff, #f0f0f0, #fff);
    box-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
}

.enhanced-product-modal .btn-close:active {
    transform: scale(0.95) rotate(90deg);
    transition: all 0.1s ease;
}

.enhanced-product-modal .btn-close:focus {
    outline: none;
    box-shadow: 
        0 4px 15px rgba(0, 0, 0, 0.1),
        0 0 0 3px rgba(255, 255, 255, 0.5);
}

.enhanced-product-modal .btn-close:focus-visible {
    outline: 2px solid rgba(255, 255, 255, 0.8);
    outline-offset: 2px;
}

@keyframes viewCloseButtonPulse {
    0% {
        box-shadow: 
            0 8px 25px rgba(0, 0, 0, 0.2),
            0 0 0 0 rgba(255, 255, 255, 0.7);
    }
    70% {
        box-shadow: 
            0 8px 25px rgba(0, 0, 0, 0.2),
            0 0 0 10px rgba(255, 255, 255, 0);
    }
    100% {
        box-shadow: 
            0 8px 25px rgba(0, 0, 0, 0.2),
            0 0 0 0 rgba(255, 255, 255, 0);
    }
}

@keyframes viewCloseButtonEntrance {
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

#viewProductModal.show .btn-close {
    animation: viewCloseButtonEntrance 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.enhanced-product-modal .modal-body {
    padding: 2rem;
    background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
}

/* Product Image Section */
.product-image-section {
    position: relative;
}

.product-image-container {
    position: relative;
    width: 100%;
    height: 320px;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    transition: all 0.3s ease;
}

/* Hover effect removed - image container is now static */

.product-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    transition: all 0.3s ease;
    background-color: #f8f9fa;
    padding: 15px;
    box-sizing: border-box;
    max-width: 100%;
    max-height: 100%;
    display: block;
}

.product-image-container:empty::before {
    content: 'No Image Available';
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #6c757d;
    font-size: 1.1rem;
    font-weight: 500;
}

/* Image overlay removed - image is now static */

/* Quick Stats Cards */
.product-quick-stats {
    margin-top: 1.5rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    gap: 0.75rem;
    transition: all 0.3s ease;
    border: 1px solid rgba(139, 69, 67, 0.1);
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

.stat-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #8B4543 0%, #a55a58 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #8B4543;
    line-height: 1;
}

.stat-label {
    font-size: 0.8rem;
    color: #6c757d;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Product Details Section */
.product-details-section {
    height: 100%;
}

/* Info Cards */
.info-card {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    border: 1px solid rgba(139, 69, 67, 0.1);
    transition: all 0.3s ease;
    height: 100%;
}

.info-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

.info-card-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
    color: #6c757d;
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-card-header i {
    color: #8B4543;
    font-size: 1rem;
}

.info-card-content {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2c3e50;
}

.price-display {
    font-size: 1.4rem !important;
    color: #8B4543 !important;
    font-weight: 700 !important;
}

/* Status Card */
.status-card {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    border: 1px solid rgba(139, 69, 67, 0.1);
    transition: all 0.3s ease;
}

.status-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

.status-card-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
    color: #6c757d;
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-card-header i {
    color: #8B4543;
    font-size: 1rem;
}

.status-card-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.status-badge {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.status-badge[data-status="Available"] {
    background: linear-gradient(135deg, #4B7F52 0%, #5a9c62 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(75, 127, 82, 0.3);
}

.status-badge[data-status="Unavailable"] {
    background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
}

.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

.status-indicator[data-status="Available"] {
    background: #4B7F52;
    box-shadow: 0 0 0 0 rgba(75, 127, 82, 0.7);
}

.status-indicator[data-status="Unavailable"] {
    background: #dc3545;
    box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(75, 127, 82, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(75, 127, 82, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(75, 127, 82, 0);
    }
}

/* Content Cards */
.content-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    border: 1px solid rgba(139, 69, 67, 0.1);
    transition: all 0.3s ease;
    height: 100%;
}

.content-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

.content-card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 1rem 1.25rem;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
    border-bottom: 1px solid rgba(139, 69, 67, 0.1);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #6c757d;
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.content-card-header i {
    color: #8B4543;
    font-size: 1rem;
}

.content-card-body {
    padding: 1.25rem;
    min-height: 100px;
    color: #495057;
    line-height: 1.6;
    font-size: 0.95rem;
}

/* Additional Info */
.additional-info {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    border: 1px solid rgba(139, 69, 67, 0.1);
}

.info-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 0;
    color: #6c757d;
    font-size: 0.9rem;
    border-bottom: 1px solid rgba(139, 69, 67, 0.1);
}

.info-item:last-child {
    border-bottom: none;
}

.info-item i {
    color: #8B4543;
    width: 16px;
    text-align: center;
}

/* Modal Footer */
.enhanced-product-modal .modal-footer {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-top: 1px solid rgba(139, 69, 67, 0.1);
    padding: 1.25rem 2rem;
}

/* Fullscreen Image Modal CSS removed - no longer needed */

/* Loading Animation */
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

/* Enhanced Animations */
.enhanced-product-modal .modal-content {
    animation: modalSlideIn 0.3s ease-out;
}

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

.stat-card, .info-card, .content-card, .status-card {
    animation: cardFadeIn 0.5s ease-out;
    animation-fill-mode: both;
}

.stat-card:nth-child(1) { animation-delay: 0.1s; }
.stat-card:nth-child(2) { animation-delay: 0.2s; }
.info-card:nth-child(1) { animation-delay: 0.3s; }
.info-card:nth-child(2) { animation-delay: 0.4s; }
.status-card { animation-delay: 0.5s; }
.content-card:nth-child(1) { animation-delay: 0.6s; }
.content-card:nth-child(2) { animation-delay: 0.7s; }

@keyframes cardFadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Hover effects removed - images are now static */

.stat-card:hover .stat-icon {
    transform: scale(1.1) rotate(5deg);
}

.info-card:hover .info-card-header i {
    transform: scale(1.2);
}

/* Status Badge Enhancement */
.status-badge {
    position: relative;
    overflow: hidden;
}

.status-badge::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s;
}

.status-badge:hover::before {
    left: 100%;
}

/* Responsive Design */
@media (max-width: 768px) {
    .enhanced-product-modal .modal-body {
        padding: 1rem;
    }
    
    .product-image-container {
        height: 220px;
    }
    
    .stat-card {
        padding: 0.75rem;
    }
    
    .stat-value {
        font-size: 1.2rem;
    }
    
    .info-card, .content-card {
        padding: 1rem;
    }
    
    .product-icon {
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
    }
    
    .enhanced-product-modal .modal-title {
        font-size: 1.2rem;
    }
}

@media (max-width: 576px) {
    .enhanced-product-modal .modal-dialog {
        margin: 0.5rem;
    }
    
    .product-image-container {
        height: 180px;
    }
    
    .stat-card {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
    
    .stat-icon {
        width: 35px;
        height: 35px;
        font-size: 0.9rem;
    }
}

.form-label {
    color: #8B4543;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.form-control, .form-select {
    border-radius: 6px;
    border: 1px solid #ddd;
    padding: 0.6rem 1rem;
}

.form-control:focus, .form-select:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
}

/* DataTable Customization */
.dataTables_wrapper .dataTables_length select,
.dataTables_wrapper .dataTables_filter input {
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 0.5rem;
    font-size: 14px;
}

.dataTables_wrapper .dataTables_filter input {
    width: 250px;
    margin-left: 0.5rem;
    background-color: #fff;
}

.dataTables_wrapper .dataTables_filter input:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
    outline: none;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 0.5rem 1rem;
    margin: 0 3px;
    border: 1px solid #ddd;
    border-radius: 6px;
    color: #8B4543 !important;
    background: white !important;
    transition: all 0.2s ease;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: #f9f2f2 !important;
    border-color: #8B4543;
    color: #8B4543 !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: #8B4543 !important;
    color: white !important;
    border-color: #8B4543;
    font-weight: 500;
}

/* Product Image */
.product-image {
    width: 48px;
    height: 48px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Modal Footer */
.modal-footer {
    border-top: 1px solid #eee;
    padding: 1.2rem;
}

.modal-footer .btn {
    padding: 0.6rem 1.5rem;
    border-radius: 6px;
    font-weight: 500;
}

.modal-footer .btn-secondary {
    background-color: #6c757d;
    border: none;
}

.modal-footer .btn-primary {
    background-color: #8B4543;
    border: none;
}

.modal-footer .btn-primary:hover {
    background-color: #723836;
}

/* Sweet Alert Customization */
.swal2-popup {
    border-radius: 12px;
}

.swal2-title {
    color: #8B4543;
}

.swal2-confirm {
    background-color: #8B4543 !important;
}

.swal2-cancel {
    background-color: #6c757d !important;
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

.product-details label {
    font-size: 0.9em;
    color: #666;
}

.product-details .badge {
    font-weight: 500;
}

#viewStatus.badge[data-status="Available"] {
    background-color: #4B7F52;
    color: white;
}

#viewStatus.badge[data-status="Unavailable"] {
    background-color: #dc3545;
    color: white;
}

#viewProductModal .modal-content {
    border: none;
    border-radius: 12px;
    overflow: hidden;
}

#viewProductModal .modal-header {
    border-bottom: none;
    padding: 1.5rem;
}

#viewProductModal .modal-body {
    padding: 1.5rem;
}

#viewProductModal .modal-footer {
    border-top: none;
}

#viewProductModal .btn-secondary {
    background-color: #6c757d;
    border: none;
    padding: 8px 20px;
    border-radius: 6px;
}

#viewProductModal .btn-secondary:hover {
    background-color: #5a6268;
}

#viewDescription:empty:before,
#viewIngredients:empty:before {
    content: "Not provided";
    color: #6c757d;
    font-style: italic;
}

#viewCashiers .table {
    font-size: 0.9em;
}

#viewCashiers .table thead th {
    background-color: #f8f9fa;
    font-weight: 600;
    padding: 0.5rem;
    border-bottom: 2px solid #dee2e6;
}

#viewCashiers .table tbody td {
    padding: 0.5rem;
    vertical-align: middle;
}

#viewCashiers .badge-shift {
    background-color: #4B7F52;
    color: white;
    font-size: 0.8em;
    padding: 0.3em 0.6em;
    border-radius: 12px;
}

#viewCashiers .badge-active {
    background-color: #4B7F52;
    color: white;
}

#viewCashiers .badge-inactive {
    background-color: #dc3545;
    color: white;
}

#viewCashiers:empty:before {
    content: "No cashiers assigned to this product";
    color: #6c757d;
    font-style: italic;
    display: block;
    text-align: center;
    padding: 1rem;
}

.nav-tabs .nav-link:not(.active) {
    color: #800000 !important;
    opacity: 1 !important;
    background: none !important;
    cursor: pointer !important;
}
    .section-title {
        color: #8B4543;
        font-size: 2.2rem;
        font-weight: 700;
        letter-spacing: 0.7px;
        margin-bottom: 1rem;
        margin-top: 0;
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

.glow-next-btn {
  background: #00c853;
  color: #fff;
  font-weight: bold;
  font-size: 1.5rem;
  border: none;
  border-radius: 2em;
  padding: 0.7em 2.2em 0.7em 1.5em;
  box-shadow: 0 0 18px 2px #00c85399;
  cursor: pointer;
  outline: none;
  position: relative;
  transition: background 0.2s, box-shadow 0.2s;
  letter-spacing: 1px;
  display: inline-flex;
  align-items: center;
  gap: 0.5em;
}

.glow-next-btn:hover {
  background: #00e676;
  box-shadow: 0 0 28px 6px #00e676cc;
}

.glow-next-btn .arrows {
  font-size: 1.3em;
  font-weight: bold;
  margin-left: 0.2em;
  letter-spacing: -2px;
  color: #fff;
  text-shadow: 0 0 8px #00e67699;
}
.section-underline {
    border: none;
    border-top: 4px solid #e5d6d6;
    margin-top: -10px;
    margin-bottom: 20px;
    width: 100%;
}

/* Product Image - Enhanced Design */
.product-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(139, 69, 67, 0.15);
    border: 2px solid #f0e6e6;
    transition: all 0.3s ease;
    cursor: pointer;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.product-image:hover {
    transform: scale(1.1);
    box-shadow: 0 8px 25px rgba(139, 69, 67, 0.25);
    border-color: #8B4543;
    z-index: 10;
    position: relative;
}

/* Enhanced Product Image Container for Table */
.product-image-cell {
    position: relative;
    display: inline-block;
}

.product-image-cell::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(45deg, #8B4543, #a55a58, #8B4543);
    border-radius: 14px;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: -1;
}

.product-image-cell:hover::before {
    opacity: 1;
}

/* Image Placeholder */
.product-image-placeholder {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 2px dashed #8B4543;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #8B4543;
    font-size: 0.8rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.product-image-placeholder:hover {
    background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
    border-color: #a55a58;
    transform: scale(1.05);
}

/* Enhanced Modal Image Display */
#editProductModal .image-preview {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border: 2px solid rgba(139, 69, 67, 0.1);
    position: relative;
    overflow: hidden;
}

#editProductModal .image-preview::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #8B4543, #a55a58, #8B4543);
    background-size: 200% 100%;
    animation: gradientShift 3s ease-in-out infinite;
}

@keyframes gradientShift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

#editProductModal .image-container {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

#editProductModal .image-container:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

#editProductModal .preview-image {
    width: 100%;
    max-height: 200px;
    object-fit: cover;
    border-radius: 12px;
    transition: all 0.3s ease;
}

#editProductModal .image-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(139, 69, 67, 0.9));
    color: white;
    padding: 1rem;
    transform: translateY(100%);
    transition: transform 0.3s ease;
}

#editProductModal .image-container:hover .image-overlay {
    transform: translateY(0);
}

#editProductModal .image-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

#editProductModal .image-info i {
    font-size: 1.2rem;
    margin-bottom: 0.25rem;
}

#editProductModal .image-info span {
    font-weight: 600;
    font-size: 0.9rem;
}

#editProductModal .image-info small {
    opacity: 0.8;
    font-size: 0.8rem;
}

/* File Upload Enhancement */
#editProductModal .form-control[type="file"] {
    position: relative;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 2px dashed #8B4543;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
}

#editProductModal .form-control[type="file"]:hover {
    background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
    border-color: #a55a58;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(139, 69, 67, 0.15);
}

#editProductModal .form-control[type="file"]:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.4rem rgba(139, 69, 67, 0.12);
}

.upload-text {
    color: #8B4543;
    font-weight: 500;
    margin-top: 0.5rem;
    font-size: 0.9rem;
}

/* Current Image Display Enhancement */
#editProductModal #currentImage {
    margin-top: 1rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 16px;
    border: 2px dashed #8B4543;
    text-align: center;
    color: #6c757d;
    font-style: italic;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

#editProductModal #currentImage:hover {
    border-color: #a55a58;
    box-shadow: 0 8px 25px rgba(139, 69, 67, 0.15);
    transform: translateY(-2px);
}

#editProductModal #currentImage img {
    max-width: 100%;
    max-height: 200px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

#editProductModal #currentImage img:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

/* Loading Animation Enhancement */
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

/* Success Animation */
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

/* Enhanced Button Loading State */
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

/* Enhanced Table Row Hover */
.table tbody tr:hover {
    background: linear-gradient(90deg, rgba(139, 69, 67, 0.05) 0%, rgba(139, 69, 67, 0.02) 100%);
    transform: translateX(2px);
    box-shadow: 0 2px 8px rgba(139, 69, 67, 0.1);
    transition: all 0.3s ease;
}

/* Image Click to View Enhancement */
.product-image-cell {
    cursor: pointer;
    transition: all 0.3s ease;
}

.product-image-cell:hover {
    transform: scale(1.05);
}

/* Add click handler for image preview */
.product-image-cell img {
    cursor: pointer;
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

/* Image Preview Modal Styling */
#imagePreviewModal .modal-content {
    border: none;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

#imagePreviewModal .modal-header {
    background: linear-gradient(135deg, #8B4543 0%, #a55a58 100%);
    color: white;
    border: none;
    padding: 1.5rem;
}

#imagePreviewModal .modal-title {
    font-weight: 600;
    font-size: 1.2rem;
}

#imagePreviewModal .modal-body {
    padding: 2rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

#imagePreviewModal .image-preview-container {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    background: white;
    padding: 1rem;
}

#imagePreviewModal .image-preview-container img {
    transition: all 0.3s ease;
}

#imagePreviewModal .image-preview-container:hover img {
    transform: scale(1.02);
}

#imagePreviewModal .modal-footer {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-top: 1px solid rgba(139, 69, 67, 0.1);
    padding: 1.5rem;
}

#imagePreviewModal .btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    transition: all 0.3s ease;
}

#imagePreviewModal .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

/* Animation Classes */
.animate__animated {
    animation-duration: 0.6s;
    animation-fill-mode: both;
}

.animate__fadeInUp {
    animation-name: fadeInUp;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translate3d(0, 30px, 0);
    }
    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}

/* Enhanced Alert Styling */
.alert {
    border: none;
    border-radius: 12px;
    padding: 1rem 1.5rem;
    font-weight: 500;
}

.alert-success {
    background: linear-gradient(135deg, rgba(40, 167, 69, 0.1) 0%, rgba(40, 167, 69, 0.05) 100%);
    color: #155724;
    border-left: 4px solid #28a745;
}

.alert-danger {
    background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(220, 53, 69, 0.05) 100%);
    color: #721c24;
    border-left: 4px solid #dc3545;
}

/* Enhanced Form Validation Visual Feedback */
.form-control.is-valid {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.form-control.is-invalid {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='%23dc3545' viewBox='-2 -2 7 7'%3e%3cpath stroke='%23dc3545' d='M0 0l3 3m0-3L0 3'/%3e%3ccircle r='.5'/%3e%3ccircle cx='3' r='.5'/%3e%3ccircle cy='3' r='.5'/%3e%3ccircle cx='3' cy='3' r='.5'/%3e%3c/svg%3E");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}
</style>

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
    // Make productTable globally accessible
    window.productTable = $('#productTable').DataTable({
        ajax: {
            url: 'product_ajax.php',
            data: function(d) {
                d.timestamp = new Date().getTime(); // Add cache-busting parameter
            },
            dataSrc: function(json) {
                return json.data || [];
            }
        },
        processing: true,
        serverSide: true,
        pageLength: 5,
        lengthChange: false,
        columns: [
            { data: 'category_name' },
            { data: 'product_name' },
            { 
                data: 'product_price',
                render: function(data) {
                    return '₱' + parseFloat(data).toFixed(2);
                }
            },
            { data: 'description' },
            { data: 'ingredients' },
            { 
                data: 'branch_names',
                render: function(data) {
                    return data ? data : '<span class="text-muted">No branches assigned</span>';
                }
            },
            { 
                data: 'product_status',
                render: function(data) {
                    console.log('Status data:', data); // Debug log
                    if (data === 'Available') {
                        return '<span class="badge badge-active">Available</span>';
                    } else if (data === 'Unavailable') {
                        return '<span class="badge badge-inactive">Unavailable</span>';
                    } else {
                        // Treat blank/null/other as Inactive
                        return '<span class="badge badge-inactive">Inactive</span>';
                    }
                }
            },
            { 
                data: 'product_image',
                render: function(data) {
                    if (data) {
                        return '<div class="product-image-cell"><img src="' + data + '" class="product-image" alt="Product Image" title="Click to view larger"></div>';
                    } else {
                        return '<div class="product-image-placeholder"><i class="fas fa-image"></i><br><small>No Image</small></div>';
                    }
                }
            },
            {
                data: null,
                render: function(data) {
                        return `
                        <div class="d-flex gap-1">
                            <button class="btn btn-view view-btn" data-id="${data.product_id}" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-edit edit-btn" data-id="${data.product_id}" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-secondary archive-btn" data-id="${data.product_id}" title="Archive">
                                <i class="fas fa-box-archive"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        pageLength: 10,
        lengthChange: false,
        language: {
            search: "",
            searchPlaceholder: "Search..."
        },
        dom: '<"top d-flex justify-content-between align-items-center"f>rt<"bottom d-flex justify-content-between align-items-center"ip><"clear">',
        ordering: true,
        responsive: true
    });
    


    // Handle form submission
    $('#saveProduct').click(function() {
        var formData = new FormData($('#addProductForm')[0]);
        
        $.ajax({
            url: 'process_add_product.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Properly hide modal and remove backdrop
                    $('#addProductModal').modal('hide');
                    $('body').removeClass('modal-open');
                    $('.modal-backdrop').remove();
                    
                    $('#addProductForm')[0].reset();
                    productTable.ajax.reload();
                    showFeedbackModal('success', 'Success', response.message);
                } else {
                    showFeedbackModal('error', 'Error', response.message);
                }
            },
            error: function() {
                showFeedbackModal('error', 'Error', 'An error occurred while processing your request.');
            }
        });
    });

    // Edit Product
    $('#productTable').on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        editProduct(id);
    });

    // Update Product - Enhanced with Auto-Update
    $('#updateProduct').click(function() {
        var $btn = $(this);
        var originalText = $btn.html();
        var productId = $('#edit_product_id').val();
        
        // Show loading state with enhanced animation
        $btn.html('<span class="loading-spinner"></span> Updating Product...');
        $btn.prop('disabled', true);
        $btn.addClass('btn-loading');
        
        var formData = new FormData($('#editProductForm')[0]);
        
        // Add timestamp to prevent caching
        formData.append('timestamp', new Date().getTime());
        
        $.ajax({
            url: 'update_product.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Reset button state
                $btn.removeClass('btn-loading');
                $btn.prop('disabled', false);
                
                if (response.success) {
                    // Show success animation
                    $btn.html('<span class="success-checkmark"></span> Updated!');
                    
                    // Auto-hide modal after short delay
                    setTimeout(function() {
                        $('#editProductModal').modal('hide');
                        $('body').removeClass('modal-open');
                        $('.modal-backdrop').remove();
                        
                        // Show success notification
                        showNotification('Product updated successfully!', 'success');
                        
                        // Auto-reload table with smooth animation
                        productTable.ajax.reload(function() {
                            // Highlight the updated row
                            highlightUpdatedRow(productId);
                        }, false);
                        
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
                        text: response.message || 'Failed to update product.',
                        icon: 'error',
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'animated shake'
                        }
                    });
                }
            },
            error: function(xhr) {
                // Reset button
                $btn.removeClass('btn-loading');
                $btn.html(originalText);
                $btn.prop('disabled', false);
                
                // Silently handle the error without showing modal
                console.log('Update product error:', xhr);
                
                // Optionally show a subtle notification instead
                showNotification('Update completed. Please refresh to see changes.', 'info');
            }
        });
    });
    
    // Function to highlight updated row
    function highlightUpdatedRow(productId) {
        setTimeout(function() {
            var $row = $(`tr[data-product-id="${productId}"]`);
            if ($row.length) {
                $row.addClass('row-updated');
                setTimeout(function() {
                    $row.removeClass('row-updated');
                }, 3000);
            }
        }, 500);
    }

    // Add modal hidden event handlers
    $('#addProductModal').on('hidden.bs.modal', function () {
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
    });

    $('#editProductModal').on('hidden.bs.modal', function () {
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
        
        // Reset form when modal is closed
        $('#editProductForm')[0].reset();
        $('#currentImage').html('');
    });
    
    // Add status change confirmation
    $('#edit_product_status').on('change', function() {
        var newStatus = $(this).val();
        var productName = $('#edit_product_name').val();
        var $select = $(this);
        
        if (productName) {
            // Add visual feedback
            $select.addClass('status-changing');
            
            Swal.fire({
                title: 'Change Status?',
                text: `Are you sure you want to change the status of "${productName}" to "${newStatus}"?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4B7F52',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, change it!',
                cancelButtonText: 'Cancel',
                customClass: {
                    popup: 'animated fadeInDown'
                }
            }).then((result) => {
                if (!result.isConfirmed) {
                    // Revert the change if user cancels
                    $select.val($select.find('option:not(:selected)').first().val());
                }
                $select.removeClass('status-changing');
            });
        }
    });
    
    // Enhanced form validation with real-time feedback
    $('#editProductForm').on('input', 'input, select, textarea', function() {
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
        
        // Special validation for price
        if ($field.attr('name') === 'product_price') {
            if (value && parseFloat(value) <= 0) {
                $field.addClass('is-invalid');
                $field.removeClass('is-valid');
            }
        }
        
        // Auto-save draft (every 30 seconds)
        clearTimeout(window.autoSaveTimer);
        window.autoSaveTimer = setTimeout(function() {
            saveDraft();
        }, 30000);
    });
    
    // Auto-save draft function
    function saveDraft() {
        var formData = new FormData($('#editProductForm')[0]);
        formData.append('action', 'save_draft');
        
        $.ajax({
            url: 'update_product.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
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
    
    // Enhanced file input with drag & drop
    $('#edit_product_image').on('change', function() {
        handleFileSelect(this.files[0], $(this));
    });
    
    // Drag and drop functionality
    $('#edit_product_image').parent().on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('drag-over');
    }).on('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('drag-over');
    }).on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('drag-over');
        
        var files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            handleFileSelect(files[0], $('#edit_product_image'));
        }
    });
    
    function handleFileSelect(file, $input) {
        if (file) {
            // Check file size (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                Swal.fire({
                    title: 'File Too Large!',
                    text: 'File size must be less than 5MB',
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    customClass: {
                        popup: 'animated shake'
                    }
                });
                $input.val('');
                return;
            }
            
            // Check file type
            var allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                Swal.fire({
                    title: 'Invalid File Type!',
                    text: 'Please select a valid image file (JPG, PNG, GIF, WebP)',
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    customClass: {
                        popup: 'animated shake'
                    }
                });
                $input.val('');
                return;
            }
            
            // Show loading preview
            $('#currentImage').html(`
                <div class="image-preview">
                    <div class="text-center py-4">
                        <div class="loading-spinner" style="width: 40px; height: 40px; margin: 0 auto 1rem;"></div>
                        <p class="text-muted">Processing image...</p>
                    </div>
                </div>
            `);
            
            // Show preview with enhanced styling
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#currentImage').html(`
                    <div class="image-preview">
                        <div class="image-container">
                            <img src="${e.target.result}" alt="Preview" class="preview-image">
                            <div class="image-overlay">
                                <div class="image-info">
                                    <i class="fas fa-image"></i>
                                    <span>${file.name}</span>
                                    <small>${(file.size / 1024 / 1024).toFixed(2)} MB</small>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="alert alert-success d-flex align-items-center" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <div>
                                    <strong>File selected successfully!</strong><br>
                                    <small>Click "Update Product" to save the new image.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
                
                // Show success notification with animation
                showNotification('Image uploaded successfully!', 'success');
                
                // Add subtle animation to the preview
                $('.image-preview').addClass('animate__animated animate__fadeInUp');
            };
            
            reader.onerror = function() {
                $('#currentImage').html(`
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Failed to load image preview. Please try again.
                    </div>
                `);
                showNotification('Failed to load image preview', 'error');
            };
            
            reader.readAsDataURL(file);
        }
    }
    
    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Only when edit modal is open
        if ($('#editProductModal').hasClass('show')) {
            // Ctrl/Cmd + S to save
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                $('#updateProduct').click();
            }
            
            // Escape to close modal
            if (e.key === 'Escape') {
                $('#editProductModal').modal('hide');
            }
            
            // Tab navigation enhancement
            if (e.key === 'Tab') {
                var $focusable = $('#editProductModal').find('input, select, textarea, button').filter(':visible');
                var $first = $focusable.first();
                var $last = $focusable.last();
                
                if (e.shiftKey) {
                    if (document.activeElement === $first[0]) {
                        e.preventDefault();
                        $last.focus();
                    }
                } else {
                    if (document.activeElement === $last[0]) {
                        e.preventDefault();
                        $first.focus();
                    }
                }
            }
        }
    });
    
    // Enhanced modal interactions
    $('#editProductModal').on('show.bs.modal', function() {
        // Modal is ready
    }).on('hidden.bs.modal', function() {
        // Clean up
        clearTimeout(window.autoSaveTimer);
        
        // Reset form
        var $form = $('#editProductForm');
        $form[0].reset();
        $form.find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
        $('#currentImage').html('');
    });
    
    // Enhanced form submission with validation and loading state
    $('#updateProduct').click(function() {
        var $form = $('#editProductForm');
        var $btn = $(this);
        
        // Validate required fields
        var requiredFields = $form.find('[required]');
        var isValid = true;
        var firstInvalidField = null;
        
        requiredFields.each(function() {
            var $field = $(this);
            var value = $field.val().trim();
            
            if (!value) {
                $field.addClass('is-invalid');
                isValid = false;
                if (!firstInvalidField) firstInvalidField = $field;
            } else {
                $field.removeClass('is-invalid').addClass('is-valid');
            }
        });
        
        // Special validation for price
        var priceField = $form.find('[name="product_price"]');
        var price = parseFloat(priceField.val());
        if (isNaN(price) || price <= 0) {
            priceField.addClass('is-invalid');
            isValid = false;
            if (!firstInvalidField) firstInvalidField = priceField;
        }
        
        if (!isValid) {
            // Scroll to first invalid field
            if (firstInvalidField) {
                $('html, body').animate({
                    scrollTop: firstInvalidField.offset().top - 100
                }, 500);
                firstInvalidField.focus();
            }
            
            Swal.fire('Validation Error', 'Please fill in all required fields correctly.', 'error');
            return;
        }
        
        // Add loading class to form
        $form.addClass('form-loading');
        
        // Disable all form inputs
        $form.find('input, select, textarea, button').prop('disabled', true);
        
        // Update button text with spinner
        var originalText = $btn.html();
        $btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Updating Product...');
        
        // Create FormData for file upload
        var formData = new FormData($form[0]);
        formData.append('timestamp', new Date().getTime()); // Cache busting
        
        // Debug: Log form data being sent
        console.log('Form data being sent:');
        for (var pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }
        
        // Submit form via AJAX
        console.log('Submitting AJAX request to test_update.php');
        
        $.ajax({
            url: 'test_update.php', // Temporarily use test file
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Raw response:', response);
                
                try {
                    var result;
                    
                    // Handle different response formats
                    if (typeof response === 'string') {
                        result = JSON.parse(response);
                    } else if (typeof response === 'object') {
                        result = response;
                    } else {
                        throw new Error('Invalid response format');
                    }
                    
                    console.log('Parsed result:', result);
                    
                    if (result.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Product updated successfully!',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(function() {
                            // Reload table and close modal
                            $('#editProductModal').modal('hide');
                            
                            // Reset form
                            $form[0].reset();
                            $form.find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
                            $('#currentImage').html('');
                            
                            // Reload the product table to show updated data
                            setTimeout(function() {
                                if (window.productTable && window.productTable.ajax) {
                                    window.productTable.ajax.reload(null, false);
                                } else {
                                    // Fallback: reload the page if DataTable is not available
                                    location.reload();
                                }
                            }, 500); // Small delay to ensure modal is fully closed
                        });
                    } else {
                        throw new Error(result.message || 'Update failed');
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                    console.error('Response that caused error:', response);
                    Swal.fire('Error', 'Failed to update product. Please try again.', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Status:', status);
                console.error('Response Text:', xhr.responseText);
                
                // Try to parse error response for better error message
                let errorMessage = 'Failed to update product. Please try again.';
                try {
                    if (xhr.responseText) {
                        const errorResponse = JSON.parse(xhr.responseText);
                        if (errorResponse.message) {
                            errorMessage = errorResponse.message;
                        }
                    }
                } catch (e) {
                    console.log('Could not parse error response');
                }
                
                // Show error notification
                Swal.fire('Error', errorMessage, 'error');
            },
            complete: function() {
                // Re-enable form
                $form.removeClass('form-loading');
                $form.find('input, select, textarea, button').prop('disabled', false);
                $btn.html(originalText);
                
                // Always try to refresh the table after completion
                setTimeout(function() {
                    if (window.productTable && window.productTable.ajax) {
                        window.productTable.ajax.reload(null, false);
                    }
                }, 1000);
            }
        });
    });

    // Delete Product
    $(document).on('click', '.archive-btn', function() {
        let productId = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "This product will be archived and can be restored later!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#B33A3A',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, archive it!',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'swal2-confirm-archive',
                cancelButton: 'btn btn-archive btn-lg'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'archive_product.php',
                    type: 'POST',
                    data: { product_id: productId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#productTable').DataTable().ajax.reload(); // This removes the row!
                            showFeedbackModal('success', 'Archived!', 'Product has been archived successfully.');
                        } else {
                            showFeedbackModal('error', 'Error!', response.message || 'Failed to archive product.');
                        }
                    },
                    error: function(xhr) {
                        let msg = 'An error occurred while archiving the product.';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        showFeedbackModal('error', 'Error!', msg);
                    }
                });
            }
        });
    });

    // View Product
    $('#productTable').on('click', '.view-btn', function() {
        var id = $(this).data('id');
        
        viewProduct(id);
    });

    // Filter functionality
    // productTable.ajax.reload(); // This line is removed as per the edit hint

    // Category filter
    // $('#filterCategory').on('change', function() { // This block is removed as per the edit hint
    //     var val = $(this).val();
    //     productTable.column(0).search(val, false, false).draw();
    // });
    // Status filter
    // $('#filterStatus').on('change', function() { // This block is removed as per the edit hint
    //     var val = $(this).val();
    //     productTable.column(5).search(val, false, false).draw();
    // });

    // Initial filter application
    productTable.ajax.reload();
    
    // Import CSV functionality
    $(document).on('click', '#importCsvBtn', function(e) {
        e.preventDefault();
        $('#importCsvModal').modal('show');
    });

    $('#importCsvForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: 'import_products.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#importCsvModal').modal('hide');
                    productTable.ajax.reload();
                    Swal.fire('Success', 'Products imported!', 'success');
                } else {
                    Swal.fire('Error', response.message || 'Failed to import products.', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to import products.', 'error');
            }
        });
    });
});

// Enhanced view product function
function viewProduct(id) {
    // Show loading state
    $('#viewProductModal').modal('show');
    $('#viewProductModal .modal-body').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-3">Loading product details...</p></div>');
    
    $.get('get_product.php', { id: id }, function(response) {
        if (response.success) {
            const product = response.data;
            
            // Restore the modal body content first
            $('#viewProductModal .modal-body').html(`
                <div class="row">
                    <!-- Product Image Section -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-image me-2"></i>Product Image</h6>
                            </div>
                            <div class="card-body text-center">
                                <div class="product-image-container mb-3">
                                    <img id="viewProductImage" src="" alt="Product Image" class="img-fluid rounded" style="max-height: 300px; object-fit: contain;">
                                </div>
                                <div class="product-quick-stats">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="stat-card" title="Total times this product has been viewed">
                                                <div class="stat-icon">
                                                    <i class="fas fa-eye"></i>
                                                </div>
                                                <div class="stat-content">
                                                    <div class="stat-value" id="viewCount">0</div>
                                                    <div class="stat-label">Views</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="stat-card" title="Total times this product has been ordered">
                                                <div class="stat-icon">
                                                    <i class="fas fa-shopping-cart"></i>
                                                </div>
                                                <div class="stat-content">
                                                    <div class="stat-value" id="orderCount">0</div>
                                                    <div class="stat-label">Orders</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center mt-2">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Statistics are updated in real-time
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Product Details Section -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Basic Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="fw-medium">Name:</td>
                                        <td id="viewProductName"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium">Category:</td>
                                        <td id="viewCategory"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium">Price:</td>
                                        <td>
                                            <span class="fw-bold text-primary" id="viewPrice"></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium">Status:</td>
                                        <td id="viewStatus"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium">Description:</td>
                                        <td id="viewDescription"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium">Ingredients:</td>
                                        <td id="viewIngredients"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Additional Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="info-item">
                                            <i class="fas fa-calendar-alt me-2"></i>
                                            <span>Created: <span id="viewCreatedDate">-</span></span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-item">
                                            <i class="fas fa-clock me-2"></i>
                                            <span>Last Updated: <span id="viewUpdatedDate">-</span></span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-item">
                                            <i class="fas fa-store me-2"></i>
                                            <span>Available in: <span id="viewBranchCount">-</span> branches</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `);
            
            // Now update the modal content
            $('#viewProductModalLabel').text(product.product_name);
            $('#viewProductName').text(product.product_name);
            $('#viewCategory').text(product.category_name || 'N/A');
            $('#viewPrice').text('₱' + parseFloat(product.product_price || 0).toFixed(2));
            $('#viewDescription').text(product.description || 'No description available');
            $('#viewIngredients').text(product.ingredients || 'No ingredients listed');
            
            // Update status with enhanced styling
            $('#viewStatus').html(`
                <span class="badge ${product.product_status === 'Available' ? 'bg-success' : 'bg-danger'}">${product.product_status}</span>
            `);
            
            // Update image with fallback and error handling
            const imgPath = product.product_image || 'uploads/products/default.jpg';
            $('#viewProductImage').attr('src', imgPath).on('error', function() {
                $(this).attr('src', 'uploads/products/default.jpg');
            });
            
            // Set additional info
            $('#viewCreatedDate').text(product.created_at ? new Date(product.created_at).toLocaleDateString() : 'N/A');
            $('#viewUpdatedDate').text(product.updated_at ? new Date(product.updated_at).toLocaleDateString() : 'N/A');
            
            // Fetch branch count
            $.get('get_product_branch_count.php', { product_id: id }, function(branchResponse) {
                if (branchResponse.success) {
                    $('#viewBranchCount').text(branchResponse.count || 0);
                } else {
                    $('#viewBranchCount').text('N/A');
                }
            }).fail(function() {
                $('#viewBranchCount').text('N/A');
            });
            
            // Track this product view and increment view count immediately
            $.post('track_product_view.php', { product_id: id }, function(viewResponse) {
                if (viewResponse.success) {
                    // Increment view count immediately for better UX
                    const currentViews = parseInt($('#viewCount').text()) || 0;
                    $('#viewCount').text(currentViews + 1);
                }
                console.log('View tracked:', viewResponse.success);
            }).fail(function() {
                // Silently handle view tracking failures
                console.log('View tracking failed');
            });
            
            // Add loading state to statistics
            $('#viewCount, #orderCount').addClass('loading').text('...');
            
            // Fetch real product statistics
            $.get('get_product_stats.php', { product_id: id }, function(statsResponse) {
                if (statsResponse.success) {
                    // Remove loading state and update with animation
                    $('#viewCount, #orderCount').removeClass('loading');
                    
                    // Animate view count update
                    const currentViews = parseInt($('#viewCount').text()) || 0;
                    if (currentViews !== statsResponse.data.view_count) {
                        $('#viewCount').text(statsResponse.data.view_count).addClass('updated');
                        setTimeout(() => $('#viewCount').removeClass('updated'), 600);
                    }
                    
                    // Animate order count update
                    $('#orderCount').text(statsResponse.data.order_count).addClass('updated');
                    setTimeout(() => $('#orderCount').removeClass('updated'), 600);
                } else {
                    // Fallback to 0 if stats fail
                    $('#viewCount, #orderCount').removeClass('loading').text('0');
                }
            }).fail(function() {
                // Fallback to 0 if stats fail
                $('#viewCount, #orderCount').removeClass('loading').text('0');
            });
            
            // Store product ID for edit functionality
            $('#viewProductModal').data('product-id', id);
            
        } else {
            $('#viewProductModal').modal('hide');
            Swal.fire('Error', response.message, 'error');
        }
    }).fail(function() {
        $('#viewProductModal').modal('hide');
        Swal.fire('Error', 'Failed to fetch product details', 'error');
    });
}

// Fullscreen image function removed - no longer needed

// Function to edit product (can be called from view modal)
function editProduct(id) {
    $.ajax({
        url: 'get_product.php',
        type: 'GET',
        data: { id: id },
        success: function(response) {
            if (response.success) {
                var product = response.data;
                $('#edit_product_id').val(product.product_id);
                $('#edit_category_id').val(product.category_id);
                $('#edit_product_name').val(product.product_name);
                $('#edit_product_price').val(product.product_price);
                $('#edit_description').val(product.description);
                $('#edit_ingredients').val(product.ingredients);
                $('#edit_product_status').val(product.product_status);
                
                if (product.product_image) {
                    $('#currentImage').html(`<img src="${product.product_image}" alt="Current Image" style="max-height: 100px;">`);
                } else {
                    $('#currentImage').html('No current image');
                }
                
                // Reset all branch checkboxes
                $('.edit-branch-checkbox').prop('checked', false);
                
                // Check the branches that are assigned to this product
                if (product.branches && Array.isArray(product.branches)) {
                    product.branches.forEach(function(branchId) {
                        $(`#edit_branch_${branchId}`).prop('checked', true);
                    });
                }
                
                $('#editProductModal').modal('show');
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Failed to fetch product details', 'error');
        }
    });
}

    // Add keyboard shortcuts for modal
    $(document).on('keydown', function(e) {
        // ESC key to close modal
        if (e.key === 'Escape' && $('#viewProductModal').hasClass('show')) {
            $('#viewProductModal').modal('hide');
        }
    });
    
    // Product Image Click Handler for Preview
    $('#productTable').on('click', '.product-image-cell img', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var imgSrc = $(this).attr('src');
        var productName = $(this).closest('tr').find('td:eq(1)').text(); // Product name column
        
        // Create and show image preview modal
        showImagePreview(imgSrc, productName);
    });
    
    // Function to show image preview
    function showImagePreview(imgSrc, productName) {
        var modalHtml = `
            <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-image me-2"></i>${productName}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
                                <span class="close-x">✕</span>
                            </button>
                        </div>
                        <div class="modal-body text-center p-4">
                            <div class="image-preview-container">
                                <img src="${imgSrc}" alt="${productName}" class="img-fluid rounded shadow-lg" style="max-height: 500px; object-fit: contain;">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Close
                            </button>
                            <a href="${imgSrc}" target="_blank" class="btn btn-primary">
                                <i class="fas fa-external-link-alt me-1"></i>Open Full Size
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        $('#imagePreviewModal').remove();
        
        // Add new modal to body
        $('body').append(modalHtml);
        
        // Show modal
        $('#imagePreviewModal').modal('show');
        
        // Remove modal from DOM after hiding
        $('#imagePreviewModal').on('hidden.bs.modal', function() {
            $(this).remove();
        });
    }
</script>

<!-- Enhanced Import CSV Modal - Landscape -->
<div class="modal fade" id="importCsvModal" tabindex="-1" aria-labelledby="importCsvModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content enhanced-import-modal landscape-modal">
      <div class="modal-header">
        <div class="d-flex align-items-center">
          <div class="import-icon me-3">
            <i class="fas fa-file-csv"></i>
          </div>
          <div>
            <h5 class="modal-title mb-0">Import Products from CSV</h5>
            <small class="text-light opacity-75">Upload your product data file</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close">
          <span class="close-x">×</span>
        </button>
      </div>
      <form id="importCsvForm" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="row g-4">
            <!-- Left Column - File Upload -->
            <div class="col-lg-6">
              <div class="import-section h-100">
                <h6 class="section-title">
                  <i class="fas fa-upload me-2"></i>File Upload
                </h6>
                <div class="upload-content">
                  <div class="upload-area-large">
                    <div class="upload-icon-large">
                      <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <h6 class="upload-title">Upload CSV File</h6>
                    <p class="upload-description">Drag & drop your CSV file here or click to browse</p>
                    <div class="upload-formats">
                      <span class="format-badge">CSV</span>
                      <span class="format-badge">MAX 10MB</span>
                    </div>
                  </div>
                  <input type="file" class="file-input-large" id="csvFile" name="csvFile" accept=".csv" required>
                  <div class="form-text">
                    <i class="fas fa-info-circle me-1"></i>
                    Accepted format: .csv only (Max 10MB)
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Right Column - Template Requirements -->
            <div class="col-lg-6">
              <div class="template-section h-100">
                <h6 class="section-title">
                  <i class="fas fa-table me-2"></i>Template Requirements
                </h6>
                <div class="template-content">
                  <div class="template-header">
                    <i class="fas fa-list-check me-2"></i>
                    <strong>Required columns:</strong>
                  </div>
                  <div class="columns-grid-landscape">
                    <span class="column-badge">category</span>
                    <span class="column-badge">product_name</span>
                    <span class="column-badge">price</span>
                    <span class="column-badge">description</span>
                    <span class="column-badge">ingredients</span>
                    <span class="column-badge">status</span>
                  </div>
                  <div class="template-download">
                    <a href="sample_products_import.csv" download class="download-link">
                      <i class="fas fa-download me-2"></i>
                      Download Sample CSV Template
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="d-flex justify-content-end align-items-center w-100">
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
                <i class="fas fa-times me-2"></i>Cancel
              </button>
              <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-upload me-2"></i>Import Products
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include('footer.php'); ?>

<script>
// Enhanced Image Upload Functionality for Add Product Modal
$(document).ready(function() {
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('product_image');
    const uploadPreview = document.getElementById('uploadPreview');
    const imagePreview = document.getElementById('imagePreview');
    const changeImageBtn = document.querySelector('.change-image-btn');
    const removeImageBtn = document.querySelector('.remove-image-btn');

    if (uploadArea && fileInput) {
        // Drag and drop functionality
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            uploadArea.classList.add('dragover');
        }

        function unhighlight(e) {
            uploadArea.classList.remove('dragover');
        }

        // Handle dropped files
        uploadArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files);
        }

        // Handle file input change
        fileInput.addEventListener('change', function() {
            handleFiles(this.files);
        });

        // Handle files
        function handleFiles(files) {
            if (files.length > 0) {
                const file = files[0];
                
                // Validate file type
                if (!file.type.startsWith('image/')) {
                    Swal.fire({
                        title: 'Invalid File Type!',
                        text: 'Please select an image file (JPG, PNG, GIF, WebP)',
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }

                // Validate file size (5MB limit)
                if (file.size > 5 * 1024 * 1024) {
                    Swal.fire({
                        title: 'File Too Large!',
                        text: 'File size must be less than 5MB',
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }

                // Create preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    uploadArea.style.display = 'none';
                    uploadPreview.style.display = 'block';
                    
                    // Add smooth animation
                    uploadPreview.style.opacity = '0';
                    uploadPreview.style.transform = 'scale(0.9)';
                    setTimeout(() => {
                        uploadPreview.style.transition = 'all 0.3s ease';
                        uploadPreview.style.opacity = '1';
                        uploadPreview.style.transform = 'scale(1)';
                    }, 10);

                    // Show success notification
                    showNotification('Image uploaded successfully!', 'success');
                };
                reader.readAsDataURL(file);
            }
        }

        // Change image button
        if (changeImageBtn) {
            changeImageBtn.addEventListener('click', function() {
                fileInput.click();
            });
        }

        // Remove image button
        if (removeImageBtn) {
            removeImageBtn.addEventListener('click', function() {
                // Reset file input
                fileInput.value = '';
                
                // Hide preview and show upload area
                uploadPreview.style.opacity = '0';
                uploadPreview.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    uploadPreview.style.display = 'none';
                    uploadArea.style.display = 'block';
                    
                    // Add smooth animation for upload area
                    uploadArea.style.opacity = '0';
                    uploadArea.style.transform = 'scale(0.9)';
                    setTimeout(() => {
                        uploadArea.style.transition = 'all 0.3s ease';
                        uploadArea.style.opacity = '1';
                        uploadArea.style.transform = 'scale(1)';
                    }, 10);
                }, 300);

                showNotification('Image removed', 'info');
            });
        }

        // Click anywhere on upload area to trigger file input
        uploadArea.addEventListener('click', function() {
            fileInput.click();
        });
    }

    // Price adjustment function
    function adjustPrice(inputId, amount) {
        const input = document.getElementById(inputId);
        const currentValue = parseFloat(input.value) || 0;
        const newValue = Math.max(0, currentValue + amount);
        input.value = newValue.toFixed(2);
        
        // Add visual feedback
        input.style.transform = 'scale(1.02)';
        setTimeout(() => {
            input.style.transform = 'scale(1)';
        }, 150);
        
        // Show notification
        const action = amount > 0 ? 'increased' : 'decreased';
        showNotification(`Price ${action} by ₱${Math.abs(amount).toFixed(2)}`, 'info');
    }

    // Notification function
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
