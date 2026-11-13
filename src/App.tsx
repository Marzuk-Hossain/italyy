import React, { useState, useMemo } from 'react';
import {
  ShoppingBag,
  MapPin,
  Phone,
  Clock,
  Search,
  SlidersHorizontal,
  X,
  Check,
  MessageCircle,
  ArrowRight,
  CheckCircle2,
  User,
  LogIn,
  LogOut,
  ShieldCheck,
  Plus,
  Trash2,
  Upload,
  Image as ImageIcon,
  AlertCircle,
  Package,
  Database,
  Download,
  Copy
} from 'lucide-react';
import { Product, ProductColor, CartItem, CustomerOrder } from './types';
import { INITIAL_PRODUCTS, INITIAL_REVIEWS, VECTOR_EYEWEAR, STUDIO_PRESETS } from './data/eyewearPresets';

interface UserAccount {
  id: number;
  name: string;
  email: string;
  phone: string;
  role: 'customer' | 'admin';
}

interface StoredUserAccount extends UserAccount {
  password: string;
}

const INITIAL_ACCOUNTS: StoredUserAccount[] = [
  {
    id: 1,
    name: 'Store Manager (Admin)',
    email: 'admin@italyoptics.com',
    phone: '01708-541674',
    role: 'admin',
    password: 'admin123'
  },
  {
    id: 2,
    name: 'Tanvir Chowdhury',
    email: 'customer@italyoptics.com',
    phone: '01819-556677',
    role: 'customer',
    password: 'customer123'
  }
];

const DEFAULT_PRODUCT_COLOR: ProductColor = {
  name: 'Obsidian Black',
  hex: '#111111',
  isDefault: true
};

const STORE_CONFIG = {
  name: 'Italy Optics',
  tagline: 'Luxury Monochrome Eyewear Studio',
  address: '12 Jamal Khan Road, Chattogram 4000',
  phone: '01708-541674',
  whatsappUrl: 'https://wa.me/8801708541674',
  hours: 'Open · Closes 10:00 PM',
  rating: '5.0',
  reviewsCount: '3 Google reviews · Chattogram',
  currency: '৳'
};

const CATEGORIES = [
  'All Collections',
  'Eyeglasses',
  'Sunglasses',
  'Blue Light',
  'Reading Glasses',
  'Contact Lenses',
  'Kids'
];

export default function App() {
  // Authentication State
  const [registeredUsers, setRegisteredUsers] = useState<StoredUserAccount[]>(INITIAL_ACCOUNTS);
  const [currentUser, setCurrentUser] = useState<UserAccount | null>(null);
  const [isAuthModalOpen, setIsAuthModalOpen] = useState<boolean>(false);
  const [authTab, setAuthTab] = useState<'login' | 'register'>('login');
  const [authEmail, setAuthEmail] = useState<string>('');
  const [authPassword, setAuthPassword] = useState<string>('');
  const [authName, setAuthName] = useState<string>('');
  const [authPhone, setAuthPhone] = useState<string>('');
  const [authError, setAuthError] = useState<string | null>(null);
  const [isProfileOpen, setIsProfileOpen] = useState<boolean>(false);

  // Admin-only Store Inventory State (strictly hidden unless logged in as admin)
  const [isAdminOpen, setIsAdminOpen] = useState<boolean>(false);
  const [newProdName, setNewProdName] = useState<string>('');
  const [newProdBrand, setNewProdBrand] = useState<string>('Italy Optics Heritage');
  const [newProdCategory, setNewProdCategory] = useState<string>('Eyeglasses');
  const [newProdGender, setNewProdGender] = useState<Product['gender']>('Unisex');
  const [newProdShape, setNewProdShape] = useState<Product['shape']>('Round');
  const [newProdPrice, setNewProdPrice] = useState<string>('3500');
  const [newProdOriginalPrice, setNewProdOriginalPrice] = useState<string>('4200');
  const [newProdDescription, setNewProdDescription] = useState<string>('');
  const [newProdImage, setNewProdImage] = useState<string>(VECTOR_EYEWEAR.round);
  const [newProdColors, setNewProdColors] = useState<ProductColor[]>([DEFAULT_PRODUCT_COLOR]);
  const [customColorName, setCustomColorName] = useState<string>('');
  const [customColorHex, setCustomColorHex] = useState<string>('#D4AF37');

  // Storefront & Catalog State
  const [products, setProducts] = useState<Product[]>(INITIAL_PRODUCTS);
  const [selectedCategory, setSelectedCategory] = useState<string>('All Collections');
  const [selectedGender, setSelectedGender] = useState<string>('All');
  const [selectedShape, setSelectedShape] = useState<string>('All');
  const [searchQuery, setSearchQuery] = useState<string>('');
  const [sortBy, setSortBy] = useState<string>('featured');

  // Modals & Drawers
  const [cart, setCart] = useState<CartItem[]>([]);
  const [isCartOpen, setIsCartOpen] = useState<boolean>(false);
  const [selectedProductForModal, setSelectedProductForModal] = useState<Product | null>(null);
  const [orderConfirmation, setOrderConfirmation] = useState<CustomerOrder | null>(null);
  const [userOrders, setUserOrders] = useState<CustomerOrder[]>([]);

  // Selected color tracking per product
  const [activeColors, setActiveColors] = useState<Record<number, ProductColor>>(() => {
    const initial: Record<number, ProductColor> = {};
    INITIAL_PRODUCTS.forEach(p => {
      initial[p.id] = p.colors[0] || { name: 'Standard', hex: '#000000' };
    });
    return initial;
  });

  // Checkout form state
  const [checkoutName, setCheckoutName] = useState<string>('');
  const [checkoutPhone, setCheckoutPhone] = useState<string>('');
  const [checkoutEmail] = useState<string>('');
  const [checkoutAddress, setCheckoutAddress] = useState<string>('');
  const [deliveryType, setDeliveryType] = useState<'pickup' | 'chattogram' | 'outside'>('chattogram');
  const [paymentMethod] = useState<'cod' | 'bkash' | 'store'>('cod');

  // Toast banner
  const [toastMessage, setToastMessage] = useState<string | null>(null);

  const showToast = (msg: string) => {
    setToastMessage(msg);
    setTimeout(() => setToastMessage(null), 3500);
  };

  // Auth Handling (Supports both Admin and Customer accounts)
  const handleAuthSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setAuthError(null);

    const normalizedEmail = authEmail.trim().toLowerCase();
    const enteredPassword = authPassword;

    if (authTab === 'login') {
      if (!normalizedEmail || !enteredPassword) {
        setAuthError('Please enter both your email address and password.');
        return;
      }

      const matchedAccount = registeredUsers.find(
        u => u.email.toLowerCase() === normalizedEmail
      );

      if (!matchedAccount) {
        setAuthError(`No account found for "${authEmail.trim()}". Check your email or create a new account.`);
        return;
      }

      if (matchedAccount.password !== enteredPassword) {
        setAuthError('Incorrect password for this account. Access denied.');
        return;
      }

      const authenticatedUser: UserAccount = {
        id: matchedAccount.id,
        name: matchedAccount.name,
        email: matchedAccount.email,
        phone: matchedAccount.phone,
        role: matchedAccount.role
      };

      setCurrentUser(authenticatedUser);
      setIsAuthModalOpen(false);
      setAuthPassword('');

      if (authenticatedUser.role === 'admin') {
        setIsAdminOpen(true);
        showToast(`Admin verified. Welcome back, ${authenticatedUser.name}!`);
      } else {
        setIsAdminOpen(false);
        showToast(`Welcome back, ${authenticatedUser.name}! Logged in successfully.`);
      }
    } else if (authTab === 'register') {
      if (!authName.trim() || !normalizedEmail || !enteredPassword) {
        setAuthError('Please fill in your name, email address, and password.');
        return;
      }

      if (enteredPassword.length < 4) {
        setAuthError('Password must be at least 4 characters long.');
        return;
      }

      const alreadyExists = registeredUsers.some(
        u => u.email.toLowerCase() === normalizedEmail
      );
      if (alreadyExists) {
        setAuthError('This email address is already registered. Please sign in instead.');
        return;
      }

      const newStoredUser: StoredUserAccount = {
        id: Date.now(),
        name: authName.trim(),
        email: authEmail.trim(),
        phone: authPhone.trim() || '01708-XXXXXX',
        role: 'customer',
        password: enteredPassword
      };

      setRegisteredUsers(prev => [...prev, newStoredUser]);
      setCurrentUser({
        id: newStoredUser.id,
        name: newStoredUser.name,
        email: newStoredUser.email,
        phone: newStoredUser.phone,
        role: 'customer'
      });
      setIsAdminOpen(false);
      setIsAuthModalOpen(false);
      setAuthPassword('');
      showToast(`Welcome to Italy Optics, ${newStoredUser.name}! Account created.`);
    }
  };

  const handleLogout = () => {
    setCurrentUser(null);
    setIsProfileOpen(false);
    setIsAdminOpen(false);
    showToast('Logged out of Italy Optics account.');
  };

  // Admin Inventory Helpers
  const handleAdminImageUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    if (file.size > 3 * 1024 * 1024) {
      showToast('Image too large. Please keep under 3MB.');
      return;
    }
    const reader = new FileReader();
    reader.onload = ev => {
      if (typeof ev.target?.result === 'string') {
        setNewProdImage(ev.target.result);
        showToast('Frame photo loaded into preview');
      }
    };
    reader.readAsDataURL(file);
  };

  const handleAddCustomColor = () => {
    const trimmedName = customColorName.trim();
    if (!trimmedName) {
      showToast('Please enter a color finish name.');
      return;
    }

    const exists = newProdColors.some(
      c => c.name.toLowerCase() === trimmedName.toLowerCase()
    );
    if (exists) {
      showToast(`"${trimmedName}" is already added.`);
      return;
    }

    setNewProdColors(prev => [
      ...prev,
      {
        name: trimmedName,
        hex: customColorHex,
        isDefault: prev.length === 0
      }
    ]);
    setCustomColorName('');
  };

  const handleRemoveProductColor = (colorName: string) => {
    setNewProdColors(prev => prev.filter(c => c.name !== colorName));
  };

  const handleAddProduct = (e: React.FormEvent) => {
    e.preventDefault();
    if (currentUser?.role !== 'admin') return;
    if (!newProdName.trim() || !newProdPrice) {
      showToast('Please enter a frame model name and price.');
      return;
    }

    const finalColors =
      newProdColors.length > 0 ? newProdColors : [DEFAULT_PRODUCT_COLOR];

    const priceNum = Math.max(100, Number(newProdPrice) || 3500);
    const origNum = Number(newProdOriginalPrice) || undefined;
    const newId = Date.now();
    const newProduct: Product = {
      id: newId,
      name: newProdName.trim(),
      slug: newProdName.trim().toLowerCase().replace(/[^a-z0-9]+/g, '-'),
      brand: newProdBrand.trim() || 'Italy Optics Heritage',
      category: newProdCategory,
      price: priceNum,
      originalPrice: origNum && origNum > priceNum ? origNum : undefined,
      rating: 5.0,
      reviewsCount: 1,
      image: newProdImage.trim() || VECTOR_EYEWEAR.round,
      fallbackSvgType: 'round',
      description:
        newProdDescription.trim() ||
        'Crafted Italian eyewear frame stocked at 12 Jamal Khan Road, Chattogram.',
      features: [
        'Authentic Italian Craftsmanship',
        'Anti-reflective lens compatible',
        '2-Year Official Store Warranty'
      ],
      gender: newProdGender,
      shape: newProdShape,
      inStock: true,
      isFeatured: true,
      colors: finalColors
    };

    setProducts(prev => [newProduct, ...prev]);
    setActiveColors(prev => ({ ...prev, [newId]: newProduct.colors[0] }));
    setNewProdName('');
    setNewProdDescription('');
    showToast(`Added "${newProduct.name}" to the live showroom catalog.`);
  };

  const handleToggleStock = (productId: number) => {
    if (currentUser?.role !== 'admin') return;
    setProducts(prev =>
      prev.map(p => (p.id === productId ? { ...p, inStock: !p.inStock } : p))
    );
  };

  const handleDeleteProduct = (productId: number, name: string) => {
    if (currentUser?.role !== 'admin') return;
    setProducts(prev => prev.filter(p => p.id !== productId));
    showToast(`Removed "${name}" from catalog.`);
  };

  // 1-Click Auto MySQL Script Generator (Matches Live Website Catalog & Schema)
  const buildAutoMysqlScript = () => {
    const esc = (val: string) =>
      String(val ?? '')
        .replace(/\\/g, '\\\\')
        .replace(/'/g, "\\'")
        .replace(/\r?\n/g, ' ');

    const cats = CATEGORIES.filter(c => c !== 'All Collections');
    const catValues = cats
      .map((name, idx) => {
        const slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-');
        return `(${idx + 1}, '${esc(name)}', '${esc(slug)}', '${esc(name)} collection at Italy Optics', ${idx + 1})`;
      })
      .join(',\n');

    const prodRows: string[] = [];
    const colorRows: string[] = [];

    products.forEach((p, idx) => {
      const pid = idx + 1;
      const catIndex = Math.max(1, cats.indexOf(p.category) + 1);
      const origPrice = p.originalPrice ? p.originalPrice.toFixed(2) : 'NULL';
      const feats = p.features.join('|');
      prodRows.push(
        `(${pid}, '${esc(p.name)}', '${esc(p.slug)}-${pid}', '${esc(p.brand)}', ${catIndex}, ${p.price.toFixed(2)}, ${origPrice}, ${p.rating.toFixed(1)}, ${p.reviewsCount}, '${esc(p.image)}', '${esc(p.description)}', '${esc(feats)}', '${esc(p.gender)}', '${esc(p.shape)}', ${p.inStock ? 1 : 0}, ${p.isFeatured ? 1 : 0})`
      );

      const cols = p.colors.length > 0 ? p.colors : [DEFAULT_PRODUCT_COLOR];
      cols.forEach((c, cIdx) => {
        colorRows.push(
          `(${pid}, '${esc(c.name)}', '${esc(c.hex)}', ${cIdx === 0 ? 1 : 0})`
        );
      });
    });

    const revRows = INITIAL_REVIEWS.map(
      r => `(${r.id}, '${esc(r.name)}', '${esc(r.location)}', ${r.rating}, '${esc(r.text)}', 1)`
    ).join(',\n');

    return `-- ========================================================
-- ITALY OPTICS - 1-CLICK AUTO MYSQL DATABASE SETUP
-- 12 Jamal Khan Road, Chattogram 4000 | Phone: 01708-541674
-- Generated dynamically from live website (${products.length} frames)
-- ========================================================

CREATE DATABASE IF NOT EXISTS \`italy_optics\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE \`italy_optics\`;

CREATE TABLE IF NOT EXISTS \`users\` (
  \`id\` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  \`name\` VARCHAR(120) NOT NULL,
  \`email\` VARCHAR(150) NOT NULL UNIQUE,
  \`phone\` VARCHAR(30) NULL,
  \`password_hash\` VARCHAR(255) NOT NULL,
  \`role\` ENUM('customer', 'admin') DEFAULT 'customer',
  \`created_at\` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS \`categories\` (
  \`id\` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  \`name\` VARCHAR(100) NOT NULL UNIQUE,
  \`slug\` VARCHAR(100) NOT NULL UNIQUE,
  \`description\` VARCHAR(255) NULL,
  \`display_order\` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS \`products\` (
  \`id\` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  \`name\` VARCHAR(150) NOT NULL,
  \`slug\` VARCHAR(160) NOT NULL UNIQUE,
  \`brand\` VARCHAR(100) NOT NULL DEFAULT 'Italy Optics Heritage',
  \`category_id\` INT UNSIGNED NOT NULL,
  \`price\` DECIMAL(10,2) NOT NULL,
  \`original_price\` DECIMAL(10,2) NULL,
  \`rating\` DECIMAL(2,1) DEFAULT 5.0,
  \`reviews_count\` INT UNSIGNED DEFAULT 1,
  \`image\` LONGTEXT NOT NULL,
  \`description\` TEXT NULL,
  \`features\` TEXT NULL,
  \`gender\` VARCHAR(50) DEFAULT 'Unisex',
  \`shape\` VARCHAR(50) DEFAULT 'Classic',
  \`in_stock\` TINYINT(1) DEFAULT 1,
  \`is_featured\` TINYINT(1) DEFAULT 0,
  \`created_at\` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (\`category_id\`) REFERENCES \`categories\`(\`id\`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS \`product_colors\` (
  \`id\` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  \`product_id\` INT UNSIGNED NOT NULL,
  \`color_name\` VARCHAR(80) NOT NULL,
  \`color_hex\` VARCHAR(20) NOT NULL,
  \`is_default\` TINYINT(1) DEFAULT 0,
  FOREIGN KEY (\`product_id\`) REFERENCES \`products\`(\`id\`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS \`orders\` (
  \`id\` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  \`order_number\` VARCHAR(50) NOT NULL UNIQUE,
  \`user_id\` INT UNSIGNED NULL,
  \`customer_name\` VARCHAR(120) NOT NULL,
  \`customer_phone\` VARCHAR(30) NOT NULL,
  \`customer_email\` VARCHAR(150) NOT NULL,
  \`delivery_type\` VARCHAR(50) DEFAULT 'home',
  \`delivery_address\` TEXT NOT NULL,
  \`payment_method\` VARCHAR(50) DEFAULT 'cod',
  \`shipping_fee\` DECIMAL(10,2) DEFAULT 0.00,
  \`total_amount\` DECIMAL(10,2) NOT NULL,
  \`status\` ENUM('pending', 'confirmed', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
  \`created_at\` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (\`user_id\`) REFERENCES \`users\`(\`id\`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS \`order_items\` (
  \`id\` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  \`order_id\` INT UNSIGNED NOT NULL,
  \`product_id\` INT UNSIGNED NULL,
  \`product_name\` VARCHAR(150) NOT NULL,
  \`color_name\` VARCHAR(80) NOT NULL,
  \`color_hex\` VARCHAR(20) NOT NULL,
  \`unit_price\` DECIMAL(10,2) NOT NULL,
  \`quantity\` INT UNSIGNED NOT NULL DEFAULT 1,
  \`subtotal\` DECIMAL(10,2) NOT NULL,
  \`prescription_note\` TEXT NULL,
  FOREIGN KEY (\`order_id\`) REFERENCES \`orders\`(\`id\`) ON DELETE CASCADE,
  FOREIGN KEY (\`product_id\`) REFERENCES \`products\`(\`id\`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS \`reviews\` (
  \`id\` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  \`reviewer_name\` VARCHAR(120) NOT NULL,
  \`location\` VARCHAR(120) DEFAULT 'Chattogram',
  \`rating\` INT NOT NULL DEFAULT 5,
  \`review_text\` TEXT NOT NULL,
  \`is_verified\` TINYINT(1) DEFAULT 1,
  \`created_at\` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS \`contact_messages\` (
  \`id\` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  \`name\` VARCHAR(120) NOT NULL,
  \`email\` VARCHAR(150) NOT NULL,
  \`phone\` VARCHAR(50) NOT NULL,
  \`subject\` VARCHAR(150) NULL,
  \`message\` TEXT NOT NULL,
  \`status\` ENUM('new', 'read', 'replied') DEFAULT 'new',
  \`created_at\` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO \`categories\` (\`id\`, \`name\`, \`slug\`, \`description\`, \`display_order\`) VALUES
${catValues}
ON DUPLICATE KEY UPDATE \`name\`=VALUES(\`name\`);

INSERT INTO \`products\` (\`id\`, \`name\`, \`slug\`, \`brand\`, \`category_id\`, \`price\`, \`original_price\`, \`rating\`, \`reviews_count\`, \`image\`, \`description\`, \`features\`, \`gender\`, \`shape\`, \`in_stock\`, \`is_featured\`) VALUES
${prodRows.join(',\n')}
ON DUPLICATE KEY UPDATE \`name\`=VALUES(\`name\`), \`price\`=VALUES(\`price\`);

DELETE FROM \`product_colors\`;
INSERT INTO \`product_colors\` (\`product_id\`, \`color_name\`, \`color_hex\`, \`is_default\`) VALUES
${colorRows.join(',\n')};

INSERT INTO \`reviews\` (\`id\`, \`reviewer_name\`, \`location\`, \`rating\`, \`review_text\`, \`is_verified\`) VALUES
${revRows}
ON DUPLICATE KEY UPDATE \`reviewer_name\`=VALUES(\`reviewer_name\`);
`;
  };

  const handleDownloadAutoMysql = () => {
    const sql = buildAutoMysqlScript();
    const blob = new Blob([sql], { type: 'application/sql;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'italy_optics_auto_mysql.sql';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    showToast('Downloaded ready-to-import MySQL database (.sql)!');
  };

  const handleCopyAutoMysql = async () => {
    try {
      await navigator.clipboard.writeText(buildAutoMysqlScript());
      showToast('Copied MySQL CREATE DATABASE script to clipboard!');
    } catch {
      handleDownloadAutoMysql();
    }
  };

  // Filtered Products
  const filteredProducts = useMemo(() => {
    return products.filter(product => {
      const matchCat =
        selectedCategory === 'All Collections' || product.category === selectedCategory;
      const matchGender = selectedGender === 'All' || product.gender === selectedGender;
      const matchShape = selectedShape === 'All' || product.shape === selectedShape;
      const matchSearch =
        searchQuery.trim() === '' ||
        product.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        product.description.toLowerCase().includes(searchQuery.toLowerCase()) ||
        product.brand.toLowerCase().includes(searchQuery.toLowerCase());

      return matchCat && matchGender && matchShape && matchSearch;
    }).sort((a, b) => {
      if (sortBy === 'price-low') return a.price - b.price;
      if (sortBy === 'price-high') return b.price - a.price;
      if (sortBy === 'rating') return b.rating - a.rating;
      return b.isFeatured ? 1 : -1;
    });
  }, [products, selectedCategory, selectedGender, selectedShape, searchQuery, sortBy]);

  // Cart calculations
  const cartSubtotal = useMemo(() => {
    return cart.reduce((sum, item) => sum + item.product.price * item.quantity, 0);
  }, [cart]);

  const shippingFee = deliveryType === 'pickup' ? 0 : deliveryType === 'chattogram' ? 60 : 120;
  const cartTotal = cartSubtotal + (cart.length > 0 ? shippingFee : 0);

  const handleAddToCart = (product: Product, color?: ProductColor) => {
    const selectedColor = color || activeColors[product.id] || product.colors[0];
    const itemId = `${product.id}-${selectedColor.name}`;

    setCart(prev => {
      const existing = prev.find(item => item.id === itemId);
      if (existing) {
        return prev.map(item =>
          item.id === itemId ? { ...item, quantity: item.quantity + 1 } : item
        );
      }
      return [...prev, { id: itemId, product, selectedColor, quantity: 1 }];
    });

    showToast(`Added ${product.name} (${selectedColor.name}) to Bag`);
  };

  const handleUpdateQuantity = (itemId: string, delta: number) => {
    setCart(prev =>
      prev
        .map(item => {
          if (item.id === itemId) {
            const newQty = item.quantity + delta;
            return newQty > 0 ? { ...item, quantity: newQty } : null;
          }
          return item;
        })
        .filter(Boolean) as CartItem[]
    );
  };


  const handleCompleteOrder = (e: React.FormEvent) => {
    e.preventDefault();
    if (cart.length === 0) return;
    if (!checkoutName || !checkoutPhone || !checkoutAddress) {
      showToast('Please fill in required contact and delivery details');
      return;
    }

    const orderNumber = `IO-CK-${Math.floor(100000 + Math.random() * 900000)}`;
    const newOrder: CustomerOrder = {
      id: orderNumber,
      customerName: checkoutName,
      phone: checkoutPhone,
      email: checkoutEmail,
      deliveryType,
      deliveryAddress: checkoutAddress,
      paymentMethod,
      items: [...cart],
      shippingFee,
      totalAmount: cartTotal,
      createdAt: new Date().toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      }),
      status: 'Confirmed'
    };

    setOrderConfirmation(newOrder);
    setUserOrders(prev => [newOrder, ...prev]);
    setCart([]);
    setIsCartOpen(false);
  };

  return (
    <div className="min-h-screen bg-[#FAFAFA] text-[#111111] flex flex-col font-sans selection:bg-black selection:text-white">
      {/* Toast Notification */}
      {toastMessage && (
        <div className="fixed bottom-6 right-6 z-50 bg-black text-white px-5 py-3 rounded-lg shadow-2xl flex items-center gap-3 border border-neutral-700 animate-in fade-in slide-in-from-bottom-3 duration-200">
          <CheckCircle2 className="w-5 h-5 text-emerald-400 shrink-0" />
          <span className="text-xs font-semibold tracking-wide">{toastMessage}</span>
        </div>
      )}

      {/* TOP ANNOUNCEMENT BAR */}
      <div className="bg-black text-white text-[11px] font-semibold py-2 px-4 border-b border-neutral-800 tracking-wider uppercase">
        <div className="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2">
          <div className="flex items-center gap-3">
            <span className="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>12 Jamal Khan Road, Chattogram 4000</span>
            <span className="text-neutral-500 hidden sm:inline">|</span>
            <span className="text-neutral-300 hidden sm:inline">{STORE_CONFIG.hours}</span>
          </div>
          <div className="flex items-center gap-4">
            <span className="text-amber-300 flex items-center gap-1">
              ★★★★★ <span className="text-white text-[10px]">5.0 (Google Verified)</span>
            </span>
            <a
              href={`tel:${STORE_CONFIG.phone}`}
              className="hover:text-neutral-300 transition-colors flex items-center gap-1 font-bold"
            >
              <Phone className="w-3 h-3" /> {STORE_CONFIG.phone}
            </a>
          </div>
        </div>
      </div>

      {/* MAIN NAVIGATION WITH PROMINENT LOG IN BUTTON */}
      <header className="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-neutral-200 shadow-xs">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 h-20 flex items-center justify-between gap-4">
          {/* Logo & Brand Identity */}
          <div className="flex items-center gap-3">
            <a href="#" className="flex flex-col">
              <span className="font-serif-luxury text-2xl sm:text-3xl font-bold tracking-tight text-black uppercase leading-none">
                Italy Optics
              </span>
              <span className="text-[10px] uppercase font-bold tracking-[0.25em] text-neutral-500 mt-1">
                ইতালি অপটিক্স · Luxury Studio
              </span>
            </a>
          </div>

          {/* Nav Links */}
          <nav className="hidden lg:flex items-center gap-7 text-xs font-bold uppercase tracking-wider text-neutral-700">
            <a href="#showroom" className="hover:text-black transition-colors">
              Showroom Catalog
            </a>
            <a href="#about-store" className="hover:text-black transition-colors">
              Store Details
            </a>
            <a href="#reviews" className="hover:text-black transition-colors">
              5.0 Reviews
            </a>
            <a href="#contact-channels" className="hover:text-black transition-colors">
              Contact
            </a>
          </nav>

          {/* Action Buttons: Log In Button & Cart */}
          <div className="flex items-center gap-2 sm:gap-3">
            {/* PROMINENT LOG IN / USER BUTTON */}
            {currentUser ? (
              <div className="flex items-center gap-2">
                {currentUser.role === 'admin' && (
                  <button
                    onClick={() => setIsAdminOpen(prev => !prev)}
                    className={`text-xs font-bold uppercase tracking-wider px-3 py-2 rounded-md border transition-all flex items-center gap-1.5 ${
                      isAdminOpen
                        ? 'bg-emerald-600 text-white border-emerald-600'
                        : 'bg-white text-black border-black hover:bg-neutral-100'
                    }`}
                    title="Toggle Store Inventory Panel"
                  >
                    <ShieldCheck className="w-3.5 h-3.5" />
                    <span className="hidden sm:inline">Inventory</span>
                  </button>
                )}
                <button
                  onClick={() => setIsProfileOpen(true)}
                  className="bg-black text-white hover:bg-neutral-800 text-xs font-bold px-3 py-2 rounded-md transition-all flex items-center gap-2 shadow-xs"
                >
                  <User className="w-3.5 h-3.5 text-emerald-400" />
                  <span className="truncate max-w-[120px]">
                    {currentUser.role === 'admin' ? 'Admin' : currentUser.name.split(' ')[0]}
                  </span>
                  {currentUser.role === 'admin' && (
                    <span className="text-[9px] font-extrabold uppercase bg-emerald-500 text-black px-1.5 py-0.5 rounded">
                      ADMIN
                    </span>
                  )}
                </button>
                <button
                  onClick={handleLogout}
                  className="p-2 text-neutral-500 hover:text-black hover:bg-neutral-100 rounded-md transition-colors"
                  title="Log Out"
                >
                  <LogOut className="w-4 h-4" />
                </button>
              </div>
            ) : (
              <button
                onClick={() => {
                  setAuthTab('login');
                  setAuthError(null);
                  setIsAuthModalOpen(true);
                }}
                className="bg-white hover:bg-neutral-50 text-black border-2 border-black text-xs uppercase font-extrabold px-3.5 sm:px-4 py-2 rounded-md transition-all flex items-center gap-1.5 shadow-2xs hover:shadow-xs"
                title="Account Login"
              >
                <LogIn className="w-3.5 h-3.5" />
                <span>Log In</span>
              </button>
            )}

            {/* Shopping Bag Button */}
            <button
              onClick={() => setIsCartOpen(true)}
              className="relative p-2.5 rounded-full bg-black text-white hover:bg-neutral-800 transition-all shadow-xs"
              title="Shopping Bag"
            >
              <ShoppingBag className="w-4 h-4" />
              {cart.length > 0 && (
                <span className="absolute -top-1 -right-1 bg-emerald-500 text-white font-extrabold text-[10px] w-5 h-5 rounded-full flex items-center justify-center border-2 border-white shadow-xs">
                  {cart.reduce((sum, i) => sum + i.quantity, 0)}
                </span>
              )}
            </button>
          </div>
        </div>
      </header>

      {/* HERO SECTION (Notice the broken Milano card from Image 1 is completely removed) */}
      <section className="bg-white border-b border-neutral-200 py-12 sm:py-16 relative overflow-hidden">
        <div
          className="absolute inset-0 opacity-[0.03] pointer-events-none"
          style={{
            backgroundImage: `radial-gradient(#000 1px, transparent 1px)`,
            backgroundSize: '24px 24px'
          }}
        />

        <div className="max-w-5xl mx-auto px-4 sm:px-6 relative z-10 text-center">
          <div className="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-neutral-100 border border-neutral-300 text-[11px] font-bold uppercase tracking-wider text-neutral-800 mb-6 shadow-xs">
            <span className="text-amber-500">★★★★★</span>
            <span>{STORE_CONFIG.rating}</span>
            <span className="text-neutral-400">•</span>
            <span>{STORE_CONFIG.reviewsCount}</span>
            <span className="text-neutral-400">•</span>
            <span className="text-neutral-600">Jamal Khan, Chattogram</span>
          </div>

          <h1 className="font-serif-luxury text-4xl sm:text-6xl font-bold tracking-tight text-neutral-900 leading-[1.1] mb-6">
            Perfection in Vision.<br />
            <span className="italic font-normal">Italian Optics in Chittagong.</span>
          </h1>

          <p className="max-w-2xl mx-auto text-neutral-600 text-sm sm:text-base leading-relaxed mb-8">
            Welcome to <strong className="text-black font-semibold">Italy Optics</strong> at{' '}
            <strong className="text-black font-semibold">{STORE_CONFIG.address}</strong>. Discover bespoke Italian
            acetate frames, aerospace titanium eyeglasses, polarized UV400 sunglasses, and precision prescription
            lenses.
          </p>

          <div className="flex flex-wrap items-center justify-center gap-3 sm:gap-4 mb-10">
            <a
              href="#showroom"
              className="bg-black hover:bg-neutral-800 text-white text-xs font-bold uppercase tracking-wider px-6 py-3.5 rounded-md transition-all shadow-md flex items-center gap-2"
            >
              <span>Explore Eyewear Catalog</span>
              <ArrowRight className="w-3.5 h-3.5" />
            </a>
            <a
              href={`tel:${STORE_CONFIG.phone}`}
              className="bg-white hover:bg-neutral-50 text-neutral-900 border border-neutral-300 text-xs font-bold uppercase tracking-wider px-5 py-3.5 rounded-md transition-all shadow-xs flex items-center gap-2"
            >
              <Phone className="w-3.5 h-3.5 text-neutral-600" />
              <span>Call: {STORE_CONFIG.phone}</span>
            </a>
          </div>

          <div className="pt-6 border-t border-neutral-200 grid grid-cols-2 sm:grid-cols-4 gap-4 text-[11px] font-bold uppercase tracking-wider text-neutral-600">
            <div className="flex items-center justify-center gap-1.5">
              <Check className="w-3.5 h-3.5 text-black" />
              <span>Free Store Fitting</span>
            </div>
            <div className="flex items-center justify-center gap-1.5">
              <Check className="w-3.5 h-3.5 text-black" />
              <span>Computerized Lenses</span>
            </div>
            <div className="flex items-center justify-center gap-1.5">
              <Check className="w-3.5 h-3.5 text-black" />
              <span>Aerospace Titanium</span>
            </div>
            <div className="flex items-center justify-center gap-1.5">
              <Clock className="w-3.5 h-3.5 text-black" />
              <span>Open Closes 10 PM</span>
            </div>
          </div>
        </div>
      </section>

      {/* HIGHLIGHTS BAR */}
      <section className="bg-neutral-900 text-white py-4 border-b border-neutral-800 text-xs" id="about-store">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 flex flex-wrap items-center justify-between gap-4">
          <div className="flex items-center gap-2">
            <MapPin className="w-4 h-4 text-amber-400 shrink-0" />
            <span className="font-semibold text-neutral-200">Showroom Address:</span>
            <span className="text-neutral-400">{STORE_CONFIG.address}</span>
          </div>
          <div className="flex items-center gap-6 text-[11px] font-bold uppercase tracking-wider text-neutral-400">
            <span>✓ 2-Year Warranty</span>
            <span>✓ Ultrasonic Frame Spa</span>
            <span>✓ 24H Chittagong Dispatch</span>
          </div>
        </div>
      </section>


      {/* CATALOG FILTERS & SEARCH */}
      <section className="bg-white border-b border-neutral-200 py-6 sticky top-20 z-30 shadow-2xs" id="showroom">
        <div className="max-w-7xl mx-auto px-4 sm:px-6">
          <div className="flex items-center gap-2 overflow-x-auto no-scrollbar pb-3 sm:pb-0">
            {CATEGORIES.map(category => (
              <button
                key={category}
                onClick={() => setSelectedCategory(category)}
                className={`text-xs font-bold uppercase tracking-wider px-4 py-2.5 rounded-full transition-all shrink-0 ${
                  selectedCategory === category
                    ? 'bg-black text-white shadow-xs'
                    : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200'
                }`}
              >
                {category}
              </button>
            ))}
          </div>

          <div className="mt-4 pt-4 border-t border-neutral-100 flex flex-wrap items-center justify-between gap-3">
            <div className="flex items-center gap-2 flex-1 max-w-md">
              <div className="relative w-full">
                <Search className="w-4 h-4 text-neutral-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                  type="text"
                  value={searchQuery}
                  onChange={e => setSearchQuery(e.target.value)}
                  placeholder="Search titanium frames, polarized sunglasses, blue light..."
                  className="w-full text-xs pl-9 pr-4 py-2 bg-neutral-50 border border-neutral-200 rounded-md focus:bg-white focus:border-black outline-none transition-colors"
                />
              </div>
            </div>

            <div className="flex items-center gap-3 text-xs">
              <div className="flex items-center gap-1.5 text-neutral-500 font-semibold">
                <span className="hidden sm:inline">Gender:</span>
                <select
                  value={selectedGender}
                  onChange={e => setSelectedGender(e.target.value)}
                  className="bg-neutral-50 border border-neutral-200 rounded px-2.5 py-1.5 text-neutral-800 font-bold outline-none"
                >
                  <option value="All">All Genders</option>
                  <option value="Unisex">Unisex</option>
                  <option value="Men">Men</option>
                  <option value="Women">Women</option>
                  <option value="Kids">Kids</option>
                </select>
              </div>

              <div className="flex items-center gap-1.5 text-neutral-500 font-semibold">
                <span className="hidden sm:inline">Shape:</span>
                <select
                  value={selectedShape}
                  onChange={e => setSelectedShape(e.target.value)}
                  className="bg-neutral-50 border border-neutral-200 rounded px-2.5 py-1.5 text-neutral-800 font-bold outline-none"
                >
                  <option value="All">All Shapes</option>
                  <option value="Round">Round</option>
                  <option value="Wayfarer">Wayfarer</option>
                  <option value="Square">Square</option>
                  <option value="Cat-Eye">Cat-Eye</option>
                  <option value="Aviator">Aviator</option>
                  <option value="Rectangle">Rectangle</option>
                </select>
              </div>

              <select
                value={sortBy}
                onChange={e => setSortBy(e.target.value)}
                className="bg-neutral-50 border border-neutral-200 rounded px-2.5 py-1.5 text-neutral-800 font-bold outline-none"
              >
                <option value="featured">Featured First</option>
                <option value="price-low">Price: Low to High</option>
                <option value="price-high">Price: High to Low</option>
                <option value="rating">Top Rated (5.0)</option>
              </select>
            </div>
          </div>
        </div>
      </section>

      {/* PRODUCT GRID */}
      <main className="flex-1 max-w-7xl mx-auto px-4 sm:px-6 py-10 w-full">
        <div className="flex items-center justify-between mb-8">
          <div>
            <h2 className="font-serif-luxury text-2xl sm:text-3xl font-bold text-neutral-900">
              {selectedCategory === 'All Collections' ? 'Italian Luxury Showroom' : selectedCategory}
            </h2>
            <p className="text-xs text-neutral-500 mt-1">
              Showing {filteredProducts.length} curated frames in stock at 12 Jamal Khan Road, Chattogram
            </p>
          </div>
        </div>

        {filteredProducts.length === 0 ? (
          <div className="bg-white rounded-lg border border-neutral-200 p-12 text-center max-w-md mx-auto my-12">
            <SlidersHorizontal className="w-8 h-8 text-neutral-400 mx-auto mb-3" />
            <h3 className="font-serif-luxury text-xl font-bold text-neutral-800">
              No Matching Eyewear Found
            </h3>
            <p className="text-xs text-neutral-500 mt-1">
              Try adjusting your search terms or filters to browse other Italian frames.
            </p>
            <button
              onClick={() => {
                setSelectedCategory('All Collections');
                setSelectedGender('All');
                setSelectedShape('All');
                setSearchQuery('');
              }}
              className="mt-4 text-xs font-bold uppercase tracking-wider bg-black text-white px-4 py-2 rounded"
            >
              Reset Filters
            </button>
          </div>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            {filteredProducts.map(product => {
              const selectedColor =
                activeColors[product.id] || product.colors[0] || { name: 'Standard', hex: '#000' };

              return (
                <div
                  key={product.id}
                  className="bg-white rounded-lg border border-neutral-200 hover:border-black transition-all hover:shadow-lg flex flex-col group overflow-hidden"
                >
                  <div className="p-3 pb-0 flex items-center justify-between text-[10px] font-bold uppercase tracking-wider">
                    <span className="text-neutral-500">{product.brand}</span>
                    {product.inStock ? (
                      <span className="bg-black text-white px-2 py-0.5 rounded text-[9px]">
                        In Stock Jamal Khan
                      </span>
                    ) : (
                      <span className="bg-neutral-200 text-neutral-600 px-2 py-0.5 rounded text-[9px]">
                        Pre-Order
                      </span>
                    )}
                  </div>

                  <div
                    onClick={() => setSelectedProductForModal(product)}
                    className="relative aspect-4/3 bg-neutral-50 cursor-pointer overflow-hidden p-4 flex items-center justify-center"
                  >
                    <img
                      src={product.image}
                      alt={product.name}
                      className="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300"
                      onError={e => {
                        (e.target as HTMLImageElement).src =
                          VECTOR_EYEWEAR[product.fallbackSvgType || 'round'];
                      }}
                    />
                    <div className="absolute inset-0 bg-black/5 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                      <span className="bg-white text-black text-[11px] font-bold uppercase tracking-wider px-3 py-1.5 rounded-full shadow-md">
                        Quick View
                      </span>
                    </div>
                  </div>

                  <div className="p-4 flex-1 flex flex-col justify-between">
                    <div>
                      <div className="text-[10px] font-bold uppercase tracking-wider text-neutral-400 mb-1">
                        {product.category} • {product.shape}
                      </div>

                      <h3
                        onClick={() => setSelectedProductForModal(product)}
                        className="font-serif-luxury text-lg font-bold text-neutral-900 group-hover:text-neutral-700 cursor-pointer transition-colors leading-snug"
                      >
                        {product.name}
                      </h3>

                      <div className="flex items-baseline gap-2 mt-2">
                        <span className="font-sans text-base font-extrabold text-black">
                          {STORE_CONFIG.currency}
                          {product.price.toLocaleString()}
                        </span>
                        {product.originalPrice && (
                          <span className="text-xs text-neutral-400 line-through">
                            {STORE_CONFIG.currency}
                            {product.originalPrice.toLocaleString()}
                          </span>
                        )}
                      </div>

                      <div className="mt-3 pt-3 border-t border-neutral-100 flex items-center justify-between text-[11px]">
                        <span className="text-neutral-500 font-medium truncate max-w-[120px]">
                          {selectedColor.name}
                        </span>
                        <div className="flex items-center gap-1.5">
                          {product.colors.map((c, idx) => (
                            <button
                              key={idx}
                              type="button"
                              onClick={() =>
                                setActiveColors(prev => ({ ...prev, [product.id]: c }))
                              }
                              className={`w-3.5 h-3.5 rounded-full border transition-all ${
                                selectedColor.name === c.name
                                  ? 'ring-2 ring-black ring-offset-1 scale-110'
                                  : 'border-neutral-300 opacity-80 hover:opacity-100'
                              }`}
                              style={{ backgroundColor: c.hex }}
                              title={c.name}
                            />
                          ))}
                        </div>
                      </div>
                    </div>

                    <div className="mt-4 pt-3 border-t border-neutral-100 grid grid-cols-2 gap-2">
                      <button
                        onClick={() => handleAddToCart(product, selectedColor)}
                        className="bg-black hover:bg-neutral-800 text-white text-[11px] font-bold uppercase tracking-wider py-2 px-2.5 rounded transition-colors flex items-center justify-center gap-1.5"
                      >
                        <ShoppingBag className="w-3 h-3" />
                        <span>Add to Bag</span>
                      </button>
                      <a
                        href={`${STORE_CONFIG.whatsappUrl}?text=${encodeURIComponent(
                          `Hello Italy Optics, I want to order ${product.name} (${selectedColor.name}) at ৳${product.price}. Is it available at Jamal Khan store?`
                        )}`}
                        target="_blank"
                        rel="noreferrer"
                        className="bg-white hover:bg-neutral-50 text-neutral-800 border border-neutral-300 text-[11px] font-bold uppercase tracking-wider py-2 px-2 rounded transition-colors flex items-center justify-center gap-1 text-center"
                      >
                        <MessageCircle className="w-3 h-3 text-emerald-600 shrink-0" />
                        <span>WhatsApp</span>
                      </a>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </main>

      {/* ADMIN-ONLY STORE INVENTORY WORKSPACE (Rendered ONLY when authenticated as role === 'admin') */}
      {currentUser?.role === 'admin' && isAdminOpen && (
        <section className="bg-neutral-100 border-y border-neutral-300 py-10" id="admin-inventory">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 space-y-8">
            <div className="flex flex-wrap items-center justify-between gap-4 border-b border-neutral-300 pb-4">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 bg-black text-white rounded-md flex items-center justify-center">
                  <ShieldCheck className="w-5 h-5 text-emerald-400" />
                </div>
                <div>
                  <span className="text-[10px] font-extrabold uppercase tracking-widest text-emerald-700 block">
                    Authenticated Admin Session • {currentUser.email}
                  </span>
                  <h2 className="font-serif-luxury text-2xl font-bold text-neutral-900">
                    Showroom Inventory Management
                  </h2>
                </div>
              </div>
              <button
                type="button"
                onClick={() => setIsAdminOpen(false)}
                className="text-xs font-bold uppercase tracking-wider bg-white border border-neutral-300 hover:border-black px-3.5 py-2 rounded-md flex items-center gap-1.5"
              >
                <X className="w-3.5 h-3.5" />
                <span>Collapse Panel</span>
              </button>
            </div>

            {/* 1-Click Auto MySQL & PHP Export Bar */}
            <div className="bg-black text-white p-5 rounded-lg border border-neutral-800 flex flex-wrap items-center justify-between gap-4">
              <div className="flex items-start gap-3">
                <div className="w-9 h-9 rounded-md bg-neutral-900 border border-neutral-700 flex items-center justify-center shrink-0">
                  <Database className="w-4 h-4 text-emerald-400" />
                </div>
                <div>
                  <h3 className="text-sm font-bold tracking-wide">
                    1-Click Auto MySQL Database &amp; PHP Site Export
                  </h3>
                  <p className="text-[11px] text-neutral-400 mt-0.5">
                    Auto-creates <code className="text-white">italy_optics</code> MySQL database with all live frames, custom colors, SEO &amp; PHP files (<code className="text-emerald-400">setup.php?auto=1</code>).
                  </p>
                </div>
              </div>
              <div className="flex flex-wrap items-center gap-2">
                <button
                  type="button"
                  onClick={handleDownloadAutoMysql}
                  className="bg-white hover:bg-neutral-200 text-black text-[11px] font-bold uppercase tracking-wider px-3.5 py-2.5 rounded-md flex items-center gap-1.5 transition-colors"
                >
                  <Download className="w-3.5 h-3.5" />
                  <span>1-Click Download .SQL</span>
                </button>
                <button
                  type="button"
                  onClick={handleCopyAutoMysql}
                  className="bg-neutral-900 hover:bg-neutral-800 text-white border border-neutral-700 text-[11px] font-bold uppercase tracking-wider px-3 py-2.5 rounded-md flex items-center gap-1.5 transition-colors"
                >
                  <Copy className="w-3.5 h-3.5" />
                  <span>Copy SQL</span>
                </button>
                <a
                  href="/assets/italy-optics-php-mysql.zip"
                  download="italy-optics-php-mysql.zip"
                  className="bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-bold uppercase tracking-wider px-3.5 py-2.5 rounded-md flex items-center gap-1.5 transition-colors"
                >
                  <Download className="w-3.5 h-3.5" />
                  <span>Download PHP + MySQL (.ZIP)</span>
                </a>
              </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
              {/* Add Product Form */}
              <form
                onSubmit={handleAddProduct}
                className="lg:col-span-5 bg-white p-6 rounded-lg border border-neutral-200 shadow-xs space-y-4"
              >
                <div className="flex items-center gap-2 pb-3 border-b border-neutral-100">
                  <Plus className="w-4 h-4 text-black" />
                  <h3 className="font-bold text-sm uppercase tracking-wider text-neutral-900">
                    Add New Eyewear Frame
                  </h3>
                </div>

                {/* Image Upload & Studio Presets */}
                <div className="space-y-2.5 bg-neutral-50 p-3.5 rounded-md border border-neutral-200">
                  <div className="flex items-center justify-between">
                    <label className="text-[11px] font-bold uppercase tracking-wider text-neutral-700 flex items-center gap-1.5">
                      <ImageIcon className="w-3.5 h-3.5" />
                      <span>Product Image</span>
                    </label>
                    {newProdImage && (
                      <button
                        type="button"
                        onClick={() => setNewProdImage('')}
                        className="text-[10px] font-bold text-red-600 hover:underline"
                      >
                        Clear Image
                      </button>
                    )}
                  </div>

                  <div className="aspect-4/2 bg-white border border-neutral-200 rounded flex items-center justify-center overflow-hidden p-2">
                    {newProdImage ? (
                      <img
                        src={newProdImage}
                        alt="Frame Preview"
                        className="max-h-32 object-contain"
                      />
                    ) : (
                      <span className="text-xs text-neutral-400">No image selected</span>
                    )}
                  </div>

                  <div className="flex flex-wrap gap-2">
                    <label className="flex-1 cursor-pointer bg-black hover:bg-neutral-800 text-white text-[11px] font-bold uppercase tracking-wider py-2 px-3 rounded text-center flex items-center justify-center gap-1.5">
                      <Upload className="w-3.5 h-3.5" />
                      <span>Upload Photo</span>
                      <input
                        type="file"
                        accept="image/*"
                        onChange={handleAdminImageUpload}
                        className="hidden"
                      />
                    </label>
                  </div>

                  <div>
                    <span className="text-[10px] font-bold uppercase tracking-wider text-neutral-500 block mb-1.5">
                      Or Pick Studio Vector Preset:
                    </span>
                    <div className="grid grid-cols-3 gap-1.5">
                      {STUDIO_PRESETS.map(preset => (
                        <button
                          key={preset.id}
                          type="button"
                          onClick={() => setNewProdImage(preset.url)}
                          className={`text-[10px] font-semibold p-1.5 rounded border text-left truncate transition-colors ${
                            newProdImage === preset.url
                              ? 'border-black bg-black text-white'
                              : 'border-neutral-200 bg-white hover:border-neutral-400 text-neutral-700'
                          }`}
                          title={preset.title}
                        >
                          {preset.icon} {preset.category}
                        </button>
                      ))}
                    </div>
                  </div>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div className="sm:col-span-2">
                    <label className="block text-[10px] font-bold uppercase tracking-wider text-neutral-600 mb-1">
                      Model Name *
                    </label>
                    <input
                      type="text"
                      required
                      value={newProdName}
                      onChange={e => setNewProdName(e.target.value)}
                      placeholder="e.g. Napoli Carbon Titanium"
                      className="w-full text-xs p-2 bg-neutral-50 border border-neutral-300 rounded focus:bg-white focus:border-black outline-none"
                    />
                  </div>
                  <div>
                    <label className="block text-[10px] font-bold uppercase tracking-wider text-neutral-600 mb-1">
                      Brand
                    </label>
                    <input
                      type="text"
                      value={newProdBrand}
                      onChange={e => setNewProdBrand(e.target.value)}
                      className="w-full text-xs p-2 bg-neutral-50 border border-neutral-300 rounded outline-none"
                    />
                  </div>
                  <div>
                    <label className="block text-[10px] font-bold uppercase tracking-wider text-neutral-600 mb-1">
                      Category
                    </label>
                    <select
                      value={newProdCategory}
                      onChange={e => setNewProdCategory(e.target.value)}
                      className="w-full text-xs p-2 bg-neutral-50 border border-neutral-300 rounded outline-none"
                    >
                      {CATEGORIES.filter(c => c !== 'All Collections').map(cat => (
                        <option key={cat} value={cat}>
                          {cat}
                        </option>
                      ))}
                    </select>
                  </div>
                  <div>
                    <label className="block text-[10px] font-bold uppercase tracking-wider text-neutral-600 mb-1">
                      Sale Price (৳) *
                    </label>
                    <input
                      type="number"
                      required
                      min={100}
                      value={newProdPrice}
                      onChange={e => setNewProdPrice(e.target.value)}
                      className="w-full text-xs p-2 bg-neutral-50 border border-neutral-300 rounded outline-none"
                    />
                  </div>
                  <div>
                    <label className="block text-[10px] font-bold uppercase tracking-wider text-neutral-600 mb-1">
                      Original Price (৳)
                    </label>
                    <input
                      type="number"
                      min={100}
                      value={newProdOriginalPrice}
                      onChange={e => setNewProdOriginalPrice(e.target.value)}
                      className="w-full text-xs p-2 bg-neutral-50 border border-neutral-300 rounded outline-none"
                    />
                  </div>
                  <div>
                    <label className="block text-[10px] font-bold uppercase tracking-wider text-neutral-600 mb-1">
                      Gender
                    </label>
                    <select
                      value={newProdGender}
                      onChange={e => setNewProdGender(e.target.value as Product['gender'])}
                      className="w-full text-xs p-2 bg-neutral-50 border border-neutral-300 rounded outline-none"
                    >
                      <option value="Unisex">Unisex</option>
                      <option value="Men">Men</option>
                      <option value="Women">Women</option>
                      <option value="Kids">Kids</option>
                    </select>
                  </div>
                  <div>
                    <label className="block text-[10px] font-bold uppercase tracking-wider text-neutral-600 mb-1">
                      Shape
                    </label>
                    <select
                      value={newProdShape}
                      onChange={e => setNewProdShape(e.target.value as Product['shape'])}
                      className="w-full text-xs p-2 bg-neutral-50 border border-neutral-300 rounded outline-none"
                    >
                      <option value="Round">Round</option>
                      <option value="Wayfarer">Wayfarer</option>
                      <option value="Square">Square</option>
                      <option value="Cat-Eye">Cat-Eye</option>
                      <option value="Aviator">Aviator</option>
                      <option value="Rectangle">Rectangle</option>
                    </select>
                  </div>
                </div>

                <div className="p-4 rounded-lg border border-neutral-200 bg-neutral-50/50 space-y-3.5">
                  <h4 className="font-serif-luxury text-lg font-bold text-neutral-900 pb-2 border-b border-neutral-200">
                    Color Options &amp; Finishes
                  </h4>

                  <div className="flex flex-wrap gap-2">
                    {newProdColors.length === 0 ? (
                      <span className="text-[11px] text-neutral-400 italic">
                        No colors added yet — add a finish below.
                      </span>
                    ) : (
                      newProdColors.map(c => (
                        <div
                          key={c.name}
                          className="inline-flex items-center gap-2 px-3 py-1.5 rounded-md border border-neutral-300 bg-white text-xs font-medium text-neutral-900 shadow-2xs"
                        >
                          <span
                            className="w-3.5 h-3.5 rounded-full border border-neutral-300 shrink-0"
                            style={{ backgroundColor: c.hex }}
                          />
                          <span>{c.name}</span>
                          <button
                            type="button"
                            onClick={() => handleRemoveProductColor(c.name)}
                            className="text-neutral-400 hover:text-black ml-0.5 transition-colors"
                            title={`Remove ${c.name}`}
                          >
                            <X className="w-3.5 h-3.5" />
                          </button>
                        </div>
                      ))
                    )}
                  </div>

                  <div className="flex items-center gap-2">
                    <input
                      type="text"
                      value={customColorName}
                      onChange={e => setCustomColorName(e.target.value)}
                      onKeyDown={e => {
                        if (e.key === 'Enter') {
                          e.preventDefault();
                          handleAddCustomColor();
                        }
                      }}
                      placeholder="New color name (e.g. Brushed Gold)"
                      className="flex-1 min-w-0 text-xs px-3 py-2.5 bg-white border border-neutral-300 rounded-md focus:border-black outline-none"
                    />
                    <input
                      type="color"
                      value={customColorHex}
                      onChange={e => setCustomColorHex(e.target.value)}
                      title="Pick frame finish color"
                      className="w-10 h-10 p-1 bg-white border border-neutral-300 rounded-md cursor-pointer shrink-0"
                    />
                    <button
                      type="button"
                      onClick={handleAddCustomColor}
                      className="bg-black hover:bg-neutral-800 text-white text-xs font-bold px-4 py-2.5 rounded-md transition-colors shrink-0"
                    >
                      Add Color
                    </button>
                  </div>
                </div>

                <div>
                  <label className="block text-[10px] font-bold uppercase tracking-wider text-neutral-600 mb-1">
                    Description
                  </label>
                  <textarea
                    rows={2}
                    value={newProdDescription}
                    onChange={e => setNewProdDescription(e.target.value)}
                    placeholder="Italian titanium craftsmanship..."
                    className="w-full text-xs p-2 bg-neutral-50 border border-neutral-300 rounded outline-none"
                  />
                </div>

                <button
                  type="submit"
                  className="w-full bg-black hover:bg-neutral-800 text-white text-xs font-bold uppercase tracking-wider py-3 rounded-md transition-colors"
                >
                  Publish Frame to Showroom
                </button>
              </form>

              {/* Live Inventory List */}
              <div className="lg:col-span-7 bg-white p-6 rounded-lg border border-neutral-200 shadow-xs flex flex-col">
                <div className="flex items-center justify-between pb-3 border-b border-neutral-100 mb-4">
                  <div className="flex items-center gap-2">
                    <Package className="w-4 h-4 text-black" />
                    <h3 className="font-bold text-sm uppercase tracking-wider text-neutral-900">
                      Active Catalog ({products.length} Frames)
                    </h3>
                  </div>
                  <span className="text-[11px] font-semibold text-neutral-500">
                    Customer Orders: {userOrders.length}
                  </span>
                </div>

                <div className="divide-y divide-neutral-100 max-h-[540px] overflow-y-auto pr-1">
                  {products.map(prod => (
                    <div
                      key={prod.id}
                      className="py-3 flex items-center justify-between gap-3"
                    >
                      <div className="flex items-center gap-3 min-w-0">
                        <div className="w-14 h-11 rounded border border-neutral-200 bg-neutral-50 overflow-hidden shrink-0 flex items-center justify-center p-1">
                          <img
                            src={prod.image}
                            alt={prod.name}
                            className="w-full h-full object-contain"
                            onError={e => {
                              (e.target as HTMLImageElement).src =
                                VECTOR_EYEWEAR[prod.fallbackSvgType || 'round'];
                            }}
                          />
                        </div>
                        <div className="min-w-0">
                          <h4 className="text-xs font-bold text-neutral-900 truncate">
                            {prod.name}
                          </h4>
                          <div className="text-[11px] text-neutral-500">
                            {prod.category} • {STORE_CONFIG.currency}
                            {prod.price.toLocaleString()}
                          </div>
                        </div>
                      </div>

                      <div className="flex items-center gap-2 shrink-0">
                        <button
                          type="button"
                          onClick={() => handleToggleStock(prod.id)}
                          className={`text-[10px] font-bold uppercase px-2.5 py-1 rounded border transition-colors ${
                            prod.inStock
                              ? 'bg-emerald-50 text-emerald-800 border-emerald-200 hover:bg-emerald-100'
                              : 'bg-neutral-100 text-neutral-600 border-neutral-300 hover:bg-neutral-200'
                          }`}
                        >
                          {prod.inStock ? 'In Stock' : 'Pre-Order'}
                        </button>
                        <button
                          type="button"
                          onClick={() => handleDeleteProduct(prod.id, prod.name)}
                          className="p-1.5 text-neutral-400 hover:text-red-600 rounded hover:bg-red-50 transition-colors"
                          title="Delete Product"
                        >
                          <Trash2 className="w-4 h-4" />
                        </button>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>
        </section>
      )}


      {/* CUSTOMER REVIEWS & GOOGLE RATING SECTION */}
      <section className="bg-white border-b border-neutral-200 py-12" id="reviews">
        <div className="max-w-7xl mx-auto px-4 sm:px-6">
          <div className="text-center max-w-2xl mx-auto mb-10">
            <div className="flex items-center justify-center gap-1 text-amber-500 text-base mb-2">
              ★★★★★
            </div>
            <h2 className="font-serif-luxury text-3xl font-bold text-neutral-900">
              5.0 Star Rated Optical Shop in Chittagong
            </h2>
            <p className="text-xs text-neutral-500 mt-2">
              Verified customer feedback from 12 Jamal Khan Road, Chattogram. Trusted by doctors, students, and executives.
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            {INITIAL_REVIEWS.map(rev => (
              <div
                key={rev.id}
                className="p-6 rounded-lg border border-neutral-200 bg-neutral-50/50 flex flex-col justify-between"
              >
                <div>
                  <div className="flex items-center justify-between mb-3">
                    <div className="text-amber-500 text-xs font-bold">★★★★★ 5.0</div>
                    <span className="text-[10px] text-neutral-400 font-semibold">{rev.date}</span>
                  </div>
                  <p className="text-xs text-neutral-700 leading-relaxed italic">
                    "{rev.text}"
                  </p>
                </div>
                <div className="mt-4 pt-3 border-t border-neutral-200/60 flex items-center justify-between">
                  <div>
                    <h4 className="text-xs font-bold text-neutral-900">{rev.name}</h4>
                    <span className="text-[10px] text-neutral-500">{rev.location}</span>
                  </div>
                  <span className="text-[9px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">
                    Verified Buyer
                  </span>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* MULTI-CHANNEL CONTACT SECTION */}
      <section className="bg-neutral-50 py-14 border-b border-neutral-200" id="contact-channels">
        <div className="max-w-7xl mx-auto px-4 sm:px-6">
          <div className="text-center max-w-2xl mx-auto mb-10">
            <span className="text-[11px] font-bold uppercase tracking-wider text-neutral-500 block mb-1">
              Direct Communication
            </span>
            <h2 className="font-serif-luxury text-3xl font-bold text-neutral-900">
              Contact Italy Optics
            </h2>
            <p className="text-xs text-neutral-600 mt-1">
              Reach our optical specialists via Phone, WhatsApp, Facebook, or visit our showroom in Jamal Khan.
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a
              href={`tel:${STORE_CONFIG.phone}`}
              className="bg-white p-6 rounded-lg border border-neutral-200 hover:border-black transition-all flex items-start gap-4 group shadow-xs"
            >
              <div className="w-12 h-12 bg-black text-white rounded flex items-center justify-center shrink-0">
                <Phone className="w-5 h-5" />
              </div>
              <div>
                <span className="text-[10px] font-bold uppercase tracking-wider text-neutral-400 block">
                  Hotline Consultation
                </span>
                <span className="font-bold text-base text-neutral-900 block mt-0.5 group-hover:text-black">
                  {STORE_CONFIG.phone}
                </span>
                <span className="text-xs text-neutral-500 block mt-1">
                  Open everyday · 10 AM to 10 PM
                </span>
              </div>
            </a>

            <a
              href={STORE_CONFIG.whatsappUrl}
              target="_blank"
              rel="noreferrer"
              className="bg-white p-6 rounded-lg border border-neutral-200 hover:border-black transition-all flex items-start gap-4 group shadow-xs"
            >
              <div className="w-12 h-12 bg-emerald-600 text-white rounded flex items-center justify-center shrink-0">
                <MessageCircle className="w-5 h-5" />
              </div>
              <div>
                <span className="text-[10px] font-bold uppercase tracking-wider text-neutral-400 block">
                  WhatsApp Direct
                </span>
                <span className="font-bold text-base text-neutral-900 block mt-0.5 group-hover:text-emerald-700">
                  Chat & Send Prescription
                </span>
                <span className="text-xs text-neutral-500 block mt-1">
                  Fast response & lens quotations
                </span>
              </div>
            </a>

            <div className="bg-white p-6 rounded-lg border border-neutral-200 flex items-start gap-4 shadow-xs">
              <div className="w-12 h-12 bg-neutral-900 text-white rounded flex items-center justify-center shrink-0">
                <MapPin className="w-5 h-5" />
              </div>
              <div>
                <span className="text-[10px] font-bold uppercase tracking-wider text-neutral-400 block">
                  Showroom Location
                </span>
                <span className="font-bold text-sm text-neutral-900 block mt-0.5">
                  {STORE_CONFIG.address}
                </span>
                <span className="text-xs text-neutral-500 block mt-1">
                  Opposite St. Placid's / Jamal Khan Mor
                </span>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* FOOTER */}
      <footer className="bg-black text-white pt-12 pb-8 border-t border-neutral-800 text-xs">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 grid grid-cols-1 md:grid-cols-4 gap-8 mb-10">
          <div className="space-y-3">
            <span className="font-serif-luxury text-2xl font-bold text-white tracking-wide uppercase block">
              Italy Optics
            </span>
            <span className="text-[10px] uppercase font-bold tracking-[0.2em] text-neutral-400 block">
              ইতালি অপটিক্স · EST. JAMAL KHAN
            </span>
            <p className="text-neutral-400 text-xs leading-relaxed">
              Luxury monochrome eyewear studio offering bespoke Italian acetate frames, aerospace titanium eyeglasses, and computerized optical lens crafting.
            </p>
          </div>

          <div className="space-y-2">
            <h4 className="font-bold uppercase tracking-wider text-neutral-200 text-xs mb-3">
              Showroom Location
            </h4>
            <p className="text-neutral-400 flex items-start gap-2">
              <MapPin className="w-4 h-4 text-neutral-200 shrink-0 mt-0.5" />
              <span>{STORE_CONFIG.address}</span>
            </p>
            <p className="text-neutral-400 flex items-center gap-2">
              <Clock className="w-4 h-4 text-neutral-200 shrink-0" />
              <span>{STORE_CONFIG.hours}</span>
            </p>
            <p className="text-neutral-400 flex items-center gap-2">
              <Phone className="w-4 h-4 text-neutral-200 shrink-0" />
              <span>Call & WhatsApp: {STORE_CONFIG.phone}</span>
            </p>
          </div>

          <div className="space-y-2">
            <h4 className="font-bold uppercase tracking-wider text-neutral-200 text-xs mb-3">
              Collections
            </h4>
            <ul className="space-y-1.5 text-neutral-400">
              <li>Aerospace Titanium Frames</li>
              <li>Polarized UV400 Sunglasses</li>
              <li>Firenze Blue Light Defense</li>
              <li>Executive Half-Rim Readers</li>
              <li>Hydrogel Contact Lenses</li>
            </ul>
          </div>

          <div className="space-y-2">
            <h4 className="font-bold uppercase tracking-wider text-neutral-200 text-xs mb-3">
              Account Access
            </h4>
            <div className="space-y-2">
              {currentUser ? (
                <div>
                  <span className="text-neutral-300 block font-bold">{currentUser.name}</span>
                  <span className="text-neutral-500 text-[10px] block">{currentUser.email}</span>
                  <button
                    onClick={handleLogout}
                    className="text-red-400 hover:text-red-300 underline text-xs mt-1"
                  >
                    Log Out
                  </button>
                </div>
              ) : (
                <button
                  onClick={() => {
                    setAuthTab('login');
                    setAuthError(null);
                    setIsAuthModalOpen(true);
                  }}
                  className="bg-neutral-800 hover:bg-neutral-700 text-white font-bold px-3 py-1.5 rounded flex items-center gap-1.5 text-[11px]"
                >
                  <LogIn className="w-3.5 h-3.5" />
                  <span>Log In to Account</span>
                </button>
              )}
            </div>
          </div>
        </div>

        <div className="max-w-7xl mx-auto px-4 sm:px-6 pt-6 border-t border-neutral-900 flex flex-wrap items-center justify-between gap-4 text-neutral-500 text-[11px]">
          <div>
            © {new Date().getFullYear()} Italy Optics. All rights reserved. 12 Jamal Khan Road, Chattogram 4000.
          </div>
          <div className="flex items-center gap-4">
            <span>Designed for Luxury Monochrome Eyewear</span>
            <span>•</span>
            <span className="text-emerald-400 font-semibold">Live Operational</span>
          </div>
        </div>
      </footer>

      {/* ========================================================================= */}
      {/* AUTHENTICATION MODAL (ADMIN & CUSTOMER LOG IN / SIGN UP)                  */}
      {/* ========================================================================= */}
      {isAuthModalOpen && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 animate-in fade-in duration-150">
          <div className="bg-white max-w-md w-full rounded-lg border border-neutral-200 p-6 sm:p-8 shadow-2xl relative">
            <button
              onClick={() => setIsAuthModalOpen(false)}
              className="absolute top-4 right-4 p-1.5 text-neutral-400 hover:text-black rounded-full hover:bg-neutral-100"
            >
              <X className="w-5 h-5" />
            </button>

            <div className="text-center mb-6">
              <span className="text-3xl block mb-1">👓</span>
              <h3 className="font-serif-luxury text-2xl font-bold text-neutral-900">
                Italy Optics Account
              </h3>
              <p className="text-xs text-neutral-500 mt-1">
                12 Jamal Khan Road, Chattogram 4000
              </p>
            </div>

            {/* Auth Mode Tabs */}
            <div className="grid grid-cols-2 border border-black rounded-md overflow-hidden mb-5 text-[11px] font-bold uppercase tracking-wider text-center">
              <button
                type="button"
                onClick={() => {
                  setAuthTab('login');
                  setAuthError(null);
                }}
                className={`py-2.5 transition-colors ${
                  authTab === 'login' ? 'bg-black text-white' : 'bg-white text-black hover:bg-neutral-100'
                }`}
              >
                Sign In
              </button>
              <button
                type="button"
                onClick={() => {
                  setAuthTab('register');
                  setAuthError(null);
                }}
                className={`py-2.5 transition-colors border-l border-black ${
                  authTab === 'register' ? 'bg-black text-white' : 'bg-white text-black hover:bg-neutral-100'
                }`}
              >
                Sign Up
              </button>
            </div>

            {authError && (
              <div className="mb-4 p-3 rounded-md bg-red-50 border border-red-200 text-red-700 text-xs flex items-start gap-2">
                <AlertCircle className="w-4 h-4 shrink-0 mt-0.5" />
                <span>{authError}</span>
              </div>
            )}

            {/* Form */}
            <form onSubmit={handleAuthSubmit} className="space-y-4">
              {authTab === 'register' && (
                <div>
                  <label className="block text-[11px] font-bold uppercase tracking-wider text-neutral-700 mb-1">
                    Full Name *
                  </label>
                  <input
                    type="text"
                    required
                    value={authName}
                    onChange={e => setAuthName(e.target.value)}
                    placeholder="e.g. Asif Chowdhury"
                    className="w-full text-xs p-2.5 bg-neutral-50 border border-neutral-300 rounded focus:bg-white focus:border-black outline-none"
                  />
                </div>
              )}

              <div>
                <label className="block text-[11px] font-bold uppercase tracking-wider text-neutral-700 mb-1">
                  Email Address *
                </label>
                <input
                  type="email"
                  required
                  value={authEmail}
                  onChange={e => setAuthEmail(e.target.value)}
                  placeholder="admin@italyoptics.com or customer@italyoptics.com"
                  className="w-full text-xs p-2.5 bg-neutral-50 border border-neutral-300 rounded focus:bg-white focus:border-black outline-none"
                />
              </div>

              {authTab === 'register' && (
                <div>
                  <label className="block text-[11px] font-bold uppercase tracking-wider text-neutral-700 mb-1">
                    Phone Number (Chattogram)
                  </label>
                  <input
                    type="tel"
                    value={authPhone}
                    onChange={e => setAuthPhone(e.target.value)}
                    placeholder="018XXXXXXXX"
                    className="w-full text-xs p-2.5 bg-neutral-50 border border-neutral-300 rounded focus:bg-white focus:border-black outline-none"
                  />
                </div>
              )}

              <div>
                <label className="block text-[11px] font-bold uppercase tracking-wider text-neutral-700 mb-1">
                  Password *
                </label>
                <input
                  type="password"
                  required
                  value={authPassword}
                  onChange={e => setAuthPassword(e.target.value)}
                  placeholder="••••••••"
                  className="w-full text-xs p-2.5 bg-neutral-50 border border-neutral-300 rounded focus:bg-white focus:border-black outline-none"
                />
              </div>

              <button
                type="submit"
                className="w-full bg-black hover:bg-neutral-800 text-white text-xs font-bold uppercase tracking-wider py-3 rounded-md transition-colors shadow-xs"
              >
                {authTab === 'login' ? 'Sign In to Account' : 'Create New Account'}
              </button>
            </form>
          </div>
        </div>
      )}

      {/* USER PROFILE DRAWER */}
      {isProfileOpen && currentUser && (
        <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex justify-end animate-in fade-in duration-200">
          <div className="bg-white w-full max-w-md h-full shadow-2xl flex flex-col justify-between border-l border-neutral-200">
            <div className="p-5 border-b border-neutral-200 flex items-center justify-between">
              <div className="flex items-center gap-2">
                <User className="w-5 h-5 text-black" />
                <h3 className="font-serif-luxury text-xl font-bold text-neutral-900">
                  Account Dashboard
                </h3>
              </div>
              <button
                onClick={() => setIsProfileOpen(false)}
                className="p-1.5 text-neutral-400 hover:text-black rounded-full"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <div className="p-5 overflow-y-auto flex-1 space-y-6">
              {/* Profile Card */}
              <div className="bg-neutral-50 p-4 rounded-lg border border-neutral-200">
                <div className="flex items-center justify-between mb-2">
                  <span className="text-xs font-bold text-neutral-900">{currentUser.name}</span>
                  <span
                    className={`text-[9px] font-extrabold uppercase px-2 py-0.5 rounded ${
                      currentUser.role === 'admin'
                        ? 'bg-emerald-600 text-white'
                        : 'bg-black text-white'
                    }`}
                  >
                    {currentUser.role}
                  </span>
                </div>
                <div className="text-xs text-neutral-500 space-y-1">
                  <div>Email: {currentUser.email}</div>
                  <div>Phone: {currentUser.phone}</div>
                </div>
              </div>

              {currentUser.role === 'admin' && (
                <div className="bg-black text-white p-4 rounded-lg space-y-3">
                  <div className="flex items-center gap-2">
                    <ShieldCheck className="w-4 h-4 text-emerald-400" />
                    <span className="text-xs font-bold uppercase tracking-wider">
                      Administrator Controls
                    </span>
                  </div>
                  <p className="text-[11px] text-neutral-300 leading-relaxed">
                    Add frames, upload product photos, toggle stock status, or update showroom listings.
                  </p>
                  <button
                    type="button"
                    onClick={() => {
                      setIsAdminOpen(true);
                      setIsProfileOpen(false);
                    }}
                    className="w-full bg-white text-black hover:bg-neutral-100 text-xs font-bold uppercase tracking-wider py-2.5 rounded transition-colors"
                  >
                    Open Inventory Management
                  </button>
                </div>
              )}

              {/* Orders */}
              <div>
                <h4 className="text-xs font-bold uppercase tracking-wider text-neutral-800 mb-2.5">
                  Recent Eyewear Orders ({userOrders.length})
                </h4>
                {userOrders.length === 0 ? (
                  <p className="text-xs text-neutral-400">No orders placed yet.</p>
                ) : (
                  <div className="space-y-2">
                    {userOrders.map(ord => (
                      <div
                        key={ord.id}
                        className="p-3 rounded border border-neutral-200 bg-white text-xs space-y-1"
                      >
                        <div className="flex justify-between font-bold">
                          <span>Order #{ord.id}</span>
                          <span className="font-mono text-black">
                            {STORE_CONFIG.currency}
                            {ord.totalAmount.toLocaleString()}
                          </span>
                        </div>
                        <div className="text-neutral-500 text-[11px]">
                          {ord.items.length} items • {ord.deliveryType} • {ord.status}
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </div>

            <div className="p-5 border-t border-neutral-200 bg-neutral-50 space-y-2">
              <button
                onClick={handleLogout}
                className="w-full bg-white hover:bg-neutral-100 text-neutral-800 border border-neutral-300 text-xs font-bold uppercase tracking-wider py-2.5 rounded transition-colors"
              >
                Log Out
              </button>
            </div>
          </div>
        </div>
      )}

      {/* QUICK VIEW & SPECS MODAL */}
      {selectedProductForModal && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 animate-in fade-in duration-150">
          <div className="bg-white max-w-2xl w-full rounded-lg border border-neutral-200 shadow-2xl overflow-hidden relative">
            <button
              onClick={() => setSelectedProductForModal(null)}
              className="absolute top-4 right-4 p-2 text-neutral-400 hover:text-black rounded-full hover:bg-neutral-100 transition-colors z-10"
            >
              <X className="w-5 h-5" />
            </button>

            <div className="grid grid-cols-1 sm:grid-cols-2">
              <div className="bg-neutral-50 p-6 flex flex-col items-center justify-center border-b sm:border-b-0 sm:border-r border-neutral-200">
                <img
                  src={selectedProductForModal.image}
                  alt={selectedProductForModal.name}
                  className="w-full max-h-64 object-contain"
                  onError={e => {
                    (e.target as HTMLImageElement).src =
                      VECTOR_EYEWEAR[selectedProductForModal.fallbackSvgType || 'round'];
                  }}
                />
                <div className="mt-4 text-center">
                  <span className="text-[10px] font-bold uppercase tracking-wider text-neutral-400">
                    Hand-finished in Italy
                  </span>
                </div>
              </div>

              <div className="p-6 flex flex-col justify-between">
                <div>
                  <div className="flex items-center gap-2 mb-2">
                    <span className="text-[10px] font-bold uppercase tracking-wider bg-black text-white px-2 py-0.5 rounded">
                      In Stock Jamal Khan
                    </span>
                    <span className="text-amber-500 text-xs">★★★★★ 5.0</span>
                  </div>

                  <h3 className="font-serif-luxury text-2xl font-bold text-neutral-900">
                    {selectedProductForModal.name}
                  </h3>
                  <div className="text-xs text-neutral-500 mb-3">
                    {selectedProductForModal.brand} • {selectedProductForModal.category}
                  </div>

                  <div className="flex items-baseline gap-2 mb-4">
                    <span className="text-xl font-extrabold text-black font-sans">
                      {STORE_CONFIG.currency}
                      {selectedProductForModal.price.toLocaleString()}
                    </span>
                    {selectedProductForModal.originalPrice && (
                      <span className="text-sm text-neutral-400 line-through">
                        {STORE_CONFIG.currency}
                        {selectedProductForModal.originalPrice.toLocaleString()}
                      </span>
                    )}
                  </div>

                  <p className="text-xs text-neutral-600 leading-relaxed mb-4">
                    {selectedProductForModal.description}
                  </p>

                  <div className="space-y-1.5 mb-5">
                    {selectedProductForModal.features.map((feat, i) => (
                      <div key={i} className="text-[11px] text-neutral-700 flex items-center gap-2">
                        <Check className="w-3.5 h-3.5 text-black shrink-0" />
                        <span>{feat}</span>
                      </div>
                    ))}
                  </div>

                  <div className="mb-6">
                    <div className="text-[11px] font-bold uppercase tracking-wider text-neutral-800 mb-2">
                      Available Color Finishes:
                    </div>
                    <div className="flex flex-wrap gap-2">
                      {selectedProductForModal.colors.map((c, i) => (
                        <button
                          key={i}
                          onClick={() =>
                            setActiveColors(prev => ({
                              ...prev,
                              [selectedProductForModal.id]: c
                            }))
                          }
                          className="inline-flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full border border-neutral-300 hover:border-black"
                        >
                          <span
                            className="w-3 h-3 rounded-full border border-neutral-300"
                            style={{ backgroundColor: c.hex }}
                          />
                          <span>{c.name}</span>
                        </button>
                      ))}
                    </div>
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-3 pt-4 border-t border-neutral-100">
                  <button
                    onClick={() => {
                      handleAddToCart(
                        selectedProductForModal,
                        activeColors[selectedProductForModal.id] || selectedProductForModal.colors[0]
                      );
                      setSelectedProductForModal(null);
                    }}
                    className="bg-black hover:bg-neutral-800 text-white text-xs font-bold uppercase tracking-wider py-3 rounded text-center"
                  >
                    Add to Bag
                  </button>
                  <a
                    href={`${STORE_CONFIG.whatsappUrl}?text=${encodeURIComponent(
                      `Hello Italy Optics, I am interested in ${selectedProductForModal.name} (৳${selectedProductForModal.price}).`
                    )}`}
                    target="_blank"
                    rel="noreferrer"
                    className="bg-neutral-100 hover:bg-neutral-200 text-neutral-800 text-xs font-bold uppercase tracking-wider py-3 rounded text-center flex items-center justify-center gap-1.5"
                  >
                    <MessageCircle className="w-3.5 h-3.5 text-emerald-600" />
                    <span>WhatsApp</span>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* SHOPPING BAG DRAWER */}
      {isCartOpen && (
        <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex justify-end animate-in fade-in duration-200">
          <div className="bg-white w-full max-w-md h-full shadow-2xl flex flex-col justify-between border-l border-neutral-200">
            <div className="p-5 border-b border-neutral-200 flex items-center justify-between">
              <div className="flex items-center gap-2">
                <ShoppingBag className="w-5 h-5 text-black" />
                <h3 className="font-serif-luxury text-xl font-bold text-neutral-900">
                  Your Eyewear Bag ({cart.reduce((s, i) => s + i.quantity, 0)})
                </h3>
              </div>
              <button
                onClick={() => setIsCartOpen(false)}
                className="p-1.5 text-neutral-400 hover:text-black rounded-full"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <div className="p-5 overflow-y-auto flex-1 divide-y divide-neutral-100">
              {cart.length === 0 ? (
                <div className="text-center py-16 text-neutral-500">
                  <ShoppingBag className="w-10 h-10 text-neutral-300 mx-auto mb-3" />
                  <p className="font-bold text-neutral-800">Your bag is empty</p>
                  <p className="text-xs mt-1">Explore our Italian titanium & acetate collection.</p>
                  <button
                    onClick={() => setIsCartOpen(false)}
                    className="mt-4 text-xs font-bold uppercase tracking-wider bg-black text-white px-5 py-2.5 rounded"
                  >
                    Start Shopping
                  </button>
                </div>
              ) : (
                <div className="space-y-4 py-2">
                  {cart.map(item => (
                    <div key={item.id} className="flex gap-3 items-center justify-between pt-3">
                      <div className="flex items-center gap-3">
                        <div className="w-14 h-12 rounded border border-neutral-200 bg-neutral-50 overflow-hidden shrink-0 flex items-center justify-center">
                          <img
                            src={item.product.image}
                            alt={item.product.name}
                            className="w-full h-full object-cover"
                            onError={e => {
                              (e.target as HTMLImageElement).src = VECTOR_EYEWEAR.round;
                            }}
                          />
                        </div>
                        <div>
                          <h4 className="text-xs font-bold text-neutral-900">{item.product.name}</h4>
                          <div className="text-[11px] text-neutral-500 flex items-center gap-1.5 mt-0.5">
                            <span
                              className="w-2.5 h-2.5 rounded-full border border-neutral-300"
                              style={{ backgroundColor: item.selectedColor.hex }}
                            />
                            <span>{item.selectedColor.name}</span>
                          </div>
                          <div className="text-xs font-mono font-bold text-black mt-1">
                            {STORE_CONFIG.currency}
                            {(item.product.price * item.quantity).toLocaleString()}
                          </div>
                        </div>
                      </div>

                      <div className="flex items-center gap-2 border border-neutral-300 rounded px-2 py-1">
                        <button
                          onClick={() => handleUpdateQuantity(item.id, -1)}
                          className="text-neutral-500 hover:text-black font-bold px-1"
                        >
                          -
                        </button>
                        <span className="text-xs font-bold min-w-[16px] text-center">
                          {item.quantity}
                        </span>
                        <button
                          onClick={() => handleUpdateQuantity(item.id, 1)}
                          className="text-neutral-500 hover:text-black font-bold px-1"
                        >
                          +
                        </button>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>

            {cart.length > 0 && (
              <form onSubmit={handleCompleteOrder} className="p-5 border-t border-neutral-200 bg-neutral-50 space-y-4">
                <div className="space-y-2">
                  <div className="flex justify-between text-xs text-neutral-600">
                    <span>Subtotal</span>
                    <span className="font-mono font-semibold">
                      {STORE_CONFIG.currency}
                      {cartSubtotal.toLocaleString()}
                    </span>
                  </div>
                  <div className="flex justify-between text-xs text-neutral-600">
                    <span>Delivery ({deliveryType === 'pickup' ? 'Jamal Khan' : 'Courier'})</span>
                    <span className="font-mono font-semibold">
                      {shippingFee === 0 ? 'FREE' : `${STORE_CONFIG.currency}${shippingFee}`}
                    </span>
                  </div>
                  <div className="flex justify-between text-sm font-bold text-black pt-2 border-t border-neutral-200">
                    <span>Total Amount</span>
                    <span className="font-mono font-extrabold text-base">
                      {STORE_CONFIG.currency}
                      {cartTotal.toLocaleString()}
                    </span>
                  </div>
                </div>

                <div>
                  <label className="block text-[10px] font-bold uppercase tracking-wider text-neutral-700 mb-1">
                    Delivery Option
                  </label>
                  <select
                    value={deliveryType}
                    onChange={e => setDeliveryType(e.target.value as any)}
                    className="w-full text-xs p-2 bg-white border border-neutral-300 rounded outline-none"
                  >
                    <option value="chattogram">Inside Chittagong (Jamal Khan / GEC / Agrabad - ৳60)</option>
                    <option value="pickup">Store Pickup at 12 Jamal Khan Road (FREE)</option>
                    <option value="outside">Outside Chittagong (Courier Express - ৳120)</option>
                  </select>
                </div>

                <div className="grid grid-cols-2 gap-2">
                  <input
                    type="text"
                    required
                    placeholder="Your Name *"
                    value={checkoutName}
                    onChange={e => setCheckoutName(e.target.value)}
                    className="w-full text-xs p-2 bg-white border border-neutral-300 rounded outline-none"
                  />
                  <input
                    type="tel"
                    required
                    placeholder="Phone (01... ) *"
                    value={checkoutPhone}
                    onChange={e => setCheckoutPhone(e.target.value)}
                    className="w-full text-xs p-2 bg-white border border-neutral-300 rounded outline-none"
                  />
                </div>

                <input
                  type="text"
                  required
                  placeholder="Delivery Address / Road Name *"
                  value={checkoutAddress}
                  onChange={e => setCheckoutAddress(e.target.value)}
                  className="w-full text-xs p-2 bg-white border border-neutral-300 rounded outline-none"
                />

                <button
                  type="submit"
                  className="w-full bg-black hover:bg-neutral-800 text-white text-xs font-bold uppercase tracking-wider py-3.5 rounded-md transition-colors flex items-center justify-center gap-2"
                >
                  <Check className="w-4 h-4" />
                  <span>Confirm Order ({STORE_CONFIG.currency}{cartTotal.toLocaleString()})</span>
                </button>
              </form>
            )}
          </div>
        </div>
      )}

      {/* ORDER CONFIRMATION MODAL */}
      {orderConfirmation && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white max-w-md w-full rounded-lg border border-neutral-200 p-6 text-center shadow-2xl animate-in zoom-in-95 duration-150">
            <div className="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
              <Check className="w-6 h-6 stroke-[3]" />
            </div>

            <h3 className="font-serif-luxury text-2xl font-bold text-neutral-900">
              Order Confirmed!
            </h3>
            <p className="text-xs text-neutral-600 mt-1">
              Thank you, {orderConfirmation.customerName}. Your order reference is:
            </p>
            <div className="my-3 px-4 py-2 bg-neutral-100 rounded text-sm font-mono font-bold text-black border border-neutral-300 inline-block">
              {orderConfirmation.id}
            </div>

            <div className="text-left bg-neutral-50 p-4 rounded text-xs space-y-1.5 border border-neutral-200 my-4">
              <div className="flex justify-between">
                <span className="text-neutral-500">Store Showroom:</span>
                <span className="font-bold text-neutral-800">12 Jamal Khan Road</span>
              </div>
              <div className="flex justify-between">
                <span className="text-neutral-500">Contact Phone:</span>
                <span className="font-bold text-neutral-800">{orderConfirmation.phone}</span>
              </div>
              <div className="flex justify-between border-t border-neutral-200 pt-1.5 font-bold">
                <span>Total Amount:</span>
                <span className="font-mono text-black">
                  {STORE_CONFIG.currency}
                  {orderConfirmation.totalAmount.toLocaleString()}
                </span>
              </div>
            </div>

            <div className="space-y-2">
              <a
                href={`${STORE_CONFIG.whatsappUrl}?text=${encodeURIComponent(
                  `Hello Italy Optics, I just placed order #${orderConfirmation.id} for ৳${orderConfirmation.totalAmount}. Please confirm.`
                )}`}
                target="_blank"
                rel="noreferrer"
                className="w-full bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold uppercase tracking-wider py-3 rounded-md flex items-center justify-center gap-2 transition-colors"
              >
                <MessageCircle className="w-4 h-4" />
                <span>Message Store via WhatsApp</span>
              </a>

              <button
                onClick={() => setOrderConfirmation(null)}
                className="w-full bg-neutral-100 hover:bg-neutral-200 text-neutral-800 text-xs font-bold uppercase tracking-wider py-2.5 rounded transition-colors"
              >
                Close & Continue Browsing
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
