/**
 * Misma proporción y tratamiento base del área de imagen en hero y vitrina.
 */
export const landingProductImageAreaClass = 'relative aspect-4/3 overflow-hidden bg-muted';

export const LANDING_PRODUCT_PLACEHOLDER = '/product-placeholder.svg';

export function landingProductImageSrc(thumbnailUrl: string | null | undefined): string {
    return thumbnailUrl && thumbnailUrl.length > 0 ? thumbnailUrl : LANDING_PRODUCT_PLACEHOLDER;
}
