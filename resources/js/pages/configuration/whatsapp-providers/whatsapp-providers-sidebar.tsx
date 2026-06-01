export function WhatsappProvidersSidebar() {
    return (
        <div className="flex max-w-xs flex-col gap-4">
            <h1 className="text-2xl font-semibold tracking-tight">
                Proveedores de WhatsApp
            </h1>
            <p className="text-muted-foreground text-sm">
                Configura las credenciales de los proveedores de WhatsApp usados
                para recibir y enviar mensajes del agente de ventas.
            </p>
            <p className="text-muted-foreground text-sm">
                Las credenciales se guardan cifradas en la base de datos. Solo
                se mostrarán los últimos caracteres de los campos secretos y la
                fecha de actualización en el listado.
            </p>
        </div>
    );
}
