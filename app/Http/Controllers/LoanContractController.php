<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
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
        // $status     = $req->get('status');

        $activities = LoanContract::with('loan.budget') //,'budget.project','budget.project.plan')
                        ->with('loan.project','loan.project.place','loan.project.owner')
                        ->with('details','details.expense','loan.department')
                        ->with('loan.employee','loan.employee.prefix','loan.employee.position','loan.employee.level')
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

        $activities = LoanContract::with('budget','budget.project','budget.project.plan')
                        ->with('project','project.place','project.owner')
                        ->with('details','details.expense','department')
                        ->with('employee','employee.prefix','employee.position','employee.level')
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
        return LoanContract::with('budget','budget.project','budget.project.plan')
                ->with('project','project.place','project.owner')
                ->with('details','details.expense','department')
                ->with('employee','employee.prefix','employee.position','employee.level')
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
            'loanTypes'     => $loanTypes,
            'moneyTypes'    => $moneyTypes,
        ];
    }

    public function store(Request $req)
    {
        try {
            $contract = new LoanContract();
            $contract->contract_no      = $req['contract_no'];
            $contract->contract_date    = $req['contract_date'];
            $contract->loan_id          = $req['loan_id'];
            $contract->bill_no          = $req['bill_no'];
            $contract->sent_date        = $req['sent_date'];
            $contract->bk02_date        = $req['bk02_date'];
            $contract->deposit_date     = $req['deposit_date'];
            $contract->net_total        = currencyToNumber($req['net_total']);
            // $contract->employee_id      = $req['employee_id'];
            // $contract->remark           = $req['remark'];
            // $contract->status           = $req['status'] ? 1 : 0;

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

                Loan::find($req['loan_id'])->update(['status' => 2]);

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
            // $contract->employee_id      = $req['employee_id'];
            // $contract->remark           = $req['remark'];
            // $contract->status           = $req['status'] ? 1 : 0;

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
}
