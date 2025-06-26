<?php
/**
 * Debug Test - Find what's causing the white page
 * Upload this as debug-test.php and visit it
 */

// Force error display
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h1>Debug Test - Ted Rubin Consulting</h1>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>Timestamp: " . date('Y-m-d H:i:s') . "</p>";

// Test 1: Check files exist
echo "<h3>1. File Existence Check</h3>";
$files = [
    'config/config.php',
    'config/database.php', 
    'config/geo-restrictions.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file - EXISTS<br>";
    } else {
        echo "❌ $file - MISSING<br>";
    }
}

// Test 2: Try loading geo-restrictions first (simpler file)
echo "<h3>2. Testing Geo Restrictions</h3>";
try {
    define('SKIP_GEO_CHECK', true); // Skip the automatic check
    require_once 'config/geo-restrictions.php';
    echo "✅ geo-restrictions.php loaded successfully<br>";
} catch (Exception $e) {
    echo "❌ geo-restrictions.php ERROR: " . $e->getMessage() . "<br>";
} catch (ParseError $e) {
    echo "❌ geo-restrictions.php SYNTAX ERROR: " . $e->getMessage() . "<br>";
} catch (Error $e) {
    echo "❌ geo-restrictions.php FATAL ERROR: " . $e->getMessage() . "<br>";
}

// Test 3: Try loading database config
echo "<h3>3. Testing Database Config</h3>";
try {
    require_once 'config/database.php';
    echo "✅ database.php loaded successfully<br>";
} catch (Exception $e) {
    echo "❌ database.php ERROR: " . $e->getMessage() . "<br>";
} catch (ParseError $e) {
    echo "❌ database.php SYNTAX ERROR: " . $e->getMessage() . "<br>";
} catch (Error $e) {
    echo "❌ database.php FATAL ERROR: " . $e->getMessage() . "<br>";
}

// Test 4: Try loading main config
echo "<h3>4. Testing Main Config</h3>";
try {
    require_once 'config/config.php';
    echo "✅ config.php loaded successfully<br>";
} catch (Exception $e) {
    echo "❌ config.php ERROR: " . $e->getMessage() . "<br>";
} catch (ParseError $e) {
    echo "❌ config.php SYNTAX ERROR: " . $e->getMessage() . "<br>";
} catch (Error $e) {
    echo "❌ config.php FATAL ERROR: " . $e->getMessage() . "<br>";
}

// Test 5: Try database connection
echo "<h3>5. Testing Database Connection</h3>";
if (class_exists('Database')) {
    try {
        $db = Database::getInstance();
        echo "✅ Database connection successful<br>";
    } catch (Exception $e) {
        echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Database class not found<br>";
}

// Test 6: Check constants
echo "<h3>6. Configuration Constants</h3>";
$constants = ['DB_HOST', 'DB_NAME', 'DB_USER', 'SITE_URL'];
foreach ($constants as $const) {
    if (defined($const)) {
        $value = constant($const);
        // Hide password for security
        if ($const === 'DB_PASS') {
            $value = str_repeat('*', strlen($value));
        }
        echo "✅ $const = $value<br>";
    } else {
        echo "❌ $const - NOT DEFINED<br>";
    }
}

echo "<h3>7. Memory and Server Info</h3>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . "<br>";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";

echo "<p><strong>Debug test completed!</strong></p>";
echo "<p><em>Delete this file after debugging.</em></p>";
?>