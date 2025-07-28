<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Only allow AJAX access for logged-in users
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    http_response_code(403);
    exit('You are not authorized to access this resource.');
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (!isset($_FILES['csvFile']) || $_FILES['csvFile']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Please select a valid file.']);
    exit;
}

$file = $_FILES['csvFile'];
$fileName = $file['name'];
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Check file extension
if ($fileExt !== 'csv') {
    echo json_encode(['success' => false, 'message' => 'Only CSV files are allowed.']);
    exit;
}

try {
    $rows = [];
    
    if ($fileExt === 'csv') {
        // Use PHP's built-in CSV functions
        if (($handle = fopen($file['tmp_name'], "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $rows[] = $data;
            }
            fclose($handle);
        }
    } else {
        // For Excel files, we'll need PHPSpreadsheet
        echo json_encode(['success' => false, 'message' => 'Excel (.xlsx) files require PHPSpreadsheet library. Please use CSV format or install the library.']);
        exit;
    }
    
    if (empty($rows) || count($rows) < 2) {
        echo json_encode(['success' => false, 'message' => 'File is empty or has no data rows.']);
        exit;
    }
    
    // Get header row
    $headers = array_map('strtolower', array_map('trim', $rows[0]));
    
    // Expected columns
    $expectedColumns = ['category', 'product_name', 'price', 'description', 'ingredients', 'status'];
    
    // Check if all required columns are present
    $missingColumns = array_diff($expectedColumns, $headers);
    if (!empty($missingColumns)) {
        echo json_encode(['success' => false, 'message' => 'Missing required columns: ' . implode(', ', $missingColumns)]);
        exit;
    }
    
    // Get column indices
    $categoryNameIndex = array_search('category', $headers);
    $productNameIndex = array_search('product_name', $headers);
    $productPriceIndex = array_search('price', $headers);
    $descriptionIndex = array_search('description', $headers);
    $ingredientsIndex = array_search('ingredients', $headers);
    $productStatusIndex = array_search('status', $headers);
    
    $successCount = 0;
    $skippedRows = [];
    $errors = [];
    
    // Process data rows (skip header)
    for ($i = 1; $i < count($rows); $i++) {
        $row = $rows[$i];
        
        // Skip empty rows
        if (empty(array_filter($row))) {
            continue;
        }
        
        $categoryName = trim($row[$categoryNameIndex] ?? '');
        $productName = trim($row[$productNameIndex] ?? '');
        $productPrice = trim($row[$productPriceIndex] ?? '');
        $description = trim($row[$descriptionIndex] ?? '');
        $ingredients = trim($row[$ingredientsIndex] ?? '');
        $productStatus = trim($row[$productStatusIndex] ?? 'Available');
        
        // Map status values
        if (strtolower($productStatus) === 'active') {
            $productStatus = 'Available';
        } elseif (strtolower($productStatus) === 'inactive') {
            $productStatus = 'Unavailable';
        }
        
        // Validate required fields
        if (empty($categoryName) || empty($productName) || empty($productPrice)) {
            $skippedRows[] = "Row " . ($i + 1) . ": Missing required fields";
            continue;
        }
        
        // Validate price
        if (!is_numeric($productPrice) || $productPrice < 0) {
            $skippedRows[] = "Row " . ($i + 1) . ": Invalid price value";
            continue;
        }
        
        // Get category_id from category_name
        $stmt = $pdo->prepare("SELECT category_id FROM pos_category WHERE LOWER(TRIM(category_name)) = LOWER(TRIM(?)) AND status = 'active'");
        $stmt->execute([$categoryName]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$category) {
            $skippedRows[] = "Row " . ($i + 1) . ": Category '$categoryName' not found";
            continue;
        }
        
        $categoryId = $category['category_id'];
        
        // Validate status
        $validStatuses = ['Available', 'Unavailable'];
        if (!in_array($productStatus, $validStatuses)) {
            $productStatus = 'Available'; // Default to Available if invalid
        }
        
        try {
            // Insert product
            $stmt = $pdo->prepare("INSERT INTO pos_product (category_id, product_name, product_price, description, ingredients, product_status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$categoryId, $productName, $productPrice, $description, $ingredients, $productStatus]);
            
            $successCount++;
            
            // Log activity
            $admin_id = $_SESSION['user_id'] ?? null;
            logActivity($pdo, $admin_id, 'Imported Product', 'Product: ' . $productName . ' (Category: ' . $categoryName . ')');
            
        } catch (PDOException $e) {
            $errors[] = "Row " . ($i + 1) . ": " . $e->getMessage();
        }
    }
    
    $message = "Successfully imported $successCount products.";
    if (!empty($skippedRows)) {
        $message .= " Skipped " . count($skippedRows) . " rows: " . implode(', ', array_slice($skippedRows, 0, 5));
        if (count($skippedRows) > 5) {
            $message .= " and " . (count($skippedRows) - 5) . " more";
        }
    }
    if (!empty($errors)) {
        $message .= " Errors: " . implode(', ', array_slice($errors, 0, 3));
        if (count($errors) > 3) {
            $message .= " and " . (count($errors) - 3) . " more";
        }
    }
    
    echo json_encode(['success' => true, 'message' => $message]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error processing file: ' . $e->getMessage()]);
}
?> 