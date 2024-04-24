<?php

namespace App\Services;

use App\Repositories\EmployeeRepository;
use App\Models\Employee;
use App\Models\Prefix;
use App\Models\Position;
use App\Models\Level;
use App\Models\Department;
use App\Models\Division;
use App\Models\Member;
use App\Models\Changwat;
use App\Models\Amphur;
use App\Models\Tambon;
use App\Traits\SaveImage;

class EmployeeService
{
    use SaveImage;

    /**
     * @var $employeeRepo
     */
    protected $employeeRepo;

    public function __construct(EmployeeRepository $employeeRepo)
    {
        $this->employeeRepo = $employeeRepo;
    }

    public function find($id)
    {
        return $this->employeeRepo->getEmployee($id);
    }

    public function findAll($params = [])
    {
        return $this->employeeRepo->getEmployees();
    }

    public function findById($id)
    {
        return $this->employeeRepo->getEmployeeById($id);
    }

    public function initForm()
    {
        return [
            'prefixes'      => Prefix::all(),
            'positions'     => Position::all(),
            'levels'        => Level::all(),
            'departments'   => Department::with('divisions')->get(),
            'divisions'     => Division::all(),
            'changewats'    => Changwat::all(),
            'amphurs'       => Amphur::all(),
            'tambons'       => Tambon::all()
        ];
    }

    public function delete($id)
    {
        return $this->employeeRepo->delete($id);
    }

    public function updateImage($id, $image)
    {
        $employee = $this->employeeRepo->getEmployee($id);
        $destPath = 'employees';

        /** Remove old uploaded file */
        if (\File::exists($destPath . $employee->img_url)) {
            \File::delete($destPath . $employee->img_url);
        }

        $employee->img_url = $this->saveImage($image, $destPath);

        if (!empty($employee->img_url) && $employee->save()) {
            return $employee;
        } else {
            return false;
        }
    }
}