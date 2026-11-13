<?php
require_once __DIR__ . '/../config/config.php';
?>
<!-- Main Footer -->
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      
      <!-- Brand & About -->
      <div class="footer-col">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
          <div style="width: 32px; height: 32px; background: #FFF; color: #000; display: flex; align-items: center; justify-content: center; font-size: 16px; border-radius: 4px;">👓</div>
          <span style="font-family: var(--font-serif); font-size: 20px; font-weight: 700; letter-spacing: 0.05em;"><?= htmlspecialchars(SITE_NAME) ?></span>
        </div>
        <p style="color: var(--color-light-gray); line-height: 1.6; margin-bottom: 16px; max-width: 380px;">
          Handcrafted Italian & global eyewear curated in Chittagong. Hand-cut acetate frames, aerospace titanium, precision computerized blue-light and prescription lenses.
        </p>
        <div style="color: var(--color-light-gray); font-size: 11px; line-height: 1.7;">
          <div>📍 <?= htmlspecialchars(STORE_ADDRESS) ?></div>
          <div>⏰ <?= htmlspecialchars(STORE_HOURS) ?></div>
        </div>
      </div>

      <!-- Quick Collections -->
      <div class="footer-col">
        <h4>Collections</h4>
        <ul class="footer-links">
          <li><a href="index.php?category=Eyeglasses">Prescription Frames</a></li>
          <li><a href="index.php?category=Sunglasses">Polarized Sunglasses</a></li>
          <li><a href="index.php?category=Blue Light">Blue Light Shield</a></li>
          <li><a href="index.php?category=Contact Lenses">Contact Lenses</a></li>
          <li><a href="index.php?category=Reading Glasses">Reading Glasses</a></li>
          <li><a href="index.php?category=Kids">Kids Flexible</a></li>
        </ul>
      </div>

      <!-- Customer Service -->
      <div class="footer-col">
        <h4>Customer Care</h4>
        <ul class="footer-links">
          <li><a href="#about-store">Jamal Khan Studio</a></li>
          <li><a href="#contact">Direct Consultation</a></li>
          <li><a href="#showroom">Showroom Catalog</a></li>
          <li><a href="https://www.google.com/search?q=italy+optics+chittagong" target="_blank" rel="noreferrer">Google Reviews (5.0 ★)</a></li>
        </ul>
      </div>

      <!-- Direct Contact Channels -->
      <div class="footer-col">
        <h4>Direct Contact</h4>
        <ul class="footer-links" style="font-size: 12px;">
          <li>
            <a href="tel:<?= preg_replace('/[^0-9]/', '', STORE_PHONE) ?>" style="color: #FFF; font-weight: 700;">
              📞 Phone: <?= htmlspecialchars(STORE_PHONE) ?>
            </a>
          </li>
          <li>
            <a href="mailto:<?= htmlspecialchars(STORE_EMAIL) ?>">
              ✉️ Email: <?= htmlspecialchars(STORE_EMAIL) ?>
            </a>
          </li>
          <li>
            <a href="<?= htmlspecialchars(FACEBOOK_URL) ?>" target="_blank" rel="noreferrer">
              🌐 Facebook: italyoptics.ctg
            </a>
          </li>
          <li>
            <a href="https://wa.me/88<?= preg_replace('/[^0-9]/', '', STORE_PHONE) ?>" target="_blank" rel="noreferrer" style="color: #FFF; font-weight: 700;">
              💬 WhatsApp: <?= htmlspecialchars(STORE_PHONE) ?>
            </a>
          </li>
        </ul>
      </div>

    </div>

    <div class="footer-bottom">
      <div>© <?= date('Y') ?> <?= htmlspecialchars(SITE_NAME) ?>. All rights reserved. 12 Jamal Khan Road, Chattogram 4000.</div>
      <div style="text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700; color: #FFF;">White & Black Luxury Edition</div>
    </div>
  </div>
</footer>

<!-- Monochrome Slide-In Cart Drawer -->
<div class="cart-drawer-overlay" id="cartDrawerOverlay"></div>
<div class="cart-drawer" id="cartDrawer">
  <div class="cart-header">
    <div style="display: flex; align-items: center; gap: 8px;">
      <span style="font-size: 18px;">🛒</span>
      <h3 style="font-size: 16px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Your Cart</h3>
    </div>
    <button id="closeCartBtn" style="background: none; border: none; font-size: 20px; cursor: pointer;">✕</button>
  </div>

  <div class="cart-body" id="cartItemsContainer">
    <!-- Populated dynamically via JS -->
  </div>

  <div class="cart-footer">
    <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--color-mid-gray); margin-bottom: 6px;">
      <span>Subtotal</span>
      <span id="cartDrawerSubtotal" style="font-weight: 700; color: #000;">৳0</span>
    </div>
    <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--color-mid-gray); margin-bottom: 12px;">
      <span>Chittagong Delivery</span>
      <span id="cartDrawerShipping" style="font-weight: 700; color: #000;">৳0</span>
    </div>
    <div style="display: flex; justify-content: space-between; font-size: 15px; font-weight: 800; border-top: 1px solid var(--color-border); padding-top: 10px; margin-bottom: 16px;">
      <span>Grand Total</span>
      <span id="cartDrawerTotal" style="font-size: 18px;">৳0</span>
    </div>

    <a href="checkout.php" class="btn btn-black" style="width: 100%; text-align: center;">
      Proceed to Checkout →
    </a>
  </div>
</div>

<!-- Customer & Admin Auth Modal -->
<div class="modal-overlay" id="authModal">
  <div class="modal-card">
    <button class="close-btn" id="closeAuthBtn">✕</button>
    
    <div style="text-align: center; margin-bottom: 24px;">
      <div style="font-size: 28px; margin-bottom: 6px;">👓</div>
      <h2 style="font-family: var(--font-serif); font-size: 28px; font-weight: 700;">Account Access</h2>
      <p style="font-size: 12px; color: var(--color-mid-gray);">Italy Optics · 12 Jamal Khan Road, Chattogram</p>
    </div>

    <!-- Tab Buttons -->
    <div style="display: flex; border: 1px solid var(--color-black); border-radius: 4px; overflow: hidden; margin-bottom: 20px;">
      <button type="button" id="tabCustomerBtn" style="flex: 1; padding: 10px; background: #000; color: #FFF; border: none; font-size: 11px; font-weight: 700; text-transform: uppercase; cursor: pointer;" onclick="switchAuthTab('customer')">Sign In</button>
      <button type="button" id="tabRegisterBtn" style="flex: 1; padding: 10px; background: #FFF; color: #000; border: none; border-left: 1px solid #000; font-size: 11px; font-weight: 700; text-transform: uppercase; cursor: pointer;" onclick="switchAuthTab('register')">Sign Up</button>
    </div>

    <!-- Login Form -->
    <form action="login.php" method="POST" id="authForm" style="display: flex; flex-direction: column; gap: 14px;">
      <input type="hidden" name="action_type" id="authActionType" value="login">

      <div class="form-group" id="nameFieldGroup" style="display: none;">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" id="authNameInput" class="form-input" placeholder="e.g. Asif Chowdhury">
      </div>

      <div class="form-group">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" id="authEmailInput" required class="form-input" placeholder="admin@italyoptics.com or customer@italyoptics.com">
      </div>

      <div class="form-group" id="phoneFieldGroup" style="display: none;">
        <label class="form-label">Phone Number (Chittagong)</label>
        <input type="text" name="phone" id="authPhoneInput" class="form-input" placeholder="018XXXXXXXX">
      </div>

      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" id="authPassInput" required class="form-input" placeholder="••••••••">
      </div>

      <button type="submit" id="authSubmitBtn" class="btn btn-black" style="width: 100%; margin-top: 6px;">
        Sign In to Account
      </button>
    </form>

  </div>
</div>

<script>
function switchAuthTab(type) {
  const actionType = document.getElementById('authActionType');
  const nameGrp = document.getElementById('nameFieldGroup');
  const phoneGrp = document.getElementById('phoneFieldGroup');
  const emailInput = document.getElementById('authEmailInput');
  const passInput = document.getElementById('authPassInput');
  const submitBtn = document.getElementById('authSubmitBtn');
  const btnCust = document.getElementById('tabCustomerBtn');
  const btnReg = document.getElementById('tabRegisterBtn');

  btnCust.style.background = '#FFF'; btnCust.style.color = '#000';
  btnReg.style.background = '#FFF'; btnReg.style.color = '#000';

  if (type === 'register') {
    btnReg.style.background = '#000'; btnReg.style.color = '#FFF';
    actionType.value = 'register';
    nameGrp.style.display = 'block';
    phoneGrp.style.display = 'block';
    emailInput.value = '';
    passInput.value = '';
    submitBtn.textContent = 'Create New Account';
  } else {
    btnCust.style.background = '#000'; btnCust.style.color = '#FFF';
    actionType.value = 'login';
    nameGrp.style.display = 'none';
    phoneGrp.style.display = 'none';
    submitBtn.textContent = 'Sign In to Account';
  }
}
</script>

<script src="assets/js/app.js"></script>
</body>
</html>
