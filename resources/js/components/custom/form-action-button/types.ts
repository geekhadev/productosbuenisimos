import type { ComponentProps, ReactNode } from 'react';

import type { Button } from '@/components/ui/button';

export type FormActionButtonProps = Omit<
    ComponentProps<typeof Button>,
    'children'
> & {
    /** Icono opcional; se recomienda `lucide-react` con clase `size-*` acorde al `size` del botón. */
    icon?: ReactNode;
    label: string;
    containerClassName?: string;
    buttonClassName?: string;
};
