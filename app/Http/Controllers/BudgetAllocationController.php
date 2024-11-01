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
use App\Models\BudgetAllocation;

class BudgetAllocationController extends Controller
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

        $allocations = BudgetAllocation::with('budget','budget.type','budget.activity') //'project','project.plan',
                        // ->when(!empty($project), function($q) use ($project) {
                        //     $q->where('project_id', $project);
                        // })
                        // ->when(!empty($plan), function($q) use ($plan) {
                        //     $q->whereHas('project.plan', function($sq) use ($plan) {
                        //         $sq->where('plan_id', $plan);
                        //     });
                        // })
                        // ->when(!empty($year), function($q) use ($year) {
                        //     $q->where('budget_activities.year', $year);
                        // })
                        // ->when(!empty($name), function($q) use ($name) {
                        //     $q->where('budget_activities.name', 'like', '%'.$name.'%');
                        // })
                        // ->when($status != '', function($q) use ($status) {
                        //     $q->where('status', $status);
                        // })
                        ->paginate($limit);

        return $allocations;
    }

    public function getAll(Request $req)
    {
        /** Get params from query string */
        $project    = $req->get('project');
        $plan       = $req->get('plan');
        $name       = $req->get('name');
        $status     = $req->get('status');

        $allocations = BudgetAllocation::with('budget','budget.type','budget.activity')
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

        return $allocations;
    }

    public function getByBudget(Request $req, $budgetId)
    {
        $allocations = BudgetAllocation::with('budget','budget.type','budget.activity')
                        ->whereHas('budget', function($q) use ($budgetId) {
                            $q->where('id', $budgetId);
                        })
                        ->get();

        return $allocations;
    }

    public function getById($id)
    {
        return BudgetAllocation::with('budget','budget.type','budget.activity')->find($id);
    }

    public function getInitialFormData(Request $req)
    {
        $year = $req->get('year');

        return [
            'plans'     => BudgetPlan::where('year', $year)->orderBy('plan_type_id')->orderBy('plan_no')->get(),
            'projects'  => BudgetProject::where('year', $year)->get(),
            'types'     => BudgetType::all(),
        ];
    }

    public function store(Request $req)
    {
        try {
            $allocation = new BudgetAllocation();
            $allocation->budget_id    = $req['budget_id'];
            $allocation->doc_no       = $req['doc_no'];
            $allocation->doc_date     = $req['doc_date'];
            $allocation->total        = $req['total'];
            $allocation->description  = $req['description'];

            if($allocation->save()) {
                /** อัพเดตยอดเงินในตาราง budgets ด้วย */
                // $budget = Budget::find($req['budget_id']);
                // $budget->total = $budget->total + $req['total'];
                // $budget->save();

                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'allocation'  => $allocation->load('budget','budget.type','budget.activity')
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
            $allocation = BudgetAllocation::find($id);
            $allocation->budget_id    = $req['budget_id'];
            $allocation->doc_no       = $req['doc_no'];
            $allocation->doc_date     = $req['doc_date'];
            $allocation->total        = $req['total'];
            $allocation->description  = $req['description'];

            if($allocation->save()) {
                /** อัพเดตยอดเงินในตาราง budgets ด้วย */

                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'allocation'  => $allocation->load('budget','budget.type','budget.activity')
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
            $allocation = BudgetAllocation::find($id);
            /** Store deleted budget's id to variable */
            $deletedId = $allocation->budget_id;

            if($allocation->delete()) {
                /** อัพเดตยอดเงินในตาราง budgets ด้วย */
                // $newBudget = Budget::find(deletedId);
                // $newBudget->total = $type['total'];
                // $newBudget->save();

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
