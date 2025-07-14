<?php
require_once 'db_connect.php';

try {
    $sql = "UPDATE pos_branch SET operating_hours = '08:00 - 17:00'";
    $affected = $pdo->exec($sql);
    echo "Updated $affected branches with default operating hours.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
} 