<?php

namespace Veltrix\Form\Field;

use Illuminate\Support\Facades\Storage;

class Image extends File
{
    public function getDirectory(): string
    {
        return $this->directory ?: config('veltrix.upload.directory.image', config('admin.upload.directory.image', 'images'));
    }

    public function renderField(): string
    {
        $current = $this->getValue();
        $previewHtml = '';

        if ($current && is_string($current)) {
            $url = url()->isValidUrl($current) ? $current : Storage::disk($this->getDisk())->url($current);
            $previewHtml = "<div class=\"vx-image-preview\" style=\"margin-bottom: 10px;\">
                <img src=\"{$url}\" style=\"max-width: 150px; max-height: 150px; object-fit: cover; border-radius: 6px; border: 1px solid var(--vx-border);\" />
                <label class=\"vx-file-del-label\" style=\"display: block; margin-top: 6px;\">
                    <input type=\"checkbox\" name=\"_file_del_{$this->column}\" value=\"1\">
                    " . (veltrix_trans('delete') ?: 'Remove image') . "
                </label>
            </div>";
        }

        return "{$previewHtml}
            <input type=\"file\" accept=\"image/*\" name=\"{$this->column}\" id=\"field-{$this->column}\" class=\"vx-file-input\">";
    }
}
