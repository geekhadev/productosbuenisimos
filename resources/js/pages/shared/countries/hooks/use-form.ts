import { useMemo } from 'react';
import CountriesController from '@/actions/App/Http/Controllers/Shared/CountriesController';
import type { Country } from '../types';

export function useCountryForm(country: Country | null) {
    const isEdit = country !== null;

    const formProps = useMemo(() => {
        if (isEdit && country) {
            return CountriesController.update.form({ country: country.id });
        }

        return CountriesController.store.form();
    }, [isEdit, country]);

    const headTitle = isEdit ? 'Editar país' : 'Nuevo país';

    return {
        isEdit,
        formProps,
        headTitle,
    };
}
