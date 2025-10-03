<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$database = "cake_orders";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$success_message = "";
$error_message = "";

// Process form submission
if (isset($_POST["create"])) {
    // Sanitize input data
    $name = mysqli_real_escape_string($conn, trim($_POST["name"]));
    $telephone = mysqli_real_escape_string($conn, trim($_POST["phone"]));
    $email = mysqli_real_escape_string($conn, trim($_POST["email"]));
    $event = mysqli_real_escape_string($conn, trim($_POST["event"]));
    $size = mysqli_real_escape_string($conn, trim($_POST["size"]));
    $flavor = mysqli_real_escape_string($conn, trim($_POST["flavor"]));
    $date = mysqli_real_escape_string($conn, trim($_POST["date"]));
    $message = mysqli_real_escape_string($conn, trim($_POST["message"]));
    
    // New fields for enhanced order system
    $decorations = isset($_POST["decorations"]) ? $_POST["decorations"] : [];
    $decorations_string = implode(", ", array_map(function($item) use ($conn) {
        return mysqli_real_escape_string($conn, $item);
    }, $decorations));
    
    $delivery = mysqli_real_escape_string($conn, trim($_POST["delivery"] ?? ''));
    $address = mysqli_real_escape_string($conn, trim($_POST["address"] ?? ''));
    
    // Price fields
    $base_price = floatval($_POST["base_price"] ?? 0);
    $flavor_price = floatval($_POST["flavor_price"] ?? 0);
    $decoration_price = floatval($_POST["decoration_price"] ?? 0);
    $delivery_price = floatval($_POST["delivery_price"] ?? 0);
    $total_price = floatval($_POST["total_price"] ?? 0);
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($telephone)) {
        $errors[] = "Phone number is required";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($event)) {
        $errors[] = "Event type is required";
    }
    
    if (empty($size)) {
        $errors[] = "Cake size is required";
    }
    
    if (empty($flavor)) {
        $errors[] = "Flavor is required";
    }
    
    if (empty($date)) {
        $errors[] = "Date is required";
    } else {
        // Check if date is not in the past
        $selected_date = new DateTime($date);
        $today = new DateTime();
        
        if ($selected_date <= $today) {
            $errors[] = "Date must be in the future";
        }
    }
    
    if (empty($delivery)) {
        $errors[] = "Delivery option is required";
    }
    
    if ($total_price <= 0) {
        $errors[] = "Invalid pricing calculation";
    }
    
    // If no errors, insert into database
    if (empty($errors)) {
        // Create enhanced orders table if it doesn't exist
        $create_table = "CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            email VARCHAR(255) NOT NULL,
            event_type VARCHAR(100) NOT NULL,
            cake_size VARCHAR(50) NOT NULL,
            flavor VARCHAR(100) NOT NULL,
            decorations TEXT,
            delivery_option VARCHAR(100) NOT NULL,
            delivery_address TEXT,
            delivery_date DATE NOT NULL,
            special_message TEXT,
            base_price DECIMAL(10,2) DEFAULT 0,
            flavor_price DECIMAL(10,2) DEFAULT 0,
            decoration_price DECIMAL(10,2) DEFAULT 0,
            delivery_price DECIMAL(10,2) DEFAULT 0,
            total_price DECIMAL(10,2) NOT NULL,
            order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending'
        )";
        
        mysqli_query($conn, $create_table);
        
        // Insert order with all new fields
        $sql = "INSERT INTO orders (name, phone, email, event_type, cake_size, flavor, decorations, delivery_option, delivery_address, delivery_date, special_message, base_price, flavor_price, decoration_price, delivery_price, total_price) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssssddddd", $name, $telephone, $email, $event, $size, $flavor, $decorations_string, $delivery, $address, $date, $message, $base_price, $flavor_price, $decoration_price, $delivery_price, $total_price);
        
        if ($stmt->execute()) {
            $order_id = $conn->insert_id;
            $success_message = "Order #$order_id successfully created! Total Amount: $" . number_format($total_price, 2) . ". We will contact you soon to confirm the details.";
        } else {
            $error_message = "Error creating order. Please try again.";
        }
        
        $stmt->close();
    } else {
        $error_message = implode("<br>", $errors);
    }
}

// Handle contact form submission
if (isset($_POST["send_comment"])) {
    $fullname = mysqli_real_escape_string($conn, trim($_POST["fullname"]));
    $email = mysqli_real_escape_string($conn, trim($_POST["email"]));
    $message = mysqli_real_escape_string($conn, trim($_POST["message"]));
    
    // Create contacts table if it doesn't exist
    $create_contacts_table = "CREATE TABLE IF NOT EXISTS contacts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        contact_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    mysqli_query($conn, $create_contacts_table);
    
    // Insert contact
    $sql = "INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $fullname, $email, $message);
    
    if ($stmt->execute()) {
        $success_message = "Thank you for your message! We'll get back to you soon.";
    } else {
        $error_message = "Error sending message. Please try again.";
    }
    
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Cake Cravings</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .confirmation-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 2rem 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .confirmation-box {
            background: white;
            padding: 4rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 100%;
            margin: 2rem;
            text-align: center;
        }
        
        .success-icon {
            font-size: 6rem;
            color: #4CAF50;
            margin-bottom: 2rem;
        }
        
        .error-icon {
            font-size: 6rem;
            color: #f44336;
            margin-bottom: 2rem;
        }
        
        .confirmation-box h1 {
            font-size: 3rem;
            margin-bottom: 2rem;
            color: #1B1722;
        }
        
        .confirmation-box p {
            font-size: 1.6rem;
            color: #666;
            margin-bottom: 3rem;
            line-height: 1.6;
        }
        
        .action-buttons {
            display: flex;
            gap: 2rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-primary {
            background: #bc5957;
            color: white;
            padding: 1.5rem 3rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.6rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 1rem;
        }
        
        .btn-primary:hover {
            background: #a54846;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        
        .btn-secondary {
            background: transparent;
            color: #bc5957;
            border: 2px solid #bc5957;
            padding: 1.5rem 3rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.6rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 1rem;
        }
        
        .btn-secondary:hover {
            background: #bc5957;
            color: white;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .confirmation-box {
                padding: 2rem;
                margin: 1rem;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-primary, .btn-secondary {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="confirmation-box">
            <?php if (!empty($success_message)): ?>
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1>Order Confirmed!</h1>
                <p><?php echo $success_message; ?></p>
                <div class="action-buttons">
                    <a href="home.html" class="btn-primary">
                        <i class="fas fa-home"></i> Back to Home
                    </a>
                    <a href="form.html" class="btn-secondary">
                        <i class="fas fa-plus"></i> Place Another Order
                    </a>
                </div>
            <?php elseif (!empty($error_message)): ?>
                <div class="error-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h1>Oops! Something went wrong</h1>
                <p><?php echo $error_message; ?></p>
                <div class="action-buttons">
                    <a href="form.html" class="btn-primary">
                        <i class="fas fa-arrow-left"></i> Try Again
                    </a>
                    <a href="home.html" class="btn-secondary">
                        <i class="fas fa-home"></i> Back to Home
                    </a>
                </div>
            <?php else: ?>
                <div class="success-icon">
                    <i class="fas fa-birthday-cake"></i>
                </div>
                <h1>Welcome to Cake Cravings!</h1>
                <p>Thank you for visiting our order page. We're ready to create your perfect cake!</p>
                <div class="action-buttons">
                    <a href="form.html" class="btn-primary">
                        <i class="fas fa-plus"></i> Place Order
                    </a>
                    <a href="home.html" class="btn-secondary">
                        <i class="fas fa-home"></i> Back to Home
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>