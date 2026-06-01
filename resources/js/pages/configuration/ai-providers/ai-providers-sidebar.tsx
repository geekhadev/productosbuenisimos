export function AiProvidersSidebar() {
    return (
        <div className="flex max-w-xs flex-col gap-4">
            <h1 className="text-2xl font-semibold tracking-tight">
                Proveedores de IA
            </h1>
            <p className="text-muted-foreground text-sm">
                Configura las credenciales de los proveedores de inteligencia
                artificial usados por el agente de ventas y el chatbot público.
            </p>
            <p className="text-muted-foreground text-sm">
                Las claves se guardan cifradas en la base de datos. Marca con el
                radio el proveedor predeterminado en el listado; el agente de
                ventas también puede elegir proveedor en su propia configuración
                entre los que tengan credencial disponible aquí.
            </p>
        </div>
    );
}
