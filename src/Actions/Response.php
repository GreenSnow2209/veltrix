<?php

namespace Veltrix\Actions;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class Response implements Arrayable, Jsonable
{
    protected bool $status = true;
    protected string $message = '';
    protected array $data = [];

    public function success(string $message = ''): static
    {
        $this->status = true;
        $this->message = $message;
        return $this;
    }

    public function error(string $message = ''): static
    {
        $this->status = false;
        $this->message = $message;
        return $this;
    }

    public function refresh(): static
    {
        $this->data['refresh'] = true;
        return $this;
    }

    public function redirect(string $url): static
    {
        $this->data['redirect'] = $url;
        return $this;
    }

    public function toArray(): array
    {
        return array_merge([
            'status'  => $this->status,
            'message' => $this->message,
        ], $this->data);
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
