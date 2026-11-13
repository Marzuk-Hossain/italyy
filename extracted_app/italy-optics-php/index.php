<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$db = getDB();
$selectedCategory = $_GET['category'] ?? 'All';
$selectedGender = $_GET['gender'] ?? 'All';
$selectedShape = $_GET['shape'] ?? 'All';
$sortBy = $_GET['sort'] ?? 'featured';
$searchQuery = trim($_GET['search'] ?? '');

// Handle Contact Form Submission -> Save to MySQL contact_messages table
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'submit_contact') {
    $cName = trim($_POST['contact_name'] ?? '');
    $cPhone = trim($_POST['contact_phone'] ?? '');
    $cEmail = trim($_POST['contact_email'] ?? '');
    $cTopic = trim($_POST['contact_topic'] ?? 'General Inquiry');
    $cMessage = trim($_POST['contact_message'] ?? '');

    if ($cName && $cPhone && $cEmail && $cMessage) {
        if ($db) {
            try {
                $msgStmt = $db->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
                $msgStmt->execute([$cName, $cEmail, $cPhone, $cTopic, $cMessage]);
            } catch (PDOException $e) {
                error_log('Contact save error: ' . $e->getMessage());
            }
        }
        $_SESSION['flash_success'] = "Thank you, {$cName}! Your inquiry was sent to Italy Optics at Jamal Khan Road.";
    } else {
        $_SESSION['flash_error'] = "Please fill in all required contact fields.";
    }
    header("Location: index.php#contact");
    exit;
}

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
$flashInfo = $_SESSION['flash_info'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error'], $_SESSION['flash_info']);

// Fetch Categories
$categories = [];
if ($db) {
    try {
        $categories = $db->query("SELECT * FROM categories ORDER BY display_order ASC")->fetchAll();
    } catch (Exception $e) {
        $categories = [];
    }
}

// Fetch Products from MySQL
$products = [];
if ($db) {
    try {
        $sql = "
            SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE 1=1
        ";
        $params = [];

        if ($selectedCategory !== 'All') {
            $sql .= " AND (c.name = ? OR c.slug = ?)";
            $params[] = $selectedCategory;
            $params[] = strtolower(str_replace(' ', '-', $selectedCategory));
        }

        if ($selectedGender !== 'All') {
            $sql .= " AND p.gender = ?";
            $params[] = $selectedGender;
        }

        if ($selectedShape !== 'All') {
            $sql .= " AND p.shape = ?";
            $params[] = $selectedShape;
        }

        if (!empty($searchQuery)) {
            $sql .= " AND (p.name LIKE ? OR p.brand LIKE ? OR p.description LIKE ?)";
            $searchTerm = "%{$searchQuery}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if ($sortBy === 'price-low') {
            $sql .= " ORDER BY p.price ASC";
        } elseif ($sortBy === 'price-high') {
            $sql .= " ORDER BY p.price DESC";
        } elseif ($sortBy === 'rating') {
            $sql .= " ORDER BY p.rating DESC, p.reviews_count DESC";
        } else {
            $sql .= " ORDER BY p.is_featured DESC, p.id DESC";
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rawProducts = $stmt->fetchAll();

        // Fetch colors for each product
        foreach ($rawProducts as $prod) {
            $colorStmt = $db->prepare("SELECT color_name, color_hex, is_default FROM product_colors WHERE product_id = ? ORDER BY is_default DESC");
            $colorStmt->execute([$prod['id']]);
            $prod['colors'] = $colorStmt->fetchAll();
            $products[] = $prod;
        }
    } catch (PDOException $e) {
        $products = [];
    }
}

// Fallback products if database has not been migrated yet
if (empty($products) && empty($searchQuery) && $selectedCategory === 'All') {
    // Advise migration
    $needsMigrationNotice = true;
}

// Fetch Reviews
$reviews = [];
if ($db) {
    try {
        $reviews = $db->query("SELECT * FROM reviews ORDER BY id ASC")->fetchAll();
    } catch (Exception $e) {
        $reviews = [];
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Flash Alerts -->
<?php if ($flashSuccess): ?>
  <div style="background: #000; color: #FFF; padding: 12px 20px; font-size: 13px; font-weight: 700; text-align: center; border-bottom: 1px solid #333;">
    ✓ <?= htmlspecialchars($flashSuccess) ?>
  </div>
<?php endif; ?>

<?php if ($flashError): ?>
  <div style="background: #FFF; color: #D00; padding: 12px 20px; font-size: 13px; font-weight: 700; text-align: center; border-bottom: 1px solid #D00;">
    ✕ <?= htmlspecialchars($flashError) ?>
  </div>
<?php endif; ?>

<?php if ($flashInfo): ?>
  <div style="background: #F4F4F5; color: #000; padding: 10px 20px; font-size: 12px; font-weight: 600; text-align: center; border-bottom: 1px solid #E4E4E7;">
    ℹ <?= htmlspecialchars($flashInfo) ?>
  </div>
<?php endif; ?>

<?php if (!empty($needsMigrationNotice)): ?>
  <div style="background: #111; color: #FFF; border-bottom: 1px solid #333; padding: 14px 20px; text-align: center; font-size: 12px;">
    <span>⚡ MySQL database needs initial setup.</span>
    <a href="setup.php?auto=1" class="btn btn-white" style="margin-left: 12px; padding: 5px 14px; font-size: 11px;">
      ⚡ 1-Click Auto Create MySQL Database
    </a>
  </div>
<?php endif; ?>

<!-- HERO SECTION: White & Black Luxury Monochrome -->
<section class="hero-section">
  <div class="container hero-grid">
    
    <div>
      <!-- Google Rating Badge -->
      <div class="hero-rating-pill">
        <span>★★★★★</span>
        <span><?= htmlspecialchars(STORE_RATING) ?></span>
        <span style="color: #71717A;">|</span>
        <span style="color: #52525B;"><?= htmlspecialchars(STORE_REVIEWS_COUNT) ?> · Chittagong</span>
      </div>

      <h1 class="hero-title">
        Perfection in Vision.<br>Italian Optics in Chittagong.
      </h1>

      <p class="hero-desc">
        Welcome to <strong><?= htmlspecialchars(SITE_NAME) ?></strong> at <strong><?= htmlspecialchars(STORE_ADDRESS) ?></strong>. Discover bespoke Italian acetate frames, aerospace titanium eyeglasses, polarized UV400 sunglasses, and precision prescription lenses.
      </p>

      <div class="hero-buttons">
        <a href="#showroom" class="btn btn-black" style="padding: 14px 28px;">
          Explore Eyewear Collection ↓
        </a>
        <a href="tel:<?= preg_replace('/[^0-9]/', '', STORE_PHONE) ?>" class="btn btn-white" style="padding: 14px 24px;">
          📞 Call: <?= htmlspecialchars(STORE_PHONE) ?>
        </a>
      </div>

      <div style="display: flex; gap: 20px; margin-top: 36px; padding-top: 24px; border-top: 1px solid var(--color-border); font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--color-mid-gray); flex-wrap: wrap;">
        <div>✓ Free Store Fitting</div>
        <div>✓ Computerized Lenses</div>
        <div>✓ Aerospace Titanium</div>
        <div>✓ <?= htmlspecialchars(STORE_HOURS) ?></div>
      </div>
    </div>
  </div>
</section>

<!-- STORE HIGHLIGHTS BAR -->
<section class="highlights-bar" id="about-store">
  <div class="container highlights-grid">
    <div class="highlight-item">
      <div class="highlight-title">📍 Jamal Khan Showroom</div>
      <div class="highlight-desc">12 Jamal Khan Road, Chattogram 4000. Open everyday until 10 PM.</div>
    </div>
    <div class="highlight-item">
      <div class="highlight-title">⭐ 5.0 Google Rating</div>
      <div class="highlight-desc">Verified 5.0 stars with authentic customer reviews across Chittagong.</div>
    </div>
    <div class="highlight-item">
      <div class="highlight-title">🎨 Color Selection</div>
      <div class="highlight-desc">Choose from matte black, silver chrome, tortoiseshell, and white hues.</div>
    </div>
    <div class="highlight-item">
      <div class="highlight-title">🚚 24H Chattogram Dispatch</div>
      <div class="highlight-desc">Ultrasonic frame spa and rapid delivery across Chattogram &amp; Bangladesh.</div>
    </div>
  </div>
</section>

<!-- SHOWROOM CATALOG SECTION -->
<section class="container" id="showroom" style="padding: 60px 20px;">
  
  <!-- Section Title & Search -->
  <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px; margin-bottom: 24px;">
    <div>
      <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-mid-gray);">
        Showroom Collection
      </span>
      <h2 style="font-size: 34px; font-weight: 700; margin-top: 4px;">
        Italian Frames & Designer Lenses
      </h2>
    </div>

    <!-- Search & Filters Form -->
    <form action="index.php#showroom" method="GET" style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
      <?php if ($selectedCategory !== 'All'): ?>
        <input type="hidden" name="category" value="<?= htmlspecialchars($selectedCategory) ?>">
      <?php endif; ?>
      <input
        type="text"
        name="search"
        value="<?= htmlspecialchars($searchQuery) ?>"
        placeholder="Search titanium, sunglasses..."
        class="form-input"
        style="padding: 8px 14px; font-size: 12px; width: 210px;"
      >
      <select name="gender" onchange="this.form.submit()" class="form-select" style="padding: 8px 10px; font-size: 12px; width: auto;">
        <option value="All" <?= $selectedGender === 'All' ? 'selected' : '' ?>>All Genders</option>
        <option value="Unisex" <?= $selectedGender === 'Unisex' ? 'selected' : '' ?>>Unisex</option>
        <option value="Men" <?= $selectedGender === 'Men' ? 'selected' : '' ?>>Men</option>
        <option value="Women" <?= $selectedGender === 'Women' ? 'selected' : '' ?>>Women</option>
        <option value="Kids" <?= $selectedGender === 'Kids' ? 'selected' : '' ?>>Kids</option>
      </select>
      <select name="shape" onchange="this.form.submit()" class="form-select" style="padding: 8px 10px; font-size: 12px; width: auto;">
        <option value="All" <?= $selectedShape === 'All' ? 'selected' : '' ?>>All Shapes</option>
        <option value="Round" <?= $selectedShape === 'Round' ? 'selected' : '' ?>>Round</option>
        <option value="Wayfarer" <?= $selectedShape === 'Wayfarer' ? 'selected' : '' ?>>Wayfarer</option>
        <option value="Square" <?= $selectedShape === 'Square' ? 'selected' : '' ?>>Square</option>
        <option value="Cat-Eye" <?= $selectedShape === 'Cat-Eye' ? 'selected' : '' ?>>Cat-Eye</option>
        <option value="Aviator" <?= $selectedShape === 'Aviator' ? 'selected' : '' ?>>Aviator</option>
        <option value="Rectangle" <?= $selectedShape === 'Rectangle' ? 'selected' : '' ?>>Rectangle</option>
      </select>
      <select name="sort" onchange="this.form.submit()" class="form-select" style="padding: 8px 10px; font-size: 12px; width: auto;">
        <option value="featured" <?= $sortBy === 'featured' ? 'selected' : '' ?>>Featured First</option>
        <option value="price-low" <?= $sortBy === 'price-low' ? 'selected' : '' ?>>Price: Low to High</option>
        <option value="price-high" <?= $sortBy === 'price-high' ? 'selected' : '' ?>>Price: High to Low</option>
        <option value="rating" <?= $sortBy === 'rating' ? 'selected' : '' ?>>Top Rated</option>
      </select>
      <button type="submit" class="btn btn-black" style="padding: 8px 14px; font-size: 11px;">
        Search
      </button>
    </form>
  </div>

  <!-- Category Filter Tabs -->
  <div class="filter-tabs">
    <a 
      href="index.php?category=All#showroom" 
      class="filter-tab <?= $selectedCategory === 'All' ? 'active' : '' ?>"
    >
      All Eyewear
    </a>
    <?php foreach ($categories as $cat): ?>
      <a 
        href="index.php?category=<?= urlencode($cat['name']) ?>#showroom" 
        class="filter-tab <?= ($selectedCategory === $cat['name'] || $selectedCategory === $cat['slug']) ? 'active' : '' ?>"
      >
        <?= htmlspecialchars($cat['name']) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- Active Filter Notification -->
  <?php if (!empty($searchQuery) || $selectedCategory !== 'All'): ?>
    <div style="display: flex; justify-content: space-between; align-items: center; background: #F8F8F8; border: 1px solid #EEE; padding: 10px 16px; border-radius: 4px; margin-bottom: 24px; font-size: 12px;">
      <div>
        <span>Active Filters:</span>
        <?php if ($selectedCategory !== 'All'): ?>
          <strong style="margin-left: 6px; background: #000; color: #FFF; padding: 2px 8px; border-radius: 2px; font-size: 10px; text-transform: uppercase;">Category: <?= htmlspecialchars($selectedCategory) ?></strong>
        <?php endif; ?>
        <?php if (!empty($searchQuery)): ?>
          <strong style="margin-left: 6px; background: #EEE; color: #000; padding: 2px 8px; border-radius: 2px; font-size: 10px;">Search: "<?= htmlspecialchars($searchQuery) ?>"</strong>
        <?php endif; ?>
      </div>
      <a href="index.php#showroom" style="color: #000; font-weight: 700; text-decoration: underline;">Clear Filter</a>
    </div>
  <?php endif; ?>

  <!-- Products Grid -->
  <?php if (empty($products)): ?>
    <div style="text-align: center; padding: 80px 20px; background: #FAFAFA; border: 1px dashed #DDD; border-radius: 4px;">
      <div style="font-size: 36px; margin-bottom: 10px;">🔍</div>
      <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 6px;">No eyewear matched your selection</h3>
      <p style="font-size: 13px; color: #777; margin-bottom: 16px;">Try searching with different keywords or reset to view all frames.</p>
      <a href="index.php#showroom" class="btn btn-black">View All Eyewear</a>
    </div>
  <?php else: ?>
    <div class="products-grid">
      <?php foreach ($products as $product): ?>
        <?php 
          $defaultColor = !empty($product['colors']) ? $product['colors'][0] : ['color_name' => 'Classic Black', 'color_hex' => '#000000'];
          $discount = !empty($product['original_price']) && $product['original_price'] > $product['price']
            ? round((($product['original_price'] - $product['price']) / $product['original_price']) * 100)
            : 0;
        ?>
        <div class="product-card" id="product-card-<?= $product['id'] ?>">
          
          <div class="card-img-wrap">
            <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="card-img" loading="lazy">
            
            <?php if ($discount > 0): ?>
              <div class="card-tag">SAVE <?= $discount ?>%</div>
            <?php elseif (!empty($product['is_featured'])): ?>
              <div class="card-tag">Featured</div>
            <?php endif; ?>

            <!-- Admin Quick Delete Shortcut if Admin -->
            <?php if (!empty($currentUser) && $currentUser['role'] === 'admin'): ?>
              <form action="admin/index.php" method="POST" onsubmit="return confirm('Remove \'<?= addslashes($product['name']) ?>\' from store?');" style="position: absolute; top: 10px; right: 10px; z-index: 10;">
                <input type="hidden" name="action" value="delete_product">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <button type="submit" style="background: #000; color: #FFF; border: 1px solid #FFF; border-radius: 4px; padding: 4px 8px; font-size: 10px; font-weight: 700; cursor: pointer;" title="Delete item (Admin)">
                  Delete ✕
                </button>
              </form>
            <?php endif; ?>
          </div>

          <div class="card-body">
            <div>
              <div class="card-brand"><?= htmlspecialchars($product['brand']) ?> · <?= htmlspecialchars($product['category_name'] ?? 'Eyewear') ?></div>
              <h3 class="card-title"><?= htmlspecialchars($product['name']) ?></h3>
              <div style="font-size: 11px; color: #777;">
                ★ <?= htmlspecialchars($product['rating']) ?> (<?= htmlspecialchars($product['reviews_count']) ?> reviews)
              </div>
            </div>

            <!-- Choose Color Swatches -->
            <div class="swatch-group">
              <div class="swatch-label">
                <span>Color:</span>
                <strong class="selected-color-name"><?= htmlspecialchars($defaultColor['color_name']) ?></strong>
              </div>
              <div class="swatches-list">
                <?php if (!empty($product['colors'])): ?>
                  <?php foreach ($product['colors'] as $cIdx => $col): ?>
                    <button 
                      type="button" 
                      class="swatch-btn <?= $cIdx === 0 ? 'active' : '' ?>" 
                      style="background-color: <?= htmlspecialchars($col['color_hex']) ?>;"
                      data-color-name="<?= htmlspecialchars($col['color_name']) ?>"
                      data-color-hex="<?= htmlspecialchars($col['color_hex']) ?>"
                      title="<?= htmlspecialchars($col['color_name']) ?>"
                    ></button>
                  <?php endforeach; ?>
                <?php else: ?>
                  <button type="button" class="swatch-btn active" style="background-color: #000000;" data-color-name="Matte Black" data-color-hex="#000000"></button>
                <?php endif; ?>
              </div>
            </div>

            <div class="card-footer">
              <div>
                <span class="card-price">৳<?= number_format($product['price']) ?></span>
                <?php if (!empty($product['original_price'])): ?>
                  <span class="card-original-price">৳<?= number_format($product['original_price']) ?></span>
                <?php endif; ?>
              </div>

              <!-- Add to Cart with Selected Color -->
              <button 
                type="button" 
                class="btn btn-black btn-add-to-cart"
                data-product-id="<?= $product['id'] ?>"
                data-product-name="<?= htmlspecialchars($product['name']) ?>"
                data-price="<?= $product['price'] ?>"
                data-image="<?= htmlspecialchars($product['image']) ?>"
                data-chosen-color="<?= htmlspecialchars($defaultColor['color_name']) ?>"
                data-chosen-hex="<?= htmlspecialchars($defaultColor['color_hex']) ?>"
                style="padding: 8px 14px; font-size: 11px;"
              >
                + Add to Cart
              </button>
            </div>

          </div>

        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</section>

<!-- GOOGLE REVIEWS SECTION (5.0 Stars) -->
<section style="background-color: #FAFAFA; border-top: 1px solid var(--color-border); border-bottom: 1px solid var(--color-border); padding: 70px 0;">
  <div class="container">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px; margin-bottom: 40px;">
      <div>
        <div style="display: inline-flex; align-items: center; gap: 8px; background: #000; color: #FFF; padding: 4px 12px; border-radius: 9999px; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 10px;">
          ★ 5.0 Google Business Rating
        </div>
        <h2 style="font-size: 32px; font-weight: 700; margin: 0;">Verified Patron Feedback</h2>
        <p style="font-size: 13px; color: var(--color-mid-gray); margin-top: 4px;">
          Reviews from customers visiting our 12 Jamal Khan Road, Chattogram showroom
        </p>
      </div>

      <a 
        href="https://www.google.com/search?q=italy+optics+chittagong" 
        target="_blank" 
        rel="noreferrer" 
        class="btn btn-black"
        style="font-size: 11px;"
      >
        View on Google Maps →
      </a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
      <?php foreach ($reviews as $rev): ?>
        <div style="background: #FFF; border: 1px solid var(--color-border); border-radius: 4px; padding: 24px; display: flex; flex-direction: column; justify-content: space-between;">
          <div>
            <div style="color: #000; font-size: 14px; margin-bottom: 10px; font-weight: bold;">
              ★★★★★
            </div>
            <p style="font-size: 13px; line-height: 1.6; color: #333; font-style: italic; margin-bottom: 18px;">
              "<?= htmlspecialchars($rev['review_text']) ?>"
            </p>
          </div>
          <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #EEE; padding-top: 12px; font-size: 11px;">
            <div>
              <strong style="font-size: 12px; color: #000;"><?= htmlspecialchars($rev['reviewer_name']) ?></strong>
              <div style="color: #777;"><?= htmlspecialchars($rev['location']) ?></div>
            </div>
            <span style="background: #000; color: #FFF; padding: 2px 6px; border-radius: 2px; font-size: 9px; font-weight: 800; text-transform: uppercase;">
              VERIFIED
            </span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<!-- MULTI-CHANNEL CONTACT SECTION: Phone, Email, Facebook -->
<section class="contact-section" id="contact">
  <div class="container">
    
    <div style="text-align: center; max-width: 600px; margin: 0 auto 50px;">
      <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-mid-gray);">
        Direct Communication
      </span>
      <h2 style="font-size: 36px; font-weight: 700; margin-top: 6px;">
        Contact Italy Optics
      </h2>
      <p style="font-size: 14px; color: var(--color-mid-gray); margin-top: 6px;">
        Reach our optical specialists via Phone, Email, Facebook, or visit our showroom in Jamal Khan.
      </p>
    </div>

    <div class="contact-grid">
      
      <!-- Left Column: Direct Channels -->
      <div>
        
        <!-- Phone Contact Card -->
        <a href="tel:<?= preg_replace('/[^0-9]/', '', STORE_PHONE) ?>" class="contact-card" style="display: block;">
          <div style="display: flex; align-items: flex-start; gap: 16px;">
            <div style="width: 44px; height: 44px; background: #000; color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 20px; border-radius: 4px; shrink: 0;">📞</div>
            <div style="flex: 1;">
              <div style="display: flex; justify-content: space-between; font-size: 11px; color: #777; text-transform: uppercase; font-weight: 700;">
                <span>Phone Consultation</span>
                <span style="color: #000;">Click to Call</span>
              </div>
              <div style="font-size: 20px; font-weight: 800; color: #000; margin: 4px 0;">
                <?= htmlspecialchars(STORE_PHONE) ?>
              </div>
              <div style="font-size: 12px; color: #666;">
                Available everyday · Open until 10 PM
              </div>
            </div>
          </div>
        </a>

        <!-- Email Contact Card -->
        <a href="mailto:<?= htmlspecialchars(STORE_EMAIL) ?>" class="contact-card" style="display: block;">
          <div style="display: flex; align-items: flex-start; gap: 16px;">
            <div style="width: 44px; height: 44px; background: #000; color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 20px; border-radius: 4px; shrink: 0;">✉️</div>
            <div style="flex: 1;">
              <div style="display: flex; justify-content: space-between; font-size: 11px; color: #777; text-transform: uppercase; font-weight: 700;">
                <span>Email Support</span>
                <span style="color: #000;">Send Mail</span>
              </div>
              <div style="font-size: 18px; font-weight: 800; color: #000; margin: 4px 0;">
                <?= htmlspecialchars(STORE_EMAIL) ?>
              </div>
              <div style="font-size: 12px; color: #666;">
                Send your eye doctor's prescription for quotation
              </div>
            </div>
          </div>
        </a>

        <!-- Facebook Contact Card -->
        <a href="<?= htmlspecialchars(FACEBOOK_URL) ?>" target="_blank" rel="noreferrer" class="contact-card" style="display: block;">
          <div style="display: flex; align-items: flex-start; gap: 16px;">
            <div style="width: 44px; height: 44px; background: #000; color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 20px; border-radius: 4px; shrink: 0;">🌐</div>
            <div style="flex: 1;">
              <div style="display: flex; justify-content: space-between; font-size: 11px; color: #777; text-transform: uppercase; font-weight: 700;">
                <span>Facebook & Messenger</span>
                <span style="color: #000;">Visit Page →</span>
              </div>
              <div style="font-size: 18px; font-weight: 800; color: #000; margin: 4px 0;">
                facebook.com/italyoptics.ctg
              </div>
              <div style="font-size: 12px; color: #666;">
                Message us directly on Facebook Messenger
              </div>
            </div>
          </div>
        </a>

        <!-- Location & Hours Box -->
        <div class="contact-card" style="background: #FAFAFA;">
          <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">
            Showroom Location
          </div>
          <div style="font-size: 13px; font-weight: 700; margin-bottom: 4px;">
            <?= htmlspecialchars(STORE_ADDRESS) ?>
          </div>
          <div style="font-size: 12px; color: #666; margin-bottom: 12px;">
            (<?= htmlspecialchars(STORE_ADDRESS_EN) ?>) · <?= htmlspecialchars(STORE_HOURS) ?>
          </div>
          <a 
            href="https://www.google.com/search?q=italy+optics+chittagong" 
            target="_blank" 
            rel="noreferrer" 
            class="btn btn-black"
            style="width: 100%; text-align: center; font-size: 11px;"
          >
            Google Maps Directions →
          </a>
        </div>

      </div>

      <!-- Right Column: Interactive Contact Form -->
      <div style="background: #FFF; border: 1px solid var(--color-border); padding: 32px; border-radius: 4px;">
        <h3 style="font-family: var(--font-serif); font-size: 24px; font-weight: 700; margin-bottom: 6px;">
          Send an Inquiry
        </h3>
        <p style="font-size: 12px; color: var(--color-mid-gray); margin-bottom: 24px;">
          Our optical specialists at Jamal Khan will review your message and reply promptly.
        </p>

        <form action="index.php#contact" method="POST">
          <input type="hidden" name="action" value="submit_contact">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            <div class="form-group">
              <label class="form-label">Your Name *</label>
              <input type="text" name="contact_name" required class="form-input" placeholder="e.g. Mahfuzul Alam">
            </div>
            <div class="form-group">
              <label class="form-label">Phone Number *</label>
              <input type="tel" name="contact_phone" required class="form-input" placeholder="01XXXXXXXXX">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Email Address *</label>
            <input type="email" name="contact_email" required class="form-input" placeholder="name@email.com">
          </div>

          <div class="form-group">
            <label class="form-label">Topic</label>
            <select name="contact_topic" class="form-select">
              <option value="Prescription Lens Fitting">Prescription Lens Fitting in Jamal Khan</option>
              <option value="Italian Frame Inquiries">Italian Frame Collection Inquiry</option>
              <option value="Computer Blue Light Protection">Computer Blue Light Glasses</option>
              <option value="Contact Lenses Care">Contact Lenses &amp; Solution Stock</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Your Message or Prescription Details *</label>
            <textarea name="contact_message" rows="3" required class="form-textarea" placeholder="Detail your frame preference, power requirement, or question..."></textarea>
          </div>

          <button type="submit" class="btn btn-black" style="width: 100%; padding: 12px;">
            Send Message to Italy Optics
          </button>
        </form>
      </div>

    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
