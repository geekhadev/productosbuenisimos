import { Form, Head, router } from '@inertiajs/react';
import { ArrowRight, Building2, LogOut } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import type { CompanyListItem } from '@/pages/configuration/companies/types';
import { logout } from '@/routes';
import { store as companySelectionStore } from '@/routes/company-selection';

export type CompanySelectionCard = Omit<CompanyListItem, 'can'>;

export type CompanySelectionPageProps = {
    companies: CompanySelectionCard[];
};

const SELECTION_INTRO_COPY =
    'Tu espacio de trabajo queda asociado a la empresa que elijas ahora. Cada organización mantiene su propia configuración y datos; al cambiar de contexto solo verás lo que corresponde a esa razón social. Revisa documento y alias, elige con claridad y continúa; más tarde podrás cambiar de empresa desde la barra superior.';

export default function CompanySelection({
    companies,
}: CompanySelectionPageProps) {
    return (
        <div className="bg-background min-h-screen flex items-center justify-center">
            <Head title="Seleccionar empresa" />
            <div className="mx-auto w-full max-w-8xl px-4 py-10 md:px-6">
                <div className="grid grid-cols-1 gap-10 lg:grid-cols-12 lg:gap-12">
                    <aside className="flex flex-col gap-6 lg:col-span-3">
                        <div className="space-y-3">
                            <img src="/assets/logo.png" alt="Productos Buenisimos" className="w-48 h-auto" />
                            <h1 className="text-2xl font-semibold tracking-tight">
                                Seleccionar empresa
                            </h1>
                            <p className="text-muted-foreground text-sm leading-relaxed">
                                {SELECTION_INTRO_COPY}
                            </p>
                        </div>
                        <Form {...logout.form()}>
                            <Button
                                type="submit"
                                variant="outline"
                                className="w-full gap-2 sm:w-auto"
                            >
                                <LogOut className="size-4" />
                                Cerrar sesión
                            </Button>
                        </Form>
                    </aside>

                    <div className="lg:col-span-9">
                        {companies.length === 0 ? (
                            <Card className="border-dashed">
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2 text-lg">
                                        <Building2 className="size-5" />
                                        Sin empresas disponibles
                                    </CardTitle>
                                    <CardDescription>
                                        No hay empresas asociadas a tu cuenta.
                                        Crea una desde Configuración cuando tengas
                                        permisos, o contacta a un administrador.
                                    </CardDescription>
                                </CardHeader>
                            </Card>
                        ) : (
                            <div className="grid gap-4 sm:grid-cols-3">
                                {companies.map((c) => (
                                    <Card
                                        key={c.id}
                                        className="shadow-xs flex flex-col"
                                    >
                                        <CardHeader>
                                            <CardTitle className="text-lg">
                                                {c.alias ? c.alias : c.name}
                                            </CardTitle>
                                            <CardDescription>
                                                {c.alias ? (
                                                    <div className="flex flex-wrap items-center gap-x-2 gap-y-1">
                                                        <span>{c.name}</span>
                                                    </div>
                                                ): null}
                                                <div className="flex flex-wrap items-center gap-x-2 gap-y-1">
                                                    <span className="font-medium text-foreground">
                                                        {c.document_type}
                                                    </span>
                                                    <span>
                                                        {c.document_number}
                                                    </span>
                                                </div>
                                                <Form
                                                    {...companySelectionStore.form()}
                                                    className="mt-auto flex justify-end pt-3"
                                                    onSuccess={() => {
                                                        router.flushAll();
                                                    }}
                                                >
                                                    <input
                                                        type="hidden"
                                                        name="company_id"
                                                        value={c.id}
                                                    />
                                                    <Button
                                                        type="submit"
                                                        className="w-full sm:w-auto"
                                                    >
                                                        <ArrowRight className="size-4" />
                                                        Entrar
                                                    </Button>
                                                </Form>
                                            </CardDescription>
                                        </CardHeader>
                                    </Card>
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}
