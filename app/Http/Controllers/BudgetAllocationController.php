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

        $allocations = BudgetAllocation::with('agency','budget','budget.type','budget.activity')
                                        ->with('budget.activity.project','budget.activity.project.plan')
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
        $year       = $req->get('year');
        $status     = $req->get('status');

        $allocations = BudgetAllocation::with('agency','budget','budget.type','budget.activity')
                                        ->with('budget.activity.project','budget.activity.project.plan')
                                        ->when(!empty($year), function($q) use ($year) {
                                            $q->whereHas('budget.activity', function($sq) use ($year) {
                                                $sq->where('year', $year);
                                            });
                                        })
                                        // ->when(!empty($project), function($q) use ($project) {
                                        //     $q->where('project_id', $project);
                                        // })
                                        // ->when(!empty($plan), function($q) use ($plan) {
                                        //     $q->whereHas('project.plan', function($sq) use ($plan) {
                                        //         $sq->where('plan_id', $plan);
                                        //     });
                                        // })
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
        $allocations = BudgetAllocation::with('agency','budget','budget.type','budget.activity')
                                        ->with('budget.activity.project','budget.activity.project.plan')
                                        ->whereHas('budget', function($q) use ($budgetId) {
                                            $q->where('id', $budgetId);
                                        })
                                        ->get();

        return $allocations;
    }

    public function getById($id)
    {
        return BudgetAllocation::with('agency','budget','budget.type','budget.activity')
                                ->with('budget.activity.project','budget.activity.project.plan')
                                ->find($id);
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
            $budget = Budget::find($req['budget_id']);

            $allocation = new BudgetAllocation();
            $allocation->budget_id      = $req['budget_id'];
            $allocation->doc_no         = $req['doc_no'];
            $allocation->doc_date       = $req['doc_date'];
            $allocation->allocate_type_id = $req['allocate_type_id'];
            $allocation->agency_id      = $req['agency_id'];
            $allocation->description    = $req['description'];
            $allocation->total          = $req['total'];
            $allocation->latest_budget  = $budget->total;
            $allocation->status         = 1;

            if($allocation->save()) {
                /** อัพเดตยอดเงินในตาราง budgets ด้วย */
                $budget->latest_total   = $budget->total;
                $budget->total          = $req['allocate_type_id'] == '1' ? $budget->total + $req['total'] : $budget->total - $req['total'];
                $budget->save();

                /** อัพเดต status รายการจัดสรรงบของรหัส budget_id เดียวกันให้เป็น 0 */
                BudgetAllocation::where('budget_id', $budget->id)->whereNotIn('id', [$allocation->id])->update(['status' => 0]);

                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'allocation'  => $allocation->load('agency','budget','budget.type','budget.activity',
                                                        'budget.activity.project','budget.activity.project.plan')
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
            $budget = Budget::find($req['budget_id']);

            $allocation = BudgetAllocation::find($id);
            $allocation->budget_id      = $req['budget_id'];
            $allocation->doc_no         = $req['doc_no'];
            $allocation->doc_date       = $req['doc_date'];
            $allocation->allocate_type_id = $req['allocate_type_id'];
            $allocation->agency_id      = $req['agency_id'];
            $allocation->description    = $req['description'];
            if ($allocation->status == 1) {
                $allocation->total = $req['total'];
            }

            if($allocation->save()) {
                /** อัพเดตยอดเงินในตาราง budgets ด้วย */
                if ($allocation->status == 1) {
                    $budget->total = $req['allocate_type_id'] == '1' ? $allocation->latest_budget + $req['total'] : $allocation->latest_budget - $req['total'];
                    $budget->save();
                }

                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'allocation'  => $allocation->load('agency','budget','budget.type','budget.activity',
                                                        'budget.activity.project','budget.activity.project.plan')
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
            $allocationToDelete = $allocation;

            if($allocation->delete()) {
                /** อัพเดตยอดเงินในตาราง budgets ด้วย */
                $budget = Budget::find($allocationToDelete->budget_id);
                $budget->total          = $budget->latest_total;
                $budget->latest_total   = $allocationToDelete->latest_budget;
                $budget->save();

                /** อัพเดต status รายการจัดสรรงบของรหัส budget_id เดียวกันให้เป็น 0 */
                $allocationToUpdate = BudgetAllocation::where('budget_id', $budget->id)->orderBy('doc_date', 'DESC')->first();
                if ($allocationToUpdate) {
                    $allocationToUpdate->status = 1;
                    $allocationToUpdate->save();
                }

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
