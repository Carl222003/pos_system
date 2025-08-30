<?php
/**
 * Stock Requests Updates Database Setup Script
 * 
 * This script creates the complete database structure for the Stock Requests Updates system
 * including tables, indexes, foreign keys, views, stored procedures, and sample data.
 */

require_once 'db_connect.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Stock Requests Database Setup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: #8B4543; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .step { background: #f8f9fa; border-left: 4px solid #8B4543; padding: 15px; margin: 10px 0; border-radius: 0 5px 5px 0; }
        .success { border-left-color: #28a745; background: #d4edda; }
        .error { border-left-color: #dc3545; background: #f8d7da; }
        .warning { border-left-color: #ffc107; background: #fff3cd; }
        .info { border-left-color: #17a2b8; background: #d1ecf1; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; font-size: 12px; }
        .progress { background: #e9ecef; border-radius: 10px; height: 20px; margin: 10px 0; }
        .progress-bar { background: #8B4543; height: 100%; border-radius: 10px; transition: width 0.3s; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🔄 Stock Requests Updates Database Setup</h1>
            <p>Setting up the complete database structure for the Stock Requests Updates system</p>
        </div>";

try {
    // Test database connection
    echo "<div class='step info'>
        <h3>Step 1: Database Connection Test</h3>
        <p>Testing connection to database...</p>";
    
    $pdo->query("SELECT 1");
    echo "<p class='success'>✅ Database connection successful!</p>
    </div>";

    // Read the SQL file
    echo "<div class='step info'>
        <h3>Step 2: Reading SQL Setup File</h3>
        <p>Loading database structure from SQL file...</p>";
    
    $sqlFile = 'create_stock_requests_database.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file '$sqlFile' not found!");
    }
    
    $sqlContent = file_get_contents($sqlFile);
    if (empty($sqlContent)) {
        throw new Exception("SQL file is empty!");
    }
    
    echo "<p class='success'>✅ SQL file loaded successfully!</p>
    </div>";

    // Split SQL into individual statements
    echo "<div class='step info'>
        <h3>Step 3: Parsing SQL Statements</h3>
        <p>Parsing SQL statements...</p>";
    
    // Remove comments and split by semicolon
    $sqlStatements = array_filter(
        array_map('trim', 
            explode(';', 
                preg_replace('/--.*$/m', '', $sqlContent)
            )
        ),
        function($stmt) { return !empty($stmt); }
    );
    
    echo "<p class='success'>✅ Found " . count($sqlStatements) . " SQL statements to execute</p>
    </div>";

    // Execute SQL statements
    echo "<div class='step info'>
        <h3>Step 4: Executing Database Setup</h3>
        <div class='progress'>
            <div class='progress-bar' id='progressBar' style='width: 0%'></div>
        </div>
        <p id='progressText'>Starting execution...</p>
    </div>";

    $totalStatements = count($sqlStatements);
    $executedStatements = 0;
    $errors = [];
    $warnings = [];
    $successes = [];

    foreach ($sqlStatements as $index => $sql) {
        $executedStatements++;
        $progress = ($executedStatements / $totalStatements) * 100;
        
        echo "<script>
            document.getElementById('progressBar').style.width = '$progress%';
            document.getElementById('progressText').textContent = 'Executing statement $executedStatements of $totalStatements...';
        </script>";
        
        // Flush output
        if (ob_get_level()) ob_flush();
        flush();
        
        try {
            // Skip empty statements
            if (empty(trim($sql))) {
                continue;
            }
            
            // Execute the statement
            $result = $pdo->exec($sql);
            
            // Check if it's a CREATE, INSERT, or ALTER statement
            $sqlUpper = strtoupper(trim($sql));
            if (strpos($sqlUpper, 'CREATE') === 0) {
                $successes[] = "Created: " . substr($sql, 0, 50) . "...";
            } elseif (strpos($sqlUpper, 'INSERT') === 0) {
                $successes[] = "Inserted: " . substr($sql, 0, 50) . "...";
            } elseif (strpos($sqlUpper, 'ALTER') === 0) {
                $successes[] = "Modified: " . substr($sql, 0, 50) . "...";
            } elseif (strpos($sqlUpper, 'GRANT') === 0) {
                $warnings[] = "Skipped (permissions): " . substr($sql, 0, 50) . "...";
            } else {
                $successes[] = "Executed: " . substr($sql, 0, 50) . "...";
            }
            
        } catch (PDOException $e) {
            // Check if it's a "table already exists" error
            if (strpos($e->getMessage(), 'already exists') !== false) {
                $warnings[] = "Skipped (already exists): " . substr($sql, 0, 50) . "...";
            } else {
                $errors[] = "Error in statement " . ($index + 1) . ": " . $e->getMessage();
            }
        }
    }

    // Final progress update
    echo "<script>
        document.getElementById('progressBar').style.width = '100%';
        document.getElementById('progressText').textContent = 'Setup completed!';
    </script>";

    // Display results
    echo "<div class='step success'>
        <h3>Step 5: Setup Results</h3>
        <p><strong>Total statements processed:</strong> $totalStatements</p>
        <p><strong>Successfully executed:</strong> " . count($successes) . "</p>
        <p><strong>Warnings:</strong> " . count($warnings) . "</p>
        <p><strong>Errors:</strong> " . count($errors) . "</p>
    </div>";

    // Show successes
    if (!empty($successes)) {
        echo "<div class='step success'>
            <h3>✅ Successful Operations</h3>
            <ul>";
        foreach (array_slice($successes, 0, 10) as $success) {
            echo "<li>$success</li>";
        }
        if (count($successes) > 10) {
            echo "<li>... and " . (count($successes) - 10) . " more</li>";
        }
        echo "</ul></div>";
    }

    // Show warnings
    if (!empty($warnings)) {
        echo "<div class='step warning'>
            <h3>⚠️ Warnings</h3>
            <ul>";
        foreach ($warnings as $warning) {
            echo "<li>$warning</li>";
        }
        echo "</ul></div>";
    }

    // Show errors
    if (!empty($errors)) {
        echo "<div class='step error'>
            <h3>❌ Errors</h3>
            <ul>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul></div>";
    }

    // Verify setup
    echo "<div class='step info'>
        <h3>Step 6: Verification</h3>
        <p>Verifying database structure...</p>";

    $tables = ['ingredient_requests', 'stock_request_activity_log', 'stock_request_notifications'];
    $missingTables = [];

    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() == 0) {
                $missingTables[] = $table;
            }
        } catch (PDOException $e) {
            $missingTables[] = $table;
        }
    }

    if (empty($missingTables)) {
        echo "<p class='success'>✅ All required tables created successfully!</p>";
        
        // Check sample data
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM ingredient_requests");
        $count = $stmt->fetch()['count'];
        echo "<p class='success'>✅ Sample data inserted: $count records in ingredient_requests table</p>";
        
        // Check views
        $views = ['vw_stock_requests_summary', 'vw_pending_requests', 'vw_approved_pending_delivery'];
        $missingViews = [];
        
        foreach ($views as $view) {
            try {
                $stmt = $pdo->query("SHOW TABLES LIKE '$view'");
                if ($stmt->rowCount() == 0) {
                    $missingViews[] = $view;
                }
            } catch (PDOException $e) {
                $missingViews[] = $view;
            }
        }
        
        if (empty($missingViews)) {
            echo "<p class='success'>✅ All database views created successfully!</p>";
        } else {
            echo "<p class='warning'>⚠️ Missing views: " . implode(', ', $missingViews) . "</p>";
        }
        
    } else {
        echo "<p class='error'>❌ Missing tables: " . implode(', ', $missingTables) . "</p>";
    }

    echo "</div>";

    // Final status
    if (empty($errors) && empty($missingTables)) {
        echo "<div class='step success'>
            <h3>🎉 Setup Complete!</h3>
            <p>The Stock Requests Updates database has been successfully created with:</p>
            <ul>
                <li>✅ Main ingredient_requests table</li>
                <li>✅ Activity logging table</li>
                <li>✅ Notifications table</li>
                <li>✅ Foreign key relationships</li>
                <li>✅ Database indexes for performance</li>
                <li>✅ Sample data for testing</li>
                <li>✅ Database views for easy access</li>
                <li>✅ Stored procedures for common operations</li>
                <li>✅ Triggers for automatic logging</li>
            </ul>
            <p><strong>Next steps:</strong></p>
            <ul>
                <li>Update your PHP files to use the new database structure</li>
                <li>Test the stock request functionality</li>
                <li>Configure any additional permissions if needed</li>
            </ul>
        </div>";
    } else {
        echo "<div class='step error'>
            <h3>⚠️ Setup Completed with Issues</h3>
            <p>Some parts of the setup encountered issues. Please review the errors above and fix them manually.</p>
        </div>";
    }

} catch (Exception $e) {
    echo "<div class='step error'>
        <h3>❌ Setup Failed</h3>
        <p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
        <p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>
        <p><strong>Line:</strong> " . $e->getLine() . "</p>
    </div>";
}

echo "</div></body></html>";
?>
