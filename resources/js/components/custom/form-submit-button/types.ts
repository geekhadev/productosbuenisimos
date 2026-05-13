import type { ComponentProps, ReactNode } from 'react';

import type { Button } from '@/components/ui/button';

export type FormSubmitButtonProps = Omit<
    ComponentProps<typeof Button>,
    'children'
> & {
    loading?: boolean;
    /** Icono opcional; se recomienda un icono de `lucide-react` para coherencia con el resto del UI. */
    icon?: ReactNode;
    label?: string;
    labelLoading?: string;
    error?: string;
    containerClassName?: string;
    buttonClassName?: string;
    errorClassName?: string;
};
