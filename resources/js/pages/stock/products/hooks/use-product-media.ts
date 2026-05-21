import { useCallback, useState } from 'react';
import {
    destroy as destroyImage,
    reorder,
    store as storeImages,
} from '@/actions/App/Http/Controllers/Stock/ProductImagesController';
import {
    destroy as destroyVideo,
    store as storeVideo,
} from '@/actions/App/Http/Controllers/Stock/ProductVideoController';
import type { ProductMediaItem } from '@/lib/media-fetch';
import {
    deleteProductMedia,
    postProductMediaForm,
    putProductMediaJson,
} from '@/lib/media-fetch';

type ProductMediaState = {
    images: ProductMediaItem[];
    video: ProductMediaItem | null;
};

export function useProductMedia(initial: ProductMediaState) {
    const [media, setMedia] = useState<ProductMediaState>(initial);
    const [error, setError] = useState<string | null>(null);
    const [uploadingImages, setUploadingImages] = useState(false);
    const [uploadingVideo, setUploadingVideo] = useState(false);
    const [reordering, setReordering] = useState(false);

    const applyResponse = useCallback((payload: ProductMediaState) => {
        setMedia(payload);
    }, []);

    const uploadImages = useCallback(
        async (productId: string, files: File[]) => {
            setError(null);
            setUploadingImages(true);

            try {
                const formData = new FormData();
                files.forEach((file) => formData.append('images[]', file));

                const payload = await postProductMediaForm(storeImages.url(productId), formData);
                applyResponse(payload);
            } catch (uploadError) {
                setError(
                    uploadError instanceof Error
                        ? uploadError.message
                        : 'No se pudieron subir las imágenes.',
                );
            } finally {
                setUploadingImages(false);
            }
        },
        [applyResponse],
    );

    const reorderImages = useCallback(
        async (productId: string, order: string[]) => {
            setError(null);
            setReordering(true);

            try {
                const payload = await putProductMediaJson(reorder.url(productId), { order });
                applyResponse(payload);
            } catch (reorderError) {
                setError(
                    reorderError instanceof Error
                        ? reorderError.message
                        : 'No se pudo reordenar las imágenes.',
                );
            } finally {
                setReordering(false);
            }
        },
        [applyResponse],
    );

    const deleteImage = useCallback(
        async (productId: string, mediaId: string) => {
            setError(null);

            try {
                const payload = await deleteProductMedia(destroyImage.url({ product: productId, media: mediaId }));
                applyResponse(payload);
            } catch (deleteError) {
                setError(
                    deleteError instanceof Error
                        ? deleteError.message
                        : 'No se pudo eliminar la imagen.',
                );
            }
        },
        [applyResponse],
    );

    const uploadVideo = useCallback(
        async (productId: string, file: File) => {
            setError(null);
            setUploadingVideo(true);

            try {
                const formData = new FormData();
                formData.append('video', file);

                const payload = await postProductMediaForm(storeVideo.url(productId), formData);
                applyResponse(payload);
            } catch (uploadError) {
                setError(
                    uploadError instanceof Error
                        ? uploadError.message
                        : 'No se pudo subir el video.',
                );
            } finally {
                setUploadingVideo(false);
            }
        },
        [applyResponse],
    );

    const deleteVideo = useCallback(
        async (productId: string) => {
            setError(null);

            try {
                const payload = await deleteProductMedia(destroyVideo.url(productId));
                applyResponse(payload);
            } catch (deleteError) {
                setError(
                    deleteError instanceof Error
                        ? deleteError.message
                        : 'No se pudo eliminar el video.',
                );
            }
        },
        [applyResponse],
    );

    return {
        media,
        error,
        uploadingImages,
        uploadingVideo,
        reordering,
        uploadImages,
        reorderImages,
        deleteImage,
        uploadVideo,
        deleteVideo,
        setError,
    };
}
