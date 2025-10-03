<?php
/**
 * Debug version of review submission
 */

// Enable detailed error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Include database configuration
require_once 'config.php';

// Debug: Test database connection
echo "<h2>Database Debug Information</h2>";

try {
    $conn = getDatabaseConnection();
    if ($conn) {
        echo "<p style='color: green;'>✅ Database connection successful!</p>";
        
        // Test if comment table exists
        $result = $conn->query("SHOW TABLES LIKE 'comment'");
        if ($result && $result->num_rows > 0) {
            echo "<p style='color: green;'>✅ 'comment' table exists</p>";
            
            // Show table structure
            $structure = $conn->query("DESCRIBE comment");
            echo "<h3>Table Structure:</h3><pre>";
            while ($row = $structure->fetch_assoc()) {
                echo "Field: {$row['Field']}, Type: {$row['Type']}, Null: {$row['Null']}, Key: {$row['Key']}\n";
            }
            echo "</pre>";
            
            // Count existing reviews
            $count = $conn->query("SELECT COUNT(*) as count FROM comment");
            $countRow = $count->fetch_assoc();
            echo "<p>Current review count: {$countRow['count']}</p>";
            
        } else {
            echo "<p style='color: red;'>❌ 'comment' table does not exist!</p>";
            echo "<p>Please run the SQL setup file: cake_reviews_setup.sql</p>";
        }
        
        $conn->close();
    } else {
        echo "<p style='color: red;'>❌ Database connection failed!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

// If this is a form submission, test it
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Form Submission Debug</h2>";
    
    echo "<h3>POST Data Received:</h3><pre>";
    print_r($_POST);
    echo "</pre>";
    
    if (isset($_POST['submit_review'])) {
        $name = trim($_POST['reviewerName']);
        $email = trim($_POST['reviewerEmail']);
        $rating = intval($_POST['rating']);
        $comment = trim($_POST['reviewText']);
        
        echo "<h3>Processed Data:</h3>";
        echo "<p>Name: '$name'</p>";
        echo "<p>Email: '$email'</p>";
        echo "<p>Rating: $rating</p>";
        echo "<p>Comment: '$comment'</p>";
        
        // Validate
        if (empty($name) || empty($email) || $rating < 1 || $rating > 5 || empty($comment)) {
            echo "<p style='color: red;'>❌ Validation failed!</p>";
            echo "<p>Name empty: " . (empty($name) ? 'YES' : 'NO') . "</p>";
            echo "<p>Email empty: " . (empty($email) ? 'YES' : 'NO') . "</p>";
            echo "<p>Rating invalid: " . (($rating < 1 || $rating > 5) ? 'YES' : 'NO') . "</p>";
            echo "<p>Comment empty: " . (empty($comment) ? 'YES' : 'NO') . "</p>";
        } else {
            echo "<p style='color: green;'>✅ Validation passed!</p>";
            
            // Try to add review
            echo "<h3>Adding Review to Database...</h3>";
            $success = addReview($name, $email, $rating, $comment);
            
            if ($success) {
                echo "<p style='color: green;'>✅ Review added successfully!</p>";
            } else {
                echo "<p style='color: red;'>❌ Failed to add review!</p>";
                
                // Additional debug
                $conn = getDatabaseConnection();
                if ($conn) {
                    echo "<p>MySQL Error: " . $conn->error . "</p>";
                    $conn->close();
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Review System Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-container { max-width: 500px; margin: 20px 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { background: #bc5957; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #a04846; }
        .star-rating { display: flex; gap: 5px; }
        .star { font-size: 20px; color: #ddd; cursor: pointer; }
        .star.active { color: #ffc107; }
    </style>
</head>
<body>
    <h1>🎂 Review System Debug Page</h1>
    
    <div class="form-container">
        <h2>Test Review Submission</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="reviewerName">Your Name:</label>
                <input type="text" id="reviewerName" name="reviewerName" value="Test User" required>
            </div>
            
            <div class="form-group">
                <label for="reviewerEmail">Email Address:</label>
                <input type="email" id="reviewerEmail" name="reviewerEmail" value="test@example.com" required>
            </div>
            
            <div class="form-group">
                <label for="rating">Rating:</label>
                <select id="rating" name="rating" required>
                    <option value="">Select Rating</option>
                    <option value="1">1 Star</option>
                    <option value="2">2 Stars</option>
                    <option value="3">3 Stars</option>
                    <option value="4">4 Stars</option>
                    <option value="5" selected>5 Stars</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="reviewText">Your Review:</label>
                <textarea id="reviewText" name="reviewText" rows="4" required>This is a test review to debug the system. The cake was absolutely delicious!</textarea>
            </div>
            
            <button type="submit" name="submit_review">Submit Test Review</button>
        </form>
    </div>
    
    <hr>
    
    <h2>Quick Links</h2>
    <p><a href="test_database.php">Database Test Page</a></p>
    <p><a href="pages/reviews.html">Main Reviews Page</a></p>
    <p><a href="review.php">Original Review Page</a></p>
    <p><a href="api_test.html">API Test Page</a></p>
</body>
</html>