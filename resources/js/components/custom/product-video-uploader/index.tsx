import { Film, Loader2, Trash2, Upload } from 'lucide-react';
import { useCallback, useRef, useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { cn } from '@/lib/utils';

import type { ProductVideoUploaderProps } from './types';

export function ProductVideoUploader({
    editable,
    onDelete,
    onUpload,
    video,
}: ProductVideoUploaderProps) {
    const inputRef = useRef<HTMLInputElement>(null);
    const [localError, setLocalError] = useState<string | null>(null);

    const handleFile = useCallback(
        (fileList: FileList | null) => {
            const file = fileList?.[0];

            if (!file) {
                return;
            }

            setLocalError(null);
            onUpload(file);
        },
        [onUpload],
    );

    const handleDrop = useCallback(
        (event: React.DragEvent) => {
            event.preventDefault();

            if (!editable) {
                return;
            }

            handleFile(event.dataTransfer.files);
        },
        [editable, handleFile],
    );

    return (
        <div className="space-y-4">
            <div>
                <h2 className="text-lg font-semibold">Video</h2>
                <p className="text-muted-foreground text-sm">
                    Un video opcional por producto. MP4, WebM o MOV · máx. 200 MB.
                </p>
            </div>

            {video ? (
                <div className="space-y-3 rounded-lg border border-border p-3">
                    <video
                        src={video.url}
                        controls
                        className="max-h-64 w-full rounded-md bg-black"
                    />
                    {editable ? (
                        <div className="flex flex-wrap gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={() => inputRef.current?.click()}
                            >
                                <Upload className="size-4" />
                                Reemplazar
                            </Button>
                            <Popover>
                                <PopoverTrigger asChild>
                                    <Button type="button" variant="destructive" size="sm">
                                        <Trash2 className="size-4" />
                                        Eliminar
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent className="w-48 p-3">
                                    <p className="text-sm">¿Eliminar el video del producto?</p>
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="destructive"
                                        className="mt-3 w-full"
                                        onClick={onDelete}
                                    >
                                        Eliminar
                                    </Button>
                                </PopoverContent>
                            </Popover>
                        </div>
                    ) : null}
                </div>
            ) : editable ? (
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
                    <Film className="text-muted-foreground size-8" aria-hidden />
                    <p className="text-sm font-medium">Arrastra un video o haz clic para seleccionar</p>
                </div>
            ) : (
                <p className="text-muted-foreground text-sm">Este producto no tiene video.</p>
            )}

            <input
                ref={inputRef}
                type="file"
                accept="video/mp4,video/webm,video/quicktime"
                className="sr-only"
                onChange={(event) => {
                    handleFile(event.target.files);
                    event.target.value = '';
                }}
            />

            <InputError message={localError ?? undefined} />
        </div>
    );
}

export function ProductVideoUploaderPending({ label }: { label: string }) {
    return (
        <p className="text-muted-foreground flex items-center gap-2 text-sm">
            <Loader2 className="size-4 animate-spin" aria-hidden />
            {label}
        </p>
    );
}
