<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Services\BaseService;
use App\Repositories\BudgetExpenseRepository;
use App\Models\Employee;
use App\Models\Prefix;
use App\Models\Position;
use App\Models\Level;
use App\Models\Department;
use App\Models\Division;
use App\Models\Member;


class BudgetExpenseService extends BaseService
{
    /**
     * @var $repo
     */
    protected $repo;

    public function __construct(BudgetExpenseRepository $repo)
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
            
        ];
    }

    public function storeDetails($id, array $data)
    {
        if (!$budgetExpense = $this->repo->find($id)) {
            return null;
        }

        return $budgetExpense->details()->createMany($data);
    }
}