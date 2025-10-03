# 🎨 Logo Update Instructions

## Your Beautiful New Logo!

You've provided a gorgeous circular logo with:

- Beautiful watercolor floral design with pink and blue flowers
- Baking tools (spatula, whisk, rolling pin) in the center
- "Cake Cravings" text in elegant pink script
- "Made with love" tagline below
- Gold glitter border around the circle

## 📁 How to Update Your Logo

### Step 1: Save the New Logo Image

1. **Save your new logo image** (the one you just provided) as:

   ```
   f:\projects\cake-order-site-main\assets\images\logo-new.jpg
   ```

2. **Recommended sizes:**
   - **Main logo:** 300x300px (current)
   - **Favicon:** 32x32px (create a smaller version)
   - **High-res:** 600x600px (for print/high-DPI displays)

### Step 2: Files Already Updated ✅

I've already updated the main `home.html` file to use the new logo path:

```html
<img src="assets/images/logo-new.jpg" alt="Cake Cravings - Made with Love" />
```

### Step 3: Files That Need Manual Update 📝

Update these files to use the new logo:

#### PHP Files (Important):

- **review.php** (line 367)
- **order.php** (line 277)

Change from:

```html
<img src="assets/images/logo.jpg" alt="Cake Cravings Logo" />
```

To:

```html
<img src="assets/images/logo-new.jpg" alt="Cake Cravings - Made with Love" />
```

#### Pages Folder:

- **pages/reviews.html** (line 304)
- **pages/contact.html** (line 320)
- **pages/gallery.html** (line 218)
- **pages/categories.html** (line 217)
- **pages/wedding-cakes.html** (line 168)
- **pages/birthday-cakes.html** (line 168)
- **pages/anniversary-cakes.html** (line 168)
- **pages/party-cakes.html** (line 168)

Change from:

```html
<img src="../assets/images/logo.jpg" alt="Cake Cravings Logo" />
```

To:

```html
<img src="../assets/images/logo-new.jpg" alt="Cake Cravings - Made with Love" />
```

#### JavaScript File:

- **assets/js/script.js** (line 1011)

Change from:

```javascript
'../assets/images/logo.jpg',
```

To:

```javascript
'../assets/images/logo-new.jpg',
```

### Step 4: Optional CSS Enhancement 🎨

Add this CSS to make your logo look even better:

```css
.logo img {
  width: 60px;
  height: 60px;
  object-fit: contain;
  border-radius: 50%;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease;
}

.logo img:hover {
  transform: scale(1.05);
}
```

### Step 5: Create Favicon 🌟

1. **Resize your logo** to 32x32px
2. **Save as** `assets/images/favicon.png`
3. **Update the favicon reference** in all HTML files:

```html
<link rel="shortcut icon" href="assets/images/favicon.png" />
```

## 🎉 Result

Your beautiful circular "Cake Cravings" logo with the floral design and baking tools will now appear throughout your website, giving it a professional, cohesive brand identity!

## 🔍 Quick Find & Replace

Use your text editor's "Find & Replace" feature:

**Find:** `logo.jpg`
**Replace:** `logo-new.jpg`

**Find:** `logo.JPG`
**Replace:** `logo-new.jpg`

This will update most references quickly!
