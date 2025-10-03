# 🎂 Cake Cravings Database Setup Guide

## 📋 Complete XAMPP/htdocs Setup Instructions

### Step 1: Install XAMPP

1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install XAMPP on your computer
3. Start Apache and MySQL services from XAMPP Control Panel

### Step 2: Setup Project in htdocs

1. **Copy your project folder** to XAMPP's htdocs directory:

   ```
   C:\xampp\htdocs\cake-order-site-main\
   ```

2. **Your project structure should look like this:**
   ```
   C:\xampp\htdocs\cake-order-site-main\
   ├── home.html
   ├── review.php
   ├── cake(1).sql
   ├── DATABASE_SETUP_GUIDE.md
   ├── config.php (we'll create this)
   ├── assets/
   │   ├── css/
   │   ├── js/
   │   └── images/
   └── pages/
   ```

### Step 3: Create Database

1. **Open phpMyAdmin** in your browser:

   ```
   http://localhost/phpmyadmin
   ```

2. **Import the database:**

   - Click "Import" tab
   - Choose file: `cake(1).sql`
   - Click "Import" button

   **OR manually run the SQL:**

   - Click "SQL" tab
   - Copy and paste the content from `cake(1).sql`
   - Click "Go"

### Step 4: Verify Database Creation

After importing, you should see:

- ✅ Database: `cake`
- ✅ Tables: `comment`, `orders`, `categories`, `products`, `contact_messages`
- ✅ Views: `review_stats`, `order_summary`
- ✅ Sample data in all tables

### Step 5: Access Your Website

Open your browser and go to:

```
http://localhost/cake-order-site-main/home.html
```

For the review system:

```
http://localhost/cake-order-site-main/review.php
```

## 🔧 Database Configuration

### Default Connection Settings:

- **Host:** localhost
- **Username:** root
- **Password:** (empty)
- **Database:** cake
- **Port:** 3306

### Tables Overview:

#### 1. `comment` (Reviews)

- `id` - Auto increment primary key
- `name` - Customer name
- `email` - Customer email
- `rate` - Rating (1-5 stars)
- `msg` - Review message
- `date_added` - Timestamp
- `status` - approved/pending/rejected

#### 2. `orders` (Enhanced Orders)

- `id` - Auto increment primary key
- `order_id` - Unique order reference
- `customer_name` - Customer name
- `customer_email` - Customer email
- `customer_phone` - Phone number
- `event_type` - wedding/birthday/anniversary/party
- `cake_size` - 1kg/2kg/3kg
- `cake_flavor` - Flavor selection
- `delivery_date` - Delivery date
- `total_amount` - Order total
- `order_status` - Order status tracking

#### 3. `categories` (Cake Categories)

- Predefined cake categories with descriptions
- Used for organizing products

#### 4. `products` (Cake Products)

- Product catalog with pricing
- Multiple size options
- JSON flavor storage

#### 5. `contact_messages` (Contact Form)

- Customer inquiries and messages
- Status tracking for follow-up

## 🚀 Quick Start Commands

### View Review Statistics:

```sql
SELECT * FROM review_stats;
```

### View Order Summary:

```sql
SELECT * FROM order_summary;
```

### Add New Review:

```sql
INSERT INTO comment (name, email, rate, msg)
VALUES ('Your Name', 'your@email.com', 5, 'Amazing cake!');
```

### Add New Order:

```sql
INSERT INTO orders (order_id, customer_name, customer_email, customer_phone, event_type, cake_size, cake_flavor, delivery_date, total_amount)
VALUES ('ORD005', 'John Doe', 'john@email.com', '0771234567', 'birthday', '2kg', 'chocolate', '2025-10-01', 2500.00);
```

## 🔍 Troubleshooting

### Common Issues:

1. **"Access denied for user 'root'"**

   - Check if MySQL is running in XAMPP
   - Verify username/password in config.php

2. **"Database 'cake' doesn't exist"**

   - Re-import the cake(1).sql file
   - Check if import was successful

3. **"Table 'comment' doesn't exist"**

   - Database import may have failed
   - Run the SQL commands manually

4. **Page shows errors**
   - Check if Apache is running
   - Verify file paths are correct
   - Check PHP error logs

### Check Database Connection:

Create a test file `test-db.php`:

```php
<?php
$con = mysqli_connect("localhost", "root", "", "cake");
if ($con) {
    echo "✅ Database connection successful!";
    $result = mysqli_query($con, "SELECT COUNT(*) as count FROM comment");
    $row = mysqli_fetch_assoc($result);
    echo "<br>📊 Total reviews: " . $row['count'];
} else {
    echo "❌ Database connection failed: " . mysqli_connect_error();
}
?>
```

## 📊 Features Included

### Review System:

- ⭐ Interactive star ratings
- 📊 Real-time statistics
- 💬 Customer testimonials
- 📱 Mobile responsive design

### Order Management:

- 📝 Order tracking
- 💰 Price calculation
- 📅 Delivery scheduling
- 📧 Customer notifications

### Admin Features:

- 📈 Dashboard statistics
- 📋 Order management
- 💬 Review moderation
- 📞 Contact management

## 🎯 Next Steps

1. **Customize Content:** Update images and text content
2. **Add Admin Panel:** Create admin interface for managing orders
3. **Email Integration:** Setup email notifications for orders
4. **Payment Gateway:** Integrate payment processing
5. **SEO Optimization:** Add meta tags and structured data

---

**🎉 Your cake ordering website is now ready to use!**

Access it at: `http://localhost/cake-order-site-main/home.html`
