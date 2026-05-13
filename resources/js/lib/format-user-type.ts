/**
 * Etiqueta legible para el enum de tipo de usuario (`root` | `owner` | `user`).
 */
export function formatUserType(value: string): string {
    switch (value) {
        case 'root':
            return 'Root';
        case 'owner':
            return 'Owner';
        case 'user':
            return 'Usuario';
        default:
            return value;
    }
}
