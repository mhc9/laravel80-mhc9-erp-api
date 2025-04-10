<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Arr;
use PhpOffice\PhpWord\Element\Field;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use PhpOffice\PhpWord\ComplexType\TblWidth as IndentWidth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LoanContractExport;
use App\Services\LoanService;
use App\Services\LoanContractService;
use App\Services\LoanContractDetailService;
use App\Models\Loan;
use App\Models\LoanContract;
use App\Models\LoanContractDetail;
use App\Models\Expense;
use App\Models\Employee;
use App\Models\Department;

class LoanContractController extends Controller
{
    public function __construct(
        protected LoanContractService $contractService,
        protected LoanContractDetailService $detailService,
        protected LoanService $loanService
    ) {
        // code here
    }

    public function search(Request $req)
    {
        /** ส่งแจ้งเตือนไลน์กลุ่ม "สัญญาเงินยืม09" */
        $this->contractService->notifyRefund();

        return $this->contractService->search($req->all());
    }

    public function getAll(Request $req)
    {
        return $this->contractService->getAll();
    }

    public function getById($id)
    {
        return $this->contractService->getById($id);
    }

    public function getReport(int $year)
    {
        return $this->contractService->getReport($year);
    }

    public function getInitialFormData()
    {
        return $this->contractService->getFormData();
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
            $contractData = formatCurrency($req->except(['items']), ['item_total','order_total','net_total']);

            if($contract = $this->contractService->update($id, $contractData)) {
                /** Log info */
                Log::channel('daily')->info('Updated contract ID:' .$id. ' by ' . auth()->user()->name);

                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'contract'  => $contract->load($this->contractService->getRelations())
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
            $loanId = $this->contractService->getById($id)->loan_id;

            if($this->contractService->destroy($id)) {
                /** Delete loan_contract_details according to deleted contract's id */
                $this->detailService->destroyBy(['contract_id' => $id]);

                /** Update loans's status to 1 according to deleted contract's loan_id */
                $this->loanService->update($loanId, ['status' => 1]);

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
            $depositData = Arr::add($req->only(['deposited_date','refund_date']), 'status', 2);

            if($contract = $this->contractService->update($id, $depositData)) {
                /** อัพเดตตาราง loans โดยเซตค่าฟิลด์ status=4 (4=เงินเข้าแล้ว) */
                $this->loanService->update($contract->loan_id, ['status' => 4]);

                /** แจ้งเตือนไปในไลน์กลุ่ม "สัญญาเงินยืม09" */
                $this->contractService->notifyDeposit($contract->load($this->contractService->getRelations()));

                /** Log info */
                Log::channel('daily')->info('Desposition of contract ID:' .$id. ' was operated by ' . auth()->user()->name);

                return [
                    'status'    => 1,
                    'message'   => 'Depositing successfully!!',
                    'contract'  => $contract->load($this->contractService->getRelations())
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
            $cancelData = addMultipleInputs([], ['deposited_date' => null,'refund_date' => null,'status' => 1]);

            if($contract = $this->contractService->update($id, $cancelData)) {
                /** อัพเดตตาราง loans โดยเซตค่าฟิลด์ status=3 (3=อนุมัติแล้ว) */
                $this->loanService->update($contract->loan_id, ['status' => 3]);

                /** Log info */
                Log::channel('daily')->info('Desposition of contract ID:' .$id. ' was cancelled by ' . auth()->user()->name);

                return [
                    'status'    => 1,
                    'message'   => 'Cancelation successfully!!',
                    'contract'  => $contract->load($this->contractService->getRelations())
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
