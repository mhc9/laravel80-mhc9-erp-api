<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\Loan;
use App\Models\LoanDetail;
use App\Models\LoanBudget;
use App\Models\ProjectCourse;
use App\Models\Expense;
use App\Models\Department;
use App\Models\Budget;

class LoanController extends Controller
{
    public function search(Request $req)
    {
        /** Get params from query string */
        $year       = $req->get('year');
        $status     = $req->get('status');

        $loans = Loan::with('details','details.expense','department')
                        ->with('employee','employee.prefix','employee.position','employee.level')
                        ->with('budgets','budgets.budget','budgets.budget.project','budgets.budget.project.plan')
                        ->with('courses','courses.place','courses.place.changwat')
                        ->when(!empty($year), function($q) use ($year) {
                            $q->where('year', $year);
                        })
                        ->when(!empty($status), function($q) use ($status) {
                            $q->where('status', $status);
                        })
                        ->paginate(10);

        return $loans;
    }

    public function getAll(Request $req)
    {
        /** Get params from query string */
        $project    = $req->get('project');
        $plan       = $req->get('plan');
        $name       = $req->get('name');
        $status     = $req->get('status');

        $loans = Loan::with('details','details.expense','department')
                        ->with('employee','employee.prefix','employee.position','employee.level')
                        ->with('budgets','budgets.budget','budgets.budget.project','budgets.budget.project.plan')
                        ->with('courses','courses.place','courses.place.changwat')
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

        return $loans;
    }

    public function getById($id)
    {
        return Loan::with('details','details.expense','department')
                ->with('employee','employee.prefix','employee.position','employee.level')
                ->with('budgets','budgets.budget','budgets.budget.project','budgets.budget.project.plan')
                ->with('courses','courses.place','courses.place.changwat')
                ->find($id);
    }

    public function getInitialFormData()
    {
        $loanTypes = [
            ['id' => 1, 'name' => 'ยืมเงินโครงการ'],
            ['id' => 2, 'name' => 'ยืมเงินเดินทางไปราชการ'],
        ];

        $moneyTypes = [
            ['id' => 1, 'name' => 'เงินทดลองราชการ'],
            ['id' => 2, 'name' => 'เงินยืมนอกงบประมาณ'],
            ['id' => 3, 'name' => 'เงินยืมราชการ'],
        ];

        return [
            'departments'   => Department::all(),
            'expenses'      => Expense::all(),
            'budgets'       => Budget::with('project','project.plan')->get(),
            'loanTypes'     => $loanTypes,
            'moneyTypes'    => $moneyTypes,
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
            $loan->year             = $req['year'];
            $loan->department_id    = $req['department_id'];
            // $loan->division_id      = $req['division_id'];
            $loan->employee_id      = $req['employee_id'];
            $loan->project_no       = $req['project_no'];
            $loan->project_date     = $req['project_date'];
            $loan->project_name     = $req['project_name'];
            $loan->project_sdate    = $req['project_sdate'];
            $loan->project_edate    = $req['project_edate'];
            $loan->expense_calc     = $req['expense_calc'];
            $loan->budget_total     = currencyToNumber($req['budget_total']);
            $loan->net_total        = currencyToNumber($req['net_total']);
            $loan->remark           = $req['remark'];
            $loan->status           = 0;

            if($loan->save()) {
                foreach($req['courses'] as $item) {
                    $course = new ProjectCourse();
                    $course->seq_no         = $item['id'];
                    $course->loan_id        = $loan->id;
                    $course->course_date    = $item['course_date'];
                    $course->place_id       = $item['place_id'];
                    $course->save();
                }

                foreach($req['budgets'] as $item) {
                    $budget = new LoanBudget();
                    $budget->loan_id    = $loan->id;
                    $budget->budget_id  = $item['budget_id'];
                    $budget->total      = currencyToNumber($item['total']);
                    $budget->save();
                }

                foreach($req['items'] as $item) {
                    $course = ProjectCourse::where('loan_id', $loan->id)->where('seq_no', $item['course_id'])->first();

                    $detail = new LoanDetail();
                    $detail->loan_id        = $loan->id;
                    $detail->course_id      = $course->id;
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
