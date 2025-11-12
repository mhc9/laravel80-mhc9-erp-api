<?php

namespace App\Services;

use App\Services\BaseService;
use App\Repositories\AttendanceRepository;
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

class AttendanceService extends BaseService
{
    use SaveImage;

    /**
     * @var $repo
     */
    protected $repo;

    public function __construct(AttendanceRepository $repo)
    {
        $this->repo = $repo;

        // $this->repo->setSortBy('doc_date');
        // $this->repo->setSortOrder('desc');

        $this->repo->setRelations([
            'employee','employee.prefix','employee.changwat','employee.amphur','employee.tambon','employee.position','employee.level',
            'employee.memberOf','employee.memberOf.duty','employee.memberOf.department','employee.memberOf.division'
        ]);
    }

    public function search(array $params, $all = false, $perPage = 10)
    {
        $memberLists = [];
        if (!empty($params['department'])) {
            $memberLists = Member::where('department_id', $params['department'])->pluck('employee_id');
        }

        $collections = $this->repo->getModelWithRelations()
                            ->when(!empty($params['position']), function($q) use ($params) {
                                $q->where('position_id', $params['position']);
                            })
                            ->when(!empty($params['level']), function($q) use ($params) {
                                $q->where('level_id', $params['level']);
                            })
                            ->when(!empty($params['name']), function($q) use ($params) {
                                $q->where('firstname', 'like', '%'.$params['name'].'%');
                            })
                            ->when(!empty($params['department']), function($q) use ($memberLists) {
                                $q->whereIn('id', $memberLists);
                            })
                            ->when(!empty($params['status']), function($q) use ($params) {
                                $q->where('status', $params['status']);
                            });

        return $all ?  $collections->get() : $collections->paginate($perPage);
    }

    public function getFormData()
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
        $employee = $this->repo->findOne($id);
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