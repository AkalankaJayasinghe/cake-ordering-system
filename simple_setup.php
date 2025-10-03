<?php
/**
 * Simple Database Setup for MySQL 5.5
 */

// Include database configuration
require_once 'config.php';

echo "Database Setup for MySQL 5.5\n";
echo "============================\n\n";

try {
    // Connect to MySQL server (without database)
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "✅ Connected to MySQL server\n";
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
    if ($conn->query($sql)) {
        echo "✅ Database '" . DB_NAME . "' created\n";
    } else {
        echo "❌ Error creating database: " . $conn->error . "\n";
        exit;
    }
    
    // Select database
    $conn->select_db(DB_NAME);
    echo "✅ Selected database\n";
    
    // Drop existing tables if they exist (for clean setup)
    $conn->query("DROP TABLE IF EXISTS comment");
    $conn->query("DROP VIEW IF EXISTS review_stats");
    echo "🔄 Cleaned old tables\n";
    
    // Create comment table (simplified for MySQL 5.5)
    $createTable = "
    CREATE TABLE comment (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL,
        rate INT NOT NULL,
        msg TEXT NOT NULL,
        date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
        helpful_count INT DEFAULT 0
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
    
    if ($conn->query($createTable)) {
        echo "✅ Comment table created\n";
    } else {
        echo "❌ Error creating table: " . $conn->error . "\n";
        exit;
    }
    
    // Insert sample data
    echo "🔄 Adding sample reviews...\n";
    
    $reviews = [
        ["Sarah Johnson", "sarah.j@email.com", 5, "Absolutely stunning wedding cake! The design was exactly what I dreamed of, and the taste was incredible."],
        ["Michael Chen", "michael.chen@email.com", 5, "Ordered a custom birthday cake for my daughter. The unicorn design was magical and she was thrilled!"],
        ["Emily Rodriguez", "emily.r@email.com", 5, "The anniversary cake was perfect for our celebration. Beautiful design, amazing taste!"],
        ["David Thompson", "david.t@email.com", 4, "Great party cake for our office celebration! The design was fun and creative."],
        ["Lisa Park", "lisa.park@email.com", 5, "This is my third order from Cake Cravings and they never disappoint!"],
        ["Robert Wilson", "robert.w@email.com", 5, "Exceptional wedding cake that exceeded all our expectations!"],
        ["Jessica Adams", "jessica.a@email.com", 5, "Ordered cupcakes for my baby shower and they were absolutely perfect!"],
        ["Mark Davis", "mark.davis@email.com", 4, "Good quality graduation cake with nice design. The chocolate ganache was rich and delicious."]
    ];
    
    foreach ($reviews as $review) {
        $name = $conn->real_escape_string($review[0]);
        $email = $conn->real_escape_string($review[1]);
        $rate = $review[2];
        $msg = $conn->real_escape_string($review[3]);
        
        $sql = "INSERT INTO comment (name, email, rate, msg) VALUES ('$name', '$email', $rate, '$msg')";
        if ($conn->query($sql)) {
            echo "  ✅ Added review from $name\n";
        } else {
            echo "  ❌ Failed to add review from $name: " . $conn->error . "\n";
        }
    }
    
    // Test the table
    $result = $conn->query("SELECT COUNT(*) as count FROM comment");
    $row = $result->fetch_assoc();
    echo "\n📊 Total reviews in database: " . $row['count'] . "\n";
    
    // Test inserting a new review
    echo "\n🔄 Testing review insertion...\n";
    $testResult = $conn->query("INSERT INTO comment (name, email, rate, msg) VALUES ('Test User', 'test@example.com', 5, 'This is a test review.')");
    
    if ($testResult) {
        echo "✅ Test insertion: SUCCESS\n";
        echo "✅ Database setup complete!\n\n";
        echo "You can now:\n";
        echo "1. Visit: debug_review.php (to test form)\n";
        echo "2. Visit: pages/reviews.html (main page)\n";
        echo "3. Visit: test_database.php (detailed tests)\n";
    } else {
        echo "❌ Test insertion failed: " . $conn->error . "\n";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
}
?>