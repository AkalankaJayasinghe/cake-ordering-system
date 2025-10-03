<?php
/**
 * 🎂 Database Connection Test for Cake Cravings
 * ============================================
 * Test file to verify database connectivity and data
 */

// Include the configuration
require_once 'config.php';

// Set content type to HTML for better display
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Test - Cake Cravings</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 2rem; 
            background: #f5f5f5; 
        }
        .container { 
            max-width: 800px; 
            margin: 0 auto; 
            background: white; 
            padding: 2rem; 
            border-radius: 10px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .success { 
            color: #28a745; 
            background: #d4edda; 
            padding: 1rem; 
            border-radius: 5px; 
            margin: 1rem 0;
        }
        .error { 
            color: #dc3545; 
            background: #f8d7da; 
            padding: 1rem; 
            border-radius: 5px; 
            margin: 1rem 0;
        }
        .info { 
            color: #0c5460; 
            background: #d1ecf1; 
            padding: 1rem; 
            border-radius: 5px; 
            margin: 1rem 0;
        }
        .reviews-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }
        .review-card {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #bc5957;
        }
        .stats {
            background: #e9ecef;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1rem 0;
        }
        h1, h2 { color: #bc5957; }
        .test-button {
            background: #bc5957;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
            text-decoration: none;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎂 Cake Cravings Database Test</h1>
        
        <?php
        echo "<div class='info'><strong>Test Time:</strong> " . date('Y-m-d H:i:s') . "</div>";
        
        // Test 1: Database Connection
        echo "<h2>1. Database Connection Test</h2>";
        $connection = getDatabaseConnection();
        if ($connection) {
            echo "<div class='success'>✅ Database connection successful!</div>";
            echo "<div class='info'>Connected to: " . DB_HOST . "/" . DB_NAME . "</div>";
            $connection->close();
        } else {
            echo "<div class='error'>❌ Database connection failed!</div>";
            exit;
        }
        
        // Test 2: Check if tables exist
        echo "<h2>2. Table Structure Test</h2>";
        $tables = ['comment', 'categories', 'orders'];
        foreach ($tables as $table) {
            $result = executeQuery("SHOW TABLES LIKE '$table'");
            if ($result && count($result) > 0) {
                echo "<div class='success'>✅ Table '$table' exists</div>";
            } else {
                echo "<div class='error'>❌ Table '$table' not found</div>";
            }
        }
        
        // Test 3: Review Statistics
        echo "<h2>3. Review Statistics</h2>";
        $stats = getReviewStats();
        if ($stats) {
            echo "<div class='stats'>";
            echo "<strong>Total Reviews:</strong> " . $stats['total_reviews'] . "<br>";
            echo "<strong>Average Rating:</strong> " . round($stats['average_rating'], 1) . "/5<br>";
            echo "<strong>Five Star Reviews:</strong> " . $stats['five_star_count'] . "<br>";
            echo "<strong>Satisfaction Rate:</strong> " . $stats['satisfaction_percentage'] . "%<br>";
            echo "</div>";
        } else {
            echo "<div class='error'>❌ Failed to retrieve statistics</div>";
        }
        
        // Test 4: Recent Reviews
        echo "<h2>4. Recent Reviews Test</h2>";
        $reviews = getRecentReviews(6);
        if ($reviews && count($reviews) > 0) {
            echo "<div class='success'>✅ Successfully retrieved " . count($reviews) . " reviews</div>";
            echo "<div class='reviews-grid'>";
            foreach ($reviews as $review) {
                $stars = str_repeat('⭐', $review['rate']);
                echo "<div class='review-card'>";
                echo "<strong>" . htmlspecialchars($review['name']) . "</strong> $stars<br>";
                echo "<small>" . htmlspecialchars($review['email']) . "</small><br>";
                echo "<p>" . htmlspecialchars(substr($review['msg'], 0, 100)) . "...</p>";
                echo "<small>Added: " . $review['date_added'] . "</small>";
                echo "</div>";
            }
            echo "</div>";
        } else {
            echo "<div class='error'>❌ No reviews found or failed to retrieve reviews</div>";
        }
        
        // Test 5: API Endpoints
        echo "<h2>5. API Endpoint Tests</h2>";
        $api_url = "http://localhost/cake-order-site-main/review.php?api=true";
        echo "<div class='info'>API URL: $api_url</div>";
        echo "<a href='$api_url' class='test-button' target='_blank'>Test GET API</a>";
        echo "<a href='review.php' class='test-button' target='_blank'>View Full Review Page</a>";
        echo "<a href='pages/reviews.html' class='test-button' target='_blank'>View Reviews HTML</a>";
        
        // Test 6: Sample Data Insert Test
        echo "<h2>6. Sample Data Insert Test</h2>";
        $test_name = "Test User " . date('His');
        $test_email = "test" . date('His') . "@example.com";
        $test_rating = 5;
        $test_message = "This is a test review created at " . date('Y-m-d H:i:s');
        
        $insert_result = addReview($test_name, $test_email, $test_rating, $test_message);
        if ($insert_result) {
            echo "<div class='success'>✅ Successfully inserted test review</div>";
            echo "<div class='info'>Test Data: $test_name, Rating: $test_rating</div>";
        } else {
            echo "<div class='error'>❌ Failed to insert test review</div>";
        }
        
        echo "<h2>Setup Instructions</h2>";
        echo "<div class='info'>";
        echo "<strong>To complete the setup:</strong><br>";
        echo "1. Make sure XAMPP is running (Apache + MySQL)<br>";
        echo "2. Import the SQL file: <code>cake_reviews_setup.sql</code><br>";
        echo "3. Visit: <a href='pages/reviews.html' target='_blank'>pages/reviews.html</a><br>";
        echo "4. Test form submission and review display<br>";
        echo "</div>";
        
        ?>
    </div>
    
    <script>
        // Auto-refresh every 30 seconds for testing
        setTimeout(() => {
            document.body.style.opacity = '0.7';
            document.body.insertAdjacentHTML('beforeend', '<div style="position: fixed; top: 10px; right: 10px; background: #bc5957; color: white; padding: 10px; border-radius: 5px;">Auto-refreshing...</div>');
            setTimeout(() => window.location.reload(), 2000);
        }, 30000);
    </script>
</body>
</html>