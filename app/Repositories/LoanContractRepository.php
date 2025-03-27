<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use App\Repositories\Traits\Sortable;
use App\Repositories\Traits\Relationable;
use App\Models\LoanContract;

class LoanContractRepository extends BaseRepository
{
    use Sortable, Relationable;

    /**
     *  @var $model
     */
    protected $model;

    public function __construct(LoanContract $model)
    {
        $this->model = $model;
    }
}