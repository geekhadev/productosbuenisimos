import type { InertiaLinkProps } from '@inertiajs/react';
import type { VariantProps } from 'class-variance-authority';
import type { ReactNode } from 'react';

import type { buttonVariants } from '@/components/ui/button';

type ButtonVisualProps = {
    /** Variante visual del `Button` envolvente (no confundir con props de `Link`). */
    buttonVariant?: VariantProps<typeof buttonVariants>['variant'];
    buttonSize?: VariantProps<typeof buttonVariants>['size'];
};

export type FormLinkButtonProps = Omit<InertiaLinkProps, 'children'> &
    ButtonVisualProps & {
        icon?: ReactNode;
        label: string;
        containerClassName?: string;
        buttonClassName?: string;
    };
