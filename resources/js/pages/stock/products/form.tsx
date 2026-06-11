import { Head } from '@inertiajs/react';
import { Save, X } from 'lucide-react';
import { FormLinkButton } from '@/components/custom/form-link-button';
import { FormSubmitButton } from '@/components/custom/form-submit-button';
import { FormTextInput } from '@/components/custom/form-text-input';
import { FormTextarea } from '@/components/custom/form-textarea';
import {
    ProductMediaUploader,
    ProductMediaUploaderPending,
} from '@/components/custom/product-media-uploader';
import { ProductSimilarProducts } from '@/components/custom/product-similar-products';
import {
    ProductVideoUploader,
    ProductVideoUploaderPending,
} from '@/components/custom/product-video-uploader';
import InputError from '@/components/input-error';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { useProductForm } from '@/pages/stock/products/hooks/use-product-form';
import { useProductMedia } from '@/pages/stock/products/hooks/use-product-media';
import { useSimilarProducts } from '@/pages/stock/products/hooks/use-similar-products';
import type { ProductsFormPageProps } from '@/pages/stock/products/types';
import { index as productsIndex } from '@/routes/stock/products';

function ProductForm(props: ProductsFormPageProps) {
    const { form, submit, headTitle, isEdit } = useProductForm(props);
    const productId = props.product?.id;
    const editable = props.can.updateMedia && (props.product?.is_active ?? false);

    const {
        media,
        error: mediaError,
        uploadingImages,
        uploadingVideo,
        reordering,
        uploadImages,
        reorderImages,
        deleteImage,
        uploadVideo,
        deleteVideo,
    } = useProductMedia(props.media);

    const {
        similar,
        error: similarError,
        searchResults,
        searching,
        saving: savingSimilar,
        searchProducts,
        attachProduct,
        detachProduct,
        clearSearch,
    } = useSimilarProducts(productId ?? '', props.similar);

    return (
        <>
            <Head title={headTitle} />

            <div className="flex min-w-0 flex-1 flex-col gap-6 p-4">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">{headTitle}</h1>
                        <p className="text-muted-foreground mt-1 text-sm">
                            Los campos nombre, código y SKU son únicos dentro de la empresa
                            seleccionada.
                        </p>
                    </div>
                    <FormLinkButton
                        href={productsIndex.url()}
                        icon={<X />}
                        label="Volver al listado"
                        buttonVariant="outline"
                        containerClassName="w-auto"
                    />
                </div>

                <div className={isEdit && productId != null ? 'grid gap-6 xl:grid-cols-2' : ''}>
                    <form onSubmit={submit} className="space-y-6">
                        <FormTextInput
                            label="Nombre"
                            required
                            error={form.errors.name}
                            inputProps={{
                                id: 'product-name',
                                name: 'name',
                                maxLength: 255,
                                value: form.data.name,
                                onChange: (e) => form.setData('name', e.target.value),
                            }}
                        />
                        <div className="grid gap-4 sm:grid-cols-3">
                            <FormTextInput
                                label="Código"
                                required
                                error={form.errors.code}
                                inputProps={{
                                    id: 'product-code',
                                    name: 'code',
                                    maxLength: 255,
                                    value: form.data.code,
                                    onChange: (e) => form.setData('code', e.target.value),
                                }}
                            />
                            <FormTextInput
                                label="SKU"
                                required
                                error={form.errors.sku}
                                inputProps={{
                                    id: 'product-sku',
                                    name: 'sku',
                                    maxLength: 255,
                                    value: form.data.sku,
                                    onChange: (e) => form.setData('sku', e.target.value),
                                }}
                            />
                            <FormTextInput
                                label="Precio"
                                error={form.errors.price}
                                inputProps={{
                                    id: 'product-price',
                                    name: 'price',
                                    inputMode: 'decimal',
                                    value: form.data.price,
                                    onChange: (e) => form.setData('price', e.target.value),
                                }}
                            />
                            <FormTextInput
                                label="Ancho"
                                error={form.errors.width}
                                inputProps={{
                                    id: 'product-width',
                                    name: 'width',
                                    inputMode: 'decimal',
                                    value: form.data.width,
                                    onChange: (e) => form.setData('width', e.target.value),
                                }}
                            />
                            <FormTextInput
                                label="Largo"
                                error={form.errors.length}
                                inputProps={{
                                    id: 'product-length',
                                    name: 'length',
                                    inputMode: 'decimal',
                                    value: form.data.length,
                                    onChange: (e) => form.setData('length', e.target.value),
                                }}
                            />
                            <FormTextInput
                                label="Alto"
                                error={form.errors.height}
                                inputProps={{
                                    id: 'product-height',
                                    name: 'height',
                                    inputMode: 'decimal',
                                    value: form.data.height,
                                    onChange: (e) => form.setData('height', e.target.value),
                                }}
                            />
                            <FormTextInput
                                label="Volumen"
                                error={form.errors.volume}
                                inputProps={{
                                    id: 'product-volume',
                                    name: 'volume',
                                    inputMode: 'decimal',
                                    value: form.data.volume,
                                    onChange: (e) => form.setData('volume', e.target.value),
                                }}
                            />
                            <FormTextInput
                                label="Peso"
                                error={form.errors.weight}
                                inputProps={{
                                    id: 'product-weight',
                                    name: 'weight',
                                    inputMode: 'decimal',
                                    value: form.data.weight,
                                    onChange: (e) => form.setData('weight', e.target.value),
                                }}
                            />
                            <FormTextInput
                                label="Stock mínimo"
                                error={form.errors.minimum_stock}
                                inputProps={{
                                    id: 'product-minimum_stock',
                                    name: 'minimum_stock',
                                    inputMode: 'numeric',
                                    value: form.data.minimum_stock,
                                    onChange: (e) =>
                                        form.setData('minimum_stock', e.target.value),
                                }}
                            />
                        </div>

                        <FormTextarea
                            label="Descripción"
                            error={form.errors.description}
                            textareaProps={{
                                id: 'product-description',
                                name: 'description',
                                value: form.data.description,
                                onChange: (e) => form.setData('description', e.target.value),
                            }}
                        />

                        {isEdit && productId != null ? (
                            <section className="space-y-2 rounded-lg border border-border p-4">
                                <div>
                                    <h2 className="text-base font-semibold tracking-tight">
                                        Productos similares
                                    </h2>
                                </div>

                                <ProductSimilarProducts
                                    similar={similar}
                                    editable={editable}
                                    searchResults={searchResults}
                                    searching={searching}
                                    saving={savingSimilar}
                                    onSearch={searchProducts}
                                    onAttach={(id) => void attachProduct(id)}
                                    onDetach={(id) => void detachProduct(id)}
                                    onClearSearch={clearSearch}
                                />
                                <InputError message={similarError ?? undefined} />
                            </section>
                        ) : null}

                        <div className="flex flex-row flex-wrap items-center justify-between gap-3 rounded-lg border border-border p-4">
                            <div className="grid min-w-0 gap-1">
                                <Label htmlFor="product-is_active">Activo</Label>
                                <p className="text-muted-foreground text-sm">
                                    Desactiva el producto antes de eliminarlo del catálogo.
                                </p>
                            </div>
                            <div className="flex shrink-0 items-center gap-2">
                                <input
                                    type="hidden"
                                    name="is_active"
                                    value={form.data.is_active ? '1' : '0'}
                                />
                                <Switch
                                    id="product-is_active"
                                    checked={form.data.is_active}
                                    onCheckedChange={(v) => form.setData('is_active', v)}
                                />
                            </div>
                        </div>
                        <InputError message={form.errors.is_active} />

                        <div className="flex flex-wrap gap-2">
                            <FormSubmitButton
                                loading={form.processing}
                                icon={<Save />}
                                label="Guardar"
                            />
                        </div>
                    </form>

                    {isEdit && productId != null ? (
                        <section className="space-y-8 rounded-lg border border-border p-4">
                            <div>
                                <h2 className="text-xl font-semibold tracking-tight">Medios</h2>
                                <p className="text-muted-foreground mt-1 text-sm">
                                    Las imágenes y el video se guardan al instante, sin esperar al
                                    botón Guardar del formulario.
                                </p>
                            </div>

                            <ProductMediaUploader
                                images={media.images}
                                editable={editable}
                                onUpload={(files) => void uploadImages(productId, files)}
                                onReorder={(order) => void reorderImages(productId, order)}
                                onDelete={(mediaId) => void deleteImage(productId, mediaId)}
                            />
                            {uploadingImages ? (
                                <ProductMediaUploaderPending label="Subiendo imágenes…" />
                            ) : null}
                            {reordering ? (
                                <ProductMediaUploaderPending label="Guardando orden…" />
                            ) : null}

                            <ProductVideoUploader
                                video={media.video}
                                editable={editable}
                                onUpload={(file) => void uploadVideo(productId, file)}
                                onDelete={() => void deleteVideo(productId)}
                            />
                            {uploadingVideo ? (
                                <ProductVideoUploaderPending label="Subiendo video…" />
                            ) : null}

                            <InputError message={mediaError ?? undefined} />
                        </section>
                    ) : null}
                </div>
            </div>
        </>
    );
}

export default ProductForm;
