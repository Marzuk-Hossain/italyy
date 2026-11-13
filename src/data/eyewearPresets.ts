import { Product } from '../types';

// Crisp SVG data-URIs for luxury eyewear that never fail to render
export const VECTOR_EYEWEAR = {
  aviator: `data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 300" width="100%" height="100%"><rect width="500" height="300" fill="%23F7F7F7"/><g stroke="%23111" stroke-width="6" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M110 90 Q180 80 230 110" stroke-width="4"/><path d="M270 110 Q320 80 390 90" stroke-width="4"/><path d="M225 110 Q250 102 275 110" stroke-width="5"/><path d="M220 125 Q250 120 280 125" stroke-width="4"/><path d="M120 110 C85 115 80 170 95 200 C115 235 180 235 205 195 C220 165 220 115 120 110 Z" fill="%23111111" fill-opacity="0.12"/><path d="M380 110 C415 115 420 170 405 200 C385 235 320 235 295 195 C280 165 280 115 380 110 Z" fill="%23111111" fill-opacity="0.12"/><circle cx="210" cy="140" r="4" fill="%23999"/><circle cx="290" cy="140" r="4" fill="%23999"/></g><text x="250" y="270" text-anchor="middle" font-family="sans-serif" font-size="12" font-weight="700" letter-spacing="3" fill="%23666">ITALY OPTICS · MILANO PILOT</text></svg>`,

  round: `data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 300" width="100%" height="100%"><rect width="500" height="300" fill="%23F9F9F9"/><g stroke="%23222" stroke-width="5" fill="none"><path d="M100 130 L70 120" stroke-width="4"/><path d="M400 130 L430 120" stroke-width="4"/><path d="M225 138 Q250 128 275 138" stroke-width="5"/><circle cx="165" cy="145" r="60" fill="%23EAEAEA" fill-opacity="0.5"/><circle cx="335" cy="145" r="60" fill="%23EAEAEA" fill-opacity="0.5"/><path d="M130 130 A45 45 0 0 1 180 110" stroke="%23FFF" stroke-width="3" stroke-linecap="round"/><path d="M300 130 A45 45 0 0 1 350 110" stroke="%23FFF" stroke-width="3" stroke-linecap="round"/></g><text x="250" y="268" text-anchor="middle" font-family="sans-serif" font-size="12" font-weight="700" letter-spacing="3" fill="%23666">ITALY OPTICS · TITANIUM ROUND</text></svg>`,

  square: `data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 300" width="100%" height="100%"><rect width="500" height="300" fill="%23F6F6F6"/><g stroke="%2318181B" stroke-width="6" fill="none"><path d="M228 135 L272 135" stroke-width="5"/><rect x="105" y="95" width="120" height="90" rx="18" fill="%231E293B" fill-opacity="0.08"/><rect x="275" y="95" width="120" height="90" rx="18" fill="%231E293B" fill-opacity="0.08"/><path d="M105 110 L70 100" stroke-width="4"/><path d="M395 110 L430 100" stroke-width="4"/></g><text x="250" y="265" text-anchor="middle" font-family="sans-serif" font-size="12" font-weight="700" letter-spacing="3" fill="%23666">ITALY OPTICS · BLUESHIELD TECH</text></svg>`,

  cateye: `data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 300" width="100%" height="100%"><rect width="500" height="300" fill="%23FAFAFA"/><g stroke="%230F0F0F" stroke-width="6" fill="none"><path d="M228 138 Q250 130 272 138" stroke-width="4"/><path d="M90 100 Q150 90 220 120 Q200 195 150 185 Q90 170 90 100 Z" fill="%2327272A" fill-opacity="0.12"/><path d="M410 100 Q350 90 280 120 Q300 195 350 185 Q410 170 410 100 Z" fill="%2327272A" fill-opacity="0.12"/><circle cx="95" cy="105" r="3" fill="%23D4AF37"/><circle cx="405" cy="105" r="3" fill="%23D4AF37"/></g><text x="250" y="265" text-anchor="middle" font-family="sans-serif" font-size="12" font-weight="700" letter-spacing="3" fill="%23666">ITALY OPTICS · BESPOKE CAT-EYE</text></svg>`,

  halfrim: `data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 300" width="100%" height="100%"><rect width="500" height="300" fill="%23F7F7F7"/><g stroke="%23262626" stroke-width="6" fill="none"><path d="M225 125 L275 125" stroke-width="5"/><path d="M100 115 L225 115 L220 170 Q160 175 105 165 Z" fill="%23FAFAFA"/><path d="M400 115 L275 115 L280 170 Q340 175 395 165 Z" fill="%23FAFAFA"/><path d="M100 115 L225 115" stroke-width="7"/><path d="M275 115 L400 115" stroke-width="7"/><path d="M105 165 Q160 175 220 170" stroke-dasharray="4 4" stroke-width="2"/><path d="M395 165 Q340 175 280 170" stroke-dasharray="4 4" stroke-width="2"/></g><text x="250" y="265" text-anchor="middle" font-family="sans-serif" font-size="12" font-weight="700" letter-spacing="3" fill="%23666">ITALY OPTICS · EXECUTIVE HALF-RIM</text></svg>`,

  contacts: `data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 300" width="100%" height="100%"><rect width="500" height="300" fill="%23F0F7FF"/><g stroke="%230284C7" stroke-width="3" fill="none"><circle cx="250" cy="140" r="70" fill="%23E0F2FE" fill-opacity="0.8"/><circle cx="250" cy="140" r="45" stroke-dasharray="3 3"/><ellipse cx="230" cy="120" rx="15" ry="8" fill="%23FFFFFF" fill-opacity="0.8"/><path d="M160 140 Q250 80 340 140" stroke="%2338BDF8" stroke-width="2"/></g><text x="250" y="265" text-anchor="middle" font-family="sans-serif" font-size="12" font-weight="700" letter-spacing="3" fill="%230369A1">AQUAVISION · SILICONE HYDROGEL</text></svg>`
};

// Studio Presets for Image 2 in Admin Panel
export const STUDIO_PRESETS = [
  {
    id: 'pilot-aviator',
    title: 'Italian Pilot Aviator (Sunglasses)',
    category: 'Sunglasses',
    icon: '🕶️',
    url: VECTOR_EYEWEAR.aviator
  },
  {
    id: 'amber-acetate',
    title: 'Amber Acetate Spectacles (Eyeglasses)',
    category: 'Eyeglasses',
    icon: '👓',
    url: VECTOR_EYEWEAR.round
  },
  {
    id: 'blister-pack',
    title: 'Blister Pack (Contact Lenses)',
    category: 'Contact Lenses',
    icon: '💧',
    url: VECTOR_EYEWEAR.contacts
  },
  {
    id: 'bespoke-cateye',
    title: 'Bespoke Atelier Collection (Cat-Eye)',
    category: 'Eyeglasses',
    icon: '✨',
    url: VECTOR_EYEWEAR.cateye
  },
  {
    id: 'blueshield-pro',
    title: 'Firenze BlueShield Pro (Computer)',
    category: 'Blue Light',
    icon: '💻',
    url: VECTOR_EYEWEAR.square
  },
  {
    id: 'executive-halfrim',
    title: 'Palermo Executive Half-Rim (Reading)',
    category: 'Reading Glasses',
    icon: '📖',
    url: VECTOR_EYEWEAR.halfrim
  }
];

export const INITIAL_PRODUCTS: Product[] = [
  {
    id: 1,
    name: 'Milano Elegance Titanium',
    slug: 'milano-elegance-titanium',
    brand: 'Italy Optics Heritage',
    category: 'Eyeglasses',
    price: 3850,
    originalPrice: 4800,
    rating: 5.0,
    reviewsCount: 18,
    image: VECTOR_EYEWEAR.round,
    fallbackSvgType: 'round',
    description: 'Handcrafted ultra-lightweight titanium frame inspired by Milanese architecture. Engineered for featherlight comfort all day in Chittagong.',
    features: [
      'Ultra-light Aerospace Titanium (14g)',
      'Anti-reflective computerized lens ready',
      'Silicone air-cushion nose pads',
      '2-Year Official Store Warranty'
    ],
    gender: 'Unisex',
    shape: 'Round',
    inStock: true,
    isFeatured: true,
    colors: [
      { name: 'Matte Black', hex: '#111111', isDefault: true },
      { name: 'Silver Chrome', hex: '#D1D5DB' },
      { name: 'Gunmetal Slate', hex: '#4B5563' },
      { name: 'Pure White', hex: '#F9FAFB' }
    ]
  },
  {
    id: 2,
    name: 'Venezia Riviera Polarized',
    slug: 'venezia-riviera-polarized',
    brand: 'Venezia Eyewear',
    category: 'Sunglasses',
    price: 4600,
    originalPrice: 5500,
    rating: 4.9,
    reviewsCount: 24,
    image: VECTOR_EYEWEAR.aviator,
    fallbackSvgType: 'aviator',
    description: 'Italian biological acetate sunglasses with UV400 polarized precision lenses. Eliminates harsh glare across Chattogram sunny outdoors.',
    features: [
      '100% UV400 Polarized Precision Lenses',
      'Mazzucchelli Bio-Acetate Craftsmanship',
      '5-Barrel Metal Hinge Construction',
      'Signature Italian Optics Hard Shell Case'
    ],
    gender: 'Men',
    shape: 'Wayfarer',
    inStock: true,
    isFeatured: true,
    colors: [
      { name: 'Obsidian Black', hex: '#0F0F0F', isDefault: true },
      { name: 'Havana Brown', hex: '#3D2817' },
      { name: 'Smoke Gray', hex: '#666666' }
    ]
  },
  {
    id: 3,
    name: 'Firenze BlueShield Pro',
    slug: 'firenze-blueshield-pro',
    brand: 'Italy Optics Tech',
    category: 'Blue Light',
    price: 2950,
    originalPrice: 3500,
    rating: 5.0,
    reviewsCount: 31,
    image: VECTOR_EYEWEAR.square,
    fallbackSvgType: 'square',
    description: 'Engineered for developers, designers, students and digital professionals. Filters 95% of harmful 410-450nm blue light without color distortion.',
    features: [
      'Anti-Blue Light Filter (410-450nm)',
      'Hydrophobic Oleophobic Easy-Clean Coating',
      'TR90 Memory Polymer Frame',
      'Zero Digital Eyestrain Guarantee'
    ],
    gender: 'Unisex',
    shape: 'Square',
    inStock: true,
    isFeatured: true,
    colors: [
      { name: 'Crystal Clear', hex: '#E5E7EB', isDefault: true },
      { name: 'Jet Black', hex: '#000000' },
      { name: 'Frosted Smoke', hex: '#9CA3AF' }
    ]
  },
  {
    id: 4,
    name: 'Roma Cat-Eye Silhouette',
    slug: 'roma-cat-eye-silhouette',
    brand: 'Bella Donna Italia',
    category: 'Eyeglasses',
    price: 3400,
    originalPrice: 4200,
    rating: 5.0,
    reviewsCount: 15,
    image: VECTOR_EYEWEAR.cateye,
    fallbackSvgType: 'cateye',
    description: 'Bold uplifting Italian cat-eye design that sculpts facial contours with subtle monochrome luxury. Hand-polished bevelled edges.',
    features: [
      'Flattering Lifted Silhouette for Contemporary Look',
      'High-grade Cellulose Acetate Sheet',
      'Internal Spring Hinge Temples',
      'Complimentary Custom Leather Case'
    ],
    gender: 'Women',
    shape: 'Cat-Eye',
    inStock: true,
    isFeatured: false,
    colors: [
      { name: 'Gloss Black', hex: '#000000', isDefault: true },
      { name: 'Pearl White', hex: '#F3F4F6' },
      { name: 'Dark Tortoise', hex: '#2B1E16' }
    ]
  },
  {
    id: 5,
    name: 'Torino Aviator Classic 1978',
    slug: 'torino-aviator-classic-1978',
    brand: 'Italy Optics Heritage',
    category: 'Sunglasses',
    price: 4900,
    originalPrice: 6200,
    rating: 4.8,
    reviewsCount: 19,
    image: VECTOR_EYEWEAR.aviator,
    fallbackSvgType: 'aviator',
    description: 'Timeless double-bridge teardrop frame fabricated from surgical stainless steel. Finished in obsidian black and polished titanium.',
    features: [
      'Corrosion-resistant Aerospace Alloy',
      'UV400 Polarized Impact-resistant Glass',
      'Self-adjusting Silicone Nose Guards',
      'Laser-etched Authentic Italy Optics Logo'
    ],
    gender: 'Unisex',
    shape: 'Aviator',
    inStock: true,
    isFeatured: true,
    colors: [
      { name: 'Matte Black', hex: '#000000', isDefault: true },
      { name: 'Silver Mirror', hex: '#CBD5E1' }
    ]
  },
  {
    id: 6,
    name: 'Aquavision HydraComfort Lenses',
    slug: 'aquavision-hydracomfort-lenses',
    brand: 'OptiHydro Italia',
    category: 'Contact Lenses',
    price: 1850,
    originalPrice: 2200,
    rating: 5.0,
    reviewsCount: 22,
    image: VECTOR_EYEWEAR.contacts,
    fallbackSvgType: 'contacts',
    description: 'Breathable monthly silicone hydrogel contact lenses with moisture retention matrix. Keeps eyes moist in air-conditioned and humid conditions.',
    features: [
      '6 Sterile Lenses per Factory Pack',
      '58% High Water Content Matrix',
      'UV-B Eye Surface Blocking Capability',
      'Smooth Edge Technology for All-Day Wear'
    ],
    gender: 'Unisex',
    shape: 'Round',
    inStock: true,
    isFeatured: false,
    colors: [
      { name: 'Natural Clear', hex: '#E0F2FE', isDefault: true },
      { name: 'Ocean Aqua Tint', hex: '#7DD3FC' }
    ]
  },
  {
    id: 7,
    name: 'Palermo Executive Half-Rim',
    slug: 'palermo-executive-half-rim',
    brand: 'Italy Optics Heritage',
    category: 'Reading Glasses',
    price: 3100,
    originalPrice: 3800,
    rating: 4.9,
    reviewsCount: 16,
    image: VECTOR_EYEWEAR.halfrim,
    fallbackSvgType: 'halfrim',
    description: 'Sophisticated semi-rimless frame designed for reading and executive authority. Lightweight browline structure with precision spring hinges.',
    features: [
      'Precision Semi-Rimless Titanium Browline',
      'Spring Loaded Flexible Temples',
      'Accommodates Progressive & Bifocal Lenses',
      'Ultralight Executive Balanced Weight'
    ],
    gender: 'Men',
    shape: 'Rectangle',
    inStock: true,
    isFeatured: false,
    colors: [
      { name: 'Brushed Gunmetal', hex: '#374151', isDefault: true },
      { name: 'Polished Black', hex: '#111111' },
      { name: 'Titanium Gray', hex: '#6B7280' }
    ]
  },
  {
    id: 8,
    name: 'Bambino Flex Unbreakable Kids',
    slug: 'bambino-flex-unbreakable-kids',
    brand: 'Italy Optics Kids',
    category: 'Kids',
    price: 2200,
    originalPrice: 2700,
    rating: 5.0,
    reviewsCount: 12,
    image: VECTOR_EYEWEAR.round,
    fallbackSvgType: 'round',
    description: 'Virtually indestructible, 180-degree bendable, non-toxic rubberized frames engineered for active school-going children in Chittagong.',
    features: [
      '180-degree Bendable Rubberized Temples',
      'BPA-Free Medical Grade Non-Toxic Polymer',
      'Includes Elastic Sports Retention Headband',
      'Drop-proof & Playground Shatter Guarantee'
    ],
    gender: 'Kids',
    shape: 'Round',
    inStock: true,
    isFeatured: false,
    colors: [
      { name: 'Onyx Black', hex: '#111111', isDefault: true },
      { name: 'Chalk White', hex: '#F3F4F6' },
      { name: 'Cobalt Royal Blue', hex: '#1D4ED8' }
    ]
  }
];

export const INITIAL_REVIEWS = [
  {
    id: 1,
    name: 'Tanvir Ahmed',
    location: 'Jamal Khan, Chattogram',
    rating: 5,
    date: '2 days ago',
    text: 'Best optical shop in Jamal Khan! The owner took time to test my eyes on their computerized system and helped me pick the right Italian titanium frame. Prices are very reasonable and service is top notch.'
  },
  {
    id: 2,
    name: 'Dr. Nusrat Jahan',
    location: 'Chittagong Medical College Area',
    rating: 5,
    date: '1 week ago',
    text: 'Got computer blue light glasses and prescription sunglasses from Italy Optics. Lens precision is 100% accurate. Highly recommend anyone in Chittagong looking for authentic frames.'
  },
  {
    id: 3,
    name: 'Zubair Hossain',
    location: 'Chattogram',
    rating: 5,
    date: '2 weeks ago',
    text: 'Excellent collection and 5-star customer hospitality! They delivered my customized lenses within 24 hours. The frame fits perfectly without nose pressure.'
  }
];
