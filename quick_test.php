<?php
require_once 'config.php';

// Test 1: Check if database exists
try {
    $conn = getDatabaseConnection();
    if ($conn) {
        echo "✅ Database connection: SUCCESS\n";
        
        // Test 2: Check if comment table exists
        $result = $conn->query("SHOW TABLES LIKE 'comment'");
        if ($result && $result->num_rows > 0) {
            echo "✅ Comment table: EXISTS\n";
            
            // Test 3: Count existing reviews
            $count = $conn->query("SELECT COUNT(*) as count FROM comment");
            $row = $count->fetch_assoc();
            echo "📊 Current reviews: " . $row['count'] . "\n";
            
            // Test 4: Try inserting a test review
            $testResult = addReview("CLI Test User", "test@cli.com", 5, "Test review from CLI");
            if ($testResult) {
                echo "✅ Insert test: SUCCESS\n";
            } else {
                echo "❌ Insert test: FAILED\n";
                echo "Error: " . $conn->error . "\n";
            }
            
        } else {
            echo "❌ Comment table: NOT FOUND\n";
            echo "Run this SQL to create the database:\n";
            echo "mysql -u root -p < cake_reviews_setup.sql\n";
        }
        
        $conn->close();
    } else {
        echo "❌ Database connection: FAILED\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>