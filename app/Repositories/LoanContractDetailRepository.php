<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use App\Repositories\Traits\Sortable;
use App\Repositories\Traits\Relationable;
use App\Models\LoanContractDetail;

class LoanContractDetailRepository extends BaseRepository
{
    use Sortable, Relationable;

    /**
     *  @var $model
     */
    protected $model;

    public function __construct(LoanContractDetail $model)
    {
        $this->model = $model;
    }
}