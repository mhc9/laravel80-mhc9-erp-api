<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\Budget;
use App\Models\BudgetPlan;
use App\Models\PlanType;

class BudgetPlanController extends Controller
{
    public function search(Request $req)
    {
        /** Get params from query string */
        $name       = $req->get('name');
        $status     = $req->get('status');

        $activities = BudgetPlan::with('type')
                        ->when($status != '', function($q) use ($status) {
                            $q->where('status', $status);
                        })
                        ->when(!empty($name), function($q) use ($name) {
                            $q->where('name', 'like', '%'.$name.'%');
                        })
                        ->orderBy('plan_no')
                        ->paginate(10);

        return $activities;
    }

    public function getAll(Request $req)
    {
        /** Get params from query string */
        $name       = $req->get('name');
        $status     = $req->get('status');

        $activities = BudgetPlan::with('type')
                        ->when($status != '', function($q) use ($status) {
                            $q->where('status', $status);
                        })
                        ->when(!empty($name), function($q) use ($name) {
                            $q->where('name', 'like', '%'.$name.'%');
                        })
                        ->orderBy('plan_no')
                        ->get();

        return $activities;
    }

    public function getById($id)
    {
        return BudgetPlan::with('type')->find($id);
    }

    public function getInitialFormData()
    {
        return [
            'types' => PlanType::all()
        ];
    }

    public function store(Request $req)
    {
        try {
            $budget = new BudgetPlan();
            $budget->name      = $req['name'];
            $budget->status    = $req['status'] ? 1 : 0;

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
            $budget = BudgetPlan::find($id);
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
            $budget = BudgetPlan::find($id);

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
