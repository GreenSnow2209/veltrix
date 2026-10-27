<?php

namespace Veltrix\Actions;

use Illuminate\Http\Request;
use Veltrix\Facades\Veltrix;

abstract class Action
{
    public string $name = '';

    public function name(): string
    {
        return $this->name ?: class_basename(static::class);
    }

    public function authorize(): bool
    {
        return Veltrix::user() !== null;
    }

    abstract public function handle(Request $request);

    public function response(): Response
    {
        return new Response();
    }
}
