<?php

namespace App\Repositories;

use App\Models\Employee;

class EmployeeRepository
{
    /**
     *  @var $model
     */
    protected $model;

    public function __construct(Employee $model)
    {
        $this->model = $model;
    }
}