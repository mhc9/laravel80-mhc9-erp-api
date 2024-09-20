<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\Budget;
use App\Models\BudgetType;
use App\Models\BudgetPlan;
use App\Models\BudgetProject;
use App\Models\BudgetTypeDetail;

class BudgetController extends Controller
{
    public function search(Request $req)
    {
        /** Get params from query string */
        $project    = $req->get('project');
        $plan       = $req->get('plan');
        $name       = $req->get('name');
        $year       = $req->get('year');
        $status     = $req->get('status');
        $limit      = $req->filled('limit') ? $req->get('limit') : 10;

        $budgets = Budget::select('budgets.*')
                    ->with('project','project.plan','details','details.type')
                    ->leftJoin('budget_projects','budgets.project_id','=','budget_projects.id')
                    ->leftJoin('budget_plans','budget_projects.plan_id','=','budget_plans.id')
                    ->when(!empty($project), function($q) use ($project) {
                        $q->where('project_id', $project);
                    })
                    ->when(!empty($plan), function($q) use ($plan) {
                        $q->whereHas('project.plan', function($sq) use ($plan) {
                            $sq->where('plan_id', $plan);
                        });
                    })
                    ->when(!empty($year), function($q) use ($year) {
                        $q->where('budgets.year', $year);
                    })
                    ->when(!empty($name), function($q) use ($name) {
                        $q->where('budgets.name', 'like', '%'.$name.'%');
                    })
                    // ->when($status != '', function($q) use ($status) {
                    //     $q->where('status', $status);
                    // })
                    ->orderBy('budget_plans.plan_no')
                    ->orderBy('budget_projects.name')
                    ->orderBy('budgets.budget_no')
                    ->paginate($limit);

        return $budgets;
    }

    public function getAll(Request $req)
    {
        /** Get params from query string */
        $project    = $req->get('project');
        $plan       = $req->get('plan');
        $name       = $req->get('name');
        $status     = $req->get('status');

        $activities = Budget::with('project','project.plan','details','details.type')
                    ->when(!empty($project), function($q) use ($project) {
                        $q->where('project_id', $project);
                    })
                    ->when(!empty($plan), function($q) use ($plan) {
                        $q->whereHas('project.plan', function($sq) use ($plan) {
                            $sq->where('plan_id', $plan);
                        });
                    })
                    // ->when($status != '', function($q) use ($status) {
                    //     $q->where('status', $status);
                    // })
                    // ->when(!empty($name), function($q) use ($name) {
                    //     $q->where('name', 'like', '%'.$name.'%');
                    // })
                    ->get();

        return $activities;
    }

    public function getById($id)
    {
        return Budget::with('project','project.plan','details','details.type')->find($id);
    }

    public function getInitialFormData()
    {
        return [
            'plans'     => BudgetPlan::orderBy('plan_type_id')->orderBy('plan_no')->get(),
            'projects'  => BudgetProject::all(),
            'types'     => BudgetType::all(),
        ];
    }

    public function store(Request $req)
    {
        try {
            $budget = new Budget();
            $budget->name       = $req['name'];
            $budget->budget_no  = $req['budget_no'];
            $budget->year       = $req['year'];
            $budget->project_id = $req['project_id'];
            $budget->gfmis_id   = $req['gfmis_id'];
            $budget->status     = 1;

            if($budget->save()) {
                foreach($req['budget_types'] as $type) {
                    $newBudgetType = new BudgetTypeDetail();
                    $newBudgetType->budget_id       = $budget->id;
                    $newBudgetType->budget_type_id  = $type['budget_type_id'];
                    $newBudgetType->total           = $type['total'];
                    $newBudgetType->save();
                }

                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'budget'    => $budget->load('project','project.plan','details','details.type')
                ];
            } else {
                return [
                    'status'    => 0,
                    'message'   => 'Something went wrong!!'
                ];
            }
        } catch (\Exception $ex) {
            return [
                'status'    => 0,
                'message'   => $ex->getMessage()
            ];
        }
    }

    public function update(Request $req, $id)
    {
        try {
            $budget = Budget::find($id);
            $budget->name       = $req['name'];
            $budget->budget_no  = $req['budget_no'];
            $budget->year       = $req['year'];
            $budget->project_id = $req['project_id'];
            $budget->gfmis_id   = $req['gfmis_id'];

            if($budget->save()) {
                foreach($req['budget_types'] as $item) {
                    if (empty($item['budget_id'])) {
                        $newBudgetType = new BudgetTypeDetail();
                        $newBudgetType->budget_id       = $budget->id;
                        $newBudgetType->budget_type_id  = $item['budget_type_id'];
                        $newBudgetType->total           = $item['total'];
                        $newBudgetType->save();
                    } else {
                        /** ถ้าเป็นรายการเดิมให้ตรวจสอบว่ามี property flag updated หรือไม่ */
                        if (array_key_exists('updated', $item) && $item['updated']) {
                            $updated = BudgetTypeDetail::find($item['id']);
                            $updated->budget_type_id  = $item['budget_type_id'];
                            $updated->total           = $item['total'];
                            $updated->save();
                        }

                        /** ถ้าเป็นรายการเดิมให้ตรวจสอบว่ามี property flag removed หรือไม่ */
                        if (array_key_exists('removed', $item) && $item['removed']) {
                            BudgetTypeDetail::find($item['id'])->delete();
                        }
                    }
                }

                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'budget'    => $budget->load('project','project.plan','details','details.type')
                ];
            } else {
                return [
                    'status'    => 0,
                    'message'   => 'Something went wrong!!'
                ];
            }
        } catch (\Exception $ex) {
            return [
                'status'    => 0,
                'message'   => $ex->getMessage()
            ];
        }
    }

    public function destroy(Request $req, $id)
    {
        try {
            $budget = Budget::find($id);
            /** Store deleted budget's id to variable */
            $deletedId = $budget->id;

            if($budget->delete()) {
                /** ลบข้อมูลในตาราง budget_type_details ด้วย */
                BudgetTypeDetail::where('budget_id', $deletedId)->delete();

                return [
                    'status'    => 1,
                    'message'   => 'Deleting successfully!!',
                    'id'        => $id
                ];
            } else {
                return [
                    'status'    => 0,
                    'message'   => 'Something went wrong!!'
                ];
            }
        } catch (\Exception $ex) {
            return [
                'status'    => 0,
                'message'   => $ex->getMessage()
            ];
        }
    }

    public function toggle(Request $req, $id)
    {
        try {
            $budget = Budget::find($id);
            $budget->status = $req['status'];

            if($budget->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Updating status successfully!!',
                    'budget'    => $budget->load('project','project.plan','details','details.type')
                ];
            } else {
                return [
                    'status'    => 0,
                    'message'   => 'Something went wrong!!'
                ];
            }
        } catch (\Exception $ex) {
            return [
                'status'    => 0,
                'message'   => $ex->getMessage()
            ];
        }
    }
}
