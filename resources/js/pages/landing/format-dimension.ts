export function formatLandingDimension(value: string | number): string {
    const n = typeof value === 'string' ? Number.parseFloat(value) : value;

    if (Number.isNaN(n)) {
        return '—';
    }

    return new Intl.NumberFormat('es-CL', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 3,
    }).format(n);
}
