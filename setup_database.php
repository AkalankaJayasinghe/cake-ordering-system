<?php
/**
 * Database Setup Script
 * Manually creates the required tables for the review system
 */

// Include database configuration
require_once 'config.php';

echo "<h1>Database Setup</h1>";

try {
    // Connect to MySQL (without specifying database)
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "<p>✅ Connected to MySQL server</p>";
    
    // Create database if not exists
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
    if ($conn->query($sql)) {
        echo "<p>✅ Database '" . DB_NAME . "' created/verified</p>";
    } else {
        echo "<p>❌ Error creating database: " . $conn->error . "</p>";
    }
    
    // Select the database
    $conn->select_db(DB_NAME);
    
    // Create comment table
    $createCommentTable = "
    CREATE TABLE IF NOT EXISTS comment (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL,
        rate INT NOT NULL CHECK (rate >= 1 AND rate <= 5),
        msg TEXT NOT NULL,
        date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
        helpful_count INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_date_added (date_added),
        INDEX idx_rate (rate),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($createCommentTable)) {
        echo "<p>✅ Comment table created/verified</p>";
    } else {
        echo "<p>❌ Error creating comment table: " . $conn->error . "</p>";
    }
    
    // Create categories table
    $createCategoriesTable = "
    CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        image_url VARCHAR(255),
        is_active BOOLEAN DEFAULT TRUE,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($createCategoriesTable)) {
        echo "<p>✅ Categories table created/verified</p>";
    } else {
        echo "<p>❌ Error creating categories table: " . $conn->error . "</p>";
    }
    
    // Create orders table
    $createOrdersTable = "
    CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(100) NOT NULL,
        customer_email VARCHAR(150) NOT NULL,
        customer_phone VARCHAR(20),
        cake_type VARCHAR(100),
        cake_size VARCHAR(50),
        flavor VARCHAR(100),
        custom_message TEXT,
        delivery_date DATE,
        delivery_address TEXT,
        special_instructions TEXT,
        total_amount DECIMAL(10, 2),
        order_status ENUM('pending', 'confirmed', 'in_progress', 'ready', 'delivered', 'cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($createOrdersTable)) {
        echo "<p>✅ Orders table created/verified</p>";
    } else {
        echo "<p>❌ Error creating orders table: " . $conn->error . "</p>";
    }
    
    // Check if there are any existing reviews
    $result = $conn->query("SELECT COUNT(*) as count FROM comment");
    $row = $result->fetch_assoc();
    $reviewCount = $row['count'];
    
    echo "<p>📊 Current review count: $reviewCount</p>";
    
    // Insert sample reviews if table is empty
    if ($reviewCount == 0) {
        echo "<p>🔄 Adding sample reviews...</p>";
        
        $sampleReviews = [
            ['Sarah Johnson', 'sarah.j@email.com', 5, 'Absolutely stunning wedding cake! The design was exactly what I dreamed of, and the taste was incredible. Our guests couldn\'t stop talking about how delicious it was. The team was professional and delivered on time. Highly recommend!'],
            ['Michael Chen', 'michael.chen@email.com', 5, 'Ordered a custom birthday cake for my daughter\'s 10th birthday. The unicorn design was magical and she was thrilled! The cake was moist, flavorful, and looked exactly like the picture. Will definitely order again!'],
            ['Emily Rodriguez', 'emily.r@email.com', 5, 'The anniversary cake was perfect for our 25th anniversary celebration. Beautiful design, amazing taste, and excellent customer service. They even helped us with last-minute changes. Thank you for making our day special!'],
            ['David Thompson', 'david.t@email.com', 4, 'Great party cake for our office celebration! The design was fun and creative, and everyone loved the chocolate flavor. The only minor issue was the delivery time, but overall very satisfied with the quality.'],
            ['Lisa Park', 'lisa.park@email.com', 5, 'This is my third order from Cake Cravings and they never disappoint! The quality is consistently excellent, the designs are beautiful, and the customer service is outstanding. My go-to cake shop!']
        ];
        
        foreach ($sampleReviews as $review) {
            $stmt = $conn->prepare("INSERT INTO comment (name, email, rate, msg) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssis", $review[0], $review[1], $review[2], $review[3]);
            if ($stmt->execute()) {
                echo "<p>✅ Added review from {$review[0]}</p>";
            } else {
                echo "<p>❌ Failed to add review from {$review[0]}: " . $stmt->error . "</p>";
            }
            $stmt->close();
        }
    }
    
    // Create the review statistics view
    $createView = "
    CREATE OR REPLACE VIEW review_stats AS
    SELECT COUNT(*) as total_reviews,
        AVG(rate) as average_rating,
        SUM(CASE WHEN rate = 5 THEN 1 ELSE 0 END) as five_star_count,
        SUM(CASE WHEN rate = 4 THEN 1 ELSE 0 END) as four_star_count,
        SUM(CASE WHEN rate = 3 THEN 1 ELSE 0 END) as three_star_count,
        SUM(CASE WHEN rate = 2 THEN 1 ELSE 0 END) as two_star_count,
        SUM(CASE WHEN rate = 1 THEN 1 ELSE 0 END) as one_star_count,
        ROUND((SUM(CASE WHEN rate >= 4 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as satisfaction_percentage
    FROM comment
    WHERE status = 'approved'";
    
    if ($conn->query($createView)) {
        echo "<p>✅ Review statistics view created</p>";
    } else {
        echo "<p>❌ Error creating view: " . $conn->error . "</p>";
    }
    
    // Final test
    echo "<h2>Final Tests</h2>";
    
    // Test review insertion
    $testName = "Setup Test User";
    $testEmail = "setup@test.com";
    $testRating = 5;
    $testMessage = "This is a test review created during database setup.";
    
    $stmt = $conn->prepare("INSERT INTO comment (name, email, rate, msg) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $testName, $testEmail, $testRating, $testMessage);
    
    if ($stmt->execute()) {
        echo "<p>✅ Test review insertion: SUCCESS</p>";
    } else {
        echo "<p>❌ Test review insertion: FAILED - " . $stmt->error . "</p>";
    }
    $stmt->close();
    
    // Test statistics
    $statsResult = $conn->query("SELECT * FROM review_stats");
    if ($statsResult) {
        $stats = $statsResult->fetch_assoc();
        echo "<p>✅ Statistics test: SUCCESS</p>";
        echo "<p>📊 Total reviews: {$stats['total_reviews']}</p>";
        echo "<p>📊 Average rating: " . number_format($stats['average_rating'], 1) . "/5</p>";
    } else {
        echo "<p>❌ Statistics test: FAILED</p>";
    }
    
    $conn->close();
    
    echo "<h2>🎉 Database Setup Complete!</h2>";
    echo "<p><a href='debug_review.php'>Test Review Form</a></p>";
    echo "<p><a href='pages/reviews.html'>Go to Reviews Page</a></p>";
    echo "<p><a href='test_database.php'>Database Test Page</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Fatal error: " . $e->getMessage() . "</p>";
}
?>