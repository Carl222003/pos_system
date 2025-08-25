<?php
require_once 'db_connect.php';

echo "Testing Branch-Specific Ingredient Filtering\n";
echo "==========================================\n\n";

try {
    // Get all active branches
    $branches = $pdo->query("SELECT branch_id, branch_name FROM pos_branch WHERE status = 'Active'")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($branches as $branch) {
        echo "Branch: " . $branch['branch_name'] . " (ID: " . $branch['branch_id'] . ")\n";
        echo "----------------------------------------\n";
        
        // Get ingredients for this specific branch
        $query = "SELECT 
                    i.ingredient_name,
                    bi.quantity,
                    i.ingredient_unit,
                    c.category_name
                  FROM ingredients i
                  INNER JOIN branch_ingredient bi ON i.ingredient_id = bi.ingredient_id
                  LEFT JOIN pos_category c ON i.category_id = c.category_id
                  WHERE bi.branch_id = ? 
                  AND bi.status = 'active'
                  AND i.ingredient_status != 'archived'
                  ORDER BY i.ingredient_name";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$branch['branch_id']]);
        $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($ingredients)) {
            echo "No ingredients assigned to this branch.\n";
        } else {
            foreach ($ingredients as $ingredient) {
                echo "- " . $ingredient['ingredient_name'] . " (" . $ingredient['category_name'] . ") - " . $ingredient['quantity'] . " " . $ingredient['ingredient_unit'] . "\n";
            }
        }
        
        echo "Total ingredients: " . count($ingredients) . "\n\n";
    }
    
    echo "Branch-specific filtering test completed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
