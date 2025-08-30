<?php
require_once 'db_connect.php';
require_once 'auth_function.php';
checkAdminLogin();

header('Content-Type: application/json');

// Composer autoload for PHPSpreadsheet
require_once __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

function response($success, $message = '') {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if (!isset($_FILES['csvFile']) || $_FILES['csvFile']['error'] !== UPLOAD_ERR_OK) {
    response(false, 'No file uploaded or upload error.');
}

$file = $_FILES['csvFile']['tmp_name'];
$ext = strtolower(pathinfo($_FILES['csvFile']['name'], PATHINFO_EXTENSION));

$rows = [];

try {
    if ($ext === 'csv') {
        $handle = fopen($file, 'r');
        if (!$handle) response(false, 'Failed to open CSV file.');
        $header = fgetcsv($handle);
        while (($data = fgetcsv($handle)) !== false) {
            $rows[] = array_combine($header, $data);
        }
        fclose($handle);
    } elseif ($ext === 'xlsx' || $ext === 'xls') {
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $header = [];
        foreach ($sheet->getRowIterator() as $rowIndex => $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $rowData = [];
            foreach ($cellIterator as $cell) {
                $rowData[] = $cell->getValue();
            }
            if ($rowIndex === 1) {
                $header = $rowData;
            } else {
                $rows[] = array_combine($header, $rowData);
            }
        }
    } else {
        response(false, 'Unsupported file type.');
    }
} catch (Exception $e) {
    response(false, 'Error reading file: ' . $e->getMessage());
}

// Insert rows into ingredients table
$inserted = 0;
$skipped = [];
foreach ($rows as $i => $row) {
    if (empty($row['ingredient_name'])) { $skipped[] = 'Row '.($i+2).': missing ingredient_name'; continue; }
    $category_id = $row['category_id'] ?? null;
    // If category_id is missing, try to look up by category_name (case-insensitive, trimmed)
    if ((!$category_id || !is_numeric($category_id)) && !empty($row['category_name'])) {
        $catName = trim($row['category_name']);
        $catStmt = $pdo->prepare('SELECT category_id FROM pos_category WHERE LOWER(TRIM(category_name)) = LOWER(TRIM(?))');
        $catStmt->execute([$catName]);
        $category_id = $catStmt->fetchColumn();
    }
    // Skip if category_id is still not found or not a valid int
    if (!$category_id || !is_numeric($category_id)) {
        $skipped[] = 'Row '.($i+2).': invalid or missing category (got: '.($row['category_id'] ?? $row['category_name'] ?? 'none').')';
        continue;
    }
    $stmt = $pdo->prepare("INSERT INTO ingredients (category_id, ingredient_name, ingredient_quantity, ingredient_unit, ingredient_status) VALUES (?, ?, ?, ?, 'Available')");
    
    $stmt->execute([
        $category_id,
        $row['ingredient_name'],
        $row['ingredient_quantity'] ?? 0,
        $row['ingredient_unit'] ?? ''
    ]);
    $inserted++;
}

$msg = "Imported $inserted ingredients.";
if (count($skipped) > 0) {
    $msg .= " Skipped: " . implode('; ', $skipped);
}
if ($inserted > 0) {
    response(true, $msg);
} else {
    response(false, $msg);
} 