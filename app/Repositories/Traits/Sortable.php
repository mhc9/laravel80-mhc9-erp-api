<?php

namespace App\Repositories\Traits;

trait Sortable
{
    public $sortBy = 'created_at';

    public $sortOrder = 'asc';

    public function setSortBy($sortBy = 'created_at')
    {
        $this->sortBy = $sortBy;
    }

    public function setSortOrder($sortOrder = 'asc')
    {
        $this->sortOrder = $sortOrder;
    }
}