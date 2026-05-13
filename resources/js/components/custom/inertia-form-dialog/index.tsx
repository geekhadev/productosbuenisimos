import { Form } from '@inertiajs/react';
import type { ComponentProps, ReactNode } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { cn } from '@/lib/utils';

type FormProps<TForm extends object> = ComponentProps<typeof Form<TForm>>;

export type InertiaFormDialogProps<TForm extends object = Record<string, unknown>> = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    title: ReactNode;
    description: ReactNode;
    /** Remount del `<Form>` al cambiar modo o fila (p. ej. `entity?.id ?? 'create'`). */
    formKey: string;
    /** Props del `<Form>` de Inertia sin `children`, `options`, `className` ni `key`. */
    inertiaForm: Omit<
        FormProps<TForm>,
        'children' | 'options' | 'className' | 'key'
    >;
    children: FormProps<TForm>['children'];
    formOptions?: FormProps<TForm>['options'];
    formClassName?: string;
    contentClassName?: string;
};

/**
 * Diálogo estándar: cabecera accesible + `<Form>` de Inertia con cierre en éxito.
 */
export function InertiaFormDialog<TForm extends object = Record<string, unknown>>({
    open,
    onOpenChange,
    title,
    description,
    formKey,
    inertiaForm,
    children,
    formOptions,
    formClassName,
    contentClassName,
}: InertiaFormDialogProps<TForm>) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className={cn(contentClassName)}>
                <DialogHeader>
                    <DialogTitle>{title}</DialogTitle>
                    <DialogDescription>{description}</DialogDescription>
                </DialogHeader>
                <Form<TForm>
                    key={formKey}
                    {...inertiaForm}
                    options={
                        {
                            preserveScroll: true,
                            ...formOptions,
                            onSuccess: (...args: unknown[]) => {
                                (
                                    formOptions as
                                        | { onSuccess?: (...a: unknown[]) => void }
                                        | undefined
                                )?.onSuccess?.(...args);
                                onOpenChange(false);
                            },
                        } as FormProps<TForm>['options']
                    }
                    className={cn('space-y-6', formClassName)}
                >
                    {children}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
