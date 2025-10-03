# 🍰 Cake Cravings - Premium Custom Cake Ordering Website

A beautiful, modern multi-page website for a custom cake business with comprehensive functionality and professional design.

## ✨ Features

- **Multi-Page Architecture** - Separate dedicated pages for each section with smooth navigation
- **Responsive Design** - Works perfectly on all devices with mobile-first approach
- **Modern UI/UX** - Clean, professional design with smooth animations and gradients
- **Complete Order System** - Advanced cake ordering form with validation and localStorage
- **Enhanced Gallery & Slideshow** - Professional image showcase with automatic slideshow, controls, and touch support
- **Interactive Category System** - Dedicated pages for different cake types with hover effects and animations
- **Advanced Review System** - Customer testimonials with interactive star rating, real-time submission, and celebration effects
- **Comprehensive Contact System** - Enhanced contact form with business information and interactive map
- **Lively JavaScript Interactions** - Enhanced animations, floating effects, typewriter effects, page transitions, and lightbox gallery
- **Interactive Elements** - Hover effects, parallax scrolling, smooth transitions, and responsive feedback throughout

## 🛠️ Technologies Used

- **Frontend:**

  - HTML5 (Semantic markup with accessibility)
  - CSS3 (Modern styling with animations, gradients, and responsive design)
  - JavaScript (ES6+ features with enhanced interactions)
    - Interactive slideshow with controls and touch support
    - Advanced form validation and real-time feedback
    - Star rating system with celebration animations
    - Lightbox gallery with keyboard navigation
    - Floating animations and parallax effects
    - Page transitions and smooth scrolling
    - Toast notifications and dynamic content updates
  - Font Awesome icons
  - Google Fonts (Poppins, Quicksand)

- **Backend:**
  - PHP (Form processing and review management)
  - MySQL (Database storage for orders and reviews)

## 📁 Project Structure

```
cake-order-site-main/
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet with modern design
│   ├── js/
│   │   └── script.js          # JavaScript functionality
│   └── images/                # Image assets folder
├── pages/
│   ├── gallery.html           # Gallery with slideshow and about section
│   ├── categories.html        # Categories overview page
│   ├── reviews.html           # Customer reviews and rating system
│   ├── contact.html           # Contact form and business info
│   ├── wedding-cakes.html     # Wedding cake category
│   ├── birthday-cakes.html    # Birthday cake category
│   ├── anniversary-cakes.html # Anniversary cake category
│   └── party-cakes.html       # Party cake category
├── home.html                  # Main homepage/landing page
├── form.html                  # Order form page
├── form.php                   # Form processing & confirmation
└── README.md                  # Project documentation
```

## 🚀 Setup Instructions

### 1. Web Server Setup

- Install XAMPP, WAMP, or similar local server
- Place the project folder in your web server directory (e.g., `htdocs`)

### 2. Database Setup

- Create a MySQL database named `cake_orders`
- The PHP script will automatically create the necessary tables:
  - `orders` - Store cake orders
  - `contacts` - Store contact form submissions

### 3. Image Setup

- Add your cake images to the `assets/images/` folder:
  - `logo.jpg` - Business logo
  - `hero-cake.png` - Main hero image
  - `cake1.jpeg`, `cake2.jpeg`, `cake3.jpeg` - Slideshow images
  - `wedding-cake.jpeg`, `birthday-cake.jpeg`, `anniversary-cake.jpeg`, `party-cake.jpeg` - Category images
  - `review1.jpeg` to `review4.jpeg` - Customer photos
  - `bgimg.jpeg` - Contact section background
  - Category-specific images:
    - `wedding-1.jpg` to `wedding-6.jpg` - Wedding cake gallery
    - `birthday-1.jpg` to `birthday-6.jpg` - Birthday cake gallery
    - `anniversary-1.jpg` to `anniversary-6.jpg` - Anniversary cake gallery
    - `party-1.jpg` to `party-6.jpg` - Party cake gallery

### 4. Configuration

- Update database credentials in `form.php` if needed:
  ```php
  $servername = "localhost";
  $username = "root";
  $password = "";
  $database = "cake_orders";
  ```

## 🎨 Customization

### Colors

The site uses CSS custom properties for easy theming:

```css
:root {
  --primary-color: #bc5957; /* Main brand color */
  --secondary-color: #1b1722; /* Dark text */
  --accent-color: #f9d71c; /* Star ratings */
  --text-light: #666; /* Light text */
}
```

### Content

- Edit text content directly in the HTML files
- Replace images in the `assets/images/` folder
- Update contact information in the contact section

## 📱 Responsive Breakpoints

- **Desktop:** 1200px and above
- **Tablet:** 768px - 1199px
- **Mobile:** Below 768px

## 🔧 Features Overview

### Homepage (`home.html`)

- **Hero Section** - Eye-catching introduction with navigation to all sections
- **Navigation** - Direct links to all dedicated pages (Gallery, Categories, Reviews, Contact)
- **Clean Design** - Simplified landing page focusing on navigation and branding

### Gallery Page (`pages/gallery.html`)

- **About Section** - Company story and mission statement
- **Statistics Display** - Showcase of achievements (customers served, cakes made, years in business)
- **Image Slideshow** - Automatic progression showcasing featured cakes
- **Featured Gallery** - Grid layout of professional cake photography
- **Interactive Elements** - Hover effects and smooth animations

### Categories Page (`pages/categories.html`)

- **Category Overview** - Central hub for all cake types
- **Feature Cards** - Each category with detailed features and highlights
- **Visual Design** - Hover overlays and professional card layouts
- **Direct Navigation** - Quick access to specific category pages
- **Category Features** - Icons and descriptions for each cake type

### Individual Category Pages (`pages/`)

- **Wedding Cakes** - Elegant designs for special wedding celebrations with romantic themes
- **Birthday Cakes** - Fun and colorful designs for all ages with festive elements
- **Anniversary Cakes** - Romantic designs for milestone celebrations with special touches
- **Party Cakes** - Exciting designs for parties and events with vibrant colors
- Each page features 6 different cake designs with detailed descriptions and pricing

### Reviews Page (`pages/reviews.html`)

- **Customer Statistics** - Overall rating, customer count, satisfaction rate
- **Review Showcase** - Professional display of customer testimonials
- **Interactive Rating** - Star rating system for viewing and submitting reviews
- **Review Submission** - Complete form for customers to share their experiences
- **Validation System** - Proper form validation and user feedback

### Contact Page (`pages/contact.html`)

- **Contact Information** - Comprehensive business details, phone, email, social media
- **Business Hours** - Complete schedule and availability information
- **Contact Form** - Advanced form with inquiry types, event details, budget options
- **Location Details** - Address and map placeholder for future integration
- **Professional Layout** - Clean, organized presentation of all contact methods

### Order Form (`form.html`)

- **Customer Details** - Name, phone, email validation with enhanced UX
- **Cake Specifications** - Event type, size, flavor selection with visual indicators
- **Date Selection** - Future date validation with calendar integration
- **Selected Cake Integration** - Shows pre-selected cake from gallery pages
- **Special Instructions** - Custom message field for specific requirements
- **Advanced Validation** - Client-side and server-side validation with real-time feedback

### Form Processing (`form.php`)

- **Data Validation** - Comprehensive input sanitization
- **Database Storage** - Secure data insertion
- **Email Validation** - Proper email format checking
- **Success/Error Handling** - User-friendly feedback messages

## 🎯 Key Improvements Made

1. **HTML Structure** - Clean, semantic markup with proper accessibility
2. **CSS Architecture** - Modern styling with CSS Grid and Flexbox
3. **JavaScript Functionality** - Smooth scrolling, form validation, animations
4. **Responsive Design** - Mobile-first approach with proper breakpoints
5. **Performance** - Optimized images and efficient code
6. **User Experience** - Intuitive navigation and feedback systems
7. **Security** - SQL injection prevention and input sanitization

## 🌟 Usage

1. **Start your web server** (XAMPP, WAMP, etc.)
2. **Navigate to** `http://localhost/cake-order-site-main/home.html`
3. **Browse the site** and test the order form functionality
4. **Check the database** to see stored orders

## 🤝 Contributing

Feel free to contribute to this project by:

- Reporting bugs
- Suggesting new features
- Improving the code
- Adding more cake categories
- Enhancing the design

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 📞 Support

For support or questions about this project, please open an issue in the repository.

---

**Made with ❤️ for Cake Cravings**
