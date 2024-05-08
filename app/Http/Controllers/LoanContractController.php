<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\Loan;
use App\Models\LoanContract;
use App\Models\LoanContractDetail;
use App\Models\Expense;
use App\Models\Department;

class LoanContractController extends Controller
{
    public function search(Request $req)
    {
        /** Get params from query string */
        // $year       = $req->get('year');
        // $plan       = $req->get('plan');
        // $name       = $req->get('name');
        $status     = $req->get('status');

        $contracts = LoanContract::with('details','details.expense','details.loanDetail','loan.department')
                        ->with('loan.employee','loan.employee.prefix','loan.employee.position','loan.employee.level')
                        ->with('loan.budgets','loan.budgets.budget','loan.budgets.budget.project','loan.budgets.budget.project.plan')
                        ->with('loan.courses','loan.courses.place','loan.courses.place.changwat')
                        // ->when(!empty($plan), function($q) use ($plan) {
                        //     $q->whereHas('project.plan', function($sq) use ($plan) {
                        //         $sq->where('plan_id', $plan);
                        //     });
                        // })
                        ->when(!empty($status), function($q) use ($status) {
                            $q->where('status', $status);
                        })
                        // ->when(!empty($name), function($q) use ($name) {
                        //     $q->where(function($query) use ($name) {
                        //         $query->where('item_name', 'like', '%'.$name.'%');
                        //         $query->orWhere('en_name', 'like', '%'.$name.'%');
                        //     });
                        // })
                        ->paginate(10);

        return $contracts;
    }

    public function getAll(Request $req)
    {
        /** Get params from query string */
        $project    = $req->get('project');
        $plan       = $req->get('plan');
        $name       = $req->get('name');
        $status     = $req->get('status');

        $activities = LoanContract::with('details','details.expense','details.loanDetail','loan.department')
                        ->with('loan.employee','loan.employee.prefix','loan.employee.position','loan.employee.level')
                        ->with('loan.budgets','loan.budgets.budget','loan.budgets.budget.project','loan.budgets.budget.project.plan')
                        ->with('loan.courses','loan.courses.place','loan.courses.place.changwat')
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
        return LoanContract::with('details','details.expense','details.loanDetail','loan.department')
                ->with('loan.employee','loan.employee.prefix','loan.employee.position','loan.employee.level')
                ->with('loan.budgets','loan.budgets.budget','loan.budgets.budget.project','loan.budgets.budget.project.plan')
                ->with('loan.courses','loan.courses.place','loan.courses.place.changwat')
                ->find($id);
    }

    public function getReport($year)
    {
        return LoanContract::with('details','details.expense','details.loanDetail','loan.department')
                ->with('loan.employee','loan.employee.prefix','loan.employee.position','loan.employee.level')
                ->with('loan.budgets','loan.budgets.budget','loan.budgets.budget.project','loan.budgets.budget.project.plan')
                ->with('loan.courses','loan.courses.place','loan.courses.place.changwat')
                ->with('refund')
                ->where('year', $year)
                ->paginate(10);
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
            'loanTypes'     => $loanTypes,
            'moneyTypes'    => $moneyTypes,
        ];
    }

    public function store(Request $req)
    {
        try {
            $contract = new LoanContract();
            $contract->loan_id          = $req['loan_id'];
            $contract->year             = $req['year'];
            $contract->refund_days      = $req['refund_days'];
            $contract->net_total        = currencyToNumber($req['net_total']);
            $contract->remark           = $req['remark'];
            $contract->status           = 1;

            if($contract->save()) {
                foreach($req['items'] as $item) {
                    $detail = new LoanContractDetail();
                    $detail->contract_id    = $contract->id;
                    $detail->loan_detail_id = $item['id'];
                    $detail->expense_id     = $item['expense_id'];
                    $detail->description    = $item['description'];
                    $detail->total          = currencyToNumber($item['total']);
                    $detail->save();
                }

                /** อัตเดต status ของตาราง loan เป็น 3=อนุมัติแล้ว */
                Loan::find($req['loan_id'])->update(['status' => 3]);

                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'contract'  => $contract
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
            $contract = LoanContract::find($id);
            $contract->contract_no      = $req['contract_no'];
            $contract->contract_date    = $req['contract_date'];
            $contract->loan_id          = $req['loan_id'];
            $contract->net_total        = currencyToNumber($req['net_total']);
            $contract->bill_no          = $req['bill_no'];
            $contract->sent_date        = $req['sent_date'];
            $contract->bk02_date        = $req['bk02_date'];
            $contract->deposit_date     = $req['deposit_date'];
            $contract->refund_days      = $req['refund_days'];
            $contract->remark           = $req['remark'];
            $contract->status           = $req['status'];

            if($contract->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'contract'  => $contract
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
            $contract = LoanContract::find($id);

            if($contract->delete()) {
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

    public function approve(Request $req, $id)
    {
        try {
            $contract = LoanContract::find($id);
            $contract->contract_no      = $req['contract_no'];
            $contract->approved_date    = $req['approved_date'];
            $contract->sent_date        = $req['sent_date'];
            $contract->bill_no          = $req['bill_no'];
            $contract->bk02_date        = $req['bk02_date'];
            $contract->status           = 2;

            if($contract->save()) {
                /** อัพเดตตาราง loans โดยเซตค่าฟิลด์ status=3 (3=อนุมัติแล้ว) */
                Loan::find($contract->loan_id)->update(['status', 3]);

                return [
                    'status'    => 1,
                    'message'   => 'Approving successfully!!',
                    'contract'  => $contract->load('details','details.expense','details.loanDetail','loan.department',
                                        'loan.employee','loan.employee.prefix','loan.employee.position','loan.employee.level',
                                        'loan.budgets','loan.budgets.budget','loan.budgets.budget.project','loan.budgets.budget.project.plan',
                                        'loan.courses','loan.courses.place','loan.courses.place.changwat')
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

    public function deposit(Request $req, $id)
    {
        try {
            $contract = LoanContract::find($id);
            $contract->deposited_date   = $req['deposited_date'];
            $contract->refund_date      = $req['refund_date'];

            if($contract->save()) {
                /** อัพเดตตาราง loans โดยเซตค่าฟิลด์ status=4 (4=เงินเข้าแล้ว) */
                Loan::find($contract->loan_id)->update(['status', 4]);

                return [
                    'status'    => 1,
                    'message'   => 'Approving successfully!!',
                    'contract'  => $contract->load('details','details.expense','details.loanDetail','loan.department',
                                        'loan.employee','loan.employee.prefix','loan.employee.position','loan.employee.level',
                                        'loan.budgets','loan.budgets.budget','loan.budgets.budget.project','loan.budgets.budget.project.plan',
                                        'loan.courses','loan.courses.place','loan.courses.place.changwat')
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
