import { ChevronLeft, ChevronRight, Film } from 'lucide-react';
import { useCallback, useMemo, useState } from 'react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { LANDING_PRODUCT_PLACEHOLDER } from '@/pages/landing/landing-product-visual';

import type { ProductGallerySlide, ProductImageGalleryProps } from './types';

export type {
    ProductGalleryImage,
    ProductGallerySlide,
    ProductGalleryVideo,
    ProductImageGalleryProps,
} from './types';

function buildSlides(
    images: ProductImageGalleryProps['images'],
    video: ProductImageGalleryProps['video'],
    productName: string,
    placeholderUrl: string,
): ProductGallerySlide[] {
    const slides: ProductGallerySlide[] = [];

    if (video) {
        slides.push({
            id: video.id,
            url: video.url,
            kind: 'video',
            mime_type: video.mime_type,
        });
    }

    if (images.length > 0) {
        slides.push(
            ...images.map(
                (image): ProductGallerySlide => ({
                    ...image,
                    kind: 'image',
                }),
            ),
        );
    } else if (!video) {
        slides.push({
            id: 'placeholder',
            url: placeholderUrl,
            alt: productName,
            kind: 'image',
        });
    }

    return slides;
}

export function ProductImageGallery({
    images,
    productName,
    video = null,
    className,
    placeholderUrl = LANDING_PRODUCT_PLACEHOLDER,
}: ProductImageGalleryProps) {
    const slides = useMemo(
        () => buildSlides(images, video, productName, placeholderUrl),
        [images, video, productName, placeholderUrl],
    );

    const [selectedIndex, setSelectedIndex] = useState(0);
    const selected = slides[selectedIndex] ?? slides[0];
    const hasMultiple = slides.length > 1;

    const goTo = useCallback(
        (index: number) => {
            const next = (index + slides.length) % slides.length;
            setSelectedIndex(next);
        },
        [slides.length],
    );

    const goPrev = useCallback(() => goTo(selectedIndex - 1), [goTo, selectedIndex]);
    const goNext = useCallback(() => goTo(selectedIndex + 1), [goTo, selectedIndex]);

    return (
        <div className={cn('flex min-w-0 flex-col gap-3 sm:gap-4', className)}>
            <div className="flex min-w-0 flex-col gap-3 lg:flex-row lg:gap-4">
                {hasMultiple ? (
                    <div
                        className="order-2 flex gap-2 overflow-x-auto pb-1 lg:order-1 lg:w-16 lg:shrink-0 lg:flex-col lg:overflow-x-visible lg:overflow-y-auto lg:pb-0"
                        role="tablist"
                        aria-label="Miniaturas del producto"
                    >
                        {slides.map((slide, index) => {
                            const isActive = index === selectedIndex;
                            const isVideo = slide.kind === 'video';

                            return (
                                <button
                                    key={slide.id}
                                    type="button"
                                    role="tab"
                                    aria-selected={isActive}
                                    aria-label={
                                        isVideo
                                            ? 'Ver video del producto'
                                            : `Ver imagen ${index + (video ? 0 : 1)} de ${slides.length - (video ? 1 : 0)}`
                                    }
                                    onClick={() => setSelectedIndex(index)}
                                    className={cn(
                                        'relative size-14 shrink-0 overflow-hidden rounded-md border bg-muted transition-colors sm:size-16 lg:size-16',
                                        isActive
                                            ? 'border-primary ring-2 ring-primary/30'
                                            : 'border-border hover:border-primary/50',
                                    )}
                                >
                                    {isVideo ? (
                                        <span className="flex size-full items-center justify-center bg-muted text-muted-foreground">
                                            <Film aria-hidden className="size-5" />
                                        </span>
                                    ) : (
                                        <img
                                            src={slide.url}
                                            alt=""
                                            className="size-full object-cover"
                                            loading="lazy"
                                            decoding="async"
                                        />
                                    )}
                                </button>
                            );
                        })}
                    </div>
                ) : null}

                <div className="relative order-1 min-w-0 flex-1 lg:order-2">
                    <div
                        className={cn(
                            'relative aspect-square overflow-hidden rounded-2xl border border-border/60 shadow-lg',
                            selected.kind === 'video' ? 'bg-black' : 'bg-background',
                        )}
                    >
                        {selected.kind === 'video' ? (
                            <video
                                key={selected.id}
                                src={selected.url}
                                controls
                                playsInline
                                className="size-full object-contain"
                            >
                                Tu navegador no puede reproducir este video.
                            </video>
                        ) : (
                            <img
                                key={selected.id}
                                src={selected.url}
                                alt={selected.alt || productName}
                                className="size-full object-contain"
                                loading="eager"
                                decoding="async"
                            />
                        )}
                        {hasMultiple ? (
                            <>
                                <Button
                                    type="button"
                                    variant="secondary"
                                    size="icon"
                                    className="absolute top-1/2 left-2 size-9 -translate-y-1/2 rounded-full shadow-md sm:left-3"
                                    onClick={goPrev}
                                    aria-label="Elemento anterior"
                                >
                                    <ChevronLeft className="size-5" aria-hidden />
                                </Button>
                                <Button
                                    type="button"
                                    variant="secondary"
                                    size="icon"
                                    className="absolute top-1/2 right-2 size-9 -translate-y-1/2 rounded-full shadow-md sm:right-3"
                                    onClick={goNext}
                                    aria-label="Elemento siguiente"
                                >
                                    <ChevronRight className="size-5" aria-hidden />
                                </Button>
                                <p className="pointer-events-none absolute bottom-3 left-1/2 -translate-x-1/2 rounded-full bg-background/80 px-2.5 py-0.5 text-xs font-medium tabular-nums text-foreground backdrop-blur-sm">
                                    {selectedIndex + 1} / {slides.length}
                                </p>
                            </>
                        ) : null}
                    </div>
                </div>
            </div>
        </div>
    );
}
