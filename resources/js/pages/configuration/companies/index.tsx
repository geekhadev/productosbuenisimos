import { Head, Link } from '@inertiajs/react';
import { Building2, CirclePlus, Mail, MapPin, Phone } from 'lucide-react';
import { FormLinkButton } from '@/components/custom/form-link-button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { cn } from '@/lib/utils';
import type { CompaniesIndexPageProps, CompanyListItem } from '@/pages/configuration/companies/types';
import { dashboard } from '@/routes';
import {
    create as companiesCreate,
    edit,
    index as companiesIndex,
} from '@/routes/configuration/companies';

function CompanyCard({ company: c }: { company: CompanyListItem }) {
    const body = (
        <Card
            className={cn(
                'shadow-xs h-full transition-colors',
                c.can.update &&
                    'group-hover:border-primary/40 group-hover:bg-muted/20',
            )}
        >
            <CardHeader className="pb-2">
                <CardTitle className="text-lg">{c.name}</CardTitle>
                <CardDescription>
                    {c.alias ? `Alias: ${c.alias}` : null}
                </CardDescription>
            </CardHeader>
            <CardContent className="space-y-2 text-sm">
                <div className="text-muted-foreground flex flex-wrap items-center gap-x-2 gap-y-1">
                    <span className="font-medium text-foreground">
                        {c.document_type}
                    </span>
                    <span>{c.document_number}</span>
                </div>
                {c.email ? (
                    <div className="flex items-start gap-2">
                        <Mail className="text-muted-foreground mt-0.5 size-4 shrink-0" />
                        <span className="break-all">{c.email}</span>
                    </div>
                ) : null}
                {c.phone ? (
                    <div className="flex items-center gap-2">
                        <Phone className="text-muted-foreground size-4 shrink-0" />
                        <span>{c.phone}</span>
                    </div>
                ) : null}
                {c.address ? (
                    <div className="flex items-start gap-2">
                        <MapPin className="text-muted-foreground mt-0.5 size-4 shrink-0" />
                        <span>{c.address}</span>
                    </div>
                ) : null}
                {c.owner ? (
                    <p className="text-muted-foreground pt-1 text-xs">
                        Propietario: {c.owner.name}
                    </p>
                ) : null}
            </CardContent>
        </Card>
    );

    if (c.can.update) {
        return (
            <Link
                href={edit.url(c.id)}
                aria-label={`Editar empresa ${c.name}`}
                className="group block rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background"
            >
                {body}
            </Link>
        );
    }

    return body;
}

function CompaniesIndex({ companies, can }: CompaniesIndexPageProps) {
    return (
        <>
            <Head title="Empresas" />

            <div className="flex flex-row items-start gap-12 space-y-6 p-4 max-w-[1400px]">
                <div className="flex flex-col gap-4 max-w-xs">
                    <h1 className="text-2xl font-semibold tracking-tight">
                        Empresas
                    </h1>
                    <p className="text-muted-foreground text-sm">
                        Agrega las diferentes empresas que gestiona.
                    </p>
                    <p className="text-muted-foreground text-sm">
                        Ten en cuenta que cada empresa debe ser un RUT diferente y las configuraciones e información de cada empresa se gestionan por separado.
                    </p>
                    {can.create ? (
                        <FormLinkButton
                            href={companiesCreate.url()}
                            icon={<CirclePlus />}
                            label="Nueva empresa"
                            containerClassName="w-auto"
                        />
                    ) : null}
                </div>

                <div className="w-full">
                    {companies.length === 0 ? (
                        <Card className="border-dashed">
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-lg">
                                    <Building2 className="size-5" />
                                    Sin empresas
                                </CardTitle>
                                <CardDescription>
                                    Aún no hay empresas registradas. Crea la primera
                                    para comenzar.
                                </CardDescription>
                            </CardHeader>
                        </Card>
                    ) : (
                        <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-2">
                            {companies.map((c) => (
                                <CompanyCard key={c.id} company={c} />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}

CompaniesIndex.layout = {
    breadcrumbs: [
        { title: 'Panel', href: dashboard() },
        { title: 'Empresas', href: companiesIndex() },
    ],
};

export default CompaniesIndex;
