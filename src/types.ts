export interface ProductColor {
  name: string;
  hex: string;
  isDefault?: boolean;
}

export interface Product {
  id: number;
  name: string;
  slug: string;
  brand: string;
  category: string;
  price: number;
  originalPrice?: number;
  rating: number;
  reviewsCount: number;
  image: string;
  fallbackSvgType?: 'aviator' | 'round' | 'square' | 'cateye' | 'halfrim' | 'contacts';
  description: string;
  features: string[];
  gender: 'Unisex' | 'Men' | 'Women' | 'Kids';
  shape: 'Round' | 'Wayfarer' | 'Square' | 'Cat-Eye' | 'Aviator' | 'Rectangle';
  inStock: boolean;
  isFeatured: boolean;
  colors: ProductColor[];
}

export interface CartItem {
  id: string; // productId-color
  product: Product;
  selectedColor: ProductColor;
  quantity: number;
  prescriptionNote?: string;
}

export interface CustomerOrder {
  id: string;
  customerName: string;
  phone: string;
  email: string;
  deliveryType: 'pickup' | 'chattogram' | 'outside';
  deliveryAddress: string;
  paymentMethod: 'cod' | 'bkash' | 'store';
  items: CartItem[];
  shippingFee: number;
  totalAmount: number;
  createdAt: string;
  status: 'Pending' | 'Confirmed' | 'Ready for Pickup' | 'Completed';
}
