import { jsonFetchHeaders } from '@/lib/json-fetch-headers';

type MediaApiResponse = {
    images: ProductMediaItem[];
    video: ProductMediaItem | null;
};

export type ProductMediaItem = {
    id: string;
    url: string;
    sort_order: number;
    mime_type: string;
    original_name: string;
    size: number;
};

export async function postProductMediaForm(
    url: string,
    formData: FormData,
): Promise<MediaApiResponse> {
    const response = await fetch(url, {
        method: 'POST',
        body: formData,
        headers: jsonFetchHeaders(),
        credentials: 'same-origin',
    });

    return parseMediaResponse(response);
}

export async function putProductMediaJson(
    url: string,
    body: Record<string, unknown>,
): Promise<MediaApiResponse> {
    const response = await fetch(url, {
        method: 'PUT',
        headers: {
            ...jsonFetchHeaders(),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(body),
        credentials: 'same-origin',
    });

    return parseMediaResponse(response);
}

export async function deleteProductMedia(url: string): Promise<MediaApiResponse> {
    const response = await fetch(url, {
        method: 'DELETE',
        headers: jsonFetchHeaders(),
        credentials: 'same-origin',
    });

    return parseMediaResponse(response);
}

async function parseMediaResponse(response: Response): Promise<MediaApiResponse> {
    const payload = (await response.json()) as MediaApiResponse & {
        message?: string;
        errors?: Record<string, string[]>;
    };

    if (!response.ok) {
        const firstFieldError = payload.errors
            ? Object.values(payload.errors).flat()[0]
            : undefined;

        throw new Error(firstFieldError ?? payload.message ?? 'No se pudo completar la operación.');
    }

    return payload;
}
