<?php

namespace App\Http\Requests\Stock;

use App\Http\Requests\Concerns\HandlesInvalidPhpUploads;
use App\Support\SelectedCompanySession;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadProductVideoRequest extends FormRequest
{
    use HandlesInvalidPhpUploads;

    public function authorize(): bool
    {
        $companyId = SelectedCompanySession::selectedCompanyId($this);

        return $companyId !== null && $companyId !== '';
    }

    protected function prepareForValidation(): void
    {
        $this->rejectInvalidPhpUpload('video', '200 MB');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $maxKb = (int) config('media.video.max_kb', 204800);

        return [
            'video' => [
                'required',
                'file',
                'mimetypes:video/mp4,video/webm,video/quicktime,application/mp4,video/x-m4v',
                'max:'.$maxKb,
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'video.mimetypes' => 'Formatos admitidos: MP4, WebM y MOV.',
            'video.max' => 'El video no puede superar 200 MB.',
            'video.uploaded' => 'No se pudo subir el video. Comprueba el tamaño del archivo y la configuración de PHP (upload_max_filesize).',
        ];
    }
}
