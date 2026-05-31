<?php

namespace App\Http\Requests\Concerns;

use App\Support\Http\PhpUploadErrorMessage;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

trait HandlesInvalidPhpUploads
{
    protected function rejectInvalidPhpUpload(string $attribute, string $applicationMaxHuman): void
    {
        $file = $this->file($attribute);

        if (! $file instanceof UploadedFile) {
            return;
        }

        $message = PhpUploadErrorMessage::for($file, $applicationMaxHuman);

        if ($message !== null) {
            throw ValidationException::withMessages([
                $attribute => [$message],
            ]);
        }
    }

    protected function rejectInvalidPhpUploadList(string $listKey, string $applicationMaxHuman): void
    {
        foreach (array_values($this->file($listKey, [])) as $index => $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $message = PhpUploadErrorMessage::for($file, $applicationMaxHuman);

            if ($message !== null) {
                throw ValidationException::withMessages([
                    "{$listKey}.{$index}" => [$message],
                ]);
            }
        }
    }
}
