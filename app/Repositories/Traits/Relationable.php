<?php

namespace App\Repositories\Traits;

trait Relationable
{
    public $relations = [];

    public function setRelations($relations = null)
    {
        $this->relations = $relations;
    }
}