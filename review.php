<?php
/**
 * 🎂 Cake Cravings Review System
 * =============================
 * Handles review submissions and management with enhanced API capabilities
 */

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include centralized database configuration
require_once 'config.php';

/**
 * Sanitize and validate input data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email address
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Check for duplicate reviews (same name and email within 24 hours)
 */
function checkDuplicateReview($name, $email) {
    $duplicateCheck = executeQuery(
        "SELECT id FROM comment WHERE name = ? AND email = ? AND date_added > DATE_SUB(NOW(), INTERVAL 24 HOUR)",
        [$name, $email],
        'ss'
    );
    return $duplicateCheck && count($duplicateCheck) > 0;
}

// Handle AJAX request for API functionality
if (isset($_GET['api']) && $_GET['api'] === 'true') {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    
    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get POST data
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }
        
        // Validate required fields
        $requiredFields = ['reviewerName', 'reviewerEmail', 'rating', 'reviewText'];
        foreach ($requiredFields as $field) {
            if (empty($input[$field])) {
                echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
                exit();
            }
        }
        
        // Sanitize input
        $name = sanitizeInput($input['reviewerName']);
        $email = sanitizeInput($input['reviewerEmail']);
        $rating = (int) $input['rating'];
        $reviewText = sanitizeInput($input['reviewText']);
        
        // Validate data
        if (strlen($name) < 2 || strlen($name) > 100) {
            echo json_encode(['success' => false, 'message' => 'Name must be between 2 and 100 characters.']);
            exit();
        }
        
        if (!isValidEmail($email)) {
            echo json_encode(['success' => false, 'message' => 'Please provide a valid email address.']);
            exit();
        }
        
        if ($rating < 1 || $rating > 5) {
            echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5.']);
            exit();
        }
        
        if (strlen($reviewText) < 10 || strlen($reviewText) > 1000) {
            echo json_encode(['success' => false, 'message' => 'Review text must be between 10 and 1000 characters.']);
            exit();
        }
        
        // Check for duplicate reviews
        if (checkDuplicateReview($name, $email)) {
            echo json_encode(['success' => false, 'message' => 'You have already submitted a review recently. Please wait 24 hours before submitting another review.']);
            exit();
        }
        
        // Insert review into database
        $success = addReview($name, $email, $rating, $reviewText);
        
        if ($success) {
            error_log("New review submitted via API: $name - Rating: $rating");
            echo json_encode([
                'success' => true, 
                'message' => 'Thank you for your review! It has been submitted successfully and will be visible shortly.',
                'data' => [
                    'name' => $name,
                    'rating' => $rating,
                    'submission_time' => date('Y-m-d H:i:s')
                ]
            ]);
        } else {
            error_log("Failed to save review via API for: $name");
            echo json_encode(['success' => false, 'message' => 'Sorry, there was an error saving your review. Please try again.']);
        }
        exit();
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Handle review retrieval
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? min(50, max(1, (int)$_GET['limit'])) : 10;
        $offset = ($page - 1) * $limit;
        
        // Get reviews with pagination
        $query = "SELECT id, name, email, rate, msg, date_added
                  FROM comment 
                  WHERE status = 'approved' 
                  ORDER BY date_added DESC 
                  LIMIT ? OFFSET ?";
        
        $reviews = executeQuery($query, [$limit, $offset], 'ii');
        
        // Get total count
        $totalResult = executeQuery("SELECT COUNT(*) as total FROM comment WHERE status = 'approved'");
        $total = $totalResult ? $totalResult[0]['total'] : 0;
        
        // Get statistics
        $stats = getReviewStats();
        
        if ($reviews !== false) {
            echo json_encode([
                'success' => true,
                'message' => 'Reviews retrieved successfully.',
                'data' => [
                    'reviews' => $reviews,
                    'pagination' => [
                        'current_page' => $page,
                        'total_pages' => ceil($total / $limit),
                        'total_reviews' => (int) $total,
                        'reviews_per_page' => $limit
                    ],
                    'stats' => $stats
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error retrieving reviews.']);
        }
        exit();
    }
}

// Handle traditional form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $name = sanitizeInput($_POST['reviewerName']);
    $email = sanitizeInput($_POST['reviewerEmail']);
    $rating = intval($_POST['rating']);
    $comment = sanitizeInput($_POST['reviewText']);
    
    // Validate input
    if (!empty($name) && !empty($email) && $rating >= 1 && $rating <= 5 && !empty($comment)) {
        if (!isValidEmail($email)) {
            $error_message = "Please provide a valid email address.";
        } elseif (checkDuplicateReview($name, $email)) {
            $error_message = "You have already submitted a review recently. Please wait 24 hours before submitting another review.";
        } else {
            if (addReview($name, $email, $rating, $comment)) {
                $success_message = "Thank you for your review! It has been submitted successfully and will be visible shortly.";
                // Clear form data after successful submission
                $_POST = [];
            } else {
                $error_message = "Sorry, there was an error submitting your review. Please try again.";
            }
        }
    } else {
        $error_message = "Please fill in all fields correctly.";
    }
}

// Get reviews and statistics using config functions
$reviews = getRecentReviews(50);
$stats = getReviewStats();

// Extract statistics with fallback values
$avg_rating = $stats ? round($stats['average_rating'], 1) : 4.9;
$total_reviews = $stats ? $stats['total_reviews'] : 0;
$five_star_count = $stats ? $stats['five_star_count'] : 0;
$satisfaction_rate = $stats ? round($stats['satisfaction_percentage'], 0) : 98;

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'Just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31536000) return floor($time/2592000) . ' months ago';
    return floor($time/31536000) . ' years ago';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews - Cake Cravings</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .reviews-page {
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

        .reviews-stats {
            background: white;
            padding: 3rem;
            margin: 0 7% 4rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 3rem;
            text-align: center;
        }

        .stat-item h3 {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .stat-item p {
            color: var(--text-light);
            font-size: 1.6rem;
        }

        .overall-rating {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2rem;
            margin: 2rem 0;
        }

        .rating-stars {
            font-size: 3rem;
            color: #ffc107;
        }

        .reviews-container {
            padding: 0 7%;
            margin-bottom: 5rem;
        }

        .reviews-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 3rem;
            margin-bottom: 4rem;
        }

        .review-card {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            position: relative;
        }

        .review-card:hover {
            transform: translateY(-10px);
        }

        .review-header {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .reviewer-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--primary-color), #d16866);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: 700;
        }

        .reviewer-info h4 {
            font-size: 1.8rem;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .reviewer-info .email {
            color: var(--text-light);
            font-size: 1.4rem;
        }

        .review-rating {
            color: #ffc107;
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
        }

        .review-text {
            color: var(--text-light);
            font-size: 1.6rem;
            line-height: 1.6;
            margin-bottom: 2rem;
            font-style: italic;
        }

        .review-date {
            color: var(--text-light);
            font-size: 1.2rem;
            position: absolute;
            top: 2rem;
            right: 2rem;
        }

        .add-review-section {
            background: white;
            padding: 4rem;
            margin: 0 7% 5rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .add-review-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .add-review-header h2 {
            font-size: 3rem;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }

        .add-review-header p {
            color: var(--text-light);
            font-size: 1.6rem;
        }

        .review-form {
            max-width: 600px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 2rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 1rem;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 1.6rem;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 1.5rem;
            border: 2px solid #eee;
            border-radius: 10px;
            font-size: 1.6rem;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .rating-input {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .star-rating {
            display: flex;
            gap: 0.5rem;
        }

        .star {
            font-size: 3rem;
            color: #ddd;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .star:hover,
        .star.active {
            color: #ffc107;
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
        }

        .submit-btn:hover {
            transform: translateY(-3px);
        }

        .back-home {
            text-align: center;
            padding: 3rem 0;
        }
        
        .back-home a {
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
        
        .back-home a:hover {
            background: var(--primary-color);
            transform: translateY(-3px);
        }

        @media (max-width: 768px) {
            .reviews-grid {
                grid-template-columns: 1fr;
                padding: 0;
            }
            
            .reviews-container,
            .add-review-section {
                margin: 0 2rem 3rem;
                padding: 2rem;
            }
            
            .reviews-stats {
                margin: 0 2rem 3rem;
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

    <!-- Reviews Page -->
    <section class="reviews-page">
        <div class="page-header">
            <h1><i class="fas fa-star"></i> Customer Reviews</h1>
            <p>See what our happy customers have to say about their cake experiences with us</p>
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

        <!-- Dynamic Reviews Stats -->
        <div class="reviews-stats">
            <div class="stat-item">
                <h3><?php echo $avg_rating; ?></h3>
                <div class="overall-rating">
                    <div class="rating-stars">
                        <?php 
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $avg_rating) {
                                echo '<i class="fas fa-star"></i>';
                            } else {
                                echo '<i class="far fa-star"></i>';
                            }
                        }
                        ?>
                    </div>
                </div>
                <p>Overall Rating</p>
            </div>
            <div class="stat-item">
                <h3><?php echo number_format($total_reviews); ?>+</h3>
                <p>Happy Customers</p>
            </div>
            <div class="stat-item">
                <h3><?php echo $satisfaction_rate; ?>%</h3>
                <p>Satisfaction Rate</p>
            </div>
            <div class="stat-item">
                <h3><?php echo number_format($five_star_count); ?>+</h3>
                <p>Five Star Reviews</p>
            </div>
        </div>

        <!-- Dynamic Customer Reviews -->
        <div class="reviews-container">
            <div class="reviews-grid">
                <?php if ($reviews && count($reviews) > 0): ?>
                    <?php foreach ($reviews as $review): ?>
                    <div class="review-card">
                        <?php if (isset($review['date_added'])): ?>
                            <div class="review-date"><?php echo timeAgo($review['date_added']); ?></div>
                        <?php endif; ?>
                        <div class="review-header">
                            <div class="reviewer-avatar">
                                <?php echo strtoupper(substr($review['name'], 0, 1)); ?>
                            </div>
                            <div class="reviewer-info">
                                <h4><?php echo htmlspecialchars($review['name']); ?></h4>
                                <?php if (!empty($review['email'])): ?>
                                    <div class="email"><?php echo htmlspecialchars($review['email']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="review-rating">
                            <?php 
                            $rating = isset($review['rate']) ? intval($review['rate']) : 5;
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $rating) {
                                    echo '<i class="fas fa-star"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            ?>
                        </div>
                        <div class="review-text">
                            "<?php echo htmlspecialchars($review['msg']); ?>"
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 4rem;">
                        <p style="font-size: 1.8rem; color: #666;">No reviews yet. Be the first to leave a review!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Add Review Section -->
        <div class="add-review-section">
            <div class="add-review-header">
                <h2>Share Your Experience</h2>
                <p>We'd love to hear about your experience with our cakes!</p>
            </div>
            
            <form class="review-form" method="POST" action="review.php">
                <div class="form-group">
                    <label for="reviewerName">Your Name *</label>
                    <input type="text" id="reviewerName" name="reviewerName" required 
                           value="<?php echo isset($_POST['reviewerName']) ? htmlspecialchars($_POST['reviewerName']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="reviewerEmail">Email Address *</label>
                    <input type="email" id="reviewerEmail" name="reviewerEmail" required
                           placeholder="your@email.com"
                           value="<?php echo isset($_POST['reviewerEmail']) ? htmlspecialchars($_POST['reviewerEmail']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="rating">Rating *</label>
                    <div class="rating-input">
                        <div class="star-rating" id="starRating">
                            <span class="star" data-rating="1"><i class="fas fa-star"></i></span>
                            <span class="star" data-rating="2"><i class="fas fa-star"></i></span>
                            <span class="star" data-rating="3"><i class="fas fa-star"></i></span>
                            <span class="star" data-rating="4"><i class="fas fa-star"></i></span>
                            <span class="star" data-rating="5"><i class="fas fa-star"></i></span>
                        </div>
                        <span id="ratingValue">Select Rating</span>
                    </div>
                    <input type="hidden" id="rating" name="rating" required>
                </div>
                
                <div class="form-group">
                    <label for="reviewText">Your Review *</label>
                    <textarea id="reviewText" name="reviewText" rows="5" 
                              placeholder="Tell us about your experience with our cakes..." 
                              required><?php echo isset($_POST['reviewText']) ? htmlspecialchars($_POST['reviewText']) : ''; ?></textarea>
                </div>
                
                <button type="submit" name="submit_review" class="submit-btn">
                    <i class="fas fa-star"></i> Submit Review
                </button>
            </form>
        </div>

        <div class="back-home">
            <a href="home.html">
                <i class="fas fa-arrow-left"></i>
                Back to Home
            </a>
        </div>
    </section>

    <!-- Enhanced JavaScript -->
    <script src="assets/js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Star rating functionality
            const stars = document.querySelectorAll('.star');
            const ratingValue = document.getElementById('ratingValue');
            const ratingInput = document.getElementById('rating');
            let currentRating = 0;

            stars.forEach(star => {
                star.addEventListener('mouseover', function() {
                    const rating = parseInt(this.dataset.rating);
                    highlightStars(rating);
                    updateRatingText(rating);
                });

                star.addEventListener('click', function() {
                    currentRating = parseInt(this.dataset.rating);
                    ratingInput.value = currentRating;
                    updateRatingText(currentRating);
                    highlightStars(currentRating);
                    
                    // Add celebration effect for 5 stars
                    if (currentRating === 5) {
                        createStarCelebration(this);
                    }
                });
            });

            document.getElementById('starRating').addEventListener('mouseleave', function() {
                highlightStars(currentRating);
                updateRatingText(currentRating);
            });

            function highlightStars(rating) {
                stars.forEach((star, index) => {
                    if (index < rating) {
                        star.classList.add('active');
                        star.style.transform = 'scale(1.2)';
                    } else {
                        star.classList.remove('active');
                        star.style.transform = 'scale(1)';
                    }
                });
            }
            
            function updateRatingText(rating) {
                const texts = ['Select Rating', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
                ratingValue.textContent = `${rating > 0 ? rating + ' star' + (rating !== 1 ? 's' : '') + ' - ' : ''}${texts[rating] || 'Select Rating'}`;
                ratingValue.style.color = rating > 0 ? '#ffc107' : '#666';
                ratingValue.style.fontWeight = rating > 0 ? 'bold' : 'normal';
            }
            
            function createStarCelebration(targetStar) {
                // Create celebration animation
                for (let i = 0; i < 12; i++) {
                    const sparkle = document.createElement('div');
                    sparkle.innerHTML = '✨';
                    sparkle.style.cssText = `
                        position: absolute;
                        font-size: 2rem;
                        pointer-events: none;
                        z-index: 1000;
                        animation: sparkleAnimation 1.5s ease-out forwards;
                    `;
                    
                    const rect = targetStar.getBoundingClientRect();
                    sparkle.style.left = (rect.left + Math.random() * 50) + 'px';
                    sparkle.style.top = (rect.top + Math.random() * 50) + 'px';
                    
                    document.body.appendChild(sparkle);
                    setTimeout(() => sparkle.remove(), 1500);
                }
                
                // Add CSS animation for sparkles
                if (!document.getElementById('sparkleStyles')) {
                    const sparkleStyle = document.createElement('style');
                    sparkleStyle.id = 'sparkleStyles';
                    sparkleStyle.textContent = `
                        @keyframes sparkleAnimation {
                            0% {
                                opacity: 1;
                                transform: scale(0) rotate(0deg);
                            }
                            50% {
                                opacity: 1;
                                transform: scale(1.5) rotate(180deg);
                            }
                            100% {
                                opacity: 0;
                                transform: scale(0) rotate(360deg) translate(${Math.random() * 200 - 100}px, ${Math.random() * 200 - 100}px);
                            }
                        }
                    `;
                    document.head.appendChild(sparkleStyle);
                }
            }

            // Auto-hide alerts
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-20px)';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });

            // Animate review cards on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);
            
            const reviewCards = document.querySelectorAll('.review-card');
            reviewCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
                observer.observe(card);
            });

            // Add floating animation to stats
            const statItems = document.querySelectorAll('.stat-item');
            statItems.forEach((item, index) => {
                item.style.animation = `float 4s ease-in-out ${index * 0.5}s infinite`;
            });

            // Add CSS for floating animation
            if (!document.getElementById('floatingAnimation')) {
                const floatingStyle = document.createElement('style');
                floatingStyle.id = 'floatingAnimation';
                floatingStyle.textContent = `
                    @keyframes float {
                        0%, 100% { transform: translateY(0px); }
                        50% { transform: translateY(-10px); }
                    }
                `;
                document.head.appendChild(floatingStyle);
            }

            // Form validation enhancement
            const form = document.querySelector('.review-form');
            form.addEventListener('submit', function(e) {
                if (currentRating === 0) {
                    e.preventDefault();
                    alert('Please select a rating before submitting your review.');
                    return false;
                }
                
                const reviewText = document.getElementById('reviewText').value.trim();
                if (reviewText.length < 10) {
                    e.preventDefault();
                    alert('Please write at least 10 characters in your review.');
                    return false;
                }
            });
        });
    </script>
</body>
</html>