<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use App\Repositories\Traits\Sortable;
use App\Repositories\Traits\Relationable;
use App\Models\Employee;

class EmployeeRepository extends BaseRepository
{
    use Sortable, Relationable;

    /**
     *  @var $model
     */
    protected $model;

    public function __construct(Employee $model)
    {
        $this->model = $model;
    }
}