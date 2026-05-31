<?php

namespace App\Support\Http;

use Illuminate\Http\UploadedFile;

final class PhpUploadErrorMessage
{
    public static function for(UploadedFile $file, string $applicationMaxHuman): ?string
    {
        if ($file->isValid()) {
            return null;
        }

        return match ($file->getError()) {
            UPLOAD_ERR_INI_SIZE => sprintf(
                'El archivo supera el límite de subida del servidor (%s). Aumenta upload_max_filesize en PHP o reduce el archivo. Máximo de la aplicación: %s.',
                IniSize::uploadMaxHuman(),
                $applicationMaxHuman,
            ),
            UPLOAD_ERR_FORM_SIZE => sprintf(
                'El archivo supera el límite permitido por el formulario. Máximo de la aplicación: %s.',
                $applicationMaxHuman,
            ),
            UPLOAD_ERR_PARTIAL => 'La subida se interrumpió. Vuelve a intentarlo.',
            UPLOAD_ERR_NO_FILE => 'No se recibió ningún archivo.',
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_EXTENSION => 'El servidor no pudo guardar el archivo temporal. Contacta al administrador.',
            default => 'No se pudo subir el archivo. Vuelve a intentarlo.',
        };
    }
}
