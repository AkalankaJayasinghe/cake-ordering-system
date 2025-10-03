<?php
// Include centralized database configuration
require_once 'config.php';

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_order'])) {
    $orderData = [
        'name' => trim($_POST['customerName']),
        'email' => trim($_POST['customerEmail']),
        'phone' => trim($_POST['customerPhone']),
        'event' => $_POST['eventType'],
        'size' => $_POST['cakeSize'],
        'flavor' => $_POST['cakeFlavor'],
        'date' => $_POST['deliveryDate'],
        'amount' => floatval($_POST['totalAmount']),
        'message' => trim($_POST['specialMessage'] ?? '')
    ];
    
    // Validate required fields
    if (!empty($orderData['name']) && !empty($orderData['email']) && 
        !empty($orderData['phone']) && !empty($orderData['event']) && 
        !empty($orderData['size']) && !empty($orderData['flavor']) && 
        !empty($orderData['date']) && $orderData['amount'] > 0) {
        
        if (addOrder($orderData)) {
            $success_message = "Thank you! Your order has been submitted successfully. We will contact you soon.";
        } else {
            $error_message = "Sorry, there was an error processing your order. Please try again.";
        }
    } else {
        $error_message = "Please fill in all required fields correctly.";
    }
}

// Get categories for the form
$categories = getCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Cake - Cake Cravings</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .order-page {
            padding-top: 10rem;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .page-header {
            text-align: center;
            padding: 4rem 0;
            background: white;
            margin-bottom: 4rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .page-header h1 {
            font-size: 4rem;
            color: var(--text-dark);
            margin-bottom: 1rem;
            font-weight: 700;
        }
        
        .page-header p {
            font-size: 1.8rem;
            color: var(--text-light);
            max-width: 600px;
            margin: 0 auto;
        }

        .alert {
            padding: 1.5rem 2rem;
            margin: 2rem 7%;
            border-radius: 10px;
            font-size: 1.6rem;
            font-weight: 600;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .order-container {
            padding: 0 7%;
            margin-bottom: 5rem;
        }

        .order-form-section {
            background: white;
            padding: 4rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
        }

        .form-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .form-header h2 {
            font-size: 3rem;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }

        .form-header p {
            color: var(--text-light);
            font-size: 1.6rem;
        }

        .order-form {
            display: grid;
            gap: 2rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 1rem;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 1.6rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 1.5rem;
            border: 2px solid #eee;
            border-radius: 10px;
            font-size: 1.6rem;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .pricing-info {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 10px;
            margin: 2rem 0;
        }

        .pricing-info h3 {
            font-size: 2rem;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }

        .price-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }

        .price-item {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }

        .price-item .size {
            font-weight: 600;
            color: var(--primary-color);
        }

        .price-item .price {
            font-size: 1.4rem;
            color: var(--text-dark);
            margin-top: 0.5rem;
        }

        .total-display {
            background: var(--primary-color);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            font-size: 2rem;
            font-weight: 700;
            margin: 2rem 0;
        }

        .submit-btn {
            width: 100%;
            background: linear-gradient(45deg, var(--primary-color), #d16866);
            color: white;
            padding: 2rem;
            border: none;
            border-radius: 50px;
            font-size: 1.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s ease;
            margin-top: 2rem;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
        }

        .back-link {
            text-align: center;
            margin-top: 3rem;
        }
        
        .back-link a {
            display: inline-flex;
            align-items: center;
            gap: 1rem;
            background: var(--text-dark);
            color: white;
            padding: 1.5rem 3rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.6rem;
            transition: all 0.3s ease;
        }
        
        .back-link a:hover {
            background: var(--primary-color);
            transform: translateY(-3px);
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .order-form-section {
                margin: 0 2rem;
                padding: 2rem;
            }
            
            .alert {
                margin: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logoContent">
            <a href="home.html" class="logo" title="Cake Cravings Home">
                <img src="assets/images/logo-new.jpg" alt="Cake Cravings - Made with Love">
            </a>
            <h1 class="logoName">Cake Cravings</h1>
        </div>  

        <nav class="navbar">
            <a href="home.html">Home</a>
            <a href="pages/gallery.html">Gallery</a>
            <a href="category.html">Categories</a>
            <a href="review.php">Reviews</a>
            <a href="contact.html">Contact</a>
        </nav>
    </header>

    <!-- Order Page -->
    <section class="order-page">
        <div class="page-header">
            <h1><i class="fas fa-birthday-cake"></i> Order Your Cake</h1>
            <p>Create your perfect cake for any occasion - we'll make it special!</p>
        </div>

        <!-- Alert Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="order-container">
            <div class="order-form-section">
                <div class="form-header">
                    <h2>Place Your Order</h2>
                    <p>Fill in the details below and we'll create the perfect cake for you!</p>
                </div>

                <!-- Pricing Information -->
                <div class="pricing-info">
                    <h3><i class="fas fa-tags"></i> Pricing Guide</h3>
                    <div class="price-list">
                        <div class="price-item">
                            <div class="size">1kg Cake</div>
                            <div class="price">Rs. 1,500 - 3,500</div>
                        </div>
                        <div class="price-item">
                            <div class="size">2kg Cake</div>
                            <div class="price">Rs. 2,500 - 6,500</div>
                        </div>
                        <div class="price-item">
                            <div class="size">3kg Cake</div>
                            <div class="price">Rs. 3,500 - 9,500</div>
                        </div>
                    </div>
                    <p style="margin-top: 1rem; font-size: 1.4rem; color: #666;">
                        <i class="fas fa-info-circle"></i> Final price will be confirmed based on design complexity
                    </p>
                </div>
                
                <form class="order-form" method="POST" action="order.php">
                    <!-- Customer Information -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="customerName">Full Name *</label>
                            <input type="text" id="customerName" name="customerName" required 
                                   value="<?php echo isset($_POST['customerName']) ? htmlspecialchars($_POST['customerName']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="customerEmail">Email Address *</label>
                            <input type="email" id="customerEmail" name="customerEmail" required
                                   value="<?php echo isset($_POST['customerEmail']) ? htmlspecialchars($_POST['customerEmail']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="customerPhone">Phone Number *</label>
                            <input type="tel" id="customerPhone" name="customerPhone" required
                                   value="<?php echo isset($_POST['customerPhone']) ? htmlspecialchars($_POST['customerPhone']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="eventType">Event Type *</label>
                            <select id="eventType" name="eventType" required>
                                <option value="">Select Event Type</option>
                                <option value="birthday">Birthday</option>
                                <option value="wedding">Wedding</option>
                                <option value="anniversary">Anniversary</option>
                                <option value="party">Party</option>
                                <option value="corporate">Corporate Event</option>
                                <option value="custom">Custom Order</option>
                            </select>
                        </div>
                    </div>

                    <!-- Cake Details -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="cakeSize">Cake Size *</label>
                            <select id="cakeSize" name="cakeSize" required>
                                <option value="">Select Size</option>
                                <option value="1kg">1kg (6-8 people)</option>
                                <option value="2kg">2kg (12-15 people)</option>
                                <option value="3kg">3kg (18-20 people)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="cakeFlavor">Cake Flavor *</label>
                            <select id="cakeFlavor" name="cakeFlavor" required>
                                <option value="">Select Flavor</option>
                                <option value="vanilla">Vanilla</option>
                                <option value="chocolate">Chocolate</option>
                                <option value="strawberry">Strawberry</option>
                                <option value="red-velvet">Red Velvet</option>
                                <option value="butterscotch">Butterscotch</option>
                                <option value="black-forest">Black Forest</option>
                                <option value="pineapple">Pineapple</option>
                                <option value="custom">Custom Flavor</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="deliveryDate">Delivery Date *</label>
                            <input type="date" id="deliveryDate" name="deliveryDate" required min="<?php echo date('Y-m-d', strtotime('+2 days')); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="totalAmount">Estimated Amount (Rs.) *</label>
                            <input type="number" id="totalAmount" name="totalAmount" required min="1000" step="0.01" placeholder="2500.00">
                        </div>
                    </div>

                    <!-- Special Message -->
                    <div class="form-group">
                        <label for="specialMessage">Special Message/Instructions</label>
                        <textarea id="specialMessage" name="specialMessage" rows="4" 
                                  placeholder="Any special decorations, text on cake, dietary requirements, etc."><?php echo isset($_POST['specialMessage']) ? htmlspecialchars($_POST['specialMessage']) : ''; ?></textarea>
                    </div>

                    <div class="total-display" id="totalDisplay">
                        Total Amount: Rs. <span id="totalValue">0.00</span>
                    </div>
                    
                    <button type="submit" name="submit_order" class="submit-btn">
                        <i class="fas fa-shopping-cart"></i> Place Order
                    </button>
                </form>

                <div class="back-link">
                    <a href="home.html">
                        <i class="fas fa-arrow-left"></i>
                        Back to Home
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- JavaScript -->
    <script src="assets/js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const totalAmountInput = document.getElementById('totalAmount');
            const totalDisplay = document.getElementById('totalValue');
            
            // Update total display when amount changes
            totalAmountInput.addEventListener('input', function() {
                const amount = parseFloat(this.value) || 0;
                totalDisplay.textContent = amount.toFixed(2);
            });

            // Auto-hide alerts
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-20px)';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });

            // Form validation
            const form = document.querySelector('.order-form');
            form.addEventListener('submit', function(e) {
                const requiredFields = form.querySelectorAll('[required]');
                let isValid = true;

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.style.borderColor = '#e74c3c';
                    } else {
                        field.style.borderColor = '#eee';
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                    return false;
                }

                // Check delivery date is at least 2 days from now
                const deliveryDate = new Date(document.getElementById('deliveryDate').value);
                const minDate = new Date();
                minDate.setDate(minDate.getDate() + 2);

                if (deliveryDate < minDate) {
                    e.preventDefault();
                    alert('Please select a delivery date at least 2 days from today.');
                    return false;
                }
            });
        });
    </script>
</body>
</html>