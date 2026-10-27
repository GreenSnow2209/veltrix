<?php

namespace Veltrix\Controllers;

trait HasResourceActions
{
    /**
     * Update the specified resource in storage.
     */
    public function update(mixed $id): mixed
    {
        return $this->form()->update($id);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(): mixed
    {
        return $this->form()->store();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(mixed $id): mixed
    {
        return $this->form()->destroy($id);
    }
}
