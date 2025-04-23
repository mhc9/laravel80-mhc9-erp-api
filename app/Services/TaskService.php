<?php

namespace App\Services;

use App\Services\BaseService;
use App\Repositories\TaskRepository;
use App\Models\Task;
use App\Models\TaskType;
use App\Models\TaskGroup;
use App\Models\TaskAsset;
use App\Models\TaskCause;
use App\Models\Employee;

class TaskService extends BaseService
{
    /**
     * @var $repo
     */
    protected $repo;

    public function __construct(TaskRepository $repo)
    {
        $this->repo = $repo;

        // $this->repo->setSortBy('date_in');
        // $this->repo->setSortOrder('desc');

        $this->repo->setRelations([
            'group','group.type','assets','assets.asset','assets.asset.category','assets.asset.brand',
            'reporter','reporter.prefix','reporter.position','reporter.level','cause',
            'handler','handler.prefix','handler.position','handler.level'
        ]);
    }

    private function getEmployeeList($fname, $lname)
    {
        return Employee::where('firstname', 'like', '%'.$fname.'%')
                        ->when(!empty($lname), function($q) use ($lname) {
                            $q->where('lastname', 'like', '%'.$lname.'%');
                        })
                        ->pluck('id');
    }

    private function getGroupListOfType($type)
    {
        return TaskGroup::where('task_type_id', $type)->pluck('id');
    }

    public function search(array $params, $all = false, $perPage = 10)
    {
        $reporterList = $this->getEmployeeList($reporter, '');
        $groupList = $this->getGroupListOfType($type);

        $collections = $this->repo
                            ->getModelwithRelations()
                            ->when(!empty($params['reporter']), function($q) use ($reporterList) {
                                $q->whereIn('reporter_id', $reporterList);
                            })
                            ->when(!empty($params['type']), function($q) use ($groupList) {
                                $q->whereIn('task_group_id', $groupList);
                            })
                            // ->when(!empty($group), function($q) use ($group) {
                            //     $q->where('task_group_id', $group);
                            // })
                            ->when(!empty($params['status']), function($q) use ($params) {
                                $q->where('status', $params['status']);
                            })
                            ->orderBy('task_date', 'desc')
                            ->orderBy('task_time', 'desc')

        return $all ?  $collections->get() : $collections->paginate($perPage);
    }

    public function getFormData()
    {
        $handleTypes = [
            ['id' => 1, 'name'  => 'ซ่อม'],
            ['id' => 2, 'name'  => 'บำรุงรักษา'],
            ['id' => 3, 'name'  => 'สร้าง'],
            ['id' => 4, 'name'  => 'แก้ไข'],
        ];

        $statuses = [
            ['id' => 1, 'name'  => 'รอดำเนินการ'],
            ['id' => 2, 'name'  => 'เสร็จแล้ว'],
            ['id' => 3, 'name'  => 'เสร็จ (ชั่วคราว)'],
            ['id' => 4, 'name'  => 'ส่งซ่อม'],
            ['id' => 9, 'name'  => 'ยกเลิก'],
        ];

        return [
            'types'         => TaskType::all(),
            'groups'        => TaskGroup::all(),
            'causes'        => TaskCause::all(),
            'handleTypes'   => $handleTypes,
            'statuses'      => $statuses,
            'employees'     => Employee::whereIn('status', [1,2])->get(),
        ];
    }
}