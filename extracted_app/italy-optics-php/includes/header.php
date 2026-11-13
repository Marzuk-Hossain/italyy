<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$currentUser = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars(SITE_NAME_TITLE) ?></title>
  
  <!-- SEO Meta Tags -->
  <meta name="description" content="<?= htmlspecialchars(SITE_DESCRIPTION) ?>">
  <meta name="keywords" content="<?= htmlspecialchars(SITE_KEYWORDS) ?>">
  <meta name="author" content="Italy Optics">
  <meta name="robots" content="index, follow, max-image-preview:large">
  <meta name="geo.region" content="BD-B">
  <meta name="geo.placename" content="Chattogram">
  <meta name="geo.position" content="22.3475;91.8350">
  <meta name="ICBM" content="22.3475, 91.8350">
  <link rel="canonical" href="<?= htmlspecialchars(BASE_URL) ?>/index.php">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?= htmlspecialchars(SITE_NAME_TITLE) ?>">
  <meta property="og:description" content="<?= htmlspecialchars(SITE_DESCRIPTION) ?>">
  <meta property="og:url" content="<?= htmlspecialchars(BASE_URL) ?>/index.php">
  <meta property="og:site_name" content="<?= htmlspecialchars(SITE_NAME) ?>">
  <meta property="og:image" content="https://images.unsplash.com/photo-1591076482161-42ce6da69f68?auto=format&fit=crop&w=1200&q=80">
  <meta property="og:locale" content="en_US">

  <!-- Twitter Meta Tags -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= htmlspecialchars(SITE_NAME_TITLE) ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars(SITE_DESCRIPTION) ?>">
  <meta name="twitter:image" content="https://images.unsplash.com/photo-1591076482161-42ce6da69f68?auto=format&fit=crop&w=1200&q=80">

  <!-- JSON-LD Local Business Structured Data for Google Indexing -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Optician",
    "name": "<?= htmlspecialchars(SITE_NAME) ?>",
    "image": "https://images.unsplash.com/photo-1591076482161-42ce6da69f68?auto=format&fit=crop&w=1200&q=80",
    "telephone": "<?= htmlspecialchars(STORE_PHONE) ?>",
    "email": "<?= htmlspecialchars(STORE_EMAIL) ?>",
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "12 Jamal Khan Road",
      "addressLocality": "Chattogram",
      "postalCode": "4000",
      "addressCountry": "BD"
    },
    "geo": {
      "@type": "GeoCoordinates",
      "latitude": "22.3569",
      "longitude": "91.8331"
    },
    "openingHoursSpecification": {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
      "opens": "09:00",
      "closes": "22:00"
    },
    "aggregateRating": {
      "@type": "AggregateRating",
      "ratingValue": "5.0",
      "reviewCount": "3"
    },
    "priceRange": "৳৳"
  }
  </script>
  
  <!-- Typography: Cormorant Garamond + Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- Luxury Monochrome Theme Stylesheet -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- Top Black Notification Bar -->
<div class="top-bar">
  <div class="container top-bar-content">
    <div style="display: flex; align-items: center; gap: 16px;">
      <span class="badge-open">
        <span class="badge-dot"></span>
        <?= htmlspecialchars(STORE_HOURS) ?>
      </span>
      <span style="color: #A1A1AA;">
        📍 <?= htmlspecialchars(STORE_ADDRESS) ?>
      </span>
    </div>

    <div style="display: flex; align-items: center; gap: 16px;">
      <a href="tel:<?= preg_replace('/[^0-9]/', '', STORE_PHONE) ?>" style="color: #FFF; font-weight: 700;">
        📞 <?= htmlspecialchars(STORE_PHONE) ?>
      </a>
      <span style="color: #71717A;">|</span>
      <span>★ <?= htmlspecialchars(STORE_RATING) ?> (<?= htmlspecialchars(STORE_REVIEWS_COUNT) ?>)</span>
      <?php if (!empty($currentUser) && $currentUser['role'] === 'admin'): ?>
        <span style="background: #FFF; color: #000; padding: 2px 8px; border-radius: 4px; font-weight: 800; font-size: 10px;">
          ADMIN MODE
        </span>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Main Sticky Header -->
<header class="site-header">
  <div class="container navbar">
    
    <!-- Brand Logo -->
    <a href="index.php" class="brand-logo">
      <div class="brand-icon">👓</div>
      <div>
        <div class="brand-name"><?= htmlspecialchars(SITE_NAME) ?></div>
        <div class="brand-tagline">Optician · Jamal Khan, Chattogram</div>
      </div>
    </a>

    <!-- Navigation Links -->
    <ul class="nav-links">
      <li><a href="index.php">Showroom</a></li>
      <li><a href="index.php?category=Eyeglasses">Eyeglasses</a></li>
      <li><a href="index.php?category=Sunglasses">Sunglasses</a></li>
      <li><a href="index.php?category=Blue Light">Blue Light</a></li>
      <li><a href="#contact">Contact</a></li>
    </ul>

    <!-- Header Actions -->
    <div class="nav-actions">

      <!-- Admin-Only Controls (Hidden from guests & customers) -->
      <?php if (!empty($currentUser) && $currentUser['role'] === 'admin'): ?>
        <a href="setup.php" class="btn btn-white" style="padding: 6px 12px; font-size: 11px;" title="MySQL Database Auto-Setup">
          ⚙️ DB Setup
        </a>
        <a href="admin/index.php" class="btn btn-black" style="padding: 6px 14px; font-size: 11px;">
          Inventory Panel
        </a>
      <?php endif; ?>

      <!-- User Account -->
      <?php if (!empty($currentUser)): ?>
        <div style="display: flex; align-items: center; gap: 8px;">
          <span style="font-size: 12px; font-weight: 700;">
            <?= htmlspecialchars(explode(' ', $currentUser['name'])[0]) ?>
          </span>
          <a href="logout.php" class="btn btn-white" style="padding: 6px 10px; font-size: 11px;">
            Logout
          </a>
        </div>
      <?php else: ?>
        <button type="button" class="btn btn-white open-auth-btn" style="padding: 6px 14px; font-size: 11px;">
          Log In
        </button>
      <?php endif; ?>

      <!-- Cart Button -->
      <button type="button" class="btn-icon toggle-cart-btn" aria-label="Cart">
        🛒
        <span class="cart-count-badge" style="display: none;">0</span>
      </button>

    </div>

  </div>
</header>
