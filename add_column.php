<?php
/**
 * Add company_description column to welcome_page_settings table
 * Run this file by accessing it in your browser: http://localhost:8000/add_column.php
 * OR run from command line: php add_column.php
 */

// Get database connection details from .env
$env_file = __DIR__ . '/.env';
$env_vars = parse_ini_file($env_file);

$host = $env_vars['DB_HOST'] ?? '127.0.0.1';
$database = $env_vars['DB_DATABASE'] ?? 'urea';
$username = $env_vars['DB_USERNAME'] ?? 'root';
$password = $env_vars['DB_PASSWORD'] ?? '';

// Connect to database
try {
    $mysqli = new mysqli($host, $username, $password, $database);
    
    if ($mysqli->connect_error) {
        die('Connection failed: ' . $mysqli->connect_error);
    }
    
    echo "✓ Connected to database<br>";
    
    // Check if column already exists
    $result = $mysqli->query("SHOW COLUMNS FROM welcome_page_settings LIKE 'company_description'");
    
    if ($result && $result->num_rows > 0) {
        echo "✓ Column 'company_description' already exists!<br>";
    } else {
        // Add the column
        $sql = "ALTER TABLE `welcome_page_settings` ADD COLUMN `company_description` LONGTEXT NULL AFTER `company_logo`";
        
        if ($mysqli->query($sql)) {
            echo "✓ Successfully added 'company_description' column!<br>";
            echo "✓ You can now use the Company Description field in the Welcome Page Settings<br>";
        } else {
            echo "✗ Error adding column: " . $mysqli->error . "<br>";
        }
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
}
?>

<hr>
<p><a href="http://localhost:8000/admin/welcome-page">Go back to Welcome Page Settings</a></p>
