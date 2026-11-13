<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$currentUser = $_SESSION['user'] ?? null;
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: 40px 20px 80px;">
  
  <div style="border-bottom: 2px solid #000; padding-bottom: 16px; margin-bottom: 32px; display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
    <div>
      <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-mid-gray);">
        Order Finalization
      </span>
      <h1 style="font-family: var(--font-serif); font-size: 38px; font-weight: 700; margin: 4px 0 0;">
        Checkout & Prescription Delivery
      </h1>
    </div>
    <div style="font-size: 12px; color: var(--color-mid-gray);">
      Showroom: 12 Jamal Khan Road, Chattogram · Call: <?= htmlspecialchars(STORE_PHONE) ?>
    </div>
  </div>

  <div id="checkoutSuccessCard" style="display: none; background: #000; color: #FFF; padding: 40px; border-radius: 4px; text-align: center; max-width: 600px; margin: 40px auto;">
    <div style="font-size: 48px; margin-bottom: 12px;">✓</div>
    <h2 style="font-family: var(--font-serif); font-size: 32px; font-weight: 700; margin-bottom: 10px;">Order Confirmed!</h2>
    <p style="font-size: 14px; color: #AAA; line-height: 1.6; margin-bottom: 24px;">
      Thank you for choosing <strong>Italy Optics</strong>. Your order <span id="confirmedOrderNum" style="color: #FFF; font-weight: 800;"></span> has been dispatched to our optical team at 12 Jamal Khan Road.
    </p>
    <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
      <a id="whatsappConfirmBtn" href="#" target="_blank" class="btn btn-white" style="font-size: 12px;">
        💬 Notify Shop on WhatsApp
      </a>
      <a href="index.php" class="btn btn-white" style="background: transparent; color: #FFF; border-color: #555; font-size: 12px;">
        Return to Showroom
      </a>
    </div>
  </div>

  <div id="checkoutMainGrid" style="display: grid; grid-template-columns: 1fr; gap: 32px;">
    
    <div style="display: grid; grid-template-columns: 1fr; gap: 32px;">
      
      <!-- Order Form -->
      <form id="orderForm" onsubmit="submitOrder(event)">
        
        <!-- Section 1: Customer Info -->
        <div style="background: #FFF; border: 1px solid var(--color-border); padding: 24px; border-radius: 4px; margin-bottom: 24px;">
          <h3 style="font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #EEE; padding-bottom: 10px; margin-bottom: 16px;">
            1. Customer & Delivery Contact
          </h3>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
              <label class="form-label">Full Name *</label>
              <input type="text" id="custName" required class="form-input" value="<?= htmlspecialchars($currentUser['name'] ?? '') ?>" placeholder="e.g. Shakil Chowdhury">
            </div>

            <div class="form-group">
              <label class="form-label">Phone Number (Call / WhatsApp) *</label>
              <input type="tel" id="custPhone" required class="form-input" value="<?= htmlspecialchars($currentUser['phone'] ?? '01708-541674') ?>" placeholder="01XXXXXXXXX">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Email Address (Order Confirmation) *</label>
            <input type="email" id="custEmail" required class="form-input" value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>" placeholder="customer@example.com">
          </div>

          <div class="form-group">
            <label class="form-label">Delivery Type</label>
            <div style="display: flex; gap: 12px; margin-top: 6px;">
              <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; cursor: pointer;">
                <input type="radio" name="delivery_type" value="home" checked onchange="toggleDeliveryType('home')">
                Chittagong Home Delivery
              </label>
              <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; cursor: pointer;">
                <input type="radio" name="delivery_type" value="pickup" onchange="toggleDeliveryType('pickup')">
                Jamal Khan Showroom Pickup (12 Jamal Khan Rd)
              </label>
            </div>
          </div>

          <div class="form-group" id="addressGroup">
            <label class="form-label">Delivery Address in Chittagong *</label>
            <textarea id="custAddress" rows="2" class="form-textarea" placeholder="House/Flat #, Road, Area (Jamal Khan, GEC, Nasirabad, Agrabad, Halishahar, etc.)">12 Jamal Khan Road, Chattogram 4000</textarea>
          </div>
        </div>

        <!-- Section 2: Prescription / Notes -->
        <div style="background: #FFF; border: 1px solid var(--color-border); padding: 24px; border-radius: 4px; margin-bottom: 24px;">
          <h3 style="font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #EEE; padding-bottom: 10px; margin-bottom: 16px;">
            2. Lens Prescription Note (Optional)
          </h3>
          <div class="form-group">
            <label class="form-label">Eye Power Details or Fitting Request</label>
            <textarea id="prescriptionNote" rows="2" class="form-textarea" placeholder="Enter SPH / CYL / Axis power or write 'Will test eyes at Jamal Khan showroom' or 'Will send prescription on WhatsApp'"></textarea>
          </div>
        </div>

        <!-- Section 3: Payment Method -->
        <div style="background: #FFF; border: 1px solid var(--color-border); padding: 24px; border-radius: 4px; margin-bottom: 24px;">
          <h3 style="font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #EEE; padding-bottom: 10px; margin-bottom: 16px;">
            3. Payment Method
          </h3>
          <div style="display: flex; flex-direction: column; gap: 10px;">
            <label style="display: flex; align-items: center; gap: 10px; padding: 12px; border: 1px solid #000; border-radius: 4px; cursor: pointer;">
              <input type="radio" name="payment_method" value="cod" checked>
              <div>
                <strong style="font-size: 13px;">Cash on Delivery (Chittagong)</strong>
                <div style="font-size: 11px; color: #666;">Pay cash upon delivery or pickup at 12 Jamal Khan Road</div>
              </div>
            </label>

            <label style="display: flex; align-items: center; gap: 10px; padding: 12px; border: 1px solid #E5E5E5; border-radius: 4px; cursor: pointer;">
              <input type="radio" name="payment_method" value="bkash">
              <div>
                <strong style="font-size: 13px;">bKash / Nagad Mobile Banking</strong>
                <div style="font-size: 11px; color: #666;">Merchant / Personal payment to 01708-541674</div>
              </div>
            </label>
          </div>
        </div>

        <!-- Section 4: Items Review & Place Order -->
        <div style="background: #FAFAFA; border: 1px solid #000; padding: 24px; border-radius: 4px;">
          <h3 style="font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 16px;">
            Order Summary
          </h3>
          <div id="checkoutItemsList" style="margin-bottom: 20px;">
            <!-- Rendered by JS -->
          </div>

          <div style="border-top: 1px solid #DDD; padding-top: 14px; font-size: 14px; display: flex; justify-content: space-between; font-weight: 800; margin-bottom: 20px;">
            <span>Payable Amount</span>
            <span id="checkoutGrandTotal" style="font-size: 20px;">৳0</span>
          </div>

          <button type="submit" class="btn btn-black" style="width: 100%; padding: 16px; font-size: 13px;">
            Confirm & Place Eyewear Order
          </button>
        </div>

      </form>

    </div>

  </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  renderCheckoutSummary();
});

function toggleDeliveryType(type) {
  const addr = document.getElementById('addressGroup');
  if (type === 'pickup') {
    addr.style.display = 'none';
  } else {
    addr.style.display = 'block';
  }
  renderCheckoutSummary();
}

function renderCheckoutSummary() {
  const list = document.getElementById('checkoutItemsList');
  const totalElem = document.getElementById('checkoutGrandTotal');
  const cart = getCart();

  if (cart.length === 0) {
    list.innerHTML = '<p style="font-size: 12px; color: #777;">Your cart is currently empty. Please select eyewear from the showroom first.</p>';
    if (totalElem) totalElem.textContent = '৳0';
    return;
  }

  let subtotal = 0;
  let html = '';
  cart.forEach(item => {
    const lineTotal = item.price * item.quantity;
    subtotal += lineTotal;
    html += `
      <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #EEE; font-size: 12px;">
        <div>
          <strong>${item.name}</strong> × ${item.quantity}
          <div style="font-size: 10px; color: #666;">Finish: ${item.color}</div>
        </div>
        <strong>৳${lineTotal.toLocaleString()}</strong>
      </div>
    `;
  });

  const deliveryType = document.querySelector('input[name="delivery_type"]:checked')?.value || 'home';
  const shipping = (deliveryType === 'pickup' || subtotal >= 2000) ? 0 : 60;
  const grandTotal = subtotal + shipping;

  html += `
    <div style="display: flex; justify-content: space-between; padding-top: 8px; font-size: 12px; color: #666;">
      <span>Subtotal</span>
      <span>৳${subtotal.toLocaleString()}</span>
    </div>
    <div style="display: flex; justify-content: space-between; font-size: 12px; color: #666; margin-top: 4px;">
      <span>Delivery</span>
      <span>${shipping === 0 ? 'FREE' : '৳' + shipping}</span>
    </div>
  `;

  list.innerHTML = html;
  if (totalElem) totalElem.textContent = '৳' + grandTotal.toLocaleString();
}

function submitOrder(e) {
  e.preventDefault();
  const cart = getCart();
  if (cart.length === 0) {
    alert('Please add items to your cart before checking out.');
    return;
  }

  const name = document.getElementById('custName').value;
  const phone = document.getElementById('custPhone').value;
  const email = document.getElementById('custEmail').value;
  const address = document.getElementById('custAddress').value;
  const deliveryType = document.querySelector('input[name="delivery_type"]:checked')?.value || 'home';
  const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'cod';
  const prescription = document.getElementById('prescriptionNote').value;

  // Add prescription to items if provided
  const itemsWithNotes = cart.map(item => ({ ...item, prescription }));

  fetch('api/cart.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      action: 'place_order',
      name, phone, email, address, deliveryType, paymentMethod,
      items: itemsWithNotes
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'ok') {
      // Clear cart
      localStorage.removeItem('italy_optics_cart');
      updateCartBadges();

      document.getElementById('checkoutMainGrid').style.display = 'none';
      const successCard = document.getElementById('checkoutSuccessCard');
      document.getElementById('confirmedOrderNum').textContent = '#' + data.order_number;
      document.getElementById('whatsappConfirmBtn').href = data.whatsapp_url;
      successCard.style.display = 'block';
    } else {
      alert(data.message || 'Error creating order.');
    }
  })
  .catch(err => {
    alert('Order submitted. Optical team will contact your phone: ' + phone);
    localStorage.removeItem('italy_optics_cart');
    window.location.href = 'index.php';
  });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
