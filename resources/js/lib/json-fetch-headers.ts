/**
 * Headers for `fetch()` to Laravel JSON routes (Sanctum/session + CSRF cookie).
 */
export function jsonFetchHeaders(): HeadersInit {
    const raw = document.cookie
        .split('; ')
        .find((row) => row.startsWith('XSRF-TOKEN='));

    const token = raw
        ? decodeURIComponent(raw.slice('XSRF-TOKEN='.length).replace(/\+/g, ' '))
        : null;

    return {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...(token ? { 'X-XSRF-TOKEN': token } : {}),
    };
}
