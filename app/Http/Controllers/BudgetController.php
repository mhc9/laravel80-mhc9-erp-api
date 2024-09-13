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
                    ->with('type','project','project.plan')
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

        $activities = Budget::with('type','project','project.plan')
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
        return Budget::find($id);
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
            $budget->type_id    = $req['type_id'];
            $budget->project_id = $req['project_id'];
            $budget->gfmis_id   = $req['gfmis_id'];
            $budget->year       = $req['year'];
            $budget->status     = 1;

            if($budget->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'Budget'  => $budget
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
            $budget->type_id    = $req['type_id'];
            $budget->project_id = $req['project_id'];
            $budget->gfmis_id   = $req['gfmis_id'];
            $budget->year       = $req['year'];
            $budget->status     = $req['status'] ? 1 : 0;

            if($budget->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'Budget'  => $budget
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

            if($budget->delete()) {
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
}
