<?php

namespace Veltrix\Actions;

use Illuminate\Database\Eloquent\Model;

abstract class RowAction extends Action
{
    protected ?Model $row = null;

    public function setRow(Model $row): static
    {
        $this->row = $row;
        return $this;
    }

    public function getRow(): ?Model
    {
        return $this->row;
    }
}
