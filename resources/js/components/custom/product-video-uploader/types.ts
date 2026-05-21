import type { ProductMediaItem } from '@/lib/media-fetch';

export type ProductVideoUploaderProps = {
    editable: boolean;
    onDelete: () => void;
    onUpload: (file: File) => void;
    video: ProductMediaItem | null;
};
