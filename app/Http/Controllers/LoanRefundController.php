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
use App\Models\LoanRefund;
use App\Models\LoanRefundDetail;
use App\Models\Expense;
use App\Models\Department;

class LoanRefundController extends Controller
{
    public function search(Request $req)
    {
        /** Get params from query string */
        // $year       = $req->get('year');
        // $plan       = $req->get('plan');
        // $name       = $req->get('name');
        // $status     = $req->get('status');

        $contracts = LoanRefund::with('details','details.contractDetail.expense','contract','contract.loan','contract.loan.department')
                        ->with('contract.loan.employee','contract.loan.employee.prefix','contract.loan.employee.position','contract.loan.employee.level')
                        ->when((!auth()->user()->isAdmin() && !auth()->user()->isFinancial()), function($q) {
                            $q->where('employee_id', auth()->user()->employee_id);
                        })
                        // ->with('loan.budgets','loan.budgets.budget','loan.budgets.budget.project','loan.budgets.budget.project.plan')
                        // ->with('loan.courses','loan.courses.place','loan.courses.place.changwat')
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
                        ->orderBy('doc_date', 'DESC')
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

        $activities = LoanRefund::with('budget','budget.project','budget.project.plan')
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
                        ->orderBy('doc_date', 'DESC')
                        ->get();

        return $activities;
    }

    public function getById($id)
    {
        return LoanRefund::with('details','details.contractDetail.expense','details.contractDetail.loanDetail','contract','contract.loan')
                ->with('contract.loan.budgets','contract.loan.budgets.budget','contract.loan.courses','contract.loan.courses.place','contract.loan.department')
                ->with('contract.loan.employee','contract.loan.employee.prefix','contract.loan.employee.position','contract.loan.employee.level')
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
            $refund = new LoanRefund();
            $refund->doc_no         = $req['doc_no'];
            $refund->doc_date       = $req['doc_date'];
            $refund->contract_id    = $req['contract_id'];
            $refund->refund_type_id = $req['refund_type_id'];
            // $refund->employee_id    = $req['employee_id'];
            $refund->item_total     = currencyToNumber($req['item_total']);
            $refund->order_total    = currencyToNumber($req['order_total']);
            $refund->net_total      = currencyToNumber($req['net_total']);
            $refund->balance        = currencyToNumber($req['balance']);
            // $refund->remark         = $req['remark'];
            $refund->status         = 'N';

            if($refund->save()) {
                foreach($req['items'] as $item) {
                    $detail = new LoanRefundDetail();
                    $detail->refund_id      = $refund->id;
                    $detail->contract_detail_id = $item['contract_detail_id'];
                    $detail->description    = $item['description'];
                    $detail->total          = currencyToNumber($item['total']);
                    $detail->save();
                }

                /** อัตเดต status ของตาราง loan_contracts เป็น 3=รอเคลียร์ **/
                $contract = LoanContract::find($req['contract_id'])->update(['status' => 3]);

                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'refund'    => $refund
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
            $refund = LoanRefund::find($id);
            $refund->doc_no             = $req['doc_no'];
            $refund->doc_date           = $req['doc_date'];
            $refund->contract_id        = $req['contract_id'];
            $refund->refund_type_id     = $req['refund_type_id'];
            $refund->net_total          = currencyToNumber($req['net_total']);
            $refund->balance            = currencyToNumber($req['balance']);
            // $refund->remark           = $req['remark'];
            // $refund->status           = $req['status'] ? 1 : 0;

            if($refund->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'refund'    => $refund
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
            $refund = LoanRefund::find($id);

            /** สร้างออบเจ็ค contract จากรหัส contract_id ของ $refund */
            $contract = LoanContract::find($refund->contract_id);

            if($refund->delete()) {
                /** ลบรายการในตาราง loan_refund_details */
                LoanRefundDetail::where('refund_id', $id)->delete();

                /** Revert status ของตาราง loan_contracts เป็น 2=เงินเข้าแล้ว **/
                $contract->update(['status' => 2]);

                /** Revert status ของตาราง loans เป็น 4=เงินเข้าแล้ว **/
                Loan::find($contract->loan_id)->update(['status' => 4]);

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
            $refund = LoanRefund::find($id);
            $refund->approved_date  = $req['approved_date'];
            $refund->bill_no        = $req['bill_no'];
            $refund->bill_date      = $req['bill_date'];
            $refund->status         = 'Y';

            if($refund->save()) {
                /** อัตเดต status ของตาราง loan_contracts เป็น 4=เคลียร์แล้ว **/
                $contract = LoanContract::find($req['contract_id']);
                $contract->status = 4;
                $contract->save();

                /** อัตเดต status ของตาราง loans เป็น 5=เคลียร์แล้ว **/
                Loan::find($contract->loan_id)->update(['status' => 5]);

                return [
                    'status'    => 1,
                    'message'   => 'Approval successfully!!',
                    'refund'    => $refund->load('details','details.contractDetail.expense','contract','contract.loan',
                                                'contract.loan.budgets','contract.loan.budgets.budget','contract.loan.department',
                                                'contract.loan.employee','contract.loan.employee.prefix','contract.loan.employee.position',
                                                'contract.loan.employee.level')
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

    public function receipt(Request $req, $id)
    {
        try {
            $refund = LoanRefund::find($id);
            $refund->receipt_no     = $req['receipt_no'];
            $refund->receipt_date   = $req['receipt_date'];

            if($refund->save()) {
                /** อัตเดต status ของตาราง loan_contracts เป็น 4=เคลียร์แล้ว **/
                // $contract = LoanContract::find($req['contract_id']);
                // $contract->status = 4;
                // $contract->save();

                /** อัตเดต status ของตาราง loans เป็น 5=เคลียร์แล้ว **/
                // Loan::find($contract->loan_id)->update(['status' => 5]);

                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'refund'    => $refund->load('details','details.contractDetail.expense','contract','contract.loan',
                                                'contract.loan.budgets','contract.loan.budgets.budget','contract.loan.department',
                                                'contract.loan.employee','contract.loan.employee.prefix','contract.loan.employee.position',
                                                'contract.loan.employee.level')
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
