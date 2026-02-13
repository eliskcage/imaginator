<?php
// Database credentials — load from secrets.php
require_once __DIR__.'/secrets.php';
$host = defined('SF_DB_HOST') ? SF_DB_HOST : 'YOUR_DB_HOST';
$user = defined('SF_DB_USER') ? SF_DB_USER : 'YOUR_DB_USER';
$pass = defined('SF_DB_PASS') ? SF_DB_PASS : 'YOUR_DB_PASS';

echo "<h1>Database Connection Test</h1>";
echo "<style>body{font-family:Arial;padding:20px;background:#000;color:#fff;} .pass{color:#0f0;} .fail{color:#f00;}</style>";

// Test database names
$databases_to_test = [
    'dbs14671918',
    'dbs14968010',
    'dbs14671918.SHORTFACTORY'
];

foreach ($databases_to_test as $db) {
    echo "<h2>Testing: $db</h2>";
    
    try {
        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        echo "<p class='pass'>✅ Connected to $db</p>";
        
        // Try to list tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($tables)) {
            echo "<p class='fail'>⚠️ No tables found in $db</p>";
        } else {
            echo "<p class='pass'>📋 Tables found in $db:</p>";
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li>$table</li>";
            }
            echo "</ul>";
            
            // Check if users table exists
            if (in_array('users', $tables)) {
                $stmt = $pdo->query("SELECT COUNT(*) FROM users");
                $count = $stmt->fetchColumn();
                echo "<p class='pass'>✅ 'users' table has $count rows</p>";
            }
        }
        
    } catch (PDOException $e) {
        echo "<p class='fail'>❌ Failed: " . $e->getMessage() . "</p>";
    }
    
    echo "<hr>";
}

echo "<h2>Your phpMyAdmin Info</h2>";
echo "<p>Check which database shows your tables in phpMyAdmin and use that name in config.php</p>";
?>


