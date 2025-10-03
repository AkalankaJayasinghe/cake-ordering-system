<?php
/**
 * 🎂 Cake Cravings Database Configuration
 * =====================================
 * Centralized database connection file
 * Compatible with XAMPP/localhost setup
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'cake');
define('DB_CHARSET', 'utf8mb4');

/**
 * Create database connection
 * @return mysqli|false Database connection or false on failure
 */
function getDatabaseConnection() {
    // Create connection
    $connection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    // Check connection
    if ($connection->connect_error) {
        error_log("Database connection failed: " . $connection->connect_error);
        return false;
    }
    
    // Set charset
    $connection->set_charset(DB_CHARSET);
    
    return $connection;
}

/**
 * Execute a prepared statement safely
 * @param string $query SQL query with placeholders
 * @param array $params Parameters for the query
 * @param string $types Parameter types (s=string, i=integer, d=double, b=blob)
 * @return array|false Result array or false on failure
 */
function executeQuery($query, $params = [], $types = '') {
    $conn = getDatabaseConnection();
    if (!$conn) {
        return false;
    }
    
    try {
        if (empty($params)) {
            // Simple query without parameters
            $result = $conn->query($query);
            if ($result === false) {
                throw new Exception("Query failed: " . $conn->error);
            }
            
            // If it's a SELECT query, fetch all results
            if (stripos($query, 'SELECT') === 0) {
                $data = [];
                while ($row = $result->fetch_assoc()) {
                    $data[] = $row;
                }
                return $data;
            }
            
            return true;
        } else {
            // Prepared statement
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            if (!empty($types) && !empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            // If it's a SELECT query, fetch all results
            if (stripos($query, 'SELECT') === 0) {
                $result = $stmt->get_result();
                $data = [];
                while ($row = $result->fetch_assoc()) {
                    $data[] = $row;
                }
                return $data;
            }
            
            return true;
        }
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        return false;
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        $conn->close();
    }
}

/**
 * Get review statistics
 * @return array|false Statistics array or false on failure
 */
function getReviewStats() {
    $query = "SELECT 
                COUNT(*) as total_reviews,
                AVG(rate) as average_rating,
                SUM(CASE WHEN rate = 5 THEN 1 ELSE 0 END) as five_star_count,
                SUM(CASE WHEN rate = 4 THEN 1 ELSE 0 END) as four_star_count,
                SUM(CASE WHEN rate = 3 THEN 1 ELSE 0 END) as three_star_count,
                SUM(CASE WHEN rate = 2 THEN 1 ELSE 0 END) as two_star_count,
                SUM(CASE WHEN rate = 1 THEN 1 ELSE 0 END) as one_star_count,
                ROUND((SUM(CASE WHEN rate >= 4 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as satisfaction_percentage
              FROM comment 
              WHERE status = 'approved'";
    $result = executeQuery($query);
    return $result ? $result[0] : false;
}

/**
 * Get recent reviews
 * @param int $limit Number of reviews to fetch
 * @return array|false Reviews array or false on failure
 */
function getRecentReviews($limit = 10) {
    $query = "SELECT id, name, email, rate, msg, date_added 
              FROM comment 
              WHERE status = 'approved' 
              ORDER BY date_added DESC 
              LIMIT ?";
    return executeQuery($query, [$limit], 'i');
}

/**
 * Add new review
 * @param string $name Customer name
 * @param string $email Customer email
 * @param int $rating Rating (1-5)
 * @param string $message Review message
 * @return bool True on success, false on failure
 */
function addReview($name, $email, $rating, $message) {
    $query = "INSERT INTO comment (name, email, rate, msg) VALUES (?, ?, ?, ?)";
    return executeQuery($query, [$name, $email, $rating, $message], 'ssis');
}

/**
 * Add new order
 * @param array $orderData Order information
 * @return bool True on success, false on failure
 */
function addOrder($orderData) {
    $orderId = 'ORD' . str_pad(time() % 100000, 5, '0', STR_PAD_LEFT);
    
    $query = "INSERT INTO orders (order_id, customer_name, customer_email, customer_phone, 
              event_type, cake_size, cake_flavor, delivery_date, total_amount, special_message) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    return executeQuery($query, [
        $orderId,
        $orderData['name'],
        $orderData['email'],
        $orderData['phone'],
        $orderData['event'],
        $orderData['size'],
        $orderData['flavor'],
        $orderData['date'],
        $orderData['amount'],
        $orderData['message'] ?? ''
    ], 'ssssssssds');
}

/**
 * Get order summary statistics
 * @return array|false Order summary or false on failure
 */
function getOrderSummary() {
    $query = "SELECT * FROM order_summary";
    $result = executeQuery($query);
    return $result ? $result[0] : false;
}

/**
 * Get categories
 * @return array|false Categories array or false on failure
 */
function getCategories() {
    $query = "SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order";
    return executeQuery($query);
}

/**
 * Get products by category
 * @param int $categoryId Category ID
 * @return array|false Products array or false on failure
 */
function getProductsByCategory($categoryId) {
    $query = "SELECT * FROM products WHERE category_id = ? AND is_active = 1";
    return executeQuery($query, [$categoryId], 'i');
}

/**
 * Add contact message
 * @param string $name Customer name
 * @param string $email Customer email
 * @param string $phone Customer phone
 * @param string $subject Message subject
 * @param string $message Message content
 * @return bool True on success, false on failure
 */
function addContactMessage($name, $email, $phone, $subject, $message) {
    $query = "INSERT INTO contact_messages (name, email, phone, subject, message) 
              VALUES (?, ?, ?, ?, ?)";
    return executeQuery($query, [$name, $email, $phone, $subject, $message], 'sssss');
}

/**
 * Test database connection
 * @return array Connection status and basic info
 */
function testConnection() {
    $conn = getDatabaseConnection();
    if (!$conn) {
        return [
            'status' => 'error',
            'message' => 'Failed to connect to database',
            'details' => []
        ];
    }
    
    try {
        // Get basic database info
        $reviewCount = $conn->query("SELECT COUNT(*) as count FROM comment")->fetch_assoc()['count'];
        $orderCount = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
        $categoryCount = $conn->query("SELECT COUNT(*) as count FROM categories")->fetch_assoc()['count'];
        
        $conn->close();
        
        return [
            'status' => 'success',
            'message' => 'Database connection successful',
            'details' => [
                'reviews' => $reviewCount,
                'orders' => $orderCount,
                'categories' => $categoryCount,
                'server_info' => $conn->server_info ?? 'Unknown'
            ]
        ];
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Database connection failed',
            'details' => ['error' => $e->getMessage()]
        ];
    }
}

// Auto-test connection in development (comment out in production)
if (isset($_GET['test_db'])) {
    header('Content-Type: application/json');
    echo json_encode(testConnection(), JSON_PRETTY_PRINT);
    exit;
}
?>