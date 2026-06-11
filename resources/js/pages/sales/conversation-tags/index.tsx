import { Head, useForm } from '@inertiajs/react';
import { CirclePlus, PencilIcon, TrashIcon } from 'lucide-react';
import { useCallback, useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { dashboard } from '@/routes';
import { destroy, index as conversationTagsIndex, store, update } from '@/routes/sales/conversation-tags';

const TAILWIND_COLORS = [
    'red-500', 'orange-500', 'amber-500', 'yellow-500',
    'lime-500', 'green-500', 'emerald-500', 'teal-500',
    'cyan-500', 'sky-500', 'blue-500', 'indigo-500',
    'violet-500', 'purple-500', 'fuchsia-500', 'pink-500',
    'rose-500', 'slate-500', 'gray-500', 'zinc-500',
] as const;

const COLOR_HEX: Record<string, string> = {
    'red-500': '#ef4444', 'orange-500': '#f97316', 'amber-500': '#f59e0b',
    'yellow-500': '#eab308', 'lime-500': '#84cc16', 'green-500': '#22c55e',
    'emerald-500': '#10b981', 'teal-500': '#14b8a6', 'cyan-500': '#06b6d4',
    'sky-500': '#0ea5e9', 'blue-500': '#3b82f6', 'indigo-500': '#6366f1',
    'violet-500': '#8b5cf6', 'purple-500': '#a855f7', 'fuchsia-500': '#d946ef',
    'pink-500': '#ec4899', 'rose-500': '#f43f5e', 'slate-500': '#64748b',
    'gray-500': '#6b7280', 'zinc-500': '#71717a',
};

type ConversationTag = {
    id: string;
    name: string;
    description: string;
    color: string;
    sort_order: number;
};

type PageProps = {
    tags: ConversationTag[];
};

type TagFormData = {
    name: string;
    description: string;
    color: string;
    sort_order: number;
};

function TagForm({
    data,
    errors,
    setData,
    submit,
    onClose,
    isEdit,
}: {
    data: TagFormData;
    errors: Partial<Record<keyof TagFormData, string>>;
    setData: (key: keyof TagFormData, value: string | number) => void;
    submit: (e: React.FormEvent) => void;
    onClose: () => void;
    isEdit: boolean;
}) {
    return (
        <form onSubmit={submit} className="flex flex-col gap-4">
            <div className="flex flex-col gap-1.5">
                <Label htmlFor="tag-sort_order">Orden</Label>
                <Input
                    id="tag-sort_order"
                    type="number"
                    min={0}
                    value={data.sort_order}
                    onChange={(e) => setData('sort_order', parseInt(e.target.value, 10) || 0)}
                />
                {errors.sort_order && (
                    <p className="text-destructive text-sm">{errors.sort_order}</p>
                )}
            </div>

            <div className="flex flex-col gap-1.5">
                <Label htmlFor="tag-name">Nombre</Label>
                <Input
                    id="tag-name"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    placeholder="Ej: Interés confirmado"
                />
                {errors.name && (
                    <p className="text-destructive text-sm">{errors.name}</p>
                )}
            </div>

            <div className="flex flex-col gap-1.5">
                <Label htmlFor="tag-description">Descripción</Label>
                <Textarea
                    id="tag-description"
                    value={data.description}
                    onChange={(e) => setData('description', e.target.value)}
                    placeholder="Describe cuándo el agente debe asignar esta etiqueta"
                    rows={3}
                />
                {errors.description && (
                    <p className="text-destructive text-sm">{errors.description}</p>
                )}
            </div>

            <div className="flex flex-col gap-1.5">
                <Label>Color</Label>
                <div className="flex flex-wrap gap-2">
                    {TAILWIND_COLORS.map((color) => (
                        <button
                            key={color}
                            type="button"
                            title={color}
                            onClick={() => setData('color', color)}
                            className="size-6 rounded-full transition-transform hover:scale-110 focus:outline-none"
                            style={{
                                backgroundColor: COLOR_HEX[color],
                                outline: data.color === color ? '2px solid currentColor' : 'none',
                                outlineOffset: '2px',
                            }}
                        />
                    ))}
                </div>
                {errors.color && (
                    <p className="text-destructive text-sm">{errors.color}</p>
                )}
            </div>

            <div className="flex justify-end gap-2 pt-2">
                <Button type="button" variant="outline" onClick={onClose}>
                    Cancelar
                </Button>
                <Button type="submit">
                    {isEdit ? 'Guardar cambios' : 'Crear etiqueta'}
                </Button>
            </div>
        </form>
    );
}

function ConversationTagsIndex({ tags }: PageProps) {
    const [dialogOpen, setDialogOpen] = useState(false);
    const [editing, setEditing] = useState<ConversationTag | null>(null);

    const form = useForm<TagFormData>({
        name: '',
        description: '',
        color: 'blue-500',
        sort_order: 0,
    });

    const openCreate = useCallback(() => {
        form.reset();
        form.setData({
            name: '',
            description: '',
            color: 'blue-500',
            sort_order: tags.length > 0 ? Math.max(...tags.map((t) => t.sort_order)) + 1 : 0,
        });
        setEditing(null);
        setDialogOpen(true);
    }, [tags, form]);

    const openEdit = useCallback((tag: ConversationTag) => {
        form.setData({
            name: tag.name,
            description: tag.description,
            color: tag.color,
            sort_order: tag.sort_order,
        });
        setEditing(tag);
        setDialogOpen(true);
    }, [form]);

    const closeDialog = useCallback(() => {
        setDialogOpen(false);
        setEditing(null);
        form.reset();
    }, [form]);

    const submit = useCallback((e: React.FormEvent) => {
        e.preventDefault();

        if (editing !== null) {
            form.put(update.url(editing.id), {
                preserveScroll: true,
                onSuccess: closeDialog,
            });
        } else {
            form.post(store.url(), {
                preserveScroll: true,
                onSuccess: closeDialog,
            });
        }
    }, [editing, form, closeDialog]);

    const handleDelete = useCallback((tag: ConversationTag) => {
        if (!window.confirm(`¿Eliminar la etiqueta «${tag.name}»?`)) return;

        form.delete(destroy.url(tag.id), { preserveScroll: true });
    }, [form]);

    return (
        <>
            <Head title="Etiquetas de conversación" />

            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-xl font-semibold">Etiquetas de conversación</h1>
                        <p className="text-muted-foreground text-sm">
                            Define las etapas del embudo de ventas. El agente las asignará automáticamente.
                        </p>
                    </div>
                    <Button onClick={openCreate} className="gap-1.5">
                        <CirclePlus className="size-4" />
                        Nueva etiqueta
                    </Button>
                </div>

                {tags.length === 0 ? (
                    <div className="text-muted-foreground flex flex-1 items-center justify-center rounded-lg border border-dashed py-16 text-sm">
                        Sin etiquetas. Crea la primera para que el agente pueda rastrear el embudo.
                    </div>
                ) : (
                    <div className="rounded-lg border">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b">
                                    <th className="text-muted-foreground w-16 px-4 py-3 text-left font-medium">Orden</th>
                                    <th className="text-muted-foreground w-8 px-4 py-3 text-left font-medium">Color</th>
                                    <th className="text-muted-foreground px-4 py-3 text-left font-medium">Nombre</th>
                                    <th className="text-muted-foreground px-4 py-3 text-left font-medium">Descripción</th>
                                    <th className="text-muted-foreground w-0 px-4 py-3 text-right font-medium">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                {tags.map((tag) => (
                                    <tr key={tag.id} className="hover:bg-muted/40 border-b last:border-0">
                                        <td className="px-4 py-3 tabular-nums">{tag.sort_order}</td>
                                        <td className="px-4 py-3">
                                            <span
                                                className="inline-block size-4 rounded-full"
                                                style={{ backgroundColor: COLOR_HEX[tag.color] ?? '#6b7280' }}
                                            />
                                        </td>
                                        <td className="px-4 py-3 font-medium">{tag.name}</td>
                                        <td className="text-muted-foreground px-4 py-3">{tag.description}</td>
                                        <td className="px-4 py-3">
                                            <div className="flex justify-end gap-1">
                                                <Button
                                                    variant="outline"
                                                    size="icon"
                                                    type="button"
                                                    onClick={() => openEdit(tag)}
                                                    aria-label={`Editar ${tag.name}`}
                                                >
                                                    <PencilIcon className="size-3" />
                                                </Button>
                                                <Button
                                                    variant="destructive"
                                                    size="icon"
                                                    type="button"
                                                    onClick={() => handleDelete(tag)}
                                                    aria-label={`Eliminar ${tag.name}`}
                                                >
                                                    <TrashIcon className="size-3" />
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>

            <Dialog open={dialogOpen} onOpenChange={(open) => { if (!open) closeDialog(); }}>
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>
                            {editing !== null ? 'Editar etiqueta' : 'Nueva etiqueta'}
                        </DialogTitle>
                    </DialogHeader>
                    <TagForm
                        data={form.data}
                        errors={form.errors}
                        setData={form.setData}
                        submit={submit}
                        onClose={closeDialog}
                        isEdit={editing !== null}
                    />
                </DialogContent>
            </Dialog>
        </>
    );
}

ConversationTagsIndex.layout = {
    breadcrumbs: [
        { title: 'Panel', href: dashboard() },
        { title: 'Etiquetas de conversación', href: conversationTagsIndex() },
    ],
};

export default ConversationTagsIndex;
