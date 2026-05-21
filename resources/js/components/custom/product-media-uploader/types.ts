import type { ProductMediaItem } from '@/lib/media-fetch';

export type ProductMediaUploaderProps = {
    images: ProductMediaItem[];
    editable: boolean;
    onDelete: (mediaId: string) => void;
    onReorder: (order: string[]) => void;
    onUpload: (files: File[]) => void;
};

export const MAX_PRODUCT_IMAGES = 5;
