export type LandingSectionProps = {
    canRegister: boolean;
};

export type LandingProductImage = {
    id: string;
    url: string;
    alt: string;
};

export type LandingProduct = {
    id: string;
    name: string;
    code: string;
    sku: string;
    price: number;
    description: string | null;
    thumbnail_url: string;
};

/** Ficha pública (Inertia): decimales pueden llegar como string desde Laravel. */
export type PublicLandingProductDetail = {
    id: string;
    name: string;
    code: string;
    sku: string;
    description: string | null;
    width: string | number;
    length: string | number;
    height: string | number;
    volume: string | number;
    weight: string | number;
    minimum_stock: number;
    price: string | number;
    thumbnail_url: string;
    images: LandingProductImage[];
};
