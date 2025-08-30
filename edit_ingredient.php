<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

// Fetch categories for the dropdown
$categories = $pdo->query("SELECT category_id, category_name FROM pos_category")->fetchAll(PDO::FETCH_ASSOC);

$message = '';
$errors = [];
$ingredient_id = isset($_GET['id']) ? $_GET['id'] : '';
$category_id = '';
$ingredient_name = '';
$ingredient_quantity = 0; // Ensure it's numeric
$ingredient_unit = '';
$ingredient_status = 'Available';

if ($ingredient_id) {
    $stmt = $pdo->prepare("SELECT * FROM ingredients WHERE ingredient_id = :ingredient_id");
    $stmt->execute(['ingredient_id' => $ingredient_id]);
    $ingredient = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ingredient) {
        $category_id = $ingredient['category_id'];
        $ingredient_name = $ingredient['ingredient_name'];
        $ingredient_quantity = (float) $ingredient['ingredient_quantity']; // Convert to float
        $ingredient_unit = $ingredient['ingredient_unit'];
        $ingredient_status = $ingredient['ingredient_status'];
        $consume_before = $ingredient['consume_before'] ?? '';
    } else {
        $message = 'Ingredient not found.';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $category_id = $_POST['category_id'];
    $ingredient_name = trim($_POST['ingredient_name']);
    $ingredient_unit = trim($_POST['ingredient_unit']);
    $ingredient_status = $_POST['ingredient_status'];
    $consume_before = $_POST['consume_before'] ?? '';
    $change_quantity = isset($_POST['change_quantity']) ? (float) $_POST['change_quantity'] : 0; // Convert to float, default to 0

    // Validate fields
    if (empty($category_id)) {
        $errors[] = 'Category is required.';
    }
    if (empty($ingredient_name)) {
        $errors[] = 'Ingredient Name is required.';
    }
    if (empty($ingredient_unit)) {
        $errors[] = 'Unit of Measurement is required.';
    }
    if ($change_quantity < 0) {
        $errors[] = 'Quantity cannot be negative.';
    }

    // Adjust quantity if change_quantity is provided
    if ($change_quantity > 0) {
        $ingredient_quantity += $change_quantity;
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE ingredients SET category_id = ?, ingredient_name = ?, ingredient_quantity = ?, ingredient_unit = ?, ingredient_status = ?, consume_before = ? WHERE ingredient_id = ?");
        $stmt->execute([$category_id, $ingredient_name, $ingredient_quantity, $ingredient_unit, $ingredient_status, $consume_before, $ingredient_id]);
        
        // Check if this is an AJAX request (modal mode)
        if (isset($_GET['modal']) && $_GET['modal'] == 1) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Ingredient updated successfully']);
            exit;
        } else {
            header("Location: ingredients.php");
            exit;
        }
    } else {
        if (isset($_GET['modal']) && $_GET['modal'] == 1) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
            exit;
        } else {
            $message = '<ul class="list-unstyled">';
            foreach ($errors as $error) {
                $message .= '<li>' . $error . '</li>';
            }
            $message .= '</ul>';
        }
    }
}

$modal = isset($_GET['modal']) && $_GET['modal'] == 1;

if ($modal) {
    ?>
    <style>
    /* Enhanced Modal Styles */
    .ingredient-modal-header {
        background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
        color: #fff;
        border-top-left-radius: 1.25rem;
        border-top-right-radius: 1.25rem;
        padding: 2rem 2rem 1.5rem 2rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        font-size: 1.5rem;
        font-weight: 700;
        border-bottom: 3px solid #D4A59A;
        box-shadow: 0 4px 20px 0 rgba(139, 69, 67, 0.15);
        position: sticky;
        top: 0;
        z-index: 2;
        margin: 0;
        position: relative;
        overflow: hidden;
    }
    
    .ingredient-modal-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
        animation: shimmer 3s infinite;
    }
    
    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }
    
    .ingredient-modal-header .ingredient-header-icon {
        background: linear-gradient(135deg, #D4A59A 0%, #C4B1B1 100%);
        color: #8B4543;
        border-radius: 50%;
        width: 3rem;
        height: 3rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-right: 0.75rem;
        box-shadow: 0 4px 12px rgba(139, 69, 67, 0.2);
        position: relative;
        z-index: 1;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .ingredient-modal-header .ingredient-header-icon:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(139, 69, 67, 0.3);
    }
    
    .ingredient-modal-close {
        position: absolute;
        right: 2rem;
        top: 1.5rem;
        color: #fff;
        font-size: 1.8rem;
        background: rgba(255, 255, 255, 0.1);
        border: 2px solid rgba(255, 255, 255, 0.2);
        cursor: pointer;
        z-index: 10;
        transition: all 0.3s ease;
        border-radius: 50%;
        width: 2.8rem;
        height: 2.8rem;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(10px);
    }
    
    .ingredient-modal-close:hover {
        background: rgba(255, 255, 255, 0.2);
        color: #D4A59A;
        border-color: rgba(255, 255, 255, 0.4);
        transform: rotate(90deg);
    }
    
    .ingredient-modal-body {
        background: linear-gradient(180deg, #fff 0%, #fafafa 100%);
        border-bottom-left-radius: 1.25rem;
        border-bottom-right-radius: 1.25rem;
        padding: 3rem 3rem 2.5rem 3rem;
        margin: 0;
        position: relative;
        min-height: 700px;
    }
    
    .ingredient-modal-body::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent 0%, #D4A59A 50%, transparent 100%);
    }
    
    /* Remove default modal-content padding/margin if present */
    .modal-content {
        border-radius: 1.25rem !important;
        margin: 0 !important;
        padding: 0 !important;
        background: transparent !important;
        box-shadow: 0 8px 32px 0 rgba(139, 69, 67, 0.15);
        border: none !important;
        max-width: 1400px !important;
        width: 95vw !important;
    }
    
    /* Landscape Modal Optimization */
    .modal-xl {
        max-width: 1400px !important;
        width: 95vw !important;
    }
    
    @media (min-width: 1200px) {
        .modal-xl {
            max-width: 1400px !important;
            width: 95vw !important;
        }
        
        .landscape-form-container {
            grid-template-columns: 1fr 1.4fr 1fr;
            gap: 3rem;
        }
        
        .form-column {
            padding: 2.5rem;
        }
    }
    
    @media (min-width: 1600px) {
        .modal-xl {
            max-width: 1600px !important;
            width: 95vw !important;
        }
        
        .landscape-form-container {
            grid-template-columns: 1fr 1.5fr 1fr;
            gap: 3.5rem;
        }
    }
    
    /* Remove default modal-body padding if present */
    #editIngredientModalBody {
        padding: 0 !important;
        margin: 0 !important;
        background: transparent !important;
    }
    
    .ingredient-form-group {
        margin-bottom: 1.75rem;
        position: relative;
    }
    
    .ingredient-form-label {
        font-weight: 700;
        color: #3C2A2A;
        margin-bottom: 0.75rem;
        display: block;
        letter-spacing: 0.5px;
        font-size: 1.1rem;
        position: relative;
        padding-left: 1.5rem;
        white-space: nowrap;
        overflow: visible;
        min-width: 0;
    }
    
    .ingredient-form-label::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 8px;
        height: 8px;
        background: linear-gradient(135deg, #8B4543 0%, #D4A59A 100%);
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(139, 69, 67, 0.2);
    }
    
    .ingredient-form-input, .ingredient-form-select {
        width: 100%;
        border-radius: 0.75rem;
        border: 2px solid #E8E8E8;
        font-size: 1.1rem;
        padding: 1rem 1.25rem;
        color: #3C2A2A;
        background: #fff;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(139, 69, 67, 0.05);
        position: relative;
    }
    
    .ingredient-form-input:focus, .ingredient-form-select:focus {
        border-color: #8B4543;
        outline: none;
        box-shadow: 0 0 0 4px rgba(139, 69, 67, 0.1), 0 4px 16px rgba(139, 69, 67, 0.1);
        transform: translateY(-2px);
    }
    
    .ingredient-form-input:hover, .ingredient-form-select:hover {
        border-color: #D4A59A;
        box-shadow: 0 4px 12px rgba(139, 69, 67, 0.08);
    }
    
    /* Enhanced input animations */
    .ingredient-form-input, .ingredient-form-select {
        position: relative;
        overflow: hidden;
    }
    
    .ingredient-form-input::before, .ingredient-form-select::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(139, 69, 67, 0.1), transparent);
        transition: left 0.5s;
    }
    
    .ingredient-form-input:focus::before, .ingredient-form-select:focus::before {
        left: 100%;
    }
    
    .ingredient-form-input[readonly] {
        background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f4 100%);
        color: #8B4543;
        font-weight: 700;
        border-color: #D4A59A;
        position: relative;
    }
    
    .ingredient-form-input[readonly]::after {
        content: '📊';
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.2rem;
    }
    
    /* Enhanced Current Quantity Field */
    .current-quantity-group {
        position: relative;
        background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f4 100%);
        border-radius: 0.75rem;
        padding: 1.5rem;
        border: 2px solid #D4A59A;
        margin-bottom: 1.75rem;
    }
    
    .current-quantity-group::before {
        content: '📈 Current Stock Level';
        position: absolute;
        top: -0.75rem;
        left: 1rem;
        background: #8B4543;
        color: white;
        padding: 0.25rem 1rem;
        border-radius: 1rem;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .current-quantity-group .ingredient-form-label {
        color: #8B4543;
        font-weight: 800;
        font-size: 1.2rem;
        margin-bottom: 0.5rem;
    }
    
    .current-quantity-group .ingredient-form-input {
        background: #fff;
        border-color: #8B4543;
        font-size: 1.3rem;
        font-weight: 700;
        text-align: center;
        color: #8B4543;
    }
    
    /* Enhanced Change Quantity Field */
    .change-quantity-group {
        position: relative;
        background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
        border-radius: 0.75rem;
        padding: 1.5rem;
        border: 2px dashed #D4A59A;
        margin-bottom: 1.75rem;
    }
    
    .change-quantity-group::before {
        content: '🔄 Stock Adjustment';
        position: absolute;
        top: -0.75rem;
        left: 1rem;
        background: #4A7C59;
        color: white;
        padding: 0.25rem 1rem;
        border-radius: 1rem;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .change-quantity-group .ingredient-form-label {
        color: #4A7C59;
        font-weight: 700;
    }
    
    .change-quantity-group .ingredient-form-input {
        border-color: #4A7C59;
    }
    
    .change-quantity-group .ingredient-form-input:focus {
        border-color: #4A7C59;
        box-shadow: 0 0 0 4px rgba(74, 124, 89, 0.1);
    }
    
    /* Quantity adjustment buttons */
    .quantity-adjustment {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.5rem;
        flex-wrap: wrap;
    }
    
    .quantity-btn {
        background: linear-gradient(135deg, #4A7C59 0%, #3a6147 100%);
        color: white;
        border: none;
        border-radius: 0.5rem;
        padding: 0.6rem 1rem;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s ease;
        font-weight: 600;
        min-width: 50px;
        box-shadow: 0 2px 4px rgba(74, 124, 89, 0.2);
    }
    
    .quantity-btn:hover {
        background: linear-gradient(135deg, #3a6147 0%, #2d4d3a 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(74, 124, 89, 0.3);
    }
    
    .quantity-btn:active {
        transform: translateY(0);
    }
    
    .quantity-btn.clear-btn {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        margin-left: auto;
    }
    
    .quantity-btn.clear-btn:hover {
        background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
    }
    
    .quantity-preview {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border: 1px solid #90caf9;
        border-radius: 0.5rem;
        padding: 0.75rem;
        margin-top: 1rem;
    }
    
    .quantity-preview small {
        color: #1976d2 !important;
        font-weight: 600;
    }
    
    #newTotal {
        color: #1565c0;
        font-size: 1.1em;
    }
    
    /* Enhanced Status Field */
    .status-group {
        position: relative;
    }
    
    .status-group .ingredient-form-select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%238B4543' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 1.5em;
        padding-right: 3rem;
    }
    
    .status-available {
        color: #28a745;
        font-weight: 600;
    }
    
    .status-out-of-stock {
        color: #dc3545;
        font-weight: 600;
    }
    
    /* Enhanced Action Buttons */
    .ingredient-modal-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.5rem;
        margin-top: 2.5rem;
        padding-top: 2rem;
        border-top: 2px solid #f0f0f0;
    }
    
    .ingredient-modal-actions .btn-primary {
        background: linear-gradient(135deg, #4A7C59 0%, #3a6147 100%);
        border: none;
        color: #fff;
        font-weight: 700;
        border-radius: 0.75rem;
        padding: 1rem 2.5rem;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 16px rgba(74, 124, 89, 0.3);
        min-width: 180px;
        justify-content: center;
    }
    
    .ingredient-modal-actions .btn-primary:hover {
        background: linear-gradient(135deg, #3a6147 0%, #2d4d3a 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 24px rgba(74, 124, 89, 0.4);
    }
    
    .ingredient-modal-actions .btn-primary:active {
        transform: translateY(0);
    }
    
    .ingredient-modal-actions .btn-cancel {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: 2px solid #8B4543;
        color: #8B4543;
        font-weight: 700;
        font-size: 1.1rem;
        text-decoration: none;
        padding: 1rem 2rem;
        border-radius: 0.75rem;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 140px;
        justify-content: center;
    }
    
    .ingredient-modal-actions .btn-cancel:hover {
        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
        color: #723937;
        border-color: #723937;
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(139, 69, 67, 0.15);
    }
    
    .ingredient-modal-actions .btn-cancel:active {
        transform: translateY(0);
    }
    
    /* Enhanced form validation */
    .ingredient-form-input.is-invalid,
    .ingredient-form-select.is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.1);
    }
    
    .ingredient-form-input.is-valid,
    .ingredient-form-select.is-valid {
        border-color: #28a745;
        box-shadow: 0 0 0 4px rgba(40, 167, 69, 0.1);
    }
    
    .validation-feedback {
        display: none;
        margin-top: 0.5rem;
        font-size: 0.9rem;
        font-weight: 600;
    }
    
    .validation-feedback.invalid-feedback {
        color: #dc3545;
        display: block;
    }
    
    .validation-feedback.valid-feedback {
        color: #28a745;
        display: block;
    }
    
    /* Loading state */
    .btn-loading {
        position: relative;
        pointer-events: none;
    }
    
    .btn-loading::after {
        content: '';
        position: absolute;
        width: 1rem;
        height: 1rem;
        top: 50%;
        left: 50%;
        margin-left: -0.5rem;
        margin-top: -0.5rem;
        border: 2px solid transparent;
        border-top: 2px solid currentColor;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .ingredient-modal-header {
            padding: 1.5rem 1.5rem 1rem 1.5rem;
            font-size: 1.25rem;
        }
        
        .ingredient-modal-body {
            padding: 2rem 1.5rem 1.5rem 1.5rem;
        }
        
        .ingredient-modal-actions {
            flex-direction: column;
            gap: 1rem;
        }
        
        .ingredient-modal-actions .btn-primary,
        .ingredient-modal-actions .btn-cancel {
            width: 100%;
        }
    }
    
    /* Enhanced animations */
    .ingredient-form-group {
        animation: slideInUp 0.6s ease forwards;
        opacity: 0;
        transform: translateY(20px);
    }
    
    .ingredient-form-group:nth-child(1) { animation-delay: 0.1s; }
    .ingredient-form-group:nth-child(2) { animation-delay: 0.2s; }
    .ingredient-form-group:nth-child(3) { animation-delay: 0.3s; }
    .ingredient-form-group:nth-child(4) { animation-delay: 0.4s; }
    .ingredient-form-group:nth-child(5) { animation-delay: 0.5s; }
    .ingredient-form-group:nth-child(6) { animation-delay: 0.6s; }
    
    @keyframes slideInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Success animation */
    .success-animation {
        animation: successPulse 0.6s ease-in-out;
    }
    
    @keyframes successPulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    /* Enhanced loading states */
    .btn-loading {
        position: relative;
        pointer-events: none;
        overflow: hidden;
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
    
    /* Enhanced Landscape Layout Styles */
    .landscape-form-container {
        display: grid;
        grid-template-columns: 1fr 1.3fr 1fr;
        gap: 2.5rem;
        margin-bottom: 2rem;
        min-height: 600px;
    }
    
    .form-column {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-radius: 1rem;
        padding: 2rem;
        border: 1px solid #e9ecef;
        box-shadow: 0 4px 20px rgba(139, 69, 67, 0.08);
        transition: all 0.3s ease;
        position: relative;
        overflow: visible;
        min-width: 0;
    }
    
    .form-column::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #8B4543 0%, #D4A59A 50%, #8B4543 100%);
    }
    
    .form-column:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(139, 69, 67, 0.15);
    }
    
    .column-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f1f3f4;
    }
    
    .column-header i {
        background: linear-gradient(135deg, #8B4543 0%, #D4A59A 100%);
        color: white;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        box-shadow: 0 4px 12px rgba(139, 69, 67, 0.2);
    }
    
    .column-header h5 {
        margin: 0;
        color: #3C2A2A;
        font-weight: 700;
        font-size: 1.1rem;
        letter-spacing: 0.5px;
        white-space: nowrap;
        overflow: visible;
    }
    
    /* Enhanced Header Styles */
    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
    }
    
    .header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .header-text {
        display: flex;
        flex-direction: column;
    }
    
    .modal-title {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 800;
        color: white;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .modal-subtitle {
        margin: 0;
        font-size: 0.95rem;
        color: rgba(255,255,255,0.9);
        font-weight: 400;
    }
    
    /* Stock Management Styles */
    .current-stock-section {
        margin-bottom: 2rem;
    }
    
    .stock-display {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 1rem;
        padding: 1.5rem;
        border: 2px solid #dee2e6;
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .stock-display:hover {
        border-color: #8B4543;
        box-shadow: 0 4px 20px rgba(139, 69, 67, 0.1);
    }
    
    .stock-icon {
        background: linear-gradient(135deg, #8B4543 0%, #D4A59A 100%);
        color: white;
        width: 4rem;
        height: 4rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin: 0 auto 1rem;
        box-shadow: 0 4px 16px rgba(139, 69, 67, 0.3);
    }
    
    .stock-label {
        font-weight: 600;
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
        display: block;
    }
    
    .stock-value {
        margin-bottom: 1rem;
    }
    
    .quantity-number {
        font-size: 2.5rem;
        font-weight: 800;
        color: #8B4543;
        display: block;
        line-height: 1;
    }
    
    .quantity-unit {
        font-size: 1.1rem;
        color: #6c757d;
        font-weight: 500;
    }
    
    .stock-status {
        margin-top: 0.5rem;
    }
    
    .status-indicator {
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-indicator.in-stock {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }
    
    .status-indicator.out-of-stock {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }
    
    /* Stock Adjustment Styles */
    .stock-adjustment-section {
        margin-top: 1.5rem;
    }
    
    .adjustment-input-group {
        margin-bottom: 1rem;
    }
    
    .adjustment-input {
        margin-bottom: 1rem;
        text-align: center;
        font-size: 1.2rem;
        font-weight: 600;
    }
    
    .adjustment-buttons {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .button-row {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
    }
    
    .quantity-btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s ease;
        min-width: 3rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .quantity-btn.add-btn {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }
    
    .quantity-btn.add-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    }
    
    .quantity-btn.subtract-btn {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }
    
    .quantity-btn.subtract-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    }
    
    .quantity-btn.clear-btn {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        color: white;
        margin-top: 0.5rem;
        width: 100%;
        padding: 0.75rem;
    }
    
    .quantity-btn.clear-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
    }
    
    .quantity-preview {
        margin: 1.5rem 0;
    }
    
    .preview-card {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border: 2px solid #2196f3;
        border-radius: 1rem;
        padding: 1rem;
        text-align: center;
        color: #1976d2;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        box-shadow: 0 4px 16px rgba(33, 150, 243, 0.15);
    }
    
    .preview-card i {
        font-size: 1.2rem;
        color: #2196f3;
    }
    
    /* Enhanced Action Buttons */
    .ingredient-modal-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 2rem 0 0;
        border-top: 2px solid #f1f3f4;
        margin-top: 2rem;
    }
    
    .btn-cancel {
        background: white;
        color: #8B4543;
        border: 2px solid #8B4543;
        padding: 0.75rem 2rem;
        border-radius: 0.75rem;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-cancel:hover {
        background: #8B4543;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(139, 69, 67, 0.3);
    }
    
    .btn-save {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 0.75rem;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 4px 16px rgba(40, 167, 69, 0.3);
    }
    
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 24px rgba(40, 167, 69, 0.4);
    }
    
    /* Responsive Design */
    @media (max-width: 1400px) {
        .landscape-form-container {
            grid-template-columns: 1fr 1.2fr 1fr;
            gap: 2rem;
        }
    }
    
    @media (max-width: 1200px) {
        .landscape-form-container {
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        .right-column {
            grid-column: 1 / -1;
        }
        
        .form-column {
            padding: 1.5rem;
        }
    }
    
    @media (max-width: 768px) {
        .landscape-form-container {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        .ingredient-modal-header {
            padding: 1.5rem;
        }
        
        .ingredient-modal-body {
            padding: 1.5rem;
        }
        
        .form-column {
            padding: 1.5rem;
        }
        
        .modal-xl {
            width: 95vw !important;
            max-width: none !important;
        }
    }
    </style>
    <div class="ingredient-modal-header">
        <div class="header-content">
            <div class="header-left">
                <span class="ingredient-header-icon"><i class="fas fa-carrot"></i></span>
                <div class="header-text">
                    <h4 class="modal-title">Edit Ingredient</h4>
                    <p class="modal-subtitle">Update ingredient details and stock levels</p>
                </div>
            </div>
            <button type="button" class="ingredient-modal-close" data-bs-dismiss="modal" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    
    <div class="ingredient-modal-body">
        <?php if ($message !== '') {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>' . $message . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
        } ?>
        
        <form method="POST" action="edit_ingredient.php?id=<?php echo htmlspecialchars($ingredient_id); ?>&modal=1" id="editIngredientForm">
            <div class="landscape-form-container">
                <!-- Left Column - Basic Information -->
                <div class="form-column left-column">
                    <div class="column-header">
                        <i class="fas fa-info-circle"></i>
                        <h5>Basic Information</h5>
                    </div>
                    
                    <div class="ingredient-form-group">
                        <label for="category_id" class="ingredient-form-label">
                            <i class="fas fa-tag"></i>Category
                        </label>
                        <select name="category_id" id="category_id" class="ingredient-form-select" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>" <?php if ($category_id == $category['category_id']) echo 'selected'; ?>><?php echo $category['category_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="validation-feedback invalid-feedback" id="category-feedback"></div>
                    </div>
                    
                    <div class="ingredient-form-group">
                        <label for="ingredient_name" class="ingredient-form-label">
                            <i class="fas fa-carrot"></i>Ingredient Name
                        </label>
                        <input type="text" name="ingredient_name" id="ingredient_name" class="ingredient-form-input" value="<?php echo htmlspecialchars($ingredient_name); ?>" required>
                        <div class="validation-feedback invalid-feedback" id="name-feedback"></div>
                    </div>
                    
                    <div class="ingredient-form-group">
                        <label for="ingredient_unit" class="ingredient-form-label">
                            <i class="fas fa-ruler"></i>Unit of Measurement
                        </label>
                        <input type="text" name="ingredient_unit" id="ingredient_unit" class="ingredient-form-input" value="<?php echo htmlspecialchars($ingredient_unit); ?>" required>
                        <div class="validation-feedback invalid-feedback" id="unit-feedback"></div>
                    </div>
                    
                    <div class="ingredient-form-group status-group">
                        <label for="ingredient_status" class="ingredient-form-label">
                            <i class="fas fa-toggle-on"></i>Status
                        </label>
                        <select name="ingredient_status" id="ingredient_status" class="ingredient-form-select" required>
                            <option value="Available" <?php if ($ingredient_status == 'Available') echo 'selected'; ?>>🟢 Available</option>
                            <option value="Out of Stock" <?php if ($ingredient_status == 'Out of Stock') echo 'selected'; ?>>🔴 Out of Stock</option>
                            <option value="Low Stock" <?php if ($ingredient_status == 'Low Stock') echo 'selected'; ?>>🟡 Low Stock</option>
                        </select>
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i>
                            Set ingredient availability status
                        </div>
                    </div>
                </div>
                
                <!-- Center Column - Stock Management -->
                <div class="form-column center-column">
                    <div class="column-header">
                        <i class="fas fa-chart-line"></i>
                        <h5>Stock Management</h5>
                    </div>
                    
                    <div class="current-stock-section">
                        <div class="stock-display">
                            <div class="stock-icon">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div class="stock-info">
                                <label class="stock-label">Current Stock Level</label>
                                <div class="stock-value">
                                    <span class="quantity-number"><?php echo htmlspecialchars($ingredient_quantity); ?></span>
                                    <span class="quantity-unit"><?php echo htmlspecialchars($ingredient_unit); ?></span>
                                </div>
                                <div class="stock-status">
                                    <span class="status-indicator <?php echo $ingredient_quantity > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                                        <?php echo $ingredient_quantity > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stock-adjustment-section">
                        <label for="change_quantity" class="ingredient-form-label">
                            <i class="fas fa-plus-minus"></i>Adjust Stock
                        </label>
                        <div class="adjustment-input-group">
                            <input type="number" name="change_quantity" id="change_quantity" class="ingredient-form-input adjustment-input" step="0.01" placeholder="Enter quantity to add/subtract">
                            <div class="adjustment-buttons">
                                <div class="button-row">
                                    <button type="button" class="quantity-btn add-btn" onclick="adjustQuantity(1)">+1</button>
                                    <button type="button" class="quantity-btn add-btn" onclick="adjustQuantity(5)">+5</button>
                                    <button type="button" class="quantity-btn add-btn" onclick="adjustQuantity(10)">+10</button>
                                </div>
                                <div class="button-row">
                                    <button type="button" class="quantity-btn subtract-btn" onclick="adjustQuantity(-1)">-1</button>
                                    <button type="button" class="quantity-btn subtract-btn" onclick="adjustQuantity(-5)">-5</button>
                                    <button type="button" class="quantity-btn subtract-btn" onclick="adjustQuantity(-10)">-10</button>
                                </div>
                                <button type="button" class="quantity-btn clear-btn" onclick="clearQuantity()">
                                    <i class="fas fa-eraser"></i> Clear
                                </button>
                            </div>
                        </div>
                        
                        <div class="quantity-preview">
                            <div class="preview-card">
                                <i class="fas fa-calculator"></i>
                                <span>New Total: <strong id="newTotal"><?php echo htmlspecialchars($ingredient_quantity); ?></strong> <?php echo htmlspecialchars($ingredient_unit); ?></span>
                            </div>
                        </div>
                        
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i>
                            Use positive values to add stock, negative values to subtract
                        </div>
                    </div>
                </div>
                
                <!-- Right Column - Additional Details -->
                <div class="form-column right-column">
                    <div class="column-header">
                        <i class="fas fa-cog"></i>
                        <h5>Additional Details</h5>
                    </div>
                    
                    <div class="ingredient-form-group">
                        <label for="consume_before" class="ingredient-form-label">
                            <i class="fas fa-calendar-times"></i>Consume Before Date
                        </label>
                        <input type="date" name="consume_before" id="consume_before" class="ingredient-form-input" value="<?php echo htmlspecialchars($consume_before); ?>" min="<?php echo date('Y-m-d'); ?>">
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i>
                            Expiry date or best before date (cannot be before today)
                        </div>
                    </div>
                    
                    <div class="ingredient-form-group">
                        <label for="minimum_stock" class="ingredient-form-label">
                            <i class="fas fa-exclamation-triangle"></i>Minimum Stock Threshold
                        </label>
                        <input type="number" name="minimum_stock" id="minimum_stock" class="ingredient-form-input" min="0" step="0.01" placeholder="0.00" value="<?php echo isset($ingredient['minimum_stock']) ? htmlspecialchars($ingredient['minimum_stock']) : ''; ?>">
                        <div class="form-text">
                            <i class="fas fa-bell"></i>
                            Low stock alert threshold
                        </div>
                    </div>
                    
                    <!-- Storage Location field removed -->
                    
                    <div class="ingredient-form-group">
                        <label for="notes" class="ingredient-form-label">
                            <i class="fas fa-sticky-note"></i>Notes
                        </label>
                        <textarea name="notes" id="notes" class="ingredient-form-input" rows="3" placeholder="Additional notes about the ingredient"><?php echo isset($ingredient['notes']) ? htmlspecialchars($ingredient['notes']) : ''; ?></textarea>
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i>
                            Any additional information
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="ingredient-modal-actions">
                <button type="button" class="btn-cancel" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" class="btn-save" id="saveBtn">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
            
            <input type="hidden" name="ingredient_id" value="<?php echo htmlspecialchars($ingredient_id); ?>">
        </form>
    </div>
    
    <script>
    // Enhanced form validation and interactions
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('editIngredientForm');
        const inputs = form.querySelectorAll('input, select');
        
        // Real-time validation
        inputs.forEach(input => {
            input.addEventListener('blur', validateField);
            input.addEventListener('input', clearValidation);
        });
        
        // Form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (validateForm()) {
                submitForm();
            }
        });
        
        // Status change effect
        const statusSelect = document.getElementById('ingredient_status');
        statusSelect.addEventListener('change', function() {
            updateStatusDisplay(this.value);
        });
        
        // Initial status display
        updateStatusDisplay(statusSelect.value);
        
        // Quantity change preview
        const changeQuantityInput = document.getElementById('change_quantity');
        if (changeQuantityInput) {
            changeQuantityInput.addEventListener('input', updateQuantityPreview);
            // Initial preview
            updateQuantityPreview();
        }
        
        // Date validation for consume_before
        const consumeBeforeInput = document.getElementById('consume_before');
        if (consumeBeforeInput) {
            consumeBeforeInput.addEventListener('change', validateDate);
        }
    });
    
    function validateField(e) {
        const field = e.target;
        const value = field.value.trim();
        let isValid = true;
        let feedback = '';
        
        // Remove existing validation classes
        field.classList.remove('is-valid', 'is-invalid');
        
        // Field-specific validation
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            feedback = 'This field is required.';
        } else if (field.id === 'ingredient_name' && value.length < 2) {
            isValid = false;
            feedback = 'Ingredient name must be at least 2 characters long.';
        } else if (field.id === 'change_quantity' && value && parseFloat(value) < -1000) {
            isValid = false;
            feedback = 'Quantity change cannot be less than -1000.';
        } else if (field.id === 'consume_before' && value) {
            const selectedDate = new Date(value);
            const today = new Date();
            today.setHours(0, 0, 0, 0); // Reset time to start of day
            
            if (selectedDate < today) {
                isValid = false;
                feedback = 'Consume before date cannot be before today.';
            }
        }
        
        // Apply validation styling
        if (isValid && value) {
            field.classList.add('is-valid');
        } else if (!isValid) {
            field.classList.add('is-invalid');
        }
        
        // Show feedback
        const feedbackElement = document.getElementById(field.id + '-feedback');
        if (feedbackElement) {
            feedbackElement.textContent = feedback;
            feedbackElement.className = 'validation-feedback ' + (isValid ? 'valid-feedback' : 'invalid-feedback');
        }
        
        return isValid;
    }
    
    function clearValidation(e) {
        const field = e.target;
        field.classList.remove('is-valid', 'is-invalid');
        const feedbackElement = document.getElementById(field.id + '-feedback');
        if (feedbackElement) {
            feedbackElement.className = 'validation-feedback';
        }
    }
    
    function validateDate(e) {
        const field = e.target;
        const value = field.value;
        
        if (value) {
            const selectedDate = new Date(value);
            const today = new Date();
            today.setHours(0, 0, 0, 0); // Reset time to start of day
            
            field.classList.remove('is-valid', 'is-invalid');
            
            if (selectedDate < today) {
                field.classList.add('is-invalid');
                // Show error message
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Date',
                    text: 'Consume before date cannot be before today.',
                    confirmButtonColor: '#8B4543'
                });
                field.value = ''; // Clear the invalid date
            } else {
                field.classList.add('is-valid');
            }
        }
    }
    
    function validateForm() {
        let isValid = true;
        const requiredFields = document.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!validateField({ target: field })) {
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    function submitForm() {
        const changeQuantity = parseFloat(document.getElementById('change_quantity').value) || 0;
        const currentQuantity = <?php echo $ingredient_quantity; ?>;
        const newTotal = currentQuantity + changeQuantity;
        
        // Check for large quantity changes
        if (Math.abs(changeQuantity) > currentQuantity * 0.5) {
            const action = changeQuantity > 0 ? 'increase' : 'decrease';
            const message = `You are about to ${action} the quantity by ${Math.abs(changeQuantity)} ${document.getElementById('ingredient_unit').value}. This is a significant change (${Math.abs(changeQuantity/currentQuantity*100).toFixed(1)}% of current stock). Are you sure?`;
            
            if (!confirm(message)) {
                return;
            }
        }
        
        // Check for negative stock
        if (newTotal < 0) {
            if (!confirm(`Warning: This will result in negative stock (${newTotal} ${document.getElementById('ingredient_unit').value}). Are you sure you want to proceed?`)) {
                return;
            }
        }
        
        const saveBtn = document.getElementById('saveBtn');
        const originalText = saveBtn.innerHTML;
        
        // Show loading state
        saveBtn.classList.add('btn-loading');
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        saveBtn.disabled = true;
        
        // Submit form via AJAX
        const formData = new FormData(document.getElementById('editIngredientForm'));
        
        fetch(document.getElementById('editIngredientForm').action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Add success animation to the form
                const form = document.getElementById('editIngredientForm');
                form.classList.add('success-animation');
                
                // Success - close modal and show success message
                const modal = bootstrap.Modal.getInstance(document.getElementById('editIngredientModal'));
                modal.hide();
                
                // Show success notification
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        confirmButtonColor: '#4A7C59',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
                
                // Refresh the table if it exists
                if (typeof refreshIngredientTable === 'function') {
                    refreshIngredientTable();
                }
                
                // Remove animation class after animation completes
                setTimeout(() => {
                    form.classList.remove('success-animation');
                }, 600);
            } else {
                // Show error message
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message,
                        confirmButtonColor: '#8B4543'
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while saving. Please try again.',
                    confirmButtonColor: '#8B4543'
                });
            }
        })
        .finally(() => {
            // Restore button state
            saveBtn.classList.remove('btn-loading');
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        });
    }
    
    function adjustQuantity(amount) {
        const input = document.getElementById('change_quantity');
        const currentValue = parseFloat(input.value) || 0;
        input.value = currentValue + amount;
        
        // Update quantity preview
        updateQuantityPreview();
        
        // Trigger validation
        input.dispatchEvent(new Event('blur'));
    }
    
    function clearQuantity() {
        document.getElementById('change_quantity').value = '';
        updateQuantityPreview();
    }
    
    function updateQuantityPreview() {
        const changeInput = document.getElementById('change_quantity');
        const currentQuantity = <?php echo $ingredient_quantity; ?>;
        const changeValue = parseFloat(changeInput.value) || 0;
        const newTotal = currentQuantity + changeValue;
        
        const newTotalElement = document.getElementById('newTotal');
        if (newTotalElement) {
            newTotalElement.textContent = newTotal.toFixed(2);
            
            // Add visual feedback for negative quantities
            if (newTotal < 0) {
                newTotalElement.style.color = '#dc3545';
                newTotalElement.innerHTML += ' <i class="fas fa-exclamation-triangle text-warning"></i>';
            } else if (newTotal < currentQuantity * 0.1) {
                newTotalElement.style.color = '#ffc107';
                newTotalElement.innerHTML += ' <i class="fas fa-exclamation-triangle text-warning"></i>';
            } else {
                newTotalElement.style.color = '#1565c0';
            }
        }
    }
    
    function updateStatusDisplay(status) {
        const statusSelect = document.getElementById('ingredient_status');
        statusSelect.className = 'ingredient-form-select ' + 
            (status === 'Available' ? 'status-available' : 'status-out-of-stock');
    }
    </script>
    <?php
    return;
}

include('header.php');

?>

<h1 class="mt-4">Edit Ingredient</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="ingredients.php">Ingredient Management</a></li>
    <li class="breadcrumb-item active">Edit Ingredient</li>
</ol>

<?php
if ($message !== '') {
    echo '<div class="alert alert-danger">' . $message . '</div>';
}
?>

<div class="modal-dialog" style="max-width: 500px; margin: 2rem auto;">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Ingredient Details</h5>
            <a href="ingredients.php" class="btn-close"></a>
        </div>
        <div class="modal-body">
            <form method="POST" action="edit_ingredient.php?id=<?php echo htmlspecialchars($ingredient_id); ?>">
                <div class="row mb-3">
                    <div class="col-5 fw-bold">Category:</div>
                    <div class="col-7">
                        <select name="category_id" id="category_id" class="form-select">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>" <?php if ($category_id == $category['category_id']) echo 'selected'; ?>><?php echo $category['category_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-5 fw-bold">Ingredient Name:</div>
                    <div class="col-7">
                        <input type="text" name="ingredient_name" id="ingredient_name" class="form-control" value="<?php echo htmlspecialchars($ingredient_name); ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-5 fw-bold">Current Quantity:</div>
                    <div class="col-7">
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($ingredient_quantity); ?>" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-5 fw-bold">Change Quantity:</div>
                    <div class="col-7">
                        <input type="number" name="change_quantity" id="change_quantity" class="form-control" step="0.01">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-5 fw-bold">Unit:</div>
                    <div class="col-7">
                        <input type="text" name="ingredient_unit" id="ingredient_unit" class="form-control" value="<?php echo htmlspecialchars($ingredient_unit); ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-5 fw-bold">Status:</div>
                    <div class="col-7">
                        <select name="ingredient_status" id="ingredient_status" class="form-select">
                            <option value="Available" <?php if ($ingredient_status == 'Available') echo 'selected'; ?>>Available</option>
                            <option value="Out of Stock" <?php if ($ingredient_status == 'Out of Stock') echo 'selected'; ?>>Out of Stock</option>
                        </select>
                    </div>
                </div>
                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include('footer.php');
?>

<style>
.modal-footer .btn-primary,
.modal-footer .btn-secondary {
    background: #8B4543 !important;
    border: none !important;
    color: #fff !important;
    font-weight: 600;
    border-radius: 0.8rem !important;
    padding: 1.2rem 2.5rem !important;
    font-size: 1.25rem !important;
    box-shadow: 0 2px 8px 0 rgba(139, 69, 67, 0.08) !important;
    transition: background 0.2s, box-shadow 0.2s;
    min-width: 160px;
    width: 100%;
    text-align: center;
    display: block;
    margin: 0;
}
.modal-footer {
    display: flex;
    gap: 1.5rem;
    justify-content: center;
    align-items: center;
    padding: 2rem 1.5rem 2rem 1.5rem !important;
    background: none;
    border: none;
}
.modal-footer .btn-primary:hover, .modal-footer .btn-primary:focus,
.modal-footer .btn-secondary:hover, .modal-footer .btn-secondary:focus {
    background: #723937 !important;
    color: #fff !important;
    box-shadow: 0 4px 16px 0 rgba(139, 69, 67, 0.13) !important;
}
</style>
