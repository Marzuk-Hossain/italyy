<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

// Check admin role
$currentUser = $_SESSION['user'] ?? null;
if (empty($currentUser) || $currentUser['role'] !== 'admin') {
    // If not logged in as admin, require login
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'admin_login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $db = getDB();
        
        $stmt = $db ? $db->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(?)") : null;
        if ($stmt) {
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user && $user['role'] === 'admin' && password_verify($password, $user['password_hash'])) {
                $_SESSION['user'] = [
                    'id' => (int)$user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'phone' => $user['phone'] ?? '',
                    'role' => 'admin'
                ];
                $_SESSION['admin_success'] = "Welcome back, Store Administrator!";
                header("Location: index.php");
                exit;
            }
        }
        $loginError = "Authentication failed: Incorrect admin email or password.";
    }
    
    // Render Admin Login Form
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Admin Login | <?= htmlspecialchars(SITE_NAME) ?></title>
      <link rel="stylesheet" href="../assets/css/style.css">
      <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@700&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
      <style>
        body { background: #000; color: #FFF; font-family: 'Plus Jakarta Sans', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .login-box { max-width: 420px; width: 100%; background: #111; border: 1px solid #333; border-radius: 8px; padding: 32px; box-shadow: 0 20px 40px rgba(0,0,0,0.8); }
      </style>
    </head>
    <body>
      <div class="login-box">
        <div style="text-align: center; margin-bottom: 24px;">
          <div style="font-size: 32px; margin-bottom: 8px;">🛡️</div>
          <h1 style="font-family: 'Cormorant Garamond', serif; font-size: 28px; font-weight: 700; margin: 0;">Italy Optics Admin</h1>
          <p style="font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 0.08em; margin-top: 4px;">12 Jamal Khan Road, Chattogram</p>
        </div>

        <?php if (!empty($loginError)): ?>
          <div style="background: #2A0000; color: #FF6B6B; border: 1px solid #D00; padding: 10px 14px; border-radius: 4px; font-size: 12px; font-weight: 600; margin-bottom: 16px;">
            ✕ <?= htmlspecialchars($loginError) ?>
          </div>
        <?php endif; ?>

        <form action="index.php" method="POST" style="display: flex; flex-direction: column; gap: 14px;">
          <input type="hidden" name="action" value="admin_login">
          
          <div class="form-group">
            <label class="form-label" style="color: #FFF;">Admin Email Address</label>
            <input type="email" name="email" required class="form-input" value="admin@italyoptics.com" style="background: #000; border-color: #333; color: #FFF;">
          </div>

          <div class="form-group">
            <label class="form-label" style="color: #FFF;">Admin Password</label>
            <input type="password" name="password" required class="form-input" placeholder="••••••••" style="background: #000; border-color: #333; color: #FFF;">
          </div>

          <button type="submit" class="btn btn-white" style="width: 100%; padding: 12px; font-size: 12px; margin-top: 6px;">
            Verify Admin Password & Log In
          </button>
        </form>

        <div style="margin-top: 20px; text-align: center;">
          <a href="../index.php" style="font-size: 11px; color: #888; text-decoration: underline;">
            ← Return to Italy Optics Showroom
          </a>
        </div>
      </div>
    </body>
    </html>
    <?php
    exit;
}

$db = getDB();
$flashSuccess = $_SESSION['admin_success'] ?? null;
$flashError = $_SESSION['admin_error'] ?? null;
unset($_SESSION['admin_success'], $_SESSION['admin_error']);

// Handle Action: Toggle In-Stock / Pre-Order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_stock') {
    $productId = (int)($_POST['product_id'] ?? 0);
    if ($productId > 0 && $db) {
        try {
            $stmt = $db->prepare("UPDATE products SET in_stock = IF(in_stock = 1, 0, 1) WHERE id = ?");
            $stmt->execute([$productId]);
            $_SESSION['admin_success'] = "Stock status updated for item #{$productId}.";
        } catch (PDOException $e) {
            $_SESSION['admin_error'] = "Could not toggle stock: " . $e->getMessage();
        }
    }
    header("Location: index.php");
    exit;
}

// Handle Action: Delete Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_product') {
    $productId = (int)($_POST['product_id'] ?? 0);
    if ($productId > 0 && $db) {
        try {
            $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $_SESSION['admin_success'] = "Eyewear item #{$productId} removed from showroom inventory successfully.";
        } catch (PDOException $e) {
            $_SESSION['admin_error'] = "Could not delete item: " . $e->getMessage();
        }
    }
    header("Location: index.php");
    exit;
}

// Handle Action: Update Product Price
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_price') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $newPrice = (float)($_POST['new_price'] ?? 0);
    $newOrigPrice = !empty($_POST['new_original_price']) ? (float)$_POST['new_original_price'] : null;

    if ($productId > 0 && $newPrice > 0 && $db) {
        try {
            $stmt = $db->prepare("UPDATE products SET price = ?, original_price = ? WHERE id = ?");
            $stmt->execute([$newPrice, $newOrigPrice, $productId]);
            $_SESSION['admin_success'] = "Price for item #{$productId} updated to ৳" . number_format($newPrice) . " successfully!";
        } catch (PDOException $e) {
            $_SESSION['admin_error'] = "Could not update price: " . $e->getMessage();
        }
    }
    header("Location: index.php");
    exit;
}

// Handle Action: Add New Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_product') {
    $name = trim($_POST['name'] ?? '');
    $brand = trim($_POST['brand'] ?? 'Italy Optics Heritage');
    $categoryId = (int)($_POST['category_id'] ?? 1);
    $price = (float)($_POST['price'] ?? 0);
    $originalPrice = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : ($price * 1.25);
    $image = trim($_POST['image'] ?? 'https://images.unsplash.com/photo-1591076482161-42ce6da69f68?auto=format&fit=crop&w=800&q=80');
    $description = trim($_POST['description'] ?? '');
    $features = trim($_POST['features'] ?? 'Handmade Italian Acetate|Anti-reflective Lenses|2-Year Guarantee');
    $gender = $_POST['gender'] ?? 'Unisex';
    $shape = $_POST['shape'] ?? 'Classic';
    $inStock = isset($_POST['in_stock']) ? 1 : 0;
    $colorsInput = trim($_POST['colors'] ?? 'Matte Black:#000000, Silver Slate:#777777, Pure White:#FFFFFF');

    if (!empty($name) && $price > 0 && $db) {
        try {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name))) . '-' . time();
            $stmt = $db->prepare("INSERT INTO products 
                (name, slug, brand, category_id, price, original_price, rating, reviews_count, image, description, features, gender, shape, in_stock, is_featured)
                VALUES (?, ?, ?, ?, ?, ?, 5.0, 1, ?, ?, ?, ?, ?, ?, 1)");
            $stmt->execute([
                $name, $slug, $brand, $categoryId, $price, $originalPrice, $image, $description, $features, $gender, $shape, $inStock
            ]);
            $newProductId = $db->lastInsertId();

            // Insert Colors
            $colorPairs = explode(',', $colorsInput);
            $colorStmt = $db->prepare("INSERT INTO product_colors (product_id, color_name, color_hex, is_default) VALUES (?, ?, ?, ?)");
            $isFirst = 1;
            foreach ($colorPairs as $cp) {
                $parts = explode(':', trim($cp));
                $cName = trim($parts[0] ?? 'Classic');
                $cHex = trim($parts[1] ?? '#000000');
                if (!empty($cName)) {
                    $colorStmt->execute([$newProductId, $cName, $cHex, $isFirst]);
                    $isFirst = 0;
                }
            }

            $_SESSION['admin_success'] = "New Eyewear item '{$name}' published to Italy Optics showroom!";
        } catch (PDOException $e) {
            $_SESSION['admin_error'] = "Error saving product: " . $e->getMessage();
        }
    } else {
        $_SESSION['admin_error'] = "Product name and selling price are required.";
    }
    header("Location: index.php");
    exit;
}

// Fetch categories & products
$categories = [];
$products = [];
if ($db) {
    try {
        $categories = $db->query("SELECT * FROM categories ORDER BY display_order ASC")->fetchAll();
        $prodQuery = $db->query("
            SELECT p.*, c.name as category_name,
            GROUP_CONCAT(CONCAT(pc.color_name, ':', pc.color_hex) SEPARATOR ', ') as color_list
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_colors pc ON p.id = pc.product_id
            GROUP BY p.id
            ORDER BY p.id DESC
        ");
        $products = $prodQuery->fetchAll();
    } catch (PDOException $e) {
        $categories = [];
        $products = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Inventory Control | <?= htmlspecialchars(SITE_NAME) ?></title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@700&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style>
    body { background-color: #FAFAFA; color: #000000; font-family: 'Plus Jakarta Sans', sans-serif; }
    .admin-header { background: #000000; color: #FFFFFF; padding: 24px 0; border-bottom: 2px solid #222222; }
    .admin-container { max-width: 1200px; margin: 32px auto; padding: 0 20px; }
    .admin-grid { display: grid; grid-template-columns: 1fr; gap: 32px; }
    @media (min-width: 900px) { .admin-grid { grid-template-columns: 380px 1fr; } }
    .admin-card { background: #FFFFFF; border: 1px solid #E5E5E5; border-radius: 8px; padding: 28px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
    .badge-stock { display: inline-block; font-size: 10px; font-weight: 800; text-transform: uppercase; padding: 2px 6px; border-radius: 4px; }
    .badge-in { background: #000; color: #FFF; }
    .badge-out { background: #EEE; color: #777; border: 1px solid #DDD; }
    .table-prod { width: 100%; border-collapse: collapse; font-size: 12px; }
    .table-prod th { text-align: left; padding: 12px 10px; background: #000; color: #FFF; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; }
    .table-prod td { padding: 12px 10px; border-bottom: 1px solid #EEE; vertical-align: middle; }
    .table-prod tr:hover td { background-color: #F8F8F8; }
  </style>
</head>
<body>

<!-- Admin Header -->
<div class="admin-header">
  <div class="container" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
    <div style="display: flex; align-items: center; gap: 12px;">
      <div style="width: 36px; height: 36px; background: #FFF; color: #000; display: flex; align-items: center; justify-content: center; font-size: 18px; border-radius: 4px;">🛡️</div>
      <div>
        <h1 style="font-family: 'Cormorant Garamond', serif; font-size: 26px; font-weight: 700; color: #FFF; margin: 0; line-height: 1;">
          Italy Optics · Store Administration
        </h1>
        <div style="font-size: 11px; color: #AAA; text-transform: uppercase; letter-spacing: 0.06em; margin-top: 4px;">
          12 Jamal Khan Road, Chattogram · Add & Remove Eyewear Inventory
        </div>
      </div>
    </div>

    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
      <a href="../setup.php?download_sql=1" class="btn btn-white" style="font-size: 11px; padding: 8px 14px;">
        ⬇ Export .SQL
      </a>
      <a href="../setup.php" class="btn btn-white" style="font-size: 11px; padding: 8px 14px;">
        ⚡ Auto MySQL Setup
      </a>
      <a href="../index.php" class="btn btn-white" style="font-size: 11px; padding: 8px 14px;">
        👁️ View Showroom
      </a>
      <a href="../logout.php" class="btn" style="background: #222; color: #FFF; font-size: 11px; padding: 8px 14px;">
        Logout
      </a>
    </div>
  </div>
</div>

<div class="admin-container">

  <?php if ($flashSuccess): ?>
    <div style="background: #000; color: #FFF; border: 1px solid #333; padding: 14px 18px; border-radius: 4px; margin-bottom: 24px; font-size: 13px; font-weight: 600;">
      ✓ <?= htmlspecialchars($flashSuccess) ?>
    </div>
  <?php endif; ?>

  <?php if ($flashError): ?>
    <div style="background: #FFF; color: #D00; border: 1px solid #D00; padding: 14px 18px; border-radius: 4px; margin-bottom: 24px; font-size: 13px; font-weight: 600;">
      ✕ <?= htmlspecialchars($flashError) ?>
    </div>
  <?php endif; ?>

  <div class="admin-grid">
    
    <!-- Left Column: Add New Eyewear Item Form -->
    <div class="admin-card">
      <div style="border-bottom: 1px solid #000; padding-bottom: 12px; margin-bottom: 20px;">
        <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 700; margin: 0;">
          + Add New Eyewear Item
        </h2>
        <p style="font-size: 11px; color: #666; margin-top: 2px;">
          Adds instantly to MySQL database and live store
        </p>
      </div>

      <form action="index.php" method="POST">
        <input type="hidden" name="action" value="add_product">

        <div class="form-group">
          <label class="form-label">Eyewear Title / Frame Name *</label>
          <input type="text" name="name" required class="form-input" placeholder="e.g. Firenze Minimalist Titanium">
        </div>

        <div class="form-group">
          <label class="form-label">Brand / Label *</label>
          <input type="text" name="brand" value="Italy Optics Heritage" required class="form-input">
        </div>

        <div class="form-group">
          <label class="form-label">Section / Category *</label>
          <select name="category_id" required class="form-select">
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
          <div class="form-group">
            <label class="form-label">Price (৳ BDT) *</label>
            <input type="number" name="price" step="50" required class="form-input" placeholder="3800">
          </div>
          <div class="form-group">
            <label class="form-label">Original Price (৳)</label>
            <input type="number" name="original_price" step="50" class="form-input" placeholder="4800">
          </div>
        </div>

        <!-- ========== PRODUCT IMAGE (Local Upload & Gallery) ========== -->
        <div class="form-group" style="border: 1px dashed #D5C9A6; border-radius: 8px; padding: 20px; background: #FDFBF5;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <label class="form-label" style="margin-bottom: 0; font-size: 13px;">Product Image (Local Upload & Gallery)</label>
            <span style="font-size: 10px; color: #666; font-weight: 600;">✓ Supports local file upload & presets</span>
          </div>

          <input type="hidden" name="image" id="adminImgInput" value="https://images.unsplash.com/photo-1591076482161-42ce6da69f68?auto=format&fit=crop&w=800&q=80">

          <div style="display: grid; grid-template-columns: 140px 1fr; gap: 20px; align-items: start;">
            <!-- Image Preview Box -->
            <div>
              <div id="imgPreviewBox" style="width: 140px; height: 120px; border: 2px dashed #000; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: #FFF; cursor: pointer; position: relative;" onclick="document.getElementById('localFileInput').click()" title="Click to upload from computer">
                <img id="imgPreview" src="https://images.unsplash.com/photo-1591076482161-42ce6da69f68?auto=format&fit=crop&w=800&q=80" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;" onerror="handleImageError(this)">
                <div id="imgPlaceholder" style="display: none; text-align: center; color: #000; font-size: 11px; padding: 8px;">
                  <div style="font-size: 26px; margin-bottom: 4px;">👓</div>
                  <strong>Upload Image</strong>
                  <div style="font-size: 9px; color: #666;">or drag file here</div>
                </div>
              </div>
              <button type="button" onclick="clearSelectedImage()" style="margin-top: 6px; width: 100%; font-size: 11px; padding: 4px 8px; background: #F5F5F5; border: 1px solid #CCC; border-radius: 4px; cursor: pointer; color: #444;">
                ✕ Clear Image
              </button>
            </div>

            <div>
              <!-- Upload from Local Computer -->
              <div style="margin-bottom: 14px;">
                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #111; margin-bottom: 8px;">Upload From Local Computer</div>
                <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                  <label id="localUploadBtn" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border: 1px solid #000; border-radius: 6px; background: #000; color: #FFF; cursor: pointer; font-size: 12px; font-weight: 600; transition: all 0.2s;">
                    <span style="font-size: 15px;">📁</span> Choose Local Image File...
                    <input type="file" id="localFileInput" accept="image/*" style="display: none;" onchange="handleLocalUpload(this)">
                  </label>
                  <span style="font-size: 11px; color: #666;">PNG, JPG, WebP up to 10MB</span>
                </div>
                <div style="font-size: 11px; color: #666; margin-top: 6px;">Converts your local computer image to high-fidelity storage with instant preview.</div>
              </div>

              <!-- Preset Gallery with Solid Fallbacks -->
              <div>
                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #111; margin-bottom: 8px;">Or Select From Italy Optics Studio Presets:</div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                  <div class="preset-card active-preset" onclick="selectPreset('https://images.unsplash.com/photo-1591076482161-42ce6da69f68?auto=format&fit=crop&w=800&q=80', this, 'Italian Pilot Aviator (Sunglasses)')" style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border: 2px solid #000; border-radius: 6px; background: #FFF; cursor: pointer; font-size: 11px; font-weight: 600; transition: all 0.2s;">
                    <span style="font-size: 20px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; background: #F4F4F5; border-radius: 4px;">🕶️</span>
                    <span>Italian Pilot Aviator (Sunglasses)</span>
                  </div>
                  <div class="preset-card" onclick="selectPreset('https://images.unsplash.com/photo-1574258495973-f010dfbb5371?auto=format&fit=crop&w=800&q=80', this, 'Amber Acetate Spectacles (Eyeglasses)')" style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border: 1px solid #DDD; border-radius: 6px; background: #FFF; cursor: pointer; font-size: 11px; font-weight: 600; transition: all 0.2s;">
                    <span style="font-size: 20px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; background: #F4F4F5; border-radius: 4px;">👓</span>
                    <span>Amber Acetate Spectacles...</span>
                  </div>
                  <div class="preset-card" onclick="selectPreset('https://images.unsplash.com/photo-1587854692152-cbe660dbde88?auto=format&fit=crop&w=800&q=80', this, 'HydraComfort (Contact Lenses)')" style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border: 1px solid #DDD; border-radius: 6px; background: #FFF; cursor: pointer; font-size: 11px; font-weight: 600; transition: all 0.2s;">
                    <span style="font-size: 20px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; background: #F4F4F5; border-radius: 4px;">💧</span>
                    <span>Blister Pack (Contact Lenses)</span>
                  </div>
                  <div class="preset-card" onclick="selectPreset('https://images.unsplash.com/photo-1508296695146-257a814070b4?auto=format&fit=crop&w=800&q=80', this, 'Bespoke Atelier Cat-Eye')" style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border: 1px solid #DDD; border-radius: 6px; background: #FFF; cursor: pointer; font-size: 11px; font-weight: 600; transition: all 0.2s;">
                    <span style="font-size: 20px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; background: #F4F4F5; border-radius: 4px;">✨</span>
                    <span>Bespoke Atelier Eyewear</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div id="imgSelectedLabel" style="font-size: 11px; color: #555; margin-top: 10px; font-weight: 600;">Current Image Selected: Italian Pilot Aviator</div>
        </div>

        <!-- ========== COLOR OPTIONS & FINISHES (Visual Picker) ========== -->
        <div class="form-group" style="border: 1px solid #E5E5E5; border-radius: 8px; padding: 18px; background: #FAFAFA; margin-bottom: 18px;">
          <div style="font-family: 'Cormorant Garamond', serif; font-size: 18px; font-weight: 700; color: #111; padding-bottom: 8px; border-bottom: 1px solid #E5E5E5; margin-bottom: 14px;">
            Color Options &amp; Finishes
          </div>
          <input type="hidden" name="colors" id="colorsHiddenInput" value="Obsidian Black:#111111">

          <!-- Color Chips / Tags -->
          <div id="colorChipsList" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px;">
            <div class="color-chip" data-name="Obsidian Black" data-hex="#111111" style="display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border-radius: 6px; background: #FFFFFF; border: 1px solid #D4D4D4; font-size: 12px; font-weight: 500; color: #111;">
              <span style="width: 14px; height: 14px; border-radius: 50%; background: #111111; border: 1px solid #CCC; display: inline-block;"></span>
              Obsidian Black
              <span onclick="removeColorChip(this.parentElement)" style="cursor: pointer; color: #888; font-size: 14px; margin-left: 2px;">&times;</span>
            </div>
          </div>

          <!-- Color Picker Row -->
          <div style="display: flex; gap: 8px; align-items: center;">
            <input type="text" id="newColorName" onkeydown="if(event.key==='Enter'){event.preventDefault();addColorChip();}" placeholder="New color name (e.g. Brushed Gold)" style="flex: 1; min-width: 0; padding: 9px 12px; font-size: 12px; border: 1px solid #D4D4D4; border-radius: 6px; background: #FFFFFF;">
            <input type="color" id="newColorHex" value="#D4AF37" style="width: 40px; height: 40px; border: 1px solid #D4D4D4; border-radius: 6px; cursor: pointer; padding: 3px; background: #FFFFFF; flex-shrink: 0;">
            <button type="button" onclick="addColorChip()" style="padding: 10px 16px; background: #000000; color: #FFFFFF; border: none; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer; flex-shrink: 0;">
              Add Color
            </button>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" rows="2" class="form-textarea" placeholder="Crafted with Italian aesthetic precision and aerospace-grade lightness..."></textarea>
        </div>

        <div style="margin-bottom: 20px;">
          <label style="display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 700; cursor: pointer;">
            <input type="checkbox" name="in_stock" value="1" checked style="accent-color: #000;">
            Mark as In Stock in Jamal Khan Store
          </label>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 8px;">
          <button type="button" onclick="if(confirm('Discard all changes?')) location.reload();" style="flex: 1; padding: 12px; background: #FFF; color: #333; border: 1px solid #DDD; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; text-transform: uppercase; letter-spacing: 0.04em;">
            Cancel
          </button>
          <button type="submit" style="flex: 2; padding: 12px; background: #1B7A3D; color: #FFF; border: none; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer; text-transform: uppercase; letter-spacing: 0.04em; display: flex; align-items: center; justify-content: center; gap: 8px;">
            <span style="font-size: 16px;">✓</span> Publish Product to Store
          </button>
        </div>
      </form>
    </div>

    <!-- Right Column: Current Inventory List with Delete Buttons -->
    <div class="admin-card">
      <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #000; padding-bottom: 12px; margin-bottom: 20px;">
        <div>
          <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 700; margin: 0;">
            Live Showroom Eyewear (<?= count($products) ?> Items)
          </h2>
          <p style="font-size: 11px; color: #666; margin-top: 2px;">
            Remove discontinued items or update stock availability
          </p>
        </div>
      </div>

      <?php if (empty($products)): ?>
        <div style="text-align: center; padding: 40px; color: #888;">
          <p style="font-weight: 700; color: #000;">No products in database.</p>
          <p style="font-size: 12px; margin-top: 6px;">Click the button below to auto-seed initial Italian collection.</p>
          <a href="../setup.php" class="btn btn-black" style="margin-top: 14px;">Run Auto Migration</a>
        </div>
      <?php else: ?>
        <div style="overflow-x: auto;">
          <table class="table-prod">
            <thead>
              <tr>
                <th style="width: 50px;">Item</th>
                <th>Frame Details</th>
                <th>Category</th>
                <th>Price</th>
                <th>Colors</th>
                <th>Stock</th>
                <th style="text-align: right;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($products as $prod): ?>
                <tr>
                  <td>
                    <img src="<?= htmlspecialchars($prod['image']) ?>" alt="" style="width: 44px; height: 44px; object-fit: cover; border-radius: 4px; border: 1px solid #E5E5E5;">
                  </td>
                  <td>
                    <strong style="font-size: 13px; color: #000;"><?= htmlspecialchars($prod['name']) ?></strong>
                    <div style="font-size: 10px; color: #777; text-transform: uppercase;"><?= htmlspecialchars($prod['brand']) ?></div>
                  </td>
                  <td>
                    <span style="border: 1px solid #DDD; padding: 2px 6px; border-radius: 2px; font-size: 10px; text-transform: uppercase;">
                      <?= htmlspecialchars($prod['category_name'] ?? 'Eyewear') ?>
                    </span>
                  </td>
                  <td>
                    <div style="display: flex; align-items: baseline; gap: 4px;">
                      <strong style="font-size: 13px;">৳<?= number_format($prod['price']) ?></strong>
                      <?php if (!empty($prod['original_price'])): ?>
                        <span style="font-size: 10px; color: #AAA; text-decoration: line-through;">৳<?= number_format($prod['original_price']) ?></span>
                      <?php endif; ?>
                    </div>
                    
                    <!-- Quick Price Change Form -->
                    <details style="margin-top: 4px; font-size: 11px;">
                      <summary style="cursor: pointer; color: #000; text-decoration: underline; font-weight: 600;">Edit Price ✏️</summary>
                      <form action="index.php" method="POST" style="margin-top: 6px; display: flex; flex-direction: column; gap: 4px; background: #F8F8F8; padding: 6px; border: 1px solid #DDD; border-radius: 4px;">
                        <input type="hidden" name="action" value="update_price">
                        <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                        <div style="display: flex; gap: 4px;">
                          <input type="number" name="new_price" value="<?= $prod['price'] ?>" step="50" required placeholder="New Price" style="width: 75px; padding: 2px 4px; font-size: 11px; border: 1px solid #CCC; border-radius: 2px;">
                          <input type="number" name="new_original_price" value="<?= $prod['original_price'] ?>" step="50" placeholder="Old Price" style="width: 75px; padding: 2px 4px; font-size: 11px; border: 1px solid #CCC; border-radius: 2px;">
                          <button type="submit" class="btn btn-black" style="padding: 2px 6px; font-size: 10px;">Save</button>
                        </div>
                      </form>
                    </details>
                  </td>
                  <td style="max-width: 140px; font-size: 11px; color: #555;">
                    <?= htmlspecialchars($prod['color_list'] ?? 'Standard Black') ?>
                  </td>
                  <td>
                    <form action="index.php" method="POST" style="display: inline-block;">
                      <input type="hidden" name="action" value="toggle_stock">
                      <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                      <button type="submit" class="badge-stock <?= $prod['in_stock'] ? 'badge-in' : 'badge-out' ?>" style="cursor: pointer; border: none;" title="Click to toggle stock status">
                        <?= $prod['in_stock'] ? 'In Stock' : 'Pre-Order' ?>
                      </button>
                    </form>
                  </td>
                  <td style="text-align: right;">
                    <!-- Remove Item Form -->
                    <form action="index.php" method="POST" onsubmit="return confirm('Remove \'<?= addslashes($prod['name']) ?>\' from Italy Optics showroom?');" style="display: inline-block;">
                      <input type="hidden" name="action" value="delete_product">
                      <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                      <button type="submit" class="btn btn-white" style="padding: 6px 10px; font-size: 11px; color: #D00; border-color: #D00;" title="Remove this item">
                        Remove ✕
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>

    </div>

  </div>

</div>
<script>
// ============ IMAGE UPLOAD & GALLERY PRESETS ============
function handleImageError(img) {
  img.style.display = 'none';
  const placeholder = document.getElementById('imgPlaceholder');
  if (placeholder) {
    placeholder.style.display = 'block';
  }
}

function clearSelectedImage() {
  document.getElementById('adminImgInput').value = '';
  const img = document.getElementById('imgPreview');
  img.src = '';
  img.style.display = 'none';
  document.getElementById('imgPlaceholder').style.display = 'block';
  document.getElementById('imgSelectedLabel').textContent = 'No image selected (Click upload or pick a preset)';
  document.querySelectorAll('.preset-card').forEach(c => { 
    c.style.borderColor = '#DDD'; 
    c.style.borderWidth = '1px'; 
    c.classList.remove('active-preset'); 
  });
  const fileInput = document.getElementById('localFileInput');
  if (fileInput) fileInput.value = '';
}

function handleLocalUpload(input) {
  if (input.files && input.files[0]) {
    const file = input.files[0];
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('adminImgInput').value = e.target.result;
      const img = document.getElementById('imgPreview');
      img.src = e.target.result;
      img.style.display = 'block';
      document.getElementById('imgPlaceholder').style.display = 'none';
      document.getElementById('imgSelectedLabel').textContent = 'Current Image Selected: ' + file.name;
      document.querySelectorAll('.preset-card').forEach(c => { c.style.borderColor = '#DDD'; c.style.borderWidth = '1px'; c.classList.remove('active-preset'); });
    };
    reader.readAsDataURL(file);
  }
}

function selectPreset(url, el, label) {
  document.getElementById('adminImgInput').value = url;
  const img = document.getElementById('imgPreview');
  img.src = url;
  img.style.display = 'block';
  document.getElementById('imgPlaceholder').style.display = 'none';
  document.getElementById('imgSelectedLabel').textContent = 'Current Image Selected: ' + (label || 'Preset Frame');
  document.querySelectorAll('.preset-card').forEach(c => { c.style.borderColor = '#DDD'; c.style.borderWidth = '1px'; c.classList.remove('active-preset'); });
  el.style.borderColor = '#000';
  el.style.borderWidth = '2px';
  el.classList.add('active-preset');
}

// ============ COLOR PICKER & CHIPS ============
function addColorChip() {
  const nameInput = document.getElementById('newColorName');
  const hexInput = document.getElementById('newColorHex');
  const name = nameInput.value.trim();
  const hex = hexInput.value;
  if (!name) { nameInput.style.borderColor = '#D00'; nameInput.focus(); setTimeout(() => nameInput.style.borderColor = '#DDD', 2000); return; }

  const chip = document.createElement('div');
  chip.className = 'color-chip';
  chip.setAttribute('data-name', name);
  chip.setAttribute('data-hex', hex);
  chip.style.cssText = 'display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border-radius: 6px; background: #FFFFFF; border: 1px solid #D4D4D4; font-size: 12px; font-weight: 500; color: #111;';
  chip.innerHTML = '<span style="width:14px;height:14px;border-radius:50%;background:'+hex+';border:1px solid #CCC;display:inline-block;"></span> '+name+' <span onclick="removeColorChip(this.parentElement)" style="cursor:pointer;color:#888;font-size:14px;margin-left:2px;">&times;</span>';
  document.getElementById('colorChipsList').appendChild(chip);
  nameInput.value = '';
  updateColorsHiddenInput();
}

function removeColorChip(el) { el.remove(); updateColorsHiddenInput(); }

function updateColorsHiddenInput() {
  const chips = document.querySelectorAll('#colorChipsList .color-chip');
  const parts = [];
  chips.forEach(c => { const n=c.getAttribute('data-name'), h=c.getAttribute('data-hex'); if(n&&h) parts.push(n+':'+h); });
  document.getElementById('colorsHiddenInput').value = parts.join(', ') || 'Matte Black:#000000';
}

// Hover effects for presets
document.querySelectorAll('.preset-card').forEach(card => {
  card.addEventListener('mouseenter', function() { if(!this.classList.contains('active-preset')) this.style.background='#FAF8F2'; });
  card.addEventListener('mouseleave', function() { if(!this.classList.contains('active-preset')) this.style.background='#FFF'; });
});
</script>

</body>
</html>
