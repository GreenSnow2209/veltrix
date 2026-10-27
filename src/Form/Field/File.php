<?php

namespace Veltrix\Form\Field;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Veltrix\Form\Field;

class File extends Field
{
    protected ?string $disk = null;
    protected ?string $directory = null;
    protected array $allowedExtensions = [];

    public function disk(string $disk): static
    {
        $this->disk = $disk;
        return $this;
    }

    public function move(string $directory): static
    {
        $this->directory = $directory;
        return $this;
    }

    public function rules(mixed $rules): static
    {
        $this->rules = $rules;
        return $this;
    }

    public function getDisk(): string
    {
        return $this->disk ?: config('veltrix.upload.disk', config('admin.upload.disk', 'public'));
    }

    public function getDirectory(): string
    {
        return $this->directory ?: config('veltrix.upload.directory.file', config('admin.upload.directory.file', 'files'));
    }

    public function prepare(mixed $value): mixed
    {
        $request = request();

        // Handle file deletion
        if ($request->boolean('_file_del_' . $this->column)) {
            $this->destroyExisting();
            return null;
        }

        if (!$value instanceof UploadedFile) {
            return $this->model ? $this->model->getAttribute($this->column) : null;
        }

        // Strict security validation: reject malicious script extensions and multi-extension bypasses (CVE-2023-24249)
        $ext = strtolower($value->getClientOriginalExtension());
        $disallowed = config('veltrix.upload.disallowed_types', [
            'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phar', 'sh', 'bash', 'exe', 'pl', 'cgi', 'bat', 'cmd', 'py', 'jsp', 'asp', 'aspx',
        ]);

        $clientName = strtolower($value->getClientOriginalName());
        $parts = explode('.', $clientName);
        foreach ($parts as $part) {
            if (in_array($part, $disallowed, true)) {
                throw new \InvalidArgumentException("Security error: Upload of executable or script files (.{$part}) is strictly prohibited.");
            }
        }

        if ($this instanceof Image) {
            $allowed = $this->allowedExtensions ?: config('veltrix.upload.allowed_image_types', ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'avif']);
        } else {
            $allowed = $this->allowedExtensions ?: config('veltrix.upload.allowed_file_types', ['pdf', 'zip', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt']);
        }

        if (!empty($allowed) && !in_array($ext, $allowed, true)) {
            throw new \InvalidArgumentException("Invalid file type [.{$ext}]. Allowed types: " . implode(', ', $allowed));
        }

        $this->destroyExisting();

        // Store file with safe random name
        $name = Str::random(32) . '.' . $ext;
        $path = $value->storeAs($this->getDirectory(), $name, $this->getDisk());

        return $path;
    }

    protected function destroyExisting(): void
    {
        if ($this->model) {
            $old = $this->model->getAttribute($this->column);
            if ($old && Storage::disk($this->getDisk())->exists($old)) {
                Storage::disk($this->getDisk())->delete($old);
            }
        }
    }

    public function renderField(): string
    {
        $current = $this->getValue();
        $previewHtml = '';

        if ($current && is_string($current)) {
            $url = Storage::disk($this->getDisk())->url($current);
            $previewHtml = "<div class=\"vx-file-preview\">
                <a href=\"{$url}\" target=\"_blank\" class=\"vx-link\">View Current File</a>
                <label class=\"vx-file-del-label\">
                    <input type=\"checkbox\" name=\"_file_del_{$this->column}\" value=\"1\">
                    Remove file
                </label>
            </div>";
        }

        return "{$previewHtml}
            <input type=\"file\" name=\"{$this->column}\" id=\"field-{$this->column}\" class=\"vx-file-input\">";
    }
}
