<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\Budget;
use App\Models\BudgetPlan;
use App\Models\BudgetProject;

class BudgetProjectController extends Controller
{
    public function search(Request $req)
    {
        /** Get params from query string */
        $plan       = $req->get('plan');
        $name       = $req->get('name');
        $year       = $req->get('year');
        $status     = $req->get('status');

        $projects = BudgetProject::select('budget_projects.*')->with('plan')
                        ->leftJoin('budget_plans', 'budget_projects.plan_id', '=', 'budget_plans.id')
                        ->when(!empty($plan), function($q) use ($plan) {
                            $q->where('plan_id', $plan);
                        })
                        ->when(!empty($year), function($q) use ($year) {
                            $q->where('budget_projects.year', $year);
                        })
                        ->when(!empty($name), function($q) use ($name) {
                            $q->where('name', 'like', '%'.$name.'%');
                        })
                        ->when($status != '', function($q) use ($status) {
                            $q->where('status', $status);
                        })
                        ->orderBy('budget_plans.plan_no')
                        ->orderBy('budget_projects.name')
                        ->paginate(10);

        return $projects;
    }

    public function getAll(Request $req)
    {
        /** Get params from query string */
        $plan       = $req->get('plan');
        $name       = $req->get('name');
        $year       = $req->get('year');
        $status     = $req->get('status');

        $projects = BudgetProject::with('plan')
                        ->when(!empty($plan), function($q) use ($plan) {
                            $sq->where('plan_id', $plan);
                        })
                        ->when(!empty($year), function($q) use ($year) {
                            $q->where('year', $year);
                        })
                        ->when(!empty($name), function($q) use ($name) {
                            $query->where('name', 'like', '%'.$name.'%');
                        })
                        ->when($status != '', function($q) use ($status) {
                            $q->where('status', $status);
                        })
                        ->get();

        return $projects;
    }

    public function getById($id)
    {
        return BudgetProject::find($id);
    }

    public function getInitialFormData(Request $req)
    {
        $year = $req->get('year');

        return [
            'plans' => BudgetPlan::where('year', $year)->orderBy('plan_no')->get()
        ];
    }

    public function store(Request $req)
    {
        try {
            $project = new BudgetProject();
            $project->name      = $req['name'];
            $project->year      = $req['year'];
            $project->project_type_id = $req['project_type_id'];
            $project->plan_id   = $req['plan_id'];
            $project->gfmis_id  = $req['gfmis_id'];
            $project->status    = $req['status'] ? 1 : 0;

            if($project->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'project'   => $project->load('plan')
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
            $project = BudgetProject::find($id);
            $project->name      = $req['name'];
            $project->year      = $req['year'];
            $project->project_type_id = $req['project_type_id'];
            $project->plan_id   = $req['plan_id'];
            $project->gfmis_id  = $req['gfmis_id'];
            $project->status    = $req['status'] ? 1 : 0;

            if($project->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'project'   => $project->load('plan')
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
            $budget = BudgetProject::find($id);

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
