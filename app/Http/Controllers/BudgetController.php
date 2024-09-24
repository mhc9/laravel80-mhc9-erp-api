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
use App\Models\BudgetActivity;

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

        $budgets = Budget::with('activity','activity.project','activity.project.plan','type')
                    ->when(!empty($plan), function($q) use ($plan) {
                        $q->whereHas('activity.project.plan', function($sq) use ($plan) {
                            $sq->where('plan_id', $plan);
                        });
                    })
                    ->when(!empty($project), function($q) use ($project) {
                        $q->whereHas('activity', function($sq) use ($project) {
                            $sq->where('project_id', $project);
                        });
                    })
                    ->when(!empty($name), function($q) use ($name) {
                        $q->whereHas('activity', function($sq) use ($name) {
                            $sq->where('name', 'like', '%'.$name.'%');
                        });
                    })
                    ->when(!empty($year), function($q) use ($year) {
                        $q->whereHas('activity', function($sq) use ($year) {
                            $sq->where('year', $year);
                        });
                    })
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

        $activities = Budget::with('activity','type')
                    // ->when(!empty($project), function($q) use ($project) {
                    //     $q->where('project_id', $project);
                    // })
                    // ->when(!empty($plan), function($q) use ($plan) {
                    //     $q->whereHas('project.plan', function($sq) use ($plan) {
                    //         $sq->where('plan_id', $plan);
                    //     });
                    // })
                    ->get();

        return $activities;
    }

    public function getById($id)
    {
        return Budget::with('activity','type')->find($id);
    }

    public function getInitialFormData(Request $req)
    {
        $year = $req->get('year');

        return [
            'types'     => BudgetType::all(),
            'plans'     => BudgetPlan::where('year', $year)->orderBy('plan_no')->get(),
            'projects'  => BudgetProject::where('year', $year)->get(),
        ];
    }

    public function store(Request $req)
    {
        try {
            $budget = new Budget();
            $budget->activity_id    = $req['activity_id'];
            $budget->budget_type_id = $req['budget_type_id'];
            $budget->total          = $req['total'];

            if($budget->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'budget'    => $budget->load('activity','type')
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
            $budget->activity_id    = $req['activity_id'];
            $budget->budget_type_id = $req['budget_type_id'];
            $budget->total          = $req['total'];

            if($budget->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'budget'    => $budget->load('activity','type')
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
