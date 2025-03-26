<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use App\Repositories\Traits\Sortable;
use App\Repositories\Traits\Relationable;
use App\Models\Loan;

class LoanRepository extends BaseRepository
{
    use Sortable, Relationable;

    /**
     *  @var $model
     */
    protected $model;

    public function __construct(Loan $model)
    {
        $this->model = $model;
    }
}