<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use PhpOffice\PhpWord\Element\Field;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use PhpOffice\PhpWord\ComplexType\TblWidth as IndentWidth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LoanContractExport;
use App\Services\LoanContractService;
use App\Models\Loan;
use App\Models\LoanContract;
use App\Models\LoanContractDetail;
use App\Models\Expense;
use App\Models\Employee;
use App\Models\Department;

class LoanContractController extends Controller
{
    /**
    * @var $contractService
    */
    protected $contractService;

    public function __construct(LoanContractService $contractService)
    {
        $this->contractService = $contractService;
    }

    public function search(Request $req)
    {
        /** Get params from query string */
        $year       = $req->get('year');
        $employee   = $req->get('employee');
        $status     = $req->get('status');

        $contracts = LoanContract::with('details','details.expense','details.loanDetail','loan.department')
                                ->with('loan.employee','loan.employee.prefix','loan.employee.position','loan.employee.level')
                                ->with('loan.budgets','loan.budgets.budget','loan.budgets.budget.activity.project','loan.budgets.budget.activity.project.plan')
                                ->with('loan.courses','loan.courses.place','loan.courses.place.changwat')
                                ->when((!auth()->user()->isAdmin() && !auth()->user()->isFinancial()), function($q) {
                                    $q->where('employee_id', auth()->user()->employee_id);
                                })
                                ->when(!empty($employee), function($q) use ($employee) {
                                    $q->where('employee_id', $employee);
                                })
                                ->when(!empty($year), function($q) use ($year) {
                                    $q->where('year', $year);
                                })
                                ->when(!empty($status), function($q) use ($status) {
                                    $q->where('status', $status);
                                })
                                ->orderBy('approved_date', 'DESC')
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
                                ->with('loan.budgets','loan.budgets.budget','loan.budgets.budget.activity.project','loan.budgets.budget.activity.project.plan')
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
                                ->orderBy('approved_date', 'DESC')
                                ->get();

        return $activities;
    }

    public function getById($id)
    {
        return LoanContract::with('details','details.expense','details.loanDetail','loan.department')
                            ->with('loan.employee','loan.employee.prefix','loan.employee.position','loan.employee.level')
                            ->with('loan.budgets','loan.budgets.budget','loan.budgets.budget.activity.project','loan.budgets.budget.activity.project.plan')
                            ->with('loan.courses','loan.courses.place','loan.courses.place.changwat')
                            ->find($id);
    }

    public function getReport($year)
    {
        return LoanContract::with('details','details.expense','details.loanDetail','loan.department')
                            ->with('loan.employee','loan.employee.prefix','loan.employee.position','loan.employee.level')
                            ->with('loan.budgets','loan.budgets.budget','loan.budgets.budget.activity.project','loan.budgets.budget.activity.project.plan')
                            ->with('loan.courses','loan.courses.place','loan.courses.place.changwat')
                            ->with('refund')
                            ->where('year', $year)
                            ->orderBy('approved_date', 'DESC')
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

        $employees = Employee::with('prefix','position','level','memberOf')
                                ->with('memberOf.duty','memberOf.department','memberOf.division')
                                ->whereIn('status', [1,5,6])
                                ->get();

        return [
            'departments'   => Department::all(),
            'expenses'      => Expense::all(),
            'loanTypes'     => $loanTypes,
            'moneyTypes'    => $moneyTypes,
            'employees'     => $employees,
        ];
    }

    public function store(Request $req)
    {
        try {
            $contract = new LoanContract();
            $contract->contract_no      = $req['contract_no'];
            $contract->year             = $req['year'];
            $contract->loan_id          = $req['loan_id'];
            $contract->employee_id      = $req['employee_id'];
            $contract->item_total       = currencyToNumber($req['item_total']);
            $contract->order_total      = currencyToNumber($req['order_total']);
            $contract->net_total        = currencyToNumber($req['net_total']);
            $contract->approved_date    = $req['approved_date'];
            $contract->sent_date        = $req['sent_date'];
            $contract->bill_no          = $req['bill_no'];
            $contract->bk02_date        = $req['bk02_date'];
            $contract->year             = $req['year'];
            $contract->refund_days      = $req['refund_days'];
            $contract->refund_notify    = 0;
            $contract->remark           = $req['remark'];
            $contract->status           = 1;

            if($contract->save()) {
                foreach($req['items'] as $item) {
                    $detail = new LoanContractDetail();
                    $detail->contract_id    = $contract->id;
                    $detail->loan_detail_id = $item['id'];
                    $detail->expense_id     = $item['expense_id'];
                    $detail->expense_group  = $item['expense_group'];
                    $detail->description    = $item['description'];
                    $detail->total          = currencyToNumber($item['total']);
                    $detail->save();
                }

                /** อัตเดต status ของตาราง loan เป็น 3=อนุมัติแล้ว */
                Loan::find($req['loan_id'])->update(['status' => 3]);

                /** Log info */
                Log::channel('daily')->info('Added new contract ID:' .$contract->id. ' by ' . auth()->user()->name);

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
            $contract->year             = $req['year'];
            $contract->loan_id          = $req['loan_id'];
            $contract->employee_id      = $req['employee_id'];
            $contract->item_total       = currencyToNumber($req['item_total']);
            $contract->order_total      = currencyToNumber($req['order_total']);
            $contract->net_total        = currencyToNumber($req['net_total']);
            $contract->approved_date    = $req['approved_date'];
            $contract->sent_date        = $req['sent_date'];
            $contract->bill_no          = $req['bill_no'];
            $contract->bk02_date        = $req['bk02_date'];
            $contract->year             = $req['year'];
            $contract->refund_days      = $req['refund_days'];
            $contract->remark           = $req['remark'];

            if($contract->save()) {
                /** Log info */
                Log::channel('daily')->info('Updated contract ID:' .$id. ' by ' . auth()->user()->name);

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
            $loanId = $contract->loan_id;

            if($contract->delete()) {
                /** Delete loan_contract_details according to deleted contract's id */
                LoanContractDetail::where('contract_id', $id)->delete();

                /** Update loans's status to 1 according to deleted contract's loan_id */
                Loan::find($loanId)->update(['status' => 1]);

                /** Log info */
                Log::channel('daily')->info('Deleted contract ID:' .$id. ' by ' . auth()->user()->name);

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

    public function deposit(Request $req, $id)
    {
        try {
            $contract = LoanContract::find($id);
            $contract->deposited_date   = $req['deposited_date'];
            $contract->refund_date      = $req['refund_date'];
            $contract->status           = 2;

            if($contract->save()) {
                /** อัพเดตตาราง loans โดยเซตค่าฟิลด์ status=4 (4=เงินเข้าแล้ว) */
                Loan::find($contract->loan_id)->update(['status' => 4]);

                /** แจ้งเตือนไปในไลน์กลุ่ม "สัญญาเงินยืม09" */
                $this->contractService->notifyDeposit($contract->load($this->contractService->getRelations()));

                /** Log info */
                Log::channel('daily')->info('Desposition of contract ID:' .$id. ' was operated by ' . auth()->user()->name);

                return [
                    'status'    => 1,
                    'message'   => 'Depositing successfully!!',
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

    public function cancel(Request $req, $id)
    {
        try {
            $contract = LoanContract::find($id);
            $contract->deposited_date   = null;
            $contract->refund_date      = null;
            $contract->status           = 1;

            if($contract->save()) {
                /** อัพเดตตาราง loans โดยเซตค่าฟิลด์ status=3 (3=อนุมัติแล้ว) */
                Loan::find($contract->loan_id)->update(['status' => 3]);

                /** Log info */
                Log::channel('daily')->info('Desposition of contract ID:' .$id. ' was cancelled by ' . auth()->user()->name);

                return [
                    'status'    => 1,
                    'message'   => 'Cancelation successfully!!',
                    'contract'  => $contract->load('details','details.expense','details.loanDetail','loan.department',
                                                    'loan.employee','loan.employee.prefix','loan.employee.position','loan.employee.level',
                                                    'loan.budgets','loan.budgets.budget','loan.budgets.budget.activity.project','loan.budgets.budget.activity.project.plan',
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

    public function export($year)
    {
        return Excel::download(new LoanContractExport($year), 'contract.xlsx');
    }
}
