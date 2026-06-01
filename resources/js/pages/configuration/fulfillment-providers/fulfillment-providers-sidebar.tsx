export function FulfillmentProvidersSidebar() {
    return (
        <div className="flex max-w-xs flex-col gap-4">
            <h1 className="text-2xl font-semibold tracking-tight">
                Proveedores de fulfillment
            </h1>
            <p className="text-muted-foreground text-sm">
                Configura las credenciales de los proveedores de fulfillment
                usados para gestionar envíos y entregas de pedidos.
            </p>
            <p className="text-muted-foreground text-sm">
                Las credenciales se guardan cifradas en la base de datos. Solo
                se mostrarán los últimos caracteres de la contraseña y la fecha
                de actualización en el listado.
            </p>
        </div>
    );
}
