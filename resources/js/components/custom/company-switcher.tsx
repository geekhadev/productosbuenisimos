import { router, usePage } from '@inertiajs/react';
import { Building2, ChevronsUpDown } from 'lucide-react';
import { useCallback, useMemo } from 'react';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { cn } from '@/lib/utils';
import { store as companySelectionStore } from '@/routes/company-selection';
import type { CompanySelectedSession, SelectableCompanyOption } from '@/types';

function companyPrimaryLabel(row: {
    name: string;
    alias: string | null;
}): string {
    const trimmed = row.alias?.trim();

    return trimmed ? trimmed : row.name;
}

function companyDocumentLine(row: {
    document_type: string | null;
    document_number: string | null;
}): string {
    const tipo = row.document_type?.trim() || '—';
    const numero = row.document_number?.trim() || '—';

    return `${tipo} ${numero}`;
}

export function CompanySwitcher() {
    const page = usePage<{
        show_company_switcher: boolean;
        company_selected: CompanySelectedSession | null;
        selectable_companies: SelectableCompanyOption[];
    }>();

    const {
        show_company_switcher: showSwitcher,
        company_selected: selected,
        selectable_companies: options,
    } = page.props;

    const primaryLabel = useMemo(() => {
        if (!selected) {
            return 'Empresa';
        }

        return companyPrimaryLabel(selected);
    }, [selected]);

    const selectCompany = useCallback((companyId: string) => {
        router.post(
            companySelectionStore.url(),
            { company_id: companyId },
            {
                preserveScroll: true,
                onSuccess: () => {
                    // Las respuestas prefetch del menú incluyen props compartidas antiguas
                    // (p. ej. `company_selected`); sin vaciar la caché, la siguiente visita
                    // puede reutilizar datos obsoletos hasta que expire el prefetch.
                    router.flushAll();
                },
            },
        );
    }, []);

    if (!showSwitcher) {
        return null;
    }

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button
                    variant="outline"
                    size="sm"
                    className="border-sidebar-border/70 text-muted-foreground data-[state=open]:bg-sidebar-accent h-auto max-w-[220px] shrink-0 gap-2 py-2"
                    aria-label="Cambiar empresa"
                >
                    <Building2 className="size-4 shrink-0 self-center" />
                    <div className="flex min-w-0 flex-1 flex-col items-start gap-0.5 text-left">
                        <span className="text-foreground w-full truncate font-medium">
                            {primaryLabel}
                        </span>
                    </div>
                    <ChevronsUpDown className="size-4 shrink-0 self-center opacity-60" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-64">
                <DropdownMenuLabel className="text-muted-foreground text-xs font-normal">
                    Cambiar empresa
                </DropdownMenuLabel>
                <DropdownMenuSeparator />
                {options.map((row) => (
                    <DropdownMenuItem
                        key={row.id}
                        onSelect={() => selectCompany(row.id)}
                        disabled={row.id === selected?.id}
                        className={cn(row.id === selected?.id ? 'cursor-not-allowed font-bold text-blue-500' : 'cursor-pointer')}
                    >
                        <div className="flex min-w-0 flex-col gap-0.5">
                            <span className="truncate font-medium">
                                {companyPrimaryLabel(row)}
                            </span>
                            <span className="text-muted-foreground truncate text-xs">
                                {companyDocumentLine(row)}
                            </span>
                        </div>
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
