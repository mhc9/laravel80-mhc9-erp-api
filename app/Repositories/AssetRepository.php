<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use App\Repositories\Traits\Sortable;
use App\Repositories\Traits\Relationable;
use App\Models\Asset;

class AssetRepository extends BaseRepository
{
    use Sortable, Relationable;

    /**
     *  @var $model
     */
    protected $model;

    public function __construct(Asset $model)
    {
        $this->model = $model;
    }
}