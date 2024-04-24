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

    public function getEmployee($id)
    {
        return $this->model->find($id);
    }

    public function getEmployees()
    {
        return $this->model
                    ->with('prefix','changwat','amphur','tambon','position','level')
                    ->with('memberOf','memberOf.duty','memberOf.department','memberOf.division')
                    ->get();
    }

    public function getEmployeeById($id)
    {
        return $this->getEmployee($id)
                    ->load('prefix','changwat','amphur','tambon','position','level',
                            'memberOf','memberOf.duty','memberOf.department','memberOf.division');
    }

    public function delete($id)
    {
        return $this->getEmployee($id)->delete();
    }
}