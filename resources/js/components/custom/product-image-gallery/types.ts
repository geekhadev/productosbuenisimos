export type ProductGalleryImage = {
    id: string;
    url: string;
    alt: string;
};

export type ProductImageGalleryProps = {
    images: ProductGalleryImage[];
    productName: string;
    className?: string;
    placeholderUrl?: string;
};
