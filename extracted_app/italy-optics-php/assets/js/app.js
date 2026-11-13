/**
 * ITALY OPTICS - MONOCHROME CLIENT APPLICATION
 * Color Picker, Shopping Cart, and Interactive Modals
 */

document.addEventListener('DOMContentLoaded', () => {
  initColorPickers();
  initCart();
  initModals();
  initQuickView();
});

// Toast notification helper
function showToast(message) {
  let toast = document.getElementById('toastNotification');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'toastNotification';
    toast.className = 'toast-msg';
    document.body.appendChild(toast);
  }
  toast.textContent = message;
  toast.classList.add('show');
  setTimeout(() => {
    toast.classList.remove('show');
  }, 3200);
}

// 1. Color Picker on Product Cards
function initColorPickers() {
  document.querySelectorAll('.product-card').forEach(card => {
    const swatches = card.querySelectorAll('.swatch-btn');
    const colorLabel = card.querySelector('.selected-color-name');
    const addToCartBtn = card.querySelector('.btn-add-to-cart');

    swatches.forEach(swatch => {
      swatch.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();

        // Remove active class from sibling swatches
        swatches.forEach(s => s.classList.remove('active'));
        swatch.classList.add('active');

        const colorName = swatch.dataset.colorName;
        const colorHex = swatch.dataset.colorHex;

        if (colorLabel) {
          colorLabel.textContent = colorName;
        }

        // Update button data attributes
        if (addToCartBtn) {
          addToCartBtn.dataset.chosenColor = colorName;
          addToCartBtn.dataset.chosenHex = colorHex;
        }
      });
    });
  });
}

// 2. Shopping Cart System (Synced with PHP Session & LocalStorage)
function initCart() {
  const cartDrawer = document.getElementById('cartDrawer');
  const cartOverlay = document.getElementById('cartDrawerOverlay');
  const cartToggleBtns = document.querySelectorAll('.toggle-cart-btn');
  const closeCartBtn = document.getElementById('closeCartBtn');

  const openCart = () => {
    if (cartDrawer && cartOverlay) {
      cartDrawer.classList.add('active');
      cartOverlay.classList.add('active');
      renderCartItems();
    }
  };

  const closeCart = () => {
    if (cartDrawer && cartOverlay) {
      cartDrawer.classList.remove('active');
      cartOverlay.classList.remove('active');
    }
  };

  cartToggleBtns.forEach(btn => btn.addEventListener('click', (e) => {
    e.preventDefault();
    openCart();
  }));

  if (closeCartBtn) closeCartBtn.addEventListener('click', closeCart);
  if (cartOverlay) cartOverlay.addEventListener('click', closeCart);

  // Add to cart buttons
  document.querySelectorAll('.btn-add-to-cart').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const productId = btn.dataset.productId;
      const productName = btn.dataset.productName;
      const price = parseFloat(btn.dataset.price);
      const image = btn.dataset.image;
      const colorName = btn.dataset.chosenColor || 'Classic Black';
      const colorHex = btn.dataset.chosenHex || '#000000';

      addToCart({
        id: productId,
        name: productName,
        price: price,
        image: image,
        color: colorName,
        hex: colorHex,
        quantity: 1
      });

      showToast(`Added "${productName}" (${colorName}) to cart!`);
    });
  });

  updateCartBadges();
}

function getCart() {
  try {
    return JSON.parse(localStorage.getItem('italy_optics_cart') || '[]');
  } catch (e) {
    return [];
  }
}

function saveCart(cart) {
  localStorage.setItem('italy_optics_cart', JSON.stringify(cart));
  updateCartBadges();
  renderCartItems();

  // Also sync to PHP Session via fetch
  fetch('api/cart.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'sync', items: cart })
  }).catch(() => {});
}

function addToCart(item) {
  let cart = getCart();
  const existingIndex = cart.findIndex(c => c.id === item.id && c.color === item.color);
  if (existingIndex > -1) {
    cart[existingIndex].quantity += item.quantity;
  } else {
    cart.push(item);
  }
  saveCart(cart);
}

function updateCartQuantity(index, delta) {
  let cart = getCart();
  if (cart[index]) {
    cart[index].quantity += delta;
    if (cart[index].quantity <= 0) {
      cart.splice(index, 1);
    }
  }
  saveCart(cart);
}

function removeFromCart(index) {
  let cart = getCart();
  cart.splice(index, 1);
  saveCart(cart);
  showToast('Item removed from cart.');
}

function updateCartBadges() {
  const cart = getCart();
  const totalCount = cart.reduce((sum, item) => sum + item.quantity, 0);
  document.querySelectorAll('.cart-count-badge').forEach(badge => {
    badge.textContent = totalCount;
    badge.style.display = totalCount > 0 ? 'flex' : 'none';
  });
}

function renderCartItems() {
  const container = document.getElementById('cartItemsContainer');
  const totalElem = document.getElementById('cartDrawerTotal');
  const subtotalElem = document.getElementById('cartDrawerSubtotal');
  const shippingElem = document.getElementById('cartDrawerShipping');
  if (!container) return;

  const cart = getCart();
  if (cart.length === 0) {
    container.innerHTML = `
      <div style="text-align: center; padding: 40px 20px; color: #777;">
        <div style="font-size: 32px; margin-bottom: 10px;">👓</div>
        <p style="font-weight: 700; color: #000; text-transform: uppercase; font-size: 13px;">Your cart is empty</p>
        <p style="font-size: 12px; margin-top: 6px;">Explore our handcrafted Italian eyewear to add your favorite color.</p>
      </div>
    `;
    if (totalElem) totalElem.textContent = '৳0';
    if (subtotalElem) subtotalElem.textContent = '৳0';
    if (shippingElem) shippingElem.textContent = '৳0';
    return;
  }

  let subtotal = 0;
  let html = '';

  cart.forEach((item, idx) => {
    const itemTotal = item.price * item.quantity;
    subtotal += itemTotal;
    html += `
      <div class="cart-item">
        <img src="${item.image}" alt="${item.name}" class="cart-item-img">
        <div style="flex: 1; min-width: 0;">
          <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <h4 style="font-size: 13px; font-weight: 700; line-height: 1.2; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">${item.name}</h4>
            <button onclick="removeFromCart(${idx})" style="background: none; border: none; cursor: pointer; color: #999; font-size: 14px; padding: 0 4px;">✕</button>
          </div>
          <div style="font-size: 11px; color: #666; margin: 4px 0; display: flex; align-items: center; gap: 4px;">
            <span>Color:</span>
            <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: ${item.hex}; border: 1px solid #CCC;"></span>
            <strong>${item.color}</strong>
          </div>
          <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 6px;">
            <span style="font-size: 13px; font-weight: 700;">৳${itemTotal.toLocaleString()}</span>
            <div style="display: flex; align-items: center; border: 1px solid #E5E5E5; border-radius: 4px; overflow: hidden;">
              <button onclick="updateCartQuantity(${idx}, -1)" style="padding: 2px 8px; background: #FFF; border: none; cursor: pointer; font-weight: bold;">-</button>
              <span style="padding: 0 8px; font-size: 11px; font-weight: 700;">${item.quantity}</span>
              <button onclick="updateCartQuantity(${idx}, 1)" style="padding: 2px 8px; background: #FFF; border: none; cursor: pointer; font-weight: bold;">+</button>
            </div>
          </div>
        </div>
      </div>
    `;
  });

  container.innerHTML = html;
  const shipping = subtotal >= 2000 ? 0 : 60;
  const grandTotal = subtotal + shipping;

  if (subtotalElem) subtotalElem.textContent = '৳' + subtotal.toLocaleString();
  if (shippingElem) shippingElem.textContent = shipping === 0 ? 'FREE' : '৳' + shipping;
  if (totalElem) totalElem.textContent = '৳' + grandTotal.toLocaleString();
}

// 3. Modals Management (Auth & Quick View)
function initModals() {
  const authModal = document.getElementById('authModal');
  const openAuthBtns = document.querySelectorAll('.open-auth-btn');
  const closeAuthBtn = document.getElementById('closeAuthBtn');

  openAuthBtns.forEach(btn => btn.addEventListener('click', (e) => {
    e.preventDefault();
    if (authModal) authModal.classList.add('open');
  }));

  if (closeAuthBtn && authModal) {
    closeAuthBtn.addEventListener('click', () => authModal.classList.remove('open'));
    authModal.addEventListener('click', (e) => {
      if (e.target === authModal) authModal.classList.remove('open');
    });
  }
}

// 4. Quick View Modal
function initQuickView() {
  const quickViewModal = document.getElementById('quickViewModal');
  const closeBtn = document.getElementById('closeQuickViewBtn');

  if (closeBtn && quickViewModal) {
    closeBtn.addEventListener('click', () => quickViewModal.classList.remove('open'));
    quickViewModal.addEventListener('click', (e) => {
      if (e.target === quickViewModal) quickViewModal.classList.remove('open');
    });
  }
}
