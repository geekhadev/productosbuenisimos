import type { LandingShowcaseProduct } from '@/pages/landing/types';

/**
 * Datos estáticos de vitrina para la landing (sin página de detalle aún).
 */
export const landingShowcaseProducts: LandingShowcaseProduct[] = [
    {
        id: 'jugo-naranja-1l',
        name: 'Jugo naranja 1 L',
        subtitle: 'Exprimido, sin conservantes. Ideal para el desayuno.',
        priceLabel: '$2.990',
        imageUrl:
            'https://images.unsplash.com/photo-1621506289937-a8e4df240d0b?auto=format&fit=crop&w=900&q=80',
        badge: 'Favorito del mes',
    },
    {
        id: 'mix-frutos-secos-500',
        name: 'Mix frutos secos premium 500 g',
        subtitle: 'Almendras, nueces y castañas seleccionadas.',
        priceLabel: '$8.490',
        imageUrl:
            'https://images.unsplash.com/photo-1599599810769-bcde5a160d32?auto=format&fit=crop&w=900&q=80',
        badge: 'Nuevo',
    },
    {
        id: 'miel-ulmo-500',
        name: 'Miel de ulmo 500 g',
        subtitle: 'Origen local, textura cremosa y aroma floral.',
        priceLabel: '$6.200',
        imageUrl:
            'https://images.unsplash.com/photo-1587049352846-4a222e2d9654?auto=format&fit=crop&w=900&q=80',
    },
    {
        id: 'aceite-oliva-500',
        name: 'Aceite de oliva extra virgen 500 ml',
        subtitle: 'Primera prensada en frío. Cocina y ensaladas.',
        priceLabel: '$7.900',
        imageUrl:
            'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?auto=format&fit=crop&w=900&q=80',
    },
    {
        id: 'granola-400',
        name: 'Granola artesanal 400 g',
        subtitle: 'Con avena, miel y frutos secos tostados.',
        priceLabel: '$4.450',
        imageUrl:
            'https://images.unsplash.com/photo-1517686469429-8bdb388b13f6?auto=format&fit=crop&w=900&q=80',
    },
    {
        id: 'chocolate-70-100',
        name: 'Chocolate 70 % cacao 100 g',
        subtitle: 'Amargo equilibrado, ideal para compartir.',
        priceLabel: '$2.200',
        imageUrl:
            'https://images.unsplash.com/photo-1549007995-15f48c8852c8?auto=format&fit=crop&w=900&q=80',
    },
];

/** Producto destacado en el hero de la landing (único). */
export const landingHeroProduct: LandingShowcaseProduct = landingShowcaseProducts[0];
