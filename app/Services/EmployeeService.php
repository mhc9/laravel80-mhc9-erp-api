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
     * @var $repo
     */
    protected $repo;

    public function __construct(EmployeeRepository $repo)
    {
        $this->repo = $repo;

        // $this->repo->setSortBy('doc_date');
        // $this->repo->setSortOrder('desc');

        $this->repo->setRelations([
            'prefix','changwat','amphur','tambon','position','level',
            'memberOf','memberOf.duty','memberOf.department','memberOf.division'
        ]);
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

    public function updateImage($id, $image)
    {
        $employee = $this->repo->getEmployee($id);
        $destPath = 'employees';

        /** Remove old uploaded file */
        if (\File::exists($destPath . $employee->avatar_url)) {
            \File::delete($destPath . $employee->avatar_url);
        }

        $employee->avatar_url = $this->saveImage($image, $destPath);

        if (!empty($employee->avatar_url) && $employee->save()) {
            return $employee;
        } else {
            return false;
        }
    }
}