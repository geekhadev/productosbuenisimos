export default function AppLogo() {
    return (
        <>
            <div className="flex aspect-square size-8 shrink-0 items-center justify-center overflow-hidden rounded-md border border-sidebar-border/80 bg-sidebar">
                <img
                    src="/assets/icono.png"
                    alt="Productos Buenisimos"
                    width={32}
                    height={32}
                    className="size-full object-contain p-0.5"
                />
            </div>
            <div className="ml-1 grid flex-1 text-left text-sm">
                <span className="mb-0.5 truncate leading-tight font-semibold">
                    Productos Buenisimos
                </span>
            </div>
        </>
    );
}
