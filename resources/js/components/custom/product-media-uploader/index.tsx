import { GripVertical, ImagePlus, Loader2, Trash2 } from 'lucide-react';
import { useCallback, useRef, useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { cn } from '@/lib/utils';

import { MAX_PRODUCT_IMAGES  } from './types';
import type {ProductMediaUploaderProps} from './types';

export function ProductMediaUploader({
    images,
    editable,
    onDelete,
    onReorder,
    onUpload,
}: ProductMediaUploaderProps) {
    const inputRef = useRef<HTMLInputElement>(null);
    const [draggingId, setDraggingId] = useState<string | null>(null);
    const [localError, setLocalError] = useState<string | null>(null);

    const remainingSlots = MAX_PRODUCT_IMAGES - images.length;
    const canUpload = editable && remainingSlots > 0;

    const handleFiles = useCallback(
        (fileList: FileList | null) => {
            if (!fileList || fileList.length === 0) {
                return;
            }

            const files = Array.from(fileList).slice(0, remainingSlots);

            if (files.length + images.length > MAX_PRODUCT_IMAGES) {
                setLocalError('El producto no puede tener más de 5 imágenes.');

                return;
            }

            setLocalError(null);
            onUpload(files);
        },
        [images.length, onUpload, remainingSlots],
    );

    const handleDrop = useCallback(
        (event: React.DragEvent) => {
            event.preventDefault();

            if (!canUpload) {
                return;
            }

            handleFiles(event.dataTransfer.files);
        },
        [canUpload, handleFiles],
    );

    const handleReorderDrop = useCallback(
        (targetId: string) => {
            if (!draggingId || draggingId === targetId) {
                setDraggingId(null);

                return;
            }

            const next = [...images];
            const fromIndex = next.findIndex((item) => item.id === draggingId);
            const toIndex = next.findIndex((item) => item.id === targetId);

            if (fromIndex < 0 || toIndex < 0) {
                setDraggingId(null);

                return;
            }

            const [moved] = next.splice(fromIndex, 1);
            next.splice(toIndex, 0, moved);
            setDraggingId(null);
            onReorder(next.map((item) => item.id));
        },
        [draggingId, images, onReorder],
    );

    return (
        <div className="space-y-4">
            <div className="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 className="text-lg font-semibold">Imágenes</h2>
                    <p className="text-muted-foreground text-sm">
                        Hasta 5 imágenes. La primera es la principal del catálogo.
                    </p>
                </div>
                <span className="text-muted-foreground text-sm tabular-nums">
                    {images.length} / {MAX_PRODUCT_IMAGES}
                </span>
            </div>

            {canUpload ? (
                <div
                    role="button"
                    tabIndex={0}
                    onKeyDown={(event) => {
                        if (event.key === 'Enter' || event.key === ' ') {
                            inputRef.current?.click();
                        }
                    }}
                    onClick={() => inputRef.current?.click()}
                    onDragOver={(event) => event.preventDefault()}
                    onDrop={handleDrop}
                    className={cn(
                        'flex min-h-28 cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border border-dashed border-border bg-muted/30 p-6 text-center transition-colors hover:bg-muted/50',
                    )}
                >
                    <ImagePlus className="text-muted-foreground size-8" aria-hidden />
                    <p className="text-sm font-medium">Arrastra imágenes o haz clic para seleccionar</p>
                    <p className="text-muted-foreground text-xs">JPG, PNG o WebP · máx. 10 MB por archivo</p>
                    <input
                        ref={inputRef}
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        multiple
                        className="sr-only"
                        onChange={(event) => {
                            handleFiles(event.target.files);
                            event.target.value = '';
                        }}
                    />
                </div>
            ) : null}

            {images.length > 0 ? (
                <ul className="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-5">
                    {images.map((image, index) => (
                        <li
                            key={image.id}
                            draggable={editable}
                            onDragStart={() => setDraggingId(image.id)}
                            onDragOver={(event) => event.preventDefault()}
                            onDrop={() => {
                                if (editable) {
                                    handleReorderDrop(image.id);
                                }
                            }}
                            className={cn(
                                'group relative overflow-hidden rounded-lg border border-border bg-muted/20',
                                editable && 'cursor-grab active:cursor-grabbing',
                            )}
                        >
                            <img
                                src={image.url}
                                alt={image.original_name}
                                className="aspect-square w-full object-cover"
                            />
                            {index === 0 ? (
                                <span className="bg-primary text-primary-foreground absolute top-2 left-2 rounded px-1.5 py-0.5 text-[10px] font-medium">
                                    Principal
                                </span>
                            ) : null}
                            {editable ? (
                                <div className="absolute top-2 right-2 flex gap-1">
                                    <span className="bg-background/90 text-muted-foreground inline-flex size-7 items-center justify-center rounded-md border border-border">
                                        <GripVertical className="size-4" aria-hidden />
                                    </span>
                                    <Popover>
                                        <PopoverTrigger asChild>
                                            <Button
                                                type="button"
                                                size="icon"
                                                variant="secondary"
                                                className="size-7"
                                                aria-label="Eliminar imagen"
                                            >
                                                <Trash2 className="size-3.5" />
                                            </Button>
                                        </PopoverTrigger>
                                        <PopoverContent align="end" className="w-48 p-3">
                                            <p className="text-sm">¿Eliminar esta imagen?</p>
                                            <Button
                                                type="button"
                                                size="sm"
                                                variant="destructive"
                                                className="mt-3 w-full"
                                                onClick={() => onDelete(image.id)}
                                            >
                                                Eliminar
                                            </Button>
                                        </PopoverContent>
                                    </Popover>
                                </div>
                            ) : null}
                        </li>
                    ))}
                </ul>
            ) : (
                <p className="text-muted-foreground text-sm">Este producto aún no tiene imágenes.</p>
            )}

            <InputError message={localError ?? undefined} />
        </div>
    );
}

export function ProductMediaUploaderPending({ label }: { label: string }) {
    return (
        <p className="text-muted-foreground flex items-center gap-2 text-sm">
            <Loader2 className="size-4 animate-spin" aria-hidden />
            {label}
        </p>
    );
}
