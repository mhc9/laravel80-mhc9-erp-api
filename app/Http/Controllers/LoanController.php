<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\Loan;
use App\Models\LoanDetail;
use App\Models\Expense;
use App\Models\Department;

class LoanController extends Controller
{
    public function search(Request $req)
    {
        /** Get params from query string */
        $year       = $req->get('year');
        $plan       = $req->get('plan');
        $name       = $req->get('name');
        $status     = $req->get('status');

        $activities = Loan::with('project','budget','budget.project','budget.project.plan')
                        ->with('department','details','details.expense')
                        ->with('employee','employee.prefix','employee.position','employee.level')
                        ->when(!empty($year), function($q) use ($year) {
                            $q->where('year', $year);
                        })
                        // ->when(!empty($plan), function($q) use ($plan) {
                        //     $q->whereHas('project.plan', function($sq) use ($plan) {
                        //         $sq->where('plan_id', $plan);
                        //     });
                        // })
                        // ->when($status != '', function($q) use ($status) {
                        //     $q->where('status', $status);
                        // })
                        // ->when(!empty($name), function($q) use ($name) {
                        //     $q->where(function($query) use ($name) {
                        //         $query->where('item_name', 'like', '%'.$name.'%');
                        //         $query->orWhere('en_name', 'like', '%'.$name.'%');
                        //     });
                        // })
                        ->paginate(10);

        return $activities;
    }

    public function getAll(Request $req)
    {
        /** Get params from query string */
        $project    = $req->get('project');
        $plan       = $req->get('plan');
        $name       = $req->get('name');
        $status     = $req->get('status');

        $activities = Loan::with('project','budget','budget.project','budget.project.plan','expenses')
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
        return Loan::find($id);
    }

    public function getInitialFormData()
    {
        return [
            'departments'   => Department::all(),
            'expenses'      => Expense::all(),
        ];
    }

    public function store(Request $req)
    {
        try {
            $loan = new Loan();
            $loan->doc_no           = $req['doc_no'];
            $loan->doc_date         = $req['doc_date'];
            $loan->loan_type_id     = $req['loan_type_id'];
            $loan->money_type_id    = $req['money_type_id'];
            $loan->budget_id        = $req['budget_id'];
            $loan->project_id       = $req['project_id'];
            $loan->department_id    = $req['department_id'];
            $loan->employee_id      = $req['employee_id'];
            $loan->net_total        = currencyToNumber($req['net_total']);
            // $loan->remark           = $req['remark'];
            $loan->status           = $req['status'] ? 1 : 0;

            if($loan->save()) {
                foreach($req['items'] as $item) {
                    $detail = new LoanDetail();
                    $detail->loan_id        = $loan->id;
                    $detail->expense_id     = $item['expense_id'];
                    $detail->description    = $item['description'];
                    $detail->total          = currencyToNumber($item['total']);
                    $detail->save();
                }

                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'loan'  => $loan
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
            $budget = Loan::find($id);
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
            $budget = Loan::find($id);

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
