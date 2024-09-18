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
        $year       = $req->get('year');
        $status     = $req->get('status');

        $activities = BudgetPlan::with('type')
                        ->when(!empty($year), function($q) use ($year) {
                            $q->where('year', $year);
                        })
                        ->when(!empty($name), function($q) use ($name) {
                            $q->where('name', 'like', '%'.$name.'%');
                        })
                        ->when($status != '', function($q) use ($status) {
                            $q->where('status', $status);
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
            $plan = new BudgetPlan();
            $plan->plan_no      = $req['plan_no'];
            $plan->name         = $req['name'];
            $plan->year         = $req['year'];
            $plan->plan_type_id = $req['plan_type_id'];
            $plan->status       = $req['status'] ? 1 : 0;

            if($plan->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'plan'  => $plan
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
            $plan = BudgetPlan::find($id);
            $plan->plan_no      = $req['plan_no'];
            $plan->name         = $req['name'];
            $plan->year         = $req['year'];
            $plan->plan_type_id = $req['plan_type_id'];
            $plan->status       = $req['status'] ? 1 : 0;

            if($plan->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'plan'      => $plan
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
            $plan = BudgetPlan::find($id);

            if($plan->delete()) {
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
