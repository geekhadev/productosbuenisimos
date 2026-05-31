export type ProductGalleryImage = {
    id: string;
    url: string;
    alt: string;
};

export type ProductGalleryVideo = {
    id: string;
    url: string;
    mime_type?: string | null;
};

export type ProductGallerySlide =
    | (ProductGalleryImage & { kind: 'image' })
    | {
          id: string;
          url: string;
          kind: 'video';
          mime_type?: string | null;
      };

export type ProductImageGalleryProps = {
    images: ProductGalleryImage[];
    productName: string;
    video?: ProductGalleryVideo | null;
    className?: string;
    placeholderUrl?: string;
};
