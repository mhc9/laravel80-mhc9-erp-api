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
        $status     = $req->get('status');

        $activities = BudgetProject::select('budget_projects.*')->with('plan')
                        ->leftJoin('budget_plans', 'budget_projects.plan_id', '=', 'budget_plans.id')
                        ->when(!empty($plan), function($q) use ($plan) {
                            $sq->where('plan_id', $plan);
                        })
                        ->when(!empty($name), function($q) use ($name) {
                            $query->where('name', 'like', '%'.$name.'%');
                        })
                        ->when($status != '', function($q) use ($status) {
                            $q->where('status', $status);
                        })
                        ->orderBy('budget_plans.plan_no')
                        ->paginate(10);

        return $activities;
    }

    public function getAll(Request $req)
    {
        /** Get params from query string */
        $plan       = $req->get('plan');
        $name       = $req->get('name');
        $status     = $req->get('status');

        $activities = BudgetProject::with('plan')
                        ->when(!empty($plan), function($q) use ($plan) {
                            $sq->where('plan_id', $plan);
                        })
                        ->when(!empty($name), function($q) use ($name) {
                            $query->where('name', 'like', '%'.$name.'%');
                        })
                        ->when($status != '', function($q) use ($status) {
                            $q->where('status', $status);
                        })
                        ->get();

        return $activities;
    }

    public function getById($id)
    {
        return BudgetProject::find($id);
    }

    public function store(Request $req)
    {
        try {
            $budget = new BudgetProject();
            $budget->name      = $req['name'];
            $budget->status    = $req['status'] ? 1 : 0;

            if($budget->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'Budget'    => $budget
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
            $budget = BudgetProject::find($id);
            $budget->name     = $req['name'];
            $budget->status   = $req['status'] ? 1 : 0;

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
